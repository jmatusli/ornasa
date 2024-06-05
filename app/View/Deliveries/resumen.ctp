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
<?php 
  $deliveryTables='';
  $excelOutput='';
  
  foreach ($deliveriesByStatus as $currentDeliveryStatusId => $currentDeliveryStatusData){
    $deliveryTableHead='';
    $deliveryTableHead.='<thead>';
      $deliveryTableHead.='<tr>';
      if ($currentDeliveryStatusId == DELIVERY_STATUS_PROGRAMMED && $boolCanRegisterDelivery){
        $deliveryTableHead.='<th class="actions"></th>';
      } 
      if (in_array($currentDeliveryStatusId,[DELIVERY_STATUS_PROGRAMMED,DELIVERY_STATUS_DELIVERED])){
        $deliveryTableHead.='<th># Entrega</th>';
      }  
        $deliveryTableHead.='<th># OV</th>';
        $deliveryTableHead.='<th># Factura</th>';
        $deliveryTableHead.='<th>Cliente</th>';
      if (in_array($currentDeliveryStatusId,[DELIVERY_STATUS_PROGRAMMED,DELIVERY_STATUS_DELIVERED])){  
        $deliveryTableHead.='<th>Fecha programada</th>';
      }
      if ($currentDeliveryStatusId == DELIVERY_STATUS_DELIVERED){
        $deliveryTableHead.='<th>Fecha efectiva</th>';
      }
        //$deliveryTableHead.='<th>Dirección</th>';
      if ($currentDeliveryStatusId == DELIVERY_STATUS_UNASSIGNED){
        $deliveryTableHead.='<th class="actions"></th>';
      }  
      if (in_array($currentDeliveryStatusId,[DELIVERY_STATUS_PROGRAMMED,DELIVERY_STATUS_DELIVERED])){  
        if ($userRoleId != ROLE_DRIVER){
          $deliveryTableHead.='<th>Conductor</th>';
        }
        $deliveryTableHead.='<th>Vehículo</th>';
      }
      
      $deliveryTableHead.='</tr>';
    $deliveryTableHead.='</thead>';
    
    $deliveryTableRows='';
    if ($currentDeliveryStatusId == DELIVERY_STATUS_UNASSIGNED){
      if (!empty($currentDeliveryStatusData['SalesOrder'])){
        foreach ($currentDeliveryStatusData['SalesOrder'] as $salesOrder){
          //$deliveryDateTime=new Datetime($salesOrder['SalesOrder']['delivery_datetime']);
          $deliveryTableRows.='<tr>';
           $deliveryTableRows.='<td>'.(empty($salesOrder['SalesOrder']['id'])?"-":($boolSalesOrderDetail?$this->Html->link($salesOrder['SalesOrder']['sales_order_code'], ['controller' => 'salesOrders', 'action' => 'detalle',$salesOrder['SalesOrder']['id']]):$salesOrder['SalesOrder']['sales_order_code'])).'</td>';
            $deliveryTableRows.='<td>-</td>';
            $deliveryTableRows.='<td>'.$salesOrder['SalesOrder']['client_name'].'</td>';
            //$deliveryTableRows.='<td>'.$deliveryDateTime->format('d-m-Y H:i').'</td>';
            $deliveryTableRows.='<td class="actions">';
              $deliveryTableRows.=$this->Html->link('Crear Orden de Entrega', ['controller'=>'deliveries','action' => 'crear', $salesOrder['SalesOrder']['id']],['class' => 'btn btn-primary']);
            $deliveryTableRows.='</td>';
            //$deliveryTableRows.='<td>'.h($salesOrder['SalesOrder']['delivery_address']).'&nbsp;</td>';
          $deliveryTableRows.='</tr>';
        }
      }
      if (!empty($currentDeliveryStatusData['Order'])){
        foreach ($currentDeliveryStatusData['Order'] as $order){
          //pr($order);
          //pr($order['Invoice']);
          $deliveryTableRows.='<tr>';
            $deliveryTableRows.='<td>'.(empty($order['Invoice'][0]['SalesOrder'])?"-":($boolSalesOrderDetail?$this->Html->link($order['Invoice'][0]['SalesOrder']['sales_order_code'], ['controller' => 'salesOrders', 'action' => 'detalle',$order['Invoice'][0]['SalesOrder']['id']]):$salesOrder['SalesOrder']['sales_order_code'])).'</td>';
            $deliveryTableRows.='<td>'.(empty($order['Order']['id'])?"-":($boolOrderDetail?$this->Html->link($order['Order']['order_code'], ['controller' => 'orders', 'action' => 'verVenta', $order['Order']['id']]):$order['Order']['order_code'])).' </td>';
            $deliveryTableRows.='<td>'.$order['Order']['client_name'].'</td>';
            //$deliveryTableRows.='<td>'.$deliveryDateTime->format('d-m-Y H:i').'</td>';
            $deliveryTableRows.='<td class="actions">';
              if ($bool_add_permission){
                $deliveryTableRows.=$this->Html->link('Crear Orden de Entrega', ['controller'=>'deliveries','action' => 'crear', (empty($order['Invoice'][0]['SalesOrder'])?0:$order['Invoice'][0]['SalesOrder']['id']), $order['Order']['id']],['class' => 'btn btn-primary']);
              }
            $deliveryTableRows.='</td>';
            //$deliveryTableRows.='<td>'.h($order['Order']['delivery_address']).'&nbsp;</td>';
            
          $deliveryTableRows.='</tr>';
        }
      }
    }
    else {
      foreach ($currentDeliveryStatusData['Delivery'] as $delivery){
        $plannedDeliveryDateTime=new Datetime($delivery['Delivery']['planned_delivery_datetime']);
        if ($currentDeliveryStatusId == DELIVERY_STATUS_DELIVERED){
          $actualDeliveryDateTime=new Datetime($delivery['Delivery']['actual_delivery_datetime']);
        }
      
        $deliveryTableRows.='<tr>';
        if ($currentDeliveryStatusId == DELIVERY_STATUS_PROGRAMMED && $boolCanRegisterDelivery){
          $deliveryTableRows.='<td class="actions">'.$this->Html->link('Registrar Entrega', ['controller'=>'deliveries','action' => 'registrarEntregaAlCliente',$delivery['Delivery']['id']],['class' => 'btn btn-primary']).'</td>';
        }
        if (in_array($currentDeliveryStatusId,[DELIVERY_STATUS_PROGRAMMED,DELIVERY_STATUS_DELIVERED])){
          $deliveryTableRows.='<td>'.$this->Html->link($delivery['Delivery']['delivery_code'],['action' => 'detalle',$delivery['Delivery']['id']]).'&nbsp;</td>';
        }
          $deliveryTableRows.='<td>'.(empty($delivery['SalesOrder']['id'])?"-":($boolSalesOrderDetail?$this->Html->link($delivery['SalesOrder']['sales_order_code'], ['controller' => 'salesOrders', 'action' => 'detalle',$delivery['SalesOrder']['id']]):$delivery['SalesOrder']['sales_order_code'])).'</td>';
          $deliveryTableRows.='<td>'.(empty($delivery['Order']['id'])?"-":($boolOrderDetail?$this->Html->link($delivery['Order']['order_code'], ['controller' => 'orders', 'action' => 'verVenta', $delivery['Order']['id']]):$delivery['Order']['order_code'])).' </td>';
          $deliveryTableRows.='<td>'.(empty($delivery['Order']['id'])?$delivery['SalesOrder']['client_name']:$delivery['Order']['client_name']).' </td>';
          $deliveryTableRows.='<td>'.$plannedDeliveryDateTime->format('d-m-Y H:i').'</td>';
        if ($currentDeliveryStatusId == DELIVERY_STATUS_DELIVERED){
          $deliveryTableRows.='<td>'.$actualDeliveryDateTime->format('d-m-Y H:i').'</td>';
        }
          //$deliveryTableRows.='<td>'.h($delivery['Delivery']['delivery_address']).'&nbsp;</td>';
        if (in_array($currentDeliveryStatusId,[DELIVERY_STATUS_PROGRAMMED,DELIVERY_STATUS_DELIVERED])) {
          if ($userRoleId != ROLE_DRIVER){
            $deliveryTableRows.='<td>'.$this->Html->link($delivery['DriverUser']['first_name'].' '.$delivery['DriverUser']['last_name'], ['controller' => 'users', 'action' => 'view', $delivery['DriverUser']['id']]).'</td>';
          }
          $deliveryTableRows.='<td>'.($boolVehicleDetail?$this->Html->link($delivery['Vehicle']['name'], ['controller' => 'vehicles', 'action' => 'detalle', $delivery['Vehicle']['id']]):$delivery['Vehicle']['name']).'</td>';
        }
        
        $deliveryTableRows.='</tr>';
      }
    }
     
    $deliveryTableBody='<tbody>'.$deliveryTableRows.'</tbody>';
    $tableId='entregas_'.$deliveryStatuses[$currentDeliveryStatusId];
    $deliveryTable='<table id="'.$tableId.'">'.$deliveryTableHead.$deliveryTableBody.'</table>';
    if ($currentDeliveryStatusId != DELIVERY_STATUS_UNASSIGNED || !in_array($userRoleId,[ROLE_DRIVER])){
      $excelOutput.=$deliveryTable;
      $deliveryTables.='<h2>Entregas a Domicilio _ '.$deliveryStatuses[$currentDeliveryStatusId].'</h2>';
      $deliveryTables.=$deliveryTable;
    }
  }
  if ($userRoleId == ROLE_ADMIN || $canSeeExecutiveSummary){
    $executiveTableHead='';  
    $executiveTableHead.='<thead>';
      $executiveTableHead.='<tr>';
        $executiveTableHead.='<th>Conductor</th>';
        $executiveTableHead.='<th>Botellas</th>';
        $executiveTableHead.='<th>Botellas por día</th>';
      $executiveTableHead.='</tr>';
    $executiveTableHead.='</thead>';
    
    $executiveTableRows='';
    //pr($deliveriesByDriverUser);
    foreach ($deliveriesByDriverUser as $driverUserId=>$driverUserData){
      if ($driverUserId > 0){
        $executiveTableRows.='<tr>';
          $executiveTableRows.='<td>'.$driverUsers[$driverUserId].'</td>';
          $executiveTableRows.='<td class="number"><span class="amountright">'.$driverUserData['quantity_products'].'</span></td>';
          $executiveTableRows.='<td class="number"><span class="amountright">'.($driverUserData['quantity_products']/$workingDays).'</span></td>';
        $executiveTableRows.='</tr>';
      }
    }
    $executiveTotalRow='';
    $executiveTableRows.='<tr class="totalrow">';
      $executiveTableRows.='<td>Total</td>';
      $executiveTableRows.='<td class="number"><span class="amountright">'.$deliveriesByDriverUser[0]['quantity_products'].'</td>';
      $executiveTableRows.='<td class="number"><span class="amountright">'.($deliveriesByDriverUser[0]['quantity_products']/$workingDays).'</span></td>';
    $executiveTableRows.='</tr>';
    
    $executiveTableBody='<tbody>'.$executiveTotalRow.$executiveTableRows.$executiveTotalRow.'</tbody>';							
    
    $executiveSummaryDriverTable='<table id="ejecutivo_entregas_x_conductor">'.$executiveTableHead.$executiveTableBody.'</table>';
    $excelOutput.=$executiveSummaryDriverTable;   
    
    $executiveTableHead='';  
    $executiveTableHead.='<thead>';
      $executiveTableHead.='<tr>';
        $executiveTableHead.='<th>Vehículo</th>';
        $executiveTableHead.='<th>Botellas</th>';
        $executiveTableHead.='<th>Botellas por día</th>';
      $executiveTableHead.='</tr>';
    $executiveTableHead.='</thead>';
    
    $executiveTableRows='';
    //pr($deliveriesByVehicle);
    foreach ($deliveriesByVehicle as $vehicleId=>$vehicleData){
      if ($vehicleId > 0){
        $executiveTableRows.='<tr>';
          $executiveTableRows.='<td>'.$vehicles[$vehicleId].'</td>';
          $executiveTableRows.='<td class="number"><span class="amountright">'.$vehicleData['quantity_products'].'</td>';
          $executiveTableRows.='<td class="number"><span class="amountright">'.($vehicleData['quantity_products']/$workingDays).'</span></td>';
        $executiveTableRows.='</tr>';
      }
    }
    $executiveTotalRow='';
    $executiveTableRows.='<tr class="totalrow">';
      $executiveTableRows.='<td>Total</td>';
      $executiveTableRows.='<td class="number"><span class="amountright">'.$deliveriesByVehicle[0]['quantity_products'].'</span></td>';
      $executiveTableRows.='<td class="number"><span class="amountright">'.($deliveriesByVehicle[0]['quantity_products']/$workingDays).'</span></td>';
    $executiveTableRows.='</tr>';
    
    $executiveTableBody='<tbody>'.$executiveTotalRow.$executiveTableRows.$executiveTotalRow.'</tbody>';							
    
    $executiveSummaryVehicleTable='<table id="ejecutivo_entregas_x_vehicle">'.$executiveTableHead.$executiveTableBody.'</table>';
    $excelOutput.=$executiveSummaryVehicleTable;     
  }

  echo '<div class="deliveries resumen">';
    echo '<h1>'.__('Deliveries').'</h1>';
  

    echo "<div class='container-fluid'>";
      echo '<div class="row">';
        echo '<div class="'.($userRoleId == ROLE_DRIVER ? "col-sm-12":($userRoleId == ROLE_ADMIN || $canSeeExecutiveSummary?"col-sm-7":"col-sm-9")).'">';
          echo $this->Form->create('Report');
            echo '<fieldset>';
              echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
              echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
              echo $this->Form->input('Report.delivery_status_id',['default'=>$deliveryStatusId,'empty'=>[0=>'-- Todas Entregas --']]);
              echo $this->Form->input('Report.vehicle_id',['default'=>$vehicleId,'empty'=>[0=>'-- Vehículo --']]);
              if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
                echo $this->Form->input('Report.driver_user_id',['default'=>$driverUserId,'empty'=>[0=>'-- Conductor --']]);
              }
              else {
                echo $this->Form->input('Report.driver_user_id',['default'=>$driverUserId,'type'=>'hidden']);
              }
              //echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            echo '</fieldset>';
            echo '<button id="previousmonth" class="monthswitcher">Mes Previo</button>';
            echo '<button id="nextmonth" class="monthswitcher">Mes Siguiente</button>';
          echo '<br/>';	
          echo $this->Form->Submit('Actualizar');
          echo $this->Form->end();
          if (in_array($userRoleId,[ROLE_ADMIN,ROLE_MANAGER,ROLE_ACCOUNTING,ROLE_ASSISTANT])){
            echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'],['class' => 'btn btn-primary']);
          }
        echo "</div>";
      if ($userRoleId==ROLE_ADMIN  ||  $canSeeExecutiveSummary) {       
        echo '<div class="col-sm-5">';	  	
          echo '<h2>Totales por Conductor</h2>';
          echo $executiveSummaryDriverTable;
          echo '<h2>Totales por Vehículo</h2>';
          echo $executiveSummaryVehicleTable;
          echo '<p style="font-size:0.8em;">Note que solamente se toman en cuenta productos fabricados y otros productos principales importados, no los accesorios.</p>';
          echo '<p style="font-size:0.8em;">La cantidad de días está todos días del período seleccionado sin los domingos.  En este caso la cantidad de días iguala '.$workingDays.'.</p>';
      }
      elseif ($userRoleId != ROLE_DRIVER) {
        echo '<div class="col-sm-3">';
      }
        if ($userRoleId != ROLE_DRIVER){
          echo '<h2>'.__('Actions').'</h2>';
          echo '<ul style="list-style-type:none;">';
            // no permitir que se crean entregas sin especificar la orden de venta o factura antes
            //echo '<li>'.$this->Html->link(__('New Delivery'), ['action' => 'crear']).'</li>';
            echo '<br/>';
            echo '<li>'.$this->Html->link('Todos conductores', ['controller' => 'users', 'action' => 'resumen',ROLE_DRIVER]).'</li>';
            echo '<li>'.$this->Html->link('Nuevo Conductor', ['controller' => 'users', 'action' => 'crear',ROLE_DRIVER]).'</li>';
            echo '<br/>';
            echo '<li>'.$this->Html->link(__('List Vehicles'), ['controller' => 'vehicles', 'action' => 'resumen']).'</li>';
            echo '<li>'.$this->Html->link(__('New Vehicle'), ['controller' => 'vehicles', 'action' => 'crear']).'</li>';
            echo '<br/>';
          echo '</ul>';
        }
      if ($userRoleId != ROLE_DRIVER){  
        echo '</div>';
      }
        
      echo '</div>';
    echo '</div>';  
  echo '</div>';
  echo '<div>';
    echo $deliveryTables;
  echo '</div>';
  $_SESSION['resumenEntregasADomicilio'] = $excelOutput;