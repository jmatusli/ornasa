<div class="productionTypes view">
<?php 
	echo '<h2>'.__('Production Type').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($productionType['ProductionType']['name']).'</dd>';
		echo '<dt>'.__('Short Description').'</dt>';
		echo '<dd>'.h($productionType['ProductionType']['short_description']).'</dd>';
		echo '<dt>'.__('Long Description').'</dt>';
		echo '<dd>'.(empty($productNature['ProductNature']['long_description'])?"-":$productNature['ProductNature']['long_description']).'</dd>';
		echo '<dt>'.__('List Order').'</dt>';
		echo '<dd>'.h($productionType['ProductionType']['list_order']).'</dd>';
		echo '<dt>'.__('Hex Color').'</dt>';
		echo '<dd>'.h($productionType['ProductionType']['hex_color']).'</dd>';
	echo '</dl>';
  if (!empty($productionTypePlants)){ 
    echo '<h3>'.__('Plants').'</h3>';
    echo '<ul style="list-style:none;">';
    foreach ($productionTypePlants as $plantId=>$plantName){
      echo '<li>'.$plantName.'</li>';
    }
    echo "</ul>";
  }  
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Production Type'), ['action' => 'editar', $productionType['ProductionType']['id']]).'</li>';
	echo '<br/>';
		}
		if ($bool_edit_permission){
			echo '<li>'.$this->Form->postLink(__('Delete Production Type'), ['action' => 'delete', $productionType['ProductionType']['id']], [], __('Está seguro que quiere eliminar # %s?', $productionType['ProductionType']['id'])).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Production Types'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Production Type'), ['action' => 'crear']).'</li>';
		echo '<br/>';
	echo '</ul>';
?> 
</div>
<br/>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left; width:100%;">
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Tipo de Producción'), ['action' => 'delete', $productionType['ProductionType']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar el tipo de producción # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productionType['ProductionType']['name']));
  }
?>
</div>
