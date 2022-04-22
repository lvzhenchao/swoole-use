# 实战弹幕

# 直播协议

- HLS Http Live Streaming 苹果公司定义的基于http的流媒体实时传输协议；延时5~20秒；HTTP短链接
- RTMP Real Time Messaging Protocol 实时消息传输协议；延时1~3秒；TCP长链接
- HTTP—FLV RTMP封装在HTTP协议之上的；延时1~3秒；HTTP长链接

# WebRTC Web Real-Time Communication 网页即时通信，可以实现点对点的视频直播

# 直播流程
- 视频采集端：摄像头等（RTMP传输视频数据到服务端：推流）
- 直播流视频服务端：将采集到的的视频流，解析（H264/ACC编码），推送RTMP/HLS格式视频流至视频播放端
- 视频播放端：电脑的播放器（QuickTime Player、VLC）、手机端的播放器（native）、H5的video标签（拉流：）