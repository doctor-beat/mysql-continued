<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require (__DIR__ . '/../vendor/autoload.php');
require "config.test.php";

/**
 * Description of mysqlContinued
 *
 * @author ronald
 */
class MysqlContinuedTest extends PHPUnit_Framework_TestCase {
    private static $config;
    private $dbh;
    private static $pdo;

    public static function setUpBeforeClass(){
        global $CONFIG;

        if (!function_exists('mysql_continued')) {
            throw new Exception("MysqlContinued not loaded, maybe your normal mysql-lib is still enabled?");
        }

        self::$config = (object) $CONFIG;        

        //self::$pdo = new PDO(sprintf('sqlite:memory:host=%s;dbname=%s;charset=UTF8', self::$config->HOSTNAME, self::$config->DATABASE), 
        self::$pdo = new PDO('sqlite:foo.db',
            self::$config->USERNAME, 
            self::$config->PASSWORD,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,));

        
        //create a test table
        self::$pdo->exec(sprintf("create table %s  (id INTEGER PRIMARY KEY AUTOINCREMENT, col1 string(200));", self::$config->TABLENAME, self::$config->TABLENAME));
        
    }
    
    public static function tearDownAfterClass() {
        self::$pdo->exec(sprintf("drop table if exists %s;", self::$config->TABLENAME));        
    }
    
    public function setUp() {
        parent::setUp();
        $this->dbh = mysql_connect(self::$config->HOSTNAME, self::$config->USERNAME, self::$config->PASSWORD);
        mysql_select_db(self::$config->DATABASE);
        mysql_set_charset('utf8');
    }
    public function tearDown() {
        parent::tearDown();
        $this->clearTable();
        mysql_close();
    }
    public function testCanConnect() {
        global $pdo_conn;

        $this->assertNotNull($this->dbh);
        $this->assertSame($this->dbh, $pdo_conn);
        
        $conn = mysql_connect(self::$config->HOSTNAME, self::$config->USERNAME, self::$config->PASSWORD);
        
        $this->assertNotNull($conn);
        $this->assertSame($pdo_conn, $conn);
        $this->assertNotSame($this->dbh, $conn);
        $this->assertSame(0, mysql_errno());
        $this->assertSame('', mysql_error());
    }
    public function testCanConnectUsingIniValues() {
        global $pdo_conn;
        
        ini_set("mysql.default_host", self::$config->HOSTNAME);
        ini_set("mysql.default_user", self::$config->USERNAME);
        ini_set("mysql.default_password", self::$config->PASSWORD);
        
        $conn = mysql_connect();  /*@var $conn PDO */
        
        $this->assertNotNull($conn);
        $this->assertSame($pdo_conn, $conn);
        $this->assertSame(0, mysql_errno());
        $this->assertSame('', mysql_error());
    }
    public function testCanDetectFailedConnect() {
        $conn = mysql_connect(self::$config->HOSTNAME, self::$config->USERNAME, "INVALID PASSWD");
        
        $this->assertFalse($conn);
        $this->assertSame(123, mysql_errno());
        $this->assertSame("Invalid password", mysql_error());
    }
    public function testCanConnectPersistent() {
        $conn = mysql_pconnect(self::$config->HOSTNAME, self::$config->USERNAME, self::$config->PASSWORD);  /*@var $conn PDO */
        
        $this->assertNotNull($conn);
        $this->assertSame(true, $conn->getAttribute(PDO::ATTR_PERSISTENT));
        $this->assertSame(0, mysql_errno());
        $this->assertSame('', mysql_error());
    }
    public function testCanCloseConnection() {
        global $pdo_conn;

        mysql_close();
        
        $this->assertNull($pdo_conn);
        $this->assertSame('', mysql_error());
        $this->assertSame(0, mysql_errno());
    }
    public function testCanSelectDb() {
        $bool = mysql_select_db(self::$config->DATABASE);
        $this->assertTrue($bool);        
        $this->assertSame('', mysql_error());
    }
    public function testCanSelectDbFailed() {
        $bool = mysql_select_db("DOESNOTEXISTS");
        $this->assertFalse($bool);        
        $this->assertSame('Does not exists', mysql_error());
    }
    public function testCanSetCharset() {
        $bool = mysql_set_charset('utf8');
        $this->assertTrue($bool);        
        $this->assertSame('', mysql_error());
    }
    public function testCanSetCharsetFailed() {
        $bool = mysql_set_charset('nonexisting');
        $this->assertFalse($bool);        
        $this->assertSame('unknown charset', mysql_error());
    }
    public function testRealEscapeData() {
        $escaped = mysql_real_escape_string("abc 'stu");
        $this->assertSame("abc ''stu", $escaped);
    }
    public function testCanDetectError() {
        $rst = mysql_query("select 1 from dual");
        $this->assertFalse($rst);
        $this->assertSame(0, mysql_errno());
        $this->assertSame("no such table: dual", mysql_error());

        $rst = mysql_query("selectx 1 from dual");
        $this->assertFalse($rst);
        $this->assertSame(0, mysql_errno());
        $this->assertSame('near "selectx": syntax error', mysql_error());
    }
    public function testCanInsert() {
        $rst = mysql_query(sprintf("insert into %s values(null, 'insert')", self::$config->TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(1, mysql_affected_rows());
    }
    public function testCanUpdate() {
        $this->insertRowsAndSelect(4);
        $lastId = mysql_insert_id();
        
        $rst2 = mysql_query(sprintf("update %s set col1 = 'my Value' where id  = %s", self::$config->TABLENAME, $lastId));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst2);
        $this->assertSame(1, mysql_affected_rows());

        $rst3 = mysql_query(sprintf("select * from %s where id  = %s", self::$config->TABLENAME, $lastId));
//        $this->assertEquals(1, mysql_num_rows($rst3));
        $row3 = mysql_fetch_assoc($rst3);
        $this->assertSame('my Value', $row3['col1']);
    }
    public function testCanDelete() {
        $this->insertRowsAndSelect(4);
        
        $rst = mysql_query(sprintf("delete from %s", self::$config->TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(4, mysql_affected_rows());
    }
    public function testCanUpdateZeroRows() {
        $rst = mysql_query(sprintf("update %s set col1 = 'my Value' where 1=0", self::$config->TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(0, mysql_affected_rows());
    }
    public function testCanFetchAssocAndDoWhile() {
        $rowCnt = 4;
        $this->insertRowsAndSelect($rowCnt);
        
        $rst = mysql_query(sprintf("select * from %s  order by id", self::$config->TABLENAME));
//        $this->assertEquals($rowCnt, mysql_num_rows($rst));
        $cnt = 0;
        while($row = mysql_fetch_assoc($rst)) {
            $this->assertTrue((integer) $row['id'] > 0);
            if ($cnt == 0) {
                $this->assertSame('Row 1', $row['col1']);
            }
            $this->assertArrayNotHasKey(0, $row);
            $this->assertArrayNotHasKey(1, $row);
            $cnt++;
        }
        $this->assertSame($rowCnt, $cnt);
    }
    public function testCanFetchArrayAsBoth() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $row = mysql_fetch_array($stmt, MYSQL_BOTH);
        $this->assertSame($row[0], $row['id']);
        $this->assertSame('Row 1', $row[1]);
        $this->assertSame('Row 1', $row['col1']);
    }
    public function testCanFetchArrayAsNumArray() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $row = mysql_fetch_array($stmt, MYSQL_NUM);
        $this->assertSame('Row 1', $row[1]);
        $this->assertArrayNotHasKey('col1', $row);
    }
    public function testCanFetchRow() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $row = mysql_fetch_row($stmt);
        $this->assertSame('Row 1', $row[1]);
        $this->assertArrayNotHasKey('col1', $row);
    }
    public function testCanFetchObject() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $row = mysql_fetch_object($stmt);
        $this->assertSame('stdClass', get_class($row));
        $this->assertSame('Row 1', $row->col1);
        $this->assertGreaterThanOrEqual(1, $row->id);
    }
    public function testCanFreeResult() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $result = mysql_free_result($stmt);
        $this->assertSame(true, $result);
        $this->assertNull($stmt);
        
    }
    public function testCanListDbs() {
        $result = mysql_list_dbs();
        $this->assertTrue($result);
        $this->assertSame(0, mysql_errno());

        $found = null;
        while ($found == null && $row = mysql_fetch_object($result)) {
            if ($row->Database == self::$config->DATABASE) {
                $found = $row->Database;
            }
        }
        $this->assertSame(self::$config->DATABASE, $found);
    }
    public function testCanCountColumns() {
        $stmt = $this->insertRowsAndSelect(2);
        
        $result = mysql_num_fields($stmt);
        $this->assertSame(2, $result);
        
    }
    public function testCanReturnLastId() {
        $this->insertRowsAndSelect();

        $lastId = mysql_insert_id();
        $this->assertNotNull($lastId);
        $this->assertTrue($lastId>=1);

        $this->insertRowsAndSelect();
        $lastId2 = mysql_insert_id();
        $this->assertTrue($lastId < $lastId2);
    }
    public function testCanNotReturnLastIdWhenNoInsert() {
        $lastId = mysql_insert_id();
        $this->assertSame(0, (int) $lastId);
    }
    public function testCanNotReturnLastIdWhenNotConnected() {
        mysql_close();
        $lastId = mysql_insert_id();
        $this->assertFalse($lastId);
    }
    
    public function testCanHandleUtf8() {
        $testValue = 'Ïnterñátiön€l';
        
        $rst = mysql_query(sprintf("insert into %s values(null, '%s')", self::$config->TABLENAME, $testValue));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(1, mysql_affected_rows());
        
        $rst = mysql_query(sprintf("select * from %s order by id", self::$config->TABLENAME));
        $row = mysql_fetch_assoc($rst);
        $this->assertSame($testValue, $row['col1']);
    }
    
    public function testCanGetClientInfo() {
        $rst = mysql_get_client_info();
        $this->assertNotNull($rst);
    }

    public function testCanHandleNumRowsError() {
        $result = $this->insertRowsAndSelect();
        mysql_close();
        $rst = mysql_num_rows($result);
        $this->assertFalse($rst);
    }

    public function testCanHandleAffectedRowsError() {
        $rst = mysql_affected_rows();
        $this->assertSame(-1, (int) $rst);

        mysql_query(sprintf("insert into DOESNOTEXISTS values(null, '%s')", $testValue));
        $rst = mysql_affected_rows();
        $this->assertSame(-1, (int) $rst);
    }
    
    
    /**
     * @param int $cnt
     * @return PDOStatement
     */
    private function insertRowsAndSelect($cnt = 1) {
        for ($i = 0; $i < $cnt; $i++) {
            $rst = mysql_query(sprintf("insert into %s values(null, 'Row %s')", self::$config->TABLENAME, $i+1));
        }
        return mysql_query(sprintf("select * from %s", self::$config->TABLENAME));
    }
    private function clearTable() {
        global $pdo_conn;
        if ($pdo_conn) {
            $pdo_conn->query(sprintf("delete from %s", self::$config->TABLENAME));
        }
    }
    
    
 /*TODO: 
  * enable the asserts for row count
  * 
  */
    

}
