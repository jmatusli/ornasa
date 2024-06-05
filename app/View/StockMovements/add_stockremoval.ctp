<div class="stockMovements form stockRemovals">
<?php echo $this->Form->create('StockMovement'); ?>
	<fieldset>
		<legend><?php echo __('Add Stock Removal'); ?></legend>
	<?php
		echo $this->Form->input('movement_date');
		echo $this->Form->input('product_type_id');
		echo $this->Form->input('production_result_code_id',array('label'=>__('Result Code')));
		echo $this->Form->input('product_type_quantity');
		echo $this->Form->input('product_type_unit_price',array('label'=>__('Product Type Unit Price')));
		
		// echo $this->Form->input('name');
		// echo $this->Form->input('description');
		// echo $this->Form->input('order_id');
		// echo $this->Form->input('product_type_quantity');
		// echo $this->Form->input('product_type_unit_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<?php echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, true); ?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<!--li><?php // echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<!--li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li-->
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<!--li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
