<div class="accountingRegisterTypes view">
<h2><?php echo __('Accounting Register Type'); ?></h2>
	<dl>
		<dt><?php echo __('Abbreviation'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterType['AccountingRegisterType']['abbreviation']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterType['AccountingRegisterType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($accountingRegisterType['AccountingRegisterType']['description']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Accounting Register Type'), array('action' => 'edit', $accountingRegisterType['AccountingRegisterType']['id']))."</li>";
		}
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $accountingRegisterType['AccountingRegisterType']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterType['AccountingRegisterType']['id']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Accounting Register Types'), array('action' => 'index'))."</li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register Type'), array('action' => 'add'))."</li>";
		}
		echo "<br/>";
		if ($bool_accountingregister_index_permission){
			echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		}
		if ($bool_accountingregister_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>	
</div>
<div class="related">
	<h3><?php echo __('Related Accounting Registers of this Accounting Register Type'); ?></h3>
	<?php if (!empty($accountingRegisterType['AccountingRegister'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Concept'); ?></th>
		<th><?php echo __('Register Date'); ?></th>
		<th><?php echo __('Currency Id'); ?></th>
		<th><?php echo __('Observation'); ?></th>
		<th><?php echo __('Accounting Register Type Id'); ?></th>
		<th><?php echo __('Bool Invoice'); ?></th>
		<th><?php echo __('Bool Fuel Order'); ?></th>
		<th><?php echo __('Bool Section Biweekly'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($accountingRegisterType['AccountingRegister'] as $accountingRegister): ?>
		<tr>
			<td><?php echo $accountingRegister['id']; ?></td>
			<td><?php echo $accountingRegister['concept']; ?></td>
			<td><?php echo $accountingRegister['register_date']; ?></td>
			<td><?php echo $accountingRegister['currency_id']; ?></td>
			<td><?php echo $accountingRegister['observation']; ?></td>
			<td><?php echo $accountingRegister['accounting_register_type_id']; ?></td>
			<td><?php echo $accountingRegister['bool_invoice']; ?></td>
			<td><?php echo $accountingRegister['bool_fuel_order']; ?></td>
			<td><?php echo $accountingRegister['bool_section_biweekly']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'accounting_registers', 'action' => 'view', $accountingRegister['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'accounting_registers', 'action' => 'edit', $accountingRegister['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_registers', 'action' => 'delete', $accountingRegister['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegister['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
