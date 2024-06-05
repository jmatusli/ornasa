<script>
  var jsProductRemainingQuantities=<?php echo json_encode($productRemainingQuantities);?>;
  var jsProductWarehouses=<?php echo json_encode($productWarehouses); ?>;
  
	$('body').on('change','#ReportMovementDateDay',function(){
		$('#refresh').trigger('click');
	});
	$('body').on('change','#ReportMovementDateMonth',function(){
		$('#refresh').trigger('click');
	});
	$('body').on('change','#ReportMovementDateYear',function(){
		$('#refresh').trigger('click');
	});
	$('body').on('change','#ReportOriginWarehouseId',function(){
		$('#refresh').trigger('click');
	});
	
	$('body').on('change','#ReportProductId',function(){
		var selectedProductId=$(this).find("option:selected").val();
		var quantityPresent=jsProductRemainingQuantities[selectedProductId];
		$('#ReportPresentQuantity').val(parseInt(quantityPresent));
    
    var productWarehousesForProduct=Object.values(jsProductWarehouses[selectedProductId])
    var targetWarehouseId=$('#ReportTargetWarehouseId').val();
    // change targetWarehouseId if it is not allowed
    if (productWarehousesForProduct.indexOf(targetWarehouseId)<0){
      $('#ReportTargetWarehouseId').val(productWarehousesForProduct[0])
    }
    $('.targetWarehouse option').each(function(){
      var warehouseId=$(this).val();
      $(this).prop('disabled',(productWarehousesForProduct.indexOf(warehouseId)<0?true:false));
    });
	});
	
	$('body').on('blur','#ReportTransferQuantity',function(){
		var quantityPresent=parseInt($('#ReportPresentQuantity').val());
		var transferQuantity=parseInt($('#ReportTransferQuantity').val());
		if (transferQuantity>quantityPresent){
			$('#ReportTransferQuantity').val(parseInt(quantityPresent));
		}
	});

</script>
<div class="stockMovements form fullwidth">
<?php 
	echo $this->Form->create('StockMovement'); 
		echo "<fieldset>";
			echo "<legend>".__('Transferencia entre Bodegas')."</legend>";
			echo "<p>Aviso importante: las cantidades que se muestran son las cantidades que están presentes el día de hoy de este producto.  La fecha del movimiento determina cuales son los productos que ya eran fabricados en aquel momento.</p>";
			echo $this->Form->input('Report.movement_date',['type'=>'date','dateFormat'=>'DMY','default'=>$inventoryDate]);
			echo $this->Form->input('Report.origin_warehouse_id',['label'=>__('Bodega de Origen'),'options'=>$warehouses,'default'=>$originWarehouseId]);
			
      echo $this->Form->submit(__('Refresh'),['name'=>'refresh', 'id'=>'refresh']);
			
      echo "<br/>";
			echo $this->Form->input('Report.product_id',['default'=>'0','empty'=>['0'=>'Seleccione el producto a transferir']]);
			echo "<br/>";
			echo $this->Form->input('Report.present_quantity',['label'=>'Cantidad presente en bodega','default'=>0,'readonly'=>'readonly']);
			echo $this->Form->input('Report.transfer_quantity',['default'=>0,'label'=>'Cantidad a transferir']);
			echo $this->Form->input('Report.target_warehouse_id',['label'=>'Bodega de Destino','class'=>'targetWarehouse','options'=>$warehouses,'default'=>$targetWarehouseId]);
      echo $this->Form->input('StockMovement.description',['type'=>'textarea','rows'=>2,'required'=>false]);
      
			echo $this->Form->submit(__('Submit'),['name'=>'submit', 'id'=>'submit']);
		echo "</fieldset>";
	echo $this->Form->end(); 
?>
</div>