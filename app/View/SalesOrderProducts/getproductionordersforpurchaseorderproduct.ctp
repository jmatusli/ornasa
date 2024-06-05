<?php
	$options="<option value='0'>Seleccione Producto</option>";
	//pr($productionOrdersForPurchaseOrderProduct);
	if (!empty($productionOrdersForPurchaseOrderProduct)){
		foreach ($productionOrdersForPurchaseOrderProduct as $ProductionOrder){
			$options.="<option value='".$ProductionOrder['ProductionOrder']['id']."'>".$ProductionOrder['ProductionOrder']['production_order_code']."</option>";
		}
	}
	echo $options;
?>