<div class="plants form">
<?php 
	echo $this->Form->create('Plant'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit Plant')."</legend>";
		echo $this->Form->input('id',['hidden'=>'hidden']);
		echo $this->Form->input('name');
    echo $this->Form->input('short_name',['label'=>'Nombre corto']);
    echo $this->Form->input('series',['label'=>'Letra serial de facturas']);
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
			//echo "<li>".$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('Warehouse.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('Warehouse.id')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Plants'), ['action' => 'resumen'])."</li>";
		
	echo "</ul>";
?>
</div>
