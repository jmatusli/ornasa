<?php
  echo "<div class='orders view fullwidth'>";
 
	//pr($invoice);
	$orderDateTime=new DateTime($order['Order']['order_date']);
  
	echo '<h1>'.__('Exit').' '.$order['Order']['order_code'].($order['Order']['bool_annulled']?' (Anulada)':'').'</h1>';
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-sm-6'>";	
        echo "<dl class='dl50'>";
          $currencyAbbreviation="C$";
          if (!empty($invoice)){
            if ($invoice['Invoice']['currency_id'] == CURRENCY_USD){
              $currencyAbbreviation="US$";
            }
          }
          echo "<dt>".__('Sale Date')."</dt>";
          echo "<dd>".$orderDateTime->format('d-m-Y')."</dd>";
          echo "<dt>".__('Order Code')."</dt>";
          echo "<dd>".$order['Order']['order_code']."</dd>";
          echo "<dt>".__('Sales Order')."</dt>";
          echo "<dd>".(empty($invoice['SalesOrder']['id'])?'-':($this->Html->Link($invoice['SalesOrder']['sales_order_code'],['controller'=>'salesOrders','action'=>'detalle',$invoice['SalesOrder']['id']])))."</dd>";
          echo "<dt>".__('Warehouse')."</dt>";
          echo "<dd>".$this->Html->link($order['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'view', $order['Warehouse']['id']],['target'=>'_blank'])."</dd>";
          if (!empty($invoice)){
            //pr($invoice);
            if ($invoice['Invoice']['bool_credit']){
              $dueDate=new DateTime($invoice['Invoice']['due_date']);
              echo "<dt>".__('Due Date')."</dt>";
              echo "<dd>".$dueDate->format('d-m-Y')."</dd>";
            }
          }
        echo '</dl>';
        //pr($order['Order']);
        //pr($order['ThirdParty']);
        echo '<h2 style="clear:left;width:100%">Datos de cliente</h2>';    
        //pr($order['ThirdParty']);
        echo '<dl>';
          echo "<dt>".__('Client')."</dt>";
          echo "<dd>".($order['ThirdParty']['bool_generic'] || $userRoleId == ROLE_SALES?$order['Order']['client_name']:$this->Html->link($order['ThirdParty']['company_name'], ['controller' => 'thirdParties', 'action' => 'verCliente',$order['ThirdParty']['id']],['target'=>'_blank']))."</dd>";
          
          echo "<dt>".__('Phone')."</dt>";
          /*
          echo "<dd>".(
            empty($order['ThirdParty']['id'])?
            (empty($order['Order']['client_phone'])?"-":$order['Order']['client_phone']):
            (empty($order['ThirdParty']['phone'])?"-":$order['ThirdParty']['phone'])
          )."</dd>";
          */
          echo "<dd>".(
            empty($order['Order']['client_phone'])?
            (
              empty($order['Order']['third_party_id'])?
              "-":
              ( 
                empty($order['ThirdParty']['phone'])?
                "-":
                $order['ThirdParty']['phone']
              )
            ):
            $order['Order']['client_phone']
          )."</dd>";
          echo "<dt>".__('Email')."</dt>";
          echo "<dd>".(
            empty($order['Order']['client_email'])?
            ( 
              empty($order['Order']['third_party_id'])?
              "-":
              (
                empty($order['ThirdParty']['email'])?
                "-":
                $order['ThirdParty']['email']
              )
            ):
            $order['Order']['client_email']
          )."</dd>";
          echo "<dt>".__('RUC')."</dt>";
          echo "<dd>".(
            empty($order['Order']['client_ruc'])?
            (
              empty($order['Order']['third_party_id'])?
              "-":
              (
                empty($order['ThirdParty']['ruc_number'])?
                "-":
                $order['ThirdParty']['ruc_number']
              )
            ):
            $order['Order']['client_ruc']
          )."</dd>";
          echo "<dt>".__('Address')."</dt>";
          echo "<dd>".(
            empty($order['Order']['client_address'])?
            (
              empty($order['Order']['third_party_id'])?
              "-":
              (
                empty($order['ThirdParty']['address'])?
                "-":
                $order['ThirdParty']['address']
              )
            ):
            $order['Order']['client_address']
          )."</dd>";
          
          echo "<dt>".__('Client Type')."</dt>";
          echo "<dd>".(empty($order['ClientType']['id'])?(empty($order['ThirdParty']['ClientType']['id'])?"-":$this->Html->link($order['ThirdParty']['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$order['ThirdParty']['ClientType']['id']])):$this->Html->link($order['ClientType']['name'], ['controller' => 'clientTypes', 'action' => 'detalle', $order['ClientType']['id']]))."</dd>";
          echo "<dt>".__('Zone')."</dt>";
          echo "<dd>".(empty($order['Zone']['id'])?(empty($order['ThirdParty']['Zone']['id'])?'-':$this->Html->link($order['ThirdParty']['Zone']['name'],['controller'=>'zones','action'=>'detalle',$order['ThirdParty']['Zone']['id']])):$this->Html->link($order['Zone']['name'], ['controller' => 'zones', 'action' => 'detalle', $order['Zone']['id']]))."</dd>";
        /*  
          echo '<dt>Conductor</dt>';
          echo '<dd>'.(empty($order['DriverUser']['id'])?"-":($order['DriverUser']['first_name']." ".$order['DriverUser']['last_name'])).'</dd>';
          echo '<dt>Vehículo</dt>';
          echo '<dd>'.(empty($order['Vehicle']['id'])?"-":$order['Vehicle']['name']).'</dd>';
        */  
          echo '<dt>Entrega a Domilio</dt>';
          //pr($order['Delivery']);
          echo '<dd>'.h($order['Order']['bool_delivery']?__("Yes"):__("No")).'</dd>';
          if ($order['Order']['bool_delivery']){
            echo '<dt>Dirección de entrega</dt>';
            echo '<dd>'.h($order['Order']['delivery_address']).'</dd>';
            if (empty($order['Delivery'])){
              echo $this->Html->link('Crear Orden de Entrega', ['controller'=>'deliveries','action' => 'crear', $invoice['SalesOrder']['id'], $order['Order']['id']],['class' => 'btn btn-primary']);
            }
            else {
              echo '<dt>Orden de Entrega a Domicilio</dt>';
              echo '<dd>'.$this->Html->Link($order['Delivery'][0]['delivery_code'],['controller'=>'deliveries','action'=>'detalle',$order['Delivery'][0]['id']]).'</dd>';
              echo '<dt>Estado de Entrega a Domicilio</dt>';
              echo '<dd>'.h($order['Delivery'][0]['DeliveryStatus']['code']).'</dd>';
            }
          }
          
          echo '<dt>Grabado Por</dt>';
          echo "<dd>".(empty($order['RecordUser']['id'])?"-":($order['RecordUser']['first_name']." ".$order['RecordUser']['last_name']))."</dd>";
          
          echo "<dt>".__('Vendedor')."</dt>";
          echo "<dd>".(empty($order['VendorUser']['id'])?"-":($order['VendorUser']['first_name']." ".$order['VendorUser']['last_name']))."</dd>";
          echo "<dt>".__('Comment')."</dt>";
          if (!empty($order['Order']['comment'])){
            echo "<dd>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$order['Order']['comment']))."</dd>";
          }
          else {
            echo "<dd>-</dd>";
          }
          if (!empty($invoice)){
            echo "<dt>".__('Subtotal Factura (sin IVA)')."</dt>";
            echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['sub_total_price'],2,".",",")."</dd>";
            echo "<dt>".__('IVA Factura')."</dt>";
            echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['iva_price'],2,".",",")."</dd>";
            echo "<dt>".__('Total Factura (con IVA)')."</dt>";
            echo "<dd>".$currencyAbbreviation." ".number_format($invoice['Invoice']['total_price'],2,".",",")."</dd>";
            
            if ($invoice['Invoice']['bool_retention'] && (!$invoice['Invoice']['bool_credit'] || $invoice['Invoice']['bool_paid'])){
              echo "<dt>".__('Monto de Retención')."</dt>";
              echo "<dd>".$invoice['Currency']['abbreviation']." ".$invoice['Invoice']['retention_amount']."</dd>";
              echo "<dt>".__('Número de Retención')."</dt>";
              echo "<dd>".$invoice['Invoice']['retention_number']."</dd>";
            }
          }
        echo "</dl>";
      echo '</div>';  
      echo "<div class='col-sm-3'>";
        if (!empty($invoice)){
          if ($invoice['Invoice']['bool_credit']){	
            echo "<h4>Factura de Crédito</h4>";
            echo "<dl>";
              $invoiceDateTime=new DateTime($invoice['Invoice']['due_date']);
              echo "<dt>".__('Due Date')."</dt>";
              echo "<dd>".$invoiceDateTime->format('d-m-Y')."</dd>";
              echo '<dt>Authorización de Crédito</dt>';
              echo '<dd>'.(empty($invoice['CreditAuthorizationUser']['id'])?"-":($invoice['CreditAuthorizationUser']['first_name'].' '.$invoice['CreditAuthorizationUser']['last_name'])).'</dd>';
            echo '</dl>';
            echo '<br/>';
            if ($invoice['Invoice']['total_price_CS']==$invoice['Invoice']['pendingCS']){
              echo "<div>No se han realizado pagos para esta factura aun</div>";
            }
            else{
              if ($invoice['Invoice']['pendingCS']<=0){
                echo "<div>Esta factura está cancelada.</div>";
              }
              else {
                echo "<div>Saldo pendiente es: C$ ".number_format($invoice['Invoice']['pendingCS'],2,".",",")."</div>";
              }
            }
          }
          else {	
            echo "<h4>Factura de Contado</h4>";
            echo "<div>Pagado a caja ".$invoice['CashboxAccountingCode']['description']."</div>";
          }
          $boolPaid=$invoice['Invoice']['bool_paid'];
          
          if ($boolPaid){
            $statusText="El estado actual de la factura es: PAGADO";
            $changeText="Marcar factura ".$invoice['Invoice']['invoice_code']." como NO PAGADO";
            $confirmText="Está seguro que quiere marcar factura ".$invoice['Invoice']['invoice_code']." como NO PAGADO?";
          }
          else {
            $statusText="El estado actual de la factura es: NO PAGADO";
            $changeText="Marcar factura ".$invoice['Invoice']['invoice_code']." como PAGADO";
            $confirmText="Está seguro que quiere marcar factura ".$invoice['Invoice']['invoice_code']." como PAGADO?";
          }
          echo "<p>".$statusText."</p>";
          echo $this->Html->link($changeText,['controller'=>'invoices','action' => 'changePaidStatus',$invoice['Invoice']['id']], ['confirm' => $confirmText, 'class' => 'btn btn-primary']);
        }
        echo '<br/>';
        echo '<br/>';
        echo '<button onclick="printContent(\'printinfo\')">Imprime Orden de Salida</button>';
      echo '</div>';  
      echo "<div class='col-sm-3'>";
      if ($userrole != ROLE_SALES){
        //echo "<div class='actions'>";
          echo "<h3>". __('Actions')."</h3>";
          echo "<ul style='list-style:none;'>";
            $orderCode=str_replace(' ','',$order['Order']['order_code']);
            $orderCode=str_replace('/','',$orderCode);
            $filename='Factura_'.$orderCode;
            
            echo "<li>".$this->Html->link(__('Imprimir'), ['action' => 'imprimirVenta', $order['Order']['id']])."</li>";
            
            echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'verPdfVenta','ext'=>'pdf',$order['Order']['id'],$filename))."</li>";
            echo "<br/>";
            if ($bool_sale_edit_permission) { 
              echo "<li>".$this->Html->link(__('Edit Sale'), ['action' => 'editarVenta', $order['Order']['id']])."</li>";
              echo "<br/>";
            }
            if ($bool_sale_annul_permission){ 
              echo "<li>".$this->Form->postLink('Anular Venta', ['action' => 'anularVenta', $order['Order']['id']], [], __('Está seguro que quiere anular la venta # %s?', $order['Order']['order_code']))."</li>";
              echo "<br/>";
            } 
            echo "<li>".$this->Html->link(__('List Sales'), array('action' => 'resumenVentasRemisiones'))."</li>";
            if ($bool_sale_add_permission) { 
              echo "<li>".$this->Html->link(__('New Sale'), array('action' => 'crearVenta'))."</li>";
              echo "<br/>"; 
            } 
            if ($bool_client_index_permission) { 
              echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
            }
            if ($bool_client_add_permission) { 
              echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
            }
          echo "</ul>";
        //echo "</div>";
      }
      echo '</div>';
    echo '</div>';    
  echo "</div>";
?>
<div class="related">
<?php 
	if (!empty($summedMovements)){
		echo "<h3>".__('Products Sold')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Unit Price')."</th>";
					echo "<th class='centered'>".__('Quantity')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Total Price')."</th>";
          echo "<th class='centered' style='width:10%'>Margen</th>";
				echo "</tr>";
			echo "</thead>";
			
			$totalQuantity=0;
			$totalPrice=0;
      $totalCost=0;
			echo "<tbody>";
			foreach ($summedMovements as $summedMovement){ 
        $totalQuantity+=$summedMovement['StockMovement']['total_product_quantity'];
				$totalPrice+=$summedMovement['StockMovement']['total_product_price'];
        $totalCost+=($summedMovement['StockMovement']['service_total_cost'] > 0?$summedMovement['StockMovement']['service_total_cost']:$summedMovement['StockMovement']['total_product_cost']);
				
				echo "<tr>";
          //if ($summedMovement['StockMovement']['production_result_code_id']>0){	
          if ($summedMovement['Product']['product_type_id'] == PRODUCT_TYPE_BOTTLE){	
            echo "<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$rawMaterials[$summedMovement['StockItem']['raw_material_id']].")</td>";
          }
          else {
            echo "<td>".$summedMovement['Product']['name']."</td>";
          }
          echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],4,".",",")."</span></td>";
          echo "<td class='centered'>".number_format($summedMovement['StockMovement']['total_product_quantity'],0,".",",")."</td>";
          echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['total_product_price'],2,".",",")."</span></td>";
          echo "<td class='centered percentage'><span>".number_format(100*($summedMovement['StockMovement']['total_product_price']-($summedMovement['StockMovement']['service_total_cost'] > 0? $summedMovement['StockMovement']['service_total_cost']:$summedMovement['StockMovement']['total_product_cost']))/$summedMovement['StockMovement']['total_product_price'],2,".",",")."</span> %</td>";
				echo "</tr>";
			}
		
				echo "<tr class='totalrow'>";
					echo "<td>Sub Total</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".($totalQuantity>0?number_format($totalPrice/$totalQuantity,2,".",","):"-")."</span></td>";
					echo "<td class='centered'>".number_format($totalQuantity,0,".",",")."</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalPrice,2,".",",")."</span></td>";
          echo "<td class='centered percentage'><span>".number_format(100 * ($totalPrice-$totalCost)/$totalPrice,2,".",",")."</span> %</td>";
				echo "</tr>";
				
			echo "</tbody>";

		echo "</table>";
	}
	if (!empty($invoice)){
		if ($invoice['Invoice']['bool_credit']&&!empty($cashReceiptsForInvoice)){
			echo "<h3>".__('Pagos para esta Factura de Crédito')."</h3>";
			echo "<table cellpadding = '0' cellspacing = '0'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>Fecha Recibo</th>";
						echo "<th>Número Recibo</th>";
						echo "<th class='centered' style='width:70px'>Monto Pagado</th>";
					echo "</tr>";
				echo "</thead>";
				
				$totalPaidCS=0;
				echo "<tbody>";
				foreach ($cashReceiptsForInvoice as $cashReceipt){ 
					if ($order['Order']['id'] == 12534){
            //pr($cashReceipt);  
          }
					$receiptDateTime=new DateTime($cashReceipt['CashReceipt']['receipt_date']);
          //PAYMENT IS LITERALLY WHAT WAS PAID, AMOUNT INCLUDES INCREMENT, DISCOUNT AND EXCHANGE RATE DIFFERENCES
					if ($cashReceipt['Currency']['id']==CURRENCY_USD){
							$totalPaidCS+=$cashReceipt['CashReceiptInvoice']['payment']*$exchangeRateCurrent;
              //$totalPaidCS+=$cashReceipt['CashReceiptInvoice']['amount']*$exchangeRateCurrent;
						}
						else {
							$totalPaidCS+=$cashReceipt['CashReceiptInvoice']['payment'];
              //$totalPaidCS+=$cashReceipt['CashReceiptInvoice']['amount'];
						}
          
          echo "<tr>";
						echo "<td>".$receiptDateTime->format('d-m-Y')."</td>";
						echo "<td>".$this->Html->Link($cashReceipt['CashReceipt']['receipt_code'],array('controller'=>'cash_receipts','action'=>'view',$cashReceipt['CashReceipt']['id']))."</td>";
						echo "<td class='centered amount'><span class='currency'>".$cashReceipt['Currency']['abbreviation']." </span><span class='amountright'>".number_format($cashReceipt['CashReceiptInvoice']['payment'],2,".",",")."</td>";
            //echo "<td class='centered amount'><span class='currency'>".$cashReceipt['Currency']['abbreviation']." </span><span class='amountright'>".number_format($cashReceipt['CashReceiptInvoice']['amount'],2,".",",")."</td>";
					echo "</tr>";
				}
					echo "<tr class='totalrow'>";
						echo "<td>Total</td>";
						echo "<td></td>";
						echo "<td class='centered amount'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalPaidCS,2,".",",")."</span></td>";
					echo "</tr>";
				echo "</tbody>";

			echo "</table>";
		}
	}
?>

</div>


<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='related'>";
    if (!empty($invoice)){
      if (!empty($invoice['AccountingRegisterInvoice'])){
        foreach ($invoice['AccountingRegisterInvoice'] as $accountingRegisterInvoice){
          $accountingRegister=$accountingRegisterInvoice['AccountingRegister'];
          echo "<h3>Comprobante ".$accountingRegister['register_code']."</h3>";
          $accountingMovementTable= "<table cellpadding = '0' cellspacing = '0'>";
            $accountingMovementTable.="<thead>"; 
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<th>".__('Accounting Code')."</th>";
                $accountingMovementTable.= "<th>".__('Description')."</th>";
                $accountingMovementTable.= "<th>".__('Concept')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Debe')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Haber')."</th>";
                //$accountingMovementTable.= "<th></th>";
              $accountingMovementTable.= "</tr>";
            $accountingMovementTable.="</thead>";
            $totalDebit=0;
            $totalCredit=0;
            $accountingMovementTable.="<tbody>";				
            foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
              //pr($accountingMovement);
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['concept']."</td>";
                
                if ($accountingMovement['bool_debit']){
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $totalDebit+=$accountingMovement['amount'];
                }
                else {
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $totalCredit+=$accountingMovement['amount'];
                }
                //$accountingMovementTable.= "<td>".($accountingMovement['bool_debit']?__('Debe'):__('Haber'))."</td>";
                //$accountingMovementTable.= "<td class='actions'>";
                  //$accountingMovementTable.= $this->Html->link(__('View'), array('controller' => 'accounting_movements', 'action' => 'view', $accountingMovement['id'])); 
                
                  //$accountingMovementTable.= $this->Html->link(__('Edit'), array('controller' => 'accounting_movements', 'action' => 'edit', $accountingMovement['id'])); 
                  //$accountingMovementTable.= $this->Form->postLink(__('Delete'), array('controller' => 'accounting_movements', 'action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['id'])); 
                //$accountingMovementTable.= "</td>";
              $accountingMovementTable.= "</tr>";
            } 
            if (!empty($accountingRegister['AccountingMovement'])){
              $accountingMovementTable.= "<tr class='totalrow'>";
                $accountingMovementTable.= "<td>Total</td>";
                $accountingMovementTable.= "<td></td>";
                $accountingMovementTable.= "<td></td>";
                $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalDebit."</span></td>";
                $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalCredit."</span></td>";
              $accountingMovementTable.= "</tr>";
            }
            $accountingMovementTable.= "</tbody>";
          $accountingMovementTable.= "</table>";
          echo $accountingMovementTable;				
        }
      }
    }
    echo "</div>";  
  }
?>	

<div id='printinfo'>
<!-- buscar el  que ha sido asignado con el insert -->
<?php 
  echo "<div id='companytitle'>ORNASA</div>";
  echo "<br/>";
  echo "<br/>";
  
  //visualize the date
  $orderDateTime=new DateTime($order['Order']['order_date']);
  echo "<div class='orderdate'>";
    echo "<table>";
      echo "<thead>";
        echo "<tr>";
          echo "<th>Día</th>";
          echo "<th>Mes</th>";
          echo "<th>Año</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";
        echo "<tr>";
          echo "<td>".date_format($orderDateTime,'d')."</td>";
          echo "<td>".date_format($orderDateTime,'m')."</td>";
          echo "<td>".date_format($orderDateTime,'Y')."</td>";
        echo "</tr>";
      echo "</tbody>";
    echo "</table>";
  echo "</div>";
  
  echo "<div class='ordertext'><span style='width:15%'>Factura Número:</span>".$order['Order']['order_code']."</div>"; 
  echo "<br/>";
  echo "<div class='ordertext'><span style='width:15%'>Cliente:</span>".$order['ThirdParty']['company_name']."</div>";
  
  if (!empty($summedMovements)){
    echo "<table class='producttable'>";
      echo "<thead>";
        echo "<tr>";		
          echo "<th class='productname'>Producto</th>";
          echo "<th class='unitprice'>Precio Unidad</th>";
          echo "<th class='quantity'>Cantidad</th>";
          echo "<th class='totalprice'>Precio</th>";
          //echo "<th class='margin'>Margen</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";				
  
      $totalQuantity=0;
      $totalPrice=0;
      $totalCost=0;
      foreach ($summedMovements as $summedMovement){ 
        $totalQuantity+=$summedMovement['StockMovement']['total_product_quantity'];
        $totalPrice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement['StockMovement']['total_product_quantity'];
        $totalCost+=$summedMovement['StockMovement']['total_product_cost'];
        echo "<tr>";
          if ($summedMovement['StockMovement']['production_result_code_id']>0){
            echo "<td class='productname'>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$rawMaterials[$summedMovement['StockItem']['raw_material_id']].")</td>";
          }
          else {
            echo "<td class='productname'>".$summedMovement['Product']['name']."</td>";
          }
          
          echo "<td class='unitprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</td>";
          echo "<td class='quantity'>".number_format($summedMovement['StockMovement']['total_product_quantity'],0,".",",")."</td>";
          echo "<td class='totalprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['total_product_price'],2,".",",")."</td>";
          //echo "<td class='margin percentage'><span>".number_format(100*($summedMovement['StockMovement']['total_product_price']-$summedMovement['StockMovement']['total_product_cost'])/$summedMovement['StockMovement']['total_product_price'],2,".",",")."</span> %</td>";
          
          
        echo "</tr>";
      }
        echo "<tr><td class='totaltext bottomrow'>SUB TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>SUB TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>IVA</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($invoice['Invoice']['iva_price'],2,".",",")."</td></tr>";
        echo "<tr><td class='totaltext bottomrow'>TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($invoice['Invoice']['total_price'],2,".",",")."</td></tr>";
        
      echo "</tbody>";
    
    echo "</table>";
  }			
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";
  echo "&nbsp;<br/>";	
?>
</div>
</div>

<script type="text/javascript">
	<!--
		function printContent(id){
			str=document.getElementById(id).innerHTML
			newwin=window.open('','printwin','left=5,top=5,width=640,height=480')
			newwin.document.write('<HTML>\n<HEAD>\n')
			
			newwin.document.write('<style type="text/css">\n')
			newwin.document.write('#all {font-size:12px;}\n')
			newwin.document.write('#companytitle {font-size:20px;font-weight:bold;}\n')
			newwin.document.write('.ordertext {font-size:14px;font-weight:bold;}\n')
			newwin.document.write('.orderdate {width:20%;float:right;clear:right;}\n')
			newwin.document.write('.producttable {width:100%;}\n')
			newwin.document.write('.productname {width:45%;}\n')
			newwin.document.write('.unitprice {width:15%;}\n')
			newwin.document.write('.quantity {width:20%;}\n')
			newwin.document.write('.totalprice {width:20%;}\n')
			newwin.document.write('td {border:1px solid black;}\n')
			newwin.document.write('td.bottomrow {border:0px;}\n')
			newwin.document.write('td.totaltext {font-weight:bold;}\n')
			newwin.document.write('</style>\n')
			
			newwin.document.write('<script>\n')
			newwin.document.write('function chkstate(){\n')
			newwin.document.write('if(document.readyState=="complete"){\n')
			newwin.document.write('window.close()\n')
			newwin.document.write('}\n')
			newwin.document.write('else{\n')
			newwin.document.write('setTimeout("chkstate()",2000)\n')
			newwin.document.write('}\n')
			newwin.document.write('}\n')
			newwin.document.write('function print_win(){\n')
			newwin.document.write('window.print();\n')
			newwin.document.write('chkstate();\n')
			newwin.document.write('}\n')
			newwin.document.write('<\/script>\n')
			newwin.document.write('</HEAD>\n')
			newwin.document.write('<BODY style="margin:2px;max-height:300px;" onload="print_win()">\n')
			newwin.document.write('<div id="all" style="font-size:11px;">\n')
			newwin.document.write(str)
			newwin.document.write('</div>\n')
			newwin.document.write('</BODY>\n')
			newwin.document.write('</HTML>\n')
			newwin.document.close()
		}
	//-->
</script>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Venta'), ['action' => 'delete', $order['Order']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la venta # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $order['Order']['order_code']));
  }
?>
</div>