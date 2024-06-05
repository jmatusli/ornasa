<div class="accountingMovements view">
<h2><?php echo __('Accounting Movement'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($accountingMovement['AccountingMovement']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Accounting Register'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingMovement['AccountingRegister']['name'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingMovement['AccountingRegister']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Accounting Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingMovement['AccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingMovement['AccountingCode']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Amount'); ?></dt>
		<dd>
			<?php echo h($accountingMovement['AccountingMovement']['amount']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Currency'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingMovement['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $accountingMovement['Currency']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bool Debit'); ?></dt>
		<dd>
			<?php echo h($accountingMovement['AccountingMovement']['bool_debit']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($accountingMovement['AccountingMovement']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($accountingMovement['AccountingMovement']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Accounting Movement'), array('action' => 'edit', $accountingMovement['AccountingMovement']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Accounting Movement'), array('action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['AccountingMovement']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Movements'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Movement'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
	</ul>
</div>
