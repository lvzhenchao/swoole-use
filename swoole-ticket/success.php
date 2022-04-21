<?php
//ids=cart-item-6_10|cart-item-5_10|

if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    $ids = explode("|", $_GET['ids']);
    foreach ($ids as $id) {
        if ($id == "") {
            continue;
        }
        $arr[] = substr($id, 10);
    }

    function http_post($data,$url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    http_post($arr, 'http://192.168.33.10:9999');
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width; initial-scale=1.0" />
    <title>在线选座订座（影院版）</title>
    <meta name="keywords" content="jQuery,选座" />
    <link href="./css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <a href="index.php?status=success">选座成功</a>
</body>

</html>