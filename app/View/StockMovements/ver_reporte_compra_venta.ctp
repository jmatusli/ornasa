<div class="stockMovements view report">
<?php 
	echo "<h2>".__('Reporte Compra Venta de Tapones')."</h2>"; 
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 

	$inventorytable="";
	$inventorytableheader="";
	$inventorytablebody="";
	
	$inventorytableheader.="<thead>";
		$inventorytableheader.="<tr>";
			$inventorytableheader.="<th class='orderdate'>".__('Date')."</th>";
			$inventorytableheader.="<th>".__('Proveedor o Cliente')."</th>";
			$inventorytableheader.="<th>".__('Invoice Code')."</th>";
			foreach($allOtherMaterials as $otherMaterial){
				//$inventorytableheader.="<th class='centered' colspan='4'>".$otherMaterial['Product']['name']."</th>";
				$inventorytableheader.="<th class='centered' colspan='4'>".$this->Html->link($otherMaterial['Product']['name'], array('controller' => 'products', 'action' => 'view', $otherMaterial['Product']['id']))."</th>";
			}
		$inventorytableheader.="</tr>";
	$inventorytableheader.="</thead>";


		$tablerows="";
		$tablerows.="<tr>";
			$tablerows.="<td></td>";
			$tablerows.="<td></td>";
			$tablerows.="<td>Inventario Inicial</td>";
			for ($i=0;$i<count($originalInventory);$i++){
				$tablerows.="<td class='centered number'>".$originalInventory[$i]."</td>";
			}
		$tablerows.="</tr>";
	
	foreach($resultMatrix as $row){
		//pr($row);
		$rowdate=new DateTime($row['date']);
		$tablerows.="<tr>";
			$tablerows.="<td class='orderdate'>".$rowdate->format('d-m-Y')."</td>";
			if ($row['providerbool']){
				$providerAction="verProveedor";
			}
			else {
				$providerAction="verCliente";
			}
			$tablerows.="<td>".$this->Html->Link($row['providerclient'],array('controller'=>'third_parties','action'=>$providerAction,$row['providerid']))."</td>";
			if ($row['entrybool']){
				$orderAction="verEntrada";
			}
			else {
        if ($row['issale']){
          $orderAction="verVenta";
        }
        else {
          $orderAction="verRemision";
        }
			}
			$tablerows.="<td>".$this->Html->Link($row['invoicecode'],array('controller'=>'orders','action'=>$orderAction,$row['invoiceid']))."</td>";
      //pr($row);
			for($i=0;$i<count($row)-8;$i++){
				if ($row[$i]>0){
					$tablerows.="<td class='centered number' style='font-weight:bold'>".$row[$i]."</td>";
				}
				else {
					$tablerows.="<td class='centered'>-</td>";
				}
			}
		$tablerows.="</tr>";
	}
	
	$totalrow="";
	$totalrow.="<tr class='totalrow'>";
		$totalrow.="<td>Total</td>";
		$totalrow.="<td></td>";
		$totalrow.="<td>Inventario Final</td>";
		for ($i=0;$i<count($currentInventory);$i++){
			if ($i%4!=2){
				$totalrow.="<td class='centered number'>".$currentInventory[$i]."</td>";
			}
			else {
				$totalrow.="<td class='centered'>".$currentInventory[$i]."</td>";
			}
		}
	$totalrow.="</tr>";
	
	$inventorytablebody="<tbody>";
		$inventorytablebody.="<tr>";
			$inventorytablebody.="<td></td>";
			$inventorytablebody.="<td></td>";
			$inventorytablebody.="<td></td>";
			foreach($allOtherMaterials as $otherMaterial){
				$inventorytablebody.="<td class='centered'>Entrada</td>";
				$inventorytablebody.="<td class='centered'>Salida</td>";
				$inventorytablebody.="<td class='centered'>Reclas</td>";
				$inventorytablebody.="<td class='centered'>Saldo</td>";
			}
		$inventorytablebody.="</tr>";
		$inventorytablebody.=$totalrow.$tablerows.$totalrow;
	$inventorytablebody.="</tbody>";
	
	$inventorytable="<table id='compra_venta_tapones'>".$inventorytableheader.$inventorytablebody."</table>";
		
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteCompraVenta'), array( 'class' => 'btn btn-primary')); 
	echo "<br/>";
	echo "<br/>";

	echo $inventorytable; 
	$_SESSION['reporteCompraVenta'] = $inventorytable;
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