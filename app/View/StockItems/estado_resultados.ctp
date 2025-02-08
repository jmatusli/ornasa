<script>
	function formatNumbers(){
		$("td.number span").each(function(){
      if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
      if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
      $(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
		formatCurrencies();
	});
</script>
<div class="stockItems view report">

<?php 
	echo "<h2>".__('Estado de Resultados')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
    echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteEstado'), array( 'class' => 'btn btn-primary')); 
	echo "<br/>";
	echo "<br/>";

	// visualizar totales
  
  $consumibleMaterialTable="<table id='insumos'>";
		$consumibleMaterialTable.="<thead>";
			$consumibleMaterialTable.="<tr>";
				$consumibleMaterialTable.="<th class='hidden'>Product Id</th>";
				$consumibleMaterialTable.="<th>".__('Product')."</th>";
				$consumibleMaterialTable.="<th class='centered'>".__('Cantidad')."</th>";
				//$consumibleMaterialTable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$consumibleMaterialTable.="<th class='centered'>".__('Costo de Compra')."</th>";
				//$consumibleMaterialTable.="<th class='centered'>".__('Utilidad')."</th>";
				//$consumibleMaterialTable.="<th class='centered'>".__('Margen Utilidad')."</th>";
			$consumibleMaterialTable.="</tr>";
		$consumibleMaterialTable.="</thead>";
		$consumibleMaterialTable.="<tbody>";
    $total_quantity_all=0;
		$total_price_all=0;
		$total_cost_all=0;
		$total_gain_all=0;
		foreach ($consumibleMaterials as $consumibleMaterial){
			$total_quantity_all+=$consumibleMaterial['total_quantity'];
			$total_price_all+=$consumibleMaterial['total_price'];
			$total_cost_all+=$consumibleMaterial['total_cost']; 
			$total_gain_all+=$consumibleMaterial['total_gain'];

			$consumibleMaterialTable.="<tr>"; 			
				$consumibleMaterialTable.="<td class='hidden'>".$consumibleMaterial['id']."</td>";
				$consumibleMaterialTable.="<td>".$this->Html->link($consumibleMaterial['name'], ['controller' => 'products', 'action' => 'view', $consumibleMaterial['id']])."</td>";
				$consumibleMaterialTable.="<td class='centered number'><span>".$consumibleMaterial['total_quantity']."</span></td>";
				//$consumibleMaterialTable.="<td class='centered currency'><span>".$consumibleMaterial['total_price']."</span></td>";
				$consumibleMaterialTable.="<td class='centered currency'><span>".$consumibleMaterial['total_cost']."</span></td>";
				//$consumibleMaterialTable.="<td class='centered currency'><span>".$consumibleMaterial['total_gain']."</span></td>";
				//if (!empty($consumibleMaterial['total_price'])){
				//	$consumibleMaterialTable.="<td class='centered percentage'><span>".(100*$consumibleMaterial['total_gain']/$consumibleMaterial['total_price'])."</span></td>";
				//}
				//else {
				//	$consumibleMaterialTable.="<td class='centered percentage'><span>0</span></td>";
				//}
			$consumibleMaterialTable.="</tr>";
		}
			$consumibleMaterialTable.="<tr class='totalrow'>";
				$consumibleMaterialTable.="<td>Total</td>";
				$consumibleMaterialTable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				//$consumibleMaterialTable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$consumibleMaterialTable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				//$consumibleMaterialTable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";			
				//if (!empty($total_price_all)){
				//	$consumibleMaterialTable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				//}
				//else {
				//	$consumibleMaterialTable.="<td class='centered percentage'><span>0</span></td>";
				//}
			$consumibleMaterialTable.="</tr>";
		$consumibleMaterialTable.="</tbody>";
	$consumibleMaterialTable.="</table>";	
  
	
  $rawMaterialTable="<table id='preformas'>";
		$rawMaterialTable.="<thead>";
			$rawMaterialTable.="<tr>";
				$rawMaterialTable.="<th class='hidden'>Product Id</th>";
				$rawMaterialTable.="<th>".__('Product')."</th>";
				$rawMaterialTable.="<th class='centered'>".__('Cantidad')."</th>";
				$rawMaterialTable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$rawMaterialTable.="<th class='centered'>".__('Costo de Producción')."</th>";
				$rawMaterialTable.="<th class='centered'>".__('Utilidad')."</th>";			
				$rawMaterialTable.="<th class='centered'>".__('Margen Utilidad')."</th>";			
			$rawMaterialTable.="</tr>";
		$rawMaterialTable.="</thead>";
	
		$rawMaterialTable.="<tbody>";

		$total_quantity_all=0;
		$total_price_all=0;
		$total_cost_all=0;
		$total_gain_all=0;
		foreach ($rawMaterials as $rawMaterial){
			$total_quantity_all+=$rawMaterial['total_quantity'];
			$total_price_all+=$rawMaterial['total_price'];
			$total_cost_all+=$rawMaterial['total_cost']; 
			$total_gain_all+=$rawMaterial['total_gain'];
			$rawMaterialTable.="<tr>"; 			
				$rawMaterialTable.="<td class='hidden'>".$rawMaterial['id']."</td>";
				$rawMaterialTable.="<td>".$this->Html->link($rawMaterial['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $rawMaterial['id']))."</td>";
				$rawMaterialTable.="<td class='centered number'><span>".$rawMaterial['total_quantity']."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$rawMaterial['total_price']."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$rawMaterial['total_cost']."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$rawMaterial['total_gain']."</span></td>";
				if (!empty($rawMaterial['total_price'])){
					$rawMaterialTable.="<td class='centered percentage'><span>".(100*$rawMaterial['total_gain']/$rawMaterial['total_price'])."</span></td>";
				}
				else {
					$rawMaterialTable.="<td class='centered percentage'><span>0</span></td>";
				}			
			$rawMaterialTable.="</tr>";
		}
			$rawMaterialTable.="<tr class='totalrow'>";			
				$rawMaterialTable.="<td class='hidden'></td>";
				$rawMaterialTable.="<td>Total</td>";
				$rawMaterialTable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				$rawMaterialTable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";
				if (!empty($total_price_all)){
					$rawMaterialTable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				}
				else {
					$rawMaterialTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$rawMaterialTable.="</tr>";
		$rawMaterialTable.="</tbody>";
	$rawMaterialTable.="</table>";
  
  
  
	$producedmaterialtable="<table id='botellas'>";
		$producedmaterialtable.="<thead>";
			$producedmaterialtable.="<tr>";
				$producedmaterialtable.="<th class='hidden'>Product Id</th>";
				$producedmaterialtable.="<th>".__('Product')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Cantidad')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Costo de Producción')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Utilidad')."</th>";			
				$producedmaterialtable.="<th class='centered'>".__('Margen Utilidad')."</th>";			
			$producedmaterialtable.="</tr>";
		$producedmaterialtable.="</thead>";
	
		$producedmaterialtable.="<tbody>";

		$total_quantity_all=0;
		$total_price_all=0;
		$total_cost_all=0;
		$total_gain_all=0;
    
    
    
		foreach ($producedMaterials as $producedmaterial){
			$total_quantity_all+=$producedmaterial['total_quantity'];
			$total_price_all+=$producedmaterial['total_price'];
			$total_cost_all+=$producedmaterial['total_cost']; 
			$total_gain_all+=$producedmaterial['total_gain'];
			$producedmaterialtable.="<tr>"; 			
				$producedmaterialtable.="<td class='hidden'>".$producedmaterial['id']."</td>";
				$producedmaterialtable.="<td>".$this->Html->link($producedmaterial['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['id']))."</td>";
				$producedmaterialtable.="<td class='centered number'><span>".$producedmaterial['total_quantity']."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$producedmaterial['total_price']."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$producedmaterial['total_cost']."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$producedmaterial['total_gain']."</span></td>";
				if (!empty($producedmaterial['total_price'])){
					$producedmaterialtable.="<td class='centered percentage'><span>".(100*$producedmaterial['total_gain']/$producedmaterial['total_price'])."</span></td>";
				}
				else {
					$producedmaterialtable.="<td class='centered percentage'><span>0</span></td>";
				}			
			$producedmaterialtable.="</tr>";
		}
			$producedmaterialtable.="<tr class='totalrow'>";			
				$producedmaterialtable.="<td class='hidden'></td>";
				$producedmaterialtable.="<td>Total</td>";
				$producedmaterialtable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				$producedmaterialtable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";
				if (!empty($total_price_all)){
					$producedmaterialtable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				}
				else {
					$producedmaterialtable.="<td class='centered percentage'><span>0</span></td>";
				}
			$producedmaterialtable.="</tr>";
		$producedmaterialtable.="</tbody>";
	$producedmaterialtable.="</table>";
  
  $totalQuantity=0;
  $totalPrice=0;
  $totalCost=0;
  $totalGain=0;
	
	$total_quantity_products=$total_quantity_all;
	$total_price_products=$total_price_all;
	$total_cost_products=$total_cost_all;
	$total_gain_products=$total_gain_all;
	
  $totalQuantity+=$total_quantity_products;
  $totalPrice+=$total_price_products;
  $totalCost+=$total_cost_products;
  $totalGain+=$total_gain_products;
  
	$othermaterialtable="<table id='tapones'>";
		$othermaterialtable.="<thead>";
			$othermaterialtable.="<tr>";
				$othermaterialtable.="<th class='hidden'>Product Id</th>";
				$othermaterialtable.="<th>".__('Product')."</th>";
				$othermaterialtable.="<th class='centered'>".__('Cantidad')."</th>";
				$othermaterialtable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$othermaterialtable.="<th class='centered'>".__('Costo de Compra')."</th>";
				$othermaterialtable.="<th class='centered'>".__('Utilidad')."</th>";
				$othermaterialtable.="<th class='centered'>".__('Margen Utilidad')."</th>";
			$othermaterialtable.="</tr>";
		$othermaterialtable.="</thead>";
		$othermaterialtable.="<tbody>";
    $total_quantity_all=0;
		$total_price_all=0;
		$total_cost_all=0;
		$total_gain_all=0;
		foreach ($otherMaterials as $othermaterial){
			$total_quantity_all+=$othermaterial['total_quantity'];
			$total_price_all+=$othermaterial['total_price'];
			$total_cost_all+=$othermaterial['total_cost']; 
			$total_gain_all+=$othermaterial['total_gain'];

			$othermaterialtable.="<tr>"; 			
				$othermaterialtable.="<td class='hidden'>".$othermaterial['id']."</td>";
				$othermaterialtable.="<td>".$this->Html->link($othermaterial['name'], array('controller' => 'products', 'action' => 'view', $othermaterial['id']))."</td>";
				$othermaterialtable.="<td class='centered number'><span>".$othermaterial['total_quantity']."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$othermaterial['total_price']."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$othermaterial['total_cost']."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$othermaterial['total_gain']."</span></td>";
				if (!empty($othermaterial['total_price'])){
					$othermaterialtable.="<td class='centered percentage'><span>".(100*$othermaterial['total_gain']/$othermaterial['total_price'])."</span></td>";
				}
				else {
					$othermaterialtable.="<td class='centered percentage'><span>0</span></td>";
				}
			$othermaterialtable.="</tr>";
		}
			$othermaterialtable.="<tr class='totalrow'>";
				$othermaterialtable.="<td>Total</td>";
				$othermaterialtable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				$othermaterialtable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";			
				if (!empty($total_price_all)){
					$othermaterialtable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				}
				else {
					$othermaterialtable.="<td class='centered percentage'><span>0</span></td>";
				}
			$othermaterialtable.="</tr>";
		$othermaterialtable.="</tbody>";
	$othermaterialtable.="</table>";	
	
   /*ingroup*/	
   	$ingrouptable="<table id='tapones'>";
		$ingrouptable.="<thead>";
			$ingrouptable.="<tr>";
				$ingrouptable.="<th class='hidden'>Product Id</th>";
				$ingrouptable.="<th>".__('Product')."</th>";
				$ingrouptable.="<th class='centered'>".__('Cantidad')."</th>";
				$ingrouptable.="<th class='centered'>".__('Precio de Venta')."</th>";
				$ingrouptable.="<th class='centered'>".__('Costo de Compra')."</th>";
				$ingrouptable.="<th class='centered'>".__('Utilidad')."</th>";
				$ingrouptable.="<th class='centered'>".__('Margen Utilidad')."</th>";
			$ingrouptable.="</tr>";
		$ingrouptable.="</thead>";
		$ingrouptable.="<tbody>";
    $total_quantity_all=0;
		$total_price_all=0;
		$total_cost_all=0;
		$total_gain_all=0;
		foreach ($productsIngroup as $itemingroup){
			$total_quantity_all+=$itemingroup['total_quantity'];
			$total_price_all+=$itemingroup['total_price'];
			$total_cost_all+=$itemingroup['total_cost']; 
			$total_gain_all+=$itemingroup['total_gain'];

			$ingrouptable.="<tr>"; 			
				$ingrouptable.="<td class='hidden'>".$itemingroup['id']."</td>";
				$ingrouptable.="<td>".$this->Html->link($itemingroup['name'], array('controller' => 'products', 'action' => 'view', $itemingroup['id']))."</td>";
				$ingrouptable.="<td class='centered number'><span>".$itemingroup['total_quantity']."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$itemingroup['total_price']."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$itemingroup['total_cost']."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$itemingroup['total_gain']."</span></td>";
				if (!empty($itemingroup['total_price'])){
					$ingrouptable.="<td class='centered percentage'><span>".(100*$itemingroup['total_gain']/$itemingroup['total_price'])."</span></td>";
				}
				else {
					$ingrouptable.="<td class='centered percentage'><span>0</span></td>";
				}
			$ingrouptable.="</tr>";
		}
			$ingrouptable.="<tr class='totalrow'>";
				$ingrouptable.="<td>Total</td>";
				$ingrouptable.="<td class='centered number'><span>".$total_quantity_all."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$total_price_all."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$total_cost_all."</span></td>";
				$ingrouptable.="<td class='centered currency'><span>".$total_gain_all."</span></td>";			
				if (!empty($total_price_all)){
					$ingrouptable.="<td class='centered percentage'><span>".(100*$total_gain_all/$total_price_all)."</span></td>";
				}
				else {
					$ingrouptable.="<td class='centered percentage'><span>0</span></td>";
				}
			$ingrouptable.="</tr>";
		$ingrouptable.="</tbody>";
	$ingrouptable.="</table>";
   /*ingroup*/	
	
	
	
/*
  $total_quantity_caps=$total_quantity_all;
	$total_price_caps=$total_price_all;
	$total_cost_caps=$total_cost_all;
	$total_gain_caps=$total_gain_all;
  
  $totalQuantity+=$total_quantity_caps;
  $totalPrice+=$total_price_caps;
  $totalCost+=$total_cost_caps;
  $totalGain+=$total_gain_caps;
*/
  $total_quantity_services=0;
  $total_price_services=0;
  $total_cost_services=0;
  $total_gain_services=0;
	
  foreach ($services as $service){
    $total_quantity_services+=$service['total_quantity'];
    $total_price_services+=$service['total_price'];
    $total_cost_services+=$service['total_cost']; 
    $total_gain_services+=$service['total_gain'];
  } 
  
  $totalQuantity+=$total_quantity_services;
  $totalPrice+=$total_price_services;
  $totalCost+=$total_cost_services;
  $totalGain+=$total_gain_services;
/*
  $total_quantity_consumibles=0;
  $total_price_consumibles=0;
  $total_cost_consumibles=0;
  $total_gain_consumibles=0;
	
  foreach ($consumibleMaterials as $consumibleMaterial){
    $total_quantity_consumibles+=$consumibleMaterial['total_quantity'];
    $total_price_consumibles+=$consumibleMaterial['total_price'];
    $total_cost_consumibles+=$consumibleMaterial['total_cost']; 
    $total_gain_consumibles+=$consumibleMaterial['total_gain'];
  } 
  
  $totalQuantity+=$total_quantity_consumibles;
  //$totalPrice+=$total_price_consumibles;
  $totalCost+=$total_cost_consumibles;
  $totalGain+=$total_gain_consumibles;
*/
	$utilityTable="";
  $utilitySummaryTableHeader="<thead>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th>Tipo de Producto</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Cantidad</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Precio Producto Ventas</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Costo Producto Ventas</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad %</th>";
        $utilitySummaryTableHeader.="<th class='centered'>Reclasificaciones ajustes</th>";
      $utilitySummaryTableHeader.="</tr>";
    $utilitySummaryTableHeader.="</thead>";
    
    $utilityTableRows="";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Ventas Producto Fabricado Calidad A</td>";  
      $utilityTableRows.="<td class='centered number'><span>".$total_quantity_products."</span></td>";  
      $utilityTableRows.="<td class='centered currency'><span>".$total_price_products."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_cost_products."</span></td>";  
      $utilityTableRows.="<td class='centered currency'><span>".(abs($total_gain_products)>0.01?$total_gain_products:0)."</span></td>";  
      if (!empty($total_price_products) && abs($total_gain_products)>0.01){
        $utilityTableRows.="<td class='centered percentage'><span>".(100*$total_gain_products/$total_price_products)."</span></td>";
      }
      else {
        $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
      }         
      $utilityTableRows.="<td class='centered number'><span>".($stockMovementsAdjustmentsInA[0]['StockMovement']['total_in_A']-$stockMovementsAdjustmentsOutA[0]['StockMovement']['total_out_A'])."</span></td>";
    $utilityTableRows.="</tr>";
  /*  
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Ventas Producto Tapones</td>";  
      $utilityTableRows.="<td class='centered number'><span>".$total_quantity_caps."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_price_caps."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_cost_caps."</span></td>";  
      $utilityTableRows.="<td class='centered currency'><span>".(abs($total_gain_caps)>0.01?$total_gain_caps:0)."</span></td>";  
      if (!empty($total_price_caps) && abs($total_gain_caps)>0.01){
        $utilityTableRows.="<td class='centered percentage'><span>".(100*($total_gain_caps)/$total_price_caps)."</span></td>";
      }
      else {
        $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
      }
      $utilityTableRows.="<td class='centered number'><span>".($stockMovementsAdjustmentsInCaps[0]['StockMovement']['total_in_caps']-$stockMovementsAdjustmentsOutCaps[0]['StockMovement']['total_out_caps'])."</span></td>";
    $utilityTableRows.="</tr>";
  */
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Ventas Servicios</td>";  
      $utilityTableRows.="<td class='centered number'><span>".$total_quantity_services."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_price_services."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_cost_services."</span></td>";  
      $utilityTableRows.="<td class='centered currency'><span>".(abs($total_gain_services)>0.01?$total_gain_services:0)."</span></td>";  
      if (!empty($total_price_services && abs($total_gain_services)>0.01)){
        $utilityTableRows.="<td class='centered percentage'><span>".(100*($total_gain_services)/$total_price_services)."</span></td>";
      }
      else {
        $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
      }         
      $utilityTableRows.="<td class='centered'>-</td>";      
    $utilityTableRows.="</tr>";
/*
    //echo "consumible percentage ".($total_gain_consumibles/$total_price_consumibles)."<br/>";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Ventas Producto Consumible</td>";  
      $utilityTableRows.="<td class='centered number'><span>".$total_quantity_consumibles."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_price_consumibles."</span></td>"; 
      $utilityTableRows.="<td class='centered currency'><span>".$total_cost_consumibles."</span></td>";  
      $utilityTableRows.="<td class='centered currency'><span>".(abs($total_gain_consumibles)>0.01?$total_gain_consumibles:0)."</span></td>";  
      if (!empty($total_price_consumibles) && abs($total_gain_consumibles)>0.01){
        $utilityTableRows.="<td class='centered percentage'><span>".(100*$total_gain_consumibles/$total_price_consumibles)."</span></td>";
      }
      else {
        $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
      }
      $utilityTableRows.="<td class='centered'>-</td>";            
    $utilityTableRows.="</tr>";
*/  

    if (!empty($otherMaterialProductTypes)){
      foreach ($otherMaterialProductTypes as $otherMaterialProductType){
        $total_quantity_other=0;
        $total_price_other=0;
        $total_cost_other=0;
        $total_gain_other=0;
        
        foreach ($otherMaterialProductType['Products'] as $otherProduct){
          $total_quantity_other+=$otherProduct['total_quantity'];
          $total_price_other+=$otherProduct['total_price'];
          $total_cost_other+=$otherProduct['total_cost']; 
          $total_gain_other+=$otherProduct['total_gain'];
        }
        $totalQuantity+=$total_quantity_other;
        $totalPrice+=$total_price_other;
        $totalCost+=$total_cost_other;
        $totalGain+=$total_gain_other;
        
        $utilityTableRows.="<tr>";
          $utilityTableRows.="<td>Ventas Producto ".$otherMaterialProductType['ProductType']['name']."</td>";  
          $utilityTableRows.="<td class='centered'><span>".$total_quantity_other."</span></td>";
          $utilityTableRows.="<td class='centered currency'><span>".$total_price_other."</span></td>"; 
          $utilityTableRows.="<td class='centered currency'><span>".$total_cost_other."</span></td>";  
          $utilityTableRows.="<td class='centered currency'><span>".(abs($total_gain_other)>0.01?$total_gain_other:0)."</span></td>";  
          if (!empty($total_price_other) && abs($total_gain_other)>0.01){
            $utilityTableRows.="<td class='centered percentage'><span>".(100*($total_gain_other)/$total_price_other)."</span></td>";
          }
          else {
            $utilityTableRows.="<td class='centered percentage'><span>0</span></td>";
          }
          $utilityTableRows.="<td class='centered'>-</td>";      
        $utilityTableRows.="</tr>";          
      }
    } 
    
    $utilityTableTotalRow="";
    $utilityTableTotalRow.="<tr class='totalrow'>";
      $utilityTableTotalRow.="<td>Todas Ventas</td>";
      $utilityTableTotalRow.="<td class='centered number'><span>".$totalQuantity."</span></td>";      
      $utilityTableTotalRow.="<td class='centered currency'><span>".$totalPrice."</span></td>";  
      $utilityTableTotalRow.="<td class='centered currency'><span>".$totalCost."</span></td>";  
      $utilityTableTotalRow.="<td class='centered currency'><span>".$totalGain."</span></td>"; 
      if (!empty($total_price_all)){
        $utilityTableTotalRow.="<td class='centered percentage'><span>".(100*$totalGain/$totalPrice)."</span></td>";
      }
      else {
        $utilityTableTotalRow.="<td class='centered percentage'><span>0</span></td>";
      }
      $utilityTableTotalRow.="<td class='centered'></td>";      
    $utilityTableTotalRow.="</tr>";
    
    $utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
  $utilityTable.="<table id='utilidad_por_tipo'>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
  
  echo "<h2>Utilidad de Ventas por Tipo de Producto</h2>";
  echo $utilityTable;
  $excelUtilityPerType=$utilityTable;  
    
 
  /*  
	$overviewTable="<table id='overview'>";
		$overviewTable.="<thead>";
			$overviewTable.="<tr>";
				$overviewTable.="<th></th>";
				$overviewTable.="<th class='centered'>Total A</th>";
				$overviewTable.="<th class='centered'>Total Tapones</th>";
        $overviewTable.="<th class='centered'>Total Servicios</th>";
			$overviewTable.="</tr>";
		$overviewTable.="</thead>";
		
		$overviewTable.="<tbody>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Cantidad</td>";
				$overviewTable.="<td class='centered number'><span>".$total_quantity_products."</span></td>";
				//$overviewTable.="<td class='centered number'><span>".$total_quantity_caps."</span></td>";
        $overviewTable.="<td class='centered number'><span>".$total_quantity_other."</span></td>";
        $overviewTable.="<td class='centered number'><span>".$total_quantity_services."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Venta</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_price_products."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_price_caps."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_price_other."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_price_services."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Costo</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_cost_products."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_cost_caps."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_cost_other."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_cost_services."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Utilidad</td>";
				$overviewTable.="<td class='centered currency'><span>".$total_gain_products."</span></td>";
				//$overviewTable.="<td class='centered currency'><span>".$total_gain_caps."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_gain_other."</span></td>";
        $overviewTable.="<td class='centered currency'><span>".$total_gain_services."</span></td>";
			$overviewTable.="</tr>";
			$overviewTable.="<tr>";
				$overviewTable.="<td>Margen Utilidad</td>";
				if (!empty($total_price_products)){
					$overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_products/$total_price_products)."</span></td>";
				}
				else {
					$overviewTable.="<td class='centered percentage'><span>0</span></td>";
				}
				//if (!empty($total_price_caps)){
        if (!empty($total_price_other)){
					//$overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_caps/$total_price_caps)."</span></td>";
          $overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_other/$total_price_other)."</span></td>";
				}
				else {
					$overviewTable.="<td class='centered percentage'><span>0</span></td>";
				}
        if (!empty($total_price_services)){
					$overviewTable.="<td class='centered percentage'><span>".(100*$total_gain_services/$total_price_services)."</span></td>";
				}
				else {
					$overviewTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$overviewTable.="</tr>";
      $overviewTable.="<tr>";
				$overviewTable.="<td>Ajustes y Reclasificaciones</td>";
				$overviewTable.="<td class='centered number'><span>".($stockMovementsAdjustmentsInA[0]['StockMovement']['total_in_A']-$stockMovementsAdjustmentsOutA[0]['StockMovement']['total_out_A'])."</span></td>";
				$overviewTable.="<td class='centered number'><span>".($stockMovementsAdjustmentsInCaps[0]['StockMovement']['total_in_caps']-$stockMovementsAdjustmentsOutCaps[0]['StockMovement']['total_out_caps'])."</span></td>";
        $overviewTable.="<td class='centered'>-</td>";
			$overviewTable.="</tr>";
		$overviewTable.="</tbody>";
	$overviewTable.="</table>";
	*/	
	$clientTable="<table id='tapones'>";
		$clientTable.="<thead>";
			$clientTable.="<tr>";
				$clientTable.="<th>".__('Client')."</th>";
				
				$clientTable.="<th class='centered'>".__('Cantidad Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Venta Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Costo Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Utilidad Botellas')."</th>";
				$clientTable.="<th class='centered'>".__('Margen Utilidad Botellas')."</th>";
				
				$clientTable.="<th class='centered'>".__('Cantidad Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Venta Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Costo Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Utilidad Tapones')."</th>";
				$clientTable.="<th class='centered'>".__('Margen Utilidad Tapones')."</th>";
			$clientTable.="</tr>";
		$clientTable.="</thead>";
		$clientTable.="<tbody>";

		$total_quantity_all_bottles=0;
		$total_price_all_bottles=0;
		$total_cost_all_bottles=0;
		$total_gain_all_bottles=0;
		
		$total_quantity_all_caps=0;
		$total_price_all_caps=0;
		$total_cost_all_caps=0;
		$total_gain_all_caps=0;
		
		foreach ($clientutility as $client){
			if (($client['bottle_total_price']+$client['cap_total_price'])>0){
				$total_quantity_all_bottles+=$client['bottle_total_quantity'];
				$total_price_all_bottles+=$client['bottle_total_price'];
				$total_cost_all_bottles+=$client['bottle_total_cost']; 
				$total_gain_all_bottles+=$client['bottle_total_gain'];

				$total_quantity_all_caps+=$client['cap_total_quantity'];
				$total_price_all_caps+=$client['cap_total_price'];
				$total_cost_all_caps+=$client['cap_total_cost']; 
				$total_gain_all_caps+=$client['cap_total_gain'];
				
				$clientTable.="<tr>"; 				
					$clientTable.="<td>".$this->Html->link($client['name'], array('controller' => 'third_parties', 'action' => 'verCliente', $client['id']))."</td>";
					
					$clientTable.="<td class='centered number'><span>".$client['bottle_total_quantity']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_price']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_cost']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['bottle_total_gain']."</span></td>";
					if (!empty($client['bottle_total_price'])){
						$clientTable.="<td class='centered percentage'><span>".(100*$client['bottle_total_gain']/$client['bottle_total_price'])."</span></td>";
					}
					else {
						$clientTable.="<td class='centered percentage'><span>0</span></td>";
					}
					
					$clientTable.="<td class='centered number'><span>".$client['cap_total_quantity']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_price']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_cost']."</span></td>";
					$clientTable.="<td class='centered currency'><span>".$client['cap_total_gain']."</span></td>";
					if (!empty($client['cap_total_price'])){
						$clientTable.="<td class='centered percentage'><span>".(100*$client['cap_total_gain']/$client['cap_total_price'])."</span></td>";
					}
					else {
						$clientTable.="<td class='centered percentage'><span>0</span></td>";
					}	
				$clientTable.="</tr>";
			}
		}
			$clientTable.="<tr class='totalrow'>";
				$clientTable.="<td>Total</td>";
				
				$clientTable.="<td class='centered number'><span>".$total_quantity_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_price_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_cost_all_bottles."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_gain_all_bottles."</span></td>";
				if (!empty($total_price_all_bottles)){
					$clientTable.="<td class='centered percentage'><span>".(100*$total_gain_all_bottles/$total_price_all_bottles)."</span></td>";
				}
				else {
					$clientTable.="<td class='centered percentage'><span>0</span></td>";
				}
				
				$clientTable.="<td class='centered number'><span>".$total_quantity_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_price_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_cost_all_caps."</span></td>";
				$clientTable.="<td class='centered currency'><span>".$total_gain_all_caps."</span></td>";			
				if (!empty($total_price_all_caps)){
					$clientTable.="<td class='centered percentage'><span>".(100*$total_gain_all_caps/$total_price_all_caps)."</span></td>";			
				}
				else {
					$clientTable.="<td class='centered percentage'><span>0</span></td>";
				}
			$clientTable.="</tr>";
		$clientTable.="</tbody>";
	$clientTable.="</table>";	
	
	//echo $overviewTable;
	
  echo "<h2>".__('Insumos Utilizados en Producción de este período')."</h2>"; 
	echo $consumibleMaterialTable; 
  
  echo "<h2>".__('Preformas Utilizados en Ventas de este período')."</h2>"; 
	echo $rawMaterialTable; 
	echo "<h2>".__('Productos Fabricados de Calidad A')."</h2>"; 
	echo $producedmaterialtable; 
	echo "<h2>".__('Other Materials')."</h2>"; 
	echo $othermaterialtable; 	
	
	echo "<h2>".__('Categoria Ingroup')."</h2>"; 
	echo $ingrouptable; 
	
	echo "<h2>".__('Utilidad Por Cliente basado en botellas calidad A')."</h2>"; 
	echo $clientTable; 
	
	
	$_SESSION['statusReport'] = $excelUtilityPerType.$producedmaterialtable.$othermaterialtable.$clientTable;
?>
</div>