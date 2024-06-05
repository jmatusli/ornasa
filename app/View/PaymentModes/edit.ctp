<div class="paymentModes form">
<?php echo $this->Form->create('PaymentMode'); ?>
	<fieldset>
		<legend><?php echo __('Edit Payment Mode'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('PaymentMode.id')), array(), __('Está seguro que quiere eliminar el modo de pago %s?', $this->Form->value('PaymentMode.name')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Payment Modes'), array('action' => 'index'))."</li>";
	echo "</ul>";
?>
</div>
