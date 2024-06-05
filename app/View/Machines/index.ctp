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
<div class="machines index">
<?php 
	echo "<h2>".__('Machines')."</h2>";
	//pr($machines);
	echo "<table cellpadding='0' cellspacing='0'>";
    echo "<thead>";
      echo "<tr>";
        echo "<th>".$this->Paginator->sort('plant_id',__('Plant'))."</th>";
        echo "<th>".$this->Paginator->sort('name')."</th>";
        echo "<th>".$this->Paginator->sort('description')."</th>";
        echo "<th class='actions'>".__('Actions')."</th>";
      echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
		foreach ($machines as $machine){
			if ($machine['Machine']['bool_active']){
				echo "<tr>";
			}
			else {
				echo "<tr class='italic'>";
			}
        echo '<td>'.$this->Html->link($machine['Plant']['name'],['controller'=>'plants','action'=>'detalle',$machine['Plant']['id']]).'</td>';
				echo "<td>".$this->Html->link($machine['Machine']['name'].($machine['Machine']['bool_active']?"":" (Deshabilitada)"),array('action'=>'view',$machine['Machine']['id']))."</td>";
				echo "<td>".$machine['Machine']['description']."</td>";
				echo "<td class='actions'>";
					if($bool_edit_permission){
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $machine['Machine']['id'])); 
					}
					if($bool_delete_permission){
						// echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $machine['Machine']['id']), array(), __('Are you sure you want to delete # %s?', $machine['Machine']['id']));
					}
				echo "</td>";
			echo "</tr>";
		}
    echo "</tbody>";
	echo "</table>";
?>  
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Machine'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
<div class='related'>
<?php 
  if ($userRoleId == ROLE_ADMIN){
    $utilitySummaryTableHeader="<thead>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th>Máquina</th>";
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
          $productRowSummary.="<tr>";
            $productRowSummary.="<td></td>";
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
        $rawMaterialRowSummary.="<tr style='background-color:lightblue;'>";
          $rawMaterialRowSummary.="<td></td>";
          $rawMaterialRowSummary.="<td colspan=2>".$rawMaterialList[$rawMaterialId]."</td>";
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
      $machineRowSummary.="<tr class='totalrow'>";
        $machineRowSummary.="<td>".$machine['Machine']['name']."</td>";
        $machineRowSummary.="<td></td>";
        $machineRowSummary.="<td></td>";
        $machineRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
        $machineRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllRawMaterials."</span></td>";
        $machineRowSummary.="<td class='centered number'>".$totalQuantityAllRawMaterials."</td>";
        $machineRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllRawMaterials."</span></td>";
        $machineRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceAllRawMaterials-$totalCostAllRawMaterials)."</span></td>";
        if (!empty($totalPriceAllRawMaterials)){
          $machineRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceAllRawMaterials-$totalCostAllRawMaterials)/$totalPriceAllRawMaterials)."</span></td>";
        }
        else {
          $machineRowSummary.="<td class='centered percentage'><span>0</span></td>";
        }
      $machineRowSummary.="</tr>";
      
      $machineSummaryBlock.=$machineRowSummary.$rawMaterialSummaryBlock.$machineRowSummary;
    }      
    
    $totalRowSummary="";
    $totalRowSummary.="<tr class='totalrow'>";
      $totalRowSummary.="<td>Total</td>";
      $totalRowSummary.="<td></td>";
      $totalRowSummary.="<td></td>";
      $totalRowSummary.="<td class='centered number'>".$totalQuantityAllMachines."</td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalCostAllMachines."</span></td>";
      $totalRowSummary.="<td class='centered number'>".$totalQuantityAllMachines."</td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".$totalPriceAllMachines."</span></td>";
      $totalRowSummary.="<td class='centered currency'><span class='currency'>C$ </span><span class='amountright'>".($totalPriceAllMachines-$totalCostAllMachines)."</span></td>";
      if (!empty($totalPriceAllMachines)){
        $totalRowSummary.="<td class='centered percentage'><span>".(100*($totalPriceAllMachines-$totalCostAllMachines)/$totalPriceAllMachines)."</span></td>";
      }
      else {
        $totalRowSummary.="<td class='centered percentage'><span>0</span></td>";
      }
    $totalRowSummary.="</tr>";
    
    $utilitySummaryTable="<table id='utility_summary'>".$utilitySummaryTableHeader.$totalRowSummary.$machineSummaryBlock.$totalRowSummary."</table>";


    echo "<h3>Utilidad por Materia Prima</h3>";
    echo "<p class='comment'>La utilidad se calcula en base a la producción y sus ventas desde septiembre 2014.  El valor del precio se calcula tomando en cuenta el porcentaje de las ventas.  Ya que se calcula la utilidad de un producto fabricado pero no necesariamente vendido, la utilidad final solo se conocerá en el momento que todo se habrá vendido.  También, si hay parte de la producción que se reclasifica o transfiere entre bodegas, no se da seguimiento a la venta eventual en favor de un cálculo más rápido.</p>";
    
    echo $utilitySummaryTable;
  }
?>
</div>

