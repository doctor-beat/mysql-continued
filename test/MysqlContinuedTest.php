<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require (__DIR__ . '/../vendor/autoload.php');

/**
 * Description of mysqlContinued
 *
 * @author ronald
 */
class MysqlContinuedTest extends PHPUnit_Framework_TestCase {
    const HOSTNAME = 'localhost';
    const USERNAME = '';
    const PASSWORD = '';
    const DATABASE = 'abc';
    const TABLENAME = 'v70lgvf2p3b5';
    
    private $dbh;
    private static $pdo;

    public static function setUpBeforeClass(){
        if (!function_exists('mysql_continued')) {
            throw new Exception("MysqlContinued not loaded, maybe your normal mysql-lib is still enabled?");
        }

        //self::$pdo = new PDO(sprintf('sqlite:memory:host=%s;dbname=%s;charset=UTF8', self::HOSTNAME, self::DATABASE), 
        self::$pdo = new PDO('sqlite:foo.db',
            self::USERNAME, 
            self::PASSWORD,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,));

        
        //create a test table
        self::$pdo->exec(sprintf("create table %s  (id INTEGER PRIMARY KEY AUTOINCREMENT, col1 string(200));", self::TABLENAME, self::TABLENAME));
        
    }
    
    public static function tearDownAfterClass() {
        self::$pdo->exec(sprintf("drop table if exists %s;", self::TABLENAME));        
    }
    
    public function setUp() {
        parent::setUp();
        $this->dbh = mysql_connect(self::HOSTNAME, self::USERNAME, self::PASSWORD);
        mysql_select_db(self::DATABASE);
        mysql_set_charset('utf8');
    }
    public function tearDown() {
        parent::tearDown();
        $this->clearTable();
    }
    public function testCanConnect() {
        global $pdo_conn;

        //connection done in setUp
        
        $this->assertNotNull($this->dbh);
        $this->assertSame($this->dbh, $pdo_conn);
        echo mysql_errno();
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
        $bool = mysql_select_db(self::DATABASE);
        $this->assertTrue($bool);        
        $this_>assertSame('', mysql_error());
    }
    public function testCanSetCharset() {
        $bool = mysql_set_charset('utf8');
        $this->assertTrue($bool);        
        $this_>assertSame('', mysql_error());
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
        $rst = mysql_query(sprintf("insert into %s values(null, 'insert')", self::TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(1, mysql_affected_rows());
    }
    public function testCanUpdate() {
        $this->insertRowsAndSelect(4);
        $lastId = mysql_insert_id();
        
        $rst2 = mysql_query(sprintf("update %s set col1 = 'my Value' where id  = %s", self::TABLENAME, $lastId));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst2);
        $this->assertSame(1, mysql_affected_rows());

        $rst3 = mysql_query(sprintf("select * from %s where id  = %s", self::TABLENAME, $lastId));
//        $this->assertEquals(1, mysql_num_rows($rst3));
        $row3 = mysql_fetch_assoc($rst3);
        $this->assertSame('my Value', $row3['col1']);
    }
    public function testCanDelete() {
        $this->insertRowsAndSelect(4);
        
        $rst = mysql_query(sprintf("delete from %s", self::TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(4, mysql_affected_rows());
    }
    public function testCanUpdateZeroRows() {
        $rst = mysql_query(sprintf("update %s set col1 = 'my Value' where 1=0", self::TABLENAME));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(0, mysql_affected_rows());
    }
    public function testCanFetchAssocAndDoWhile() {
        $rowCnt = 4;
        $this->insertRowsAndSelect($rowCnt);
        
        $rst = mysql_query(sprintf("select * from %s  order by id", self::TABLENAME));
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
    public function testCanReturnLastId() {
        $this->insertRowsAndSelect();

        $lastId = mysql_insert_id();
        $this->assertNotNull($lastId);
        $this->assertTrue($lastId>=1);

        $this->insertRowsAndSelect();
        $lastId2 = mysql_insert_id();
        $this->assertTrue($lastId < $lastId2);
    }
    
    public function testCanHandleUtf8() {
        $testValue = 'Ïnterñátiön€l';
        
        $rst = mysql_query(sprintf("insert into %s values(null, '%s')", self::TABLENAME, $testValue));
        $this->assertSame('', mysql_error());
        $this->assertTrue((boolean) $rst);
        $this->assertSame(1, mysql_affected_rows());
        
        $rst = mysql_query(sprintf("select * from %s order by id", self::TABLENAME));
        $row = mysql_fetch_assoc($rst);
        $this->assertSame($testValue, $row['col1']);
    }
    
    
    /**
     * @param int $cnt
     * @return PDOStatement
     */
    private function insertRowsAndSelect($cnt = 1) {
        for ($i = 0; $i < $cnt; $i++) {
            $rst = mysql_query(sprintf("insert into %s values(null, 'Row %s')", self::TABLENAME, $i+1));
        }
        return mysql_query(sprintf("select * from %s", self::TABLENAME));
    }
    private function clearTable() {
        global $pdo_conn;
        if ($pdo_conn) {
            $pdo_conn->query(sprintf("delete from %s", self::TABLENAME));
        }
    }
    
    
 /*TODO: 
  * enable the asserts for row count
  * test error conditions:
    - invalid query
  * connect failed
  * select db failed
  * invalid charset
  * last insert id not found
  * num rows emtpy
  * affected rows empty
  * 
  */
    

}
