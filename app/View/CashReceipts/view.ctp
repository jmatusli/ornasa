<div class="cashReceipts view">
<?php 
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		echo "<h2>Recibo de Caja (Factura de Crédito)</h2>";
	}
	else if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
		echo "<h2>Recibo de Caja (Otros Ingresos)</h2>";
	}
	echo "<dl>";
		$receiptDateTime=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
		$receiptCode=$cashReceipt['CashReceipt']['receipt_code'];
		if ($cashReceipt['CashReceipt']['bool_annulled']){
			$receiptCode.=" (Anulado)";
		}
    echo '<dt>Planta</dt>';
		echo '<dd>'.$cashReceipt['Plant']['name'].'</dd>';
		echo "<dt>".__('Receipt Date')."</dt>";
		echo "<dd>".$receiptDateTime->format('d-m-Y')."</dd>";
		echo "<dt>".__('Receipt Code')."</dt>";
		echo "<dd>".$receiptCode."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".$cashReceipt['Currency']['abbreviation']." ".$cashReceipt['CashReceipt']['amount']."</dd>";
		echo "<dt>".__('Cashbox Accounting Code')."</dt>";
		if (!empty($cashReceipt['CashboxAccountingCode']['AccountingRegister']['id'])){
			echo "<dd>".$this->Html->link($cashReceipt['CashboxAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $cashReceipt['CashboxAccountingCode']['id']))."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		echo "<dt>".__('Comprobante')."</dt>";
		if (!empty($cashReceipt['AccountingRegisterCashReceipt'][0]['AccountingRegister']['id'])){
			echo "<dd>".$this->Html->link($cashReceipt['AccountingRegisterCashReceipt'][0]['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $cashReceipt['AccountingRegisterCashReceipt'][0]['AccountingRegister']['id']))."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_OTHER){
			echo "<dt>".__('Credit Accounting Code')."</dt>";
			if (!empty($cashReceipt['CreditAccountingCode']['id'])){
				echo "<dd>".$this->Html->link($cashReceipt['CreditAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $cashReceipt['CreditAccountingCode']['id']))."</dd>";
			}
			else {
				echo "<dd>-</dd>";
			}
			echo "<dt>".__('Recibido de ')."</dt>";
			if (!empty($cashReceipt['CashReceipt']['received_from'])){
				echo "<dd>".$cashReceipt['CashReceipt']['received_from']."</dd>";
			}
			else {
				echo "<dd>-</dd>";
			}
		}
		if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
			echo "<dt>".__('Client')."</dt>";
			echo "<dd>".$this->Html->link($cashReceipt['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $cashReceipt['Client']['id']))."</dd>";
		}
		
		echo "<dt>".__('Concept')."</dt>";
		echo "<dd>".h($cashReceipt['CashReceipt']['concept'])."</dd>";
		if (!empty($cashReceipt['CashReceipt']['observation'])){
			echo "<dt>".__('Observation')."</dt>";
			echo "<dd>".$cashReceipt['CashReceipt']['observation']."</dd>";
		}
		/*
		echo "<dt>".__('Bool Cash')."</dt>";
		echo "<dd>".h($cashReceipt['CashReceipt']['bool_cash'])."</dd>";
		echo "<dt>".__('Cheque Number')."</dt>";
		echo "<dd>".h($cashReceipt['CashReceipt']['cheque_number'])."</dd>";
		echo "<dt>".__('Cheque Bank')."</dt>";
		echo "<dd>".h($cashReceipt['CashReceipt']['cheque_bank'])."</dd>";
		*/
	echo "</dl>";
	echo "<div class='related'>";
	if ($cashReceipt['CashReceiptType']['id']==CASH_RECEIPT_TYPE_CREDIT){
		echo "<h3>Pagos de Factura en este Recibo de Caja</h3>";
		//pr($cashReceipt);
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>Factura</th>";
					echo "<th>Monto Pagado en Efectivo</th>";
					echo "<th>Monto Pagado en Retención</th>";
					echo "<th>Monto Total Abonado</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$totalPayment=0;
			$totalPaymentRetention=0;
			$totalPaymentCredit=0;
			foreach ($invoicesForCashReceipt as $invoiceForCashReceipt){
				echo "<tr>";
					$totalPayment+=$invoiceForCashReceipt['CashReceiptInvoice']['payment'];
					$totalPaymentRetention+=$invoiceForCashReceipt['CashReceiptInvoice']['payment_retention'];
					$totalPaymentCredit+=$invoiceForCashReceipt['CashReceiptInvoice']['payment_credit_CS'];
					echo "<td>".$this->Html->Link($invoiceForCashReceipt['Invoice']['invoice_code'],array('controller'=>'orders','action'=>'verVenta',$invoiceForCashReceipt['Invoice']['order_id']))."</td>";
					echo "<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment'],2,".",",")."</span></td>";
					echo "<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment_retention'],2,".",",")."</span></td>";
					echo "<td><span class='currency'>C$</span><span class='amountright'>".number_format($invoiceForCashReceipt['CashReceiptInvoice']['payment_credit_CS'],2,".",",")."</span></td>";
				echo "</tr>";
			}
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($totalPayment,2,".",",")."</span></td>";
				echo "<td>".$invoiceForCashReceipt['Currency']['abbreviation']." <span class='amountright'>".number_format($totalPaymentRetention,2,".",",")."</span></td>";
				echo "<td><span class='currency'>C$</span><span class='amountright'>".number_format($totalPaymentCredit,2,".",",")."</span></td>";
			echo "</tr>";
			echo "</tbody>";
		echo "</table>";
	}
	echo "</div>";
?>
</div>
<div class='actions'>
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<?php
		$receiptCode=str_replace(' ','',$cashReceipt['CashReceipt']['receipt_code']);
		$receiptCode=str_replace('/','',$receiptCode);
		$filename='Recibo_de_Caja_'.$receiptCode;
		echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'viewPdf','ext'=>'pdf',$cashReceipt['CashReceipt']['id'],$filename))."</li>";
		echo "<br/>";
		if ($bool_edit_permission) { 
			echo "<li>".$this->Html->link(__('Edit Cash Receipt'), array('action' => 'edit', $cashReceipt['CashReceipt']['id']))."</li>"; 
		}
		if ($bool_delete_permission) { 
			echo "<li>".$this->Form->postLink(__('Delete Cash Receipt'), array('action' => 'delete', $cashReceipt['CashReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $cashReceipt['CashReceipt']['receipt_code']))."</li>";
		} 
		echo "<li>".$this->Html->link(__('List Cash Receipts'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_add_permission) { 
			echo "<li>".$this->Html->link('Nuevo Recibo de Caja (Factura de Crédito)',array('action' => 'add',CASH_RECEIPT_TYPE_CREDIT))."</li>";
			echo "<br/>";
			echo "<li>".$this->Html->link(__('Nuevo Recibo de Caja (Otros Ingresos)'),array('action' => 'add',CASH_RECEIPT_TYPE_OTHER))."</li>";
			echo "<br/>";
		}
		if ($bool_client_index_permission) { 
			echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'indexClients'))."</li>";
		}
		if ($bool_client_add_permission) { 
			echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'addClient'))."</li>";
		} 
	?>
	</ul>
</div>
