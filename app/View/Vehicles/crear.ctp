<div class="vehicles form">
<?php
	echo $this->Form->create('Vehicle'); 
	echo '<fieldset>';
		echo '<legend>'.__('Add Vehicle').'</legend>';
		echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
		echo $this->Form->input('name');
		echo $this->Form->input('license_plate');
		echo $this->Form->input('bool_active',['checked'=>true]);
		echo $this->Form->input('list_order',['default'=>100]);
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Vehicles'), ['action' => 'resumen']).'</li>';
		//echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
		//echo '<li>'.$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
