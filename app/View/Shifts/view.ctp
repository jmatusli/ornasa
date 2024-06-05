<div class="shifts view">
<h2>
<?php 
  echo "<h2>".__('Shift')." ".$shift['Shift']['name']."</h2>";
	echo "<dl>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($shift['Shift']['description'])."</dd>";
	echo "</dl>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
?>

</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){ 
			echo "<li>".$this->Html->link(__('Edit Shift'), array('action' => 'edit', $shift['Shift']['id']))."</li>";
			echo "<br/>";
		} 
		if ($bool_delete_permission){ 
			//echo "<li>".$this->Form->postLink(__('Delete Shift'), array('action' => 'delete', $shift['Shift']['id']), array(), __('Are you sure you want to delete # %s?', $shift['Shift']['id']))."</li>";
			//echo "<br/>";
		} 
		echo "<li>".$this->Html->link(__('List Shifts'), array('action' => 'index'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Shift'), array('action' => 'add'))."</li>";
		}
		echo "<br/>";
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
		foreach ($otherShifts as $otherShift){
			echo "<li>".$this->Html->link($otherShift['Shift']['name'], array('controller' => 'Shifts', 'action' => 'view',$otherShift['Shift']['id']))."</li>";
		}
	echo "</ul>";
?>		
</div>
<div class="related">
<?php 
	if (!empty($producedProducts)){
		echo "<h3>Productos fabricados en el turno en el período</h3>";
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


	if (!empty($producedProductsPerMachine)){
		echo "<h3>Productos fabricados en el turno en cada máquina en el período</h3>";
		echo "<table class='grid'>";
			echo "<thead>";
			// First the line with the raw material names
				echo "<tr>";
					echo "<th></th>";
					foreach ($producedProductsPerMachine[0]['rawmaterial'] as $rawMaterial){
						//pr($rawMaterial);
						echo "<th  class='centered' colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
					}
				echo "</tr>";
			echo "</thead>";
					
			echo "<tbody>";
			// Then the line with the finished product names 
			echo "<tr>";
			echo "<td></td>";
			foreach ($producedProductsPerMachine[0]['rawmaterial'] as $rawMaterial){
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
			foreach ($producedProductsPerMachine[0]['rawmaterial'] as $rawMaterial){
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
			//pr($producedProductsPerMachine);
			$firstrow=true;
			$machineRows="";
			foreach ($producedProductsPerMachine as $machineData){
				$machineRow="";
				$productQuantityForRow=0;
				$machineRow.="<tr>";
				$machineRow.="<td>".$this->Html->link($machineData['machine_name'], array('controller' => 'machines','action' => 'view',$machineData['machine_id']))."</td>";
				$productCounter=0;
				foreach ($machineData['rawmaterial'] as $rawMaterial){
					foreach ($rawMaterial['products'] as $product){
						if ($visibleArray[$rawMaterial['raw_material_id']][$product['finished_product_id']]['visible']>0){
							foreach($product['product_quantity'] as $quantity){
								if ($quantity>0){
									$machineRow.="<td class='centered bold number'>".$quantity."</td>";
								}
								else {
									$machineRow.="<td class='centered'>-</td>";
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
				$machineRow.="</tr>";
				if ($productQuantityForRow){
					$machineRows.=$machineRow;
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
			echo $totalRows.$machineRows.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}

	if (!empty($producedProductsPerOperator)){
		echo "<h3>Productos fabricados en el turno por cada operadoren el período</h3>";
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
					$quantityForOperator=0;
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
										$quantityForOperator+=$quantity;
										$productCounter++;
									}
								}
							}
						}
					//pr($totalsArray);
					$firstrow=false;
					$operatorRow.="</tr>";
					if ($quantityForOperator>0){
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

	?>


<?php if (!empty($shift['ProductionRun'])): ?>
	<h3><?php echo __('Related Production Runs for Shift'); ?></h3>
	
	<table cellpadding = "0" cellspacing = "0">
	<thead>
		<tr>
			<th><?php echo __('Production Run Code'); ?></th>
			<th><?php echo __('Production Run Date'); ?></th>
			<th><?php echo __('Materia Prima'); ?></th>
			<th><?php echo __('Producto'); ?></th>
			
			<th class='centered'><?php echo __('Cantidad A'); ?></th>
			<th class='centered'><?php echo __('Cantidad B'); ?></th>
			<th class='centered'><?php echo __('Cantidad C'); ?></th>
			
			<th class='centered'><?php echo __('Valor A'); ?></th>
			<th class='centered'><?php echo __('Valor B'); ?></th>
			<th class='centered'><?php echo __('Valor C'); ?></th>
			
			<th class='centered'><?php echo __('Cantidad Total'); ?></th>
			<th class='centered'><?php echo __('Total Value'); ?></th>
			
			<th><?php echo __('Machine'); ?></th>
			<th><?php echo __('Operator'); ?></th>
			<!--th class='centered'><?php //echo __('Energy Use'); ?></th-->
			<th class="actions"><?php echo __('Actions'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
		$totalquantityA=0;
		$totalquantityB=0;
		$totalquantityC=0;
		$totalvalueA=0;
		$totalvalueB=0;
		$totalvalueC=0;
		$totalquantity=0;
		$totalvalue=0;
		//$totalenergy=0;	
		$productionRunRows="";
		foreach ($shift['ProductionRun'] as $productionRun){
			$productionrundate= new DateTime($productionRun['production_run_date']);
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
      //$totalenergy+=$energyConsumption[$productionRun['id']];
        
			$productionRunRows.="<tr>";
				$productionRunRows.="<td>".$this->Html->link($productionRun['production_run_code'], array('controller' => 'production_runs', 'action' => 'view', $productionRun['id']))."</td>";
				$productionRunRows.="<td>".$productionrundate->format('d-m-Y')."</td>";
				$productionRunRows.="<td>".$productionRun['RawMaterial']['name']."</td>";
				$productionRunRows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
			
				$productionRunRows.="<td class='centered number'><span>".$quantityA."</span></td>";
				$productionRunRows.="<td class='centered number'><span>".$quantityB."</span></td>";
				$productionRunRows.="<td class='centered number'><span>".$quantityC."</span></td>";
				
				$productionRunRows.="<td class='centered currency'><span>".$quantityA*$unitprice."</span></td>";
				$productionRunRows.="<td class='centered currency'><span>".$quantityB*$unitprice."</span></td>";
				$productionRunRows.="<td class='centered currency'><span>".$quantityC*$unitprice."</span></td>";
				
				$productionRunRows.="<td class='centered number'><span>".($quantityA+$quantityB+$quantityC)."</span></td>";
				$productionRunRows.="<td class='centered currency'><span>".($quantityA+$quantityB+$quantityC)*$unitprice."</span></td>";
				
				$productionRunRows.="<td>".$this->Html->link($productionRun['Machine']['name'],['controller'=>'machines','action'=>'view',$productionRun['Machine']['id']])."</td>";
				$productionRunRows.="<td>".$this->Html->link($productionRun['Operator']['name'],['controller'=>'operators','action'=>'view',$productionRun['Operator']['id']])."</td>";
				//$productionRunRows.="<td class='centered'><span>".$energyConsumption[$productionRun['id']]."</span></td>";
				
				$productionRunRows.="<td class='actions'>";
					//$productionRunRows.=$this->Html->link(__('View'), array('controller' => 'production_runs', 'action' => 'view', $productionRun['id'])); 
					if ($bool_productionrun_edit_permission){ 
						$productionRunRows.=$this->Html->link(__('Edit'), array('controller' => 'production_runs', 'action' => 'edit', $productionRun['id'])); 
					}
					//$productionRunRows.=$this->Form->postLink(__('Delete'), array('controller' => 'production_runs', 'action' => 'delete', $productionRun['id']), array(), __('Are you sure you want to delete # %s?', $productionRun['id'])); 
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
				//$totalRows.="<td class='centered number'><span>".$totalenergy."</td>";
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
				//$totalRows.="<td></td>";
				$totalRows.="<td></td>";
			$totalRows.="</tr>";
		echo $totalRows.$productionRunRows.$totalRows;
?>	
		</tbody>
	
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
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