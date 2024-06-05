<div class="stockItemLogs index">
	<h2><?php echo __('Stock Item Logs'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('stock_item_id'); ?></th>
			<th><?php echo $this->Paginator->sort('stock_movement_id'); ?></th>
			<th><?php echo $this->Paginator->sort('production_movement_id'); ?></th>
			<th><?php echo $this->Paginator->sort('stockitem_date'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('product_unit_price'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($stockItemLogs as $stockItemLog): ?>
	<tr>
		<td><?php echo h($stockItemLog['StockItemLog']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($stockItemLog['StockItem']['name'], array('controller' => 'stockItems', 'action' => 'view', $stockItemLog['StockItem']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($stockItemLog['StockMovement']['name'], array('controller' => 'stockMovements', 'action' => 'view', $stockItemLog['StockMovement']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($stockItemLog['ProductionMovement']['name'], array('controller' => 'productionMovements', 'action' => 'view', $stockItemLog['ProductionMovement']['id'])); ?>
		</td>
		<td><?php echo h($stockItemLog['StockItemLog']['stockitem_date']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($stockItemLog['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockItemLog['Product']['id'])); ?>
		</td>
		<td><?php echo h($stockItemLog['StockItemLog']['product_quantity']); ?>&nbsp;</td>
		<td><?php echo h($stockItemLog['StockItemLog']['product_unit_price']); ?>&nbsp;</td>
		<td><?php echo h($stockItemLog['StockItemLog']['created']); ?>&nbsp;</td>
		<td><?php echo h($stockItemLog['StockItemLog']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $stockItemLog['StockItemLog']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $stockItemLog['StockItemLog']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $stockItemLog['StockItemLog']['id']), array(), __('Are you sure you want to delete # %s?', $stockItemLog['StockItemLog']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Stock Item Log'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stockMovements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stockMovements', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Movements'), array('controller' => 'productionMovements', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Movement'), array('controller' => 'productionMovements', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
