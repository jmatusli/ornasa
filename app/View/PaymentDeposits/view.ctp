<div class="paymentDeposits view">
<?php 
	echo "<h2>".__('Payment Deposit')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Cash Receipt')."</dt>";
		echo "<dd>".$this->Html->link($paymentDeposit['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $paymentDeposit['CashReceipt']['id']))."</dd>";
		echo "<dt>".__('Invoice')."</dt>";
		echo "<dd>".$this->Html->link($paymentDeposit['Invoice']['id'], array('controller' => 'invoices', 'action' => 'view', $paymentDeposit['Invoice']['id']))."</dd>";
		echo "<dt>".__('Transfer')."</dt>";
		echo "<dd>".$this->Html->link($paymentDeposit['Transfer']['id'], array('controller' => 'transfers', 'action' => 'view', $paymentDeposit['Transfer']['id']))."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".h($paymentDeposit['PaymentDeposit']['amount'])."</dd>";
		echo "<dt>".__('Bool Deposit Complete')."</dt>";
		echo "<dd>".h($paymentDeposit['PaymentDeposit']['bool_deposit_complete'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Payment Deposit'), array('action' => 'edit', $paymentDeposit['PaymentDeposit']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Payment Deposit'), array('action' => 'delete', $paymentDeposit['PaymentDeposit']['id']), array(), __('Are you sure you want to delete # %s?', $paymentDeposit['PaymentDeposit']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Payment Deposits'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Deposit'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Transfers'), array('controller' => 'transfers', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Transfer'), array('controller' => 'transfers', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
