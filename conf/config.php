<?php

// import all defaults
require_once ("default_conf.php");

$dir = dirname(__FILE__);

// override if needed
if (file_exists($dir."/test_config.php")) {
    require_once ($dir."/test_config.php");
}
// override if needed
if (file_exists($dir."/debug_config.php")) {
    require_once ($dir."/debug_config.php");
}

// override if needed
if (file_exists($dir."/debug2_config.php")) {
    require_once ($dir."/debug2_config.php");
}


