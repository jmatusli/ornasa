<div class="stockItems view report">

<?php
	
  echo $this->Form->create('Report');
  echo "<fieldset>";
    echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
    echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
  echo "</fieldset>";
  echo "<button id='previousmonth' class='monthswitcher'>". __('Previous Month')."</button>";
  echo "<button id='nextmonth' class='monthswitcher'>". __('Next Month')."</button>";
  echo $this->Form->end(__('Refresh')); 
				
  
	echo "<button id='onlyProblems' type='button'>".__('Only Show Problems')."</button>";
	//echo "<h3>".$this->Html->link('Recreate All Bottle Costs',array('action' => 'recreateAllBottleCosts'))."</h3>";

		
	$producedmaterialtable="<table id='finished'>";
	
	$producedmaterialtable.="<thead>";
	$producedmaterialtable.="<tr>";
	$producedmaterialtable.="<th>StockItem Id</th>";
	$producedmaterialtable.="<th>StockItem Creation Date</th>";
	$producedmaterialtable.="<th>Raw Material</th>";
	$producedmaterialtable.="<th>Product Name</th>";
	
	$producedmaterialtable.="<th>Price Bottle Creation Movement</th>";
	$producedmaterialtable.="<th>Price Bottle StockItem</th>";
	//$producedmaterialtable.="<th>Price StockItemLog</th>";
	$producedmaterialtable.="<th>Price Preforma Based</th>";
	$producedmaterialtable.="<th>Price StockItemLog</th>";
	
	$producedmaterialtable.="<th></th>";
	$producedmaterialtable.="<th></th>";
	$producedmaterialtable.="</tr>";
	$producedmaterialtable.="</thead>";
	
	$producedmaterialtable.="<tbody>";
	
	foreach ($allFinishedStockItems as $stockitem){
		//pr($stockitem);
		if ($stockitem['StockItem']['movement_price']!=-1){
			$producedmaterialtable.="<tr>";
			
			$producedmaterialtable.="<td>".$this->Html->link($stockitem['StockItem']['id'],array('action' => 'view', $stockitem['StockItem']['id']))."</td>";
			$producedmaterialtable.="<td>".$stockitem['StockItem']['stockitem_creation_date']."</td>";
			$producedmaterialtable.="<td>".$stockitem['RawMaterial']['name']."</td>";
			$producedmaterialtable.="<td>".$stockitem['Product']['name']."(".$stockitem['ProductionResultCode']['code'].")"."</td>";
			
			$producedmaterialtable.="<td".(round($stockitem['StockItem']['movement_price'],4)!=round($stockitem['StockItem']['right_price'],4)?" class='warning'":"").">".$stockitem['StockItem']['movement_price']."</td>";
			$producedmaterialtable.="<td".(round($stockitem['StockItem']['product_unit_price'],4)!=round($stockitem['StockItem']['right_price'],4)?" class='warning'":"").">".$stockitem['StockItem']['product_unit_price']."</td>";
			//$producedmaterialtable.="<td".(round($stockitem['StockItem']['stockitemlog_price'],4)!=round($stockitem['StockItem']['right_price'],4)?" class='warning'":"").">".$stockitem['StockItem']['stockitemlog_price']."</td>";
			$producedmaterialtable.="<td>".$stockitem['StockItem']['right_price']."</td>";
			
			$producedmaterialtable.="<td>".$this->Html->link('Recreate ProductionMovement Bottle Price',array('action' => 'recreateProductionMovementPriceForSquaring', $stockitem['StockItem']['production_movement_id'],$stockitem['StockItem']['right_price']))."</td>";
			$producedmaterialtable.="<td>".$this->Html->link('Recreate StockItem Bottle Price',array('action' => 'recreateStockItemPriceForSquaring', $stockitem['StockItem']['id'],$stockitem['StockItem']['right_price']))."</td>";
			
			$producedmaterialtable.="</tr>";
		}
	}
		
	$producedmaterialtable.="</tbody>";
	
	$producedmaterialtable.="</table>";
	
	echo "<h2>".__('Produced Materials')."</h2>"; 
	echo $producedmaterialtable; 
	
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