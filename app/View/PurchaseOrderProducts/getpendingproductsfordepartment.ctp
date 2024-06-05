<?php
	$options="<option value='0'>Seleccione Producto</option>";
	//pr($purchaseOrderProductsForDepartment);
	if (!empty($purchaseOrderProductsForDepartment)){
		foreach ($purchaseOrderProductsForDepartment as $product){
			$options.="<option value='".$product['PurchaseOrderProduct']['id']."'>".$product['Product']['name']." (".$product['ProductionOrder']['production_order_code']." Cantidad: ".$product['PurchaseOrderProduct']['product_quantity'].") </option>";
		}
	}
	echo $options;
?>