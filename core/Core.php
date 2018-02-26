<?php
namespace corephp;

//框架根目录
defined('CORE_PATH') or define('CORE_PATH',__DIR__);

/**
 * core框架核心
 */

class Corephp{
    protected  $config = [];

    public function __construct($config)
    {
        $this->config=$config;
    }

    public function run(){
        spl_autoload_register(array($this,'loadClass'));
        $this->setReporting();
        $this->unregisterGlobals();
        $this->setDbConfig();
        $this->route();
    }

    //路由处理
    public  function route(){
        $controllerName = $this->config['defaultController'];
        $actionName = $this->config['defaultAction'];
        $param = array();

        $url = $_SERVER['REQUEST_URI'];

        //清除?之后的内容
        $position = strpos($url,'?');
        $url = $position === false ? $url : substr($url,0,$position);
        //删除前后的"/"
        $url = trim($url,'/');

        if($url){
            //使用'/'分割字符串，并保存在数组中
            $urlArray = explode('/',$url);
            //删除空的数组元素
            $urlArray = array_filter($urlArray);

            //获取控制器名
            $controllerName = ucfirst($urlArray['0']);

            //获取动作名
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : $actionName;



            //获取URL参数
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();

        }
        //判断控制器和操作是否存在

        $controller = 'app\\controllers\\' . $controllerName . 'Controller';

        if (!class_exists($controller)) {
            header('location:/404.html');
        }
        if (!method_exists($controller,$actionName)){
            header('location:/404.html');
        }

        //实例化控制器
        $dispath = new $controller($controllerName,$actionName);


        //调用控制器的方法，一下等同于$dispath->$actionName($parme)

        call_user_func_array(array($dispath,$actionName),$param);
    }

    // 检测开发环境
    public function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }

    // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
    // 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
    // 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
    // 形式存在，这样写是不好的实现，会影响代码中的其他变量。
        public function unregisterGlobals()
        {
            if (ini_get('register_globals')) {
                $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
                foreach ($array as $value) {
                    foreach ($GLOBALS[$value] as $key => $var) {
                        if ($var === $GLOBALS[$key]) {
                            unset($GLOBALS[$key]);
                        }
                    }
                }
            }
        }

    //配置数据库信息
    public function setDbConfig(){
        if($this->config['db']){
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['dbname']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
        }
    }

    //自动加载类
    public  function loadClass($className){
        $classMap = $this->classMap();

        if (isset($classMap[$className])) {
            //包含内核文件
            $file = $classMap[$className];

        }elseif (strpos($className,'\\') !==false){
            //包含应用(application目录)文件
            $file = APP_PATH .str_replace('\\','/',$className). '.php';

            if (!is_file($file)) {
                return;
            }
        }else{
            return;
        }
        include $file;
    }

    // 内核文件命名空间映射关系
    protected function classMap()
    {
        return [
            'corephp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'corephp\base\Model' => CORE_PATH . '/base/Model.php',
            'corephp\base\View' => CORE_PATH . '/base/View.php',
            'corephp\base\Page' => CORE_PATH . '/base/Page.php',
            'corephp\db\Db' => CORE_PATH . '/db/Db.php',
            'corephp\db\Sql' => CORE_PATH . '/db/Sql.php',
        ];
    }


}
