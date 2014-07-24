<?php
/**
 * api: freshcode
 * title: Import project description
 * description: Allow DOAP/JSON/etc. import prior manual /submit form intake.
 * version: 0.5
 *
 *
 * Checks for uploaded $_FILES or ?import_url=
 *  → Deciphers project name, description, license, tags, etc.
 *  → Passes on extra $data to /submit <form>
 *
 */



define("UP_IMPORT_TYPE", "import_via");
define("UP_IMPORT_FILE", "import_file");
define("UP_IMPORT_NAME", "import_name");



/**
 * Invoked by page_submit itself to populate any empty $data set.
 *
 */
class project_import {


    /**
     * Evaluate request params, and import data if any.
     *
     */
    static function fetch($data=NULL) {
    
        #-- file upload?
        if (!empty($_FILES[UP_IMPORT_FILE]["tmp_name"])) {
            $data = file_get_contents($_FILES[UP_IMPORT_FILE]["tmp_name"]);
        }
        
        #-- import scheme, and project name
        $type = $_REQUEST->id[UP_IMPORT_TYPE];
        $name = $_REQUEST->text[UP_IMPORT_NAME];

        if ($type and ($data or $name)) {
            $i = new self;
            return (array)@($i->convert($type, $data, $name));
        }
        else {
            return array();
        }
    }

    
    /**
     * Dispatch to submodules.
     *
     */
    function convert($type, $data, $name) {
    
        #-- switch to fetch methods
        switch (strtoupper($type)) {

            case "JSON":
               return $this->JSON($data);

            case "PKG-INFO":
            case "PKGINFO":
            case "LSM":
            case "DEBIAN":
            case "RPMSPEC":
               return $this->PKG_INFO($data);

            case "DOAP":
               return $this->DOAP($data);

            case "FREECODE":
               return $this->FREECODE($name);

            case "SOURCEFORGE":
               return $this->SOURCEFORGE($name);

            default:
               return array();
        }
    }

    
    
    /**
     * Extract from common JSON formats.
     *
     *   release.json  common.js     package.json  bower.json    composer.json   pypi.json
     *   ------------- ------------- ------------- ------------- --------------- -------------
     *   name          name          name          name          name            name
     *   version       version       version       version       version         version
     *   title                                                                     
     *   description   description   description   description   description     description
     *   homepage      homepage      homepage      homepage      homepage        home_page
     *   license       licenses*     license       license*      license         license
     *   image
     *   state                                                                   classifiers
     *   download                    repository    repository                    download_url
     *   urls*         repositories                              repositories    release_url
     *   tags          keywords      keywords      keywords      keywords        keywords
     *   trove                                                                   classifiers
     *
     */
    function JSON($data) {
    
        // check if it is actually json
        if ($data = json_decode($data, TRUE)) {


            // rename a few plain fields
            $map = array(
                "name" => "title",            // title is commonly absent
                "screenshot" => "image",
                "home_page" => "homepage",    // pypi
                "download_url" => "download", // pypi
                "summary" => "description",   // pypi
                "release_url" => "urls",      // pypi
            );
            foreach ($map as $old=>$new) {
                if (empty($data[$new]) and !empty($data[$old])) {
                    $data[$to] = $data[$from];
                }
            }


            // complex mapping
            $map = array(
                 "keywords" => "tags",
                 "classifiers" => "tags",
                 "licenses" => "license",
                 "license" => "license",
                 "repository" => "urls",
                 "repositories" => "urls",
                 "urls" => "urls",
            );
            foreach ($map as $old=>$new) {
                if (!empty($data[$old])) {
                    switch ($old) {

                        // keywords (common.js, composer.json) become tags
                        case "keywords":                        
                            $data[$new] = strtolower(join(", ", $data[$old]));
                            break;

                        // Trove classifiers (pypi)
                        case "classifiers":
                            $data[$new] = tags::trove_to_tags($data[$old]);
                            break;

                        // license alias  // see spdx.org
                        case "licenses":
                        case "license":
                            while (is_array($data[$old])) {
                                $data[$old] = current($data[$old]);
                            }
                            $data[$new] = tags::map_license($data[$old]);
                            break;

                        // URLs
                        case "repository":
                            $data[$new] = $data[$old]["type"] . "=" . $data[$old]["url"] . "\n";
                            break;
                        case "repositories":
                            $data[$new] = http_build_query(array_column($data[$old], "url", "type"), "", "\n");
                            break;
                        case "urls":
                            is_array($data[$old]) and
                            $data[$new] = http_build_query(array_column($data[$old], "url", "packagetype"), "", "\n");
                            break;
                        
                    }
                }
            }
            

            // common fields from releases.json are just kept asis
            $asis = array(
                "name", "title", "homepage", "description",
                "license", "tags", "image", "version", "state",
                "scope", "changes", "download", "urls",
                "autoupdate_module", "autoupdate_url", "autoupdate_regex",
                "submitter", "lock",
            );

            // done
            return(
                array_filter(
                    array_intersect_key($data, array_flip($asis)),
                    "is_string"
                )
            );
        }

    }



    /**
     * Extracts from PKG-INFO and other RFC822-style text files.
     *
     *  used   PKG-INFO       LSM            Debian        RPMSpec
     *  ----   -------------  -------------  ------------  -------
     *   →     Name           Title          Package       Name
     *   →     Version        Version        Version       Version
     *   →     Description    Description    Description   
     *   →     Summary                                     Summary
     *   →     Home-Page      Primary-Site   Homepage      URL
     *         Author         Author                       Vendor
     *   →     License        Coding-Policy                Copyright
     *   →     Keywords       Keywords       Section       Group
     *         Classifiers                                 
     *   →     Platform       Platforms                    
     *
     *  [1] http://legacy.python.org/dev/peps/pep-0345/
     *  [2] http://lsm.execpc.com/LSM.README
     *  [3] http://www.debian.org/doc/debian-policy/ch-controlfields.html
     *  [4] http://www.rpm.org/max-rpm/s1-rpm-build-creating-spec-file.html
     *
     */
    function PKG_INFO($data) {
    
        // Simple KEY: VALUE format (value may span multiple lines).
        preg_match_all("/
                ^ %?
                ([\w-]+): \s*
                (.+ (?:\R[\v].+$)* )
                $
            /xm", $data, $uu
        )
        and $data = array_change_key_case(array_combine($uu[1], $uu[2]), CASE_LOWER);

        // Test if it's PKG-INFO
        if (!empty($data["description"])) {

            return array(
                "title" => $data["name"] ?: $data["title"],
                "version" => $data["version"],
                "description" => $data["description"] ?: $data["summary"],
                "tags" => preg_replace("/[\s,;]+/", ", ", "$data[platform], $data[keywords]"),
              # "trove-tags" => $data["classifiers"],
                "homepage" => $data["home-page"] ?: $data["url"] ?: $data["homepage"] ?: $data["primary-site"],
                "download" => $data["download-url"],
                "license" => tags::map_license($data["license"] ?: $data["coding-policy"] ?: $data["copyright"]),
            );
        }
    }



    /**
     * Import from DOAP description.
     *
     * Would actually require a RDF toolkit,
     * but for the simple use case here, it's just processed namespace-unaware as xml.
     *
     */
    function DOAP($data) {
        if ($x = simplexml_load_string($data)->Project) {
            $x = array(
                "name" => strval($x->shortname),
                "title" => strval($x->name),
                "description" => strval($x->description ?: $x->shortdesc),
                "homepage" => strval($x->homepage["resource"]),
                "download" => strval($x->{'download-page'}["resource"]),
                "tags" => strval($x->{'programming-language'}) .", ". strval($x->category["resource"]),
                "license" => tags::map_license(basename(strval($x->license["resource"]))),
                "version" => strval($x->release->Version->revision),
            );
            return $x;
        }
    }



    /**
     * Freecodes JSON API is gone, so we have to extract from the project
     * page itself.
     *
     */
    function FREECODE($name) {
        include_once("lib/curl.php");

        // retrieve
        if ($html = curl("http://freecode.com/projects/$name")->exec()) {
        
            // regex extract to reduce false positives
            preg_match_all('~
                  <meta \s+ property="og:title" \s+ content="(?<title>[^"]+)"
               |  <meta \s+ name="keywords" \s+ content="(?<tags>[^"]+)"
               |  class="project-detail">  \s+  <p>  (?<description>[^<>]+)</p>
               |  >Licenses< .+? rel="tag">  (?<license>[^<>]+)</a>
               |  >Implementation< .+? rel="tag">  (?<lang>[^<>]+)</a>
            ~smix', $html, $uu, PREG_SET_ORDER);

            // join fields
            if (!empty($uu[0][0])) {
                $uu = call_user_func_array("array_merge", array_map("array_filter", $uu));
                return array(
                    "name" => $name,
                    "title" => $uu["title"],
                    "description" => $uu["description"],
                    "tags" => strtolower((!empty($uu["lang"]) ? "$uu[lang], " : "") . $uu["tags"]),
                    "license" => tags::map_license($uu["license"]),
                );
            }        
        }
    }



    /**
     * Sourceforge still provides a JSON export.
     *
     */
    function SOURCEFORGE($name) {
        include_once("lib/curl.php");

        // retrieve
        if ($data = json_decode(curl("https://sourceforge.net/rest/p/$name")->exec(), TRUE)) {

            // custom json extraction
            return array(
                "name" => $data["shortname"],
                "title" => $data["name"],
                "homepage" => $data["external_homepage"] ?: $data["url"],
                "description" => $data["short_description"],
                "image" => $data["screenshots"][0]["thumbnail_url"],
                "license" => tags::map_license($data["categories"]["license"][0]["fullname"]),
                "tags" => implode(", ",
                    array_merge(
                        array_column($data["categories"]["language"], "shortname"),
                        array_column($data["categories"]["environment"], "fullname"),
                        array_column($data["categories"]["topic"], "shortname")
                    )
                ),
                "state" => $data["categories"]["developmentstatus"][0]["shortname"]
            );
        }
    }

}


#print_r((new project_import)->freecode("firefox"));



?>