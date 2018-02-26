<?php
namespace corephp\base;
/**
 * 计算分页代码
 * @param int $num 总文章数
 * @param int $cnt 每页显示文章数
 * @param int $curr 当前显示页码数
 * @return arr $pages 返回一个页码数=>地址栏值的关联数组
 */
class Page{
    static public function cPage($num,$cnt,$curr)
    {
        //计算最大页码数 $max


        if($num>0){
            $max=(ceil($num/$cnt));
        }else{
            return $pages='';
        }
        //计算最左边的页码数
        $left = max($curr - 2,1);


        //计算最右侧页码数
        $right = $left+4;
        $right = min($max,$right);
        //当页码使劲靠右侧不足5个页码时再次确认页码
        $left = $right - 4;
        $left = max($left,1);

        //将获取的5个页码数放进数组里
        for($i=$left;$i<=$right;$i++){
            $pages[$i]  = $i;
        }
        return $pages;
    }
}
