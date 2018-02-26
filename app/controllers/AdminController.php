<?php
namespace app\controllers;

use app\models\AdminModel;
use app\models\CategoryModel;
use app\models\FlinkModel;
use corephp\base\Controller;
use app\models\ArticleModel;
use corephp\base\Page;

class AdminController extends Controller{
    //登录界面
    public function index(){
        $this->render();
    }

    //判断用户是否正确
    public function login(){
        session_start();
        if(isset($_SESSION['admin']) && $_SESSION['admin']) {
            echo json_encode(['error' => '0','msg' => '已经登录过，将自动转入主页']);
            exit();
        }

        if( empty($_POST)){
            header('location:/admin');
            exit();
        }
        $name = $_POST['name'];
        $pwd  = md5($_POST['pwd']);
        //查询用户
        $res = (new AdminModel())->where(["name = ?"],[$name])->fetch();
        $password = $res['password'];

        if($pwd === $password){
            //上次登录ip
            $ip = long2ip($res['last_ip']);
            //上次登录时间
            $time = $res['last_time'];
            //第几次数登录
            $num = $res['num'] + 1;
            setcookie('last_ip',$ip);
            setcookie('last_time',$time);
            setcookie('num',$num);
            //更改最后登录时间，ip，次数
            $data['last_ip'] = sprintf('%u',ip2long($_SERVER['REMOTE_ADDR']));;
            $data['num'] = $res['num'] + 1;
            $data['last_time'] = date('Y-m-d H:i:s',time());
            (new AdminModel())->where(['name = :name'],[':name' => $name])->update($data);
            $_SESSION['admin'] = true;
            $_SESSION['user'] = $name;
            echo json_encode(['error' => '0','msg' => '登录成功']);
        }else{
            echo json_encode(['error' => '1','msg' => '用户名或密码错误']);
        }
    }

    //退出
    public function outLogin(){
        session_start();
        if (isset($_SESSION['admin']) && $_SESSION['admin']){
            setcookie('last_ip','',time()-3600);
            setcookie('last_time','',time()-3600);
            setcookie('num','',time()-3600);
            unset($_SESSION['admin']);
            unset($_SESSION['user']);
            session_destroy();
        }
        header('location:/admin');
    }

    //主页面
    public  function main(){
        session_start();
        if (!$_SESSION['admin']){
            header('location:/admin');
            exit();
        }
        // 获取系统信息
        $data['system'] = php_uname('s');
        $data['web'] = $_SERVER["SERVER_SOFTWARE"];
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['php'] = PHP_VERSION;
        $data['msql'] = 'mysql 5.7.1';
        //文章数
        $data['count'] =  (new ArticleModel())->count();
        //链接数
        $data['link'] =  (new FlinkModel())->count();
        $this->assign('data',$data);
        $this->render();

    }
    //栏目显示
    public function category() {
        session_start();
        if (!$_SESSION['admin']){
            header('location:/admin');
            exit();
        }
        //获取栏目
        $category = (new CategoryModel())->order(['id DESC'])->fetchALL();
        //不在任何栏目中的文章数量
        $count = (new ArticleModel())->where(['cat_id = ?'],['0'])->count();

        $this->assign('category', $category);
        $this->assign('count',$count);
        $this->assign('i', 0);
        $this->render();
    }
    //更新栏目显示界面
    public function updateCategory(){
        empty($_GET['id']) ? exit(header("location:/404.html")) :  $id = $_GET['id'];
        if(!is_numeric($id))  exit(header("location:/404.html"));
        //获取要更新的栏目信息
        $category = (new CategoryModel())->where(['id = ?'],[$id])->fetch();
        if(empty($category)) header('location:/404.html');
        $this->assign('category',$category);
        $this->render();

    }

    //增加或更新栏目
    public  function addCategory (){
        //获取栏目信息
        if(empty($_POST)) exit('error');
        $data['name']= $_POST['name'];
        $data['nickname'] = $_POST['nickname'];
        $data['keywords'] = $_POST['keywords'];
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        //判断栏目名称或别名是否存在
        $catname = (new CategoryModel())->where(['name = ?'],[$data['name']])->fetch();
        $catnickname = (new CategoryModel()) ->where(['nickname = ?'],[$data['nickname']])->fetch();
        if($catname || $catnickname){
            //如果栏目存在返回"栏目存在",否则有具体id更新栏目
            echo json_encode(['error' => '1','msg' => '此栏目已存在']);
        }else{
            if(!empty($id)){
                (new CategoryModel())->where(['id = :id'],[':id' => $id])->update($data);
                echo json_encode(['error' => '0', 'msg' => '更新成功']);
            }else {
                (new CategoryModel())->add($data);
                echo json_encode(['error' => '0', 'msg' => '添加成功']);
            }
        }
    }


    //删除栏目
    public function deleteCategory(){
        if(empty($_POST['id'])) exit('error');
        $id = $_POST['id'];
        (new CategoryModel())->delete($id);
        echo json_encode(['error' => '0', 'msg' => '删除成功']);

    }

    //文章显示
    public function article($param = ''){
        session_start();
        if (!$_SESSION['admin']){
            header('location:/admin');
            exit();
        }
        //分页
        //当前页码
        $curr = isset($_GET['page']) ? $_GET['page'] : 1;
        //每页文章数
        $cnt = 5;
        //限制搜索结果
        $offset =($curr-1)*$cnt;

        //获取文章
        //$param 栏目名称 如果存在获取栏目下文章，否则获取全部文章
        if(empty($param)) {
            $article = (new ArticleModel())->order(['id DESC limit '.$offset .' , '.$cnt])->fetchALL();
            $catname = array();
            $catnick = array();
            //获取文章对应的栏目名称和别名
            foreach ($article as $value){
                $category = (new CategoryModel())->where(['id = ?'],[$value['cat_id']])->fetch();
                //栏目名称
                $catname[$value['cat_id']] = empty($category['name']) ? '未分类' : $category['name'];
                //栏目别名
                $catnick[$value['cat_id']] = empty($category['nickname']) ? 'undefined' : $category['nickname'];
            }
            //文章总数
            $count = (new ArticleModel())->count();
        }else{
            if($param == 'undefined'){
                $article = (new ArticleModel())->where(['cat_id = ?'],['0'])->order(['id DESC limit '.$offset .' , '.$cnt])->fetchALL();
                $catname = '未分类';
                $count = (new ArticleModel())->where(['cat_id = ?'],['0'])->count();
            }else {
                $cat = (new CategoryModel())->where(['nickname = ?'], [$param])->fetch();
                if(empty($cat)) exit(header("location:/404.html"));
                $article = (new ArticleModel())->where(['cat_id = ?'], [$cat['id']])->order(['id DESC limit ' . $offset . ' , ' . $cnt])->fetchALL();
                $catname = $cat['name'];
                $count = (new ArticleModel())->where(['cat_id = ?'], [$cat['id']])->count();
            }
            $catnick = $param;

        }
        //栏目名称
        $this->assign('catname', $catname);
        //栏目别名
        $this->assign('catnick',$catnick);
        //文章信息
        $this->assign('article', $article);
        $this->assign('param',$param);
        //分页
        $pages = Page::cPage($count,$cnt,$curr);
        $this->assign('pages',$pages);
        $this->assign('curr',$curr);
        $this->assign('count',$count);
        $this->render();

    }

    //添加文章表单页
    public function addArticle(){
        session_start();
        if (!$_SESSION['admin']){
            exit(header('location:/admin'));
        }
        //获取栏目
        $category = (new CategoryModel())->order(['id DESC'])->fetchALL();
        $this->assign('category', $category);
        $this->render();
    }

    //更新文章表单页
    public function upArticle(){
        //获取文章
        $id = empty($_GET['id']) ? '' : $_GET['id'];
        if(empty($id)) exit(header('location:/admin/article'));
        $art = (new ArticleModel())->where(['id = ?'], [$id])->fetch();
        if (empty($art)) exit(header('location:/404.html'));
        //获取栏目
        $category = (new CategoryModel())->order(['id DESC'])->fetchALL();
        $this->assign('category', $category);
        $this->assign('art', $art);
        $this->render();
    }

    //文章增加或修改
    public function newArticle(){
        if(empty($_POST)) exit('not found');
        $data['title']  = empty($_POST['title']) ? ''  : $_POST['title'];
        $data['cat_id'] = empty($_POST['cat_id']) ? '0' : $_POST['cat_id'];
        $art_id = empty($_POST['art_id']) ? '' : $_POST['art_id'];
        $data['content'] = empty($_POST['content']) ? '' : $_POST['content'];
        $data['pubtime'] = date('Y-m-d H:i:s',time());
            //添加文章
        if(empty($art_id)){
            //增加栏目中文章的个数
            if($data['cat_id'] != 0){
                $cat = (new CategoryModel())->where(['id = ?'],[$data['cat_id']])->fetch();
                $num['num'] = $cat['num']+1;
                (new CategoryModel())->where(['id = :id'],[':id' => $data['cat_id']])->update($num);
            }
            (new ArticleModel())->add($data);
            echo json_encode(['error' => '0','msg' => '添加成功']);
        }else {
            //更新文章
            $art = (new ArticleModel())->where(['id = ?'], [$art_id])->fetch();
            //判断栏目是否变更，如果改变对应的栏目文章数也改变
            if($data['cat_id'] !== $art['cat_id']){
                $cat = (new CategoryModel())->where(['id = ?'],[$data['cat_id']])->fetch();
                $num['num'] = $cat['num']+1;
                (new CategoryModel())->where(['id = :id'],[':id' => $data['cat_id']])->update($num);
                if($art['cat_id'] != '0'){
                    $cat = (new CategoryModel())->where(['id = ?'],[$art['cat_id']])->fetch();
                    $num['num'] = $cat['num']-1;
                    (new CategoryModel())->where(['id = :id'],[':id' => $art['cat_id']])->update($num);
                }
            }
            (new ArticleModel())->where(['id = :id'],[':id' => $art_id])->update($data);
            echo json_encode(['error' => '0', 'msg' => '更新成功']);
        }
    }

    //删除文章
    public function delArticle(){
        if(empty($_POST)) exit (header('location:/admin/article'));
        $id = $_POST['id'];
        if(is_array($id)) {
            for($i=0;$i<count($id);$i++) {
                //对应栏目文章数减少
                $art = (new ArticleModel())->where(['id = ?'], [$id[$i]])->fetch();
                if ($art['cat_id'] != '0') {
                    $cat = (new CategoryModel())->where(['id = ?'], [$art['cat_id']])->fetch();
                    $num['num'] = $cat['num'] - 1;
                    (new CategoryModel())->where(['id = :id'], [':id' => $art['cat_id']])->update($num);
                }
                //删除文章
                (new ArticleModel())->delete($id[$i]);
            }
            echo json_encode(['error' => '0', 'msg' => '删除成功']);
        }

    }

    //友情链接显示
    public function flink() {
         session_start();
          if (!$_SESSION['admin']){
              exit(header('location:/admin'));
          }
        //获取链接
        $link = (new FlinkModel())->order(['id DESC'])->fetchALL();
        $this->assign('link', $link);
        $this->render();
    }

    //增加或更新链接
    public  function addLink (){
        //获取栏目信息
        $data['name']= empty($_POST['name']) ? '' : $_POST['name'];
        $data['url']= empty($_POST['web']) ? '' : $_POST['web'];
        $data['keywords']= empty($_POST['keywords']) ? '' : $_POST['keywords'];
        if(empty($data['name'] || empty($data['url']))) exit('网页不能为空');
        $id = empty($_POST['id']) ? '' : $_POST['id'];

        if(!empty($id)){
            (new FlinkModel())->where(['id = :id'],[':id' => $id])->update($data);
        }else {
            (new FlinkModel())->add($data);
        }
        header('location:/admin/flink');
    }

    //更新链接显示界面
    public function updateLink(){
        empty($_GET['id']) ? exit(header("location:/404.html")) :  $id = $_GET['id'];
        if(!is_numeric($id))  exit(header("location:/404.html"));
        //获取要更新的栏目信息
        $link = (new FlinkModel())->where(['id = ?'],[$id])->fetch();
        if(empty($link)) exit(header("location:/404.html"));
        $this->assign('link',$link);
        $this->render();

    }

    //删除链接
    public function delLink(){
        is_numeric($_POST['id']) ? $id = $_POST['id'] :  exit(header("location:/404.html"));
        echo json_encode(['error' => '0', 'msg' => '删除成功']);

    }

}

