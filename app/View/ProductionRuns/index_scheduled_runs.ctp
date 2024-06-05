<div class="productionRuns index">
	<h2><?php echo __('Production Runs'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('production_run_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('machine_id'); ?></th>
			<th><?php echo $this->Paginator->sort('operator_id'); ?></th>
			<th><?php echo $this->Paginator->sort('shift_id'); ?></th>
			<th><?php echo $this->Paginator->sort('energy_use'); ?></th>
			<th><?php echo $this->Paginator->sort('production_complete'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($productionRuns as $productionRun): ?>
	<tr>
		<td><?php echo h($productionRun['ProductionRun']['id']); ?>&nbsp;</td>
		<td><?php echo h($productionRun['ProductionRun']['production_run_code']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($productionRun['ProductType']['name'], array('controller' => 'product_types', 'action' => 'view', $productionRun['ProductType']['id'])); ?>
		</td>
		<td><?php echo h($productionRun['ProductionRun']['product_type_quantity']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($productionRun['Machine']['name'], array('controller' => 'machines', 'action' => 'view', $productionRun['Machine']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($productionRun['Operator']['name'], array('controller' => 'operators', 'action' => 'view', $productionRun['Operator']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($productionRun['Shift']['name'], array('controller' => 'shifts', 'action' => 'view', $productionRun['Shift']['id'])); ?>
		</td>
		<td><?php echo h($productionRun['ProductionRun']['energy_use']); ?>&nbsp;</td>
		<td><?php echo h($productionRun['ProductionRun']['production_complete']); ?>&nbsp;</td>
		<td><?php echo h($productionRun['ProductionRun']['created']); ?>&nbsp;</td>
		<td><?php echo h($productionRun['ProductionRun']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $productionRun['ProductionRun']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $productionRun['ProductionRun']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productionRun['ProductionRun']['id']), array(), __('Are you sure you want to delete # %s?', $productionRun['ProductionRun']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Production Run'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
