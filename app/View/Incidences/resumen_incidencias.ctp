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

<div class="incidences index">
<?php 
	echo "<h2>".__('Incidences')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo $this->Form->end(__('Refresh'));
	//echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Incidence'), array('action' => 'crearIncidencia'))."</li>";
	echo "</ul>";
?>
</div>
<div>
<?php
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('list_order')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('creating_user_id','Creado por')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('list_order')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('creating_user_id')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	foreach ($incidences as $incidence){ 
		$pageRow="";
		$pageRow.="<td>".$this->Html->link($incidence['Incidence']['name'].($incidence['Incidence']['bool_active']?'':' (desactivado)'),['action' => 'verIncidencia', $incidence['Incidence']['id']])."</td>";
		$pageRow.="<td>".$incidence['Incidence']['list_order']."</td>";
    $pageRow.="<td>".$incidence['CreatingUser']['username']."</td>";
			$excelBody.="<tr>".$pageRow."</tr>";
      
			$pageRow.="<td class='actions'>";
			if ($bool_edit_permission){
				$pageRow.=$this->Html->link(__('Edit'), array('action' => 'editarIncidencia', $incidence['Incidence']['id']));
      }  
			$pageRow.="</td>";
		$pageBody.="<tr".($incidence['Incidence']['bool_active']?"":" class='italic'").">".$pageRow."</tr>";
	}

	$pageTotalRow="";
	$pageTotalRow.="<tr class='totalrow'>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
	$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="incidencias";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>
