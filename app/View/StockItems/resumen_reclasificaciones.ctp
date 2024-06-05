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
  echo "<h2>Resumen Reclasificaciones</h2>";
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
        echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteReclasificaciones'), array( 'class' => 'btn btn-primary')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";	
				echo "<div class='actions fullwidth' style=''>";	
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
            if ($userrole==ROLE_ADMIN) { 
              echo "<li>".$this->Html->link(__('Reclasificar Tapones'), array('action' => 'reclasificarTapones'))."</li>";
              echo "<li>".$this->Html->link(__('Reclasificar Envases'), array('action' => 'reclasificarBotellas'))."</li>";
              echo "<li>".$this->Html->link(__('Reclasificar Envases a Preformas'), array('action' => 'reclasificarBotellasPreformas'))."</li>";
            }
					echo "</ul>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			
?>
</div>
<div class='related'>
<?php  
	$startDateTime= new DateTime($startDate);
	
	$capReclassificationTable="";
	if (!empty($reclassificationsCaps)){
		$capReclassificationTable="<table id='tapones_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$capReclassificationTable.="<thead>";
				$capReclassificationTable.="<tr>";
					$capReclassificationTable.="<th>".__('Movement Date')."</th>";
					$capReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$capReclassificationTable.="<th>".__('Original Product')."</th>";
					$capReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$capReclassificationTable.="<th>".__('Destination Product')."</th>";
					$capReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $capReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$capReclassificationTable.="</tr>";			
			$capReclassificationTable.="</thead>";
			
			$capReclassificationTable.="<tbody>";
			foreach ($reclassificationsCaps as $reclassificationCaps){
				$movementDate=new DateTime($reclassificationCaps['movement_date']);
				$capReclassificationTable.="<tr>";
					$capReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
					$capReclassificationTable.="<td>".$reclassificationCaps['reclassification_code']."</td>";
					$capReclassificationTable.="<td>".$this->Html->link($reclassificationCaps['origin_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationCaps['origin_product_id']))."</td>";
					$capReclassificationTable.="<td class='centered number negative'>".$reclassificationCaps['origin_product_quantity']."</td>";
					$capReclassificationTable.="<td>".$this->Html->link($reclassificationCaps['destination_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationCaps['destination_product_id']))."</td>";
					$capReclassificationTable.="<td class='centered number'>".$reclassificationCaps['destination_product_quantity']."</td>";
          $capReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationCaps['comment']))."</td>";
				$capReclassificationTable.="</tr>";			
			}
			$capReclassificationTable.="</tbody>";
		$capReclassificationTable.="</table>";
	}
	
	$bottleReclassificationTable="";
	if (!empty($reclassificationsBottles)){
		$bottleReclassificationTable="<table id='envase_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$bottleReclassificationTable.="<thead>";
				$bottleReclassificationTable.="<tr>";
					$bottleReclassificationTable.="<th>".__('Movement Date')."</th>";
					$bottleReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$bottleReclassificationTable.="<th>".__('Original Product')."</th>";
					$bottleReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$bottleReclassificationTable.="<th>".__('Destination Product')."</th>";
					$bottleReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $bottleReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$bottleReclassificationTable.="</tr>";			
			$bottleReclassificationTable.="</thead>";
			
			$bottleReclassificationTable.="<tbody>";
			//pr ($reclassificationsBottles);
			foreach ($reclassificationsBottles as $reclassificationBottles){
				$movementDate=new DateTime($reclassificationBottles['movement_date']);
				$bottleReclassificationTable.="<tr>";
					$bottleReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
					$bottleReclassificationTable.="<td>".$reclassificationBottles['reclassification_code']."</td>";
					$bottleReclassificationTable.="<td>".$this->Html->link($reclassificationBottles['origin_product_name']."_".$reclassificationBottles['origin_production_result_code']." (".$reclassificationBottles['origin_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationBottles['origin_product_id']))."</td>";
					$bottleReclassificationTable.="<td class='centered number negative'>".$reclassificationBottles['origin_product_quantity']."</td>";
					$bottleReclassificationTable.="<td>".$this->Html->link($reclassificationBottles['destination_product_name']."_".$reclassificationBottles['destination_production_result_code']." (".$reclassificationBottles['destination_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationBottles['destination_product_id']))."</td>";
					$bottleReclassificationTable.="<td class='centered number'>".$reclassificationBottles['destination_product_quantity']."</td>";
          $bottleReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationBottles['comment']))."</td>";
				$bottleReclassificationTable.="</tr>";			
			}
			$bottleReclassificationTable.="</tbody>";
		$bottleReclassificationTable.="</table>";
	}
	
	$preformaReclassificationTable="";
	if (!empty($reclassificationsPreformas)){
		$preformaReclassificationTable="<table id='envase_preforma_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$preformaReclassificationTable.="<thead>";
				$preformaReclassificationTable.="<tr>";
					$preformaReclassificationTable.="<th>".__('Movement Date')."</th>";
					$preformaReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$preformaReclassificationTable.="<th>".__('Original Product')."</th>";
					$preformaReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$preformaReclassificationTable.="<th>".__('Destination Product')."</th>";
					$preformaReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $preformaReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$preformaReclassificationTable.="</tr>";			
			$preformaReclassificationTable.="</thead>";
			
			$preformaReclassificationTable.="<tbody>";
			//pr ($reclassificationsBottles);
			foreach ($reclassificationsPreformas as $reclassificationPreformas){
				$movementDate=new DateTime($reclassificationPreformas['movement_date']);
				$preformaReclassificationTable.="<tr>";
					$preformaReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
					$preformaReclassificationTable.="<td>".$reclassificationPreformas['reclassification_code']."</td>";
					$preformaReclassificationTable.="<td>".$this->Html->link($reclassificationPreformas['origin_product_name']."_".$reclassificationPreformas['origin_production_result_code']." (".$reclassificationPreformas['origin_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationPreformas['origin_product_id']))."</td>";
					$preformaReclassificationTable.="<td class='centered number negative'>".$reclassificationPreformas['origin_product_quantity']."</td>";
					$preformaReclassificationTable.="<td>".$this->Html->link($reclassificationPreformas['destination_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationPreformas['destination_product_id']))."</td>";
					$preformaReclassificationTable.="<td class='centered number'>".$reclassificationPreformas['destination_product_quantity']."</td>";
          $preformaReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationPreformas['comment']))."</td>";
				$preformaReclassificationTable.="</tr>";			
			}
			$preformaReclassificationTable.="</tbody>";
		$preformaReclassificationTable.="</table>";
	}
	
	$exportData="";
	if (strlen($capReclassificationTable)>0){
		echo "<h2>Resumen Reclasificaciones de Tapones</h2>";
		echo $capReclassificationTable; 
		$exportData.=$capReclassificationTable; 
	}
	
	if (strlen($bottleReclassificationTable)>0){
		echo "<h2>Resumen Reclasificaciones Internas de Envases</h2>";
		echo $bottleReclassificationTable; 
		$exportData.=$bottleReclassificationTable; 
	}
	
	if (strlen($preformaReclassificationTable)>0){
		echo "<h2>Resumen Reclasificaciones de Envases a Preformas</h2>";
		echo $preformaReclassificationTable; 
		$exportData.=$preformaReclassificationTable; 
	}
	
	$_SESSION['reclassificationData'] = $exportData;
?>
</div>