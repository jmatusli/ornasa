<div class="chequeTypes index">
<?php

	echo "<h2>". __('Cheque Types')."</h2>";
	
	echo "<table cellpadding='0' cellspacing='0'>";
?>
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('name'); ?></th>
		<th><?php echo $this->Paginator->sort('accounting_code_id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($chequeTypes as $chequeType): ?>
	<tr>
		<td><?php echo h($chequeType['ChequeType']['name']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($chequeType['AccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $chequeType['AccountingCode']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $chequeType['ChequeType']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $chequeType['ChequeType']['id'])); ?>
			<?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $chequeType['ChequeType']['id']), array(), __('Are you sure you want to delete # %s?', $chequeType['ChequeType']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Cheque Type'), array('action' => 'add')); ?></li>
		<br/>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cheques'), array('controller' => 'cheques', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cheque'), array('controller' => 'cheques', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
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