<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
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
<div class="stockItems view report">
<?php 
	echo "<h2>".__('Reporte Producción de Productos Fabricados')." ".$finishedProduct['Product']['name']."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		if (!isset($startDate)){
			$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
		}
		if (!isset($endDate)){
			$endDate= date( "Y-m-d", strtotime( date("Y-m-d") ) );
		}
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	
	foreach ($productionData as $productionDataForPreforma){
		$productiontable="";
		$productiontableheader="";
		$productiontablebody="";
		$totalsold=array();
		$quantityA=0;
		$quantityB=0;
		$quantityC=0;
		$quantityRaw=0;
		
		if($userrole!=ROLE_FOREMAN){
			$valueA=0;
			$valueB=0;
			$valueC=0;
			$valueTotal=0;
		}
		// production data per preforma
		if (!empty($productionDataForPreforma['ProductionRun'])){
			foreach ($productionDataForPreforma['ProductionRun'] as $productionData){		
				$quantityA+=$productionData['quantityA'];
				$quantityB+=$productionData['quantityB'];
				$quantityC+=$productionData['quantityC'];
				$quantityRaw+=$productionData['rawUsed'];
				if($userrole!=ROLE_FOREMAN){
					$valueA+=$productionData['valueA'];
					$valueB+=$productionData['valueB'];
					$valueC+=$productionData['valueC'];
					$valueTotal+=$productionData['valueTotal'];
				}
				$productiontablebody.="<tr>";
					$productionrundate=new DateTime($productionData['productionrundate']);
					$productiontablebody.="<td>".$productionrundate->format('d-m-Y')."</td>";
					//$productiontablebody.="<td>".$productionDataForPreforma['Product']['productname']."</td>";
					$productiontablebody.="<td>".$this->Html->link($productionDataForPreforma['Product']['productname'], array('controller' => 'products', 'action' => 'view', $productionDataForPreforma['Product']['id']))."</td>";
					
					$productiontablebody.="<td class='centered number'>".($productionData['quantityA'])."</td>";
					$productiontablebody.="<td class='centered number'>".($productionData['quantityB'])."</td>";
					$productiontablebody.="<td class='centered number'>".($productionData['quantityC'])."</td>";
					$productiontablebody.="<td class='centered number'>".($productionData['rawUsed'])."</td>";
					if($userrole!=ROLE_FOREMAN){
						$productiontablebody.="<td class='centered currency'><span>".($productionData['valueA'])."</span></td>";
						$productiontablebody.="<td class='centered currency'><span>".($productionData['valueB'])."</span></td>";
						$productiontablebody.="<td class='centered currency'><span>".($productionData['valueC'])."</span></td>";
						$productiontablebody.="<td class='centered currency'><span>".($productionData['valueTotal'])."</span></td>";
					}
					$productiontablebody.="<td class='centered percentage'><span>".(100*$productionData['quantityA']/$productionData['rawUsed'])."</span></td>";
					$productiontablebody.="<td class='centered percentage'><span>".(100*$productionData['quantityB']/$productionData['rawUsed'])."</span></td>";
					$productiontablebody.="<td class='centered percentage'><span>".(100*$productionData['quantityC']/$productionData['rawUsed'])."</span></td>";
				$productiontablebody.="</tr>";
			}
			$totalrow="";
			$totalrow.="<tr class='totalrow'>";
				$totalrow.="<td>Total</td>";
				$totalrow.="<td></td>";
				$totalrow.="<td class='centered number'>".($quantityA)."</td>";
				$totalrow.="<td class='centered number'>".($quantityB)."</td>";
				$totalrow.="<td class='centered number'>".($quantityC)."</td>";
				$totalrow.="<td class='centered number'>".($quantityRaw)."</td>";
				if($userrole!=ROLE_FOREMAN){
					$totalrow.="<td class='centered currency'><span>".($valueA)."</span></td>";
					$totalrow.="<td class='centered currency'><span>".($valueB)."</span></td>";
					$totalrow.="<td class='centered currency'><span>".($valueC)."</span></td>";
					$totalrow.="<td class='centered currency'><span>".($valueTotal)."</span></td>";
				}
				$totalrow.="<td class='centered percentage'><span>".(100*$quantityA/$quantityRaw)."</span></td>";
				$totalrow.="<td class='centered percentage'><span>".(100*$quantityB/$quantityRaw)."</span></td>";
				$totalrow.="<td class='centered percentage'><span>".(100*$quantityC/$quantityRaw)."</span></td>";
			$totalrow.="</tr>";
				
			// for each preforma print the table
			$productiontableheader="<thead>";	
				$productiontableheader.="<tr>";
					$productiontableheader.="<th>".__('Production Run Date')."</th>";
					$productiontableheader.="<th>Envase</th>";
					$productiontableheader.="<th class='centered'>A</th>";
					$productiontableheader.="<th class='centered'>B</th>";
					$productiontableheader.="<th class='centered'>C</th>";
					$productiontableheader.="<th class='centered'>Cantidad Preforma Usada</th>";
					if($userrole!=ROLE_FOREMAN){
						$productiontableheader.="<th class='centered'>A</th>";
						$productiontableheader.="<th class='centered'>B</th>";
						$productiontableheader.="<th class='centered'>C</th>";
						$productiontableheader.="<th class='centered'>Costo Preforma Usada</th>";
					}
					$productiontableheader.="<th class='centered'>% A</th>";
					$productiontableheader.="<th class='centered'>% B</th>";
					$productiontableheader.="<th class='centered'>% C</th>";
				$productiontableheader.="</tr>";
			$productiontableheader.="</thead>";	
			
			$productiontablebody="<tbody>".$totalrow.$productiontablebody.$totalrow."</tbody>";
			
			$productiontable="<table id='produccion_".$productionDataForPreforma['Product']['productname']."'>";
			$productiontable.=$productiontableheader;
			$productiontable.=$productiontablebody;
			$productiontable.="</table>";
		
			echo $this->Html->link(__('Save as Excel'), array('action' => 'guardarReporteProductoFabricado',$productionDataForPreforma['Product']['productname']), array('class' => 'btn btn-primary')); 
		
			echo "<h2>".__('Report')." para Producto ".$productionDataForPreforma['Product']['productname']." y Materia Prima ".$productionDataForPreforma['RawMaterial']['name']."</h2>";
			echo $productiontable;
			
			
			$_SESSION['fabricatedProductReport'] = $productiontable;
		}
	}
?>
