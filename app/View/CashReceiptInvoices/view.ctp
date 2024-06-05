<div class="cashReceiptInvoices view">
<h2><?php echo __('Cash Receipt Invoice'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($cashReceiptInvoice['CashReceiptInvoice']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Cash Receipt'); ?></dt>
		<dd>
			<?php echo $this->Html->link($cashReceiptInvoice['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $cashReceiptInvoice['CashReceipt']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Invoice'); ?></dt>
		<dd>
			<?php echo $this->Html->link($cashReceiptInvoice['Invoice']['id'], array('controller' => 'invoices', 'action' => 'view', $cashReceiptInvoice['Invoice']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($cashReceiptInvoice['CashReceiptInvoice']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($cashReceiptInvoice['CashReceiptInvoice']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Cash Receipt Invoice'), array('action' => 'edit', $cashReceiptInvoice['CashReceiptInvoice']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Cash Receipt Invoice'), array('action' => 'delete', $cashReceiptInvoice['CashReceiptInvoice']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptInvoice['CashReceiptInvoice']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipt Invoices'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt Invoice'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
	</ul>
</div>
