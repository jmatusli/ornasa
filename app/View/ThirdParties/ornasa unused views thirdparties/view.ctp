<div class="thirdParties view">
<h2><?php echo __('Third Party'); ?></h2>
	<dl>
		<!--dt><?php echo __('Id'); ?></dt-->
		<!--dd>
			<?php echo h($thirdParty['ThirdParty']['id']); ?>
			&nbsp;
		</dd-->
		<dt><?php echo __('Provider?'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['bool_provider']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Company Name'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['company_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['first_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['last_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Phone'); ?></dt>
		<dd>
			<?php echo h($thirdParty['ThirdParty']['phone']); ?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Created'); ?></dt-->
		<!--dd>
			<?php echo h($thirdParty['ThirdParty']['created']); ?>
			&nbsp;
		</dd-->
		<!--dt><?php echo __('Modified'); ?></dt-->
		<!--dd>
			<?php echo h($thirdParty['ThirdParty']['modified']); ?>
			&nbsp;
		</dd-->
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($userrole==ROLE_ADMIN){ ?>
		<li><?php echo $this->Html->link(__('Edit Third Party'), array('action' => 'edit', $thirdParty['ThirdParty']['id'])); ?> </li>
		<?php } ?>
		<!--li><?php // echo $this->Form->postLink(__('Delete Third Party'), array('action' => 'delete', $thirdParty['ThirdParty']['id']), array(), __('Are you sure you want to delete # %s?', $thirdParty['ThirdParty']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Third Parties'), array('action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Third Party'), array('action' => 'add')); ?> </li>
		<?php } ?>
		
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Orders for Third Party'); ?></h3>
	<?php if (!empty($thirdParty['Order'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<!--th><?php echo __('Id'); ?></th-->
		<th><?php echo __('Order Date'); ?></th>
		<th><?php echo __('Invoice Code'); ?></th>
		<th><?php echo __('Third Party Id'); ?></th>
		<th><?php echo __('Stock Movement Type Id'); ?></th>
		<th><?php echo __('Total Price'); ?></th>
		<!--th><?php echo __('Created'); ?></th-->
		<!--th><?php echo __('Modified'); ?></th-->
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($thirdParty['Order'] as $order): ?>
		<tr>
			<!--td><?php echo $order['id']; ?></td-->
			<td><?php echo $order['order_date']; ?></td>
			<td><?php echo $order['invoice_code']; ?></td>
			<td><?php echo $order['third_party_id']; ?></td>
			<td><?php echo $order['stock_movement_type_id']; ?></td>
			<td><?php echo $order['total_price']; ?></td>
			<!--td><?php echo $order['created']; ?></td-->
			<!--td><?php echo $order['modified']; ?></td-->
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'orders', 'action' => 'view', $order['id'])); ?>
				<?php if ($userrole==ROLE_ADMIN){ ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'orders', 'action' => 'edit', $order['id'])); ?>
				<?php } ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'orders', 'action' => 'delete', $order['id']), array(), __('Are you sure you want to delete # %s?', $order['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<?php if ($userrole!=ROLE_FOREMAN){ ?>
			<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
			<?php } ?>
		</ul>
	</div>
</div>
