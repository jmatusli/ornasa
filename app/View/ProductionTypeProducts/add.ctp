<div class="productionTypeProducts form">
<?php	
$this->Form->create('ProductionTypeProduct'); 
		echo '<fieldset>';
__('Add Production Type Product')	echo '<legend>'.33'</legend>';
'		echo $this->Form->input('assignment_datetime');
		echo $this->Form->input('production_type_id');
		echo $this->Form->input('product_id');
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Production Type Products'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('List Production Types'), ['controller' => 'production_types', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Production Type'), ['controller' => 'production_types', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
