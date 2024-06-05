<div class="quotationImages form">
<?php echo $this->Form->create('QuotationImage'); ?>
	<fieldset>
		<legend><?php echo __('Add Quotation Image'); ?></legend>
	<?php
		echo $this->Form->input('quotation_id');
		echo $this->Form->input('url_image');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Quotation Images'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add')); ?> </li>
	</ul>
</div>
