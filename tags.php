<?php
/**
 * API: freshcode
 * title: Tags and Trove
 * description: Provides categorization backend for tree-mapped tags and Trove grouping.
 * version: 0.2
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
        "BSDL-4" => "BSD 4-Clause 'Old' License",
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
        "MPL-1" => "Mozilla Public License 1.x (Netscape)",
        "MS-RL" => "Microsoft Reciprocal License",
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
    static public $tree =
        [
        "Topic" => [
            "Adaptive Technologies",
            "Artistic Software",
            "Communication",
            "Communication" => [
                "BBS",
                "Chat",
                "Chat" => [
                    "ICQ",
                    "Internet Relay Chat",
                    "Skype",
                    "Unix Talk",
                    "XMPP"
                ],
                "Conferencing",
                "Email",
                "Email" => [
                    "Address Book",
                    "Email Client",
                    "Email Filter",
                    "Mailing List Server",
                    "Mail Transport Agent",
                    "IMAP",
                    "POP3"
                ],
                "Fax",
                "FIDO",
                "File Sharing",
                "Ham Radio",
                "Internet Phone",
                "Telephony",
                "Usenet"
            ],
            "Database",
            "Database" => [
                "Database-server",
                "Front-End"
            ],
            "Desktop",
            "Desktop" => [
                "File Manager",
                "Gnome",
                "GNUstep",
                "KDE",
                "PicoGUI",
                "Screen Savers",
                "Window Manager",
                "Window Manager" => [
                    "Afterstep",
                    "Applet",
                    "Blackbox",
                    "CTWM",
                    "Enlightenment",
                    "Fluxbox",
                    "FVWM",
                    "IceWM",
                    "MetaCity",
                    "Openbox",
                    "Oroborus",
                    "Sawfish",
                    "Waimea",
                    "Window Maker",
                    "XFCE"
                ]
            ],
            "Documentation",
            "Education",
            "Education" => [
                "Computer Aided Instruction",
                "Testing"
            ],
            "Game",
            "Game" => [
                "Arcade",
                "Board Game",
                "First Person Shooter",
                "Fortune Cookies",
                "Multi-User Dungeons",
                "Puzzle",
                "Real Time Strategy",
                "Role-Playing",
                "Side-Scrolling",
                "Simulation",
                "Turn Based Strategy"
            ],
            "Home Automation",
            "Internet",
            "Internet" => [
                "FTP",
                "Finger",
                "Log Analysis",
                "DNS",
                "Proxy Server",
                "WAP",
                "WWW",
                "WWW" => [
                    "Browser",
                    "Dynamic Content",
                    "Dynamic Content" => [
                        "CGI Library",
                        "Message Board",
                        "News/Diary",
                        "Page Counter"
                    ],
                    "HTTP Server",
                    "Indexing/Search",
                    "Session",
                    "Site Management",
                    "Site Management" => [
                        "Link Checking"
                    ],
                    "WSGI"
                ]
            ],
            "Multimedia",
            "Multimedia" => [
                "Graphics",
                "Graphics" => [
                    "3D Modeling",
                    "3D Rendering",
                    "Capture",
                    "Capture" => [
                        "Digital Camera",
                        "Scanner",
                        "Screen Capture"
                    ],
                    "Editor",
                    "Editor" => [
                        "Raster-Based",
                        "Vector-Based"
                    ],
                    "Graphics Conversion",
                    "Presentation",
                    "Viewer"
                ],
                "Audio",
                "Audio" => [
                    "Analysis",
                    "Recording",
                    "CD Audio",
                    "CD Audio" => [
                        "CD Playing",
                        "CD Ripping",
                        "CD Writing"
                    ],
                    "Conversion",
                    "Editors",
                    "MIDI",
                    "Mixers",
                    "Player",
                    "Player" => [
                        "MP3"
                    ],
                    "Sound Synthesis",
                    "Speech"
                ],
                "Video",
                "Video" => [
                    "Capture",
                    "Conversion",
                    "Display",
                    "Non-Linear Editor"
                ]
            ],
            "Office",
            "Office" => [
                "Financial",
                "Financial" => [
                    "Accounting",
                    "Investment",
                    "Point-Of-Sale",
                    "Spreadsheet"
                ],
                "Groupware",
                "News/Diary",
                "Office Suite",
                "Scheduling"
            ],
            "Printing",
            "Religion",
            "Scientific",
            "Scientific" => [
                "Artificial Intelligence",
                "Artificial Life",
                "Astronomy",
                "Atmospheric Science",
                "Bio-Informatics",
                "Chemistry",
                "Electronic Design Automation",
                "GIS",
                "Human Machine Interfaces",
                "Image Recognition",
                "Information Analysis",
                "Interface Engine",
                "Mathematics",
                "Medical Science",
                "Physics",
                "Visualization"
            ],
            "Security",
            "Security" => [
                "Cryptography"
            ],
            "Sociology",
            "Sociology" => [
                "Genealogy",
                "History"
            ],
            "Software Development",
            "Software Development" => [
                "Assembler",
                "Bug Tracking",
                "Build Tool",
                "Code Generator",
                "Compiler",
                "Debugger",
                "Disassembler",
                "Documentation",
                "Embedded Systems",
                "Internationalization",
                "Interpreter",
                "Library",
                "Library" => [
                    "Application Framework",
                    "Java Library",
                    "Perl Module",
                    "PHP Class",
                    "Pike Module",
                    "pygame",
                    "Python Module",
                    "Ruby Modules",
                    "Tcl Extension"
                ],
                "Localization",
                "Object Brokering",
                "Object Brokering" => [
                    "CORBA",
                    "D-Bus",
                    "SOAP"
                ],
                "Pre-processor",
                "Quality Assurance",
                "Testing",
                "Testing" => [
                    "Traffic Generation"
                ],
                "User Interfaces",
                "Version Control",
                "Widget Set"
            ],
            "System",
            "System" => [
                "Archiving",
                "Archiving" => [
                    "Backup",
                    "Compression",
                    "Mirroring",
                    "Packaging"
                ],
                "Benchmark",
                "Boot",
                "Boot" => [
                    "Init"
                ],
                "Clustering",
                "Console Font",
                "Distributed Computing",
                "Emulator",
                "Filesystem",
                "Hardware",
                "Hardware" => [
                    "Hardware Driver",
                    "Mainframes",
                    "Symmetric Multi-processing"
                ],
                "Installation",
                "Logging",
                "Monitoring",
                "Networking",
                "Networking" => [
                    "Firewalls",
                    "Monitoring",
                    "Monitoring" => [
                        "Hardware Watchdog"
                    ],
                    "Time Synchronization"
                ],
                "Operating System",
                "Kernel",
                "Power (UPS)",
                "Recovery Tool",
                "Shells",
                "Software Distribution",
                "Systems Administration",
                "Systems Administration" => [
                    "Authentication/Directory",
                    "Authentication/Directory" => [
                        "LDAP",
                        "NIS"
                    ]
                ]
            ],
            "Shell",
            "Terminal",
            "Terminal" => [
                "Serial",
                "Telnet",
                "Terminal Emulator"
            ],
            "Text Editor",
            "Text Editor" => [
                "Documentation",
                "Emacs",
                "IDE",
                "Text Processing",
                "Word Processor"
            ],
            "Text Processing",
            "Text Processing" => [
                "Filter",
                "Font",
                "General",
                "Indexing",
                "Linguistic",
                "Markup",
                "Markup" => [
                    "DocBook",
                    "HTML",
                    "LaTeX",
                    "Markdown",
                    "ReStructuredText",
                    "SGML",
                    "VRML",
                    "Wiki",
                    "XML"
                ]
            ],
            "Utilities"
        ],
        "Programming Language" => [
            "Ada",
            "APL",
            "ASP",
            "Assembly",
            "Awk",
            "Bash",
            "Basic",
            "C",
            "C#",
            "C++",
            "Clojure",
            "Cold Fusion",
            "Cython",
            "D",
            "Delphi",
            "Dylan",
            "Eiffel",
            "Emacs-Lisp",
            "Erlang",
            "Euler",
            "Forth",
            "Fortran",
            "Go",
            "Groovy",
            "Haskell",
            "Haxe",
            "Java",
            "JavaScript",
            "Lua",
            "Lisp",
            "Logo",
            "Matlab",
            "ML",
            "Modula",
            "Oberon",
            "Objective C",
            "Object Pascal",
            "OCaml",
            "Parrot",
            "Pascal",
            "Perl",
            "PHP",
            "PHP" => [
                "HHVM",
                "Quercus"
            ],
            "Pike",
            "PL/SQL",
            "PROGRESS",
            "Prolog",
            "Python",
            "Python" => [
                "CPython",
                "IronPython",
                "Jython",
                "PyPy",
                "Stackless"
            ],
            "REBOL",
            "R",
            "Regex",
            "Rexx",
            "Ruby",
            "Scala",
            "Scheme",
            "Simula",
            "Smalltalk",
            "SQL",
            "Tcl",
            "Unix Shell",
            "Vala",
            "YACC",
            "Zope"
        ],
        "Environment" => [
            "Console",
            "Console" => [
                "Curses",
                "Framebuffer",
                "Newt",
                "svgalib"
            ],
            "Mobile",
            "MacOS X",
            "MacOS X" => [
                "Aqua",
                "Carbon",
                "Cocoa"
            ],
            "Daemon",
            "OpenStack",
            "Plugin",
            "Web Environment",
            "Web Environment" => [
                "Buffet",
                "Mozilla",
                "ToscaWidgets"
            ],
            "Win32",
            "X11",
            "X11" => [
                "Gnome",
                "GTK",
                "KDE",
                "Qt",
                "Tk"
            ],
            "Wayland"
        ],
        "Framework" => [
            "C++" => [
                "Boost"
            ],
            "Groovy" => [
                "Grails"
            ],
            "Java" => [
                "Hibernate",
                "Spring",
                "Sinatra",
                "Struts",
                "OpenXava"
            ],
            "JavaScript" => [
                "AngularJS",
                "extJS",
                "jQuery",
                "MooTools",
                "Prototype",
                "qooxdoo"
            ],
            "Perl" => [
                "Mason",
                "Catalyst"
            ],
            "Python" => [
                "BFG",
                "Bob",
                "Bottle",
                "Buildout",
                "Chandler",
                "CherryPy",
                "CubicWeb",
                "Django",
                "Flask",
                "IDLE",
                "IPython",
                "Opps",
                "Paste",
                "Plone",
                "py2web",
                "Pylons",
                "Pyramid",
                "Review Board",
                "Setuptools Plugin",
                "Trac",
                "Tryton",
                "TurboGears",
                "Twisted",
                "ZODB",
                "Zope2",
                "Zope3"
            ],
            "PHP" => [
                "CakePHP",
                "Laravel",
                "Symfony",
                "Yii",
                "Zend Framework"
            ],
            "Ruby" => [
                "Rails"
            ]
        ],
        "Operating System" => [
            "BeOS",
            "Darwin",
            "MacOS",
            "MS-DOS",
            "Windows",
            "OS2",
            "Cross-plattform",
            "PalmOS",
            "PDA Systems",
            "POSIX",
            "AIX",
            "BSD",
            "BSD" => [
                "FreeBSD",
                "NetBSD",
                "OpenBSD"
            ],
            "Hurd",
            "HP-UX",
            "IRIX",
            "Linux",
            "SCO",
            "Solaris",
            "QNX",
            "Unix"
        ],
        "Audience" => [
            "Customer Service",
            "Developers",
            "Education",
            "End Users",
            "Financial and Insurance Industry",
            "Healthcare Industry",
            "Information Technology",
            "Legal Industry",
            "Manufacturing",
            "Religion",
            "Science/Research",
            "System Administrators",
            "Telecommunications Industry"
        ],
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
        
        // extract moniker and optional version or number
        if (preg_match_all("/\b[\d.]+\b|\b(?!GNU)\w+\b/", "$id xyDummy", $p)) {

            list($name, $ver) = @array($p[0][0], $p[0][1]);

            // close or approximated license description match
            if ($match = preg_grep("/$name.+?$ver/i", tags::$licenses)
            or  $match = preg_grep("/$name/i", tags::$licenses))
            {
                return key($match);
            }
            
            // or just abbreviation keys
            if ($match = preg_grep("/{$name}[Lv-]*{$ver[0]}/i", array_keys(tags::$licenses))
            or  $match = preg_grep("/$name/i", array_keys(tags::$licenses)))
            {
                return reset($match);
            }
        }
    }



    /**
     * Guess leaves from standard Trove categories
     * (Does not utilize self::$tree yet!)
     *
     */
    function trove_to_tags($array, $tags=array()) {
        preg_match_all("~^Topic :: .+ :: (\w[\w\s/-]+)$~m", implode("\n", (array)$array), $uu);
        foreach ($uu[1] as $trove) {
            $tags[] =
                strtolower(
                    strtr($trove, " /.", "--_")
                );
        }
        return implode(", ", $tags);
    }



    /**
     * HTML output list of Trove tags.
     *
     * Is used in page_submit within the <div class=select id=trove_tags>
     *
     * Here everything just wrapped in <span>s, because <select> optgroups
     * can't be nested, and <ul> breaks out of inline flow text DOM
     * structure.
     *
     */
    function trove_select($trove, $level=0, $html="") {

        // loop through one level
        foreach ($trove as $key=>$value) {
        
            // normalize title into tag-key
            $tag = is_numeric($key) ? $value : $key;
            $tag = strtr(strtolower($tag), " /.:-", "-----");
            $style = "style='margin-left: {$level}px;'";
        
            // descend into groups
            if (is_array($value)) {
                $html .= "<span class=optgroup data-tag=$tag><b class=option data-tag=$tag>$key</b>";
                $html .= self::trove_select($value, $level + 10);
                $html .= "</span>";
            }
            // skip if entry repeated as subgroup
            elseif (isset($trove[$value])) {
                #..
            }
            // single tag entry
            else {
                $html .= "<span data-tag=$tag class=option>$value</span>";
            }
        }
        
        return $html;
    }



    /**
     * Returns just leaves from trove $tree.
     *
     */
    function leaves() {
        return iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator(self::$tree)));
    }


    /**
     * Extract typical release tags.
     *
     */
    function scope_tags($s) {
        preg_match_all("/major|minor|bugfix|feature|security|documentation|hidden|cleanup/i", strtolower($s), $uu);
        return join(" ", array_unique($uu[0]));
    }

    /**
     * Extract typical release tags.
     *
     */
    function state_tag($s) {
        preg_match_all("/initial|alpha|beta|development|prerelease|stable|mature|historic/i", strtolower($s), $uu);
        return isset($uu[0][0]) ? $uu[0][0] : "";
    }

}




?>