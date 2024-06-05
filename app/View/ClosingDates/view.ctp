<div class="closingDates view">
<h2><?php echo __('Closing Date'); ?></h2>
	<dl>
		<dt><?php echo __('ìd'); ?></dt>
		<dd>
			<?php echo h($closingDate['ClosingDate']['ìd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($closingDate['ClosingDate']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Closing Date'); ?></dt>
		<dd>
			<?php echo h($closingDate['ClosingDate']['closing_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($closingDate['ClosingDate']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($closingDate['ClosingDate']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Closing Date'), array('action' => 'edit', $closingDate['ClosingDate']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Closing Date'), array('action' => 'delete', $closingDate['ClosingDate']['id']), array(), __('Are you sure you want to delete # %s?', $closingDate['ClosingDate']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Closing Dates'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Closing Date'), array('action' => 'add')); ?> </li>
	</ul>
</div>
