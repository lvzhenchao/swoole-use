<?php
namespace server;

use Swoole\Server;

class TaskServerTcp
{
    protected $server = null; //示例server对象
    protected $host   = "0.0.0.0"; //监听对应外网的IP
    protected $port   = 9503; //监听端口

    public function __construct()
    {
        //实例化swoole服务
        $this->server = new Server($this->host, $this->port);//第三个参数默认是TCP,可不填

        //设置参数 worker_num：全异步IO的，CPU核数的1~4倍；同步IO的，需要根据请求响应时间和系统负载来调整,例如：100-500
        $this->server->set([
            "worker_num"  => 4,      //设置启动的worker进程数 【默认是CPU的核数】
            "max_request" => 10000,  //设置每个worker进程的最大任务数 【默认值：0 即不会退出进程】
            "daemonize"   => 0,      //守护进程化【默认值：0】
            'task_worker_num' => 4,    //设置异步任务的工作进程数量
        ]);

        //监听服务启动事件
        $this->server->on("start", function ($serv) {
            echo "Swoole http server is started".PHP_EOL;
        });

//        //监听请求
//        $this->server->on("request", function ($request, $response) {
//
//            //投递异步任务
//            //需要执行很耗时的操作时，可以投递一个异步任务到TaskWorker进程池中执行，不影响当前请求的处理速度
//            //模拟耗时10S的场景
//            $data = [
//                'task' => 'sendSms'
//            ];
//            $task_id = $this->server->task($data);
//            $response->end("hello end");
//        });

        $this->server->on("receive", function ($serv, $fd, $reactor_id, $data){//这个方法是TCP协议运行时必须有的

            $data = [
                'task' => 'sendSms'
            ];
            $task_id = $this->server->task($data);
            echo "login...".PHP_EOL;//即时返回
        });


        //处理异步任务【此回调函数在task进程中执行】
        //$task_id 任务ID
        //$reactor_id 进程ID
        //$data 传递过来的参数
        $this->server->on("task", function ($serv, $task_id, $src_worker_id, $data) {
            print_r($data);
            sleep(5);//模拟耗时任务的场景
            echo "新的异步Task任务的ID：{$task_id}".PHP_EOL;

            $serv->finish("OK");
            return "task finish";
        });

        //处理异步任务的结果【此回调函数在worker进程中执行】
        //$task_id 任务ID
        //$data [onTask]事件返回的数据
        $this->server->on("finish", function ($serv, $task_id, $data) {
            print_r($data);
            echo "异步任务完成：Task任务的ID：{$task_id}".PHP_EOL;
        });

        $this->server->start();


    }
}

$taskServer = new TaskServerTcp();