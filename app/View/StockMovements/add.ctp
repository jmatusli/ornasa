<div class="stockMovements form">
<?php echo $this->Form->create('StockMovement'); ?>
	<fieldset>
		<legend><?php echo __('Add Stock Movement'); ?></legend>
	<?php
		echo $this->Form->input('movement_date');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('order_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('product_price');
		echo $this->Form->input('product_type_quantity');
		echo $this->Form->input('product_type_unit_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Stock Movements'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
