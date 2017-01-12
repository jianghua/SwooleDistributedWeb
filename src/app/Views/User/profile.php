<?php $this->layout('app::layout', ['title'=>'个人信息', 'userid'=>$userid, 'userinfo'=>$userinfo]) ?>
<link rel="stylesheet" type="text/css" href="<?=$this->url('/css/reg.css')?>" />
<?php $this->insert('app::formValidator', ['form'=>$form])?>
<script type="text/javascript">
</script>

<div class="form-style2">
	<form id="form1" name="form1" method="post" onSubmit='return submitCheck()'>
    	<?php foreach ($form['input'] as $field=>$input):?>
        <div class="line" <?php if($input['type'] == 'cascade_select')echo "id='$field'";?>>
        	<span><?php if (isset($form['label'][$field]))echo $form['label'][$field]?></span>
        	
        	<!-- 单图上传 -->
        	<?php if ($input['type'] == 'upload'):?>
        		<div class="add-photo">
        			<?=$input['upload']?>
        		</div>
        		<?=$input['tips_container']?>
        		<p><?php if (isset($form['tips'][$field]))echo $form['tips'][$field]?></p>
        		
        	<!-- 多图上传 -->
        	<?php elseif ($input['type'] == 'multi_upload'):?>
        		<?php if ($field == 'pics'):?>
        			<div class="vip-text">最多可上传5张账片。<i></i></div>
        			<div class="add-photo specialty_photo">
            			<button class="add-photo2 add-photo2-off"style="background-color: #38ade6;color:white;font-family:Microsoft Yahei,SimSun;" type="button" id="upload">上传图片</button>
            			<?=$input['upload']?>
            		</div>
        		<?php endif;?>
        		<?=$input['tips_container']?>
        		<p><?php if (isset($form['tips'][$field]))echo $form['tips'][$field]?></p>
        	
        	<!-- 级联select -->	
        	<?php elseif ($input['type'] == 'cascade_select'):?>
        		<?php if($field == 'city_select'):?>	
        		<div class="select1">
					<?=$input['province']?>
				</div>
				<div class="select1">
					<?=$input['city']?>
				</div>
				<div class="select1">
					<?=$input['county']?>
				</div>
				<?php elseif($field == 'realm_div_id'):?>	
				<div class="select1">
					<?=$input['realm']?>
				</div>
				<?php endif;?>
				<?=$input['js']?>
        		<?=$input['tips_container']?>
        		
        	<?php else:?>
        		<?=$input['input']?>
        		<?=$input['tips_container']?>
        		<p><?php if (isset($form['tips'][$field]))echo $form['tips'][$field]?></p>
        	<?php endif;?>
        	<div class="clear"></div>
        </div>
        <?php endforeach;?>
		<div class="btn-line">
			<input type="submit" id="dosubmit" name="submit" class="btn1" value="提交" />
			<div class="clear"></div>
		</div>
    </form>
</div>
		