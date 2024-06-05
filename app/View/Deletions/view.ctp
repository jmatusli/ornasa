<div class="deletions view">
<h2><?php echo __('Deletion'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($deletion['Deletion']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Reference'); ?></dt>
		<dd>
			<?php echo h($deletion['Deletion']['reference']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type'); ?></dt>
		<dd>
			<?php echo h($deletion['Deletion']['type']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($deletion['Deletion']['created']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Deletion'), array('action' => 'edit', $deletion['Deletion']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Deletion'), array('action' => 'delete', $deletion['Deletion']['id']), array(), __('Are you sure you want to delete # %s?', $deletion['Deletion']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Deletions'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deletion'), array('action' => 'add')); ?> </li>
	</ul>
</div>
