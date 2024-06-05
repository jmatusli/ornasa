<div class="invoices index">
	<h2><?php echo __('Invoices'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('order_id'); ?></th>
			<th><?php echo $this->Paginator->sort('invoice_code'); ?></th>
			<th><?php echo $this->Paginator->sort('invoice_date'); ?></th>
			<th><?php echo $this->Paginator->sort('bool_annulled'); ?></th>
			<th><?php echo $this->Paginator->sort('client_id'); ?></th>
			<th><?php echo $this->Paginator->sort('currency_id'); ?></th>
			<th><?php echo $this->Paginator->sort('bool_credit'); ?></th>
			<th><?php echo $this->Paginator->sort('due_date'); ?></th>
			<th><?php echo $this->Paginator->sort('cashbox_accounting_code_id'); ?></th>
			<th><?php echo $this->Paginator->sort('bool_retention'); ?></th>
			<th><?php echo $this->Paginator->sort('retention_amount'); ?></th>
			<th><?php echo $this->Paginator->sort('retention_number'); ?></th>
			<th><?php echo $this->Paginator->sort('bool_IVA'); ?></th>
			<th><?php echo $this->Paginator->sort('sub_total_price'); ?></th>
			<th><?php echo $this->Paginator->sort('IVA_price'); ?></th>
			<th><?php echo $this->Paginator->sort('total_price'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($invoices as $invoice): ?>
	<tr>
		<td><?php echo h($invoice['Invoice']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($invoice['Order']['id'], array('controller' => 'orders', 'action' => 'view', $invoice['Order']['id'])); ?>
		</td>
		<td><?php echo h($invoice['Invoice']['invoice_code']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['invoice_date']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['bool_annulled']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($invoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'view', $invoice['Client']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($invoice['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $invoice['Currency']['id'])); ?>
		</td>
		<td><?php echo h($invoice['Invoice']['bool_credit']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['due_date']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($invoice['CashboxAccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $invoice['CashboxAccountingCode']['id'])); ?>
		</td>
		<td><?php echo h($invoice['Invoice']['bool_retention']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['retention_amount']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['retention_number']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['bool_IVA']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['sub_total_price']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['IVA_price']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['total_price']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['created']); ?>&nbsp;</td>
		<td><?php echo h($invoice['Invoice']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $invoice['Invoice']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $invoice['Invoice']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $invoice['Invoice']['id']), array(), __('Are you sure you want to delete # %s?', $invoice['Invoice']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Invoice'), array('action' => 'add')); ?></li>
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
