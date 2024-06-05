<div class="stockMovements index">
	<h2><?php echo __('Stock Movements'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('movement_date'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('order_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('product_price'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('product_type_unit_price'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($stockMovements as $stockMovement): ?>
	<tr>
		<td><?php echo h($stockMovement['StockMovement']['id']); ?>&nbsp;</td>
		<td><?php echo h($stockMovement['StockMovement']['movement_date']); ?>&nbsp;</td>
		<td><?php echo h($stockMovement['StockMovement']['name']); ?>&nbsp;</td>
		<!--td><?php echo h($stockMovement['StockMovement']['description']); ?>&nbsp;</td-->
		<td>
			<?php echo $this->Html->link($stockMovement['Order']['id'], array('controller' => 'orders', 'action' => 'view', $stockMovement['Order']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($stockMovement['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockMovement['Product']['id'])); ?>
		</td>
		<td><?php echo h($stockMovement['StockMovement']['product_quantity']); ?>&nbsp;</td>
		<td><?php echo h($stockMovement['StockMovement']['product_price']); ?>&nbsp;</td>
		<!--td><?php echo h($stockMovement['StockMovement']['product_type_quantity']); ?>&nbsp;</td-->
		<!--td><?php echo h($stockMovement['StockMovement']['product_type_unit_price']); ?>&nbsp;</td-->
		<td><?php echo h($stockMovement['StockMovement']['created']); ?>&nbsp;</td>
		<td><?php echo h($stockMovement['StockMovement']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $stockMovement['StockMovement']['id'])); ?>
			<?php // echo $this->Html->link(__('Edit'), array('action' => 'edit', $stockMovement['StockMovement']['id'])); ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $stockMovement['StockMovement']['id']), array(), __('Are you sure you want to delete # %s?', $stockMovement['StockMovement']['id'])); ?>
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
		<?php if ($userrole != ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Stock Movement'), array('action' => 'add')); ?></li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<?php if ($userrole != ROLE_FOREMAN){ ?>
		<!--li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<?php if ($userrole != ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole != ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
