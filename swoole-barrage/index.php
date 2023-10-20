<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
	<link rel="stylesheet" href="js/DPlayer.min.css" />
</head>
<body>

<div style="width:600px;height:500px" id="dplayer"></div>



<script src="js/hls.min.js"></script>
<script src="js/DPlayer.min.js"></script>
<script>
const dp = new DPlayer({
	container: document.getElementById('dplayer'),
	live: true,
    danmaku: true,
    apiBackend: {
        read: function() {
			ws = new WebSocket('ws://192.168.33.10:9505');
			ws.onopen=function(){
				console.log('success connect');
			}
        },
        send: function(endpoint) {
			console.log(endpoint.data.text);

			ws.send(endpoint.data.text);
        },
    },

    video: {
        url: '怀念青春.mp4',//http://ivi.bupt.edu.cn/hls/cctv5phd.m3u8',
		pic:'111.jpg',
        type: 'hls',
    },
});

ws.onmessage=function(e){

	const danmaku = {
    	text: e.data,
    	color: '#fff',
    	type: 'right',
	};
	dp.danmaku.draw(danmaku);
}


</script>
</body>
</html>

