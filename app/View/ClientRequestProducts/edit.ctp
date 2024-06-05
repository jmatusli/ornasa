<div class="requestProducts form">
<?php echo $this->Form->create('RequestProduct'); ?>
	<fieldset>
		<legend><?php echo __('Edit Request Product'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('request_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_unit_price');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('product_total_price');
		echo $this->Form->input('production_result_code_id');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('RequestProduct.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('RequestProduct.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Request Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Requests'), array('controller' => 'requests', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Request'), array('controller' => 'requests', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Result Codes'), array('controller' => 'production_result_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Result Code'), array('controller' => 'production_result_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>
