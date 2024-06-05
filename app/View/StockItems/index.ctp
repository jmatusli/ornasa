<div class="stockItems index">
	<h2><?php echo __('Materiales en Inventario'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<!--th><?php echo $this->Paginator->sort('id'); ?></th-->
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<!--th><?php echo $this->Paginator->sort('description'); ?></th-->
			<!--th><?php echo $this->Paginator->sort('stock_movement_id'); ?></th-->
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th class='centered'><?php echo $this->Paginator->sort('product_unit_price'); ?></th>
			<th class='centered'><?php echo $this->Paginator->sort('original_quantity'); ?></th>
			<th class='centered'><?php echo $this->Paginator->sort('remaining_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('production_result_code_id'); ?></th>
			<!--th><?php echo $this->Paginator->sort('created'); ?></th-->
			<!--th><?php echo $this->Paginator->sort('modified'); ?></th-->
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($stockItems as $stockItem): ?>
	<tr>
		<!--td><?php echo h($stockItem['StockItem']['id']); ?>&nbsp;</td-->
		<td><?php echo h($stockItem['StockItem']['name']); ?>&nbsp;</td>
		<!--td><?php echo h($stockItem['StockItem']['description']); ?>&nbsp;</td-->
		<!--td><?php echo h($stockItem['StockItem']['stock_movement_id']); ?>&nbsp;</td-->
		<td>
			<?php echo $this->Html->link($stockItem['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockItem['Product']['id'])); ?>
		</td>
		<td class='centered'><?php echo h($stockItem['StockItem']['product_unit_price']); ?>&nbsp;C$</td>
		<td class='centered'><?php echo h($stockItem['StockItem']['original_quantity']); ?>&nbsp;</td>
		<td class='centered'><?php echo h($stockItem['StockItem']['remaining_quantity']); ?>&nbsp;</td>
		<td>
			<?php //echo $this->Html->link($stockItem['ProductionResultCode']['code'], array('controller' => 'production_result_codes', 'action' => 'view', $stockItem['ProductionResultCode']['id'])); ?>
			<?php echo h($stockItem['ProductionResultCode']['code']); ?>
		</td>
		<!--td><?php echo h($stockItem['StockItem']['created']); ?>&nbsp;</td-->
		<!--td><?php echo h($stockItem['StockItem']['modified']); ?>&nbsp;</td-->
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $stockItem['StockItem']['id'])); ?>
			<?php if ($userrole==ROLE_ADMIN){ ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $stockItem['StockItem']['id'])); ?>
			<?php } ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $stockItem['StockItem']['id']), array(), __('Are you sure you want to delete # %s?', $stockItem['StockItem']['id'])); ?>
			
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
<?php /* ?>
<!--div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<!--li><?php echo $this->Html->link(__('New Stock Item'), array('action' => 'add')); ?></li-->
		<!--li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'productTypes', 'action' => 'index')); ?> </li-->
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<!--li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'productTypes', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<!--li><?php echo $this->Html->link(__('List Production Result Codes'), array('controller' => 'productionResultCodes', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Production Result Code'), array('controller' => 'productionResultCodes', 'action' => 'add')); ?> </li-->
		<!--li><?php // echo $this->Html->link(__('List Production Movements'), array('controller' => 'productionMovements', 'action' => 'index')); ?> </li-->
		<!--li><?php // echo $this->Html->link(__('New Production Movement'), array('controller' => 'productionMovements', 'action' => 'add')); ?> </li-->
		<!--li><?php // echo $this->Html->link(__('List Stock Movements'), array('controller' => 'stockMovements', 'action' => 'index')); ?> </li-->
		<!--li><?php // echo $this->Html->link(__('New Stock Movement'), array('controller' => 'stockMovements', 'action' => 'add')); ?> </li-->
	</ul>
</div-->
<?php */ ?>