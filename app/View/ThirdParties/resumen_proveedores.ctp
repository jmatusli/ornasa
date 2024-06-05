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

<div class="thirdParties index providers fullwidth">
<?php 
  echo "<h2>".__('Providers')."</h2>";
  
    echo "<div class='container_fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-md-5'>";
        echo $this->Form->create('Report',['style'=>'width:100%']); 
        echo "<fieldset>"; 
          echo $this->PlantFilter->displayPlantFilter($plants, $userRoleId,$plantId);              
          echo $this->Form->input('Report.active_display_option_id',['label'=>__('Clientes Activos'),'default'=>$activeDisplayOptionId]);
          //if ($userRoleId == ROLE_ADMIN || $userRoleId==ROLE_ASSISTANT) { 
          if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
            echo $this->Form->input('Report.aggregate_option_id',['label'=>__('Mostrar y Ordenar Por'),'default'=>$aggregateOptionId]);
          }
          else {
            echo $this->Form->input('Report.aggregate_option_id',['label'=>__('Mostrar y Ordenar Por'),'default'=>AGGREGATES_NONE,'type'=>'hidden']);
          }
          echo $this->Form->input('Report.searchterm',['label'=>'Buscar']);
      echo "</div>";
      echo "<div class='col-md-5'>";
        if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers) {   
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2015,'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2015,'maxYear'=>date('Y')]);
          //echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId]);
        }
        echo  "</fieldset>";
        echo "<br/>";
        echo $this->Form->end(__('Refresh')); 
      echo "</div>";  
      echo "<div class='col-md-2'>";
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
       
        if ($bool_add_permission) {
          echo "<li>".$this->Html->link(__('New Provider'), ['action' => 'crearProveedor'])."</li>";
          echo "<br/>";
        }
        if ($bool_purchase_index_permission) {
          echo "<li>".$this->Html->link(__('List Purchases'), ['controller' => 'orders', 'action' => 'resumenEntradas'])." </li>";
        }
        if ($bool_purchase_add_permission) {
          echo "<li>".$this->Html->link(__('New Purchase'), ['controller' => 'orders', 'action' => 'crearEntrada'])." </li>";
        }
        if ($bool_purchase_order_index_permission) {
          echo "<br/>";
          echo "<li>".$this->Html->link(__('List Purchase Orders'), ['controller' => 'purchase_orders', 'action' => 'resumen'])." </li>";
        }
        if ($bool_purchase_order_add_permission) {
          echo "<li>".$this->Html->link(__('New Purchase Order'), ['controller' => 'purchase_orders', 'action' => 'crear'])." </li>";
        }
        echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
  
  echo "<br>";
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenProveedores'], ['class' => 'btn btn-primary']);  
  
  $pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
    $pageHeader.="<tr>";
      $pageHeader.='<th>Planta</th>';
      $pageHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('provider_nature_id','Naturaleza Proveedor')."</th>";
      //$pageHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $pageHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      //$pageHeader.="<th class='centered'>Pago Pendiente</th>";
      $pageHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $pageHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      if ($userRoleId==ROLE_ADMIN || $canSeeFinanceData) { 
        $pageHeader.="<th># Ordenes de Compra</th>";
        $pageHeader.="<th>$ Ordenes de Compra</th>";
			}
      //$pageHeader.="<th class='actions'>".__('Actions')."</th>";
    $pageHeader.="</tr>";
  $pageHeader.="</thead>";
  $excelHeader.="<thead>";
    $excelHeader.="<tr>";
      $excelHeader.='<th>Planta</th>';
      $excelHeader.="<th>".$this->Paginator->sort('company_name')."</th>";
      $excelHeader.="<th>Naturaleza Proveedor</th>";
      //$excelHeader.="<th>".$this->Paginator->sort('accounting_code_id')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('credit_days')."</th>";
      $excelHeader.="<th class='centered'>".$this->Paginator->sort('credit_amount')."</th>";
      //$excelHeader.="<th class='centered'>Pago Pendiente</th>";
      $excelHeader.="<th>".$this->Paginator->sort('first_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('last_name')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('email')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('phone')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('address')."</th>";
      $excelHeader.="<th>".$this->Paginator->sort('ruc_number')."</th>";
      if ($userRoleId==ROLE_ADMIN || $canSeeFinanceData) { 
        $excelHeader.="<th># Ordenes de Compra</th>";
        $excelHeader.="<th>$ Ordenes de Compra</th>";
			}
    $excelHeader.="</tr>";
  $excelHeader.="</thead>";
	$pageBody="";
	$excelBody="";
  
  $totalPaymentPending=0;
  $totalQuantityOrders=0;
  $totalAmountOrders=0;
  
		foreach ($providers as $provider){
      //$totalPaymentPending+=$provider['ThirdParty']['pending_payment'];
      $totalQuantityOrders+=count($provider['PurchaseOrder']);
      $totalAmountOrders+=$provider['ThirdParty']['purchase_order_total'];
      
      $pageRow="";
      $pageRow.="<td>".$this->Html->link($provider['Plant']['name'], ['controller'=>'plants','action' => 'detalle', $provider['Plant']['id']])."</td>";
      $pageRow.="<td>".$this->Html->link($provider['ThirdParty']['company_name'].($provider['ThirdParty']['bool_active']?"":" (Inactivo)"), ['action' => 'verProveedor', $provider['ThirdParty']['id']])."</td>";
      $pageRow.="<td>".$provider['ProviderNature']['name']."</td>";
      //$pageRow.="<td>".(empty($provider['AccountingCode']['code'])?"-":$this->Html->link($provider['AccountingCode']['code']." ".$provider['AccountingCode']['description'],['controller'=>'accounting_codes','action'=>'view',$provider['AccountingCode']['id']]))."</td>";
    
      $pageRow.="<td class='centered'>".(empty($provider['ThirdParty']['credit_days'])?0:$provider['ThirdParty']['credit_days'])."</td>";
      if (!empty($provider['ThirdParty']['credit_amount'])){
        $pageRow.="<td class='centered ".($provider['ThirdParty']['credit_currency_id']==CURRENCY_USD?'USDCurrency':'CSCurrency')."' ><span class='currency'></span><span class='amountright'>".$provider['ThirdParty']['credit_amount']."</span></td>";
      }
      else {
        $pageRow.="<td class='centered'>-</td>";
      }
      //$pageRow.="<td class='centered CSCurrency'><span class='currency'></span><span class='amountright'>".$provider['ThirdParty']['pending_payment']."</span></td>";
      
      $pageRow.="<td>".$provider['ThirdParty']['first_name']."</td>";
      $pageRow.="<td>".$provider['ThirdParty']['last_name']."</td>";
      $pageRow.="<td>".$provider['ThirdParty']['email']."</td>";
      $pageRow.="<td>".$provider['ThirdParty']['phone']."</td>";
      $pageRow.="<td >".(empty($provider['ThirdParty']['address'])?"-":$provider['ThirdParty']['address'])."</span></td>";
      $pageRow.="<td >".(empty($provider['ThirdParty']['ruc_number'])?"-":$provider['ThirdParty']['ruc_number'])."</span></td>";
     
      //if ($userRoleId==ROLE_ADMIN || $userRoleId==ROLE_ASSISTANT) { 
      if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { $pageRow.="<td>".count($provider['PurchaseOrder'])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$provider['ThirdParty']['purchase_order_total']."</span></td>";
			}
      $excelBody.=($provider['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
			
      //$pageRow.="<td class='actions'>";
      //  if ($bool_edit_permission){ 
      //    $pageRow.=$this->Html->link(__('Editar Proveedor'), ['action' => 'editarProveedor', $provider['ThirdParty']['id']]); 
      //  } 
      //  if ($bool_delete_permission){ 
      //    //$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'deleteProvider', $provider['ThirdParty']['id']), array(), __('Está seguro que quiere eliminar el proveedor %s?', $provider['ThirdParty']['company_name'])); 
      //  }
      //$pageRow.="</td>";
    $pageBody.=($provider['ThirdParty']['bool_active']?"<tr>":"<tr class='italic'>").$pageRow."</tr>";
  }
  $totalRow="<tr class='totalrow'>";
    $totalRow.="<td>Total</td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    //$totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    //$totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalPaymentPending."</span></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    $totalRow.="<td></td>";
    //if ($userRoleId==ROLE_ADMIN || $userRoleId==ROLE_ASSISTANT) { 
    if ($userRoleId == ROLE_ADMIN || $canSeeFinanceData) { 
      $totalRow.="<td>".$totalQuantityOrders."</td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalAmountOrders."</span></td>";
    }
    $excelBody="<tbody>".$totalRow."</tr>".$excelBody.$totalRow."</tr>"."</tbody>";
    
    //$totalRow.="<td></td>";
  $totalRow.="<tr></tr>";
      
	$pageBody="<tbody>".$totalRow.$pageBody.$totalRow."</tbody>";
  
  $tableId="Proveedores";
	$pageOutput="<table id='".$tableId."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
  
  $excelOutput="<table id='".$tableId."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumenProveedores'] = $excelOutput;
  
?>	
</div>