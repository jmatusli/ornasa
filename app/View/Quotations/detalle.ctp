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
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
      if (parseFloat($(this).find('.amountrightfour').text())<0){
				$(this).find('.amountrightfour').prepend("-");
			}
      $(this).find('.amountrightfour').number(true,4);
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
	});
</script>

<div class="quotations view">
<?php 
	echo "<h1>".__('Quotation')." ".$quotation['Quotation']['quotation_code']."</h1>";
	echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-6'>";
        echo '<h2>Datos de cotización</h2>';
				echo "<dl>";
					$quotationDateTime=new DateTime($quotation['Quotation']['quotation_date']);
					$rejectedWarning="";
					if (date('Y-m-d')>$quotation['Quotation']['due_date']){
						$rejectedWarning="<div class='redfatwarning'> Se debe marcar la cotización como caída porque ya venció; para cálculos la cotización se considera como caída</div>";
					}
					$dueDateTime=new DateTime($quotation['Quotation']['due_date']);
					echo "<dt>".__('Quotation Date')."</dt>";
					echo "<dd>".$quotationDateTime->format('d-m-Y')."</dd>";
					echo "<dt>".__('Quotation Code')."</dt>";
					echo "<dd>".h($quotation['Quotation']['quotation_code'])."</dd>";		
					echo "<dt>".__('Caída?')."</dt>";
					echo "<dd>".(($quotation['Quotation']['bool_rejected'])?__('Yes'):__('No'))."</dd>";
					if (!empty($rejectedWarning)){
						// echo $rejectedWarning;
					}
					echo "<dt>".__('Usuario quien grabó')."</dt>";
					echo "<dd>".$this->Html->link($quotation['RecordUser']['first_name'].' '.$quotation['RecordUser']['last_name'], ['controller' => 'users', 'action' => 'view', $quotation['RecordUser']['id']],['target'=>'_blank'])."</dd>";
          
          if (!empty($quotation['VendorUser']['id'])){
            echo "<dt>".__('Ejecutivo de Venta (dueño)')."</dt>";
            echo "<dd>".$this->Html->link($quotation['VendorUser']['first_name'].' '.$quotation['VendorUser']['last_name'], ['controller' => 'users', 'action' => 'view', $quotation['VendorUser']['id']],['target'=>'_blank'])."</dd>";
          }
          echo "<dt>".__('Warehouse')."</dt>";
          echo "<dd>".$this->Html->link($quotation['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'view', $quotation['Warehouse']['id']],['target'=>'_blank'])."</dd>";
        echo '</dl>';
        
        echo '<h2 style="clear:left;width:100%">Datos de cliente</h2>';    
        echo '<dl>';
					echo "<dt>".__('Client')."</dt>";
					echo '<dd>'.(
            empty($quotation['Client']['id']) || $quotation['Client']['bool_generic'] ?
            $quotation['Quotation']['client_name']:
            $this->Html->link($quotation['Client']['company_name'], ['controller' => 'thirdParties', 'action' => 'verCliente', $quotation['Client']['id']],['target'=>'_blank'])
          )."</dd>";
          
          echo "<dt>".__('Phone')."</dt>";
          echo "<dd>".(
            empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']?
            $quotation['Quotation']['client_phone']:
            (
              empty($quotation['Quotation']['client_phone'])?
                (
                  empty($quotation['Client']['phone'])?
                  "-":
                  $quotation['Client']['phone']
                ):
                $quotation['Quotation']['client_phone']
            )
          )."</dd>";
          echo "<dt>".__('Email')."</dt>";
          echo "<dd>".(
            empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']?
            $quotation['Quotation']['client_email']:
            (
              empty($quotation['Quotation']['client_email'])?
              (
                empty($quotation['Client']['email'])?
                "-":
                $quotation['Client']['email']
              ):
              $quotation['Quotation']['client_email']
            )
          )."</dd>";
        /*  
          echo "<dt>".__('RUC')."</dt>";
          echo "<dd>".(
            empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']?
            $quotation['Quotation']['client_ruc']:
            (
              empty($quotation['Quotation']['client_ruc'])?
              (
                empty($quotation['Client']['ruc_number'])?
                "-":
                $quotation['Client']['ruc_number']
              ):
              $quotation['Quotation']['client_ruc']
            )
          )."</dd>";
        */  
          echo "<dt>".__('Client Type')."</dt>";
          echo "<dd>".(
            empty($quotation['ClientType']['id'])?
            (
              empty($quotation['Client']['ClientType']['id'])?
              "-":
              $this->Html->link($quotation['Client']['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$quotation['Client']['ClientType']['id']]
              )
            )
            :$this->Html->link($quotation['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$quotation['ClientType']['id']])
          )."</dd>";
          echo "<dt>".__('Zone')."</dt>";
          echo "<dd>".(
            empty($quotation['Zone']['id'])?
            (
              empty($quotation['Client']['Zone']['id'])?
              "-":
              $this->Html->link($quotation['Client']['Zone']['name'],['controller'=>'zones','action'=>'detalle',$quotation['Client']['Zone']['id']])
            ):
            $this->Html->link($quotation['Zone']['name'],['controller'=>'zones','action'=>'detalle',$quotation['Zone']['id']])
          )."</dd>";
          
          echo "<dt>".__('Address')."</dt>";
          echo "<dd>".(
            empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']?
            $quotation['Quotation']['client_address']:
            (
              empty($quotation['Quotation']['client_address'])?
              (
                empty($quotation['Client']['address'])?
                "-":
                $quotation['Client']['address']
              ):
              $quotation['Quotation']['client_address']
            )
          )."</dd>";
        echo '</dl>';
			echo "</div>";
			echo "<div class='col-md-6'>";
        echo "<dl>";
          echo "<dt>".__('Price Subtotal')."</dt>";
					echo "<dd>".$quotation['Currency']['abbreviation']." ".($quotation['Quotation']['price_subtotal'])."</dd>";
					echo "<dt>".__('Price Iva')."</dt>";
					echo "<dd>".$quotation['Currency']['abbreviation']." ".($quotation['Quotation']['price_iva'])."</dd>";
					echo "<dt>".__('Price Total')."</dt>";
					echo "<dd>".$quotation['Currency']['abbreviation']." ".($quotation['Quotation']['price_total'])."</dd>";
        echo "</dl>";  
      
        echo '<br/>';
        echo "<dl>";  
          echo "<dt>Aplica retención</dt>";
          echo "<dd>".h($quotation['Quotation']['bool_retention']?__("Yes"):__("No"))."</dd>";
          if ($quotation['Quotation']['bool_retention']){
            echo "<dt>Número retención</dt>";
            echo "<dd>".h($quotation['Quotation']['retention_number'])."</dd>";
            echo "<dt>Monto retención</dt>";
            echo "<dd>".$quotation['Currency']['abbreviation']." ".number_format($quotation['Quotation']['retention_amount'],2,".",",")."</dd>";
          }
        echo "</dl>";  
      
				if (!empty($quotation['QuotationRemark'])){
					echo "<table>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Fecha</th>";
								echo "<th>Vendedor</th>";
								echo "<th>Remarca</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
						foreach ($quotation['QuotationRemark'] as $quotationRemark){
							$remarkDateTime=new DateTime($quotationRemark['remark_datetime']);
							echo "<tr>";
								echo "<td>".$remarkDateTime->format('d-m-Y H:i')."</td>";
								echo "<td>".$quotationRemark['User']['username']."</td>";
								echo "<td>".$quotationRemark['remark_text']."</td>";
							echo "</tr>";
						}
						echo "</tbody>";
					echo "</table>";
				}
        
        echo '<dl>';  
          echo "<dt>Crédito</dt>";
          echo "<dd>".h($quotation['Quotation']['bool_credit']?__("Yes"):__("No"))."</dd>";
          if ($quotation['Quotation']['bool_credit']){
            echo "<dt>Autorización de crédito</dt>";
            echo '<dd>'.(empty($quotation['CreditAuthorizationUser']['id'])?'Autorizado por configuración de cliente':$this->Html->link($quotation['CreditAuthorizationUser']['first_name'].' '.$quotation['CreditAuthorizationUser']['last_name'], ['controller' => 'users', 'action' => 'view', $quotation['CreditAuthorizationUser']['id']],['target'=>'_blank'])).'</dd>';
          }
          echo "<dt>".__('Forma de Pago')."</dt>";
					if (!empty($quotation['Quotation']['payment_form'])){
						echo "<dd>".$quotation['Quotation']['payment_form']."</dd>";
					}
					else {
						echo "<dd>-</dd>";
					}
					echo "<dt>".__('Bool Iva')."</dt>";
					echo "<dd>".($quotation['Quotation']['bool_iva']?__('Yes'):__('No'))."</dd>";
					
          echo "<dt>Orden de Venta Asociada</dt>";
					echo "<dd>".(empty($quotation['SalesOrder']['id'])?"-":($this->Html->Link($quotation['SalesOrder']['sales_order_code'],['controller'=>'salesOrders','action'=>'detalle',$quotation['SalesOrder']['id']])))."</dd>";
					echo "<dt>".__('Observaciones para pdf')."</dt>";
					if (!empty($quotation['Quotation']['observation'])){
						echo "<dd>".$quotation['Quotation']['observation']."</dd>";
					}
					else {
						echo "<dd>-</dd>";
					}
				echo "</dl>";
        
        echo "<dl>";
          echo "<dt>".__('Due Date')."</dt>";
					echo "<dd>".$dueDateTime->format('d-m-Y')."</dd>";
					echo "<dt>".__('Tiempo de Entrega')."</dt>";
					echo "<dd>".(empty($quotation['Quotation']['delivery_time'])?'-':$quotation['Quotation']['delivery_time'])."</dd>";
        echo "</dl>";  
			echo "</div>";
		echo "</div>";
	echo "</div>";	
				
?> 
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'detallePdf','ext'=>'pdf', $quotation['Quotation']['id'],$fileName],['target'=>'_blank','class'=>'pdflink'])."</li>";
		//echo "<li>".$this->Html->link(__('Enviar Cotización'), ['controller'=>'system_emails','action' => 'add', $quotation['Quotation']['id']],['target'=>'_blank'])."</li>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Quotation'), ['action' => 'editar', $quotation['Quotation']['id']])."</li>";
			echo "<br/>";
		}
		//else {
		//	if ($bool_edit_forbidden_because_salesorder_authorized){
		//		echo "<p class='comment'>No se puede editar porque la orden de venta correspondiente ya está autorizada.  Para editar la cotización, marque la orden de venta como no autorizada.</p>";
		//	}
		//}
		
		//
		echo "<li>".$this->Html->link(__('List Quotations'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Quotation'), ['action' => 'crear'])."</li>";
		echo "<br/>";
    //echo 'bool_crearOrdenVentaExterna_permission is '.$bool_crearOrdenVentaExterna_permission.'<br/>';
    //echo 'quotation bool_sales_order is '.$quotation['Quotation']['bool_sales_order'].'<br/>';
    if ($bool_crearOrdenVentaExterna_permission && !$quotation['Quotation']['bool_sales_order']){
			echo "<li>".$this->Html->link(__('Crear Orden de Venta'), ['controller' => 'salesOrders', 'action' => 'crearOrdenVentaExterna',$quotation['Quotation']['id']])."</li>";
      echo "<br/>";
		}
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
	if (!empty($quotation['QuotationProduct'])){
		echo "<h3>".__('Related Quotation Products')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th style='width:10%;'>".__('Product Id')."</th>";
        echo "<th>".__('Preforma')."</th>";
				//echo "<th>".__('Imagen')."</th>";
				//echo "<th>".__('Observación')."</th>";
				//echo "<th>".__('T.de Entrega')."</th>";
				echo "<th style='width:10%;'>".__('Product Quantity')."</th>";
				echo "<th style='width:10%;'>".__('Product Unit Price')."</th>";
				echo "<th style='width:10%;'>".__('Product Total Price')."</th>";
				//echo "<th>".__('IVA?')."</th>";
				//echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		$totalProductQuantity=0;	
		foreach ($quotation['QuotationProduct'] as $quotationProduct){
			$totalProductQuantity+=$quotationProduct['product_quantity'];
			if ($quotationProduct['currency_id']==CURRENCY_CS){
				$classCurrency=" class='CScurrency'";
			}
			elseif ($quotationProduct['currency_id']==CURRENCY_USD){
				$classCurrency=" class='USDcurrency'";
			}
			echo "<tr>";
				echo "<td>".$this->Html->link($quotationProduct['Product']['name'],array('controller'=>'products','action'=>'view',$quotationProduct['Product']['id']),array('target'=>'blank'))."</td>";
				//if (!empty($quotationProduct['Product']['url_image'])){
				//	$url=$quotationProduct['Product']['url_image'];
				//	$productimage=$this->App->assetUrl($url);
				//	echo "<td><img src='".$productimage."' class='smallimage'></img></td>";
				//}
				//else {
				//	echo "<td></td>";
				//}
				//echo "<td>".str_replace("\n","<br/>",$quotationProduct['product_description'])."</td>";
				//echo "<td>".$quotationProduct['delivery_time']."</td>";
				echo "<td>".(empty($quotationProduct['RawMaterial'])?"-":$quotationProduct['RawMaterial']['name'])."</td>";
        echo "<td><span class='amountright'>".$quotationProduct['product_quantity']."</span></td>";
				echo "<td".$classCurrency."><span class='currency'></span><span class='amountrightfour'>".$quotationProduct['product_unit_price']."</span></td>";
				echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".$quotationProduct['product_total_price']."</span></td>";
				//echo "<td>".($quotationProduct['bool_iva']?__('Yes'):__('No'))."</td>";
				//echo "<td class='actions'>";
				//	echo $this->Html->link(__('View'), array('controller' => 'quotation_products', 'action' => 'view', $quotationProduct['id']));
				//	echo $this->Html->link(__('Edit'), array('controller' => 'quotation_products', 'action' => 'edit', $quotationProduct['id']));
					//echo $this->Form->postLink(__('Delete'), array('controller' => 'quotation_products', 'action' => 'delete', $quotationProduct['id']), array(), __('Are you sure you want to delete # %s?', $quotationProduct['id']));
				//echo "</td>";
			echo "</tr>";
		}
			echo "<tr class='totalrow'>";
				echo "<td>Subtotal</td>";
				//echo "<td></td>";
				//echo "<td></td>";
				echo "<td></td>";
				echo "<td><span class='amountright'>".$totalProductQuantity."</span></td>";
				echo "<td></td>";
				echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".number_format($quotation['Quotation']['price_subtotal'],2,".",",")."</span></td>";
				//echo "<td></td>";
			echo "</tr>";
			echo "<tr'>";
				echo "<td>IVA</td>";
				//echo "<td></td>";
				//echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".number_format($quotation['Quotation']['price_iva'],2,".",",")."</span></td>";
				//echo "<td></td>";
			echo "</tr>";
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				//echo "<td></td>";
				//echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".number_format($quotation['Quotation']['price_total'],2,".",",")."</span></td>";
				//echo "<td></td>";
			echo "</tr>";
		echo "</table>";
    echo "<br/>";
	}
?>
</div>
<div class="related">
<?php 
/*
	if (!empty($quotation['SalesOrder']['id'])){
		echo "<h3>".__('Ordenes de Venta para esta Cotización')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Sales Order Date')."</th>";
					echo "<th>".__('Sales Order Code')."</th>";
					echo "<th>".__('Subtotal')."</th>";
					echo"<th class='actions'>".__('Actions')."</th>";
				echo "</tr>";
			echo "</thead>";
				
			echo "<tbody>";
				$totalSubtotalCS=0;
				$totalSubtotalUSD=0;
				foreach ($quotation['SalesOrder'] as $salesOrder){
					$salesOrderDateTime= new DateTime($salesOrder['sales_order_date']);
					
					if ($salesOrder['currency_id']==CURRENCY_CS){
						$classCurrency=" class='CScurrency'";
						$totalSubtotalCS+=$salesOrder['price_subtotal'];
					}
					elseif ($salesOrder['currency_id']==CURRENCY_USD){
						$classCurrency=" class='USDcurrency'";
						$totalSubtotalUSD+=$salesOrder['price_subtotal'];
					}
					
					if ($salesOrder['bool_annulled']){
						echo "<tr class='italic'>";
					}
					else {
						echo "<tr>";
					}
						echo "<td>".$salesOrderDateTime->format('d-m-Y')."</td>";
						echo "<td>".$this->Html->Link($salesOrder['sales_order_code'].($salesOrder['bool_annulled']?' (Anulada)':''),array('controller'=>'sales_orders','action'=>'view',$salesOrder['id']))."</td>";
						echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".$salesOrder['price_subtotal']."</td>";
					
						echo "<td class='actions'>";
							echo $this->Html->link(__('View'), array('controller' => 'sales_orders', 'action' => 'detalle', $salesOrder['id']));
							echo $this->Html->link(__('Edit'), array('controller' => 'sales_orders', 'action' => 'editar', $salesOrder['id']));
							//echo $this->Form->postLink(__('Delete'), array('controller' => 'sales_orders', 'action' => 'delete', $salesOrder['id']), array(), __('Are you sure you want to delete # %s?', $salesOrder['id']));
						echo "</td>";
					echo "</tr>";
				}
				if ($totalSubtotalCS>0){
					echo "<tr class='totalrow'>";
						echo "<td>Totales C$</td>";
						echo "<td></td>";
						echo "<td class='CScurrency'><span class='currency'></span><span class='amountright'>".number_format($totalSubtotalCS,2,".",",")."</span></td>";
						echo "<td></td>";
					echo "</tr>";
				}
				if ($totalSubtotalUSD>0){
					echo "<tr class='totalrow'>";
						echo "<td>Totales US$</td>";
						echo "<td></td>";
						echo "<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".number_format($totalSubtotalUSD,2,".",",")."</span></td>";
						echo "<td></td>";
					echo "</tr>";
				}
			echo "<tbody>";
		echo "</table>";
	}
*/
?>
</div>

<div class='related'>
<?php
	echo "<dl>";
		echo "<dt>".__('Tiempos de entrega en Pdf?')."</dt>";
		echo "<dd>".($quotation['Quotation']['bool_print_delivery_time']?"Si":"No")."</dd>";
		
		echo "<dt>".__('Remarca sobre entrega')."</dt>";
		if (!empty($quotation['Quotation']['remark_delivery'])){
			echo "<dd>".$quotation['Quotation']['remark_delivery']."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		echo "<dt>".__('Remarca sobre cheque')."</dt>";
		if (!empty($quotation['Quotation']['remark_cheque'])){
			echo "<dd>".$quotation['Quotation']['remark_cheque']."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		echo "<dt>".__('Remarca sobre elaboración')."</dt>";
		if (!empty($quotation['Quotation']['remark_elaboration'])){
			echo "<dd>".$quotation['Quotation']['remark_elaboration']."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		
		echo "<dt>".__('Persona quien autoriza')."</dt>";
		if (!empty($quotation['Quotation']['authorization'])){
			echo "<dd>".$quotation['Quotation']['authorization']."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		
	echo "</dl>";
  echo "<br/>"
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Cotización'), ['action' => 'delete', $quotation['Quotation']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la cotización # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $quotation['Quotation']['quotation_code']));
  }
?>
</div>