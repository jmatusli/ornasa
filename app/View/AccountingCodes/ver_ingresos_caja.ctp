<div class="cashReceipts index">
<?php
	if ($cashboxAccountingCodeId>0){
		echo "<h2>".__('Ingresos en Caja:')." ".$cashbox['AccountingCode']['fullname']."</h2>";
	}
	else {
		echo "<h2>".__('Ingresos en Todas Cajas')."</h2>";
	}
	
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->input('Report.cashbox_accounting_code_id',array('label'=>__('Caja'),'default'=>'0','empty'=>array('0'=>'Seleccione Caja'),'options'=>$cashboxAccountingCodes));
	echo $this->Form->end(__('Refresh')); 
	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteIngresosCaja'), array( 'class' => 'btn btn-primary')); 
	
	$reporteIngresosCajaHeader="";
	
	$reporteIngresosCajaHeader.="<thead>";
		$reporteIngresosCajaHeader.="<tr>";
		/*
			$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('receipt_date')."</th>";
			$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('concept')."</th>";
			$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('receipt_code')."</th>";
			$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('Client.company_name','Cliente')."</th>";
			$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('amount','Monto')."</th>";
			if ($cashboxAccountingCodeId>0){
				$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('saldo_CS','Saldo C$')."</th>";
			}
			//$reporteIngresosCajaHeader.="<th>".$this->Paginator->sort('saldo_USD','Saldo US$')."</th>";
		*/
			$reporteIngresosCajaHeader.="<th>Fecha Recibo</th>";
			if ($cashboxAccountingCodeId==0){
				$reporteIngresosCajaHeader.="<th>Caja</th>";
			}
			$reporteIngresosCajaHeader.="<th>Concepto</th>";
			$reporteIngresosCajaHeader.="<th>Número Recibo</th>";
			$reporteIngresosCajaHeader.="<th>Cliente</th>";
			$reporteIngresosCajaHeader.="<th>Monto (C$)</th>";
			if ($cashboxAccountingCodeId>0){
				$reporteIngresosCajaHeader.="<th>Saldo C$</th>";
			}
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
		/*
			$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('receipt_date')."</th>";
			$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('concept')."</th>";
			$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('receipt_code')."</th>";
			$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('Client.company_name','Cliente')."</th>";
			$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('amount','Monto')."</th>";
			if ($cashboxAccountingCodeId>0){
				$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('saldo_CS','Saldo C$')."</th>";
			}
			//$reporteIngresosCajaExcelHeader.="<th>".$this->Paginator->sort('saldo_USD','Saldo US$')."</th>";
		*/
			$reporteIngresosCajaExcelHeader.="<th>Fecha Recibo</th>";
			if ($cashboxAccountingCodeId==0){
				$reporteIngresosCajaExcelHeader.="<th>Caja</th>";
			}
			$reporteIngresosCajaExcelHeader.="<th>Concepto</th>";
			$reporteIngresosCajaExcelHeader.="<th>Número Recibo</th>";
			$reporteIngresosCajaExcelHeader.="<th>Cliente</th>";
			$reporteIngresosCajaExcelHeader.="<th>Monto (C$)</th>";
			if ($cashboxAccountingCodeId>0){
				$reporteIngresosCajaExcelHeader.="<th>Saldo C$</th>";
			}
		$reporteIngresosCajaExcelHeader.="</tr>";
	$reporteIngresosCajaExcelHeader.="</thead>";
	
	$runningSaldo=0;
	$reporteIngresosCajaBody="";
	$reporteIngresosCajaBody.="<tbody>";
	if ($cashboxAccountingCodeId>0){
		$reporteIngresosCajaBody.="<tr class='totalrow'>";
			$reporteIngresosCajaBody.="<td>Saldo Inicial</td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$initialSaldo."</span></td>";
		$reporteIngresosCajaBody.="</tr>";
		$runningSaldo=$initialSaldo;
	}
	
	foreach ($cashReceipts as $cashReceipt) {
		//pr($cashReceipt);
		$reporteIngresosCajaBody.="<tr>";
			$receiptDate=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
			
			$receiptCode=$cashReceipt['CashReceipt']['receipt_code'];
			if ($cashReceipt['CashReceipt']['bool_annulled']){
				$receiptCode.=" (Anulado)";
			}
			
			$reporteIngresosCajaBody.="<td>".$receiptDate->format('d-m-Y')."</td>";
			if ($cashboxAccountingCodeId==0){
				$reporteIngresosCajaBody.="<td>".h($cashReceipt['CashboxAccountingCode']['fullname'])."</td>";
			}
			$reporteIngresosCajaBody.="<td>".h($cashReceipt['CashReceipt']['concept'])."</td>";
			if (!empty($cashReceipt['CashReceipt']['order_id'])){
				if ($cashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_REMISSION){
					$reporteIngresosCajaBody.="<td>".$this->Html->link($receiptCode,array('controller'=>'orders','action'=>'viewRemission',$cashReceipt['CashReceipt']['order_id']))."</td>";
				}
			}
			else {
				$reporteIngresosCajaBody.="<td>".$receiptCode."</td>";
			}
			$reporteIngresosCajaBody.="<td>".$this->Html->link($cashReceipt['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'view', $cashReceipt['Client']['id']))."</td>";
			/*
			$reporteIngresosCajaBody.="<td><span class='currency'>".$cashReceipt['Currency']['abbreviation']."</span><span class='amountright number'>".$cashReceipt['CashReceipt']['amount']."</span></td>";
			*/
			$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$cashReceipt['CashReceipt']['amountCS']."</span></td>";
			if ($cashboxAccountingCodeId>0){
				$runningSaldo+=$cashReceipt['CashReceipt']['amountCS'];
				$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright number'>".$runningSaldo."</span></td>";
			}
			
		$reporteIngresosCajaBody.="</tr>";
	}
	if ($cashboxAccountingCodeId>0){
		$reporteIngresosCajaBody.="<tr class='totalrow'>";
			$reporteIngresosCajaBody.="<td>Saldo Final</td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td></td>";
			$reporteIngresosCajaBody.="<td><span class='currency'>C$ </span><span class='amountright  number'>".$finalSaldo."</span></td>";
		$reporteIngresosCajaBody.="</tr>";
	}
	$reporteIngresosCajaBody.="</tbody>";
	
	$reporteIngresosCaja="<table cellpadding='0' cellspacing='0'>".$reporteIngresosCajaHeader.$reporteIngresosCajaBody."</table>";
	echo $reporteIngresosCaja;
	$excelIngresosCaja="<table id='Ingresos caja' cellpadding='0' cellspacing='0'>".$reporteIngresosCajaExcelHeader.$reporteIngresosCajaBody."</table>";
	$_SESSION['reporteIngresosCaja'] = $excelIngresosCaja;
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<?php		
		echo "<li>".$this->Html->link('Nuevo Recibo de Caja (Factura de Crédito)',array('action' => 'add',CASH_RECEIPT_TYPE_CREDIT))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('Nuevo Recibo de Caja (Otros Ingresos)'),array('action' => 'add',CASH_RECEIPT_TYPE_OTHER))."</li>";
		echo "<br/>";
	
		echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'indexClients'))."</li>";
		if ($userrole!=ROLE_FOREMAN) { 
			echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'addClient'))."</li>";
		} 
	?>
	</ul>
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