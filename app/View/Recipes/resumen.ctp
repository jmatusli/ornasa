<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>
<div class="recipes index">
<?php 
	echo '<h2>'.__('Recipes').'</h2>';
	echo $this->Form->create('Report');
		echo '<fieldset>';
			echo $this->Form->input('Report.production_type_id',['value'=>$productionTypeId,'empty'=>[0=>'-- Tipo de Producción --']]);
      //echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
		echo '</fieldset>';
		//echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
		//echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
	echo $this->Form->end(__('Refresh'));
	//echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']);
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_add_permission){
			echo '<li>'.$this->Html->link(__('New Recipe'), ['action' => 'crear']).'</li>';
		}
	echo '</ul>';
?>
</div>
<div>
<?php
	$tableHeader='<thead>';
		$tableHeader.='<tr>';
			$tableHeader.='<th>'.$this->Paginator->sort('name','Receta').'</th>';
			$tableHeader.='<th>'.$this->Paginator->sort('product_id').'</th>';
      //$tableHeader.='<th>'.$this->Paginator->sort('description').'</th>';
      $tableHeader.='<th>Ingredientes</th>';
      $tableHeader.='<th>Conversión Molina</th>';
			//$tableHeader.='<th class="actions">'.__('Actions').'</th>';
		$tableHeader.='</tr>';
	$tableHeader.='</thead>';
	$excelHeader='<thead>';
		$excelHeader.='<tr>';
			$excelHeader.='<th>'.$this->Paginator->sort('name').'</th>';
      $excelHeader.='<th>'.$this->Paginator->sort('product_id').'</th>';
			//$excelHeader.='<th>'.$this->Paginator->sort('description').'</th>';
      $excelHeader.='<th>Ingredientes</th>';
      $excelHeader.='<th>Conversión Molina</th>';
		$excelHeader.='</tr>';
	$excelHeader.='</thead>';

	$tableBody='';
	$excelBody='';

	foreach ($recipes as $recipe){ 
		$tableRow='';		
    $tableRow.='<td>'.$this->Html->link($recipe['Recipe']['name'],['action'=>'detalle',$recipe['Recipe']['id']]).'</td>';
		$tableRow.='<td>'.$this->Html->link($recipe['Product']['name'], ['controller' => 'products', 'action' => 'view', $recipe['Product']['id']]).'</td>';
		//$tableRow.='<td>'.(empty($recipe['Recipe']['description'])?'-':$recipe['Recipe']['description']).'</td>';
    $tableRow.='<td>';
    if (empty($recipe['RecipeItem'])){
      $tableRow.='No ingredientes!!!';
    }
    else {
      foreach ($recipe['RecipeItem'] as $recipeItem){
        $tableRow.=$recipeItem['quantity'].' '.$recipeItem['Unit']['abbreviation'].' de producto '.$recipeItem['Product']['name'].'<br/>';
      }
    }
    if (!empty($recipe['RecipeConsumable'])){
      foreach ($recipe['RecipeConsumable'] as $recipeConsumable){
        $tableRow.=$recipeConsumable['quantity'].' '.$recipeConsumable['Unit']['abbreviation'].' de producto '.$recipeConsumable['Product']['name'].'<br/>';
      }
    }
    $tableRow.='</td>';
		$tableRow.='<td>'.$recipe['MillConversionProduct']['name'].'</td>';

    $excelBody.='<tr>'.$tableRow.'</tr>';
    
    //$tableRow.='<td class="actions">';
    //if ($bool_edit_permission){
    //    $tableRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $recipe['Recipe']['id']]);
    //}
    //$tableRow.='</td>';
    
		$tableBody.='<tr>'.$tableRow.'</tr>';
	}

	$totalRow='';
  /*
	$totalRow.='<tr class="totalrow">';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
	$totalRow.='</tr>';
  */
	$tableBody='<tbody>'.$totalRow.$tableBody.$totalRow.'</tbody>';
	$tableId='';
	$pageOutput='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$tableHeader.$tableBody.'</table>';
	echo $pageOutput;
	$excelOutput='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$excelHeader.$excelBody.'</table>';
	$_SESSION['resumen'] = $excelOutput;
?>
</div>