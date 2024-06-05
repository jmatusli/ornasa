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
		<dt><?php echo __('Parent Accounting Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($accountingCode['ParentAccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $accountingCode['ParentAccountingCode']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Saldo C$'); ?></dt>
		<dd>
			<?php 
				echo number_format($saldo['amountCS'],2,".",",")." C$";
			?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Accounting Code'), array('action' => 'edit', $accountingCode['AccountingCode']['id'])); ?> </li>
		<!--li><?php //echo $this->Form->postLink(__('Delete Accounting Code'), array('action' => 'delete', $accountingCode['AccountingCode']['id']), array(), __('Are you sure you want to delete # %s?', $accountingCode['AccountingCode']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('action' => 'add')); ?> </li>
		<br/>
	</ul>
</div>
<div class="related">
	<?php if (!empty($accountingCode['ChildAccountingCode'])): ?>
	<h3><?php echo __('Child Accounting Codes'); ?></h3>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Accounting Code'); ?></th>
		<th><?php echo __('Description'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($accountingCode['ChildAccountingCode'] as $childAccountingCode): ?>
		<tr>
	
			<td><?php echo $this->Html->Link($childAccountingCode['code'],array('action'=>'view',$childAccountingCode['id'])); ?></td>
			<td><?php echo $childAccountingCode['description']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'accounting_codes', 'action' => 'view', $childAccountingCode['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'accounting_codes', 'action' => 'edit', $childAccountingCode['id'])); ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'accounting_codes', 'action' => 'delete', $childAccountingCode['id']), array(), __('Are you sure you want to delete # %s?', $childAccountingCode['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>
<?php

	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
		echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
?>



<div class="related">
	
<?php 
	if (!empty($accountingCode['accountingMovements'])){
		
		echo "<h3>".__('Related Accounting Movements')."</h3>";
		echo "<table cellpadding='0' cellspacing='0'>";
		echo "<tr>";
			echo "<th>".__('Register Date')."</th>";
			echo "<th style='width:50%;'>".__('Concept')."</th>";
			echo "<th>".__('Debit')."</th>";
			echo "<th>".__('Credit')."</th>";
			echo "<th>".__('Saldo')."</th>";
			echo "<th class='actions'>".__('Actions')."</th>";
		echo "</tr>";
		$runningSaldo=$saldoStartDate['amountCS'];
		echo "<tr>";
			echo "<td>Saldo Inicial</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td>C$ ".number_format($runningSaldo,2,".",",")."</td>";
			echo "<td></td>";
		echo "</tr>";
		foreach ($accountingCode['accountingMovements'] as $accountingMovement){
			//pr($accountingMovement);
			echo "<tr>";
				$registerDate=new DateTime($accountingMovement['AccountingRegister']['register_date']);
				echo "<td>".$registerDate->format('d-m-Y')."</td>";
				echo "<td>".$this->Html->Link($accountingMovement['AccountingRegister']['concept'],array('controller'=>'accounting_registers','action'=>'view',$accountingMovement['AccountingRegister']['id']))."</td>";
				if ($accountingMovement['AccountingMovement']['bool_debit']){
					echo "<td>".$accountingMovement['Currency']['abbreviation']." ".number_format($accountingMovement['AccountingMovement']['amount'],2,".",",")."</td>";
					echo "<td></td>";
					$runningSaldo+=$accountingMovement['AccountingMovement']['amount'];
				}
				else {
					echo "<td></td>";
					echo "<td>".$accountingMovement['Currency']['abbreviation']." ".number_format($accountingMovement['AccountingMovement']['amount'],2,".",",")."</td>";
					$runningSaldo-=$accountingMovement['AccountingMovement']['amount'];
				}
				echo "<td>C$ ".number_format($runningSaldo,2,".",",")."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'accounting_registers', 'action' => 'view', $accountingMovement['AccountingRegister']['id'])); 
					echo $this->Html->link(__('Edit'), array('controller' => 'accounting_registers', 'action' => 'edit', $accountingMovement['AccountingRegister']['id'])); 
				echo "</td>";
			echo "</tr>";
		}
		echo "<tr>";
			echo "<td>Saldo Final</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td>C$ ".number_format($saldoEndDatePlusOne['amountCS'],2,".",",")."</td>";
			echo "<td></td>";
		echo "</tr>";

		echo "</table>";
		echo "<div class='actions'>";
			echo "<ul>";
				echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
			echo "</ul>";
		echo "</div>";
	} 
?>
</div>

