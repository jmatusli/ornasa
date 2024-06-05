<div class="transfers view">
<?php 
	echo "<h2>".__('Deposit')."</h2>";
	echo "<dl>";
		$transferDate=new DateTime($transfer['Transfer']['transfer_date']);
		echo "<dt>".__('Transfer Date')."</dt>";
		echo "<dd>".$transferDate->format('d-m-Y')."</dd>";
		echo "<dt>".__('Transfer Code')."</dt>";
		echo "<dd>".h($transfer['Transfer']['transfer_code'])."</dd>";
    echo "<dt>".__('Concept')."</dt>";
		echo "<dd>".h($transfer['Transfer']['concept'])."</dd>";
		//echo "<dt>".__('Cashbox Accounting Code')."</dt>";
		//echo "<dd>".$this->Html->link($transfer['CashboxAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['CashboxAccountingCode']['id']))."</dd>";
		echo "<dt>".__('Bank Accounting Code')."</dt>";
		echo "<dd>".$this->Html->link($transfer['BankAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['BankAccountingCode']['id']))."</dd>";
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>".$transfer['Currency']['abbreviation']." ".number_format($transfer['Transfer']['amount'],2,".",",")."</dd>";
		
		echo "<dt>".__('Accounting Register')."</dt>";
		echo "<dd>".$this->Html->link($transfer['AccountingRegister']['register_code']." ".$transfer['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $transfer['AccountingRegister']['id']))."</dd>";
	echo "</dl>";
  
  if (!empty($transfer['PaymentDeposit'])){
    //pr($transfer);
		echo "<h3>Pagos transferidos en este depósito</h3>";
    //pr($transfer['PaymentDeposit']);
		$paymentTable= "<table cellpadding = '0' cellspacing = '0' id='pagos_transferidos'>";
      $paymentTable.= "<thead>";
        $paymentTable.= "<tr>";
          $paymentTable.= "<th>Fecha</th>";
          $paymentTable.= "<th>Factura/Recibo de Caja</th>";
          $paymentTable.= "<th class='centered'>Monto depositado</th>";
        $paymentTable.= "</tr>";
      $paymentTable.= "</thead>";
      $paymentTable.= "<tbody>";
        $totalDeposited=0;
        foreach ($transfer['PaymentDeposit'] as $payment){
          if ($payment['Invoice']){
            $invoiceDateTime=new DateTime($payment['Invoice']['invoice_date']);
            $totalDeposited+=$payment['amount'];
            $paymentTable.= "<tr>";
              $paymentTable.= "<td>".$invoiceDateTime->format('d-m-Y')."</td>";
              $paymentTable.= "<td>".$this->Html->Link($payment['Invoice']['invoice_code'],array('controller'=>'orders','action'=>'verVenta',$payment['Invoice']['id']))."</td>";
              $paymentTable.= "<td class='centered ".($transfer['Transfer']['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$payment['amount']."</span></td>";
            $paymentTable.= "</tr>";
          }
          if ($payment['CashReceipt']){
            $receiptDateTime=new DateTime($payment['CashReceipt']['receipt_date']);
            $totalDeposited+=$payment['amount'];
            $paymentTable.= "<tr>";
              $paymentTable.= "<td>".$receiptDateTime->format('d-m-Y')."</td>";
              $paymentTable.= "<td>".$this->Html->Link($payment['CashReceipt']['receipt_code'],array('controller'=>'cash_receipts','action'=>'view',$payment['CashReceipt']['id']))."</td>";
              $paymentTable.= "<td class='centered ".($transfer['Transfer']['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$payment['amount']."</span></td>";
            $paymentTable.= "</tr>";
          }
        } 
        $paymentTable.= "<tr class='totalrow'>";
          $paymentTable.= "<td>Total</td>";
          $paymentTable.= "<td></td>";
          $paymentTable.= "<td class='centered  ".($transfer['Transfer']['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalDeposited."</span></td>";
        $paymentTable.= "</tr>";
      $paymentTable.= "</tbody>";
		$paymentTable.= "</table>";
		echo $paymentTable;
	}
	
	if (!empty($transfer['AccountingRegister'])){
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
		
		foreach ($transfer['AccountingRegister']['AccountingMovement'] as $accountingMovement){
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
		if ($bool_deposit_edit_permission) { 
			echo "<li>".$this->Html->link(__('Edit Deposit'), array('action' => 'editarDeposito', $transfer['Transfer']['id']))." </li>";
		}
		if ($bool_deposit_delete_permission) { 
			echo "<li>".$this->Form->postLink(__('Delete Deposit'), array('action' => 'eliminarDeposito', $transfer['Transfer']['id']), array(), __('Está seguro que quiere eliminar el depósito # %s?', $transfer['Transfer']['transfer_code']))." </li>";
		}
		echo "<li>".$this->Html->link(__('List Deposits'), array('action' => 'resumenDepositos'))." </li>";
		if ($bool_deposit_add_permission){
			echo "<li>".$this->Html->link(__('New Deposit'), array('action' => 'crearDeposito'))." </li>";
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