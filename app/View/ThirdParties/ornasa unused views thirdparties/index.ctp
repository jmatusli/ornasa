<div class="thirdParties index">
	<h2><?php echo __('Third Parties'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<!--th><?php echo $this->Paginator->sort('id'); ?></th-->
			<th><?php echo $this->Paginator->sort('bool_provider'); ?></th>
			<th><?php echo $this->Paginator->sort('company_name'); ?></th>
			<th><?php echo $this->Paginator->sort('first_name'); ?></th>
			<th><?php echo $this->Paginator->sort('last_name'); ?></th>
			<th><?php echo $this->Paginator->sort('email'); ?></th>
			<th><?php echo $this->Paginator->sort('phone'); ?></th>
			<!--th><?php echo $this->Paginator->sort('created'); ?></th-->
			<!--th><?php echo $this->Paginator->sort('modified'); ?></th-->
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($thirdParties as $thirdParty): ?>
	<tr>
		<!--td><?php echo h($thirdParty['ThirdParty']['id']); ?>&nbsp;</td-->
		<td><?php echo h($thirdParty['ThirdParty']['bool_provider']); ?>&nbsp;</td>
		<td><?php echo h($thirdParty['ThirdParty']['company_name']); ?>&nbsp;</td>
		<td><?php echo h($thirdParty['ThirdParty']['first_name']); ?>&nbsp;</td>
		<td><?php echo h($thirdParty['ThirdParty']['last_name']); ?>&nbsp;</td>
		<td><?php echo h($thirdParty['ThirdParty']['email']); ?>&nbsp;</td>
		<td><?php echo h($thirdParty['ThirdParty']['phone']); ?>&nbsp;</td>
		<!--td><?php echo h($thirdParty['ThirdParty']['created']); ?>&nbsp;</td-->
		<!--td><?php echo h($thirdParty['ThirdParty']['modified']); ?>&nbsp;</td-->
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $thirdParty['ThirdParty']['id'])); ?>
			<?php if ($userrole==ROLE_ADMIN){ ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $thirdParty['ThirdParty']['id'])); ?>
			<?php } ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $thirdParty['ThirdParty']['id']), array(), __('Are you sure you want to delete # %s?', $thirdParty['ThirdParty']['id'])); ?>
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
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Third Party'), array('action' => 'add')); ?></li>
		<br/>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN) { ?>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
