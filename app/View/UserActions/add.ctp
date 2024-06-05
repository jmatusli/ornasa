<div class="userActions form">
<?php echo $this->Form->create('UserAction'); ?>
	<fieldset>
		<legend><?php echo __('Add User Action'); ?></legend>
	<?php
		echo $this->Form->input('user_id');
		echo $this->Form->input('controller_name');
		echo $this->Form->input('action_name');
		echo $this->Form->input('item_number');
		echo $this->Form->input('action_datetime');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List User Actions'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
