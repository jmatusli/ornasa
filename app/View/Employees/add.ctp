<div class="employees form">
<?php echo $this->Form->create('Employee', array('enctype' => 'multipart/form-data')); ?>
	<fieldset>
		<legend><?php echo __('Add Employee'); ?></legend>
	<?php
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('bool_active',array('default'=>true));
		echo $this->Form->input('position');
		echo $this->Form->input('starting_date',array('dateFormat'=>'DMY','minYear'=>'2012','maxYear'=>date('Y')));
		echo $this->Form->input('ending_date',array('dateFormat'=>'DMY','minYear'=>'2012','maxYear'=>'2030','default'=>'2030-12-31'));
		
		echo $this->Form->input('Document.url_image.0',array('label'=>'Cargar Imagen','type'=>'file'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Empleados Activos'), array('action' => 'index'))."</li>";
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
