<div class="paymentModes form">
<?php echo $this->Form->create('PaymentMode'); ?>
	<fieldset>
		<legend><?php echo __('Add Payment Mode'); ?></legend>
	<?php
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
		echo "<li>".$this->Html->link(__('List Payment Modes'), array('action' => 'index'))."</li>";
	echo "</ul>";
?>
</div>
