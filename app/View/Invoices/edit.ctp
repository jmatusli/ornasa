<div class="invoices form">
<?php echo $this->Form->create('Invoice'); ?>
	<fieldset>
		<legend><?php echo __('Edit Invoice'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('order_id');
		echo $this->Form->input('invoice_code');
		echo $this->Form->input('invoice_date');
		echo $this->Form->input('bool_annulled');
		echo $this->Form->input('client_id');
		echo $this->Form->input('currency_id');
		echo $this->Form->input('bool_credit');
		echo $this->Form->input('due_date');
		echo $this->Form->input('cashbox_accounting_code_id');
		echo $this->Form->input('bool_retention');
		echo $this->Form->input('retention_amount');
		echo $this->Form->input('retention_number');
		echo $this->Form->input('bool_IVA');
		echo $this->Form->input('sub_total_price');
		echo $this->Form->input('IVA_price');
		echo $this->Form->input('total_price');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Invoice.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Invoice.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Third Parties'), array('controller' => 'third_parties', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cashbox Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>