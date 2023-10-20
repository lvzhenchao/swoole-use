$(document).ready(function() {

    //首先，找到 <canvas> 元素
    var el = document.getElementById('canvas');

    // 设置canvas的宽和高，这样才能保证页面的坐标和canvas的坐标是一致的
    el.width  = document.body.clientWidth;
    el.height = document.body.clientHeight;

    //创建 context 对象
    //getContext("2d") 对象是内建的 HTML5 对象，拥有多种绘制路径、矩形、圆形、字符以及添加图像的方法
    var ctx = el.getContext('2d');
    var isDrawing;
    var point = {};

    // 因为我们把背景色设置成了黑色，所以我们这里把线的颜色设置成白色
    ctx.strokeStyle = '#ffffff';


    var ws = new WebSocket('ws://192.168.33.10:9505');

    // 当连接成功后，再进行绘画操作，以免数据丢失
    ws.onopen = function () {
        el.onmousedown = function (e) {
            isDrawing = true;
            ctx.moveTo(e.clientX,e.clientY);//moveTo(x,y) 定义线条【开始坐标】
            // 所以这里加了一个type参数进行区分，type=1是moveTo，type=2是lineTo
            sendPoint(e,1);
        };

        el.onmousemove = function (e) {
            if(isDrawing){
                ctx.lineTo(e.clientX,e.clientY);//lineTo(x,y) 定义线条【结束坐标】
                ctx.stroke();
                sendPoint(e,2);
            }
        }

        el.onmouseup = function (e) {
            isDrawing = false;
        }
    };

    // 当从后端接收到数据时触发
    ws.onmessage = function (e) {
        // 后端发过来的也是json字符串格式的数据，所以需要解析
        var data = JSON.parse(e.data);
        if(data.type == 1){
            ctx.moveTo(data.x,data.y);
        }else if(data.type == 2){
            ctx.lineTo(data.x,data.y);
            ctx.stroke();
        }

    };

    //此方法是向后端传送数据的方法
    function sendPoint(e,type) {
        point = {
            type:type,
            x:e.clientX,
            y:e.clientY,
        };
        // 这里我们定义传过去的值是json字符串的形式
        // 这样方便我们接收后端传的数据进行解析处理
        ws.send(JSON.stringify(point));
    }
});


