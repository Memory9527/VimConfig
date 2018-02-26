<?php
/**
 * 设置数据表为文章表art的类，提供文章搜索方法
 */
namespace app\models;

use corephp\base\Model;
use corephp\db\Db;

class ArticleModel extends Model
{
//文章表
    protected $table = 'art';

    public function search($keyword,$limit='')
    {
        $sql = sprintf("select * from `$this->table` where `title` LIKE :keyword or `content` LIKE :keyword %s",$limit);
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [':keyword' => "%$keyword%"]);
        $sth->execute();

        return $sth->fetchAll();
    }
}