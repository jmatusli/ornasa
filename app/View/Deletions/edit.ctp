<div class="deletions form">
<?php echo $this->Form->create('Deletion'); ?>
	<fieldset>
		<legend><?php echo __('Edit Deletion'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('reference');
		echo $this->Form->input('type');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Deletion.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Deletion.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Deletions'), array('action' => 'index')); ?></li>
	</ul>
</div>
