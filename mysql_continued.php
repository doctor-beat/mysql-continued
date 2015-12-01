<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//$pdo_conn = null;


if (!function_exists('mysql_connect')) {
        $pdo_conn = null;   /*@var $pdo_conn PDO */
        //$pdo_last = null;   /*@var $pdo_stmt PDOStatement */
        //$pdo_error = null;  /* the last error message */
        function mysql_connect($host = 'localhost', $user = '', $pass = '' ) {
                global $pdo_conn;
                $pdo_conn = new PDO('sqlite:;host=' . $host, $user, $pass);
//                $pdo_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo_conn;
        }
        function mysql_select_db($dbname) {
                global $pdo_conn;
                return (boolean) mysql_query(sprintf("USE %s", mysql_real_escape_string($dbname)));
        }
        function mysql_set_charset($charset) {
                global $pdo_conn;
                return (boolean) mysql_query(sprintf('SET NAMES %s' , mysql_real_escape_string($charset)));
        }
        function mysql_real_escape_string($unescaped_string) {
                global $pdo_conn;
                return preg_replace("/'(.*)'/", '$1', $pdo_conn->quote($unescaped_string));
        }
        function mysql_error() {
                global $pdo_conn;
                return $pdo_conn->errorInfo()[2];
        }
        function mysql_insert_id() {
                global $pdo_conn;
                return $pdo_conn->lastInsertId();
        }
        
        
        function mysql_query($sql) {
                global $pdo_conn;
                return $pdo_conn->query($sql);
        }
        function mysql_fetch_assoc($rs) {
                return $rs->fetch(PDO::FETCH_ASSOC);
        }
        // ... etc.
}

/*
//connect
$dbh = mysql_connect();
assert($pdo_conn != null);
assert($pdo_conn === $dbh);

//mysql_error (at handle level)
assert(mysql_error() == null);

//mysql_select_db
$bool = mysql_select_db('abc');
assert($bool === true, "Bool: " . $bool);

//mysql_set_charset
$bool = mysql_set_charset('utf8');
assert($bool === true, "Bool: " . $bool);

//mysql_error (at statement level)
assert(mysql_error() === 'near "SET": syntax error', mysql_error());

//real-escape
$escaped = mysql_real_escape_string("abc 'stu");
assert($escaped === "abc ''stu", "value: $escaped");

//verify errors for wrong query
$rst = mysql_query("select 1 from dual");
assert(mysql_error() === "no such table: dual");

//mysql_insert_id
assert(mysql_insert_id() === 1, "Insert id: " . mysql_insert_id());




echo "ok: " . $pdo_conn->getAttribute(constant("PDO::ATTR_SERVER_VERSION")) . "\n";
*/
/*
 * To test:
 * 

X mysql_select_db($dbname)
X mysql_connect()
X mysql_set_charset('utf8')
mysql_affected_rows()
mysql_num_rows($cursor)
mysql_fetch_array($cursor)
X mysql_insert_id()
X mysql_real_escape_string($http_vars['autidSel'])
X mysql_error()
mysql_query($query)

 */