<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	function formatNegatives(){
		$("td.negative").each(function(){
			$(this).prepend("-");
		});
		
	}
	$(document).ready(function(){
		formatNumbers();
		formatNegatives();
	});
</script>	
<div class="stockItems index reclassifications fullwidth">
<?php 
  echo "<h2>Resumen Transferencia entre productos</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-10'>";	
        echo $this->Form->create('Report'); 
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
          echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
        echo "</fieldset>";
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->end(__('Refresh')); 
        echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteTransferenciasprod'), array( 'class' => 'btn btn-primary')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";	
				echo "<div class='actions fullwidth' style=''>";	
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
            if ($userrole==ROLE_ADMIN) { 
              echo "<li>".$this->Html->link(__('Transferir Ingroup a Preformas'), array('action' => 'transferirIngroupPreformas'))."</li>";
              echo "<li>".$this->Html->link(__('Transferir Preformas a Ingroup'), array('action' => 'transferirPreformasIngroup'))."</li>";
            }
					echo "</ul>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			
?>

<!--<div class="row">
        <div class="col-md-4 form-group">
            <label>input1</label>
            <input class="form-control" type="text"/>
        </div>
        <div class="col-md-4 form-group">
            <label>input2</label>
            <input class="form-control" type="text"/>
        </div>
         <div class="col-md-4 form-group">
            <label>input3</label>
            <input class="form-control" type="text"/>
        </div>
        
    </div> 
</div>-->
<div class='related'>
<?php  
	$startDateTime= new DateTime($startDate);
	
	$ingroupTransferTable="";
	if (!empty($transferIngroups)){
		$ingroupTransferTable="<table id='IngroupToPreforma_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$ingroupTransferTable.="<thead>";
				$ingroupTransferTable.="<tr>";
					$ingroupTransferTable.="<th>".__('Movement Date')."</th>";
					$ingroupTransferTable.="<th>".__('Codigo de Transferencia')."</th>";
					$ingroupTransferTable.="<th>".__('Original Product')."</th>";
					$ingroupTransferTable.="<th class='centered'>".__('Cantidad Transferida')."</th>";
					$ingroupTransferTable.="<th>".__('Producto destino')."</th>";
					$ingroupTransferTable.="<th class='centered'>".__('Cantidad Transferida')."</th>";
          $ingroupTransferTable.="<th class='centered'>".__('Comment')."</th>";
				$ingroupTransferTable.="</tr>";			
			$ingroupTransferTable.="</thead>";
			
			$ingroupTransferTable.="<tbody>";
			foreach ($transferIngroups as $transferIngroup){
				$movementDate=new DateTime($transferIngroup['movement_date']);
				$ingroupTransferTable.="<tr>";
					$ingroupTransferTable.="<td>".$movementDate->format('d-m-Y')."</td>";
					$ingroupTransferTable.="<td>".$transferIngroup['transfer_code']."</td>";
					$ingroupTransferTable.="<td>".$this->Html->link($transferIngroup['origin_product_name'], array('controller' => 'products', 'action' => 'view', $transferIngroup['origin_product_id']))."</td>";
					$ingroupTransferTable.="<td class='centered number negative'>".$transferIngroup['origin_product_quantity']."</td>";
					$ingroupTransferTable.="<td>".$this->Html->link($transferIngroup['destination_product_name'], array('controller' => 'products', 'action' => 'view', $transferIngroup['destination_product_id']))."</td>";
					$ingroupTransferTable.="<td class='centered number'>".$transferIngroup['destination_product_quantity']."</td>";
          $ingroupTransferTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$transferIngroup['comment']))."</td>";
				$ingroupTransferTable.="</tr>";			
			}
			$ingroupTransferTable.="</tbody>";
		$ingroupTransferTable.="</table>";
	}
	
	 
	$preformaTransferTable="";
	if (!empty($transfersPreformas)){
		$preformaTransferTable="<table id='PreformaToIngroup_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$preformaTransferTable.="<thead>";
				$preformaTransferTable.="<tr>";
					$preformaTransferTable.="<th>".__('Movement Date')."</th>";
					$preformaTransferTable.="<th>".__('Codigo de Transferencia')."</th>";
					$preformaTransferTable.="<th>".__('Original Product')."</th>";
					$preformaTransferTable.="<th class='centered'>".__('Cantidad Transferida')."</th>";
					$preformaTransferTable.="<th>".__('Producto destino')."</th>";
					$preformaTransferTable.="<th class='centered'>".__('Cantidad Transferida')."</th>";
          $preformaTransferTable.="<th class='centered'>".__('Comment')."</th>";
				$preformaTransferTable.="</tr>";			
			$preformaTransferTable.="</thead>";
			
			$preformaTransferTable.="<tbody>";
			//pr ($reclassificationsBottles);
			foreach ($transfersPreformas as $transferPreformas){
				$movementDate=new DateTime($transferPreformas['movement_date']);
				$preformaTransferTable.="<tr>";
					$preformaTransferTable.="<td>".$movementDate->format('d-m-Y')."</td>";
					$preformaTransferTable.="<td>".$transferPreformas['transfer_code']."</td>";
					$preformaTransferTable.="<td>".$this->Html->link($transferPreformas['origin_product_name']/*."_".$transferPreformas['origin_production_result_code']." (".$transferPreformas['origin_raw_material_name'].")"*/, array('controller' => 'products', 'action' => 'view', $transferPreformas['origin_product_id']))."</td>";
					$preformaTransferTable.="<td class='centered number negative'>".$transferPreformas['origin_product_quantity']."</td>";
					$preformaTransferTable.="<td>".$this->Html->link($transferPreformas['destination_product_name'], array('controller' => 'products', 'action' => 'view', $transferPreformas['destination_product_id']))."</td>";
					$preformaTransferTable.="<td class='centered number'>".$transferPreformas['destination_product_quantity']."</td>";
          $preformaTransferTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$transferPreformas['comment']))."</td>";
				$preformaTransferTable.="</tr>";			
			}
			$preformaTransferTable.="</tbody>";
		$preformaTransferTable.="</table>";
	}
	
	$exportData="";
	if (strlen($ingroupTransferTable)>0){
		echo "<h2>Resumen Transferencia Ingroup</h2>";
		echo $ingroupTransferTable; 
		$exportData.=$ingroupTransferTable; 
	}
	
	
	
	if (strlen($preformaTransferTable)>0){
		echo "<h2>Resumen Transferencias Preformas</h2>";
		echo $preformaTransferTable; 
		$exportData.=$preformaTransferTable; 
	}
	
	$_SESSION['transferData'] = $exportData;
?>


</div>

  