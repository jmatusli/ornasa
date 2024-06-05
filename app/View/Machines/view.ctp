<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			/*$(this).parent().prepend("C$ ");*/
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
		formatCurrencies();
		formatPercentages();
	});
</script>
<div class="machines view">
<?php 
  if ($roleId==ROLE_ADMIN){
    $utilitySummaryTableHeader="<thead>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th>Preforma</th>";
        $utilitySummaryTableHeader.="<th>Producto</th>";
        $utilitySummaryTableHeader.="<th class='centered'># Preforma</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Costo</th>";
        $utilitySummaryTableHeader.="<th class='centered'># Fabricado</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Precio</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
      $utilitySummaryTableHeader.="</tr>";
    $utilitySummaryTableHeader.="</thead>";
    
    
    $utilityDetailsTableHeader="<thead>";
      $utilityDetailsTableHeader.="<tr>";
        $utilityDetailsTableHeader.="<th>Preforma</th>";
        $utilityDetailsTableHeader.="<th>Producto</th>";
        $utilityDetailsTableHeader.="<th>Calidad</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Preforma</th>";
        $utilityDetailsTableHeader.="<th class='centered'>Costo</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Bodega</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Venta</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Reclasificado</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Transferido</th>";
        $utilityDetailsTableHeader.="<th class='centered'># Fabricado</th>";
        $utilityDetailsTableHeader.="<th class='centered'>C$ Bodega</th>";
        $utilityDetailsTableHeader.="<th class='centered'>C$ Venta</th>";
        $utilityDetailsTableHeader.="<th class='centered'>C$ Reclasificado</th>";
        $utilityDetailsTableHeader.="<th class='centered'>C$ Transferido</th>";
        $utilityDetailsTableHeader.="<th class='centered'>Precio</th>";
        $utilityDetailsTableHeader.="<th class='centered'>Utilidad</th>";
        $utilityDetailsTableHeader.="<th class='centered'>Utilidad %</th>";
      $utilityDetailsTableHeader.="</tr>";
    $utilityDetailsTableHeader.="</thead>";
    
    
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
    $rawMaterialDetailBlock="";
    
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
          $qualityRows.="<tr>";
            $qualityRows.="<td></td>";
            $qualityRows.="<td></td>";
            $qualityRows.="<td>".$productionResultCodeList[$productionResultCodeId]."</td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['productQuantity']."</td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$productionResultCodeData['productTotalCost']."</span></td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['stockMovementData']['quantityStock']."</td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['stockMovementData']['quantitySold']."</td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['stockMovementData']['quantityReclassified']."</td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['stockMovementData']['quantityTransferred']."</td>";
            $qualityRows.="<td class='centered number'>".$productionResultCodeData['productQuantity']."</td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$productionResultCodeData['stockMovementData']['valueStock']."</span></td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$productionResultCodeData['stockMovementData']['valueSold']."</span></td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$productionResultCodeData['stockMovementData']['valueReclassified']."</span></td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$productionResultCodeData['stockMovementData']['valueTransferred']."</span></td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalValueThisQuality."</span></td>";
            $qualityRows.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalValueThisQuality-$productionResultCodeData['productTotalCost'])."</span></td>";
            if (!empty($totalValueThisQuality)){
              $qualityRows.="<td class='centered percentage'><span>".(100*($totalValueThisQuality-$productionResultCodeData['productTotalCost'])/$totalValueThisQuality)."</span></td>";
            }
            else {
              $qualityRows.="<td class='centered percentage'><span>0</span></td>";
            }
          
          $qualityRows.="</tr>";
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
          
        $productRowDetail="";
        $productRowDetail.="<tr style='background-color:lightgreen;'>";
            $productRowDetail.="<td></td>";
            $productRowDetail.="<td colspan=2>".$finishedProductList[$productId]."</td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantityStockSelectedProduct."</td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantitySoldSelectedProduct."</td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantityReclassifiedSelectedProduct."</td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantityTransferredSelectedProduct."</td>";
            $productRowDetail.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceStockSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSoldSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceReclassifiedSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceTransferredSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedProduct."</span></td>";
            $productRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedProduct-$totalCostSelectedProduct)."</span></td>";
            if (!empty($totalPriceSelectedProduct)){
              $productRowDetail.="<td class='centered percentage'><span>".(100*($totalPriceSelectedProduct-$totalCostSelectedProduct)/$totalPriceSelectedProduct)."</span></td>";
            }
            else {
              $productRowDetail.="<td class='centered percentage'><span>0</span></td>";
            }
        $productRowDetail.="</tr>";
        
        $productRowSummary="";
        $productRowSummary.="<tr>";
          $productRowSummary.="<td></td>";
          $productRowSummary.="<td>".$finishedProductList[$productId]."</td>";
          $productRowSummary.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
          $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedProduct."</span></td>";
          $productRowSummary.="<td class='centered number'>".$totalQuantitySelectedProduct."</td>";
          $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedProduct."</span></td>";
          $productRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedProduct-$totalCostSelectedProduct)."</span></td>";
          if (!empty($totalPriceSelectedProduct)){
            $productRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceSelectedProduct-$totalCostSelectedProduct)/$totalPriceSelectedProduct)."</span></td>";
          }
          else {
            $productRowSummary.="<td class='centered percentage'><span>0</span></td>";
          }
        $productRowSummary.="</tr>";
        
        $productSummaryBlock.=$productRowSummary;
        $productDetailBlock.=$productRowDetail.$qualityRows.$productRowDetail;
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
        
      
      $rawMaterialRowDetail="";
      $rawMaterialRowDetail.="<tr style='background-color:lightblue;'>";
          
          $rawMaterialRowDetail.="<td colspan=3>". (array_key_exists($rawMaterialId,$rawMaterialList)?$rawMaterialList[$rawMaterialId]:'-')."</td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantityStockSelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantitySoldSelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantityReclassifiedSelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantityTransferredSelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceStockSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSoldSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceReclassifiedSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceTransferredSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedRawMaterial."</span></td>";
          $rawMaterialRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)."</span></td>";
          if (!empty($totalPriceSelectedRawMaterial)){
            $rawMaterialRowDetail.="<td class='centered percentage'><span>".(100*($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)/$totalPriceSelectedRawMaterial)."</span></td>";
          }
          else{
            $rawMaterialRowDetail.="<td class='centered percentage'><span>0</span></td>";
          }
      $rawMaterialRowDetail.="</tr>";
      
      $rawMaterialRowSummary="";
      $rawMaterialRowSummary.="<tr style='background-color:lightblue;'>";
        $rawMaterialRowSummary.="<td colspan=2>".(array_key_exists($rawMaterialId,$rawMaterialList)?$rawMaterialList[$rawMaterialId]:'-')."</td>";
        $rawMaterialRowSummary.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
        $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostSelectedRawMaterial."</span></td>";
        $rawMaterialRowSummary.="<td class='centered number'>".$totalQuantitySelectedRawMaterial."</td>";
        $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSelectedRawMaterial."</span></td>";
        $rawMaterialRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)."</span></td>";
        if (!empty($totalPriceSelectedRawMaterial)){
          $rawMaterialRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceSelectedRawMaterial-$totalCostSelectedRawMaterial)/$totalPriceSelectedRawMaterial)."</span></td>";
        }
        else {
          $rawMaterialRowSummary.="<td class='centered percentage'><span>0</span></td>";
        }
      $rawMaterialRowSummary.="</tr>";
        
      $rawMaterialSummaryBlock.=$rawMaterialRowSummary.$productSummaryBlock.$rawMaterialRowSummary;
      $rawMaterialDetailBlock.=$rawMaterialRowDetail.$productDetailBlock.$rawMaterialRowDetail;
    }
    
    $totalRowDetail="";
    $totalRowDetail.="<tr class='totalrow'>";
        $totalRowDetail.="<td>Total</td>";
        $totalRowDetail.="<td></td>";
        $totalRowDetail.="<td></td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantityStockAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantitySoldAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantityReclassifiedAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantityTransferredAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceStockAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceSoldAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceReclassifiedAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceTransferredAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllRawMaterials."</span></td>";
        $totalRowDetail.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceAllRawMaterials-$totalCostAllRawMaterials)."</span></td>";
        if (!empty($totalPriceAllRawMaterials)){
          $totalRowDetail.="<td class='centered percentage'><span>".(100*($totalPriceAllRawMaterials-$totalCostAllRawMaterials)/$totalPriceAllRawMaterials)."</span></td>";
        }
        else {
          $totalRowDetail.="<td class='centered percentage'><span>0</span></td>";
        }
    $totalRowDetail.="</tr>";
    
    $totalRowSummary="";
    $totalRowSummary.="<tr class='totalrow'>";
      $totalRowSummary.="<td>Total</td>";
      $totalRowSummary.="<td></td>";
      $totalRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllRawMaterials."</span></td>";
      $totalRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllRawMaterials."</span></td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceAllRawMaterials-$totalCostAllRawMaterials)."</span></td>";
      if (!empty($totalPriceAllRawMaterials)){
        $totalRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceAllRawMaterials-$totalCostAllRawMaterials)/$totalPriceAllRawMaterials)."</span></td>";
      }
      else {
        $totalRowSummary.="<td class='centered percentage'><span>0</span></td>";
      }
    $totalRowSummary.="</tr>";
      
    
    
    $utilityDetailsTable="<table id='utility_details'>".$utilityDetailsTableHeader.$totalRowDetail.$rawMaterialDetailBlock.$totalRowDetail."</table>";
    $utilitySummaryTable="<table id='utility_summary'>".$utilitySummaryTableHeader.$totalRowSummary.$rawMaterialSummaryBlock.$totalRowSummary."</table>";
  }
	echo "<h2>".__('Machine')." ".$machine['Machine']['name']."</h2>";
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-md-4'>";
        echo "<dl>";
          echo "<dt>".__('Description')."</dt>";
          echo "<dd>".(empty($machine['Machine']['description'])?"-":h($machine['Machine']['description']))."</dd>";
          echo "<dt>".__('Plant')."</dt>";
          echo '<dd>'.(empty($machine['Plant']['id'])?"-":($this->Html->Link($machine['Plant']['name'],['controller'=>'plants','action'=>'detalle',$machine['Plant']['id']]))).'</dd>';
          
          echo "<dt>".__('Activo')."</dt>";
          echo "<dd>".($machine['Machine']['bool_active']?"Activo":"Deshabilitado")."</dd>";
        echo "</dl>";
        echo $this->Form->create('Report'); 
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
        echo "</fieldset>";
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";
      echo "<div class='col-md-8'>";
        if ($roleId==ROLE_ADMIN){
          echo "<h3>Utilidad por Materia Prima</h3>";
          echo $utilitySummaryTable;
        }
      echo "</div>";
    echo "</div>";
  echo "</div>";  
	
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Machine'), array('action' => 'edit', $machine['Machine']['id']))."</li>";
			echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Machine'), array('action' => 'delete', $machine['Machine']['id']), array(), __('Are you sure you want to delete # %s?', $machine['Machine']['id']))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Machines'), array('action' => 'index'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Machine'), array('action' => 'add'))."</li>";
		}
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
		foreach ($otherMachines as $otherMachine){
			echo "<li>".$this->Html->link($otherMachine['Machine']['name'], array('controller' => 'machines', 'action' => 'view',$otherMachine['Machine']['id']))."</li>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php
  if ($roleId==ROLE_ADMIN){  
    echo "<h3>Utilidad detallado por Materia Prima</h3>";
    echo "<p class='comment'>El valor del precio se calcula tomando en cuenta el porcentaje de las ventas.  Ya que se calcula la utilidad de un producto fabricado pero no necesariamente vendido, la utilidad solo se conocerá en el momento que todo se habrá vendido.  También, si hay parte de la producción que se reclasifica o transfiere entre bodegas, no se da seguimiento a la venta eventual en favor de un cálculo más rápido.</p>";
    echo $utilityDetailsTable;
  }
	if (!empty($producedProducts)){
		echo "<h3>Productos fabricados en la máquina en el período</h3>";
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Raw Material')."</th>";
					echo "<th>".__('Finished Product')."</th>";
					foreach ($productionResultCodes as $productionResultCode){
						echo "<th class='centered'>".$productionResultCode['ProductionResultCode']['code']."</th>";
					}
					echo "<th class='centered'>".__('Total Value')."</th>";
				echo "</tr>";
			echo "</thead>";
			
			echo "<tbody>";
			
			$totalQuantityA=0;
			$totalQuantityB=0;
			$totalQuantityC=0;
			$totalValue=0;
			
			$productOverview="";
			foreach ($producedProducts as $producedProduct){
				$productOverview.="<tr>";
					$productOverview.="<td>".$this->Html->link($producedProduct['raw_material_name'], array('controller' => 'products','action' => 'view',$producedProduct['raw_material_id']))."</td>";
					$productOverview.="<td>".$this->Html->link($producedProduct['finished_product_name'], array('controller' => 'products','action' => 'view',$producedProduct['finished_product_id']))."</td>";
					$productOverview.="<td class='centered number'>".$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_A]."</td>";
					$productOverview.="<td class='centered number'>".$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_B]."</td>";
					$productOverview.="<td class='centered number'>".$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_C]."</td>";
					$productOverview.="<td class='centered currency'><span>".$producedProduct['total_value']."</span></td>";
				$productOverview.="</tr>";
				$totalQuantityA+=$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_A];
				$totalQuantityB+=$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_B];
				$totalQuantityC+=$producedProduct['produced_quantities'][PRODUCTION_RESULT_CODE_C];
				$totalValue+=$producedProduct['total_value'];
			}
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityB."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityC."</td>";
					$totalRows.="<td class='centered currency'><span>".$totalValue."</span></td>";
				$totalRows.="</tr>";
			
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityA/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityB/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityC/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					$totalRows.="<td></td>";
				$totalRows.="</tr>";
			echo $totalRows.$productOverview.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}
	if (!empty($producedProductsPerOperator)){
		echo "<h3>Productos fabricados en la máquina por cada operador en el período</h3>";
		echo "<table class='grid'>";
			echo "<thead>";
			// First the line with the raw material names
				echo "<tr>";
					echo "<th></th>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						//pr($rawMaterial);
						echo "<th  class='centered' colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
					}
				echo "</tr>";
			echo "</thead>";
					
			echo "<tbody>";
			// Then the line with the finished product names 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered' colspan='3'>".$product['finished_product_name']."</td>";					
							}
						}
					}
				echo "</tr>";

				// Then the line with the production result codes 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerOperator[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered'>A</td>";					
								echo "<td class='centered'>B</td>";
								echo "<td class='centered'>C</td>";
							}
						}
					}
				echo "</tr>";
			
				$totalsArray=array();
				//pr($producedProductsPerOperator);
				$firstrow=true;
				$operatorRows="";
				foreach ($producedProductsPerOperator as $operatorData){
					$operatorRow="";
					$productQuantityForRow=0;
					$operatorRow.="<tr>";
						$operatorRow.="<td>".$this->Html->link($operatorData['operator_name'], array('controller' => 'operators','action' => 'view',$operatorData['operator_id']))."</td>";
						$productCounter=0;
						foreach ($operatorData['rawmaterial'] as $rawMaterial){
							foreach ($rawMaterial['products'] as $product){
								if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
									foreach($product['product_quantity'] as $quantity){
										if ($quantity>0){
											$operatorRow.="<td class='centered bold number'>".$quantity."</td>";
										}
										else {
											$operatorRow.="<td class='centered'>-</td>";
										}
										if ($firstrow){
											$totalsArray[$productCounter]=$quantity;
										}
										else{
											$totalsArray[$productCounter]+=$quantity;
										}
										$productQuantityForRow+=$quantity;
										$productCounter++;
									}
								}
							}
						}
						//pr($totalsArray);
						$firstrow=false;
					$operatorRow.="</tr>";
					if ($productQuantityForRow){
						$operatorRows.=$operatorRow;
					}
				}
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						$totalRows.="<td class='centered number'>".$totalsArray[$i]."</td>";
					}
				$totalRows.="</tr>";
				
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						if ($i%3==0){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i]+$totalsArray[$i+1]+$totalsArray[$i+2]))."</span></td>";
						}
						elseif ($i%3==1){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-1]+$totalsArray[$i]+$totalsArray[$i+1]))."</span></td>";
						}
						elseif ($i%3==2){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-2]+$totalsArray[$i-1]+$totalsArray[$i]))."</span></td>";
						}
					}
				$totalRows.="</tr>";
				echo $totalRows.$operatorRows.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}

	if (!empty($producedProductsPerShift)){
		echo "<h3>Productos fabricados por el operador en cada turno en el período</h3>";
		echo "<table class='grid'>";
			echo "<thead>";
			// First the line with the raw material names
				echo "<tr>";
					echo "<th></th>";
					foreach ($producedProductsPerShift[0]['rawmaterial'] as $rawMaterial){
						//pr($rawMaterial);
						echo "<th  class='centered' colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
					}
				echo "</tr>";
			echo "</thead>";
					
			echo "<tbody>";
			// Then the line with the finished product names 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerShift[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered' colspan='3'>".$product['finished_product_name']."</td>";					
							}
						}
					}
				echo "</tr>";

				// Then the line with the production result codes 
				echo "<tr>";
					echo "<td></td>";
					foreach ($producedProductsPerShift[0]['rawmaterial'] as $rawMaterial){
						foreach ($rawMaterial['products'] as $product){
							if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
								echo "<td class='centered'>A</td>";					
								echo "<td class='centered'>B</td>";
								echo "<td class='centered'>C</td>";
							}
						}
					}
				echo "</tr>";
			
				$totalsArray=array();
				//pr($producedProductsPerShift);
				$firstrow=true;
				$shiftRows="";
				foreach ($producedProductsPerShift as $shiftData){
					$shiftRow="";
					$quantityForShift=0;
					$shiftRow.="<tr>";
						$shiftRow.="<td>".$this->Html->link($shiftData['shift_name'], array('controller' => 'shifts','action' => 'view',$shiftData['shift_id']))."</td>";
						$productCounter=0;
						foreach ($shiftData['rawmaterial'] as $rawMaterial){
							foreach ($rawMaterial['products'] as $product){
								if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
									foreach($product['product_quantity'] as $quantity){
										if ($quantity>0){
											$shiftRow.="<td class='centered bold number'>".$quantity."</td>";
										}
										else {
											$shiftRow.="<td class='centered'>-</td>";
										}
										if ($firstrow){
											$totalsArray[$productCounter]=$quantity;
										}
										else{
											$totalsArray[$productCounter]+=$quantity;
										}
										$quantityForShift+=$quantity;
										$productCounter++;
									}
								}
							}
						}
						//pr($totalsArray);
						$firstrow=false;
					$shiftRow.="</tr>";
					if ($quantityForShift>0){
						$shiftRows.=$shiftRow;
					}
				}
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						$totalRows.="<td class='centered number'>".$totalsArray[$i]."</td>";
					}
				$totalRows.="</tr>";
			
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					for ($i=0;$i<count($totalsArray);$i++){
						if ($i%3==0){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i]+$totalsArray[$i+1]+$totalsArray[$i+2]))."</span></td>";
						}
						elseif ($i%3==1){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-1]+$totalsArray[$i]+$totalsArray[$i+1]))."</span></td>";
						}
						elseif ($i%3==2){
							$totalRows.="<td class='centered percentage'><span>".(100*$totalsArray[$i]/($totalsArray[$i-2]+$totalsArray[$i-1]+$totalsArray[$i]))."</span></td>";
						}
					}
				$totalRows.="</tr>";
				echo $totalRows.$shiftRows.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}
	if (!empty($machine['ProductionRun'])){
		echo "<h3>".__('Related Production Runs for Machine')."</h3>";
	
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<!--th>".__('Id')."</th-->";
					echo "<th>".__('Production Run Code')."</th>";
					echo "<th>".__('Production Run Date')."</th>";
					echo "<th>".__('Materia Prima')."</th>";
					echo "<th>".__('Producto')."</th>";
					
					echo "<th class='centered'>".__('Cantidad A')."</th>";
					echo "<th class='centered'>".__('Cantidad B')."</th>";
					echo "<th class='centered'>".__('Cantidad C')."</th>";
					
					echo "<th class='centered'>".__('Valor A')."</th>";
					echo "<th class='centered'>".__('Valor B')."</th>";
					echo "<th class='centered'>".__('Valor C')."</th>";
					
					echo "<th class='centered'>".__('Cantidad Total')."</th>";
					echo "<th class='centered'>".__('Total Value')."</th>";
					
					echo "<th>".__('Operator')."</th>";
					echo "<th>".__('Shift')."</th>";
					echo "<th class='centered'>".__('Energy Use')."</th>";
					
					echo "<th class='actions'>".__('Actions')."</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
				$totalquantityA=0;
				$totalquantityB=0;
				$totalquantityC=0;
				$totalvalueA=0;
				$totalvalueB=0;
				$totalvalueC=0;
				$totalquantity=0;
				$totalvalue=0;
				$totalenergy=0;
				$productionRunRows="";
				foreach ($machine['ProductionRun'] as $productionRun){
					$productionrundate= new DateTime($productionRun['production_run_date']); 
					$productionRunRows.="<tr>";
						$productionRunRows.="<td>".$this->Html->link($productionRun['production_run_code'], array('controller' => 'production_runs', 'action' => 'view', $productionRun['id']))."</td>";
						
						$productionRunRows.="<td>".$productionrundate->format('d-m-Y')."</td>";
						$productionRunRows.="<td>".(empty($productionRun['RawMaterial'])?'-':$productionRun['RawMaterial']['name'])."</td>";
						$productionRunRows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
						$quantityA=0;
						$quantityB=0;
						$quantityC=0;
						$valueA=0;
						$valueB=0;
						$valueC=0;
						$unitprice=0;
						foreach ($productionRun['ProductionMovement'] as $productionMovement){
							$unitprice=$productionMovement['product_unit_price'];
							if (!$productionMovement['bool_input']){
								switch ($productionMovement['production_result_code_id']){
									case 1:
										$quantityA+=$productionMovement['product_quantity'];
										$totalquantityA+=$quantityA;
										$totalvalueA+=$quantityA*$unitprice;
										$totalquantity+=$quantityA;
										$totalvalue+=$quantityA*$unitprice;
										break;
									case 2:
										$quantityB+=$productionMovement['product_quantity'];
										$totalquantityB+=$quantityB;
										$totalvalueB+=$quantityB*$unitprice;
										$totalquantity+=$quantityB;
										$totalvalue+=$quantityB*$unitprice;
										break;
									case 3:
										$quantityC+=$productionMovement['product_quantity'];
										$totalquantityC+=$quantityC;
										$totalvalueC+=$quantityC*$unitprice;
										$totalquantity+=$quantityC;
										$totalvalue+=$quantityC*$unitprice;
										break;
								}
							}
						}

					$productionRunRows.="<td class='centered number'><span>".$quantityA."</span></td>";
					$productionRunRows.="<td class='centered number'><span>".$quantityB."</span></td>";
					$productionRunRows.="<td class='centered number'><span>".$quantityC."</span></td>";
					
					$productionRunRows.="<td class='centered currency'><span>".$quantityA*$unitprice."</span></td>";
					$productionRunRows.="<td class='centered currency'><span>".$quantityB*$unitprice."</span></td>";
					$productionRunRows.="<td class='centered currency'><span>".$quantityC*$unitprice."</span></td>";
					
					$productionRunRows.="<td class='centered number'><span>".($quantityA+$quantityB+$quantityC)."</span></td>";
					$productionRunRows.="<td class='centered currency'><span>".($quantityA+$quantityB+$quantityC)*$unitprice."</span></td>";
					
					$productionRunRows.="<td>".$productionRun['Operator']['name']."</td>";
					$productionRunRows.="<td>".$productionRun['Shift']['name']."</td>";
					$productionRunRows.="<td class='centered number'><span>".$energyConsumption[$productionRun['id']]."</span></td>";
					$totalenergy+=$energyConsumption[$productionRun['id']]; 
					$productionRunRows.="<td class='actions'>";
						$productionRunRows.=$this->Html->link(__('View'), array('controller' => 'production_runs', 'action' => 'view', $productionRun['id']));
						if ($bool_productionrun_edit_permission){ 
							$productionRunRows.=$this->Html->link(__('Edit'), array('controller' => 'production_runs', 'action' => 'edit', $productionRun['id']));
						} 				
					$productionRunRows.="</td>";
				$productionRunRows.="</tr>";
			}
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='centered number'><span>".$totalquantityA."</span></td>";
					$totalRows.="<td class='centered number'><span>".$totalquantityB."</span></td>";
					$totalRows.="<td class='centered number'><span>".$totalquantityC."</span></td>";
					
					$totalRows.="<td class='centered currency'><span>".$totalvalueA."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$totalvalueB."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$totalvalueC."</span></td>";
					
					$totalRows.="<td class='centered number'><span>".$totalquantity."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$totalvalue."</span></td>";
							
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='centered number'><span>".$totalenergy."</span></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
				$totalRows.="</tr>";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalquantityA/$totalquantity)."</span></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalquantityB/$totalquantity)."</span></td>";
					$totalRows.="<td class='centered percentage'><span>".(100*$totalquantityC/$totalquantity)."</span></td>";
					
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
							
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
				$totalRows.="</tr>";
			echo $totalRows.$productionRunRows.$totalRows;

			echo "</tbody>";
		echo "</table>";
	}
?>
</div>