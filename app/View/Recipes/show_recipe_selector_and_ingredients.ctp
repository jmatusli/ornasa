<?php
	echo $this->Form->input($referrerForm.'.preferred_recipe_id',['label'=>'Receta preferida','options'=>$recipes,'default'=>$recipeId,'empty'=>[0=>'-- Receta preferida --']]);
  if (!empty($recipe)){
    $recipeHeader='';
    $recipeHeader.='<thead>';
      $recipeHeader.='<tr>';
        $recipeHeader.='<th>Materia Prima</th>';
        $recipeHeader.='<th class="centered">Cantidad</th>';
        $recipeHeader.='<th>Unidad</th>';
        $recipeHeader.='<th></th>';
      $recipeHeader.='</tr>';
    $recipeHeader.='</thead>';
          
    $tableRows='';
    $totalQuantity=0;
    for ($i=0;$i<count($recipe['RecipeItem']);$i++){
      $recipeItem=$recipe['RecipeItem'][$i];
      $totalQuantity+=$recipeItem['quantity'];
      $tableRow='';
      $tableRow.='<tr>';
        $tableRow.='<td>'.$rawMaterials[$recipeItem['product_id']].'</td>';
        $tableRow.='<td class="centered"><span class="amount">'.$recipeItem['quantity'].'</span></td>';
        $tableRow.='<td>'.(array_key_exists($recipeItem['unit_id'],$units)?$units[$recipeItem['unit_id']]:'Unidad').'</td>';
      $tableRow.='</tr>';
      $tableRows.=$tableRow;
    }
      
    $totalRow='';
    $totalRow.='<tr class="totalrow">';
      $totalRow.='<td>Total</td>';
      $totalRow.='<td class="centered"><span class="amount">'.$totalQuantity.'</span></td>';
      $totalRow.='<td></td>';
      $totalRow.='<td></td>';
    $totalRow.='</tr>';  
  
    $recipeBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
    
    $recipeIngredientTable='<table>'.$recipeHeader.$recipeBody.'</table>';
    echo '<h3>Ingredientes para receta '.$recipes[$recipeId].'</h3>';
    echo $recipeIngredientTable;
    
    if (!empty($recipe['RecipeConsumable'])){
      $recipeHeader='';
      $recipeHeader.='<thead>';
        $recipeHeader.='<tr>';
          $recipeHeader.='<th>Consumible</th>';
          $recipeHeader.='<th class="centered">Cantidad</th>';
          $recipeHeader.='<th>Unidad</th>';
          $recipeHeader.='<th></th>';
        $recipeHeader.='</tr>';
      $recipeHeader.='</thead>';
      
      $tableRows='';
      $totalQuantity=0;
      for ($i=0;$i<count($recipe['RecipeConsumable']);$i++){
        $recipeConsumable=$recipe['RecipeConsumable'][$i];
        $totalQuantity+=$recipeConsumable['quantity'];
        $tableRow='';
        $tableRow.='<tr>';
          $tableRow.='<td>'.$consumables[$recipeConsumable['product_id']].'</td>';
          $tableRow.='<td class="centered"><span class="amount">'.$recipeConsumable['quantity'].'</span></td>';
          $tableRow.='<td>'.(array_key_exists($recipeConsumable['unit_id'],$units)?$units[$recipeConsumable['unit_id']]:'Unidad').'</td>';
        $tableRow.='</tr>';
        $tableRows.=$tableRow;
      }
        
      $totalRow='';
      $totalRow.='<tr class="totalrow">';
        $totalRow.='<td>Total</td>';
        $totalRow.='<td class="centered"><span class="amount">'.$totalQuantity.'</span></td>';
        $totalRow.='<td></td>';
        $totalRow.='<td></td>';
      $totalRow.='</tr>';  
    
      $recipeBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
      
      $recipeConsumableTable='<table>'.$recipeHeader.$recipeBody.'</table>';
      echo '<h3>Consumibles para receta '.$recipes[$recipeId].'</h3>';
      echo $recipeConsumableTable;
    }
  }
			