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
	$orderDate=new DateTime($order['Order']['order_date']);
	$orderCode=$order['Order']['order_code'];
	$output.="<div class='sales view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>VENTA FACTURA # ".$orderCode."</div>";
	
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
		if (!empty($invoice)){
			$currencyAbbreviation="C$";
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$currencyAbbreviation="US$";
			}
			if ($invoice['Invoice']['bool_credit']){
				$dueDate=new DateTime($invoice['Invoice']['due_date']);
				$output.="<tr>";
					$output.="<td style='width:50%'>";
						$output.="<div>Factura de Crédito</div>";
					$output.="</td>";
					$output.="<td style='width:50%'>";
						$output.="<div>Fecha Límite:<span class='underline'>".$dueDate->format('d-m-Y')."</span></div>";
					$output.="</td>";
				$output.="</tr>";
				
				if ($invoice['Invoice']['total_price_CS']==$invoice['Invoice']['pendingCS']){
					$output.="<tr>";
						$output.="<td style='width:100%'>";
							$output.="<div>No se han realizado pagos para esta factura aun</div>";
						$output.="</td>";
					$output.="</tr>";
				}
				else {
					$output.="<tr>";
						$output.="<td style='width:100%'>";
							$output.="<div>Saldo pendiente es: C$ ".$invoice['Invoice']['pendingCS']."</div>";
						$output.="</td>";
					$output.="</tr>";
				}
			}
			else {
				$output.="<tr>";
					$output.="<td style='width:100%'>";
						$output.="<div>Factura de Contado</div>";
					$output.="</td>";
				$output.="</tr>";
				$output.="<tr>";
					$output.="<td style='width:100%'>";
						$output.="<div>Pagado a caja ".$invoice['CashboxAccountingCode']['description']."</div>";
					$output.="</td>";
				$output.="</tr>";
			}			
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Subtotal Factura (sin IVA): ".$currencyAbbreviation."<span class='underline'>".number_format($invoice['Invoice']['sub_total_price'],2,".",",")."</span></div>";
				$output.="</td>";
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>IVA Factura: ".$currencyAbbreviation."<span class='underline'>".number_format($invoice['Invoice']['IVA_price'],2,".",",")."</span></div>";
				$output.="</td>";
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td style='width:100%'>";
				$output.="<div>Total Factura (con IVA): ".$currencyAbbreviation."<span class='underline'>".number_format($invoice['Invoice']['total_price'],2,".",",")."</span></div>";
				$output.="</td>";
			$output.="</tr>";
			if ($invoice['Invoice']['bool_retention']&&!$invoice['Invoice']['bool_credit']){
				$output.="<tr>";
					$output.="<td style='width:50%'>";
						$output.="<div>Monto Retención: ".$currencyAbbreviation."<span class='underline'>".number_format($invoice['Invoice']['retention_amount'],2,".",",")."</span></div>";
					$output.="</td>";
					$output.="<td style='width:50%'>";
						$output.="<div>Número de Retención:<span class='underline'>".$invoice['Invoice']['retention_number']."</span></div>";
					$output.="</td>";
				$output.="</tr>";
			}
		}
	$output.="</table>";

	$output.="<div class='related'>";
	if (!empty($summedMovements)){
		$output.="<h3>".__('Products Sold')."</h3>";
		$output.="<table cellpadding = '0' cellspacing = '0'>";
			$output.="<thead>";
				$output.="<tr>";
					$output.="<th>".__('Product')."</th>";
					$output.="<th class='centered' style='width:15%'>".__('Unit Price')."</th>";
					$output.="<th class='centered'>".__('Quantity')."</th>";
					$output.="<th class='centered' style='width:15%'>".__('Total Price')."</th>";
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
				$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</span></td>";
				$output.="<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
				$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
				$totalquantity+=$summedMovement[0]['total_product_quantity'];
				$totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
				$output.="</tr>";
			}
		
				$output.="<tr class='totalrow'>";
					$output.="<td>Sub Total</td>";
					$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".($totalquantity>0?number_format($totalprice/$totalquantity,2,".",","):"-")."</span></td>";
					$output.="<td class='centered'>".number_format($totalquantity,0,".",",")."</td>";
					$output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalprice,2,".",",")."</span></td>";
				$output.="</tr>";
				
			$output.="</tbody>";

		$output.="</table>";
	}
	if (!empty($invoice)){
		if ($invoice['Invoice']['bool_credit']&&!empty($cashReceiptsForInvoice)){
			$output.="<h3>".__('Pagos para esta Factura de Crédito')."</h3>";
			$output.="<table cellpadding = '0' cellspacing = '0'>";
				$output.="<thead>";
					$output.="<tr>";
						$output.="<th>Fecha Recibo</th>";
						$output.="<th>Número Recibo</th>";
						$output.="<th class='centered' style='width:70px'>Monto Pagado</th>";
					$output.="</tr>";
				$output.="</thead>";
				
				$totalpaidCS=0;
				$output.="<tbody>";
				foreach ($cashReceiptsForInvoice as $cashReceipt){ 
					//pr($cashReceipt);
					$receiptDate=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
					$output.="<tr>";
						$output.="<td>".$receiptDate->format('d-m-Y')."</td>";
						$output.="<td>".$this->Html->Link($cashReceipt['CashReceipt']['receipt_code'],array('controller'=>'cash_receipts','action'=>'view',$cashReceipt['CashReceipt']['id']))."</td>";
						$output.="<td class='centered amount'><span class='currency'>".$cashReceipt['Currency']['abbreviation']." </span><span class='amountright'>".number_format($cashReceipt['CashReceiptInvoice']['amount'],2,".",",")."</td>";
						
						if ($cashReceipt['Currency']['id']==CURRENCY_USD){
							$totalpaidCS+=$cashReceipt['CashReceiptInvoice']['amount']*$exchangeRateCurrent;
						}
						else {
							$totalpaidCS+=$cashReceipt['CashReceiptInvoice']['amount'];
						}
					$output.="</tr>";
				}
					$output.="<tr class='totalrow'>";
						$output.="<td>Total</td>";
						$output.="<td></td>";
						$output.="<td class='centered amount'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalpaidCS,2,".",",")."</span></td>";
					$output.="</tr>";
				$output.="</tbody>";

			$output.="</table>";
		}
	}
	$output.="</div>";

	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	