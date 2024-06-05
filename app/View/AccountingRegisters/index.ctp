<div class="accountingRegisters index">
	<h2><?php echo __('Accounting Registers'); ?></h2>
	
	<?php echo $this->Form->create('Report'); ?>
	<fieldset>
	<?php
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	?>
	</fieldset>
	<button id='previousmonth' class='monthswitcher'><?php echo __('Previous Month'); ?></button>
	<button id='nextmonth' class='monthswitcher'><?php echo __('Next Month'); ?></button>
	<?php echo $this->Form->end(__('Refresh')); ?>
	
	<table cellpadding='0' cellspacing='0'>
		<thead>
			<tr>
				<th><?php echo $this->Paginator->sort('date'); ?></th>
				<th><?php echo $this->Paginator->sort('accounting_register_code'); ?></th>
				<th><?php echo $this->Paginator->sort('concept'); ?></th>
				<th><?php echo $this->Paginator->sort('amount'); ?></th>
				
				<th class="actions"><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php 
			$total_CS=0;
			foreach ($accountingRegisters as $accountingRegister){
				echo "<tr>";
					$registerDate=new DateTime($accountingRegister['AccountingRegister']['register_date']);
					echo "<td>".$registerDate->format('d-m-Y')."</td>";
					echo "<td>".$accountingRegister['AccountingRegister']['register_code']."</td>";
					echo "<td>".$accountingRegister['AccountingRegister']['concept']."</td>";
					echo "<td><span class='amountright'>".number_format($accountingRegister['AccountingRegister']['amount'],2,".",",")."</span></td>";
					$total_CS+=$accountingRegister['AccountingRegister']['amount'];
					
					echo "<td class='actions'>";
						echo $this->Html->link(__('View'), array('action' => 'view', $accountingRegister['AccountingRegister']['id'])); 
						if ($bool_edit_permission){
							echo $this->Html->link(__('Edit'), array('action' => 'edit', $accountingRegister['AccountingRegister']['id'])); 
						}
						$filename=$registerDate->format('d_m_Y').'_Asiento Contable_'.$accountingRegister['AccountingRegister']['concept'];
						echo $this->Html->link(__('Pdf'), array('action' => 'viewPdf','ext'=>'pdf',$accountingRegister['AccountingRegister']['id'],$filename));
						if ($bool_delete_permission){
							//echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $accountingRegister['AccountingRegister']['id']), array(), __('Are you sure you want to delete # %s?', $accountingRegister['AccountingRegister']['id'])); 
						}
					echo "</td>";
				echo "</tr>";
			}
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
				echo "<td><span class='amountright'>".number_format($total_CS,2,".",",")."</span></td>";
				echo "<td></td>";
			echo "</tr>";
		?>
		</tbody>
	</table>
</div>
<div class='actions'>
<?php 	
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Accounting Register'), array('action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
</div>