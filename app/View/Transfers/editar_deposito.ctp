<script>
/*  
	$('body').on('change','#TransferCashboxAccountingCodeId',function(){	
		calculateCashboxSaldo();
		$('#AccountingMovement21AccountingCodeId').val($(this).val());
	});	
	$('body').on('change','#TransferTransferDateDay',function(){	
		calculateCashboxSaldo();
	});	
	$('body').on('change','#TransferTransferDateMonth',function(){	
		calculateCashboxSaldo();
	});	
	$('body').on('change','#TransferTransferDateYear',function(){	
		calculateCashboxSaldo();
	});	
	function calculateCashboxSaldo(){	
		var cashbox_id=$('#TransferCashboxAccountingCodeId').val();
		var transfer_day=parseInt($('#TransferTransferDateDay').val());
		var transfer_month=parseInt($('#TransferTransferDateMonth').val());
		var transfer_year=parseInt($('#TransferTransferDateYear').val());
		var transferdate=new Date(transfer_year,transfer_month-1,transfer_day);
		var transferdatenextday=transferdate.addDays(1);
		var transfer_day_next_day=transferdatenextday.getDate();
		var transfer_month_next_day=transferdatenextday.getMonth()+1;
		var transfer_year_next_day=transferdatenextday.getFullYear();
		
		if (cashbox_id>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>accounting_codes/getaccountsaldo/',
				data:{"accounting_code_id":cashbox_id,"accounting_code_day":transfer_day_next_day,"accounting_code_month":transfer_month_next_day,"accounting_code_year":transfer_year_next_day},
				cache: false,
				type: 'POST',
				success: function (saldo) {
					$('#cashboxSaldo').text(saldo);
				},
				error: function(e){
					$('#cashboxSaldo').html(e.responseText);
          alert(e.responseText);
					console.log(e);
				}
			});
		}
	}
*/

	$('body').on('change','#TransferCurrencyId',function(){
		var currency=$(this).children("option").filter(":selected").text();
    var currencyid=$(this).children("option").filter(":selected").val();
    $('.deposit_column').each(function(){
      $(this).removeClass('CScurrency');
      $(this).removeClass('USDcurrency');
      if (currencyid==<?php echo CURRENCY_USD; ?>){
        $(this).addClass('USDcurrency');
      }
      else {
        $(this).addClass('CScurrency');
      }
    });
    $('#deposit_column_header').text('Monto Depositado ' + currency);
    formatCSCurrencies();
    formatUSDCurrencies();
    triggerAllSelectors();
    
	});	
	
  $('body').on('change','.powerselector',function(e){
		if ($(this).is(':checked')){
			$(this).closest('fieldset').find('td.selector input').prop('checked',true);
      $(this).closest('fieldset').find('input.powerselector').prop('checked',true);
      $(this).closest('fieldset').find('tr').removeClass('noprint');
		}
		else {
			$(this).closest('fieldset').find('td.selector input').prop('checked',false);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',false);
      $(this).closest('fieldset').find('tr').addClass('noprint');
		}
    triggerAllSelectors();
	});
  
  function triggerAllSelectors(){
    $('#pagosADepositar tr:not(.totalrow)').each(function(){
      $(this).find('td.selector input[type="checkbox"]').each(function(){
        setRowValue($(this).attr('id'));
      });
    });
    calculateDepositTotal();
  }
  
  function setRowValue(selectorId){
    var rowSelector=$('#'+selectorId);
    var closestRow=rowSelector.closest('tr');
    if ($(rowSelector).is(':checked')){
      var currencyId=$('#TransferCurrencyId').children("option").filter(":selected").val();
      //var amountusd=closestRow.find('td.paidAmountUsd').html();
      //var amountcs=closestRow.find('td.paidAmountCs').html();
      var deposit= parseFloat(currencyId==<?php echo CURRENCY_USD; ?>?closestRow.find('td.paidAmountUsd').html():closestRow.find('td.paidAmountCs').html());
      var depositCS= parseFloat(closestRow.find('td.paidAmountCs').html());
      closestRow.find('td.deposit_column span div input').val(deposit);
      closestRow.find('td.deposit_column_cs').html(depositCS);
      closestRow.removeClass('noprint');
    }
    else {
      closestRow.find('td.deposit_column input').val(0);
      closestRow.addClass('noprint');
    }
  }
  
  
  $('body').on('change','.selector input',function(e){
    setRowValue($(this).attr('id'));
    calculateDepositTotal();
  });
  
  $('body').on('change','.depositInput',function(e){
    var closestRow=$(this).closest('tr');
    var depositValueCS=depositValue=$(this).val();
    var currencyId=$('#TransferCurrencyId').children("option").filter(":selected").val();
    if (!isNaN(depositValue)){
      if (parseFloat(depositValue)>0){
        $(closestRow).find('td.selector input[type="checkbox"]').prop('checked',true);
      }
      if (currencyId==<?php echo CURRENCY_USD; ?>){
        var depositUSD= parseFloat(closestRow.find('td.paidAmountUsd').html());
        var depositCS= parseFloat(closestRow.find('td.paidAmountCs').html());
        depositValueCS=roundToTwo(depositValue*depositCS/depositUSD);
      }
      closestRow.find('td.deposit_column_cs').html(depositValueCS);          
      
      calculateDepositTotal();
    }
    
          
  });
  
  function calculateDepositTotal(){
    var depositTotal=0;
    var depositTotalCS=0;
    var concept="Depósito para pagos de ";
    var currentDeposit=0;
    $('#pagosADepositar tr:not(.totalrow)').each(function(){
      if ($(this).find('td.selector input[type="checkbox"]').is(':checked')){
        currentDeposit=parseFloat($(this).find('td.deposit_column input').val());
        currentDepositCS=parseFloat($(this).find('td.deposit_column_cs').html());
        if (!isNaN(currentDeposit)){
          depositTotal+=currentDeposit;
          depositTotalCS+=currentDepositCS;
        }
        var paymentLink=$(this).find('td.payment a');
        if (paymentLink.hasClass("Invoice")){
          concept += " Fact ";
        }
        else if (paymentLink.hasClass("CashReceipt")){
          concept += " RC ";
        }
        concept+=paymentLink.text();
      }
    });
    if (concept!="Depósito para pagos de "){
      $('#TransferConcept').val(concept);
    }
    depositTotal=roundToTwo(depositTotal);
    depositTotalCS=roundToTwo(depositTotalCS);
    $('#pagosADepositar tr.totalrow td.deposit_column span.amountright').text(depositTotal);
    $('#pagosADepositar tr.totalrow td.deposit_column_cs').text(depositTotalCS);
    $('#TransferAmount').val(depositTotal);
    $('#DebitAmount').val(depositTotalCS);
    $('#CreditAmount').val(depositTotalCS);
    $('#TransferAmountCs').val(depositTotalCS);
  }  
  
  $('body').on('change','.debitamount',function(){
		calculateTotalDebit();
	});	
	
  $('body').on('change','.creditamount',function(){
		calculateTotalCredit();
	});	

	$('body').on('click','#addDebitCode',function(){
		var tableRow=$('#accountingMovementsForRegister tbody tr.debit.hidden:first');
		tableRow.removeClass("hidden");
	});

  $('body').on('click','.removeDebitCode',function(){
		$(this).closest('tr').remove();
		calculateTotalDebit();
	});	
	
  $('body').on('click','#addCreditCode',function(){
		var tableRow=$('#accountingMovementsForRegister tbody tr.credit.hidden:first');
		tableRow.removeClass("hidden");
	});

  $('body').on('click','.removeCreditCode',function(){
		$(this).parent().parent().remove();
		calculateTotalCredit();
	});	
	
	function calculateTotalDebit(){
		var totalCost=0;
		$("#accountingMovementsForRegister tbody tr:not(.hidden)").each(function() {
			var currentDebitAmount = $(this).find('td.debitamount div input');
			var currentCost = parseFloat(currentDebitAmount.val());
			if (!isNaN(currentCost)){
				totalCost = totalCost + currentCost;
			}
		});
		$('#debitTotal span[class="amount"]').text(totalCost);
		checkBalance();
		return false;
	}
	
	function calculateTotalCredit(){
		var totalCost=0;
		$("#accountingMovementsForRegister tbody tr:not(.hidden)").each(function() {
			var currentCreditAmount = $(this).find('td.creditamount div input');
			var currentCost = parseFloat(currentCreditAmount.val());
			if (!isNaN(currentCost)){
				totalCost = totalCost + currentCost;
			}
		});
		$('#creditTotal span[class="amount"]').text(totalCost);
		checkBalance();
		return false;
	}
	
	function checkBalance(){
		var totalDebit=0;
		var totalCredit=0;
		totalDebit=$('#debitTotal span.amount').text();
		totalCredit=$('#creditTotal span.amount').text();
		if (totalDebit!=totalCredit) {
			$('#accountingRegisterTotals').addClass('nomatch');	
		}
		else {
			$('#accountingRegisterTotals').removeClass('nomatch');	
			$('#TransferAmount').val(totalCredit);
		}
		return false;
	}
	
  $('body').on('keypress','#content',function(e){
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
			//$('#AccountingRegisterAddForm').submit();
		}
	});
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			if ($(this).find('.amountright').text()){
				$(this).find('.amountright').number(true,2);
			}
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			if ($(this).find('.amountright').text()){
				$(this).find('.amountright').number(true,2);
			}
			$(this).find('.currency').text("US$");
		});
	}
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
  
  $( document ).ajaxComplete(function() {
		formatCSCurrencies();
		formatUSDCurrencies();
	});	
	
	
	$(document).ready(function(){
		//calculateCashboxSaldo();
		var cashbox=$('#TransferCashboxAccountingCodeId').val();
		$('#AccountingMovement21AccountingCodeId').val(cashbox);
    $('#TransferCurrencyId').trigger('change');
	});
</script>
<div class="transfers form fullwidth">
<?php 
  echo "<h2>".__('Edit Deposit')." ".$this->request->data['Transfer']['transfer_code']."</h2>";
  
  echo $this->Form->create('Transfer'); 
  echo "<fieldset>";
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-md-7'>";
        echo $this->Form->input('bool_deposit',['type'=>'hidden','value'=>1]);
        echo $this->Form->input('cashbox_accounting_code_id',['type'=>'hidden','value'=>ACCOUNTING_CODE_CASHBOX_MAIN]);
        echo $this->Form->input('bank_accounting_code_id');
        echo $this->Form->input('transfer_date',['dateFormat'=>'DMY','minYear'=>2013, 'maxYear'=>date('Y')]);
        echo $this->Form->input('transfer_code',['class'=>'narrow','readonly'=>'readonly']);
        echo $this->Form->input('bank_reference');
        echo $this->Form->input('concept');
        echo $this->Form->input('amount',array('class'=>'narrow'));
        echo $this->Form->input('amount_cs',['label'=>'Monto en C$','class'=>'narrow']);
        echo $this->Form->input('currency_id');
      echo "</div>";
      echo "<div class='col-md-3'>";
        //echo "<p style='margin-right:10%'>Saldo para caja: <span id='cashboxSaldo' class='amountright'></span></p>";
      echo "</div>";
      echo "<div class='col-md-2 actions'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          echo "<li>".$this->Html->link(__('List Deposits'), array('action' => 'resumenDepositos'))."</li>";
        echo "</ul>";
      echo "</div>";
    echo "</div>";
    echo "<br/>";
    echo "<p class='comment'>El monto pagado de una factura de contado se calcula tomando el precio total incluyendo IVA, restándole la retención si es presente</p>";
    echo "<p class='comment'>El monto pagado de un recibo de caja es sencillamente el monto del recibo de caja</p>";
    
    $paymentTableHeader="";
    $paymentTableHeader.="<thead>";
      $paymentTableHeader.="<tr>";
        $paymentTableHeader.="<th></th>";
        $paymentTableHeader.="<th>Fecha Pagado</th>";
        $paymentTableHeader.="<th>Factura de Contado / Recibo de Caja</th>";
        $paymentTableHeader.="<th class='centered'>Monto Pagado C$</th>";
        $paymentTableHeader.="<th class='centered'>Monto Pagado US$</th>";
        $paymentTableHeader.="<th class='centered' id='deposit_column_header'>Monto Depositado</th>";
      $paymentTableHeader.="</tr>";
    $paymentTableHeader.="</thead>";
    
    $paymentTableRows="";
    echo $this->Form->input('powerselector1',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
    
    $totalCS=0;
    $totalUSD=0;
    $totalDeposited=0;
    foreach ($undepositedPayments as $paymentInvoiceCashReceipt){
      //pr($paymentInvoiceCashReceipt);
      //if (!empty($payment['invoice_date'])){
      if (!empty($paymentInvoiceCashReceipt['Invoice'])){
        $payment=$paymentInvoiceCashReceipt['Invoice'];
        //pr($payment);
        $invoiceDateTime=new DateTime($payment['invoice_date']);
        $paymentTableRow="";
        $paymentTableRow.="<tr>";
          $paymentTableRow.="<td class='selector'>".$this->Form->input('Payment.Invoice.'.$payment['id'].'.selector',['checked'=>(in_array($payment['id'],$requestInvoicePaymentIds)?true:false),'label'=>false])."</td>";
          $paymentTableRow.="<td>".$invoiceDateTime->format('d-m-Y')."</td>";
          $paymentTableRow.="<td class='payment'>".$this->Html->link($payment['invoice_code'],['controller'=>'invoices','action'=>'view',$payment['id']],['class'=>'Invoice'])."</td>";
          if ($payment['currency_id']==CURRENCY_CS){
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".($payment['paid_amount_CS'])."</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $totalCS+=($payment['paid_amount_CS']);
          }
          elseif ($payment['currency_id']==CURRENCY_USD){
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".($payment['paid_amount_USD'])."</span></td>";
            $totalUSD+=($payment['paid_amount_USD']);
          }
          else {
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
          }
          $paymentTableRow.="<td class='paidAmountCs' style='display:none'>".($payment['paid_amount_CS'])."</td>";
          $paymentTableRow.="<td class='paidAmountUsd' style='display:none'>".($payment['paid_amount_USD'])."</td>";
          $paymentTableRow.="<td class='deposit_column'><span class='currency'></span><span class='amountright'>";
            $paymentTableRow.=$this->Form->input('Payment.Invoice.'.$payment['id'],['type'=>'decimal','label'=>false,'style'=>'margin-left:10px;','class'=>'depositInput']);
          $paymentTableRow.="</span></td>";
          $paymentTableRow.="<td class='deposit_column_cs' style='display:none'></td>";
          
        $paymentTableRow.="</tr>";
        $paymentTableRows.=$paymentTableRow;
      }
      //elseif (!empty($payment['receipt_date'])){
      elseif (!empty($paymentInvoiceCashReceipt['CashReceipt'])){  
        $payment=$paymentInvoiceCashReceipt['CashReceipt'];
        $receiptDateTime=new DateTime($payment['receipt_date']);
        $paymentTableRow="";
        $paymentTableRow.="<tr>";
          $paymentTableRow.="<td class='selector'>".$this->Form->input('Payment.CashReceipt.'.$payment['id'].'.selector',['checked'=>(in_array($payment['id'],$requestCashReceiptPaymentIds)?true:false),'label'=>false])."</td>";
          $paymentTableRow.="<td>".$receiptDateTime->format('d-m-Y')."</td>";
          $paymentTableRow.="<td class='payment'>".$this->Html->link($payment['receipt_code'],['controller'=>'cash_receipts','action'=>'view',$payment['id']],['class'=>'CashReceipt'])."</td>";
          if ($payment['currency_id']==CURRENCY_CS){
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".($payment['paid_amount_CS'])."</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $totalCS+=($payment['paid_amount_CS']);
          }
          elseif ($payment['currency_id']==CURRENCY_USD){
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".($payment['paid_amount_USD'])."</span></td>";
            $totalUSD+=($payment['paid_amount_USD']);
          }
          else {
            $paymentTableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
            $paymentTableRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>0</span></td>";
          }
          $paymentTableRow.="<td class='paidAmountCs' style='display:none'>".($payment['paid_amount_CS'])."</td>";
          $paymentTableRow.="<td class='paidAmountUsd' style='display:none'>".($payment['paid_amount_USD'])."</td>";
          $paymentTableRow.="<td class='deposit_column'><span class='currency'></span><span class='amountright'>";
            $paymentTableRow.=$this->Form->input('Payment.CashReceipt.'.$payment['id'],['type'=>'decimal','label'=>false,'style'=>'margin-left:10px;','class'=>'depositInput']);
          $paymentTableRow.="</span></td>";
          $paymentTableRow.="<td class='deposit_column_cs' style='display:none'></td>";
          
        $paymentTableRow.="</tr>";
        $paymentTableRows.=$paymentTableRow;
      }
    }
    $totalRow="";
    $totalRow.="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
      $totalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalUSD."</span></td>";
      $totalRow.="<td class='CScurrency deposit_column''><span class='currency'></span><span class='amountright'>".$totalDeposited."</span></td>";
      $totalRow.="<td class='deposit_column_cs' style='display:none'></td>";
    $totalRow.="</tr>";
    $paymentTableBody="<tbody>".$totalRow.$paymentTableRows.$totalRow."</tbody>";
		
    
    echo "<h2>Facturas de contado</h2>";
    echo "<table id='pagosADepositar'>".$paymentTableHeader.$paymentTableBody."</table>";
    echo $this->Form->input('powerselector2',['class'=>'powerselector','checked'=>false,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar facturas','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ],'div'=>['class'=>'input checkbox noprint']]);
  
    /*
    //style='visibility:hidden;position:absolute;'
    echo "<div >";
      echo "<h3>Comprobante para la transferencia</h3>";
      echo "<table id='accountingMovementsForRegister' >";
        echo "<thead>";
          echo "<tr>";
            echo "<th>".__('Code')."</th>";
            echo "<th>".__('Concept')."</th>";
            echo "<th class='centered'>".__('Debit')."</th>";
            echo "<th class='centered'>".__('Credit')."</th>";
            //echo "<th></th>";
          echo "</tr>";
        echo "</thead>";
      
        echo "<tbody>";
        for ($i=1;$i<=5;$i++) { 
          if ($i==1){
            echo "<tr class='debit'>";            
              echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',['options'=>$bankAccountingCodes,'label'=>false,'default'=>ACCOUNTING_CODE_BANK_CS,'empty' =>[0=>__('Choose Accounting Code')]])."</td>";
              echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',['label'=>false,'value'=>('Depósito '.$newTransferCode)])."</td>";
              
              echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',['class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal','id'=>"DebitAmount"])."</td>";
              echo "<td></td>";
              
              //echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
              
              //echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
            echo "</tr>";
          } 
          else {
            echo "<tr class='debit hidden'>";				
              echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('options'=>$bankAccountingCodes,'label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
              echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
              
              echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal'))."</td>";
              echo "<td></td>";
              
              //echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
              
              //echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
            echo "</tr>";
          } 
        }
          
        for ($i=6;$i<=10;$i++) { 
          if ($i==6){
            echo "<tr class='credit'>";
              echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',['label'=>false,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN,'empty' =>[0=>__('Choose Accounting Code')]])."</td>";
              echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',['label'=>false,'value'=>('Depósito '.$newTransferCode)])."</td>";
              echo "<td></td>";
              echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',['class'=>'accountingregisteramount','type'=>'decimal','label'=>false,'default'=>'0','id'=>"CreditAmount"])."</td>";
              //echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
            
            echo "</tr>";
          } 
          else {
            echo "<tr class='credit hidden'>";
           
              echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
              //echo "<td class='accountingcodename' style='width:25%;'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_description',array('id'=>'creditcode'.$i,'label'=>false,'default'=>'Descripción'))."</td>";
              echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
              echo "<td></td>";
              echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',array('class'=>'accountingregisteramount','type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
              //echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
              //echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
            echo "</tr>";
          }
          
        }
          //echo "<tr>";
          //  echo "<td></td>";
          //  echo "<td></td>";
          //  echo "<td class='centered'><button id='addDebitCode' type='button'>".__('Add Debit Code')."</button></td>";
          //  echo "<td class='centered'><button id='addCreditCode' type='button'>".__('Add Credit Code')."</button></td>";
          //  echo "<td></td>";
          //echo "</tr>";
          
          //echo "<tr id='accountingRegisterTotals' class='match'>";
          //  echo "<td></td>";
          //  echo "<td></td>";
          //  echo "<td id='debitTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
          //  echo "<td id='creditTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
          //  echo "<td></td>";
          //echo "</tr>";
        echo "</tbody>";
      echo "</table>";
    echo "</div>";  
    */
	echo "</div>";
  echo "</fieldset>";
  echo $this->Form->Submit(__('Submit')); 
  echo $this->Form->end(); 
?>
</div>

