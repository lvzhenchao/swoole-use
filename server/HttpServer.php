<?php
namespace server;

use Swoole\Http\Server;

class HttpServer
{
    protected $server = null; //示例server对象
    protected $host   = "0.0.0.0"; //监听对应外网的IP
    protected $port   = 6789; //监听端口

    public function __construct()
    {
        //实例化swoole服务
        $this->server = new Server($this->host, $this->port);//第三个参数默认是TCP,可不填

        //设置参数 worker_num：全异步IO的，CPU核数的1~4倍；同步IO的，需要根据请求响应时间和系统负载来调整,例如：100-500
        $this->server->set([
            "worker_num"  => 4,      //设置启动的worker进程数 【默认是CPU的核数】
            "max_request" => 10000,  //设置每个worker进程的最大任务数 【默认值：0 即不会退出进程】
            "daemonize"   => 0,      //守护进程化【默认值：0】
        ]);

        //监听服务启动事件
        $this->server->on("start", function ($serv) {
            echo "Swoole http server is started".PHP_EOL;
        });

        //监听请求
        $this->server->on("request", function ($request, $response) {
            print_r($request);
            print_r($response);

            //返回内容
            $response->header("Content-Type", "text/html; charset=utf-8");
            //$response->end("我是Swoole Http服务器输出的返回内容");

            list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
            //根据 $controller, $action 映射到不同的控制器类和方法

            // 控制器首字母大写
            $controller = ucfirst($controller);

            //判断控制器类是否存在
            if (file_exists(__DIR__.'/'.str_replace('\\', '/', $controller).'.php')) {
                require_once __DIR__.'/'.str_replace('\\', '/', $controller).'.php';
                $obj= new $controller;
                //判断控制器方法是否存在
                if (!method_exists($obj, $action)) {
                    $response->status(404);
                    $response->end("<meta charset='UTF-8'>兄弟,方法不存在！");
                } else {
                    //如果存在此方法，输出结果
                    $response->end($obj->$action($request));
                }
            } else {
                $response->status(404);
                $response->end("<meta charset='UTF-8'>兄弟,方法不存在！");
            }

        });

        $this->server->start();


    }
}

$httpServer = new HttpServer();