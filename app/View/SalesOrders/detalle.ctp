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
			//$(this).find('.amountright').number(true,2);
      var roundedValue=parseFloat($(this).find('.amountright').text()).toString()
      $(this).find('.amountright').text(roundedValue);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>

<div class="salesOrders view">
<?php 
	echo "<h1>";
		echo __('Sales Order')." ".$salesOrder['SalesOrder']['sales_order_code'];
		if ($salesOrder['SalesOrder']['bool_annulled']){
			echo " (Anulada)";
		}
		else {
			if ($salesOrder['SalesOrder']['bool_invoice']){
				echo " (Entregada)";
			}
			//elseif ($salesOrder['SalesOrder']['bool_authorized']){
			//	echo " (Autorizada)";
			//}
		}
		
	echo "</h1>";
  $salesOrderDateTime=new DateTime($salesOrder['SalesOrder']['sales_order_date']);
  $dueDateTime=new DateTime($salesOrder['SalesOrder']['due_date']);
  //pr($salesOrder['Delivery']);
        
  
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo '<h2>Datos de orden de venta</h2>';
        echo "<dl>";
          echo "<dt>".__('Sales Order Date')."</dt>";
          echo "<dd>".$salesOrderDateTime->format('d-m-Y')."</dd>";
          echo "<dt>".__('Sales Order Code')."</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['sales_order_code'])."</dd>";
          
          echo "<dt>".__('Usuario quien grabó')."</dt>";
					echo "<dd>".$this->Html->link($salesOrder['RecordUser']['first_name'].' '.$salesOrder['RecordUser']['last_name'], ['controller' => 'users', 'action' => 'view', $salesOrder['RecordUser']['id']],['target'=>'_blank'])."</dd>";
          if (!empty($salesOrder['VendorUser']['id'])){
            echo "<dt>".__('Ejecutivo de Venta (dueño)')."</dt>";
            echo "<dd>".$this->Html->link($salesOrder['VendorUser']['first_name'].' '.$salesOrder['VendorUser']['last_name'], ['controller' => 'users', 'action' => 'view', $salesOrder['VendorUser']['id']],['target'=>'_blank'])."</dd>";
          }
          echo "<dt>".__('Warehouse')."</dt>";
          echo "<dd>".$this->Html->link($salesOrder['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'view', $salesOrder['Warehouse']['id']],['target'=>'_blank'])."</dd>";
        echo '</dl>';
        
        echo '<h2 style="clear:left;width:100%">Datos de cliente</h2>';    
        echo '<dl>';
					echo "<dt>".__('Client')."</dt>";
					echo "<dd>".(
            empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']?
            $salesOrder['SalesOrder']['client_name']:
            $this->Html->link($salesOrder['Client']['company_name'], ['controller' => 'thirdParties', 'action' => 'verCliente', $salesOrder['Client']['id']],['target'=>'_blank'])
          )."</dd>";
          echo "<dt>".__('Phone')."</dt>";
          echo "<dd>".(
            empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']?
            $salesOrder['SalesOrder']['client_phone']:
            (
              empty($salesOrder['SalesOrder']['client_phone'])?
                (
                  empty($salesOrder['Client']['phone'])?
                  "-":
                  $salesOrder['Client']['phone']
                ):
                $salesOrder['SalesOrder']['client_phone']
            )
          )."</dd>";
          echo "<dt>".__('Email')."</dt>";
          echo "<dd>".(
            empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']?
            $salesOrder['SalesOrder']['client_email']:
            (
              empty($salesOrder['SalesOrder']['client_email'])?
              (
                empty($salesOrder['Client']['email'])?
                "-":
                $salesOrder['Client']['email']
              ):
              $salesOrder['SalesOrder']['client_email']
            )
          )."</dd>";
          echo "<dt>".__('RUC')."</dt>";
          echo "<dd>".(
            empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']?
            $salesOrder['SalesOrder']['client_ruc']:
            (
              empty($salesOrder['SalesOrder']['client_ruc'])?
              (
                empty($salesOrder['Client']['ruc_number'])?
                "-":
                $salesOrder['Client']['ruc_number']
              ):
              $salesOrder['SalesOrder']['client_ruc']
            )
          )."</dd>";

          echo "<dt>".__('Client Type')."</dt>";
          echo "<dd>".(
            empty($salesOrder['ClientType']['id'])?
            (
              empty($salesOrder['Client']['ClientType']['id'])?
              "-":
              $this->Html->link($salesOrder['Client']['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$salesOrder['Client']['ClientType']['id']])
             ):
             $this->Html->link($salesOrder['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$salesOrder['ClientType']['id']])
          )."</dd>";
          echo "<dt>".__('Zone')."</dt>";
          echo "<dd>".(
            empty($salesOrder['Zone']['id'])?
            (
              empty($salesOrder['Client']['Zone']['id'])?
              "-":
              $this->Html->link($salesOrder['Client']['Zone']['name'],['controller'=>'zones','action'=>'detalle',$salesOrder['Client']['Zone']['id']])
            ):
            $this->Html->link($salesOrder['Zone']['name'],['controller'=>'zones','action'=>'detalle',$salesOrder['Zone']['id']])
          )."</dd>";
          echo "<dt>".__('Address')."</dt>";
          echo "<dd>".(empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']?(empty($salesOrder['SalesOrder']['client_address'])?"-":$salesOrder['SalesOrder']['client_address']):(empty($salesOrder['Client']['address'])?"-":$salesOrder['Client']['address']))."</dd>";
          
          
        /*  
          echo "<dt>".__('Vehicle')."</dt>";
          echo "<dd>".(empty($salesOrder['Vehicle']['id'])?'-':$salesOrder['Vehicle']['name'])."</dd>";
          
          echo "<dt>".__('Conductor')."</dt>";
					echo "<dd>".(empty($salesOrder['DriverUser']['id'])?'-':$this->Html->link($salesOrder['DriverUser']['first_name'].' '.$salesOrder['DriverUser']['last_name'], ['controller' => 'users', 'action' => 'view', $salesOrder['RecordUser']['id']],['target'=>'_blank']))."</dd>";
        */  
        echo '</dl>';
        echo '<dl>';  
          echo '<dt>Entrega a Domilio</dt>';
          echo '<dd>'.h($salesOrder['SalesOrder']['bool_delivery']?__("Yes"):__("No")).'</dd>';
          if ($salesOrder['SalesOrder']['bool_delivery']){
            echo '<dt>Dirección de entrega</dt>';
            echo '<dd>'.h($salesOrder['SalesOrder']['delivery_address']).'</dd>';
            if (empty($salesOrder['Delivery'])){
              echo $this->Html->link('Crear Orden de Entrega', ['controller'=>'deliveries','action' => 'crear', $salesOrder['SalesOrder']['id']],['class' => 'btn btn-primary']);
            }
            else {
              echo '<dt>Orden de Entrega a Domicilio</dt>';
              echo '<dd>'.$this->Html->Link($salesOrder['Delivery'][0]['delivery_code'],['controller'=>'deliveries','action'=>'detalle',$salesOrder['Delivery'][0]['id']]).'</dd>';
              echo '<dt>Estado de Entrega a Domicilio</dt>';
              echo '<dd>'.h($salesOrder['Delivery'][0]['DeliveryStatus']['code']).'</dd>';
            }
          }
        echo '</dl>'; 
          
			echo "</div>";
      echo '<div class="col-sm-6">';
        echo "<dl>";
          echo "<dt>".__('Price Subtotal')."</dt>";
          echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_subtotal'],2,".",",")."</dd>";
          echo "<dt>".__('Price IVA')."</dt>";
          echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_iva'],2,".",",")."</dd>";
          echo "<dt>".__('Price Total')."</dt>";
          echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_total'],2,".",",")."</dd>";
        echo "</dl>";  
        echo '<br/>';
        echo "<dl>";  
          echo "<dt>Aplica retención</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['bool_retention']?__("Yes"):__("No"))."</dd>";
          if ($salesOrder['SalesOrder']['bool_retention']){
            echo "<dt>Número retención</dt>";
            echo "<dd>".h($salesOrder['SalesOrder']['retention_number'])."</dd>";
            echo "<dt>Monto retención</dt>";
            echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['retention_amount'],2,".",",")."</dd>";
          }
        echo "</dl>";  
        echo '<dl>';
          echo "<dt>Crédito</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['bool_credit']?__("Yes"):__("No"))."</dd>";
          if ($salesOrder['SalesOrder']['bool_credit']){
            echo "<dt>Días de crédito</dt>";
            echo '<dd>'.$salesOrder['SalesOrder']['credit_days'].'</dd>';
            echo "<dt>Autorización de crédito</dt>";
            echo '<dd>'.(empty($salesOrder['CreditAuthorizationUser']['id'])?'Autorizado por configuración de cliente':$this->Html->link($salesOrder['CreditAuthorizationUser']['first_name'].' '.$salesOrder['CreditAuthorizationUser']['last_name'], ['controller' => 'users', 'action' => 'view', $salesOrder['CreditAuthorizationUser']['id']],['target'=>'_blank'])).'</dd>';
          }
          echo "<dt>".__('Bool Annulled')."</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['bool_annulled']?__("Yes"):__("No"))."</dd>";
          echo "<dt>Factura</dt>";
          echo "<dd>".($salesOrder['SalesOrder']['bool_invoice']?(__("Yes")." (".($this->Html->link($salesOrder['Invoice']['invoice_code'],['controller'=>'orders','action'=>'verVenta',$salesOrder['Invoice']['order_id']])).")"):__("No"))."</dd>";
          if (!empty($salesOrder['Quotation'])){
            echo "<dt>".__('Quotation')."</dt>";
            echo "<dd>".(empty($salesOrder['Quotation']['quotation_code'])?"-":$this->Html->link($salesOrder['Quotation']['quotation_code'], ['controller' => 'quotations', 'action' => 'detalle', $salesOrder['Quotation']['id']]))."</dd>";
          }
            
          echo "<dt>".__('IVA?')."</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['bool_iva']?__("Yes"):__("No"))."</dd>";

          echo "<dt>Autorizada?</dt>";
          echo "<dd>".h($salesOrder['SalesOrder']['bool_authorized']?__("Yes"):__("No"))."</dd>";
          if ($salesOrder['SalesOrder']['bool_authorized']){
            echo "<dt>".__('Persona quien autoriza')."</dt>";
            echo "<dd>".(empty($salesOrder['AuthorizationUser']['id'])?'-':$salesOrder['AuthorizingUser']['first_name']." ".$salesOrder['AuthorizingUser']['last_name'])."</dd>";
          }
          echo "<dt>".__('Observation')."</dt>";
          if (!empty($salesOrder['SalesOrder']['observation'])){
            echo "<dd>".$salesOrder['SalesOrder']['observation']."</dd>";
          }
          else {
            echo "<dd>-</dd>";
          }
        echo "</dl>";
        //echo "<dl>";
        //  echo "<dt>".__('Due Date')."</dt>";
				//	echo "<dd>".$dueDateTime->format('d-m-Y')."</dd>";
				//	echo "<dt>".__('Tiempo de Entrega')."</dt>";
				//	echo "<dd>".(empty($salesOrder['SalesOrder']['delivery_time'])?'-':$salesOrder['SalesOrder']['delivery_time'])."</dd>";
        //echo "</dl>";  
      echo '</div>';
    echo '</div>';
  echo '</div>';
?>
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'detallePdf','ext'=>'pdf', $salesOrder['SalesOrder']['id'],$fileName],['target'=>'_blank'])."</li>";
    echo "<br/>";
		if ($bool_edit_permission && !$salesOrder['SalesOrder']['bool_invoice']){
			echo "<li>".$this->Html->link(__('Edit Sales Order'), ['action' => 'editarOrdenVentaExterna', $salesOrder['SalesOrder']['id']])."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Sales Orders'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order'), ['action' => 'crearOrdenVentaExterna'])."</li>";
		echo "<br/>";
    if ($bool_crearVenta_permission && !$salesOrder['SalesOrder']['bool_invoice']){
			echo "<li>".$this->Html->link(__('Crear Factura'), ['controller' => 'orders', 'action' => 'crearVenta',$salesOrder['SalesOrder']['id']])."</li>";
      echo "<br/>";
		}
		if ($bool_quotation_index_permission){
			echo "<li>".$this->Html->link(__('List Quotations'), ['controller' => 'quotations', 'action' => 'resumen'])."</li>";
		}
		if ($bool_quotation_index_permission){
			echo "<li>".$this->Html->link(__('New Quotation'), ['controller' => 'quotations', 'action' => 'crear'])."</li>";
		}
	echo "</ul>";
?>
</div>
<div class='related'>
<?php
	if (!empty($salesOrder['SalesOrderProduct'])){
		echo "<h3>".__('Productos de esta Orden de Venta')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product Id')."</th>";
					echo "<th>".__('Preforma')."</th>";
					echo "<th class='centered'>".__('Product Quantity')."</th>";
					echo "<th class='centered'>".__('Product Unit Price')."</th>";
					echo "<th class='centered'>".__('Product Total Price')."</th>";
					//echo "<th>".__('IVA?')."</th>";
					//echo"<th class='actions'>".__('Actions')."</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$totalProductQuantity=0;
			foreach ($salesOrder['SalesOrderProduct'] as $salesOrderProduct){
				//pr($salesOrderProduct);
        $totalProductQuantity+=$salesOrderProduct['product_quantity'];
				if ($salesOrderProduct['currency_id']==CURRENCY_CS){
					$classCurrency=" class='CScurrency'";
				}
				elseif ($salesOrderProduct['currency_id']==CURRENCY_USD){
					$classCurrency=" class='USDcurrency'";
				}
				echo "<tr>";
					echo "<td>".$this->Html->link($salesOrderProduct['Product']['name'],['controller'=>'products','action'=>'view',$salesOrderProduct['Product']['id']],['target'=>'_blank'])."</td>";
					echo "<td>".(empty($salesOrderProduct['RawMaterial'])?"-":$salesOrderProduct['RawMaterial']['name'])."</td>";
					echo "<td class='centered'>".$salesOrderProduct['product_quantity']."</td>";
					echo "<td".$classCurrency."><span class='currency'></span><span class='amountrightfour'>".$salesOrderProduct['product_unit_price']."</span></td>";
					echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".$salesOrderProduct['product_total_price']."</span></td>";
					//echo "<td class='centered'>".($salesOrderProduct['bool_iva']?__('Yes'):__('No'))."</td>";
				echo "</tr>";
			}
				echo "<tr class='totalrow'>";
					echo "<td>Subtotal</td>";
					echo "<td></td>";
					echo "<td class='centered'>".$totalProductQuantity."</td>";
					echo "<td></td>";
					echo "<td".$classCurrency."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amountright'>".$salesOrder['SalesOrder']['price_subtotal']."</span></td>";
					//echo "<td></td>";
					//echo "<td></td>";
				echo "</tr>";
				echo "<tr class='totalrow'>";
					echo "<td>IVA</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td".$classCurrency."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amountright'>".$salesOrder['SalesOrder']['price_iva']."</span></td>";
					//echo "<td></td>";
					//echo "<td></td>";
				echo "</tr>";
				echo "<tr class='totalrow'>";
					echo "<td>Total</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td".$classCurrency."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amountright'>".$salesOrder['SalesOrder']['price_total']."</span></td>";
					//echo "<td></td>";
					//echo "<td></td>";
				echo "</tr>";
			echo "</tbody>";
		echo "</table>";
	}
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php

  if ($bool_delete_permission && !$salesOrder['SalesOrder']['bool_invoice']){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Orden de Venta'), ['action' => 'delete', $salesOrder['SalesOrder']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la orden de venta # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $salesOrder['SalesOrder']['sales_order_code']));
  }
?>
</div>