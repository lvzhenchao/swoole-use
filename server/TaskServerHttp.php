<?php
namespace server;

use Swoole\Http\Server;

class TaskServerHttp
{
    protected $serv = null;       //Swoole\Server对象
    protected $host = '0.0.0.0'; //监听对应外网的IP 0.0.0.0监听所有ip
    protected $port = 6789;      //监听端口号

    public function __construct()
    {
        $this->serv = new Server($this->host, $this->port);

        //设置参数
        //如果业务代码是全异步 IO 的，worker_num设置为 CPU 核数的 1-4 倍最合理
        //如果业务代码为同步 IO，worker_num需要根据请求响应时间和系统负载来调整，例如：100-500
        //假设每个进程占用 40M 内存，100 个进程就需要占用 4G 内存
        $this->serv->set(array(
            'worker_num' => 4,         //设置启动的worker进程数。【默认值：CPU 核数】
            'max_request' => 10000,    //设置每个worker进程的最大任务数。【默认值：0 即不会退出进程】
            'task_worker_num' => 4,    //设置异步任务的工作进程数量
            'daemonize' => 0,          //开启守护进程化【默认值：0】
        ));

        //监听服务器启动事件
        $this->serv->on('start', function ($server) {
            echo "Swoole http server is started";
        });

        //监听请求，HTTP服务器只需要关注请求响应即可
        //当有新的 HTTP 请求进入就会触发此事件
        //$request 对象，包含了请求的相关信息
        //$response 对象，对 request 的响应可以通过操作 response 对象来完成。
        //$response->end() 方法表示输出一段 HTML 内容，并结束此请求
        $this->serv->on('request', function ($request, $response) {

            // 使用 Chrome 浏览器访问服务器，会产生额外的一次请求，/favicon.ico，可以在代码中响应 404 错误。
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $response->end();
                return;
            }

            //投递异步任务
            //需要执行很耗时的操作时，可以投递一个异步任务到 TaskWorker 进程池中执行，不影响当前请求的处理速度。
            //这里模拟耗时10S的场景
            $data = [
                'task' => 'sendSms'
            ];
            $task_id = $this->serv->task($data);
            $response->end();
        });

        //处理异步任务(此回调函数在task进程中执行)
        //$task_id 任务ID
        //$reactor_id 进程ID
        //$data 传递过来的参数
        $this->serv->on('task', function ($serv, $task_id, $reactor_id, $data) {
            var_dump($data);
            // 模拟耗时10S的场景
            sleep(10);
            echo "New AsyncTask[id=$task_id]".PHP_EOL;
            //返回任务执行的结果
            $serv->finish("OK");
            // 告诉work进程
            return "task finish";
        });

        //处理异步任务的结果(此回调函数在worker进程中执行)
        //$task_id 任务ID
        //$data [onTask]事件返回的数据
        $this->serv->on('finish', function ($serv, $task_id, $data) {
            var_dump($data);
            echo "AsyncTask[$task_id] Finish".PHP_EOL;
        });

        //启动服务
        $this->serv->start();
    }
}

$taskServer = new TaskServerHttp();