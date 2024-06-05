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
		$("td.CSCurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);-
      $(this).parent().find('span.currency').text('C$ ');
		});
		
	}
  function formatUSDCurrencies(){
		$("td.USDCurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
      $(this).parent().find('span.currency').text('US$ ');
		});
		
	}
	
	$(document).ready(function(){
    formatNumbers();
		formatCSCurrencies();
    formatUSDCurrencies();
  });
</script>
<div class="thirdParties index clients fullwidth">
<?php 
  echo "<h2>".__('Clients')."</h2>";
 
  echo "<div class='container_fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-md-5'>";
        echo $this->Form->create('Report',['style'=>'width:100%']); 
        echo "<fieldset>"; 
          echo $this->PlantFilter->displayPlantFilter($plants, $userRoleId,$plantId);              
          if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers || $canSeeAllVendors) { 
            echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Cliente asociado con Usuario'),'options'=>$users,'default'=>$userId,'empty'=>['0'=>'-- Todos Usuarios --']]);
          }
          else {
            echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Cliente asociado con Usuario'),'options'=>$users,'default'=>$userId,'type'=>'hidden']);
          }												
          echo $this->Form->input('Report.active_display_option_id',['label'=>__('Clientes Activos'),'default'=>$activeDisplayOptionId]);
          if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {
            echo $this->Form->input('Report.aggregate_option_id',['label'=>__('Mostrar y Ordenar Por'),'default'=>$aggregateOptionId]);
          }
          else {
            echo $this->Form->input('Report.aggregate_option_id',array('label'=>__('Mostrar y Ordenar Por'),'default'=>AGGREGATES_NONE,'type'=>'hidden'));
          }
          echo $this->Form->input('Report.client_type_id',['default'=>$clientTypeId,'empty'=>[0=>'-- Tipo de Cliente --']]);
          echo $this->Form->input('Report.zone_id',['default'=>$zoneId,'empty'=>[0=>'-- Zona --']]);
          echo $this->Form->input('Report.searchterm',array('label'=>__('Buscar')));
      echo "</div>";
      echo "<div class='col-md-5'>";
        //if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers) {  
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2015,'maxYear'=>date('Y')));
          echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2015,'maxYear'=>date('Y')));
          //echo $this->Form->input('Report.currency_id',array('label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId));
        //}
        echo  "</fieldset>";
        echo "<br/>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
          if ($bool_client_add_permission) {
            echo "<li>".$this->Html->link(__('New Client'), array('action' => 'crearCliente'))."</li>";
            echo "<br/>";
          }
          if ($bool_saleremission_index_permission) {
            echo "<li>".$this->Html->link(__('Todas Ventas y Remisiones'), array('controller' => 'orders', 'action' => 'resumenVentasRemisiones'))."</li>";
          }
          if ($bool_sale_add_permission) {
            echo "<li>".$this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'crearVenta'))."</li>";
          }
          if ($bool_remission_add_permission) {
            echo "<li>".$this->Html->link(__('New Remission'), array('controller' => 'orders', 'action' => 'crarRemision'))."</li>";
          }
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
    
	echo "<br>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenClientes'), array( 'class' => 'btn btn-primary'));  

  $excelOutput="";
  
	$pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
    $pageHeader.="<tr>";
      $pageHeader.='<th>Planta</th>';
      $pageHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('client_type_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $pageHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      $pageHeader.="<th class='centered'>Pago Pendiente</th>";
      $pageHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
        if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
        $pageHeader.="<th># Salidas</th>";
        $pageHeader.="<th>$ Salidas</th>";
			}
      $pageHeader.="<th class='actions'>".__('Actions')."</th>";
    $pageHeader.="</tr>";
  $pageHeader.="</thead>";
  $excelHeader.="<thead>";
    $excelHeader.="<tr>";
      $excelHeader.='<th>Planta</th>';
      $excelHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('client_type_id')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $excelHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      $excelHeader.="<th class='centered'>Pago Pendiente</th>";
      $excelHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
        if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
        $excelHeader.="<th># Salidas</th>";
        $excelHeader.="<th>$ Salidas</th>";
			}
    $excelHeader.="</tr>";
  $excelHeader.="</thead>";
	$pageBody="";
	$excelBody="";

  $totalPaymentPending=0;
  $totalQuantityOrders=0;
  $totalAmountOrders=0;
  
	foreach ($clients as $client){
    //pr($client);
    
    $totalPaymentPending+=$client['ThirdParty']['pending_payment'];
    $totalQuantityOrders+=count($client['Order']);
    $totalAmountOrders+=$client['Client']['order_total'];
  
    $pageRow="";
    $pageRow.="<tr class='".($client['ThirdParty']['bool_active']?"":" italic").($client['ThirdParty']['credit_amount']>=$client['ThirdParty']['pending_payment']?"":" redbg")."'>"; 
      
      $pageRow.="<td>".$this->Html->link($client['Plant']['name'], ['controller'=>'plants','action' => 'detalle', $client['Plant']['id']])."</td>";
      $pageRow.="<td>".$this->Html->link($client['ThirdParty']['company_name'].($client['ThirdParty']['bool_active']?"":" (Inactivo)"), ['action' => 'verCliente', $client['ThirdParty']['id']])."</td>";
      $pageRow.="<td>".(empty($client['ClientType']['id'])?"-":($this->Html->link($client['ClientType']['name'], ['controller'=>'clientTypes','action' => 'detalle', $client['ClientType']['id']])))."</td>";
      if (!empty($client['AccountingCode']['code'])){
        $pageRow.="<td>".$this->Html->link($client['AccountingCode']['code']." ".$client['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$client['AccountingCode']['id']))."</td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if (!empty($client['ThirdParty']['credit_days'])){
        $pageRow.="<td class='centered'>".$client['ThirdParty']['credit_days']."</td>";
      }
      else {
        $pageRow.="<td class='centered'>0</td>";
      }
      if (!empty($client['ThirdParty']['credit_amount'])){
        $pageRow.="<td class='centered ".($client['ThirdParty']['credit_currency_id']==CURRENCY_USD?'USDCurrency':'CSCurrency')."' ><span class='currency'></span><span class='amountright'>".$client['ThirdParty']['credit_amount']."</span></td>";
      }
      else {
        $pageRow.="<td class='centered'>-</td>";
      }
      $pageRow.="<td class='centered CSCurrency'><span class='currency'></span><span class='amountright'>".$client['ThirdParty']['pending_payment']."</span></td>";
      
      $pageRow.="<td>".$client['ThirdParty']['first_name']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['last_name']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['email']."</td>";
      $pageRow.="<td>".$client['ThirdParty']['phone']."</td>";
      if (!empty($client['ThirdParty']['address'])){
        $pageRow.="<td >".$client['ThirdParty']['address']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      if (!empty($client['ThirdParty']['ruc_number'])){
        $pageRow.="<td >".$client['ThirdParty']['ruc_number']."</span></td>";
      }
      else {
        $pageRow.="<td>-</td>";
      }
      //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
      if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {  
        $pageRow.="<td>".count($client['Order'])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$client['Client']['order_total']."</span></td>";
			}
      $excelBody.=($client['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
			
      $pageRow.="<td class='actions'>";
        if ($bool_client_edit_permission){ 
          $pageRow.=$this->Html->link(__('Editar Cliente'), ['action' => 'editarCliente', $client['ThirdParty']['id']]); 
        } 
        if ($bool_delete_permission){ 
          //$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'deleteClient', $client['ThirdParty']['id']), array(), __('Está seguro que quiere eliminar el cliente # %s?', $client['ThirdParty']['company_name'])); 
        }
      $pageRow.="</td>";
    $pageBody.=($client['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
  }
  
  $totalRow="<tr class='totalrow'>";
    $totalRow.="<td>Total</td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalPaymentPending."</span></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
        if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
      $totalRow.="<td>".$totalQuantityOrders."</td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalAmountOrders."</span></td>";
    }
    $excelBody="<tbody>".$totalRow."</tr>".$excelBody.$totalRow."</tr>"."</tbody>";
    
    $totalRow.="<td></td>";
  $totalRow.="</tr>";
      
	$pageBody="<tbody>".$totalRow.$pageBody.$totalRow."</tbody>";
  
  $table_id="Clientes";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
  echo '<h2>Clientes registrados</h2>';
	echo $pageOutput;
  
  $excelOutput.="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	
  

  
  $pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
    $pageHeader.="<tr>";
      $pageHeader.="<th>Planta</th>";
      $pageHeader.="<th>Genérico</th>";
      $pageHeader.="<th>Nombre</th>";
      $pageHeader.="<th>Email</th>";
      $pageHeader.="<th>Tel</th>";
      $pageHeader.="<th>Dir</th>";
      $pageHeader.="<th>RUC</th>";
      //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
      if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {  
        $pageHeader.="<th># Salidas</th>";
        $pageHeader.='<th style="min-width:220px;">Salidas</th>';
        $pageHeader.="<th>$ Salidas</th>";
			}
      $pageHeader.="<th class='actions'>".__('Actions')."</th>";
    $pageHeader.="</tr>";
  $pageHeader.="</thead>";
  $excelHeader.="<thead>";
    $excelHeader.="<tr>";
      $excelHeader.='<th>Planta</th>';
      $excelHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $excelHeader.="<th>Nombre</th>";
      //$excelHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $excelHeader.="<th>Email</th>";
      $excelHeader.="<th>Tel</th>";
      $excelHeader.="<th>Dir</th>";
      $excelHeader.="<th>RUC</th>";
      //if ($userrole == ROLE_ADMIN||$userrole == ROLE_ASSISTANT) {
      if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) {  
        $excelHeader.="<th># Salidas</th>";
        $excelHeader.="<th>Salidas</th>";
        $excelHeader.="<th>$ Salidas</th>";
			}
    $excelHeader.="</tr>";
  $excelHeader.="</thead>";
	$pageBody="";
	$excelBody="";

  //$totalPaymentPending=0;
  $totalQuantityOrders=0;
  $totalAmountOrders=0;
  
	foreach ($genericClients as $genericClientId=>$genericClientData){
    //pr($genericClientData);
    foreach ($genericClientData['SpecificClient'] as $specificClientName=>$specificClientData){
      //pr($specificClientData);
      $totalQuantityOrders+=$specificClientData['quantity_orders'];
      $totalAmountOrders+=$specificClientData['order_total'];
      
      $pageRow="";
      $pageRow.='<tr class="generic"'.($specificClientData['order_total']>10000?'style="background-color:yellow"':'').'>';       
        $pageRow.="<td>".$this->Html->link($plants[$genericClientData['GenericClient']['plant_id']], ['controller'=>'plants','action' => 'detalle', $genericClientData['GenericClient']['plant_id']])."</td>"; 
        $pageRow.="<td>".$this->Html->link($genericClientData['GenericClient']['company_name'], ['action' => 'verCliente', $genericClientData['GenericClient']['id']])."</td>";
        
        $pageRow.='<td>'.(empty($specificClientData['name'])?'-':$specificClientData['name']).'</td>';
        $pageRow.='<td>'.(empty($specificClientData['mail'])?'-':$specificClientData['mail']).'</td>';
        $pageRow.='<td>'.(empty($specificClientData['phone'])?'-':$specificClientData['phone']).'</td>';
        $pageRow.='<td>'.(empty($specificClientData['address'])?'-':$specificClientData['address']).'</td>';
        $pageRow.='<td>'.(empty($specificClientData['ruc'])?'-':$specificClientData['ruc']).'</td>';
        
        if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
          $pageRow.='<td>'.$specificClientData['quantity_orders'].'</td>';
          $pageRow.='<td>';
          foreach ($specificClientData['Order'] as $orderId=>$orderData){
            $orderDateTime=new DateTime($orderData['order_date']);
            $pageRow.=$this->Html->link($orderData['order_code']." (".$orderDateTime->format('d-m-Y').')',['controller'=>'orders','action'=>($orderData['bool_invoice']?'verVenta':'verRemision'),$orderId]).'<br/>';
          
          }
          $pageRow.='</td>';
          $pageRow.='<td class="CSCurrency"><span class="currency"></span><span class="amountright">'.$specificClientData['order_total'].'</span></td>';
        }
      $excelBody.='<tr class="generic">'.$pageRow.'</tr>';
        
        $pageRow.='<td class="actions">';
          if ($userRoleId == ROLE_ADMIN){ 
            $pageRow.=$this->Html->link('Convertir', ['action' => 'convertirCliente', $specificClientData['name']]); 
          } 
          //if ($bool_client_edit_permission){ 
          //  $pageRow.=$this->Html->link(__('Editar Cliente'), ['action' => 'editarCliente', $client['ThirdParty']['id']]); 
          //} 
        $pageRow.="</td>";
      $pageBody.='<tr class="generic">'.$pageRow.'</tr>';
    }
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      if ($userrole==ROLE_ADMIN || $canSeeFinanceData) { 
        $totalRow.='<td>'.$totalQuantityOrders.'</td>';
        $totalRow.="<td></td>";
        $totalRow.='<td class="CSCurrency"><span class="currency"></span><span class="amountright">'.$totalAmountOrders.'</span></td>';
      }
      $excelBody="<tbody>".$totalRow."</tr>".$excelBody.$totalRow."</tr>"."</tbody>";
      
      $totalRow.="<td></td>";
    $totalRow.="</tr>";
  }
      
	$pageBody="<tbody>".$totalRow.$pageBody.$totalRow."</tbody>";
  
  $table_id="Varios";
	$pageOutput="<table id='".$table_id."'>".$pageHeader.$pageBody."</table>";
  
  echo '<h2>Clientes genéricos</h2>';
	echo $pageOutput;
  
  $excelOutput.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	
  
  
  $_SESSION['resumenClientes'] = $excelOutput;
 
  
?>	
</div>