<div class="stockMovementTypes view">
<h2><?php echo __('Stock Movement Type'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($stockMovementType['StockMovementType']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($stockMovementType['StockMovementType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($stockMovementType['StockMovementType']['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($stockMovementType['StockMovementType']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($stockMovementType['StockMovementType']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Stock Movement Type'), array('action' => 'edit', $stockMovementType['StockMovementType']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Stock Movement Type'), array('action' => 'delete', $stockMovementType['StockMovementType']['id']), array(), __('Are you sure you want to delete # %s?', $stockMovementType['StockMovementType']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Movement Types'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Movement Type'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Orders'); ?></h3>
	<?php if (!empty($stockMovementType['Order'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Order Date'); ?></th>
		<th><?php echo __('Invoice Code'); ?></th>
		<th><?php echo __('Third Party Id'); ?></th>
		<th><?php echo __('Stock Movement Type Id'); ?></th>
		<th><?php echo __('Total Price'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($stockMovementType['Order'] as $order): ?>
		<tr>
			<td><?php echo $order['id']; ?></td>
			<td><?php echo $order['order_date']; ?></td>
			<td><?php echo $order['invoice_code']; ?></td>
			<td><?php echo $order['third_party_id']; ?></td>
			<td><?php echo $order['stock_movement_type_id']; ?></td>
			<td><?php echo $order['total_price']; ?></td>
			<td><?php echo $order['created']; ?></td>
			<td><?php echo $order['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'orders', 'action' => 'view', $order['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'orders', 'action' => 'edit', $order['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'orders', 'action' => 'delete', $order['id']), array(), __('Are you sure you want to delete # %s?', $order['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
