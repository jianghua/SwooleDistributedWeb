<!DOCTYPE HTML>
<html>

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>SwooleDistributedWeb</title>
		<meta http-equiv="X-UA-Compatible" content="IE=10" />
		<meta name="keywords" content="">
		<meta name="description" content="">
		<link rel="stylesheet" type="text/css" href="<?=$this->url('css/globle.css')?>" />
		<link rel="stylesheet" type="text/css" href="<?=$this->url('css/css.css')?>" />
		<script type="text/javascript" src="<?=$this->url('js/jquery.min.js')?>"></script>
		<script type="text/javascript" src="<?=$this->url('js/common.js')?>"></script>
		<script type="text/javascript">
			function login(){
				var un = $("#username").val();
				var pw = $("#userpass").val();
				if(un == ""){
					$("#agress").html("请输入登录名");
				}else if(pw == ""){
					$("#agress").html("请输入登录密码");
				}else{
					$.getJSON('<?=$this->url('user/ajaxLogin')?>?r='+Math.random(), {username:un, password:pw}, function(result){
						if (result.msg){
							$("#agress").html(result.msg);
						}
						if (result.url){
							location.href=result.url;
						}
					});
				}
			}
			function placeholderSupport() {
				return 'placeholder' in document.createElement('input');
			}
			document.onkeydown = function (event) {
                var e = event || window.event || arguments.callee.caller.arguments[0];
                if (e && e.keyCode == 13) {
                    login();
                }
            };
		</script>
	</head>

	<body>
		<div id="layout1">
			<div class="w1000">
				
				<div id="login">
					<div class="bg"></div>
					<div class="con">
						<h3>账户登录</h3>
						<div id="agress" style="color: red;">
							
						</div>
						<form>
							<input type="text" id="username" class="inp name" placeholder="登录名" />
							<input type="password" id="userpass" class="inp pwd" placeholder="请输入登录密码" />
							<div class="line3">
								<div class="check"><a href="<?=url('user/reg')?>" class="forget">注册账号</a></div>
								<a href="<?=url('user/getPass')?>" class="forget">忘记密码？</a>
								<div class="clear"></div>
							</div>
							<input type="button" class="sub" value="登 录" onclick="login()"/>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="blank50"></div>

		<?php $this->insert('app::footer')?>
	</body>
</html>