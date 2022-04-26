<?php
use Swoole\Process;

$start_time = time();

$citys = ['gulou','jianye','xuanwu','yuhuatai','jiangning','pukou'];

$page = 10;

$connect = [
    'host'=>'127.0.0.1',
    'port'=>3306,
    'user'=>'root',
    'password'=>'lampol',
    'database'=>'lianjia'
];

foreach($citys as $city){

    $process = new Swoole\Process(function($worker)use($city,$page){
        echo  'start city...'.$city.PHP_EOL;
        for($i=1;$i<$page;$i++){
            echo 'start page...'.$i.PHP_EOL;
            $url = 'https://nj.lianjia.com/zufang/'.$city.'/pg'.$i;
            $data = get_data($url);
            $worker->push(json_encode($data,JSON_UNESCAPED_UNICODE));
        }
    });
    $process->useQueue();
    $pid = $process->start();
    $workers[$pid] = $process;

}

foreach($workers as $worker){
    for($i=1;$i<$page;$i++){
        $data = $worker->pop();
        insert_data($data,$connect);
    }
}

function insert_data($data,$connect){
    $data = json_decode($data,true);

    $scheduler = new Swoole\Coroutine\Scheduler();
    $scheduler->add(function()use($data,$connect){
        $mysql = new Swoole\Coroutine\MySQL();
        $mysql->connect($connect);

        foreach($data as $v){
            $stmt = $mysql->prepare('INSERT INTO info (title,address,square,direction,huxing,price)VALUES(?,?,?,?,?,?)');
            $stmt->execute([$v['title'],$v['address'],$v['square'],$v['direction'],$v['huxing'],$v['price']]);
        }
    });

    $scheduler->start();

}


function get_data($url){
    $html = file_get_contents($url);

    $preg_div = '/<div class=\"content__list--item--main\">.*?<\/div>/ism';

    preg_match_all($preg_div,$html,$match_div);

    foreach($match_div[0] as $k=>$v){
        $preg_a = '/<a .*?>.*?<\/a>/ism';
        //匹配房产信息标题  地址
        preg_match_all($preg_a,$v,$match_a);
        if(count($match_a[0])<4){
            continue;
        }

        list($a,$b,$c,$d) = $match_a[0];
        $data[$k]['title'] = trim(strip_tags($a));
        $data[$k]['address'] =trim(strip_tags($b)).'/'.trim(strip_tags($c)).'/'.trim(strip_tags($d)) ;
        //匹配房产信息的面积  朝向 户型

        $preg_i = '/<\/i>.*?<i>/ism';
        preg_match_all($preg_i,$v,$match_i);
        list($e,$f,$g) = $match_i[0];
        $data[$k]['square'] = trim(strip_tags($e));
        $data[$k]['direction'] = trim(strip_tags($f));
        $data[$k]['huxing'] = trim(strip_tags($g));
        //匹配月租

        $preg_span = '/<em>.*?<\/em>/ism';
        preg_match_all($preg_span,$v,$match_span);

        $data[$k]['price'] = trim(strip_tags($match_span[0][0]));
    }

    return $data;
}