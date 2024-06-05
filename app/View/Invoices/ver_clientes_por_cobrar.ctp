<div class="invoices index fullwidth">
<?php 
	echo "<h2>".__('Reporte de Clientes por Cobrar')."</h2>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarClientesPorCobrar'), array( 'class' => 'btn btn-primary')); 
	$reportTable="";
	$table_id="clientes_por_cobrar";
	$reportTable.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th>Cliente</th>";
        $reportTable.="<th>Contacto</th>";
				$reportTable.="<th class='centered'>Saldo Pendiente</th>";
				$reportTable.="<th class='centered'>1-30</th>";
				$reportTable.="<th class='centered'>31-45</th>";
				$reportTable.="<th class='centered'>46-60</th>";
				$reportTable.="<th class='centered'>>60</th>";
				$reportTable.="<th class='centered'>Promedio Crédito Año</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		$reportTable.="<tbody>";
		$totalCSPending=0;
		$totalCSUnder30=0;
		$totalCSUnder45=0;
		$totalCSUnder60=0;
		$totalCSOver60=0;
		$clientBody="";
		foreach ($clients as $client){
      $contactData=(empty($client['ThirdParty']['first_name'])?"":strtoupper($client['ThirdParty']['first_name'])).(empty($client['ThirdParty']['last_name'])?"":(" ".strtoupper($client['ThirdParty']['last_name'])));
      $contactData.=(empty($client['ThirdParty']['phone'])?"-":((empty($contactData)?"":"<br/>")."Tel: ".$client['ThirdParty']['phone']));
			//pr($client);
			if ($client['saldo']>0){
				$clientBody.="<tr>";
					$clientBody.="<td>".$this->Html->link($client['ThirdParty']['company_name'], array('controller' => 'invoices', 'action' => 'verFacturasPorCobrar', $client['ThirdParty']['id']))."</td>";
          $clientBody.="<td>".$contactData."</td>";
					$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['saldo']."</span></td>";
					$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder30']."</span></td>";
					$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder45']."</span></td>";
					$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingUnder60']."</span></td>";
					$clientBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$client['pendingOver60']."</span></td>";
					$clientBody.="<td class='centered'><span class='amountright'>".round($client['historicalCredit'])."</span></td>";
					$totalCSPending+=$client['saldo'];
					$totalCSUnder30+=$client['pendingUnder30'];
					$totalCSUnder45+=$client['pendingUnder45'];
					$totalCSUnder60+=$client['pendingUnder60'];
					$totalCSOver60+=$client['pendingOver60'];
				$clientBody.="</tr>";
			}
		}	
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
        $totalRow.="<td></td>";	
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder30."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder45."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder60."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSOver60."</span></td>";
				$totalRow.="<td class='centered'><span class='amountright'></span></td>";
			$totalRow.="</tr>";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total %</td>";	
        $totalRow.="<td></td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSPending/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder30/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder45/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder60/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSOver60/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'></td>";
			$totalRow.="</tr>";
			$reportTable.=$totalRow.$clientBody.$totalRow;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
	echo $reportTable;
	
	$_SESSION['clientesPorCobrar'] = $reportTable;
?>
</div>
<script>
	
	function formatCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$ ");
		});
	};
	
	$(document).ready(function(){
		formatCurrencies();
	});
</script>