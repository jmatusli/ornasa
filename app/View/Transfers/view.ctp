<div class="transfers view">
<?php 
	echo "<h2>".__('Transfer')."</h2>";
	echo "<dl>";
		$transferDate=new DateTime($transfer['Transfer']['transfer_date']);
		echo "<dt>".__('Transfer Date')."</dt>";
		echo "<dd>".$transferDate->format('d-m-Y')."</dd>";
		echo "<dt>".__('Transfer Code')."</dt>";
		echo "<dd>".h($transfer['Transfer']['transfer_code'])."</dd>";
		echo "<dt>".__('Cashbox Accounting Code')."</dt>";
		echo "<dd>".$this->Html->link($transfer['CashboxAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['CashboxAccountingCode']['id']))."</dd>";
		//echo "<dt>".__('Bank Accounting Code')."</dt>";
		//echo "<dd>".$this->Html->link($transfer['BankAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['BankAccountingCode']['id']))."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".$transfer['Currency']['abbreviation']." ".number_format($transfer['Transfer']['amount'],2,".",",")."</dd>";
		
		echo "<dt>".__('Accounting Register')."</dt>";
		echo "<dd>".$this->Html->link($transfer['AccountingRegister']['register_code']." ".$transfer['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $transfer['AccountingRegister']['id']))."</dd>";
	echo "</dl>";
	
	//pr($accountingRegister);
	if (!empty($accountingRegister['AccountingMovement'])){
		echo "<h3>Comprobante ".$transfer['AccountingRegister']['register_code']."</h3>";
		$accountingMovementTable= "<table cellpadding = '0' cellspacing = '0' id='comprobante_pago'>";
		$accountingMovementTable.= "<tr>";
			$accountingMovementTable.= "<th>".__('Accounting Code')."</th>";
			$accountingMovementTable.= "<th>".__('Description')."</th>";
			$accountingMovementTable.= "<th>".__('Concept')."</th>";
			$accountingMovementTable.= "<th class='centered'>".__('Debe')."</th>";
			$accountingMovementTable.= "<th class='centered'>".__('Haber')."</th>";
			//$accountingMovementTable.= "<th></th>";
		$accountingMovementTable.= "</tr>";
		
		$totalDebit=0;
		$totalCredit=0;
		
		foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
			//pr($accountingMovement);
			$accountingMovementTable.= "<tr>";
				$accountingMovementTable.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
				$accountingMovementTable.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
				$accountingMovementTable.= "<td>".$accountingMovement['concept']."</td>";
				
				if ($accountingMovement['bool_debit']){
					$accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
					$accountingMovementTable.= "<td class='centered'>-</td>";
					$totalDebit+=$accountingMovement['amount'];
				}
				else {
					$accountingMovementTable.= "<td class='centered'>-</td>";
					$accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
					$totalCredit+=$accountingMovement['amount'];
				}
				//$accountingMovementTable.= "<td>".($accountingMovement['bool_debit']?__('Debe'):__('Haber'))."</td>";
				//$accountingMovementTable.= "<td class='actions'>";
					//$accountingMovementTable.= $this->Html->link(__('View'), array('controller' => 'accounting_movements', 'action' => 'view', $accountingMovement['id'])); 
					//$accountingMovementTable.= $this->Html->link(__('Edit'), array('controller' => 'accounting_movements', 'action' => 'edit', $accountingMovement['id'])); 
					//$accountingMovementTable.= $this->Form->postLink(__('Delete'), array('controller' => 'accounting_movements', 'action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['id'])); 
				//$accountingMovementTable.= "</td>";
			$accountingMovementTable.= "</tr>";
		} 
			$accountingMovementTable.= "<tr class='totalrow'>";
				$accountingMovementTable.= "<td>Total</td>";
				$accountingMovementTable.= "<td></td>";
				$accountingMovementTable.= "<td></td>";
				$accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalDebit."</span></td>";
				$accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalCredit."</span></td>";
			$accountingMovementTable.= "</tr>";
		$accountingMovementTable.= "</table>";
		echo $accountingMovementTable;
	}
?>
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission) { 
			echo "<li>".$this->Html->link(__('Edit Transfer'), array('action' => 'edit', $transfer['Transfer']['id']))." </li>";
		}
		if ($bool_delete_permission) { 
			echo "<li>".$this->Form->postLink(__('Delete Transfer'), array('action' => 'delete', $transfer['Transfer']['id']), array(), __('Are you sure you want to delete # %s?', $transfer['Transfer']['transfer_code']))." </li>";
		}
		echo "<li>".$this->Html->link(__('List Transfers'), array('action' => 'index'))." </li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Transfer'), array('action' => 'add'))." </li>";
		}	
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
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,2);
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("US$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>