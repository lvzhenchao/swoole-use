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
            'worker_num'      => 2, //开启2个worker进程
            'max_request'     => 4, //每个worker进程 max_request设置为4次
            'task_worker_num' => 4, //开启4个task进程
            'dispatch_mode'   => 4, //数据包分发策略 - IP分配
            'daemonize'       => false, //守护进程(true/false)
        ]);

        $this->server->on('Start',   [$this, 'onStart']);
        $this->server->on('Open',    [$this, 'onOpen']);
        $this->server->on("Message", [$this, 'onMessage']);
        $this->server->on("Close",   [$this, 'onClose']);
        $this->server->on("Task",    [$this, 'onTask']);
        $this->server->on("Finish",  [$this, 'onFinish']);

        $this->server->start();
    }

    public function onStart($serv) {
        echo "#### onStart ####".PHP_EOL;
        echo "SWOOLE ".SWOOLE_VERSION . " 服务已启动".PHP_EOL;
        echo "master_pid: {$serv->master_pid}".PHP_EOL;
        echo "manager_pid: {$serv->manager_pid}".PHP_EOL;
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onOpen($serv, $request) {
        echo "#### onOpen ####".PHP_EOL;
        echo "server: handshake success with fd{$request->fd}".PHP_EOL;
        $serv->task([
            'type' => 'login'
        ]);
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onTask($serv, $task_id, $from_id, $data) {
        echo "#### onTask ####".PHP_EOL;
        echo "#{$serv->worker_id} onTask: [PID={$serv->worker_pid}]: task_id={$task_id}".PHP_EOL;
        $msg = '';
        switch ($data['type']) {
            case 'login':
                $msg = '我来了...';
                break;
            case 'speak':
                $msg = $data['msg'];
                break;
        }
        foreach ($serv->connections as $fd) {
            $connectionInfo = $serv->getClientInfo($fd);//获取连接的信息
            if ($connectionInfo['websocket_status'] == 3) {
                $serv->push($fd, $msg); //长度最大不得超过2M
            }
        }
        $serv->finish($data);
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onMessage($serv, $frame) {
        echo "#### onMessage ####".PHP_EOL;
        echo "receive from fd{$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}".PHP_EOL;
        $serv->task([
            'type' => 'speak',
            'msg'  => $frame->data
        ]);
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onFinish($serv,$task_id, $data) {
        echo "#### onFinish ####".PHP_EOL;
        echo "Task {$task_id} 已完成".PHP_EOL;
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onClose($serv, $fd) {
        echo "#### onClose ####".PHP_EOL;
        echo "client {$fd} closed".PHP_EOL;
        echo "########".PHP_EOL.PHP_EOL;
    }


}

$webSocketServer = new WebSocketServer();