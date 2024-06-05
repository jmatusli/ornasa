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
  $outputHeader="";
  $outputBody="";
  $totalCheque=0;
  
  if (!empty($entries) || !empty($purchaseOrderInvoices)){
    if (!empty($entries)){
      $paymentDateTime=new DateTime($entries[0]['Order']['payment_date']);
    }
    else {
      $paymentDateTime=new DateTime($purchaseOrderInvoices[0]['PurchaseOrderInvoice']['payment_date']);
    }
    $outputBody.="<div class='related'>";
      $outputBody.="<h3>".__('Entradas y facturas pagadas con este cheque')."</h3>";
      $outputBody.="<table cellpadding = '0' cellspacing = '0'>";		
        $outputBody.="<tr>";
          $outputBody.="<th>".__('Fecha Entrada')."</th>";
          $outputBody.="<th>".__('# Entrada')."</th>";
          $outputBody.="<th class='centered'>".__('Total Price')."</th>";
        $outputBody.="</tr>";

        $totalprice=0;
        foreach ($entries as $entry){
          $entryDateTime=new DateTime ($entry['Order']['order_date']);
          $totalCheque+=1.15*$entry['Order']['total_price'];
          
          $outputRow="<tr>";
            $outputRow.="<td>".$entryDateTime->format('d-m-Y')."</td>";
            $outputRow.="<td>".$entry['Order']['order_code']."</td>";
            $outputRow.="<td><span class='currency'>C$ </span><span class='amountright'>".number_format(1.15*$entry['Order']['total_price'],2,".",",")."</span></td>";
          $outputRow.="</tr>";
          $outputBody.=$outputRow;
        }
        foreach ($purchaseOrderInvoices as $purchaseOrderInvoice){
          $invoiceDateTime=new DateTime ($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_date']);
          $totalCheque+=$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_total'];
          
          $outputRow="<tr>";
            $outputRow.="<td>".$invoiceDateTime->format('d-m-Y')."</td>";
            $outputRow.="<td>".$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_code'].' ('.$purchaseOrderInvoice['PurchaseOrder']['purchase_order_code'].')'."</td>";
            $outputRow.="<td><span class='currency'>C$ </span><span class='amountright'>".number_format($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_total'],2,".",",")."</span></td>";
          $outputRow.="</tr>";
          $outputBody.=$outputRow;
        }
        $outputBody.="<tr class='totalrow'>";
          $outputBody.="<td>Total</td>";
          $outputBody.="<td></td>";
          $outputBody.="<td class='centered'>C$ ".number_format($totalCheque,2,".",",")."</td>";
        $outputBody.="</tr>";
      $outputBody.="</table>";
    $outputBody.="</div>";
    if (!empty($entries)){
      $outputHeader.="<h2>".__('Cheque')." ".$entries[0]['Order']['entry_cheque_number']."</h2>";
    
    }
    else {
      $outputHeader.="<h2>".__('Cheque')." ".$purchaseOrderInvoices[0]['PurchaseOrderInvoice']['invoice_cheque_number']."</h2>";
    }
    $outputHeader.="<dl>";		
      $outputHeader.="<dt>".__('Fecha de pago')."</dt>";
      $outputHeader.="<dd>".$paymentDateTime->format('d-m-Y')."</dd>";
      $outputHeader.="<dt>".__('Provider')."</dt>";
      if (!empty($entries)){
        $outputHeader.="<dd>".$this->Html->link($entries[0]['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $entries[0]['ThirdParty']['id']])."</dd>";
      }
      else {
        $outputHeader.="<dd>".$this->Html->link($purchaseOrderInvoices[0]['Entry']['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $purchaseOrderInvoices[0]['Entry']['ThirdParty']['id']])."</dd>";
      }
      $outputHeader.="<dt>".__('Total para cheque')."</dt>";
      $outputHeader.="<dd>C$ ".number_format($totalCheque,2,".",",")."</dd>";
    $outputHeader.="</dl>";
	}
  else {
    $outputHeader.="<h2>No existe un cheque con este número</h2>";
  }
	
  $output="";
  $output.="<div class='orders view'>";
    $output.=$outputHeader;
	$output.="</div>";
	$output.=$outputBody;
  
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	