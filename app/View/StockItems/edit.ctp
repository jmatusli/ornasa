<div class="stockItems form">
<?php echo $this->Form->create('StockItem'); ?>
	<fieldset>
		<legend><?php echo __('Edit Stock Item'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		// echo $this->Form->input('description');
		// echo $this->Form->input('stock_movement_id');
		echo $this->Form->input('product_type_id');
		echo $this->Form->input('unit_price');
		echo $this->Form->input('original_quantity');
		echo $this->Form->input('remaining_quantity');
		// echo $this->Form->input('production_result_code_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<!--li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('StockItem.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('StockItem.id'))); ?></li-->
		<li><?php echo $this->Html->link(__('List Stock Items'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'productTypes', 'action' => 'index')); ?> </li>
		<?php if($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'productTypes', 'action' => 'add')); ?> </li>
		<?php } ?>
		<!--li><?php echo $this->Html->link(__('List Production Result Codes'), array('controller' => 'productionResultCodes', 'action' => 'index')); ?> </li-->
		<?php if($userrole!=ROLE_FOREMAN){ ?>
		<!--li><?php // echo $this->Html->link(__('New Production Result Code'), array('controller' => 'productionResultCodes', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Production Movements'), array('controller' => 'productionMovements', 'action' => 'index')); ?> </li>
		<!--li><?php echo $this->Html->link(__('New Production Movement'), array('controller' => 'productionMovements', 'action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stockMovements', 'action' => 'index')); ?> </li>
		<!--li><?php echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stockMovements', 'action' => 'add')); ?> </li-->
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>