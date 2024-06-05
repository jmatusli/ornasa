<div class="accountingRegisters form fullwidth">
<?php 
	echo $this->Form->create('AccountingRegister'); 
	echo "<fieldset>";
		echo "<legend>".__('Add Accounting Register')."</legend>";
		echo "<div class='righttop'>";
			echo $this->Form->input('amount',array('readonly'=>'readonly','type'=>'decimal'));
		echo "</div>";
		echo $this->Form->input('register_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('accounting_register_type_id');
		echo $this->Form->input('register_code',array('readonly'=>'readonly'));
		echo $this->Form->input('currency_id',array('id'=>'currencyId','default'=>CURRENCY_CS,'div'=>array('hidden'=>'hidden')));
		echo $this->Form->input('concept');
		echo $this->Form->input('observation');
		
		//echo $this->Form->input('bool_invoice');
		
		
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
			for ($i=1;$i<=20;$i++) { 
				if ($i==1){
					echo "<tr class='debit'>";
				} 
				else {
					echo "<tr class='debit hidden'>";
				} 
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					//echo "<td class='accountingcodename' style='width:25%;'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_description',array('id'=>'debitcode'.$i,'label'=>false,'default'=>'Descripción'))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false))."</td>";
					
					echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'default'=>'0','type'=>'decimal'))."</td>";
					echo "<td></td>";
					
					echo "<td><button class='removeDebitCode' type='button'>".__('Remove Debit Code')."</button></td>";
					
					//echo "<td class='invoiceid'>".$this->Form->input('AccountingMovement.'.$i.'.invoice_id',array('class'=>'invoice','label'=>false,'default'=>'0','empty' =>array(0=>__('Choose Invoice'))))."</td>";			
				echo "</tr>";
			}
				
			for ($i=21;$i<=40;$i++) { 
				if ($i==21){
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
	
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
	echo $this->Html->Link('Cancelar',array('action'=>'add'),array( 'class' => 'btn btn-primary cancel')); 
?>
</div>
<!--div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('action' => 'index')); ?></li>
	</ul>
</div-->

<script>
	$('#AccountingRegisterAccountingRegisterTypeId').change(function(){
		var accountingregistertypeid=parseInt($(this).children("option").filter(":selected").val());
		getregistercode(accountingregistertypeid);
	});	
	function getregistercode(accountingregistertypeid){
		if (accountingregistertypeid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>accounting_registers/getaccountingregistercode/',
				data:{"accountingregistertypeid":accountingregistertypeid},
				cache: false,
				type: 'POST',
				success: function (registercode) {
					$('#AccountingRegisterRegisterCode').val(registercode);
				},
				error: function(e){
					//$('#AccountingRegisterRegisterCode').val(e.responseText);
					console.log(e);
					alert(e.responseText);
				}
			});
		}
	}

	$('#currencyId').change(function(){
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
		$('#debitTotal span[class="amount"]').text(roundToTwo(totalCost));
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
		$('#creditTotal span[class="amount"]').text(roundToTwo(totalCost));
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
			$('#AccountingRegisterAmount').val(totalCredit);
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
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
	$(document).ready(function(){
		//formatNumbers();
		//formatCSCurrencies();
		$('.invoiceid').hide();
		// calculate the totals in case the page returns an error in controller
		calculateTotalDebit();
		calculateTotalCredit();
		getregistercode(<?php echo ACCOUNTING_REGISTER_TYPE_CD; ?>);
		
	});
</script>
