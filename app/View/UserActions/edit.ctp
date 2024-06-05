<div class="userActions form">
<?php echo $this->Form->create('UserAction'); ?>
	<fieldset>
		<legend><?php echo __('Edit User Action'); ?></legend>
	<?php
		echo $this->Form->input('id');
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

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('UserAction.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('UserAction.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List User Actions'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
