<div class="cashReceiptInvoices form">
<?php echo $this->Form->create('CashReceiptInvoice'); ?>
	<fieldset>
		<legend><?php echo __('Edit Cash Receipt Invoice'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('cash_receipt_id');
		echo $this->Form->input('invoice_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('CashReceiptInvoice.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('CashReceiptInvoice.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipt Invoices'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>