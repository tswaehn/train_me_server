<?php
function myErrorHandler($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile)
{
    if (!(error_reporting() & $fehlercode)) {
        // Dieser Fehlercode ist nicht in error_reporting enthalten
        //return false;
    }

    // $fehlertext muss möglicherweise maskiert werden:
    $fehlertext = htmlspecialchars($fehlertext);

    switch ($fehlercode) {
        case E_USER_ERROR:
            echo "<b>Mein FEHLER</b> [$fehlercode] $fehlertext<br />\n";
            echo "  Fataler Fehler in Zeile $fehlerzeile in der Datei $fehlerdatei";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Abbruch...<br />\n";
            break;

        case E_USER_WARNING:
            echo "<b>Meine WARNUNG</b> [$fehlercode] $fehlertext<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>Mein HINWEIS</b> [$fehlercode] $fehlertext<br />\n";
            break;

        default:
            echo "Unbekannter Fehlertyp: [$fehlercode] $fehlertext<br />\n";
            break;
    }

    die();
    /* Damit die PHP-interne Fehlerbehandlung nicht ausgeführt wird */
    return true;
}

function exception_handler($exception) {
    print("Nicht aufgefangene Exception: " . $exception->getMessage());
    echo "Nicht aufgefangene Exception: " , $exception->getMessage(), "\n";
    die();
}

set_exception_handler('exception_handler');

set_error_handler ( "myErrorHandler", E_ALL | E_STRICT |  E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR );
