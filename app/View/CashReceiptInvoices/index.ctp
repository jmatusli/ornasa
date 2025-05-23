<div class="cashReceiptInvoices index">
	<h2><?php echo __('Cash Receipt Invoices'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('cash_receipt_id'); ?></th>
			<th><?php echo $this->Paginator->sort('invoice_id'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($cashReceiptInvoices as $cashReceiptInvoice): ?>
	<tr>
		<td><?php echo h($cashReceiptInvoice['CashReceiptInvoice']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($cashReceiptInvoice['CashReceipt']['id'], array('controller' => 'cash_receipts', 'action' => 'view', $cashReceiptInvoice['CashReceipt']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($cashReceiptInvoice['Invoice']['id'], array('controller' => 'invoices', 'action' => 'view', $cashReceiptInvoice['Invoice']['id'])); ?>
		</td>
		<td><?php echo h($cashReceiptInvoice['CashReceiptInvoice']['created']); ?>&nbsp;</td>
		<td><?php echo h($cashReceiptInvoice['CashReceiptInvoice']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $cashReceiptInvoice['CashReceiptInvoice']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $cashReceiptInvoice['CashReceiptInvoice']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $cashReceiptInvoice['CashReceiptInvoice']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptInvoice['CashReceiptInvoice']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Cash Receipt Invoice'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
	</ul>
</div>
