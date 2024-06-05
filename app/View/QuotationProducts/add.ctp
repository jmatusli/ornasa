<div class="quotationProducts form">
<?php echo $this->Form->create('QuotationProduct'); ?>
	<fieldset>
		<legend><?php echo __('Add Quotation Product'); ?></legend>
	<?php
		echo $this->Form->input('quotation_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('product_unit_price');
		echo $this->Form->input('product_quantity');
		echo $this->Form->input('product_total_price');
		echo $this->Form->input('currency_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Quotation Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add')); ?> </li>
	</ul>
</div>
