# 小直播解决方案服务端

为观众端提供直播间列表和回放列表，以及账号注册、登录、个人信息维护等功能。


### 开通直播服务

#### step 1.1  开通视频直播服务

登录腾讯云官网，进入 [云直播管理控制台](https://console.cloud.tencent.com/live)，如果服务还没有开通，单击【申请开通】。

#### step 1.2  绑定直播域名
按照相关政策要求，您需要添加自有的**已备案域名**才能使用腾讯云的 CDN 播放功能，请参见 [域名管理](https://cloud.tencent.com/document/product/267/20381) 和 [CNAME 配置](https://cloud.tencent.com/document/product/267/30560) 进行配置。

<span id="get_lvb_license"></span>
#### step 1.3  获取 SDK 的测试 License
“小直播”是基于腾讯云 LiteAVSDK 实现推流和播放功能的，但您需要提前绑定 License 才能使用其提供的推流功能，您可以先按照如下步骤获取“小直播”所需要的测试 License：

1. 在  [云直播管理控制台](https://console.cloud.tencent.com/live) 中点击进入 [移动直播 License](https://console.cloud.tencent.com/live/license) 页面。
2. 填写【Package Name】为 Android 的包名，【Bundle Id】为 iOS 的 Bundle Id。
3. 单击【免费创建】，创建成功后，页面会显示生成的 License 信息。请记录 Key 和 LicenseUrl，便于在 SDK 初始化时使用。

![](https://main.qcloudimg.com/raw/b654ecd69f30ea7c5503bc15819dc01e.png)

<span id="get_im_config"></span>
#### step 1.4  在应用管理中添加一个新的应用
“小直播”是基于腾讯云 TIMSDK 实现文字聊天室和弹幕消息等互动功能的，但您需要先创建一个 IM 应用才能使用其提供的聊天室功能，您可以先按照如下步骤获取“小直播”所需要的 SDKAppID 和 SecretKey 两个重要信息：

1. 进入【云直播控制台】>【直播SDK】>【[应用管理](https://console.cloud.tencent.com/live/license/appmanage)】，单击【创建应用】，待应用创建完成后，记录其 SDKAppID 信息。
2. 单击目标应用的 SDKAppID，进入应用详情页面。
3. 选择【应用管理】页签，单击【编辑】，输入一个管理员名称（例如“admin”），单击【添加】按钮，之后再单击【确定】按钮。
4. 单击【查看密钥】，复制保存密钥信息 SecretKey。

![](https://main.qcloudimg.com/raw/2b5a8e84c9f4a79e7d98e1e2c928f113.jpg)

### 开通对象存储服务
<h4 id="Create_COS">step 2.1 申请开通对象存储服务</h4>

进入 [对象存储服务控制台](https://console.cloud.tencent.com/cos5)，如果还没有服务，直接单击【创建存储桶】即可。

<h4 id="Get_COS_Info">step 2.2 创建存储桶并获取基本信息</h4>

1. 填写名称，选择所属地域，设置访问权限为【公有读私有写】。单击【确定】创建存储桶。
![](https://main.qcloudimg.com/raw/cd92d21f4473fa86125719ca1033e65b/new_cos_dialog.jpg)

2. 单击【基础配置】，记录`存储空间名称`、`所属地域`，分别对应于后文 [修改云服务器配置信息](#STEP4_1) 中的 `COSKEY_BUCKET` 和 `COSKEY_BUCKET_REGION`。
![](https://main.qcloudimg.com/raw/fb059908dd4b13ffbf82814fbca4f020.png)

<h4 id="Get_COS_SEC">step 2.3  获取密钥信息</h4>

进入[【对象存储控制台】>【密钥管理】>【云API密钥】](https://console.cloud.tencent.com/cam/capi) 获取 `APPID`、`SecretId` 和 `SecretKey`，分别对应下文 [修改云服务器配置信息](#STEP4_1) 中的 `COSKEY_APPID`、`COSKEY_SECRETID` 和 `COSKEY_SECRETKEY`。
![](https://main.qcloudimg.com/raw/d93dccb3376fd73e6206786013cf1e73.jpg)


### 腾讯云CVM镜像部署

<h4 id="Create_CVM">step 3.1 创建虚拟主机</h4>

进入 [CVM 控制台](https://console.cloud.tencent.com/cvm) ，单击【新建】开始创建云服务器。

选择【自定义配置】选择符合您需求的虚拟主机，在镜像提供栏选择【镜像市场】，并单击【从镜像市场选择】进服务市场选取镜像。选中图中的【小直播镜像】，您可以直接在搜索栏中搜索。
![](https://main.qcloudimg.com/raw/80b9e0090a65b70512a2a1c2c16c98a0.png)

<h4 id="Config_CVM">step 3.2 设置云服务器</h4>
配置云服务器的访问密码，设置安全组。

>!安全组一定要放开80、443服务端口
>请**妥善保管 root 密码**，该密码将用于后续 [修改云服务器配置信息](#STEP4_1) 操作。

![](https://main.qcloudimg.com/raw/e24a07a47f4f7d2291889467c997eb8b.png)

<h4 id="Get_CVM_Info">step 3.3 查看云服务器信息</h4>

付款后生成云服务器。请记录外网 IP 地址，将用于后续 [配置录制回调](#STEP3_2) 和 [终端集成](#STEP5) 操作。
![](https://main.qcloudimg.com/raw/a3de2d654e9b73c7ce7b2457077fa6ad.png)

<span id="STEP3_4"></span>
#### step 3.4 准备配置文件
将以下内容粘贴到文本编辑器（如记事本），按照下方脚本中的注释填写各项内容，其中`xxxx`的部分在本文前半部分均能找到对应的值。

```bash
#!/bin/bash

echo "<?php
define('API_KEY', 'xxxxxxxx'); //api key，用于录制回调的鉴权。请替换为配置录制回调中记录的回调密钥

// COS配置用于头像和推流封面的上传存储
define('COSKEY_BUCKET', 'xxxxxxxx'); //请替换为创建存储桶并获取基本信息中记录的存储空间名称
define('COSKEY_BUCKET_REGION', 'xxxxxxxx'); //请请替换为创建存储桶并获取基本信息中记录的所属地域
define('COSKEY_SECRECTID', 'xxxxxxxx'); //请替换为获取密钥信息中记录的SecretId（和SecretKey配对）
define('COSKEY_SECRECTKEY', 'xxxxxxxx'); //请替换为获取密钥信息中记录的SecretKey
define('COSKEY_APPID', 12345678); //请替换为获取密钥信息中记录的APPID
define('COSKEY_EXPIRED_TIME', 30); //COS签名过期时间，单位s

define('IM_SDKAPPID', 12345678); // 即时通信 sdkappid
define('IM_SECRETKEY', 'xxxxxxxx'); // 即时通信 密钥
" > /data/live_demo_service/conf/OutDefine.php;
```

#### step 3.5  登录云服务器

1. 进入 [CVM 控制台](https://console.cloud.tencent.com/cvm) ，单击目标主机所在行【登录】。
![](https://main.qcloudimg.com/raw/f1b5c3f646e7db26f9b595642e8efd17.png)

2. 选择【标准登录方式】区域的【立即登录】，输入配置主机时设置的密码，单击【确认】。

#### step 3.6  修改配置
登录成功后会进入一个网页版的控制台界面，您只需要直接将 [准备配置文件](#STEP3_4) 中准备好的文本粘贴过来，按 `Enter` 键确认即可。
![](https://main.qcloudimg.com/raw/1f6dfb3221b6d262e3ada6aa0a0305bb.png)

**至此业务后台部署完成**


###  终端集成及回调设置

终端集成主要是小直播源码集成，主要是以下简单几步：

####  小直播源码下载
小直播 App 的源码位于 Github/Github 仓库中，可以在`Android/XiaoZhiBo`和`iOS/XiaoZhiBo`分别获取到 Android 和 iOS 的源码。

<table>
   <tr>
      <th width="0px" style="text-align:center">ZIP 包</td>
      <th width="0px"  style="text-align:center">Github</td>
      <th width="0px" style="text-align:center">Gitee</td>
   </tr>
   <tr>
      <td style="text-align:center"><a onclick=MtaH5.clickStat("mlvb_xzb_zip_download") href="https://github-1252463788.cos.ap-shanghai.myqcloud.com/mlvbsdk/MLVBSDK.zip">DOWNLOAD</a></td>
      <td style="text-align:center"><a href="https://github.com/tencentyun/MLVBSDK/">Github</a></td>
      <td style="text-align:center"><a href="https://gitee.com/cloudtencent/MLVBSDK">Gitee</a></td>
   </tr>
</table>

####  替换“小直播” 中的 License 配置

我们在 step 1.3 中拿到的 LiteAVSDK 的测试版 License 在这一步可以发挥作用了，参照如下的说明替换源代码中的两行字符串（License URL 和 Key） 即可：

- **iOS 版替换方案**：
打开`iOS/XiaoZhiBo/XiaoZhiBoApp/Classes/App/`目录下的 `TCGlobalConfig.h` 文件，将文件里的 `LICENCE_URL` 和 `LICENCE_KEY` 分别替换为[ step 1.3：获取 SDK 的测试 License ](#get_lvb_license)中记录的 License URL 和 Key。

- **Android 版替换方案**：
 打开`Android/XiaoZhiBo/app/src/main/java/com/tencent/qcloud/xiaozhibo`目录下的 `TCGlobalConfig.java` 文件，将文件里的 `LICENCE_URL` 和 `LICENCE_KEY` 分别替换为[ step 1.3：获取 SDK 的测试 License ](#get_lvb_license)中记录的 License URL 和 Key。

 ####   替换小直播后台服务器地址
小直播后台服务的地址为`http://云服务器公网 IP 地址`。例如`http://134.175.197.138`：
- iOS：
打开`iOS/XiaoZhiBo/XiaoZhiBoApp/Classes/App/`目录下的 **TCGlobalConfig.h** 文件，将文件里的`kHttpServerAddr`改为您的小直播后台服务的地址。
- Android：
打开`Android/XiaoZhiBo/app/src/main/java/com/tencent/qcloud/xiaozhibo`目录下的 **TCGlobalConfig.java** 文件，将文件里的`APP_SVR_URL`改为您的小直播后台服务的地址。

`注意：如果服务器没有配置证书，这里的云主机服务器地址需要用http，而不能用https。`

###  在直播控制台配置录制回调地址

小直播 App 中的“精彩回放”功能依托于云直播的录制功能。
#### step 4.1 配制录制参数
1. 在云直播菜单栏内选择【功能模板】>【[录制配置](https://console.cloud.tencent.com/live/config/record)】，单击 "+" 进行设置。
![](https://main.qcloudimg.com/raw/bdf497e5fa0f583aed7335b784ced2d0.png)

2. 设置基本信息，填写【模板名称】，并选择录制文件类型（HLS、MP4 或者 FLV），单击【保存】。

<span id="STEP4_2"></span>
#### step 4.2 配置录制回调
1. 在云直播菜单栏内选择【功能模板】>【[回调配置](https://console.cloud.tencent.com/live/config/callback)】，单击 "+" 创建回调模板。
![](https://main.qcloudimg.com/raw/ccad925aa508503eb8f51c0c8b92735d.png)

2. 填写并记录【回调密钥】，填写【录制回调】为 `http://您的云服务器公网 IP 地址/callback/tape_callback.php`，单击【保存】。
![](https://main.qcloudimg.com/raw/d31e9dcabf17fd394b56207c0d9d557a.png)

#### step 4.3 应用配置到域名
1. 进入云直播控制台 [域名管理](https://console.cloud.tencent.com/live/domainmanage)，单击推流域名后的【管理】。
![](https://main.qcloudimg.com/raw/dd8895666c8df59802703c23d3a611a8.png)

2. 单击【模板配置】，分别将【回调配置】和【录制配置】设置为上述步骤中新建的模板。
![](https://main.qcloudimg.com/raw/aab97213e80dfc428f7c201ec89f450b/domain_cfg.png)



## 后台源码目录结构说明
```
xiaozhibo_server_php
├── callback
│   └── tape_callback.php        //录制回调的处理
├── common
│   ├── AbstractInterface.php    //所有接口类的抽象父类，输入输出的统一处理
│   ├── Common.php						
│   ├── ConfFactory.php					 //用于读取数据库配置文件
│   ├── ErrorCode.php					   //错误码定义
│   ├── GlobalDefine.php				 //一些全局定义
│   ├── GlobalFunctions.php			 //通用函数，一些无用的函数清理掉
│   ├── Ini.php							     //ini配置文件读取
│   ├── MiniLog.php						   //日志
│   ├── Param.php						     //输入参数检查
│   ├── ParamChecker.php				 //输入参数检查
│   └── TLSSigAPIv2.php				   //UserSig计算
├── conf
│   ├── OutDefine.php					   //腾讯云账号信息配置文件
│   ├── cdn.inc.ini              //总配置文件
│   └── cdn.route.ini            //数据库配置文件
├── dao                          //数据库相关
│   ├── dao_base
│   │   └── dao.class.php				 //数据库对外的类
│   ├── dao_live
│   │   └── dao_live.class.php	 //数据库对外的实现类，各种sql语句在这里
│   └── redis_cache.php          //redis本地缓存
├── fastcgi.conf                 //nginx配置文件
├── index.php
├── interface
│   ├── get_cos_sign.php			   //上传头像、封面图片用
│   ├── get_user_info.php			   //获取用户信息，用于漫游个人信息	
│   ├── get_vod_list.php			   //获取回放列表
│   ├── login.php					       //登录，获取token
│   ├── refresh.php					     //刷新token
│   ├── register.php				     //注册
│   └── upload_user_info.php		 //更新个人信息
├── interface.php					       //接口处理入口
└── liteav_demo.nginx				     //nginx配置文件
```
