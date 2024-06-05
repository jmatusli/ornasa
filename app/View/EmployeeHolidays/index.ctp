<div class="employeeHolidays index">
	

	<h2><?php echo __('Employee Holidays'); ?></h2>
<?php	
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo $this->Form->end(__('Refresh'));
?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('employee_id'); ?></th>
		<th><?php echo $this->Paginator->sort('holiday_date'); ?></th>
		<th><?php echo $this->Paginator->sort('days_taken'); ?></th>
		<th><?php echo $this->Paginator->sort('holiday_type_id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
<?php 
	foreach ($employeeHolidays as $employeeHoliday){
		$holidayDate= new DateTime($employeeHoliday['EmployeeHoliday']['holiday_date']);
		echo "<tr>";
			echo "<td>".$this->Html->link($employeeHoliday['Employee']['fullname'], array('controller' => 'employees', 'action' => 'view', $employeeHoliday['Employee']['id']))."</td>";
			echo "<td>".$holidayDate->format('d-m-Y')."&nbsp;</td>";
			echo "<td>".h($employeeHoliday['EmployeeHoliday']['days_taken'])."&nbsp;</td>";
			echo "<td>".$this->Html->link($employeeHoliday['HolidayType']['name'], array('controller' => 'employees', 'action' => 'view', $employeeHoliday['HolidayType']['id']))."</td>";
			echo "<td class='actions'>";
				echo $this->Html->link(__('View'), array('action' => 'view', $employeeHoliday['EmployeeHoliday']['id'])); 
				if ($bool_edit_permission){
					echo $this->Html->link(__('Edit'), array('action' => 'edit', $employeeHoliday['EmployeeHoliday']['id'])); 
				}
				if ($bool_delete_permission){
					echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $employeeHoliday['EmployeeHoliday']['id']), array(), __('Are you sure you want to delete # %s?', $employeeHoliday['EmployeeHoliday']['id'])); 
				}
			echo "</td>";
		echo "</tr>";
	}
?>
	</tbody>
	</table>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_holiday_add_permission) {
			echo "<li>".$this->Html->link(__('Registrar Día Feriado'), array('action' => 'registrarFeriado'))."</li>";
			echo "<br/>";
		}
		if ($bool_employee_index_permission) {
			echo "<li>".$this->Html->link(__('List Employees'), array('controller' => 'employees', 'action' => 'index'))." </li>";
		}
		if ($bool_employee_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee'), array('controller' => 'employees', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
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