<?php
	
	if (!empty($salesOrders)){
		//pr($salesOrder);
		foreach ($salesOrders as $salesOrder){
			$salesOrderDateTime=new DateTime($salesOrder['SalesOrder']['sales_order_date']);
			echo "<h3 style='clear:left;'>Resumen Orden Venta ".$salesOrder['SalesOrder']['sales_order_code']."</h3>";
			echo "<dl>";
				echo "<dt>Ejecutivo</dt>";
				echo "<dd>".$this->Html->link($salesOrder['Quotation']['quotation_code'],array('controller'=>'quotations','action'=>'view',$salesOrder['Quotation']['id']))."</dd>";
				echo "<dt>Cliente</dt>";
				echo "<dd>".$this->Html->link($salesOrder['Quotation']['Client']['name'],array('controller'=>'clients','action'=>'view',$salesOrder['Quotation']['Client']['id']))."</dd>";
				echo "<dt>Contacto</dt>";
				echo "<dd>".$this->Html->link($salesOrder['Quotation']['Contact']['first_name']." ".$salesOrder['Quotation']['Contact']['last_name'],array('controller'=>'contacts','action'=>'view',$salesOrder['Quotation']['Contact']['id']))."</dd>";
				echo "<dt>Teléfono</dt>";
				echo "<dd>".$salesOrder['Quotation']['Contact']['phone']."</a></dd>";
				echo "<dt>Correo</dt>";
				echo "<dd><a href='mailto:".$salesOrder['Quotation']['Contact']['email']."'>".$salesOrder['Quotation']['Contact']['email']."</a></dd>";
				echo "<dt>Fecha Orden</dt>";
				echo "<dd>".$salesOrderDateTime->format('d-m-Y')."</dd>";
			echo "</dl>";
		}
	}
?>
<script>
	$(document).ajaxComplete(function() {	
	});
</script>