<div class="stockItems inventory">
	
	<h2>Inventario</h2>
<?php 
	echo $this->Form->create('Report'); 
	
	echo "<fieldset>"; 
		echo  $this->Form->input('Report.inventorydate',array('type'=>'date','label'=>__('Inventory Date'),'dateFormat'=>'DMY','default'=>$inventoryDate));
		echo  $this->Form->input('Report.warehouse_id',array('label'=>__('Warehouse'),'default'=>$warehouseId,'empty'=>array('0'=>'Todas Bodegas')));
	echo  "</fieldset>";
	echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo "<br/>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteInventario'), array( 'class' => 'btn btn-primary')); 
	echo "<br/>";
	echo "<br/>";
	echo $this->Html->link(__('Hoja de Inventario'), array('action' => 'verPdfHojaInventario','ext'=>'pdf',$inventoryDate,$filename),array( 'class' => 'btn btn-primary','target'=>'blank')); 
	
	echo "<h2>Preformas</h2>";
	$rawMaterialTable="<table id='preformas' cellpadding='0' cellspacing='0'>";
		$rawMaterialTable.="<thead>";
		$rawMaterialTable.="<tr>";
			$rawMaterialTable.="<th>".$this->Paginator->sort('Product.name')."</th>";
			if($userrole!=ROLE_FOREMAN) {
				$rawMaterialTable.="<th class='centered'>".__('Average Unit Price')."</th>";
			}
			$rawMaterialTable.="<th class='centered'>".$this->Paginator->sort('Remaining')."</th>";
			if($userrole!=ROLE_FOREMAN) {
				$rawMaterialTable.="<th class='centered'>".$this->Paginator->sort('Total Value')."</th>";
			}
		$rawMaterialTable.="</tr>";
		$rawMaterialTable.="</thead>";
		$rawMaterialTable.="<tbody>";
	
		$valuepreformas=0;
		$quantitypreformas=0; 
		$tableRows="";
		foreach ($preformas as $stockItem){
			$remaining="";
			$average="";
			$totalvalue="";
			if ($stockItem['0']['Remaining']!=""){
				$remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
				$packagingunit=$stockItem['Product']['packaging_unit'];
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0){
					$numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
					$leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
					$remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
					if ($leftovers >0){
						$remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
					}
					else {
						$remaining.=")";
					}
				}
				$average=$stockItem['0']['Remaining']>0?$stockItem['0']['Saldo']/$stockItem['0']['Remaining']:0;
				$totalvalue=$stockItem['0']['Saldo'];
				$valuepreformas+=$stockItem['0']['Saldo'];
				$quantitypreformas+=$stockItem['0']['Remaining'];
			}
			else {
				$remaining= "0";
				$average="0";
				$totalvalue="0";
			}
			$tableRows.= "<tr>";
			if($userrole!=ROLE_FOREMAN) {
				$tableRows.= "<td>".$this->Html->link($stockItem['Product']['name'], array('controller' => 'stockItems', 'action' => 'verReporteProducto', $stockItem['Product']['id']))."</td>";
				$tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
			}
			else {
				$tableRows.= "<td>".$stockItem['Product']['name']."</td>";
			}
			$tableRows.= "<td class='centered'>".$remaining."</td>";
			if($userrole!=ROLE_FOREMAN) {
				$tableRows.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
			}
	
			$tableRows.= "</tr>";
		}
		$totalRow="";
		$totalRow.= "<tr class='totalrow'>";
			$totalRow.= "<td>Total</td>";
			if($quantitypreformas>0){
				$avg=$valuepreformas/$quantitypreformas;
			}
			else {
				$avg=0;
			}
			if($userrole!=ROLE_FOREMAN) {
				$totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
			}
			$totalRow.= "<td class='centered number'>".$quantitypreformas."</td>";
			if($userrole!=ROLE_FOREMAN) {
				$totalRow.= "<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuepreformas."</span></td>";
			}
		$totalRow.= "</tr>";
		$rawMaterialTable.=$totalRow.$tableRows.$totalRow;
		$rawMaterialTable.= "</tbody>";
	$rawMaterialTable.= "</table>";
	echo $rawMaterialTable;
?>
	
	<h2>Botellas</h2>
	
<?php
	$finishedMaterialTable= "<table id='botellas' cellpadding='0' cellspacing='0'>";
		$finishedMaterialTable.= "<thead>";
			$finishedMaterialTable.= "<tr>";
				$finishedMaterialTable.= "<th>".$this->Paginator->sort('RawMaterial.name')."</th>";
				$finishedMaterialTable.= "<th>".$this->Paginator->sort('Product.name')."</th>";
				if($userrole!=ROLE_FOREMAN) {
					$finishedMaterialTable.= "<th class='centered'>".__('Average Unit Price')."</th>";
				}
				$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Remaining_A')."</th>";
				$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Remaining_B')."</th>";
				$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Remaining_C')."</th>";
				if($userrole!=ROLE_FOREMAN) {
					$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Value_A')."</th>";
					$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Value_B')."</th>";
					$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Value_C')."</th>";
				}
				$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Remaining')."</th>";
				if($userrole!=ROLE_FOREMAN) {
					$finishedMaterialTable.= "<th class='centered'>".$this->Paginator->sort('Total Value')."</th>";
				}
			$finishedMaterialTable.= "</tr>";
		$finishedMaterialTable.= "</thead>";
		$finishedMaterialTable.="<tbody>";

		$valuebotellasA=0;
		$quantitybotellasA=0; 
		$valuebotellasB=0;
		$quantitybotellasB=0; 
		$valuebotellasC=0;
		$quantitybotellasC=0; 
		$valuebotellas=0;
		$quantitybotellas=0; 
		
		$tableRows="";
		foreach ($productos as $stockItem){
			$average="";
			$remainingA=0;
			$remainingB=0;
			$remainingC=0;
			$remaining="";
			$totalvalueA="";
			$totalvalueB="";
			$totalvalueC="";
			$totalvalue="";
			$packagingunit=$stockItem['Product']['packaging_unit'];
			if ($stockItem['0']['Remaining_A']!=""){
				$remainingA= number_format($stockItem['0']['Remaining_A'],0,".",","); 
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0 && $stockItem['0']['Remaining_A']!=0){
					$numberpackagingunitsA=floor($stockItem['0']['Remaining_A']/$packagingunit);
					$leftoversA=$stockItem['0']['Remaining_A']-$numberpackagingunitsA*$packagingunit;
					$remainingA .= " (".$numberpackagingunitsA." ".__("emps");
					if ($leftoversA >0){
						$remainingA.= " + ".$leftoversA.")";
					}
					else {
						$remainingA.=")";
					}
				}
				$totalvalueA=$stockItem['0']['Saldo_A'];
				$valuebotellasA+=$stockItem['0']['Saldo_A'];
				$quantitybotellasA+=$stockItem['0']['Remaining_A'];
			}
			else {
				$remainingA= "0";
				$totalvalueA="0";
			}
			if ($stockItem['0']['Remaining_B']!=""){
				$remainingB= number_format($stockItem['0']['Remaining_B'],0,".",","); 
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0 && $stockItem['0']['Remaining_B']!=0){
					$numberpackagingunitsB=floor($stockItem['0']['Remaining_B']/$packagingunit);
					$leftoversB=$stockItem['0']['Remaining_B']-$numberpackagingunitsB*$packagingunit;
					$remainingB .= " (".number_format($numberpackagingunitsB,0,".",",")." ".__("emps");
					if ($leftoversB >0){
						$remainingB.= " + ".number_format($leftoversB,0,".",",").")";
					}
					else {
						$remainingB.=")";
					}
				}
				$totalvalueB=$stockItem['0']['Saldo_B'];
				$valuebotellasB+=$stockItem['0']['Saldo_B'];
				$quantitybotellasB+=$stockItem['0']['Remaining_B'];
			}
			else {
				$remainingB= "0";
				$totalvalueB="0";
			}
			if ($stockItem['0']['Remaining_C']!=""){
				$remainingC= number_format($stockItem['0']['Remaining_C'],0,".",","); 
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0 && $remainingC!=0){
					$numberpackagingunitsC=floor($stockItem['0']['Remaining_C']/$packagingunit);
					$leftoversC=$stockItem['0']['Remaining_C']-$numberpackagingunitsC*$packagingunit;
					$remainingC .= " (".number_format($numberpackagingunitsC,0,".",",")." ".__("emps");
					if ($leftoversC >0){
						$remainingC.= " + ".number_format($leftoversC,0,".",",").")";
					}
					else {
						$remainingC.=")";
					}
				}
				$totalvalueC=$stockItem['0']['Saldo_C'];
				$valuebotellasC+=$stockItem['0']['Saldo_C'];
				$quantitybotellasC+=$stockItem['0']['Remaining_C'];
			}
			else {
				$remainingC= "0";
				$totalvalueC="0";
			}
			$remaining=$stockItem['0']['Remaining_A']+$stockItem['0']['Remaining_B']+$stockItem['0']['Remaining_C'];
			$totalvalue=$totalvalueA+$totalvalueB+$totalvalueC;
			
			$valuebotellas+=$totalvalue;
			
			if (!empty($stockItem['0']['Remaining'])){
				//$remaining= h($stockItem['0']['Remaining']); 
				$average=$remaining>0?($stockItem['0']['Saldo']/$remaining):0;
				//$totalvalue=$stockItem['0']['Saldo'];
				$quantitybotellas+=$stockItem['0']['Remaining'];
			}
			else {
				$remaining= "0";
				$average="0";
				$totalvalue="0";
			}
			
			$tableRows.="<tr>";
				$tableRows.="<td>".$stockItem['RawMaterial']['name']."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $stockItem['Product']['id']))."</td>";
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
				}
				else {
					$tableRows.="<td>".$stockItem['Product']['name']."</td>";
				}
				$tableRows.="<td class='centered'>".$remainingA."</td>";
				$tableRows.="<td class='centered'>".$remainingB."</td>";
				$tableRows.="<td class='centered'>".$remainingC."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueA."</span></td>";
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueB."</span></td>";
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalueC."</span></td>";
				}
				$tableRows.="<td class='totalcolumn centered number'>".$remaining."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$tableRows.="<td class='totalcolumn centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";		
				}
			$tableRows.="</tr>";
		}
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				$totalRow.="<td></td>";
				if($quantitybotellas>0){
					$avg=$valuebotellas/$quantitybotellas;
				}
				else {
					$avg=0;
				}
				if($userrole!=ROLE_FOREMAN) {				
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
				}
				$totalRow.="<td class='centered number'>".$quantitybotellasA."</td>";
				$totalRow.="<td class='centered number'>".$quantitybotellasB."</td>";
				$totalRow.="<td class='centered number'>".$quantitybotellasC."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasA."</span></td>";
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasB."</span></td>";
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellasC."</span></td>";
				}
				$totalRow.="<td class='centered number'>".$quantitybotellas."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuebotellas."</span></td>";
				}
			$totalRow.="</tr>";
			$finishedMaterialTable.=$totalRow.$tableRows.$totalRow;
		$finishedMaterialTable.="</tbody>";
	$finishedMaterialTable.="</table>";
	
	echo $finishedMaterialTable;
?>	
	
	<!-- TAPONES -->
	<h2>Tapones</h2>
	
<?php	
	$otherMaterialTable="<table id='tapones' cellpadding='0' cellspacing='0'>";
		$otherMaterialTable.="<thead>";
			$otherMaterialTable.="<tr>";
				$otherMaterialTable.="<th>".$this->Paginator->sort('Product.name')."</th>";
				if($userrole!=ROLE_FOREMAN) {
					$otherMaterialTable.="<th class='centered'>".__('Average Unit Price')."</th>";
				}
				$otherMaterialTable.="<th class='centered'>".$this->Paginator->sort('Remaining')."</th>";
				if($userrole!=ROLE_FOREMAN) {
					$otherMaterialTable.="<th class='centered'>".$this->Paginator->sort('Total Value')."</th>";
				}
			$otherMaterialTable.="</tr>";
		$otherMaterialTable.="</thead>";
		$otherMaterialTable.="<tbody>";

		$valuetapones=0;
		$quantitytapones=0; 
		$tableRows="";
		foreach ($tapones as $stockItem){
			$remaining="";
			$average="";
			$totalvalue="";
			if ($stockItem['0']['Remaining']!=""){
				$remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
				$packagingunit=$stockItem['Product']['packaging_unit'];
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0){
					$numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
					$leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
					$remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
					if ($leftovers >0){
						$remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
					}
					else {
						$remaining.=")";
					}
				}
				$average=$stockItem['0']['Remaining']>0?number_format($stockItem['0']['Saldo']/$stockItem['0']['Remaining'],4,".",","):0;
				$totalvalue=$stockItem['0']['Saldo'];
				$valuetapones+=$stockItem['0']['Saldo'];
				$quantitytapones+=$stockItem['0']['Remaining'];
			}
			else {
				$remaining= "0";
				$average="0";
				$totalvalue="0";
			}
			$tableRows.="<tr>";
				$tableRows.="<td>".$this->Html->link($stockItem['Product']['name'], array('controller' => 'stockMovements', 'action' => 'verReporteCompraVenta', $stockItem['Product']['id']))."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$average."</span></td>";
				}
				$tableRows.="<td class='centered'>".$remaining."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$tableRows.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$totalvalue."</span></td>";
				}
			$tableRows.="</tr>";
		}
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				if($quantitytapones>0){
					$avg=$valuetapones/$quantitytapones;
				}
				else {
					$avg=0;
				}
				if($userrole!=ROLE_FOREMAN) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$avg."</span></td>";
				}
				$totalRow.="<td class='centered number'>".$quantitytapones."</td>";
				if($userrole!=ROLE_FOREMAN) {
					$totalRow.="<td class='centered currency'><span class='currency'></span><span class='amountright'>".$valuetapones."</span></td>";
				}
			$totalRow.="</tr>";
			$otherMaterialTable.=$totalRow.$tableRows.$totalRow;
		$otherMaterialTable.="</tbody>";
	$otherMaterialTable.="</table>";
	echo $otherMaterialTable;
	
	$_SESSION['inventoryReport'] = $rawMaterialTable.$finishedMaterialTable.$otherMaterialTable;
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