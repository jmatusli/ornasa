<div class="employees form">
<?php echo $this->Form->create('Employee', array('enctype' => 'multipart/form-data')); ?>
	<fieldset>
		<legend><?php echo __('Edit Employee'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('bool_active');
		echo $this->Form->input('position');
		echo $this->Form->input('starting_date',array('dateFormat'=>'DMY','minYear'=>'2012','maxYear'=>date('Y')));
		echo $this->Form->input('ending_date',array('dateFormat'=>'DMY','minYear'=>'2012','maxYear'=>'2030'));
		
		//echo $this->Form->input('Document.url_image.0',array('label'=>'Cargar Imagen','type'=>'file'));
		if (!empty($this->request->data['Employee']['url_image'])){
			$url=$this->request->data['Employee']['url_image'];
			//pr($url);
			//echo "image url is ".$this->Html->url('/').$url."<br/>";
			echo "<img src='".$this->Html->url('/').$url."' alt='Empleado' class='resize'></img>";
		}
		if (empty($this->request->data['Employee']['url_image'])){
			echo $this->Form->input('Document.url_image.0',array('label'=>'Cargar Imagen','type'=>'file'));
		}
		else {
			echo $this->Form->input('Document.url_image.0',array('label'=>'Cargar nueva Imagen','type'=>'file'));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Employee.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Employee.id')))."</li>";
			echo "<br/>";
		}
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
	})

</script>
