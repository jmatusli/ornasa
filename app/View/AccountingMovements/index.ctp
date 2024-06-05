<div class="accountingMovements index">
	<h2><?php echo __('Accounting Movements'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('accounting_register_id'); ?></th>
			<th><?php echo $this->Paginator->sort('accounting_code_id'); ?></th>
			<th><?php echo $this->Paginator->sort('amount'); ?></th>
			<th><?php echo $this->Paginator->sort('currency_id'); ?></th>
			<th><?php echo $this->Paginator->sort('bool_debit'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($accountingMovements as $accountingMovement): ?>
	<tr>
		<td><?php echo h($accountingMovement['AccountingMovement']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($accountingMovement['AccountingRegister']['name'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingMovement['AccountingRegister']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($accountingMovement['AccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingMovement['AccountingCode']['id'])); ?>
		</td>
		<td><?php echo h($accountingMovement['AccountingMovement']['amount']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($accountingMovement['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $accountingMovement['Currency']['id'])); ?>
		</td>
		<td><?php echo h($accountingMovement['AccountingMovement']['bool_debit']); ?>&nbsp;</td>
		<td><?php echo h($accountingMovement['AccountingMovement']['created']); ?>&nbsp;</td>
		<td><?php echo h($accountingMovement['AccountingMovement']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $accountingMovement['AccountingMovement']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $accountingMovement['AccountingMovement']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['AccountingMovement']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Accounting Movement'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
	</ul>
</div>
