<?php
/**
 * title: GitHubArchive releases poll
 * description: Fetch GitHub releases via BigQuery githubarchive:github.timeline
 * version: 0.4
 * depends: config.local
 * doc: http://www.githubarchive.org/
 * github-url: https://github.com/igrigorik/githubarchive.org
 *
 * Queries githubarchive.org event blobs.
 * (Fetching via Google BigQuery too easily exceeded the quotas.)
 *
 * JSON blobs are stored under:
 *    http://data.githubarchive.org/2014-10-30-{0..23}.json.gz
 * which contain newline-separated JSON objects.
 *   → fetched via SplFileObject and gzip-decoding stream prefix
 *   → pre-filtered for "type":"ReleaseEvent" by RegexIterator
 *   → merged with repo meta data (desc, urls, lang) per GitHub API
 *
 * Stores everything into github.db cache table.
 *
 *    CREATE TABLE releases ( 
 *        t_published      INT,
 *        created_at       VARCHAR,
 *        repo_name        VARCHAR,
 *        author_login     VARCHAR,
 *        author_avatar    VARCHAR,
 *        release_url      VARCHAR UNIQUE,
 *        release_tag      VARCHAR,
 *        release_title    VARCHAR,
 *        release_body     TEXT,
 *        repo_url         VARCHAR,
 *        repo_description TEXT,
 *        repo_homepage    VARCHAR,
 *        repo_language    VARCHAR,
 *        UNIQUE ( repo_name, release_tag )  ON CONFLICT FAIL 
 *    );
 *    CREATE INDEX idx_releases ON releases ( 
 *        t_published ASC 
 *    );
 *    CREATE INDEX unique_releases ON releases ( 
 *        repo_name,
 *        release_tag 
 *    );
 *
 * Which allows easier display/feed generation for news_github.php.
 *
 */


// Common settings
chdir(dirname(__DIR__));
include("./config.php");
// Separate github.releases database
db(new PDO("sqlite:github.db"));




/**
 * GitHubArchive via Google BigQuery
 * (unused now)
 *
 */
class GHA_BQ {

    /**
     * Google API OAuth connection
     *
     */
    function Google_API_Client() {
        $client = new Google_Client();
        $client->setApplicationName("freshcode-github");
        $client->setDeveloperKey(GOOGLEAPI_DEV_KEY);
        $client->setClientId(GOOGLEAPI_CLIENT_ID);
        $cred = new Google_Auth_AssertionCredentials(
             GOOGLEAPI_EMAIL,
             array('https://www.googleapis.com/auth/bigquery'),
             file_get_contents(GOOGLEAPI_KEYFILE)
        );
        $client->setAssertionCredentials($cred);
        $client->getAuth()->isAccessTokenExpired() and $client->getAuth()->refreshTokenWithAssertion($cred);
        return $client;
    }


    /**
     * Populates BigQuery configuration and Job,
     * executes it right away, and waits for response.
     *
     */
    function BigQuery_execute($sql) {

        // new BigQuery + Google_Client instances
        $client = new Google_Service_Bigquery( self::Google_API_Client() );
        
        // create query job
        $job = new Google_Service_Bigquery_Job();
        $config = new Google_Service_Bigquery_JobConfiguration();
        $queryConfig = new Google_Service_Bigquery_JobConfigurationQuery();
        $queryConfig->setQuery($sql);
        $queryConfig->setPriority("INTERACTIVE");  // speedier results
        $config->setQuery($queryConfig);
        $job->setId(md5(microtime()));
        $job->setConfiguration($config);

        // run job and pack results
        $res = $client->jobs->getQueryResults(
            GOOGLEAPI_PROFILE,
            $client->jobs->insert(GOOGLEAPI_PROFILE, $job)->getJobReference()["jobId"]
        );
        return $res;
    }


    // run query for recent project releases
    function githubarchive_releases() {
       return self::BigQuery_execute("
           SELECT
                TIMESTAMP(created_at) as t_published,
                created_at, url, type,
                repository_url, repository_owner, repository_name,
                repository_description, repository_language, repository_homepage
                -- , payload_name, payload_url, payload_desc, payload_commit, payload_member_avatar_url
                -- , payload_release_tag_name, payload_release_name, payload_release_body
             FROM
                [githubarchive:github.timeline]
            WHERE
                type='ReleaseEvent'
                -- AND LENGTH(repository_description) > 0
         ORDER BY
                created_at DESC
            LIMIT
                500
        ");
    }
}




/**
 * Query GitHub projects (max 5000/hour)
 *
 */
class GitHub_API {

    /**
     * Generic wrapper for simple GitHub API
     *
     */
    function call($api = "events", $data = []) {
        return json_decode(
            curl("https://api.github.com/$api")
            ->userpwd(GITHUB_API_PW)
            //->writeheader(fopen("gh-curl-header.txt", "a+"))
            ->timeout(5)
            ->exec()
        );
    }


    /**
     * Retrieve Github global /events (push, pull, commit, comment, release, ...)
     *
     */
    function events() {
        return self::call("events");
    }


    /**
     * Fetch meta data for repository
     *
     */
    function repo_meta($fullname) {
        return self::call("repos/$fullname");
    }


    /**
     * HTML extract repo title
     * @obsolete, see `repo_meta`
     *
     */
    function repo_title($gh_url) {
        preg_match('~<meta\s+content="([^<>"]+?)"\s+property="og:description"\s+/?>~s', curl($gh_url)->exec(), $m);
        $gh_title = htmlentities(html_entity_decode($m[1]));
        if (preg_match("/Contribute to .+? development by creating an account on GitHub./", $gh_title)) {
            continue;
        }
    }


    /**
     * Normalize Github.com URL, split full, owner and repo name
     *
     */
    function repo_name($url) {
        if (preg_match("~^https?://github\.com/([\w.-]+)/([\w.-]+)(?:/|$)~", $url, $m)
        or  preg_match("~^https?://(?:api|uploads)\.github\.com/(?:repos|users)/([\w.-]+)/([\w.-]+)(?:/|$)~", $url, $m))
        {
            return array("https://github.com/$m[1]/$m[2]/", "$m[1]/$m[2]", $m[1], $m[2]);
        }
    }

}




/**
 * Retrieve event archives from //data.githubarchive.org/YYYY-MM-DD-HH.json.gz
 *
 */
class GitHubArchive {
    
    /**
     * date/timespec for GHA
     *
     */
    function last_hour($hour=1) {
        // return 2014-05-31-hH (minus 8 hours, because GHA uses filenames according to its own timezone)
        return gmdate("Y-m-d-G", time() - 5*60 - (7 + $hour) * 3600);
    }


    /**
     * Fetch GHA day+hour .json.gz resource line-wise filtered by ReleaseEvent type
     *
     */
    function json_lines($day_hour) {
        // open remote resource and decompress implicitly
        $gz = new SplFileObject("compress.zlib://http://data.githubarchive.org/$day_hour.json.gz");
        // filter JSON blob lines on type=ReleaseEvents
        return new RegexIterator($gz, '/ "type" : "ReleaseEvent" /smux');
    }

    
    /**
     * Iterator over last GitHubArchive data files
     *
     */
    function fetch_json_last_hours($count = 2) {
        $it = new AppendIterator;
        foreach (range(1, 24) as $prev) {
            // Keep iterating for successful URL accesses
            try {
                $it->append(self::json_lines($day_hour = self::last_hour($prev)));
                print "fetching .../$day_hour.json.gz\n";
            }
            catch (Exception $http_fail) {  // SplFileObject just gives RuntimeException for HTTP 404 results
                print "not present .../$day_hour.json.gz\n";
                continue;
            }
            // Retrieve at least two blob files
            if (--$count <= 0) {
                break;
            }
        }
        return $it;
    }

}


/**
 * Apply callback over iterator values.
 *
 */
class CallbackIterator extends IteratorIterator
{
    public $mapfn;

    function __construct($iter, $mapfn) {
        $this->mapfn = $mapfn;
        parent::__construct($iter);
    }

    function current() {
        return call_user_func($this->mapfn, parent::current());
    }
}




#-- Select input source,
#   JSON lines/blobs/files, pre-converted to objects

// from GHA files
$in_json = new CallbackIterator(GitHubArchive::fetch_json_last_hours(), "json_decode");

// direct polling of GitHub API (priorly polled in a loop)
#$in_json = GitHub_API::events();

// previously collected event blobs
#$in_json = new CallbackIterator(new CallbackIterator(new GlobIterator("./@/github/ev*"), "file_get_contents"), "json_decode");



#-- Load into cache database
insert_github_releases($in_json);




/**
 * Traverse github /events JSON objects and update DB.
 *
 */
function insert_github_releases($in_json) {
    foreach ($in_json as $event) {

        // Properties from events payload
        $p = array(
            "t_published" => strtotime($event->payload->release->published_at),
            "created_at" => $event->payload->release->created_at,
            "repo_name" => GitHub_API::repo_name($event->payload->release->html_url)[1],
            "author_login" => $event->payload->release->author->login,
            "author_avatar" => $event->payload->release->author->avatar_url,
            "release_url" => $event->payload->release->html_url,
            "release_tag" => $event->payload->release->tag_name,
            "release_title" => $event->payload->release->name,
            "release_body" => $event->payload->release->body,
        );

        // Skip existing entries    
        $exists = db("
             SELECT t_published
               FROM releases
              WHERE release_url = ?
                 OR (repo_name = ? AND release_tag = ?)",
             $p["release_url"], $p["repo_name"], $p["release_tag"]
        );
        if ($exists->t_published) {
            print "already have $p[release_url]\n";
            continue;
        }
        
        // Add additional repository infos
        if ($meta = GitHub_API::repo_meta($p["repo_name"])) {
            $p = $p + array(
                "repo_url" => $meta->html_url,
                "repo_description" => $meta->description,
                "repo_homepage" => $meta->homepage,
                "repo_language" => $meta->language,
            );
        }
        else {
            continue;
        }

        // Store
        print_r($p);
        db("INSERT INTO releases (:?) VALUES (::)", $p, $p);
    }
}



?>