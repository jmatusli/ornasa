<div class="stockMovements form">
<?php echo $this->Form->create('StockMovement'); ?>
	<fieldset>
		<legend><?php echo __('Edit Stock Movement'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('movement_date');
		
		
		echo $this->Form->input('order_id',array('hidden'=>'hidden','label'=>false));
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_quantity',array('label'=>__('Number of Boxes')));
		echo $this->Form->input('product_price',array('label'=>__('Price per Box')));
		echo $this->Form->input('name',array('label'=>__('Lot Identifier')));
		// echo $this->Form->input('description');
		//echo $this->Form->input('product_type_quantity');
		//echo $this->Form->input('product_type_unit_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<!--li><?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('StockMovement.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('StockMovement.id'))); ?></li-->
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<!-- li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Purchases'), array('controller' => 'orders', 'action' => 'indexPurchases')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Purchase'), array('controller' => 'orders', 'action' => 'addPurchase')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Sales'), array('controller' => 'orders', 'action' => 'indexSales')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'addSale')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>