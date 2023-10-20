<?php
namespace server;

use Swoole\Server;

class TcpServer
{
    protected $server = null;       //swoole\server对象
    protected $host   = "0.0.0.0";  //监听对应外网的IP端口，0.0.0.0监听所有IP
    protected $port   = 9501;       //监听端口

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

        //监听链接进入事件
        $this->server->on("connect", function ($serv, $fd) {
            echo "链接成功".PHP_EOL;
        });

        //监听数据接收事件
        $this->server->on("receive", function ($serv, $fd, $from_id, $data) {
            print_r($fd."--".$data);

            $serv->send($fd, "服务端向用户{$fd}发送数据: {$data}");
        });

        $this->server->on("close", function ($serv, $fd) {
            echo $fd."关闭链接".PHP_EOL;
        });

        $this->server->start();

    }
}

$tcpServer = new TcpServer();

//lsof -i:9501
//COMMAND   PID USER   FD   TYPE DEVICE SIZE/OFF NODE NAME
//php     24393 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)
//php     24394 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)
//php     24397 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)
//php     24398 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)
//php     24399 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)
//php     24400 root    6u  IPv4  57845      0t0  TCP *:9501 (LISTEN)

//netstat -nltp

//pstree -p 24393
//php(24393)【master进程】─┬─php(24394)【manager进程】─┬─php(24397)【worker进程】
            //            │                          ├─php(24398)【worker进程】
            //            │                          ├─php(24399)【worker进程】
            //            │                          └─php(24400)【worker进程】
            //            ├─{php}(24395)
            //            └─{php}(24396)




















