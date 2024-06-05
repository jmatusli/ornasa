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
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
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
		width:200px; /* you can use % */
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
			$output.="<td class='bold' style='width:30%;'><img src='".$imageurl."' class='resize'></img></td>";		
			$output.="<td class='centered big' style='width:40%;'>".strtoupper(COMPANY_NAME)."<br/>COTIZACIÓN<br/>".$quotation['Quotation']['quotation_code']."</td>";
			$output.="<td class='bold' style='width:30%;'>MANAGUA, ".$quotationDateTime->format('d-m-Y')."</td>";
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:50%'>";
			$output.="<div>Cliente: <span class='underline'>".$quotation['Client']['name']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:50%'>";
			$output.="<div>Contacto: <span class='underline'>".$quotation['Contact']['fullname']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
			
		$output.="<tr>";
			$output.="<td style='width:30%'>";
			$output.="<div>Vendedor: <span class='underline'>".$quotation['User']['first_name']." ".$quotation['User']['last_name']."</span></div>";
			$output.="</td>";
			if (!empty($quotation['User']['phone'])){
				$output.="<td style='width:20%'>";
				$output.="<div>Teléfono: <span class='underline'>".$quotation['User']['phone']."</span></div>";
				$output.="</td>";
			}
			if (!empty($quotation['User']['email'])){
				$output.="<td style='width:50%'>";
				$output.="<div>Correo: <span class='underline'>".$quotation['User']['email']."</span></div>";
				$output.="</td>";
			}
		$output.="</tr>";
		
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	if (!empty($quotation['QuotationProduct'])){
		$output.="<table cellpadding = '0' cellspacing = '0'>";
			$output.="<tr>";
				$output.="<th>".__('Product')."</th>";
				$output.="<th>".__('Description')."</th>";
				$output.="<th>".__('Product Quantity')."</th>";
				$output.="<th>".__('Product Unit Price')."</th>";
				$output.="<th>".__('Product Total Price')."</th>";
			$output.="</tr>";
		foreach ($quotation['QuotationProduct'] as $quotationProduct){
			if ($quotationProduct['currency_id']==CURRENCY_CS){
				$classCurrency=" class='CScurrency'";
			}
			elseif ($quotationProduct['currency_id']==CURRENCY_USD){
				$classCurrency=" class='USDcurrency'";
			}
			$output.="<tr>";
				$output.="<td>".$quotationProduct['Product']['name']."</td>";
				$output.="<td>".$quotationProduct['product_description']."</td>";
				$output.="<td class='centered'>".number_format($quotationProduct['product_quantity'],0,".",",")."</td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_unit_price'],2,".",",")."</span></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_total_price'],2,".",",")."</span></td>";
			$output.="</tr>";
		}
			$output.="<tr class='totalrow'>";
				$output.="<td>Subtotal</td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_subtotal'],2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="<tr'>";
				$output.="<td>IVA</td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_iva'],2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="<tr class='totalrow'>";
				$output.="<td>Total</td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td></td>";
				$output.="<td><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotation['Quotation']['price_total'],2,".",",")."</span></td>";
			$output.="</tr>";
		$output.="</table>";
	}
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
	