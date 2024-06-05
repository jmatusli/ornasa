<div class="cashReceipts form" style='width:100%;'>
<?php 
	echo $this->Form->create('CashReceipt'); 
		echo "<fieldset>";
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
				echo "<legend>".__('Crear Nuevo Recibo de Caja (Factura de Crédito)')."</legend>";
			}
			else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
				echo "<legend>".__('Crear Nuevo Recibo de Caja (Otros Ingresos)')."</legend>";
			}
			
			echo $this->Form->input('receipt_date',array('dateFormat'=>'DMY'));
			echo $this->Form->input('receipt_code',array('default'=>$newCashReceiptCode,'class'=>'narrow','readonly'=>'readonly'));
			echo $this->Form->input('exchange_rate',array('default'=>$exchangeRateCashReceipt,'class'=>'narrow','readonly'=>'readonly'));
			echo $this->Form->input('bool_annulled');
			
			echo $this->Form->input('cash_receipt_type_id',array('default'=>$cash_receipt_type_id,'div'=>array('hidden'=>'hidden')));
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
				echo $this->Form->input('received_from');
			}
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
				echo $this->Form->input('client_id',array('empty'=>array('0'=>'Seleccione Cliente')));
			}
			echo $this->Form->input('concept',array('class'=>'narrow'));
			echo $this->Form->input('observation',array('type'=>'textarea', 'rows' => 2, 'cols' => 25,'style'=>'width:40%'));
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
				echo $this->Form->input('amount',array('type'=>'decimal','class'=>'narrow','default'=>'0'));
			}
			echo $this->Form->input('currency_id');
			echo $this->Form->input('cashbox_accounting_code_id',array('options'=>$cashboxAccountingCodes,'empty'=>array('0'=>'Seleccione Caja')));
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
				echo $this->Form->input('credit_accounting_code_id',array('label'=>'Cuenta Contable HABER (OTROS INGRESOS)','options'=>$accountingCodes,'empty'=>array('0'=>'Seleccione Cuenta HABER')));
				echo $this->Form->submit(__('Submit'));
			}
			if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
				echo $this->Form->input('bool_retention',array('type'=>'checkbox','label'=>'Retención'));
				echo $this->Form->input('retention_number',array('label'=>'Número Retención'));
				echo "<div class='righttop'>";
					echo "<h4>Desglose</h4>";
					//echo $this->Form->input('amount',array('label'=>'Abono para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
					echo $this->Form->input('amount_cuentas_por_cobrar',array('label'=>'Total para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
					echo "<br/>";
					echo $this->Form->input('amount_increment',array('label'=>'Monto Incremento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
					echo $this->Form->input('amount_discount',array('label'=>'Monto Descuento','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
					echo $this->Form->input('amount_difference_exchange_rate',array('label'=>'Monto Cambiario','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'C$'));
					echo "<br/>";					
					echo $this->Form->input('amount_total_payment',array('label'=>'Monto Total Pagado Efectivo','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>'));
					echo $this->Form->input('amount_retention_paid',array('label'=>'Retención para Facturas','type'=>'decimal','readonly'=>'readonly','default'=>'0','between'=>'<span class=\'currencyrighttop\'></span>'));
					echo $this->Form->submit(__('Submit'));
				echo "</div>";
			}
			
		echo "</fieldset>";
		if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
			echo "<div id='invoicesForClient'>";
			echo "</div>";
		}
	echo $this->Form->end(); 
?>
</div>
<!--div class='actions'>
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Cash Receipts'), array('action' => 'index')); ?></li>
		<br/>
	<?php	
		echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'indexClients'))."</li>";
		if ($userrole!=ROLE_FOREMAN) { 
			echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'addClient'))."</li>";
		} 
	?>
	</ul>
</div-->
<script>
	$('body').on('change','#CashReceiptReceiptDateDay',function(){	
		updateExchangeRate();
		calculateAllRows();
	});	
	$('body').on('change','#CashReceiptReceiptDateMonth',function(){	
		updateExchangeRate();
		calculateAllRows();
	});		
	$('body').on('change','#CashReceiptReceiptDateYear',function(){	
		updateExchangeRate();
		calculateAllRows();
	});			
	function updateExchangeRate(){
		var receiptday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#CashReceiptExchangeRate').val(exchangerate);
				updateExchangeRateDifferences();
				updatePending();
				updateRetention();
			},
			error: function(e){
				$('#invoicesForClient').html(e.responseText);
				console.log(e);
			}
		});
	}
	function updateExchangeRateDifferences(){
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('td.differenceexchangerates input').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoiceexchangerate=invoicerow.find('td.invoiceexchangerate input').val();
			var exchangeratedifference=roundToFour(cashreceiptexchangerate-invoiceexchangerate);
			$(this).val(exchangeratedifference);
			var invoicecurrency=invoicerow.find('td.invoicecurrency input').val();
			if (invoicecurrency==2){
				var invoiceamount=invoicerow.find('td.totalprice span.amount').text();
				invoicerow.find('td.exchangeratedifference input').val(roundToTwo(invoiceamount*exchangeratedifference));
			}
		});
	}
	function updatePending(){
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('tr:not(.totalrow) td.pending span.amountright').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoicecurrencyid=invoicerow.find('td.invoicecurrency input').val();
			var pendingcashreceiptcurrency=invoicerow.find('td.totalprice span').text();
			if (cashreceiptcurrencyid!=invoicecurrencyid){
				if (invoicecurrencyid==1){
					pendingcashreceiptcurrency/=$cashreceiptexchangerate;
				}
				else if (invoicecurrencyid==2){
					pendingcashreceiptcurrency*=$cashreceiptexchangerate;
				}
			}
			var paidalreadyCS=invoicerow.find('td.paidalready span').text();
			var diferenciacambiariapagado=invoicerow.find('td.diferenciacambiariapagado span').text();
			if (cashreceiptcurrencyid==1){
				//pendingcashreceiptcurrency-=paidalreadyCS;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-paidalreadyCS-diferenciacambiariapagado;
			}
			else if (cashreceiptcurrencyid==2){
				//pendingcashreceiptcurrency-=paidalreadyCS/cashreceiptexchangerate;
				pendingcashreceiptcurrency=pendingcashreceiptcurrency-(paidalreadyCS+diferenciacambiariapagado)/cashreceiptexchangerate;
			}
			pendingcashreceiptcurrency=roundToTwo(pendingcashreceiptcurrency,2);
			$(this).text(pendingcashreceiptcurrency);		
		});
	}
	function updateRetention(){
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var cashreceiptexchangerate=$('#CashReceiptExchangeRate').val();
		$('td.retention div input').each(function(){
			var invoicerow=$(this).closest('tr');
			var invoicecurrencyid=invoicerow.find('td.invoicecurrency input').val();
			var retentioncashreceiptcurrency=invoicerow.find('td.retentioninvoicecurrency div input').val();
			if (invoicecurrencyid!=cashreceiptcurrencyid){
				if (invoicecurrencyid==1){
					retentioncashreceiptcurrency/=cashreceiptexchangerate;
				}
				else if (invoicecurrencyid==2){
					retentioncashreceiptcurrency*=cashreceiptexchangerate;
				}
			}
			retentioncashreceiptcurrency=roundToTwo(retentioncashreceiptcurrency,2);
			$(this).text(retentioncashreceiptcurrency);		
		});			
	}
	
	
	function calculateAllRows(){
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	}

	$('body').on('change','#CashReceiptBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#CashReceiptAmount').parent().addClass('hidden');
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().addClass('hidden');
			$('#invoicesForClient').addClass('hidden');
			$('#CashReceiptBoolRetention').parent().addClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			$('#CashReceiptRetentionNumber').parent().addClass('hidden');
		}
		else {
			$('#CashReceiptAmount').parent().removeClass('hidden');
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().removeClass('hidden');
			$('#CashReceiptBoolRetention').parent().removeClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			if ($('#CashReceiptBoolRetention').is(':checked')){
				$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
			}
			else {
				$('#CashReceiptRetentionNumber').parent().addClass('hidden');
			}
			$('#invoicesForClient').removeClass('hidden');
		}
	});	
	
	$('body').on('change','#CashReceiptCurrencyId',function(){	
		var currencyid=$(this).children("option").filter(":selected").val();
		var exchangerate=parseFloat($('#CashReceiptExchangeRate').val());
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			//$('td.amount div').prepend('C$ ');
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
			var value_in_USD=0;
			$('tr:not(.totalrow) td.pending span.amount').each(function(){
				value_in_USD=parseFloat($(this).text());
				$(this).text(roundToTwo(value_in_USD*exchangerate));
			});
			var value_in_USD=0;
			$('tr:not(.totalrow) td.retention div input').each(function(){
				value_in_USD=parseFloat($(this).text());
				$(this).val(roundToTwo(value_in_USD*exchangerate));
			});
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			//$('td.amount div').prepend('US$ ');
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
			var value_in_CS=0;
			$('tr:not(.totalrow) td.pending span.amount').each(function(){
				value_in_CS=parseFloat($(this).text());
				$(this).text(roundToTwo(value_in_CS/exchangerate));
			});
			var value_in_CS=0;
			$('tr:not(.totalrow) td.retention div input').each(function(){
				value_in_CS=parseFloat($(this).val());
				$(this).val(roundToTwo(value_in_CS/exchangerate));
			});
		}
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	});	
	
<?php
	if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
?>
	$('body').on('change','#CashReceiptClientId',function(){	
		var clientid=$(this).children("option").filter(":selected").val();
		if (clientid>0){
			loadInvoicesForClient(clientid);
		}
	});	
		
	function loadInvoicesForClient(clientid){
		var receiptday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var receiptmonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var receiptyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		var boolretention=$('#CashReceiptBoolRetention').is(':checked');
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>invoices/getpendinginvoicesforclient/',
			data:{"clientid":clientid,"receiptday":receiptday,"receiptmonth":receiptmonth,"receiptyear":receiptyear,"currencyid":currencyid,"boolretention":boolretention},
			cache: false,
			type: 'POST',
			success: function (invoices) {
				$('#invoicesForClient').html(invoices);
				formatCurrencies();
			},
			error: function(e){
				$('#invoicesForClient').html(e.responseText);
				console.log(e);
			}
		});
	}
		
<?php	
	}
?>
	
	$('body').on('change','#CashReceiptBoolRetention',function(){	
		if ($(this).is(':checked')){
			$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			$('#invoicesForClient th.retention').removeClass('hidden');
			$('#invoicesForClient td.retention').removeClass('hidden');
			$('#invoicesForClient th.retentionpayment').removeClass('hidden');
			$('#invoicesForClient td.retentionpayment').removeClass('hidden');
		}
		else {
			$('#CashReceiptRetentionNumber').parent().addClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			$('#invoicesForClient th.retention').addClass('hidden');
			$('#invoicesForClient td.retention').addClass('hidden');
			$('#invoicesForClient th.retentionpayment').addClass('hidden');
			$('#invoicesForClient td.retentionpayment').addClass('hidden');
		}
		$('tr').each(function(){
			calculateRow($(this).attr('id'));
		});
		calculateTotalRow();
	});	
	
	$('body').on('change','td.increment div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
	});	
	
	$('body').on('change','td.discount div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
	});	
	
	$('body').on('change','td.retention div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		//calculateRow(invoiceid);
		calculateTotalRow();
	});	
	
	$('body').on('change','td.payment div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
		calculateTotalPayment();
	});	
	
	$('body').on('change','td.retentionpayment div input',function(){	
		var invoiceid=$(this).closest('tr').attr('id');
		calculateRow(invoiceid);
		calculateTotalRow();
		calculateTotalPayment();
	});	
	
	function calculateRow(invoiceid) {    
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').val();
		var exchangeratecashreceipt=$('#CashReceiptExchangeRate').val();
		if ($.isNumeric(invoiceid)){
			// calculate value saldo
			var currentrow=$('#'+invoiceid);
			var pending=parseFloat(currentrow.find('td.pending span.amount.right').text());
			var increment=parseFloat(currentrow.find('td.increment div input').val());
			var discount=parseFloat(currentrow.find('td.discount div input').val());
			
			var bool_retention=$('#CashReceiptBoolRetention').is(':checked');
			var retention=parseFloat(currentrow.find('td.retention div input').val());
			var saldo=pending+increment-discount;
			if (bool_retention){
				if (pending>retention){
					saldo=pending+increment-discount-retention;
				}
				else {
					retention=retention-pending;
					saldo=increment-discount;
				}
			}
			
			currentrow.find('td.saldo div input').val(roundToTwo(saldo));
			
			var ratedifference=parseFloat(currentrow.find('td.exchangeratedifference div input').val());
			
			var payment=parseFloat(currentrow.find('td.payment div input').val());
			if (!$.isNumeric(payment)){
				payment=0;
				currentrow.find('td.payment div input').val('0');
			}
			
			if (payment>0){
				if (payment<saldo){
					currentrow.find('td.payment div input').addClass('yellowbg');
					currentrow.find('td.payment div input').removeClass('redbg');
					currentrow.find('td.payment div input').removeClass('greenbg');
				}
				else {
					if ((payment-saldo)<0.01){
						currentrow.find('td.payment div input').addClass('greenbg');
						currentrow.find('td.payment div input').removeClass('redbg');
						currentrow.find('td.payment div input').removeClass('yellowbg');
					}
					else {
						currentrow.find('td.payment div input').addClass('redbg');
						currentrow.find('td.payment div input').removeClass('yellowbg');
						currentrow.find('td.payment div input').removeClass('greenbg');
					}
				}
			}
			else {
				currentrow.find('td.payment div input').removeClass('yellowbg');
				currentrow.find('td.payment div input').removeClass('greenbg');
				currentrow.find('td.payment div input').removeClass('redbg');
			}
			var retentionpayment=parseFloat(currentrow.find('td.retentionpayment div input').val());
			if (!$.isNumeric(retentionpayment)){
				retentionpayment=0;
				currentrow.find('td.retentionpayment div input').val('0');
			}
			if (retentionpayment>0){
				if (retentionpayment<retention){
					currentrow.find('td.retentionpayment div input').addClass('yellowbg');
					currentrow.find('td.retentionpayment div input').removeClass('redbg');
					currentrow.find('td.retentionpayment div input').removeClass('greenbg');
				}
				else {
					if (retentionpayment==retention){
						currentrow.find('td.retentionpayment div input').addClass('greenbg');
						currentrow.find('td.retentionpayment div input').removeClass('redbg');
						currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
					}
					else {
						currentrow.find('td.retentionpayment div input').addClass('redbg');
						currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
						currentrow.find('td.retentionpayment div input').removeClass('greenbg');
					}
				}
			}
			else {
				currentrow.find('td.retentionpayment div input').removeClass('yellowbg');
				currentrow.find('td.retentionpayment div input').removeClass('greenbg');
				currentrow.find('td.retentionpayment div input').removeClass('redbg');
			}
			
			var retentionpaymentCS=parseFloat(currentrow.find('td.retentionpayment div input').val());
			var paymentCS=payment;
			var incrementCS=increment;
			var discountCS=discount;
			if (cashreceiptcurrencyid==2){
				paymentCS=roundToTwo(paymentCS*exchangeratecashreceipt);
				retentionpaymentCS=roundToTwo(retentionpaymentCS*exchangeratecashreceipt);
			}
			var paymentleft=roundToTwo(paymentCS+retentionpaymentCS);
			var pendingCS=parseFloat(currentrow.find('td.pending span.amount.right').text());
			if (cashreceiptcurrencyid==2){
				pendingCS=roundToTwo(pendingCS*exchangeratecashreceipt);
			}
			var pendingcreditCS=roundToTwo(pendingCS-ratedifference);
			if (paymentleft>0){
				if (paymentleft<pendingcreditCS){
					currentrow.find('td.creditpayment div input').val(roundToTwo(paymentleft));
					if (paymentleft<pendingcreditCS){
						currentrow.find('td.creditpayment div input').addClass('red');
					}
					paymentleft=0;
				}
				else {
					currentrow.find('td.creditpayment div input').val(roundToTwo(pendingcreditCS));
					currentrow.find('td.creditpayment div input').removeClass('red');
					paymentleft-=pendingcreditCS;
				}
				if (paymentleft>0){
					currentrow.find('td.descpayment div input').val(roundToTwo(discountCS));
					paymentleft+=discountCS;
				}
				if (paymentleft<incrementCS){
					currentrow.find('td.incpayment div input').addClass('red');
				}
				else {
					currentrow.find('td.incpayment div input').removeClass('red');
				}
				if (paymentleft>0){
					if (paymentleft<incrementCS){
						currentrow.find('td.incpayment div input').val(roundToTwo(paymentleft));
						paymentleft=0;
					}
					else {
						currentrow.find('td.incpayment div input').val(roundToTwo(incrementCS));
						paymentleft-=incrementCS;
					}
				}
				if (paymentleft<ratedifference){
					currentrow.find('td.difpayment div input').addClass('red');
				}
				else {
					currentrow.find('td.difpayment div input').removeClass('red');
				}
				if (paymentleft>0){
					if (paymentleft<ratedifference){
						currentrow.find('td.difpayment div input').val(roundToTwo(paymentleft));
						paymentleft=0;
					}
					else {
						currentrow.find('td.difpayment div input').val(roundToTwo(ratedifference));
						paymentleft-=ratedifference;
					}
				}
			}
		}
	}
	
	function calculateTotalRow() {    
		var invoicetotal=0;
		var totalpaidalready=0;
		var totalpending=0;
		
		var totalincrement=0;
		var totaldiscount=0;
		var totalretention=0;
		var totalratedifference=0;
		var totalsaldo=0;
		
		var totalpayment=0;
		var totalretentionpayment=0;
		var totalcreditpayment=0;
		var totalincpayment=0;
		var totaldescpayment=0;
		var totaldifpayment=0;
		
		var cashcurrency=$('#CashReceiptCurrencyId').val();
		
		$('tr:not(.totalrow) td.totalprice span.amount.right').each(function(){
			var invoicecurrency=$(this).closest('tr').find('td.invoicecurrency input').val();
			var invoicecost=parseFloat($(this).text());
			if (cashcurrency==invoicecurrency){
				invoicetotal+=invoicecost;
			}
			else {
				if (cashcurrency==<?php echo CURRENCY_CS; ?>){
					invoicetotal+=roundToTwo(invoicecost*<?php echo $exchangeRateCashReceipt; ?>);
				}
				else {
					invoicetotal+=roundToTwo(invoicecost/<?php echo $exchangeRateCashReceipt; ?>);
				}
			}
		});
		$('tr.totalrow td.totalprice span.totalamount').text(roundToTwo(invoicetotal));
		
		$('tr:not(.totalrow) td.paidalready span.amount.right').each(function(){
			totalpaidalready+=parseFloat($(this).text());
		});
		$('tr.totalrow td.totalpaidalready span.totalamount').text(roundToTwo(totalpaidalready));

		$('tr:not(.totalrow) td.pending span.amount.right').each(function(){
			totalpending+=parseFloat($(this).text());
		});
		//var roundedtotalpending=roundToTwo(totalpending);
		$('tr.totalrow td.pending span.totalamount').text(parseFloat(roundToTwo(totalpending)));
		
		$('tr:not(.totalrow) td.increment div input').each(function(){
			totalincrement+=parseFloat($(this).val());
		});
		$('tr.totalrow td.increment span.totalamount').text(roundToTwo(totalincrement));
		
		$('tr:not(.totalrow) td.discount div input').each(function(){
			totaldiscount+=parseFloat($(this).val());
		});
		$('tr.totalrow td.discount span.totalamount').text(roundToTwo(totaldiscount));
		
		$('tr:not(.totalrow) td.exchangeratedifference div input').each(function(){
			totalratedifference+=parseFloat($(this).val());
		});
		$('tr.totalrow td.exchangeratedifference span.totalamount').text(roundToTwo(totalratedifference));
		
		$('tr:not(.totalrow) td.retention div input').each(function(){
			totalretention+=parseFloat($(this).val());
		});
		$('tr.totalrow td.retention span.totalamount').text(roundToTwo(totalretention));
		
		$('tr:not(.totalrow) td.saldo div input').each(function(){
			totalsaldo+=parseFloat($(this).val());
		});
		$('tr.totalrow td.saldo span.totalamount').text(roundToTwo(totalsaldo));
		
		$('tr:not(.totalrow) td.payment div input').each(function(){
			if ($.isNumeric($(this).val())){
				totalpayment+=parseFloat($(this).val());
			}
		});
		$('tr.totalrow td.payment span.totalamount').text(roundToTwo(totalpayment));
		
		$('tr:not(.totalrow) td.retentionpayment div input').each(function(){
			if ($.isNumeric($(this).val())){
				totalretentionpayment+=parseFloat($(this).val());
			}
		});
		$('tr.totalrow td.retentionpayment span.totalamount').text(roundToTwo(totalretentionpayment));
		
		$('tr:not(.totalrow) td.creditpayment div input').each(function(){
			totalcreditpayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.creditpayment span.totalamount').text(roundToTwo(totalcreditpayment));
		
		$('tr:not(.totalrow) td.incpayment div input').each(function(){
			totalincpayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.incpayment span.totalamount').text(roundToTwo(totalincpayment));
		
		$('tr:not(.totalrow) td.descpayment div input').each(function(){
			totaldescpayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.descpayment span.totalamount').text(roundToTwo(totaldescpayment));
		
		$('tr:not(.totalrow) td.difpayment div input').each(function(){
			totaldifpayment+=parseFloat($(this).val());
		});
		$('tr.totalrow td.difpayment span.totalamount').text(roundToTwo(totaldifpayment));
		var totalrowfinished=true;
	}

	function calculateTotalPayment() {    
		var totalcreditpayment=parseFloat($('#pendingInvoices tr.totalrow td.creditpayment span.totalamount').text());
		var totalretentionpayment=parseFloat($('#pendingInvoices tr.totalrow td.retentionpayment span.totalamount').text());
		var totalincrement=parseFloat($('#pendingInvoices tr.totalrow td.incpayment span.totalamount').text());
		var totaldiscount=parseFloat($('#pendingInvoices tr.totalrow td.descpayment span.totalamount').text());
		var totalerdiff=parseFloat($('#pendingInvoices tr.totalrow td.difpayment span.totalamount').text());
		var totalpayment=parseFloat($('#pendingInvoices tr.totalrow td.payment span.totalamount').text());
		
		
		//$('#CashReceiptAmount').val(totalcreditpayment);
		$('#CashReceiptAmountRetentionPaid').val(totalretentionpayment);
		$('#CashReceiptAmountCuentasPorCobrar').val(roundToTwo(totalcreditpayment));
		$('#CashReceiptAmountDiscount').val(totaldiscount);
		$('#CashReceiptAmountIncrement').val(totalincrement);
		$('#CashReceiptAmountDifferenceExchangeRate').val(totalerdiff);
		$('#CashReceiptAmountTotalPayment').val(totalpayment);
	}
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToFour(num) {    
		return +(Math.round(num + "e+4")  + "e-4");
	}
	
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
	
	function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			//$(this).parent().prepend("C$ ");
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
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
	$(document).ready(function(){
		if ($('#CashReceiptBoolAnnulled').is(':checked')){
			$('#CashReceiptAmount').parent().addClass('hidden');
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().addClass('hidden');
			$('#CashReceiptBoolRetention').parent().addClass('hidden');
			$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			$('#CashReceiptRetentionNumber').parent().addClass('hidden');
		}
		else {
			$('#CashReceiptAmount').parent().removeClass('hidden');
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#CashReceiptCreditAccountingCodeId').parent().removeClass('hidden');	
			$('#CashReceiptBoolRetention').parent().removeClass('hidden');
			if ($('#CashReceiptBoolRetention').is(':checked')){
				$('#CashReceiptRetentionNumber').parent().removeClass('hidden');
				$('#CashReceiptAmountRetentionPaid').parent().removeClass('hidden');
			}
			else {
				$('#CashReceiptRetentionNumber').parent().addClass('hidden');
				$('#CashReceiptAmountRetentionPaid').parent().addClass('hidden');
			}
		}
		
	<?php 
		if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){ 
	?>
		
		var cashreceiptcurrencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		
		if (cashreceiptcurrencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (cashreceiptcurrencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}	
			
		var clientid=$('#CashReceiptClientId').children("option").filter(":selected").val();
		if (clientid>0){
			loadInvoicesForClient(clientid);
		<?php	
			echo "$(document).ajaxComplete(function() {\r\n";
			if (!empty($postedInvoiceData)){
				foreach ($postedInvoiceData as $postedInvoice){
					echo "var invoiceRow=$('#'+".$postedInvoice['invoice_id'].");\r\n";
					echo "invoiceRow.find('td.increment div input').val('".$postedInvoice['increment']."');\r\n";
					echo "invoiceRow.find('td.discount div input').val('".$postedInvoice['discount']."');\r\n";
					echo "invoiceRow.find('td.saldo div input').val('".$postedInvoice['saldo']."');\r\n";
					echo "invoiceRow.find('td.payment div input').val('".$postedInvoice['payment']."');\r\n";
					echo "invoiceRow.find('td.retention div input').val('".$postedInvoice['retention']."');\r\n";
					echo "calculateTotalRow();";
					echo "calculateTotalPayment();";
				}
			}
		?>
			if (!$('#CashReceiptBoolAnnulled').is(':checked')){
				if ($('#CashReceiptBoolRetention').is(':checked')){				
					$('#invoicesForClient th.retention').removeClass('hidden');
				}
			}
		<?php
			echo "});\r\n";
		?>
		}
	<?php } ?>
		
	});
	
</script>