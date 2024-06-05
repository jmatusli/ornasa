<div class="warehouses form">
<?php 
	echo $this->Form->create('Warehouse'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit Warehouse')."</legend>";
		echo $this->Form->input('id',array('hidden'=>'hidden'));
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Warehouse.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Warehouse.id')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Warehouses'), array('action' => 'index'))."</li>";
		
	echo "</ul>";
?>
</div>
