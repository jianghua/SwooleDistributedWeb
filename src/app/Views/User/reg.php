<?php $this->layout('app::layout', ['title'=>'注册']) ?>
<?php $this->insert('app::formValidator', ['form'=>$form])?>
<script type="text/javascript">
</script>
<div class="form-style1">
	<form id="form1" name="form1" method="post">
    	<?php $this->insert('app::formCreator', ['form'=>$form])?>
		<div class="btn-line">
			<input type="submit" id="dosubmit" name="submit" class="btn1" value="注册"/>
			<div class="clear"></div>
		</div>
    </form>
</div>
		