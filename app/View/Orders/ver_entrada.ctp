<div class="orders view fullwidth">
<?php 
	$orderDateTime=new DateTime($order['Order']['order_date']);
  
	echo '<h1>'.__('Purchase').' '.$order['Order']['order_code'].'</h1>';
  
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-8">';
      $outputHeader="";
      $outputHeader.="<dl>";
        $outputHeader.="<dt>".__('Purchase Date')."</dt>";
        $outputHeader.="<dd>".$orderDateTime->format('d-m-Y')."</dd>";
        $outputHeader.="<dt># Entrada</dt>";
        $outputHeader.="<dd>".$order['Order']['order_code']."</dd>";
        $outputHeader.='<dt># Orden de Compra</dt>';
        $outputHeader.='<dd>'.(empty($order['PurchaseOrder']['id'])?"-":($this->Html->Link($order['PurchaseOrder']['purchase_order_code'],['controller'=>'purchaseOrders','action'=>'ver',$order['PurchaseOrder']['id']],['target'=>'_blank']))).'</dd>';
        $outputHeader.="<dt>".__('Warehouse')."</dt>";
        $outputHeader.="<dd>".$this->Html->link($order['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'view', $order['Warehouse']['id']],['target'=>'_blank'])."</dd>";
        $outputHeader.="<dt>".__('Provider')."</dt>";
        $outputHeader.="<dd>".$this->Html->link($order['ThirdParty']['company_name'], array('controller' => 'third_parties', 'action' => 'verProveedor', $order['ThirdParty']['id']))."</dd>";
        $outputHeader.="<dt>".__('Subtotal')."</dt>";
        $outputHeader.="<dd>C$ ".number_format($order['Order']['total_price'],2,".",",")."</dd>";
        $outputHeader.="<dt>".__('IVA')."</dt>";
        $outputHeader.="<dd>C$ ".number_format($order['Order']['entry_cost_iva'],2,".",",")."</dd>";
        $outputHeader.="<dt>".__('Total')."</dt>";
        $outputHeader.="<dd>C$ ".number_format($order['Order']['entry_cost_total'],2,".",",")."</dd>";
        $outputHeader.="<dt>".__('Cancelada?')."</dt>";
        $outputHeader.="<dd>".($order['Order']['bool_entry_paid']?'Si':'No')."</dd>";
        if ($order['Order']['bool_entry_paid']){
          $paymentDateTime=new DateTime($order['Order']['payment_date']);
          $outputHeader.="<dt>".__('Fecha de pago')."</dt>";
          $outputHeader.="<dd>".$paymentDateTime->format('d-m-Y')."</dd>";
          $outputHeader.="<dt>".__('Pagado con cheque')."</dt>";
          $outputHeader.="<dd> ".$this->Html->Link($order['Order']['entry_cheque_number'],['action'=>'verPagoEntradas',$order['Order']['entry_cheque_number']])."</dd>";
        }
      $outputHeader.="</dl>";
      echo $outputHeader;
      echo "</div>";
      echo '<div class="col-sm-4">';
        echo "<h2>".__('Actions')."</h2>";
        echo '<ul style="list-style:none;">';
          $companyName=str_replace(".","",$order['ThirdParty']['company_name']);
          $companyName=str_replace(" ","",$companyName);
          //$warehouseName=$order['Warehouse']['name'];
          $namepdf="Compra_".$companyName."_".$order['Order']['order_code'];
          //$namepdf=$warehouseName."_Compra_".$companyName."_".$order['Order']['order_code'];
          echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'verPdfEntrada','ext'=>'pdf', $order['Order']['id'],$namepdf])."</li>";
          echo '<br/>';
          if ($bool_edit_permission){ 
            echo "<li>".$this->Html->link(__('Edit Purchase'), ['action' => 'editarEntrada', $order['Order']['id']])."</li>";
            echo '<br/>';
          } 
          echo "<li>".$this->Html->link(__('List Purchases'), ['action' => 'resumenEntradas'])."</li>";
          if ($bool_add_permission) { 
            echo "<li>".$this->Html->link(__('New Purchase'), ['action' => 'crearEntrada'])."</li>";
          }
          echo "<br/>";
          if ($bool_provider_index_permission){
            echo "<li>".$this->Html->link(__('List Providers'), ['controller' => 'third_parties', 'action' => 'resumenProveedores'])."</li>";
          }
          if ($bool_provider_add_permission) { 
            echo "<li>".$this->Html->link(__('New Provider'), ['controller' => 'third_parties', 'action' => 'crearProveedor'])."</li>";
          } 
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";

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
          $purchaseOrderInvoiceTableRow.='<td>'.($purchaseOrderInvoice['bool_iva']?'Si':'No').'</td>';
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
      echo '<div class="row">';
      echo '<h2>Facturas</h2>';
      echo '<table id="invoicesForEntry">'.$purchaseOrderInvoiceTableHead.$purchaseOrderInvoiceTableBody.'</table>';
      echo '<button class="addInvoice">Añadir Factura</button>';
      echo '</div>';
  }  
?>
</div>
<div class="related">
<?php  
	if (!empty($order['StockMovement'])){
  	$totalprice=0;
    
    $stockItemTableHeader='';    
    $stockItemTableHeader.='<thead>';
			$stockItemTableHeader="<tr>";
				$stockItemTableHeader.="<th>".__('Purchase Date')."</th>";
				$stockItemTableHeader.="<th>".__('Product')."</th>";
				$stockItemTableHeader.="<th>".__('Lot Identifier')."</th>";
				$stockItemTableHeader.="<th class='centered'>".__('Quantity')."</th>";
				$stockItemTableHeader.="<th class='centered'>".__('Total Price')."</th>";
			$stockItemTableHeader.="</tr>";
    $stockItemTableHeader.='</thead>';  
    
    $stockItemTableRows='';
    foreach ($order['StockMovement'] as $stockEntry){
      if ($stockEntry['product_quantity']>0){
        $stockMovementDateTime=new DateTime($stockEntry['movement_date']);
        
        $totalprice+=$stockEntry['product_total_price'];
        $stockItemTableRows.="<tr>";
          $stockItemTableRows.="<td>".$stockMovementDateTime->format('d-m-Y')."</td>";
          $stockItemTableRows.="<td>".$stockEntry['Product']['name']."</td>";
          $stockItemTableRows.="<td>".$stockEntry['name']."</td>";
          $stockItemTableRows.="<td class='centered'>".number_format($stockEntry['product_quantity'],0,".",",")."</td>";
          $stockItemTableRows.="<td class='centered'>C$ ".number_format($stockEntry['product_total_price'],2,".",",")."</td>";
        $stockItemTableRows.="</tr>";
      }
    }
    $stockItemTableTotalRow='';
   $stockItemTableTotalRow.="<tr class='totalrow'>";
      $stockItemTableTotalRow.="<td>Total</td>";
      $stockItemTableTotalRow.="<td></td>";
      $stockItemTableTotalRow.="<td></td>";
      $stockItemTableTotalRow.="<td></td>";
      $stockItemTableTotalRow.="<td class='centered'>C$ ".number_format($totalprice,2,".",",")."</td>";
    $stockItemTableTotalRow.="</tr>";
    $stockItemTableBody='<tbody>'.$stockItemTableTotalRow.$stockItemTableRows.$stockItemTableTotalRow.'</tbody>';
    
    $stockItemTable='<table id="lotes">'.$stockItemTableHeader.$stockItemTableBody.'</table>';
    
    echo '<h2>Lote de Inventario para esta Compra</h2>';
		echo 	$stockItemTable;
		
	}
	$_SESSION['output_compra']=$stockItemTable;
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($deletabilityData['boolDeletable'] && $bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Entrada'), ['action' => 'eliminarEntrada', $order['Order']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la entrada # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $order['Order']['order_code']));
  }
  else {
    $warning=$deletabilityData['message'];
    
    if (!empty($deletabilityData['productionRuns'])){
      $warning.="  Los productos ya se ocuparon en procesos de producción ";
      
      $productionRunCounter=0;
      foreach ($deletabilityData['productionRuns'] as $productionRunId=>$productionRunData){
        $productionRunCounter++;
        $productionRunDateTime=new DateTime($productionRunData['production_run_date']);
        $warning.=$this->Html->Link(($productionRunData['production_run_code'].' ('.$productionRunDateTime->format('d-m-Y').')'),['controller'=>'productionRuns','action'=>'detalle',$productionRunId]);
        $warning.=($productionRunCounter < count($deletabilityData['productionRuns'])?', ':'.  ');
      }
    }
    if (!empty($deletabilityData['orders'])){
      $warning.="  Los productos ya se ocuparon en ventas ";
      $orderCounter=0;
      foreach ($deletabilityData['orders'] as $orderId=>$orderData){
        $orderCounter++;
        $orderDateTime=new DateTime($orderData['order_date']);
        $warning.=$this->Html->Link(($orderData['order_code'].' ('.$orderDateTime->format('d-m-Y').')'),['controller'=>'orders','action'=>'verVenta',$orderId]);
        $warning.=($orderCounter < count($deletabilityData['orders'])?', ':'.  ');
      }
    }
    if (!empty($deletabilityData['transfers'])){
      $warning.="  Los productos ya se ocuparon en transferencias ";
      $transferCounter=0;
      foreach ($deletabilityData['transfers'] as $transferData){
        $transferCounter++;
        $transferDateTime=new DateTime($transferData['transfer_date']);
        $warning.=$transferData['transfer_code'].' ('.$transferDateTime->format('d-m-Y').')';
        $warning.=($transferCounter < count($deletabilityData['transfers'])?', ':'.  ');
      }
    }

    echo '<p class="warning">'.$warning.'</p>';
  }
?>
</div>



