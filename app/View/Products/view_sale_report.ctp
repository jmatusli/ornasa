<div class="stockItems view report">
<!--h2><?php echo __('Overview'); ?></h2-->
<?php echo $this->Form->create('Report'); ?>
	<fieldset>
	<?php
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	?>
	</fieldset>
	<button id='previousmonth' class='monthswitcher'><?php echo __('Previous Month'); ?></button>
	<button id='nextmonth' class='monthswitcher'><?php echo __('Next Month'); ?></button>
	<?php echo $this->Form->end(__('Refresh')); ?>

<?php
	$exportsaletables="";
	foreach ($salesData as $salesDataForPreforma){
		//pr($salesDataForPreforma);
		$saletable="";
		$saletableheader="";
		$saletablebody="";
		//$saletablebody.="<tbody>";
		$totalsold=array();
		$visiblearray=array();
		for ($i=0;$i<3*count($allFinishedProducts);$i++){
			$visiblearray[$i]=0;
		}
		
		// sale data per preforma
		if (!empty($salesDataForPreforma['Sale'])){
			// generate an array that determines whether the column will be shown or not based on initial and final inventory
			$saleTableRows="";
			foreach ($salesDataForPreforma['Sale'] as $saleData){	
				// initialize visible array
				
				for ($i=0;$i<count($salesDataForPreforma['final_stock']);$i++){
					$visiblearray[$i]=$salesDataForPreforma['initial_stock'][$i]+$salesDataForPreforma['final_stock'][$i]+$salesDataForPreforma['produced_stock'][$i]+$saleData['sold_products'][$i];
				}
				for ($i=0;$i<count($salesDataForPreforma['final_stock']);$i++){
					if ($i%3==2){
						if ($visiblearray[$i-2]>0 || $visiblearray[$i-1]>0 || $visiblearray[$i]>0){
							$visiblearray[$i-2]=true;
							$visiblearray[$i-1]=true;
							$visiblearray[$i]=true;
						}
						else {
							$visiblearray[$i-2]=false;
							$visiblearray[$i-1]=false;
							$visiblearray[$i]=false;
						}
					}
				}	
				//if ($salesDataForPreforma['RawMaterial']['id']==10){
				//	pr($visiblearray);
				//	pr($saleData);
				//}
				
				$saleTableRows.="<tr>";
				$orderdate=new DateTime($saleData['order_date']);
				$saleTableRows.="<td>".$orderdate->format('d-m-Y')."</td>";
				if ($saleData['order_code']!="0"){
          if ($saleData['is_sale']){
            $saleTableRows.="<td>".$this->Html->Link($saleData['order_code'],array('controller'=>'orders','action'=>'verVenta',$saleData['id']))."</td>";
          }
          else {
            $saleTableRows.="<td>".$this->Html->Link($saleData['order_code'],array('controller'=>'orders','action'=>'verRemision',$saleData['id']))."</td>";
          }
				}
				else {
					$saleTableRows.="<td>".$saleData['reclassification_code']."</td>";
				}
				$saleTableRows.="<td>".$saleData['client']."</td>";
				$saleTableRows.="<td class='separator'>&nbsp;</td>";
					
				// get the sales data
				for ($i=0;$i<count($saleData['sold_products']);$i++){
					if ($visiblearray[$i]){
						if ($saleData['sold_products'][$i]!=0){
							if ($saleData['sold_products'][$i]>0){
								$saleTableRows.="<td class='centered number' style='font-weight:bold;'>".$saleData['sold_products'][$i]."</td>";	
							}
							else {
								$saleTableRows.="<td class='centered negative' style='font-weight:bold;'>".number_format($saleData['sold_products'][$i],0,".",",")."</td>";	
							}
						}
						else {
							$saleTableRows.="<td class='centered'>-</td>";	
						}
					}
				}
				
				//echo "invoice code is ".$saleData['order_code']."<br/>";
				if ($saleData['order_code']!='0'){
					//echo "entering the totalsold logic<br/>";
					foreach (array_keys($saleData['sold_products'] + $totalsold) as $key) {
						$totalsold[$key] = (isset($saleData['sold_products'][$key]) ? $saleData['sold_products'][$key] : 0) + (isset($totalsold[$key]) ? $totalsold[$key] : 0);
					}
				}
				$saleTableRows.="</tr>";
			}
			$totalRows="";
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td class='separator'></td>";
				$i=0;
				//pr($visiblearray);
				foreach ($allFinishedProducts as $finishedProduct){
					if ($visiblearray[$i]){
						$totalRows.="<td colspan='3' class='centered'>".$finishedProduct['Product']['name']."</td>";
					}
					$i+=3;
				}
			$totalRows.="</tr>";
			
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td>Inventario Inicial</td>";
				$totalRows.="<td class='separator'></td>";
				//pr($visiblearray);
				//pr($saleData['initial_stock']);
				for ($i=0;$i<count($salesDataForPreforma['initial_stock']);$i++){
					if ($visiblearray[$i]){
						$totalRows.="<td class='centered number'>".$salesDataForPreforma['initial_stock'][$i]."</td>";
					}
				}
			$totalRows.="</tr>";
			
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td>Producido</td>";
				$totalRows.="<td class='separator'></td>";
				for ($i=0;$i<count($salesDataForPreforma['produced_stock']);$i++){
					if ($visiblearray[$i]){
						$totalRows.="<td class='centered number'>".$salesDataForPreforma['produced_stock'][$i]."</td>";
					}
				}
			$totalRows.="</tr>";
			
			$totalRows.="<tr class='totalrow'>";
			$totalRows.="<td></td>";
			$totalRows.="<td></td>";
			$totalRows.="<td>Reclassificado</td>";
			$totalRows.="<td class='separator'></td>";
			for ($i=0;$i<count($salesDataForPreforma['reclassified_stock']);$i++){
				if ($visiblearray[$i]){
					//pr ($salesDataForPreforma['reclassified_stock'][$i]);
					//echo "string?".is_string($salesDataForPreforma['reclassified_stock'][$i])."<br/>";
					//echo "integer?".is_integer($salesDataForPreforma['reclassified_stock'][$i])."<br/>";
					//echo intval($salesDataForPreforma['reclassified_stock'][$i])."<br/>";
					//echo strval($salesDataForPreforma['reclassified_stock'][$i])."<br/>";
					//echo "negative?".($salesDataForPreforma['reclassified_stock'][$i]<0)."<br/>";
					$totalRows.="<td class='centered'>";
						//$totalRows.=($salesDataForPreforma['reclassified_stock'][$i]<0?"-":"+");
						$totalRows.=number_format($salesDataForPreforma['reclassified_stock'][$i],0,".",",");
					$totalRows.="</td>";
				}
			}
			$totalRows.="</tr>";
			
			$totalRows.="<tr class='totalrow'>";
				$totalRows.="<td></td>";
				$totalRows.="<td></td>";
				$totalRows.="<td>Venta</td>";
				$totalRows.="<td class='separator'></td>";
				for ($i=0;$i<sizeof($totalsold);$i++){
					if ($visiblearray[$i]){
						$totalRows.="<td class='centered number'>".$totalsold[$i]."</td>";
					}
				}
			$totalRows.="</tr>";
			
			$totalRows.="<tr class='totalrow'>";
			$totalRows.="<td></td>";
			$totalRows.="<td></td>";
			$totalRows.="<td>Inventario Final</td>";
			$totalRows.="<td class='separator'></td>";		
			for ($i=0;$i<count($salesDataForPreforma['final_stock']);$i++){
				if ($visiblearray[$i]){
					$totalRows.="<td class='centered number'>".$salesDataForPreforma['final_stock'][$i]."</td>";
				}
			}
			$totalRows.="</tr>";
			
			
			//$saletablebody.="</tbody>";
		
			// for each preforma print the table
			$saletableheader="<thead>";	
				$saletableheader.="<tr>";
					$saletableheader.="<th>".__('Date')."</th>";
					$saletableheader.="<th>".__('Código')."</th>";
					$saletableheader.="<th>".__('Client')."</th>";
					$saletableheader.="<th class='separator'></th>";
					
					// then add the production outcomes with a foreach
					$i=0;
					foreach ($allFinishedProducts as $finishedProduct){
						if ($visiblearray[$i]){
							$saletableheader.="<th colspan='3' class='centered'>".$finishedProduct['Product']['name']."</th>";
						}
						$i+=3;
					}
				$saletableheader.="</tr>";
				$saletableheader.="<tr>";
				$saletableheader.="<th></th>";
				$saletableheader.="<th></th>";
				$saletableheader.="<th></th>";

				$saletableheader.="<th class='separator'></th>";
				// then add the production outcomes with a foreach
				$i=0;
				foreach ($allFinishedProducts as $finishedProduct){
					foreach ($allProductionResultCodes as $productionResultCode){
						if ($visiblearray[$i]){
							$saletableheader.="<th class='centered'>".$productionResultCode['ProductionResultCode']['code']."</th>";
						}
						$i++;
					}
				}
				$saletableheader.="</tr>";
			$saletableheader.="</thead>";
			
			$saletablebody="<tbody>".$totalRows.$saleTableRows.$totalRows."</tbody>";
			$table_id=substr("preformas_".$salesDataForPreforma['RawMaterial']['name'],0,30);
			$saletable="<table id='".$table_id."'>";
			$saletable.=$saletableheader;
			$saletable.=$saletablebody;
			$saletable.="</table>";
		
			echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteSalidasMateriaPrima'), array( 'class' => 'btn btn-primary')); 
		
			echo "<h2>".__('Report')." salidas productos con Materia Prima ".$salesDataForPreforma['RawMaterial']['name']."</h2>";
			echo $saletable;
			
			$exportsaletables.=$saletable;
		}
	}
	$_SESSION['rawMaterialExitReport'] = $exportsaletables;
	
?>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
	});

</script>