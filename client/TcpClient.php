<?php
namespace client;

use Swoole\Client;

class TcpClient
{

    public function __construct()
    {
        $this->index();
    }
    public function index()
    {
        $client = new Client(SWOOLE_SOCK_TCP);

        if (!$client->connect("127.0.0.1", 9503, 0.5)){
            die("链接失败");
        }

        if (!$client->send("1370164516@qq.com")) {
            echo "发送失败";
        }

        //接收服务端接收数据
        $data = $client->recv();
        if (empty($data)) {
            die("服务端没有数据返回");
        }
        print_r($data).PHP_EOL;

        $client->close();
        exit();
    }
}

$tcpClientr = new TcpClient();
