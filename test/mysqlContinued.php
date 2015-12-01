<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require "../mysql_continued.php";


/**
 * Description of mysqlContinued
 *
 * @author ronald
 */
class mysqlContinued extends PHPUnit_Framework_TestCase {
    public function testCanConnect() {
        $dbh = mysql_connect();
        
        $this->assertNotNull($dbh);
        
        global $pdo_conn;
        $this->assertEquals($dbh, $pdo_conn);

    }

}
