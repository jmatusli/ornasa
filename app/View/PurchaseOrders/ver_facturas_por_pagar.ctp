<div class="purchaseorders index fullwidth">
<?php 
	echo "<h2>".__('Reporte de Facturas por Pagar para Proveedor ').$provider['ThirdParty']['company_name']."</h2>";
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarFacturasPorPagar',$provider['ThirdParty']['company_name']],['class' => 'btn btn-primary']); 
	$reportTable="";
	$table_id=substr("facturas_por_pagar_".$provider['ThirdParty']['company_name'],0,31);
	$reportTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th class='actions'></th>";
				$reportTable.="<th>Fecha de Emisión</th>";
				$reportTable.="<th>Orden de compra</th>";
				//$reportTable.="<th class='centered'>Costo Total</th>";
				//$reportTable.="<th class='centered'>Abonado</th>";
				$reportTable.="<th class='centered'>Saldo Pendiente</th>";
				//$reportTable.="<th class='centered'>Diferencia Cambiaria</th>";
				//$reportTable.="<th class='centered'>A pagar</th>";
				$reportTable.="<th>Fecha de Vencimiento</th>";
				$reportTable.="<th>Días de Crédito</th>";
				//$reportTable.="<th class='centered'>1-30</th>";
				//$reportTable.="<th class='centered'>31-60</th>";
				//$reportTable.="<th class='centered'>>60</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		$reportTable.="<tbody>";
		$facturasPorPagarBody="";
		$totalCSPurchaseOrder=0;
		$totalCSPaid=0;
		$totalCSPending=0;
		$totalCSUnder30=0;
		$totalCSUnder60=0;
		$totalCSOver60=0;
		foreach ($pendingPurchaseOrders as $purchaseOrder){
			$purchaseOrderDate=new DateTime($purchaseOrder['PurchaseOrder']['purchase_order_date']);
			$dueDate=new DateTime($purchaseOrder['PurchaseOrder']['due_date']);
			$currentDate= new DateTime(date('Y-m-d'));
			$daysLate=$currentDate->diff($purchaseOrderDate);
			//pr($daysLate);
			$currencyClass="CScurrency";
			if ($purchaseOrder['Currency']['id']==CURRENCY_USD){
				$currencyClass="USDcurrency";
			}
			$facturasPorPagarBody.="<tr>";
				$facturasPorPagarBody.="<td class='actions'>".$this->Html->link('Cancelar Factura', ['controller'=>'cash_receipts','action' => 'add', CASH_RECEIPT_TYPE_CREDIT])."</td>";
				$facturasPorPagarBody.="<td>".$purchaseOrderDate->format('d-m-Y')."</td>";
				$facturasPorPagarBody.="<td>".$this->Html->link($purchaseOrder['PurchaseOrder']['purchase_order_code'], ['controller' => 'purchaseOrders', 'action' => 'ver', $purchaseOrder['PurchaseOrder']['id']])."</td>";
				//$facturasPorPagarBody.="<td class='centered ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$purchaseOrder['PurchaseOrder']['cost_total']."</span></td>";
				//$facturasPorPagarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$purchaseOrder['PurchaseOrder']['paidCS']."</span></td>";
				$facturasPorPagarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$purchaseOrder['PurchaseOrder']['cost_total']."</span></td>";
				if ($purchaseOrder['Currency']['id']==CURRENCY_CS){
						$totalCSPurchaseOrder+=$purchaseOrder['PurchaseOrder']['cost_total'];
				}
				elseif ($purchaseOrder['Currency']['id']==CURRENCY_USD) {
					$totalCSPurchaseOrder+=$purchaseOrder['PurchaseOrder']['cost_total']*$exchangeRateCurrent;
				}
				//$totalCSPaid+=$purchaseOrder['PurchaseOrder']['paidCS'];
				$totalCSPending+=$purchaseOrder['PurchaseOrder']['pendingCS'];
				//$facturasPorPagarBody.="<td class='centered number'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'></span></td>";
				//$facturasPorPagarBody.="<td class='centered number'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'></span></td>";
				$facturasPorPagarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
				/*
				if ($daysLate->format('%d')<31){
					$facturasPorPagarBody.="<td class='centered number'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$purchaseOrder['PurchaseOrder']['pendingCS']."</span></td>";
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$totalCSUnder30+=$purchaseOrder['PurchaseOrder']['pendingCS'];
				}
				else if ($daysLate->format('%d')<61){
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$facturasPorPagarBody.="<td class='centered number'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$purchaseOrder['PurchaseOrder']['pendingCS']."</span></td>";
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$totalCSUnder60+=$purchaseOrder['PurchaseOrder']['pendingCS'];
				}
				else{
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$facturasPorPagarBody.="<td class='centered number'>-</td>";
					$facturasPorPagarBody.="<td class='centered number'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$purchaseOrder['PurchaseOrder']['pendingCS']."</span></td>";
					$totalCSOver60+=$purchaseOrder['PurchaseOrder']['pendingCS'];
				}
				*/
				$facturasPorPagarBody.="<td>".$daysLate->format('%a')."</td>";
			$facturasPorPagarBody.="</tr>";
		}	
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				//$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPurchaseOrder."</span></td>";
				//$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPaid."</span></td>";
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				//$totalRow.="<td class='centered number CScurrency'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder30."</span></td>";
				//$totalRow.="<td class='centered number CScurrency'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder60."</span></td>";
				//$totalRow.="<td class='centered number CScurrency'>".$purchaseOrder['Currency']['abbreviation']."<span class='amountright'>".$totalCSOver60."</span></td>";
			$totalRow.="</tr>";
			$reportTable.=$totalRow.$facturasPorPagarBody.$totalRow;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
	echo $reportTable;
	
	
	$excelTable="";
	$table_id=substr("facturas_por_pagar_".$provider['ThirdParty']['company_name'],0,31);
	$excelTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
		$excelTable.="<thead>";
			$excelTable.="<tr>";
				$excelTable.="<th>Fecha de Emisión</th>";
				$excelTable.="<th>Factura/Orden</th>";
				//$excelTable.="<th></th>";
				//$excelTable.="<th class='centered'>Precio Total</th>";
				//$excelTable.="<th></th>";
				//$excelTable.="<th class='centered'>Abonado</th>";
				$excelTable.="<th></th>";
				$excelTable.="<th class='centered'>Saldo Pendiente</th>";
				$excelTable.="<th>Fecha de Vencimiento</th>";
				$excelTable.="<th>Días de Crédito</th>";
			$excelTable.="</tr>";
		$excelTable.="</thead>";
		$excelTable.="<tbody>";
		$facturasPorPagarBody="";
		$totalCSPurchaseOrder=0;
		$totalCSPaid=0;
		$totalCSPending=0;
		$totalCSUnder30=0;
		$totalCSUnder60=0;
		$totalCSOver60=0;
		foreach ($pendingPurchaseOrders as $purchaseOrder){
			$purchaseOrderDate=new DateTime($purchaseOrder['PurchaseOrder']['purchase_order_date']);
			$dueDate=new DateTime($purchaseOrder['PurchaseOrder']['due_date']);
			$currentDate= new DateTime(date('Y-m-d'));
			$daysLate=$currentDate->diff($purchaseOrderDate);
			//pr($daysLate);
			$facturasPorPagarBody.="<tr>";
				$facturasPorPagarBody.="<td>".$purchaseOrderDate->format('d-m-Y')."</td>";
				$facturasPorPagarBody.="<td>".$purchaseOrder['PurchaseOrder']['purchase_order_code']."</td>";
				//$facturasPorPagarBody.="<td>".$purchaseOrder['Currency']['abbreviation']."</td>";
				//$facturasPorPagarBody.="<td class='centered'><span class='amountright'>".$purchaseOrder['PurchaseOrder']['cost_total']."</span></td>";
				//$facturasPorPagarBody.="<td>C$</td>";
				//$facturasPorPagarBody.="<td class='centered'><span class='amountright'>".$purchaseOrder['PurchaseOrder']['paidCS']."</span></td>";
				$facturasPorPagarBody.="<td>C$</td>";
				$facturasPorPagarBody.="<td class='centered'><span class='amountright'>".$purchaseOrder['PurchaseOrder']['pendingCS']."</span></td>";
				if ($purchaseOrder['Currency']['id']==CURRENCY_CS){
						$totalCSPurchaseOrder+=$purchaseOrder['PurchaseOrder']['cost_total'];
				}
				elseif ($purchaseOrder['Currency']['id']==CURRENCY_USD) {
					$totalCSPurchaseOrder+=$purchaseOrder['PurchaseOrder']['cost_total']*$exchangeRateCurrent;
				}
				//$totalCSPaid+=$purchaseOrder['PurchaseOrder']['paidCS'];
				$totalCSPending+=$purchaseOrder['PurchaseOrder']['pendingCS'];
				
				$facturasPorPagarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
				$facturasPorPagarBody.="<td>".$daysLate->days."</td>";
			$facturasPorPagarBody.="</tr>";
		}	
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				$totalRow.="<td></td>";	
				//$totalRow.="<td>C$</td>";	
				//$totalRow.="<td><span class='amountright'>".$totalCSPurchaseOrder."</span></td>";
				//$totalRow.="<td>C$</td>";	
				//$totalRow.="<td><span class='amountright'>".$totalCSPaid."</span></td>";
				$totalRow.="<td>C$</td>";	
				$totalRow.="<td><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td class='centered'></td>";	
				$totalRow.="<td class='centered'></td>";	
			$totalRow.="</tr>";
			$excelTable.=$totalRow.$facturasPorPagarBody.$totalRow;
		$excelTable.="</tbody>";
	$excelTable.="</table>";
	
	$_SESSION['facturasPorPagar'] = $excelTable;
?>
</div>
<!--div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Invoice'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div-->
<script>
	
	function formatCurrencies(){
		$("td.number span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				var boolnegative=true;
				//$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("-");
			}
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("C$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("C$");
			}
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("US$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("US$");
			}
		});
	};
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>