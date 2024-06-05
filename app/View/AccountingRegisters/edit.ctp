<div class="accountingRegisters form fullwidth">
<?php 
	echo $this->Form->create('AccountingRegister'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit Accounting Register')."</legend>";
		echo "<div class='righttop'>";
			echo $this->Form->input('amount',array('readonly'=>'readonly','type'=>'decimal'));
		echo "</div>";
		
		echo $this->Form->input('id');
		echo $this->Form->input('register_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('accounting_register_type_id');
		echo $this->Form->input('register_code');
		echo $this->Form->input('currency_id',array('id'=>'currencyId','div'=>array('hidden'=>'hidden')));
		echo $this->Form->input('concept');
		echo $this->Form->input('observation');
		
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
				//echo "accounting code to select is ".$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['accounting_code_id']."<br/>";
				//echo "concept to display is ".$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['concept']."<br/>";
				echo "<tr class='debit'>";
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'value'=>$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['accounting_code_id'],'empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false,'value'=>$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['concept']))."</td>";
					echo "<td class='debitamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.debit_amount',array('class'=>'accountingregisteramount','label'=>false,'type'=>'decimal','default'=>$debitMovementsAlreadyInAccountingRegister[$i]['AccountingMovement']['amount']))."</td>";
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
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'value'=>'0','empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
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
				echo "<tr class='credit'>";
					echo "<td class='accountingcodeid'>".$this->Form->input('AccountingMovement.'.$i.'.accounting_code_id',array('label'=>false,'value'=>$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['accounting_code_id'],'empty' =>array(0=>__('Choose Accounting Code'))))."</td>";
					echo "<td class='concept'>".$this->Form->input('AccountingMovement.'.$i.'.concept',array('label'=>false,'value'=>$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['concept']))."</td>";
					echo "<td></td>";
					echo "<td class='creditamount centered'><span class='currency'>C$ </span>".$this->Form->input('AccountingMovement.'.$i.'.credit_amount',array('class'=>'accountingregisteramount','label'=>false,'type'=>'decimal','default'=>$creditMovementsAlreadyInAccountingRegister[$i-$startingposition]['AccountingMovement']['amount']))."</td>";
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
			
			if ($debitTotal==$creditTotal){
				echo "<tr id='accountingRegisterTotals' class='match'>";
			}
			else {
				echo "<tr id='accountingRegisterTotals' class='match nomatch'>";
			}
				echo "<td></td>";
				echo "<td></td>";
				echo "<td id='debitTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>".$debitTotal."</span></td>";
				echo "<td id='creditTotal' class='centered'><span class='currency'>C$ </span><span class='amount'>".$creditTotal."</span></td>";
				echo "<td></td>";
			echo "</tr>";
			
			echo "</tbody>";
		
		echo "</table>";
	
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
	echo $this->Html->Link('Cancelar',array('action'=>'edit',$id),array( 'class' => 'btn btn-primary cancel'));
?>
</div>
<div class='actions'>
<?php 	
	//echo "<h3>".__('Actions')."</h3>";
	//echo "<ul>";
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingRegister.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingRegister.id')))."</li>";
		}
		//echo "<li>".$this->Html->link(__('List Accounting Registers'), array('action' => 'index'))."</li>";
	//echo "</ul>";
?>
	
</div>

<script>
	$('#currencyId').change(function(){
		var currency=$(this).children("option").filter(":selected").text();
		$("span.currency").each(function() {
			$(this).text(currency);
		});
	});	
	
	$('#AccountingRegisterBoolInvoice').change(function() {
		if ($(this).is(":checked")){
			$('.invoiceid').show();
		}
		else {
			$('.invoiceid').hide();
		}
    });
	
	$('.debitamount').change(function(){
		//var amount=parseFloat($(this).find('div input').val());
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
	
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
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
	
	function displayCurrencies(){
		var currency=$('#currencyId').children("option").filter(":selected").text()+" ";
		$("span.currency").each(function() {
			$(this).text(currency);
		});
	}
	
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
	$(document).ready(function(){
		//displayCodeDescriptions();
		displayCurrencies();
		calculateTotalDebit();
		calculateTotalCredit();
		/*
		var boolinvoice=<?php echo (empty($this->request->data['AccountingRegister']['bool_invoice'])?"0":"1"); ?>;
		if (!boolinvoice){
			$('.invoiceid').hide();
		}
		*/
	});
</script>
