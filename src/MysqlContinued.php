<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if (!function_exists('mysql_connect')) {
    $pdo_conn = null;       /* @var $pdo_conn PDO */
    $pdo_error = array('0000', '', '');      /* @var $pdo_error array() */
    $pdo_last_stmt = null;   /* @var $pdo_stmt PDOStatement */

    define('MYSQL_ASSOC', 1);
    define('MYSQL_NUM', 2);
    define('MYSQL_BOTH', 3);

    function mysql_continued() {
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0, $persistent = false, $dsn = 'mysql:host=%s') {
        global $pdo_conn, $pdo_last_stmt, $pdo_error;
        
        //this section below does not work:
        if ($server === null)   {$server = ini_get("mysql.default_host");}
        if ($username === null) {$username = ini_get("mysql.default_user");}
        if ($password === null) {$password = ini_get("mysql.default_password");}
       
        try{
            $pdo_conn = new PDO(sprintf($dsn, $server), $username, $password, array(PDO::ATTR_PERSISTENT => $persistent));
            mysql_store_error($pdo_conn);
        }
        catch (PDOException $e) {
            $pdo_conn = false;
            //grep and store the error info
            if (preg_match ('/\\[(.*)\\]\\s*\\[(.*)\\]\\s*(.*)$/', $e->getMessage(), $matches)) {
                $pdo_error = array($matches[1], $matches[2], $matches[3]);
            } else {
                $pdo_error = array('9999', '9999', 'Unknown error');
            }
        }
        $pdo_last_stmt = null;
        return $pdo_conn;
    }

    //clients_flags is ignored!
    function mysql_pconnect($server = null, $username = null, $password = null, $client_flags = 0) {
        return mysql_connect($server, $username, $password, false, $client_flags, true);
    }

    function mysql_select_db($dbname) {
        //global $pdo_conn, $pdo_error;
        $rst = (boolean) mysql_query(sprintf("USE `%s`", mysql_real_escape_string($dbname)));
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_set_charset($charset) {
        //global $pdo_conn, $pdo_error;
        $rst = (boolean) mysql_query(sprintf('SET NAMES `%s`', mysql_real_escape_string($charset)));
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_real_escape_string($unescaped_string) {
        global $pdo_conn;
        return preg_replace("/'(.*)'/", '$1', $pdo_conn->quote($unescaped_string));
    }
    function mysql_escape_string($unescaped_string) {
        return mysql_real_escape_string($unescaped_string);
    }

    function mysql_error() {
        global $pdo_error;
        return $pdo_error[2];
    }
    function mysql_errno() {
        global $pdo_error;
        return (integer) $pdo_error[1];
    }

    function mysql_insert_id() {
        global $pdo_conn;
        $rst = $pdo_conn ? $pdo_conn->lastInsertId() : false;
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_num_rows($stmt) {
        global $pdo_conn;
        $cnt = $pdo_conn ? $pdo_conn->query("SELECT FOUND_ROWS()") : false;
        return ($cnt ? (integer) $cnt->fetchColumn() : false);
    }

    function mysql_affected_rows() {
        global $pdo_last_stmt;
        return $pdo_last_stmt == null ? -1 : $pdo_last_stmt->rowCount();
    }

    function mysql_query($sql, $buffered = true) {
        global $pdo_conn, $pdo_last_stmt;
        $opts = ($pdo_conn->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') ? array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => $buffered) : array();
        $rst = $pdo_conn->prepare($sql, $opts);
        if ($rst) {
            $rst->execute();
        }
        mysql_store_error($rst);
        $pdo_last_stmt = $rst;      //store the last stmt for use in affected rows
        return $rst;
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
    
/*    function mysql_list_dbs () {
        return mysql_query("SHOW DATABASES");
    }
*/    
    function mysql_num_fields ($result ){
        return $result->columnCount();
    }
    function mysql_close() {
        global $pdo_conn, $pdo_last_stmt;
        $pdo_conn = null;
        $pdo_last_stmt = null;
        mysql_store_error(true);
        return true;
    }
    
    /**
     * Only works AFTER connecting to a database
     * @global PDO $pdo_conn
     * @return type
     */
    function mysql_get_client_info (){
        global $pdo_conn;
        return $pdo_conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }
    function mysql_get_host_info (){
        global $pdo_conn;
        return $pdo_conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }
    function mysql_get_server_info (){
        global $pdo_conn;
        return $pdo_conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
    function mysql_stat(){
        global $pdo_conn;
        return $pdo_conn->getAttribute(PDO::ATTR_SERVER_INFO);
    }
    
    /**
     * PRIVATE
     * @global PDO $pdo_conn
     * @global type $pdo_error
     * @param type $rst
     */
    function mysql_store_error($rst) {
        global $pdo_conn, $pdo_error;
        $pdo_error = $rst ? array('0000', '', '') : ($pdo_conn ? $pdo_conn->errorInfo() : array('9999', '9999', 'Unknown error'));
    }

}

/*
 * 
 * ?? mysql_ping ([ resource $link_identifier = NULL ] )
 * ?? mysql_result ( resource $result , int $row [, mixed $field = 0 ] )
 * ?? mysql_tablename ( resource $result , int $i )
 * 
 * WILL NOT DO:
 * ?? mysql_list_fields ( string $database_name , string $table_name [, resource $link_identifier = NULL ] )
 * ?? mysql_info ([ resource $link_identifier = NULL ] )
     * ?? mysql_get_proto_info ([ resource $link_identifier = NULL ] )
 * mysql_client_encoding
 * bool mysql_create_db ( string $database_name [, resource $link_identifier = NULL ] )
 * mysql_data_seek ( resource $result , int $row_number )
 * mysql_db_name ( resource $result , int $row [, mixed $field = NULL ] )
 * mysql_db_query ( string $database , string $query [, resource $link_identifier = NULL ] )
 * mysql_drop_db ( string $database_name [, resource $link_identifier = NULL ] )
 * mysql_fetch_field ( resource $result [, int $field_offset = 0 ] )
 * mysql_fetch_lengths ( resource $result )
 * mysql_field_flags ( resource $result , int $field_offset )
 * mysql_field_*
 * mysql_list_processes ([ resource $link_identifier = NULL ] )
 * mysql_thread_id() 
 * mysql_list_*
 * 
 * Limitations:
 * - does not handle multiple connection (e.g. to more then 1 database)
 * - does not implement these function [[FUNCTION LISTING]]
 * - does not accept the $link_identifier resource on any function
 * - requires pdo-mysql
 * - does not use the default ini-values in mysql_connect()
 */