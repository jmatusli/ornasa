<script>
  function displayActualDeliveryDateTime(boolDisplay){
    if (boolDisplay){
      $('#DeliveryActualDeliveryDatetimeDay').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMonth').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeYear').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeHour').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMin').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMeridian').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeDay').closest('div').removeClass('d-none');
      $('#DeliveryActualDeliveryDatetimeDay').closest('div').find('label').removeClass('d-none');
    }
    else {
      $('#DeliveryActualDeliveryDatetimeDay').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMonth').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeYear').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeHour').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMin').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeMeridian').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeDay').closest('div').addClass('d-none');
      $('#DeliveryActualDeliveryDatetimeDay').closest('div').find('label').addClass('d-none');
    }
  }
  
  $('body').on('change','#DeliveryDeliveryStatusId',function(){
    var boolDisplay=$(this).val() == <?php echo DELIVERY_STATUS_DELIVERED; ?>;
    displayActualDeliveryDateTime(boolDisplay)
  });
  
	$(document).ready(function(){	
    displayActualDeliveryDateTime(<?php echo $this->request->data['Delivery']['delivery_status_id'] == DELIVERY_STATUS_DELIVERED?1:0; ?>);
    $('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>  
<div class="deliveries form">
<?php
	echo $this->Form->create('Delivery'); 
	echo '<fieldset>';
  	echo '<legend>'.__('Edit Delivery').' '.$this->request->data['Delivery']['delivery_code'].'</legend>';
		echo $this->Form->input('id');
		echo $this->Form->input('registering_user_id',['type'=>'hidden']);
    echo $this->Form->input('client_id',['default'=>$clientId]);
    echo $this->Form->input('client_name',['default'=>$clientName]);
    echo $this->Form->input('client_phone',['default'=>$clientPhone]);
    
    echo $this->Form->input('delivery_code',['default'=>$this->request->data['Delivery']['delivery_code'],'readonly'=>true]);
		echo $this->Form->input('sales_order_id',['value'=>$salesOrderId,'empty'=>[0=>'-- Orden de Venta --'],'class'=>'fixed']);
		echo $this->Form->input('order_id',['label'=>'Factura','value'=>$orderId,'empty'=>[0=>'-- Factura --'],'class'=>'fixed']);
		
    echo $this->Form->input('planned_delivery_datetime',['dateFormat'=>'DMY','minYear'=>2021,'maxYear'=>date('Y')+1]);
    echo $this->Form->input('actual_delivery_datetime',['dateFormat'=>'DMY','minYear'=>2021,'maxYear'=>date('Y')]);
		
    echo $this->Form->input('delivery_status_id',['empty'=>[0=>'-- Estado --']]);
		echo $this->Form->input('delivery_address');
		echo $this->Form->input('driver_user_id',['label'=>'Conductor','empty'=>[0=>'-- Conductor --']]);
		echo $this->Form->input('vehicle_id',['label'=>'Vehículo','empty'=>[0=>'-- Vehículo --']]);
    
    echo $this->Form->input('remark',['label'=>'Remarca','type'=>'textarea']);
	echo '</fieldset>';
	echo $this->Form->Submit('Guardar');
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Deliveries'), ['action' => 'resumen']).'</li>';
		echo '<br/>';
    echo '<li>'.$this->Html->link(__('List Vehicles'), ['controller' => 'vehicles', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Vehicle'), ['controller' => 'vehicles', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
</div>
