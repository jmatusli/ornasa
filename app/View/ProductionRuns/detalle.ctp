<div class="productionRuns view fullwidth">
<?php
  $productionRunDateTime= new DateTime($productionRun['ProductionRun']['production_run_date']); 
  //pr($productionRun);

  $outputheader="";
  $outputheader.="<h1>".__('Production Run')." ".$productionRun['ProductionRun']['production_run_code']."</h1>";
  $outputheader.='<dl class="dl100">';
    $outputheader.="<dt>".__('Production Run Code')."</dt>";
    $outputheader.="<dd>";
    $outputheader.=h($productionRun['ProductionRun']['production_run_code'])."&nbsp;";
    $outputheader.="</dd>";
    $outputheader.='<dt>Planta</dt>';
    $outputheader.='<dd>'.$productionRun['Plant']['name'].'</dd>';
    $outputheader.="<dt>".__('Date')."</dt>";
    $outputheader.="<dd>".$productionRunDateTime->format('d-m-Y')."</dd>";
    $outputheader.="<dt>".__('Product')."</dt>";
    $outputheader.="<dd>";
    $outputheader.=$this->Html->link($productionRun['FinishedProduct']['name'], ['controller' => 'products', 'action' => 'view', $productionRun['FinishedProduct']['id']])."&nbsp;";
    $outputheader.="</dd>";
    $outputheader.="<dt>".__('Product Quantity')."</dt>";
    $outputheader.="<dd>";
    $outputheader.="".number_format($productionRun['ProductionRun']['finished_product_quantity'],0,".",",")."&nbsp;";
    $outputheader.="</dd>";
    foreach($usedConsumablesArray as $bagId=>$bagData){
      $outputheader.="<dt>".__('Cantidad ').$bagData['consumable_name']."</dt>";
      $outputheader.="<dd>";
      $outputheader.="".number_format($bagData['product_quantity'],0,".",",")."&nbsp;";
      $outputheader.="</dd>";
    }
    
    $outputheader.="<dt>".__('Machine')."</dt>";
    $outputheader.="<dd>";
    $outputheader.=$this->Html->link($productionRun['Machine']['name'], array('controller' => 'machines', 'action' => 'view', $productionRun['Machine']['id']))."&nbsp;";
    $outputheader.="</dd>";
    $outputheader.="<dt>".__('Operator')."</dt>";
    $outputheader.="<dd>";
    $outputheader.=$this->Html->link($productionRun['Operator']['name'], array('controller' => 'operators', 'action' => 'view', $productionRun['Operator']['id']))."&nbsp;";
    $outputheader.="</dd>";
    $outputheader.="<dt>".__('Shift')."</dt>";
    $outputheader.="<dd>";
    $outputheader.="".$this->Html->link($productionRun['Shift']['name'], ['controller' => 'shifts', 'action' => 'view', $productionRun['Shift']['id']])."&nbsp;";
    $outputheader.="</dd>";
    $outputheader.="<dt>".__('Energy Use')."</dt>";
    $outputheader.="<dd>";
    //$outputheader.=number_format($productionRun['ProductionRun']['meter_finish']-$productionRun['ProductionRun']['meter_start'],0,".",",")."&nbsp;";
    $outputheader.=round($energyConsumption,2);
    $outputheader.="</dd>";
    $outputheader.="<dt>".__('Verificada?')."</dt>";
    $outputheader.="<dd>".($productionRun['ProductionRun']['bool_verified']?__('Yes'):__('No'))."</dd>";
    $outputheader.="<dt>".__('Anulada?')."</dt>";
    $outputheader.="<dd>".($productionRun['ProductionRun']['bool_annulled']?__('Yes'):__('No'))."</dd>";
    $outputheader.="<dt>".__('Incidence')."</dt>";
    $outputheader.="<dd>".(empty($productionRun['Incidence']['name'])?'No incidencias':$productionRun['Incidence']['name'])."</dd>";
    $outputheader.="<dt>".__('Comment')."</dt>";
    $outputheader.="<dd>".(!empty($productionRun['ProductionRun']['comment'])?html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$productionRun['ProductionRun']['comment'])):'-')."</dd>";
	$outputheader.="</dl>";
 
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo $outputheader;
      echo '</div>';
      echo '<div class="col-sm-3">';
        echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,$productionRun['Plant']['id'],['header_title'=>'Preformas en bodega']); 
      echo '</div>';
      echo '<div class="col-sm-3">';
        echo '<h2>'.__('Actions').'</h2>';
      $namepdf="Produccion_".$productionRun['ProductionRun']['production_run_code'];
        
      echo '<ul>';
        echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'detallePdf','ext'=>'pdf', $productionRun['ProductionRun']['id'],$namepdf])."</li>";		
        if ($bool_add_permission){ 
          echo "<li>".$this->Html->link(__('New Production Run'), ['action' => 'crear'])."</li>";
        }
        echo '</ul>';
        if ($editabilityData['boolEditable']){
          echo '<ul>';  
          if ($bool_edit_permission){ 
            echo "<li>".$this->Html->link(__('Edit Production Run'), ['action' => 'editar', $productionRun['ProductionRun']['id']])."</li>";
          } 
          echo '</ul>';  
        }
        else {
          echo '<h3>'.$editabilityData['message'].'</h3>';
        }
        echo '<ul>';
          echo "<li>".$this->Html->link(__('List Production Runs'), ['action' => 'resumen'])."</li>";
          
          echo "<h3>".__('Configuration Options')."</h3>";
          if ($bool_operator_totalproductionreport_permission) {
            echo "<li>".$this->Html->link('Reporte Producción Total', ['controller' => 'operators', 'action' => 'reporteProduccionTotal'])." </li>";
            echo "<br/>";
          }
          if ($bool_producttype_index_permission){
            echo "<li>".$this->Html->link(__('List Product Types'), ['controller' => 'productTypes', 'action' => 'index'])." </li>";
          }
          if ($bool_producttype_add_permission){
            echo "<li>".$this->Html->link(__('New Product Type'), ['controller' => 'productTypes', 'action' => 'add'])." </li>";
          }
          if ($bool_machine_index_permission){
            echo "<li>".$this->Html->link(__('List Machines'), ['controller' => 'machines', 'action' => 'resumen'])." </li>";
          }
          if ($bool_machine_add_permission){
            echo "<li>".$this->Html->link(__('New Machine'), ['controller' => 'machines', 'action' => 'crear'])." </li>";
          }
          if ($bool_operator_index_permission){
            echo "<li>".$this->Html->link(__('List Operators'), ['controller' => 'operators', 'action' => 'resumen'])." </li>";
          }
          if ($bool_operator_add_permission){
            echo "<li>".$this->Html->link(__('New Operator'), ['controller' => 'operators', 'action' => 'crear'])." </li>";
          }
          if ($bool_shift_index_permission){
            echo "<li>".$this->Html->link(__('List Shifts'), ['controller' => 'shifts', 'action' => 'index'])." </li>";
          }
          if ($bool_shift_add_permission){
            echo "<li>".$this->Html->link(__('New Shift'), ['controller' => 'shifts', 'action' => 'add'])." </li>";
          }
          
        echo "</ul>";
      echo '</div>';
    echo '</div>';
  echo '</div>';


?>
</div>
<div class="related">
<?php 
	if (!empty($productionRun['ProductionMovement'])){
		$rawRows="";
    $rawRowsMill="";
    $totalCostRaw=0;
    $totalCostRawMill=0;
    $totalUsed=0;
    $totalUsedMill=0;
    
    $finishedRows="";
    $totalPrice=0;
		$totalProducts=0;
    
    $millConversionProductRows="";
    $totalCostRecycled=0;
    $totalRecycled=0;
    
    $productionLossRows="";
    $totalCostLoss=0;
    $totalLoss=0;
		
    $consumableRows="";
    $bagRows="";
    $totalCostConsumables=0;
    $totalCostConsumablesBags=0;
		$totalUsedConsumables=0;
    $totalUsedConsumablesBags=0;
    
		//$totalRemaining=0;
    
		foreach ($productionRun['ProductionMovement'] as $productionMovement){
			if ($productionMovement['bool_input']){
				if ($productionMovement['product_quantity'] > 0){
          //pr($productionMovement);
          //if ($productionMovement['StockItem']['Product']['product_type_id'] == PRODUCT_TYPE_PREFORMA){
          if ($productionMovement['StockItem']['Product']['product_nature_id'] == PRODUCT_NATURE_RAW){
            if ($productionMovement['bool_recycling'] == 0){
              $totalUsed+=$productionMovement['product_quantity'];
              $costRaw=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
                
              //$totalRemaining+=$productionMovement['StockItem']['remaining_quantity'];
              $rawRows.="<tr>";
                $rawRows.="<td>".$productionMovement['StockItem']['name']."</td>";
                $rawRows.="<td>".$productionMovement['StockItem']['Product']['name']."</td>";
                $rawRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],3,".",",")."</td>";
                $rawRows.='<td class="centered">'.(empty($productionMovement['Unit']['id'])?"Und":$productionMovement['Unit']['abbreviation']).'</td>';
                $rawRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
                $rawRows.="<td class='centered'>C$ ".number_format($costRaw,2,".",",")."</td>";
                //$rawRows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
              $rawRows.="</tr>";
              
              $totalCostRaw+=$costRaw;
            }
            else {
              // input for mill
              $totalUsedMill+=$productionMovement['product_quantity'];
              $costRawMill=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
                
              //$totalRemaining+=$productionMovement['StockItem']['remaining_quantity'];
              $rawRowsMill.="<tr>";
                $rawRowsMill.="<td>".$productionMovement['StockItem']['name']."</td>";
                $rawRowsMill.="<td>".$productionMovement['StockItem']['Product']['name']."</td>";
                $rawRowsMill.="<td class='centered'>".number_format($productionMovement['product_quantity'],3,".",",")."</td>";
                $rawRowsMill.='<td class="centered">'.(empty($productionMovement['Unit']['id'])?"Und":$productionMovement['Unit']['abbreviation']).'</td>';
                $rawRowsMill.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
                $rawRowsMill.="<td class='centered'>C$ ".number_format($costRaw,2,".",",")."</td>";
                //$rawRows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
              $rawRowsMill.="</tr>";
              
              $totalCostRawMill+=$costRawMill;
            }
          }
          // products are consumibles
          elseif ($productionMovement['StockItem']['Product']['product_type_id'] == PRODUCT_TYPE_BAGS) {
            $totalUsedConsumablesBags+=$productionMovement['product_quantity'];
            $totalCostConsumablesBags+=($productionMovement['product_quantity']*$productionMovement['product_unit_price']);
            //$totalRemainingConsumables+=$productionMovement['StockItem']['remaining_quantity'];
            
            $bagRows.="<tr>";
              $bagRows.="<td>".$productionMovement['StockItem']['name']."</td>";
              $bagRows.="<td>".$productionMovement['StockItem']['Product']['name']."</td>";
              $bagRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
              $bagRows.='<td class="centered">'.(empty($productionMovement['Unit']['id'])?"Und":$productionMovement['Unit']['abbreviation']).'</td>';
              $bagRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
              $bagRows.="<td class='centered'>C$ ".number_format($productionMovement['product_quantity']*$productionMovement['product_unit_price'],2,".",",")."</td>";
            $bagRows.="</tr>";
          }
          else {
            $totalUsedConsumables+=$productionMovement['product_quantity'];
            $totalCostConsumables+=($productionMovement['product_quantity']*$productionMovement['product_unit_price']);
            //$totalRemainingConsumables+=$productionMovement['StockItem']['remaining_quantity'];
            $consumableRows.="<tr>";
              $consumableRows.="<td>".$productionMovement['StockItem']['name']."</td>";
              $consumableRows.="<td>".$productionMovement['StockItem']['Product']['name']."</td>";
              $consumableRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
              $consumableRows.='<td class="centered">'.(empty($productionMovement['Unit']['id'])?"Und":$productionMovement['Unit']['abbreviation']).'</td>';
              $consumableRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
              $consumableRows.="<td class='centered'>C$ ".number_format($productionMovement['product_quantity']*$productionMovement['product_unit_price'],2,".",",")."</td>";
              //$consumableRows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
            $consumableRows.="</tr>";
          }
				}
			}
			else {
        $productionResultCodeId=$productionMovement['StockItem']['production_result_code_id'];
				$resultquality=$productionResultCodes[$productionMovement['StockItem']['production_result_code_id']];
        //echo 'resultquality is '.$resultquality.'<br/>';
				if ($productionResultCodeId == PRODUCTION_RESULT_CODE_MILL){
          $millConversionProductRows.="<tr>";
            $millConversionProductRows.="<td>".$productionMovement['StockItem']['name']."</td>";
            $millConversionProductRows.="<td>".$productionRun['Recipe']['MillConversionProduct']['name']."</td>";
            $millConversionProductRows.="<td class='centered'>".$resultquality."</td>";
            $millConversionProductRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
            $millConversionProductRows.='<td class="centered">'.$productionMovement['Unit']['abbreviation'].'</td>';
            $millConversionProductRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
            $costRecycled=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
            $millConversionProductRows.="<td class='centered'>C$ ".number_format($costRecycled,2,".",",")."</td>";
            $millConversionProductRows.="<td>".$productionRun['RawMaterial']['name']."</td>";
          $millConversionProductRows.="</tr>";
          
          $totalCostRecycled+=$costRecycled;
          $totalRecycled+=$productionMovement['product_quantity'];
          //pr($millConversionProductRows);
        }
        else {
          $finishedRows.="<tr>";
            $finishedRows.="<td>".$productionMovement['StockItem']['name']."</td>";
            $finishedRows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
            $finishedRows.="<td class='centered'>".($productionRun['Plant']['id'] == PLANT_COLINAS? (round($totalUsed/$productionRun['ProductionRun']['finished_product_quantity'],0)." GR"):$resultquality)."</td>";
            $finishedRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
            $finishedRows.='<td class="centered">'.(empty($productionMovement['Unit']['id'])?"Und":$productionMovement['Unit']['abbreviation']).'</td>';
            $finishedRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],4,".",",")."</td>";
            $totalPriceProduct=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
            $finishedRows.="<td class='centered'>C$ ".number_format($totalPriceProduct,2,".",",")."</td>";
            if ($plantId == PLANT_SANDINO){
              $finishedRows.="<td>".$productionRun['RawMaterial']['name']."</td>";
            }
          $finishedRows.="</tr>";
          
          $totalPrice+=$totalPriceProduct;
          $totalProducts+=$productionMovement['product_quantity'];
        }
			}
    }
		
    if (!empty($productionRun['ProductionLoss'])){
      foreach ($productionRun['ProductionLoss'] as $productionLoss){			
				$resultquality=$productionResultCodes[$productionLoss['production_result_code_id']];
        $productionLossRows.="<tr>";
          $productionLossRows.="<td class='centered'>".$resultquality."</td>";
          $productionLossRows.="<td class='centered'>".number_format($productionLoss['product_quantity'],0,".",",")."</td>";
          $productionLossRows.='<td class="centered">'.$productionLoss['Unit']['abbreviation'].'</td>';
          $productionLossRows.="<td class='centered'>C$ ".number_format($productionLoss['product_unit_price'],4,".",",")."</td>";
          $costLoss=$productionLoss['product_unit_price']*$productionLoss['product_quantity'];
          $productionLossRows.="<td class='centered'>C$ ".number_format($costLoss,2,".",",")."</td>";
          
        $productionLossRows.="</tr>";
        $totalCostLoss+=$costLoss;
        $totalLoss+=$productionLoss['product_quantity'];
      }
      $productionLossRows.="<tr class='totalrow'>";
			$productionLossRows.="<td>Total</td>";
			$productionLossRows.="<td class='centered'>".number_format($totalLoss,3,".",",")."</td>";
			$productionLossRows.="<td class='centered'></td>";
			$productionLossRows.="<td class='centered'></td>";
			$productionLossRows.="<td class='centered'>C$ ".number_format($totalCostLoss,4,".",",")."</td>";
		$productionLossRows.="</tr>";
    }  
    
    $rawRows.="<tr class='totalrow'>";
			$rawRows.="<td>Total</td>";
			$rawRows.="<td></td>";
			$rawRows.="<td class='centered'>".number_format($totalUsed,0,".",",")."</td>";
      $rawRows.="<td></td>";
      $rawRows.="<td></td>";
      $rawRows.="<td class='centered'>C$ ".number_format($totalCostRaw,4,".",",")."</td>";
      //$rawRows.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";     
		$rawRows.="</tr>";
    
     $rawRowsMill.="<tr class='totalrow'>";
			$rawRowsMill.="<td>Total Usada Molino</td>";
			$rawRowsMill.="<td></td>";
			$rawRowsMill.="<td class='centered'>".number_format($totalUsedMill,0,".",",")."</td>";
      $rawRowsMill.="<td></td>";
      $rawRowsMill.="<td></td>";
      $rawRowsMill.='<td class="centered">C$ '.number_format($totalCostRawMill,4,".",",").'</td>';
      //$rawRowsMill.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";     
		$rawRowsMill.="</tr>";
    
    $consumableRows.="<tr class='totalrow'>";
			$consumableRows.="<td>Total</td>";
			$consumableRows.="<td></td>";
			$consumableRows.="<td class='centered'>".number_format($totalUsedConsumables,0,".",",")."</td>";
			$consumableRows.="<td></td>";
      $consumableRows.="<td></td>";
      //$consumableRows.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";
      $consumableRows.='<td class="centered">C$ '.number_format($totalCostConsumables,4,".",",").'</td>';
		$consumableRows.="</tr>";
    
    $bagRows.="<tr class='totalrow'>";
			$bagRows.="<td>Total</td>";
			$bagRows.="<td></td>";
			$bagRows.="<td class='centered'>".number_format($totalUsedConsumablesBags,0,".",",")."</td>";
			$bagRows.="<td></td>";
      $bagRows.="<td></td>";
      $bagRows.='<td class="centered">C$ '.number_format($totalCostConsumablesBags,4,".",",").'</td>';
		$bagRows.="</tr>";
    
		$finishedRows.="<tr class='totalrow'>";
			$finishedRows.="<td>Total</td>";
			$finishedRows.="<td class='centered'></td>";
      $finishedRows.="<td class='centered'></td>";
			$finishedRows.="<td class='centered'>".number_format($totalProducts,3,".",",")."</td>";
			$finishedRows.="<td class='centered'></td>";
			$finishedRows.="<td class='centered'></td>";
			$finishedRows.="<td class='centered'>C$ ".number_format($totalPrice,4,".",",")."</td>";
      if ($plantId == PLANT_SANDINO){
        $finishedRows.="<td></td>";
      }
		$finishedRows.="</tr>";
    
    $millConversionProductRows.="<tr class='totalrow'>";
			$millConversionProductRows.="<td>Total</td>";
			$millConversionProductRows.="<td class='centered'></td>";
      $millConversionProductRows.="<td class='centered'></td>";
			$millConversionProductRows.="<td class='centered'>".number_format($totalRecycled,3,".",",")."</td>";
			$millConversionProductRows.="<td class='centered'></td>";
			$millConversionProductRows.="<td class='centered'></td>";
			$millConversionProductRows.="<td class='centered'>C$ ".number_format($totalCostRecycled,4,".",",")."</td>";
			$millConversionProductRows.="<td></td>";
		$millConversionProductRows.="</tr>";
				
    // PRINT TABLES

		$outputproduced="<h3>".__('Products Produced in Production Run')."</h3>";
		$outputproduced.="<table cellpadding = '0' cellspacing = '0'>";
			$outputproduced.="<thead>";
				$outputproduced.="<tr>";
					$outputproduced.="<th>".__('Identificación Lote')."</th>";
					$outputproduced.="<th>".__('Producto Fabricado')."</th>";
					$outputproduced.="<th class='centered'>".($productionRun['Plant']['id'] == PLANT_COLINAS?"Peso":"Calidad")."</th>";
          $outputproduced.="<th class='centered'>".__('Produced Quantity')."</th>";
          $outputproduced.='<th class="centered">Und</th>';
					$outputproduced.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputproduced.="<th class='centered'>".__('Total Cost')."</th>";
          if ($plantId == PLANT_SANDINO){
            $outputproduced.="<th>".__('Raw Material')."</th>";
          }
				$outputproduced.="</tr>";
			$outputproduced.="</thead>";
			$outputproduced.="<tbody>";
				$outputproduced.=$finishedRows;
			$outputproduced.="</tbody>";
		$outputproduced.="</table>";
		echo $outputproduced;
    
    $outputConsumable="<h3>".__('Consumibles utilizados  en Orden de Producción')."</h3>";
		$outputConsumable.="<table cellpadding = '0' cellspacing = '0'>";
			$outputConsumable.="<thead>";
				$outputConsumable.="<tr>";
					$outputConsumable.="<th>".__('Identificación Lote')."</th>";
					$outputConsumable.="<th>".__('Consumable Usada')."</th>";
					$outputConsumable.="<th class='centered'>".__('Used Quantity')."</th>";
          $outputConsumable.='<th class="centered">Und</th>';
					$outputConsumable.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputConsumable.="<th class='centered'>Costo Total</th>";
				$outputConsumable.="</tr>";
			$outputConsumable.="</thead>";
			$outputConsumable.="<tbody>";
				$outputConsumable.=$consumableRows;
			$outputConsumable.="</tbody>";
		$outputConsumable.="</table>";
		echo $outputConsumable;
    
    echo '<h3>Cálculo de precio unitario</h3>';
    $unitCostTableRows='';
    $unitCostTableRows.='<tr>';
      $unitCostTableRows.='<th>Costo total materia prima</th>';
      $unitCostTableRows.='<td class="centered">C$ '.number_format($totalCostRaw,2,".",",").'</td>';
    $unitCostTableRows.='</tr>';
    $unitCostTableRows.='<tr>';
      $unitCostTableRows.='<th>Costo total consumibles</th>';
      $unitCostTableRows.='<td class="centered">C$ '.number_format($totalCostConsumables,2,".",",").'</td>';
    $unitCostTableRows.='</tr>';
    $unitCostTableRows.='<tr>';
      $unitCostTableRows.='<th>Costo total neto</th>';
      $unitCostTableRows.='<td class="centered">C$ '.number_format($totalCostRaw+$totalCostConsumables,2,".",",").'</td>';
    $unitCostTableRows.='</tr>';
    $unitCostTableRows.='<tr>';
      $unitCostTableRows.='<th>Cantidad Productos Fabricados</th>';
      $unitCostTableRows.='<td class="centered">'.$productionRun['ProductionRun']['finished_product_quantity'].'</td>';
    $unitCostTableRows.='</tr>';
    $unitCostTableRows.='<tr>';
      $unitCostTableRows.='<th>Costo Unitario</th>';
      $unitCostTableRows.='<td class="centered">C$ '.number_format(($totalCostRaw+$totalCostConsumables)/$productionRun['ProductionRun']['finished_product_quantity'],4,".",",").'</td>';
    $unitCostTableRows.='</tr>';
    $unitCostTable='<table id="calculo_costo_unitario">'.$unitCostTableRows.'</table>';
    echo $unitCostTable;
    
    if (!empty($millConversionProductRows)){
      $millConversionProductTable='<h3>Productos reciclados en molino</h3>';
      $millConversionProductTable.='<table>';
        $millConversionProductTable.='<thead>';
          $millConversionProductTable.='<tr>';
            $millConversionProductTable.='<th>Identificación Lote</th>';
            $millConversionProductTable.='<th>Producto Reciclado</th>';
            $millConversionProductTable.='<th class="centered">'.__("Quality").'</th>';
            $millConversionProductTable.='<th class="centered"># Reciclado</th>';
            $millConversionProductTable.='<th class="centered">Und</th>';
            $millConversionProductTable.='<th class="centered">'.__("Unit Cost").'</th>';
            $millConversionProductTable.='<th class="centered">'.__("Total Cost").'</th>';
          $millConversionProductTable.='</tr>';
        $millConversionProductTable.='</thead>';
        $millConversionProductTable.='<tbody>';
          $millConversionProductTable.=$millConversionProductRows;
        $millConversionProductTable.='</tbody>';
      $millConversionProductTable.='</table>';
      echo $millConversionProductTable;
    }
    
    if (!empty($productionLossRows)){
      $productionLossTable='<h3>Pérdidas en merma</h3>';
      $productionLossTable.='<table>';
        $productionLossTable.='<thead>';
          $productionLossTable.='<tr>';
            $productionLossTable.='<th class="centered">'.__("Quality").'</th>';
            $productionLossTable.='<th class="centered"># Reciclado</th>';
            $productionLossTable.='<th class="centered">Und</th>';
            $productionLossTable.='<th class="centered">'.__("Unit Cost").'</th>';
            $productionLossTable.='<th class="centered">'.__("Total Cost").'</th>';
          $productionLossTable.='</tr>';
        $productionLossTable.='</thead>';
        $productionLossTable.='<tbody>';
          $productionLossTable.=$productionLossRows;
        $productionLossTable.='</tbody>';
      $productionLossTable.='</table>';
      echo $productionLossTable;
		}
    
    $outputConsumableBags="<h3>Bolsas utilizadas  en Proceso de Producción</h3>";
		$outputConsumableBags.="<table>";
			$outputConsumableBags.="<thead>";
				$outputConsumableBags.="<tr>";
					$outputConsumableBags.="<th>".__('Identificación Lote')."</th>";
					$outputConsumableBags.="<th>Bolsa</th>";
					$outputConsumableBags.="<th class='centered'>".__('Used Quantity')."</th>";
          $outputConsumableBags.='<th class="centered">Und</th>';
					$outputConsumableBags.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputConsumableBags.="<th class='centered'>Costo Total</th>";
				$outputConsumableBags.="</tr>";
			$outputConsumableBags.="</thead>";
			$outputConsumableBags.="<tbody>";
				$outputConsumableBags.=$bagRows;
			$outputConsumableBags.="</tbody>";
		$outputConsumableBags.="</table>";
		echo $outputConsumableBags;
    
    $outputraw="<h3>Materias Primas Usadas para Producción</h3>";
		$outputraw.="<table cellpadding = '0' cellspacing = '0'>";
			$outputraw.="<thead>";
				$outputraw.="<tr>";
					$outputraw.="<th>".__('Identificación Lote')."</th>";
					$outputraw.="<th>".__('Materia Prima Usada')."</th>";
					$outputraw.="<th class='centered'>".__('Used Quantity')."</th>";
          $outputraw.='<th class="centered">Und</th>';
					$outputraw.="<th class='centered'>".__('Unit Cost')."</th>";
          $outputraw.="<th class='centered'>".__('Total Cost')."</th>";
					//$outputraw.="<th class='centered'>".__('Cantidad que sobre en lote')."</th>";
				$outputraw.="</tr>";
			$outputraw.="</thead>";
			$outputraw.="<tbody>";
				$outputraw.=$rawRows;
			$outputraw.="</tbody>";
		$outputraw.="</table>";
		echo $outputraw;
    
    if (!empty($outputRawMill)){  
      $outputRawMill="<h3>Materias Primas Usadas para Molino</h3>";
      $outputRawMill.="<table cellpadding = '0' cellspacing = '0'>";
        $outputRawMill.="<thead>";
          $outputRawMill.="<tr>";
            $outputRawMill.="<th>".__('Identificación Lote')."</th>";
            $outputRawMill.="<th>".__('Materia Prima Usada')."</th>";
            $outputRawMill.="<th class='centered'>".__('Used Quantity')."</th>";
            $outputRawMill.='<th class="centered">Und</th>';
            $outputRawMill.="<th class='centered'>".__('Unit Cost')."</th>";
            $outputRawMill.="<th class='centered'>".__('Total Cost')."</th>";
          $outputRawMill.="</tr>";
        $outputRawMill.="</thead>";
        $outputRawMill.="<tbody>";
          $outputRawMill.=$rawRowsMill;
        $outputRawMill.="</tbody>";
      $outputRawMill.="</table>";
      echo $outputRawMill;
    }  
    
		$css="<style>.centered{	text-align:center;		}	</style>";
		$output=$css.$outputheader.$outputproduced.$outputraw;
		$_SESSION['output_produccion']=$output;
	}
	
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($bool_delete_permission && $editabilityData['boolEditable']){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Proceso de Producción'), ['action' => 'delete', $productionRun['ProductionRun']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar el proceso de producción # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productionRun['ProductionRun']['production_run_code']));
  }
?>
</div>