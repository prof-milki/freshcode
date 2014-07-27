#!/usr/bin/php -qC
<?php
/**
 * api: cli
 * title: convert trove.csv
 * description: generates PHP array of trove categories and tag leaves
 *
 *  `soffice --headleass --convert-to csv trove.ods`
 *  does not work, so we need it preconverted in trove.csv
 *
 *  only first 4 columns contain the tree and leaves
 *
 */


#-- read in
$csv = array_map("str_getcsv", file(__DIR__."/trove.csv"));
unset($csv[0]);  // remove head


// target
$TREE = array();
$TMAP = array();

// last tree
$last_path = array();

# loop over lines
foreach ($csv as $row) {

    // Cut out only 5 columns (rest of spreadsheet is documentation)
    $py_trove = $row[5];
    $row = array_map("trim", array_slice($row, 0, 5));


    // merge current leave parts with last path
    if ($row = array_filter($row)) {
        $row = array_slice($last_path, 0, key($row)) + $row;
        $last_path = $row;
    }
    // skip empty
    else {
        continue;
    }
    $TMAP[] = array($py_trove, implode(" :: ", $row));

    
    // append to what we have
    $path = array_filter($row);
    $leaf = array_pop($path);
    $var = enclose("['", $path, "']");
    eval("
        \$TREE{$var}[] = '$leaf';
    ");
#            if ($up_var) unset(\$TREE{$up_var}[array_search('$up_leaf', \$TREE{$up_var})]);

}

// reorder, strip Status and License, then output
print var_export54(array(
    "Topic" => $TREE["Topic"],
    "Programming Language" => $TREE["Programming Language"],
    "Environment" => $TREE["Environment"],
    "Framework" => $TREE["Framework"],
    "Operating System" => $TREE["Operating System"],
    "Audience" => $TREE["Audience"],
#    "Natural" => $TREE["Natural"],
));

// save mapping onto py-trove
file_put_contents(__DIR__."/trove.pypi-map.csv", json_encode($TMAP, JSON_PRETTY_PRINT));


#-- le functions

function enclose($pre, $values, $post) {
    return $values ? $pre . implode("$post$pre", $values) . $post : "";
}


function var_export54($var, $indent="") {
    switch (gettype($var)) {
        case "string":
            return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
        case "array":
          //  $indexed = array_keys($var) === range(0, count($var) - 1);
            $r = [];
            foreach ($var as $key => $value) {
                $indexed = is_numeric($key);
                $r[] = "$indent    "
                     . ($indexed ? "" : var_export54($key) . " => ")
                     . var_export54($value, "$indent    ");
            }
            return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
        case "boolean":
            return $var ? "TRUE" : "FALSE";
        default:
            return var_export($var, TRUE);
    }
}
