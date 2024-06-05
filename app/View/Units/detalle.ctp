<div class="units view">
<?php 
	echo '<h2>'.__('Unit').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($unit['Unit']['name']).'</dd>';
		echo '<dt>'.__('Abbreviation').'</dt>';
		echo '<dd>'.h($unit['Unit']['abbreviation']).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Unit'), ['action' => 'editar', $unit['Unit']['id']]).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Units'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Unit'), ['action' => 'crear']).'</li>';
		echo '<br/>';
	echo '</ul>';
?> 
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_delete_permission){
			echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Naturaleza'), ['action' => 'delete', $productNature['ProductNature']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la naturaleza de producto # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productNature['ProductNature']['name']));
	echo '<br/>';
		}
?>
</div>