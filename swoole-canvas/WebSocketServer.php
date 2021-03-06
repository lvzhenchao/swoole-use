<?php
namespace server;

use Swoole\WebSocket\Server;

class WebSocketServer
{
    protected $server = null; //示例server对象
    protected $host   = "0.0.0.0"; //监听对应外网的IP
    protected $port   = 9505; //监听端口

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
        $this->server->on("open", [$this, 'onOpen']);
        //监听WebSocket消息事件
        $this->server->on("message", [$this, 'onMessage']);
        //监听客户端连接关闭事件
        $this->server->on("close", [$this, 'onClose']);
        $this->server->start();
    }

    public function onOpen($ws, $request) {
        echo "客户端连接 : {$request->fd}".PHP_EOL;
    }


    //前端已经把数据传了过来，需要把传来的数据发送给其他人
    public function onMessage($ws, $frame) {
        foreach ($ws->connections as $fd) {
            $ws->push($fd, $frame->data);//向 WebSocket 客户端连接推送数据，长度最大不得超过 2M
        }
    }

    public function onClose($ws, $fd) {
        echo "客户端关闭：{$fd}".PHP_EOL;
    }
}

$webSocketServer = new WebSocketServer();