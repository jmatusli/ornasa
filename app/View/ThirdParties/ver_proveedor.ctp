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

<div class="thirdParties view providers">
<?php 
  echo '<h1>Proveedor '.$provider['ThirdParty']['company_name'].($provider['ThirdParty']['bool_active']?"":" (Inactivo)").' ('.$provider['Plant']['name'].')</h1>';
	echo "<dl>";
    echo '<dt>Planta</dt>';
		echo '<dd>'.h($provider['Plant']['name']).'</dd>';
		echo '<dt>'.__('Company Name').'</dt>';
		echo "<dd>".h($provider['ThirdParty']['company_name'])."</dd>";
    echo "<dt>".__('Accounting Code')."</dt>";
    echo '<dd>'.(empty($provider['AccountingCode']['code'])?'-':($this->Html->link($provider['AccountingCode']['code']." ".$provider['AccountingCode']['description'],['controller'=>'accounting_codes','action'=>'view',$provider['AccountingCode']['id']]))).'</dd>';
    echo '<dt>Naturaleza Proveedor</dt>';
    echo '<dd>'.$provider['ProviderNature']['name'].'</dd>';
    echo '<dt>'.__('Credit Days').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['credit_days'])?0:$provider['ThirdParty']['credit_days']).'</dd>';
    echo '<dt>'.__('Credit Amount').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['credit_amount'])?0:($provider['CreditCurrency']['abbreviation']." ".number_format($provider['ThirdParty']['credit_amount'],2,'.',','))).'</dd>';    
    echo '<dt>'.__('First Name').'/dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['first_name'])?'-':$provider['ThirdParty']['first_name']).'</dd>';
    echo '<dt>'.__('Last Name').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['last_name'])?'-':$provider['ThirdParty']['last_name'])."</dd>";
    echo '<dt>'.__('Email').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['email'])?'-':$provider['ThirdParty']['email']).'</dd>';
    echo '<dt>'.__('Phone').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['phone'])?'-':$provider['ThirdParty']['phone']).'</dd>';
    echo '<dt>'.__('Address').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['address'])?'-':$provider['ThirdParty']['address']).'</dd>';
    echo '<dt>'.__('Ruc Number').'</dt>';
    echo '<dd>'.(empty($provider['ThirdParty']['ruc_number'])?'-':$provider['ThirdParty']['ruc_number']).'</dd>';
	echo "</dl>";
	
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
	
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh'));
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Provider'), array('action' => 'editarProveedor', $provider['ThirdParty']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $provider['ThirdParty']['id']), array(), __('Are you sure you want to delete # %s?', $provider['ThirdParty']['id']))."</li>";
			//echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Providers'), array('action' => 'resumenProveedores'))."</li>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Provider'), array('action' => 'crearProveedor'))."</li>";
		}
		echo "<br/>";
		if ($bool_purchase_index_permission) {
			echo "<li>".$this->Html->link(__('List Purchases'), array('controller' => 'orders', 'action' => 'resumenEntradas'))." </li>";
		}
		if ($bool_purchase_add_permission) {
			echo "<li>".$this->Html->link(__('New Purchase'), array('controller' => 'orders', 'action' => 'crearEntrada'))." </li>";
		}
    if ($bool_purchase_order_index_permission) {
      echo "<br/>";
      echo "<li>".$this->Html->link(__('List Purchase Orders'), ['controller' => 'purchase_orders', 'action' => 'resumen'])." </li>";
    }
    if ($bool_purchase_order_add_permission) {
      echo "<li>".$this->Html->link(__('New Purchase Order'), ['controller' => 'purchase_orders', 'action' => 'crear'])." </li>";
    }
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
  if (!empty($provider['Order'])){
    echo '<h3>'.__('Related Purchases from Provider').'</h3>';
    echo '<table>';
      echo '<thead>';
        echo '<tr>';
          echo '<th>'.__('Purchase Date').'</th>';
          echo '<th>'.__('Order Code').'</th>';
          echo '<th class="centered">'.__('Total Price').'</th>';
        echo '</tr>';
      echo '</thead>';
      $totalPrice=0; 
      echo '<tbody>';
      foreach ($provider['Order'] as $purchase){
        $totalPrice+=$purchase['total_price'];
        $orderDateTime=new DateTime($purchase['order_date']);
        echo '<tr>';
          echo '<td>'.$orderDateTime->format('d-m-Y').'</td>';
          echo '<td>'.$this->Html->Link($purchase['order_code'],['controller'=>'orders','action'=>'verEntrada',$purchase['id']]).'</td>';
          echo '<td class="CSCurrency"><span class="currency"></span><span class="amountright">'.$purchase['total_price'].'</td>';
        echo '</tr>';
      }
        echo '<tr class="totalrow">';
          echo '<td>Total</td>';
          echo '<td></td>';
          echo '<td class="CSCurrency"><span class="currency"></span><span class="amountright">'.$totalPrice.'</span></td>';
        echo '</tr>';
      echo '</tbody>';
    echo '</table>';
  }
?>
</div>

<div class="related">
<?php 
  //pr($provider['PurchaseOrder']);
  if (!empty($provider['PurchaseOrder'])){
    echo "<h3>".__('Ordenes de Compra para este proveedor')."</h3>";
    $tableHead="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Purchase Order Date')."</th>";
        $tableHead.="<th>".__('Purchase Order Code')."</th>";
        $tableHead.="<th class='centered'>".__('Total Cost')."</th>";
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $totalCost=0;
    $pageRows="";
    foreach ($provider['PurchaseOrder'] as $purchaseOrder){
      $purchaseOrderDateTime=new DateTime($purchaseOrder['purchase_order_date']);
      $totalCost+=$purchaseOrder['cost_total'];
      $pageRow="<tr>";
        $pageRow.="<td>".$purchaseOrderDateTime->format('d-m-Y')."</td>";
        $pageRow.="<td>".$this->Html->Link($purchaseOrder['purchase_order_code'],['controller'=>'purchase_orders','action'=>'ver',$purchaseOrder['id']])."</td>";
        $pageRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$purchaseOrder['cost_total']."</span></td>";
      $pageRow.="</tr>";
      $pageRows.=$pageRow;  
    }
    
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='CSCurrency'><span class='currency'></span><span class='amountright'>".$totalCost."</span></td>";
      $totalRow.="<td></td>";
    $totalRow.="</tr>";
    $tableBody="<tbody>".$totalRow.$pageRows.$totalRow."</tbody>";
    echo "<table>".$tableHead.$tableBody."</table>";
  }
?>

</div>