1. 控制器中，如果不继续往下执行，需要写return
2. 简单的key=>value缓存，建议使用cache，扩展性更好些
3. 如果redis宕机，可修改config/redis.php中enable为false，并把cache.php/session.php改为其他保存方式
4. 任何地方不能写exit
5. 修改/src/app/Helpers/Funcs/Common.php要重启服务
6. 编程须知https://wiki.swoole.com/wiki/page/851.html
