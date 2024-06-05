<div class="productionTypeProducts view">
<?php 
	echo '<h2>'.__('Production Type Product').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Assignment Datetime').'</dt>';
		echo '<dd>'.h($productionTypeProduct['ProductionTypeProduct']['assignment_datetime']).'</dd>';
		echo '<dt>'.__('Production Type').'</dt>';
		echo '<dd>'.$this->Html->link($productionTypeProduct['ProductionType']['name'], ['controller' => 'production_types', 'action' => 'detalle', $productionTypeProduct['ProductionType']['id']]).'</dd>';
		echo '<dt>'.__('Product').'</dt>';
		echo '<dd>'.$this->Html->link($productionTypeProduct['Product']['name'], ['controller' => 'products', 'action' => 'detalle', $productionTypeProduct['Product']['id']]).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Production Type Product'), ['action' => 'editar', $productionTypeProduct['ProductionTypeProduct']['id']]).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Production Type Products'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Production Type Product'), ['action' => 'crear']).'</li>';
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Production Types'), ['controller' => 'production_types', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Production Type'), ['controller' => 'production_types', 'action' => 'crear']).'</li>';
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