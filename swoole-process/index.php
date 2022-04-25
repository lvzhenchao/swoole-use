<?php
use Swoole\Process;

$citys = ["gulou", "jianye", "xuanwu", "yuhuatai"];

foreach ($citys as $city) {
    $process = new Process(function () use ($city) {
        $url = "https://nj.lianjia.com/".$city."/gulou/pg2/#contentList";

        get_data($url);
    });

    $process->start();
}


function get_data($url){
    $html = file_get_contents($url);
    print_r($html);
}