<div class="productionMovements view">
<h2><?php echo __('Production Movement'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['name']); ?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Description'); ?></dt-->
		<!--dd>
			<?php echo h($productionMovement['ProductionMovement']['description']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Stock Item'); ?></dt>
		<dd>
			<?php echo $this->Html->link($productionMovement['StockItem']['name'], array('controller' => 'stockItems', 'action' => 'view', $productionMovement['StockItem']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Production Run'); ?></dt>
		<dd>
			<?php echo $this->Html->link($productionMovement['ProductionRun']['id'], array('controller' => 'productionRuns', 'action' => 'view', $productionMovement['ProductionRun']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product'); ?></dt>
		<dd>
			<?php echo $this->Html->link($productionMovement['Product']['name'], array('controller' => 'products', 'action' => 'view', $productionMovement['Product']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Quantity'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['product_quantity']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Unit Price'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['product_unit_price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($productionMovement['ProductionMovement']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<!--li><?php echo $this->Html->link(__('Edit Production Movement'), array('action' => 'edit', $productionMovement['ProductionMovement']['id'])); ?> </li-->
		<!--li><?php echo $this->Form->postLink(__('Delete Production Movement'), array('action' => 'delete', $productionMovement['ProductionMovement']['id']), array(), __('Are you sure you want to delete # %s?', $productionMovement['ProductionMovement']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Production Movements'), array('action' => 'index')); ?> </li>
		<!--li><?php echo $this->Html->link(__('New Production Movement'), array('action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stockItems', 'action' => 'index')); ?> </li>
		<!--li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stockItems', 'action' => 'add')); ?> </li-->
		<li><?php echo $this->Html->link(__('List Production Runs'), array('controller' => 'productionRuns', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Production Run'), array('controller' => 'productionRuns', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
