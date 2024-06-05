<script>
	function formatNumbers(){
		$("td.number").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).prepend("-");
			}
			$(this).number(true,0,'.',',');
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
  
  function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
    formatPercentages();
	});
</script>
<div class="productionRuns index fullwidth">
<?php
	echo "<h2>". __('Production Runs')."</h2>";
	 
	echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-5'>";	
				echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.acceptable_option_id',array('label'=>__('Ordenes Aceptables'),'default'=>$acceptableOptionId));
					echo $this->Form->input('Report.finished_product_id',array('label'=>__('Seleccione Producto'),'default'=>$selectedProductId,'empty'=>array('0'=>'Todos Productos')));
					echo $this->Form->input('Report.shift_id',array('label'=>__('Seleccione Turno'),'default'=>$selectedShiftId,'empty'=>array('0'=>'Todos Turnos')));
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>". __('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>". __('Next Month')."</button>";
				echo $this->Form->end(__('Refresh')); 
				echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenOrdenesDeProduccion'), array( 'class' => 'btn btn-primary')); 
			echo "</div>";
			echo "<div class='col-md-5'>";	
				$shiftTable="";
				$totalProductionRuns=0;
				$totalAcceptableRuns=0;
				$shiftTable.="<table>";
					$shiftTable.="<thead>";
						$shiftTable.="<tr>";
							$shiftTable.="<th>".__('Shift')."</th>";
							$shiftTable.="<th class='centered'># Ordenes de Producción</th>";
							$shiftTable.="<th class='centered'>% Aceptable</td>";
						$shiftTable.="</tr>";
					$shiftTable.="</thead>";
					$shiftTable.="<tbody>";
					foreach($shiftTotals as $shift){
						$totalProductionRuns+=count($shift['ProductionRun']);
						$totalAcceptableRuns+=$shift['Shift']['acceptable_runs'];
						if (count($shift['ProductionRun'])>0){
							$shiftTable.="<tr>";
								$shiftTable.="<td>".$this->Html->Link($shift['Shift']['name'],array('controller'=>'shifts','action'=>'view',$shift['Shift']['id']))."</td>";
								$shiftTable.="<td class='centered'>".count($shift['ProductionRun'])."</td>";
								$shiftTable.="<td class='centered'>".(count($shift['ProductionRun'])>0?round(100*$shift['Shift']['acceptable_runs']/count($shift['ProductionRun'],2)):"-")."</td>";
							$shiftTable.="</tr>";
						}
					}
					$shiftTable.="<tr class='totalrow'>";
							$shiftTable.="<td>TOTALES</td>";
							$shiftTable.="<td class='centered'>".$totalProductionRuns."</td>";
							$shiftTable.="<td class='centered'>".($totalProductionRuns>0?round(100*$totalAcceptableRuns/$totalProductionRuns,2):"-")."</td>";
						$shiftTable.="</tr>";
					
					$shiftTable.="</tbody>";
				$shiftTable.="</table>";
        
        $machineTable="";
        $totalA=0;
        $totalB=0;
        $totalC=0;
				$totalProductionRuns=0;
				$totalAcceptableRuns=0;
				$machineTable.="<table>";
					$machineTable.="<thead>";
						$machineTable.="<tr>";
							$machineTable.="<th>".__('Machine')."</th>";
              $machineTable.="<th class='centered'># A</th>";
              $machineTable.="<th class='centered'># B</th>";
              $machineTable.="<th class='centered'># C</th>";
              $machineTable.="<th class='centered'># Total</th>";
							$machineTable.="<th class='centered'># OP</th>";
							$machineTable.="<th class='centered'>% Aceptable</td>";
						$machineTable.="</tr>";
					$machineTable.="</thead>";
					$machineTable.="<tbody>";
					foreach($machineTotals as $machine){
            //pr($machine);
            $totalA+=$machine['Machine']['quantity_A'];
            $totalB+=$machine['Machine']['quantity_B'];
            $totalC+=$machine['Machine']['quantity_C'];
						$totalProductionRuns+=count($machine['ProductionRun']);
						$totalAcceptableRuns+=$machine['Machine']['acceptable_runs'];
						if (count($machine['ProductionRun'])>0){
							$machineTable.="<tr>";
								$machineTable.="<td>".$this->Html->Link($machine['Machine']['name'],array('controller'=>'machines','action'=>'view',$machine['Machine']['id']))."</td>";
                $machineTable.="<td class='centered number'><span class='amountright'>".($machine['Machine']['quantity_A'])."</span></td>";
                $machineTable.="<td class='centered number'><span class='amountright'>".($machine['Machine']['quantity_B'])."</span></td>";
                $machineTable.="<td class='centered number'><span class='amountright'>".($machine['Machine']['quantity_C'])."</span></td>";
                $machineTable.="<td class='centered number'><span class='amountright'>".($machine['Machine']['quantity_A']+$machine['Machine']['quantity_B']+$machine['Machine']['quantity_C'])."</span></td>";
								$machineTable.="<td class='centered'>".count($machine['ProductionRun'])."</td>";
								$machineTable.="<td class='centered'>".(count($machine['ProductionRun'])>0?round(100*$machine['Machine']['acceptable_runs']/count($machine['ProductionRun'],2)):"-")."</td>";
							$machineTable.="</tr>";
						}
					}
					$machineTable.="<tr class='totalrow'>";
							$machineTable.="<td>TOTALES</td>";
              $machineTable.="<td class='centered number'><span class='amountright'>".$totalA."</span></td>";
              $machineTable.="<td class='centered number'><span class='amountright'>".$totalB."</span></td>";
              $machineTable.="<td class='centered number'><span class='amountright'>".$totalC."</span></td>";
              $machineTable.="<td class='centered number'><span class='amountright'>".($totalA+ $totalB+$totalC)."</span></td>";
							$machineTable.="<td class='centered'>".$totalProductionRuns."</td>";
							$machineTable.="<td class='centered'>".($totalProductionRuns>0?round(100*$totalAcceptableRuns/$totalProductionRuns,2):"-")."</td>";
						$machineTable.="</tr>";
					
					$machineTable.="</tbody>";
				$machineTable.="</table>";
        
				echo $shiftTable;
        echo $machineTable;
        
        if ($userrole==ROLE_ADMIN){
          $utilitySummaryTableHeader="<thead>";
            $utilitySummaryTableHeader.="<tr>";
              $utilitySummaryTableHeader.="<th>Máquina</th>";
              //$utilitySummaryTableHeader.="<th>Preforma</th>";
              //$utilitySummaryTableHeader.="<th>Producto</th>";
              $utilitySummaryTableHeader.="<th class='centered'># Preforma</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Costo</th>";
              $utilitySummaryTableHeader.="<th class='centered'># Fabricado</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Precio</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
              $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
            $utilitySummaryTableHeader.="</tr>";
          $utilitySummaryTableHeader.="</thead>";
          
          $totalQuantityAllMachines=0;
          $totalCostAllMachines=0;
          
          $totalQuantityStockAllMachines=0;
          $totalQuantitySoldAllMachines=0;
          $totalQuantityReclassifiedAllMachines=0;
          $totalQuantityTransferredAllMachines=0;
          
          $totalPriceStockAllMachines=0;
          $totalPriceSoldAllMachines=0;
          $totalPriceReclassifiedAllMachines=0;
          $totalPriceTransferredAllMachines=0;
          
          $totalPriceAllMachines=0;
          
          $machineSummaryBlock="";
          
          foreach ($machines as $machine){
            $machineUtility=$machine['machineUtility'];
            //pr($machine['Machine']);
            $totalQuantityAllRawMaterials=0;
            $totalCostAllRawMaterials=0;
            
            $totalQuantityStockAllRawMaterials=0;
            $totalQuantitySoldAllRawMaterials=0;
            $totalQuantityReclassifiedAllRawMaterials=0;
            $totalQuantityTransferredAllRawMaterials=0;
            
            $totalPriceStockAllRawMaterials=0;
            $totalPriceSoldAllRawMaterials=0;
            $totalPriceReclassifiedAllRawMaterials=0;
            $totalPriceTransferredAllRawMaterials=0;
            
            $totalPriceAllRawMaterials=0;
            
            $rawMaterialSummaryBlock="";
            
            foreach ($machineUtility['outputTotals'] as $rawMaterialId=>$rawMaterialData){
              $totalQuantitySelectedRawMaterial=0;
              $totalCostSelectedRawMaterial=0;
              
              $totalQuantityStockSelectedRawMaterial=0;
              $totalQuantitySoldSelectedRawMaterial=0;
              $totalQuantityReclassifiedSelectedRawMaterial=0;
              $totalQuantityTransferredSelectedRawMaterial=0;
              
              $totalPriceStockSelectedRawMaterial=0;
              $totalPriceSoldSelectedRawMaterial=0;
              $totalPriceReclassifiedSelectedRawMaterial=0;
              $totalPriceTransferredSelectedRawMaterial=0;
              
              $totalPriceSelectedRawMaterial=0;
              
              $productSummaryBlock="";
              $productDetailBlock="";
              
              foreach ($rawMaterialData as $productId=>$productData){
                $totalQuantitySelectedProduct=0;
                $totalCostSelectedProduct=0;
                
                $totalQuantityStockSelectedProduct=0;
                $totalQuantitySoldSelectedProduct=0;
                $totalQuantityReclassifiedSelectedProduct=0;
                $totalQuantityTransferredSelectedProduct=0;
                
                $totalPriceStockSelectedProduct=0;
                $totalPriceSoldSelectedProduct=0;
                $totalPriceReclassifiedSelectedProduct=0;
                $totalPriceTransferredSelectedProduct=0;
                
                $totalPriceSelectedProduct=0;
                
                $qualityRows="";
                foreach ($productData as $productionResultCodeId=>$productionResultCodeData){
                  //pr($productionResultCodeData);
                  $totalQuantitySelectedProduct+=$productionResultCodeData['productQuantity'];
                  $totalCostSelectedProduct+=$productionResultCodeData['productTotalCost'];
                  
                  $totalQuantityStockSelectedProduct+=$productionResultCodeData['stockMovementData']['quantityStock'];
                  $totalQuantitySoldSelectedProduct+=$productionResultCodeData['stockMovementData']['quantitySold'];
                  $totalQuantityReclassifiedSelectedProduct+=$productionResultCodeData['stockMovementData']['quantityReclassified'];
                  $totalQuantityTransferredSelectedProduct+=$productionResultCodeData['stockMovementData']['quantityTransferred'];
                  
                  $totalPriceStockSelectedProduct+=$productionResultCodeData['stockMovementData']['valueStock'];
                  $totalPriceSoldSelectedProduct+=$productionResultCodeData['stockMovementData']['valueSold'];
                  $totalPriceReclassifiedSelectedProduct+=$productionResultCodeData['stockMovementData']['valueReclassified'];
                  $totalPriceTransferredSelectedProduct+=$productionResultCodeData['stockMovementData']['valueTransferred'];
                  $totalValueThisQuality=$productionResultCodeData['stockMovementData']['valueStock']+$productionResultCodeData['stockMovementData']['valueSold']+$productionResultCodeData['stockMovementData']['valueReclassified']+$productionResultCodeData['stockMovementData']['valueTransferred'];
                  $totalPriceSelectedProduct+=$totalValueThisQuality;
                }
                
                $totalQuantitySelectedRawMaterial+=$totalQuantitySelectedProduct;
                $totalCostSelectedRawMaterial+=$totalCostSelectedProduct;
                
                $totalQuantityStockSelectedRawMaterial+=$totalQuantityStockSelectedProduct;
                $totalQuantitySoldSelectedRawMaterial+=$totalQuantitySoldSelectedProduct;
                $totalQuantityReclassifiedSelectedRawMaterial+=$totalQuantityReclassifiedSelectedProduct;
                $totalQuantityTransferredSelectedRawMaterial+=$totalQuantityTransferredSelectedProduct;
                
                $totalPriceStockSelectedRawMaterial+=$totalPriceStockSelectedProduct;
                $totalPriceSoldSelectedRawMaterial+=$totalPriceSoldSelectedProduct;
                $totalPriceReclassifiedSelectedRawMaterial+=$totalPriceReclassifiedSelectedProduct;
                $totalPriceTransferredSelectedRawMaterial+=$totalPriceTransferredSelectedProduct;
                
                $totalPriceSelectedRawMaterial+=$totalPriceSelectedProduct;
                
                $productRowSummary="";
                /*
                $productRowSummary.="<tr>";
                  $productRowSummary.="<td></td>";
                  $productRowSummary.="<td></td>";
                  $productRowSummary.="<td>".$finishedProductList[$productId]."</td>";
                  $productRowSummary.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
                  $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedProduct."</span></td>";
                  $productRowSummary.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
                  $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedProduct."</span></td>";
                  $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedProduct-$totalCostSelectedProduct)."</span></td>";
                  $productRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceSelectedProduct-$totalCostSelectedProduct)/$totalCostSelectedProduct)."</span></td>";
                $productRowSummary.="</tr>";
                */
                $productSummaryBlock.=$productRowSummary;
              }
              
              $totalQuantityAllRawMaterials+=$totalQuantitySelectedRawMaterial;
              $totalCostAllRawMaterials+=$totalCostSelectedRawMaterial;
              
              $totalQuantityStockAllRawMaterials+=$totalQuantityStockSelectedRawMaterial;
              $totalQuantitySoldAllRawMaterials+=$totalQuantitySoldSelectedRawMaterial;
              $totalQuantityReclassifiedAllRawMaterials+=$totalQuantityReclassifiedSelectedRawMaterial;
              $totalQuantityTransferredAllRawMaterials+=$totalQuantityTransferredSelectedRawMaterial;
              
              $totalPriceStockAllRawMaterials+=$totalPriceStockSelectedRawMaterial;
              $totalPriceSoldAllRawMaterials+=$totalPriceSoldSelectedRawMaterial;
              $totalPriceReclassifiedAllRawMaterials+=$totalPriceReclassifiedSelectedRawMaterial;
              $totalPriceTransferredAllRawMaterials+=$totalPriceTransferredSelectedRawMaterial;
              
              $totalPriceAllRawMaterials+=$totalPriceSelectedRawMaterial;
               
              $rawMaterialRowSummary="";
              /* 
              $rawMaterialRowSummary.="<tr style='background-color:lightblue;'>";
                $rawMaterialRowSummary.="<td></td>";
                $rawMaterialRowSummary.="<td colspan=2>".$rawMaterialList[$rawMaterialId]."</td>";
                $rawMaterialRowSummary.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
                $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedRawMaterial."</span></td>";
                $rawMaterialRowSummary.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
                $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedRawMaterial."</span></td>";
                $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)."</span></td>";
                $rawMaterialRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)/$totalCostSelectedRawMaterial)."</span></td>";
              $rawMaterialRowSummary.="</tr>";
              */  
              $rawMaterialSummaryBlock.=$rawMaterialRowSummary.$productSummaryBlock.$rawMaterialRowSummary;
            }
            
            $totalQuantityAllMachines+=$totalQuantityAllRawMaterials;
            $totalCostAllMachines+=$totalCostAllRawMaterials;
            
            $totalQuantityStockAllMachines+=$totalQuantityStockAllRawMaterials;
            $totalQuantitySoldAllMachines+=$totalQuantitySoldAllRawMaterials;
            $totalQuantityReclassifiedAllMachines+=$totalQuantityReclassifiedAllRawMaterials;
            $totalQuantityTransferredAllMachines+=$totalQuantityTransferredAllRawMaterials;
            
            $totalPriceStockAllMachines+=$totalPriceStockAllRawMaterials;
            $totalPriceSoldAllMachines+=$totalPriceSoldAllRawMaterials;
            $totalPriceReclassifiedAllMachines+=$totalPriceReclassifiedAllRawMaterials;
            $totalPriceTransferredAllMachines+=$totalPriceTransferredAllRawMaterials;
            
            $totalPriceAllMachines+=$totalPriceAllRawMaterials;
              
            
            $machineRowSummary="";
            
            $machineRowSummary.="<tr>";
              $machineRowSummary.="<td>".$machine['Machine']['name']."</td>";
              //$machineRowSummary.="<td></td>";
              //$machineRowSummary.="<td></td>";
              $machineRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
              $machineRowSummary.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllRawMaterials."</span></td>";
              $machineRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
              $machineRowSummary.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllRawMaterials."</span></td>";
              $machineRowSummary.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceAllRawMaterials-$totalCostAllRawMaterials)."</span></td>";
              if ($totalPriceAllRawMaterials>0){
                $machineRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceAllRawMaterials-$totalCostAllRawMaterials)/$totalPriceAllRawMaterials)."</span></td>";
              }
              else {
                $machineRowSummary.="<td class='centered percentage'><span>0</span></td>";
              }
            $machineRowSummary.="</tr>";
            
            //$machineSummaryBlock.=$machineRowSummary.$rawMaterialSummaryBlock.$machineRowSummary;
            $machineSummaryBlock.=$machineRowSummary;
          }      
          
          $totalRowSummary="";
          $totalRowSummary.="<tr class='totalrow'>";
            $totalRowSummary.="<td>Total</td>";
            //$totalRowSummary.="<td></td>";
            //$totalRowSummary.="<td></td>";
            $totalRowSummary.="<td class='centered number'>".$totalQuantityAllMachines."</td>";
            $totalRowSummary.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllMachines."</span></td>";
            $totalRowSummary.="<td class='centered number'>".$totalQuantityAllMachines."</td>";
            $totalRowSummary.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllMachines."</span></td>";
            $totalRowSummary.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceAllMachines-$totalCostAllMachines)."</span></td>";
            if (!empty($totalPriceAllMachines)){
              $totalRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceAllMachines-$totalCostAllMachines)/$totalPriceAllMachines)."</span></td>";
            }
            else {
              $totalRowSummary.="<td class='centered percentage'><span>0</span></td>";
            }
          $totalRowSummary.="</tr>";
          
          $utilitySummaryTable="<table id='utility_summary'>".$utilitySummaryTableHeader.$totalRowSummary.$machineSummaryBlock.$totalRowSummary."</table>";


          echo "<h3>Utilidad por Materia Prima</h3>";
          echo "<p class='comment'>La utilidad se calcula en base a la producción del período seleccionado y sus ventas.  El valor del precio se calcula tomando en cuenta el porcentaje de las ventas.  Ya que se calcula la utilidad de un producto fabricado pero no necesariamente vendido, la utilidad final solo se conocerá en el momento que todo se habrá vendido.  También, si hay parte de la producción que se reclasifica o transfiere entre bodegas, no se da seguimiento a la venta eventual en favor de un cálculo más rápido.</p>";
          
          echo $utilitySummaryTable;        
        }
			echo "</div>";
			echo "<div class='col-md-2'>";	
				echo "<div class='actions fullwidth' style=''>";	
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
						if($bool_add_permission){
							echo "<li>".$this->Html->link(__('New Production Run'), array('action' => 'add'))."</li>";
							echo "<br/>";
						}
            if($userRoleId == ROLE_ADMIN){
							echo "<li>".$this->Html->link(__('Producción PET/Inyección'), array('action' => 'crear'))."</li>";
							echo "<br/>";
						}
						if($bool_product_index_permission){
							echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))." </li>";
						}
						if($bool_product_add_permission){
							echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))." </li>";
						}
					echo "</ul>";
						
					echo "<h3>".__('Configuration Options')."</h3>";
					echo "<ul>";
						echo "<li>".$this->Html->link('Reporte Producción Total', array('controller' => 'operators', 'action' => 'reporteProduccionTotal'))." </li>";
						echo "<li>".$this->Html->link('Reporte Producción Supervisor', array('controller' => 'production_movements', 'action' => 'verReporteProduccionMeses'))." </li>";
					echo "</ul>";
					
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			
?>
</div>
<div class='related'>
<?php		
	$tableHeader="";
	$tableHeader.="<thead>";
		$tableHeader.="<tr>";
			$tableHeader.="<th>".$this->Paginator->sort('production_run_code','Código')."</th>";
			$tableHeader.="<th>".$this->Paginator->sort('production_run_date','Fecha')."</th>";
			$tableHeader.="<th>".$this->Paginator->sort('FinishedProduct.name',__('Product'))."</th>";
			$tableHeader.="<th>".$this->Paginator->sort('RawMaterial.name')."</th>";
			$tableHeader.="<th class='centered'>".$this->Paginator->sort('raw_material_quantity')."</th>";
			$tableHeader.="<!--th>".$this->Paginator->sort('machine_id')."</th-->";
			$tableHeader.="<!--th>".$this->Paginator->sort('operator_id')."</th-->";
			$tableHeader.="<!--th>".$this->Paginator->sort('shift_id')."</th-->";
			$tableHeader.="<th class='centered'>".__('Cantidad A')."</th>";
			$tableHeader.="<th class='centered'>".__('Cantidad B')."</th>";
			$tableHeader.="<th class='centered'>".__('Cantidad C')."</th>";
			if($userrole!=ROLE_FOREMAN) {
				$tableHeader.="<th class='centered'>".__('Value A')."</th>";
				$tableHeader.="<th class='centered'>".__('Value B')."</th>";
				$tableHeader.="<th class='centered'>".__('Value C')."</th>";
			}
			$tableHeader.="<th class='centered'>".__('Cantidad Producido')."</th>";
			if($userrole!=ROLE_FOREMAN) {
				$tableHeader.="<th class='centered'>".__('Value Produced')."</th>";
			}
      $tableHeader.="<th class='centered'>".$this->Paginator->sort('incidence_id')."</th>";
			$excelHeader=$tableHeader;
			$tableHeader.="<th class='actions'>".__('Actions')."</th>";
		$tableHeader.="</tr>";
	$tableHeader.="</thead>";
	
	$totalQuantityA=0;
  $totalQuantityB=0;
  $totalQuantityC=0;
  $totalValueA=0;
  $totalValueB=0;
  $totalValueC=0;
  $totalQuantityAll=0;
  $totalValueAll=0;
  $totalIncidences=0;
	
	$orderRows="";
	$excelRows="";
	foreach ($productionRuns as $productionRun){
		//pr($productionRun);
		$quantityA=0;
		$quantityB=0;
		$quantityC=0;
		$valueA=0;
		$valueB=0;
		$valueC=0;
		$quantityAll=0;
		$valueAll=0;
    
		$orderRow="";
		$productionRunDateTime=new DateTime($productionRun['ProductionRun']['production_run_date']);
    
    foreach ($productionRun['ProductionMovement'] as $productionMovement){
    if ($productionMovement['production_run_id']==$productionRun['ProductionRun']['id']&& !$productionMovement['bool_input']){
      switch ($productionMovement['production_result_code_id']){
        case 1:
          $quantityA+=$productionMovement['product_quantity'];						
          $valueA+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
          break;
        case 2:
          $quantityB+=$productionMovement['product_quantity'];
          $valueB+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
          break;
        case 3:
          $quantityC+=$productionMovement['product_quantity'];
          $valueC+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
          break;
      }
    }
    }
    $quantityAll=$quantityA+$quantityB+$quantityC;
    $valueAll=$valueA+$valueB+$valueC;
    $totalQuantityA+=$quantityA;
    $totalQuantityB+=$quantityB;
    $totalQuantityC+=$quantityC;
    $totalValueA+=$valueA;
    $totalValueB+=$valueB;
    $totalValueC+=$valueC;
    $totalQuantityAll+=$quantityAll;
    $totalValueAll+=$valueAll;

    $acceptableProductionValue=0;
    if (!empty($productionRun['FinishedProduct']['ProductProduction'])){						
      $acceptableProductionValue=$productionRun['FinishedProduct']['ProductProduction'][0]['acceptable_production'];
      if(date('w', strtotime($productionRun['ProductionRun']['production_run_date'])) == 6){
        $acceptableProductionValue=$acceptableProductionValue/2;
      }
      elseif ($productionRun['ProductionRun']['shift_id'] == SHIFT_NIGHT){
        $acceptableProductionValue=$acceptableProductionValue*7/8.5;
      }
    }
    $boolMarkGreen=false;
    if ($acceptableProductionValue>0){
      if ($quantityA>=$acceptableProductionValue){
        $boolMarkGreen=true;
      }
    }
    
    
    $totalIncidences+=count($productionRun['Incidence']);
    
    $orderRow.="<tr".($productionRun['ProductionRun']['bool_annulled']?" class='italic'":"").(($productionRun['Shift']['id']==SHIFT_NIGHT)?" style='background-color:#888888 !important;'":"").">";
			$orderRow.="<td>".$this->html->link($productionRun['ProductionRun']['production_run_code'].($productionRun['ProductionRun']['bool_annulled']?" (Anulada)":""), array('action' => 'view', $productionRun['ProductionRun']['id']))."</td>";
			$orderRow.="<td>".$productionRunDateTime->format('d-m-Y')."</td>";
			$orderRow.="<td>".$this->Html->link($productionRun['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $productionRun['FinishedProduct']['id']))."</td>";
			$orderRow.="<td>".$this->Html->link($productionRun['RawMaterial']['name'], array('controller' => 'stock_items', 'action' => 'verReporteProducto', $productionRun['RawMaterial']['id']))."</td>";
			$orderRow.="<td class='centered'>".$productionRun['ProductionRun']['raw_material_quantity']."</td>";

      if ($boolMarkGreen){
        if(date('w', strtotime($productionRun['ProductionRun']['production_run_date'])) == 6){
          $orderRow.="<td class='centered number darkgreentext bold'>".$quantityA."</td>";
        }
        else {
          $orderRow.="<td class='centered number greentext bold'>".$quantityA."</td>";
        }
      }
      else {
        $orderRow.="<td class='centered number'>".$quantityA."</td>";
      }
      $orderRow.="<td class='centered number'>".$quantityB."</td>";
      $orderRow.="<td class='centered number'>".$quantityC."</td>";
      if($userrole!=ROLE_FOREMAN) {
        $orderRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueA."</span></td>";
        $orderRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueB."</span></td>";
        $orderRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueC."</span></td>";
      }
      $orderRow.="<td class='centered number'><span class='amountright'>".$quantityAll."</span></td>";
      if($userrole!=ROLE_FOREMAN) {
        $orderRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueAll."</td>";
      }
      $orderRow.="<td>".(empty($productionRun['Incidence']['name'])?"-":$this->html->link($productionRun['Incidence']['name'], array('controller'=>'incidences','action' => 'verIncidencia', $productionRun['Incidence']['id'])))."</td>";
    $excelRows.=$orderRow."</tr>";			
      $orderRow.="<td class='actions'>";
        if($bool_edit_permission) {
          $orderRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $productionRun['ProductionRun']['id'])); 
        }
      $orderRow.="</td>";
    $orderRow.="</tr>";
    $orderRows.=$orderRow;
  } 
  $totalRows="";
  $totalRows.="<tr class='totalrow'>";
    $totalRows.="<td>Total</td>";
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
    $totalRows.="<td class='centered number'><span class='amountright'>".$totalQuantityAll."</span></td>";
    
    $totalRows.="<td class='centered number'><span class='amountright'>".$totalQuantityA."</span></td>";
    $totalRows.="<td class='centered number'><span class='amountright'>".$totalQuantityB."</span></td>";
    $totalRows.="<td class='centered number'><span class='amountright'>".$totalQuantityC."</span></td>";
    if($userrole!=ROLE_FOREMAN) {
      $totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueA."</span></td>";
      $totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueB."</span></td>";
      $totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueC."</span></td>";
    }
    $totalRows.="<td class='centered number'>".$totalQuantityAll."</td>";
    if($userrole!=ROLE_FOREMAN) {
      $totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueAll."</span></td>";
    }
    $totalRows.="<td></td>";
    $totalRows.="<td>".$totalIncidences."</td>";
  $totalRows.="</tr>";
  $totalRows.="<tr class='totalrow'>";
    $totalRows.="<td>Porcentajes</td>";
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
    
    $totalRows.="<td class='centered'>".($totalQuantityAll>0?number_format(100*$totalQuantityA/$totalQuantityAll,2,".",","):"-")." %</td>";
    $totalRows.="<td class='centered'>".($totalQuantityAll>0?number_format(100*$totalQuantityB/$totalQuantityAll,2,".",","):"-")." %</td>";
    $totalRows.="<td class='centered'>".($totalQuantityAll>0?number_format(100*$totalQuantityC/$totalQuantityAll,2,".",","):"-")." %</td>";
    if($userrole!=ROLE_FOREMAN) {
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
      $totalRows.="<td></td>";
    }
    $totalRows.="<td></td>";
    if($userrole!=ROLE_FOREMAN) {
      $totalRows.="<td></td>";
    }
    $totalRows.="<td></td>";
    $totalRows.="<td></td>";
  $totalRows.="</tr>";
	$tableBody="<tbody>".$totalRows.$orderRows.$totalRows."</tbody>";
	$excelBody="<tbody>".$totalRows.$excelRows.$totalRows."</tbody>";
		
	echo "<p class='comment'>Ordenes de producción del turno de noche salen con un fondo gris</p>";	
	echo "<p class='comment'>Ordenes de producción con una cantidad aceptable salen con su número en <span class='greentext'>verde</span>.  Para turnos de noche, la cantidad aceptable tiene aplicada un factor 7/8.5.</p>";	
	echo "<p class='comment'>Ordenes de producción sabatinas con una cantidad aceptable salen con su número en <span class='darkgreentext'>verde oscuro</span>; la cantidad aceptable mínima es la mitad de lo normal. </p>";	
	echo "<p class='comment'>Ordenes de producción anuladas salen en <span class='italic'>cursivo</span></p>";	
	echo "<table cellpadding='0' cellspacing='0'>".$tableHeader.$tableBody."</table>";
	$excelTable="<table id='ordenes_produccion'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumenOrdenesProduccion'] = $excelTable;
?>
</div>