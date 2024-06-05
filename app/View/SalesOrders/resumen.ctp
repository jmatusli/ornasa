<script>
	$('body').on('change','.powerselector',function(e){
		if ($(this).is(':checked')){
			$('#Ordenes_de_Venta').find('td.selector div input[type="checkbox"]').prop('checked',true);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',true);
		}
		else {
			$('#Ordenes_de_Venta').find('td.selector div input[type="checkbox"]').prop('checked',false);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',false);
		}
	});

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
	function formatPercentages(){
		$("td.percentage").each(function(){
			//if (parseFloat($(this).find('.amountright').text())<0){
			//	$(this).find('.amountright').prepend("-");
			//}
			$(this).find('.amountright').number(true,2);
			$(this).find('.amountright').append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>

<div class="salesOrders index">
<?php 
	echo "<h2>".__('Sales Orders')."</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-sm-8 col-lg-6'>";		
        echo $this->Form->create('Report');
          echo "<fieldset>";
            echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2013,'maxYear'=>date('Y')]);
            echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2013,'maxYear'=>date('Y')]);
            echo "<br/>";
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            if ($userRoleId==ROLE_ADMIN || $canSeeAllUsers || $canSeeAllVendors) { 
              echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Usuario'),'options'=>$users,'default'=>0,'empty'=>['0'=>__('Todos Usuarios')]]);
            }
            else {
              echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Usuario'),'options'=>$users,'default'=>$userId]);
            }
            echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'default'=>$currencyId]);			
            
            echo $this->Form->input('Report.client_type_id',['default'=>$clientTypeId,'empty'=>[0=>'-- Tipo de cliente --']]);
            echo $this->Form->input('Report.zone_id',['default'=>$zoneId,'empty'=>[0=>'-- Zone --']]);
            
            echo $this->Form->input('Report.vehicle_id',['default'=>$vehicleId,'empty'=>[0=>'-- Vehículo --']]);
            echo $this->Form->input('Report.driver_user_id',['default'=>$driverUserId,'empty'=>[0=>'-- Conductor --']]);
            
            echo $this->Form->input('Report.invoice_display',['label'=>__('Mostrar Facturas'),'options'=>$invoiceOptions,'default'=>$invoiceDisplay]);
            echo $this->Form->input('Report.authorized_display',['label'=>__('Mostrar Autorizados'),'options'=>$authorizedOptions,'default'=>$authorizedDisplay]);
          echo "</fieldset>";
          echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
          echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
        echo "<br/>";	
        echo $this->Form->submit(__('Refresh'),['name'=>'refresh', 'id'=>'refresh','div'=>['class'=>'submit']]); 
        echo $this->Form->submit(__('Autorizar todas Ordenes de Venta seleccionados'),['name'=>'authorize_all', 'id'=>'authorize_all','style'=>'width:30em;','div'=>['class'=>'submit']]); 
        echo "<br/>";	
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], [ 'class' => 'btn btn-primary']);
      echo "</div>";
      echo "<div class='col-sm-4 col-lg-6'>";		
        if ($warehouseId>0){
          if ($userRoleId==ROLE_ADMIN || $canSeeExecutiveSummary) {  
            echo "<h3>Totales por Ejecutivo</h3>";
            $thead="";  
            $thead.="<thead>";
              $thead.="<tr>";
                $thead.="<th>Ejecutivo</th>";
                $thead.="<th>Total Período</th>";
                //$thead.="<th>Total Pendiente</th>";
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
                //$totalCSPending+=$userPendingCS[$key]; 
                //$totalUSDPending+=$userPendingUSD[$key];
                if ($currencyId==CURRENCY_CS){
                  $tbody.="<tr>";
                    $tbody.="<td>".$value."</td>";
                    $tbody.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$userPeriodCS[$key]."</span></td>";
                    //$tbody.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$userPendingCS[$key]."</span></td>";
                  $tbody.="</tr>";
                }
                elseif ($currencyId==CURRENCY_USD){
                  $tbody.="<tr>";
                    $tbody.="<td>".$value."</td>";
                    $tbody.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$userPeriodUSD[$key]."</span></td>";
                    //$tbody.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$userPendingUSD[$key]."</span></td>";
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
                //$totalRow.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>>".$totalCSPending."</span></td>";
              $totalRow.="</tr>";
            }
            elseif ($currencyId==CURRENCY_USD){
              $totalRow.="<tr class='totalrow'>";
                $totalRow.="<td>Total US$</td>";
                $totalRow.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>>".$totalUSDPeriod."</span></td>";
                //$totalRow.="<td class='USDcurrency'><span class='currency'>US$ </span><span class='amountright'>".$totalUSDPending."</span></td>";
              $totalRow.="</tr>";
            }
            echo "<table cellpadding='0' cellspacing='0'>".$thead.$totalRow.$tbody.$totalRow."</table>";
          }
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
		//	echo "<li>".$this->Html->link(__('New Sales Order'), ['action' => 'crear'])."</li>";
		}
    if ($bool_crear_orden_venta_externa_permission){
			echo "<li>".$this->Html->link(__('Nueva Orden de Venta Externa'), ['action' => 'crearOrdenVentaExterna'])."</li>";
		}
		echo "<br/>";
		if ($bool_quotation_index_permission){
			echo "<li>".$this->Html->link(__('List Quotations'), ['controller' => 'quotations', 'action' => 'resumen'])."</li>";
		}
		if ($bool_quotation_add_permission){
			echo "<li>".$this->Html->link(__('New Quotation'), ['controller' => 'quotations', 'action' => 'crear'])."</li>";
		}
	echo "</ul>";
?>
</div>
<div style='clear:left;'>
<?php
  echo "<br/>";
  if ($warehouseId == 0){
    echo '<h2 style="clear:left;">Por favor seleccione una bodega para ver datos</h2>';
  }
	else {
    echo $this->Form->input('powerselector1',['class'=>'powerselector','checked'=>true,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar Ordenes de Venta para autorizar','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ]]);

    $tableHeader="";
    $excelHeader="";
    $tableHeader.="<thead>";
      $tableHeader.="<tr>";
        if ($bool_autorizar_permission){
          $tableHeader.="<th>Seleccione</th>";
        }
        if ($userId == 0){
          $tableHeader.="<th>".$this->Paginator->sort('vendor_user_id','Vendedor')."</th>";
        }
        $tableHeader.="<th>".$this->Paginator->sort('sales_order_date','Fecha')."</th>";
        $tableHeader.="<th>".$this->Paginator->sort('sales_order_code','#')."</th>";
        $tableHeader.="<th>Factura</th>";
        $tableHeader.="<th>".$this->Paginator->sort('Quotation.quotation_code','Cotización')."</th>";
        //$tableHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
        $tableHeader.="<th>Cliente</th>";
        $tableHeader.="<th class='centered'>".$this->Paginator->sort('price_subtotal')."</th>";
        $tableHeader.="<th class='actions'>".__('Actions')."</th>";
      $tableHeader.="</tr>";
    $tableHeader.="</thead>";
    $excelHeader.="<thead>";		
      if ($userId==0){
        $excelHeader.="<tr><th colspan='6' align='center'>".COMPANY_NAME."</th></tr>";	
        $excelHeader.="<tr><th colspan='6' align='center'>".__('Resumen de Ordenes de Venta')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
        $excelHeader.="<tr>";
          $excelHeader.="<th>".$this->Paginator->sort('user_id','Vendedor')."</th>";
      }
      else {
        $excelHeader.="<tr><th colspan='6' align='center'>".COMPANY_NAME."</th></tr>";	
        $excelHeader.="<tr><th colspan='6' align='center'>".__('Resumen de Ordenes de Venta')." de ".$startDateTime->format('d-m-Y')." hasta ".$endDateTime->format('d-m-Y')."</th></tr>";
        $excelHeader.="<tr>";
      }
        $excelHeader.="<th>".$this->Paginator->sort('sales_order_date')."</th>";
        $excelHeader.="<th>".$this->Paginator->sort('sales_order_code')."</th>";
        $excelHeader.="<th>Factura</th>";
        $excelHeader.="<th>".$this->Paginator->sort('quotation_id')."</th>";
        $excelHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
        $excelHeader.="<th>".$this->Paginator->sort('price_subtotal')."</th>";
        $excelHeader.="<th>Estado</th>";
      $excelHeader.="</tr>";
    $excelHeader.="</thead>";

    $pageBody="";
    $excelBody="";

    $subtotalCS=0;
    $subtotalUSD=0;
    $statustotalCS=0;
    $statustotalUSD=0;
    
    foreach ($salesOrders as $salesOrder){ 
      if (
      (
        $invoiceDisplay == SALESORDERS_ALL 
        || (
        $invoiceDisplay == SALESORDERS_WITH_INVOICE && $salesOrder['SalesOrder']['bool_invoice'])
        || ($invoiceDisplay == SALESORDERS_WITHOUT_INVOICE && !$salesOrder['SalesOrder']['bool_invoice'])
      )
      &&
      (
        $authorizedDisplay == SALESORDERS_ALL 
        || ($authorizedDisplay == SALESORDERS_WITH_AUTHORIZATION && $salesOrder['SalesOrder']['bool_authorized'])
        ||($authorizedDisplay == SALESORDERS_WITHOUT_AUTHORIZATION && !$salesOrder['SalesOrder']['bool_authorized'])
      )
      ){
        $salesOrderDateTime=new DateTime($salesOrder['SalesOrder']['sales_order_date']);
        
        if ($salesOrder['Currency']['id']==CURRENCY_CS){
          $currencyClass=" class='CScurrency'";
          $subtotalCS+=$salesOrder['SalesOrder']['price_subtotal'];
          
          //added calculation of totals in US$
          $subtotalUSD+=round($salesOrder['SalesOrder']['price_subtotal']/$salesOrder['SalesOrder']['exchange_rate'],2);
        }
        elseif ($salesOrder['Currency']['id']==CURRENCY_USD){
          $currencyClass=" class='USDcurrency'";
          $subtotalUSD+=$salesOrder['SalesOrder']['price_subtotal'];
          
          //added calculation of totals in CS$
          $subtotalCS+=round($salesOrder['SalesOrder']['price_subtotal']*$salesOrder['SalesOrder']['exchange_rate'],2);
        }
        //echo "salesorder currency is ".$salesOrder['Currency']['id']."<br/>";
        //echo "subtotal for salesorder is ".$salesOrder['SalesOrder']['price_subtotal']."<br/>";
        //echo "running subtotal C$ is ".$subtotalCS."<br/>";
        //echo "running subtotal US$ is ".$subtotalUSD."<br/>";
                
        $pageRow="";
          if ($bool_autorizar_permission){
            if (!$salesOrder['SalesOrder']['bool_authorized']){
              $pageRow.="<td class='selector'>".$this->Form->input('Report.selector.'.$salesOrder['SalesOrder']['id'],['checked'=>true,'label'=>false])."</td>";
            }
            else {
              //$pageRow.="<td class='fixedselector'>".$this->Form->input('Report.selector.'.$salesOrder['SalesOrder']['id'],['checked'=>true,'disabled'=>'disabled','label'=>false,'onclick'=>'return false;'])."</td>";
              $pageRow.="<td class='fixedselector'>&nbsp;</td>";
            }
          }
          if ($userId == 0){
            $pageRow.="<td>".$this->Html->link($salesOrder['VendorUser']['first_name'].' '.$salesOrder['VendorUser']['last_name'], ['controller' => 'users', 'action' => 'view', $salesOrder['VendorUser']['id']])."</td>";
          }
          $pageRow.="<td>".$salesOrderDateTime->format('d-m-Y')."</td>";
          $pageRow.="<td>".$this->Html->Link($salesOrder['SalesOrder']['sales_order_code'].($salesOrder['SalesOrder']['bool_annulled']?" (Anulada)":""),['action'=>'detalle',$salesOrder['SalesOrder']['id']])."</td>";
          $pageRow.="<td>".(empty($salesOrder['Invoice']['id'])?"-":$this->Html->link($salesOrder['Invoice']['invoice_code'],['controller'=>'orders','action'=>'verVenta',$salesOrder['Invoice']['order_id']]))."</td>";
          
          if(!empty($salesOrder['Quotation'])){
            $pageRow.="<td>".$this->Html->link($salesOrder['Quotation']['quotation_code'], ['controller' => 'quotations', 'action' => 'detalle', $salesOrder['Quotation']['id']])."</td>";
          }
          if (empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic']){
            $pageRow.="<td>".$salesOrder['SalesOrder']['client_name']."</td>";
          }
          else {
            $pageRow.="<td>".$this->Html->link($salesOrder['Client']['company_name'], ['controller' => 'thirdParties', 'action' => 'verCliente', $salesOrder['Client']['id']])."</td>";
            
          }
          $pageRow.="<td".$currencyClass."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($salesOrder['SalesOrder']['price_subtotal'])."</span></td>";
          if ($salesOrder['SalesOrder']['bool_annulled']){
            $excelBody.="<tr class='italic'>".$pageRow."</tr>";
          }
          else {
            $excelBody.="<tr>".$pageRow."</tr>";
          }

          $pageRow.="<td class='actions'>";
            $fileName="Orden de Venta_".$salesOrder['SalesOrder']['sales_order_code']."_".((empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic'])?$salesOrder['SalesOrder']['client_name']:$salesOrder['Client']['company_name']);
            //$pageRow.=$this->Html->link(__('View'), ['action' => 'view', $salesOrder['SalesOrder']['id']));
            if ($bool_edit_permission){
              //$pageRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $salesOrder['SalesOrder']['id']]);
              //$pageRow.=$this->Form->postLink(__('Delete'), ['action' => 'delete', $salesOrder['SalesOrder']['id']], [], __('Está seguro que quiere eliminar orden de venta #%s?', $salesOrder['SalesOrder']['sales_order_code']));
            }
            if ($bool_autorizar_permission&&!$salesOrder['SalesOrder']['bool_authorized']){
              $pageRow.=$this->Html->link(__('Autorizar'), ['action' => 'autorizar', $salesOrder['SalesOrder']['id']], ['confirm'=>__('Está seguro que quiere autorizar Orden de Venta # %s?', $salesOrder['SalesOrder']['sales_order_code'])]);
            }
            //$pageRow.=$this->Form->postLink(__('Anular'), ['action' => 'annul', $salesOrder['SalesOrder']['id']), [), __('Está seguro que quiere anular Orden de Venta # %s?', $salesOrder['SalesOrder']['sales_order_code']));
            $pageRow.=$this->Html->link(__('Pdf'), ['action' => 'detallePdf','ext'=>'pdf', $salesOrder['SalesOrder']['id'],$fileName],['target'=>'_blank']);
          $pageRow.="</td>";
        
        $pageBody.='<tr'.($salesOrder['SalesOrder']['bool_annulled']?' class="italic"':'').(empty($salesOrder['ClientType']['id'])?'-':(empty($salesOrder['ClientType']['hex_color'])?'':(' style="background-color:#'.$salesOrder['ClientType']['hex_color'].'"'))).'>'.$pageRow.'</tr>';
        
      }
    }
    $pageTotalRow="";
    if ($currencyId==CURRENCY_CS){
      $pageTotalRow.="<tr class='totalrow'>";
        if ($bool_autorizar_permission){
          $pageTotalRow.="<td></td>";
        }
        $pageTotalRow.="<td>Total C$</td>";
        if ($userId == 0){
          $pageTotalRow.="<td></td>";
        }
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$subtotalCS."</span></td>";
        $status=0;
        if ($subtotalCS>0){
          $status=$statustotalCS/$subtotalCS;
        }
        $pageTotalRow.="<td class='percentage'><span class='amountright'>".$status."</span></td>";
        $pageTotalRow.="<td></td>";
      $pageTotalRow.="</tr>";
    }
    if ($currencyId==CURRENCY_USD){
      $pageTotalRow.="<tr class='totalrow'>";
        if ($bool_autorizar_permission){
          $pageTotalRow.="<td></td>";
        }
        $pageTotalRow.="<td>Total US$</td>";
        if ($userId==0){
          $pageTotalRow.="<td></td>";
        }
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td></td>";
        $pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$subtotalUSD."</span></td>";
        $status=0;
        if ($subtotalUSD>0){
          $status=$statustotalUSD/$subtotalUSD;
        }
        $pageTotalRow.="<td></td>";
      $pageTotalRow.="</tr>";
    }
    $pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
    $table_id="Ordenes_de_Venta";
    $pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$tableHeader.$pageBody."</table>";
    echo $pageOutput;
    
    echo $this->Form->input('powerselector2',['class'=>'powerselector','checked'=>true,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar Ordenes de Venta para autorizar','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ]]);
    
    echo $this->Form->end(); 
	
	
    $excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
    $_SESSION['resumen'] = $excelOutput;
  }  
?>
</div>