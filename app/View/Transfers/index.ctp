<div class="transfers index">
<?php 
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	
	echo "<h2>".__('Transfers')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('transfer_date')."</th>";
				echo "<th>".$this->Paginator->sort('transfer_code')."</th>";
				echo "<th>".$this->Paginator->sort('amount')."</th>";
				echo "<th>".$this->Paginator->sort('cashbox_accounting_code_id')."</th>";
				//echo "<th>".$this->Paginator->sort('bank_accounting_code_id')."</th>";
				echo "<th>".$this->Paginator->sort('accounting_register_id')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$totalCSAmount=0;
		$totalUSDAmount=0;
		foreach ($transfers as $transfer){
			echo "<tr>";
				$transferDate=new DateTime($transfer['Transfer']['transfer_date']);
				echo "<td>".$transferDate->format('d-m-Y')."</td>";
				echo "<td>".h($transfer['Transfer']['transfer_code'])."</td>";
				echo "<td>".$transfer['Currency']['abbreviation']." <span class='amountright'>".number_format($transfer['Transfer']['amount'],2,".",",")."</span></td>";
				if ($transfer['Currency']['id']==CURRENCY_CS){
					$totalCSAmount+=$transfer['Transfer']['amount'];
				}
				elseif ($transfer['Currency']['id']==CURRENCY_USD){
					$totalUSDAmount+=$transfer['Transfer']['amount'];
				}
				echo "<td>".$this->Html->link($transfer['CashboxAccountingCode']['description'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['CashboxAccountingCode']['id']))."</td>";
				//echo "<td>".$this->Html->link($transfer['BankAccountingCode']['description'], array('controller' => 'accounting_codes', 'action' => 'view', $transfer['BankAccountingCode']['id']))."</td>";
				echo "<td>".$this->Html->link($transfer['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $transfer['AccountingRegister']['id']))."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('action' => 'view', $transfer['Transfer']['id'])); 
					if ($bool_edit_permission) { 
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $transfer['Transfer']['id'])); 
					}
					if ($bool_delete_permission) { 
						echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $transfer['Transfer']['id']), array(), __('Are you sure you want to delete # %s?', $transfer['Transfer']['transfer_code'])); 
					}
				echo "</td>";
			echo "</tr>";
		}	
		if ($totalCSAmount>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
				echo "<td>C$ <span class='amountright'>".number_format($totalCSAmount,2,".",",")."</span></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
		}
		if ($totalUSDAmount>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
				echo "<td>C$ <span class='amountright'>".number_format($totalUSDAmount,2,".",",")."</span></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
?>
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Transfer'), array('action' => 'add'))."</li>";
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