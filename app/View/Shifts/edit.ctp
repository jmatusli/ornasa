<div class="shifts form">
<?php echo $this->Form->create('Shift'); ?>
	<fieldset>
		<legend><?php echo __('Edit Shift'); ?></legend>
	<?php
		echo $this->Form->input('id',array('hidden'=>'hidden'));
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
			//echo "<li>".$this->Form->postLink(__('Delete Shift'), array('action' => 'delete', $this->Form->value('Shift.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Shift.id')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Shifts'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
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