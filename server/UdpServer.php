<?php
namespace server;

use Swoole\Server;

class UdpServer
{
    protected $server = null;       //swoole\server对象
    protected $host   = "0.0.0.0";  //监听对应外网的IP端口，0.0.0.0监听所有IP
    protected $port   = 9502;       //监听端口

    public function __construct()
    {
        //实例化swoole服务
        $this->server = new Server($this->host, $this->port,SWOOLE_PROCESS,SWOOLE_SOCK_UDP);

        //设置参数 worker_num：全异步IO的，CPU核数的1~4倍；同步IO的，需要根据请求响应时间和系统负载来调整,例如：100-500
        $this->server->set([
            "worker_num"  => 4,      //设置启动的worker进程数 【默认是CPU的核数】
            "max_request" => 10000,  //设置每个worker进程的最大任务数 【默认值：0 即不会退出进程】
            "daemonize"   => 0,      //守护进程化【默认值：0】
        ]);

        //监听数据接收事件
        $this->server->on("packet", function ($serv, $data, $client_info) {
            echo "客户端信息：".$data.PHP_EOL;
            print_r($client_info);
            $this->server->sendto($client_info['address'], $client_info['port'], "This is server...".PHP_EOL);
        });



        $this->server->start();

    }
}

$udpServer = new UdpServer();

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




















