<div class="stockMovements view stockRemovals">
<h2><?php echo __('Stock Entry'); ?></h2>
	<dl>
		<!--dt><?php echo __('Id'); ?></dt-->
		<!--dd>
			<?php echo h($stockMovement['StockMovement']['id']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Movement Date'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['movement_date']); ?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Lot Identifier'); ?></dt-->
		<!--dd>
			<?php echo h($stockMovement['StockMovement']['name']); ?>
			&nbsp;
		</dd-->
		<!--dt><?php echo __('Description'); ?></dt-->
		<!--dd>
			<?php echo h($stockMovement['StockMovement']['description']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Sale'); ?></dt>
		<dd>
			<?php echo $this->Html->link($stockMovement['Order']['id'], array('controller' => 'orders', 'action' => 'view', $stockMovement['Order']['id'])); ?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Product'); ?></dt-->
		<!--dd>
			<?php echo $this->Html->link($stockMovement['Product']['name'], array('controller' => 'products', 'action' => 'view', $stockMovement['Product']['id'])); ?>
			&nbsp;
		</dd-->
		<!--dt><?php echo __('Product Quantity'); ?></dt-->
		<!--dd>
			<?php echo h($stockMovement['StockMovement']['product_quantity']); ?>
			&nbsp;
		</dd-->
		<!--dt><?php echo __('Product Price'); ?></dt-->
		<!--dd>
			<?php echo h($stockMovement['StockMovement']['product_price']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Product Type id'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['product_type_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Type Quantity'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['product_type_quantity']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Type Unit Price'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['product_type_unit_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($stockMovement['StockMovement']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<!--li><?php echo $this->Html->link(__('Edit Stock Movement'), array('action' => 'edit', $stockMovement['StockMovement']['id'])); ?> </li-->
		<!--li><?php // echo $this->Form->postLink(__('Delete Stock Movement'), array('action' => 'delete', $stockMovement['StockMovement']['id']), array(), __('Are you sure you want to delete # %s?', $stockMovement['StockMovement']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Stock Movements'), array('action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Stock Removal'), array('action' => 'addStockremoval')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<!--li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Sales'), array('controller' => 'orders', 'action' => 'indexSales')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'addSale')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
