<div class="productTypes form">
<?php echo $this->Form->create('ProductType'); ?>
	<fieldset>
		<legend><?php echo __('Add Product Type'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('product_category_id',array('label'=>__('Product Category')));
		echo $this->Form->input('accounting_code_id',array('default'=>0,'empty'=>array('0'=>__('Select Accounting Code'))));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Product Types'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_product_index_permission) { 
			echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		}
		if ($bool_product_add_permission) { 
			echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
		} 
	echo "</ul>";
?>	
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
