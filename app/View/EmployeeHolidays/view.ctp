<div class="employeeHolidays view" style="font-size:1.8em;">
<h2><?php echo __('Employee Holiday'); ?></h2>
	<dl style="width:100%;">
		<dt><?php echo __('Employee'); ?></dt>
		<dd>
			<?php echo $this->Html->link($employeeHoliday['Employee']['fullname'], array('controller' => 'employees', 'action' => 'view', $employeeHoliday['Employee']['id'])); ?>
			&nbsp;
		</dd>
	<?php 
		$holidayDate= new DateTime($employeeHoliday['EmployeeHoliday']['holiday_date']);
		echo "<dt>".__('Holiday Date')."</dt>";
		echo "<dd>".$holidayDate->format('d-m-Y')."</dd>";
	?>
		<dt><?php echo __('Days Taken'); ?></dt>
		<dd>
			<?php echo h($employeeHoliday['EmployeeHoliday']['days_taken']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Holiday Type'); ?></dt>
		<dd>
			<?php echo $this->Html->link($employeeHoliday['HolidayType']['name'], array('controller' => 'employees', 'action' => 'view', $employeeHoliday['HolidayType']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Observation'); ?></dt>
		<dd>
			<?php echo h($employeeHoliday['EmployeeHoliday']['observation']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Employee Holiday'), array('action' => 'edit', $employeeHoliday['EmployeeHoliday']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete Employee Holiday'), array('action' => 'delete', $employeeHoliday['EmployeeHoliday']['id']), array(), __('Are you sure you want to delete # %s?', $employeeHoliday['EmployeeHoliday']['id']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Employee Holidays'), array('action' => 'index'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}		
		echo "<br/>";
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
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,4);
			$(this).parent().append(" C$");
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		formatPercentages();
	});

</script>