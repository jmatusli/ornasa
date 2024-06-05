<div class="stockItemLogs view">
<h2><?php echo __('Stock Item Log'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Stock Item'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItemLog['StockItem']['name'], array('controller' => 'stockItems', 'action' => 'view', $stockItemLog['StockItem']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Stock Movement'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItemLog['StockMovement']['name'], array('controller' => 'stockMovements', 'action' => 'view', $stockItemLog['StockMovement']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Production Movement'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItemLog['ProductionMovement']['name'], array('controller' => 'productionMovements', 'action' => 'view', $stockItemLog['ProductionMovement']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Stockitem Date'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['stockitem_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockItemLog['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockItemLog['Product']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Quantity'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['product_quantity']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Unit Price'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['product_unit_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($stockItemLog['StockItemLog']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Stock Item Log'), array('action' => 'edit', $stockItemLog['StockItemLog']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Stock Item Log'), array('action' => 'delete', $stockItemLog['StockItemLog']['id']), array(), __('Are you sure you want to delete # %s?', $stockItemLog['StockItemLog']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Stock Item Logs'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item Log'), array('action' => 'add')); ?> </li>
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
