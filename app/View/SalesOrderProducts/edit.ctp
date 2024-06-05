<div class="salesOrderProducts form">
<?php echo $this->Form->create('SalesOrderProduct'); ?>
	<fieldset>
		<legend><?php echo __('Edit Sales Order Product'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('sales_order_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('sales_order_product_status_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('SalesOrderProduct.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('SalesOrderProduct.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Sales Order Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Sales Orders'), array('controller' => 'sales_orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Sales Order'), array('controller' => 'sales_orders', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Sales Order Product Statuses'), array('controller' => 'sales_order_product_statuses', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Sales Order Product Status'), array('controller' => 'sales_order_product_statuses', 'action' => 'add')); ?> </li>
	</ul>
</div>
