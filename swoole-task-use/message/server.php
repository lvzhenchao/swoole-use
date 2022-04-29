<?php


class Server
{
    protected $server = null; //示例server对象
    protected $host   = "0.0.0.0"; //监听对应外网的IP
    protected $port   = 9503; //监听端口

    public function __construct()
    {
        //实例化swoole服务
        $this->server = new Swoole\Server($this->host, $this->port);//第三个参数默认是TCP,可不填

        //设置参数 worker_num：全异步IO的，CPU核数的1~4倍；同步IO的，需要根据请求响应时间和系统负载来调整,例如：100-500
        $this->server->set([
            "worker_num"  => 2,      //设置启动的worker进程数 【默认是CPU的核数】
            "max_request" => 4,      //设置每个worker进程的最大任务数 【默认值：0 即不会退出进程】
            "daemonize"   => 0,      //守护进程化【默认值：0】
            'task_worker_num' => 4,    //设置异步任务的工作进程数量
        ]);

        //启动后在主进程（master）的主线程回调此函数
        $this->server->on("Start", [$this, "onStart"]);
        //监听新的链接进入
        $this->server->on("Connect", [$this, "onConnect"]);
        //监听客户端发送的消息
        $this->server->on("Receive", [$this, "onReceive"]);
        //监听客户端退出，关闭
        $this->server->on("Close", [$this, "onClose"]);
        //监听异步任务，处理异步任务【worker 进程可以使用task()函数向task_worker进程投递新的任务】
        $this->server->on("Task", [$this, "onTask"]);
        //监听异步任务结束的反馈通知
        $this->server->on("Finish", [$this, "onFinish"]);

        //启动服务
        $this->server->start();

    }

    public function onStart($server) {
        echo "### onStart ###".PHP_EOL;
        echo "swoole".SWOOLE_VERSION." 服务已启动".PHP_EOL;
        echo "master_pid: {$server->master_pid}".PHP_EOL;
        echo "manager_pid: {$server->manager_pid}".PHP_EOL;
        echo "######".PHP_EOL;
    }

    public function onConnect($server, $fd) {
        echo "### onConnect ###".PHP_EOL;
        echo "客户端：".$fd." 已连接".PHP_EOL;
        echo "######".PHP_EOL;
    }

    public function onReceive($server, $fd, $reactor_id, $data) {
        echo "### onReceive ###".PHP_EOL;
        echo "woerker_id：{$server->worker_pid}".PHP_EOL;
        echo "客户端：{$fd} 发来的Email：{$data}".PHP_EOL;

        $params = [
            'fd' => $fd,
            'email' => $data
        ];

        $result = $server->task(json_encode($params));

        if ($result === false) {
            echo "任务分配失败 Task".$result.PHP_EOL;
        } else {
            echo "任务分配成功".$result.PHP_EOL;
        }

        echo "######".PHP_EOL.PHP_EOL;

    }

    public function onTask($server, $task_id, $from_id, $data) {
        echo "### onTask ###".PHP_EOL;
        echo "# {$server->worker_id} onTask：[PID={$server->worker_pid}]：task_id={$task_id}".PHP_EOL;

        //业务代码
        for ($i = 1; $i <= 5; $i ++) {
            sleep(2);
            echo "Task {$task_id} 完成了 {$i}/5 的任务".PHP_EOL;
        }

        $data_str = json_decode($data, true);
        $server->send($data_str['fd'], "Email：".$data_str['email']."发送成功");
        $server->finish($data);

        echo "######".PHP_EOL;
    }

    public function onFinish($server, $task_id, $data) {
        echo "### onFinish ###".PHP_EOL;
        echo "Task {$task_id} 已完成".PHP_EOL;
        echo "######".PHP_EOL;
    }

    public function onClose($server, $fd) {
        echo "Client：{$fd} Close".PHP_EOL;
    }



}

$server = new Server();
























