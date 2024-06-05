<div class="stockItems inventory">
	
	<h2>Inventario</h2>
<?php 
	echo $this->Form->create('Report'); 
	
	echo "<fieldset>"; 
		//echo  $this->Form->input('Report.warehouse_id',array('label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>array('0'=>'Todas Bodegas')));
	echo  "</fieldset>";
	echo $this->Form->end(__('Refresh')); 
	echo "<br/>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'saveStockItemInfo'), array( 'class' => 'btn btn-primary')); 
	
	echo "<h2>Lotes</h2>";
	$stockItemTable="<table id='stockitems' cellpadding='0' cellspacing='0'>";
		$stockItemTable.="<thead>";
      $stockItemTable.="<tr>";
        $stockItemTable.="<th>".__('StockItem.id')."</th>";
        $stockItemTable.="<th>".__('StockItem.name')."</th>";
        $stockItemTable.="<th>".__('StockItem.stockitem_creation_date')."</th>";
        $stockItemTable.="<th>".__('StockItem.remaining_quantity')."</th>";
        $stockItemTable.="<th>".__('StockItemLog.stockitem_date')."</th>";
        $stockItemTable.="<th>".__('StockItemLog.product_quantity')."</th>";
      $stockItemTable.="</tr>";
		$stockItemTable.="</thead>";
		$stockItemTable.="<tbody>";
    $tableRows= "";
		foreach ($stockItems as $stockItem){
			$tableRows.= "<tr>";
        $tableRows.="<td>".$stockItem['StockItem']['id']."</td>";
        $tableRows.="<td>".$stockItem['StockItem']['name']."</td>";
        $tableRows.="<td>".$stockItem['StockItem']['stockitem_creation_date']."</td>";
        $tableRows.="<td>".$stockItem['StockItem']['remaining_quantity']."</td>";
        $tableRows.="<td>".$stockItem['StockItemLog'][0]['stockitem_date']."</td>";
        $tableRows.="<td>".$stockItem['StockItemLog'][0]['product_quantity']."</td>";
      $tableRows.= "</tr>";
		}
		$stockItemTable.=$tableRows;
		$stockItemTable.= "</tbody>";
	$stockItemTable.= "</table>";
	echo $stockItemTable;
  
  echo "<p>";
	echo $this->Paginator->counter([
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	]);
	echo "</p>";
	echo "<div class='paging'>";
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	echo "</div>";
  
  $_SESSION['stockItemInfo'] = $stockItemTable;
  
?>
</div>	
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});
</script>