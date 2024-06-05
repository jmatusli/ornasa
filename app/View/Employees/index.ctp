<div class="employees index">
	
	<h2><?php echo __('Employees'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('first_name'); ?></th>
		<th><?php echo $this->Paginator->sort('last_name'); ?></th>
		<th><?php echo $this->Paginator->sort('position','Cargo'); ?></th>
		<th><?php echo $this->Paginator->sort('starting_date'); ?></th>
		<th><?php echo $this->Paginator->sort('ending_date'); ?></th>
		<th class='centered'>Días Acumulados</th>
		<th class='centered'>Días Descansados</th>
		<th class='centered'>Saldo</th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
<?php 
	foreach ($employees as $employee){
		$startingDate= new DateTime($employee['Employee']['starting_date']);
		$endingDate= new DateTime($employee['Employee']['ending_date']);
		//echo "holidays earned is ".$employee['Employee']['holidays_earned']."<br/>";
		echo "<tr>";
			echo "<td>".$this->Html->link($employee['Employee']['first_name'], array('action' => 'view', $employee['Employee']['id']))."</td>";
				echo "<td>".$this->Html->link($employee['Employee']['last_name'], array('action' => 'view', $employee['Employee']['id']))."</td>";
			echo "<td>".h($employee['Employee']['position'])."&nbsp;</td>";
			echo "<td>".$startingDate->format('d-m-Y')."&nbsp;</td>";
			echo "<td>".$endingDate->format('d-m-Y')."&nbsp;</td>";
			echo "<td class='centered'>".number_format($employee['Employee']['holidays_earned'],2,".",",")."&nbsp;</td>";
			echo "<td class='centered'>".number_format($employee['Employee']['holidays_taken'],2,".",",")."&nbsp;</td>";
			echo "<td class='centered'>".number_format(($employee['Employee']['holidays_earned']-$employee['Employee']['holidays_taken']),2,".",",")."&nbsp;</td>";
			echo "<td class='actions'>";
				if ($bool_edit_permission){
					echo $this->Html->link(__('Edit'), array('action' => 'edit', $employee['Employee']['id'])); 
				}
				if ($bool_delete_permission){
					//echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $employee['Employee']['id']), array(), __('Are you sure you want to delete # %s?', $employee['Employee']['id'])); 
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
			echo "<li>".$this->Html->link(__('New Employee'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('Empleados Desactivados'), array('action' => 'resumenEmpleadosDesactivados'))."</li>";
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
