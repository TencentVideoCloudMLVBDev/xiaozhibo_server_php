
# REST 接口

功能接口使用json格式，所有交互的http加上header `Content-Type:application/json`。

## 注册

- 接口： 

```
路径：       /register 

```

- 参数：

```json
{
	"userid" : "jacqiu",
	"password" : "md5(md5(password)+userid)", // md5后的密码字符
}
```

- 返回：

```json
{
	"code" : 200, //610,用户名格式错误; 611,密码格式错误; 612,用户已存在; 500,服务器错误(数据库查询失败)
	"message": "",  // 错误消息
}
```

## 登陆:


- 接口: 

```
路径：		/login
```

- 参数：

```json
{
	"userid": "jacqiu",
	"password": "",
}
```

- 返回: 

```json
{
	"code": 200,   //621,密码错误; 620,用户不存在; 500,服务器错误(数据库查询失败)
	"message": "OK",
	"data":{ 
		"token": "xxx",    //随机数
		"refresh_token": "xxx", //续期token，随机数
		"expires": 300,  // 过期时间（秒）
		"roomservice_sign": {       //登录roomservice的签名
			"sdkAppID": 123456,     // 云通信 sdkappid
			"userID": "xxxx",       // 用户id
			"userSig": "xxxxxxxx",  // 云通信用户签名
		},
		"cos_info": {
		    "Bucket": "xxx",           //cos bucket名
		    "Region": "xxx",           //cos bucket所在地域
		    "Appid":  "xxx",           //cos appid
		    "SecretId": "xxx"          //cos secretid
		}
	}
}
```


## 续期

登陆快过期，续期接口

```
路径：		/refresh
```

- 参数:

```json
{
	"userid": "",
	"refresh_token": ""
}
```

- 返回：

token, 和refresh_token 会被更新

```json
{
	"code": 200,    //500,数据库查询失败; 498,校验失败; 602,参数错误
	"message": "OK",
	"data":{ 
		"token": "xxx", 
		"refresh_token": "xxx", //续期token
		"expires": 300,  // 过期时间（秒）
	}
}
```

>**除注册，登录，续期接口外，其它每个接口都需要在http header增加一个自定义类型"Liteav-Sig"，用于签名。http body需要增加userid、timestamp、expires这三个公共参数。一个完整的htt请求实体如下：**

```
请求实体(json)：
{
	"userid":"xxxx",
	"timestamp": 1512890037,  //当前时间戳
	"expires":3,  //超时时间(秒)
	<各接口请求参数>
}
```
>签名方式：md5(token+md5(http.body))


## 获取用户信息
```
路径： /get_user_info
```
- 参数：  

```json
空
```
- 返回：

```json
{
	"code": 200,
	"message": "OK",
	"data": {
		"nickname":"xxx",
		"avatar":"http://xxxx",
		"sex":0 //0:male,1:female,-1:unknown
		"frontcover":"http://xxxx",     //封面图url
	}
}
```

## 上传用户信息
```
路径： /upload_user_info
```
- 参数

```json
{
	"nickname":"xxx",
	"avatar":"http://xxxx",
	"sex":0 //0:male,1:female,-1:unknown
	"frontcover":"http://xxxx",     //封面图url
}
```
- 返回：

```json
{
	"code": 200, //601,更新失败; 500,数据库操作失败
	"message": "OK"
}
```

## 拉回放列表
```
路径： /get_vod_list
```
- 参数

```json
{
	"index":0,
	"count":20
}
```
- 返回

```json
{
	"code": 200,  //602,参数错误
	"message": "OK",
	"data": {
		"list": [
			{
				"userid":"xxx",			 //用户id
				"nickname":"xxx",		 //昵称
				"avatar":"xxx",			 //头像url
				"file_id":"xxx",        //点播文件id
				"title":"xxxx",          //标题
				"like_count": 0,			 //点赞数
      			"viewer_count": 0,		 //观看数
				"frontcover":"xxx",     //封面图url
				"location":"xxx",       //地理位置
				"play_url":"xxx",       //点播播放地址
				"create_time":"2017-12-13 08:06:29",  //回放创建时间
				"hls_play_url":"xxx",   //hls播放地址
				"start_time":"2017-12-13 08:06:29"   //直播开播时间
			}
		]
	}
}
```

## 获取COS上传签名
```
路径： /get_cos_sign
```
- 参数：  

```json
空
```
- 返回：

```json
{
	"code": 200,
	"message": "OK",
	"data": {
		"signKey":"xxx",
		"keyTime":"http://xxxx",
	}
}
```


# 数据库

## 用户信息

```mysql
CREATE IF NOT EXISTS TABLE `tb_account` (
  `userid` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `sex` int(11) DEFAULT '-1',
  `avatar` varchar(254) DEFAULT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
##回放列表
```mysql
CREATE IF NOT EXISTS TABLE `tb_vod` (
  `userid` varchar(50) NOT NULL,
  `file_id` varchar(150) NOT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `play_url` varchar(255) DEFAULT NULL,
  `like_count` int(11) NOT NULL DEFAULT '0',
  `viewer_count` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hls_play_url` varchar(255) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`userid`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
##房间信息列表
```
CREATE TABLE IF NOT EXISTS `tb_room` (
  `userid` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

##数据库测试

###插入回放记录
```
INSERT INTO tape_data (userid, file_id, play_url, start_time) VALUES('1', '1', 'hhh', '2017-12-14 07:48:18');
```

##HTTP 请求测试
###注册
```
curl -d "userid=yaobo10&password=12345678" "https://roomtest.qcloud.com/lite/register"
curl -d "userid=yaobo1&password= 12345678" "http://127.0.0.1:63795/lite/register"
```
###登录
```
curl -d "userid=yaobo3&password=135678" "https://roomtest.qcloud.com/lite/login"
curl -d "userid=yaobo1&password=pwd5" "http://127.0.0.1:63795/lite/login"
```
###续期
```
curl -d "userid=2&refresh_token=cfoe04e22wv" "http://127.0.0.1:8080/liteav/refresh"
```
###直播云回调回放信息
```
curl -d "t=1513234132&sign=xxxxxx&event_type=100&file_id=2&stream_id=123_2&start_time=1513233336&video_url=http://1252463788.vod2.myqcloud.com/e12fcc4dvodgzp1252463788/4d6dc6d54564972818651655662/f0.mp4" "http://127.0.0.1:8080/liteav/tape_callback"
```
###获取回放列表
```
curl -d "index=0&count=10" "http://127.0.0.1:8080/lite/get_vod_list"
curl -d "index=0&count=10" "https://roomtest.qcloud.com/lite/get_vod_list"
```
###更新用户信息
```
curl -d "userid=2&nickname=test&avatar=xxxx&sex=0" "http://127.0.0.1:8080/lite/upload_user_info"
```
###获取用户信息
```
curl -d "userid=2" "http://127.0.0.1:8080/lite/get_user_info"
```

