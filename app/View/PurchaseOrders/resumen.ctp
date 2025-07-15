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
<div class="purchaseOrders index">
<?php 
	echo "<h2>".__('Purchase Orders')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
      
      echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
      
			echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId]);
      echo $this->Form->input('Report.purchase_order_state_id',['label'=>'Estado Orden','default'=>$purchaseOrderStateId,'empty'=>[0=>'-- Seleccione Estado --']]);
			
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], [ 'class' => 'btn btn-primary']);
?> 
</div>
<div class='actions'>
<?php 	
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Purchase Order'), ['action' => 'crear'])."</li>";
		echo "<br/>";
		if ($bool_provider_index_permission){
			echo "<li>".$this->Html->link(__('List Providers'), ['controller' => 'thirdParties', 'action' => 'resumenProveedores'])." </li>";
		}
		if ($bool_provider_add_permission){
			echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'thirdParties', 'action' => 'crearProveedor'))." </li>";
		}
	echo "</ul>";
?>
</div>
<div style="clear:left;">
<?php
	$excelOutput="";
  
  

  if (!empty($purchaseOrderStateArray)){
    foreach ($purchaseOrderStateArray as $stateId=>$stateData){
      $tableHeader="<thead>";
        $tableHeader.="<tr>";
          $tableHeader.="<th>".$this->Paginator->sort('purchase_order_date','Fecha OC')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('purchase_order_code','# OC')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('provider_id')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
          $tableHeader.="<th>Productos</th>";
          $tableHeader.="<th>".$this->Paginator->sort('bool_credit','Crédito')."</th>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $tableHeader.="<th>Entradas Asociadas</th>";
          }
          $tableHeader.="<th>".$this->Paginator->sort('cost_subtotal')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('cost_iva')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('cost_total')."</th>";
          
          $tableHeader.="<th class='actions'>".__('Actions')."</th>";
        $tableHeader.="</tr>";
      $tableHeader.="</thead>";
      $excelHeader="<thead>";
        $excelHeader.="<tr>";
          $excelHeader.="<th>".$this->Paginator->sort('purchase_order_date','Fecha OC')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('purchase_order_code','# OC')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('provider_id')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
          $excelHeader.="<th>Productos</th>";
          $excelHeader.="<th>".$this->Paginator->sort('bool_credit','Crédito')."</th>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $excelHeader.="<th>Entradas Asociadas</th>";
          }
          $excelHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('cost_subtotal')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('cost_iva')."</th>";
          $excelHeader.="<th>".$this->Paginator->sort('cost_total')."</th>";			
        $excelHeader.="</tr>";
      $excelHeader.="</thead>";
    
    
      $tableBody="";
      $excelBody="";

      $subtotalCS=0;
      $ivaCS=0;
      $totalCS=0;
      $totalOtherCS=0;
      $subtotalUSD=0;
      $ivaUSD=0;
      $totalUSD=0;
      $totalOtherUSD=0;
      
      foreach ($stateData['PurchaseOrders'] as $purchaseOrder){ 
        //pr($purchaseOrder);
        $purchaseOrderDateTime=new DateTime($purchaseOrder['PurchaseOrder']['purchase_order_date']);
        
        if ($purchaseOrder['Currency']['id']==CURRENCY_CS){
          $currencyClass=" class='CScurrency'";
          $subtotalCS+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
          $ivaCS+=$purchaseOrder['PurchaseOrder']['cost_iva'];
          $totalCS+=$purchaseOrder['PurchaseOrder']['cost_total'];
          
          //added calculation of totals in US$
          $subtotalUSD+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
          $ivaUSD+=round($purchaseOrder['PurchaseOrder']['cost_iva']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
          $totalUSD+=round($purchaseOrder['PurchaseOrder']['cost_total']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
          
        }
        elseif ($purchaseOrder['Currency']['id']==CURRENCY_USD){
          $currencyClass=" class='USDcurrency'";
          $subtotalUSD+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
          $ivaUSD+=$purchaseOrder['PurchaseOrder']['cost_iva'];
          $totalUSD+=$purchaseOrder['PurchaseOrder']['cost_total'];
          
          
          //added calculation of totals in CS$
          $subtotalCS+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
          $ivaCS+=round($purchaseOrder['PurchaseOrder']['cost_iva']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
          $totalCS+=round($purchaseOrder['PurchaseOrder']['cost_total']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);				
        }

        $tableRow="";
          $tableRow.="<td>".$purchaseOrderDateTime->format('d-m-Y')."</td>";
          $tableRow.="<td>".$this->Html->link($purchaseOrder['PurchaseOrder']['purchase_order_code'].($purchaseOrder['PurchaseOrder']['bool_annulled']?" (Anulada)":""), ['action' => 'ver', $purchaseOrder['PurchaseOrder']['id']])."</td>";
          $tableRow.="<td>".$this->Html->link($purchaseOrder['Provider']['company_name'], ['controller' => 'thirdParties', 'action' => 'verProveedor', $purchaseOrder['Provider']['id']])."</td>";
          $tableRow.="<td>".$this->Html->link($purchaseOrder['User']['username'], ['controller' => 'users', 'action' => 'view', $purchaseOrder['User']['id']])."</td>";
          $tableRow.="<td>";
          foreach($purchaseOrder['PurchaseOrderProduct'] as $purchaseOrderProduct){
            $tableRow.=number_format($purchaseOrderProduct['product_quantity'],0,'',',').(empty($purchaseOrderProduct['Unit'])?'':(' '.$purchaseOrderProduct['Unit']['abbreviation']))." ".$purchaseOrderProduct['Product']['name']."<br/>";
          }
          $tableRow.="</td>";
          $tableRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_credit']?__('Crédito'):__('Contado'))."</td>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){          
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){          
            $tableRow.="<td>";
            
            if (!empty($purchaseOrder['Entry'])){
              $entryCounter=0;
              foreach($purchaseOrder['Entry'] as $entry){
                $entryCounter++;
                if ($bool_orders_verEntrada_permission){                      $tableRow.=$this->Html->link($entry['order_code'],['controller'=>'orders','action'=>'verEntrada',$entry['id']])." ".($entryCounter < count($purchaseOrder['Entry'])?"<br/>":'');
                }
                else {
                  $tableRow.=$entry['order_code'].($entryCounter < count($purchaseOrder['Entry'])?"<br/>":'');
                }
              }
            }
            else {
              $tableRow.="-";
            }
            $tableRow.="</td>";
          }
          $excelRow=$tableRow;
          
          $tableRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_subtotal'])."</span></td>";
          $tableRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_iva'])."</span></td>";
          $tableRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_total'])."</span></td>";
          
          $excelRow.="<td>".$purchaseOrder['Currency']['abbreviation']."</td>";
          $excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_subtotal']."</td>";
          $excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_iva']."</td>";
          $excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_total']."</td>";
          
        $excelBody.="<tr>".$excelRow."</tr>";
          $tableRow.='<td class="actions">';
            
            if ($bool_edit_permission && $stateId < PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY ){
              $tableRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $purchaseOrder['PurchaseOrder']['id']]);
            }
            if ($stateId == PURCHASE_ORDER_STATE_AWAITING_AUTHORIZATION && ($userRoleId === ROLE_ADMIN || $canAutorizePurcharse)){
              $tableRow.=$this->Html->link(__('Autorizar'), ['action' => 'autorizar', $purchaseOrder['PurchaseOrder']['id']]);
            }
            if ($stateId == PURCHASE_ORDER_STATE_AUTHORIZED && $bool_confirmar_permission){
              $tableRow.=$this->Html->link(__('Confirmar'), ['action' => 'confirmar', $purchaseOrder['PurchaseOrder']['id']]);
            }
          $tableRow.="</td>";

        if ($purchaseOrder['PurchaseOrder']['bool_annulled']){
          $tableBody.='<tr class="italic" style="background-color:'.$stateData['PurchaseOrderState']['color'].'!important;">'.$tableRow.'</tr>';
        }
        else {
          $tableBody.='<tr style="background-color:'.$stateData['PurchaseOrderState']['color'].'!important;">'.$tableRow.'</tr>';
        }
      }

      $totalRow="";
      if ($currencyId==CURRENCY_CS){
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total C$</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $totalRow.="<td></td>";
          }
          $totalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCS."</span></td>";
          $totalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$ivaCS."</span></td>";
          $totalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
          $totalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalOtherCS."</span></td>";
          $totalRow.="<td></td>";
          
        $totalRow.="</tr>";
      }
      
      if ($currencyId==CURRENCY_USD){
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total US$</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $totalRow.="<td></td>";
          }
          $totalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$subtotalUSD."</td>";
          $totalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$ivaUSD."</td>";
          $totalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalUSD."</td>";
          $totalRow.="<td></td>";
          
        $totalRow.="</tr>";
      }
      /*
      $excelTotalRow="";
      if ($subtotalCS>0){
        $excelTotalRow.="<tr class='totalrow'>";
          $excelTotalRow.="<td>Total C$</td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $excelTotalRow.="<td></td>";
          }
          $excelTotalRow.="<td>".$subtotalCS."</td>";
          $excelTotalRow.="<td>".$ivaCS."</td>";
          $excelTotalRow.="<td>".$totalCS."</td>";
          
          $excelTotalRow.="<td></td>";
        $excelTotalRow.="</tr>";
      }
      if ($subtotalUSD>0){
        $excelTotalRow.="<tr class='totalrow'>";
          $excelTotalRow.="<td>Total US$</td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          $excelTotalRow.="<td></td>";
          //if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY|| $stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
          if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){  
            $excelTotalRow.="<td></td>";
          }
          $excelTotalRow.="<td>".$subtotalUSD."</td>";
          $excelTotalRow.="<td>".$ivaUSD."</td>";
          $excelTotalRow.="<td>".$totalUSD."</td>";
          
          $excelTotalRow.="<td></td>";
        $excelTotalRow.="</tr>";
      }
      */
      $tableBody="<tbody>".$totalRow.$tableBody.$totalRow."</tbody>";
      $table_id=substr($stateData['PurchaseOrderState']['name'],0,29);
      $pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$tableHeader.$tableBody."</table>";
      echo '<h2>Ordenes de compra con estado '.$stateData['PurchaseOrderState']['name'].'</h2>';
      echo $pageOutput;
      $excelOutput.="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
    }
  }
	
	$_SESSION['resumenOrdenCompras'] = $excelOutput;
?>
</div>