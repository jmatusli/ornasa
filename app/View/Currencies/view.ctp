<div class="currencies view">
<h2><?php echo __('Currency'); ?></h2>
	<dl>
		<dt><?php echo __('Abbreviation'); ?></dt>
		<dd>
			<?php echo h($currency['Currency']['abbreviation']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Full Name'); ?></dt>
		<dd>
			<?php echo h($currency['Currency']['full_name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Currency'), array('action' => 'edit', $currency['Currency']['id'])); ?> </li>
		<!--li><?php //echo $this->Form->postLink(__('Delete Currency'), array('action' => 'delete', $currency['Currency']['id']), array(), __('Are you sure you want to delete # %s?', $currency['Currency']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Currencies'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('action' => 'add')); ?> </li>
		<br/>
	</ul>
</div>
