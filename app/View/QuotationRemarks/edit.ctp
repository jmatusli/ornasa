<div class="quotationRemarks form">
<?php echo $this->Form->create('QuotationRemark'); ?>
	<fieldset>
		<legend><?php echo __('Edit Quotation Remark'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('quotation_id');
		echo $this->Form->input('remark_datetime');
		echo $this->Form->input('remark_text');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('QuotationRemark.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('QuotationRemark.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Quotation Remarks'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add')); ?> </li>
	</ul>
</div>
