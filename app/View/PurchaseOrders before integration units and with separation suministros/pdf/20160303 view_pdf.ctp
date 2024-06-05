<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
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
	
	table.bordered {
		border-collapse:collapse; 
	}
	

	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.8em;
		border-width:2px;
		border-style:solid;
		
		border-color:#000000;
		vertical-align:top;
	}
	td.noleftbottomborder {
		border:0px!important;
		border-color:#FFFFFF;
	}
	td span.right
	{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
	
	img.resize {
		width:400px; /* you can use % */
		height: auto;
	}
</style>
<?php
	$quotationDate=date("Y-m-d",strtotime($quotation['Quotation']['quotation_date']));
	$quotationDateTime=new DateTime($quotationDate);
	$dueDate=date("Y-m-d",strtotime($quotation['Quotation']['due_date']));
	$dueDateTime=new DateTime($dueDate);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	$url="img/logo.jpg";
	$imageurl=$this->App->assetUrl($url);
	
	$output="";
	$output.="<table>";
		$output.="<tr>";
			$output.="<td class='bold' style='width:60%;'><img src='".$imageurl."' class='resize'></img></td>";		
			$output.="<td class='left small' style='width:40%;'>";
				$output.="Managua, ".$quotationDateTime->format('d-m-Y')."<br/>";
				$output.=$quotation['Quotation']['quotation_code']."<br/>";
				$output.="Teléfono: <span class='bold'>2277-5313 ext.108</span><br/>";
				$output.="Ext o Cell: <span class='underline'>".$quotation['User']['phone']."</span>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td class='centered'>";
				$output.="<div><span class='big bold centered'>".$quotation['Client']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td class='centered' style='width:80%'>";
				$output.="<div>Atención: ".$quotation['Contact']['fullname']."</div>";
				$output.="<div><span class='bold'>RUC </span><span class='underline'>".$quotation['Client']['ruc']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<div class='centered bold'>Permitanos Poner a su Consideración la Siguiente Cotización</div>";
	if (!empty($quotation['QuotationProduct'])){
		$output.="<table  class='bordered'>";
			//$output.="<tr>";
				//$output.="<td class='centered bold'>Permitanos Poner a su Consideración la Siguiente Cotización</td>";
			//$output.="</tr>";
			$output.="<tr>";
				$output.="<th>".__('Cantidad')."</th>";
				$output.="<th>".__('Código')."</th>";
				//$output.="<th>".__('Product')."</th>";
				$output.="<th>".__('Descripción del Servicio')."</th>";
				$output.="<th>".__('Unitario')."</th>";
				$output.="<th>".__('Sub-Total')."</th>";
			$output.="</tr>";
		foreach ($quotation['QuotationProduct'] as $quotationProduct){
			if ($quotationProduct['currency_id']==CURRENCY_CS){
				$classCurrency=" class='CScurrency'";
			}
			elseif ($quotationProduct['currency_id']==CURRENCY_USD){
				$classCurrency=" class='USDcurrency'";
			}
			$output.="<tr>";
				$output.="<td class='centered'>".number_format($quotationProduct['product_quantity'],0,".",",")."</td>";
				$output.="<td>".$quotationProduct['Product']['code']."</td>";
				//$output.="<td>".$quotationProduct['Product']['name']."</td>";
				$output.="<td>".$quotationProduct['product_description']."</td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_unit_price'],2,".",",")."</span></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_total_price'],2,".",",")."</span></td>";
			$output.="</tr>";
		}
			$output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td><span class='bold'>Subtotal</span></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_subtotal'],2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td><span class='bold'>IVA</span></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_iva'],2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td><span class='bold'>Total</span></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_total'],2,".",",")."</span></td>";
			$output.="</tr>";
		$output.="</table>";
	}
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:20%'>Cheque</td>";
			$output.="<td style='width:80%'>Cheque a Nombre de Mas Publicidad</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:20%'>Forma de Pago</td>";
			$output.="<td style='width:80%'>".$quotation['Quotation']['payment_form']."</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:20%'>Tiempo de Entrega</td>";
			$output.="<td style='width:80%'>".$quotation['Quotation']['delivery_time']."</td>";
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$roleName="";
	switch ($quotation['User']['role_id']){
		case ROLE_ADMIN: 
			$roleName="Gerente";
			break;
		case ROLE_ASSISTANT: 
			$roleName="Asistente Ejecutivo";
			break;
		case ROLE_VENDOR: 
			$roleName="Ejecutivo de Venta";
			break;	
	}
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:40%' class='centered bold'>".$quotation['User']['first_name']." ".$quotation['User']['last_name']." ".$roleName."</td>";
			$output.="<td style='width:60%'></td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:40%' class='centered'>Cel: ".$quotation['User']['phone']."</td>";
			$output.="<td style='width:60%'></td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:40%' class='centered'>Tel: 2277-5313 ext.108</td>";
			$output.="<td style='width:60%'></td>";
			
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<div>Dirección: De los Semáforos de ENEL Central 200 Mts al Norte</div>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
	