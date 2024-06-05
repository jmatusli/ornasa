<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$output="";
	$output.="<div class='cheques view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$receiptCode=$cashReceipt['CashReceipt']['receipt_code'];
	if ($cashReceipt['CashReceipt']['bool_annulled']){
		$receiptCode.=" (Anulado)";
	}
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		$output.="<div class='centered big bold'>Recibo de Caja (Factura de Crédito) # ".$receiptCode."</div>";
	}
	else if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
		$output.="<div class='centered big bold'>Recibo de Caja (Otros Ingresos) # ".$receiptCode."</div>";
	}
	
	$receiptDate=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div>A nombre de :<span class='underline'>".$cashReceipt['CashReceipt']['received_from']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$receiptDate->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Monto: C$<span class='underline'>".number_format($cashReceipt['CashReceipt']['amount'],2,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Caja: <span class='underline'>".$cashReceipt['CashboxAccountingCode']['fullname']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		
		
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Cuenta HABER: <span class='underline'>".$cashReceipt['CreditAccountingCode']['fullname']."</span></div>";
				$output.="</td>";
			$output.="</tr>";	
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Recibido de: <span class='underline'>".$cashReceipt['CashReceipt']['received_from']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Cliente: <span class='underline'>".$cashReceipt['Client']['company_name']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Concepto: <span class='underline'>".$cashReceipt['CashReceipt']['concept']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		if (!empty($cashReceipt['CashReceipt']['observation'])){
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Observación: <span class='underline'>".$cashReceipt['CashReceipt']['observation']."</span></div>";
				$output.="</td>";
			$output.="</tr>";
		}
	$output.="</table>";
	
	//$output.="<dl>";
	//	$output.="<dt>".__('Receipt Date')."</dt>";
	//	$output.="<dd>".$receiptDate->format('d-m-Y')."</dd>";
	//	$output.="<dt>".__('Receipt Code')."</dt>";
	//	$output.="<dd>".$receiptCode."</dd>";
	//	$output.="<dt>".__('Amount')."</dt>";
	//	$output.="<dd>".$cashReceipt['Currency']['abbreviation']." ".$cashReceipt['CashReceipt']['amount']."</dd>";
	//	$output.="<dt>".__('Cashbox Accounting Code')."</dt>";
	//	$output.="<dd>".$this->Html->link($cashReceipt['CashboxAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $cashReceipt['CashboxAccountingCode']['id']))."</dd>";
	//	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
	//		$output.="<dt>".__('Credit Accounting Code')."</dt>";
	//		$output.="<dd>".$this->Html->link($cashReceipt['CreditAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $cashReceipt['CreditAccountingCode']['id']))."</dd>";
	//	}
	//	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
	//		$output.="<dt>".__('Client')."</dt>";
	//		$output.="<dd>".$this->Html->link($cashReceipt['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $cashReceipt['Client']['id']))."</dd>";
	//	}
	//	$output.="<dt>".__('Concept')."</dt>";
	//	$output.="<dd>".h($cashReceipt['CashReceipt']['concept'])."</dd>";
	//	if (!empty($cashReceipt['CashReceipt']['observation'])){
	//		$output.="<dt>".__('Observation')."</dt>";
	//		$output.="<dd>".$cashReceipt['CashReceipt']['observation']."</dd>";
	//	}
	//$output.="</dl>";
	$output.="<div class='related'>";
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		$output.="<h3>Pagos de Factura en este Recibo de Caja</h3>";
		//pr($cashReceipt);
		$output.="<table>";
			$output.="<thead>";
				$output.="<tr>";
					$output.="<th>Factura</th>";
					$output.="<th>Monto Pagado en Efectivo</th>";
					$output.="<th>Monto Pagado en Retención</th>";
					$output.="<th>Monto Total Abonado</th>";
				$output.="</tr>";
			$output.="</thead>";
			$output.="<tbody>";
			$totalPayment=0;
			$totalPaymentRetention=0;
			$totalPaymentCredit=0;
			foreach ($invoicesForCashReceipt as $invoiceForCashReceipt){
				$output.="<tr>";
					$totalPayment+=$invoiceForCashReceipt['CashReceiptInvoice']['payment'];
					$totalPaymentRetention+=$invoiceForCashReceipt['CashReceiptInvoice']['payment_retention'];
					$totalPaymentCredit+=$invoiceForCashReceipt['CashReceiptInvoice']['payment_credit_CS'];
					$output.="<td>".$this->Html->Link($invoiceForCashReceipt['Invoice']['invoice_code'],array('controller'=>'cash_receipts','action'=>'view',$invoiceForCashReceipt['CashReceiptInvoice']['invoice_id']))."</td>";
					$output.="<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment'],2,".",",")."</span></td>";
					$output.="<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment_retention'],2,".",",")."</span></td>";
					$output.="<td><span class='currency'>C$</span><span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment_credit_CS'],2,".",",")."</span></td>";
				$output.="</tr>";
			}
			$output.="<tr class='totalrow'>";
				$output.="<td>Total</td>";
				$output.="<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($totalPayment,2,".",",")."</span></td>";
				$output.="<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($totalPaymentRetention,2,".",",")."</span></td>";
				$output.="<td><span class='currency'>C$</span><span class='amountright'>".number_format($totalPaymentCredit,2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="</tbody>";
		$output.="</table>";
	}

	$output.="</div>"; 

	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	