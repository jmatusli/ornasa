<div class="productionResultCodes view">
<h2><?php echo __('Production Result Code'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($productionResultCode['ProductionResultCode']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Code'); ?></dt>
		<dd>
			<?php echo h($productionResultCode['ProductionResultCode']['code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($productionResultCode['ProductionResultCode']['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($productionResultCode['ProductionResultCode']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($productionResultCode['ProductionResultCode']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Production Result Code'), array('action' => 'edit', $productionResultCode['ProductionResultCode']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Production Result Code'), array('action' => 'delete', $productionResultCode['ProductionResultCode']['id']), array(), __('Are you sure you want to delete # %s?', $productionResultCode['ProductionResultCode']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Production Result Codes'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Production Result Code'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Stock Items'); ?></h3>
	<?php if (!empty($productionResultCode['StockItem'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Purchase Order Id'); ?></th>
		<th><?php echo __('Production Run Id'); ?></th>
		<th><?php echo __('Sale Order Id'); ?></th>
		<th><?php echo __('Product Id'); ?></th>
		<th><?php echo __('Product Type Id'); ?></th>
		<th><?php echo __('Product Quantity'); ?></th>
		<th><?php echo __('Product Unit Price'); ?></th>
		<th><?php echo __('Previous Quantity'); ?></th>
		<th><?php echo __('Remaining Quantity'); ?></th>
		<th><?php echo __('Production Result Code Id'); ?></th>
		<th><?php echo __('Stock Movement'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($productionResultCode['StockItem'] as $stockItem): ?>
		<tr>
			<td><?php echo $stockItem['id']; ?></td>
			<td><?php echo $stockItem['purchase_order_id']; ?></td>
			<td><?php echo $stockItem['production_run_id']; ?></td>
			<td><?php echo $stockItem['sale_order_id']; ?></td>
			<td><?php echo $stockItem['product_id']; ?></td>
			<td><?php echo $stockItem['product_type_id']; ?></td>
			<td><?php echo $stockItem['product_quantity']; ?></td>
			<td><?php echo $stockItem['product_unit_price']; ?></td>
			<td><?php echo $stockItem['previous_quantity']; ?></td>
			<td><?php echo $stockItem['remaining_quantity']; ?></td>
			<td><?php echo $stockItem['production_result_code_id']; ?></td>
			<td><?php echo $stockItem['stock_movement']; ?></td>
			<td><?php echo $stockItem['created']; ?></td>
			<td><?php echo $stockItem['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'stock_items', 'action' => 'view', $stockItem['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'stock_items', 'action' => 'edit', $stockItem['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'stock_items', 'action' => 'delete', $stockItem['id']), array(), __('Are you sure you want to delete # %s?', $stockItem['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
