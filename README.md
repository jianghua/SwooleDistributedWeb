# SwooleDistributedWeb v6.2 
版本号与swooledistributed保持一致 
升级注意：model中如果$return_result已失效，之前的事务也不支持！  
model中目前不支持recv()  
默认模板依旧是plates引擎，如果使用blade注释AppServer.php中的setTemplateEngine方法  
swoole 分布式全栈框架 web开发增强版，基于SwooleDistributed，水平有限，欢迎指正。  
内置demo，包含用户注册、登录、修改信息
## Install
You can install via composer

Autoload must specify `app` and `test`.
```
{
  "require": {
    "jianghua/swooledistributedweb":">3.6.0"
  },
 "autoload": {
    "psr-4": {
      "SwooleDistributedWeb\\app\\": "src/app",
      "app\\": "src/app",
      "test\\": "src/test"
    }
  }
}
```
Then execute the following code in the root directory (the vendor higher directory)
```
php vendor/jianghua/swooledistributedweb/src/Install.php
```
The server can be executed in the bin at the end of the installation.

SwooleDistributedWeb 文档 
https://www.gitbook.com/book/jianghua/swooledistributedweb/details
# SwooleDistributed

High performance, high concurrency, PHP asynchronous distributed framework,power by ext-swoole

Development communication QQ-group：569037921  

Simple websocket case

Chat room: https://github.com/tmtbe/SD-todpole

Live Demo: http://114.55.253.83:8081/

The official website：http://sd.youwoxing.net

Development document：http://docs.youwoxing.net

Instructional video：http://v.qq.com/boke/gplay/337c9b150064b5e5bcfe344f11a106c5_m0i000801b66cfv.html

## Install
You can install via composer

Autoload must specify `app` and `test`.
```
{
  "require": {
    "tmtbe/swooledistributed":">2.0.0"
  },
 "autoload": {
    "psr-4": {
      "app\\": "src/app",
      "test\\": "src/test"
    }
  }
}
```
Then execute the following code in the root directory (the vendor higher directory)
```
php vendor/tmtbe/swooledistributed/src/Install.php
```
The server can be executed in the bin at the end of the installation.

## Advantage

1.High performance and high concurrency, asynchronous event driven

2.HttpClient, client, Mysql, Redis connection pooling

3.Timed task system

4.Coroutine Support

5.Using object pooling mode, optimizing memory allocation and GC

6.Many asynchronous clients, such as MQTT, AMQP, etc.

7.Support cluster deployment

8.User process management

9.Support multi port, multi protocol, automatic conversion between protocols

10.Micro service management based on Consul

11.Automatic discovery of cluster nodes based on Consul

12.Support pubish-subscribe mode

## Architecture diagram

### Class inheritance structure
 ![image](https://raw.githubusercontent.com/tmtbe/SwooleDistributed/v2/screenshots/k1.png)

### Process structure
 ![image](https://raw.githubusercontent.com/tmtbe/SwooleDistributed/v2/screenshots/k2.png)
 
### Cluster structure
 ![image](https://raw.githubusercontent.com/tmtbe/SwooleDistributed/v2/screenshots/k3.png)

## web 
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/v2/screenshots/login.jpg)
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/v2/screenshots/reg.png)
![image](https://github.com/jianghua/SwooleDistributedWeb/blob/v2/screenshots/profile.jpg)