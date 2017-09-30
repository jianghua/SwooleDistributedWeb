1. php 参考参数
./configure --prefix=/usr/local/php7.1 --with-config-file-path=/usr/local/php7.1/etc/ --with-mysql=mysqlnd --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd  --with-iconv --with-zlib --with-curl --with-openssl --enable-mbstring --with-gd --enable-gd-native-ttf --enable-sockets --enable-pcntl --enable-xml --with-jpeg-dir --with-freetype-dir

2. nginx处理www目录的静态文件，nginx配置如下
server{
    listen       80;
    server_name  localhost;
    index index.html;
    root  /data0/htdocs/www;
    location /index.html {
        proxy_pass http://127.0.0.1:8081;
    }
    location / {
        if (!-e $request_filename){
            proxy_pass http://127.0.0.1:8081;
        }
    }
    include auti.conf;
    location ~ .*\.(js|css|swf|jfif|jpg|gif|ico|jpeg|bmp|png)${
      expires      30d;
    }
    access_log  /var/log/nginx/access.log  main;
}
  
3. 执行db.sql，创建对应的表
4. 修改src/config目录中的配置
5. php bin/start_swoole_server.php start启动服务