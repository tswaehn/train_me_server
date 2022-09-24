<?php

class Configuration {

    const API_VERSION= "1.0";

    var $db_host = "localhost";
    var $db_database = "database";
    var $db_user = "user";
    var $db_pass = "pass";

    var $db_table_prefix = "rec_";

    var $console_log = false;

    function __construct(){

    }

}

$conf = new Configuration();




