<?php 
/**
 * 根据form生成固定格式的表单项
 */
?>
<?php foreach ($form['input'] as $field=>$input):?>
<div class="line">
	<span><?php if (isset($form['label'][$field]))echo $form['label'][$field]?></span>
	<?php if ($field != 'checkcode'){?>
		<?=$input['input']?>
		<?=$input['tips_container']?>
		<p><?php if (isset($form['tips'][$field]))echo $form['tips'][$field]?></p>
	<?php }else{?>
		<?=$input['input']?>
		<div class="code"><?=$input['img']?></div>
		<a href="javascript:;" onclick="$('#<?=$field?>img').click()" class="code-change">换一张</a>
		<?=$input['tips_container']?>
	<?php }?>
	<div class="clear"></div>
</div>
<?php endforeach;?>