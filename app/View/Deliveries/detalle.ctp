<div class="deliveries view">
<?php 
  $plannedDeliveryDateTime = new Datetime($delivery['Delivery']['planned_delivery_datetime']);
  $actualDeliveryDateTime = new Datetime($delivery['Delivery']['actual_delivery_datetime']);
  
  echo '<h1>'.__('Delivery').' '.$delivery['Delivery']['delivery_code'].'</h1>';
	echo '<dl>';
    echo '<dt>'.__('Delivery Code').'</dt>';
		echo '<dd>'.$delivery['Delivery']['delivery_code'].'</dd>';
		echo '<dt>'.__('Registering User').'</dt>';
		//echo '<dd>'.$this->Html->link($delivery['RegisteringUser']['first_name']." ".$delivery['RegisteringUser']['last_name'], ['controller' => 'users', 'action' => 'view', $delivery['RegisteringUser']['id']]).'</dd>';
    echo '<dd>'.($delivery['RegisteringUser']['first_name']." ".$delivery['RegisteringUser']['last_name']).'</dd>';
    echo '<dt>'._('Client Name').'</dt>';
		echo '<dd>'.$delivery['Delivery']['client_name'].'</dd>';
    echo '<dt>'._('Client Phone').'</dt>';
		echo '<dd>'.$delivery['Delivery']['client_phone'].'</dd>';
		echo '<dt>'.__('Sales Order').'</dt>';
		echo '<dd>'.(empty($delivery['SalesOrder']['id'])?"-":($boolSalesOrderDetail?$this->Html->link($delivery['SalesOrder']['sales_order_code'], ['controller' => 'salesOrders', 'action' => 'detalle', $delivery['SalesOrder']['id']]):$delivery['SalesOrder']['sales_order_code'])).'</dd>';
		echo '<dt>'.__('Order').'</dt>';
		echo '<dd>'.(empty($delivery['Order']['id'])?"-":($boolOrderDetail?$this->Html->link($delivery['Order']['order_code'], ['controller' => 'orders', 'action' => 'verVenta', $delivery['Order']['id']]):$delivery['Order']['order_code'])).'</dd>';
		echo '<dt>'.__('Planned Delivery Datetime').'</dt>';
		echo '<dd>'.$plannedDeliveryDateTime->format('d-m-Y H:i').'</dd>';
    if ($delivery['Delivery']['delivery_status_id'] == DELIVERY_STATUS_DELIVERED){
      echo '<dt>'.__('Actual Delivery Datetime').'</dt>';
      echo '<dd>'.$actualDeliveryDateTime->format('d-m-Y H:i').'</dd>';
		}
    echo '<dt>'.__('Delivery Status').'</dt>';
    echo '<dd>'.$delivery['DeliveryStatus']['code'].'</dd>';
		echo '<dt>'.__('Delivery Address').'</dt>';
		echo '<dd>'.h($delivery['Delivery']['delivery_address']).'</dd>';
    echo '<dt>'.__('Driver User').'</dt>';
		//echo '<dd>'.$this->Html->link($delivery['DriverUser']['first_name']." ".$delivery['DriverUser']['last_name'], ['controller' => 'users', 'action' => 'view', $delivery['DriverUser']['id']]).'</dd>';
    echo '<dd>'.($delivery['DriverUser']['first_name']." ".$delivery['DriverUser']['last_name']).'</dd>';
		echo '<dt>'.__('Vehicle').'</dt>';
		echo '<dd>'.($boolVehicleDetail?$this->Html->link($delivery['Vehicle']['name'], ['controller' => 'vehicles', 'action' => 'view', $delivery['Vehicle']['id']]):$delivery['Vehicle']['name']).'</dd>';
		
	echo '</dl>';
?>
</div>

<?php 
 if ($userRoleId != ROLE_DRIVER){
  echo '<div class="actions">';
    echo '<h2>'.__('Actions').'</h2>';
    echo '<ul>';
    
    if ($bool_edit_permission && ($delivery['Delivery']['delivery_status_id'] < DELIVERY_STATUS_DELIVERED || $userRoleId == ROLE_ADMIN)){
      echo '<li>'.$this->Html->link(__('Edit Delivery'), ['action' => 'editar', $delivery['Delivery']['id']]).'</li>';
      echo '<br/>';
    }
    if ($boolCanRegisterDelivery && $delivery['Delivery']['delivery_status_id'] < DELIVERY_STATUS_DELIVERED){
      echo '<li>'.$this->Html->link('Registrar Entrega', ['action' => 'registrarEntregaAlCliente', $delivery['Delivery']['id']]).'</li>';
      echo '<br/>';
    }  
      echo '<li>'.$this->Html->link(__('List Deliveries'), ['action' => 'resumen']).'</li>';
    if ($bool_add_permission){  
      echo '<li>'.$this->Html->link(__('New Delivery'), ['action' => 'crear']).'</li>';
    }
    echo '</ul>';
  echo '</div>';  
}  
?>  

<div class="related">
<?php 
	echo '<h2>Observaciones</h2>';
	if (!empty($delivery['DeliveryRemark'])){
    echo '<table>';
    foreach ($delivery['DeliveryRemark'] as $deliveryRemark){
      $remarkDateTime = new DateTime($deliveryRemark['remark_datetime']);
      echo '<tr>';
        echo '<td>'.$remarkDateTime->format('d-m-Y H:i:s').'</td>';
        echo '<td>'.($deliveryRemark['RegisteringUser']['first_name'].' '.$deliveryRemark['RegisteringUser']['last_name']).'</td>';
        echo '<td>'.$deliveryRemark['remark_text'].'</td>';
        
      echo '</tr>';
    }
	echo '</table>';
  }
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php

  if ($bool_delete_permission && ($delivery['Delivery']['delivery_status_id'] < DELIVERY_STATUS_DELIVERED || $userRoleId == ROLE_ADMIN)){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Entrega a Domicilio'), ['action' => 'delete', $delivery['Delivery']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la orden de entrega # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $delivery['Delivery']['delivery_code']));
  }
?>
</div>