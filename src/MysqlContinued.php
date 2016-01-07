<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if (!function_exists('mysql_connect')) {
    define('MYSQL_ASSOC', 1);
    define('MYSQL_NUM', 2);
    define('MYSQL_BOTH', 3);

    function mysql_continued() {
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0, $persistent = false, $dsn = 'mysql:host=%s') {
        global $mysc_obj;
        
        //this section below does not work:
        if ($server === null)   {$server = ini_get("mysql.default_host");}
        if ($username === null) {$username = ini_get("mysql.default_user");}
        if ($password === null) {$password = ini_get("mysql.default_password");}
       
        //wipe any history
        $errorInfo = null;
        $mysc_obj->conn = null;
        $mysc_obj->last_stmt = null;
        
        $mysc_obj->conn = new PDO(sprintf($dsn, $server), $username, $password, array(PDO::ATTR_PERSISTENT => $persistent));
        #catch (PDOException $e) {
        #    //grep and store the error info
        #    $matches = array();
        #    if (preg_match ('/\\[(.*)\\]\\s*\\[(.*)\\]\\s*(.*)$/', $e->getMessage(), $matches)) {
        #        array_shift($matches);  //remove the first element containing the full match
        #        $errorInfo = $matches;
        #    }
        #}
        
        mysql_store_error($errorInfo);
        return $mysc_obj->conn ? $mysc_obj->conn : false;
    }

    //clients_flags is ignored!
    function mysql_pconnect($server = null, $username = null, $password = null, $client_flags = 0) {
        return mysql_connect($server, $username, $password, false, $client_flags, true);
    }

    function mysql_select_db($dbname) {
        $rst = (boolean) mysql_query(sprintf("USE `%s`", mysql_real_escape_string($dbname)));
        return $rst;
    }

    function mysql_set_charset($charset) {
        $rst = (boolean) mysql_query(sprintf('SET NAMES `%s`', mysql_real_escape_string($charset)));
        return $rst;
    }

    function mysql_real_escape_string($unescaped_string) {
        global $mysc_obj;
        return preg_replace("/'(.*)'/", '$1', $mysc_obj->conn->quote($unescaped_string));
    }
    function mysql_escape_string($unescaped_string) {
        return mysql_real_escape_string($unescaped_string);
    }

    function mysql_error() {
        global $mysc_obj;
        return $mysc_obj->error[2] === null ? '' : $mysc_obj->error[2];
    }
    function mysql_errno() {
        global $mysc_obj;
        return (integer) $mysc_obj->error[1];
    }

    function mysql_insert_id() {
        global $mysc_obj;
        $rst = $mysc_obj->conn ? $mysc_obj->conn->lastInsertId() : false;
        return $rst;
    }

    function mysql_num_rows($stmt) {
        global $mysc_obj;
        $cnt = $mysc_obj->conn ? $mysc_obj->conn->query("SELECT FOUND_ROWS()") : false;
        return ($cnt ? (integer) $cnt->fetchColumn() : false);
    }

    function mysql_affected_rows() {
        global $mysc_obj;
        return $mysc_obj->last_stmt ? $mysc_obj->last_stmt->rowCount() : -1;
    }

    function mysql_query($sql, $buffered = true) {
        global $mysc_obj;
        
        $success = false;
        
        $opts = 
            ($mysc_obj->conn->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') ? 
            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => $buffered) : 
            array();
        $rst = $mysc_obj->conn->prepare($sql, $opts);
        if ($rst) {
            $success = $rst->execute();
        }
        mysql_store_error($rst);
        $mysc_obj->last_stmt = $success ? $rst : null;      //store the last stmt for use in affected rows
        return $success ? $rst : false;
    }
    function mysql_unbuffered_query($sql) {
        return mysql_query($sql, false);
    }
    function mysql_db_query($database, $sql) {
        $suc = mysql_select_db($database);
        if (! $suc) {return false;}
        return mysql_query($sql, false);
    }
    
    function mysql_fetch_assoc($rs) {
        return $rs->fetch(PDO::FETCH_ASSOC);
    }

    function mysql_fetch_row($rs) {
        return $rs->fetch(PDO::FETCH_NUM);
    }

    function mysql_fetch_array($rs, $result_type = MYSQL_BOTH) {
        switch ($result_type) {
            case MYSQL_ASSOC:
                return mysql_fetch_assoc($rs);
            case MYSQL_NUM:
                return mysql_fetch_row($rs);
            default :
                return $rs->fetch(PDO::FETCH_BOTH);
        }
    }
    
    function mysql_fetch_object($rs, $class_name = "stdClass", $params = array()) {
        return $rs->fetchObject($class_name, $params);
    }
    /* keeping track of the cursor is difficult using pdo: thus not supported 
    function mysql_result($rs, $row , $field = 0) {
        $all = $rs->fetchAll(PDO::FETCH_BOTH);
        try{
            return $all[$row][$field];
        } catch (Exception $ex) {
            return false;
        }
    }*/
    
    function mysql_free_result(&$rs) {
        $rs = null;
        return true;
    }
    
    function mysql_num_fields ($result ){
        return $result->columnCount();
    }
    function mysql_close() {
        global $mysc_obj;
        $mysc_obj->conn = null;
        $mysc_obj->last_stmt = null;
        mysql_store_error(true);
        return true;
    }
    
    /**
     * Only works AFTER connecting to a database
     * @global PDO $mysc_obj->conn
     * @return type
     */
    function mysql_get_client_info (){
        global $mysc_obj;
        return $mysc_obj->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }
    function mysql_get_host_info (){
        global $mysc_obj;
        return $mysc_obj->conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }
    function mysql_get_server_info (){
        global $mysc_obj;
        return $mysc_obj->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
    /**
     * PRIVATE
     * @global PDO $mysc_obj->conn
     * @global array $mysc_obj->error
     * @param mixed $rst: 
     *          if true => reset error; 
     *          if is_array => store array
     *          if PDOStatement => store $rst->errorInfo(() ; 
     *          if false => store $pdo->errorInfo();
     */
    function mysql_store_error($rst = null) {
        global $mysc_obj;
        $mysc_obj->error = 
            ($rst === true) ?   array('0000', 0, '') : 
            is_array($rst) ?    $rst : 
            ($rst ?             $rst->errorInfo() : 
            ($mysc_obj->conn ?        $mysc_obj->conn->errorInfo() : 
                                array('9999', 9999, 'Unknown error')));
    }

    //init globals:
    $mysc_obj = new stdClass();
    $mysc_obj->conn = null;           /* @var $mysc_obj->conn PDO */
    $mysc_obj->last_stmt = null;      /* @var $pdo_stmt PDOStatement */
    $mysc_obj->error = array();       /* @var $mysc_obj->error array() */
    mysql_store_error(true);
}
