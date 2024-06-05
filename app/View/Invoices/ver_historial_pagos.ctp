<div class="invoices index fullwidth">
<?php 
	echo "<h2>".__('Reporte Historial de Pagos')."</h2>";
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo $this->Form->input('Report.client_id',array('label'=>__('Client'),'default'=>$client_id,'empty'=>array('0'=>'Seleccione Cliente'),'options'=>$clients));
	echo $this->Form->end(__('Refresh')); 
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarHistorialPagos'), array( 'class' => 'btn btn-primary')); 
	
	$output="";
	foreach ($selectedClients as $client){
		//pr($client);
		if (!empty($client['invoices'])){
			$reportTable="";
			$table_id=substr($client['ThirdParty']['company_name'],0,30);
			$reportTable.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
				$reportTable.="<thead>";
					$reportTable.="<tr>";
						$reportTable.="<th>Fecha</th>";
						$reportTable.="<th>Factura</th>";
						$reportTable.="<th>Recibo de Caja</th>";
						$reportTable.="<th>Contado/Crédito</th>";
						$reportTable.="<th>Subtotal</th>";
						$reportTable.="<th>IVA</th>";
						$reportTable.="<th>Total</th>";
						$reportTable.="<th>Abono</th>";
						$reportTable.="<th>Estado</th>";
						$reportTable.="<th>Pendiente</th>";
					$reportTable.="</tr>";
				$reportTable.="</thead>";
				$reportTable.="<tbody>";
				$invoiceBody="";
				$invoiceTotalCS=0;
				$invoiceTotalUSD=0;
				$paymentTotalCS=0;
				$paymentTotalUSD=0;
				$pendingTotalCS=0;
				foreach ($client['invoices'] as $invoice){
					if ($invoice['Currency']['id']==CURRENCY_USD){
						$invoiceTotalUSD+=$invoice['Invoice']['total_price'];
					}
					else {
						$invoiceTotalCS+=$invoice['Invoice']['total_price'];
					}
					$pendingTotalCS+=$invoice['Invoice']['pendingCS'];
					//pr($invoice);
					$invoiceDate=new DateTime($invoice['Invoice']['invoice_date']);
					$currencyClass="CScurrency";
					if ($invoice['Currency']['id']==CURRENCY_USD){
						$currencyClass="USDcurrency";
					}
					$invoiceBody.="<tr>";
						$invoiceBody.="<td>".$invoiceDate->format('d-m-Y')."</td>"; 
						$invoiceBody.="<td>".$this->Html->link($invoice['Invoice']['invoice_code']." (".$invoice['Currency']['abbreviation'].")", array('controller' => 'orders', 'action' => 'verVenta', $invoice['Invoice']['order_id']))."</td>";
						$invoiceBody.="<td></td>";
						$invoiceBody.="<td>".($invoice['Invoice']['bool_credit']?"Crédito":"Contado")."</td>";
						$invoiceBody.="<td class='amount ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['sub_total_price']."</span></td>";
						$invoiceBody.="<td class='amount ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['IVA_price']."</span></td>";
						$invoiceBody.="<td class='amount ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['total_price']."</span></td>";
						$invoiceBody.="<td></td>";
						$invoiceBody.="<td>".($invoice['Invoice']['bool_paid']?"Pagado":"Pendiente")."</td>";
						$invoiceBody.="<td class='amount CScurrency'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
						
					$invoiceBody.="</tr>";
					
					if (!empty($invoice['cashreceiptpayments'])){
						foreach ($invoice['cashreceiptpayments'] as $payment){
							if ($payment['Currency']['id']==CURRENCY_USD){
								$paymentTotalUSD+=($payment['CashReceiptInvoice']['payment']+$payment['CashReceiptInvoice']['payment_retention']);
							}
							else {
								$paymentTotalCS+=($payment['CashReceiptInvoice']['payment']+$payment['CashReceiptInvoice']['payment_retention']);
							}
							$currencyClass="CScurrency";
							if ($payment['Currency']['id']==CURRENCY_USD){
								$currencyClass="USDcurrency";
							}
							$invoiceBody.="<tr>";
								$receiptDate=new DateTime($payment['CashReceipt']['receipt_date']);
								$invoiceBody.="<td>".$receiptDate->format('d-m-Y')."</td>"; 
								$invoiceBody.="<td></td>";
								$invoiceBody.="<td>".$this->Html->link($payment['CashReceipt']['receipt_code']." (".$payment['Currency']['abbreviation'].")", array('controller' => 'cash_receipts', 'action' => 'view', $payment['CashReceipt']['id']))."</td>";
								$invoiceBody.="<td></td>";
								$invoiceBody.="<td></td>";
								$invoiceBody.="<td></td>";
								$invoiceBody.="<td></td>";
								// IF DISCOUNT AND INCREMENT NEED TO BE REFLECTED, THEY WOULD HAVE TO BE ADDED TO THE AMOUNT
								$invoiceBody.="<td class='amount ".$currencyClass."'><span class='currency'></span><span class='amountright'>".($payment['CashReceiptInvoice']['payment']+$payment['CashReceiptInvoice']['payment_retention'])."</span></td>";	
								$invoiceBody.="<td></td>";
								$invoiceBody.="<td></td>";
							$invoiceBody.="</tr>";
						}
					}
				}	
					$totalRowInvoices="";
					$totalRowInvoices.="<tr class='totalrow'>";
						$totalRowInvoices.="<td>Total Facturas C$</td>";	
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td class='amount CScurrency'><span class='currency'></span><span class='amountright'>".$invoiceTotalCS."</span></td>";
						$totalRowInvoices.="<td class='amount CScurrency'><span class='currency'></span><span class='amountright'>".$paymentTotalCS."</span></td>";
						$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="<td class='amount CScurrency'><span class='currency'></span><span class='amountright'>".$pendingTotalCS."</span></td>";
					$totalRowInvoices.="</tr>";
					if ($invoiceTotalUSD>0||$paymentTotalUSD>0){
						$totalRowInvoices.="<tr class='totalrow'>";
							$totalRowInvoices.="<td>Total Facturas US$</td>";	
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td class='amount USDcurrency'><span class='currency'></span><span class='amountright'>".$invoiceTotalUSD."</span></td>";
							$totalRowInvoices.="<td class='amount USDcurrency'><span class='currency'></span><span class='amountright'>".$paymentTotalUSD."</span></td>";
							$totalRowInvoices.="<td></td>";
							$totalRowInvoices.="<td></td>";
						$totalRowInvoices.="</tr>";
					}
					$reportTable.=$totalRowInvoices.$invoiceBody;
				$reportTable.="</tbody>";
			$reportTable.="</table>";

			echo "<h2>".$client['ThirdParty']['company_name']."</h2>";
			echo $reportTable;
			$output.=$reportTable;
		}
	}
	
	
	$_SESSION['historialPagos'] = $output;
?>
</div>
<script>
	
	function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			$(this).number(true,2);
		});
	};
	
	function formatCSCurrencies(){
		$("td.amount.CScurrency").each(function(){
			$(this).find('span.amountright').number(true,2);
			$(this).find('span.currency').text("C$ ");
		});
	};
	
	function formatUSDCurrencies(){
		$("td.amount.USDcurrency").each(function(){
			$(this).find('span.amountright').number(true,2);
			$(this).find('span.currency').text("US$ ");
		});
	};
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>