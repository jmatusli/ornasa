<div class="warehouseProducts view">
<?php 
	echo '<h2>'.__('Warehouse Product').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Assignment Datetime').'</dt>';
		echo '<dd>'.h($warehouseProduct['WarehouseProduct']['assignment_datetime']).'</dd>';
		echo '<dt>'.__('Warehouse').'</dt>';
		echo '<dd>'.$this->Html->link($warehouseProduct['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'detalle', $warehouseProduct['Warehouse']['id']]).'</dd>';
		echo '<dt>'.__('Product').'</dt>';
		echo '<dd>'.$this->Html->link($warehouseProduct['Product']['name'], ['controller' => 'products', 'action' => 'detalle', $warehouseProduct['Product']['id']]).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Warehouse Product'), ['action' => 'editar', $warehouseProduct['WarehouseProduct']['id']]).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Warehouse Products'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Warehouse Product'), ['action' => 'crear']).'</li>';
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'crear']).'</li>';
	echo '</ul>';
?> 
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_edit_permission){
			echo '<li>'.$this->Form->postLink(__(->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Naturaleza'), ['action' => 'delete', $productNature['ProductNature']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la naturaleza de producto # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productNature['ProductNature']['name'])).'</li>';
	echo '<br/>';
		}
?>
</div>