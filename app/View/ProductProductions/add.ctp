<div class="productProductions form">
<?php echo $this->Form->create('ProductProduction'); ?>
	<fieldset>
		<legend><?php echo __('Add Product Production'); ?></legend>
	<?php
		echo $this->Form->input('application_date');
		echo $this->Form->input('product_id');
		echo $this->Form->input('acceptable_production');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Product Productions'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
