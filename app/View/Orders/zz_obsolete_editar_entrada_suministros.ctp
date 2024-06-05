<script>
	$('body').on('change','#OrderWarehouseId',function(){
    $('#refresh').trigger('click');
  });
  
  $('body').on('change','#OrderPurchaseOrderId',function(){
    var purchaseOrderId=$(this).val();
    if (purchaseOrderId == 0){
      $('#OrderThirdPartyId').val(0);
    }
    else {
      $.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchaseOrders/getPurchaseOrderInfo/',
				data:{"purchaseOrderId":purchaseOrderId},
        dataType:'json',
				cache: false,
				type: 'POST',
				success: function (purchaseOrder) {
					var purchaseOrderProviderId=purchaseOrder.PurchaseOrder.provider_id;
          $('#OrderThirdPartyId option:not(:selected)').attr('disabled', false);
          $('#OrderThirdPartyId').val(purchaseOrderProviderId);  
          $('#OrderThirdPartyId option:not(:selected)').attr('disabled', true);
          
        },
				error: function(e){
					alert(e.responseText);
					console.log(e);
				}
			});  
    }
	});
  
  $('body').on('click','#addMaterial',function(){
		var tableRow=$('#productsForPurchase tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});
	$('body').on('click','.removeMaterial',function(){
		var tableRow=$(this).parent().parent().remove();
		calculateTotal();
	});	
	$('body').on('change','.productprice',function(){
		calculateTotal();
	});	
	
	$('body').on('change','#OrderBoolEntryIva',function(){	
		calculateTotal();
	});
	
	function calculateTotal(){
		var subtotal=0;
    var iva=0
    var total=0
		$("#productsForPurchase tbody tr:not(.hidden)").each(function() {
			var currentProduct = $(this).find('td.productprice div input');
			var currentPrice = parseFloat(currentProduct.val());
      if (!isNaN(currentPrice)){
        subtotal = subtotal + currentPrice;
      }
		});
		if ($('#OrderBoolEntryIva').is(':checked')){
      iva =roundToTwo(0.15*subtotal)
		}
    total=subtotal+iva;
		$('#OrderTotalPrice').val(subtotal);
    $('#OrderEntryCostIva').val(iva);
    $('#OrderEntryCostTotal').val(total);
		return false;
	}
	
	$(document).ready(function(){
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>
<div class="purchases fullwidth">
<?php 
	echo $this->Form->create('Order'); 
	echo "<fieldset>";
		echo "<legend>Editar Compra de Suministros</legend>";
    echo '<div class="container-fluid">';
      echo '<div class="row">';
        echo '<div class="col-sm-8">';  
          echo $this->Form->input('id');
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
          echo $this->Form->input('order_date',array('label'=>__('Purchase Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')));
          echo $this->Form->input('order_code',['label'=>'#Factura Entrada']);
          echo $this->Form->input('purchase_order_id',['empty'=>[0=>'-- Seleccione Orden de Compra --']]);
          echo $this->Form->input('bool_purchase_order_delivery_complete',['options'=>$purchaseOrderDeliveryOptions]);
          echo $this->Form->input('third_party_id',['label'=>__('Provider'),'class'=>'fixed']);
          echo $this->Form->input('bool_entry_iva',['type'=>'checkbox','label'=>'Se aplica IVA']);
        echo '</div>';
      echo '<div class="col-sm-4">';		
        echo $this->Form->input('total_price',['label'=>__('Costo Subtotal'),'readonly'=>'readonly']);
        echo $this->Form->input('entry_cost_iva',['label'=>__('Costo IVA'),'readonly'=>'readonly']);
        echo $this->Form->input('entry_cost_total',['label'=>__('Costo Total'),'readonly'=>'readonly']);
        echo "<h3>".__('Actions')."</h3>";
        echo '<ul style="list-style-type:none;">';
          if ($bool_delete_permission){
            //echo "<!--li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Order.id')), [], __('Are you sure you want to delete # %s?', $this->Form->value('Order.id')))."</li-->";
          }
          echo "<li>".$this->Html->link('Resumen Entradas Suministros',['action' => 'resumenEntradasSuministros'])."</li>";
          echo "<br/>";
          if ($bool_provider_index_permission) {
            echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))."</li>";
          }
          if ($bool_provider_add_permission) {
            echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))."</li>";
          } 
        echo "</ul>";
      echo '</div>';
    echo '</div>';
	echo '</div>';	
	echo "<div>";
		echo "<table id='productsForPurchase'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
					echo "<th>".__('Quantity')."</th>";
					echo "<th>".__('Cost')."</th>";
					echo "<th></th>";
				echo "</tr>";
			echo "</thead>";
			echo $this->Form->input('bool_editable',array('value'=>$boolEditable,'type'=>'hidden'));
			echo $this->Form->input('reason_for_non_editable',array('value'=>$reasonForNonEditable,'type'=>'hidden'));
			if (!empty($boolEditable)){			
				echo "<tbody>";
				//load products already in purchase
				$i=1;
				for ($p=0;$p<sizeof($products);$p++) { 
					for ($b=0;$b<sizeof($productsPurchasedEarlier);$b++) { 
						if (array_keys($products)[$p]==$productsPurchasedEarlier[$b]['StockMovement']['product_id']){
							echo "<tr>";
								echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'default'=>$productsPurchasedEarlier[$b]['StockMovement']['product_id'],'empty' =>array(0=>__('Choose a Product'))))."</td>";
								echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'numeric','label'=>false,'default'=>$productsPurchasedEarlier[$b]['StockMovement']['product_quantity']))."</td>";
								echo "<td  class='productprice'>". $this->Form->input('Product.'.$i.'.product_price',array('type'=>'numeric','label'=>false,'default'=>round($productsPurchasedEarlier[$b]['StockMovement']['product_total_price'],2)))."</td>";
								echo "<td><button class='removeMaterial'>".__('Remove Purchase Item')."</button></td>";
							echo "</tr>";
							$i++;
						}
					}
				}
				
				for ($j=$i;$j<=25;$j++) { 
					echo "<tr class='hidden'>";
						echo "<td class='productid'>".$this->Form->input('Product.'.$j.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
						echo "<td class='productquantity'>".$this->Form->input('Product.'.$j.'.product_quantity',array('type'=>'numeric','label'=>false))."</td>";
						echo "<td  class='productprice'>".$this->Form->input('Product.'.$j.'.product_price',array('type'=>'float','label'=>false))."</td>";
						echo "<td><button class='removeMaterial'>".__('Remove Purchase Item')."</button></td>";
					echo "</tr>";
				}
				echo "</tbody>";
			}
			else {
				echo "<p>".$reasonForNonEditable."</p>";
				echo "<tbody>";
				//load products already in purchase
				$i=1;
				for ($p=0;$p<sizeof($products);$p++) { 
					for ($b=0;$b<sizeof($productsPurchasedEarlier);$b++) { 
						if (array_keys($products)[$p]==$productsPurchasedEarlier[$b]['StockMovement']['product_id']){
							echo "<tr>";
								echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'value'=>$productsPurchasedEarlier[$b]['StockMovement']['product_id'],'empty' =>array(0=>__('Choose a Product')),'class'=>'fixed'))."</td>";
								echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'numeric','label'=>false,'readonly'=>'readonly','value'=>$productsPurchasedEarlier[$b]['StockMovement']['product_quantity']))."</td>";
								echo "<td  class='productprice'>". $this->Form->input('Product.'.$i.'.product_price',array('type'=>'numeric','label'=>false,'readonly'=>'readonly','value'=>round($productsPurchasedEarlier[$b]['StockMovement']['product_total_price'],2)))."</td>";
								//echo "<td><button class='removeMaterial'>".__('Remove Purchase Item')."</button></td>";
								echo "<td></td>";
							echo "</tr>";
							$i++;
						}
					}
				}
				/*
				for ($j=$i;$j<=25;$j++) { 
					echo "<tr class='hidden'>";
						echo "<td class='productid'>".$this->Form->input('Product.'.$j.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
						echo "<td class='productquantity'>".$this->Form->input('Product.'.$j.'.product_quantity',array('type'=>'numeric','label'=>false))."</td>";
						echo "<td  class='productprice'>".$this->Form->input('Product.'.$j.'.product_price',array('type'=>'float','label'=>false))."</td>";
						echo "<td><button class='removeMaterial'>".__('Remove Purchase Item')."</button></td>";
					echo "</tr>";
				}
				*/
				echo "</tbody>";
			}
		echo "</table>";
	echo "</div>";
	echo "<button id='addMaterial' type='button'>".__('Add Purchase Item')."</button>";	
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
?>
</div>
