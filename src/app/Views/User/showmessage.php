<?php $this->layout('app::layout', ['title'=>'提示信息']) ?>
<script type="text/javascript">
<!--
function hide_dialog(){
	$('#dialog_div').hide();
	<?php if ($location == 'back'):?>
		window.location = history.back();
	<?php else :?>
		window.location = '<?=$location?>';
	<?php endif;?>
}
//-->
</script>
<div id="dialog_div" class="drop-dialog" style="<?=$dialog_message?'display:block;':'display:none;'?>">
	<div class="box">
		<p><?=$dialog_message?></p>
		<div class="btn-line">
			<a href="javascript:;" onclick="hide_dialog()" class="btn1">确定</a>
		</div>
	</div>
</div>