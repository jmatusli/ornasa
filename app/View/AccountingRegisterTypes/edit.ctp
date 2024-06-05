<div class="accountingRegisterTypes form">
<?php echo $this->Form->create('AccountingRegisterType'); ?>
	<fieldset>
		<legend><?php echo __('Edit Accounting Register Type'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('abbreviation');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingRegisterType.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingRegisterType.id')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Accounting Register Types'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_accountingregister_index_permission){
			echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		}
		if ($bool_accountingregister_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
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