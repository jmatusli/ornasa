<div class="accountingRegisteres balancegeneral">
<?php	
	echo "<h2>".__('Balance General')."</h2>";
	
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
	//echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
	echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo $this->Form->end(__('Refresh')); 
	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarBalanceGeneral'), array( 'class' => 'btn btn-primary')); 

	//$totalPrevious=0;
	$totalCurrent=0;
	
	$dateEnd=new DateTime($endDate);
	echo "<h2>Balance General en ".$dateEnd->format('d-m-Y')."</h2>";
	
		$resultsTableHeadOutput="<thead>";
			$resultsTableHeadOutput.="<tr>";
				$resultsTableHeadOutput.="<th>".__('Code')."</th>";
				$resultsTableHeadOutput.="<th>".__('Description')."</th>";
				$resultsTableHeadOutput.="<th class='centered'>".__('Saldo C$')."</th>";
				$resultsTableHeadOutput.="<th class='centered'>".__('Total C$')."</th>";
			$resultsTableHeadOutput.="</tr>";
		$resultsTableHeadOutput.="</thead>";
		
		$dateEnd=new DateTime($endDate);
		$resultsTableHeadFile="<thead>";
			$resultsTableHeadFile.="<tr><th colspan='4' align='center'>".COMPANY_NAME."</th></tr>";
			$resultsTableHeadFile.="<tr><th colspan='4' align='center'>".__('Reporte Contable - Balance General')." ".date('d-m-Y')."</th></tr>";
			$resultsTableHeadFile.="<tr><th colspan='4' align='center'>Balance General en ".$dateEnd->format('d-m-Y')."</th></tr>";
			$resultsTableHeadFile.="<tr>";
				$resultsTableHeadFile.="<th>".__('Code')."</th>";
				$resultsTableHeadFile.="<th>".__('Description')."</th>";
				$resultsTableHeadFile.="<th class='centered'>".__('Saldo')."</th>";
				$resultsTableHeadFile.="<th class='centered'>".__('Total')."</th>";
			$resultsTableHeadFile.="</tr>";
		$resultsTableHeadFile.="</thead>";
	
		$resultsTableBody="";
		foreach ($results as $result){
			$resultsTableBody.="<tr>";
				$resultsTableBody.="<td>".$result['accounting_code_description']."</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
			$resultsTableBody.="</tr>";
			
			foreach ($result['children'] as $child){
				$resultsTableBody.="<tr class='subaccountheader'>";
					$resultsTableBody.="<td></td>";
					$resultsTableBody.="<td>".$child['accounting_code_description']."</td>";
					$resultsTableBody.="<td></td>";
					$resultsTableBody.="<td></td>";
				$resultsTableBody.="</tr>";
				if (!empty($child['grandchildren'])){
					foreach ($child['grandchildren'] as $grandchild){
						$resultsTableBody.="<tr>";
							$resultsTableBody.="<td>".$grandchild['accounting_code_code']."</td>";
							$resultsTableBody.="<td>".$grandchild['accounting_code_description']."</td>";
							$resultsTableBody.="<td class='number right'><span class='amountright'>".$grandchild['saldo']."</span></td>";
							$resultsTableBody.="<td></td>";
						$resultsTableBody.="</tr>";
						foreach ($grandchild['greatgrandchildren'] as $greatgrandchild){
								$resultsTableBody.="<tr>";
								$resultsTableBody.="<td style='text-indent:20px;'>".$greatgrandchild['accounting_code_code']."</td>";
								$resultsTableBody.="<td>".$greatgrandchild['accounting_code_description']."</td>";
								$resultsTableBody.="<td class='number right'><span class='amountright'>".$greatgrandchild['saldo']."</span></td>";
								$resultsTableBody.="<td></td>";
							$resultsTableBody.="</tr>";
						}
					}
				}
				$resultsTableBody.="<tr class='subaccountfooter'>";
					$resultsTableBody.="<td>TOTAL</td>";
					$resultsTableBody.="<td>".$child['accounting_code_description']."</td>";
					$resultsTableBody.="<td></td>";
					$resultsTableBody.="<td class='number right'><span class='amountright'>".$child['saldo']."</span></td>";
				$resultsTableBody.="</tr>";
			}
			$resultsTableBody.="<tr class='totalrow'>";
				$resultsTableBody.="<td>TOTAL</td>";
				$resultsTableBody.="<td>".$result['accounting_code_description']."</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td class='number right'><span class='amountright'>".$result['saldo']."</span></td>";
			$resultsTableBody.="</tr>";
			$resultsTableBody.="<tr><td style='height:40px;'></td><td></td><td></td><td></td></tr>";
		}
		
		$totalActivo=0;
		$totalPasivo=0;
		$totalPasivoPatrimonio=0;
		foreach ($results as $result){
			//pr($result);
			if ($result['accounting_code_id']==ACCOUNTING_CODE_ACTIVOS){
				$totalActivo+=$result['saldo'];
			}
			elseif  ($result['accounting_code_id']==ACCOUNTING_CODE_PASIVOS){
				$totalPasivo+=$result['saldo'];
				$totalPasivoPatrimonio+=$result['saldo'];
			}
			else {
				$totalPasivoPatrimonio+=$result['saldo'];
			}
		}
			
			$resultsTableBody.="<tr>";
				$resultsTableBody.="<td>TOTAL ACTIVO</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td class='number right'><span class='amountright'>".$totalActivo."</span></td>";
			$resultsTableBody.="</tr>";
			$resultsTableBody.="<tr>";
				$resultsTableBody.="<td>TOTAL PASIVO</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td class='number right'><span class='amountright'>".$totalPasivo."</span></td>";
				$resultsTableBody.="<td></td>";
			$resultsTableBody.="</tr>";
			/*
			$resultsTableBody.="<tr>";
				$resultsTableBody.="<td>PATRIMONIO Y CAPITAL</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
			$resultsTableBody.="</tr>";
			*/
			/*
			foreach($patrimonyResults as $patrimonyResult){
				//pr($patrimonyResult);
				$resultsTableBody.="<tr>";
					$resultsTableBody.="<td>". $patrimonyResult['accounting_code_description']."</td>";
					$resultsTableBody.="<td></td>";
					if ($patrimonyResult['accounting_code_id']==ACCOUNTING_CODE_LOSSES_GAINS_EXERCISE){
						$resultsTableBody.="<td class='number right'><span class='amountright'>".($patrimonyResult['saldo']+$utilityAmount)."</span></td>";
					}
					else {
						$resultsTableBody.="<td class='number right'><span class='amountright'>".$patrimonyResult['saldo']."</span></td>";
					}
					
					
					$resultsTableBody.="<td></td>";
				$resultsTableBody.="</tr>";
			}
			*/
			//$resultsTableBody.="<tr>";
			//	$resultsTableBody.="<td>TOTAL UTILIDAD/PERDIDA (NO REGISTRADO)</td>";
			//	$resultsTableBody.="<td></td>";
			//	$resultsTableBody.="<td class='number right'><span class='amountright'>".$utilityAmount."</span></td>";
			//	$resultsTableBody.="<td></td>";
			//$resultsTableBody.="</tr>";
			/*
			$resultsTableBody.="<tr>";
				$resultsTableBody.="<td>TOTAL PASIVO + PATRIMONIO (INCLUYENDO UTILIDAD)</td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td></td>";
				$resultsTableBody.="<td class='number right'><span class='amountright'>".($totalPasivoPatrimonio+$utilityAmount)."</span></td>";
			$resultsTableBody.="</tr>";
			*/
		
			$balanceTableCSFooter="<tr style='border:0px;'>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
			$balanceTableCSFooter.="</tr>";
			$balanceTableCSFooter.="<tr style='border:0px;'>";
				$balanceTableCSFooter.="<td align='center' style='border:0px;'>Elaborado</td>";
				$balanceTableCSFooter.="<td align='right' style='border:0px;'>Revisado</td>";
				$balanceTableCSFooter.="<td style='border:0px;'></td>";
				$balanceTableCSFooter.="<td align='center' style='border:0px;'>Autorizado</td>";
			$balanceTableCSFooter.="</tr>";
		
		$resultsTableBodyOutput="<tbody>".$resultsTableBody."</tbody>";
		$resultsTableBodyExcel="<tbody>".$resultsTableBody.$balanceTableCSFooter."</tbody>";
	
	$resultsTableOutput="<table id='balance general'>".$resultsTableHeadOutput.$resultsTableBodyOutput."</table>";
	echo $resultsTableOutput;
	
	$reportFile="<table id='balance general'>".$resultsTableHeadFile.$resultsTableBodyExcel."</table>";
	$_SESSION['reporteBalanceGeneral'] = $reportFile;
	
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
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
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
		formatCSCurrencies();
	});
</script>
