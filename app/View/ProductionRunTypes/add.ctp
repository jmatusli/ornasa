<div class="productionRunTypes form">
<?php echo $this->Form->create('ProductionRunType'); ?>
	<fieldset>
		<legend><?php echo __('Add Production Run Type'); ?></legend>
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
		echo "<li>".$this->Html->link(__('List Production Run Types'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_productionrun_index_permission) { 
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))."</li>";
		}
		if ($bool_productionrun_add_permission) { 
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))."</li>";
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
