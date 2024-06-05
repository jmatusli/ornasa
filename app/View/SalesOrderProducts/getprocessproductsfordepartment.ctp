<?php
	$options="<option value='0'>Seleccione Producto</option>";
	//pr($salesOrderProductsForDepartment);
	if (!empty($salesOrderProductsForDepartment)){
		foreach ($salesOrderProductsForDepartment as $product){
			$options.="<option value='".$product['SalesOrderProduct']['id']."'>".$product['Product']['name']." (".$product['SalesOrder']['ProductionOrder'][0]['production_order_code']." Cantidad: ".$product['SalesOrderProduct']['product_quantity'].") </option>";
		}
	}
	echo $options;
?>