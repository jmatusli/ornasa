<div class="deletions form">
<?php echo $this->Form->create('Deletion'); ?>
	<fieldset>
		<legend><?php echo __('Add Deletion'); ?></legend>
	<?php
		echo $this->Form->input('reference');
		echo $this->Form->input('type');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Deletions'), array('action' => 'index')); ?></li>
	</ul>
</div>
