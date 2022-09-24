<?php

function do_log($level, $msg){
    global $conf;
    if ($conf->console_log == false){
        return;
    }

    echo "<pre>".$msg."</pre>";
}