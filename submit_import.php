<?php
/**
 * api: freshcode
 * title: Import project description
 * description: Allow DOAP/JSON/etc. import prior manual /submit form intake.
 * version: 0.3
 *
 *
 * Checks for uploaded $_FILES or ?import_url=
 *  → Deciphers project name, description, license, tags, etc.
 *  → Passes on extra $data to /submit <form>
 *  → 
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
    static function fetch() {
    
        #-- file upload?
        if (!empty($_FILES[UP_IMPORT_FILE]["tmp_name"])) {
            $data = file_get_contents($_FILES[UP_IMPORT_FILE]["tmp_name"]);
        }
        
        #-- import scheme, and project name
        $type = $_REQUEST->id[UP_IMPORT_TYPE];
        $name = $_REQUEST->text[UP_IMPORT_NAME];

        if ($type and ($data or $name)) {
            $i = new self;
            return (array)($i->convert($type, $data, $name));
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
               return $this->PKG_INFO($data);
            case "DOAP":
               return $this->DOAP($data);
            case "FREECODE":
               return $this->FREECODE($name);
            default:
               return array();
        }
    }
    
    
    /**
     * Extract from common JSON formats.
     *  → common.js (research-standardized)
     *  → package.json (npm)
     *  → bower.json (jquery)
     *  → composer.json (php)
     *  → pypi.json (python)
     *  → releases.json (native freshcode scheme)
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
     * Extracts from a PKG-INFO text file.
     *
     */
    function PKG_INFO($data) {
    
        // Simple rfc822-style KEY: VALUE format.
        preg_match_all("/^([\w-]+):\s*(.+)$/m", $data, $uu)
        and $data = array_change_key_case(array_combine($uu[1], $uu[2]), CASE_LOWER);

        // Test if it's PKG-INFO
        if (!empty($data["description"])) {

            return @array(
                "title" => $data["name"],
                "version" => $data["version"],
                "description" => $data["description"],
                "tags" => preg_replace("/[\s,;]+/", ", ", "$data[platform], $data[keywords]"),
              # "trove-tags" => $data["classifiers"],
                "homepage" => $data["home-page"],
                "download" => $data["download-url"],
                "license" => tags::map_license($data["license"]),
            );
        }
    }

    /**
     * Import from DOAP description.
     * Would actually require a RDF toolkit,
     * but for the simple use case here, it's just namespace-free xml processed.
     *
     *  <Project xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns="http://usefulinc.com/ns/doap#" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:admin="http://webns.net/mvcb/">
     *  <name>ex-name</name>
     *  <shortname>shortex</shortname>
     *  <shortdesc>shortdesc</shortdesc>
     *  <description>desc</description>
     *  <homepage rdf:resource="homepage"/>
     *  <wiki rdf:resource="wiki"/>
     *  <download-page rdf:resource="download"/>
     *  <download-mirror rdf:resource="mirr"/>
     *  <bug-database rdf:resource="bugs"/>
     *  <category rdf:resource="all the tags"/>
     *  <programming-language>php</programming-language>
     *  <license rdf:resource="http://usefulinc.com/doap/licenses/bsd"/>
     *  </Project>
     */
    function DOAP($data) {
        if ($x = simplexml_load_string($data)->Project) {
            $x = @array(
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
                return @array(
                    "name" => $name,
                    "title" => $uu["title"],
                    "description" => $uu["description"],
                    "tags" => strtolower((!empty($uu["lang"]) ? "$uu[lang], " : "") . $uu["tags"]),
                    "license" => tags::map_license($uu["license"]),
                );
            }        
        }
    }

}


#$s = new project_import();
#print_r($s->freecode("firefox"));



?>