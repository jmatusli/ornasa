<div class="closingDates form">
<?php echo $this->Form->create('ClosingDate'); ?>
	<fieldset>
		<legend><?php echo __('Edit Closing Date'); ?></legend>
	<?php
		echo $this->Form->input('ìd');
		echo $this->Form->input('name');
		echo $this->Form->input('closing_date');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ClosingDate.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ClosingDate.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Closing Dates'), array('action' => 'index')); ?></li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>