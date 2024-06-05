<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
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
<div class="zones view fullwidth">
<?php 
	echo '<h1>'.__('Zone').' '.$zone['Zone']['name'].'</h1>';
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-md-5">';
        echo '<dl>';
          echo '<dt>'.__('Code').'</dt>';
          echo '<dd>'.(empty($zone['Zone']['code'])?'-':$zone['Zone']['code']).'</dd>';
          echo '<dt>'.__('Name').'</dt>';
          echo '<dd>'.h($zone['Zone']['name']).'</dd>';
          echo '<dt>'.__('Description').'</dt>';
          echo '<dd>'.(empty($zone['Zone']['description'])?'-':$zone['Zone']['description']).'</dd>';
          echo '<dt>'.__('List Order').'</dt>';
          echo '<dd>'.h($zone['Zone']['list_order']).'</dd>';
        echo '</dl>';
      echo '</div>';  
      echo '<div class="col-md-5">';
        echo $this->Form->create('Report'); 
        echo "<fieldset>";
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
        echo "</fieldset>";
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo $this->Form->end(__('Refresh')); 
      echo '</div>';  
      echo '<div class="col-md-2">';  
        echo '<h2>'.__('Actions').'</h2>';
        echo '<ul style="list-style-type:none;">';	
          if ($bool_edit_permission){
            echo '<li>'.$this->Html->link(__('Edit Zone'), ['action' => 'editar', $zone['Zone']['id']]).'</li>';
            echo '<br/>';
          }
          echo '<li>'.$this->Html->link(__('List Zones'), ['action' => 'resumen']).'</li>';
          echo '<li>'.$this->Html->link(__('New Zone'), ['action' => 'crear']).'</li>';
          echo '<br/>';
        echo '</ul>';
      echo '</div>';  
    echo '</div>';  
  echo '</div>';   
?> 
</div>
<div class="related">
<?php 
  if (empty($zone['ThirdParty'])){
    echo '<h2>No hay clientes para esta zona</h2>';
  }
  else {
    $tableHeader='';
    $tableHeader.='<thead>';
      $tableHeader.='<tr>';
        $tableHeader.='<th>Cliente</th>';
        $tableHeader.='<th>Teléfono</th>';
        $tableHeader.='<th>Correo</th>';
        $tableHeader.='<th>RUC</th>';
        $tableHeader.='<th>Dirección</th>';
      $tableHeader.='</tr>';
    $tableHeader.='</thead>';
    
    $tableRows='';
    foreach ($zone['ThirdParty'] as $client){
      $tableRows.='<tr>';
        $tableRows.='<td>'.$client['company_name'].'</td>';
        $tableRows.='<td>'.$client['phone'].'</td>';
        $tableRows.='<td>'.$client['email'].'</td>';
        $tableRows.='<td>'.$client['ruc_number'].'</th>';
        $tableRows.='<td>'.$client['address'].'</td>';
      $tableRows.='</tr>';
    }
    
    $tableBody='<tbody>'.$tableRows.'</tbody>';
    echo '<h2>Clientes de esta Zona</h2>';
    echo '<table>'.$tableHeader.$tableBody.'</table>';
  }
?>
</div>

<div class="related">
<?php 
  if (empty($zone['Order'])){
    echo '<h2>No hay ventas para esta zona en el período seleccionado</h2>';
  }
  else {
    $tableHeader='';
    $tableHeader.='<thead>';
      $tableHeader.='<tr>';
        $tableHeader.='<th>Vendedor</th>';
        $tableHeader.='<th>Fecha</th>';
        $tableHeader.='<th>Código</th>';
        $tableHeader.='<th>Cliente</th>';
        $tableHeader.='<th>Subtotal</th>';
      $tableHeader.='</tr>';
    $tableHeader.='</thead>';
    
    $totalPriceUSD=0;
    $tableRows='';
    foreach ($zone['Order'] as $order){
      $totalPriceUSD+=$order['price_subtotal_usd'];
      $orderDateTime=new DateTime($order['order_date']);
      
      $tableRows.='<tr>';
        $tableRows.='<td>'.$order['User']['first_name'].' '.$order['User']['last_name'].'</td>';
        $tableRows.='<td>'.($orderDateTime->format('d-m-Y')).'</td>';
        $tableRows.='<td>'.$this->Html->link($order['order_code'],['controller'=>'orders','action'=>'verVenta',$order['id']]).'</td>';
        $tableRows.='<td>'.(empty($order['Client'])?(empty($order['client_name'])?'-':$order['client_name']):($this->Html->Link($order['Client']['company_name'],['controller'=>'thirdParties','action'=>'verCliente',$order['Client']['id']]))).'</th>';
        $tableRows.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$order['price_subtotal_usd'].'</span></td>';
      $tableRows.='</tr>';
    }
    
    $totalRow='';
    $totalRow.='<tr>';
      $totalRow.='<td>Total US$</td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </th>';
      $totalRow.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$totalPriceUSD.'</span></td>';
    $totalRow.='</tr>';
    
    $tableBody='<tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
    echo '<h2>Ventas para esta zona para el período seleccionado</h2>';
    echo '<table>'.$tableHeader.$tableBody.'</table>';
  }
?>
</div>


<div class="related">
<?php 
  if (empty($zone['SalesOrder'])){
    echo '<h2>No hay ordenes de venta para esta zona en el período seleccionado</h2>';
  }
  else {
    $tableHeader='';
    $tableHeader.='<thead>';
      $tableHeader.='<tr>';
        $tableHeader.='<th>Vendedor</th>';
        $tableHeader.='<th>Fecha</th>';
        $tableHeader.='<th>Código</th>';
        $tableHeader.='<th>Cliente</th>';
        $tableHeader.='<th>Subtotal</th>';
      $tableHeader.='</tr>';
    $tableHeader.='</thead>';
    
    $totalPriceUSD=0;
    $tableRows='';
    foreach ($zone['SalesOrder'] as $salesOrder){
      $totalPriceUSD+=$salesOrder['price_subtotal_usd'];
      $salesOrderDateTime=new DateTime($salesOrder['sales_order_date']);
      
      $tableRows.='<tr>';
        $tableRows.='<td>'.$salesOrder['User']['first_name'].' '.$salesOrder['User']['last_name'].'</td>';
        $tableRows.='<td>'.($salesOrderDateTime->format('d-m-Y')).'</td>';
        $tableRows.='<td>'.$this->Html->link($salesOrder['sales_order_code'],['controller'=>'salesOrders','action'=>'detalle',$salesOrder['id']]).'</td>';
        $tableRows.='<td>'.(empty($salesOrder['Client'])?(empty($salesOrder['client_name'])?'-':$salesOrder['client_name']):($this->Html->Link($salesOrder['Client']['company_name'],['controller'=>'thirdParties','action'=>'verCliente',$salesOrder['Client']['id']]))).'</th>';
        $tableRows.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$salesOrder['price_subtotal_usd'].'</span></td>';
      $tableRows.='</tr>';
    }
    
    $totalRow='';
    $totalRow.='<tr>';
      $totalRow.='<td>Total US$</td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </th>';
      $totalRow.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$totalPriceUSD.'</span></td>';
    $totalRow.='</tr>';
    
    $tableBody='<tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
    echo '<h2>Ordenes de venta para esta zona para el período seleccionado</h2>';
    echo '<table>'.$tableHeader.$tableBody.'</table>';
  }
?>
</div>


<div class="related">
<?php 
  if (empty($zone['Quotation'])){
    echo '<h2>No hay cotizaciones para esta zona en el período seleccionado</h2>';
  }
  else {
    $tableHeader='';
    $tableHeader.='<thead>';
      $tableHeader.='<tr>';
        $tableHeader.='<th>Vendedor</th>';
        $tableHeader.='<th>Fecha</th>';
        $tableHeader.='<th>Código</th>';
        $tableHeader.='<th>Cliente</th>';
        $tableHeader.='<th>Subtotal</th>';
      $tableHeader.='</tr>';
    $tableHeader.='</thead>';
    
    $totalPriceUSD=0;
    $tableRows='';
    foreach ($zone['Quotation'] as $quotation){
      $totalPriceUSD+=$quotation['price_subtotal_usd'];
      $quotationDateTime=new DateTime($quotation['quotation_date']);
      
      $tableRows.='<tr>';
        $tableRows.='<td>'.$quotation['User']['first_name'].' '.$quotation['User']['last_name'].'</td>';
        $tableRows.='<td>'.($quotationDateTime->format('d-m-Y')).'</td>';
        $tableRows.='<td>'.$this->Html->link($quotation['quotation_code'],['controller'=>'quotations','action'=>'detalle',$quotation['id']]).'</td>';
        $tableRows.='<td>'.(empty($quotation['Client'])?(empty($quotation['client_name'])?'-':$quotation['client_name']):($this->Html->Link($quotation['Client']['company_name'],['controller'=>'thirdParties','action'=>'verCliente',$quotation['Client']['id']]))).'</th>';
        $tableRows.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$quotation['price_subtotal_usd'].'</span></td>';
      $tableRows.='</tr>';
    }
    
    $totalRow='';
    $totalRow.='<tr>';
      $totalRow.='<td>Total US$</td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </td>';
      $totalRow.='<td> </th>';
      $totalRow.='<td class="USDcurrency"><span class="currency">US$</span><span class="amountright">'.$totalPriceUSD.'</span></td>';
    $totalRow.='</tr>';
    
    $tableBody='<tbody>'.$totalRow.$tableRows.$totalRow.'</tbody>';
    echo '<h2>Cotizaciones para esta zona para el período seleccionado</h2>';
    echo '<table>'.$tableHeader.$tableBody.'</table>';
  }
?>
</div>


<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_delete_permission){
			echo 
      $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Zona'), ['action' => 'delete', $zone['Zone']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la zona # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $zone['Zone']['name']));
	echo '<br/>';
		}
?>
</div>