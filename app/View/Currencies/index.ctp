<div class="currencies index">
	<h2><?php echo __('Currencies'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('abbreviation'); ?></th>
		<th><?php echo $this->Paginator->sort('full_name'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($currencies as $currency): ?>
	<tr>
		<td><?php echo h($currency['Currency']['abbreviation']); ?>&nbsp;</td>
		<td><?php echo h($currency['Currency']['full_name']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $currency['Currency']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $currency['Currency']['id'])); ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $currency['Currency']['id']), array(), __('Are you sure you want to delete # %s?', $currency['Currency']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Currency'), array('action' => 'add')); ?></li>
		<!--li><?php echo $this->Html->link(__('List Purchase Order Products'), array('controller' => 'purchase_order_products', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Purchase Order Product'), array('controller' => 'purchase_order_products', 'action' => 'add')); ?> </li-->
	</ul>
</div>
