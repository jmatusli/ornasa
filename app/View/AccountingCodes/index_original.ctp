<div class="accountingCodes index">

	<h2><?php echo __('Accounting Codes'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('accounting_code'); ?></th>
		<th><?php echo $this->Paginator->sort('description'); ?></th>
		<!--th><?php echo $this->Paginator->sort('bool_creditor'); ?></th-->
		<!--th><?php echo $this->Paginator->sort('lft'); ?></th-->
		<!--th><?php echo $this->Paginator->sort('rght'); ?></th-->
		<th><?php echo $this->Paginator->sort('parent_id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($accountingCodes as $accountingCode): ?>
	<tr>
		<td><?php echo h($accountingCode['AccountingCode']['code']); ?>&nbsp;</td>
		<td><?php echo h($accountingCode['AccountingCode']['description']); ?>&nbsp;</td>
		<!--td><?php echo h($accountingCode['AccountingCode']['bool_creditor']); ?>&nbsp;</td-->
		<!--td><?php echo h($accountingCode['AccountingCode']['lft']); ?>&nbsp;</td-->
		<!--td><?php echo h($accountingCode['AccountingCode']['rght']); ?>&nbsp;</td-->
		<td>
			<?php echo $this->Html->link($accountingCode['ParentAccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingCode['ParentAccountingCode']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $accountingCode['AccountingCode']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $accountingCode['AccountingCode']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $accountingCode['AccountingCode']['id']), array(), __('Are you sure you want to delete # %s?', $accountingCode['AccountingCode']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('action' => 'add')); ?></li>
		<br/>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<br/>
		<li><?php echo $this->Html->link(__('List Transactions'), array('controller' => 'transactions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Transaction'), array('controller' => 'transactions', 'action' => 'add')); ?> </li>
	</ul>
</div>
