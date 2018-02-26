<?php
namespace corephp\db;

use PDO;
use PDOException;
/**
 * 数据库操作类。
 * 单例模确保运行期间只有一个
 * 数据库连接对象
 *
 */
class Db{
    private static $pdo = null;
    public  static function pdo(){
        if (self::$pdo !==null) {
            return self::$pdo;
        }

        try{
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8',DB_HOST,DB_NAME);
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC);
            return self::$pdo = new PDO($dsn,DB_USER,DB_PASS,$option);
        }catch (PDOException $e){
            exit($e->getMessage());
        }
    }
}