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

<div class="plants view fullwidth">
<?php 
	echo "<h2>".__('Plant')." ".$plant['Plant']['name']."</h2>";
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-5">';
        echo '<dl class="dl100">';
          echo "<dt>".__('Description')."</dt>";
          echo "<dd>".h($plant['Plant']['description'])."</dd>";
          echo "<dt>Nombre corto</dt>";
          echo "<dd>".h($plant['Plant']['short_name'])."</dd>";
          echo "<dt>".__('# serial facturas')."</dt>";
          echo "<dd>".h($plant['Plant']['series'])."</dd>";
        echo "</dl>";
      echo '</div>';
      echo '<div class="col-sm-4">';
        echo "<h3>Operadores activos para esta planta</h3>";
        echo '<ul style="list-style:none;">';
        foreach ($plant['Operator'] as $operator){
          echo "<li>".$this->Html->link($operator['name'], ['controller'=>'operators','action' => 'view',$operator['id']])."</li>";
        }
        echo "</ul>";
        echo '<h3>Máquinas activas para esta planta</h3>';
        echo '<ul style="list-style:none;">';
        foreach ($plant['Machine'] as $machine){
          echo "<li>".$this->Html->link($machine['name'], ['controller'=>'machines','action' => 'view',$machine['id']])."</li>";
        }
        echo "</ul>";
        echo "<h3>Tipos de producción para esta planta</h3>";
        echo '<ul style="list-style:none;">';
        foreach ($plantProductionTypes as $productionTypeId=>$productionTypeName){
          echo "<li>".$this->Html->link($productionTypeName, ['controller'=>'productionTypes','action' => 'detalle',$productionTypeId])."</li>";
        }
        echo "</ul>";
        echo "<h3>Usuarios para esta planta</h3>";
        echo '<ul style="list-style:none;">';
        foreach ($plantUsers as $userId=>$userData){
          //echo "<li>".$this->Html->link($userData, ['controller'=>'users','action' => 'view',$userId])."</li>";
          echo "<li>".$userData."</li>";
        }
        echo "</ul>";
      echo '</div>';
      echo '<div class="col-sm-3">';
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          if ($bool_edit_permission){ 
            echo "<li>".$this->Html->link(__('Edit Plant'), array('action' => 'edit', $plant['Plant']['id']))."</li>";
            echo "<br/>";
          }
          if ($bool_delete_permission){ 
            //echo "<li>".$this->Form->postLink(__('Delete Plant'), ['action' => 'delete', $plant['Plant']['id']], [], __('Are you sure you want to delete # %s?', $plant['Plant']['name']))."</li>";
          }
          echo "<li>".$this->Html->link(__('List Plants'), ['action' => 'resumen'])."</li>";
          if ($bool_add_permission){ 
            echo "<li>".$this->Html->link(__('New Plant'), ['action' => 'crear'])."</li>";
          }
        
        echo "</ul>";
      echo '</div>';
    echo '</div>';
  echo '</div>';
?>
</div>
<div class="related">
<?php 
  echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
  
  
  if (!empty($plant['ProductionRun'])){
		echo '<h3>Procesos de Producción para este planta</h3>';
		
    $productionRunTableHeader='';
		$productionRunTableHeader.='<thead>';
			$productionRunTableHeader.='<tr>';
				$productionRunTableHeader.='<th># Proceso</th>';
				$productionRunTableHeader.='<th>Fecha</th>';
				$productionRunTableHeader.='<th>Materia Prima</th>';
				$productionRunTableHeader.='<th>Producto</th>';
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $productionRunTableHeader.='<th class="centered">Cantidad '.$plantProductionResultCodeName.'</th>';
        }
        foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $productionRunTableHeader.='<th class="centered">Valor '.$plantProductionResultCodeName.'</th>';
        }
				$productionRunTableHeader.='<th class="centered">Cantidad Total</th>';
				$productionRunTableHeader.='<th class="centered">Valor</th>';
				
				$productionRunTableHeader.='<th>'.__('Machine').'</th>';
				$productionRunTableHeader.='<th>'.__('Operator').'</th>';
			$productionRunTableHeader.='</tr>';
		$productionRunTableHeader.='</thead>';
		

		$totalQuantities=['total'=>0];
    $totalValues=['total'=>0];
    foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
      $totalQuantities[$productionResultCodeId]=0;
      $totalValues[$productionResultCodeId]=0;
    }
    
    $productionRunRows="";
		foreach ($plant['ProductionRun'] as $productionRun){
			$productionRunDateTime= new DateTime($productionRun['production_run_date']);
			$productionRunRows.="<tr>";
				$productionRunRows.="<td>".$this->Html->link($productionRun['production_run_code'], ['controller' => 'production_runs', 'action' => 'view', $productionRun['id']])."</td>";
				$productionRunRows.="<td>".$productionRunDateTime->format('d-m-Y')."</td>";
				$productionRunRows.="<td>".(empty($productionRun['RawMaterial'])?'-':$productionRun['RawMaterial']['name'])."</td>";
				$productionRunRows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
			
        $quantities=['total'=>0];
        $values=['total'=>0];
        foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $quantities[$productionResultCodeId]=0;
          $values[$productionResultCodeId]=0;
        }
      
				$unitPrice=0;
				
        foreach ($productionRun['ProductionMovement'] as $productionMovement){
					$unitPrice=$productionMovement['product_unit_price'];
					if (!$productionMovement['bool_input']){
						
            foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
              if ($productionMovement['production_result_code_id'] == $productionResultCodeId){
                $quantities[$productionResultCodeId]+=$productionMovement['product_quantity'];
                $quantities['total']+=$productionMovement['product_quantity'];
                
                $totalQuantities[$productionResultCodeId]+=$productionMovement['product_quantity'];
                $totalQuantities['total']+=$productionMovement['product_quantity'];
                $values[$productionResultCodeId]+=$productionMovement['product_quantity']*$unitPrice;
                $values['total']+=$productionMovement['product_quantity']*$unitPrice;
                $totalValues[$productionResultCodeId]+=$productionMovement['product_quantity']*$unitPrice;
                $totalValues['total']+=$productionMovement['product_quantity']*$unitPrice;
                break;
              }
            }
          }
        }  
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $productionRunRows.="<td class='centered number'><span>".$quantities[$productionResultCodeId]."</span></td>";
				}
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $productionRunRows.="<td class='centered currency'><span>".$values[$productionResultCodeId]."</span></td>";
				}
				
        $productionRunRows.="<td class='centered number'><span>".$quantities['total']."</span></td>";
				$productionRunRows.="<td class='centered currency'><span>".$values['total']."</span></td>";
					
        $productionRunRows.="<td>".$this->Html->Link($productionRun['Machine']['name'],['controller'=>'machines','action'=>'view',$productionRun['Machine']['id']])."</td>";
				$productionRunRows.="<td>".$this->Html->Link($productionRun['Operator']['name'],['controller'=>'operators','action'=>'view',$productionRun['Operator']['id']])."</td>";
			$productionRunRows.="</tr>";
		}
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td>Total</td>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $totalRows.="<td class='centered number'><span>".$totalQuantities[$productionResultCodeId]."</span></td>";
				}
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $totalRows.="<td class='centered currency'><span>".$totalValues[$productionResultCodeId]."</span></td>";
				}
				
				$totalRows.="<td class='centered number'><span>".$totalQuantities['total']."</span></td>";
				$totalRows.="<td class='centered currency'><span>".$totalValues['total']."</span></td>";
						
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
			$totalRows.="</tr>";
	
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td>Porcentajes</td>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
        foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $totalRows.="<td class='centered percentage'><span>".(100*$totalQuantities[$productionResultCodeId]/$totalQuantities['total'])."</span></td>";
				}
				foreach ($plantProductionResultCodes as $productionResultCodeId=>$plantProductionResultCodeName){
          $totalRows.="<td></td>";
        }
				
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
						
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
			$totalRows.="</tr>";
      
      
	$productionRunTableBody='<tbody>'.$totalRows.$productionRunRows.$totalRows.'</tbody>';
	
  echo '<table>'.$productionRunTableHeader.$productionRunTableBody.'</table>';
		
}
/*
	

	if (!empty($producedProducts)){
		echo "<h3>Productos fabricados en el turno en el período</h3>";
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Raw Material')."</th>";
					echo "<th>".__('Finished Product')."</th>";
					foreach ($productionResultCodes as $productionResultCode){
						echo "<th class="centered">".$productionResultCode['ProductionResultCode']['code']."</th>";
					}
					echo "<th class="centered">".__('Total Value')."</th>";
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
						echo "<th  class="centered" colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
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
						echo "<td class="centered" colspan='3'>".$product['finished_product_name']."</td>";					
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
						echo "<td class="centered">A</td>";					
						echo "<td class="centered">B</td>";
						echo "<td class="centered">C</td>";
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
									$machineRow.="<td class="centered">-</td>";
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
						echo "<th  class="centered" colspan='".$rawMaterialsUse[$rawMaterial['raw_material_id']]."'>".$rawMaterial['raw_material_name']."</th>";					
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
								echo "<td class="centered" colspan='3'>".$product['finished_product_name']."</td>";					
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
								echo "<td class="centered">A</td>";					
								echo "<td class="centered">B</td>";
								echo "<td class="centered">C</td>";
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
											$operatorRow.="<td class="centered">-</td>";
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
*/
?>
</div>
