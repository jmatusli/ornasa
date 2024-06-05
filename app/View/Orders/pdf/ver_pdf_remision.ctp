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
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$output="";
	$output.="<div class='remissions view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>REMISION # ".$order['Order']['order_code']."</div>";
	$orderDate=new DateTime($order['Order']['order_date']);
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div>Cliente :<span class='underline'>".$order['ThirdParty']['company_name']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$orderDate->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
  $output.="<table>";	
    $output.="<tr>";
			$output.="<td style='width:100%'>";
      if (!empty($order['Order']['comment'])){
        $output.="<div>".__('Comment').": <p>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$order['Order']['comment']))."</p></div>";
      }
      else {
        $output.="<div>".__('Comment').": -</div>";
      }
			$output.="</td>";
		$output.="</tr>";
		if (!empty($cashReceipt)){
			$currencyAbbreviation="C$";
			if ($cashReceipt['CashReceipt']['currency_id']==CURRENCY_USD){
				$currencyAbbreviation="US$";
			}
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Precio Total: ".$currencyAbbreviation."<span class='underline'>".number_format($cashReceipt['CashReceipt']['amount'],2,".",",")."</span></div>";
				$output.="</td>";
			$output.="</tr>";
			if (!empty($cashReceipt['CashboxAccountingCode']['description'])){
				$output.="<tr>";
					$output.="<td style='width:100%'>";
					$output.="<div>Pagado a caja <span class='underline'>".$cashReceipt['CashboxAccountingCode']['description']."</span></div>";
					$output.="</td>";
				$output.="</tr>";
			}
		}
	$output.="</table>";
	
	if (!empty($summedMovements)){
	$output.="<h3>".__('Productos en Remisión')."</h3>";
		$output.="<table cellpadding = '0' cellspacing = '0'>";
			$output.="<thead>";
				$output.="<tr>";
					$output.="<th>".__('Product')."</th>";
					$output.="<th class='centered' style='width:10%'>".__('Unit Price')."</th>";
					$output.="<th class='centered' style='width:15%'>".__('Quantity')."</th>";
					$output.="<th class='centered' style='width:25%'>".__('Total Price')."</th>";
					$output.="<th class='centered' style='width:10%'>".__('Empaque')."</th>";
				$output.="</tr>";
			$output.="</thead>";
			
			$totalquantity=0;
			$totalprice=0;
			$output.="<tbody>";
			foreach ($summedMovements as $summedMovement){ 
				$output.="<tr>";
				if ($summedMovement['StockMovement']['production_result_code_id']>0){
					$output.="<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
				}
				else {
					$output.="<td>".$summedMovement['Product']['name']."</td>";
				}
				$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],4,".",",")."</span></td>";
				$output.="<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
				$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
				
				$productpackingunit=$summedMovement['Product']['packaging_unit'];
				$productquantity=$summedMovement[0]['total_product_quantity'];
				$packunits=0;
				$remainder=0;
				$extraunits=0;
				$packingtext="";
				if ($productpackingunit>0){
					$packunits=floor($productquantity/$productpackingunit);
					$remainder=$productquantity-$productpackingunit*$packunits;
					$extraunits=$remainder%$productpackingunit;
					if ($packunits>0){
						$packingtext.=$packunits." emp + ".$extraunits." unds";
					}
					else {
						$packingtext.=$extraunits." unds";
					}
				}
				else {
					$packingtext.=$productquantity." unds";
				}
				
				$output.="<td class='centered'>".$packingtext."</td>";
				
				$totalquantity+=$summedMovement[0]['total_product_quantity'];
				$totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
				$output.="</tr>";
			}
		
				$output.="<tr class='totalrow'>";
					$output.="<td>Total</td>";
					$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".($totalquantity>0?number_format($totalprice/$totalquantity,2,".",","):"-")."</span></td>";
					$output.="<td class='centered'>".number_format($totalquantity,0,".",",")."</td>";
					$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalprice,2,".",",")."</span></td>";
					$output.="<td></td>";
				$output.="</tr>";
			$output.="</tbody>";

		$output.="</table>";
	}
	$output.="</div>"; 

	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	