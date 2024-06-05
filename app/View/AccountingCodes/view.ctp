<div class="accountingCodes view">
<?php 
	echo "<h2>";
	echo __('Accounting Code'); 
	if ($accountingCode['AccountingCode']['bool_main']){
		echo " (Mayor)"; 
	}
	else {
		echo " (Auxiliar)"; 
	}
	
	echo "</h2>";
?>
	<dl>
		<dt><?php echo __('Accounting Code'); ?></dt>
		<dd>
			<?php echo h($accountingCode['AccountingCode']['code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($accountingCode['AccountingCode']['description']); ?>
			&nbsp;
		</dd>
	<?php
		echo "<dt>".__('Naturaleza')."</dt>";
		if ($accountingCode['AccountingCode']['bool_creditor']){
			echo "<dd>Acreedora</dd>";
		}
		else {
			echo "<dd>Deudora</dd>";
		}
	?>
		<dt><?php echo __('Parent Accounting Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingCode['ParentAccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingCode['ParentAccountingCode']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Saldo')." (".date('d-m-Y').")"; ?></dt>
		<dd>
			<?php 
				echo "C$ ".number_format($saldo,2,".",",");
			?>
			&nbsp;
		</dd>
		<!--dt><?php echo __('Saldo US$'); ?></dt-->
		<!--dd>
			<?php 
				echo $saldo['amountUSD']." US$";
			?>
			&nbsp;
		</dd-->
	</dl>
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Accounting Code'), array('action' => 'edit', $accountingCode['AccountingCode']['id']))." </li>";
		}
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete Accounting Code'), array('action' => 'delete', $accountingCode['AccountingCode']['id']), array(), __('Are you sure you want to delete # %s?', $accountingCode['AccountingCode']['id']))." </li>";
		}
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Accounting Codes'), array('action' => 'index'))." </li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Code'), array('action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>

<div class="related">
<?php 
	if ($accountingCode['AccountingCode']['bool_main']){
		if (!empty($accountingCode['ChildAccountingCode'])){
			echo "<h3>".__('Child Accounting Codes')."</h3>";
			echo "<table cellpadding = '0' cellspacing = '0'>";
				echo "<tr>";
					echo "<th>".__('Accounting Code')."</th>";
					echo "<th>".__('Description')."</th>";
					echo "<th>".__('Saldo')."</th>";
					echo "<th class='actions'>".__('Actions')."</th>";
				echo "</tr>";
				foreach ($accountingCode['ChildAccountingCode'] as $childAccountingCode){
					//pr($childAccountingCode);
					echo "<tr>";
						echo "<td>".$this->Html->Link($childAccountingCode['code'],array('action'=>'view',$childAccountingCode['id']))."</td>";
						echo "<td>".$childAccountingCode['description']."</td>";
						echo "<td class='number'>C$ <span class='amountright'>".number_format($childAccountingCode['saldo'],2,".",",")."</span></td>";
						echo "<td class='actions'>";
							echo $this->Html->link(__('View'), array('controller' => 'accounting_codes', 'action' => 'view', $childAccountingCode['id'])); 
							echo $this->Html->link(__('Edit'), array('controller' => 'accounting_codes', 'action' => 'edit', $childAccountingCode['id'])); 
							// echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_codes', 'action' => 'delete', $childAccountingCode['id']), array(), __('Are you sure you want to delete # %s?', $childAccountingCode['id'])); 
						echo "</td>";
					echo "</tr>";
				}
			echo "</table>";
		}
	}
?>
</div>
<?php
	if (!$accountingCode['AccountingCode']['bool_main']){
		echo $this->Form->create('Report');
			echo "<fieldset>";
				echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
				echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
			echo "</fieldset>";
			echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
			echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
		echo $this->Form->end(__('Refresh')); 

		
		echo "<div class='related'>";
		
			echo "<h3>".__('Related Accounting Movements')."</h3>";
			echo "<table cellpadding='0' cellspacing='0'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>".__('Tipo')."</th>";
						echo "<th>".__('Register Date')."</th>";
						echo "<th>".__('Register Code')."</th>";
						echo "<th style='width:30%;'>".__('Concept')."</th>";
						if ($accountingCode['bankaccount']){
							echo "<th>Cheque no</th>";
							echo "<th>A favor de</th>";
						}
						echo "<th class='centered'>".__('Debit')."</th>";
						echo "<th class='centered'>".__('Credit')."</th>";
						echo "<th class='centered'>".__('Saldo')."</th>";
						//echo "<th class='actions'>".__('Actions')."</th>";
					echo "</tr>";
				echo "</thead>";
				
				$debit=0;
				$credit=0;			
				$runningSaldo=$saldoStartDate;
				
				echo "<tbody>";
					echo "<tr class='totalrow'>";
						echo "<td>Saldo Inicial</td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						if ($accountingCode['bankaccount']){
							echo "<td></td>";
							echo "<td></td>";
						}
						echo "<td></td>";
						echo "<td></td>";
						echo "<td class='number'>C$ <span class='amountright'>".number_format($saldoStartDate,2,".",",")."</span></td>";
						echo "<td></td>";
					echo "</tr>";
					//pr($accountingCode);
					foreach ($accountingCode['accountingMovements'] as $accountingMovement){
						//pr($accountingMovement);
						echo "<tr>";
							echo "<td>".$accountingRegisterTypes[$accountingMovement['AccountingRegister']['accounting_register_type_id']]."</td>";
							$registerDate=new DateTime($accountingMovement['AccountingRegister']['register_date']);
							echo "<td>".$registerDate->format('d-m-Y')."</td>";
							echo "<td>".$this->Html->Link($accountingMovement['AccountingRegister']['register_code'],array('controller'=>'accounting_registers','action'=>'view',$accountingMovement['AccountingRegister']['id']))."</td>";
							echo "<td>".$this->Html->Link($accountingMovement['AccountingRegister']['concept'],array('controller'=>'accounting_registers','action'=>'view',$accountingMovement['AccountingRegister']['id']))."</td>";
							if ($accountingCode['bankaccount']){
								if (!empty($accountingMovement['PaymentProof'])){
									echo "<td>".$accountingMovement['PaymentProof']['cheque_code']."</td>";
									echo "<td>".$accountingMovement['PaymentProof']['receiver']."</td>";
								}
								else {
									echo "<td>-</td>";
									echo "<td>-</td>";
								}
							}
							if ($accountingMovement['AccountingMovement']['bool_debit']){
								echo "<td class='number'>".$accountingMovement['Currency']['abbreviation']." <span class='amountright'>".number_format($accountingMovement['AccountingMovement']['total_amount'],2,".",",")."</span></td>";
								echo "<td class='centered'>-</td>";
								$debit+=$accountingMovement['AccountingMovement']['total_amount'];
								if ($accountingCode['AccountingCode']['bool_creditor']){
									$runningSaldo-=$accountingMovement['AccountingMovement']['total_amount'];
								}
								else {
									$runningSaldo+=$accountingMovement['AccountingMovement']['total_amount'];
								}
							}
							else {
								echo "<td class='centered'>-</td>";
								echo "<td class='number'>".$accountingMovement['Currency']['abbreviation']." <span class='amountright'>".number_format($accountingMovement['AccountingMovement']['total_amount'],2,".",",")."</span></td>";
								
								$credit+=$accountingMovement['AccountingMovement']['total_amount'];
								if ($accountingCode['AccountingCode']['bool_creditor']){
									$runningSaldo+=$accountingMovement['AccountingMovement']['total_amount'];
								}
								else {
									$runningSaldo-=$accountingMovement['AccountingMovement']['total_amount'];
								}
							}
							if (abs($runningSaldo)<0.001){
								$runningSaldo=0;
							}
							echo "<td class='number'>C$ <span class='amountright'>".number_format($runningSaldo,2,".",",")."</span></td>";
							//echo "<td class='actions'>";
							//	echo $this->Html->link(__('View'), array('controller' => 'accounting_registers', 'action' => 'view', $accountingMovement['AccountingRegister']['id'])); 
							//	echo $this->Html->link(__('Edit'), array('controller' => 'accounting_registers', 'action' => 'edit', $accountingMovement['AccountingRegister']['id'])); 
							//echo "</td>";
						echo "</tr>";
					}
					echo "<tr class='totalrow'>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td>Total Movimientos</td>";
						if ($accountingCode['bankaccount']){
							echo "<td></td>";
							echo "<td></td>";
						}
						echo "<td class='number'>C$ <span class='amountright'>".number_format($debit,2,".",",")."</span></td>";
						echo "<td class='number'>C$ <span class='amountright'>".number_format($credit,2,".",",")."</span></td>";
						echo "<td></td>";
						echo "<td></td>";
					echo "</tr>";
					echo "<tr class='totalrow'>";
						echo "<td>Saldo Final</td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						if ($accountingCode['bankaccount']){
							echo "<td></td>";
							echo "<td></td>";
						}
						echo "<td></td>";
						echo "<td></td>";
						echo "<td class='number'>C$ <span class='amountright'>".number_format($saldoEndDatePlusOne,2,".",",")."</span></td>";
						echo "<td></td>";
					echo "</tr>";
					
				echo "</tbody>";		
			echo "</table>";
			echo "<div class='actions'>";
				echo "<ul>";
					if ($bool_add_permission){
						echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
					}
				echo "</ul>";
			echo "</div>";
		
		echo "</div>";			

	}
