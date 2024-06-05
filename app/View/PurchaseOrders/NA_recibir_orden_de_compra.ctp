<script>
	$('body').on('change','#ReportPurchaseOrderId',function(){
		getPurchaseOrderInfo();
		getPurchaseOrderProducts();
	});
	
	function getPurchaseOrderInfo() {    
		var purchaseorderid=$('#ReportPurchaseOrderId').val();
		if (purchaseorderid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchase_orders/getpurchaseorderinfo/',
				data:{"purchaseorderid":purchaseorderid},
				dataType:'json',
				cache: false,
				type: 'POST',
				success: function (purchaseorderdata) {
					var purchaseorderdate = purchaseorderdata.PurchaseOrder.purchase_order_date.split(" ");//dateTime[0] = date, dateTime[1] = time
					var date = purchaseorderdate[0].split("-");
					$('#PurchaseOrderTime').text(date[2]+"-"+date[1]+"-"+date[0]);
					$('#PurchaseOrderCode').text(purchaseorderdata.PurchaseOrder.purchase_order_code);
					$('#PurchaseOrderProviderName').text(purchaseorderdata.Provider.name);
					$('#PurchaseOrderUserName').text(purchaseorderdata.User.username);
					if (purchaseorderdata.PurchaseOrder.bool_annulled){
						$('#PurchaseOrderBoolAnnulled').text('Si');
					}
					else {
						$('#PurchaseOrderBoolAnnulled').text('No');
					}
					if (purchaseorderdata.PurchaseOrder.bool_iva){
						$('#PurchaseOrderBoolIva').text('Si');
					}
					else {
						$('#PurchaseOrderBoolIva').text('No');
					}
					$('#PurchaseOrderCostSubTotal').text(purchaseorderdata.Currency.abbreviation +" "+purchaseorderdata.PurchaseOrder.cost_subtotal);
					$('#PurchaseOrderCostIva').text(purchaseorderdata.Currency.abbreviation +" "+purchaseorderdata.PurchaseOrder.cost_iva);
					$('#PurchaseOrderCostTotal').text(purchaseorderdata.Currency.abbreviation +" "+purchaseorderdata.PurchaseOrder.cost_total);
					$('#PurchaseOrderCostOtherTotal').text(purchaseorderdata.Currency.abbreviation +" "+purchaseorderdata.PurchaseOrder.cost_other_total);
					$('#PurchaseOrderPaymentMode').text(purchaseorderdata.PaymentMode.name);
					$('#PurchaseOrderPaymentDocument').text(purchaseorderdata.PurchaseOrder.payment_document);
					
					$('#PurchaseOrderInfo').removeClass('hidden');
				},
				error: function(e){
					console.log(e);
					alert(e.responseText);
				}
			});
		}
		else {
			$('#PurchaseOrderInfo').addClass('hidden');
		}
	}
	
	function getPurchaseOrderProducts() {    
		var purchaseorderid=$('#ReportPurchaseOrderId').val();
		if (purchaseorderid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchase_orders/getpurchaseorderproducts/',
				data:{"purchaseorderid":purchaseorderid},
				cache: false,
				type: 'POST',
				success: function (purchaseorderproducts) {
					$('#purchaseOrderProducts').html(purchaseorderproducts);
				},
				error: function(e){
					console.log(e);
					alert(e.responseText);
				}
			});
		}
		else {
			$('#purchaseOrderProducts').empty();
		}
	}
	
	
	
	$('body').on('change','.boolreceived',function(){
		setPurchaseOrderReceivedState();
	});
	
	function setPurchaseOrderReceivedState(){
		var boolPurchaseOrderReceived=true;
		$("td.boolreceived div input[type=checkbox]").each(function(){
			var checkbox=$(this);
			if (!$(this).is(':checked')){
				boolPurchaseOrderReceived=false;
			}
		});
		$('#PurchaseOrderBoolReceived').prop('checked',boolPurchaseOrderReceived);
	}
	
	function formatNumbers(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0);
		});
	}	
	
	function formatCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
		$("td.USDcurrency span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
		var currencyid=$('#PurchaseOrderCurrencyId').children("option").filter(":selected").val();
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
		}
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		
		getPurchaseOrderInfo();
		getPurchaseOrderProducts();
		
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
	
	$(document).ajaxComplete(function() {	
		formatNumbers();
		formatCurrencies();
	});

</script>
<div class="purchaseOrders form fullwidth">
<?php 
	echo $this->Form->create('PurchaseOrder'); 
	echo "<fieldset>";
		echo "<legend>".__('Recepción de productos de orden de compra')."</legend>";
		
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-md-6'>";
					echo $this->Form->input('Report.purchase_order_id',array('default'=>$purchaseOrderId,'empty'=>array('0'=>'Seleccione Orden de Compra')));
					echo "<dl id='PurchaseOrderInfo'>";
					
						
						echo "<dt>".__('Purchase Order Date')."</dt>";
						echo "<dd id='PurchaseOrderTime'> </dd>";
						echo "<dt>".__('Purchase Order Code')."</dt>";
						echo "<dd id='PurchaseOrderCode'> </dd>";
						echo "<dt>".__('Provider')."</dt>";
						echo "<dd id='PurchaseOrderProviderName'> </dd>";
						echo "<dt>".__('User')."</dt>";
						echo "<dd id='PurchaseOrderUserName'> </dd>";
						echo "<dt>".__('Bool Annulled')."</dt>";
						echo "<dd id='PurchaseOrderBoolAnnulled'> </dd>";
						echo "<dt>".__('Bool Iva')."</dt>";
						echo "<dd id='PurchaseOrderBoolIva'> </dd>";
						echo "<dt>".__('Cost Subtotal')."</dt>";
						echo "<dd id='PurchaseOrderCostSubTotal'> </dd>";
						echo "<dt>".__('Cost Iva')."</dt>";
						echo "<dd id='PurchaseOrderCostIva'> </dd>";
						echo "<dt>".__('Cost Total')."</dt>";
						echo "<dd id='PurchaseOrderCostTotal'> </dd>";
						echo "<dt>".__('Cost Other Total')."</dt>";
						echo "<dd id='PurchaseOrderCostOtherTotal'> </dd>";
						echo "<dt>".__('Payment Mode')."</dt>";
						echo "<dd id='PurchaseOrderPaymentMode'> </dd>";
						echo "<dt>".__('Payment Document')."</dt>";
						echo "<dd id='PurchaseOrderPaymentDocument'> </dd>";
					
					echo "</dl>";
					echo $this->Form->input('bool_received',array('onclick'=>'return false'));
				echo "</div>";
				echo "<div class='col-md-4'>";
				// 20160723 PurchaseOrderRemarks integration pending
				echo "</div>";
				echo "<div class='col-md-2 actions'>";
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul style='list-style:none;'>";
						//if ($bool_delete_permission){
						//	echo "<li>".$this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('PurchaseOrder.id')), array('confirm'=>__('Está seguro que quiere eliminar orden de compra # %s?', $this->Form->value('PurchaseOrder.purchase_order_code'))))."</li>";
						//	echo "<br/>";
						//}
						echo "<li>".$this->Html->link(__('List Purchase Orders'), array('action' => 'index'))."</li>";
						echo "<br/>";
						if ($bool_provider_index_permission){
							echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'providers', 'action' => 'index'))." </li>";
						}
						if ($bool_provider_add_permission){
							echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'providers', 'action' => 'add'))." </li>";
						}
					echo "</ul>";
				echo "</div>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<div class='col-md-12'>";
					echo "<h3>Productos en Orden de Compra</h3>";
					echo "<table id='purchaseOrderProducts'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Orden de Producción</th>";
								echo "<th>Departamento</th>";
								echo "<th>Producto</th>";
								//echo "<th class='hidden'>Producto en Orden de Producción</th>";
								echo "<th style='width:20%;'>Descripción</th>";
								echo "<th>Cantidad</th>";
								echo "<th>Costo Unitario</th>";
								echo "<th>Costo Total</th>";
								echo "<th class='hidden'>Purchase Order Product</th>";
								echo "<th>Producto Recibido</th>";
								echo "<th>Fecha de Recepción</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
						for ($pop=0;$pop<count($requestProducts);$pop++){
							//pr($requestProducts[$pop]['PurchaseOrderProduct']['ProductionOrderProduct']);
							echo "<tr row='".$pop."'>";
							echo "<tr row='".$pop."'>";
								if (!empty($requestProducts[$pop]['ProductionOrder'])){
								echo "<td class='productionorderid'>".$this->Html->link($requestProducts[$pop]['ProductionOrder']['production_order_code'],array('controller'=>'production_orders','action'=>'view',$requestProducts[$pop]['ProductionOrder']['id']))."</td>";
								}
								else {
									echo "<td class='productionorderid'>-</td>";
								}
								if (!empty($requestProducts[$pop]['PurchaseOrderProduct']['Department'])){
									echo "<td class='departmentid'>".$requestProducts[$pop]['PurchaseOrderProduct']['Department']['name']."</td>";
								}
								else {
									echo "<td class='productionorderid'>-</td>";
								}
								
								echo "<td class='productid'>".$requestProducts[$pop]['PurchaseOrderProduct']['Product']['name']."</td>";
								echo "<td class='productdescription'>".$requestProducts[$pop]['PurchaseOrderProduct']['product_description']."</td>";
								echo "<td class='productquantity amount'>".$requestProducts[$pop]['PurchaseOrderProduct']['product_quantity']."</td>";
								echo "<td class='productunitcost'><span class='currency'></span>".$requestProducts[$pop]['PurchaseOrderProduct']['product_unit_cost']."</td>";
								echo "<td class='producttotalcost'><span class='currency'></span>".$requestProducts[$pop]['PurchaseOrderProduct']['product_total_cost']."</td>";
								echo "<td class='purchaseorderproductid'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.id',array('label'=>false,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['id'],'type'=>'hidden'))."</td>";
								echo "<td class='boolreceived'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.bool_received',array('label'=>false,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['bool_received'],'default'=>false))."</td>";
								echo "<td class='datereceived'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.date_received',array('label'=>false,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['date_received'],'dateFormat'=>'DMY'))."</td>";
							echo "</tr>";
						}
						echo "</tbody>";
					echo "</table>";
				echo "</div>";
	echo "</fieldset>";
	echo $this->Form->submit(__('Submit')); 
	echo $this->Form->end(); 
?>
</div>

