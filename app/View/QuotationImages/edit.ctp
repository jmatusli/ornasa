<div class="quotationImages form">
<?php echo $this->Form->create('QuotationImage'); ?>
	<fieldset>
		<legend><?php echo __('Edit Quotation Image'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('quotation_id');
		echo $this->Form->input('url_image');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('QuotationImage.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('QuotationImage.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Quotation Images'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add')); ?> </li>
	</ul>
</div>
