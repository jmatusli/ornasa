<div class="productionMovements index">
	<h2><?php echo __('Production Movements'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('stock_item_id'); ?></th>
			<th><?php echo $this->Paginator->sort('production_run_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_unit_price'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($productionMovements as $productionMovement): ?>
	<tr>
		<td><?php echo h($productionMovement['ProductionMovement']['id']); ?>&nbsp;</td>
		<td><?php echo h($productionMovement['ProductionMovement']['name']); ?>&nbsp;</td>
		<td><?php echo h($productionMovement['ProductionMovement']['description']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($productionMovement['StockItem']['name'], array('controller' => 'stockItems', 'action' => 'view', $productionMovement['StockItem']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($productionMovement['ProductionRun']['id'], array('controller' => 'productionRuns', 'action' => 'view', $productionMovement['ProductionRun']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($productionMovement['ProductType']['name'], array('controller' => 'productTypes', 'action' => 'view', $productionMovement['ProductType']['id'])); ?>
		</td>
		<td><?php echo h($productionMovement['ProductionMovement']['product_type_quantity']); ?>&nbsp;</td>
		<td><?php echo h($productionMovement['ProductionMovement']['product_type_unit_price']); ?>&nbsp;</td>
		<td><?php echo h($productionMovement['ProductionMovement']['created']); ?>&nbsp;</td>
		<td><?php echo h($productionMovement['ProductionMovement']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $productionMovement['ProductionMovement']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $productionMovement['ProductionMovement']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productionMovement['ProductionMovement']['id']), array(), __('Are you sure you want to delete # %s?', $productionMovement['ProductionMovement']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Production Movement'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Runs'), array('controller' => 'productionRuns', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Run'), array('controller' => 'productionRuns', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'productTypes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'productTypes', 'action' => 'add')); ?> </li>
	</ul>
</div>
