<?php
namespace app\controllers;

use app\models\CategoryModel;
use app\models\FlinkModel;
use corephp\base\Controller;
use app\models\ArticleModel;
use corephp\base\Page;

class ArticleController extends Controller{

    /**
     * @param  int $cnt 每页显示的文章数
     * @param int $offset mysql搜索的限制条件
     * @param int $curr 页面当前页码
     */
    public $cnt;
    public $offset;
    public $curr;
    /**
     * ArticleController constructor.
     * 用于获取必要的公用数据
     * @param array $new 从数据库获取最新的文章
     * @param array $category 从数据库获取所有栏目
     * @param array $link 从数据库获取所有友情链接
     */
    public function __construct($controller, $action)
    {
        parent::__construct($controller, $action);
        $this->cnt = 5;
        $this->curr = isset($_GET['page']) ? is_numeric($_GET['page']) ? $_GET['page'] : exit(header("location:/404.html")) : 1;
        $this->offset =($this->curr-1)*$this->cnt;
        $new = (new ArticleModel())->order(['id DESC limit 0,5 '])->fetchALL();
        $category = (new CategoryModel())->order(['id DESC '])->fetchALL();
        $link = (new FlinkModel())->order(['id DESC '])->fetchALL();
        $this->assign('curr',$this->curr);
        $this->assign('new',$new);
        $this->assign('cat',$category);
        $this->assign('link',$link);
    }

    /**
     * @method article()获取文章,视图化文章显示页
     * @param string $keyword 文章搜索的关键词
     * 如果存在$keyword在文章表中搜索相关文章并对结果进行关键词突出处理
     * @param string $limit mysql限制条件
     * @param int $count 文章总数
     * @param array $pages 分页数
     * @param array $article 文章
     * @param array @category 文章对应的栏目
     * $catname array 文章对应的栏目名称
     */
    public function article(){
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        if($keyword){
            $limit = 'ORDER BY id DESC limit '.$this->offset.','.$this->cnt;
            $article = (new ArticleModel())->search($keyword,$limit);
            if(!empty($article)){
                $count = (new ArticleModel())->search($keyword);
                $count = count($count);
                $pages = Page::cPage($count,$this->cnt,$this->curr);
                foreach ($article as &$value){
                    $category = (new CategoryModel())->where(['id = ?'],[$value['cat_id']])->fetch();
                    $catname[$value['cat_id']] = empty($category) ? '' : $category['name'];
                    //对搜索结果中的关键词进行突出处理
                    $replace = '<span class="keyword">' .$keyword.'</span>' ;//关键词替换的字符串
                    $start = mb_stripos($value['title'],$keyword);
                    if(is_numeric($start)) $value['title'] = str_ireplace($keyword,$replace,$value['title']);
                    $start=mb_stripos($value['content'],$keyword);
                    if(is_numeric($start)){
                        $value['content'] = strip_tags($value['content'] );
                        if($start>10) $start -= 10;
                        if (mb_strlen($value['content'],'UTF-8')-$start > 240)  $value['content'] = mb_substr($value['content'],$start,240,'UTF-8') .'...';
                        $value['content'] = str_ireplace($keyword,$replace,$value['content']);
                    }
                }
            }else{
                $pages = '';
                $catname = '';
                $article = '';
            }
        }else{
            $article = (new ArticleModel())->order(['id DESC limit '.$this->offset .' , '.$this->cnt])->fetchALL();
            if(empty($article))  exit(header("location:/404.html"));
            $count = (new ArticleModel())->count();
            $pages = Page::cPage($count,$this->cnt,$this->curr);
            foreach ($article as &$value){
                $category = (new CategoryModel())->where(['id = ?'],[$value['cat_id']])->fetch();
                $catname[$value['cat_id']] = empty($category) ? '' : $category['name'];
                //对文章内容进行节选
                $content = strip_tags($value['content']);
                if (mb_strlen($value['content'],'UTF-8') > 240)  $value['content'] = mb_substr($content,0,240,'UTF-8');
                $value['content'] .= "...";
            }
        }

        $this->assign('keyword', $keyword);
        $this->assign('catname', $catname);
        $this->assign('article', $article);
        $this->assign('pages',$pages);
        $this->render();

    }

    /**
     * @method category()获取栏目下的文章,视图化显示
     * @param string $param 栏目别名
     * @param array $cat 栏目别名对应的栏目
     * 如果为空跳转到404页面
     * @param int $count 文章总数
     * @param array $pages 分页数
     * @param array $article 文章
     * @param array @category 文章对应的栏目
     *
     */
    public function category($param){

        $cat = (new CategoryModel())->where(['nickname = ?'], [$param])->fetch();
        if(empty($cat)) exit(header("location:/404.html"));
        $count = (new ArticleModel())->where(['cat_id = ?'], [$cat['id']])->count();
        $pages = Page::cPage($count,$this->cnt,$this->curr);
        $article = (new ArticleModel())->where(['cat_id = ?'], [$cat['id']])->order(['id DESC limit ' . $this->offset . ' , ' . $this->cnt])->fetchALL();

        foreach ($article as &$value){
            $content = strip_tags($value['content']);
            if (mb_strlen($value['content'],'UTF-8') > 240)  $value['content'] = mb_substr($content,0,240,'UTF-8');
            $value['content'] .= "...";
        }


        $this->assign('category', $cat);
        $this->assign('article', $article);
        $this->assign('pages',$pages);
        $this->render();

    }

    /**
     * @method article_detail()单篇文章,视图化显示
     * @param int $id 文章id
     * 如果不存在id转到blog主页面
     * @param array $art id对应的文章
     * 如果为不存在跳转到404页面
     * @param array @category 文章对应的栏目
     *
     */
    public function article_detail(){
        $id = empty($_GET['id']) ? '' : $_GET['id'];
        if(empty($id)) exit(header("location:/404.html"));
        $art = (new ArticleModel())->where(['id = ?'], [$id])->fetch();
        if (empty($art)) exit(header("location:/404.html"));
        $category = (new CategoryModel())->where(['id = ?'],[$art['cat_id']])->fetch();
        $this->assign('category', $category);
        $this->assign('art', $art);
        $this->render();
    }

}

