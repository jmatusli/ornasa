<script>
  $('body').on('change','#DeliveryWarehouseId',function(){	
    setDeliveryCode()
  });

  function setDeliveryCode(){
    //$('#clientProcessMsg').html('Calculando el código de la orden de entrega');
		//showMessageModal();
    
		var warehouseId=parseInt($('#DeliveryWarehouseId').val());
    //var userId=parseInt($('#SalesOrderVendorUserId').val());
		if (warehouseId>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>deliveries/getDeliveryCode/',
				data:{"warehouseId":warehouseId},
				cache: false,
				type: 'POST',
				success: function (deliveryCode) {
					$('#DeliveryDeliveryCode').val(deliveryCode);
          $('#DeliveryCrearForm legend').html("Crear Nueva Orden de Entrega "+deliveryCode);
          //hideMessageModal()
          
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
          //$('#clientProcessMsg').html('Se ha producido un error mientras se calculaba el código');
          //$('#modalFooter').removeClass('hidden');
				}
			});
		}
    else {
      $('#DeliveryDeliveryCode').val('');
      $('#DeliveryCrearForm legend').html("Crear Orden de Entrega");
      //hideMessageModal()
    }
	}

	$(document).ready(function(){	
    setDeliveryCode();
  
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>  
<div class="deliveries form">
<?php
	echo $this->Form->create('Delivery'); 
	echo '<fieldset>';
		echo '<legend>'.__('Add Delivery').'</legend>';
    
    echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
     echo $this->Form->Submit('Actualizar Bodega',['class'=>'updateWarehouse','name'=>'updateWarehouse']);
          
		echo $this->Form->input('registering_user_id',['type'=>'hidden']);
    echo $this->Form->input('client_id',['default'=>$clientId]);
    echo $this->Form->input('client_name',['default'=>$clientName]);
    echo $this->Form->input('client_phone',['default'=>$clientPhone]);
    
    echo $this->Form->input('delivery_code',['readonly'=>true]);
		echo $this->Form->input('sales_order_id',['default'=>$salesOrderId,'empty'=>[0=>'-- Orden de Venta --'],'class'=>'fixed']);
		echo $this->Form->input('order_id',['label'=>'Factura','default'=>$orderId,'empty'=>[0=>'-- Factura --'],'class'=>'fixed']);
		echo $this->Form->input('planned_delivery_datetime',['dateFormat'=>'DMY','minYear'=>2021,'maxYear'=>date('Y')+1]);
    
		//echo $this->Form->input('actual_delivery_datetime');
		
    echo $this->Form->input('delivery_status_id',['default'=>DELIVERY_STATUS_PROGRAMMED,'empty'=>[0=>'-- Estado --']]);
		echo $this->Form->input('delivery_address',['default'=>$deliveryAddress]);
		echo $this->Form->input('driver_user_id',['label'=>'Conductor','default'=>0,'empty'=>[0=>'-- Conductor --']]);
		echo $this->Form->input('vehicle_id',['label'=>'Vehículo','default'=>0,'empty'=>[0=>'-- Vehículo --']]);
    echo $this->Form->input('remark',['label'=>'Remarca','type'=>'textarea']);
    
	echo '</fieldset>';
	echo $this->Form->Submit('Guardar');
	echo $this->Form->end();
?>
</div>

<?php
if ($userRoleId != ROLE_DRIVER){
  //echo 'userRoleId is '.$userRoleId.'<br/>';
  echo '<div class="actions">';
    echo '<h3>'.__('Actions').'</h3>';
    echo '<ul>';
      echo '<li>'.$this->Html->link(__('List Deliveries'), ['action' => 'resumen']).'</li>';
      echo '<br/>';
      echo '<li>'.$this->Html->link(__('List Vehicles'), ['controller' => 'vehicles', 'action' => 'resumen']).'</li>';
      echo '<li>'.$this->Html->link(__('New Vehicle'), ['controller' => 'vehicles', 'action' => 'crear']).'</li>';
    echo '</ul>';
  echo '</div>';
}