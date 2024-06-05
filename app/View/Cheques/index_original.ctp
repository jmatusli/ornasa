<div class="cheques index">
<?php 
	echo $this->Form->create('Report');
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	

	echo "<h2>".__('Cheques')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('cheque_date')."</th>";
				echo "<th>".$this->Paginator->sort('cheque_code')."</th>";
				echo "<th>".$this->Paginator->sort('receiver_name')."</th>";
				echo "<th>".$this->Paginator->sort('amount')."</th>";
				echo "<th>".$this->Paginator->sort('concept',__('Observation'))."</th>";
				echo "<th>".$this->Paginator->sort('bank_accounting_code_id')."</th>";
				echo "<th>".$this->Paginator->sort('accounting_register_id')."</th>";
				//echo "<th>".$this->Paginator->sort('Purchase.order_code')."</th>";
				//echo "<th>".$this->Paginator->sort('cheque_type_id')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$total_CS=0;
		$total_USD=0;
		foreach ($cheques as $cheque) {
			$chequeDate=new DateTime($cheque['Cheque']['cheque_date']);
			$filename=$filename='Cheque_'.$cheque['Cheque']['cheque_code'];
			echo "<tr>";
				echo "<td>".$chequeDate->format('d-m-Y')."&nbsp;</td>";
				echo "<td>".h($cheque['Cheque']['cheque_code'])."&nbsp;</td>";
				echo "<td>".h($cheque['Cheque']['receiver_name'])."&nbsp;</td>";
				echo "<td class='number'>".$cheque['Currency']['abbreviation']." <span class='amountright'>".$cheque['Cheque']['amount']."</span></td>";
				echo "<td>".h($cheque['Cheque']['concept'])."&nbsp;</td>";
				echo "<td>".$this->Html->link($cheque['BankAccountingCode']['description'], array('controller' => 'accounting_codes', 'action' => 'view', $cheque['BankAccountingCode']['id']))."</td>";
				echo "<td>".$this->Html->link($cheque['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $cheque['AccountingRegister']['id']))."</td>";
				//echo "<td>".$this->Html->link($cheque['Purchase']['order_code'], array('controller' => 'orders', 'action' => 'view', $cheque['Purchase']['id']))."</td>";
				//echo "<td>".$this->Html->link($cheque['ChequeType']['name'], array('controller' => 'cheque_types', 'action' => 'view', $cheque['ChequeType']['id']))."</td>";
				
				if ($cheque['Currency']['id']==CURRENCY_CS){
					$total_CS+=$cheque['Cheque']['amount'];
				}
				elseif ($cheque['Currency']['id']==CURRENCY_USD){
					$total_USD+=$cheque['Cheque']['amount'];
				}
				
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('action' => 'view', $cheque['Cheque']['id']));
					echo $this->Html->link(__('Edit'), array('action' => 'edit', $cheque['Cheque']['id']));
					echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $cheque['Cheque']['id']), array(), __('Are you sure you want to delete # %s?', $cheque['Cheque']['cheque_code']));
					echo $this->Html->link(__('Pdf'), array('action' => 'viewPdf','ext'=>'pdf',$cheque['Cheque']['id'],$filename));
				echo "</td>";
			echo "</tr>";
		}
		if ($total_CS>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total C$</td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td>C$ <span class='amountright'>".number_format($total_CS,2,".",",")."</span></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
		}
		if ($total_USD>0){
			echo "<tr class='totalrow'>";
				echo "<td>Total US$</td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td>US$ <span class='amountright'>".number_format($total_USD,2,".",",")."</span></td>";
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
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Cheque'), array('action' => 'add')); ?></li>
		<br/>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add')); ?> </li>
		<!--li><?php //echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li-->
		<!--li><?php //echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?></li-->
		<!--li><?php echo $this->Html->link(__('List Cheque Types'), array('controller' => 'cheque_types', 'action' => 'index')); ?>." </li-->
		<!--li><?php echo $this->Html->link(__('New Cheque Type'), array('controller' => 'cheque_types', 'action' => 'add')); ?>." </li-->
	</ul>
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
		$("td.CScurrency span").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
	});

	$('#previousmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var previousMonth= (thisMonth-1)%12;
		var previousYear=parseInt($('#ReportStartdateYear').val());
		if (previousMonth==0){
			previousMonth=12;
			previousYear-=1;
		}
		if (previousMonth<10){
			previousMonth="0"+previousMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(previousMonth);
		$('#ReportStartdateYear').val(previousYear);
		var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
		$('#ReportEnddateDay').val(daysInPreviousMonth);
		$('#ReportEnddateMonth').val(previousMonth);
		$('#ReportEnddateYear').val(previousYear);
	});
	
	$('#nextmonth').click(function(event){
		var thisMonth = parseInt($('#ReportStartdateMonth').val());
		var nextMonth= (thisMonth+1)%12;
		var nextYear=parseInt($('#ReportStartdateYear').val());
		if (nextMonth==0){
			nextMonth=12;
		}
		if (nextMonth==1){
			nextYear+=1;
		}
		if (nextMonth<10){
			nextMonth="0"+nextMonth;
		}
		$('#ReportStartdateDay').val('1');
		$('#ReportStartdateMonth').val(nextMonth);
		$('#ReportStartdateYear').val(nextYear);
		var daysInNextMonth=daysInMonth(nextMonth,nextYear);
		$('#ReportEnddateDay').val(daysInNextMonth);
		$('#ReportEnddateMonth').val(nextMonth);
		$('#ReportEnddateYear').val(nextYear);
	});
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}
</script>