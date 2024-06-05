<div class="productionResultCodes index">
	<h2><?php echo __('Production Result Codes'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('code'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($productionResultCodes as $productionResultCode): ?>
	<tr>
		<td><?php echo h($productionResultCode['ProductionResultCode']['id']); ?>&nbsp;</td>
		<td><?php echo h($productionResultCode['ProductionResultCode']['code']); ?>&nbsp;</td>
		<td><?php echo h($productionResultCode['ProductionResultCode']['description']); ?>&nbsp;</td>
		<td><?php echo h($productionResultCode['ProductionResultCode']['created']); ?>&nbsp;</td>
		<td><?php echo h($productionResultCode['ProductionResultCode']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $productionResultCode['ProductionResultCode']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $productionResultCode['ProductionResultCode']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productionResultCode['ProductionResultCode']['id']), array(), __('Are you sure you want to delete # %s?', $productionResultCode['ProductionResultCode']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Production Result Code'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
