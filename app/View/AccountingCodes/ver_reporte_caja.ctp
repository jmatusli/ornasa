<div class="cashReceipts index fullwidth">
<?php
	if ($cashboxAccountingCodeId>0){
		echo "<h2>".__('Ingresos en Caja:')." ".$cashbox['AccountingCode']['fullname']."</h2>";
	}
	else {
		echo "<h2>".__('Ingresos en Todas Cajas')."</h2>";
	}
	
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-md-6'>";
        echo $this->Form->create('Report');
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
           echo $this->Form->input('Report.cashbox_accounting_code_id',array('label'=>__('Caja'),'default'=>ACCOUNTING_CODE_CASHBOX_MAIN,'empty'=>array('0'=>'Seleccione Caja'),'options'=>$cashboxAccountingCodes));
        echo "</fieldset>";
        
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
       
        echo $this->Form->end(__('Refresh')); 
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteCaja'], ['class' => 'btn btn-primary']); 
      echo "</div>";
      echo "<div class='col-md-3'>";
      echo "</div>";
      echo "<div class='col-md-3'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
        
        if ($bool_cash_receipt_add_permission){
          echo "<li>".$this->Html->link('Nuevo Recibo de Caja (Factura de Crédito)',array('controller'=>'cash_receipts','action' => 'add',CASH_RECEIPT_TYPE_CREDIT))."</li>";
          echo "<br/>";
          echo "<li>".$this->Html->link(__('Nuevo Recibo de Caja (Otros Ingresos)'),array('controller'=>'cash_receipts','action' => 'add',CASH_RECEIPT_TYPE_OTHER))."</li>";
          echo "<br/>";
        }
        if ($bool_deposit_add_permission){
          echo "<li>".$this->Html->link(__('Nuevo Depósito'),array('controller'=>'transfers','action' => 'crearDeposito'))."</li>";
          echo "<br/>";
        }
        if ($bool_client_index_permission){
        echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'indexClients'))."</li>";
        }
        if ($bool_client_add_permission) { 
          echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'addClient'))."</li>";
        } 
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";      
	
	$reporteIngresosCajaHeader="";
	
	$reporteIngresosCajaHeader.="<thead>";
		$reporteIngresosCajaHeader.="<tr>";
			$reporteIngresosCajaHeader.="<th>Fecha Recibo</th>";
			$reporteIngresosCajaHeader.="<th>Cliente/Recibido de</th>";
			$reporteIngresosCajaHeader.="<th>Número Factura</th>";
			$reporteIngresosCajaHeader.="<th>Número Recibo</th>";
			$reporteIngresosCajaHeader.="<th>Número Transferencia</th>";
			$reporteIngresosCajaHeader.="<th>Concepto</th>";
			$reporteIngresosCajaHeader.="<th>Monto Ingreso(C$)</th>";
			$reporteIngresosCajaHeader.="<th>Monto Depósitos(C$)</th>";
			$reporteIngresosCajaHeader.="<th>Saldo C$</th>";
		$reporteIngresosCajaHeader.="</tr>";
	$reporteIngresosCajaHeader.="</thead>";
	
	$reporteIngresosCajaExcelHeader="";
	
	$startDate=new DateTime($startDate);
	$endDate=new DateTime($endDate);
	$reporteIngresosCajaExcelHeader.="<thead>";
		$reporteIngresosCajaExcelHeader.="<tr><th colspan='6' align='center'>REPORTE INGRESOS DE CAJA</th></tr>";
		$reporteIngresosCajaExcelHeader.="<tr><th colspan='6' align='center'>PERÍODO DE ".$startDate->format('d-m-Y')." A ".$endDate->format('d-m-Y')."</th></tr>";
		if ($cashboxAccountingCodeId>0){
			$reporteIngresosCajaExcelHeader.="<tr><th colspan='6' align='center'>CAJA ".$cashbox['AccountingCode']['fullname']."</th></tr>";
		}
		$reporteIngresosCajaExcelHeader.="<tr>";
		
			$reporteIngresosCajaExcelHeader.="<th>Fecha Recibo</th>";
			$reporteIngresosCajaExcelHeader.="<th>Cliente/Recibido de</th>";
			$reporteIngresosCajaExcelHeader.="<th>Número Factura</th>";
			$reporteIngresosCajaExcelHeader.="<th>Número Recibo</th>";
			$reporteIngresosCajaExcelHeader.="<th>Número Transferencia</th>";
			$reporteIngresosCajaExcelHeader.="<th>Concepto</th>";
			$reporteIngresosCajaExcelHeader.="<th>Monto Ingresos(C$)</th>";
			$reporteIngresosCajaExcelHeader.="<th>Monto Depósitos(C$)</th>";
			$reporteIngresosCajaExcelHeader.="<th>Saldo C$</th>";
		$reporteIngresosCajaExcelHeader.="</tr>";
	$reporteIngresosCajaExcelHeader.="</thead>";
	
	
	$reporteIngresosCajaBody="";
	$reporteIngresosCajaBody.="<tbody>";
	if ($cashboxAccountingCodeId>0){
		$reporteIngresosCajaBody.="<tr class='totalrow'>";
			$reporteIngresosCajaBody.="<td>Saldo Inicial</td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$initialSaldo."</span></td>";
		$reporteIngresosCajaBody.="</tr>";
		$runningSaldo=$initialSaldo;
	
	
		$ingresos=0;
		$egresos=0;
		//pr($cashboxMovements);
		foreach ($cashboxMovements as $cashboxMovement) {
			//pr($cashboxMovement);
			$bool_CS=true;
			$reporteIngresosCajaBody.="<tr>";
				$registerDate=new DateTime($cashboxMovement['AccountingRegister']['register_date']);
				$reporteIngresosCajaBody.="<td>".$registerDate->format('d-m-Y')."</td>";
				if (!empty($cashboxMovement['Invoice'])){
					//pr($cashboxMovement['Invoice']);
					$reporteIngresosCajaBody.="<td>".$this->Html->link($cashboxMovement['Invoice']['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $cashboxMovement['Invoice']['Client']['id']))."</td>";
					$reporteIngresosCajaBody.="<td>".$this->Html->link($cashboxMovement['Invoice']['Invoice']['invoice_code'],['controller'=>'orders','action'=>'verVenta',$cashboxMovement['Invoice']['Invoice']['order_id']])." (Contado)</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					if ($cashboxMovement['AccountingMovement']['bool_debit']){
						$runningSaldo+=$cashboxMovement['AccountingMovement']['amount'];
						$ingresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					else {
						//$runningSaldo-=$cashboxMovement['AccountingMovement']['amount'];
						$egresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					if ($cashboxMovement['Invoice']['Invoice']['currency_id']!=CURRENCY_CS){
						$bool_CS=false;
					}
				}
				elseif (!empty($cashboxMovement['CashReceipt'])){
					//pr($cashboxMovement['CashReceipt']);
					if (!empty($cashboxMovement['CashReceipt']['Client']['id'])){
						$reporteIngresosCajaBody.="<td>".$this->Html->link($cashboxMovement['CashReceipt']['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'view', $cashboxMovement['CashReceipt']['Client']['id']))."</td>";
					}
					else {
						//pr($cashboxMovement['CashReceipt']['CashReceipt']['received_from']);
						if (!empty($cashboxMovement['CashReceipt']['CashReceipt']['received_from'])){
							$reporteIngresosCajaBody.="<td>".$cashboxMovement['CashReceipt']['CashReceipt']['received_from']."</td>";
						}
						else {
							$reporteIngresosCajaBody.="<td>-</td>";
						}
					}
					
					$receiptCode=$cashboxMovement['CashReceipt']['CashReceipt']['receipt_code'];
					
          switch ($cashboxMovement['CashReceipt']['CashReceipt']['cash_receipt_type_id']){
            case CASH_RECEIPT_TYPE_CREDIT:
              $receiptCode.=" (Crédito)";
              break;
            case CASH_RECEIPT_TYPE_REMISSION:
              $receiptCode.=" (Remisión)";
              break;
            case CASH_RECEIPT_TYPE_OTHER:
              $receiptCode.=" (Otros ingresos)";
              break;
          }
          if ($cashboxMovement['CashReceipt']['CashReceipt']['bool_annulled']){
						$receiptCode.=" (Anulado)";
					}  
          switch ($cashboxMovement['CashReceipt']['CashReceipt']['cash_receipt_type_id']){
            case CASH_RECEIPT_TYPE_CREDIT:
              $reporteIngresosCajaBody.="<td>";
              foreach ($cashboxMovement['CashReceipt']['CashReceiptInvoice'] as $cashReceiptInvoice){
                 $reporteIngresosCajaBody.=$this->Html->link($cashReceiptInvoice['Invoice']['invoice_code'],['controller'=>'orders','action'=>'verVenta',$cashReceiptInvoice['Invoice']['order_id']])." (Crédito, saldo pendiente C$ ".$cashReceiptInvoice['Invoice']['pending_saldo_cs'].")<br/>";
              }
              $reporteIngresosCajaBody.="</td>";
              $reporteIngresosCajaBody.="<td>".$this->Html->link($receiptCode,['controller'=>'cash_receipts','action'=>'view',$cashboxMovement['CashReceipt']['CashReceipt']['id']])."</td>";
              break;
            case CASH_RECEIPT_TYPE_REMISSION:
              $reporteIngresosCajaBody.="<td>-</td>";
              $reporteIngresosCajaBody.="<td>".$this->Html->link($receiptCode,['controller'=>'orders','action'=>'verRemision',$cashboxMovement['CashReceipt']['CashReceipt']['order_id']])."</td>";
              break;
            case CASH_RECEIPT_TYPE_OTHER:
              $reporteIngresosCajaBody.="<td>-</td>";
              $reporteIngresosCajaBody.="<td>".$this->Html->link($receiptCode,['controller'=>'cash_receipts','action'=>'view',$cashboxMovement['CashReceipt']['CashReceipt']['id']])."</td>";
              break;
          }
					
					$reporteIngresosCajaBody.="<td>-</td>";
					if ($cashboxMovement['AccountingMovement']['bool_debit']){
						$runningSaldo+=$cashboxMovement['AccountingMovement']['amount'];
						$ingresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					else {
						$runningSaldo-=$cashboxMovement['AccountingMovement']['amount'];
						$egresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					if ($cashboxMovement['CashReceipt']['CashReceipt']['currency_id']!=CURRENCY_CS){
						$bool_CS=false;
					}
				}
				elseif (!empty($cashboxMovement['Transfer'])){
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>".$this->Html->link($cashboxMovement['Transfer']['Transfer']['transfer_code'],array('controller'=>'transfers','action'=>'view',$cashboxMovement['Transfer']['Transfer']['id']))."</td>";
					if ($cashboxMovement['AccountingMovement']['bool_debit']){
						$runningSaldo+=$cashboxMovement['AccountingMovement']['amount'];
						$ingresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					else {
						$runningSaldo-=$cashboxMovement['AccountingMovement']['amount'];
						$egresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					if ($cashboxMovement['Transfer']['Transfer']['currency_id']!=CURRENCY_CS){
						$bool_CS=false;
					}
				}
				else {
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					$reporteIngresosCajaBody.="<td>-</td>";
					if ($cashboxMovement['AccountingMovement']['bool_debit']){
						$runningSaldo+=$cashboxMovement['AccountingMovement']['amount'];
						$ingresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					else {
						$runningSaldo-=$cashboxMovement['AccountingMovement']['amount'];
						$egresos+=$cashboxMovement['AccountingMovement']['amount'];
					}
					//pr($cashboxMovement);
				}
				$reporteIngresosCajaBody.="<td>".h($cashboxMovement['AccountingRegister']['concept'])."</td>";
				if ($cashboxMovement['AccountingMovement']['bool_debit']){
					if ($bool_CS){
						$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$cashboxMovement['AccountingMovement']['amount']."</span></td>";
					}
					else {
						$reporteIngresosCajaBody.="<td class='italic'><span class='currency'>C$ </span><span class='amountright number'>".$cashboxMovement['AccountingMovement']['amount']."</span></td>";
					}
					$reporteIngresosCajaBody.="<td class='centered'>-</td>";
					$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$runningSaldo."</span></td>";
				}
				else {
					$reporteIngresosCajaBody.="<td class='centered'>-</td>";
					if ($bool_CS){
						$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$cashboxMovement['AccountingMovement']['amount']."</span></td>";
					}
					else {
						$reporteIngresosCajaBody.="<td class='italic'><span class='currency'>C$ </span><span class='amountright number'>".$cashboxMovement['AccountingMovement']['amount']."</span></td>";
					}
					$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$runningSaldo."</span></td>";
				}
			$reporteIngresosCajaBody.="</tr>";
		}

			$reporteIngresosCajaBody.="<tr class='totalrow'>";
				$reporteIngresosCajaBody.="<td>Saldo Final</td>";
				$reporteIngresosCajaBody.="<td></td>";
				$reporteIngresosCajaBody.="<td></td>";
				$reporteIngresosCajaBody.="<td></td>";
				$reporteIngresosCajaBody.="<td></td>";
				$reporteIngresosCajaBody.="<td></td>";
				$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright  number'>".$ingresos."</span></td>";
				$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright  number'>".$egresos."</span></td>";
				$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright  number'>".$finalSaldo."</span></td>";
			$reporteIngresosCajaBody.="</tr>";
	}
	$reporteIngresosCajaBody.="</tbody>";
	
	$reporteIngresosCaja="<table cellpadding='0' cellspacing='0'>".$reporteIngresosCajaHeader.$reporteIngresosCajaBody."</table>";
  
  echo "<h3>Este reporte muestra los pagos realizados a caja.  Como tal, no aparecerán las facturas de crédito en que no se ha realizado ningún pago aun.</h3>";
  
	echo $reporteIngresosCaja;
	$excelIngresosCaja="<table id='Ingresos caja' cellpadding='0' cellspacing='0'>".$reporteIngresosCajaExcelHeader.$reporteIngresosCajaBody."</table>";
	$_SESSION['reporteCaja'] = $excelIngresosCaja;
?>
	<!--p>
	<?php	//echo $this->Paginator->counter(array(	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')	));	?>	
	</p-->
	<!--div class="paging">
	<?php
		//echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => ''));
		//echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div-->
</div>
<script>
	function formatNumbers(){
		$("td span.number").each(function(){
			$(this).number(true,2);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,4);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});

</script>