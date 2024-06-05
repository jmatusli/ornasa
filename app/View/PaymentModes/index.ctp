<div class="paymentModes index">
<?php 
	echo "<h2>".__('Payment Modes')."</h2>";
	//echo $this->Form->create('Report');
		//echo "<fieldset>";
		//	echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		//	echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
		//echo "</fieldset>";
		//echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		//echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	//echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardar'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Payment Mode'), array('action' => 'add'))."</li>";
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

	foreach ($paymentModes as $paymentMode){ 
		$pageRow="";
		$pageRow.="<td>".h($paymentMode['PaymentMode']['name'])."</td>";
		$pageRow.="<td>".h($paymentMode['PaymentMode']['description'])."</td>";

			$excelBody.="<tr>".$pageRow."</tr>";

			$pageRow.="<td class='actions'>";
				$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $paymentMode['PaymentMode']['id']));
				if ($bool_edit_permission){
					$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $paymentMode['PaymentMode']['id']));
				}
				if ($bool_delete_permission){
					$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $paymentMode['PaymentMode']['id']), array(), __('Are you sure you want to delete # %s?', $paymentMode['PaymentMode']['id']));
				}
			$pageRow.="</td>";
		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageTotalRow="";
	//$pageTotalRow.="<tr class='totalrow'>";
	//	$pageTotalRow.="<td></td>";
	//	$pageTotalRow.="<td></td>";
	//	$pageTotalRow.="<td></td>";
	//$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>
<script>
	$(document).ready(function(){
		
	});
</script>