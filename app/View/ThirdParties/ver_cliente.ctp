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
		$("td.CSCurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDCurrency").each(function(){
			
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

<div class="thirdParties view clients">
<?php 
  echo '<h1>Cliente '.$client['ThirdParty']['company_name'].($client['ThirdParty']['bool_generic']?' (Genérico!)':'').($client['ThirdParty']['bool_active']?"":"  (Inactivo)").'</h1>';
	echo "<dl>";
    //echo '<dt>Planta</dt>';
		//echo '<dd>'.h($client['Plant']['name']).'</dd>';
		echo '<dt>'.__('Company Name').'</dt>';
		echo '<dd>'.($client['ThirdParty']['company_name']).'</dd>';
    echo '<dt>'.__('Price Client Category').'</dt>';
		echo '<dd>'.(empty($client['PriceClientCategory'])?"-":$client['PriceClientCategory']['name']).'</dd>';
		echo "<dt>".__('Accounting Code')."</dt>";
		echo "<dd>".(empty($client['AccountingCode']['code'])?"-":($this->Html->Link(($client['AccountingCode']['code']." ".$client['AccountingCode']['description']),['controller'=>'accounting_codes','action'=>'view',$client['AccountingCode']['id']])))."</dd>";
		echo "<dt>".__('Credit Days')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['credit_days'])?0:$client['ThirdParty']['credit_days'])."</dd>";
		echo "<dt>".__('Credit Amount')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['credit_amount'])?"0":($client['CreditCurrency']['abbreviation']." ".number_format($client['ThirdParty']['credit_amount'],2,'.',',')))."</dd>";
		echo "<dt>".__('Tasa Expiración (%)')."</dt>";
		echo "<dd>".$client['ThirdParty']['expiration_rate']."</dd>";
    echo "<dt>Pago Pendiente</dt>";
		echo "<dd>C$ ".(empty($client['ThirdParty']['pending_payment'])?"0.00":number_format($client['ThirdParty']['pending_payment'],2,'.',','))."</dd>";
		
    echo "<dt>".__('Client Type')."</dt>";
		echo "<dd>".(empty($client['ClientType']['name'])?"-":($this->Html->Link($client['ClientType']['name'],['controller'=>'clientTypes','action'=>'detalle',$client['ClientType']['id']])))."</dd>";
    echo "<dt>".__('Zone')."</dt>";
		echo "<dd>".(empty($client['Zone']['id'])?"-":($this->Html->Link($client['Zone']['name'],['controller'=>'zones','action'=>'detalle',$client['Zone']['id']])))."</dd>";
		echo "<dt>".__('First Name')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['first_name'])?"-":$client['ThirdParty']['first_name'])."</dd>";
		echo "<dt>".__('Last Name')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['last_name'])?"-":$client['ThirdParty']['last_name'])."</dd>";
		echo "<dt>".__('Email')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['email'])?"-":$client['ThirdParty']['email'])."</dd>";
		echo "<dt>".__('Phone')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['phone'])?"-":$client['ThirdParty']['phone'])."</dd>";
		echo "<dt>".__('Address')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['address'])?"-":$client['ThirdParty']['address'])."</dd>";
		echo "<dt>".__('Ruc Number')."</dt>";
		echo "<dd>".(empty($client['ThirdParty']['ruc_number'])?"-":$client['ThirdParty']['ruc_number'])."</dd>";
	echo '</dl>'; 
	
	echo $this->Form->create('Report');
	echo '<fieldset>';
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo '</fieldset>';
	echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
	echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
	echo $this->Form->end(__('Refresh')); 
?>
	
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($userRoleId == ROLE_ADMIN){
      echo '<li>'.$this->Html->link(__('Precios de Productos para Cliente'), ['controller' => 'productPriceLogs', 'action' => 'registrarPreciosCliente',$client['ThirdParty']['id']]).'</li>';
    }
    
    if ($bool_edit_permission) {
			echo "<li>".$this->Html->link(__('Edit Client'), array('action' => 'editarCliente', $client['ThirdParty']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $client['ThirdParty']['id']), array(), __('Are you sure you want to delete # %s?', $client['ThirdParty']['id']))."</li>";
			//echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Clients'), array('action' => 'resumenClientes'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Client'), array('action' => 'crearCliente'))."</li>";
		}
		echo "<br/>";
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
?>
		
</div>
<div class="related">
<?php 
  //pr($client['Order']);
  if (!empty($client['Order'])){
    echo "<h3>".__('Related Sales to Client')."</h3>";
    $tableHead="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Exit Date')."</th>";
        $tableHead.="<th>".__('Order Code')."</th>";
        $tableHead.="<th class='centered'>".__('Total Price')."</th>";
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $totalPrice=0;
    $pageRows="";
    foreach ($client['Order'] as $sale){
      $orderDateTime=new DateTime($sale['order_date']);
      $totalPrice+=$sale['total_price'];
      $pageRow="<tr>";
        $pageRow.="<td>".$orderDateTime->format('d-m-Y')."</td>";
        $pageRow.="<td>".$this->Html->Link($sale['order_code'],['controller'=>'orders','action'=>'verVenta',$sale['id']])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$sale['total_price']."</span></td>";
      $pageRow.="</tr>";
      $pageRows.=$pageRow;  
    }
    
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalPrice."</span></td>";
      $totalRow.="<td></td>";
    $totalRow.="</tr>";
    $tableBody="<tbody>".$totalRow.$pageRows.$totalRow."</tbody>";
    echo "<table>".$tableHead.$tableBody."</table>";
  }
?>

</div>


<div class="related">
<?php 
	if(!empty($client['ThirdPartyUser'])){
		echo "<h3>".__('Vendedores asociados con este Cliente')."</h3>";
		echo '<table>';
      $tableHeader="";
      $tableHeader.="<thead>";
        $tableHeader.="<tr>";
          $tableHeader.="<th>".__('Username')."</th>";
          $tableHeader.="<th>".__('First Name')."</th>";
          $tableHeader.="<th>".__('Last Name')."</th>";
          $tableHeader.="<th>".__('Email')."</th>";
          $tableHeader.="<th>".__('Phone')."</th>";
          $tableHeader.="<th style='width:15%;'>Historial de Asignaciones</th>";
        $tableHeader.="</tr>";
      $tableHeader.="</thead>";
      echo $tableHeader;
      $tableBody="";
      $tableBody.="<tbody>";
      foreach ($uniqueUsers as $user){
        //pr($clientUser);
        $tableBody.=($user['ThirdPartyUser'][0]['bool_assigned']?"<tr>":"<tr class='italic'>");
          $tableBody.="<td>".$this->Html->link($user['User']['username'], ['controller' => 'users', 'action' => 'view', $user['User']['id']])."</td>";
          $tableBody.="<td>".$user['User']['first_name']."</td>";
          $tableBody.="<td>".$user['User']['last_name']."</td>";
          $tableBody.="<td>".$user['User']['email']."</td>";
          $tableBody.="<td>".$user['User']['phone']."</td>";
          $tableBody.="<td>";
          foreach ($user['ThirdPartyUser'] as $clientUser){
            //pr($clientUser);
            $assignmentDateTime=new DateTime($clientUser['assignment_datetime']);
            $tableBody.=($clientUser['bool_assigned']?"Asignado":"Desasignado")." el ".($assignmentDateTime->format('d-m-Y H:i:s'))."<br>";
          }  
          $tableBody.="</td>";
        $tableBody.="</tr>";
      }
      $tableBody.="</tbody>";
      echo $tableBody;
		echo "</table>";
	}
?>
</div>

<div class="related">
<?php 
	if(!empty($client['PlantThirdParty'])){
		echo "<h3>".__('Plantas asociados con este Cliente')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
      $tableHeader="";
      $tableHeader.="<thead>";
        $tableHeader.="<tr>";
          $tableHeader.="<th>".__('Name')."</th>";
          $tableHeader.="<th style='width:50%;'>Historial de Asignaciones</th>";
        $tableHeader.="</tr>";
      $tableHeader.="</thead>";
      echo $tableHeader;
      $tableBody="";
      $tableBody.="<tbody>";
      foreach ($uniquePlants as $plant){
        $tableBody.=($plant['PlantThirdParty'][0]['bool_assigned']?"<tr>":"<tr class='italic'>");
          $tableBody.="<td>".$this->Html->link($plant['Plant']['name'],['controller' => 'plants', 'action' => 'detalle', $plant['Plant']['id']])."</td>";
          
          $tableBody.="<td>";
          foreach ($plant['PlantThirdParty'] as $plantThirdParty){
            //pr($plantThirdParty);
            $assignmentDateTime=new DateTime($plantThirdParty['assignment_datetime']);
            $tableBody.=($plantThirdParty['bool_assigned']?"Asignado":"Desasignado")." el ".($assignmentDateTime->format('d-m-Y H:i:s'))."<br>";
          }  
          $tableBody.="</td>";
        $tableBody.="</tr>";
      }
      $tableBody.="</tbody>";
      echo $tableBody;
		echo "</table>";
	}
?>
</div>
