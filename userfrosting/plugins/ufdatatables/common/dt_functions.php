<?php
function echobr($par_str) {
    echo("<br>$par_str<br>");
    error_log("$par_str \n");
}

function echoarr($par_arr, $par_comment = 'none') {
    if ($par_comment != 'none')
        echobr($par_comment);
    echo "<pre>";
    print_r($par_arr);
    echo "</pre>";
    error_log("<pre>$par_comment \n" .
            print_r($par_arr, true) . " \n\n </pre>");
}

function logarr($par_arr, $par_comment = 'none') {
    error_log("<pre>$par_comment \n" .
            print_r($par_arr, true) . " \n\n </pre>");
}

function valueIfSet($par_arr, $par_value, $par_default = '') {
    return isset($par_arr[$par_value]) ? $par_arr[$par_value] : $par_default;
}
