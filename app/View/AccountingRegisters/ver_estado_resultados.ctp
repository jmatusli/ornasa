<div class="accountingRegisteres estadoresultados">
<?php	
	echo "<h2>".__('Estado de Resultados')."</h2>";
	
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh'));
	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarEstadoResultadosDetallado'), array( 'class' => 'btn btn-primary')); 

	$totalPrevious=0;
	$totalCurrent=0;
	
	echo "<h2>Estado de Resultados</h2>";
	$dateBegin=new DateTime($startDate);
	$dateEnd=new DateTime($endDate);
	$resultsTableHeadOutput="<thead>";
		$resultsTableHeadOutput.="<tr>";
			$resultsTableHeadOutput.="<th class='right'>".__('Número de Cuenta')."</th>";
			$resultsTableHeadOutput.="<th>".__('Resultado')."</th>";
			$resultsTableHeadOutput.="<th class='centered'>Saldo Inicial</th>";
			$resultsTableHeadOutput.="<th class='centered'>Movimientos</th>";
			$resultsTableHeadOutput.="<th class='centered'>Saldo Final</th>";
		$resultsTableHeadOutput.="</tr>";
	$resultsTableHeadOutput.="</thead>";
	
	$dateBegin=new DateTime($startDate);
	$dateEnd=new DateTime($endDate);
	$resultsTableHeadFile="<thead>";
		$resultsTableHeadFile.="<tr><th colspan='5' align='center'>".COMPANY_NAME."</th></tr>";
		$resultsTableHeadFile.="<tr><th colspan='5' align='center'>".__('Reporte Estado de Resultados Detallado')." ".date('d-m-Y')."</th></tr>";
		$resultsTableHeadFile.="<tr><th colspan='5' align='center'>Período de ".$dateBegin->format('d-m-Y')." hasta ".$dateEnd->format('d-m-Y')."</th></tr>";
		
		$resultsTableHeadFile.="<tr>";
			$resultsTableHeadFile.="<th>".__('Número de Cuenta')."</th>";
			$resultsTableHeadFile.="<th>".__('Resultado')."</th>";
			$resultsTableHeadFile.="<th class='centered'>Saldo Inicial</th>";
			$resultsTableHeadFile.="<th class='centered'>Movimientos</th>";
			$resultsTableHeadFile.="<th class='centered'>Saldo Final</th>";
		$resultsTableHeadFile.="</tr>";
	$resultsTableHeadFile.="</thead>";
	
	$previousTotalCS=0;
	$currentTotalCS=0;
	
	$resultsTableBody="";
		$resultsTableBody.="<tr class='bold'>";
			$resultsTableBody.="<td class='right'>400</td>";
			$resultsTableBody.="<td>INGRESOS</td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
		$resultsTableBody.="</tr>";
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		//pr($results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]);
		if (!empty($results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_INGRESOS_OTROS]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_INGRESOS_OTROS]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_OTROS]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_OTROS]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_INGRESOS_OTROS]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_INGRESOS_OTROS]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_INGRESOS_OTROS]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}				
		
		$ingresoTotalInitial=$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['initial_saldo']+$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['initial_saldo']+$results[ACCOUNTING_CODE_INGRESOS_OTROS]['initial_saldo'];
		$ingresoTotalCurrent=$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['current_total']+$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['current_total']+$results[ACCOUNTING_CODE_INGRESOS_OTROS]['current_total'];
		$ingresoTotalFinal=$results[ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR]['final_saldo']+$results[ACCOUNTING_CODE_INGRESOS_DESCUENTOS]['final_saldo']+$results[ACCOUNTING_CODE_INGRESOS_OTROS]['final_saldo'];
		$resultsTableBody.="<tr class='totalrow'>";
			$resultsTableBody.="<td class='right'></td>";
			$resultsTableBody.="<td style='font-weight:bold'>TOTAL INGRESOS</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$ingresoTotalInitial."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$ingresoTotalCurrent."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$ingresoTotalFinal."</span></td>";
		$resultsTableBody.="</tr>";
		
		
		
		$resultsTableBody.="<tr class='bold'>";
			$resultsTableBody.="<td class='right'>500</td>";
			$resultsTableBody.="<td>COSTOS</td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
		$resultsTableBody.="</tr>";
		//pr($results[ACCOUNTING_CODE_COSTOS_VENTA]);
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_COSTOS_VENTA]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_COSTOS_VENTA]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_COSTOS_VENTA]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_COSTOS_VENTA]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_COSTOS_VENTA]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_COSTOS_VENTA]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_COSTOS_VENTA]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
		$costoTotalInitial=$results[ACCOUNTING_CODE_COSTOS_VENTA]['initial_saldo'];
		$costoTotalCurrent=$results[ACCOUNTING_CODE_COSTOS_VENTA]['current_total'];
		$costoTotalFinal=$results[ACCOUNTING_CODE_COSTOS_VENTA]['final_saldo'];
		$resultsTableBody.="<tr class='totalrow'>";
			$resultsTableBody.="<td class='right'></td>";
			$resultsTableBody.="<td style='font-weight:bold'>TOTAL COSTOS</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$costoTotalInitial."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$costoTotalCurrent."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$costoTotalFinal."</span></td>";
		$resultsTableBody.="</tr>";

		$resultsTableBody.="<tr class='bold'>";
			$resultsTableBody.="<td class='right'>600</td>";
			$resultsTableBody.="<td>GASTOS</td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
		$resultsTableBody.="</tr>";
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_GASTOS_ADMIN]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_GASTOS_ADMIN]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_ADMIN]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_ADMIN]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_ADMIN]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_GASTOS_ADMIN]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_GASTOS_ADMIN]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}		
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_GASTOS_VENTA]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_GASTOS_VENTA]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_VENTA]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_VENTA]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_VENTA]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_GASTOS_VENTA]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_GASTOS_VENTA]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}		
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
		$resultsTableBody.="<tr class='olive'>";
			$resultsTableBody.="<td class='right'>".$results[ACCOUNTING_CODE_GASTOS_OTROS]['code']."</td>";
			$resultsTableBody.="<td>".$results[ACCOUNTING_CODE_GASTOS_OTROS]['description']."</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_OTROS]['initial_saldo']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_OTROS]['current_total']."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$results[ACCOUNTING_CODE_GASTOS_OTROS]['final_saldo']."</span></td>";
		$resultsTableBody.="</tr>";
		if (!empty($results[ACCOUNTING_CODE_GASTOS_OTROS]['secondary'])){
			foreach ($results[ACCOUNTING_CODE_GASTOS_OTROS]['secondary'] as $secondaryCode){
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td class='right'>".$secondaryCode['code']."</td>";
					$resultsTableBody.="<td>".$secondaryCode['description']."</td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['initial_saldo']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['current_total']."</span></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$secondaryCode['final_saldo']."</span></td>";
					if (!empty($secondaryCode['tertiary'])){
						foreach ($secondaryCode['tertiary'] as $tertiaryCode){
							$resultsTableBody.="<tr class='italic small'>";
								$resultsTableBody.="<td class='right'>".$tertiaryCode['code']."</td>";
								$resultsTableBody.="<td>".$tertiaryCode['description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['initial_saldo']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['current_total']."</span></td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$tertiaryCode['final_saldo']."</span></td>";
							$resultsTableBody.="</tr>";
						}
					}
				$resultsTableBody.="</tr>";
			}
		}
				
		$gastoTotalInitial=$results[ACCOUNTING_CODE_GASTOS_ADMIN]['initial_saldo'];
		$gastoTotalInitial+=$results[ACCOUNTING_CODE_GASTOS_VENTA]['initial_saldo']+$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['initial_saldo'];
		$gastoTotalInitial+=$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['initial_saldo']+$results[ACCOUNTING_CODE_GASTOS_OTROS]['initial_saldo'];
		
		$gastoTotalCurrent=$results[ACCOUNTING_CODE_GASTOS_ADMIN]['current_total'];
		$gastoTotalCurrent+=$results[ACCOUNTING_CODE_GASTOS_VENTA]['current_total']+$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['current_total'];
		$gastoTotalCurrent+=$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['current_total']+$results[ACCOUNTING_CODE_GASTOS_OTROS]['current_total'];
		
		$gastoTotalFinal=$results[ACCOUNTING_CODE_GASTOS_ADMIN]['final_saldo'];
		$gastoTotalFinal+=$results[ACCOUNTING_CODE_GASTOS_VENTA]['final_saldo']+$results[ACCOUNTING_CODE_GASTOS_FINANCIEROS]['final_saldo'];
		$gastoTotalFinal+=$results[ACCOUNTING_CODE_GASTOS_PRODUCCION]['final_saldo']+$results[ACCOUNTING_CODE_GASTOS_OTROS]['final_saldo'];
		
		$resultsTableBody.="<tr class='totalrow'>";
			$resultsTableBody.="<td class='right'></td>";
			$resultsTableBody.="<td>TOTAL GASTOS</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$gastoTotalInitial."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$gastoTotalCurrent."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$gastoTotalFinal."</span></td>";
		$resultsTableBody.="</tr>";
		
		$resultsTableBody.="<tr class='bold'>";
			$resultsTableBody.="<td class='right'></td>";
			$resultsTableBody.="<td>UTILIDAD O PERDIDA</td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
			$resultsTableBody.="<td></td>";
		$resultsTableBody.="</tr>";

		$utilidadInitial=$ingresoTotalInitial-$costoTotalInitial-$gastoTotalInitial;
		$utilidadCurrent=$ingresoTotalCurrent-$costoTotalCurrent-$gastoTotalCurrent;
		$utilidadFinal=$ingresoTotalFinal-$costoTotalFinal-$gastoTotalFinal;
		$resultsTableBody.="<tr class='totalrow'>";
			$resultsTableBody.="<td class='right'></td>";
			$resultsTableBody.="<td>Utilidad o Pérdida del ejercicio</td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$utilidadInitial."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$utilidadCurrent."</span></td>";
			$resultsTableBody.="<td class='number right'><span class='amountright'>".$utilidadFinal."</span></td>";
		$resultsTableBody.="</tr>";
	
				
			$balanceTableCSFooter="<tr style='border:0px;'>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";

			$balanceTableCSFooter.="</tr>";
			$balanceTableCSFooter.="<tr style='border:0px;'>";
				$balanceTableCSFooter.="<td align='center' style='border:0px;'>Elaborado</td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td align='center' style='border:0px;'>Revisado</td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td align='center' style='border:0px;'>Autorizado</td>";
			$balanceTableCSFooter.="</tr>";
	
	
	$resultsTableBodyOutput="<tbody>".$resultsTableBody."</tbody>";
	$resultsTableBodyExcel="<tbody>".$resultsTableBody.$balanceTableCSFooter."</tbody>";
	
	$resultsTable="<table id='estado resultados'>".$resultsTableHeadOutput.$resultsTableBodyOutput."</table>";
	echo $resultsTable;
	
	$reportFile="<table id='estado resultados'>".$resultsTableHeadFile.$resultsTableBodyExcel."</table>";
	
	$_SESSION['reporteEstadoResultadosDetallado'] = $reportFile;
	
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("US$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});

</script>
