<div class="accountingRegisterInvoices view">
<h2><?php echo __('Accounting Register Invoice'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterInvoice['AccountingRegisterInvoice']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Accounting Register'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingRegisterInvoice['AccountingRegister']['id'], array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegisterInvoice['AccountingRegister']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Invoice'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingRegisterInvoice['Invoice']['id'], array('controller' => 'invoices', 'action' => 'view', $accountingRegisterInvoice['Invoice']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterInvoice['AccountingRegisterInvoice']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterInvoice['AccountingRegisterInvoice']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Accounting Register Invoice'), array('action' => 'edit', $accountingRegisterInvoice['AccountingRegisterInvoice']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Accounting Register Invoice'), array('action' => 'delete', $accountingRegisterInvoice['AccountingRegisterInvoice']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterInvoice['AccountingRegisterInvoice']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Register Invoices'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register Invoice'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add')); ?> </li>
	</ul>
</div>
