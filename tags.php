<?php
/**
 * API: freshcode
 * title: Tags and Trove
 * description: Provides categorization backend for tree-mapped tags and Trove grouping.
 * version: 0.1
 * type: library
 * category: taxonomy
 * doc: http://fossil.include-once.org/freshcode/wiki/Trove+map
 * license: mixed
 *
 * This module provides major tags in a tree, which serves as base for trove categories.
 * 
 *  → Still permits free-form tags.
 *  → Provides for aliasing.
 *  → Only major topic tags end up in trove tree.
 *  → Allows to map licenses from and to tags.
 *  → Handles some HTML and JS output.
 *
 * 
 */


/**
 * Foremost bundles static arrays for tags.
 *
 * @static
 * @dataProvider map
 *
 */
class Tags {


    /**
     * License monikers and full names.
     *
     */
    static public $licenses = [
        "" => "Unspecified",
        "Apache" => "Apache License 2.0",
        "Artistic" => "Artistic license 2.0",
        "BSDL" => "BSD 3-Clause 'New/Revised' License",
        "BSDL-2" => "BSD 2-Clause 'Simplified/FreeBSD' License",
        "CDDL" => "Common Development and Distribution License 1.0",
        "MITL" => "MIT license",
        "MPL" => "Mozilla Public License 2.0",
        "Public Domain" => "Public Domain (no copyright)",
        "Python" => "Python License",
        "PHPL" => "PHP License 3.0",
        "GNU GPL" => "GNU General Public License 2.0",
        "GNU GPLv3" => "GNU General Public License 3.0",
        "GNU LGPL" => "GNU Library/Lesser General Public License 2.1",
        "GNU LGPLv3" => "GNU Library/Lesser General Public License 3.0",
        "Affero GPL" => "Affero GNU Public License 2.0",
        "Affero GPLv3" => "GNU Affero General Public License v3",
        "AFL" => "Academic Free License 3.0",
        "APL" => "Adaptive Public License",
        "APSL" => "Apple Public Source License",
        "AAL" => "Attribution Assurance Licenses",
        "BSL" => "Boost Software License",
        "CECILL" => "CeCILL License 2.1",
        "CATOSL" => "Computer Associates Trusted Open Source License 1.1",
        "CDDL" => "Common Development and Distribution License 1.0",
        "CPAL" => "Common Public Attribution License 1.0",
        "CUA" => "CUA Office Public License Version 1.0",
        "EUDatagrid" => "EU DataGrid Software License",
        "EPL" => "Eclipse Public License 1.0",
        "ECL" => "Educational Community License, Version 2.0",
        "EFL" => "Eiffel Forum License V2.0",
        "Entessa" => "Entessa Public License",
        "EUPL" => "European Union Public License, Version 1.1 (EUPL-1.1)",
        "Fair" => "Fair License",
        "Frameworx" => "Frameworx License",
        "HPND" => "Historical Permission Notice and Disclaimer",
        "IPL" => "IBM Public License 1.0",
        "IPA" => "IPA Font License",
        "ISC" => "ISC License",
        "LPPL" => "LaTeX Project Public License 1.3c",
        "LPL" => "Lucent Public License Version 1.02",
        "MirOS" => "MirOS Licence",
        "MS" => "Microsoft Reciprocal License",
        "MIT" => "MIT license",
        "Motosoto" => "Motosoto License",
        "Multics" => "Multics License",
        "NASA" => "NASA Open Source Agreement 1.3",
        "NTP" => "NTP License",
        "Naumen" => "Naumen Public License",
        "NGPL" => "Nethack General Public License",
        "Nokia" => "Nokia Open Source License",
        "NPOSL" => "Non-Profit Open Software License 3.0",
        "OCLC" => "OCLC Research Public License 2.0",
        "OFL" => "Open Font License 1.1",
        "OGTSL" => "Open Group Test Suite License",
        "OSL" => "Open Software License 3.0",
        "PostgreSQL" => "The PostgreSQL License",
        "CNRI" => "CNRI Python license (CNRI-Python)",
        "QPL" => "Q Public License",
        "RPSL" => "RealNetworks Public Source License V1.0",
        "RPL" => "Reciprocal Public License 1.5",
        "RSCPL" => "Ricoh Source Code Public License",
        "SimPL" => "Simple Public License 2.0",
        "Sleepycat" => "Sleepycat License",
        "SPL" => "Sun Public License 1.0",
        "Watcom" => "Sybase Open Watcom Public License 1.0",
        "NCSA" => "University of Illinois/NCSA Open Source License",
        "VSL" => "Vovida Software License v. 1.0",
        "W3C" => "W3C License",
        "WXwindows" => "wxWindows Library License",
        "Xnet" => "X.Net License",
        "ZPL" => "Zope Public License 2.0",
        "Zlib" => "zlib/libpng license",
        "Other" => "Other License",
        "Mixed" => "Multiple Licenses",
    ];   // todo: Dicuss entry for Commercial/Proprietary code anyhow.
         // hint: Separation usually works better than prohibition.
         //       (Filtering instead of cleanups)


    /**
     * Tag aliases.
     *
     */
    static public $alias = [
        "email" => "e-mail",
    ];


    /**
     * Tag tree.
     *
     */
    static public $tree = [
    ];


    /**
     * Try to map SPDX.org names onto our license tags,
     * or find entry in long description;
     *
     */
    function map_license($id) {

        // exact find
        if (isset(tags::$licenses[$id])) {
            return $id;
        }
        
        // partial matches
        if (preg_match_all("/\d+.\d+|\w+/", $id, $p)  and  ($p = $p[0])
        and (preg_grep("/$p[0].+$p[1]/", tags::$licenses, $match)
          or preg_grep("/$p[0]/", tags::$licenses, $match)))
        {
            return key($match);
        }
    }

    /**
     * Get leaves from Trove categories
     *
     */
    function trove_to_tags($array, $tags="") {
        foreach (preg_grep("/^Topic :: .+ :: \w+$/", $array) as $trove) {
            $tags .= ", " . trim(strrchr($trove, ":"), ": ");
        }
        return $tags;
    }

}



?>