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
          if (empty($entry['PurchaseOrderInvoice'])){
            $entryDateTime=new DateTime ($entry['Order']['order_date']);
            $totalCheque+=1.15*$entry['Order']['total_price'];
            
            $outputRow="<tr>";
              $outputRow.="<td>".$entryDateTime->format('d-m-Y')."</td>";
              $outputRow.="<td>".$this->Html->link($entry['Order']['order_code'],['action'=>'verEntrada',$entry['Order']['id']])."</td>";
              $outputRow.="<td><span class='currency'>C$ </span><span class='amountright'>".number_format(1.15*$entry['Order']['total_price'],2,".",",")."</span></td>";
            $outputRow.="</tr>";
            $outputBody.=$outputRow;
          }
        }
        foreach ($purchaseOrderInvoices as $purchaseOrderInvoice){
          $invoiceDateTime=new DateTime ($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_date']);
          $totalCheque+=$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_total'];
          
          $outputRow="<tr>";
            $outputRow.="<td>".$invoiceDateTime->format('d-m-Y')."</td>";
            $outputRow.="<td>".$this->Html->link($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_code'].' ('.$purchaseOrderInvoice['PurchaseOrder']['purchase_order_code'].')',['action'=>'verEntrada',$purchaseOrderInvoice['Entry']['id']])."</td>";
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
      $outputHeader.="<dl>";		
        $outputHeader.="<dt>".__('Fecha de pago')."</dt>";
        $outputHeader.="<dd>".$paymentDateTime->format('d-m-Y')."</dd>";
        $outputHeader.="<dt>".__('Provider')."</dt>";
        $outputHeader.="<dd>".$this->Html->link($entries[0]['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $entries[0]['ThirdParty']['id']])."</dd>";
        $outputHeader.="<dt>".__('Total para cheque')."</dt>";
        $outputHeader.="<dd>C$ ".number_format($totalCheque,2,".",",")."</dd>";
      $outputHeader.="</dl>";
    }
    else {
      $outputHeader.="<h2>".__('Cheque')." ".$purchaseOrderInvoices[0]['PurchaseOrderInvoice']['invoice_cheque_number']."</h2>";
      $outputHeader.="<dl>";		
        $outputHeader.="<dt>".__('Fecha de pago')."</dt>";
        $outputHeader.="<dd>".$paymentDateTime->format('d-m-Y')."</dd>";
        $outputHeader.="<dt>".__('Provider')."</dt>";
        $outputHeader.="<dd>".$this->Html->link($purchaseOrderInvoices[0]['Entry']['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $purchaseOrderInvoices[0]['Entry']['ThirdParty']['id']])."</dd>";
        $outputHeader.="<dt>".__('Total para cheque')."</dt>";
        $outputHeader.="<dd>C$ ".number_format($totalCheque,2,".",",")."</dd>";
      $outputHeader.="</dl>";
    }
    
    $outputHeader.="<br/>";
    if (!empty($entries)){
      $namePdf="Cheque_".$entries[0]['Order']['entry_cheque_number']."_".$entries[0]['ThirdParty']['company_name'];
    
      $outputHeader.=$this->Html->link(__('Guardar como pdf'), ['action' => 'verPagoEntradasPdf','ext'=>'pdf', $entries[0]['Order']['entry_cheque_number'],$namePdf],['class'=>'btn btn-primary'])."</li>";
    }
    else {
      $namePdf="Cheque_".$purchaseOrderInvoices[0]['PurchaseOrderInvoice']['invoice_cheque_number']."_".$purchaseOrderInvoices[0]['Entry']['ThirdParty']['company_name'];
    
      $outputHeader.=$this->Html->link(__('Guardar como pdf'), ['action' => 'verPagoEntradasPdf','ext'=>'pdf', $purchaseOrderInvoices[0]['PurchaseOrderInvoice']['invoice_cheque_number'],$namePdf],['class'=>'btn btn-primary'])."</li>";
    }
	}
  else {
    $outputHeader.="<h2>No existe un cheque con este número</h2>";
  }
	
  $output="";
  $output.="<div class='orders view fullwidth'>";
    $output.=$outputHeader;
	$output.="</div>";
	$output.=$outputBody;
	echo $output;
	$output=$outputHeader.$outputBody;
	//echo "this is the output for the pdf";
	//echo $output;
	//$_SESSION['output_cheque']=$output;
?>