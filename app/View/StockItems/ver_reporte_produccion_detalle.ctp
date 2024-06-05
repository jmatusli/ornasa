<div class="stockItems view report">
<?php 
	echo "<h2>".__('Reporte de Producción Detalle')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo $this->Form->input('Report.display_option_id',array('label'=>__('Mostrar Datos'),'default'=>$displayOptionId));
		echo $this->Form->input('Report.report_format_id',array('label'=>__('Reporte de '),'default'=>$reportFormatId,'type'=>'hidden'));
		//echo $this->Form->input('Report.sort_option_id',array('label'=>__('Ordenar por'),'default'=>$sortOptionId));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	$productionTableConsolidated="";
	$productionTableDetailed="";
	
	$productionTableConsolidated="<table id='produccion_consolidado' style='overflow:auto'>";
		$productionTableConsolidated.="<thead>";
			$productionTableConsolidated.="<tr>";
				$productionTableConsolidated.="<th>".__('Raw Material')."</th>";
				$productionTableConsolidated.="<th>".__('Product')."</th>";
				$productionTableConsolidated.="<th class='centered'>".__('Quantity Initial Stock')." A</th>";
				foreach ($allProductionResultCodes as $id=>$code){
					$productionTableConsolidated.="<th class='centered'>".__('Quantity Produced')." ".$code."</th>";
				}
				$productionTableConsolidated.="<th class='centered'>".__('Cantidad Materia Prima')."</th>";
				if($userrole!=ROLE_FOREMAN){
					foreach ($allProductionResultCodes as $id=>$code){
						$productionTableConsolidated.="<th class='centered'>".__('Value Produced')." ".$code."</th>";
					}
					$productionTableConsolidated.="<th class='centered'>".__('Valor Fabricado Total')."</th>";					
				}
				foreach ($allProductionResultCodes as $id=>$code){
					$productionTableConsolidated.="<th class='centered'>".__('Quantity Final Stock')." ".$code."</th>";
				}
			$productionTableConsolidated.="</tr>";
		$productionTableConsolidated.="</thead>";

		$productionTableConsolidated.="<tbody>";
		$resultCodeTotals=array();
		foreach ($allProductionResultCodes as $id=>$code){
			$resultCodeTotals[$id]['unit_price']=0;
			$resultCodeTotals[$id]['quantity_start']=0;
			$resultCodeTotals[$id]['quantity_produced']=0;
			$resultCodeTotals[$id]['quantity_reclassified']=0;
			$resultCodeTotals[$id]['quantity_sold']=0;
			$resultCodeTotals[$id]['quantity_end']=0;
			
			if($userrole!=ROLE_FOREMAN){
				$resultCodeTotals[$id]['value_start']=0;
				$resultCodeTotals[$id]['value_produced']=0;
				$resultCodeTotals[$id]['value_reclassified']=0;
				$resultCodeTotals[$id]['value_sold']=0;
				$resultCodeTotals[$id]['value_end']=0;				
				$resultCodeTotals[$id]['productprofit']=0;				
			}
		}
		$tableRows="";
		$totalQuantityA=0;
		$rawMaterialQuantity=0;
		$totalProducedValue=0;
		
		//20170209 AND THEN AGAIN THE SAME BUT PER RAW MATERIAL
		$resultCodeTotalsPerRawMaterial=array();
		foreach ($allProductionResultCodes as $id=>$code){
			$resultCodeTotalsPerRawMaterial[$id]['unit_price']=0;
			$resultCodeTotalsPerRawMaterial[$id]['quantity_start']=0;
			$resultCodeTotalsPerRawMaterial[$id]['quantity_produced']=0;
			$resultCodeTotalsPerRawMaterial[$id]['quantity_reclassified']=0;
			$resultCodeTotalsPerRawMaterial[$id]['quantity_sold']=0;
			$resultCodeTotalsPerRawMaterial[$id]['quantity_end']=0;
			
			if($userrole!=ROLE_FOREMAN){
				$resultCodeTotalsPerRawMaterial[$id]['value_start']=0;
				$resultCodeTotalsPerRawMaterial[$id]['value_produced']=0;
				$resultCodeTotalsPerRawMaterial[$id]['value_reclassified']=0;
				$resultCodeTotalsPerRawMaterial[$id]['value_sold']=0;
				$resultCodeTotalsPerRawMaterial[$id]['value_end']=0;				
				$resultCodeTotalsPerRawMaterial[$id]['productprofit']=0;				
			}
		}
		$tableRowsPerRawMaterial="";
		$totalQuantityAPerRawMaterial=0;
		$rawMaterialQuantityPerRawMaterial=0;
		$totalProducedValuePerRawMaterial=0;
		
		
		// initialize raw material
		$lastRawMaterialId=-1;
		$lastRawMaterialName="none";
		
		foreach ($producedMaterials as $producedmaterial){
      $newRawMaterialId=empty($producedmaterial['RawMaterial'])?0:$producedmaterial['RawMaterial']['id'];
			if ($newRawMaterialId != $lastRawMaterialId){
				if ($lastRawMaterialId != -1){
					$subTotalRow="";
					$subTotalRow.="<tr class='lightgreen bold'>";
						$subTotalRow.="<td class='hidden'></td>";
						$subTotalRow.="<td>".$lastRawMaterialName."</td>";
						$subTotalRow.="<td></td>";
						$subTotalRow.="<td class='centered number'>".$totalQuantityAPerRawMaterial."</td>";
						foreach ($allProductionResultCodes as $id=>$code){	
							$subTotalRow.="<td class='centered number'>".$resultCodeTotalsPerRawMaterial[$id]['quantity_produced']."</td>";
						}
						$subTotalRow.="<td class='centered number'>".$rawMaterialQuantityPerRawMaterial."</td>";
						if($userrole!=ROLE_FOREMAN){
							foreach ($allProductionResultCodes as $id=>$code){		
								$subTotalRow.="<td class='centered currency'><span>".$resultCodeTotalsPerRawMaterial[$id]['value_produced']."</span></td>";
							}
							$subTotalRow.="<td class='centered currency'><span>".$totalProducedValuePerRawMaterial."</span></td>";
						}
						foreach ($allProductionResultCodes as $id=>$code){		
							$subTotalRow.="<td class='centered number'>".$resultCodeTotalsPerRawMaterial[$id]['quantity_end']."</td>";
						}
					$subTotalRow.="</tr>";
					$tableRows.=$subTotalRow;
				}
				$resultCodeTotalsPerRawMaterial=array();
				foreach ($allProductionResultCodes as $id=>$code){
					$resultCodeTotalsPerRawMaterial[$id]['unit_price']=0;
					$resultCodeTotalsPerRawMaterial[$id]['quantity_start']=0;
					$resultCodeTotalsPerRawMaterial[$id]['quantity_produced']=0;
					$resultCodeTotalsPerRawMaterial[$id]['quantity_reclassified']=0;
					$resultCodeTotalsPerRawMaterial[$id]['quantity_sold']=0;
					$resultCodeTotalsPerRawMaterial[$id]['quantity_end']=0;
					
					if($userrole!=ROLE_FOREMAN){
						$resultCodeTotalsPerRawMaterial[$id]['value_start']=0;
						$resultCodeTotalsPerRawMaterial[$id]['value_produced']=0;
						$resultCodeTotalsPerRawMaterial[$id]['value_reclassified']=0;
						$resultCodeTotalsPerRawMaterial[$id]['value_sold']=0;
						$resultCodeTotalsPerRawMaterial[$id]['value_end']=0;				
						$resultCodeTotalsPerRawMaterial[$id]['productprofit']=0;				
					}
				}
				$tableRowsPerRawMaterial="";
				$totalQuantityAPerRawMaterial=0;
				$rawMaterialQuantityPerRawMaterial=0;
				$totalProducedValuePerRawMaterial=0;
				
				$lastRawMaterialId=$newRawMaterialId;
				$lastRawMaterialName=empty($producedmaterial['RawMaterial'])?"":$producedmaterial['RawMaterial']['name'];
			}
			
			$totalQuantityAPerRawMaterial+=$producedmaterial['ProductionResultCodeValues'][PRODUCTION_RESULT_CODE_A]['initial_quantity'];
	
			$rawMaterialQuantityProduct=0;
			$totalProducedValueProduct=0;
			
			foreach ($allProductionResultCodes as $id=>$code){	
				$resultCodeTotalsPerRawMaterial[$id]['quantity_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity']; 
				$rawMaterialQuantityProduct+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity'];
				//$resultCodeTotalsPerRawMaterial[$id]['quantity_sold']+=$producedmaterial['ProductionResultCodeValues'][$id]['sold_quantity']; 
				$resultCodeTotalsPerRawMaterial[$id]['quantity_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_quantity']; 
			
				if($userrole!=ROLE_FOREMAN){
					$resultCodeTotalsPerRawMaterial[$id]['value_start']+=$producedmaterial['ProductionResultCodeValues'][$id]['initial_value'];
					$resultCodeTotalsPerRawMaterial[$id]['value_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_value'];
					$totalProducedValueProduct+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_value'];
					$resultCodeTotalsPerRawMaterial[$id]['value_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_value'];
				}
			}
			$rawMaterialQuantityPerRawMaterial+=$rawMaterialQuantityProduct;
			$totalProducedValuePerRawMaterial+=$totalProducedValueProduct;
			
			
			$totalQuantityA+=$producedmaterial['ProductionResultCodeValues'][PRODUCTION_RESULT_CODE_A]['initial_quantity'];
			
			$rawMaterialQuantityProduct=0;
			$totalProducedValueProduct=0;
			
			foreach ($allProductionResultCodes as $id=>$code){	
				$resultCodeTotals[$id]['quantity_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity']; 
				$rawMaterialQuantityProduct+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity'];
				//$resultCodeTotals[$id]['quantity_sold']+=$producedmaterial['ProductionResultCodeValues'][$id]['sold_quantity']; 
				$resultCodeTotals[$id]['quantity_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_quantity']; 
			
				if($userrole!=ROLE_FOREMAN){
					$resultCodeTotals[$id]['value_start']+=$producedmaterial['ProductionResultCodeValues'][$id]['initial_value'];
					$resultCodeTotals[$id]['value_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_value'];
					$totalProducedValueProduct+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_value'];
					$resultCodeTotals[$id]['value_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_value'];
				}
			}
			$rawMaterialQuantity+=$rawMaterialQuantityProduct;
			$totalProducedValue+=$totalProducedValueProduct;			
			
			$tableRows.="<tr>"; 
				$tableRows.="<td>".(empty($producedmaterial['RawMaterial'])?"-":$this->Html->link($producedmaterial['RawMaterial']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['RawMaterial']['id'])))."</td>";
				$tableRows.="<td>".$this->Html->link($producedmaterial['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['FinishedProduct']['id']))."</td>";
				$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][PRODUCTION_RESULT_CODE_A]['initial_quantity']."</td>";
				foreach ($allProductionResultCodes as $id=>$code){	
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity']."</td>";
				}
				$tableRows.="<td class='centered number'>".$rawMaterialQuantityProduct."</td>";
				
				if($userrole!=ROLE_FOREMAN){
					foreach ($allProductionResultCodes as $id=>$code){	
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['produced_value']."</span></td>";
					}
					$tableRows.="<td class='centered currency'><span>".$totalProducedValueProduct."</span></td>";	
				}
				foreach ($allProductionResultCodes as $id=>$code){	
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['final_quantity']."</td>";
				}
			$tableRows.="</tr>";
		}
		if ($lastRawMaterialName!=-1){
			$subTotalRow="";
			$subTotalRow.="<tr class='lightgreen bold'>";
				$subTotalRow.="<td class='hidden'></td>";
				$subTotalRow.="<td>".$lastRawMaterialName."</td>";
				$subTotalRow.="<td></td>";
				$subTotalRow.="<td class='centered number'>".$totalQuantityAPerRawMaterial."</td>";
				foreach ($allProductionResultCodes as $id=>$code){	
					$subTotalRow.="<td class='centered number'>".$resultCodeTotalsPerRawMaterial[$id]['quantity_produced']."</td>";
				}
				$subTotalRow.="<td class='centered number'>".$rawMaterialQuantityPerRawMaterial."</td>";
				if($userrole!=ROLE_FOREMAN){
					foreach ($allProductionResultCodes as $id=>$code){		
						$subTotalRow.="<td class='centered currency'><span>".$resultCodeTotalsPerRawMaterial[$id]['value_produced']."</span></td>";
					}
					$subTotalRow.="<td class='centered currency'><span>".$totalProducedValuePerRawMaterial."</span></td>";
				}
				foreach ($allProductionResultCodes as $id=>$code){		
					$subTotalRow.="<td class='centered number'>".$resultCodeTotalsPerRawMaterial[$id]['quantity_end']."</td>";
				}
			$subTotalRow.="</tr>";
			$tableRows.=$subTotalRow;
		}
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td class='hidden'></td>";
				$totalRows.="<td>Total</td>";
				$totalRows.="<td></td>";
				$totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
				foreach ($allProductionResultCodes as $id=>$code){	
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_produced']."</td>";
				}
				$totalRows.="<td class='centered number'>".$rawMaterialQuantity."</td>";
				if($userrole!=ROLE_FOREMAN){
					foreach ($allProductionResultCodes as $id=>$code){		
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_produced']."</span></td>";
					}
					$totalRows.="<td class='centered currency'><span>".$totalProducedValue."</span></td>";
				}
				foreach ($allProductionResultCodes as $id=>$code){		
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_end']."</td>";
				}
			$totalRows.="</tr>";
		$productionTableConsolidated.=$totalRows.$tableRows.$totalRows."</tbody>";
	$productionTableConsolidated.="</table>";

	$productionTableDetailed="<table id='produccion_detallado' style='overflow:auto'>";
		$productionTableDetailed.="<thead>";
			$productionTableDetailed.="<tr>";
				$productionTableDetailed.="<th class='hidden'>Product Id</th>";
				
				$productionTableDetailed.="<th>".__('Raw Material')."</th>";
				$productionTableDetailed.="<th>".__('Product')."</th>";

				foreach ($allProductionResultCodes as $id=>$code){
					$productionTableDetailed.="<th class='separator'></th>";
					$productionTableDetailed.="<th class='centered'>".__('Quantity Initial Stock')." ".$code."</th>";
					$productionTableDetailed.="<th class='centered'>".__('Quantity Produced')." ".$code."</th>";
					$productionTableDetailed.="<th class='centered'>".__('Quantity Reclassified')." ".$code."</th>";
					$productionTableDetailed.="<th class='centered'>".__('Quantity Sold')." ".$code."</th>";
					$productionTableDetailed.="<th class='centered'>".__('Quantity Final Stock')." ".$code."</th>";

					if($userrole!=ROLE_FOREMAN){
						$productionTableDetailed.="<th class='separator'></th>";
						$productionTableDetailed.="<th class='centered'>".__('Value Initial Stock')." ".$code."</th>";
						$productionTableDetailed.="<th class='centered'>".__('Value Produced')." ".$code."</th>";
						$productionTableDetailed.="<th class='centered'>".__('Value Reclassified')." ".$code."</th>";
						$productionTableDetailed.="<th class='centered'>".__('Value Sold')." ".$code."</th>";
						$productionTableDetailed.="<th class='centered'>".__('Value Final Stock')." ".$code."</th>";
					}
				}
			$productionTableDetailed.="</tr>";
		$productionTableDetailed.="</thead>";

		$productionTableDetailed.="<tbody>";
		$resultCodeTotals=array();
		foreach ($allProductionResultCodes as $id=>$code){
			$resultCodeTotals[$id]['unit_price']=0;
			$resultCodeTotals[$id]['quantity_start']=0;
			$resultCodeTotals[$id]['quantity_produced']=0;
			$resultCodeTotals[$id]['quantity_reclassified']=0;
			$resultCodeTotals[$id]['quantity_sold']=0;
			$resultCodeTotals[$id]['quantity_end']=0;
			
			if($userrole!=ROLE_FOREMAN){
				$resultCodeTotals[$id]['value_start']=0;
				$resultCodeTotals[$id]['value_produced']=0;
				$resultCodeTotals[$id]['value_reclassified']=0;
				$resultCodeTotals[$id]['value_sold']=0;
				$resultCodeTotals[$id]['value_end']=0;				
				$resultCodeTotals[$id]['productprofit']=0;				
			}
		}
		$tableRows="";
		foreach ($producedMaterials as $producedmaterial){
			foreach ($allProductionResultCodes as $id=>$code){	
				$resultCodeTotals[$id]['quantity_start']+=$producedmaterial['ProductionResultCodeValues'][$id]['initial_quantity']; 
				$resultCodeTotals[$id]['quantity_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity']; 
				$resultCodeTotals[$id]['quantity_reclassified']+=$producedmaterial['ProductionResultCodeValues'][$id]['reclassified_quantity']; 
				$resultCodeTotals[$id]['quantity_sold']+=$producedmaterial['ProductionResultCodeValues'][$id]['sold_quantity']; 
				$resultCodeTotals[$id]['quantity_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_quantity']; 
			
				if($userrole!=ROLE_FOREMAN){
					$resultCodeTotals[$id]['value_start']+=$producedmaterial['ProductionResultCodeValues'][$id]['initial_value'];
					$resultCodeTotals[$id]['value_produced']+=$producedmaterial['ProductionResultCodeValues'][$id]['produced_value'];
					$resultCodeTotals[$id]['value_reclassified']+=$producedmaterial['ProductionResultCodeValues'][$id]['reclassified_value'];
					$resultCodeTotals[$id]['value_sold']+=$producedmaterial['ProductionResultCodeValues'][$id]['sold_value'];
					$resultCodeTotals[$id]['value_end']+=$producedmaterial['ProductionResultCodeValues'][$id]['final_value'];
					$resultCodeTotals[$id]['productprofit']=$producedmaterial['ProductionResultCodeValues'][$id]['final_value'] - $producedmaterial['ProductionResultCodeValues'][$id]['produced_value'] + $producedmaterial['ProductionResultCodeValues'][$id]['sold_value']-$producedmaterial['ProductionResultCodeValues'][$id]['initial_value'];
				}
			}	
			
			$tableRows.="<tr>"; 
				$tableRows.="<td>".(empty($producedmaterial['RawMaterial'])?"-":$this->Html->link($producedmaterial['RawMaterial']['name'], ['controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['RawMaterial']['id']]))."</td>";
				$tableRows.="<td>".$this->Html->link($producedmaterial['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['FinishedProduct']['id']))."</td>";

				foreach ($allProductionResultCodes as $id=>$code){	
					$tableRows.="<td class='separator'></td>";					
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['initial_quantity']."</td>";
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['produced_quantity']."</td>";
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['reclassified_quantity']."</td>";
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['sold_quantity']."</td>";
					$tableRows.="<td class='centered number'>".$producedmaterial['ProductionResultCodeValues'][$id]['final_quantity']."</td>";
				
					if($userrole!=ROLE_FOREMAN){
						$tableRows.="<td class='separator'></td>";
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['initial_value']."</span></td>";
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['produced_value']."</span></td>";
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['reclassified_value']."</span></td>";
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['sold_value']."</span></td>";
						$tableRows.="<td class='centered currency'><span>".$producedmaterial['ProductionResultCodeValues'][$id]['final_value']."</span></td>";

						//$tableRows.="<td class='separator'></td>";
						//$tableRows.="<td class='centered number'>".$productprofit."</span></td>";
					}
				}
			$tableRows.="</tr>";
		}
		
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td class='hidden'></td>";
				$totalRows.="<td>Total</td>";
				$totalRows.="<td></td>";
				//if ($quantity_start>0){
				//	//$totalRows.="<td>".$value_start/$quantity_start."</td>";
				//}
				//else {
				//	//$totalRows.="<td>0</td>";
				//}
				foreach ($allProductionResultCodes as $id=>$code){	
					$totalRows.="<td class='separator'></td>";
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_start']."</td>";
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_produced']."</td>";
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_reclassified']."</td>";
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_sold']."</td>";
					$totalRows.="<td class='centered number'>".$resultCodeTotals[$id]['quantity_end']."</td>";
					
					if($userrole!=ROLE_FOREMAN){
						$totalRows.="<td class='separator'></td>";
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_start']."</span></td>";
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_produced']."</span></td>";
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_reclassified']."</span></td>";
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_sold']."</span></td>";
						$totalRows.="<td class='centered currency'><span>".$resultCodeTotals[$id]['value_end']."</span></td>";
						
						//$totalRows.="<td class='separator'></td>";				
						//$totalRows.="<td>".$profit,2)."</td>";
					}
				}
			$totalRows.="</tr>";
		$productionTableDetailed.=$totalRows.$tableRows.$totalRows."</tbody>";
	$productionTableDetailed.="</table>";
	/*	
	switch ($reportFormatId){
		case FORMAT_CONSOLIDATED:
			echo "<h2>".__('Reporte Producción Consolidado')."</h2>"; 
			echo $productionTableConsolidated; 
			break;
		case FORMAT_DETAILED:	
			echo "<h2>".__('Reporte Producción Detallada')."</h2>"; 
			echo $productionTableDetailed; 
			break;
	}
	*/
	echo "<h2>".__('Reporte Producción Consolidado')."</h2>"; 
	echo $productionTableConsolidated; 
	echo "<h2>".__('Reporte Producción Detallada')."</h2>"; 
	echo $productionTableDetailed; 

	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteProduccionDetalle'), array( 'class' => 'btn btn-primary')); 

	
	
	$_SESSION['productionDetailReport'] = $productionTableConsolidated.$productionTableDetailed;
?>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});		
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});

</script>