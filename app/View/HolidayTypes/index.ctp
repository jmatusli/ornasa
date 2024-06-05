<div class="holidayTypes index">
<?php 
	echo "<h2>".__('Holiday Types')."</h2>";
	
	//echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Holiday Type'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_employeeholiday_index_permission) {
			echo "<li>".$this->Html->link(__('List Employee Holidays'), array('controller' => 'employee_holidays', 'action' => 'index'))." </li>";
		}
		if ($bool_employeeholiday_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee Holiday'), array('controller' => 'employee_holidays', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
<div>
<?php
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('name')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('description')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('name')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('description')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	foreach ($holidayTypes as $holidayType){ 
			$pageRow="";
			$pageRow.="<td>".h($holidayType['HolidayType']['name'])."</td>";
			$pageRow.="<td>".h($holidayType['HolidayType']['description'])."</td>";

			$excelBody.="<tr>".$pageRow."</tr>";

			$pageRow.="<td class='actions'>";
				$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $holidayType['HolidayType']['id']));
				$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $holidayType['HolidayType']['id']));
				//$pageRow.=->postLink(__('Delete'), array('action' => 'delete', $holidayType['HolidayType']['id']), array(), __('Are you sure you want to delete # %s?', $holidayType['HolidayType']['id']));
			$pageRow.="</td>";

		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageBody="<tbody>".$pageBody."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
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