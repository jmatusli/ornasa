<div class="accountingRegisterCashReceipts view">
<h2><?php echo __('Accounting Register Cash Receipt'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Accounting Register'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingRegisterCashReceipt['AccountingRegister']['id'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegisterCashReceipt['AccountingRegister']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Cash Receipt'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingRegisterCashReceipt['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $accountingRegisterCashReceipt['CashReceipt']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Accounting Register Cash Receipt'), array('action' => 'edit', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Accounting Register Cash Receipt'), array('action' => 'delete', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Register Cash Receipts'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register Cash Receipt'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
	</ul>
</div>
