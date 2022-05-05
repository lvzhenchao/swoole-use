<?php

class Live
{

    public function push() {
        $data = [
            'type' => intval($_GET['type']),
            'title' => !empty($teams[$_GET['team_id']]['name']) ?$teams[$_GET['team_id']]['name'] : '直播员',
            'logo' => !empty($teams[$_GET['team_id']]['logo']) ?$teams[$_GET['team_id']]['logo'] : 'logo',
            'content' => !empty($_GET['content']) ? $_GET['content'] : 'content',
            'image' => !empty($_GET['image']) ? $_GET['image'] : 'image',
        ];
        //print_r($_GET);
        // 获取连接的用户
        // 赛况的基本信息入库   2、数据组织好 push到直播页面
        $taskData = [
            'method' => 'pushLive',
            'data'   => $data
        ];
        $_POST['http_server']->task($taskData);
        return "操作成功";
    }

}