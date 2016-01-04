# mysql-continued
All existing mysql_* functions are deprecated and will be removed from php in version 7. This forces many users to migrate to mysqli or pdo:mysql.
mysql-continued aims to be a drop in replacement for the existsing mysql-library. Simply include the php-file and keep on running
without modifying and testing your existing code.

## limitations
mysql-continued has these limitations:
- it can not handle multiple database connections
- it does not accept passing the $link_identifier resource into its functions (follows from first limitation)
- it implements most but not all exeisting functions (see below)
- it ignores the default ini-connect-values in mysql_connect();
- mysql_connect ignores the $new_link and $client_flags parameters

## dependencies
- pdo_mysql 

## unsupported functions
- mysql_list_*
- mysql_info()
- mysql_get_proto_info()
- mysql_client_encoding()
- mysql_create_db()
- mysql_data_seek()
- mysql_db_name()
- mysql_drop_db()
- mysql_fetch_field()
- mysql_fetch_lengths()
- mysql_fetch_flags()
- mysql_fields_*
- mysql_list_processes()
- mysql_thread_id()

## install
Composer
```
composer require doctorbeat/mysql-continued
```
  
Or old-school: download and
```
require_once MysqlContinued.php;
```
