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

<div class="salesOrders view fullwidth">
<?php 
	echo "<h2>";
		echo __('Sales Order')." ".$salesOrder['SalesOrder']['sales_order_code'];
		if ($salesOrder['SalesOrder']['bool_annulled']){
			echo " (Anulada)";
		}
		else {
			if ($salesOrder['SalesOrder']['bool_completely_delivered']){
				echo " (Entregada)";
			}
			elseif ($salesOrder['SalesOrder']['bool_authorized']){
				echo " (Autorizada)";
			}
		}
		
	echo "</h2>";
	echo "<dl>";
		$salesOrderDateTime=new DateTime($salesOrder['SalesOrder']['sales_order_date']);
		echo "<dt>".__('Sales Order Date')."</dt>";
		echo "<dd>".$salesOrderDateTime->format('d-m-Y')."</dd>";
		echo "<dt>".__('Sales Order Code')."</dt>";
		echo "<dd>".h($salesOrder['SalesOrder']['sales_order_code'])."</dd>";
		
		echo "<dt>".__('Client')."</dt>";
    echo "<dd>".$salesOrder['SalesOrder']['client_name']."</dd>";
    echo "<dt>".__('Phone')."</dt>";
    echo "<dd>".(empty($salesOrder['SalesOrder']['client_phone'])?"-":$salesOrder['SalesOrder']['client_phone'])."</dd>";
    echo "<dt>".__('Email')."</dt>";
    echo "<dd>".(empty($salesOrder['SalesOrder']['client_mail'])?"-":$salesOrder['SalesOrder']['client_mail'])."</dd>";
		echo "<dt>".__('Bool Annulled')."</dt>";
		echo "<dd>".h($salesOrder['SalesOrder']['bool_annulled']?__("Yes"):__("No"))."</dd>";
		echo "<dt>".__('Bool Completely Delivered')."</dt>";
		echo "<dd>".h($salesOrder['SalesOrder']['bool_completely_delivered']?__("Yes"):__("No"))."</dd>";
    if (!empty($salesOrder['Quotation'])){
      echo "<dt>".__('Quotation')."</dt>";
      echo "<dd>".(empty($salesOrder['Quotation']['quotation_code'])?"-":$this->Html->link($salesOrder['Quotation']['quotation_code'], ['controller' => 'quotations', 'action' => 'detalle', $salesOrder['Quotation']['id']]))."</dd>";
    }
      
		echo "<dt>".__('IVA?')."</dt>";
		echo "<dd>".h($salesOrder['SalesOrder']['bool_iva']?__("Yes"):__("No"))."</dd>";
		echo "<dt>".__('Price Subtotal')."</dt>";
		echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_subtotal'],2,".",",")."</dd>";
		echo "<dt>".__('Price IVA')."</dt>";
		echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_iva'],2,".",",")."</dd>";
		echo "<dt>".__('Price Total')."</dt>";
		echo "<dd>".$salesOrder['Currency']['abbreviation']." ".number_format($salesOrder['SalesOrder']['price_total'],2,".",",")."</dd>";
		echo "<dt>".__('Authorizada?')."</dt>";
		echo "<dd>".h($salesOrder['SalesOrder']['bool_authorized']?__("Yes"):__("No"))."</dd>";
		if ($salesOrder['SalesOrder']['bool_authorized']){
			echo "<dt>".__('Persona quien autoriza')."</dt>";
			echo "<dd>".$salesOrder['AuthorizingUser']['first_name']." ".$salesOrder['AuthorizingUser']['last_name']."</dd>";
		}
		echo "<dt>".__('Observation')."</dt>";
		if (!empty($salesOrder['SalesOrder']['observation'])){
			echo "<dd>".$salesOrder['SalesOrder']['observation']."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		
	echo "</dl>";
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
				$totalProductQuantity+=$salesOrderProduct['product_quantity'];
				if ($salesOrderProduct['currency_id']==CURRENCY_CS){
					$classCurrency=" class='CScurrency'";
				}
				elseif ($salesOrderProduct['currency_id']==CURRENCY_USD){
					$classCurrency=" class='USDcurrency'";
				}
				echo "<tr>";
					echo "<td>".$salesOrderProduct['Product']['name']."</td>";
					echo "<td>".(empty($salesOrderProduct['RawMaterial'])?"-":$salesOrderProduct['RawMaterial']['name'])."</td>";
					echo "<td class='centered'>".$salesOrderProduct['product_quantity']."</td>";
					echo "<td".$classCurrency."><span class='currency'></span><span class='amountright'>".$salesOrderProduct['product_unit_price']."</span></td>";
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