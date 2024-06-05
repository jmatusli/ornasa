<div class="accountingRegisters view">
<h2><?php echo __('Accounting Register'); ?></h2>
	<dl>
	<?php
		//pr($accountingRegister);
		echo "<dt>".__('Date')."</dt>";
		$registerDate=new DateTime($accountingRegister['AccountingRegister']['register_date']);
		echo "<dd>".$registerDate->format('d-m-Y')."</dd>";
		
		echo "<dt>".__('Tipo Comprobante')."</dt>";
		if (!empty($accountingRegister['AccountingRegisterType']['name'])){
			echo "<dd>".h($accountingRegister['AccountingRegisterType']['name'])."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		
		echo "<dt>".__('Register Code')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['register_code'])."</dd>";
		
		echo "<dt>".__('Amount')."</dt>";
		echo "<dd>C$ ".number_format($accountingRegister['AccountingRegister']['amount'],2,".",",")."</dd>";
		
		echo "<dt>".__('Concept')."</dt>";
		echo "<dd>".h($accountingRegister['AccountingRegister']['concept'])."</dd>";
		
		if (!empty($accountingRegister['AccountingRegister']['observation'])){
			echo "<dt>".__('Observation')."</dt>";
			echo "<dd>".$accountingRegister['AccountingRegister']['observation']."</dd>";
		}
	?>
	
	</dl>
	<?php 
		echo "<div class='righttop'>".$this->Html->link(__('Guardar como pdf'), array('action' => 'viewPdf','ext'=>'pdf',$accountingRegister['AccountingRegister']['id'],$filename),array( 'class' => 'btn btn-primary'))."</div>";
	?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Accounting Register'), array('action' => 'edit', $accountingRegister['AccountingRegister']['id']))." </li>";
			echo "<br/>";
		}
		//echo "<li>".$this->Form->postLink(__('Delete Accounting Register'), array('action' => 'delete', $accountingRegister['AccountingRegister']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegister['AccountingRegister']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Accounting Registers'), array('action' => 'index'))." </li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('action' => 'add'))." </li>";
		}
		
	echo "</ul>";
?>
</div>
<div class="related">
	<h3><?php echo __('Related Accounting Movements'); ?></h3>
<?php 
	if (!empty($accountingRegister['AccountingMovement'])){
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