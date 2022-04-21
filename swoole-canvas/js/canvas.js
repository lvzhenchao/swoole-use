//使用 JavaScript 来绘制图像；canvas 元素本身是没有绘图能力的。所有的绘制工作必须在 JavaScript 内部完成
//参考地址：https://www.runoob.com/html/html5-canvas.html

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

el.onmousedown = function (e) {
    isDrawing = true;
    ctx.moveTo(e.clientX,e.clientY);//moveTo(x,y) 定义线条【开始坐标】
};

el.onmousemove = function (e) {
    if(isDrawing){
        ctx.lineTo(e.clientX,e.clientY);//lineTo(x,y) 定义线条【结束坐标】
        ctx.stroke();//stroke() 方法来【绘制线条】
    }
};
el.onmouseup = function (e) {
    isDrawing = false;
};


