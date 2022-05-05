<?php
/**
 * 对外可访问的控制器
 */
class Send
{
    /**
     * 发送验证码
     */
    public function index() {
        $taskData = [
            'method' => 'sendSms',
            'data' => [
                'phone' => "15910371690",
                'code'  => "489512",
            ]
        ];

        //调取异步任务 $_POST['http_server'] == $this->>server
        $_POST['http_server']->task($taskData);

        return "操作成功";
    }

}