<script>
	$('body').on('change','#PurchaseOrderCurrencyId',function(){
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
		/*
		// now update all prices
		var exchangerate=parseFloat($('#QuotationExchangeRate').val());
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice/exchangerate);
				$(this).find('div input').val(newprice);
				//$(this).find('div input').trigger('change');
				//$(this).trigger('change');
				calculateRow($(this).closest('tr').attr('row'));
			});
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice*exchangerate);
				$(this).find('div input').val(newprice);
				//$(this).find('div input').trigger('change');
				//$(this).trigger('change');
				calculateRow($(this).closest('tr').attr('row'));
			});
		}
		calculateTotal();
		*/
	});

	$('body').on('change','.productselector',function(){
		if ($(this).is(':checked')){
			var currentrow=$(this).closest('tr');
			var productquantity=currentrow.find('td.productionorderquantity span.amountright').text();
			currentrow.find('td.quantity div input').val(productquantity);
			currentrow.find('td.unitcost div input').addClass('redbg');
			currentrow.find('td.totalcost div input').addClass('redbg');
		}
		else {
			var currentrow=$(this).closest('tr');
			currentrow.find('td.quantity div input').val(0);
			currentrow.find('td.unitcost div input').removeClass('redbg');
			currentrow.find('td.unitcost div input').removeClass('greenbg');
			currentrow.find('td.totalcost div input').removeClass('redbg');
			currentrow.find('td.totalcost div input').removeClass('greenbg');
		}
	});
	$('body').on('change','.quantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var selectedquantity=parseInt($(this).find('div input').val());
			var productionorderquantity=parseInt($(this).closest('tr').find('td.productionorderquantity span.amountright').text());
			if (selectedquantity>productionorderquantity){
				$(this).find('div input').val(productionorderquantity);
			}
		}
		
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.unitcost div input',function(){
		var currentrow=$(this).closest('tr');
		var rowid=currentrow.attr('row');
		if ($(this).val()>0){
			$(this).removeClass('redbg');
			$(this).addClass('greenbg');
			currentrow.find('td.totalcost div input').removeClass('redbg');
			currentrow.find('td.totalcost div input').addClass('greenbg');
		}
		else {
			$(this).addClass('redbg');
			$(this).removeClass('greenbg');
			currentrow.find('td.totalcost div input').addClass('greenbg');
			currentrow.find('td.totalcost div input').removeClass('greenbg');
		}
		calculateRow(rowid);
		calculateTotal();
		copyRow(rowid);
	});
	
	function calculateRow(rowid) {    
		var currentrow=$('#pendingProducts').find("[row='" + rowid + "']");
		
		var quantity=parseFloat(currentrow.find('td.quantity div input').val());
		var unitcost=parseFloat(currentrow.find('td.unitcost div input').val());
		
		var totalcost=quantity*unitcost;
		
		currentrow.find('td.totalcost div input').val(roundToTwo(totalcost));
	}
	
	function copyRow(rowid) {    
		var currentrow=$('#pendingProducts').find("[row='" + rowid + "']");
		
		//var productid=currentrow.find('td.productid div input').val();
		//var productdescription=currentrow.find('td.productdescription').val();
		//var productionorderid=currentrow.find('td.productionorderid').val();
		var quantity=parseFloat(currentrow.find('td.quantity div input').val());
		var unitcost=parseFloat(currentrow.find('td.unitcost div input').val());
		var totalcost=parseFloat(currentrow.find('td.totalcost div input').val());		
		
		var targetrow=$('#purchaseOrderProducts').find("[row='" + rowid + "']");
		//targetrow.find('td.productid div input').val();
		//targetrow.find('td.productdescription div input').val();
		//targetrow.find('td.productionorderid div input').val();
		targetrow.find('td.productquantity div input').val(quantity);
		targetrow.find('td.productunitcost div input').val(unitcost);
		targetrow.find('td.producttotalcost div input').val(totalcost);		
		
		if (quantity>0&&unitcost>0){
			targetrow.removeClass('hidden');
		}
		else {
			targetrow.addClass('hidden');
		}
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
		$("#pendingProducts tbody tr:not(.purchased)").each(function() {
			var currentProductQuantity = $(this).find('td.quantity div input');
			if (!isNaN(currentProductQuantity.val())){
				var currentQuantity = parseFloat(currentProductQuantity.val());
				totalProductQuantity += currentQuantity;
			}
			
			var currentProduct = $(this).find('td.totalcost div input');
			if (!isNaN(currentProduct.val())){
				var currentCost = parseFloat(currentProduct.val());
				subtotalCost += currentCost;
			}
		});
		$('#purchaseOrderProducts tbody tr.totalrow.subtotal td.quantity span').text(totalProductQuantity.toFixed(0));
		$('#purchaseOrderProducts tbody tr.totalrow.subtotal td.totalcost div input').val(subtotalCost.toFixed(2));
		
		if (booliva){
			ivaCost=roundToTwo(0.15*subtotalCost);
		}
		$('#purchaseOrderProducts tbody tr.totalrow.iva td.totalcost div input').val(ivaCost.toFixed(2));
		totalCost=subtotalCost + ivaCost;
		
		$('#purchaseOrderProducts tbody tr.totalrow.total td.totalcost div input').val(totalCost.toFixed(2));
		
		return false;
	}
	
	
	$('body').on('change','.taskquantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		calculateOtherRow($(this).closest('tr').attr('row'));
		calculateOtherTotal();
	});	
	$('body').on('change','.taskunitcost div input',function(){
		var currentrow=$(this).closest('tr');
		var rowid=currentrow.attr('row');
		/*
		if ($(this).val()>0){
			$(this).removeClass('redbg');
			$(this).addClass('greenbg');
			currentrow.find('td.totalcost div input').removeClass('redbg');
			currentrow.find('td.totalcost div input').addClass('greenbg');
		}
		else {
			$(this).addClass('redbg');
			$(this).removeClass('greenbg');
			currentrow.find('td.totalcost div input').addClass('greenbg');
			currentrow.find('td.totalcost div input').removeClass('greenbg');
		}
		*/
		calculateOtherRow(rowid);
		calculateOtherTotal();
	});
	function calculateOtherRow(rowid) {    
		var currentrow=$('#otherCosts').find("[row='" + rowid + "']");
		
		var quantity=parseFloat(currentrow.find('td.taskquantity div input').val());
		var unitcost=parseFloat(currentrow.find('td.taskunitcost div input').val());
		
		var totalcost=quantity*unitcost;
		
		currentrow.find('td.tasktotalcost div input').val(roundToTwo(totalcost));
	}
	function calculateOtherTotal(){
		//var booliva=$('#PurchaseOrderBoolIva').is(':checked');
		//var totalOtherQuantity=0;
		var subtotalCost=0;
		//var ivaCost=0
		//var totalCost=0
		$("#otherCosts tbody tr:not(.hidden)").each(function() {
			//var currentProductQuantity = $(this).find('td.quantity div input');
			//if (!isNaN(currentProductQuantity.val())){
			//	var currentQuantity = parseFloat(currentProductQuantity.val());
			//	totalProductQuantity += currentQuantity;
			//}
			
			var currentTask = $(this).find('td.tasktotalcost div input');
			if (!isNaN(currentTask.val())){
				var currentCost = parseFloat(currentTask.val());
				subtotalCost += currentCost;
			}
		});
		//$('#otherCosts tbody tr.totalrow.subtotal td.quantity span').text(totalProductQuantity.toFixed(0));
		
		
		$('#otherCosts tbody tr.totalrow.total td.subtotalcost div input').val(subtotalCost.toFixed(2));
		
		//if (booliva){
		//	ivaCost=roundToTwo(0.15*subtotalCost);
		//}
		//$('#purchaseOrderProducts tbody tr.totalrow.iva td.totalcost div input').val(ivaCost.toFixed(2));
		//totalCost=subtotalCost + ivaCost;
		//$('#purchaseOrderProducts tbody tr.totalrow.total td.totalcost div input').val(totalCost.toFixed(2));
		
		return false;
	}
	
	$('body').on('click','.addItem',function(){
		var tableRow=$('#otherCosts tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeItem',function(){
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
		
		$('#pendingProducts tr td div input.productselector').each(function(){
			if ($(this).is(':checked')){
				calculateRow($(this).closest('tr').attr('row'));
				copyRow($(this).closest('tr').attr('row'));
				calculateTotal();
			}
		});
		/*
		$('a.productview').addClass('hidden');
		$('#ContactInfo').addClass('hidden');
		
		getNewQuotationCode();
		updateDueDate();
		updateExchangeRate();
		
		$('#QuotationClientId').trigger('change');
		
		$('#QuotationRemarkUserId').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeDay').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeMonth').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeYear').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeHour').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeMin').addClass('fixed');
		$('#QuotationRemarkRemarkDatetimeMeridian').addClass('fixed');
		*/
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});

</script>
<div class="purchaseOrders form fullwidth">
<?php 
	echo $this->Form->create('PurchaseOrder'); 
	echo "<fieldset>";
	
		echo "<legend>".__('Resumen de Productos por Comprar')."</legend>";
		
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-md-12'>";
					echo $this->Form->input('currency_id',array('default'=>CURRENCY_CS,'label'=>'Moneda'));
					echo "<table id='pendingProducts'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Selección</th>";
								echo "<th>Departamento</th>";
								echo "<th>Producto</th>";
								echo "<th>Descripción Producto</th>";
								echo "<th>Orden de Producción</th>";
								echo "<th>Orden de Compra</th>";
								echo "<th># A comprar</th>";
								echo "<th># Comprado</th>";
								echo "<th>Costo Unitario</th>";
								echo "<th>Costo Total</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
							for($ppop=0;$ppop<count($pendingProductionOrderProducts);$ppop++){
								$productName=$pendingProductionOrderProducts[$ppop]['Product']['name'];
								if (!empty($pendingProductionOrderProducts[$ppop]['Product']['code'])){
									$productName.=" (".$pendingProductionOrderProducts[$ppop]['Product']['code'];
									$productName.=")";
								}
								if (empty($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'])){
									echo "<tr row='".$ppop."'>";
										echo "<td>".$this->Form->input('productselector.'.$ppop,array('label'=>false,'type'=>'checkbox', 'class'=>'productselector'))."</td>";
										echo "<td>";
										foreach ($pendingProductionOrderProducts[$ppop]['ProductionOrderProductDepartment'] as $productDepartment){
											echo $productDepartment['Department']['name'];
										}
										echo "</td>";
										echo "<td>".$this->Html->Link($productName,array('controller'=>'products','action'=>'view',$pendingProductionOrderProducts[$ppop]['Product']['id']))."</td>";
										echo "<td class='productid hidden'>".$pendingProductionOrderProducts[$ppop]['Product']['id']."</td>";
										echo "<td class='salesorderproductid hidden'>".$pendingProductionOrderProducts[$ppop]['ProductionOrderProduct']['sales_order_product_id']."</td>";
										echo "<td class='productdescription'>".$pendingProductionOrderProducts[$ppop]['ProductionOrderProduct']['product_description']."</td>";
										echo "<td>".$pendingProductionOrderProducts[$ppop]['ProductionOrder']['production_order_code']."</td>";
										echo "<td class='productionorderid hidden'>".$pendingProductionOrderProducts[$ppop]['ProductionOrder']['id']."</td>";
										echo "<td class='centered'>-</td>";
										echo "<td class='productionorderquantity'><span class='amountright'>".$pendingProductionOrderProducts[$ppop]['ProductionOrderProduct']['product_quantity']."</span></td>";
										echo "<td class='quantity amount'>".$this->Form->input('purchase_order_product_quantity.'.$ppop,array('label'=>false,'type'=>'decimal','default'=>0))."</td>";
										if ($currencyId==CURRENCY_CS){
											echo "<td class='unitcost CScurrency'><span class='currency'></span>".$this->Form->input('purchase_order_product_unit_cost.'.$ppop,array('label'=>false,'type'=>'decimal','default'=>0))."</td>";
											echo "<td class='totalcost CScurrency'><span class='currency'></span>".$this->Form->input('purchase_order_product_total_cost.'.$ppop,array('label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly'))."</td>";
										}
										if ($currencyId==CURRENCY_USD){
											echo "<td class='unitcost USDcurrency'><span class='currency'></span>".$this->Form->input('purchase_order_product_unit_cost.'.$ppop,array('label'=>false,'type'=>'decimal','default'=>0))."</td>";
											echo "<td class='totalcost USDcurrency'><span class='currency'></span>".$this->Form->input('purchase_order_product_total_cost.'.$ppop,array('label'=>false,'type'=>'decimal','default'=>0,'readonly'=>'readonly'))."</td>";
										}
									echo "</tr>";
								}
							}
						echo "</tbody>";
					echo "</table>";
				echo "</div>";
			echo "</div>";
			
			echo "<div class='row'>";
				echo "<h2>Datos de Orden de Compra</h2>";
				echo "<div class='col-md-3'>";
					echo $this->Form->input('purchase_order_date',array('dateFormat'=>'DMY'));
					echo $this->Form->input('purchase_order_code');
					echo $this->Form->input('provider_id',array('default'=>0,'empty'=>array('0'=>'Seleccione Proveedor')));
					echo $this->Form->input('user_id',array('type'=>'hidden','value'=>$loggedUserId));
					//echo $this->Form->input('bool_annulled',array('checked'=>false));
					echo $this->Form->input('bool_iva',array('checked'=>true));
					//echo $this->Form->input('currency_id',array('default'=>CURRENCY_CS));
					echo $this->Form->input('payment_mode_id',array('default'=>0,'empty'=>array('0'=>'Seleccione Modo de Pago')));
					echo $this->Form->input('payment_document');
					//echo $this->Form->input('bool_received');
				echo "</div>";
				echo "<div class='col-md-9'>";
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
								echo "<td class='tasktotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$oc.'.task_total_cost',array('label'=>false,'default'=>$requestOtherCosts[$oc]['PurchaseOrderOtherCost']['task_total_cost']))."</td>";
								
								echo "<td>";
										echo "<button class='removeItem' type='button'>".__('Remove Item')."</button>";
										echo "<button class='addItem' type='button'>".__('Add Item')."</button>";
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
								echo "<td class='tasktotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderOtherCost.'.$j.'.task_total_cost',array('label'=>false,'default'=>0))."</td>";
								echo "<td>";
										echo "<button class='removeItem' type='button'>".__('Remover Costo')."</button>";
										echo "<button class='addItem' type='button'>".__('Otro Costo')."</button>";
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
				echo "</div>";
				echo "<div class='col-md-12'>";
					echo "<h3>Productos en Orden de Compra</h3>";
					echo "<table id='purchaseOrderProducts'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Departamento</th>";
								echo "<th>Orden de Producción</th>";
								echo "<th>Producto</th>";
								echo "<th>Descripción</th>";
								echo "<th>Cantidad</th>";
								echo "<th>Costo Unitario</th>";
								echo "<th>Costo Total</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
						$pop=0;
						foreach ($pendingProductionOrderProducts as $pendingProduct){
							if (empty($pendingProduct['PurchaseOrderProduct'])){
								echo "<tr row='".$pop."' class='hidden'>";
								
									echo "<td>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.department_id',array('label'=>false,'class'=>'fixed','value'=>$pendingProduct['ProductionOrderProductDepartment'][0]['department_id'],'empty'=>array('0'=>'Seleccione Departamento')))."</td>";
									echo "<td>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.production_order_id',array('label'=>false,'readonly'=>'readonly','value'=>$pendingProduct['ProductionOrder']['production_order_code']))."</td>";
									echo "<td>";
										echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_id',array('label'=>false,'value'=>$pendingProduct['Product']['id'],'class'=>'fixed'));
										echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.production_order_product_id',array('label'=>false,'value'=>$pendingProduct['ProductionOrderProduct']['id'],'type'=>'hidden'));
										echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.sales_order_product_id',array('label'=>false,'value'=>$pendingProduct['ProductionOrderProduct']['sales_order_product_id'],'type'=>'hidden'));
									echo "</td>";
									echo "<td>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_description',array('label'=>false,'value'=>$pendingProduct['ProductionOrderProduct']['product_description'],'readonly'=>'readonly'))."</td>";
									echo "<td class='productquantity amount'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_quantity',array('label'=>false,'type'=>'decimal','readonly'=>'readonly'))."</td>";
									echo "<td class='productunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_unit_cost',array('label'=>false,'type'=>'decimal','readonly'=>'readonly'))."</td>";
									echo "<td class='producttotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_total_cost',array('label'=>false,'type'=>'decimal','readonly'=>'readonly'))."</td>";
								echo "</tr>";
							}
							$pop++;
						}
							echo "<tr class='totalrow subtotal'>";
								echo "<td>Subtotal</td>";
								echo "<td></td>";
								echo "<td></td>";
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
								echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_iva',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
							echo "</tr>";		
							echo "<tr class='totalrow total'>";
								echo "<td>Total</td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_total',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
							echo "</tr>";		
						echo "</tbody>";
					echo "</table>";
				echo "</div>";
			echo "</div>";
		echo "</div>";	
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
	
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('purchase_order_date')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('purchase_order_code')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('provider_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('user_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('bool_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_subtotal')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_iva')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_total')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('cost_other_total')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_mode_id')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('payment_document')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('bool_received')."</th>";
			//$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$pageBody="";

	$subtotalCS=0;
	$ivaCS=0;
	$totalCS=0;
	$totalOtherCS=0;
	$subtotalUSD=0;
	$ivaUSD=0;
	$totalUSD=0;
	$totalOtherUSD=0;
	
	foreach ($purchaseOrders as $purchaseOrder){ 
		$purchaseOrderDateTime=new DateTime($purchaseOrder['PurchaseOrder']['purchase_order_date']);
		
		if ($purchaseOrder['Currency']['id']==CURRENCY_CS){
			$currencyClass=" class='CScurrency'";
			$subtotalCS+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
			$ivaCS+=$purchaseOrder['PurchaseOrder']['cost_iva'];
			$totalCS+=$purchaseOrder['PurchaseOrder']['cost_total'];
			$totalCS+=$purchaseOrder['PurchaseOrder']['cost_other_total'];
			
			//added calculation of totals in US$
			$subtotalUSD+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$ivaUSD+=round($purchaseOrder['PurchaseOrder']['cost_iva']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$totalUSD+=round($purchaseOrder['PurchaseOrder']['cost_total']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$totalUSD+=round($purchaseOrder['PurchaseOrder']['cost_other_total']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
		}
		elseif ($purchaseOrder['Currency']['id']==CURRENCY_USD){
			$currencyClass=" class='USDcurrency'";
			$subtotalUSD+=$purchaseOrder['PurchaseOrder']['cost_subtotal'];
			$ivaUSD+=$purchaseOrder['PurchaseOrder']['cost_iva'];
			$totalUSD+=$purchaseOrder['PurchaseOrder']['cost_total'];
			$totalUSD+=$purchaseOrder['PurchaseOrder']['cost_other_total'];
			
			//added calculation of totals in CS$
			$subtotalCS+=round($purchaseOrder['PurchaseOrder']['cost_subtotal']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$ivaCS+=round($purchaseOrder['PurchaseOrder']['cost_iva']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$totalCS+=round($purchaseOrder['PurchaseOrder']['cost_total']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
			$totalCS+=round($purchaseOrder['PurchaseOrder']['cost_other_total']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
		}

		
		$pageRow="";
			$pageRow.="<td>".$purchaseOrderDateTime->format('d-m-Y')."</td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['PurchaseOrder']['purchase_order_code'].($purchaseOrder['PurchaseOrder']['bool_annulled']?" (Anulada)":""),array('action'=>'view',$purchaseOrder['PurchaseOrder']['id']))."</td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['Provider']['name'], array('controller' => 'providers', 'action' => 'view', $purchaseOrder['Provider']['id']))."</td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['User']['username'], array('controller' => 'users', 'action' => 'view', $purchaseOrder['User']['id']))."</td>";
			$pageRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_iva']?__('Yes'):__('No'))."</td>";
			
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_subtotal'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_iva'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_total'])."</span></td>";
			$pageRow.="<td".$currencyClass."><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='amountright'>".h($purchaseOrder['PurchaseOrder']['cost_other_total'])."</span></td>";
			$pageRow.="<td>".$this->Html->link($purchaseOrder['PaymentMode']['name'], array('controller' => 'payment_modes', 'action' => 'view', $purchaseOrder['PaymentMode']['id']))."</td>";
			$pageRow.="<td>".h($purchaseOrder['PurchaseOrder']['payment_document'])."</td>";
			$pageRow.="<td>".($purchaseOrder['PurchaseOrder']['bool_received']?__('Yes'):__('No'))."</td>";

			//$pageRow.="<td class='actions'>";
			//	$pageRow.=$this->Html->link(__('View'), array('action' => 'view', $purchaseOrder['PurchaseOrder']['id']));
			//	if ($bool_edit_permission){
			//		$pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $purchaseOrder['PurchaseOrder']['id']));
			//	}
			//	if ($bool_delete_permission){
			//		$pageRow.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $purchaseOrder['PurchaseOrder']['id']), array(), __('Está seguro que quiere eliminar orden de compra # %s?', $purchaseOrder['PurchaseOrder']['purchase_order_code']));
			//	}
			//$pageRow.="</td>";

		if ($purchaseOrder['PurchaseOrder']['bool_annulled']){
			$pageBody.="<tr class='italic'>".$pageRow."</tr>";
		}
		else {
			$pageBody.="<tr>".$pageRow."</tr>";
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
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalOtherCS."</span></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
	}
	
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
			$pageTotalRow.="<td class='USDcurrency'><span class='currency'></span><span class='amountright'>".$totalOtherUSD."</td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
		$pageTotalRow.="</tr>";
	}
	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="ordenes_compra";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo "<h2>Ordenes de Compra con productos pendientes de entregar</h2>";
	echo $pageOutput;
	
	$pageHeader="";
	$pageHeader.="<thead>";
		$pageHeader.="<tr>";
			//$pageHeader.="<th>Selección</th>";
			$pageHeader.="<th>Departamento</th>";
			$pageHeader.="<th>Producto</th>";
			$pageHeader.="<th>Descripción Producto</th>";
			$pageHeader.="<th>Orden de Producción</th>";
			$pageHeader.="<th>Orden de Compra</th>";
			//$pageHeader.="<th># A comprar</th>";
			$pageHeader.="<th># Comprado</th>";
			$pageHeader.="<th>Costo Unitario</th>";
			$pageHeader.="<th>Costo Total</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
		
	$totalProductQuantity=0;
	$totalCostCS=0;
	$totalCostUSD=0;
	$pageBody="";
	for($ppop=0;$ppop<count($pendingProductionOrderProducts);$ppop++){
		if (!empty($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'])){
			$productName=$pendingProductionOrderProducts[$ppop]['Product']['name'];
		
			//pr($pendingProductionOrderProducts[$ppop]);
			$totalProductQuantity+=$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_quantity'];
			
			if ($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['PurchaseOrder']['Currency']['id']==CURRENCY_CS){
				$currencyClass=" class='CScurrency'";
				$totalCostCS+=$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost'];
				
				
				$totalCostUSD+=round($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost']/$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				
			}
			elseif ($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['PurchaseOrder']['Currency']['id']==CURRENCY_USD){
				$currencyClass=" class='USDcurrency'";
				$totalCostUSD+=$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost'];
				
				//added calculation of totals in CS$
				$totalCostCS+=round($pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost']*$purchaseOrder['PurchaseOrder']['exchange_rate'],2);
				
			}
			$totalCostCS+=$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost'];
			
			if (!empty($pendingProductionOrderProducts[$ppop]['Product']['code'])){
				$productName.=" (".$pendingProductionOrderProducts[$ppop]['Product']['code'];
				$productName.=")";
			}
			$pageRow="";
			$pageRow.="<tr class='purchased'>";
				$pageRow.="<td>".$pendingProductionOrderProducts[$ppop]['Product']['Department']['name']."</td>";
				$pageRow.="<td>".$this->Html->Link($productName,array('controller'=>'products','action'=>'view',$pendingProductionOrderProducts[$ppop]['Product']['id']))."</td>";
				$pageRow.="<td>".$pendingProductionOrderProducts[$ppop]['ProductionOrderProduct']['product_description']."</td>";
				$pageRow.="<td>".$pendingProductionOrderProducts[$ppop]['ProductionOrder']['production_order_code']."</td>";
				$pageRow.="<td>".$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['PurchaseOrder']['purchase_order_code']."</td>";										
				$pageRow.="<td class='amount'><span class='amountright'>".$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_quantity']."</span></td>";
				$pageRow.="<td".$currencyClass."><span class='currency'></span><span class='amountright'>".$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_unit_cost']."</span></td>";
				$pageRow.="<td".$currencyClass."><span class='currency'></span><span class='amountright'>".$pendingProductionOrderProducts[$ppop]['PurchaseOrderProduct'][0]['product_total_cost']."</span></td>";
			$pageRow.="</tr>";
			
			if ($purchaseOrder['PurchaseOrder']['bool_annulled']){
				$pageBody.="<tr class='italic'>".$pageRow."</tr>";
			}
			else {
				$pageBody.="<tr>".$pageRow."</tr>";
			}
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
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td></td>";
			$pageTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalCS."</span></td>";
		$pageTotalRow.="</tr>";
	}
	
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
		$pageTotalRow.="</tr>";
	}
	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";

	$table_id="purchasedProducts";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo "<h2>Productos pendientes de entregar</h2>";
	echo $pageOutput;
?>
</div>