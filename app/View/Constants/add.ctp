<div class="constants form">
<?php echo $this->Form->create('Constant'); ?>
	<fieldset>
		<legend><?php echo __('Add Constant'); ?></legend>
	<?php
		echo $this->Form->input('constant');
		echo $this->Form->input('description');
		echo $this->Form->input('value',['class'=>'keepcase']);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Constants'), array('action' => 'index')); ?></li>
	</ul>
</div>
