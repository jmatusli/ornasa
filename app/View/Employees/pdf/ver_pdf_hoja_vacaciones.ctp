<style>
	div, span {
		font-size:1.1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	table {
		font-size:0.8em;
	}
	
	pre {
		font-size:0.5em;
	}
	
	div.centered,
	td.centered,
	th.centered
	{
		text-align:center;
	}
	
	div.right{
		text-align:right;
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td,
	.bordered tr.totalrow td,
	{
		border-width:1px;
		border-style:solid;
		border-color:#000000;
	}
	
	.bordered tr td{
		border-width:0 1px;
	}
	
	table {
		width:100%;
	}
</style>
<?php
	$startDate=date("Y-m-d",strtotime($startDate));
	$startDateTime=new DateTime($startDate);
	$endDate=date("Y-m-d",strtotime($endDate));
	$endDateTime=new DateTime($endDate);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	
	$output="";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	//$output.="<div class="employees viewPDF">";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<table style='width:90%;'>";
		$output.="<tr>";
			$output.="<td class='bold' style='width:70%;'>HOJA DE VACACIONES ".strtoupper($employee['Employee']['first_name'])." ".strtoupper($employee['Employee']['last_name'])."</td>";		
			$output.="<td class='bold' style='width:30%;'>MANAGUA, ".$nowDateTime->format('d-m-Y')."</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td class='bold center' style='width:100%;'>Período desde ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</td>";		
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div class='accountingRegisters balancegeneral'>";
	$totalCurrent=0;
	
	
	$output.="<h2>Días de Vacaciones</h2>";
	
	$output.="<table cellpadding = '0' cellspacing = '0'>";
		$output.="<thead>";
			$output.="<tr>";
				$output.="<th class='centered'>".__('Holiday Date')."</th>";
				$output.="<th >".__('Days Taken')."</th>";
				$output.="<th class='centered'>".__('Holiday Type')."</th>";
			$output.="</tr>";
		$output.="</thead>";
		$output.="<tbody>";
		$daysTaken=0;
		foreach ($employee['EmployeeHoliday'] as $employeeHoliday){
      $holidayDateTime = new DateTime($employeeHoliday['holiday_date']);
			$daysTaken+=$employeeHoliday['days_taken'];
			$output.="<tr>";
				$output.="<td class='centered'>".$holidayDateTime->format('d-m-Y')."</td>";
				$output.="<td class='centered'>".$employeeHoliday['days_taken']."</td>";
				$output.="<td class='centered'>".$employeeHoliday['HolidayType']['name']."</td>";
			$output.="</tr>";
		}
			$output.="<tr class='totalrow'>";
				$output.="<td>Total</td>";
				$output.="<td class='number centered'>".$daysTaken."</td>";
				$output.="<td></td>";
				$output.="<td></td>";
			$output.="</tr>";
		$output.="</tbody>";
	$output.="</table>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$footer="";
	$footer.="<table style='width:100%'>";
		$footer.="<tr style='border:0px;'>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Elaborado</td>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Firma Empleado</td>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Autorizado</td>";
		$footer.="</tr>";
	$footer.="</table>";
	$output.=$footer;
	$output.="</div>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>