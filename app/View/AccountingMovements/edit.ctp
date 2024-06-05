<div class="accountingMovements form">
<?php echo $this->Form->create('AccountingMovement'); ?>
	<fieldset>
		<legend><?php echo __('Edit Accounting Movement'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('accounting_register_id');
		echo $this->Form->input('accounting_code_id');
		echo $this->Form->input('amount');
		echo $this->Form->input('currency_id');
		echo $this->Form->input('bool_debit');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
<?php echo $this->Html->Link('Cancelar',array('action'=>'edit',$id),array( 'class' => 'btn btn-primary cancel')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingMovement.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingMovement.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Movements'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>