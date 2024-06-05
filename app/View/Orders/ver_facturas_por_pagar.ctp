<script>
  $('body').on('change','#OrderChequeNumber',function(){	
		$('.chequenumber div input').val($(this).val());
	});  

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
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("C$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("C$");
			}
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("US$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("US$");
			}
		});
	};
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
    
    $('#saving').addClass('hidden');
	});
  $('body').on('click','.save',function(e){	
    $(".save").data('clicked', true);
  });
  $('body').on('submit','#PurchaseOrderCrearForm',function(e){	
    if($(".save").data('clicked'))
    {
      $('.save').attr('disabled', 'disabled');
      $("#mainform").fadeOut();
      $("#saving").removeClass('hidden');
      $("#saving").fadeIn();
      var opts = {
          lines: 12, // The number of lines to draw
          length: 7, // The length of each line
          width: 4, // The line thickness
          radius: 10, // The radius of the inner circle
          color: '#000', // #rgb or #rrggbb
          speed: 1, // Rounds per second
          trail: 60, // Afterglow percentage
          shadow: false, // Whether to render a shadow
          hwaccel: false // Whether to use hardware acceleration
      };
      var target = document.getElementById('saving');
      var spinner = new Spinner(opts).spin(target);
    }
    
    return true;
  });

</script>
<div class="orders index fullwidth">
<?php 
	echo "<h2>".__('Reporte de Facturas por Pagar para Proveedor ').$provider['ThirdParty']['company_name']."</h2>";
  echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarFacturasPorPagar',$provider['ThirdParty']['company_name']],['class' => 'btn btn-primary']); 
  
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la venta...</p>";
    echo "</div>";
  echo "</div>";
  
  echo $this->Form->create('Order');
	echo "<fieldset id='mainform'>";
  echo "<legend>".__('Cancelar Facturas Seleccionados')."</legend>";
    
	$reportTable="";
	$table_id=substr("facturas_por_pagar_".$provider['ThirdParty']['company_name'],0,31);
	$reportTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th class='actions'></th>";
				$reportTable.="<th>Fecha Entrada</th>";
        $reportTable.="<th>Total Preformas</th>";
        $reportTable.="<th>Total Tapones</th>";
        $reportTable.="<th>Total Bolsas</th>";
        $reportTable.="<th>Factura</th>";
        $reportTable.="<th>Monto C$</th>";
        $reportTable.="<th>Monto con IVA C$</th>";
        $reportTable.="<th>Uds Empaque</th>";
        $reportTable.="<th>Días Factura</th>";
        $reportTable.="<th>Número cheque</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		$reportTable.="<tbody>";
    
    $excelTableBodyRows="";
    
		$facturasPorPagarBody="";
		
    $grandTotalRaw=0;
    $grandTotalCaps=0;
    $grandTotalBags=0;
		$grandTotalPackages=0;
    
    $subtotalCSEntry=0;
    $totalCSEntry=0;
    
    $currentDateTime= new DateTime(date('Y-m-d'));
    
    foreach ($pendingEntries as $entry){
      $totalRaw=0;
      $packagesRaw=0;
      $totalCaps=0;
      $packagesCaps=0;
      $totalBags=0;
      $packagesBags=0;
      if (!empty($entry['StockMovement'])){
        foreach ($entry['StockMovement'] as $stockMovement){
          switch ($stockMovement['Product']['product_type_id']){
            case PRODUCT_TYPE_PREFORMA:
              $totalRaw+=$stockMovement['product_quantity'];
              $packagesRaw+=(empty($stockMovement['Product']['packaging_unit'])?$totalRaw:floor($totalCaps/$stockMovement['Product']['packaging_unit']));
              break;
            case PRODUCT_TYPE_CAP:
              $totalCaps+=$stockMovement['product_quantity'];
              $packagesCaps+=(empty($stockMovement['Product']['packaging_unit'])?$totalCaps:floor($totalCaps/$stockMovement['Product']['packaging_unit']));
              break;
            case PRODUCT_TYPE_ROLL:
            case PRODUCT_TYPE_CONSUMIBLES:
              $totalBags+=$stockMovement['product_quantity'];
              $packagesBags+=(empty($stockMovement['Product']['packaging_unit'])?$totalBags:floor($totalBags/$stockMovement['Product']['packaging_unit']));
              break;  
            default:
              break;
          }
        }
      }
      $grandTotalRaw+=$totalRaw;
      $grandTotalCaps+=$totalCaps;
      $grandTotalBags+=$totalBags;
      $grandTotalPackages+=$packagesRaw;
      $grandTotalPackages+=$packagesCaps;
      $grandTotalPackages+=$packagesBags;

      $currencyClass="CScurrency";
  
      
      if (empty($entry['PurchaseOrderInvoice'])){
        $entryDateTime=new DateTime($entry['Order']['order_date']);
        //$entryDatePlusCreditDays=date("Y-m-d",strtotime($entry['Order']['order_date']."+".$provider['ThirdParty']['credit_days']." days"));
        //$dueDate= new DateTime($entryDatePlusCreditDays);
        
        $daysSinceEntry=$currentDateTime->diff($entryDateTime);
        //pr($daysSinceEntry);
      
        $subtotalCSEntry+=$entry['Order']['total_price'];
        $totalCSEntry+=$entry['Order']['entry_cost_total'];
        
        $facturasPorPagarBodyRowContent="";
        $facturasPorPagarBodyRowContent.="<td>".$entryDateTime->format('d-m-Y')."</td>";
        $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".$totalRaw."</span></td>";
        $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".$totalCaps."</span></td>";
        $facturasPorPagarBodyRowContent.="<td class='bags centered number'><span class='amountright'>".$totalBags."</span></td>";
        $facturasPorPagarBodyRowContent.="<td>".$this->Html->link($entry['Order']['order_code'], ['controller' => 'orders', 'action' => 'verEntrada', $entry['Order']['id']])."</td>";
        $facturasPorPagarBodyRowContent.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$entry['Order']['total_price']."</span></td>";
        $facturasPorPagarBodyRowContent.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".($entry['Order']['entry_cost_total'])."</span></td>";
        $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".($packagesCaps+$packagesBags)."</span></td>";
        $facturasPorPagarBodyRowContent.="<td class='days centered'>".$daysSinceEntry->format('%a')."</td>";
              
        $excelTableBodyRows.="<tr>".$facturasPorPagarBodyRowContent."</tr>";
        
        $facturasPorPagarBodyRowContent="<td class='orderid'>".$this->Form->input('Entry.'.$entry['Order']['id'].'.payment', ['label'=>false,'type' => 'checkbox'])."</td>".$facturasPorPagarBodyRowContent;
        $facturasPorPagarBodyRowContent.="<td class='chequenumber'>".$this->Form->input('Entry.'.$entry['Order']['id'].'.entry_cheque_number', ['label'=>false,'type' => 'text'])."</td>";	
        
        $facturasPorPagarBody.='<tr style="background-color:cyan">'.$facturasPorPagarBodyRowContent.'</tr>';
      }
      else {
        foreach ($entry['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
          //pr($purchaseOrderInvoice);
          
          $invoiceDateTime=new DateTime($purchaseOrderInvoice['invoice_date']);
          $daysSinceInvoice=$currentDateTime->diff($invoiceDateTime);
        
          $subtotalCSEntry+=$purchaseOrderInvoice['invoice_subtotal'];
          $totalCSEntry+=$purchaseOrderInvoice['invoice_total'];
          
          $facturasPorPagarBodyRowContent="";
          $facturasPorPagarBodyRowContent.="<td>".$invoiceDateTime->format('d-m-Y')."</td>";
          $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".$totalRaw."</span></td>";
          $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".$totalCaps."</span></td>";
          $facturasPorPagarBodyRowContent.="<td class='bags centered number'><span class='amountright'>".$totalBags."</span></td>";
          $facturasPorPagarBodyRowContent.="<td>".$this->Html->link($purchaseOrderInvoice['invoice_code'].' ('.$purchaseOrderInvoice['PurchaseOrder']['purchase_order_code'].')', ['controller' => 'orders', 'action' => 'verEntrada', $entry['Order']['id']])."</td>";
          $facturasPorPagarBodyRowContent.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$purchaseOrderInvoice['invoice_subtotal']."</span></td>";
          $facturasPorPagarBodyRowContent.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$purchaseOrderInvoice['invoice_total']."</span></td>";
          $facturasPorPagarBodyRowContent.="<td class='caps centered number'><span class='amountright'>".($packagesCaps+$packagesBags)."</span></td>";
          $facturasPorPagarBodyRowContent.="<td class='days centered'>".$daysSinceInvoice->format('%a')."</td>";
                
          $excelTableBodyRows.="<tr>".$facturasPorPagarBodyRowContent."</tr>";
          
          $facturasPorPagarBodyRowContent="<td class='orderid'>".$this->Form->input('PurchaseOrderInvoice.'.$purchaseOrderInvoice['id'].'.payment', ['label'=>false,'type' => 'checkbox'])."</td>".$facturasPorPagarBodyRowContent;
          $facturasPorPagarBodyRowContent.="<td class='chequenumber'>".$this->Form->input('PurchaseOrderInvoice.'.$purchaseOrderInvoice['id'].'.invoice_cheque_number', ['label'=>false,'type' => 'text'])."</td>";	
          
          $facturasPorPagarBody.="<tr>".$facturasPorPagarBodyRowContent."</tr>";
          
        }
      }
      
      
		}				
   	
			$totalRow="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				$totalRow.="<td></td>";	
        $totalRow.="<td class='centered number'><span class='amountright'>".$grandTotalRaw."</span></td>";
        $totalRow.="<td class='centered number'><span class='amountright'>".$grandTotalCaps."</span></td>";
				$totalRow.="<td class='centered number'><span class='amountright'>".$grandTotalBags."</span></td>";
				$totalRow.="<td></td>";	
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCSEntry."</span></td>";
        $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".($totalCSEntry)."</span></td>";

				$totalRow.="<td class='centered number'><span class='amountright'>".$grandTotalPackages."</span></td>";
        $totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
			$totalRow.="</tr>";
      $totalRowUSD="<tr class='totalrow green'>";
				$totalRowUSD.="<td>Total US$</td>";	
				$totalRowUSD.="<td></td>";	
        $totalRowUSD.="<td class='centered number'><span class='amountright'>".$grandTotalRaw."</span></td>";
        $totalRowUSD.="<td class='centered number'><span class='amountright'>".$grandTotalCaps."</span></td>";
				$totalRowUSD.="<td class='centered number'><span class='amountright'>".$grandTotalBags."</span></td>";
				$totalRowUSD.="<td></td>";	
				$totalRowUSD.="<td class='centered number USDcurrency'><span class='currency'></span><span class='amountright'>".($subtotalCSEntry/$exchangeRateCurrent)."</span></td>";
        $totalRowUSD.="<td class='centered number USDcurrency'><span class='currency'></span><span class='amountright'>".($totalCSEntry/$exchangeRateCurrent)."</span></td>";
				$totalRowUSD.="<td class='centered number'><span class='amountright'>".$grandTotalPackages."</span></td>";
        $totalRowUSD.="<td></td>";	
				$totalRowUSD.="<td></td>";	
			$totalRowUSD.="</tr>";
      
      $excelTotalRow="<tr class='totalrow'>";
				$excelTotalRow.="<td>Total</td>";	
        $excelTotalRow.="<td class='centered number'><span class='amountright'>".$grandTotalRaw."</span></td>";
				$excelTotalRow.="<td class='centered number'><span class='amountright'>".$grandTotalCaps."</span></td>";
				$excelTotalRow.="<td class='centered number'><span class='amountright'>".$grandTotalBags."</span></td>";
				$excelTotalRow.="<td></td>";	
				$excelTotalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCSEntry."</span></td>";
 				$excelTotalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".($totalCSEntry)."</span></td>";

				$excelTotalRow.="<td class='bags centered'><span class='amountright'>".$grandTotalPackages."</span></td>";
        $excelTotalRow.="<td></td>";	
			$excelTotalRow.="</tr>";
      //pr($excelTotalRow);
			$reportTable.=$totalRowUSD.$totalRow.$facturasPorPagarBody.$totalRow.$totalRowUSD;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
  
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
				echo "<div class='col-sm-6'>";
          echo $this->Form->input('cheque_number',['label'=>'Número cheque']);
        echo "</div>";
        echo "<div class='col-sm-6'>";
          echo "<h3>".__('Días de Crédito ').$provider['ThirdParty']['credit_days']."</h3>";
          echo "<h3>Tasa cambio para conversión a US$ es ".$currentExchangeRate."</h3>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
    
  echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
  echo '<p class="info">Entradas sin facturas se muestran en azul, si hay facturas presentes no se muestra la entrada en su totalidad.</p>';
	
  echo "<p class='warning'>Seleccione las facturas que se van a cancelar con la casilla a la izquierda.  Cada factura que se cancela debe tener un número de cheque.  No se permiten pagos parciales en este momento.</p>";
	echo $reportTable;
  echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
	
  echo "</fieldset>";
	echo $this->Form->end();
  
  
	$table_id=substr("facturas_por_pagar_".$provider['ThirdParty']['company_name'],0,31);
  $excelTableHeader="";
  $excelTableHeader.="<thead>";
			$excelTableHeader.="<tr>";
				$excelTableHeader.="<th>Fecha Entrada</th>";
        $excelTableHeader.="<th>Total Preformas</th>";
        $excelTableHeader.="<th>Total Tapones</th>";
        $excelTableHeader.="<th>Total Bolsas</th>";
        $excelTableHeader.="<th>Factura</th>";
        $excelTableHeader.="<th>Monto C$</th>";
        $excelTableHeader.="<th>Monto con IVA C$</th>";
        $excelTableHeader.="<th>Uds Empaque</th>";
        $excelTableHeader.="<th>Días Factura</th>";
			$excelTableHeader.="</tr>";
		$excelTableHeader.="</thead>";
	$excelTable= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelTableHeader."<tbody>".$excelTableBodyRows.$excelTotalRow."</tbody></table>";
		
	$_SESSION['facturasPorPagar'] = $excelTable;
?>
</div>