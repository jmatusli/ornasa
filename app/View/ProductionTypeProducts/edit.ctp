<div class="productionTypeProducts form">
<?php	
$this->Form->create('ProductionTypeProduct'); 
		echo '<fieldset>';
__('Edit Production Type Product')	echo '<legend>'.34'</legend>';
'		echo $this->Form->input('id');
		echo $this->Form->input('assignment_datetime');
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
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('ProductionTypeProduct.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('ProductionTypeProduct.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Production Type Products'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('List Production Types'), ['controller' => 'production_types', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Production Type'), ['controller' => 'production_types', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
