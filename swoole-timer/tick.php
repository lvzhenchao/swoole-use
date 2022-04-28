<?php

//每个3000ms触发一次
$timer_id = swoole_timer_tick(3000, function ($id) {
    echo "tick 3000ms - ".date("Y-m-d H:i:s")."-".$id."\n";
});

//9000ms后删除定时器
swoole_timer_after(9000, function () use ($timer_id) {
    echo "9000ms - ".date("Y-m-d H:i:s")."-".$timer_id."\n";
    echo swoole_timer_clear($timer_id);
});
