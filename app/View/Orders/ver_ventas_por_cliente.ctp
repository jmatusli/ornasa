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
	$salesPerPeriod=array();
	for ($i=0;$i<count($monthArray);$i++){
		$salesPerPeriod[$i]=0;
	}
	
	$salestable="";
	$tableid="venta_cierre_".substr($client['ThirdParty']['company_name'],0,17);
	$salestable="<table id='".$tableid."' >";
	$salestable.="<thead>";
	$salestable.="<tr>";
	$salestable.="<th>Fecha</th>";
	$salestable.="<th>".__('Sale')."</th>";
	$salestable.="<th class='centered'>Cantidad Envase</th>";
	$salestable.="<th class='centered'>Cantidad Tapones</th>";
	$salestable.="<th class='centered'>Precio</th>";
	$salestable.="<th class='centered'>Costo</th>";
	$salestable.="<th class='centered'>Utilidad</th>";
	$salestable.="</tr>";
	$salestable.="</thead>";
	
	$salestable.="<tbody>";	
	foreach ($salesArray as $clientSale){
		//pr($clientSale);
		foreach ($clientSale['sales'] as $sale){
			//pr($sale);	
			if ($sale['product_total_price']>0){
				$salestable.="<tr>";
				$orderDate=new DateTime($sale['order_date']);
				$salestable.="<td>".$orderDate->format('d-m-Y')."</td>";
				$salestable.="<td>".$this->Html->Link($sale['order_code'], array('action' => 'verVenta',$sale['order_id']))."</td>";
				$salestable.="<td class='centered number'>".$sale['amount_bottles']."</td>";
				$salestable.="<td class='centered number'>".$sale['amount_caps']."</td>";
				$salestable.="<td class='centered currency'><span>".$sale['product_total_price']."</span></td>";
				$salestable.="<td class='centered currency'><span>".$sale['product_total_cost']."</span></td>";
				$salestable.="<td class='centered currency'><span>".$sale['product_total_utility']."</span></td>";
				$salestable.="</tr>";
			}
		}
		
		
		$salestable.="<tr class='totalrow'>";
		$salestable.="<td>".$clientSale['period']."</td>";
		$salestable.="<td></td>";
		$salestable.="<td class='centered number'>".$clientSale['totalQuantityProducedMonth']."</td>";
		$salestable.="<td class='centered number'>".$clientSale['totalQuantityOtherMonth']."</td>";
		
		$monthlysale=($clientSale['totalSaleMonth']=="")?0:$clientSale['totalSaleMonth'];
		$monthlycost=($clientSale['totalCostMonth']=="")?0:$clientSale['totalCostMonth'];
		$salestable.="<td class='centered currency'><span>".$monthlysale."</span></td>";
		$salestable.="<td class='centered currency'><span>".$monthlycost."</span></td>";
		$salestable.="<td class='centered currency'><span>".($monthlysale-$monthlycost)."</span></td>";
		$salestable.="</tr>";
	}
	
	$salestable.="<tr class='totalrow'>";
	$salestable.="<td>Total</td>";
	$salestable.="<td></td>";
	$salestable.="<td class='centered number'>".$totals['totalQuantityProduced']."</td>";
	$salestable.="<td class='centered number'>".$totals['totalQuantityOther']."</td>";
	$salestable.="<td class='centered currency'><span>".$totals['totalSale']."</span></td>";
	$salestable.="<td class='centered currency'><span>".$totals['totalCost']."</span></td>";
	$salestable.="<td class='centered currency'><span>".$totals['totalProfit']."</span></td>";
	$salestable.="</tr>";
	$salestable.="</tbody>";
	$salestable.="</table>";

	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteVentasCliente',$client['ThirdParty']['company_name']), array( 'class' => 'btn btn-primary')); 
	
	echo "<h2>".__('Resumen de Venta para Cliente')." ".$client['ThirdParty']['company_name']."</h2>"; 
	echo $salestable; 
	
	$_SESSION['reporteVentasPorCliente'] = $salestable;
?>
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
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});
	
</script>