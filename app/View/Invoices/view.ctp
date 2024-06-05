<div class="invoices view">
<h2><?php echo __('Invoice'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Order'); ?></dt>
		<dd>
			<?php echo $this->Html->link($invoice['Order']['id'], array('controller' => 'orders', 'action' => 'view', $invoice['Order']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Invoice Code'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['invoice_code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Invoice Date'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['invoice_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bool Annulled'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['bool_annulled']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Client'); ?></dt>
		<dd>
			<?php echo $this->Html->link($invoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'view', $invoice['Client']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Currency'); ?></dt>
		<dd>
			<?php echo $this->Html->link($invoice['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $invoice['Currency']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bool Credit'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['bool_credit']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Due Date'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['due_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Cashbox Accounting Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($invoice['CashboxAccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $invoice['CashboxAccountingCode']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bool Retention'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['bool_retention']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Retention Amount'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['retention_amount']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Retention Number'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['retention_number']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bool IVA'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['bool_IVA']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sub Total Price'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['sub_total_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('IVA Price'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['IVA_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Total Price'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['total_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($invoice['Invoice']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Invoice'), array('action' => 'edit', $invoice['Invoice']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Invoice'), array('action' => 'delete', $invoice['Invoice']['id']), array(), __('Are you sure you want to delete # %s?', $invoice['Invoice']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), array('action' => 'add')); ?> </li>
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
