<?php

function table($table){
    global $conf;
    return $conf->db_table_prefix.Configuration::API_VERSION."_".$table;
}
function t($table){
    return "`".table($table)."`";
}

function v($value){
    return "'".$value."'";
}


class DB_Connection {
    var $pdo = NULL;

    var $tableDefs = array();

    var $host, $db, $user, $pass;

    function __construct(Configuration $conf){
        $this->host= $conf->db_host;
        $this->db= $conf->db_database;
        $this->user= $conf->db_user;
        $this->pass= $conf->db_pass;
    }

    function connect(){
        if ($this->createPDO() == false){
            return False;
        }
        $this->createTableDefs();

        do_log(0, "connected");

        // make sure all tables are setup properly
        if ($this->tableCheck() == False){
            return False;
        }

        return true;
    }

    function createPDO(){
        $dsn = 'mysql:dbname='.$this->db.';host='.$this->host;

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            // needed to allow LIMIT :a OFFSET :b in prepared statements
            $this->pdo->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
        } catch (PDOException $e) {
            do_log(0,'Connection failed: ' . $e->getMessage() );
            return false;
        }
        return true;
    }

    function createTableDefs(){
        $this->tableDefs= array(
            // org data
            table("users")=>"CREATE TABLE IF NOT EXISTS ".t("users")." (`uid` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (uid), `name` varchar(64) NOT NULL, `token` varchar(64) NOT NULL, `password` varchar(64) NOT NULL, `first_name` varchar(64) NOT NULL, `last_name` varchar(64) NOT NULL, `email` varchar(128) NOT NULL, `enabled` int(11) NOT NULL, INDEX(`name`, `token`) )",
            table("sessions")=>"CREATE TABLE IF NOT EXISTS ".t("sessions")." (`uid` int(11) NOT NULL, PRIMARY KEY (uid), `sid` varchar(64) NOT NULL, `created_at` timestamp(6) NOT NULL, INDEX(`uid`, `sid`) )",
        );
    }

    function q($sql, $values = array()){
        $ret = false;
        $stmt = NULL;
        try {
            $stmt = $this->pdo->prepare($sql);
            $ret = $stmt->execute($values);
        } catch (PDOException $e) {
            do_log(0,'query failed: ' . $e->getMessage() );
        }
        if ($ret == false) {
            return NULL;
        } else {
            return $stmt;
        }
    }

    function tableCheck(){
        // get the available tables
        $stmt= $this->q( "show tables");
        $actualTables= array();
        if ($stmt->rowCount() > 0){
            while ($row = $stmt->fetch()){
                $actualTables[$row[0]] = True;
            }
        }

        // loop expected tables
        foreach ($this->tableDefs as $table_name=>$sql){
            if (isset($actualTables[$table_name]) == false){
                // create table
                $sql= $this->tableDefs[$table_name];
                if ($this->q($sql) == false){
                    return false;
                }
            }
        }

        return True;
    }


}