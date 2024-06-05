<div class="cashReceiptTypes index">
	<h2><?php echo __('Cash Receipt Types'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($cashReceiptTypes as $cashReceiptType): ?>
	<tr>
		<td><?php echo h($cashReceiptType['CashReceiptType']['id']); ?>&nbsp;</td>
		<td><?php echo h($cashReceiptType['CashReceiptType']['name']); ?>&nbsp;</td>
		<td><?php echo h($cashReceiptType['CashReceiptType']['description']); ?>&nbsp;</td>
		<td><?php echo h($cashReceiptType['CashReceiptType']['created']); ?>&nbsp;</td>
		<td><?php echo h($cashReceiptType['CashReceiptType']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $cashReceiptType['CashReceiptType']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $cashReceiptType['CashReceiptType']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $cashReceiptType['CashReceiptType']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceiptType['CashReceiptType']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Cash Receipt Type'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('controller' => 'cash_receipts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cash Receipt'), array('controller' => 'cash_receipts', 'action' => 'add')); ?> </li>
	</ul>
</div>
