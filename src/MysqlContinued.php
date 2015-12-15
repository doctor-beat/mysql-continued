<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//$pdo_conn = null;


if (!function_exists('mysql_connect')) {
    $pdo_conn = null;       /* @var $pdo_conn PDO */
    $pdo_error = array('0000', '', '');      /* @var $pdo_error array() */
    $pdo_last_stmt = null;   /* @var $pdo_stmt PDOStatement */

    define('MYSQL_ASSOC', 1);
    define('MYSQL_NUM', 2);
    define('MYSQL_BOTH', 3);

    function mysql_continued() {
    }

    function mysql_connect($host = 'localhost', $user = '', $pass = '') {
        global $pdo_conn, $pdo_last_stmt;
        //$pdo_conn = new PDO('sqlite:host=' . $host, $user, $pass);
        $pdo_conn = new PDO('sqlite:foo.db');
//                $pdo_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        mysql_store_error($pdo_conn);
        $pdo_last_stmt = null;
        return $pdo_conn;
    }

    function mysql_select_db($dbname) {
        global $pdo_conn, $pdo_error;
        $rst = (boolean) mysql_query(sprintf("USE %s", mysql_real_escape_string($dbname)));
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_set_charset($charset) {
        global $pdo_conn, $pdo_error;
        $rst = (boolean) mysql_query(sprintf('SET NAMES %s', mysql_real_escape_string($charset)));
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_real_escape_string($unescaped_string) {
        global $pdo_conn;
        return preg_replace("/'(.*)'/", '$1', $pdo_conn->quote($unescaped_string));
    }

    function mysql_error() {
        global $pdo_error;
        return $pdo_error[2];
    }
    function mysql_errno() {
        global $pdo_error;
        return (integer) $pdo_error[0];
    }

    function mysql_insert_id() {
        global $pdo_conn, $pdo_error;
        $rst = $pdo_conn ? $pdo_conn->lastInsertId() : null;
        mysql_store_error($rst);
        return $rst;
    }

    function mysql_num_rows(PDOStatement $stmt) {
        global $pdo_conn;
        $cnt = $pdo_conn->query("SELECT FOUND_ROWS()");
        return (integer) ($cnt ? $cnt->fetchColumn() : 0);
    }

    function mysql_affected_rows() {
        global $pdo_last_stmt;
        return $pdo_last_stmt == null ? null : $pdo_last_stmt->rowCount();
    }

    function mysql_query($sql) {
        global $pdo_conn, $pdo_error, $pdo_last_stmt;
        $rst = $pdo_conn->query($sql);
        mysql_store_error($rst);
        $pdo_last_stmt = $rst;      //store the last stmt for use in affected rows
        return $rst;
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
    
    function mysql_free_result(&$rs) {
        $rs = null;
        return true;
    }
    
    function mysql_list_dbs () {
        return mysql_query("SHOW DATABASES");
    }
    
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
     * PRIVATE
     * @global PDO $pdo_conn
     * @global type $pdo_error
     * @param type $rst
     */
    function mysql_store_error($rst) {
        global $pdo_conn, $pdo_error;
        $pdo_error = $rst ? array('0000', '', '') : $pdo_conn->errorInfo();
    }

}

/*
 * To test:
 * 

X mysql_select_db($dbname)
X mysql_connect()
X mysql_set_charset('utf8')
X mysql_affected_rows()
X mysql_num_rows($cursor)
X mysql_fetch_array($cursor)
X mysql_insert_id()
X mysql_real_escape_string($http_vars['autidSel'])
X mysql_error()
X mysql_query($query)
X mysql_fetch_row() 

TODO:
 * X mysql_close
 * X mysql_errno ([ resource $link_identifier = NULL ] 
 * X mysql_fetch_object ( resource $result [, string $class_name [, array $params ]] ))
 * X mysql_free_result ( resource $result )
 * X mysql_list_dbs ([ resource $link_identifier = NULL ] )
 * mysql_num_fields ( resource $result )
 * mysql_pconnect ([ string $server = ini_get("mysql.default_host") [, string $username = ini_get("mysql.default_user") [, string $password = ini_get("mysql.default_password") [, int $client_flags = 0 ]]]] )
 * 
 * 
 * ?? mysql_get_client_info ( void )
 * ?? mysql_get_host_info ([ resource $link_identifier = NULL ] )
 * ?? mysql_get_proto_info ([ resource $link_identifier = NULL ] )
 * ?? mysql_get_server_info ([ resource $link_identifier = NULL ] )
 * ?? mysql_info ([ resource $link_identifier = NULL ] )
 * ?? mysql_list_fields ( string $database_name , string $table_name [, resource $link_identifier = NULL ] )
 * ?? mysql_ping ([ resource $link_identifier = NULL ] )
 * ?? mysql_result ( resource $result , int $row [, mixed $field = 0 ] )
 * ?? mysql_stat ([ resource $link_identifier = NULL ] )
 * ?? mysql_tablename ( resource $result , int $i )
 * ?? mysql_unbuffered_query ( string $query [, resource $link_identifier = NULL ] )
 * 
 * WILL NOT DO:
 * mysql_client_encoding
 * bool mysql_create_db ( string $database_name [, resource $link_identifier = NULL ] )
 * mysql_data_seek ( resource $result , int $row_number )
 * mysql_db_name ( resource $result , int $row [, mixed $field = NULL ] )
 * mysql_db_query ( string $database , string $query [, resource $link_identifier = NULL ] )
 * mysql_drop_db ( string $database_name [, resource $link_identifier = NULL ] )
 * mysql_escape_string ( string $unescaped_string )
 * mysql_fetch_field ( resource $result [, int $field_offset = 0 ] )
 * mysql_fetch_lengths ( resource $result )
 * mysql_field_flags ( resource $result , int $field_offset )
 * mysql_field_*
 * mysql_list_processes ([ resource $link_identifier = NULL ] )
 */