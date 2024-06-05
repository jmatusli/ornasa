<div class="vehicles view">
<?php 
	echo '<h2>'.__('Vehicle').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Warehouse').'</dt>';
		echo '<dd>'.$this->Html->link($vehicle['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'detalle', $vehicle['Warehouse']['id']]).'</dd>';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($vehicle['Vehicle']['name']).'</dd>';
		echo '<dt>'.__('License Plate').'</dt>';
		echo '<dd>'.h($vehicle['Vehicle']['license_plate']).'</dd>';
		echo '<dt>'.__('Bool Active').'</dt>';
		echo '<dd>'.h($vehicle['Vehicle']['bool_active']).'</dd>';
		echo '<dt>'.__('List Order').'</dt>';
		echo '<dd>'.h($vehicle['Vehicle']['list_order']).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		//if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Vehicle'), ['action' => 'editar', $vehicle['Vehicle']['id']]).'</li>';
      echo '<br/>';
		//}
		echo '<li>'.$this->Html->link(__('List Vehicles'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Vehicle'), ['action' => 'crear']).'</li>';
		//echo '<br/>';
		//echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
		//echo '<li>'.$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
	echo '</ul>';
?> 
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_delete_permission){
			echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Vehículo'), ['action' => 'delete', $vehicle['Vehicle']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar el vehículo # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $vehicle['Vehicle']['name']));
	echo '<br/>';
		}
?>
</div>