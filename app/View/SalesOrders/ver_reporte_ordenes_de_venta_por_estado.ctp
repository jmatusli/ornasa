<div class="sales index" style='width:100%;'>
<?php	
	echo "<h2>".__('Reporte de Ordenes de Venta por Estado')."</h2>";
	
	echo $this->Form->create('Report'); 
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
			echo "<br/>";			
			echo $this->Form->input('Report.sales_order_product_status_id',array('label'=>__('Estado de Orden'),'default'=>$sales_order_product_status_id,'empty'=>array('0'=>__('Seleccione Estado'))));
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh')); 
	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteOrdenesDeVentaPorEstado'), array( 'class' => 'btn btn-primary')); 
	
	$startDateTime=new DateTime($startDate);
	$endDateTime=new DateTime($endDate);
	
	$output="";
	$excel="";
	
	foreach ($selectedStatuses as $status){
		if (!empty($status['SalesOrders'])){
			echo "<h3>Ordenes de Venta ".$status['SalesOrderProductStatus']['status']."</h3>";
			$outputhead="<thead>";
				$outputhead.="<tr>";
					$outputhead.="<th style='width:10%;'>".__('Date')."</th>";
					$outputhead.="<th style='width:20%;'>".__('Sales Order Code')."</th>";
					$outputhead.="<th style='width:20%;'>".__('Quotation Code')."</th>";
					$outputhead.="<th style='width:30%;'>".__('Client')."</th>";
					$outputhead.="<th  style='width:20%;'class='right centered'>".__('Subtotal')."</th>";
				$outputhead.="</tr>";
			$outputhead.="</thead>";
		
			$excelhead="<thead>";
				$excelhead.="<tr><th colspan='5' align='center'>".COMPANY_NAME."</th></tr>";	
				$excelhead.="<tr><th colspan='5' align='center'>".__('Reporte Ordenes de Venta por Estado')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
				$excelhead.="<tr>";
					$excelhead.="<th>".__('Date')."</th>";
					$excelhead.="<th>".__('Sales Order Code')."</th>";
					$excelhead.="<th>".__('Quotation Code')."</th>";
					$excelhead.="<th>".__('Client')."</th>";
					$excelhead.="<th class='centered'>".__('Subtotal')."</th>";
				$excelhead.="</tr>";
			$excelhead.="</thead>";
		
			$totalSubTotalCS=0;		
			$totalSubTotalUSD=0;
	
			$bodyRows="";
			foreach ($status['SalesOrders'] as $order){
				//pr($order);
				$orderDate=new DateTime($order['SalesOrder']['sales_order_date']);
				$currencyClass="";
				if ($order['SalesOrder']['currency_id']==CURRENCY_CS){
					$totalSubTotalCS+=$order['SalesOrder']['price_subtotal'];
					$currencyClass="class='CScurrency'";
				}
				else if ($order['SalesOrder']['currency_id']==CURRENCY_USD){
					$totalSubTotalUSD+=$order['SalesOrder']['price_subtotal'];
					$currencyClass="class='USDcurrency'";
				}
				
				
				$bodyRows.="<tr>";
					$bodyRows.="<td>".$orderDate->format('d-m-Y')."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['SalesOrder']['sales_order_code'],array('controller'=>'sales_orders','action'=>'view',$order['SalesOrder']['id']),array('target'=>'_blank'))."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['Quotation']['quotation_code'],array('controller'=>'quotations','action'=>'view',$order['Quotation']['id']),array('target'=>'_blank'))."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['Quotation']['Client']['name'],array('controller'=>'clients','action'=>'view',$order['Quotation']['Client']['id']),array('target'=>'_blank'))."</td>";
					//$bodyRows.="<td>".$this->Html->Link($order['Quotation']['Contact']['fullname'],array('controller'=>'contacts','action'=>'view',$order['Quotation']['Contact']['id']),array('target'=>'_blank'))."</td>";
					$bodyRows.="<td ".$currencyClass."><span class='amountright'>".$order['SalesOrder']['price_subtotal']."</span></td>";
				$bodyRows.="</tr>";
			}
			$totalRows="";
			if ($totalSubTotalCS>0){
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total C$</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='CScurrency'><span class='amountright'>".$totalSubTotalCS."</span></td>";
				$totalRows.="</tr>";
			}
			if ($totalSubTotalUSD>0){
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total US$</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='USDcurrency'><span class='amountright'>".$totalSubTotalUSD."</span></td>";
				$totalRows.="</tr>";
			}
			$body="<tbody>".$totalRows.$bodyRows.$totalRows."</tbody>";

			$table_id=substr(trim($status['SalesOrderProductStatus']['status']),0,30);
			echo "<table id='".$table_id."'>".$outputhead.$body."</table>";
			$excel.="<table id='".$table_id."'>".$excelhead.$body."</table>";
		}
	}
	
	//pr($annulledSalesOrders);
	if (!empty($annulledSalesOrders)){
		echo "<h3>Ordenes de Venta Anuladas</h3>";
		$outputhead="<thead>";
			$outputhead.="<tr>";
				$outputhead.="<th style='width:10%;'>".__('Date')."</th>";
				$outputhead.="<th style='width:20%;'>".__('Sales Order Code')."</th>";
				$outputhead.="<th style='width:20%;'>".__('Quotation Code')."</th>";
				$outputhead.="<th style='width:30%;'>".__('Client')."</th>";
				$outputhead.="<th  style='width:20%;'class='right centered'>".__('Subtotal')."</th>";
			$outputhead.="</tr>";
		$outputhead.="</thead>";
	
		$excelhead="<thead>";
			$excelhead.="<tr><th colspan='5' align='center'>".COMPANY_NAME."</th></tr>";	
			$excelhead.="<tr><th colspan='5' align='center'>".__('Reporte Ordenes de Venta Anuladas')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
			$excelhead.="<tr>";
				$excelhead.="<th>".__('Date')."</th>";
				$excelhead.="<th>".__('Sales Order Code')."</th>";
				$excelhead.="<th>".__('Quotation Code')."</th>";
				$excelhead.="<th>".__('Client')."</th>";
				$excelhead.="<th class='centered'>".__('Subtotal')."</th>";
			$excelhead.="</tr>";
		$excelhead.="</thead>";
	
		$totalSubTotalCS=0;		
		$totalSubTotalUSD=0;

		$bodyRows="";
		foreach ($annulledSalesOrders as $order){
			$orderDate=new DateTime($order['SalesOrder']['sales_order_date']);
			$currencyClass="";
			if ($order['SalesOrder']['currency_id']==CURRENCY_CS){
				$totalSubTotalCS+=$order['SalesOrder']['price_subtotal'];
				$currencyClass="class='CScurrency'";
			}
			else if ($order['SalesOrder']['currency_id']==CURRENCY_USD){
				$totalSubTotalUSD+=$order['SalesOrder']['price_subtotal'];
				$currencyClass="class='USDcurrency'";
			}
				
				
			$bodyRows.="<tr>";
					$bodyRows.="<td>".$orderDate->format('d-m-Y')."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['SalesOrder']['sales_order_code'],array('controller'=>'sales_orders','action'=>'view',$order['SalesOrder']['id']),array('target'=>'_blank'))."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['Quotation']['quotation_code'],array('controller'=>'quotations','action'=>'view',$order['Quotation']['id']),array('target'=>'_blank'))."</td>";	
					$bodyRows.="<td>".$this->Html->Link($order['Quotation']['Client']['name'],array('controller'=>'clients','action'=>'view',$order['Quotation']['Client']['id']),array('target'=>'_blank'))."</td>";
					//$bodyRows.="<td>".$this->Html->Link($order['Quotation']['Contact']['fullname'],array('controller'=>'contacts','action'=>'view',$order['Quotation']['Contact']['id']),array('target'=>'_blank'))."</td>";
					$bodyRows.="<td ".$currencyClass."><span class='amountright'>".$order['SalesOrder']['price_subtotal']."</span></td>";
				$bodyRows.="</tr>";
			}
			$totalRows="";
			if ($totalSubTotalCS>0){
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total C$</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='CScurrency'><span class='amountright'>".$totalSubTotalCS."</span></td>";
				$totalRows.="</tr>";
			}
			if ($totalSubTotalUSD>0){
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total US$</td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td></td>";
					$totalRows.="<td class='USDcurrency'><span class='amountright'>".$totalSubTotalUSD."</span></td>";
				$totalRows.="</tr>";
			}
			$body="<tbody>".$totalRows.$bodyRows.$totalRows."</tbody>";

			$table_id="Anuladas";
			echo "<table id='".$table_id."'>".$outputhead.$body."</table>";
			$excel.="<table id='".$table_id."'>".$excelhead.$body."</table>";
		}
	
	
	
	$_SESSION['reporteCotizacionesPorEjecutivo'] = $excel;
?>
</div>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	function formatPercentages(){
		$("td.percentage span.amountright").each(function(){
			$(this).number(true,0);
			$(this).append(" %");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			$(this).parent().prepend("US$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
	
</script>
