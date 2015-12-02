<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//$pdo_conn = null;


if (!function_exists('mysql_connect')) {
    $pdo_conn = null;       /* @var $pdo_conn PDO */
    $pdo_error = null;      /* @var $pro_error String */
    $pdo_last_stmt = null;   /* @var $pdo_stmt PDOStatement */

    define('MYSQL_ASSOC', 1);
    define('MYSQL_NUM', 2);
    define('MYSQL_BOTH', 3);

    function mysql_continued() {
    }

    function mysql_connect($host = 'localhost', $user = '', $pass = '') {
        global $pdo_conn, $pdo_error;
        //$pdo_conn = new PDO('sqlite:host=' . $host, $user, $pass);
        $pdo_conn = new PDO('sqlite:foo.db');
//                $pdo_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        mysql_store_error($pdo_conn);
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
        return $pdo_error;
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

    /**
     * PRIVATE
     * @global PDO $pdo_conn
     * @global type $pdo_error
     * @param type $rst
     */
    function mysql_store_error($rst) {
        global $pdo_conn, $pdo_error;
        $pdo_error = $rst ? null : $pdo_conn->errorInfo()[2];
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

 */