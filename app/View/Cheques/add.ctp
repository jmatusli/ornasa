<div class="cheques form">
<?php echo $this->Form->create('Cheque'); ?>
	<fieldset>
		<legend><?php echo __('Add Cheque'); ?></legend>
	<?php
		echo "<div class='righttop'>";
			echo "<div id='currentSaldo' class='hidden'>Saldo Cuenta Bancaria: <span id='accountSaldo' class='amountright'></span></div>";
			echo $this->Form->input('amount',array('class'=>'with60','readonly'=>'readonly','type'=>'decimal'));
		echo "</div>";
		echo $this->Form->input('bank_accounting_code_id',array('default'=>'0','empty'=>array('0'=>'Seleccione cuenta bancaria')));
		echo $this->Form->input('cheque_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('cheque_code',array('class'=>'narrow'));
		echo $this->Form->input('receiver_name',array('class'=>'narrow'));
		
		echo $this->Form->input('currency_id',array('default'=>CURRENCY_CS));
		echo $this->Form->input('concept');
		echo $this->Form->input('observation');
		//echo $this->Form->input('purchase_id',array('default'=>'0','empty'=>array('0'=>'Seleccione la compra')));
		echo "<h3>Comprobante para el cheque</h3>";
		echo "<table id='accountingMovementsForRegister' style='font-size:95%'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Accounting Code')."</th>";
					echo "<th>".__('Concept')."</th>";
					echo "<th class='centered'>".__('Debit')."</th>";
					//echo "<th class='centered'>".__('Credit')."</th>";
					echo "<th></th>";
				echo "</tr>";
			echo "</thead>";
		
			echo "<tbody>";
			for ($i=0;$i<20;$i++) { 
				if ($i==0){
					echo "<tr class='debit'>";
				} 
				else {
					echo "<tr class='debit hidden'>";
				} 
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					//echo "<td class='accountingcodename' style='width:25%;'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_description',array('id'=>'debitcode'.$i,'label'=>false,'default'=>'Descripción'))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
					
					echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal'))."</td>";
					//echo "<td></td>";
					
					echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
					
					//echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
				echo "</tr>";
			}
			/*	
			for ($i=20;$i<40;$i++) { 
				if ($i==20){
					echo "<tr class='credit'>";
				} 
				else {
					echo "<tr class='credit hidden'>";
				} 
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					//echo "<td class='accountingcodename' style='width:25%;'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_description',array('id'=>'creditcode'.$i,'label'=>false,'default'=>'Descripción'))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
					echo "<td></td>";
					echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',array('class'=>'accountingregisteramount','type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
					echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
					
					//echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
				echo "</tr>";
			}
			*/
				echo "<tr class='credit hidden'>";
				//echo "<tr class='credit'>";

					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.20.accounting_code_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					//echo "<td class='accountingcodename' style='width:25%;'>".$this->Form->input('AccountingMovement.20.accounting_code_description',array('id'=>'creditcode20,'label'=>false,'default'=>'Descripción'))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.20.concept',array('label'=>false))."</td>";
					echo "<td></td>";
					echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.20.credit_amount',array('class'=>'accountingregisteramount','type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
					echo "<td><button class='removeCreditCode' type='button'>".__('Remove Credit Code')."</button></td>";
					
					//echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.20.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
				echo "</tr>";
				echo "<tr>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td class='centered'><button id='addDebitCode' type='button'>".__('Add Debit Code')."</button></td>";
					//echo "<td class='centered'><button id='addCreditCode' type='button'>".__('Add Credit Code')."</button></td>";
					echo "<td></td>";
				echo "</tr>";
				
				echo "<tr id='accountingRegisterTotals' class='match'>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td id='debitTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
					echo "<td id='creditTotal' class='centered hidden'><span class='currency'>C$ </span><span class='amount'>0</span></td>";
					echo "<td></td>";
				echo "</tr>";
			
			echo "</tbody>";
		
		echo "</table>";
		
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Cheques'), array('action' => 'index'))."</li>";
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
	$('#ChequeBankAccountingCodeId').change(function(){
		getsaldo();
		getchequenumber();
		$('#AccountingMovement20AccountingCodeId').val($(this).val());
		if ($(this).val()==<?php echo ACCOUNTING_CODE_BANK_CS; ?>){
			$('#ChequeCurrencyId').val('<?php echo CURRENCY_CS; ?>');
			$('#ChequeCurrencyId').addClass('fixedselection');
			fixSelections();
			$('#ChequeCurrencyId').trigger('change');
		}
		else {
			if ($(this).val()==<?php echo ACCOUNTING_CODE_BANK_USD; ?>){
				$('#ChequeCurrencyId').val('<?php echo CURRENCY_USD; ?>');
				$('#ChequeCurrencyId').addClass('fixedselection');
				fixSelections();
				$('#ChequeCurrencyId').trigger('change');
			}
			else {
				$('ChequeCurrencyId').removeClass('fixedselection');
			}
		}
	});	
	function fixSelections(){
		$('select.fixedselection option:not(:selected)').attr('disabled', true);
	}
	
	$('#ChequeChequeDateDay').change(function(){
		getsaldo();
	});	
	$('#ChequeChequeDateMonth').change(function(){
		getsaldo();
	});	
	$('#ChequeChequeDateYear').change(function(){
		getsaldo();
	});	
	function getsaldo(){
		var bank_account_id=parseInt($('#ChequeBankAccountingCodeId').children("option").filter(":selected").val());
		var accounting_code_day=parseInt($('#ChequeChequeDateDay').val());
		var accounting_code_month=parseInt($('#ChequeChequeDateMonth').val());
		var accounting_code_year=parseInt($('#ChequeChequeDateYear').val());
		
		var nextday=new Date(accounting_code_year,accounting_code_month-1,accounting_code_day);
		var datenextday=nextday.addDays(1);
		var day_next_day=datenextday.getDate();
		var month_next_day=datenextday.getMonth()+1;
		var year_next_day=datenextday.getFullYear();
		
		
		if (bank_account_id>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>accounting_codes/getaccountsaldo/',
				data:{"accounting_code_id":bank_account_id,"accounting_code_day":day_next_day,"accounting_code_month":month_next_day,"accounting_code_year":year_next_day},
				cache: false,
				type: 'POST',
				success: function (saldo) {
					$('#accountSaldo').parent().removeClass('hidden');
					$('#accountSaldo').text(saldo);
					formatCurrencies();
				},
				error: function(e){
					
					$('#accountSaldo').parent().removeClass('hidden');
					$('#accountSaldo').text(e.responseText);
					console.log(e);
				}
			});
		}
	}
	function getchequenumber(){
		var bank_account_id=parseInt($('#ChequeBankAccountingCodeId').children("option").filter(":selected").val());
		if (bank_account_id>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>cheques/getchequenumber/',
				data:{"bank_account_id":bank_account_id},
				cache: false,
				type: 'POST',
				success: function (chequenumber) {
					$('#ChequeChequeCode').val(chequenumber);
				},
				error: function(e){
					$('#ChequeChequeCode').val(e.responseText);
					console.log(e);
				}
			});
		}
	}
	
	$('#ChequeCurrencyId').on('change',function(){
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
		$('#AccountingMovement20CreditAmount').val(totalCost);
		$('#creditTotal span[class="amount"]').text(totalCost);
		checkBalance();
		return false;
	}
	/*
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
	*/
	
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
			$('#ChequeAmount').val(totalCredit);
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
	
	function formatCurrencies(){
		$("span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("-");
			}
			$(this).prepend("C$ ");
		});
	}
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>