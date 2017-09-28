<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=$this->e($title)?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="keywords" content="">
	<meta name="description" content="">
	<link rel="stylesheet" type="text/css" href="<?=$this->url('/css/globle.css')?>" />
	<link rel="stylesheet" type="text/css" href="<?=$this->url('/css/css.css')?>" />

	<script type="text/javascript" src="<?=$this->url('/js/jquery.min.js')?>"></script>
	<script type="text/javascript">
		var base_url = '<?=$this->get_www()?>';
	</script>
</head>
<body>
	<div id="top" class="wrapper">
		<div class="w1000">
			<div class="logo"></div>
			<?php if (!empty($userid)):?>
				<span>你好，<?=$userinfo['username']?></span>
				<a href="<?=url('/user/logout')?>" class="right-btn right-color2">退出</a>
			<?php else:?>
				<a href="<?=url('/user')?>" class="right-btn right-color1">登录</a>
			<?php endif;?>
			<div class="clear"></div>
		</div>
	</div>
	<div class="blank55"></div>
	<div class="w1000 content">
		<?=$this->section('content')?>
	</div>
	<div class="blank50"></div>
	<?php $this->insert('app::footer')?>
</body>
</html>