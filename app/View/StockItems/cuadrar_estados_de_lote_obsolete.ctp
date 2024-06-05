<div class="stockItems view report">

<?php
	
	echo "<button id='onlyProblems' type='button'>".__('Only Show Problems')."</button>";
	echo "<h3>".$this->Html->link('Recreate All StockItemLogs',array('action' => 'recreateAllStockItemLogs'))."</h3>";

	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
			//echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
			
			echo $this->Form->input('Report.product_category_id',array('label'=>__('Categoría de Producto'),'default'=>$productCategoryId));
			echo $this->Form->input('Report.finished_product_id',array('label'=>__('Producto Fabricado'),'default'=>$finishedProductId,'empty'=>array('0'=>'Seleccione el producto fabricado')));
		echo "</fieldset>";
		//echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		//echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
		echo "<br/>";
	echo $this->Form->end(__('Refresh'));
	
	$rawmaterialtable="";
  $consumibleMaterialTable="";
	$producedmaterialtable="";
	$othermaterialtable="";
  
  //pr($allConsumibleStockItems);

	
	$rawmaterialtable="<table id='preformas'>";	
		$rawmaterialtable.="<thead>";
			$rawmaterialtable.="<tr>";
				$rawmaterialtable.="<th>StockItem Id</th>";
				$rawmaterialtable.="<th>Product Name</th>";
				$rawmaterialtable.="<th>Original Quantity (StockItem)</th>";
				$rawmaterialtable.="<th>Original based on Movements</th>";
				$rawmaterialtable.="<th>Quantity Used in Production</th>";
				$rawmaterialtable.="<th>Quantity Exited</th>";
				$rawmaterialtable.="<th>Remaining based on Movements</th>";
				$rawmaterialtable.="<th>Remaining based (StockItem)</th>";
				$rawmaterialtable.="<th>Remaining based on StockitemLog</th>";
				$rawmaterialtable.="<th>actions</th>";
			$rawmaterialtable.="</tr>";
		$rawmaterialtable.="</thead>";
		
		$rawmaterialtable.="<tbody>";
	
		$totalOriginalStockItem=0;
		$totalOriginalMovement=0;
		$totalUsedProduction=0;
		$totalExited=0;
		$totalRemainingStockItem=0;
		$totalRemainingStockItemLog=0;
		
		$productname="";
		if (!empty($allRawStockItems)){
			foreach ($allRawStockItems as $stockitem){
				if (!empty($stockitem['StockItem']['id'])){
					$remainingMovement=$stockitem['StockItem']['total_moved_in']-$stockitem['StockItem']['total_used_in_production']-$stockitem['StockItem']['total_moved_out'];
					if ($productname!=$stockitem['Product']['name']){
						if ($productname!=""){
							$rawmaterialtable.="<tr class='totalrow'>";
							$rawmaterialtable.="<td>Total</td>";
							$rawmaterialtable.="<td>".$productname."</td>";
							$rawmaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalStockItem."</td>";
							$rawmaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalMovement."</td>";
							$rawmaterialtable.="<td>".$totalUsedProduction."</td>";
							$rawmaterialtable.="<td>".$totalExited."</td>";
							$rawmaterialtable.="<td".($totalRemainingStockItem!=($totalOriginalMovement-$totalUsedProduction-$totalExited)?" class='warning'":"").">".($totalOriginalMovement-$totalUsedProduction-$totalExited)."</td>";
							$rawmaterialtable.="<td".(($totalRemainingStockItem!=($totalOriginalMovement-$totalUsedProduction-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?" class='warning'":"").">".$totalRemainingStockItem."</td>";
							$rawmaterialtable.="<td".($totalRemainingStockItem!=$totalRemainingStockItemLog?" class='warning'":"").">".$totalRemainingStockItemLog."</td>";
							$rawmaterialtable.="<td></td>";
							$rawmaterialtable.="</tr>";
						}
						
						$totalOriginalStockItem=$stockitem['StockItem']['original_quantity'];
						$totalOriginalMovement=$stockitem['StockItem']['total_moved_in'];
						$totalUsedProduction=$stockitem['StockItem']['total_used_in_production'];
						$totalExited=$stockitem['StockItem']['total_moved_out'];
						$totalRemainingStockItem=$stockitem['StockItem']['remaining_quantity'];
						$totalRemainingStockItemLog=$stockitem['StockItem']['latest_log_quantity'];
						
						$productname=$stockitem['Product']['name'];
					}
					else {
						$totalOriginalStockItem+=$stockitem['StockItem']['original_quantity'];
						$totalOriginalMovement+=$stockitem['StockItem']['total_moved_in'];
						$totalUsedProduction+=$stockitem['StockItem']['total_used_in_production'];
						$totalExited+=$stockitem['StockItem']['total_moved_out'];
						$totalRemainingStockItem+=$stockitem['StockItem']['remaining_quantity'];
						$totalRemainingStockItemLog+=$stockitem['StockItem']['latest_log_quantity'];
					}
					
					$rawmaterialtable.="<tr>";
					$rawmaterialtable.="<td>".$this->Html->link($stockitem['StockItem']['id'],array('action' => 'view', $stockitem['StockItem']['id']))."</td>";
					$rawmaterialtable.="<td>".$stockitem['Product']['name']."</td>";
					$rawmaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['original_quantity']."</td>";
					$rawmaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['total_moved_in']."</td>";
					$rawmaterialtable.="<td>".$stockitem['StockItem']['total_used_in_production']."</td>";
					$rawmaterialtable.="<td>".$stockitem['StockItem']['total_moved_out']."</td>";
					$rawmaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$remainingMovement?" class='warning'":"").">".$remainingMovement."</td>";
					$rawmaterialtable.="<td".(($stockitem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'])?" class='warning'":"").">".$stockitem['StockItem']['remaining_quantity']."</td>";
					$rawmaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity']?" class='warning'":"").">".$stockitem['StockItem']['latest_log_quantity']."</td>";
					//if ($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'] && ($stockitem['StockItem']['remaining_quantity']==$remainingMovement)){
						$rawmaterialtable.="<td>".$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockitem['StockItem']['id']))."</td>";
					//}
					//else {
					//	$rawmaterialtable.="<td></td>";
					//}
					$rawmaterialtable.="</tr>";
				}
			}
			$rawmaterialtable.="<tr class='totalrow'>";
				$rawmaterialtable.="<td>Total</td>";
				$rawmaterialtable.="<td>".$productname."</td>";
				$rawmaterialtable.="<td>".$totalOriginalStockItem."</td>";
				$rawmaterialtable.="<td>".$totalOriginalMovement."</td>";
				$rawmaterialtable.="<td>".$totalUsedProduction."</td>";
				$rawmaterialtable.="<td>".$totalExited."</td>";
				$rawmaterialtable.="<td>".($totalOriginalMovement-$totalUsedProduction-$totalExited)."</td>";
				$rawmaterialtable.="<td>".$totalRemainingStockItem."</td>";
				$rawmaterialtable.="<td>".$totalRemainingStockItemLog."</td>";
				$rawmaterialtable.="<td></td>";
			$rawmaterialtable.="</tr>";
		}
		$rawmaterialtable.="</tbody>";
	$rawmaterialtable.="</table>";
	
	$consumibleMaterialTable="<table id='consumibles'>";	
		$consumibleMaterialTable.="<thead>";
			$consumibleMaterialTable.="<tr>";
				$consumibleMaterialTable.="<th>StockItem Id</th>";
				$consumibleMaterialTable.="<th>Product Name</th>";
				$consumibleMaterialTable.="<th>Original Quantity (StockItem)</th>";
				$consumibleMaterialTable.="<th>Original based on Movements</th>";
				$consumibleMaterialTable.="<th>Quantity Used in Production</th>";
				$consumibleMaterialTable.="<th>Quantity Exited</th>";
				$consumibleMaterialTable.="<th>Remaining based on Movements</th>";
				$consumibleMaterialTable.="<th>Remaining based (StockItem)</th>";
				$consumibleMaterialTable.="<th>Remaining based on StockitemLog</th>";
				$consumibleMaterialTable.="<th>actions</th>";
			$consumibleMaterialTable.="</tr>";
		$consumibleMaterialTable.="</thead>";
		
		$consumibleMaterialTable.="<tbody>";
	
		$totalOriginalStockItem=0;
		$totalOriginalMovement=0;
		$totalUsedProduction=0;
		$totalExited=0;
		$totalRemainingStockItem=0;
		$totalRemainingStockItemLog=0;
		
		$productname="";
		if (!empty($allConsumibleStockItems)){
			foreach ($allConsumibleStockItems as $stockitem){
				if (!empty($stockitem['StockItem']['id'])){
					$remainingMovement=$stockitem['StockItem']['total_moved_in']-$stockitem['StockItem']['total_used_in_production']-$stockitem['StockItem']['total_moved_out'];
					if ($productname!=$stockitem['Product']['name']){
						if ($productname!=""){
							$consumibleMaterialTable.="<tr class='totalrow'>";
							$consumibleMaterialTable.="<td>Total</td>";
							$consumibleMaterialTable.="<td>".$productname."</td>";
							$consumibleMaterialTable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalStockItem."</td>";
							$consumibleMaterialTable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalMovement."</td>";
							$consumibleMaterialTable.="<td>".$totalUsedProduction."</td>";
							$consumibleMaterialTable.="<td>".$totalExited."</td>";
							$consumibleMaterialTable.="<td".($totalRemainingStockItem!=($totalOriginalMovement-$totalUsedProduction-$totalExited)?" class='warning'":"").">".($totalOriginalMovement-$totalUsedProduction-$totalExited)."</td>";
							$consumibleMaterialTable.="<td".(($totalRemainingStockItem!=($totalOriginalMovement-$totalUsedProduction-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?" class='warning'":"").">".$totalRemainingStockItem."</td>";
							$consumibleMaterialTable.="<td".($totalRemainingStockItem!=$totalRemainingStockItemLog?" class='warning'":"").">".$totalRemainingStockItemLog."</td>";
							$consumibleMaterialTable.="<td></td>";
							$consumibleMaterialTable.="</tr>";
						}
						
						$totalOriginalStockItem=$stockitem['StockItem']['original_quantity'];
						$totalOriginalMovement=$stockitem['StockItem']['total_moved_in'];
						$totalUsedProduction=$stockitem['StockItem']['total_used_in_production'];
						$totalExited=$stockitem['StockItem']['total_moved_out'];
						$totalRemainingStockItem=$stockitem['StockItem']['remaining_quantity'];
						$totalRemainingStockItemLog=$stockitem['StockItem']['latest_log_quantity'];
						
						$productname=$stockitem['Product']['name'];
					}
					else {
						$totalOriginalStockItem+=$stockitem['StockItem']['original_quantity'];
						$totalOriginalMovement+=$stockitem['StockItem']['total_moved_in'];
						$totalUsedProduction+=$stockitem['StockItem']['total_used_in_production'];
						$totalExited+=$stockitem['StockItem']['total_moved_out'];
						$totalRemainingStockItem+=$stockitem['StockItem']['remaining_quantity'];
						$totalRemainingStockItemLog+=$stockitem['StockItem']['latest_log_quantity'];
					}
					
					$consumibleMaterialTable.="<tr>";
					$consumibleMaterialTable.="<td>".$this->Html->link($stockitem['StockItem']['id'],array('action' => 'view', $stockitem['StockItem']['id']))."</td>";
					$consumibleMaterialTable.="<td>".$stockitem['Product']['name']."</td>";
					$consumibleMaterialTable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['original_quantity']."</td>";
					$consumibleMaterialTable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['total_moved_in']."</td>";
					$consumibleMaterialTable.="<td>".$stockitem['StockItem']['total_used_in_production']."</td>";
					$consumibleMaterialTable.="<td>".$stockitem['StockItem']['total_moved_out']."</td>";
					$consumibleMaterialTable.="<td".($stockitem['StockItem']['remaining_quantity']!=$remainingMovement?" class='warning'":"").">".$remainingMovement."</td>";
					$consumibleMaterialTable.="<td".(($stockitem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'])?" class='warning'":"").">".$stockitem['StockItem']['remaining_quantity']."</td>";
					$consumibleMaterialTable.="<td".($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity']?" class='warning'":"").">".$stockitem['StockItem']['latest_log_quantity']."</td>";
					//if ($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'] && ($stockitem['StockItem']['remaining_quantity']==$remainingMovement)){
						$consumibleMaterialTable.="<td>".$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockitem['StockItem']['id']))."</td>";
					//}
					//else {
					//	$consumibleMaterialTable.="<td></td>";
					//}
					$consumibleMaterialTable.="</tr>";
				}
			}
			$consumibleMaterialTable.="<tr class='totalrow'>";
				$consumibleMaterialTable.="<td>Total</td>";
				$consumibleMaterialTable.="<td>".$productname."</td>";
				$consumibleMaterialTable.="<td>".$totalOriginalStockItem."</td>";
				$consumibleMaterialTable.="<td>".$totalOriginalMovement."</td>";
				$consumibleMaterialTable.="<td>".$totalUsedProduction."</td>";
				$consumibleMaterialTable.="<td>".$totalExited."</td>";
				$consumibleMaterialTable.="<td>".($totalOriginalMovement-$totalUsedProduction-$totalExited)."</td>";
				$consumibleMaterialTable.="<td>".$totalRemainingStockItem."</td>";
				$consumibleMaterialTable.="<td>".$totalRemainingStockItemLog."</td>";
				$consumibleMaterialTable.="<td></td>";
			$consumibleMaterialTable.="</tr>";
		}
		$consumibleMaterialTable.="</tbody>";
	$consumibleMaterialTable.="</table>";
  
  
	$producedmaterialtable="<table id='finished'>";
	
		$producedmaterialtable.="<thead>";
			$producedmaterialtable.="<tr>";
				$producedmaterialtable.="<th>StockItem Id</th>";
				$producedmaterialtable.="<th>Raw Material</th>";
				$producedmaterialtable.="<th>Product Name</th>";
				$producedmaterialtable.="<th>Original Quantity (StockItem)</th>";
				$producedmaterialtable.="<th>Original based on Production</th>";
				
				$producedmaterialtable.="<th>Quantity Exited</th>";
				$producedmaterialtable.="<th>Remaining based on Movements</th>";
				$producedmaterialtable.="<th>Remaining based (StockItem)</th>";
				$producedmaterialtable.="<th>Remaining based on StockitemLog</th>";
				$producedmaterialtable.="<th>actions</th>";
			$producedmaterialtable.="</tr>";
		$producedmaterialtable.="</thead>";
		
		$producedmaterialtable.="<tbody>";
		
		$totalOriginalStockItem=0;
		$totalOriginalMovement=0;
		$totalExited=0;
		$totalRemainingStockItem=0;
		$totalRemainingStockItemLog=0;
		
		$productname="";
		if (!empty($allFinishedStockItems)){
		//	pr($allFinishedStockItems);
		
			foreach ($allFinishedStockItems as $stockitem){
				$remainingMovement=$stockitem['StockItem']['total_produced_in_production']-$stockitem['StockItem']['total_moved_out'];
				if ($productname!=$stockitem['Product']['name']."(".$stockitem['ProductionResultCode']['code'].")"){
					if ($productname!=""){
						$producedmaterialtable.="<tr class='totalrow'>";
						$producedmaterialtable.="<td>Total</td>";
						$producedmaterialtable.="<td>".$productname."</td>";
						$producedmaterialtable.="<td></td>";
						$producedmaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalStockItem."</td>";
						$producedmaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalMovement."</td>";
						$producedmaterialtable.="<td>".$totalExited."</td>";
						$producedmaterialtable.="<td".($totalRemainingStockItem!=($totalOriginalMovement-$totalExited)?" class='warning'":"").">".($totalOriginalMovement-$totalExited)."</td>";
						$producedmaterialtable.="<td".(($totalRemainingStockItem!=($totalOriginalMovement-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?" class='warning'":"").">".$totalRemainingStockItem."</td>";
						$producedmaterialtable.="<td".($totalRemainingStockItem!=$totalRemainingStockItemLog?" class='warning'":"").">".$totalRemainingStockItemLog."</td>";
						$producedmaterialtable.="</tr>";
					}
					
					$totalOriginalStockItem=$stockitem['StockItem']['original_quantity'];
					$totalOriginalMovement=$stockitem['StockItem']['total_produced_in_production'];
					$totalExited=$stockitem['StockItem']['total_moved_out'];
					$totalRemainingStockItem=$stockitem['StockItem']['remaining_quantity'];
					$totalRemainingStockItemLog=$stockitem['StockItem']['latest_log_quantity'];
					
					$productname=$stockitem['Product']['name']."(".$stockitem['ProductionResultCode']['code'].")";
				}
				else {
					$totalOriginalStockItem+=$stockitem['StockItem']['original_quantity'];
					$totalOriginalMovement+=$stockitem['StockItem']['total_produced_in_production'];
					$totalExited+=$stockitem['StockItem']['total_moved_out'];
					$totalRemainingStockItem+=$stockitem['StockItem']['remaining_quantity'];
					$totalRemainingStockItemLog+=$stockitem['StockItem']['latest_log_quantity'];
				}
				
				$producedmaterialtable.="<tr>";
				$producedmaterialtable.="<td>".$this->Html->link($stockitem['StockItem']['id'],array('action' => 'view', $stockitem['StockItem']['id']))."</td>";
				$producedmaterialtable.="<td>".$stockitem['RawMaterial']['name']."</td>";
				$producedmaterialtable.="<td>".$stockitem['Product']['name']."(".$stockitem['ProductionResultCode']['code'].")"."</td>";
				$producedmaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_produced_in_production']?" class='warning'":"").">".$stockitem['StockItem']['original_quantity']."</td>";
				$producedmaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_produced_in_production']?" class='warning'":"").">".$stockitem['StockItem']['total_produced_in_production']."</td>";
				$producedmaterialtable.="<td>".$stockitem['StockItem']['total_moved_out']."</td>";
				$producedmaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$remainingMovement?" class='warning'":"").">".$remainingMovement."</td>";
				$producedmaterialtable.="<td".(($stockitem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'])?" class='warning'":"").">".$stockitem['StockItem']['remaining_quantity']."</td>";
				$producedmaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity']?" class='warning'":"").">".$stockitem['StockItem']['latest_log_quantity']."</td>";
				//if ($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'] && ($stockitem['StockItem']['remaining_quantity']==$remainingMovement)){
					$producedmaterialtable.="<td>".$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockitem['StockItem']['id']))."</td>";
				//}
				//else {
				//	$producedmaterialtable.="<td></td>";
				//}
				$producedmaterialtable.="</tr>";
			
			}
			
			$producedmaterialtable.="<tr class='totalrow'>";
				$producedmaterialtable.="<td>Total</td>";
				$producedmaterialtable.="<td></td>";
				$producedmaterialtable.="<td>".$productname."</td>";
				$producedmaterialtable.="<td>".$totalOriginalStockItem."</td>";
				$producedmaterialtable.="<td>".$totalOriginalMovement."</td>";
				
				$producedmaterialtable.="<td>".$totalExited."</td>";
				$producedmaterialtable.="<td>".($totalOriginalMovement-$totalExited)."</td>";
				$producedmaterialtable.="<td>".$totalRemainingStockItem."</td>";
				$producedmaterialtable.="<td>".$totalRemainingStockItemLog."</td>";
			$producedmaterialtable.="</tr>";				
		
		}
	
		$producedmaterialtable.="</tbody>";
	$producedmaterialtable.="</table>";
	
	
	$othermaterialtable="<table id='finished'>";
	
		$othermaterialtable.="<thead>";
			$othermaterialtable.="<tr>";
				$othermaterialtable.="<th>StockItem Id</th>";
				$othermaterialtable.="<th>Product Name</th>";
				$othermaterialtable.="<th>Original Quantity (StockItem)</th>";
				$othermaterialtable.="<th>Original based on Movements</th>";
				
				$othermaterialtable.="<th>Quantity Exited</th>";
				$othermaterialtable.="<th>Remaining based on Movements</th>";
				$othermaterialtable.="<th>Remaining based (StockItem)</th>";
				$othermaterialtable.="<th>Remaining based on StockitemLog</th>";
				$othermaterialtable.="<th>actions</th>";
			$othermaterialtable.="</tr>";
		$othermaterialtable.="</thead>";
		
		$othermaterialtable.="<tbody>";
		
		$totalOriginalStockItem=0;
		$totalOriginalMovement=0;
		$totalExited=0;
		$totalRemainingStockItem=0;
		$totalRemainingStockItemLog=0;
		
		$productname="";
		if (!empty($allOtherStockItems)){
			foreach ($allOtherStockItems as $stockitem){
				$remainingMovement=$stockitem['StockItem']['total_moved_in']-$stockitem['StockItem']['total_moved_out'];
				if ($productname!=$stockitem['Product']['name']){
					if ($productname!=""){
						$othermaterialtable.="<tr class='totalrow'>";
						$othermaterialtable.="<td>Total</td>";
						$othermaterialtable.="<td>".$productname."</td>";
						$othermaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalStockItem."</td>";
						$othermaterialtable.="<td".($totalOriginalStockItem!=$totalOriginalMovement?" class='warning'":"").">".$totalOriginalMovement."</td>";
						$othermaterialtable.="<td>".$totalExited."</td>";
						$othermaterialtable.="<td".($totalRemainingStockItem!=($totalOriginalMovement-$totalExited)?" class='warning'":"").">".($totalOriginalMovement-$totalExited)."</td>";
						$othermaterialtable.="<td".(($totalRemainingStockItem!=($totalOriginalMovement-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?" class='warning'":"").">".$totalRemainingStockItem."</td>";
						$othermaterialtable.="<td".($totalRemainingStockItem!=$totalRemainingStockItemLog?" class='warning'":"").">".$totalRemainingStockItemLog."</td>";
						$othermaterialtable.="</tr>";
					}
					
					$totalOriginalStockItem=$stockitem['StockItem']['original_quantity'];
					$totalOriginalMovement=$stockitem['StockItem']['total_moved_in'];
					$totalExited=$stockitem['StockItem']['total_moved_out'];
					$totalRemainingStockItem=$stockitem['StockItem']['remaining_quantity'];
					$totalRemainingStockItemLog=$stockitem['StockItem']['latest_log_quantity'];
					
					$productname=$stockitem['Product']['name'];
				}
				else {
					$totalOriginalStockItem+=$stockitem['StockItem']['original_quantity'];
					$totalOriginalMovement+=$stockitem['StockItem']['total_moved_in'];
					$totalExited+=$stockitem['StockItem']['total_moved_out'];
					$totalRemainingStockItem+=$stockitem['StockItem']['remaining_quantity'];
					$totalRemainingStockItemLog+=$stockitem['StockItem']['latest_log_quantity'];
				}
				
				$othermaterialtable.="<tr>";
				$othermaterialtable.="<td>".$this->Html->link($stockitem['StockItem']['id'],array('action' => 'view', $stockitem['StockItem']['id']))."</td>";
				$othermaterialtable.="<td>".$stockitem['Product']['name']."</td>";
				$othermaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['original_quantity']."</td>";
				$othermaterialtable.="<td".($stockitem['StockItem']['original_quantity']!=$stockitem['StockItem']['total_moved_in']?" class='warning'":"").">".$stockitem['StockItem']['total_moved_in']."</td>";
				$othermaterialtable.="<td>".$stockitem['StockItem']['total_moved_out']."</td>";
				$othermaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$remainingMovement?" class='warning'":"").">".$remainingMovement."</td>";
				$othermaterialtable.="<td".(($stockitem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'])?" class='warning'":"").">".$stockitem['StockItem']['remaining_quantity']."</td>";
				$othermaterialtable.="<td".($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity']?" class='warning'":"").">".$stockitem['StockItem']['latest_log_quantity']."</td>";
				//if ($stockitem['StockItem']['remaining_quantity']!=$stockitem['StockItem']['latest_log_quantity'] && ($stockitem['StockItem']['remaining_quantity']==$remainingMovement)){
					$othermaterialtable.="<td>".$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockitem['StockItem']['id']))."</td>";
				//}
				//else {
				//	$othermaterialtable.="<td></td>";
				//}
				$othermaterialtable.="</tr>";
			}
			$othermaterialtable.="<tr class='totalrow'>";
				$othermaterialtable.="<td>Total</td>";
				$othermaterialtable.="<td></td>";
				$othermaterialtable.="<td>".$totalOriginalStockItem."</td>";
				$othermaterialtable.="<td>".$totalOriginalMovement."</td>";
				
				$othermaterialtable.="<td>".$totalExited."</td>";
				$othermaterialtable.="<td>".($totalOriginalMovement-$totalExited)."</td>";
				$othermaterialtable.="<td>".$totalRemainingStockItem."</td>";
				$othermaterialtable.="<td>".$totalRemainingStockItemLog."</td>";
			$othermaterialtable.="</tr>";
		}
		$othermaterialtable.="</tbody>";
	$othermaterialtable.="</table>";
	
	//echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteProductos'), array( 'class' => 'btn btn-primary')); 

	echo "<h2>".__('Raw Materials')."</h2>"; 
	echo $rawmaterialtable; 
  echo "<h2>".__('Consumibles')."</h2>"; 
	echo $consumibleMaterialTable; 
	echo "<h2>".__('Produced Materials')."</h2>"; 
	echo $producedmaterialtable; 
	echo "<h2>".__('Other Materials')."</h2>"; 
	echo $othermaterialtable; 
	
	$_SESSION['productsReport'] = $rawmaterialtable.$consumibleMaterialTable.$producedmaterialtable.$othermaterialtable;
?>

<script>
	$('#onlyProblems').click(function(){
		$("tbody tr:not(.totalrow)").each(function() {
			$(this).hide();
		});
		$("td.warning").each(function() {
			$(this).parent().show();
		});
	});
</script>