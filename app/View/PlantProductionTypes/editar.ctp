<div class="warehouseProducts form">
<?php	
$this->Form->create('WarehouseProduct'); 
		echo '<fieldset>';
__('Edit Warehouse Product')	echo '<legend>'.28'</legend>';
'		echo $this->Form->input('id');
		echo $this->Form->input('assignment_datetime');
		echo $this->Form->input('warehouse_id');
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
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('WarehouseProduct.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('WarehouseProduct.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Warehouse Products'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
