<div class="purchaseOrderProducts form">
<?php echo $this->Form->create('PurchaseOrderProduct'); ?>
	<fieldset>
		<legend><?php echo __('Edit Purchase Order Product'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('purchase_order_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_description');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('product_unit_cost');
		echo $this->Form->input('product_total_cost');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('PurchaseOrderProduct.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('PurchaseOrderProduct.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Purchase Order Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Purchase Orders'), array('controller' => 'purchase_orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Purchase Order'), array('controller' => 'purchase_orders', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
