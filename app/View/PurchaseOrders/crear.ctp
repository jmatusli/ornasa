<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
  //jsPlants=<?php  echo json_encode($plants); ?>;
  //jsWarehousePlants=<?php  echo json_encode($warehousePlants); ?>;
  //jsProviderNatures=<?php  //echo json_encode($providerNatures); ?>;
  
  jsProductUnits=<?php  echo json_encode($productUnits); ?>;
  jsProductPackagingUnits=<?php  echo json_encode($productPackagingUnits); ?>;
  jsProductDefaultCosts=<?php  echo json_encode($productDefaultCosts); ?>;
  
  $('body').on('change','#PurchaseOrderWarehouseId',function(){	
    //setPurchaseOrderCode()
    //showPlant();
    $('#refresh').trigger('click')
  });
/*
  function setPurchaseOrderCode(){
    //$('#clientProcessMsg').html('Calculando el código de la orden de venta');
		//showMessageModel();
    
		var warehouseId=parseInt($('#PurchaseOrderWarehouseId').val());
		if (warehouseId>0){
    	$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchaseOrders/getPurchaseOrderCode/',
				data:{"warehouseId":warehouseId},
				cache: false,
				type: 'POST',
				success: function (purchaseOrderCode) {
					$('#PurchaseOrderPurchaseOrderCode').val(purchaseOrderCode);
          $('#PurchaseOrderCrearForm legend').html("Crear Nueva Orden de Compra "+purchaseOrderCode);
          //hideMessageModal()
          
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
          //$('#clientProcessMsg').html('Se ha producido un error mientras se calculaba el código');
          //$('#modalFooter').removeClass('d-none');
          //$('#modalFooter').removeClass('hidden');
				}
			});
		}
    else {
      $('#PurchaseOrderPurchaseOrderCode').val('');
      $('#PurchaseOrderCrearForm legend').html("Crear Nueva Orden de Venta");
      //hideMessageModal()
    }
	}
  
  function showPlant(){
    var warehouseId=parseInt($('#PurchaseOrderWarehouseId').val());
    var plantId=jsWarehousePlants[warehouseId];
    var plantName=jsPlants[plantId];
    $('#PurchaseOrderPlant').text(plantName);
  }
*/  
  
  $('body').on('change','#PurchaseOrderBoolAnnulled',function(){	
    hideFieldsForAnnulled();
	});	
 
	function hideFieldsForAnnulled(){
		if ($('#PurchaseOrderBoolAnnulled').is(':checked')){
		  $('.productid').each(function(index,item){
        $(this).find('div select').val(0);
      });
      $('.productquantity').each(function(index,item){
        $(this).find('div input').val(0);
      });
      $('.productunitcost').each(function(index,item){
        $(this).find('div input').val(0);
      });
      $('.producttotalcost').each(function(index,item){
        $(this).find('div input').val(0);
      });
      $('#PurchaseOrderSubtotal').val(0);
      $('#PurchaseOrderIva').val(0);
      $('#PurchaseOrderTotal').val(0);
      $('#PurchaseOrderCostSubtotal').val(0);
      $('#PurchaseOrderCostIva').val(0);
      $('#PurchaseOrderCostTotal').val(0);
      
			$('#PurchaseOrderCurrencyId').parent().addClass('hidden');
			$('#PurchaseOrderBoolCredit').parent().addClass('hidden');
			$('#divDueDate').addClass('hidden');
			$('#PurchaseOrderBoolIva').parent().addClass('hidden');
			$('#purchaseOrderProducts').addClass('hidden');
		}
		else {
			$('#PurchaseOrderCurrencyId').parent().removeClass('hidden');
			$('#PurchaseOrderBoolCredit').parent().removeClass('hidden');
			if ($('#PurchaseOrderBoolCredit').is(':checked')){
				$('#divDueDate').removeClass('hidden');
			}
			else {
				//$('#PurchaseOrderCashboxAccountingCodeId').parent().removeClass('hidden');
			}
			$('#PurchaseOrderBoolIva').parent().removeClass('hidden');
			$('#purchaseOrderProducts').removeClass('hidden');
		}
	}
  
	$('body').on('change','#PurchaseOrderCurrencyId',function(){
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
      $('span.currencyrighttop').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
      $('span.currencyrighttop').text("C$");
		}
	});
  
  $('#PurchaseOrderPurchaseOrderDateDay').change(function(){
		setDueDate();
	});	
	$('#PurchaseOrderPurchaseOrderDateMonth').change(function(){
		setDueDate();
	});	
	$('#PurchaseOrderPurchaseOrderDateYear').change(function(){
		setDueDate();
	});	
  
  $('body').on('change','#PurchaseOrderProviderId',function(){	
    var providerId=$(this).val();
    //$('#PurchaseOrderBoolPackaging').prop('checked',(jsProviderNatures[providerId] == <?php echo PROVIDER_NATURE_CONSUMABLE; ?>)?true:false);
    
		setDueDate();
		setOrderType();
	});  
	function setDueDate(){
		var providerid=$('#PurchaseOrderProviderId').children("option").filter(":selected").val();
		var emissionday=$('#PurchaseOrderPurchaseOrderDateDay').children("option").filter(":selected").val();
		var emissionmonth=$('#PurchaseOrderPurchaseOrderDateMonth').children("option").filter(":selected").val();
		var emissionyear=$('#PurchaseOrderPurchaseOrderDateYear').children("option").filter(":selected").val();
		if (providerid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchaseOrders/setduedate/',
				data:{"providerid":providerid,"emissionday":emissionday,"emissionmonth":emissionmonth,"emissionyear":emissionyear},
				cache: false,
				type: 'POST',
				success: function (data) {
					$('#divDueDate').html(data);
				},
				error: function(e){
					alert(e.responseText);
				}
			});
		}
	}
	function setOrderType(){
		var providerid=$('#PurchaseOrderProviderId').children("option").filter(":selected").val();
		if (providerid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>third_parties/getprovidercreditdays/',
				data:{"providerid":providerid},
				cache: false,
				type: 'POST',
				success: function (creditdays) {
          $('#CreditDays').val(creditdays);  
					if (creditdays==0){
						$('#PurchaseOrderBoolCredit').prop('checked',false);
					}
					else {
						$('#PurchaseOrderBoolCredit').prop('checked',true);
					}
					$('#PurchaseOrderBoolCredit').trigger("change");
				},
				error: function(e){
					alert(e.responseText);
				}
			});
		}
	}
  
  $('body').on('change','#PurchaseOrderBoolCredit',function(){	
    setCreditConditions();
  });

  function setCreditConditions(){      
    if ($('#PurchaseOrderBoolCredit').is(':checked')){
      $('#divDueDate').removeClass('hidden');
    }
    else {
      $('#divDueDate').addClass('hidden');
    }
	}  
  
	$('body').on('change','.productid',function(){
    var thisRow=$(this).closest('tr');
    
    var productId=thisRow.find('td.productid div select').val();
    if (productId > 0){
      var packagingUnit=jsProductPackagingUnits[productId];
      thisRow.find('.productpackagingunit').val(packagingUnit)
      thisRow.find('.productquantity div input').val(packagingUnit)
      calculatePackagingUnits(thisRow);
      thisRow.find('.productunit div select').val(jsProductUnits[productId])  
      thisRow.find('.productunitcost div input').val(jsProductDefaultCosts[productId])
    }
    else {
      thisRow.find('.productpackagingunit').val(0)
      thisRow.find('.productquantity div input').val(0)
      calculatePackagingUnits(thisRow);
      thisRow.find('.productunit div select').val(0)  
      thisRow.find('.productunitcost div input').val(0)
    }
		calculateRow(thisRow.attr('row'));
    calculateTotal();
	});
  
	$('body').on('change','.productquantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
    var thisRow=$(this).closest('tr');
    calculatePackagingUnits(thisRow);
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
  
  function calculatePackagingUnits(currentRow){
    var packagingUnit=parseInt(currentRow.find('.productpackagingunit').val());
    var productQuantity=parseInt(currentRow.find('.productquantity').find('div input').val());
    var productPackaging=productQuantity + ' Uds'
    if (packagingUnit>0 && productQuantity >= packagingUnit){
      var numPacks=Math.floor(productQuantity/packagingUnit);
      var numUnits=productQuantity%packagingUnit;
      productPackaging=numPacks + " Uds de empaque y " + numUnits + " Uds" 
    }
    currentRow.find('.packagingunits').find('div input').val(productPackaging);
  }
  
	$('body').on('change','.productunitcost',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			//var roundedValue=roundToFive($(this).find('div input').val());
			//$(this).find('div input').val(roundedValue);
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
		$("#purchaseOrderProducts tbody tr:not(.totalrow):not(.hidden)").each(function() {
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
    $('#PurchaseOrderSubtotal').val(subtotalCost.toFixed(2));
		
		if (booliva){
			ivaCost=roundToTwo(0.15*subtotalCost);
		}
		$('#purchaseOrderProducts tbody tr.iva td.totalcost div input').val(ivaCost.toFixed(2));
    $('#PurchaseOrderIva').val(ivaCost.toFixed(2));
		
    totalCost=subtotalCost + ivaCost;
		$('#purchaseOrderProducts tbody tr.totalrow.total td.totalcost div input').val(totalCost.toFixed(2));
    $('#PurchaseOrderTotal').val(totalCost.toFixed(2));
		
		return false;
	}
	
	$('body').on('click','.addProduct',function(){
		var tableRow=$('#purchaseOrderProducts tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeProduct',function(){
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
      $('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
      $('span.currencyrighttop').text('US$ ');
		}
	}
	
  function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	} 
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		
		$('select.fixed option:not(:selected)').attr('disabled', true);
    
		hideFieldsForAnnulled();
		
		if (!$('#PurchaseOrderBoolCredit').is(':checked')){
			$('#divDueDate').addClass('hidden');
		}
		
		var currencyid=$('#PurchaseOrderCurrencyId').children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
    //$('#PurchaseOrderWarehouseId').trigger('change');
    
    $('#saving').addClass('hidden');
	});
  
  $('body').on('click','.save',function(e){	
    $(".save").data('clicked', true);
  });
  $('body').on('submit','#PurchaseOrderCrearForm',function(e){	
    if($(".save").data('clicked'))
    {
      $('.save').attr('disabled', 'disabled');
      $("#PurchaseOrderCrearForm").fadeOut();
      $("#saving").removeClass('hidden');
      $("#saving").fadeIn();
      var opts = {
          lines: 12, // The number of lines to draw
          length: 7, // The length of each line
          width: 4, // The line thickness
          radius: 10, // The radius of the inner circle
          color: '#000', // #rgb or #rrggbb
          speed: 1, // Rounds per second
          trail: 60, // Afterglow percentage
          shadow: false, // Whether to render a shadow
          hwaccel: false // Whether to use hardware acceleration
      };
      var target = document.getElementById('saving');
      var spinner = new Spinner(opts).spin(target);
    }
    
    return true;
  });

</script>
<div class="purchaseOrders form fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la orden de compra...</p>";
    echo "</div>";
  echo "</div>";
  
	echo $this->Form->create('PurchaseOrder',['style'=>'width:100%']); 
	echo "<fieldset id='mainform'>";
		echo "<legend>".'Crear Orden de Compra '.$purchaseOrderCode."</legend>";
		echo '<h3 id="PurchaseOrderPlant">'.($plantId>0?$plants[$plantId]:'').'</h3>';
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-6'>";
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          
          echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
						
          echo $this->Form->input('purchase_order_date',['dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')+1]);
          echo $this->Form->input('purchase_order_code',['value'=>$purchaseOrderCode,'readonly'=>'readonly']);
          
          echo $this->Form->input('provider_id',['default'=>0,'empty'=>['0'=>'Seleccione Proveedor']]);
          //echo $this->Form->input('bool_packaging',['label'=>'Compra Suministros?']);
          echo $this->Form->input('purchase_order_state_id',['default'=>PURCHASE_ORDER_STATE_AWAITING_AUTHORIZATION,'readonly'=>'readonly','type'=>'hidden']);
          
          echo $this->Form->input('user_id',['type'=>'hidden','value'=>$loggedUserId]);
          echo $this->Form->input('bool_annulled',['checked'=>false]);
          echo $this->Form->input('bool_iva',['checked'=>true]);
          echo $this->Form->input('currency_id',['default'=>CURRENCY_USD,'label'=>'Moneda']);
          echo $this->Form->input('bool_credit',['type'=>'checkbox','label'=>'Crédito']);
          echo "<div id='divDueDate'>";
            echo $this->Form->input('due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>(date('Y')+1)]);
          echo "</div>";
          if ($userRoleId === ROLE_ADMIN){
            echo $this->Form->input('bool_authorized',['type'=>'checkbox','label'=>'Autorizada']);
          }
          //echo $this->Form->input('cashbox_accounting_code_id',['empty'=>['0'=>'Seleccione Caja'],'class'=>'narrow','options'=>$accountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN]);
				echo "</div>";

				echo "<div class='col-sm-3' style='padding:0 5px 0 30px;'>";
          echo "<h4>".__('Costo de Orden de Compra')."</h4>";			
          echo $this->Form->input('subtotal',['label'=>__('SubTotal'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
          echo $this->Form->input('iva',['label'=>__('IVA'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
          echo $this->Form->input('total',['label'=>__('Total'),'readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;']);
				echo "</div>";
				echo "<div class='col-sm-2 actions'>";
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
          echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
					echo "<h3>Productos en Orden de Compra</h3>";
					echo "<table id='purchaseOrderProducts' style='font-size:13px;'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>Producto</th>";
								echo "<th>Unidades de Empaque</th>";
								echo "<th>Cantidad</th>";
                echo "<th>Und</th>";
								echo "<th style='width:12%;'>Costo Unitario</th>";
								echo "<th style='width:12%;'>Costo Total</th>";
								echo "<th>Acciones</th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody style='font-size:75%;'>";
						$counter=0;
						
						for ($pop=0;$pop<count($requestProducts);$pop++){
							echo '<tr row="'.$pop.'">';
								echo "<td class='productid width100'>";
									echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_id',[
                    'label'=>false,
                    'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_id'],
                    'empty'=>['0'=>'Seleccione Producto']]);
                  echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_packaging_unit',[
                    'label'=>false,
                    'value'=>0,
                    'type'=>'hidden',
                    'class'=>'productpackagingunit'
                  ]);
								echo "</td>";
								echo "<td class='packagingunits'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.packaging_units',['label'=>false,'type'=>'text','readonly'=>'readonly'])."</td>";
								echo "<td class='productquantity amount'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_quantity',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$pop]['PurchaseOrderProduct']['product_quantity'],'required'=>false])."</td>";
                echo "<td class='productunit'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.unit_id',['label'=>false,'value'=>$requestProducts[$pop]['PurchaseOrderProduct']['unit_id'],'empty'=>[0=>'-- Unidad --'],])."</td>";
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
								echo '<tr row="'.$pop.'">';
							}
							else {
                echo '<tr row="'.$pop.'" class="hidden">';
							}
								echo "<td class='productid width100'>";
									echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Producto']]);
                  echo $this->Form->input('PurchaseOrderProduct.'.$pop.'.product_packaging_unit',['label'=>false,'value'=>0,'type'=>'hidden','class'=>'productpackagingunit']);
								echo "</td>";
								echo "<td class='packagingunits'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.packaging_units',['label'=>false,'type'=>'text','readonly'=>'readonly'])."</td>";
								echo "<td class='productquantity amount'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0])."</td>";
								echo "<td class='productunit'>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.unit_id',['label'=>false,'default'=>0,'empty'=>[0=>'-- Unidad --'],])."</td>";
								
                echo "<td class='productunitcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_unit_cost',['label'=>false,'type'=>'decimal','default'=>0])."</td>";
								echo "<td class='producttotalcost'><span class='currency'></span>".$this->Form->input('PurchaseOrderProduct.'.$pop.'.product_total_cost',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>0])."</td>";
								echo "<td>";
										echo "<button class='removeProduct' type='button'>".__('Remover Producto')."</button>";
										echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
								echo "</td>";
							echo "</tr>";
						}
							echo "<tr class='totalrow subtotal'>";
								echo "<td>Subtotal</td>";
								echo "<td></td>";
								echo "<td class='productquantity amount right'><span></span></td>";
								echo "<td></td>";
                echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                echo "<td></td>";
							echo "</tr>";		
							echo "<tr class='iva'>";
								echo "<td>IVA</td>";
								echo "<td></td>";
								echo "<td></td>";
                echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                echo "<td></td>";
							echo "</tr>";		
							echo "<tr class='totalrow total'>";
								echo "<td>Total</td>";
								echo "<td></td>";
								echo "<td></td>";
                echo "<td></td>";
								echo "<td></td>";
								echo "<td class='totalcost amount right'><span class='currency'></span>".$this->Form->input('cost_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                echo "<td></td>";
							echo "</tr>";		
						echo "</tbody>";
					echo "</table>";
				echo "</div>";
        echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
      echo "</div>";
    echo "</div>";
  echo "</fieldset>";
	echo $this->Form->end();
?>
</div>

<?php 	
	
?>

