<div class="shifts index">
	<h2><?php echo __('Shifts'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<!--th><?php echo $this->Paginator->sort('id'); ?></th-->
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<!--th><?php echo $this->Paginator->sort('created'); ?></th-->
			<!--th><?php echo $this->Paginator->sort('modified'); ?></th-->
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($shifts as $shift): ?>
	<tr>
		<!--td><?php echo h($shift['Shift']['id']); ?>&nbsp;</td-->
		<td><?php echo h($shift['Shift']['name']); ?>&nbsp;</td>
		<td><?php echo h($shift['Shift']['description']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $shift['Shift']['id'])); ?>
			<? if ($bool_edit_permission){ ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $shift['Shift']['id'])); ?>
			<? } ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shift['Shift']['id']), array(), __('Are you sure you want to delete # %s?', $shift['Shift']['id'])); ?>
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
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Shift'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
