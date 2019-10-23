<?php
// 获取方式请参考:https://www.qcloud.com/document/product/454/7953
define('API_KEY', ''); //api key，用于录制回调的鉴权

// COS配置用于头像和推流封面的上传存储
define('COSKEY_BUCKET', ''); //请替换为对象和存储服务（COS）中您所新建的bucket
define('COSKEY_BUCKET_REGION', ''); //请替换为对象和存储服务（COS）中您所新建的bucket的地域
define('COSKEY_SECRECTKEY', ''); //请替换为对象和存储服务（COS）中您所新建的secrectkey
define('COSKEY_APPID', 0); //请替换为对象和存储服务（COS）的appid
define('COSKEY_SECRECTID', ''); //请替换为对象和存储服务（COS）中您所新建的secrectid（和secrectkey配对）
define('COSKEY_EXPIRED_TIME', 30); //COS签名过期时间，单位s

define('IM_SDKAPPID', 0); // 即时通信 sdkappid
define('IM_SECRETKEY', ''); // 即时通信 密钥
