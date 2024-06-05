<div class="userLogs form">
<?php echo $this->Form->create('UserLog'); ?>
	<fieldset>
		<legend><?php echo __('Edit User Log'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('username');
		echo $this->Form->input('event');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('UserLog.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('UserLog.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List User Logs'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<?php if ($userrole==ROLE_ADMIN) { ?>	
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<?php } ?>	
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>