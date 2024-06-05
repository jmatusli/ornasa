<div class="recipes view">
<?php 
	echo '<h1>'.__('Recipe').' '.$recipe['Recipe']['name'].'</h1>';
	echo '<dl class="dl50">';
    echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($recipe['Recipe']['name']).'</dd>';
		echo '<dt>'.__('Product').'</dt>';
		echo '<dd>'.$this->Html->link($recipe['Product']['name'], ['controller' => 'products', 'action' => 'view', $recipe['Product']['id']]).'</dd>';
		echo '<dt>'.__('Description').'</dt>';
		echo '<dd>'.(empty($recipe['Recipe']['description'])?'-':$recipe['Recipe']['description']).'</dd>';
    echo '<dt>'.__('Mill Conversion Product').'</dt>';
		echo '<dd>'.(empty($recipe['MillConversionProduct']['id'])?'-':$recipe['MillConversionProduct']['name']).'</dd>';
		
    echo '<dt>'.__('Production Type').'</dt>';
		echo '<dd>'.$this->Html->link($recipe['Product']['ProductionType']['name'], ['controller' => 'productionTypes', 'action' => 'detalle', $recipe['Product']['ProductionType']['id']]).'</dd>';
		
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Recipe'), ['action' => 'editar', $recipe['Recipe']['id']]).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Recipes'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Recipe'), ['action' => 'crear']).'</li>';
	echo '</ul>';
?> 
</div>
<div class="related">
<?php 
	if (!empty($recipe['RecipeItem'])){
		echo '<h2>Ingredientes</h2>';
		echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
				echo '<th>'.__('Product').'</th>';
				echo '<th>'.__('Quantity').'</th>';
				echo '<th>'.__('Unit').'</th>';
			echo '</tr>';
		foreach ($recipe['RecipeItem'] as $recipeItem){ 
			echo '<tr>';
				echo '<td>'.$this->Html->link($recipeItem['Product']['name'],['controller'=>'products','action'=>'view',$recipeItem['Product']['id']]).'</td>';
				echo '<td>'.$recipeItem['quantity'].'</td>';
				echo '<td>'.$recipeItem['Unit']['name'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($recipe['RecipeConsumable'])){
		echo '<h2>Consumibles</h2>';
		echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
				echo '<th>'.__('Product').'</th>';
				echo '<th>'.__('Quantity').'</th>';
				echo '<th>'.__('Unit').'</th>';
			echo '</tr>';
		foreach ($recipe['RecipeConsumable'] as $recipeConsumable){ 
			echo '<tr>';
				echo '<td>'.$this->Html->link($recipeConsumable['Product']['name'],['controller'=>'products','action'=>'view',$recipeConsumable['Product']['id']]).'</td>';
				echo '<td>'.$recipeConsumable['quantity'].'</td>';
				echo '<td>'.$recipeConsumable['Unit']['name'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_delete_permission){
			echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Receta'), ['action' => 'delete', $recipe['Recipe']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la receta  %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $recipe['Recipe']['name']));
	echo '<br/>';
		}
?>
</div>