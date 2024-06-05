<script>
	$('body').on('change','#PurchaseOrderCurrencyId',function(){
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
	});

	$('body').on('change','.productid',function(){
		var productname =$(this).find('div select option:selected').text();
		$(this).closest('tr').find('td.productdescription textarea').val(productname);
	});
	$('body').on('change','.productquantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.productunitcost',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=roundToFive($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	
	function calculateRow(rowid) {    
		var currentrow=$('#purchaseOrderProducts').find("[row='" + rowid + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitcost=parseFloat(currentrow.find('td.productunitcost div input').val());
		
		var totalcost=quantity*unitcost;
		
		currentrow.find('td.producttotalcost div input').val(roundToTwo(totalcost));
	}
	
	$('body').on('change','#PurchaseOrderBoolIva',function(){
		calculateTotal();
	});
	
	function calculateTotal(){
		var booliva=$('#PurchaseOrderBoolIva').is(':checked');
		var totalProductQuantity=0;
		var subtotalCost=0;
		var ivaCost=0
		var totalCost=0
		$("#purchaseOrderProducts tbody tr:not(.totalrow.hidden)").each(function() {
			var currentProductQuantity = $(this).find('td.productquantity div input');
			if (!isNaN(currentProductQuantity.val())){
				var currentQuantity = parseFloat(currentProductQuantity.val());
				totalProductQuantity += currentQuantity;
			}
			
			var currentProduct = $(this).find('td.producttotalcost div input');
			if (!isNaN(currentProduct.val())){
				var currentCost = parseFloat(currentProduct.val());
				subtotalCost += currentCost;
			}
		});
		$('#purchaseOrderProducts tbody tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));
		
		
		$('#purchaseOrderProducts tbody tr.totalrow.subtotal td.totalcost div input').val(subtotalCost.toFixed(2));
		
		if (booliva){
			ivaCost=roundToTwo(0.15*subtotalCost);
		}
		$('#purchaseOrderProducts tbody tr.totalrow.iva td.totalcost div input').val(ivaCost.toFixed(2));
		totalCost=subtotalCost + ivaCost;
		
		$('#purchaseOrderProducts tbody tr.totalrow.total td.totalcost div input').val(totalCost.toFixed(2));
		
		return false;
	}
	
	$('body').on('click','.addProduct',function(){
		var tableRow=$('#purchaseOrderProducts tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeProducts',function(){
		var tableRow=$(this).closest('tr').remove();
		calculateTotal();
	});
	
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
		
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});

</script>
<div class="purchaseOrders form fullwidth">
<?php 
	echo $this->Form->create('PurchaseOrder'); 
	echo "<fieldset>";
		echo "<legend>".__('Crear Orden de Compra')."</legend>";
		
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-md-6'>";
					echo $this->Form->input('purchase_order_date',['dateFormat'=>'DMY']);
					echo $this->Form->input('purchase_order_code');
					echo $this->Form->input('provider_id',['default'=>0,'empty'=>['0'=>'Seleccione Proveedor']]);
					echo $this->Form->input('user_id',['type'=>'hidden','value'=>$loggedUserId]);
					echo $this->Form->input('bool_annulled',['checked'=>false]);
					echo $this->Form->input('bool_iva',['checked'=>true]);
					echo $this->Form->input('currency_id',['default'=>CURRENCY_USD,'label'=>'Moneda']);
					echo $this->Form->input('payment_mode_id',['default'=>0,'empty'=>['0'=>'Seleccione Modo de Pago']]);
					echo $this->Form->input('payment_document');
					echo $this->Form->input('planned_delivery_date',['dateFormat'=>'DMY']);
					echo $this->Form->input('bool_received');
				echo "</div>";
				echo "<div class='col-md-4'>";
					
				echo "</div>";
				echo "<div class='col-md-2 actions'>";
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul style='list-style:none;'>";
						echo "<li>".$this->Html->link(__('List Purchase Orders'), ['action' => 'resumen'])."</li>";
						echo "<br/>";
						if ($bool_provider_index_permission){
							echo "<li>".$this->Html->link(__('List Providers'), ['controller' => 'thirdParties', 'action' => 'resumeneProveedores'])." </li>";
						}
						if ($bool_provider_add_permission){
							echo "<li>".$this->Html->link(__('New Provider'), ['controller' => 'thirdParties', 'action' => 'crearProveedor'])." </li>";
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
								echo "<th>Producto</th>";
								echo "<th style='width:20%;'>Descripción</th>";
								echo "<th>Cantidad</th>";
								echo "<th style='width:10%;'>Costo Unitario</th>";
								echo "<th style='width:12%;'>Costo Total</th>";
								echo "<th>Acciones</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
						$counter=0;
						
						for ($pop=0;$pop<count($requestProducts);$pop++){
							echo "<tr row='".$pop."'>";
								echo "<td class='productid'>";
									echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_id',['label'=>false,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_id'],'empty'=>['0'=>'Seleccione Producto']]);
								echo "</td>";
								echo "<td class='productdescription'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_description',['label'=>false,'cols'=>1,'rows'=>5,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_description']])."</td>";
								echo "<td class='productquantity amount'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_quantity',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_quantity'],'required'=>false])."</td>";
								echo "<td class='productunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_unit_cost',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_unit_cost']])."</td>";
								echo "<td class='producttotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_total_cost',['label'=>false,'type'=>'decimal','readonly'=>'readonly','value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_total_cost']])."</td>";
								echo "<td>";
										echo "<button class='removeProduct' type='button'>".__('Remove Product')."</button>";
										echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
								echo "</td>";
							echo "</tr>";
							$counter++;
						}
						for ($pop=$counter;$pop<30;$pop++){
							if ($pop==$counter){
								echo "<tr row='".$pop."'>";
							}
							else {
								echo "<tr row='".$pop."' class='hidden'>";
							}
								echo "<td class='productid'>";
									echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Producto']]);
								echo "</td>";
								// 20160723 Crear Orden de Compra solamente se debe utilizar para ordenes de compra que tienen productos que no eran en la orden de producción aun
								//echo "<td class='hidden'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.production_order_product_id',['label'=>false,'value'=>0,'type'=>'hidden'])."</td>";
								echo "<td class='productdescription'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_description',['label'=>false,'cols'=>1,'rows'=>5])."</td>";
								echo "<td class='productquantity amount'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0])."</td>";
								echo "<td class='productunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_unit_cost',['label'=>false,'type'=>'decimal','default'=>0])."</td>";
								echo "<td class='producttotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_total_cost',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>0])."</td>";
								echo "<td>";
										echo "<button class='removeProduct' type='button'>".__('Remove Product')."</button>";
										echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
								echo "</td>";
							echo "</tr>";
						}
							echo "<tr class='totalrow subtotal'>";
								echo "<td>Subtotal</td>";
								echo "<td></td>";
								echo "<td class='productquantity amount right'><span></span></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_subtotal',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
							echo "</tr>";		
							echo "<tr class='totalrow iva'>";
								echo "<td>IVA</td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_iva',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
							echo "</tr>";		
							echo "<tr class='totalrow total'>";
								echo "<td>Total</td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_total',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
							echo "</tr>";		
						echo "</tbody>";
					echo "</table>";
        /*  
					echo "<h3>Otros Costos</h3>";
					echo "<table id='otherCosts'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Departamento</th>";
								echo "<th>Descripción</th>";
								echo "<th>Cantidad</th>";
								echo "<th>Costo Unitario</th>";
								echo "<th>Costo Total</th>";
								echo "<th>Acciones</th>";
							echo "</tr>";
						echo "</thead>";
						
						echo "<tbody>";
						$oc=0;
						for ($oc=0;$oc<count($requestOtherCosts);$oc++){
							echo "<tr row='".$oc."'>";
								echo "<td class='departmentid'>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.department_id',array('label'=>false,'default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['department_id'],'empty'=>array('0'=>'Seleccione Departamento')))."</td>";
								echo "<td class='taskdescription'>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.task_description',array('label'=>false,'default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['task_description']))."</td>";
								echo "<td class='taskquantity amount'>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.task_quantity',array('label'=>false,'default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['task_quantity']))."</td>";
								
								echo "<td class='taskunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.task_unit_cost',array('label'=>false,'default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['task_unit_cost']))."</td>";
								echo "<td class='tasktotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.task_total_cost',array('label'=>false,'readonly'=>'readonly','default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['task_total_cost']))."</td>";
								
								echo "<td>";
										echo "<button class='removeCost' type='button'>".__('Remove Cost')."</button>";
										echo "<button class='addCost' type='button'>".__('Add Cost')."</button>";
								echo "</td>";
							echo "</tr>";
						}
							
						for ($j=$oc;$j<30;$j++){
							if ($j==$oc){
								echo "<tr row='".$j."'>";
							}
							else {
								echo "<tr row='".$j."' class='hidden'>";
							}
								echo "<td class='departmentid'>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.department_id',array('label'=>false,'default'=>0,'empty'=>array('0'=>'Seleccione Departamento')))."</td>";
								echo "<td class='taskdescription'>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.task_description',array('label'=>false))."</td>";
								echo "<td class='taskquantity'>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.task_quantity',array('label'=>false,'default'=>0))."</td>";
								echo "<td class='taskunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.task_unit_cost',array('label'=>false,'default'=>0))."</td>";
								echo "<td class='tasktotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.task_total_cost',array('label'=>false,'readonly'=>'readonly','default'=>0))."</td>";
								echo "<td>";
										echo "<button class='removeCost' type='button'>".__('Remover Costo')."</button>";
										echo "<button class='addCost' type='button'>".__('Otro Costo')."</button>";
								echo "</td>";
							echo "</tr>";
						}
							echo "<tr class='totalrow total'>";
								echo "<td>Total</td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td class='subtotalcost amount right'><span class='currency'></span>".$this->Form->input('cost_other_total',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
								echo "<td></td>";
							echo "</tr>";		
						echo "</tbody>";
					echo "</table>";
        */  
				echo "</div>";
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
?>
</div>

<?php 	
	
?>

