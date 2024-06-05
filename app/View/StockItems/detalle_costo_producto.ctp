<script>
	$('body').on('change','#ReportCurrencyId',function(){
		$('#ReportInventarioForm').submit();
	});
	
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		var currencyName=$('#ReportCurrencyId option:selected').text();
		$("td.currency span.amountright").each(function(){
			$(this).number(true,4);
			
			$(this).parent().find('span.currency').text(currencyName);
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});
</script>

<div class="stockItems costdetail">
<?php 	
	echo "<h2>Detalle de Costo de Producto</h2>";

	echo $this->Form->create('Report'); 
	
	echo "<fieldset>"; 
		echo  $this->Form->input('Report.inventorydate',array('type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate,'minYear'=>2014,'maxYear'=>date('Y')));
    echo  $this->Form->input('Report.finished_product_id',array('label'=>__('Producto fabricado'),'default'=>$finishedProductId,'empty'=>array(0=>'Seleccione Producto Fabricado')));
    echo  $this->Form->input('Report.raw_material_id',array('label'=>__('Materia Prima'),'default'=>$rawMaterialId,'empty'=>array(0=>'Seleccione Materia Prima')));
	echo  "</fieldset>";
	//echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	//echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo "<br/>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarDetalleCostoProducto'), array( 'class' => 'btn btn-primary')); 
	echo "<br/>";
	
  $inventoryDateTime=new DateTime($inventoryDate);
	
	$productCostTable="";
	if (!empty($stockItems)){
    $averageCost=0;
    $quantityInventoryProducts=0; 
    $totalValueInventoryProducts=0;
		$productCostTable.="<table id='costo_producto' cellpadding='0' cellspacing='0'>";
			$productCostTable.="<thead>";
				$productCostTable.="<tr>";
					$productCostTable.="<th>Fecha Entrada</th>";
          $productCostTable.="<th># Entrada</th>";
          $productCostTable.="<th>Proveedor</th>";
          $productCostTable.="<th>Fecha Producción</th>";
          $productCostTable.="<th># Producción</th>";
          $productCostTable.="<th>Calidad</th>";
          $productCostTable.="<th>Costo Unitario</th>";
          $productCostTable.="<th>Cant.</th>";
          $productCostTable.="<th>Costo Total</th>";
				$productCostTable.="</tr>";
			$productCostTable.="</thead>";
			$productCostTable.="<tbody>";

			
			$tableRows="";
			foreach ($stockItems as $stockItem){
        if (empty($stockItem['ProductionRun'])||empty($stockItem['Entry'])){
          //pr($stockItem);
        }
        
        if (!empty($stockItem['ProductionRun'])){
          
        }
        $quantityInventoryProducts+=$stockItem['product_quantity']; 
        $totalValueInventoryProducts+=$stockItem['product_quantity']*$stockItem['product_unit_price'];
			 
				$tableRows.="<tr>";
          if (!empty($stockItem['Entry'])){
            //pr($stockItem['Entry']);
            $tableRows.="<td class='centered'>";
            foreach ($stockItem['Entry'] as $entry){
              $entryDateTime=new DateTime($entry['order_date']); 
              $tableRows.=$entryDateTime->format('d-m-Y')."<br/>";
            }
            $tableRows.="</td>";
            $tableRows.="<td>";
            foreach ($stockItem['Entry'] as $entry){
              $tableRows.=$this->Html->link($entry['order_code'], array('controller' => 'orders', 'action' => 'verEntrada', $entry['id']))."<br/>";
            }
            $tableRows."</td>";
            $tableRows.="<td>";
            foreach ($stockItem['Entry'] as $entry){
              $tableRows.=$this->Html->link($entry['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verProveedor', $entry['ThirdParty']['id']))."<br/>";
            }
            $tableRows.="</td>";
          }
          else {
            $tableRows.="<td class='centered'>-</td>";
            $tableRows.="<td class='centered'>-</td>";
            $tableRows.="<td class='centered'>-</td>";
          }
          if (!empty($stockItem['ProductionRun'])){
            $productionRunDateTime=new DateTime($stockItem['ProductionRun']['production_run_date']);
            $tableRows.="<td class='centered'>".$productionRunDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$this->Html->link($stockItem['ProductionRun']['production_run_code'], array('controller' => 'production_runs', 'action' => 'view', $stockItem['ProductionRun']['id']))."</td>";
					}
          else {
            $tableRows.="<td class='centered'>-</td>";
            $tableRows.="<td class='centered'>-</td>";
          }
          $tableRows.="<td class='centered'>".$productionResultCodes[$stockItem['production_result_code_id']]."</td>";
          $tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$stockItem['product_unit_price']."</span></td>";
					$tableRows.="<td class='centered number'>".$stockItem['product_quantity']."</td>";
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".($stockItem['product_unit_price']*$stockItem['product_quantity'])."</span></td>";					
				$tableRows.="</tr>";
			}
				$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
					$totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
					if($quantityInventoryProducts>0){
						$averageCost=$totalValueInventoryProducts/$quantityInventoryProducts;
					}
          $totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$averageCost."</span></td>";
					$totalRow.="<td class='centered number'>".$quantityInventoryProducts."</td>";
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalValueInventoryProducts."</span></td>";
				$totalRow.="</tr>";
				$productCostTable.=$totalRow.$tableRows.$totalRow;
			$productCostTable.="</tbody>";
		$productCostTable.="</table>";
    echo "<h2>El costo promedio del producto ".$finishedProducts[$finishedProductId]." con preforma ".$rawMaterials[$rawMaterialId]." el día ".$inventoryDateTime->format('d-m-Y')." es: ".number_format($totalValueInventoryProducts,2,".",",")." / ".number_format($quantityInventoryProducts,2,".",",")." = ".number_format($averageCost,4,".",",")."</h2>";
    echo "<p class='comment'>note que si se produce un lote utilizando preformas de diferentes costos (de 2 entradas diferentes), el costo resultando es el promedio ponderado de los costos</p>";
    echo $productCostTable;
	}
  else {
    if ($finishedProductId>0 && $rawMaterialId>0){
      echo "<h2>No hay inventario para el producto ".$finishedProducts[$finishedProductId]." con preforma ".$rawMaterials[$rawMaterialId]." el día ".$inventoryDateTime->format('d-m-Y')."</h2>";  
    }
  }
  
	
	$_SESSION['inventoryReport'] = $productCostTable;
?>

</div>