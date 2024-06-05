<div class="salesOrderProductStatuses form">
<?php echo $this->Form->create('SalesOrderProductStatus'); ?>
	<fieldset>
		<legend><?php echo __('Add Sales Order Product Status'); ?></legend>
	<?php
		echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Sales Order Product Statuses'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Sales Order Products'), array('controller' => 'sales_order_products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Sales Order Product'), array('controller' => 'sales_order_products', 'action' => 'add')); ?> </li>
	</ul>
</div>
