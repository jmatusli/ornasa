<div class="paymentDeposits form">
<?php echo $this->Form->create('PaymentDeposit'); ?>
	<fieldset>
		<legend><?php echo __('Edit Payment Deposit'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('cash_receipt_id');
		echo $this->Form->input('invoice_id');
		echo $this->Form->input('transfer_id');
		echo $this->Form->input('amount');
		echo $this->Form->input('bool_deposit_complete');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('PaymentDeposit.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('PaymentDeposit.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Payment Deposits'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Transfers'), array('controller' => 'transfers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Transfer'), array('controller' => 'transfers', 'action' => 'add')); ?> </li>
	</ul>
</div>
