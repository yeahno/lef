# lef
##简介
lef是一个免费开源、快速、简单的面向对象的PHP高性能轻量级框架，本框架以高性能及高扩展为设计原则，在保持至简的代码的同时，也注重易用性和实用性。

主要特性：

- 高性能：采用服务式组件架构，惰性加载，服务随用随取，输出hello world平均耗时3.55ms,占用内存和峰值内存不超过200kb
- 高扩展：方便引入第三方类库，系统服务可扩展可替换减少核心依赖
- 灵活：分层级配置，单模块多模块支持，系统服务可配置，应用结构随心所欲
- 安全：基于pdo绑定数据过滤，多路验证

##快速入门
###多模块应用

新建目录结构

    project  应用部署目录
    ├─App           应用目录（可设置）
    │  ├─Config             公共配置目录（可更改,可选）
    │  │  └─config.php      公共配置文件（可更改,可选）
    │  ├─Home               模块1目录(可更改)  
    │  │  ├─Config          模块配置目录（可更改,可选）
    │  │  │  └─config.php   模块配置文件（可更改,可选）
    │  │  ├─Controller      模块控制器目录（不可更改）
    │  │  │  └─Index.php    Index控制器
    │  │  ├─Model           模型目录（可更改,可选）
    │  │  └─View            视图目录（可更改,可选）
    │  ├─Admin              模块2目录(可更改)  
    │  │  ├─Config          模块配置目录（可更改,可选）
    │  │  │  └─config.php   模块配置文件（可更改,可选）
    │  │  ├─Controller      模块控制器目录（不可更改）
    │  │  │  └─Index.php    Index控制器
    │  │  ├─Model           模型目录（可更改,可选）
    │  │  └─View            视图目录（可更改,可选）
    │  ├─Model              公共模型目录(可更改,可选)
    │  └─Runtime            运行目录(可更改,写权限)
    ├─Lef                   框架目录
    └─Public                项目域名根目录(可更改)
       └─index.php          入口文件

入口文件index.php

```
<?php
//define('DEBUG',true);
include '../Lef/Application.php';
class App extends Application{
    //public $app_dir='App/';
    protected function onConstruct(){
        /*
        //加载调试工具
        $console=new \Util\Console\Console();
        $this->registerService('console',$console);
        if(DEBUG){
            $console->setup();
        }*/
        //注册空间位置
        $this->registerNamespaces(array(
            'Home'=>$this->app_dir.'Home',
            'Admin'=>$this->app_dir.'Admin',
        ));
        /*
        //加载公共配置
        $config=new \Kenel\Config();
        $config->load(include APP_PATH.'Config/config.php');
        //注册配置服务
        $this->registerService('config',$config);
        */
        //注册及初始化路由服务
        $this->registerService('router',function(\Kenel\Router $router){
            //注册模块
            $router->registerModules(array('Home','Admin'));
            //设置默认模块(默认Home)
            $router->setDefaultModule('Home');
            //设置默认控制器(默认Index)
            $router->setDefaultController('Index');
            //设置默方法(默认index)
            $router->setDefaultAction('index');
            //设置路由规则
            $router->addRules(array(
                array(
                    'pattern'=>'/log',
                    'defaults'=>array(
                        'module'=>'home',
                        'controller'=>'index',
                        'action'=>'log',
                    ),
                    'requirements'=>[
                        '_method'=>'get'
                    ]
                )
            ));
            return $router;
        });
    }
}
(new App())->run();
?>
```

App/Home/Controller/Index.php

```
<?php
namespace Home\Controller;
class Index extends \ControllerAbstract{
    function indexAction($name='world'){
        return 'hello '.$name;
    }
    function logAction(){
        return 'log page';
    }
}
?>
```

###单模块应用
新建目录结构

    project  应用部署目录
    ├─App           应用目录（可设置）
    │  ├─Config             配置目录（可更改,可选）
    │  │  ├─config.php      配置文件
    │  ├─Controller         控制器目录(不可更改)
    │  │  ├─Index.php       Index控制器
    │  ├─Model              模型目录(可更改,可选)
    │  ├─Runtime            运行目录(可更改,写权限)
    │  └─View               视图目录(可更改,可选)
    ├─Lef                   框架目录
    └─Public                项目域名根目录(可更改)
       └─index.php          入口文件

入口文件index.php

```
<?php
//define('DEBUG',true);
include '../Lef/Application.php';
$app=new Application();
//$app->app_dir='App/'; //默认App/
//$app->runtime_dir='Runtime/'; //默认运行目录Runtime/
$app->registerNamespaces(array(
    'Controller'=>$app->app_dir.'Controller', //注册控制器目录
    'Model'=>$app->app_idr.'Model'            //注册模型目录
));
//初始化配置
/*
$this->registerService('config',function(\Kenel\Config $config){
    $config->load(include APP_PATH.'Config/config.php');
});
*/
$app->run();
?>
```

控制器App/Controller/Index.php

```
<?php
namespace Controller;
class Index extends \ControllerAbstract{
    function indexAction(){
        return 'hello world';
    }
}
?>
```




