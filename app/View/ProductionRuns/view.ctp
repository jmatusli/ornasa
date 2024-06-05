<?php 
	echo "<div class='productionRuns view'>";
	$outputheader="";
	$outputheader.="<h2>".__('Production Run')." ".$productionRun['ProductionRun']['production_run_code']."</h2>";
	$outputheader.="<dl>";
		$outputheader.="<dt>".__('Production Run Code')."</dt>";
		$outputheader.="<dd>";
		$outputheader.=h($productionRun['ProductionRun']['production_run_code'])."&nbsp;";
		$outputheader.="</dd>";
		$outputheader.="<dt>".__('Date')."</dt>";
		$outputheader.="<dd>";
		$productionrundate= new DateTime($productionRun['ProductionRun']['production_run_date']); 
		$outputheader.=$productionrundate->format('d-m-Y'); 
		$outputheader.="</dd>";
		$outputheader.="<dt>".__('Product')."</dt>";
		$outputheader.="<dd>";
		$outputheader.=$this->Html->link($productionRun['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'view', $productionRun['FinishedProduct']['id']))."&nbsp;";
		$outputheader.="</dd>";
		$outputheader.="<dt>".__('Product Quantity')."</dt>";
		$outputheader.="<dd>";
		$outputheader.="".number_format($productionRun['ProductionRun']['raw_material_quantity'],0,".",",")."&nbsp;";
		$outputheader.="</dd>";
    foreach($usedConsumiblesArray as $bagId=>$bagData){
      $outputheader.="<dt>".__('Cantidad ').$bagData['consumible_name']."</dt>";
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
		$outputheader.="".$this->Html->link($productionRun['Shift']['name'], array('controller' => 'shifts', 'action' => 'view', $productionRun['Shift']['id']))."&nbsp;";
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
	echo $outputheader;
	echo "</div>";
	echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,'Preformas en bodega'); 
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<?php 
		$namepdf="Produccion_".$productionRun['ProductionRun']['production_run_code'];
		echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'viewPdf','ext'=>'pdf', $productionRun['ProductionRun']['id'],$namepdf))."</li>";
		
		if ($bool_add_permission){ 
			echo "<li>".$this->Html->link(__('New Production Run'), array('action' => 'add'))."</li>";
		}
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Production Run'), array('action' => 'edit', $productionRun['ProductionRun']['id']))."</li>";
		} 
		echo "<br/>";
	
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $productionRun['ProductionRun']['id']), array(), __('Are you sure you want to delete # %s?', $productionRun['ProductionRun']['production_run_code']))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Production Runs'), array('action' => 'index'))."</li>";
		
		echo "<h3>".__('Configuration Options')."</h3>";
		if ($bool_operator_totalproductionreport_permission) {
			echo "<li>".$this->Html->link('Reporte Producción Total', array('controller' => 'operators', 'action' => 'reporteProduccionTotal'))." </li>";
			echo "<br/>";
		}
		if ($bool_producttype_index_permission){
			echo "<li>".$this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index'))." </li>";
		}
		if ($bool_producttype_add_permission){
			echo "<li>".$this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add'))." </li>";
		}
		if ($bool_machine_index_permission){
			echo "<li>".$this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index'))." </li>";
		}
		if ($bool_machine_add_permission){
			echo "<li>".$this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add'))." </li>";
		}
		if ($bool_operator_index_permission){
			echo "<li>".$this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index'))." </li>";
		}
		if ($bool_operator_add_permission){
			echo "<li>".$this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add'))." </li>";
		}
		if ($bool_shift_index_permission){
			echo "<li>".$this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index'))." </li>";
		}
		if ($bool_shift_add_permission){
			echo "<li>".$this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add'))." </li>";
		}
		
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
	if (!empty($productionRun['ProductionMovement'])){
		$rawrows="";
		$finishedrows="";
    $consumibleRows="";
		$totalPrice=0;
		$totalProducts=0;
		$totalUsed=0;
    $totalUsedConsumibles=0;
		//$totalRemaining=0;
		foreach ($productionRun['ProductionMovement'] as $productionMovement){
			if ($productionMovement['bool_input']){
				if ($productionMovement['product_quantity']>0){
          //pr($productionMovement);
          if ($productionMovement['StockItem']['Product']['product_type_id']==PRODUCT_TYPE_PREFORMA){
            $totalUsed+=$productionMovement['product_quantity'];
            //$totalRemaining+=$productionMovement['StockItem']['remaining_quantity'];
            $rawrows.="<tr>";
              $rawrows.="<td>".$productionMovement['StockItem']['name']."</td>";
              $rawrows.="<td>".$productionRun['RawMaterial']['name']."</td>";
              $rawrows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
              $rawrows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],2,".",",")."</td>";
              //$rawrows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
            $rawrows.="</tr>";
          }
          else {
            $totalUsedConsumibles+=$productionMovement['product_quantity'];
            //$totalRemainingConsumibles+=$productionMovement['StockItem']['remaining_quantity'];
            $consumibleRows.="<tr>";
              $consumibleRows.="<td>".$productionMovement['StockItem']['name']."</td>";
              $consumibleRows.="<td>".$productionMovement['StockItem']['Product']['name']."</td>";
              $consumibleRows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
              $consumibleRows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],2,".",",")."</td>";
              //$consumibleRows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
            $consumibleRows.="</tr>";
          }
				}
			}
			else {
				$resultquality="";
				switch ($productionMovement['StockItem']['production_result_code_id']){
					case 1:
						$resultquality="A";
						break;
					case 2:
						$resultquality="B";
						break;
					case 3:
						$resultquality="C";
						break;
					default:
						$resultquality=$productionMovement['StockItem']['production_result_code_id'];
						
				}	
				
				$finishedrows.="<tr>";
					$finishedrows.="<td>".$productionMovement['StockItem']['name']."</td>";
					$finishedrows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
					$finishedrows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
					$finishedrows.="<td class='centered'>".$resultquality."</td>";
					$finishedrows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],2,".",",")."</td>";
					$totalPriceProduct=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
					$finishedrows.="<td class='centered'>C$ ".number_format($totalPriceProduct,2,".",",")."</td>";
					$finishedrows.="<td>".$productionRun['RawMaterial']['name']."</td>";
				$finishedrows.="</tr>";
				$totalPrice+=$totalPriceProduct;
				$totalProducts+=$productionMovement['product_quantity'];
			}
    }
		$rawrows.="<tr class='totalrow'>";
			$rawrows.="<td>Total</td>";
			$rawrows.="<td></td>";
			$rawrows.="<td class='centered'>".number_format($totalUsed,0,".",",")."</td>";
			$rawrows.="<td></td>";
      //$rawrows.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";
      $rawrows.="<td></td>";
		$rawrows.="</tr>";
    $consumibleRows.="<tr class='totalrow'>";
			$consumibleRows.="<td>Total</td>";
			$consumibleRows.="<td></td>";
			$consumibleRows.="<td class='centered'>".number_format($totalUsedConsumibles,0,".",",")."</td>";
			$consumibleRows.="<td></td>";
      //$consumibleRows.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";
      $consumibleRows.="<td></td>";
		$consumibleRows.="</tr>";
		$finishedrows.="<tr class='totalrow'>";
			$finishedrows.="<td>Total</td>";
			$finishedrows.="<td class='centered'></td>";
			$finishedrows.="<td class='centered'>".number_format($totalProducts,0,".",",")."</td>";
			$finishedrows.="<td class='centered'></td>";
			$finishedrows.="<td class='centered'></td>";
			$finishedrows.="<td class='centered'>C$ ".number_format($totalPrice,4,".",",")."</td>";
			$finishedrows.="<td></td>";
		$finishedrows.="</tr>";
				
		$outputraw="<h3>".__('Raw Materials used in Production Run')."</h3>";
		$outputraw.="<table cellpadding = '0' cellspacing = '0'>";
			$outputraw.="<thead>";
				$outputraw.="<tr>";
					$outputraw.="<th>".__('Identificación Lote')."</th>";
					$outputraw.="<th>".__('Materia Prima Usada')."</th>";
					$outputraw.="<th class='centered'>".__('Used Quantity')."</th>";
					$outputraw.="<th class='centered'>".__('Unit Cost')."</th>";
					//$outputraw.="<th class='centered'>".__('Cantidad que sobre en lote')."</th>";
				$outputraw.="</tr>";
			$outputraw.="</thead>";
			$outputraw.="<tbody>";
				$outputraw.=$rawrows;
			$outputraw.="</tbody>";
		$outputraw.="</table>";
		echo $outputraw;
		
    $outputproduced="<h3>".__('Products Produced in Production Run')."</h3>";
		$outputproduced.="<table cellpadding = '0' cellspacing = '0'>";
			$outputproduced.="<thead>";
				$outputproduced.="<tr>";
					$outputproduced.="<th>".__('Identificación Lote')."</th>";
					$outputproduced.="<th>".__('Producto Fabricado')."</th>";
					$outputproduced.="<th class='centered'>".__('Produced Quantity')."</th>";
					$outputproduced.="<th class='centered'>".__('Quality')."</th>";
					$outputproduced.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputproduced.="<th class='centered'>".__('Total Cost')."</th>";
					$outputproduced.="<th>".__('Raw Material')."</th>";
				$outputproduced.="</tr>";
			$outputproduced.="</thead>";
			$outputproduced.="<tbody>";
				$outputproduced.=$finishedrows;
			$outputproduced.="</tbody>";
		$outputproduced.="</table>";
		echo $outputproduced;
		
    $outputConsumible="<h3>".__('Consumibles utilizados  en Orden de Producción')."</h3>";
		$outputConsumible.="<table cellpadding = '0' cellspacing = '0'>";
			$outputConsumible.="<thead>";
				$outputConsumible.="<tr>";
					$outputConsumible.="<th>".__('Identificación Lote')."</th>";
					$outputConsumible.="<th>".__('Consumible Usada')."</th>";
					$outputConsumible.="<th class='centered'>".__('Used Quantity')."</th>";
					$outputConsumible.="<th class='centered'>".__('Unit Cost')."</th>";
					//$outputConsumible.="<th class='centered'>".__('Cantidad que sobre en lote')."</th>";
				$outputConsumible.="</tr>";
			$outputConsumible.="</thead>";
			$outputConsumible.="<tbody>";
				$outputConsumible.=$consumibleRows;
			$outputConsumible.="</tbody>";
		$outputConsumible.="</table>";
		echo $outputConsumible;
    
		$css="<style>.centered{	text-align:center;		}	</style>";
		$output=$css.$outputheader.$outputraw.$outputproduced;
		$_SESSION['output_produccion']=$output;
	}
	
?>
	<!--div class="actions">
		<ul>
			<!--li><?php // echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li-->
		</ul>
	</div-->
</div>
