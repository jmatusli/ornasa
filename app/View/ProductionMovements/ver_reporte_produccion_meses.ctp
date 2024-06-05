<div class="stockItems view report">

<?php 
	echo "<h2>".__('Reporte Producción Supervisor')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>"; 
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		$reportTypes=array();
		$reportTypes[]="Costo del Producto";
		$reportTypes[]="Efectividad de Producción";
		$reportTypes[]="Cantidad de Envases";
		$reportTypes[]="Global";
		echo $this->Form->input('Report.report_type',array('label'=>__('Tipo de Reporte'),'default'=>$report_type,'options'=>$reportTypes));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	$produccionTable="";
	$produccionTable="<table>";
		$produccionTable.="<thead>";
			$produccionTable.="<tr>";
			switch ($report_type){
				case "0":
					$produccionTable.="<th class='centered'>".__('Mes')."</th>";
					$produccionTable.="<th class='centered'>".__('# Ordenes')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo A')."</th>";
					$produccionTable.="<th class='centered'>".__('A %')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo B')."</th>";
					$produccionTable.="<th class='centered'>".__('B %')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo C')."</th>";
					$produccionTable.="<th class='centered'>".__('C %')."</th>";
					$produccionTable.="<th class='centered'>".__('TOTAL')."</th>";
					break;
				case "1":
					$produccionTable.="<th class='centered'>".__('Mes')."</th>";
					$produccionTable.="<th class='centered'>".__('# Ordenes')."</th>";
					$produccionTable.="<th class='centered'>".__('A %')."</th>";
					$produccionTable.="<th class='centered'>".__('B %')."</th>";
					$produccionTable.="<th class='centered'>".__('C %')."</th>";
					$produccionTable.="<th class='centered'>".__('TOTAL')."</th>";
					break;
				case "2":
					$produccionTable.="<th class='centered'>".__('Mes')."</th>";
					$produccionTable.="<th class='centered'>".__('# Ordenes')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases A')."</th>";
					$produccionTable.="<th class='centered'>".__('A %')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases B')."</th>";
					$produccionTable.="<th class='centered'>".__('B %')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases C')."</th>";
					$produccionTable.="<th class='centered'>".__('C %')."</th>";
					$produccionTable.="<th class='centered'>".__('TOTAL')."</th>";
					break;
				case "3":
					$produccionTable.="<th class='centered'>".__('Mes')."</th>";
					$produccionTable.="<th class='centered'>".__('# Ordenes')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo A')."</th>";
					//$produccionTable.="<th class='centered'>".__('Costo A %')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases A')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases A %')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo B')."</th>";
					//$produccionTable.="<th class='centered'>".__('Costo B %')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases B')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases B %')."</th>";
					$produccionTable.="<th class='centered'>".__('Costo C')."</th>";
					//$produccionTable.="<th class='centered'>".__('Costo C %')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases C')."</th>";
					$produccionTable.="<th class='centered'>".__('Envases C %')."</th>";
					$produccionTable.="<th class='centered'>".__('TOTAL COSTO')."</th>";
					$produccionTable.="<th class='centered'>".__('TOTAL ENVASES')."</th>";
					break;
				}
			$produccionTable.="</tr>";
		$produccionTable.="</thead>";
		
		$grandTotalRuns=0;
		$grandTotalCostA=0;
		$grandTotalCostB=0;
		$grandTotalCostC=0;
		$grandTotalQuantityA=0;
		$grandTotalQuantityB=0;
		$grandTotalQuantityC=0;
		
		$produccionTable.="<tbody>";
		
		switch ($report_type){
			case "0":
				$productionRows="";
				foreach ($monthArray as $month){
					//pr($month);
					$grandTotalCostA+=$month['total_A_cost'];
					$grandTotalCostB+=$month['total_B_cost'];
					$grandTotalCostC+=$month['total_C_cost'];
					$grandTotalRuns+=$month['production_run_count'];
					
					$monthTotalCost=$month['total_A_cost']+$month['total_B_cost']+$month['total_C_cost'];
					
					$productionRows.="<tr>"; 
						$productionRows.="<td class='centered'>".$month['period']."</td>";
						$productionRows.="<td class='centered'>".$month['production_run_count']."</td>";
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_A_cost']."</span></td>";
						if (($monthTotalCost)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_A_cost']/$monthTotalCost)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_B_cost']."</span></td>";
						if (($monthTotalCost)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_B_cost']/$monthTotalCost)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_C_cost']."</span></td>";
						if (($monthTotalCost)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_C_cost']/$monthTotalCost)."</span></td>";							
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$monthTotalCost."</span></td>";
					$productionRows.="</tr>"; 
				}
				break;
			case "1":
				$productionRows="";
				foreach ($monthArray as $month){
					$grandTotalCostA+=$month['total_A_cost'];
					$grandTotalCostB+=$month['total_B_cost'];
					$grandTotalCostC+=$month['total_C_cost'];
					$grandTotalRuns+=$month['production_run_count'];
					$monthTotalCost=$month['total_A_cost']+$month['total_B_cost']+$month['total_C_cost'];
					
					if (($monthTotalCost)>0){
						$productionRows.="<tr>"; 
							$productionRows.="<td class='centered'>".$month['period']."</td>";
							$productionRows.="<td class='centered'>".$month['production_run_count']."</td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_A_cost']/$monthTotalCost)."</span></td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_B_cost']/$monthTotalCost)."</span></td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_C_cost']/$monthTotalCost)."</span></td>";							
							$productionRows.="<td class='centered percentage'><span class='amountright'>100</span></td>";							
						$productionRows.="</tr>"; 
					}
					else {
						$productionRows.="<tr>"; 
							$productionRows.="<td class='centered'>".$month['period']."</td>";
							$productionRows.="<td class='centered'>".$month['production_run_count']."</td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
							$productionRows.="<td class='centered percentage'><span class='amountright'>100</span></td>";							
						$productionRows.="</tr>"; 
					}
				}
				break;
			case "2":
				$productionRows="";
				foreach ($monthArray as $month){
					$grandTotalQuantityA+=$month['total_A_quantity'];
					$grandTotalQuantityB+=$month['total_B_quantity'];
					$grandTotalQuantityC+=$month['total_C_quantity'];
					$grandTotalRuns+=$month['production_run_count'];
					$monthTotalQuantity=$month['total_A_quantity']+$month['total_B_quantity']+$month['total_C_quantity'];
					
					$productionRows.="<tr>"; 
						$productionRows.="<td class='centered'>".$month['period']."</td>";
						$productionRows.="<td class='centered'>".$month['production_run_count']."</td>";
						$productionRows.="<td class='centered number'><span class='amountright'>".$month['total_A_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_A_quantity']/$monthTotalQuantity)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered number'><span class='amountright'>".$month['total_B_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_B_quantity']/$monthTotalQuantity)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered number'><span class='amountright'>".$month['total_C_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_C_quantity']/$monthTotalQuantity)."</span></td>";							
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered number'><span class='amountright'>".$monthTotalQuantity."</span></td>";
					$productionRows.="</tr>"; 
				}
				break;
			case "3":
				$productionRows="";
				foreach ($monthArray as $month){
					$grandTotalCostA+=$month['total_A_cost'];
					$grandTotalCostB+=$month['total_B_cost'];
					$grandTotalCostC+=$month['total_C_cost'];
					$grandTotalRuns+=$month['production_run_count'];
					$monthTotalCost=$month['total_A_cost']+$month['total_B_cost']+$month['total_C_cost'];
				
					$grandTotalQuantityA+=$month['total_A_quantity'];
					$grandTotalQuantityB+=$month['total_B_quantity'];
					$grandTotalQuantityC+=$month['total_C_quantity'];
					$monthTotalQuantity=$month['total_A_quantity']+$month['total_B_quantity']+$month['total_C_quantity'];
					
					$productionRows.="<tr>"; 
						$productionRows.="<td class='centered'>".$month['period']."</td>";
						$productionRows.="<td class='centered'>".$month['production_run_count']."</td>";
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_A_cost']."</span></td>";
						//if (($monthTotalQuantity)>0){
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_A_cost']/$monthTotalCost)."</span></td>";
						//}
						//else {
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						//}
						$productionRows.="<td class='centered number'><span class='currency'></span><span class='amountright'>".$month['total_A_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_A_quantity']/$monthTotalQuantity)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_B_cost']."</span></td>";
						//if (($monthTotalQuantity)>0){
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_B_cost']/$monthTotalCost)."</span></td>";
						//}
						//else {
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						//}
						$productionRows.="<td class='centered number'></span><span class='amountright'>".$month['total_B_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_B_quantity']/$monthTotalQuantity)."</span></td>";
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$month['total_C_cost']."</span></td>";
						//if (($monthTotalQuantity)>0){
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_C_cost']/$monthTotalCost)."</span></td>";
						//}
						//else {
						//	$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						//}
						$productionRows.="<td class='centered number'><span class='amountright'>".$month['total_C_quantity']."</span></td>";
						if (($monthTotalQuantity)>0){
							$productionRows.="<td class='centered percentage'><span class='amountright'>".(100*$month['total_C_quantity']/$monthTotalQuantity)."</span></td>";							
						}
						else {
							$productionRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						}
						$productionRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$monthTotalCost."</span></td>";
						$productionRows.="<td class='centered number'><span class='amountright'>".$monthTotalQuantity."</span></td>";
					$productionRows.="</tr>"; 
				}
				break;
		}
		$totalRows="";
		$grandTotalCost=$grandTotalCostA+$grandTotalCostB+$grandTotalCostC;
		$grandTotalQuantity=$grandTotalQuantityA+$grandTotalQuantityB+$grandTotalQuantityC;		
		switch ($report_type){	
			case "0":
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered'>".$grandTotalRuns."</td>";
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostA."</span></td>";
					if ($grandTotalCost>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostA/$grandTotalCost)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostB."</span></td>";
					if ($grandTotalCost>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostB/$grandTotalCost)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostC."</span></td>";
					if ($grandTotalCost>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostC/$grandTotalCost)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCost."</span></td>";
				$totalRows.="</tr>";	
				break;
			case "1":
				if ($grandTotalCost>0){
					$totalRows.="<tr class='totalrow'>";
						$totalRows.="<td>Total</td>";
						$totalRows.="<td class='centered'>".$grandTotalRuns."</td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostA/$grandTotalCost)."</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostB/$grandTotalCost)."</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostC/$grandTotalCost)."</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>100</span></td>";
					$totalRows.="</tr>";	
				}
				else {
					$totalRows.="<tr class='totalrow'>";
						$totalRows.="<td>Total</td>";
						$totalRows.="<td class='centered'>".$grandTotalRuns."</td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
						$totalRows.="<td class='centered percentage'><span class='amountright'>100</span></td>";
					$totalRows.="</tr>";	
				}
				break;
			case "2":
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered'>".$grandTotalRuns."</td>";
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantityA."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityA/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered number'></span><span class='amountright'>".$grandTotalQuantityB."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityB/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantityC."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityC/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantity."</span></td>";
				$totalRows.="</tr>";	
				break;
			case "3":
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered'>".$grandTotalRuns."</td>";
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostA."</span></td>";
					//if ($grandTotalCost>0){
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostA/$grandTotalCost)."</span></td>";
					//}
					//else {
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					//}
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantityA."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityA/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostB."</span></td>";
					//if ($grandTotalCost>0){
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostB/$grandTotalCost)."</span></td>";
					//}
					//else {
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					//}
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantityB."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityB/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCostC."</span></td>";
					//if ($grandTotalCost>0){
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalCostC/$grandTotalCost)."</span></td>";
					//}
					//else {
					//	$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					//}
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantityC."</span></td>";
					if ($grandTotalQuantity>0){
						$totalRows.="<td class='centered percentage'><span class='amountright'>".(100*$grandTotalQuantityC/$grandTotalQuantity)."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span class='amountright'>0</span></td>";
					}
					$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$grandTotalCost."</span></td>";
					$totalRows.="<td class='centered number'><span class='amountright'>".$grandTotalQuantity."</span></td>";
				$totalRows.="</tr>";	
				break;	
		}
		$produccionTable.=$totalRows.$productionRows.$totalRows."</tbody>";
	$produccionTable.="</table>";
  
  $shiftTables=[];
  
  $periodTotalsForMachine=[];
  $periodAcceptableTotalsForMachine=[];
  foreach ($shifts[0]['machines'] as $machine){
    $periodTotalForMachine=[];
    $periodAcceptableTotalForMachine=[];
    foreach ($shiftMonthArray as $shiftMonth){            
      $periodTotalForMachine[]=0;
      $periodAcceptableTotalForMachine[]=0; 
    }
    $periodTotalsForMachine[]=$periodTotalForMachine;
    $periodAcceptableTotalsForMachine[]=$periodAcceptableTotalForMachine;
  }
  //pr($periodTotalsForMachine);
  $periodTotalsAllShifts=[];
  $periodAcceptableTotalsAllShifts=[];      
  foreach ($shiftMonthArray as $shiftMonth){            
    $periodTotalsAllShifts[]=0;
    $periodAcceptableTotalsAllShifts[]=0; 
  }
  $grandTotalAllShiftsOP=0;
  $grandTotalAllShiftsAcceptableOP=0;
  
  foreach ($shifts as $shift){
    $shiftTable="";
    
    $shiftTable.="<table id='".$shift['Shift']['name']."'>";
      $shiftTable.="<thead>";
        $shiftTable.="<tr>";
          $shiftTable.="<th class='centered'>&nbsp;</th>";
          foreach ($shiftMonthArray as $shiftMonth){
            $shiftTable.="<th class='centered' colspan=2>".$shiftMonth['period']."</th>";
          }
          $shiftTable.="<th class='centered' colspan=2>Totales</th>";
        $shiftTable.="</tr>";
        $shiftTable.="<tr>";
          $shiftTable.="<th class='centered'>".__('Machine')."</th>";
          foreach ($shiftMonthArray as $shiftMonth){
            $shiftTable.="<th class='centered'># OP</th>";
            $shiftTable.="<th class='centered'>% Accep</th>";
          }
          $shiftTable.="<th class='centered'># OP</th>";
            $shiftTable.="<th class='centered'>% Accep</th>";
        $shiftTable.="</tr>";
      $shiftTable.="</thead>";
      
      $grandTotalOP=0;
      $grandTotalAcceptableOP=0;
      
      $periodTotalsForShift=[];
      $periodAcceptableTotalsForShift=[];      
      foreach ($shiftMonthArray as $shiftMonth){            
        $periodTotalsForShift[]=0;
        $periodAcceptableTotalsForShift[]=0; 
      }
      //pr($periodTotalsForShift);
      $shiftTable.="<tbody>";
				$machineRows="";
        $machineCounter=0;
				foreach ($shift['machines'] as $machine){
					//pr($machine);
					
          $machineTotalOP=0;
          $machineTotalAcceptableOP=0;
          
					$machineRows.="<tr>"; 
						$machineRows.="<td class='centered'>".$this->html->link($machine['name'],['controller'=>'machines','action'=>'view',$machine['id']])."</td>";
            $periodCounter=0;
            
            foreach ($machine['machine_periods'] as $machinePeriod){
              $machineRows.="<td class='centered'>".$machinePeriod['total_op']."</td>";
              $machineRows.="<td class='centered'>".($machinePeriod['total_op'] > 0 ?round(100*$machinePeriod['acceptable_op']/$machinePeriod['total_op'],2):0)." % (".$machinePeriod['acceptable_op'].")</td>";

              $machineTotalOP+=$machinePeriod['total_op'];
              $machineTotalAcceptableOP+=$machinePeriod['acceptable_op'];
              $periodTotalsForShift[$periodCounter]+=$machinePeriod['total_op'];
              $periodAcceptableTotalsForShift[$periodCounter]+=$machinePeriod['acceptable_op'];
              $grandTotalOP+=$machinePeriod['total_op'];
              $grandTotalAcceptableOP+=$machinePeriod['acceptable_op'];
              
              $periodTotalsForMachine[$machineCounter][$periodCounter]+=$machinePeriod['total_op'];
              $periodAcceptableTotalsForMachine[$machineCounter][$periodCounter]+=$machinePeriod['acceptable_op'];
              $periodTotalsAllShifts[$periodCounter]+=$machinePeriod['total_op'];
              $periodAcceptableTotalsAllShifts[$periodCounter]+=$machinePeriod['acceptable_op'];
              $grandTotalAllShiftsOP+=$machinePeriod['total_op'];
              $grandTotalAllShiftsAcceptableOP+=$machinePeriod['acceptable_op'];
              $periodCounter++;
            }
						$machineRows.="<td class='centered'>".$machineTotalOP."</td>";
            $machineRows.="<td class='centered'>".($machineTotalOP > 0 ?round(100*$machineTotalAcceptableOP/$machineTotalOP,2):0)." % (".$machineTotalAcceptableOP.")</td>";
					$machineRows.="</tr>"; 
          $machineCounter++;
				}

        $totalRows="";
        
        $totalRows.="<tr class='totalrow'>";
          $totalRows.="<td>Total</td>";
          for ($per=0;$per<count($shiftMonthArray);$per++){
            $totalRows.="<td class='centered'>".$periodTotalsForShift[$per]."</td>";
            $totalRows.="<td class='centered'>".($periodTotalsForShift[$per] > 0 ?round(100*$periodAcceptableTotalsForShift[$per]/$periodTotalsForShift[$per],2):0)." % (".$periodAcceptableTotalsForShift[$per].")</td>";
          }
          $totalRows.="<td class='centered'>".$grandTotalOP."</td>";
          $totalRows.="<td class='centered'>".($grandTotalOP > 0 ?round(100*$grandTotalAcceptableOP/$grandTotalOP,2):0)." % (".$grandTotalAcceptableOP.")</td>";
        $totalRows.="</tr>";	
  
      $shiftTable.=$totalRows.$machineRows.$totalRows."</tbody>";
    $shiftTable.="</table>";
    $shiftTables[$shift['Shift']['id']]=$shiftTable;
  }
  
  $allShiftsTable="";
    
  $allShiftsTable.="<table id='all_shifts'>";
    $allShiftsTable.="<thead>";
      $allShiftsTable.="<tr>";
        $allShiftsTable.="<th class='centered'>&nbsp;</th>";
        foreach ($shiftMonthArray as $shiftMonth){
          $allShiftsTable.="<th class='centered' colspan=2>".$shiftMonth['period']."</th>";
        }
        $allShiftsTable.="<th class='centered' colspan=2>Totales</th>";
      $allShiftsTable.="</tr>";
      $allShiftsTable.="<tr>";
        $allShiftsTable.="<th class='centered'>".__('Machine')."</th>";
        foreach ($shiftMonthArray as $shiftMonth){
          $allShiftsTable.="<th class='centered'># OP</th>";
          $allShiftsTable.="<th class='centered'>% Accep</th>";
        }
        $allShiftsTable.="<th class='centered'># OP</th>";
          $allShiftsTable.="<th class='centered'>% Accep</th>";
      $allShiftsTable.="</tr>";
    $allShiftsTable.="</thead>";
    
    $allShiftsTable.="<tbody>";
      $machineRows="";
      $machineCounter=0;
      
      $machineGrandTotalOP=0;
      $machineGrandTotalAcceptableOP=0;
      
      foreach ($shifts[0]['machines'] as $machine){
        $machineRows.="<tr>"; 
          $machineRows.="<td class='centered'>".$this->html->link($machine['name'],['controller'=>'machines','action'=>'view',$machine['id']])."</td>";
          $periodCounter=0;
          foreach ($machine['machine_periods'] as $machinePeriod){
            $machineRows.="<td class='centered'>".$periodTotalsForMachine[$machineCounter][$periodCounter]."</td>";
            $machineRows.="<td class='centered'>".($periodTotalsForMachine[$machineCounter][$periodCounter] > 0 ?round(100*$periodAcceptableTotalsForMachine[$machineCounter][$periodCounter]/$periodTotalsForMachine[$machineCounter][$periodCounter],2):0)." % (".$periodAcceptableTotalsForMachine[$machineCounter][$periodCounter].")</td>";
            
            $machineGrandTotalOP+=$periodTotalsForMachine[$machineCounter][$periodCounter];
            $machineGrandTotalAcceptableOP+=$periodAcceptableTotalsForMachine[$machineCounter][$periodCounter];
              
            $periodCounter++;
          }
          $machineRows.="<td class='centered'>".$machineGrandTotalOP."</td>";
          $machineRows.="<td class='centered'>".($machineGrandTotalAcceptableOP > 0 ?round(100*$machineTotalAcceptableOP/$machineGrandTotalAcceptableOP,2):0)." % (".$machineGrandTotalAcceptableOP.")</td>";
        $machineRows.="</tr>"; 
        $machineCounter++;
      }

      $totalRows="";
      
      $totalRows.="<tr class='totalrow'>";
        $totalRows.="<td>Total</td>";
        for ($per=0;$per<count($shiftMonthArray);$per++){
          $totalRows.="<td class='centered'>".$periodTotalsAllShifts[$per]."</td>";
          $totalRows.="<td class='centered'>".($periodTotalsAllShifts[$per] > 0 ?round(100*$periodAcceptableTotalsAllShifts[$per]/$periodTotalsAllShifts[$per],2):0)." % (".$periodAcceptableTotalsAllShifts[$per].")</td>";
        }
        $totalRows.="<td class='centered'>".$grandTotalAllShiftsOP."</td>";
        $totalRows.="<td class='centered'>".($grandTotalAllShiftsOP > 0 ?round(100*$grandTotalAllShiftsAcceptableOP/$grandTotalAllShiftsOP,2):0)." % (".$grandTotalAllShiftsAcceptableOP.")</td>";
      $totalRows.="</tr>";	

    $allShiftsTable.=$totalRows.$machineRows.$totalRows."</tbody>";
  $allShiftsTable.="</table>";
  
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteProduccionMeses'], ['class' => 'btn btn-primary']); 

	switch ($report_type){
		case "0":
			echo "<h2>".__('Costo del Producto')."</h2>"; 
			break;
		case "1":
			echo "<h2>".__('Efectividad de Producción')."</h2>"; 
			break;
		case "2":
			echo "<h2>".__('Cantidad de Envases')."</h2>"; 
			break;
		case "3":
			echo "<h2>".__('Global')."</h2>"; 
			break;
	}
	echo $produccionTable; 
  
  $excelExport=$produccionTable;
  
  echo "<h3>".__("Todos Turnos")."</h3>";
  echo $allShiftsTable;
  $excelExport.=$allShiftsTable;
  
  foreach ($shifts as $shift){
    echo "<h3>".$this->Html->link($shift['Shift']['name'],['controller'=>'shifts','action'=>'view',$shift['Shift']['id']])."</h3>";
    echo $shiftTables[$shift['Shift']['id']];
    $excelExport.=$shiftTables[$shift['Shift']['id']];
  }
  
  
	
	$_SESSION['reporteProduccionMeses'] = $excelExport;
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').prepend("C$ ");
		});
	}
	
	function formatPercentages(){
		$("td.percentage span.amountright").each(function(){
			$(this).number(true,2);
			$(this).append(" %");
		});
	}
	
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatPercentages();
	});
	
	$('#ReportReportType').change(function(event){
		$('#ReportVerReporteProduccionMesesForm').submit();
	});


</script>