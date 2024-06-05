<div class="cheques index">
<?php 
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo $this->Form->input('Report.bank_accounting_code_id',array('label'=>__('Banco'),'options'=>$bankAccountingCodes,'default'=>$bank_accounting_code_id,'empty'=>array('0'=>'Seleccione Banco')));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenCheques'), array( 'class' => 'btn btn-primary')); 
	
?>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Cheque'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
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
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
?>
</div>
<div>
<?php
$excelOutput="";

	foreach ($selectedBankAccountingCodes as $bank){
		$pageHeader="<thead>";
			$pageHeader.="<tr>";
				$pageHeader.="<th>".$this->Paginator->sort('cheque_date')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('cheque_code')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('receiver_name')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('concept')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('amount')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('accounting_register_id')."</th>";
				$pageHeader.="<th>Gastos Admon</th>";
				$pageHeader.="<th>Sueldos/Salarios</th>";
				$pageHeader.="<th>Gastos Ventas</th>";
				$pageHeader.="<th>Inventario</th>";
				$pageHeader.="<th>Costos Producción</th>";
				$pageHeader.="<th>Costos Financieros</th>";
				$pageHeader.="<th>Otros</th>";
				$pageHeader.="<th class='actions'>".__('Actions')."</th>";
			$pageHeader.="</tr>";
		$pageHeader.="</thead>";
		$excelHeader="<thead>";
			$excelHeader.="<tr>";
				$excelHeader.="<th>".$this->Paginator->sort('cheque_date')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('cheque_code')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('receiver_name')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('concept')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('amount')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('accounting_register_id')."</th>";
				$excelHeader.="<th>Gastos Admon</th>";
				$excelHeader.="<th>Sueldos/Salarios</th>";
				$excelHeader.="<th>Gastos Ventas</th>";
				$excelHeader.="<th>Inventario</th>";
				$excelHeader.="<th>Costos Producción</th>";
				$excelHeader.="<th>Costos Financieros</th>";
				$excelHeader.="<th>Otros</th>";
			$excelHeader.="</tr>";
		$excelHeader.="</thead>";
		
		$pageBody="";
		$excelBody="";
		
		$total_CS=0;
		$total_admin_CS=0;
		$total_salaries_CS=0;
		$total_sales_CS=0;
		$total_inventory_CS=0;
		$total_production_CS=0;
		$total_finance_CS=0;
		$total_other_CS=0;
		$total_USD=0;
		$total_admin_USD=0;
		$total_salaries_USD=0;
		$total_sales_USD=0;
		$total_inventory_USD=0;
		$total_production_USD=0;
		$total_finance_USD=0;
		$total_other_USD=0;
		foreach ($bank['Cheques'] as $cheque) {
			$admin=0;
			$salaries=0;
			$sales=0;
			$inventory=0;
			$production=0;
			$finance=0;
			$other=0;
			//pr($cheque);
			$chequeDate=new DateTime($cheque['Cheque']['cheque_date']);
			$filename=$filename='Cheque_'.$cheque['Cheque']['cheque_code'];
			if ($cheque['Currency']['id']==CURRENCY_CS){
				$currencyClass="CScurrency";
				$total_CS+=$cheque['Cheque']['amount'];
				foreach ($cheque['AccountingRegister']['AccountingMovement'] as $accountingMovement){
					if ($accountingMovement['bool_debit']){
						if ($accountingMovement['AccountingCode']['lft']>$adminAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$adminAccountingCode['AccountingCode']['rght']){
							$admin+=$accountingMovement['amount'];
							$total_admin_CS+=$accountingMovement['amount'];
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$salariesAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$salariesAccountingCode['AccountingCode']['rght']){
							$salaries+=$accountingMovement['amount'];
							$total_salaries_CS+=$accountingMovement['amount'];
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$salesAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$salesAccountingCode['AccountingCode']['rght']){
							$sales+=$accountingMovement['amount'];
							$total_sales_CS+=$accountingMovement['amount'];
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$inventoryAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$inventoryAccountingCode['AccountingCode']['rght']){
							$inventory+=$accountingMovement['amount'];
							$total_inventory_CS+=$accountingMovement['amount'];
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$productionAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$productionAccountingCode['AccountingCode']['rght']){
							$production+=$accountingMovement['amount'];
							$total_production_CS+=$accountingMovement['amount'];
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$financeAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$financeAccountingCode['AccountingCode']['rght']){
							$finance+=$accountingMovement['amount'];
							$total_finance_CS+=$accountingMovement['amount'];
						}
						else {
							$other+=$accountingMovement['amount'];
							$total_other_CS+=$accountingMovement['amount'];
						}
					}
				}
			}
			elseif ($cheque['Currency']['id']==CURRENCY_USD){
				$currencyClass="USDcurrency";
				$total_USD+=$cheque['Cheque']['amount'];
				foreach ($cheque['AccountingRegister']['AccountingMovement'] as $accountingMovement){
					if ($accountingMovement['bool_debit']){
						if ($accountingMovement['AccountingCode']['lft']>$adminAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$adminAccountingCode['AccountingCode']['rght']){
							$admin+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_admin_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$salariesAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$salariesAccountingCode['AccountingCode']['rght']){
							$salaries+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_salaries_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$salesAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$salesAccountingCode['AccountingCode']['rght']){
							$sales+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_sales_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$inventoryAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$inventoryAccountingCode['AccountingCode']['rght']){
							$inventory+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_inventory_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$productionAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$productionAccountingCode['AccountingCode']['rght']){
							$production+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_production_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						elseif ($accountingMovement['AccountingCode']['lft']>$financeAccountingCode['AccountingCode']['lft']&&$accountingMovement['AccountingCode']['rght']<$financeAccountingCode['AccountingCode']['rght']){
							$finance+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_finance_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
						else {
							$other+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
							$total_other_USD+=$cheque['Cheque']['amount']*($accountingMovement['amount']/$cheque['AccountingRegister']['amount']);
						}
					}
				}
			}
			
			
				$pageRow="<td>".$chequeDate->format('d-m-Y')."&nbsp;</td>";
				$pageRow.="<td>".h($cheque['Cheque']['cheque_code'])."&nbsp;</td>";
				$pageRow.="<td>".h($cheque['Cheque']['receiver_name'])."&nbsp;</td>";
				$pageRow.="<td>".h($cheque['Cheque']['concept'])."&nbsp;</td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$cheque['Cheque']['amount']."</span></td>";
				$pageRow.="<td>".$this->Html->link($cheque['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $cheque['AccountingRegister']['id']))."</td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$admin."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$salaries."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$sales."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$inventory."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$production."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$finance."</span></td>";
				$pageRow.="<td class='".$currencyClass."'><span class='currency'></span><span class='amountright'>".$other."</span></td>";
				
				$excelBody.="<tr>".$pageRow."</tr>";
				
				
				$pageRow.="<td class='actions'>";
					$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $cheque['Cheque']['id']));
					if ($bool_edit_permission) { 
						$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $cheque['Cheque']['id']));
					}
					if ($bool_delete_permission) { 
						$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $cheque['Cheque']['id']), array(), __('Are you sure you want to delete # %s?', $cheque['Cheque']['cheque_code']));
					}
					$pageRow.=$this->Html->link(__('Pdf'), array('action' => 'viewPdf','ext'=>'pdf',$cheque['Cheque']['id'],$filename));
				$pageRow.="</td>";
			$pageBody.="<tr>".$pageRow."</tr>";
		}
		$pageTotalRow="";
		if ($total_CS>0){
			$pageTotalRow.="<tr class='totalrow'>";
				$pageTotalRow.="<td>Total C$</td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_CS."</span></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_admin_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_salaries_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_sales_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_inventory_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_production_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_finance_CS."</span></td>";
				$pageTotalRow.="<td class='number CScurrency'><span class='currency'></span><span class='amountright'>".$total_other_CS."</span></td>";
				$pageTotalRow.="<td></td>";
			$pageTotalRow.="</tr>";
		}
		if ($total_USD>0){
			$pageTotalRow.="<tr class='totalrow'>";
				$pageTotalRow.="<td>Total US$</td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_USD."</span></td>";
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_admin_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_salaries_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_sales_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_inventory_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_production_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_finance_USD."</span></td>";
				$pageTotalRow.="<td class='number USDcurrency'><span class='currency'></span><span class='amountright'>".$total_other_USD."</span></td>";
				$pageTotalRow.="<td></td>";
			$pageTotalRow.="</tr>";
		}
		$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
		
		echo "<h2>".__('Cheques para banco ')." ".$bank['BankAccountingCode']['description']."</h2>";
		$table_id=$bank['BankAccountingCode']['description'];
		$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
		echo $pageOutput;
		$excelOutput.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	}
	$_SESSION['resumenCheques'] = $excelOutput;
?>
</div>
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});

</script>