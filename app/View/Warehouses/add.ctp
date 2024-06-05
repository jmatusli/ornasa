<div class="warehouses form">
<?php echo $this->Form->create('Warehouse'); ?>
	<fieldset>
		<legend><?php echo __('Add Warehouse'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
	
<?php echo "<h3>".__('Actions')."</h3>"; 
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Warehouses'), array('action' => 'index'))."</li>";
	echo "</ul>";
?>	
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
