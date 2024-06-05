<div class="accountingRegisterTypes index">
	<h2><?php echo __('Accounting Register Types'); ?></h2>
	<table cellpadding='0' cellspacing='0'>
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('abbreviation'); ?></th>
		<th><?php echo $this->Paginator->sort('name'); ?></th>
		<th><?php echo $this->Paginator->sort('description'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($accountingRegisterTypes as $accountingRegisterType): ?>
	<tr>
		<td><?php echo h($accountingRegisterType['AccountingRegisterType']['abbreviation']); ?>&nbsp;</td>
		<td><?php echo h($accountingRegisterType['AccountingRegisterType']['name']); ?>&nbsp;</td>
		<td><?php echo h($accountingRegisterType['AccountingRegisterType']['description']); ?>&nbsp;</td>
		<td class='actions'>
		<?php 
			echo $this->Html->link(__('View'), array('action' => 'view', $accountingRegisterType['AccountingRegisterType']['id']));
			if ($bool_edit_permission){
				echo $this->Html->link(__('Edit'), array('action' => 'edit', $accountingRegisterType['AccountingRegisterType']['id']));
			}
			if ($bool_delete_permission){
				//echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $accountingRegisterType['AccountingRegisterType']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegisterType['AccountingRegisterType']['id'])); 
			}
		?>
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
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register Type'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_accountingregister_index_permission){
			echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		}
		if ($bool_accountingregister_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
</div>
