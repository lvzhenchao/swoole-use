<?php
namespace server;

use Swoole\WebSocket\Server;

class WebSocketServer
{
    protected $server = null; //示例server对象
    protected $host   = "0.0.0.0"; //监听对应外网的IP
    protected $port   = 9504; //监听端口

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

        //监听连接打开事件
        $this->server->on("open", function ($ws, $request) {
            echo "connestion open : {$request->fd}".PHP_EOL;
        });

        //监听WebSocket消息事件
        $this->server->on("message", function ($ws, $frame) {
            echo "客户端：{$frame->fd}发来的消息：{$frame->data}".PHP_EOL;
            $ws->push($frame->fd, "server:服务端已收到你的消息");
        });

        //监听客户端连接关闭事件
        $this->server->on("close", function ($ws, $fd) {
            echo "客户端关闭：{$fd}".PHP_EOL;
        });

        $this->server->start();


    }
}

$webSocketServer = new WebSocketServer();