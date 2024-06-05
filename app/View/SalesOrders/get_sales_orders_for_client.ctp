<?php
	$options="<option value='0'>Seleccione Orden de Venta</option>";
	//pr($salesOrdersForClient);
	if (!empty($salesOrdersForClient)){
		foreach ($salesOrdersForClient as $salesOrder){
			$options.="<option value='".$salesOrder['SalesOrder']['id']."'>".$salesOrder['SalesOrder']['sales_order_code']."</option>";
		}
	}
	echo $options;
?>