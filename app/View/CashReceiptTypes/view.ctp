<div class="cashReceiptTypes view">
<h2><?php echo __('Cash Receipt Type'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($cashReceiptType['CashReceiptType']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($cashReceiptType['CashReceiptType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($cashReceiptType['CashReceiptType']['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($cashReceiptType['CashReceiptType']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($cashReceiptType['CashReceiptType']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Cash Receipt Type'), array('action' => 'edit', $cashReceiptType['CashReceiptType']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Cash Receipt Type'), array('action' => 'delete', $cashReceiptType['CashReceiptType']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptType['CashReceiptType']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipt Types'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt Type'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Cash Receipts'); ?></h3>
	<?php if (!empty($cashReceiptType['CashReceipt'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Receipt Date'); ?></th>
		<th><?php echo __('Receipt Code'); ?></th>
		<th><?php echo __('Cash Receipt Type Id'); ?></th>
		<th><?php echo __('Amount'); ?></th>
		<th><?php echo __('Currency Id'); ?></th>
		<th><?php echo __('Client Id'); ?></th>
		<th><?php echo __('Concept'); ?></th>
		<th><?php echo __('Bool Cash'); ?></th>
		<th><?php echo __('Cheque Number'); ?></th>
		<th><?php echo __('Cheque Bank'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($cashReceiptType['CashReceipt'] as $cashReceipt): ?>
		<tr>
			<td><?php echo $cashReceipt['id']; ?></td>
			<td><?php echo $cashReceipt['receipt_date']; ?></td>
			<td><?php echo $cashReceipt['receipt_code']; ?></td>
			<td><?php echo $cashReceipt['cash_receipt_type_id']; ?></td>
			<td><?php echo $cashReceipt['amount']; ?></td>
			<td><?php echo $cashReceipt['currency_id']; ?></td>
			<td><?php echo $cashReceipt['client_id']; ?></td>
			<td><?php echo $cashReceipt['concept']; ?></td>
			<td><?php echo $cashReceipt['bool_cash']; ?></td>
			<td><?php echo $cashReceipt['cheque_number']; ?></td>
			<td><?php echo $cashReceipt['cheque_bank']; ?></td>
			<td><?php echo $cashReceipt['created']; ?></td>
			<td><?php echo $cashReceipt['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'cash_receipts', 'action' => 'view', $cashReceipt['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'cash_receipts', 'action' => 'edit', $cashReceipt['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'cash_receipts', 'action' => 'delete', $cashReceipt['id']), array(), __('Are you sure you want to delete # %s?', $cashReceipt['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
