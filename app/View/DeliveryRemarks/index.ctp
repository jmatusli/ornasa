<div class="deliveryRemarks index">
	<h2><?php echo __('Delivery Remarks'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('delivery_id'); ?></th>
			<th><?php echo $this->Paginator->sort('registering_user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('remark_datetime'); ?></th>
			<th><?php echo $this->Paginator->sort('remark_text'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($deliveryRemarks as $deliveryRemark): ?>
	<tr>
		<td><?php echo h($deliveryRemark['DeliveryRemark']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($deliveryRemark['Delivery']['id'], array('controller' => 'deliveries', 'action' => 'view', $deliveryRemark['Delivery']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($deliveryRemark['RegisteringUser']['userName'], array('controller' => 'users', 'action' => 'view', $deliveryRemark['RegisteringUser']['id'])); ?>
		</td>
		<td><?php echo h($deliveryRemark['DeliveryRemark']['remark_datetime']); ?>&nbsp;</td>
		<td><?php echo h($deliveryRemark['DeliveryRemark']['remark_text']); ?>&nbsp;</td>
		<td><?php echo h($deliveryRemark['DeliveryRemark']['created']); ?>&nbsp;</td>
		<td><?php echo h($deliveryRemark['DeliveryRemark']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $deliveryRemark['DeliveryRemark']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $deliveryRemark['DeliveryRemark']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $deliveryRemark['DeliveryRemark']['id']), array(), __('Are you sure you want to delete # %s?', $deliveryRemark['DeliveryRemark']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Delivery Remark'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Deliveries'), array('controller' => 'deliveries', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Delivery'), array('controller' => 'deliveries', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Registering User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
