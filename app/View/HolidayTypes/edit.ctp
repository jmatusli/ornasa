<div class="holidayTypes form">
<?php echo $this->Form->create('HolidayType'); ?>
	<fieldset>
		<legend><?php echo __('Edit Holiday Type'); ?></legend>
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
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('HolidayType.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('HolidayType.id')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Holiday Types'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_employeeholiday_index_permission) {
			echo "<li>".$this->Html->link(__('List Employee Holidays'), array('controller' => 'employee_holidays', 'action' => 'index'))." </li>";
		}
		if ($bool_employeeholiday_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), array('controller' => 'employee_holidays', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>