<div class="productNatures view">
<?php 
	echo '<h2>'.__('Product Nature').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($productNature['ProductNature']['name']).'</dd>';
		echo '<dt>'.__('Short Description').'</dt>';
		echo '<dd>'.h($productNature['ProductNature']['short_description']).'</dd>';
		echo '<dt>'.__('Long Description').'</dt>';
		echo '<dd>'.(empty($productNature['ProductNature']['long_description'])?"-":$productNature['ProductNature']['long_description']).'</dd>';
		echo '<dt>'.__('List Order').'</dt>';
		echo '<dd>'.h($productNature['ProductNature']['list_order']).'</dd>';
		echo '<dt>'.__('Hex Color').'</dt>';
		echo '<dd>'.h($productNature['ProductNature']['hex_color']).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Product Nature'), ['action' => 'editar', $productNature['ProductNature']['id']]).'</li>';
	echo '<br/>';
		}
		if ($bool_edit_permission){
			echo '<li>'.$this->Form->postLink(__('Delete Product Nature'), ['action' => 'delete', $productNature['ProductNature']['id']], [], __('Está seguro que quiere eliminar # %s?', $productNature['ProductNature']['id'])).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Product Natures'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Product Nature'), ['action' => 'crear']).'</li>';
		echo '<br/>';
	echo '</ul>';
?> 
</div>
<br/>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left; width:100%;">
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Naturaleza'), ['action' => 'delete', $productNature['ProductNature']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la naturaleza de producto # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productNature['ProductNature']['name']));
  }
?>
</div>