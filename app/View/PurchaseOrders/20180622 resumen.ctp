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
			echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
			echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
			echo $this->Form->input('Report.currency_id',array('label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId));
			
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumen'), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div class='actions'>
<?php 	
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Purchase Order'), array('action' => 'crear'))."</li>";
		echo "<br/>";
		if ($bool_provider_index_permission){
			echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'thirdParties', 'action' => 'resumenProveedores'))." </li>";
		}
		if ($bool_provider_add_permission){
			echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'thirdParties', 'action' => 'crearProveedor'))." </li>";
		}
	echo "</ul>";
?>
</div>
<div>
<?php
	$excelOutput="";
	
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('purchase_order_date')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('purchase_order_code')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('provider_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_subtotal')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_total')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_other_total')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_mode_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_document')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('bool_received')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('purchase_order_date')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('purchase_order_code')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('provider_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('cost_subtotal')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('cost_iva')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('cost_total')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('cost_other_total')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_mode_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('payment_document')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('bool_received')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	$subtotalCS=0;
	$ivaCS=0;
	$totalCS=0;
	$totalOtherCS=0;
	$subtotalUSD=0;
	$ivaUSD=0;
	$totalUSD=0;
	$totalOtherUSD=0;
	
	foreach ($purchaseOrders as $purchaseOrder){ 
		$purchaseOrderDateTime=new DateTime($purchaseOrder['PurchaseOrder']['purchase_order_date']);
		
		if ($purchaseOrder['Currency']['id']==CURRENCY_CS){
				$currencyClass=" class='CScurrency'";
				$subtotalCS+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
				$ivaCS+=$purchaseOrder['PurchaseOrder']['cost_iva'];
				$totalCS+=$purchaseOrder['PurchaseOrder']['cost_total'];
				$totalCS+=$purchaseOrder['PurchaseOrder']['cost_other_total'];
				
				//added calculation of totals in US$
				$subtotalUSD+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$ivaUSD+=round($purchaseOrder['PurchaseOrder']['cost_iva']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$totalUSD+=round($purchaseOrder['PurchaseOrder']['cost_total']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$totalUSD+=round($purchaseOrder['PurchaseOrder']['cost_other_total']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			}
			elseif ($purchaseOrder['Currency']['id']==CURRENCY_USD){
				$currencyClass=" class='USDcurrency'";
				$subtotalUSD+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
				$ivaUSD+=$purchaseOrder['PurchaseOrder']['cost_iva'];
				$totalUSD+=$purchaseOrder['PurchaseOrder']['cost_total'];
				$totalUSD+=$purchaseOrder['PurchaseOrder']['cost_other_total'];
				
				//added calculation of totals in CS$
				$subtotalCS+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$ivaCS+=round($purchaseOrder['PurchaseOrder']['cost_iva']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$totalCS+=round($purchaseOrder['PurchaseOrder']['cost_total']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				$totalCS+=round($purchaseOrder['PurchaseOrder']['cost_other_total']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			}

		
		$pageRow="";
			$pageRow.="<td>".$purchaseOrderDateTime->format('d-m-Y')."</td>";
      $pageRow.="<td>".$this->Html->link($purchaseOrder['PurchaseOrder']['purchase_order_code'].($purchaseOrder['PurchaseOrder']['bool_annulled']?" (Anulada)":""), ['action' => 'ver', $purchaseOrder['PurchaseOrder']['id']])."</td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['Provider']['company_name'], array('controller' => 'providers', 'action' => 'view', $purchaseOrder['Provider']['id']))."</td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['User']['username'], array('controller' => 'users', 'action' => 'view', $purchaseOrder['User']['id']))."</td>";
			$pageRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_iva']?__('Yes'):__('No'))."</td>";
			
			$excelRow=$pageRow;
			
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_subtotal'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_iva'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_total'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_other_total'])."</span></td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['PaymentMode']['name'], array('controller' => 'payment_modes', 'action' => 'view', $purchaseOrder['PaymentMode']['id']))."</td>";
			$pageRow.="<td>".h($purchaseOrder['PurchaseOrder']['payment_document'])."</td>";
			$pageRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_received']?__('Yes'):__('No'))."</td>";

			$excelRow.="<td>".$purchaseOrder['Currency']['abbreviation']."</td>";
			$excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_subtotal']."</td>";
			$excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_iva']."</td>";
			$excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_total']."</td>";
			$excelRow.="<td>".$purchaseOrder['PurchaseOrder']['cost_other_total']."</td>";
			$excelRow.="<td>".$purchaseOrder['PaymentMode']['name']."</td>";
			$excelRow.="<td>".h($purchaseOrder['PurchaseOrder']['payment_document'])."</td>";
			$excelRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_received']?__('Yes'):__('No'))."</td>";
			
		$excelBody.="<tr>".$excelRow."</tr>";

			$pageRow.="<td class='actions'>";
				
				if ($bool_edit_permission){
					$pageRow.=$this->Html->link(__('Edit'), array('action' => 'editar', $purchaseOrder['PurchaseOrder']['id']));
				}
				//if ($bool_delete_permission){
				//	$pageRow.=$this->Form->postLink(__('Eliminar Orden'), array('action' => 'delete', $purchaseOrder['PurchaseOrder']['id']), array(), __('Está seguro que quiere eliminar orden de compra # %s?', $purchaseOrder['PurchaseOrder']['purchase_order_code']));
				//}
				//$pageRow.=$this->Html->link(__('Recepción Orden de Compra'), array('action' => 'recibirOrdenDeCompra', $purchaseOrder['PurchaseOrder']['id']));
			$pageRow.="</td>";

		if ($purchaseOrder['PurchaseOrder']['bool_annulled']){
			$pageBody.="<tr class='italic'>".$pageRow."</tr>";
		}
		else {
			$pageBody.="<tr>".$pageRow."</tr>";
		}
	}

	$pageTotalRow="";
	if ($currencyId==CURRENCY_CS){
		$pageTotalRow.="<tr class='totalrow'>";
			$pageTotalRow.="<td>Total C$</td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCS."</span></td>";
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$ivaCS."</span></td>";
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalOtherCS."</span></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
	}
	
	if ($currencyId==CURRENCY_USD){
		$pageTotalRow.="<tr class='totalrow'>";
			$pageTotalRow.="<td>Total US$</td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$subtotalUSD."</td>";
			$pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$ivaUSD."</td>";
			$pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalUSD."</td>";
			$pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalOtherUSD."</td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
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
			$excelTotalRow.="<td>".$subtotalCS."</td>";
			$excelTotalRow.="<td>".$ivaCS."</td>";
			$excelTotalRow.="<td>".$totalCS."</td>";
			$excelTotalRow.="<td>".$totalOtherCS."</td>";
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
			$excelTotalRow.="<td>".$subtotalUSD."</td>";
			$excelTotalRow.="<td>".$ivaUSD."</td>";
			$excelTotalRow.="<td>".$totalUSD."</td>";
			$excelTotalRow.="<td>".$totalOtherUSD."</td>";
			$excelTotalRow.="<td></td>";
		$excelTotalRow.="</tr>";
	}
	*/
	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="ordenes_compra";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput.="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>