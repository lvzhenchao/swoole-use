<?php
namespace server;

use Swoole\Coroutine\Redis;
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

        $http = $this->server->listen($this->host, 9999, SWOOLE_SOCK_TCP);
        $http->on("request", [$this, 'onRequest']);

            //设置参数 worker_num：全异步IO的，CPU核数的1~4倍；同步IO的，需要根据请求响应时间和系统负载来调整,例如：100-500
        $this->server->set([
            "worker_num"  => 4,      //设置启动的worker进程数 【默认是CPU的核数】
            "max_request" => 10000,  //设置每个worker进程的最大任务数 【默认值：0 即不会退出进程】
            "daemonize"   => 0,      //守护进程化【默认值：0】
        ]);

        //监听连接打开事件
        $this->server->on("open", [$this, 'onOpen']);
//        $this->server->on("request", [$this, 'onRequest']);
        //监听WebSocket消息事件
        $this->server->on("message", [$this, 'onMessage']);
        //监听客户端连接关闭事件
        $this->server->on("close", [$this, 'onClose']);
        $this->server->start();
    }

    public function onOpen($ws, $request) {
        echo "connestion open : {$request->fd}".PHP_EOL;
    }

    public function onRequest($request, $response) {
        $data = $request->post;

        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        if ($data) {
            foreach ($data as $v) {
                $redis->lPush("dy", $v);
            }
        }

    }

    //付款之后，通知所有人我已经买了这些票了
    public function onMessage($ws, $frame) {

        if ($frame->data == "success") {
            foreach ($ws->connections as $fd) {
                $ws->push($fd, '选座成功');
            }
        }

    }

    public function onClose($ws, $fd) {
        echo "客户端关闭：{$fd}".PHP_EOL;
    }
}

$webSocketServer = new WebSocketServer();