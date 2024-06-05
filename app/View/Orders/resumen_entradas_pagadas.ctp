<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			else {
				var percentageValue=parseFloat($(this).text());
				$(this).text(100*percentageValue);
			}
			$(this).number(true,2,'.',',');
			$(this).append(" %");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>
<div class="orders index purchases fullwidth">
<?php 
	echo "<h1>Entradas y Facturas Pagadas</h1>";
	
	echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-12'>";			
				echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Fecha Inicio (Pagos)'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
					echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('Fecha Final (Pagos)'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.provider_id',['default'=>$providerId,'empty'=>[0=>'Seleccione Proveedor']]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarEntradasPagadas',($providerId==0?'Todos':$providers[$providerId])],['class' => 'btn btn-primary']); 
			echo "</div>";
		echo "</div>";
    $paidPurchasesTable='';
    $paidPurchaseOrderInvoicesTable='';
    if (!empty($paidPurchases)){
      echo "<div class='row'>";
        echo "<div class='col-md-12'>";			
          $paidPurchasesTableHead="<thead>";
            $paidPurchasesTableHead.="<tr>";
              $paidPurchasesTableHead.="<th>".$this->Paginator->sort('payment_date',__('Fecha de pago'))."</th>";
              $paidPurchasesTableHead.="<th class='centered'>Cheque</th>";
              $paidPurchasesTableHead.="<th>".$this->Paginator->sort('order_date',__('Fecha de entrada'))."</th>";
              $paidPurchasesTableHead.="<th>".$this->Paginator->sort('order_code')."</th>";
              $paidPurchasesTableHead.="<th>".$this->Paginator->sort('ThirdParty.id',__('Proveedor'))."</th>";
              $paidPurchasesTableHead.="<th class='centered'>".$this->Paginator->sort('total_price',__('Total Cost'))."</th>";
              $paidPurchasesTableHead.="<th class='centered'>Costo Total mas IVA</th>";
              $paidPurchasesTableHead.="<th class='centered'>Cancelada por</th>";
            $paidPurchasesTableHead.="</tr>";
          $paidPurchasesTableHead.="</thead>";
            
          $purchaseRows="";
          $totalSubtotal=0;   
          $totalCost=0;   
          foreach ($paidPurchases as $purchase){ 
            $orderDateTime=new DateTime($purchase['Order']['order_date']);
            $paymentDateTime=new DateTime($purchase['Order']['payment_date']);
            $totalSubtotal+=$purchase['Order']['total_price']; 
            $totalCost+=$purchase['Order']['entry_cost_total'];
            
            $purchaseRows.="<tr>";
            
            $purchaseRows.="<td>".$paymentDateTime->format('d-m-Y')."</td>";
            $purchaseRows.="<td class='centered'>".$this->Html->Link($purchase['Order']['entry_cheque_number'],['action'=>'verPagoEntradas',$purchase['Order']['entry_cheque_number']])."</td>";
            $purchaseRows.="<td>".$orderDateTime->format('d-m-Y')."</td>";
            $purchaseRows.="<td>".$this->Html->link($purchase['Order']['order_code'], ['action' => ($purchase['Order']['stock_movement_type_id']== MOVEMENT_PURCHASE?'verEntrada':'verEntradaSuministros'), $purchase['Order']['id']])."</td>";
            $purchaseRows.="<td class='centered'>".$this->Html->link($purchase['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $purchase['ThirdParty']['id']])."</td>";
            $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['total_price'],4,".",",")."</td>";
            $purchaseRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchase['Order']['entry_cost_total'],4,".",",")."</td>";

            $purchaseRows.="<td class='centered'>".$purchase['PaymentUser']['username']."</td>";
            
            $purchaseRows.="</tr>";
          } 
          $totalRow="";
          $totalRow.="<tr class='totalrow'>";
            $totalRow.="<td>Total</td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td class='centered number'><span class='currency'>C$ </span>".number_format($totalSubtotal,4,".",",")."</td>";
            $totalRow.="<td class='centered number'><span class='currency'>C$ </span>".number_format($totalCost,4,".",",")."</td>";          
            $totalRow.="<td></td>";
          $totalRow.="</tr>";
          $paidPurchasesTableBody="</tbody>".$totalRow.$purchaseRows.$totalRow."</tbody>";
    
          $paidPurchasesTable="<table id='entradas_pagadas'>".$paidPurchasesTableHead.$paidPurchasesTableBody."</table>";
          echo "<h2>Entradas</h2>";
          echo $paidPurchasesTable;
        echo "</div>";
      echo "</div>";
    }
    if (!empty($paidPurchaseOrderInvoices)){
      echo "<div class='row'>";
        echo "<div class='col-md-12'>";			
          $paidPurchaseOrderInvoicesTableHead="<thead>";
            $paidPurchaseOrderInvoicesTableHead.="<tr>";
              $paidPurchaseOrderInvoicesTableHead.="<th>Fecha de pago</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th class='centered'>Cheque</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th>Fecha de Factura</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th># Factura</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th>Proveedor</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th class='centered'>Subtotal</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th class='centered'>Total</th>";
              $paidPurchaseOrderInvoicesTableHead.="<th class='centered'>Cancelada por</th>";
            $paidPurchaseOrderInvoicesTableHead.="</tr>";
          $paidPurchaseOrderInvoicesTableHead.="</thead>";
            
          $purchaseOrderInvoiceRows="";
          $totalSubtotalInvoices=0;   
          $totalCostInvoices=0;   
          foreach ($paidPurchaseOrderInvoices as $purchaseOrderInvoice){ 
            $invoiceDateTime=new DateTime($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_date']);
            $paymentDateTime=new DateTime($purchaseOrderInvoice['PurchaseOrderInvoice']['payment_date']);
            $totalSubtotalInvoices+=$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_subtotal']; 
            $totalCostInvoices+=$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_total'];
            
            $purchaseOrderInvoiceRows.="<tr>";
            
            $purchaseOrderInvoiceRows.="<td>".$paymentDateTime->format('d-m-Y')."</td>";
            $purchaseOrderInvoiceRows.="<td class='centered'>".$this->Html->Link($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_cheque_number'],['action'=>'verPagoEntradas',$purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_cheque_number']])."</td>";
            $purchaseOrderInvoiceRows.="<td>".$invoiceDateTime->format('d-m-Y')."</td>";
            $purchaseOrderInvoiceRows.="<td>".$this->Html->link($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_code'].' ('.$purchaseOrderInvoice['PurchaseOrder']['purchase_order_code'].')', ['action' => ($purchaseOrderInvoice['Entry']['stock_movement_type_id']== MOVEMENT_PURCHASE?'verEntrada':'verEntradaSuministros'), $purchaseOrderInvoice['Entry']['id']])."</td>";
            $purchaseOrderInvoiceRows.="<td class='centered'>".$this->Html->link($purchaseOrderInvoice['Entry']['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $purchaseOrderInvoice['Entry']['ThirdParty']['id']])."</td>";
            $purchaseOrderInvoiceRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_subtotal'],4,".",",")."</td>";
            $purchaseOrderInvoiceRows.="<td class='centered'><span class='currency'>C$ </span>".number_format($purchaseOrderInvoice['PurchaseOrderInvoice']['invoice_total'],4,".",",")."</td>";

            $purchaseOrderInvoiceRows.="<td class='centered'>".$purchaseOrderInvoice['PaymentUser']['username']."</td>";
            
            $purchaseOrderInvoiceRows.="</tr>";
          } 
          $totalRow="";
          $totalRow.="<tr class='totalrow'>";
            $totalRow.="<td>Total</td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td></td>";
            $totalRow.="<td class='centered number'><span class='currency'>C$ </span>".number_format($totalSubtotalInvoices,4,".",",")."</td>";
            $totalRow.="<td class='centered number'><span class='currency'>C$ </span>".number_format($totalCostInvoices,4,".",",")."</td>";          
            $totalRow.="<td></td>";
          $totalRow.="</tr>";
          $paidPurchaseOrderInvoicesTableBody="</tbody>".$totalRow.$purchaseOrderInvoiceRows.$totalRow."</tbody>";
          
          $paidPurchaseOrderInvoicesTable="<table id='facturas_pagadas'>".$paidPurchaseOrderInvoicesTableHead.$paidPurchaseOrderInvoicesTableBody."</table>";
          echo "<h2>Facturas</h2>";
          echo $paidPurchaseOrderInvoicesTable;
        echo "</div>";
      echo "</div>";
    }
	echo "</div>";
  
  $_SESSION['entradasPagadas'] = $paidPurchasesTable.$paidPurchaseOrderInvoicesTable;
?>
</div>
