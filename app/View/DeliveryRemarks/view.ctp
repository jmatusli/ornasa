<div class="deliveryRemarks view">
<h2><?php echo __('Delivery Remark'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($deliveryRemark['DeliveryRemark']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Delivery'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deliveryRemark['Delivery']['id'], array('controller' => 'deliveries', 'action' => 'view', $deliveryRemark['Delivery']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Registering User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deliveryRemark['RegisteringUser']['userName'], array('controller' => 'users', 'action' => 'view', $deliveryRemark['RegisteringUser']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Remark Datetime'); ?></dt>
		<dd>
			<?php echo h($deliveryRemark['DeliveryRemark']['remark_datetime']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Remark Text'); ?></dt>
		<dd>
			<?php echo h($deliveryRemark['DeliveryRemark']['remark_text']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($deliveryRemark['DeliveryRemark']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($deliveryRemark['DeliveryRemark']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Delivery Remark'), array('action' => 'edit', $deliveryRemark['DeliveryRemark']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Delivery Remark'), array('action' => 'delete', $deliveryRemark['DeliveryRemark']['id']), array(), __('Are you sure you want to delete # %s?', $deliveryRemark['DeliveryRemark']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Delivery Remarks'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Delivery Remark'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deliveries'), array('controller' => 'deliveries', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Delivery'), array('controller' => 'deliveries', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Registering User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
