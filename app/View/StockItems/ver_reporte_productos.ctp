<div class="stockItems view report">
<?php 
	echo "<h2>".__('Reporte General de Movimientos Productos')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
    echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	
	$rawmaterialtable="";
	$producedmaterialtable="";
	$othermaterialtable="";
	
	$rawmaterialtable="<table id='preformas'>";
		$rawmaterialtable.="<thead>";
			$rawmaterialtable.="<tr>";
				$rawmaterialtable.="<th class='hidden'>Product Id</th>";
				$rawmaterialtable.="<th>".__('Product')."</th>";
				//$rawmaterialtable.="<th>".__('Unit Price')."</th>";
				
				$rawmaterialtable.="<th class='separator'></th>";
				
				$rawmaterialtable.="<th class='centered'>".__('Quantity Initial Stock')."</th>";
				$rawmaterialtable.="<th class='centered'>".__('Quantity Purchased')."</th>";
				$rawmaterialtable.="<th class='centered'>".__('Quantity Reclassified')."</th>";
				$rawmaterialtable.="<th class='centered'>".__('Quantity Used')."</th>";
				$rawmaterialtable.="<th class='centered'>".__('Quantity Final Stock')."</th>";
				
				if($userrole!=ROLE_FOREMAN){
					$rawmaterialtable.="<th class='separator'></th>";
					
					$rawmaterialtable.="<th class='centered'>".__('Value Initial Stock')."</th>";
					$rawmaterialtable.="<th class='centered'>".__('Value Purchased')."</th>";
					$rawmaterialtable.="<th class='centered'>".__('Value Reclassified')."</th>";
					$rawmaterialtable.="<th class='centered'>".__('Value Used')."</th>";
					$rawmaterialtable.="<th class='centered'>".__('Value Final Stock')."</th>";
					//$rawmaterialtable.="<th>".__('Profit')."</th>";
				}
			$rawmaterialtable.="</tr>";
		$rawmaterialtable.="</thead>";
		
		$rawmaterialtable.="<tbody>";

		$unit_price=0;
		
		$quantity_start=0; 
		$quantity_purchased=0; 
		$quantity_reclassified=0; 
		$quantity_used=0; 
		$quantity_end=0; 

		if($userrole!=ROLE_FOREMAN){
			$value_start=0;
			$value_purchased=0;
			$value_reclassified=0;
			$value_used=0;
			$value_end=0;
		}
		//$profit=0; 
		
		$rawMaterialTableRows="";
		foreach ($rawMaterials as $rawmaterial){
			
			$quantity_start+=$rawmaterial['initial_quantity']; 
			$quantity_purchased+=$rawmaterial['purchased_quantity']; 
			$quantity_reclassified+=$rawmaterial['reclassified_quantity']; 
			$quantity_used+=$rawmaterial['used_quantity']; 
			$quantity_end+=$rawmaterial['final_quantity']; 
			
			if($userrole!=ROLE_FOREMAN){
				$value_start+=$rawmaterial['initial_value'];
				$value_purchased+=$rawmaterial['purchased_value'];
				$value_reclassified+=$rawmaterial['reclassified_value'];
				$value_used+=$rawmaterial['used_value'];
				$value_end+=$rawmaterial['final_value'];
			}
			//$productprofit=$value_end + $value_purchased - $value_used-$value_start;
			//$profit+=$productprofit;
			
			$rawMaterialTableRows.="<tr>"; 
				$rawMaterialTableRows.="<td class='hidden'>".$rawmaterial['id']."</td>";
				//$rawMaterialTableRows.="<td>".$this->Html->link($rawmaterial['name'], array('controller' => 'stockItems', 'action' => 'verReporteProducto', $rawmaterial['id'],'?' => array('startDate' => $startDate, 'endDate' => $endDate)))."</td>";
				$rawMaterialTableRows.="<td>".$this->Html->link($rawmaterial['name'], array('controller' => 'stockItems', 'action' => 'verReporteProducto', $rawmaterial['id']))."</td>";
				//$rawMaterialTableRows.="<td>".$rawmaterial['name']."</td>";
				//$rawMaterialTableRows.="<td>".$rawmaterial['unit_price']."</td>";
				
				$rawMaterialTableRows.="<td class='separator'></td>";
				
				$rawMaterialTableRows.="<td class='centered number'>".$rawmaterial['initial_quantity']."</td>";
				$rawMaterialTableRows.="<td class='centered number'>".$rawmaterial['purchased_quantity']."</td>";
				$rawMaterialTableRows.="<td class='centered number'>".$rawmaterial['reclassified_quantity']."</td>";
				$rawMaterialTableRows.="<td class='centered number'>".$rawmaterial['used_quantity']."</td>";
				$rawMaterialTableRows.="<td class='centered number'>".$rawmaterial['final_quantity']."</td>";
			
				if($userrole!=ROLE_FOREMAN){
					$rawMaterialTableRows.="<td class='separator'></td>";
					
					$rawMaterialTableRows.="<td class='centered currency'><span class='amountright'>".$rawmaterial['initial_value']."</span></td>";
					$rawMaterialTableRows.="<td class='centered currency'><span class='amountright'>".$rawmaterial['purchased_value']."</span></td>";
					$rawMaterialTableRows.="<td class='centered currency'><span class='amountright'>".$rawmaterial['reclassified_value']."</span></td>";
					$rawMaterialTableRows.="<td class='centered currency'><span class='amountright'>".$rawmaterial['used_value']."</span></td>";
					$rawMaterialTableRows.="<td class='centered currency'><span class='amountright'>".$rawmaterial['final_value']."</span></td>";
					//$rawMaterialTableRows.="<td>".$productprofit,2)."</td>";
				}
			$rawMaterialTableRows.="</tr>";
		}
	
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td class='hidden'></td>";
				$totalRows.="<td>Total</td>";
				if ($quantity_start>0){
					//$totalRows.="<td>".$value_start/$quantity_start."</td>";
				}
				else {
					//$totalRows.="<td>0</td>";
				}
				$totalRows.="<td class='separator'></td>";
				
				$totalRows.="<td class='centered number'>".$quantity_start."</td>";
				$totalRows.="<td class='centered number'>".$quantity_purchased."</td>";
				$totalRows.="<td class='centered number'>".$quantity_reclassified."</td>";
				$totalRows.="<td class='centered number'>".$quantity_used."</td>";
				$totalRows.="<td class='centered number'>".$quantity_end."</td>";
				
				if($userrole!=ROLE_FOREMAN){
					$totalRows.="<td class='separator'></td>";
					
					$totalRows.="<td class='centered currency'><span>".$value_start."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_purchased."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_reclassified."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_used."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_end."</span></td>";
					//$totalRows.="<td>".$profit,2)."</td>";
				}
			$totalRows.="</tr>";
		$rawmaterialtable.=$totalRows.$rawMaterialTableRows.$totalRows."</tbody>";
	$rawmaterialtable.="</table>";

	$producedmaterialtable="<table id='botellas'>";
		$producedmaterialtable.="<thead>";
			$producedmaterialtable.="<tr>";
				$producedmaterialtable.="<th class='hidden'>Product Id</th>";
				$producedmaterialtable.="<th>".__('Product')."</th>";
				//$producedmaterialtable.="<th>".__('Unit Price')."</th>";

				$producedmaterialtable.="<th class='separator'></th>";
				
				$producedmaterialtable.="<th class='centered'>".__('Quantity Initial Stock')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Quantity Produced')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Quantity Reclassified')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Quantity Sold')."</th>";
				$producedmaterialtable.="<th class='centered'>".__('Quantity Final Stock')."</th>";

				if($userrole!=ROLE_FOREMAN){
					$producedmaterialtable.="<th class='separator'></th>";
					
					$producedmaterialtable.="<th class='centered'>".__('Value Initial Stock')."</th>";
					$producedmaterialtable.="<th class='centered'>".__('Value Produced')."</th>";
					$producedmaterialtable.="<th class='centered'>".__('Value Reclassified')."</th>";
					$producedmaterialtable.="<th class='centered'>".__('Value Sold')."</th>";
					$producedmaterialtable.="<th class='centered'>".__('Value Final Stock')."</th>";
				}
				//$producedmaterialtable.="<th class='separator'></th>";
				
				//$producedmaterialtable.="<th>".__('Profit')."</th>";
			$producedmaterialtable.="</tr>";
		$producedmaterialtable.="</thead>";

		$producedmaterialtable.="<tbody>";

		$unit_price=0;
		
		$quantity_start=0; 
		$quantity_produced=0; 
		$quantity_reclassified=0; 
		$quantity_sold=0; 
		$quantity_end=0; 
		
		if($userrole!=ROLE_FOREMAN){
			$value_start=0;
			$value_produced=0;
			$value_reclassified=0;
			$value_sold=0;
			$value_end=0;
			$profit=0; 
		}
		
		$tableRows="";
		foreach ($producedMaterials as $producedmaterial){
			
			$quantity_start+=$producedmaterial['initial_quantity']; 
			$quantity_produced+=$producedmaterial['produced_quantity']; 
			$quantity_reclassified+=$producedmaterial['reclassified_quantity']; 
			$quantity_sold+=$producedmaterial['sold_quantity']; 
			$quantity_end+=$producedmaterial['final_quantity']; 
		
			if($userrole!=ROLE_FOREMAN){
				$value_start+=$producedmaterial['initial_value'];
				$value_produced+=$producedmaterial['produced_value'];
				$value_reclassified+=$producedmaterial['reclassified_value'];
				$value_sold+=$producedmaterial['sold_value'];
				$value_end+=$producedmaterial['final_value'];
				$productprofit=$producedmaterial['final_value'] - $producedmaterial['produced_value'] + $producedmaterial['sold_value']-$producedmaterial['initial_value'];
				$profit+=$productprofit;
			}
			
			$tableRows.="<tr>"; 
				$tableRows.="<td class='hidden'>".$producedmaterial['id']."</td>";
				//$tableRows.="<td>".$producedmaterial['name']."</td>";
				$tableRows.="<td>".$this->Html->link($producedmaterial['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $producedmaterial['id']))."</td>";
				//$tableRows.="<td>".$producedmaterial['unit_price']."</td>";
				
				$tableRows.="<td class='separator'></td>";
				
				$tableRows.="<td class='centered number'>".$producedmaterial['initial_quantity']."</td>";
				$tableRows.="<td class='centered number'>".$producedmaterial['produced_quantity']."</td>";
				$tableRows.="<td class='centered number'>".$producedmaterial['reclassified_quantity']."</td>";
				$tableRows.="<td class='centered number'>".$producedmaterial['sold_quantity']."</td>";
				$tableRows.="<td class='centered number'>".$producedmaterial['final_quantity']."</td>";
			
				if($userrole!=ROLE_FOREMAN){
					$tableRows.="<td class='separator'></td>";
					
					$tableRows.="<td class='centered currency'><span>".$producedmaterial['initial_value']."</span></td>";
					$tableRows.="<td class='centered currency'><span>".$producedmaterial['produced_value']."</span></td>";
					$tableRows.="<td class='centered currency'><span>".$producedmaterial['reclassified_value']."</span></td>";
					$tableRows.="<td class='centered currency'><span>".$producedmaterial['sold_value']."</span></td>";
					$tableRows.="<td class='centered currency'><span>".$producedmaterial['final_value']."</span></td>";

					//$tableRows.="<td class='separator'></td>";
					
					//$tableRows.="<td class='centered number'>".$productprofit."</span></td>";
				}
			$tableRows.="</tr>";
		}
		
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td class='hidden'></td>";
				$totalRows.="<td>Total</td>";
				if ($quantity_start>0){
					//$totalRows.="<td>".$value_start/$quantity_start."</td>";
				}
				else {
					//$totalRows.="<td>0</td>";
				}
				$totalRows.="<td class='separator'></td>";
				$totalRows.="<td class='centered number'>".$quantity_start."</td>";
				$totalRows.="<td class='centered number'>".$quantity_produced."</td>";
				$totalRows.="<td class='centered number'>".$quantity_reclassified."</td>";
				$totalRows.="<td class='centered number'>".$quantity_sold."</td>";
				$totalRows.="<td class='centered number'>".$quantity_end."</td>";
				
				if($userrole!=ROLE_FOREMAN){
					$totalRows.="<td class='separator'></td>";
					
					$totalRows.="<td class='centered currency'><span>".$value_start."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_produced."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_reclassified."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_sold."</span></td>";
					$totalRows.="<td class='centered currency'><span>".$value_end."</span></td>";
					
					//$totalRows.="<td class='separator'></td>";				
					//$totalRows.="<td>".$profit,2)."</td>";
				}
			$totalRows.="</tr>";
		$producedmaterialtable.=$totalRows.$tableRows.$totalRows."</tbody>";
	$producedmaterialtable.="</table>";

  if (!empty($otherMaterials)){
    $othermaterialtable="<table id='tapones'>";
      $othermaterialtable.="<thead>";
        $othermaterialtable.="<tr>";
          $othermaterialtable.="<th class='hidden'>Product Id</th>";
          $othermaterialtable.="<th>".__('Product')."</th>";
          //$othermaterialtable.="<th>".__('Unit Price')."</th>";
          
          $othermaterialtable.="<th class='separator'></th>";
          
          $othermaterialtable.="<th class='centered'>".__('Quantity Initial Stock')."</th>";
          $othermaterialtable.="<th class='centered'>".__('Quantity Purchased')."</th>";
          $othermaterialtable.="<th class='centered'>".__('Quantity Sold')."</th>";
          $othermaterialtable.="<th class='centered'>".__('Quantity Reclassified')."</th>";
          $othermaterialtable.="<th class='centered'>".__('Quantity Final Stock')."</th>";
          
          if($userrole!=ROLE_FOREMAN){
            $othermaterialtable.="<th class='separator'></th>";
            
            $othermaterialtable.="<th class='centered'>".__('Value Initial Stock')."</th>";
            $othermaterialtable.="<th class='centered'>".__('Value Purchased')."</th>";
            $othermaterialtable.="<th class='centered'>".__('Value Sold')."</th>";
            $othermaterialtable.="<th class='centered'>".__('Value Reclassified')."</th>";
            $othermaterialtable.="<th class='centered'>".__('Value Final Stock')."</th>";
            
            //$othermaterialtable.="<th class='separator'></th>";				
            //$othermaterialtable.="<th>".__('Profit')."</th>";
          }
        $othermaterialtable.="</tr>";
      $othermaterialtable.="</thead>";
      
      $othermaterialtable.="<tbody>";

      $unit_price=0;
      
      $quantity_start=0; 
      $quantity_purchased=0; 
      $quantity_sold=0; 
      $quantity_reclassified=0; 
      $quantity_end=0; 
      
      if($userrole!=ROLE_FOREMAN){
        $value_start=0;
        $value_purchased=0;
        $value_sold=0;
        $value_reclassified=0;
        $value_end=0;
        $profit=0; 
      }
      
      $tableRows="";
      
      foreach ($otherMaterials as $othermaterial){
        $quantity_start+=$othermaterial['initial_quantity']; 
        $quantity_purchased+=$othermaterial['purchased_quantity']; 
        $quantity_sold+=$othermaterial['sold_quantity']; 
        $quantity_reclassified+=$othermaterial['reclassified_quantity']; 
        $quantity_end+=$othermaterial['final_quantity']; 
      
        if($userrole!=ROLE_FOREMAN){
          $value_start+=$othermaterial['initial_value'];
          $value_purchased+=$othermaterial['purchased_value'];
          $value_sold+=$othermaterial['sold_value'];
          $value_reclassified+=$othermaterial['reclassified_value'];
          $value_end+=$othermaterial['final_value'];
          $productprofit=$othermaterial['final_value'] - $othermaterial['purchased_value'] + $othermaterial['sold_value']-$othermaterial['initial_value'];
          $profit+=$productprofit;
        }
        
        $tableRows.="<tr>"; 
          $tableRows.="<td class='hidden'>".$othermaterial['id']."</td>";
          //$tableRows.="<td>".$othermaterial['name']."</td>";
          $tableRows.="<td>".$this->Html->link($othermaterial['name'], array('controller' => 'stockMovements', 'action' => 'verReporteCompraVenta', $othermaterial['id']))."</td>";
          //$tableRows.="<td>".$othermaterial['unit_price']."</td>";
          
          $tableRows.="<td class='separator'></td>";
          
          $tableRows.="<td class='centered number'>".$othermaterial['initial_quantity']."</td>";
          $tableRows.="<td class='centered number'>".$othermaterial['purchased_quantity']."</td>";
          $tableRows.="<td class='centered number'>".$othermaterial['sold_quantity']."</td>";
          $tableRows.="<td class='centered'>".$othermaterial['reclassified_quantity']."</td>";
          $tableRows.="<td class='centered number'>".$othermaterial['final_quantity']."</td>";
        
          if($userrole!=ROLE_FOREMAN){
            $tableRows.="<td class='separator'></td>";
            
            $tableRows.="<td class='centered currency'><span>".$othermaterial['initial_value']."</span></td>";
            $tableRows.="<td class='centered currency'><span>".$othermaterial['purchased_value']."</span></td>";
            $tableRows.="<td class='centered currency'><span>".$othermaterial['sold_value']."</span></td>";
            $tableRows.="<td class='centered'><span>".$othermaterial['reclassified_value']."</span></td>";
            $tableRows.="<td class='centered currency'><span>".$othermaterial['final_value']."</span></td>";
            
            //$tableRows.="<td class='separator'></td>";
            //$tableRows.="<td>".$productprofit."</td>";
          }
        $tableRows.="</tr>";
      }
      
        $totalRows="";
        $totalRows.="<tr class='totalrow'>";
          $totalRows.="<td class='hidden'></td>";
          $totalRows.="<td>Total</td>";
          if ($quantity_start>0){
            //$totalRows.="<td>".$value_start/$quantity_start."</td>";
          }
          else {
            //$totalRows.="<td>0</td>";
          }	
          $totalRows.="<td class='separator'></td>";
          
          $totalRows.="<td class='centered number'>".$quantity_start."</td>";
          $totalRows.="<td class='centered number'>".$quantity_purchased."</td>";
          $totalRows.="<td class='centered number'>".$quantity_sold."</td>";
          $totalRows.="<td class='centered'>".$quantity_reclassified."</td>";
          $totalRows.="<td class='centered number'>".$quantity_end."</td>";

          if($userrole!=ROLE_FOREMAN){
            $totalRows.="<td class='separator'></td>";
            
            $totalRows.="<td class='centered currency'><span>".$value_start."</span></td>";
            $totalRows.="<td class='centered currency'><span>".$value_purchased."</span></td>";
            $totalRows.="<td class='centered currency'><span>".$value_sold."</span></td>";
            $totalRows.="<td class='centered'><span>".$value_reclassified."</span></td>";
            $totalRows.="<td class='centered currency'><span>".$value_end."</span></td>";
            
            //$totalRows.="<td class='separator'></td>";
            //$totalRows.="<td>".$profit,2)."</td>";
          }
        $totalRows.="</tr>";
      $othermaterialtable.=$totalRows.$tableRows.$totalRows."</tbody>";
    $othermaterialtable.="</table>";	
  }
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteProductos'), array( 'class' => 'btn btn-primary')); 

	echo "<h2>".__('Raw Materials')."</h2>"; 
	echo $rawmaterialtable; 
	echo "<h2>".__('Produced Materials')."</h2>"; 
	echo $producedmaterialtable; 
	if (empty($otherMaterials)){
    echo '<h2>No hay otros productos para esta bodega</h2>';
  }
  else {
    echo "<h2>".__('Other Materials')."</h2>"; 
    echo $othermaterialtable; 
  }
	
	$_SESSION['productsReport'] = $rawmaterialtable.$producedmaterialtable.$othermaterialtable;
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