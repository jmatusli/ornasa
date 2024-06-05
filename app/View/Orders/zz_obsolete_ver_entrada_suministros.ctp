<?php 
	echo "<div class='orders view'>";
	$outputheader="";
	$outputheader.="<h2>Entrada de Suministros ".$order['Order']['order_code']."</h2>";
	$outputheader.="<dl>";
		$orderDateTime=new DateTime($order['Order']['order_date']);
		$outputheader.="<dt>".__('Purchase Date')."</dt>";
		$outputheader.="<dd>".$orderDateTime->format('d-m-Y')."</dd>";
		$outputheader.="<dt># Entrada</dt>";
		$outputheader.="<dd>".$order['Order']['order_code']."</dd>";
    $outputheader.='<dt># Orden de Compra</dt>';
		$outputheader.='<dd>'.(empty($order['PurchaseOrder']['id'])?"-":($this->Html->Link($order['PurchaseOrder']['purchase_order_code'],['controller'=>'purchaseOrders','action'=>'ver',$order['PurchaseOrder']['id']],['target'=>'_blank']))).'</dd>';
    $outputheader.="<dt>".__('Warehouse')."</dt>";
    $outputheader.="<dd>".$this->Html->link($order['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'view', $order['Warehouse']['id']],['target'=>'_blank'])."</dd>";
		$outputheader.="<dt>".__('Provider')."</dt>";
		$outputheader.="<dd>".$this->Html->link($order['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verProveedor', $order['ThirdParty']['id']))."</dd>";
		$outputheader.="<dt>".__('Subtotal')."</dt>";
		$outputheader.="<dd>C$ ".number_format($order['Order']['total_price'],2,".",",")."</dd>";
    $outputheader.="<dt>".__('IVA')."</dt>";
		$outputheader.="<dd>C$ ".number_format($order['Order']['entry_cost_iva'],2,".",",")."</dd>";
    $outputheader.="<dt>".__('Total')."</dt>";
		$outputheader.="<dd>C$ ".number_format($order['Order']['entry_cost_total'],2,".",",")."</dd>";
    
    $outputheader.="<dt>".__('Cancelada?')."</dt>";
		$outputheader.="<dd>".($order['Order']['bool_entry_paid']?'Si':'No')."</dd>";
    if ($order['Order']['bool_entry_paid']){
      $paymentDateTime=new DateTime($order['Order']['payment_date']);
      $outputheader.="<dt>".__('Fecha de pago')."</dt>";
      $outputheader.="<dd>".$paymentDateTime->format('d-m-Y')."</dd>";
      $outputheader.="<dt>".__('Pagado con cheque')."</dt>";
      $outputheader.="<dd> ".$this->Html->Link($order['Order']['entry_cheque_number'],['action'=>'verPagoEntradas',$order['Order']['entry_cheque_number']])."</dd>";
    }
	$outputheader.="</dl>";
	echo $outputheader;
	echo "</div>";
	
?>
	<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		$companyName=str_replace(".","",$order['ThirdParty']['company_name']);
		$companyName=str_replace(" ","",$companyName);
    $warehouseName=$order['Warehouse']['name'];
		$namepdf=$warehouseName."_Compra_Suministros_".$companyName."_".$order['Order']['order_code'];
		echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'verPdfEntradaSuministros','ext'=>'pdf', $order['Order']['id'],$namepdf])."</li>";
		echo "<br/>";
		if ($bool_edit_permission) { 
			echo "<li>".$this->Html->link(__('Edit Purchase'), ['action' => 'editarEntradaSuministros', $order['Order']['id']])."</li>";
			echo "<br/>";
		}
		
		echo "<li>".$this->Html->link(__('Resumen Entradas Suministros'), ['action' => 'resumenEntradasSuministros'])."</li>";
		if ($bool_add_permission) { 
			echo "<li>".$this->Html->link('Nueva Entrada Suministros', ['action' => 'crearEntradaSuministros'])."</li>";
		}
		echo "<br/>";
		if ($bool_provider_index_permission){
			echo "<li>".$this->Html->link(__('List Providers'), ['controller' => 'third_parties', 'action' => 'resumenProveedores'])."</li>";
		}
		if ($bool_provider_add_permission) { 
			echo "<li>".$this->Html->link(__('New Provider'), ['controller' => 'third_parties', 'action' => 'crearProveedor'])."</li>";
		} 
	echo "</ul>";
?>
</div>
<?php
	if (!empty($order['PurchaseOrderInvoice'])){
    
    $purchaseOrderInvoiceTableHead='';
      $purchaseOrderInvoiceTableHead.='<thead>';
        $purchaseOrderInvoiceTableHead.='<tr>';
          $purchaseOrderInvoiceTableHead.='<th># Factura</th>';
          $purchaseOrderInvoiceTableHead.='<th style="min-width:280px;">Fecha Factura</th>';
          $purchaseOrderInvoiceTableHead.='<th style="min-width:40px;">Iva?</th>';
          $purchaseOrderInvoiceTableHead.='<th>Subtotal</th>';
          $purchaseOrderInvoiceTableHead.='<th>IVA</th>';
          $purchaseOrderInvoiceTableHead.='<th>Total</th>';
        $purchaseOrderInvoiceTableHead.='</tr>';
      $purchaseOrderInvoiceTableHead.='</thead>';
      
      $purchaseOrderInvoiceTableRows='';
      $purchaseOrderInvoiceTableBody='<tbody>';
      foreach ($order['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
        $purchaseOrderInvoiceDateTime = new DateTime($purchaseOrderInvoice['invoice_date']);  
        $purchaseOrderInvoiceTableRow='';
        $purchaseOrderInvoiceTableRow.='<tr>';
          $purchaseOrderInvoiceTableRow.='<td>'.$purchaseOrderInvoice['invoice_code'].'</td>';
          $purchaseOrderInvoiceTableRow.='<td>'.$purchaseOrderInvoiceDateTime->format('d-m-Y').'</td>';
          $purchaseOrderInvoiceTableRow.='<td>'.($purchaseOrderInvoice['invoice_code']?'Si':'No').'</td>';
          $purchaseOrderInvoiceTableRow.='<td>'.$purchaseOrderInvoice['invoice_subtotal'].'</td>';
          $purchaseOrderInvoiceTableRow.='<td>'.$purchaseOrderInvoice['invoice_iva'].'</td>';
          $purchaseOrderInvoiceTableRow.='<td>'.$purchaseOrderInvoice['invoice_total'].'</td>';
        $purchaseOrderInvoiceTableRow.='</tr>';
        
        $purchaseOrderInvoiceTableRows.=$purchaseOrderInvoiceTableRow;
      }
      
      $purchaseOrderInvoiceTableTotalRow='';            
        $purchaseOrderInvoiceTableTotalRow.='<tr class="totalrow">';
        $purchaseOrderInvoiceTableTotalRow.='<td>Totales</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td></td>';
        $purchaseOrderInvoiceTableTotalRow.='<td></td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$order['Order']['total_price'].'</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$order['Order']['entry_cost_iva'].'</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$order['Order']['entry_cost_total'].'</td>';
      $purchaseOrderInvoiceTableTotalRow.='</tr>';
    
      
      $purchaseOrderInvoiceTableBody='<tbody>'.$purchaseOrderInvoiceTableRows.$purchaseOrderInvoiceTableTotalRow.'</tbody>';
      echo '<h2>Facturas</h2>';
      echo '<table id="invoicesForEntry">'.$purchaseOrderInvoiceTableHead.$purchaseOrderInvoiceTableBody.'</table>';
      echo '<button class="addInvoice">Añadir Factura</button>';
  }  
  
  
  
  
  $outputbody="";
	$outputbody.="<div class='related'>";
	$outputbody.="<h3>".__('Lote de Inventario para esta Compra')."</h3>";
	if (!empty($order['StockMovement'])):
		$outputbody.="<table cellpadding = '0' cellspacing = '0'>";		
			$outputrow="<tr>";
				$outputrow.="<th>".__('Purchase Date')."</th>";
				$outputrow.="<th>".__('Product')."</th>";
				$outputrow.="<th>".__('Lot Identifier')."</th>";
				$outputrow.="<th class='centered'>".__('Quantity')."</th>";
				$outputrow.="<th class='centered'>".__('Total Price')."</th>";
			$outputrow.="</tr>";
			$outputbody.=$outputrow;
			echo $outputbody;
			$totalprice=0;
			foreach ($order['StockMovement'] as $stockentry):
				//pr($stockentry);
				if ($stockentry['product_quantity']>0){
					$totalprice+=$stockentry['product_total_price'];
					$outputrow="<tr>";
					$stockmovementdate=new DateTime($stockentry['movement_date']);
					$outputrow.="<td>".$stockmovementdate->format('d-m-Y')."</td>";
					$outputrow.="<td>".$stockentry['Product']['name']."</td>";
					$outputrow.="<td>".$stockentry['name']."</td>";
					$outputrow.="<td class='centered'>".number_format($stockentry['product_quantity'],0,".",",")."</td>";
					$outputrow.="<td class='centered'>C$ ".number_format($stockentry['product_total_price'],2,".",",")."</td>";
					$outputrow.="</tr>";
					echo $outputrow;
					$outputbody.=$outputrow;
				}
			endforeach;
			echo "<tr class='totalrow'>";
				echo "<td>Total</td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td class='centered'>C$ ".number_format($totalprice,2,".",",")."</td>";
			echo "</tr>";
		echo "</table>";
		$outputbody.="<tr class='totalrow'>";
			$outputbody.="<td>Total</td>";
			$outputbody.="<td></td>";
			$outputbody.="<td></td>";
			$outputbody.="<td></td>";
			$outputbody.="<td class='centered'>C$ ".number_format($totalprice,2,".",",")."</td>";
		$outputbody.="</tr>";
		$outputbody.="</table>";
		
	endif;
	
	$output=$outputheader.$outputbody;
	//echo "this is the output for the pdf";
	//echo $output;
	$_SESSION['output_compra']=$output;
?>
	<!--div class="actions">
		<ul>
			<li>".$this->Html->link(__('Add Product to Purchase'), array('controller' => 'stock_movements', 'action' => 'addStockentry',$order['Order']['id']))." </li>
		</ul>
	</div-->
</div>
<div>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">

<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.__('Eliminar Entrada'), ['action' => 'eliminarEntrada', $order['Order']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la entrada de suministros # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $order['Order']['order_code']));
  }
?>
</div>


