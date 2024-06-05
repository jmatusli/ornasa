<script>
	$('#onlyProblems').click(function(){
		$("tbody tr:not(.totalrow)").each(function() {
			$(this).hide();
		});
		$("td.warning").each(function() {
			$(this).parent().show();
		});
	});
  
  $('body').on('change','#ReportProductCategoryId',function(){
    if ($(this).val() == <?php echo CATEGORY_PRODUCED; ?>){
      $('#ReportFinishedProductId').closest('div').removeClass('d-none');
    }
    else {
      $('#ReportFinishedProductId').closest('div').addClass('d-none');
    }
	});	
  
  $('document').ready(function(){
    if ($('#ReportProductCategoryId').val() == <?php echo CATEGORY_PRODUCED; ?>){
      $('#ReportFinishedProductId').closest('div').removeClass('d-none');
    }
    else {
      $('#ReportFinishedProductId').closest('div').addClass('d-none');
    }
  });
</script>
<div class="stockItems view report">
<?php
	echo "<button id='onlyProblems' type='button'>".__('Only Show Problems')."</button>";
	echo "<h3>".$this->Html->link('Recreate All StockItemLogs',['action' => 'recreateAllStockItemLogs'])."</h3>";

	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->PlantFilter->displayPlantFilter($plants, $userRoleId,$plantId);              
      echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
			//echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
			
			echo $this->Form->input('Report.product_category_id',['label'=>'Categoría de Producto','default'=>$productCategoryId]);
			echo $this->Form->input('Report.finished_product_id',['label'=>'Producto Fabricado','default'=>$finishedProductId,'empty'=>[0=>'-- Producto fabricado --']]);
		echo "</fieldset>";
		//echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		//echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
		echo "<br/>";
	echo $this->Form->end(__('Refresh'));
	
  if ($plantId == 0){
    echo '<h2>Se debe seleccionar la planta.</h2>';
  }
  else {
    $productTable="";
    $producedMaterialTable="";
    
    if ($productCategoryId == CATEGORY_PRODUCED){
      $producedMaterialTable='<table id="'.$productCategories[$productCategoryId].'">';
        $producedMaterialTable.='<thead>';
          $producedMaterialTable.='<tr>';
            $producedMaterialTable.='<th>Warehouse</th>';
            $producedMaterialTable.='<th>StockItem Id</th>';
            $producedMaterialTable.='<th>Product Name</th>';
          if ($plantId == PLANT_SANDINO){  
            $producedMaterialTable.='<th>Raw Material</th>';
          }
            $producedMaterialTable.='<th>Original Quantity (StockItem)</th>';
            $producedMaterialTable.='<th>Original based on Production</th>';
            $producedMaterialTable.='<th>Quantity Exited</th>';
            $producedMaterialTable.='<th>Remaining based on Movements</th>';
            $producedMaterialTable.='<th>Remaining based (StockItem)</th>';
            $producedMaterialTable.='<th>Remaining based on StockitemLog</th>';
            $producedMaterialTable.='<th>actions</th>';
          $producedMaterialTable.='</tr>';
        $producedMaterialTable.='</thead>';
        
        $totalOriginalStockItem=0;
        $totalOriginalMovement=0;
        $totalExited=0;
        $totalRemainingStockItem=0;
        $totalRemainingStockItemLog=0;
        
        $productName='';
        
        $producedMaterialTable.='<tbody>';
        
        if (!empty($allStockItems)){
          foreach ($allStockItems as $stockItem){
            $remainingMovement=$stockItem['StockItem']['total_produced_in_production']-$stockItem['StockItem']['total_moved_out'];
            if ($productName!=$stockItem['Product']['name'].'('.$stockItem['ProductionResultCode']['code'].')'){
              if ($productName!=''){
                $producedMaterialTable.='<tr class="totalrow">';
                  $producedMaterialTable.='<td>Total</td>';
                  $producedMaterialTable.='<td></td>';
                  $producedMaterialTable.='<td>'.$productName.'</td>';
                if ($plantId == PLANT_SANDINO){  
                  $producedMaterialTable.='<td></td>';
                }  
                  $producedMaterialTable.='<td'.($totalOriginalStockItem!=$totalOriginalMovement?' class="warning"':'').'>'.$totalOriginalStockItem.'</td>';
                  $producedMaterialTable.='<td'.($totalOriginalStockItem!=$totalOriginalMovement?' class="warning"':'').'>'.$totalOriginalMovement.'</td>';
                  $producedMaterialTable.='<td>'.$totalExited.'</td>';
                  $producedMaterialTable.='<td'.($totalRemainingStockItem!=($totalOriginalMovement-$totalExited)?' class="warning"':'').'>'.($totalOriginalMovement-$totalExited).'</td>';
                  $producedMaterialTable.='<td'.(($totalRemainingStockItem!=($totalOriginalMovement-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?' class="warning"':'').'>'.$totalRemainingStockItem.'</td>';
                  $producedMaterialTable.='<td'.($totalRemainingStockItem!=$totalRemainingStockItemLog?' class="warning"':'').'>'.$totalRemainingStockItemLog.'</td>';
                $producedMaterialTable.='</tr>';
              }
              
              $totalOriginalStockItem=$stockItem['StockItem']['original_quantity'];
              $totalOriginalMovement=$stockItem['StockItem']['total_produced_in_production'];
              $totalExited=$stockItem['StockItem']['total_moved_out'];
              $totalRemainingStockItem=$stockItem['StockItem']['remaining_quantity'];
              $totalRemainingStockItemLog=$stockItem['StockItem']['latest_log_quantity'];
              
              $productName=$stockItem['Product']['name'].'('.$stockItem['ProductionResultCode']['code'].')';
            }
            else {
              $totalOriginalStockItem+=$stockItem['StockItem']['original_quantity'];
              $totalOriginalMovement+=$stockItem['StockItem']['total_produced_in_production'];
              $totalExited+=$stockItem['StockItem']['total_moved_out'];
              $totalRemainingStockItem+=$stockItem['StockItem']['remaining_quantity'];
              $totalRemainingStockItemLog+=$stockItem['StockItem']['latest_log_quantity'];
            }
            
            $producedMaterialTable.='<tr>';
              $producedMaterialTable.='<td>'.$stockItem['Warehouse']['name'].'</td>';
              $producedMaterialTable.='<td>'.$this->Html->link($stockItem['StockItem']['id'],['action' => 'view', $stockItem['StockItem']['id']]).'</td>';
              $producedMaterialTable.='<td>'.$stockItem['Product']['name'].'('.$stockItem['ProductionResultCode']['code'].')'.'</td>';
            if ($plantId == PLANT_SANDINO){    
              $producedMaterialTable.='<td>'.$stockItem['RawMaterial']['name'].'</td>';
            }  
              $producedMaterialTable.='<td'.($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['total_produced_in_production']?' class="warning"':'').'>'.$stockItem['StockItem']['original_quantity'].'</td>';
              $producedMaterialTable.='<td'.($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['total_produced_in_production']?' class="warning"':'').'>'.$stockItem['StockItem']['total_produced_in_production'].'</td>';
              $producedMaterialTable.='<td>'.$stockItem['StockItem']['total_moved_out'].'</td>';
              $producedMaterialTable.='<td'.($stockItem['StockItem']['remaining_quantity']!=$remainingMovement?' class="warning"':'').'>'.$remainingMovement.'</td>';
              $producedMaterialTable.='<td'.(($stockItem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockItem['StockItem']['remaining_quantity']!=$stockItem['StockItem']['latest_log_quantity'])?' class="warning"':'').'>'.$stockItem['StockItem']['remaining_quantity'].'</td>';
              $producedMaterialTable.='<td'.($stockItem['StockItem']['remaining_quantity']!=$stockItem['StockItem']['latest_log_quantity']?' class="warning"':'').'>'.$stockItem['StockItem']['latest_log_quantity'].'</td>';
              $producedMaterialTable.='<td>'.$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockItem['StockItem']['id'])).'</td>';
            $producedMaterialTable.='</tr>';
          
          }
          
          $producedMaterialTable.='<tr class="totalrow">';
            $producedMaterialTable.='<td>Total</td>';
            $producedMaterialTable.='<td></td>';
            $producedMaterialTable.='<td>'.$productName.'</td>';
          if ($plantId == PLANT_SANDINO){    
            $producedMaterialTable.='<td></td>';
          }  
            $producedMaterialTable.='<td>'.$totalOriginalStockItem.'</td>';
            $producedMaterialTable.='<td>'.$totalOriginalMovement.'</td>';
            $producedMaterialTable.='<td>'.$totalExited.'</td>';
            $producedMaterialTable.='<td>'.($totalOriginalMovement-$totalExited).'</td>';
            $producedMaterialTable.='<td>'.$totalRemainingStockItem.'</td>';
            $producedMaterialTable.='<td>'.$totalRemainingStockItemLog.'</td>';
          $producedMaterialTable.='</tr>';				
        
        }
      
        $producedMaterialTable.='</tbody>';
      $producedMaterialTable.='</table>';

    }
    else {
      $productTable='<table id="'.$productCategories[$productCategoryId].'">';	
        $productTable.='<thead>';
          $productTable.='<tr>';
            $productTable.='<th>Warehouse</th>';
            $productTable.='<th>StockItem Id</th>';
            $productTable.='<th>Product Name</th>';
            $productTable.='<th>Original Quantity (StockItem)</th>';
            $productTable.='<th>Original based on Movements</th>';
            $productTable.='<th>Quantity Used in Production</th>';
            $productTable.='<th>Quantity Exited</th>';
            $productTable.='<th>Remaining based on Movements</th>';
            $productTable.='<th>Remaining based (StockItem)</th>';
            $productTable.='<th>Remaining based on StockitemLog</th>';
            $productTable.='<th>actions</th>';
          $productTable.='</tr>';
        $productTable.='</thead>';
      
        $totalOriginalStockItem=0;
        $totalOriginalMovement=0;
        $totalUsedProduction=0;
        $totalExited=0;
        $totalProducedInProduction=0;
        $totalRemainingStockItem=0;
        $totalRemainingStockItemLog=0;
        
        $productName="";
        
        $productTable.='<tbody>';
      
        if (!empty($allStockItems)){
          foreach ($allStockItems as $stockItem){
            if (!empty($stockItem['StockItem']['id'])){
              //pr($stockItem);
              $remainingMovement=$stockItem['StockItem']['total_moved_in']-$stockItem['StockItem']['total_used_in_production']-$stockItem['StockItem']['total_moved_out']+$stockItem['StockItem']['total_produced_in_production'];
              if ($productName != $stockItem['Product']['name']){
                if ($productName != ''){
                  $productTable.='<tr class="totalrow">';
                    $productTable.='<td>Total</td>';
                    $productTable.='<td></td>';
                    $productTable.='<td>'.$productName.'</td>';
                    $productTable.='<td'.($totalOriginalStockItem !=($totalOriginalMovement + $totalProducedInProduction)?' class="warning"':'').'>'.$totalOriginalStockItem.'</td>';
                    $productTable.='<td'.($totalOriginalStockItem !=($totalOriginalMovement + $totalProducedInProduction)?' class="warning"':'').'>'.($totalOriginalMovement + $totalProducedInProduction).'</td>';
                    $productTable.='<td>'.$totalUsedProduction.'</td>';
                    $productTable.='<td>'.$totalExited.'</td>';
                    $productTable.='<td'.($totalRemainingStockItem !=($totalOriginalMovement-$totalUsedProduction-$totalExited)?' class="warning"':'').'>'.($totalOriginalMovement - $totalUsedProduction - $totalExited + $totalProducedInProduction).'</td>';
                    $productTable.='<td'.(($totalRemainingStockItem !=($totalOriginalMovement-$totalUsedProduction-$totalExited))||($totalRemainingStockItem!=$totalRemainingStockItemLog)?' class="warning"':'').'>'.$totalRemainingStockItem.'</td>';
                    $productTable.='<td'.($totalRemainingStockItem !=$totalRemainingStockItemLog?' class="warning"':'').'>'.$totalRemainingStockItemLog.'</td>';
                    $productTable.='<td></td>';
                  $productTable.='</tr>';
                }
                
                $totalOriginalStockItem=$stockItem['StockItem']['original_quantity'];
                $totalOriginalMovement=$stockItem['StockItem']['total_moved_in'];
                $totalUsedProduction=$stockItem['StockItem']['total_used_in_production'];
                $totalExited=$stockItem['StockItem']['total_moved_out'];
                $totalProducedInProduction=$stockItem['StockItem']['total_produced_in_production'];
                $totalRemainingStockItem=$stockItem['StockItem']['remaining_quantity'];
                $totalRemainingStockItemLog=$stockItem['StockItem']['latest_log_quantity'];
                
                $productName=$stockItem['Product']['name'];
              }
              else {
                $totalOriginalStockItem+=$stockItem['StockItem']['original_quantity'];
                $totalOriginalMovement+=$stockItem['StockItem']['total_moved_in'];
                $totalUsedProduction+=$stockItem['StockItem']['total_used_in_production'];
                $totalExited+=$stockItem['StockItem']['total_moved_out'];
                $totalProducedInProduction+=$stockItem['StockItem']['total_produced_in_production'];
                $totalRemainingStockItem+=$stockItem['StockItem']['remaining_quantity'];
                $totalRemainingStockItemLog+=$stockItem['StockItem']['latest_log_quantity'];
              }
              
              $productTable.='<tr>';
                $productTable.='<td>'.$stockItem['Warehouse']['name'].'</td>';
                $productTable.='<td>'.$this->Html->link($stockItem['StockItem']['id'],['action' => 'view', $stockItem['StockItem']['id']]).'</td>';
                $productTable.='<td>'.$stockItem['Product']['name'].'</td>';
                $productTable.='<td'.($stockItem['StockItem']['original_quantity'] != ($stockItem['StockItem']['total_moved_in'] + $stockItem['StockItem']['total_produced_in_production'])?' class="warning"':'').'>'.$stockItem['StockItem']['original_quantity'].'</td>';
                $productTable.='<td'.($stockItem['StockItem']['original_quantity'] != ($stockItem['StockItem']['total_moved_in'] + $stockItem['StockItem']['total_produced_in_production'])?' class="warning"':'').'>'.($stockItem['StockItem']['total_moved_in'] + $stockItem['StockItem']['total_produced_in_production']).'</td>';
                $productTable.='<td>'.$stockItem['StockItem']['total_used_in_production'].'</td>';
                $productTable.='<td>'.$stockItem['StockItem']['total_moved_out'].'</td>';
                $productTable.='<td'.($stockItem['StockItem']['remaining_quantity']!=$remainingMovement?' class="warning"':'').'>'.$remainingMovement.'</td>';
                $productTable.='<td'.(($stockItem['StockItem']['remaining_quantity']!=$remainingMovement)||($stockItem['StockItem']['remaining_quantity']!=$stockItem['StockItem']['latest_log_quantity'])?' class="warning"':'').'>'.$stockItem['StockItem']['remaining_quantity'].'</td>';
                $productTable.='<td'.($stockItem['StockItem']['remaining_quantity']!=$stockItem['StockItem']['latest_log_quantity']?' class="warning"':'').'>'.$stockItem['StockItem']['latest_log_quantity'].'</td>';
                $productTable.='<td>'.$this->Html->link('Recreate StockItemLogs',array('action' => 'recreateStockItemLogsForSquaring', $stockItem['StockItem']['id'])).'</td>';
              $productTable.='</tr>';
            }
          }
          $productTable.='<tr class="totalrow">';
            $productTable.='<td>Total</td>';
            $productTable.='<td></td>';
            $productTable.='<td>'.$productName.'</td>';
            $productTable.='<td>'.$totalOriginalStockItem.'</td>';
            $productTable.='<td>'.($totalOriginalMovement + $totalProducedInProduction).'</td>';
            $productTable.='<td>'.$totalUsedProduction.'</td>';
            $productTable.='<td>'.$totalExited.'</td>';
            $productTable.='<td>'.($totalOriginalMovement - $totalUsedProduction - $totalExited + $totalProducedInProduction).'</td>';
            $productTable.='<td>'.$totalRemainingStockItem.'</td>';
            $productTable.='<td>'.$totalRemainingStockItemLog.'</td>';
            $productTable.='<td></td>';
          $productTable.='</tr>';
        }
        $productTable.='</tbody>';
      $productTable.='</table>';
    }	
    
    //echo $this->Html->link('Guardar como Excel', ['action' => 'guardarReporteProductos'], ['class' => 'btn btn-primary']);
    echo '<h1>'.$productCategories[$productCategoryId].'</h1>'; 
    if ($productCategoryId == CATEGORY_PRODUCED){
      echo $producedMaterialTable; 
    }
    else {
      echo $productTable; 
    }
	
    //$_SESSION['productsReport'] = $productTable.$consumibleMaterialTable.$producedMaterialTable.$otherMaterialTable;
  }  
?>
</div>

