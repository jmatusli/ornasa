<?php
	$tableContents="";
	if (!empty($purchaseOrderProducts)){
		//pr($purchaseOrderProducts);
		$tableContents.="<thead>";
			$tableContents.="<tr>";
				$tableContents.="<th>Orden de Producción</th>";
				$tableContents.="<th>Departamento</th>";
				$tableContents.="<th>Producto</th>";
				$tableContents.="<th style='width:20%;'>Descripción</th>";
				$tableContents.="<th>Cantidad</th>";
				$tableContents.="<th>Costo Unitario</th>";
				$tableContents.="<th>Costo Total</th>";
				$tableContents.="<th>Producto Recibido</th>";
				$tableContents.="<th>Fecha de Recepción</th>";
			$tableContents.="</tr>";
		$tableContents.="</thead>";
		$tableContents.="<tbody>";
		for ($pop=0;$pop<count($purchaseOrderProducts);$pop++){
			$tableContents.="<tr row='".$pop."'>";
					
				if (!empty($purchaseOrderProducts[$pop]['ProductionOrder'])){
					$tableContents.="<td class='productionorderid'>".$this->Html->link($purchaseOrderProducts[$pop]['ProductionOrder']['production_order_code'],array('controller'=>'production_orders','action'=>'view',$purchaseOrderProducts[$pop]['ProductionOrder']['id']))."</td>";
				}
				else {
					$tableContents.="<td class='productionorderid'>-</td>";
				}
				if (!empty($purchaseOrderProducts[$pop]['Department'])){
					$tableContents.="<td class='departmentid'>".$purchaseOrderProducts[$pop]['Department']['name']."</td>";
				}
				else {
					$tableContents.="<td class='departmentid'>-</td>";
				}
				$tableContents.="<td class='productid'>";
					$tableContents.=$purchaseOrderProducts[$pop]['Product']['name'];
					$tableContents.=$this->Form->input('PurchaseOrderProduct.'.$pop.'.id',array('label'=>false,'value'=>$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['id'],'type'=>'hidden'));
					$tableContents.=$this->Form->input('PurchaseOrderProduct.'.$pop.'.sales_order_product_id',array('label'=>false,'value'=>$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['sales_order_product_id'],'type'=>'hidden'));
				$tableContents.="</td>";
				$tableContents.="<td class='productdescription'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_description']."</td>";
				$tableContents.="<td class='productquantity amount'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_quantity']."</td>";
				if ($currencyId==CURRENCY_CS){
					$tableContents.="<td class='productunitcost CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_unit_cost']."</span></td>";
					$tableContents.="<td class='producttotalcost CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_total_cost']."</span></td>";
				}
				if ($currencyId==CURRENCY_USD){
					$tableContents.="<td class='productunitcost USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_unit_cost']."</span></td>";
					$tableContents.="<td class='producttotalcost USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['product_total_cost']."</span></td>";
				}

				$tableContents.="<td class='boolreceived'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.bool_received',array('label'=>false,'default'=>$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['bool_received']))."</td>";
				$tableContents.="<td class='datereceived'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.date_received',array('label'=>false,'dateFormat'=>'DMY','default'=>$purchaseOrderProducts[$pop]['PurchaseOrderProduct']['date_received']))."</td>";
			$tableContents.="</tr>";
		}
		$tableContents.="</tbody>";
	}
	echo $tableContents;
?>
<script>
	
</script>