<?php
#
# title: cronjob collector
# description: parses out cron: specifiers from plugins and installs crontab
# version: 0.1
# cron: 0 0 * * *
# type: cron
# 
# Reregisters cronjobs from current dir (cron.daily/) into crontab
# according to x-cron: specifiers.


// keep previous crontab entries not pointing to current __DIR__
$dir = preg_quote(__DIR__, "~");
$CRONTAB = preg_replace(
    "~((?<=\n)\R+)?(^#.+\R+)*^.*$dir.*\R~m",
    "",
    `crontab -l`
);


// traverse all files in cron.d/
foreach (glob(__DIR__."/*.*") as $fn) {

    // coarse plugin meta data extraction
    preg_match_all("~ ^ [#/*\h]+ (?:x-)? (cron|title|description|version|type): \h* (\V+)  ~mix", file_get_contents($fn), $m);
    $m = array_change_key_case(array_combine($m[1], $m[2] ));

    // assert existing `cron:` specifier
    if (!empty($m["cron"]) and preg_match("~^([*/\d-]+(\h+|$)){5}$|^@(daily|hourly|midnight)$~", $m["cron"] = stripcslashes($m["cron"]))) {
       $CRONTAB .= "\n"
                .  "#-- $m[title]\n#   ($m[description])\n"
                .  "$m[cron] "
                .  "nice "
                .  pathinfo($fn, PATHINFO_EXTENSION)
                .  " $fn\n";
    }
    
}

// install new crontab
fwrite(popen("crontab", "w"), $CRONTAB);
