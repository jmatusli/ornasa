<div class="holidayTypes view">
<?php 
	echo "<h2>".__('Holiday Type')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($holidayType['HolidayType']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($holidayType['HolidayType']['description'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Holiday Type'), array('action' => 'edit', $holidayType['HolidayType']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete Holiday Type'), array('action' => 'delete', $holidayType['HolidayType']['id']), array(), __('Are you sure you want to delete # %s?', $holidayType['HolidayType']['id']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Holiday Types'), array('action' => 'index'))."</li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Holiday Type'), array('action' => 'add'))."</li>";
		}
		echo "<br/>";
		if ($bool_employeeholiday_index_permission) {
			echo "<li>".$this->Html->link(__('List Employee Holidays'), array('controller' => 'employee_holidays', 'action' => 'index'))." </li>";
		}
		if ($bool_employeeholiday_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), array('controller' => 'employee_holidays', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
	if (!empty($holidayType['EmployeeHoliday'])){
		echo "<h3>".__('Related Employee Holidays')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th>".__('Employee Id')."</th>";
				echo "<th>".__('Holiday Date')."</th>";
				echo "<th>".__('Days Taken')."</th>";
				echo "<th>".__('Holiday Type Id')."</th>";
				echo "<th>".__('Observation')."</th>";
				echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		foreach ($holidayType['EmployeeHoliday'] as $employeeHoliday){ 
			echo "<tr>";
				echo "<td>".$employeeHoliday['employee_id']."</td>";
				echo "<td>".$employeeHoliday['holiday_date']."</td>";
				echo "<td>".$employeeHoliday['days_taken']."</td>";
				echo "<td>".$employeeHoliday['holiday_type_id']."</td>";
				echo "<td>".$employeeHoliday['observation']."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'employee_holidays', 'action' => 'view', $employeeHoliday['id']));
					if ($bool_employeeholiday_delete_permission){
						echo $this->Form->postLink(__('Delete'), array('controller' => 'employee_holidays', 'action' => 'delete', $employeeHoliday['id']), array(), __('Are you sure you want to delete # %s?', $employeeHoliday['id']));
					}
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>
</div>
