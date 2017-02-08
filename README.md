# SwooleDistributedWeb v0.1.3
swooledistributed 更新为1.7.5  
去掉模板中的asset，命名规范化  
修复formValidator的bug  
swooledistributed 离线文档更新(2017-01-13 16:27)
# SwooleDistributedWeb v0.1.2
多图上传bug修复   
swooledistributed 更新为1.7.4
# SwooleDistributedWeb v0.1.1
SwooleDistributed v1.6升级为v1.7，增加单元测试模块  
bug修复
# SwooleDistributedWeb v0.1.0
swoole 分布式全栈框架 web开发增强版，基于SwooleDistributed，水平有限，欢迎指正。  
内置demo，包含用户注册、登录、修改信息

# 文档
1. SwooleDistributedWeb  
 https://www.gitbook.com/book/jianghua/swooledistributedweb/details
2. SwooleDistributed 文档  
 http://182.92.224.125/  
 https://www.gitbook.com/book/tmtbe/swooledistributed/details
3. Swoole 文档  
 http://wiki.swoole.com/

# 特性  
1. 优秀的框架（MVC）设计,丰富的支持极大加快开发速度  
2. 通过开启不同端口同时支持TCP和HTTP，WebSocket，同一逻辑处理不同协议  
3. 全异步支持，无需手动处理连接池，异步redis,异步mysql，mysql语法构建器，支持异步mysql事务,异步httpclient，效率出众  
4. 协程模式全支持，异步redis，异步mysql，异步httpclient，异步task，全部都提供了协程模式，业务代码摆脱处处回调的困扰（不是swoole2.0，php7同样支持）  
5. 支持协程嵌套，支持协程内异常处理（和正常逻辑用法一样）  
6. 额外提供了protobuf完整RPC实例，轻松使用protobuf  
7. 天然分布式的支持，一台机器不够零配置，零代码修改完成高效分布式系统的搭建  
  
以上都是流弊SwooleDistributed的特性，SwooleDistributedWeb特性如下：  
1. 封装常用的web功能，支持cookie、session、cache、form、validate、filter、验证码、文件上传、分页等  
2. 自动生成表单，自动生成表单前台验证js，后台自动验证表单；只需在model中配置即可使用  
3. 数据库操作封装增删改查，更加方便编写  
4. session/cache 支持redis（默认）、mysql、文件（分布式不建议使用）  
5. 使用plates模板引擎  
6. 可businessConfig.php中指定域名、默认控制器、默认方法、文件上传目录、上传大小；内置uc、ckeditor  

# 安装须知
1. php 7.0  5.6的用户需要自己修改源码，将php7的部分语法重写。强烈推荐php7.X搭配最新的swoole1.X系列。
2. 强烈建议使用最新版的swoole，请通过github下载编译。最新版修复了很多php7下的bug  
3. 需要redis支持，安装hredis扩展  swoole编译时选择异步redis选项  
4. 需要composer支持，安装composer，运行composer install安装依赖  
5. 如需集群自行搭建LVS  

# 运行
php start_swoole_server.php start  

#安装
可以参考  安装.md、注意事项.md

#离线文档
docs目录

#截图
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/master/screenshots/login.jpg)
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/master/screenshots/reg.png)
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/master/screenshots/profile.jpg)