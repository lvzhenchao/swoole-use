//使用 JavaScript 来绘制图像；canvas 元素本身是没有绘图能力的。所有的绘制工作必须在 JavaScript 内部完成

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

//1、创建WebSocket对象
var ws = new WebSocket("ws://192.168.33.10:9504");

//2、连接建立时触发；可以保证成功后，再进行绘画操作，以免数据丢失
ws.open = function() {
    el.onmousedown = function (e) {
        isDrawing = true;
        ctx.moveTo(e.clientX, e.clientY);//moveTo(x,y) 定义线条【开始坐标】
        sendPoint(e, 1);//type=1是moveTo
    };

    el.onmousemove = function (e) {
        if(isDrawing){
            ctx.lineTo(e.clientX,e.clientY);//lineTo(x,y) 定义线条【结束坐标】
            ctx.stroke();//stroke() 方法来【绘制线条】
            sendPoint(e, 2);// 注意这里传过去的type=2
        }
    };

    el.onmouseup = function (e) {
        isDrawing = false;
    };
};

//3、接收到后端的数据是触发
ws.onmessage = function (e) {
    var data = JSON.parse(e.data);
    if(data.type == 1){
        ctx.moveTo(data.x,data.y);
    }else if(data.type == 2){
        ctx.lineTo(data.x,data.y);
        ctx.stroke();
    }
};

//4、关闭
ws.onclose = function (e) {
    alert("连接已关闭...");
};


//此方法是向后端发送数据方法
function sendPoint(e,type) {
    point = {
        type:type,
        x:e.clientX,
        y:e.clientY,
    }
    // 这里我们定义传过去的值是json字符串的形式
    // 这样方便我们接收后端传的数据进行解析处理
    ws.send(JSON.stringify(point));
}


