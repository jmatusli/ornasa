<style>
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	div.centered{
		text-align:center;
	}
	div.right{
		text-align:right;
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	#ordercode {
		position:absolute;
		left:40em;
		top:5em;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		border-width:1px;
		border-style:solid;
		border-color:#000000;
	}
</style>
<?php
	$output="";
	
	$output.="<div class='entries viewPDF'>";
	
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	
	//$output.="<div class='half right'>TELÉFONOS OFICINA: </div>";
	//$output.="<div class='half'><span class='bold'>".strtoupper(COMPANY_PHONE)."</span></div>";
	//$output.="<div class='half right'>FAX: </div>";
	//$output.="<div class='half'><span class='bold'>".strtoupper(COMPANY_FAX)."</span></div>";
	$output.="<div class='centered small'>TELEFONOS OFICINA: <span class='bold'>".COMPANY_PHONE."</span></div>";
	$output.="<div class='centered small'>FAX: <span class='bold'>".COMPANY_FAX."</span></div>";
	
	$output.="<div class='centered big bold'>Entrada en Bodega</div>";
	$output.="<div id='ordercode'>No ".$entry['Entry']['entry_code']."</div>";
	
	$output.="<table style='width:100%'>";
	$output.="<tr>";
	$output.="<td style='width:70%'>";
	$output.="<div>Recibimos de:<span class='underline'>".(!empty($transporterName)?$transporterName:"-")."</span></div>";
	$output.="</td>";
	$entrydate= new DateTime($entry['Entry']['entry_date']);
	$output.="<td style='width:30%'>";
	$output.="<div>Fecha:<span class='underline'>".$entrydate->format('d-m-Y')."</span></div>";
	$output.="</td>";
	$output.="</tr>";
	$output.="<tr>";
	$output.="<td style='width:70%'>";
	$output.="<div>Proveedor:<span class='underline'>".$entry['Invoice']['Provider']['company_name']."</span></div>";
	$output.="</td>";
	$output.="</tr>";
	$output.="</table>";
	
	$output.="<br/>";
	
	$output.="<table cellpadding = '0' cellspacing = '0' class='bordered'  style='width:100%'>";
	$output.="<thead>";
	$output.="<tr>";
	$output.="<th class='centered'>CANTIDAD</th>";
	$output.="<th class='centered'>UNIDAD</th>";
	$output.="<th>PRODUCTO</th>";
	//$output.="<th>FINCA</th>";
	//$output.="<th>RUBRO</th>";
	$output.="<th class='centered'>VALOR UNITARIO</th>";
	//$output.="<th class='centered'>TOTAL</th>";
	$output.="</tr>";
	$output.="</thead>";
	
	$output.="<tbody>";
	//$totalcost=0;
	foreach ($entryProducts as $entryProduct){
		/*
		$rubro="";
		if ($purchaseOrderProduct['PurchaseOrderProduct']['crop_id']>0){
			$rubro.=$purchaseOrderProduct['Crop']['name']; 
		}
		if ($purchaseOrderProduct['PurchaseOrderProduct']['machine_id']>0){
			$rubro.=(strlen($rubro)>0?"|":"").$purchaseOrderProduct['Machine']['code']; 
		}
		if ($purchaseOrderProduct['PurchaseOrderProduct']['labor_id']>0){
			$rubro.=(strlen($rubro)>0?"|":"").$purchaseOrderProduct['Labor']['name']; 
		}
		*/
		$output.="<tr>";
		$output.="<td class='centered'>".$entryProduct['StockMovement']['product_quantity']."</td>";
		$output.="<td class='centered'>".$entryProduct['MeasuringUnit']['abbreviation']."</td>";
		$output.="<td>".h($entryProduct['Product']['fullname'])."</td>";
		/*
		if ($purchaseOrderProduct['PurchaseOrderProduct']['client_id']>0){
			$output.="<td>".$purchaseOrderProduct['Client']['company_name']."</td>"; 
		}
		else {
			$output.="<td>-</td>";
		}
		if (strlen($rubro)>0){
			$output.="<td>".$rubro."</td>"; 
		}
		else {
			$output.="<td>-</td>";
		}
		*/
		$output.="<td class='centered'>".number_format($entryProduct['StockMovement']['product_unit_cost'],2,".",",")." ".$entryProduct['Currency']['abbreviation']."</td>";
		//$output.="<td class='centered'>".number_format($purchaseOrderProduct['PurchaseOrderProduct']['product_total_cost'],2,".",",")." ".$purchaseOrder['Currency']['abbreviation']."</td>"; 
		//$totalcost+=$purchaseOrderProduct['PurchaseOrderProduct']['product_total_cost'];
		$output.="</tr>";
	}
	/*
	$output.="<tr class='totalrow'>";
	$output.="<td>Total</td>";
	$output.="<td></td>";
	$output.="<td></td>";
	$output.="<td></td>";
	$output.="<td></td>";
	$output.="<td></td>";
	$output.="<td class='centered'>".number_format($totalcost,2,".",",")." ".$purchaseOrder['Currency']['abbreviation']."</td>";
	$output.="</tr>";
	*/
	$output.="</tbody>";
	$output.="</table>";
	if (!empty($entry['Entry']['observation'])){
		$output.="<div>Observation:<span class='underline'>".$entry['Entry']['observation']."</span></div>";
	}
	
	$output.="<table style='width:100%'>";
		$output.="<tr style='margin-bottom:2em;'>";
			$output.="<td style='width:40%'>";
				$output.="<span class='underline'>                                  &nbsp;</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
				$output.="<span class='underline'>                                  &nbsp;</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
				$output.="<span class='underline'>&nbsp;</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr style='margin-bottom:2em;'>";
			$output.="<td style='width:40%;border-top:1px solid black;margin-right:10px;'>";
				$output.="<div class='bold'>Recibo conforme bodega ".$entry['Worker']['fullname']."</div>";
			$output.="</td>";
			$output.="<td style='width:20%;border-top:1px solid black;margin-right:10px;'>";
				$output.="<div class='bold'>Entrega</div>";
			$output.="</td>";
			$output.="<td style='width:20%;border-top:1px solid black;'>";
				$output.="<div class='bold'>Autorizado</div>";
			$output.="</td>";
		$output.="</tr>";
		
	$output.="</table>";
	$output.="<div>Original: Factura Copia amarilla bodega Copia Verde Consecutivo</div>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	