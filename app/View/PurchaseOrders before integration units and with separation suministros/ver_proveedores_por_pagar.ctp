<div class="purchaseorders index fullwidth">
<?php 
	echo "<h2>".__('Reporte de Proveedores por Pagar')."</h2>";
	echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarProveedoresPorPagar'], ['class' => 'btn btn-primary']); 
	$reportTable="";
	$table_id="proveedores_por_pagar";
	$reportTable.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th>Proveedor</th>";
				$reportTable.="<th class='centered'>Saldo Pendiente</th>";
				$reportTable.="<th class='centered'>1-30</th>";
				$reportTable.="<th class='centered'>31-45</th>";
				$reportTable.="<th class='centered'>46-60</th>";
				$reportTable.="<th class='centered'>>60</th>";
				//$reportTable.="<th class='centered'>Promedio Crédito Año</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		$reportTable.="<tbody>";
		$totalCSPending=0;
		$totalCSUnder30=0;
		$totalCSUnder45=0;
		$totalCSUnder60=0;
		$totalCSOver60=0;
		$providerBody="";
		foreach ($providers as $provider){
			//pr($provider);
			if ($provider['saldo']>0){
				$providerBody.="<tr>";
					$providerBody.="<td>".$this->Html->link($provider['ThirdParty']['company_name'], ['controller' => 'purchaseOrders', 'action' => 'verFacturasPorPagar', $provider['ThirdParty']['id']])."</td>";
					$providerBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$provider['saldo']."</span></td>";
					$providerBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$provider['pendingUnder30']."</span></td>";
					$providerBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$provider['pendingUnder45']."</span></td>";
					$providerBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$provider['pendingUnder60']."</span></td>";
					$providerBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$provider['pendingOver60']."</span></td>";
					//$providerBody.="<td class='centered'><span class='amountright'>".round($provider['historicalCredit'])."</span></td>";
					$totalCSPending+=$provider['saldo'];
					$totalCSUnder30+=$provider['pendingUnder30'];
					$totalCSUnder45+=$provider['pendingUnder45'];
					$totalCSUnder60+=$provider['pendingUnder60'];
					$totalCSOver60+=$provider['pendingOver60'];
				$providerBody.="</tr>";
			}
		}	
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder30."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder45."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSUnder60."</span></td>";
				$totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSOver60."</span></td>";
				//$totalRow.="<td class='centered'><span class='amountright'></span></td>";
			$totalRow.="</tr>";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total %</td>";	
				$totalRow.="<td class='centered'>".round(100*$totalCSPending/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder30/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder45/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSUnder60/$totalCSPending,2)." %</td>";
				$totalRow.="<td class='centered'>".round(100*$totalCSOver60/$totalCSPending,2)." %</td>";
				//$totalRow.="<td class='centered'></td>";
			$totalRow.="</tr>";
			$reportTable.=$totalRow.$providerBody.$totalRow;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
	echo $reportTable;
	
	$_SESSION['proveedoresPorPagar'] = $reportTable;
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