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
<div class="quotations index">
<?php 
	echo "<h2>".__('Quotations')."</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-6'>";		
        echo $this->Form->create('Report');
          echo "<fieldset>";
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2015,'maxYear'=>date('Y')));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2015,'maxYear'=>date('Y')));
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            if ($userRoleId==ROLE_ADMIN || $canSeeAllUsers || $canSeeAllVendors) { 
              echo $this->Form->input('Report.user_id',['label'=>'Mostrar Usuario','options'=>$users,'default'=>$userId,'empty'=>['0'=>'-- Todos Usuarios --']]);
            }
            else {
              echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Usuario'),'value'=>$userId,'type'=>'hidden']);
            }
            echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'default'=>$currencyId]);
            
            echo $this->Form->input('Report.client_type_id',['default'=>$clientTypeId,'empty'=>[0=>'-- Tipo de cliente --']]);
            echo $this->Form->input('Report.zone_id',['default'=>$zoneId,'empty'=>[0=>'-- Zone --']]);
            
            echo $this->Form->input('Report.sales_order_display',array('label'=>__('Mostrar Ordenes de Venta'),'options'=>$salesOrderOptions,'default'=>$salesOrderDisplay));
            echo $this->Form->input('Report.rejected_display',array('label'=>__('Mostrar Caídas'),'options'=>$rejectedOptions,'default'=>$rejectedDisplay));
          echo "</fieldset>";
          echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
          echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
        echo "<br/>";	
        echo $this->Form->end(__('Refresh'));
        echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumen'),['class' => 'btn btn-primary']);
      echo "</div>";
      echo "<div class='col-md-6'>";		
        if ($userRoleId==ROLE_ADMIN || $canSeeExecutiveSummary) {  
          
          $thead="";  
          $thead.="<thead>";
            $thead.="<tr>";
              $thead.="<th>Ejecutivo</th>";
              $thead.="<th>Total Período</th>";
              $thead.="<th>Total Pendiente</th>";
            $thead.="</tr>";
          $thead.="</thead>";
          
          $totalCSPeriod=0; 
          $totalUSDPeriod=0;
          $totalCSPending=0; 
          $totalUSDPending=0;
                
          $tbody="";         
          $tbody.="<tbody>";							
          foreach ($users as $key=>$value){
            if (!empty($userPeriodCS[$key])||!empty($userPeriodUSD[$key])||!empty($userPendingCS[$key])||!empty($userPendingUSD[$key])){
              $totalCSPeriod+=$userPeriodCS[$key]; 
              $totalUSDPeriod+=$userPeriodUSD[$key];
              $totalCSPending+=$userPendingCS[$key]; 
              $totalUSDPending+=$userPendingUSD[$key];
              if ($currencyId==CURRENCY_CS){
                $tbody.="<tr>";
                  $tbody.="<td>".$value."</td>";
                  $tbody.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$userPeriodCS[$key]."</span></td>";
                  $tbody.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$userPendingCS[$key]."</span></td>";
                $tbody.="</tr>";
              }
              elseif ($currencyId==CURRENCY_USD){
                $tbody.="<tr>";
                  $tbody.="<td>".$value."</td>";
                  $tbody.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$userPeriodUSD[$key]."</span></td>";
                  $tbody.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$userPendingUSD[$key]."</span></td>";
                $tbody.="</tr>";
              }
            }
          }
          $tbody.="</tbody>";
          $totalRow="";
          if ($currencyId==CURRENCY_CS){
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total C$</td>";
              $totalRow.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCSPeriod."</span></td>";
              $totalRow.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCSPending."</span></td>";
            $totalRow.="</tr>";
          }
          elseif ($currencyId==CURRENCY_USD){
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total US$</td>";
              $totalRow.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalUSDPeriod."</span></td>";
              $totalRow.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalUSDPending."</span></td>";
            $totalRow.="</tr>";
          }
          echo "<h3>Totales por Ejecutivo</h3>";
          echo "<table cellpadding='0' cellspacing='0'>".$thead.$totalRow.$tbody.$totalRow."</table>";
        }
        echo '<p>Colores según tipo de cliente</p>';
        echo '<ul style="list-style:none;">';
          foreach ($clientTypes as $clientTypeId=>$clientTypeName){
            echo '<li style="background-color:#'.$clientTypeHexColors[$clientTypeId].'">'.$clientTypeName.'</li>';
          }
          echo '</ul>';
      echo "</div>";
    echo "</div>";
  echo "</div>";  
	
	$startDateTime=new DateTime($startDate);
	$endDateTime=new DateTime($endDate);
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Quotation'), array('action' => 'crear'))."</li>";
		}
		echo "<br/>";
		if ($bool_client_index_permission){
			echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'clients', 'action' => 'index'))."</li>";
		}
		if ($bool_client_add_permission){
			echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'clients', 'action' => 'add'))."</li>";
		}
		//echo "<li>".$this->Html->link(__('List Contacts'), array('controller' => 'contacts', 'action' => 'index'))."</li>";
		//echo "<li>".$this->Html->link(__('New Contact'), array('controller' => 'contacts', 'action' => 'add'))."</li>";
		if ($bool_salesorder_index_permission){
			echo "<li>".$this->Html->link(__('List Sales Orders'), array('controller' => 'sales_orders', 'action' => 'resumen'))."</li>";
		}
		if ($bool_salesorder_add_permission){
			echo "<li>".$this->Html->link(__('New Sales Order'), array('controller' => 'sales_orders', 'action' => 'crear'))."</li>";
		}
    /*
		if ($bool_invoice_index_permission){
			echo "<li>".$this->Html->link(__('List Invoices'), array('controller' => 'invoices', 'action' => 'index'))."</li>";
		}
		if ($bool_invoice_add_permission){
			echo "<li>".$this->Html->link(__('New Invoice'), array('controller' => 'invoices', 'action' => 'add'))."</li>";
		}
    */
	echo "</ul>";
?>
</div>
<div style='clear:left;'>
<?php
	echo "<p class='comment'>Recordatorio: Cotizaciones parecen en rojo cuando no están marcadas como caídas y no se registraron actividades en los últimos 5 días</p>";
	
	$excelOutput="";

	$pageHeader="";
	$excelHeader="";
	$pageHeader.="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('quotation_date')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('quotation_code')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('bool_sales_order_present','Orden de Venta')."</th>";
			//$pageHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
			//$pageHeader.="<th>".$this->Paginator->sort('contact_id')."</th>";
			//$pageHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('price_subtotal')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('price_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('price_total')."</th>";
			//$pageHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('due_date')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader.="<thead>";
		$excelHeader.="<tr><th colspan='9' align='center'>".COMPANY_NAME."</th></tr>";	
		$excelHeader.="<tr><th colspan='9' align='center'>".__('Resumen de Cotizaciones')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('quotation_date')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('quotation_code')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('bool_sales_order_present','Orden de Venta')."</th>";
			//$excelHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
			//$excelHeader.="<th>".$this->Paginator->sort('contact_id')."</th>";
			//$excelHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('price_subtotal')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('price_iva')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('price_total')."</th>";
			//$excelHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('due_date')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";
	
	$subtotalCS=0;
	$ivaCS=0;
	$totalCS=0;
	$subtotalUSD=0;
	$ivaUSD=0;
	$totalUSD=0;
	foreach ($quotations as $quotation){ 
		//pr($quotation);
		if ($quotation['Quotation']['id']==1188){
			//pr($quotation['QuotationRemark']);
		}
			
		if (
      $salesOrderDisplay == 0 
      ||
      (
        $salesOrderDisplay == 1 && $quotation['Quotation']['bool_sales_order'] 
      )
      ||(
        $salesOrderDisplay == 2 && !$quotation['Quotation']['bool_sales_order']
      )
    ){
			$quotationDate=new DateTime($quotation['Quotation']['quotation_date']);
			$dueDate=new DateTime($quotation['Quotation']['due_date']);
			$subjectText="Cotización ".$quotation['Quotation']['quotation_code']." de Mas Publicidad con fecha ".$quotationDate->format('d-m-Y');
			$bodyText="Estimado cliente, %0D%0A %0D%0AEn adjunto encuentra la cotización solicitado por Usted. %0D%0A %0D%0ACordialmente, %0D%0A %0D%0A".ucwords(strtolower($quotation['VendorUser']['first_name']))." ".ucwords(strtolower($quotation['VendorUser']['last_name'])).",%0D%0A Ejecutivo de Venta Más Publicidad";
			
			if ($quotation['Currency']['id']==CURRENCY_CS){
				$currencyClass=" class='CScurrency'";
				$subtotalCS+=$quotation['Quotation']['price_subtotal'];
				$ivaCS+=$quotation['Quotation']['price_iva'];
				$totalCS+=$quotation['Quotation']['price_total'];
				
				//added calculation of totals in US$
				$subtotalUSD+=round($quotation['Quotation']['price_subtotal']/$quotation['Quotation']['exchange_rate'],2);
				$ivaUSD+=round($quotation['Quotation']['price_iva']/$quotation['Quotation']['exchange_rate'],2);
				$totalUSD+=round($quotation['Quotation']['price_total']/$quotation['Quotation']['exchange_rate'],2);
			}
			elseif ($quotation['Currency']['id']==CURRENCY_USD){
				$currencyClass=" class='USDcurrency'";
				$subtotalUSD+=$quotation['Quotation']['price_subtotal'];
				$ivaUSD+=$quotation['Quotation']['price_iva'];
				$totalUSD+=$quotation['Quotation']['price_total'];
				
				//added calculation of totals in CS$
				$subtotalCS+=round($quotation['Quotation']['price_subtotal']*$quotation['Quotation']['exchange_rate'],2);
				$ivaCS+=round($quotation['Quotation']['price_iva']*$quotation['Quotation']['exchange_rate'],2);
				$totalCS+=round($quotation['Quotation']['price_total']*$quotation['Quotation']['exchange_rate'],2);
			}
			
		$boolShowReminderWarning=false;
			$pageRow="";
				$pageRow.="<td>".$quotationDate->format('d-m-Y')."</td>";
				$pageRow.="<td>".$this->Html->link($quotation['Quotation']['quotation_code'],['action'=>'detalle',$quotation['Quotation']['id']])."</td>";
				//if ($quotation['Quotation']['bool_sales_order_present']){
        if (!empty($quotation['SalesOrder']['id'])){
					//pr($quotation);
					//$pageRow.="<td>".__("Yes")."</td>";
					$pageRow.="<td>".$this->Html->link($quotation['SalesOrder'][0]['sales_order_code'],array('controller'=>'sales_orders','action'=>'detalle',$quotation['SalesOrder'][0]['id']))."</td>";
				}
				elseif ($quotation['Quotation']['bool_rejected']) {
					$pageRow.="<td>Caída</td>";
				}
				else {
					$pageRow.="<td>".__("No")."</td>";
					if (!empty($quotation['QuotationRemark'])){
						if ($quotation['QuotationRemark'][0]['reminder_date']<=date('Y-m-d')){
							$boolShowReminderWarning=true;
						}
					}
				}
				if (empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']){
          $pageRow.="<td>".$quotation['Quotation']['client_name']."</td>";
        }
        else {
          $pageRow.="<td>".$this->Html->link($quotation['Client']['company_name'], ['controller' => 'thirdParties', 'action'  => 'verCliente', $quotation['Client']['id']],['target'=>'_blank'])."</td>";
        }
				$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_subtotal'])."</span></td>";
				$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_iva'])."</span></td>";
				$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_total'])."</span></td>";
        $pageRow.="<td>".$dueDate->format('d-m-Y')."</td>";

				$excelBody.="<tr>".$pageRow."</tr>";
				$filename="Cotización_".$quotation['Quotation']['quotation_code']."_".((empty($quotation['Client']['id']) || $quotation['Client']['bool_generic'])?$quotation['Quotation']['client_name']:$quotation['Client']['company_name']);
          
				$pageRow.="<td class='actions'>";
					if ($bool_edit_permission){
						$pageRow.=$this->Html->link(__('Edit'), array('action' => 'editar', $quotation['Quotation']['id']));
					}
					$pageRow.=$this->Html->link(__('Pdf'), ['action' => 'detallePdf','ext'=>'pdf', $quotation['Quotation']['id'],$filename],['target'=>'_blank','class'=>'pdflink']);
          if (!$quotation['Quotation']['bool_sales_order'] && !$quotation['Quotation']['bool_rejected'] && $bool_crearOrdenVentaExterna_permission){
						$pageRow.=$this->Html->link(__('Crear Orden de Venta'), ['controller'=>'salesOrders','action' => 'crearOrdenVentaExterna', $quotation['Quotation']['id']]);
					}
				$pageRow.="</td>";
			$pageBody.='<tr'.($boolShowReminderWarning?' class="reminderwarning"':'').(empty($quotation['ClientType']['id'])?'-':(empty($quotation['ClientType']['hex_color'])?'':(' style="background-color:#'.$quotation['ClientType']['hex_color'].'"'))).'>'.$pageRow.'</tr>';
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
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
	}
	//if ($subtotalUSD>0){
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
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
	}
	
	$excelTotalRow="";
	if ($subtotalCS>0){
		$excelTotalRow.="<tr class='totalrow'>";
			$excelTotalRow.="<td>Total C$</td>";
			$excelTotalRow.="<td></td>";
			$excelTotalRow.="<td></td>";
			$excelTotalRow.="<td></td>";
			$excelTotalRow.="<td></td>";
			$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCS."</span></td>";
			$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$ivaCS."</span></td>";
			$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
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
			$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$subtotalUSD."</td>";
			$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$ivaUSD."</td>";
			$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalUSD."</td>";
			$excelTotalRow.="<td></td>";
		$excelTotalRow.="</tr>";
	}

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$excelBody="<tbody>".$excelTotalRow.$excelBody.$excelTotalRow."</tbody>";
	$table_id="cotizaciones";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	
	
	echo $pageOutput;
	$excelOutput.="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	
	if (!empty($pendingQuotations)){
		$pageHeader="";
		$excelHeader="";
		$pageHeader.="<thead>";
			$pageHeader.="<tr>";
				$pageHeader.="<th>".$this->Paginator->sort('quotation_date')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('quotation_code')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('bool_sales_order','Orden de Venta')."</th>";
				//$pageHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
				//$pageHeader.="<th>".$this->Paginator->sort('contact_id')."</th>";
				//$pageHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('price_subtotal')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('price_iva')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('price_total')."</th>";
				//$pageHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
				$pageHeader.="<th>".$this->Paginator->sort('due_date')."</th>";
				$pageHeader.="<th class='actions'>".__('Actions')."</th>";
			$pageHeader.="</tr>";
		$pageHeader.="</thead>";
		$excelHeader.="<thead>";
			$excelHeader.="<tr><th colspan='9' align='center'>".COMPANY_NAME."</th></tr>";	
			$excelHeader.="<tr><th colspan='9' align='center'>".__('Resumen de Cotizaciones')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
			$excelHeader.="<tr>";
				$excelHeader.="<th>".$this->Paginator->sort('quotation_date')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('quotation_code')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('bool_sales_order','Orden de Venta')."</th>";
				//$excelHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
				//$excelHeader.="<th>".$this->Paginator->sort('contact_id')."</th>";
				//$excelHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('price_subtotal')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('price_iva')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('price_total')."</th>";
				//$excelHeader.="<th>".$this->Paginator->sort('currency_id')."</th>";
				$excelHeader.="<th>".$this->Paginator->sort('due_date')."</th>";
			$excelHeader.="</tr>";
		$excelHeader.="</thead>";

		$pageBody="";
		$excelBody="";
		
		$subtotalCS=0;
		$ivaCS=0;
		$totalCS=0;
		$subtotalUSD=0;
		$ivaUSD=0;
		$totalUSD=0;
		foreach ($pendingQuotations as $quotation){ 
			if ($salesOrderDisplay==0||($salesOrderDisplay==1&&$quotation['Quotation']['bool_sales_order'])||($salesOrderDisplay==2&&!$quotation['Quotation']['bool_sales_order'])){
				$quotationDate=new DateTime($quotation['Quotation']['quotation_date']);
				$dueDate=new DateTime($quotation['Quotation']['due_date']);
				$subjectText="Cotización ".$quotation['Quotation']['quotation_code']." de Mas Publicidad con fecha ".$quotationDate->format('d-m-Y');
				$bodyText="Estimado cliente, %0D%0A %0D%0AEn adjunto encuentra la cotización solicitado por Usted. %0D%0A %0D%0ACordialmente, %0D%0A %0D%0A".ucwords(strtolower($quotation['VendorUser']['first_name']))." ".ucwords(strtolower($quotation['VendorUser']['last_name'])).",%0D%0A Ejecutivo de Venta Más Publicidad";
				
				if ($quotation['Currency']['id']==CURRENCY_CS){
					$currencyClass=" class='CScurrency'";
					$subtotalCS+=$quotation['Quotation']['price_subtotal'];
					$ivaCS+=$quotation['Quotation']['price_iva'];
					$totalCS+=$quotation['Quotation']['price_total'];
					
					//added calculation of totals in US$
					$subtotalUSD+=round($quotation['Quotation']['price_subtotal']/$quotation['Quotation']['exchange_rate'],2);
					$ivaUSD+=round($quotation['Quotation']['price_iva']/$quotation['Quotation']['exchange_rate'],2);
					$totalUSD+=round($quotation['Quotation']['price_total']/$quotation['Quotation']['exchange_rate'],2);
				}
				elseif ($quotation['Currency']['id']==CURRENCY_USD){
					$currencyClass=" class='USDcurrency'";
					$subtotalUSD+=$quotation['Quotation']['price_subtotal'];
					$ivaUSD+=$quotation['Quotation']['price_iva'];
					$totalUSD+=$quotation['Quotation']['price_total'];
					
					//added calculation of totals in CS$
					$subtotalCS+=round($quotation['Quotation']['price_subtotal']*$quotation['Quotation']['exchange_rate'],2);
					$ivaCS+=round($quotation['Quotation']['price_iva']*$quotation['Quotation']['exchange_rate'],2);
					$totalCS+=round($quotation['Quotation']['price_total']*$quotation['Quotation']['exchange_rate'],2);
				}
				
				$boolShowReminderWarning=false;
				
				$pageRow="";
					$pageRow.="<td>".$quotationDate->format('d-m-Y')."</td>";
					$pageRow.="<td>".$this->Html->link($quotation['Quotation']['quotation_code'],array('action'=>'detalle',$quotation['Quotation']['id']))."</td>";
					if ($quotation['Quotation']['bool_sales_order']){
						$pageRow.="<td>".__("Yes")."</td>";
					}
					elseif ($quotation['Quotation']['bool_rejected']) {
						$pageRow.="<td>Caída</td>";
					}
					else {
						$pageRow.="<td>".__("No")."</td>";
						if (!empty($quotation['QuotationRemark'])){
							if ($quotation['QuotationRemark'][0]['reminder_date']<=date('Y-m-d')){
								$boolShowReminderWarning=true;
							
							}
						}
					}					
					if (empty($quotation['Client']['id']) || $quotation['Client']['bool_generic']){
            $pageRow.="<td>".$quotation['Quotation']['client_name']."</td>";
          }
          else {
            $pageRow.="<td>".$this->Html->link($quotation['Client']['company_name'], ['controller' => 'thirdParties', 'action'  => 'verCliente', $quotation['Client']['id']],['target'=>'_blank'])."</td>";
          }
					$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_subtotal'])."</span></td>";
					$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_iva'])."</span></td>";
					$pageRow.="<td".$currencyClass."><span class='currency'>".$quotation['Currency']['abbreviation']."</span><span class='amountright'>".h($quotation['Quotation']['price_total'])."</span></td>";					
					$pageRow.="<td>".$dueDate->format('d-m-Y')."</td>";

          $excelBody.="<tr>".$pageRow."</tr>";
          $filename="Cotización_".$quotation['Quotation']['quotation_code']."_".((empty($quotation['Client']['id']) || $quotation['Client']['bool_generic'])?$quotation['Quotation']['client_name']:$quotation['Client']['company_name']);
            
					$pageRow.="<td class='actions'>";
						if ($bool_edit_permission){
							$pageRow.=$this->Html->link(__('Edit'), array('action' => 'editar', $quotation['Quotation']['id']));
						}
						$pageRow.=$this->Html->link(__('Pdf'), ['action' => 'detallePdf','ext'=>'pdf', $quotation['Quotation']['id'],$filename],['target'=>'_blank']);
						if (!$quotation['Quotation']['bool_sales_order']&&!$quotation['Quotation']['bool_rejected']){
							$pageRow.=$this->Html->link(__('Orden de Venta'), array('controller'=>'sales_orders','action' => 'crearOrdenVentaExterna', $quotation['Quotation']['id']));
						}
					$pageRow.="</td>";
				
				if ($boolShowReminderWarning){
					$pageBody.="<tr class='reminderwarning'>".$pageRow."</tr>";
				}
				else {
					$pageBody.="<tr>".$pageRow."</tr>";
				}
			}
		}

		$pageTotalRow="";
		//if ($subtotalCS>0){
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
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
			$pageTotalRow.="</tr>";
		}
		//if ($subtotalUSD>0){
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
				$pageTotalRow.="<td></td>";
				$pageTotalRow.="<td></td>";
			$pageTotalRow.="</tr>";
		}
		
		$excelTotalRow="";
		if ($subtotalCS>0){
			$excelTotalRow.="<tr class='totalrow'>";
				$excelTotalRow.="<td>Total C$</td>";
				$excelTotalRow.="<td></td>";
				$excelTotalRow.="<td></td>";
				$excelTotalRow.="<td></td>";
				$excelTotalRow.="<td></td>";
				$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCS."</span></td>";
				$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$ivaCS."</span></td>";
				$excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
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
				$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$subtotalUSD."</td>";
				$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$ivaUSD."</td>";
				$excelTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalUSD."</td>";
				$excelTotalRow.="<td></td>";
			$excelTotalRow.="</tr>";
		}

		$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
		$excelBody="<tbody>".$excelTotalRow.$excelBody.$excelTotalRow."</tbody>";
		$table_id="cotizaciones_pendientes";
		$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
		echo "<h4>Cotizaciones no marcadas como caídas sin ordenes de venta de meses anteriores</h4>";
		echo $pageOutput;
		$excelOutput.="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	}
	
	$_SESSION['resumenQuotations'] = $excelOutput;
?>
</div>