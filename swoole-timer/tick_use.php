<?php
//凌晨跑脚本，脚本中包含其他业务方或第三方接口，超时无响应或没有数据返回
//重试机制：每个5分钟发送一次，最多尝试5次，不论成功失败

$api_url  = 'xxx'; //接口地址
$exec_num = 0;     //执行次数
swoole_timer_tick(/*5*60**/1000, function($timer_id) use ($api_url, &$exec_num) {
    $exec_num ++ ;
    $result = file_get_contents($api_url);
    echo date('Y-m-d H:i:s'). " 执行任务中...(".$exec_num.")\n";
    if ($result) {
        //业务代码...
        swoole_timer_clear($timer_id); // 停止定时器
        echo date('Y-m-d H:i:s'). " 第（".$exec_num."）次请求接口任务执行成功\n";
    } else {
        if ($exec_num >= 5) {
            swoole_timer_clear($timer_id); // 停止定时器
            echo date('Y-m-d H:i:s'). " 请求接口失败，已失败5次，停止执行\n";
        } else {
            echo date('Y-m-d H:i:s'). " 请求接口失败，5分钟后再次尝试\n";
        }
    }
});
