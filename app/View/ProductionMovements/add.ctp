<div class="productionMovements form">
<?php echo $this->Form->create('ProductionMovement'); ?>
	<fieldset>
		<legend><?php echo __('Add Production Movement'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('stock_item_id');
		echo $this->Form->input('production_run_id');
		echo $this->Form->input('product_type_id');
		echo $this->Form->input('product_type_quantity');
		echo $this->Form->input('product_type_unit_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Production Movements'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Runs'), array('controller' => 'productionRuns', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Run'), array('controller' => 'productionRuns', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'productTypes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'productTypes', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
