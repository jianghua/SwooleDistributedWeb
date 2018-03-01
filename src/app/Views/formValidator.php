<?php 
/**
 * 根据form生成固定格式的表单js验证
 */
?>
<script type="text/javascript" src="<?=$this->url('/js/formValidator.js')?>"></script>
<script type="text/javascript">
<!--
$(function() {
	$.formValidator.initConfig({
		formID: "form1",
		theme: "ArrowSolidBox",
		submitOnce: true,
		submitAfterAjaxPrompt: '有数据正在异步验证，请稍等...',
		onShowClass: "",
		onFocusClass: "",
		onCorrectClass: "",
		/* onSuccess: function() {$("#modifyPass").attr("disabled","disabled");$("#modifyPass").addClass("disable")}, */
		wideWord: false	//一个汉字当做1个长
	});
	

	<?php foreach ($form['js'] as $field)echo $field;?>
});
//-->
</script>