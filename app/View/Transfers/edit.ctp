<div class="transfers form">
<?php echo $this->Form->create('Transfer'); ?>
	<fieldset>
		<legend><?php echo __('Edit Transfer'); ?></legend>
	<?php
		echo "<div class='righttop'>";
			echo "<div>Saldo para caja: <span id='cashboxSaldo' class='amountright'></span></div>";
		echo "</div>";
		echo $this->Form->input('cashbox_accounting_code_id');
		//echo $this->Form->input('bank_accounting_code_id');
		echo $this->Form->input('transfer_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('transfer_code',array('class'=>'narrow','readonly'=>'readonly'));
		echo $this->Form->input('amount',array('class'=>'narrow'));
		echo $this->Form->input('currency_id');
		echo "<h3>Comprobante para la transferencia</h3>";
		echo "<table id='accountingMovementsForRegister'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Code')."</th>";
					echo "<th>".__('Concept')."</th>";
					echo "<th class='centered'>".__('Debit')."</th>";
					echo "<th class='centered'>".__('Credit')."</th>";
					echo "<th></th>";
				echo "</tr>";
			echo "</thead>";
		
			echo "<tbody>";
			$debitTotal=0;
			$creditTotal=0;
			//pr($debitMovementsAlreadyInAccountingRegister);
			for ($i=0;$i<count($debitMovementsAlreadyInAccountingRegister);$i++) { 
				$debitAmount=$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['amount'];
				if ($this->request->data['Transfer']['currency_id']==CURRENCY_USD){
					$debitAmount=round($debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['amount']/$appliedExchangeRate['ExchangeRate']['rate'],2);
				}
				echo "<tr class='debit'>";
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('options'=>$bankAccountingCodes,'label'=>false,'default'=>$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['accounting_code_id'],'empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false,'default'=>$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['concept']))."</td>";
					echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'type'=>'decimal','default'=>$debitAmount))."</td>";
					echo "<td></td>";
					echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
				echo "</tr>";
				
				$debitTotal+=$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['amount'];
			}
			$startingposition=count($debitMovementsAlreadyInAccountingRegister);
			
			for ($i=$startingposition;$i<20;$i++) { 
				if ($i==$startingposition){
					echo "<tr class='debit'>";
				} 
				else {
					echo "<tr class='debit hidden'>";
				} 
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('options'=>$bankAccountingCodes,'label'=>false,'value'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false,'value'=>''))."</td>";
					echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal'))."</td>";
					echo "<td></td>";
					echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
				
					//echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";
					
				echo "</tr>";
			}
			$startingposition=20;
			//pr($creditMovementsAlreadyInAccountingRegister);
			for ($i=$startingposition;$i<($startingposition+count($creditMovementsAlreadyInAccountingRegister));$i++) { 
				$creditAmount=$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['amount'];
				if ($this->request->data['Transfer']['currency_id']==CURRENCY_USD){
					$creditAmount=round($debitMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['amount']/$appliedExchangeRate['ExchangeRate']['rate'],2);
				}
				echo "<tr class='credit'>";
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'default'=>$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['accounting_code_id'],'empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false,'default'=>$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['concept']))."</td>";
					echo "<td></td>";
					echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',array('class'=>'accountingregisteramount','label'=>false,'type'=>'decimal','default'=>$creditAmount))."</td>";
					echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
				echo "</tr>";
				$creditTotal+=$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['amount'];
			}
			$startingposition+=count($creditMovementsAlreadyInAccountingRegister);
			for ($i=$startingposition;$i<40;$i++) { 
				$invoiceid=0;
				
				if ($i==$startingposition){
					echo "<tr class='credit'>";
				} 
				else {
					echo "<tr class='credit hidden'>";
				} 
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'value'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
					echo "<td></td>";
					echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',array('class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal'))."</td>";
					echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
				echo "</tr>";
			}
				echo "<tr>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td class='centered'><button id='addDebitCode' type='button'>".__('Add Debit Code')."</button></td>";
					echo "<td class='centered'><button id='addCreditCode' type='button'>".__('Add Credit Code')."</button></td>";
					echo "<td></td>";
				echo "</tr>";
				
				echo "<tr id='accountingRegisterTotals' class='match'>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td id='debitTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
					echo "<td id='creditTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
					echo "<td></td>";
				echo "</tr>";
			
			echo "</tbody>";
		
		echo "</table>";
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission) { 
			echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Transfer.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Transfer.transfer_code')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Transfers'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_accountingcode_index_permission){
			echo "<li>".$this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index'))." </li>";
		}
		if ($bool_accountingcode_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add'))." </li>";
			echo "<br/>";
		}
		if ($bool_accountingregister_index_permission){
			echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))." </li>";
		}
		if ($bool_accountingregister_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
<script>
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
					console.log(e);
				}
			});
		}
	}
	
	$('#TransferCurrencyId').change(function(){
		var currency=$(this).children("option").filter(":selected").text();
		$("span.currency").each(function() {
			$(this).text(currency);
		});
	});	
	
	$('.debitamount').change(function(){
		calculateTotalDebit();
	});	
	
	$('.creditamount').change(function(){
		calculateTotalCredit();
	});	

	
	$('#addDebitCode').click(function(){
		var tableRow=$('#accountingMovementsForRegister tbody tr.debit.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('.removeDebitCode').click(function(){
		$(this).parent().parent().remove();
		calculateTotalDebit();
	});	
	
	$('#addCreditCode').click(function(){
		var tableRow=$('#accountingMovementsForRegister tbody tr.credit.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('.removeCreditCode').click(function(){
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
	
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
			//$('#AccountingRegisterAddForm').submit();
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}

	$( document ).ajaxComplete(function() {
		formatCurrencies();
	});	
	
	function formatCurrencies(){
		$(".amountright").each(function(){
			var boolnegative=false;
			if ($(this).text()<0){
				boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("C$ -");
			}
			else {
				$(this).prepend("C$ ");
			}
		});
	}
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
	$(document).ready(function(){
		calculateCashboxSaldo();
		calculateTotalDebit();
		calculateTotalCredit();
	});
</script>
