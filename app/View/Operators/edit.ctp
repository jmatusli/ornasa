<div class="operators form">
<?php echo $this->Form->create('Operator'); ?>
	<fieldset>
		<legend><?php echo __('Edit Operator'); ?></legend>
	<?php
		echo $this->Form->input('id',['hidden'=>'hidden']);
		echo $this->Form->input('name');
    echo $this->Form->input('plant_id',['empty'=>[0=>'-- Planta --']]);
		echo $this->Form->input('bool_active');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Operator'), array('action' => 'delete', $this->Form->value('Operator.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Operator.id')))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Operators'), array('action' => 'index'))."</li>";
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