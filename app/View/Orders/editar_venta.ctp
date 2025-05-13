<?php
  echo $this->Html->script('sales_modals');
?>
<script>
  var updatePrices=true;
  var updateProductsFromSalesOrder=false;
  
  var boolCreditAuthorized=0;
  var creditAuthorizationUserId=0;
  var jsOtherProducts=<?php  echo json_encode($otherProducts); ?>;
  var adminUserIds=<?php  echo json_encode($adminUserIds); ?>;
  
  var jsRawMaterialsPerProduct=<?php  echo json_encode($rawMaterialsAvailablePerFinishedProduct); ?>;
  var jsProductCategoriesPerProduct=<?php  echo json_encode($productCategoriesPerProduct); ?>;
  var jsProductTypesPerProduct=<?php echo json_encode($productTypesPerProduct); ?>;

  var jsGenericClientIds=<?php  echo json_encode($genericClientIds); ?>;
  $('body').on('click','#closeMsg',function(e){	
    $('#modalFooter').addClass('hidden');
    hideMessageModal()
  });
  
  $('body').on('change','#OrderSalesOrderId',function(){
    updateProductsFromSalesOrder=true;
    setSalesOrderClientAndCurrencyData();
  });  
  
  function setSalesOrderClientAndCurrencyData(){
    var salesOrderId=$('#OrderSalesOrderId').val();
    if (parseInt(salesOrderId)>0){
      $('#clientProcessMsg').html('Copiando los datos del cliente desde la orden de venta');
      showMessageModal();
      
      updatePrices=false;
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>salesOrders/getSalesOrderInfo/',
				data:{"salesOrderId":salesOrderId},
        dataType:'json',
				cache: false,
				type: 'POST',
				success: function (salesOrder) {
					var salesOrderClientId=salesOrder.SalesOrder.client_id;
          
          if (salesOrderClientId > 0 ){
            $('#OrderThirdPartyId').val(salesOrderClientId)
            $('#OrderThirdPartyId').addClass('fixed')            
          }
          else {
            $('#OrderThirdPartyId').val(0)
          }
          
          var boolClientGeneric = salesOrder.Client.bool_generic?1:0;
          
          var salesOrderClientName=salesOrder.SalesOrder.client_name;
          var salesOrderClientPhone=salesOrder.SalesOrder.client_phone;
          var salesOrderClientEmail=salesOrder.SalesOrder.client_email;
          var salesOrderClientRuc=salesOrder.SalesOrder.client_ruc;
          var salesOrderClientTypeId=salesOrder.SalesOrder.client_type_id;
          var salesOrderZoneId=salesOrder.SalesOrder.zone_id;
          var salesOrderClientAddress=salesOrder.SalesOrder.client_address;
          var salesOrderDeliveryAddress=salesOrder.SalesOrder.delivery_address;
          
          var salesOrderObservation=salesOrder.SalesOrder.observation;
          
          $('#OrderClientGeneric').val(boolClientGeneric)
          $('#OrderClientName').val(salesOrderClientName)
          $('#OrderClientPhone').val(salesOrderClientPhone)
          $('#OrderClientEmail').val(salesOrderClientEmail)
          $('#OrderClientRuc').val(salesOrderClientRuc)
          
          if (salesOrderClientTypeId > 0){
            $('#OrderClientTypeId').val(salesOrderClientTypeId)
            $('#OrderClientTypeId').addClass('fixed');
            $('#OrderClientTypeId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#OrderClientTypeId').removeClass('fixed');
            $('#OrderClientTypeId option').attr('disabled', false);
            $('#OrderClientTypeId').val(0)
          }
          
          if (salesOrderZoneId > 0){
            $('#OrderZoneId').val(salesOrderZoneId)
            $('#OrderZoneId').addClass('fixed');
            $('#OrderZoneId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#OrderZoneId').removeClass('fixed');
            $('#OrderZoneId option').attr('disabled', false);
            $('#OrderZoneId').val(0)
          }
          
          $('#OrderClientAddress').val(salesOrderClientAddress)
          $('#OrderDeliveryAddress').val(salesOrderDeliveryAddress)
          
          $('#OrderComment').val(salesOrderObservation)

          //var salesOrderDriverUserId=salesOrder.SalesOrder.driver_user_id;
          //var salesOrderVehicleId=salesOrder.SalesOrder.vehicle_id;
          //$('#OrderDriverUserId').val(salesOrderDriverUserId)
          //$('#OrderVehicleId').val(salesOrderVehicleId)

          var salesOrderBoolDelivery=salesOrder.SalesOrder.bool_delivery;
          if (salesOrderBoolDelivery){
            $('#OrderBoolDelivery').prop('checked', true);
            $('#OrderDeliveryAddress').closest('div').removeClass('d-none');
          }
          else {
            $('#OrderBoolDelivery').prop('checked', false);
            $('#OrderDeliveryAddress').closest('div').addClass('d-none');
          }
          var salesOrderDeliveryId=salesOrder.SalesOrder.delivery_id;
          $('#OrderDeliveryId').val(salesOrderDeliveryId)
          if (salesOrderDeliveryId == 0){
            $('#deliveryAlert').addClass('hidden');
          }
          else {
            $('#deliveryAlert').removeClass('hidden');
          }

          $('#clientProcessMsg').html('Copiando los datos de moneda desde la orden de venta');
          var salesOrderCurrencyId=salesOrder.SalesOrder.currency_id;
          $('#InvoiceCurrencyId').val(salesOrderCurrencyId)
          $('#InvoiceCurrencyId').addClass('fixed');
          
          $('#clientProcessMsg').html('Copiando los datos de usuario desde la orden de venta');
          var salesOrderVendorUserId=salesOrder.SalesOrder.vendor_user_id;
          $('#OrderVendorUserId option:not(:selected)').attr('disabled', false);
          $('#OrderVendorUserId').val(salesOrderVendorUserId);
          $('#OrderVendorUserId option:not(:selected)').attr('disabled', true);
          creditAuthorizationUserId=salesOrder.SalesOrder.credit_authorization_user_id;
          $('#OrderCreditAuthorizationUserId').val(creditAuthorizationUserId);
          
          $('#clientProcessMsg').html('Copiando los datos de retención desde la orden de venta');
          
          var salesOrderBoolIva=salesOrder.SalesOrder.bool_iva;
          if (salesOrderBoolIva){
            $('#InvoiceBoolIva').prop('checked', true);
          }
          else {
            $('#InvoiceBoolIva').prop('checked', false);
          }
          
          var salesOrderBoolRetention=salesOrder.SalesOrder.bool_retention;
          var salesOrderRetentionNumber=salesOrder.SalesOrder.retention_number;
          if (salesOrderBoolRetention){
            $('#InvoiceBoolRetention').prop('checked', true);
            $('#InvoiceRetentionAmount').parent().removeClass('hidden');
            $('#InvoiceRetentionNumber').parent().removeClass('hidden');
            
            $('#retentionPrice').parent().removeClass('d-none');
          }
          else {
            $('#InvoiceBoolRetention').prop('checked', false);
            $('#InvoiceRetentionAmount').parent().addClass('hidden');
            $('#InvoiceRetentionNumber').parent().addClass('hidden');
            
            $('#retentionPrice').parent().addClass('d-none');
          }
          $('#InvoiceRetentionNumber').val(salesOrderRetentionNumber);
          
          $('#clientProcessMsg').html('Aplicando el estado de crédito desde la orden de venta');
          var creditApplied=salesOrder.SalesOrder.bool_credit?1:0;
          
          if (adminUserIds.indexOf(parseInt(salesOrder.SalesOrder.credit_authorization_user_id)) != -1){
            boolCreditAuthorized=1;
            creditAuthorizationUserId=salesOrder.SalesOrder.credit_authorization_user_id;
          }
          
          if (creditApplied == 1){
            $('#BoolCredit').prop('checked',true);
            $('#InvoiceRetentionNumber').val('');
           
          }
          else {
            $('#BoolCredit').prop('checked',false);
            $('#InvoiceRetentionNumber').val('');
            $('#SaveAllowed').val(1);
          }
          $('#CreditData').removeClass('hidden');
          if (creditAuthorizationUserId){     
            setClientCreditData(salesOrderClientId,creditApplied,creditAuthorizationUserId);
          }
          else {
            setClientCreditData(salesOrderClientId,creditApplied,<?php echo $loggedUserId; ?>);
          }
          if (updateProductsFromSalesOrder){
            setSalesOrderProducts();
            updateProductsFromSalesOrder=false;
          }
          else {
            hideMessageModal();
            return false;
          }
          
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
          $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los datos de la orden de venta');
          $('#modalFooter').removeClass('hidden');
				}
			});
    }
    else {
      boolCreditAuthorized=0;
    
      $('#OrderThirdPartyId').removeClass('fixed');
      $('#OrderThirdPartyId').val(0);
      $('#InvoiceCurrencyId').removeClass('fixed');
      $('#InvoiceCurrencyId').val(<?php echo CURRENCY_CS; ?>);
      
      $('#OrderVendorUserId').val(0);  
      $('#OrderVendorUserId option:not(:selected)').attr('disabled', false);
      
      hideMessageModal()
    }  
  }
  
  function setSalesOrderProducts(){
    $('#clientProcessMsg').html('Copiando los productos desde la orden de venta');
    
    var salesOrderId=$('#OrderSalesOrderId').val();
    var warehouseId=$('#OrderWarehouseId').val();
    var currencyId=$('#InvoiceCurrencyId').val();
    var exchangeRate=$('#OrderExchangeRate').val();
    
    var orderDay=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var orderMonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var orderYear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
    
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>salesOrders/getSalesOrderProducts/',
      data:{"salesOrderId":salesOrderId,'warehouseId':warehouseId,'currencyId':currencyId,'exchangeRate':exchangeRate,"orderDay":orderDay,"orderMonth":orderMonth,"orderYear":orderYear},
      cache: false,
      type: 'POST',
      success: function (salesOrderProducts) {
        $('#productsContainer').html(salesOrderProducts);
        
        calculateTotal();
      },
      error: function(e){
        alert(e.responseText);
        console.log(e);
        $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los productos de la orden de venta');
        $('#modalFooter').removeClass('hidden');
      }
    });  
  }

  $('body').on('change','#OrderOrderDateDay',function(){
		setDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});	
	$('body').on('change','#OrderOrderDateMonth',function(){
		setDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});	
	$('body').on('change','#OrderOrderDateYear',function(){
		setDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});	
	function setDueDate(){
		var clientId=$('#OrderThirdPartyId').children("option").filter(":selected").val();
		var emissionDay=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var emissionMonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var emissionYear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
		if (clientId>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>orders/setDueDate/',
				data:{"clientId":clientId,"emissionDay":emissionDay,"emissionMonth":emissionMonth,"emissionYear":emissionYear},
				cache: false,
				type: 'POST',
				success: function (data) {
					$('#divDueDate').html(data);
				},
				error: function(e){
					//console.log(e);
          alert(e.responseText);
				}
			});
		}
	}
	
	function updateExchangeRate(){
    $('#clientProcessMsg').html('Actualizando la tasa de cambio');
    showMessageModal();
    
		var orderday=$('#OrderOrderDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#OrderExchangeRate').val(exchangerate);
        hideMessageModal()
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
        $('#clientProcessMsg').html('Se ha producido un error mientras se actualizaba la tasa de cambio');
        $('#modalFooter').removeClass('hidden');
			}
		});
	}

	$('body').on('change','#InvoiceBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			displayAnnulledState(true)
		}
		else {
			displayAnnulledState(false)
		}
	});	
  
  $('body').on('change','#OrderThirdPartyId',function(){	
		$('#clientProcessMsg').html('Buscando los datos del cliente');
    showMessageModal();
  
    var clientId=$('#OrderThirdPartyId').val();
		if (clientId == 0){
      /* $('#clientData').addClass('d-none'); */
    
      $('#ClientCompanyName').val('')
      $('#OrderClientGeneric').val(0)
    
      $('#OrderClientName').val('')
      $('#OrderClientPhone').val('')
      $('#OrderClientEmail').val('')
      $('#OrderClientRuc').val('')
      
      $('#OrderClientTypeId').removeClass('fixed');
      $('#OrderClientTypeId option').attr('disabled', false);
      $('#OrderClientTypeId').val(0)
      
      $('#OrderZoneId').removeClass('fixed');
      $('#OrderZoneId option').attr('disabled', false);
      $('#OrderZoneId').val(0)
      
      $('#OrderClientAddress').val('')
      $('#OrderDeliveryAddress').val('');
      
      $('#ClientCreditDays').val(0)
      $('#BoolCredit').prop('checked',false);
      //$('#InvoiceBoolRetention').prop('checked',false);
      //$('#InvoiceRetentionNumber').val('');
      $('#SaveAllowed').val(1);
      
      $('#EditClientId').val(0);
      $('#EditClientFirstName').val('');
      $('#EditClientLastName').val('');
      $('#EditClientPhone').val('');
      $('#EditClientEmail').val('');
      $('#EditClientRuc').val('');
      
      $('#EditClientTypeId').val(0);
      $('#EditClientZoneId').val(0);
      
      $('#EditClientAddress').val('');
      
      $('#CreditData').addClass('hidden');
      
      if (<?php echo ($boolInitialLoad?1:0); ?> != 1){
        if (updatePrices){
          updateProductPrices();
        }
        else {
          updatePrices=true;
        }
      }
    }
    else {
      $('#CreditData').removeClass('hidden');
			$.ajax({
        url: '<?php echo $this->Html->url('/'); ?>thirdParties/getclientinfo/',
        data:{"clientid":clientId},
        dataType:'json',
        cache: false,
        type: 'POST',
        success: function (clientdata) {
          var clientCompanyName=clientdata.ThirdParty.company_name;
          var clientFirstName=clientdata.ThirdParty.first_name;
          var clientLastName=clientdata.ThirdParty.last_name;
          
          var boolGenericClient=clientdata.ThirdParty.bool_generic?1:0;
          var clientPhone=clientdata.ThirdParty.phone;
          var clientEmail=clientdata.ThirdParty.email;
          var clientRuc=clientdata.ThirdParty.ruc_number;
          var clientTypeId=clientdata.ThirdParty.client_type_id;
          var zoneId=clientdata.ThirdParty.zone_id;
          var clientAddress=clientdata.ThirdParty.address;
          
          $('#OrderClientGeneric').val(boolGenericClient) 
          
          /*
          20210412 fields are no longer present
          $('#ClientCompanyName').html(clientCompanyName) 
          $('#ClientFirstName').html(clientFirstName!=""?clientFirstName:"-");
          $('#ClientLastName').html(clientLastName!=""?clientLastName:"-");
          */
          
          if (boolGenericClient){
            $('#OrderClientName').val('')
            
            $('#OrderClientPhone').val('')
            $('#OrderClientEmail').val('')
            $('#OrderClientRuc').val('')
            
            $('#OrderClientTypeId').removeClass('fixed');
            $('#OrderClientTypeId option').attr('disabled', false);
            $('#OrderClientTypeId').val(0)
            
            $('#OrderZoneId').removeClass('fixed');
            $('#OrderZoneId option').attr('disabled', false);
            $('#OrderZoneId').val(0)
            
            $('#OrderClientAddress').val('')
            $('#OrderDeliveryAddress').val('')
            
            $('#EditClientId').val(0);
            $('#EditClientFirstName').val('');
            $('#EditClientLastName').val('');
            $('#EditClientPhone').val('');
            $('#EditClientEmail').val('');
            $('#EditClientRucNumber').val('');
            
            $('#EditClientClientTypeId').val(0);
            $('#EditClientZoneId').val(0);
            
            $('#EditClientAddress').val('');
            
            $('#ClientCreditDays').val(0)
            $('#BoolCredit').prop('checked',false);
            $('#InvoiceBoolRetention').prop('checked',false);
            $('#InvoiceRetentionNumber').val('');
            $('#SaveAllowed').val(1);
            
            $('#CreditData').addClass('hidden');    
          }
          else {
            $('#OrderClientName').val(clientCompanyName)
          
            $('#OrderClientPhone').val(clientPhone)
            $('#OrderClientEmail').val(clientEmail)
            $('#OrderClientRuc').val(clientRuc)
            
            if (clientTypeId > 0){
              $('#OrderClientTypeId').val(clientTypeId)
              $('#OrderClientTypeId').addClass('fixed');
              $('#OrderClientTypeId option:not(:selected)').attr('disabled', true);
            }
            else {
              $('#OrderClientTypeId').removeClass('fixed');
              $('#OrderClientTypeId option').attr('disabled', false);
              $('#OrderClientTypeId').val(0)
            }
          
            if (zoneId > 0){
              $('#OrderZoneId').val(zoneId)
              $('#OrderZoneId').addClass('fixed');
              $('#OrderZoneId option:not(:selected)').attr('disabled', true);
            }
            else {
              $('#OrderZoneId').removeClass('fixed');
              $('#OrderZoneId option').attr('disabled', false);
              $('#OrderZoneId').val(0)
            }
          
            $('#OrderClientAddress').val(clientAddress)
            $('#OrderDeliveryAddress').val(clientAddress)
            
            $('#EditClientId').val(clientId);
            $('#EditClientFirstName').val(clientFirstName);
            $('#EditClientLastName').val(clientLastName);
            $('#EditClientPhone').val(clientPhone);
            $('#EditClientEmail').val(clientEmail);
            $('#EditClientRucNumber').val(clientRuc);
            
            $('#EditClientClientTypeId').val(clientTypeId);
            $('#EditClientZoneId').val(zoneId);
            
            $('#EditClientAddress').val(clientAddress);
            
            var clientCreditDays=clientdata.ThirdParty.credit_days;
            var creditApplied=0
            if (clientCreditDays > 0){
              creditApplied=1
              //$('#BoolCredit').prop('checked',true);
              //$('#InvoiceBoolRetention').prop('checked',false);
              //$('#InvoiceRetentionNumber').val('');
            }
            else {
              //$('#BoolCredit').prop('checked',false);
              //$('#InvoiceBoolRetention').prop('checked',false);
              //$('#InvoiceRetentionNumber').val('');
              //$('#SaveAllowed').val(1);
            }
          }
          
          setDueDate();
          
          //creditAuthorizationUserId=$('#OrderCreditAuthorizationUserId').val();
          creditAuthorizationUserId=<?php echo (empty($creditAuthorizationUserId)?0:$creditAuthorizationUserId); ?>;
          if (creditAuthorizationUserId){     
            setClientCreditData(clientId,creditApplied,creditAuthorizationUserId);
          }
          else {
            setClientCreditData(clientId,creditApplied,<?php echo $loggedUserId; ?>);
          }
        },
				error: function(e){
          //$('#clientData').addClass('d-none');
					alert(e.responseText);
					console.log(e);
          $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los datos del cliente');
          $('#modalFooter').removeClass('hidden');
				}
			});
		}
	});
  
  $('body').on('change','#OrderClientName',function(){	
    if ($('#OrderClientGeneric').val()){
      compareWithExistingClients();
    }
  });
  
  function compareWithExistingClients(tooLargeAmountAlert=''){
    //if (tooLargeAmountAlert){
    //  alert(tooLargeAmountAlert);
    //}
    //alert('próximamente se va a mostrar una ventana modal para comparar este cliente genérico con clientes existentes');
    // esta ventana debe permitir comparar con clientes existentes y grabar un nuevo cliente
  }
  
  // EDIT CLIENT SAVE IS NOT PRESENT HERE
  
  $('body').on('change','#BoolCredit',function(){	
    updatePrices=false;
    var creditChecked=$(this).is(':checked')?1:0;
    var clientId=$('#OrderThirdPartyId').val();
		if (clientId == 0){
      //20211209 allow assigning credit even if client is absent because sometimes value is not set correctly even though name appears, extra check will ensure client is present at saving time
      //$('#BoolCredit').prop('checked',false);
    }
    else {
      //creditAuthorizationUserId=$('#OrderCreditAuthorizationUserId').val();
      $('#OrderCreditAuthorizationUserId').val("<?php echo $loggedUserId; ?>");
      var creditAuthorizationUserId=<?php echo $loggedUserId; ?>;
      
      if (creditAuthorizationUserId){   
        if (adminUserIds.indexOf(parseInt(creditAuthorizationUserId)) != -1){
          boolCreditAuthorized=1;
        }
        setClientCreditData(clientId,creditChecked,creditAuthorizationUserId);
      }
      else {
        setClientCreditData(clientId,creditChecked,<?php echo $loggedUserId; ?>);
      }    
    }
  });
	
  $('body').on('change','#SetSaveAllowed',function(){	
    var allowSaving=$(this).is(':checked');
    $('#SaveAllowed').val(allowSaving?1:0)
  });
  
  
  function setClientCreditData(clientId,creditApplied,creditAuthorizationUserId){	
    $('#clientProcessMsg').html('Estableciendo el estado de crédito del cliente');
    showMessageModal();
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>thirdParties/getCreditBlock/',
      data:{"clientId":clientId,"boolCreditApplied":creditApplied,"boolCreditAuthorized":boolCreditAuthorized,"creditAuthorizationUserId":creditAuthorizationUserId},
      cache: false,
      type: 'POST',
      success: function (creditBlock) {
        $('#CreditData').html(creditBlock);
        setDueDate();
        if (updatePrices){
          updateProductPrices();
        }
        else {
          updatePrices=true;
          hideMessageModal()
        }
      },
      error: function(e){
        console.log(e);
        alert(e.responseText);
        $('#clientProcessMsg').html('Se ha producido un error mientras se buscaba el estado de crédito del cliente');
        $('#modalFooter').removeClass('hidden');
      }
    });
	}
  
  $('body').on('click','#OrderBoolDelivery',function(){	
    if ($('#OrderDeliveryId').val() != 0){
      return false;
    }
    return true;
  });
  $('body').on('change','#OrderBoolDelivery',function(){	
    if ($(this).is(':checked')){
      $('#OrderDeliveryAddress').closest('div').removeClass('d-none');
    }
    else {
      $('#OrderDeliveryAddress').closest('div').addClass('d-none');
    }
  });
  
  $('body').on('change','#OrderClientAddress',function(){	
    $('#OrderDeliveryAddress').val($(this).val());
  });
  
	$('body').on('change','.productid div select',function(){	
		$('#clientProcessMsg').html('Buscando la categoría del producto para el producto seleccionado');
    showMessageModal();
    
    var productId=$(this).val();
		var affectedProductId=$(this).attr('id');
    
		if (productId>0){
			if (jsProductCategoriesPerProduct[productId] == <?php echo CATEGORY_PRODUCED; ?> && jsProductTypesPerProduct[productId] == <?php echo PRODUCT_TYPE_BOTTLE; ?>){
        $('#clientProcessMsg').html('Visualizando los cantidades en preforma presentes en bodega');
        
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div select option').each(function(){
          var rawmaterialid=$(this).val();
          var colonPosition=$(this).text().indexOf(" (A");
          if (colonPosition>-1){
            var newText=$(this).text().substring(0,colonPosition);
            $(this).text(newText);
          }
          if (jsRawMaterialsPerProduct[productId][rawmaterialid]){
              var newText=$(this).text()+" (A "+jsRawMaterialsPerProduct[productId][rawmaterialid][1]+")";
            $(this).text(newText);
            $(this).removeAttr('disabled');
          }
          else{
            $(this).attr('disabled',true);
          }
        });
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').removeClass('d-none');
        $('#'+affectedProductId).closest('tr').find('td.productionresultcodeid div').removeClass('d-none');
        $('#'+affectedProductId).closest('tr').find('td.productunitprice div input.productcost').val(0); $('#'+affectedProductId).closest('tr').find('td.productunitprice div input.productprice').val(0);
        $('#'+affectedProductId).closest('tr').find('td.productunitprice input.defaultproductprice').val(0);
        
        hideMessageModal()
      }
      else {
        $('#clientProcessMsg').html('Escondiendo preformas');
        
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div select').val(0);
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').addClass('d-none');
        $('#'+affectedProductId).closest('tr').find('td.productionresultcodeid div').addClass('d-none');
        
        updateProductPrice($('#'+affectedProductId).closest('tr').attr('row'));
      }
    }      
    showServiceCosts();
	});	
  
  $('body').on('change','.rawmaterialid div select',function(){	
    var rawMaterialId=$(this).val();
		var affectedRawMaterialId=$(this).attr('id');
    
    updateProductPrice($(this).closest('tr').attr('row'));
	});	
  
  function updateProductPrices(){
    $("#productsForSale tbody tr:not(.totalrow):not(.hidden):not(.iva):not(.retention):not(.d-none)").each(function() {
      updateProductPrice($(this).attr('row'))
    });
  }
  
  function updateProductPrice(rowId){
    $('#clientProcessMsg').html('Actualizando el precio del producto');
    showMessageModal();
  
    var currentRow=$('#productsForSale').find("[row='" + rowId + "']");
    var productId=currentRow.find('td.productid div select').val();
    var rawMaterialId=currentRow.find('td.rawmaterialid div select').val();
    if (rawMaterialId == null || typeof rawMaterial == undefined){
      rawMaterialId=0
    }
    var productQuantity=currentRow.find('td.productquantity div input').val();
    var currentProductPrice=currentRow.find('td.productunitprice div input.productprice').val();
    
    if (productId > 0){
      hideMessageModal();
      
      $("#PriceModalRowId").val(rowId);
      $("#PriceModalProductId").removeClass('fixed');
      $("#PriceModalProductId").val(productId);
      $("#PriceModalProductId").addClass('fixed');
      $("#PriceModalRawMaterialId").removeClass('fixed');
      $("#PriceModalRawMaterialId").val(rawMaterialId);
      $("#PriceModalRawMaterialId").addClass('fixed');
      $("#priceModal").modal("show");
    
      var clientId=$('#OrderThirdPartyId').val();
      
      var selectedDay=$('#OrderOrderDateDay').children("option").filter(":selected").val();
      var selectedMonth=$('#OrderOrderDateMonth').children("option").filter(":selected").val();
      var selectedYear=$('#OrderOrderDateYear').children("option").filter(":selected").val();
      
      var warehouseId=$('#OrderWarehouseId').val();
      $.ajax({
        //url: '<?php echo $this->Html->url('/'); ?>productPriceLogs/getproductprice/',
        //data:{"productId":productId,"rawMaterialId":rawMaterialId,"clientId":clientId,"selectedDay":selectedDay,"selectedMonth":selectedMonth,"selectedYear":selectedYear,},
        url: '<?php echo $this->Html->url('/'); ?>products/getProductPriceInfo/',
        data:{"productId":productId,"rawMaterialId":rawMaterialId,"clientId":clientId,"selectedDay":selectedDay,"selectedMonth":selectedMonth,"selectedYear":selectedYear,"warehouseId":warehouseId},
        cache: false,
        type: 'POST',
        dataType:'json',
        success: function (productThresholdAndCostAndPrices) {
          var selectedPrice = 0;
          var minimumPrice=0;
           var thresholdVolume=roundToTwo(productThresholdAndCostAndPrices.threshold_volume);
          var productCost=roundToFour(productThresholdAndCostAndPrices.product_cost); 
          var clientPrice=roundToFour(productThresholdAndCostAndPrices.product_prices.client_price);
          if (clientPrice > 0){
            selectedPrice=clientPrice
            if (clientPrice > productCost){
              minimumPrice=clientPrice
            }
          }                  
           $('#PriceModalThresholdVolume').val(thresholdVolume);
          
          $('#PriceModalInventoryCost').val(productCost);
          
          var index=0;
          for (index=0;index<Object.keys(productThresholdAndCostAndPrices.product_prices.PriceClientCategory).length; index++){
            var categoryId=parseInt(Object.keys(productThresholdAndCostAndPrices.product_prices.PriceClientCategory)[index]);
            var categoryPrice=roundToFour(parseFloat(productThresholdAndCostAndPrices.product_prices.PriceClientCategory[categoryId].category_price)); 
            
            $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').val(categoryPrice);
            
            if (categoryPrice <= productCost || (categoryId === <?php echo PRICE_CLIENT_CATEGORY_VOLUME; ?> && productQuantity < thresholdVolume)){
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').removeClass('priceselector')
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').removeClass('allowed')
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').addClass('redbg')
            }
            else {
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').addClass('priceselector')
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').addClass('allowed')
              $('#PriceModalPriceClientCategory'+categoryId+'CategoryPrice').removeClass('redbg')
              
              if(minimumPrice == 0 || categoryPrice < minimumPrice){
                minimumPrice=categoryPrice;
              }
              if(clientPrice == 0){
                // IN THIS CASE THERE IS NO CLIENT PRICE 
                if (selectedPrice == 0 || categoryPrice < selectedPrice){                 
                  selectedPrice=categoryPrice;
                }
              }
            }
          }
          $('#PriceModalClientPrice').val(clientPrice);
          
          if (clientPrice <= productCost){
            $('#PriceModalClientPrice').removeClass('priceselector')
            $('#PriceModalClientPrice').removeClass('allowed')
            $('#PriceModalClientPrice').addClass('redbg')
          }
          else {
            $('#PriceModalClientPrice').addClass('priceselector')
            $('#PriceModalClientPrice').addClass('allowed')
            $('#PriceModalClientPrice').removeClass('redbg')
          }
          
          if (minimumPrice<productCost){
            minimumPrice=roundToTwo(productCost + 0.1);
          }
          $('#PriceModalMinimumPrice').val(minimumPrice);
          $('#PriceModalSelectedPrice').val(selectedPrice);

          currentRow.find('td.productunitprice input.productcost').val(productCost);
          currentRow.find('td.productunitprice input.defaultproductprice').val(minimumPrice);
          
          if (currentProductPrice >= minimumPrice){
            // price was present already
            $('#PriceModalSelectedPrice').val(currentProductPrice);  
          }
          else {
            if (currentProductPrice >productCost && <?php echo $userRoleId === ROLE_ADMIN?1:0; ?> === 1){
              // price was present already
              alert('Como Usted está el gerente, se mantiene el precio establecido anteriormente ' + currentProductPrice + ' a pesar de estar menos que el precio mínimo disponible para los usuarios normales '+ minimumPrice)
              $('#PriceModalSelectedPrice').val(currentProductPrice);  
              //20201114 PRICE SHOULD ONLY BE SET BY APPLY PRICE TO PRODUCT
              //currentRow.find('td.productunitprice div input.productprice').val(currentProductPrice);
            }
          }
        },
        error: function(e){
          console.log(e);
          alert(e.responseText);
          
          $('#clientProcessMsg').html('Se ha producido un error mientras se actualizaba el precio del producto '+productId);
          $('#modalFooter').removeClass('hidden');
        }
      });
    }  
    else {
      currentRow.find('td.productunitprice input.defaultproductprice').val(0);
      currentRow.find('td.productunitprice div input.productprice').val(0);
      
      calculateRow(rowId);
    }  
  }
  
  $('body').on('click','.priceselector',function(){
    var priceClicked=$(this).val();
    $('#PriceModalSelectedPrice').val(priceClicked);
    //20201114 PRICE SHOULD ONLY BE SET BY CLICKING ON APPLY PRICE TO PRODUCT
    //$('tr[row="'+$('#PriceModalRowId')+'"] td.productunitprice div input.productprice').val(priceClicked);
  });
  
  $('body').on('click','#applyPriceToProduct',function(){
		if ($('#PriceModalSelectedPrice').val() <= 0){
      if ($('#PriceModalRawMaterialId').val() == 0){
        alert('El precio de listado para el producto '+$('#PriceModalProductId div select option').filter(':selected').text()+' es 0 (cero) y esto implica que no se puede aplicar un control de precios.  No se permitirá guardar la cotización hasta que se graba un precio de listado para este producto.');
      }          
      else {
        alert('El precio de listado para el producto '+$('#PriceModalProductId div select option').filter(':selected').text()+' y materia prima '+$('#PriceModalRawMaterialId div select option').filter(':selected').text()+' con calidad A es 0 (cero) y esto implica que no se puede aplicar un control de precios.  No se permitirá guardar la cotización hasta que se graba un precio de listado para este producto.');
      }
    }
    if ($('#PriceModalSelectedPrice').val() < $('#PriceModalMinimumPrice').val()){
      if ($('#PriceModalRawMaterialId').val() == 0){
        //alert('El precio de listado para el producto '+$('#PriceModalProductId div select option').filter(':selected').text() +' ('+ $('#PriceModalSelectedPrice').val() +') está menor que el costo del producto ('+$('#PriceModalInventoryCost').val()+').  No se permitirá guardar la cotización porque esto constituye una venta con pérdida.');
        alert('El precio para el producto '+$('#PriceModalProductId div select option').filter(':selected').text()+' ('+ $('#PriceModalSelectedPrice').val() +') no está autorizado.');
      }          
      else {
        //alert('El precio de listado para el producto '+$('#PriceModalProductId div select option').filter(':selected').text()+' y materia prima '+$('#PriceModalRawMaterialId div select option').filter(':selected').text()+' calidad A ('+$('#PriceModalSelectedPrice').val()+') está menor que el costo del producto ('+$('#PriceModalInventoryCost').val()+').  No se permitirá guardar la cotización porque esto constituye una venta con pérdida.');
        alert('El precio para el producto '+$('#PriceModalProductId div select option').filter(':selected').text()+' y materia prima '+$('#PriceModalRawMaterialId div select option').filter(':selected').text()+' calidad A ('+$('#PriceModalSelectedPrice').val()+') no está autorizado.');
      }
      // TODO ADD THE CONTROL THAT IT IS HIGHER THAN THE COST
      if (<?php echo $userRoleId === ROLE_ADMIN?1:0; ?> === 1 ){
        alert('Como Usted es el gerente, sí puede dar precios más favorables que el precio de listado, se calculará este precio');
        $("#priceModal").modal("hide");
        $('tr[row="'+$('#PriceModalRowId').val()+'"] td.productunitprice div input.productprice').val($('#PriceModalSelectedPrice').val());
        calculateRow($('#PriceModalRowId').val());  
      }
    }
    else {
      var currentRow=$('#productsForSale').find("[row='" + $('#PriceModalRowId').val() + "']");
      currentRow.find('td.productunitprice div input.productprice').val($('#PriceModalSelectedPrice').val());
      $("#priceModal").modal("hide");
      calculateRow($('#PriceModalRowId').val());  
    }
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
	});	
	
	$('body').on('change','.productunitprice',function(){	
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
      var productPrice=parseFloat($(this).find('div input.productprice').val());
      var defaultProductPrice=parseFloat($(this).find('input.defaultproductprice').val());
      if (<?php echo ($userRoleId != ROLE_ADMIN?1:0); ?> && productPrice < defaultProductPrice){
        productPrice=defaultProductPrice
        $(this).find('div input').val(productPrice);
      }
      var productCost=parseFloat($(this).find('input.productcost').val());   
               
      if (productPrice<productCost){
        var productName = $(this).closest('tr').find('td.productid div select option').filter(':selected').text()
        var rawMaterialId=$(this).closest('tr').find('td.rawmaterialid div select').val();
        if (rawMaterialId == 0){
          alert('El precio registrado para el producto '+productName+' ('+ productPrice +') está menor que el costo del producto ('+productCost+').  No se permitirá guardar la venta porque esto constituye una venta con pérdida.');
        }          
        else {
          var rawMaterialName=$(this).closest('tr').find('td.rawmaterialid div select option').filter(':selected').text()
          alert('El precio registrado para el producto '+productName+' y materia prima '+rawMaterialName+' calidad A ('+productPrice+') está menor que el costo del producto ('+productCost+').  No se permitirá guardar la venta porque esto constituye una venta con pérdida.');
        }
      }
		}
		calculateRow($(this).closest('tr').attr('row'));
	});
  
  $('body').on('change','.serviceunitcost div input',function(){	
		var serviceunitcost=$(this).val();
		var productquantity=parseFloat($(this).closest('tr').find('td.productquantity div input').val());
		$(this).closest('tr').find('td.servicetotalcost div input').val(roundToTwo(serviceunitcost*productquantity));
		calculateTotal();
	});		
  
  function calculateRow(rowId) {    
    $('#clientProcessMsg').html('Calculando el total de la fila '+rowId);
    showMessageModal();
    
		var currentrow=$('#productsForSale').find("[row='" + rowId + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitprice=parseFloat(currentrow.find('td.productunitprice div input.productprice').val());
    
    var totalprice=quantity*unitprice;
		
		currentrow.find('td.producttotalprice div input').val(roundToTwo(totalprice));
    
    var serviceunitcost=parseFloat(currentrow.find('td.serviceunitcost div input').val());
    var servicetotalcost=quantity*serviceunitcost
    currentrow.find('td.servicetotalcost div input').val(roundToTwo(servicetotalcost));
    
    calculateTotal();
	}

  $('body').on('change','#InvoiceBoolIva',function(){	
		calculateTotal();
	});
  
	$('body').on('change','#InvoiceBoolRetention',function(){	
		if ($(this).is(':checked')){
			$('tr.retention').removeClass('d-none');
      $('#InvoiceRetentionNumber').parent().removeClass('d-none');
		}
		else {
			$('tr.retention').addClass('hidden');
			$('#InvoiceRetentionNumber').parent().addClass('d-none');
		}
    calculateTotal();
	});
	
  function calculateTotal(){
    $('#clientProcessMsg').html('Calculando el total de la venta');
    showMessageModal();
    
		var totalProductQuantity=0;
    var totalServiceCost=0;
		var subtotalPrice=0;
		var ivaPrice=0;
    var retentionAmount=0;
		var totalPrice=0;
		$("#productsForSale tbody tr:not(.hidden):not(.totalrow):not(.iva):not(.retention):not(.d-none)").each(function() {
			var currentProductQuantity = parseFloat($(this).find('td.productquantity div input').val());
			if (!isNaN(currentProductQuantity)){
				totalProductQuantity += currentProductQuantity;
			}
      
      var currentServiceTotalCost = parseFloat($(this).find('td.servicetotalcost div input').val());
			if (!isNaN(currentServiceTotalCost)){
				totalServiceCost += currentServiceTotalCost;
			}
      
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			if (!isNaN(currentPrice)){
				subtotalPrice += currentPrice;
      //  $(this).find('td.iva div input').val(roundToTwo(0.15*currentPrice));
			//	ivaPrice+=roundToTwo(0.15*currentPrice);
			}
		});
    $('tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));  
    $('tr.totalrow.subtotal td.servicetotalcost span').text(totalServiceCost.toFixed(0));
    
    subtotalPrice=roundToTwo(subtotalPrice)
    
		$('#subtotal span.amountright').text(subtotalPrice);
		$('tr.totalrow.subtotal td.totalprice div input').val(subtotalPrice.toFixed(2));
		
    if ($('#InvoiceBoolIva').is(':checked')){
			ivaPrice=0.15*subtotalPrice
		}
    ivaPrice=roundToTwo(ivaPrice);
		$('#iva span.amountright').text(ivaPrice);
    $('tr.iva td.totalprice div input').val(ivaPrice.toFixed(2));	
  
    if ($('#InvoiceBoolRetention').is(':checked')){
			retentionAmount=0.02*subtotalPrice
		}
    retentionAmount=roundToTwo(retentionAmount)
    $('#retention span.amountright').text(retentionAmount);
    $('tr.retention td.totalprice div input').val(retentionAmount.toFixed(2));	
		
    totalPrice=roundToTwo(subtotalPrice + ivaPrice);
		$('#total span.amountright').text(totalPrice);
		$('tr.totalrow.total td.totalprice div input').val(totalPrice.toFixed(2));	
		
    hideMessageModal();
    if ($('#OrderClientGeneric').val() && (totalPrice>10000 || (totalPrice> 200 && $('#OrderCurrencyId').val() == <?php echo CURRENCY_USD; ?>))){
      var currency = $('#OrderCurrencyId').val() == <?php echo CURRENCY_USD; ?>?"US$":"C$"
      compareWithExistingClients('Esta orden de venta es para un monto de '+currency+' '+totalPrice + '; no se puede grabar el cliente como genérico.')
    }
		return false;
	}
  
  $('body').on('click','.addProduct',function(){	
		var tableRow=$('#productsForSale tbody tr.d-none:first');
		tableRow.removeClass("d-none");
	});

	$('body').on('click','.removeProduct',function(){	
		var tableRow=$(this).closest('tr').remove();
		calculateTotal();
	});	
  
  $('body').on('click','.showPriceSelection',function(){
    var rowId=$(this).closest('tr').attr('row')
    
    updateProductPrice(rowId)
	});
  
  function updateCurrencies(){
		var currencyId=$('#InvoiceCurrencyId').val();
		if (currencyId==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
      $('span.currencyrighttop').text('C$ ');
		}
		else if (currencyId==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
      $('span.currencyrighttop').text('US$ ')
		}
	}
  
  $('body').on('change','#InvoiceCurrencyId',function(){	
		updateCurrencies();
	});	
  
  function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
		var currencyid=$('#InvoiceCurrencyId').children("option").filter(":selected").val();
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
		}
	}
  
  function formatCSCurrencies(){
		$("dd.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
 
  function displayAnnulledState(boolAnnulledState){
    if (boolAnnulledState){
      $('#InvoiceCurrencyId').parent().addClass('d-none');
			$('#BoolCredit').parent().addClass('d-none');
			$('#divDueDate').addClass('d-none');
			$('#InvoiceCashboxAccountingCodeId').parent().addClass('d-none');
			$('#InvoiceBoolRetention').parent().addClass('d-none');
			$('#InvoiceRetentionNumber').parent().addClass('d-none');
			$('#InvoiceBoolIva').parent().addClass('d-none');
			$('#productsForSale').addClass('d-none');
    }
    else {
      $('#InvoiceCurrencyId').parent().removeClass('d-none');
			$('#BoolCredit').parent().removeClass('d-none');
			if ($('#BoolCredit').is(':checked')){
				$('#divDueDate').removeClass('d-none');
			}
			else {
				$('#InvoiceCashboxAccountingCodeId').parent().removeClass('d-none');
			}
			$('#InvoiceBoolRetention').parent().removeClass('d-none');
			if ($('#InvoiceBoolRetention').is(':checked')){
				$('#InvoiceRetentionNumber').parent().removeClass('d-none');
			}
			$('#InvoiceBoolIva').parent().removeClass('d-none');
			$('#productsForSale').removeClass('d-none');
    }
  }
  
  function showServiceCosts(){
    var otherProductPresent=false;
    $('#productsForSale tbody tr').each(function(){
      var productid=$(this).find('td.productid select').val()
      var otherProductRow=$.inArray(productid,jsOtherProducts) > -1;
      if (otherProductRow){
        otherProductPresent=true
      }
    });
    
    if (otherProductPresent){
      $('#productsForSale tbody tr').each(function(){
        $(this).find('td.serviceunitcost').removeClass('d-none')
        $(this).find('td.servicetotalcost').removeClass('d-none')
      });  
      $('#productsForSale tbody tr:not(.totalrow):not(.hidden):not(.d-none):not(.iva):not(.retention)').each(function(){
        var productId=$(this).find('td.productid select').val()
        var otherProductRow=$.inArray(productId,jsOtherProducts) > -1;
        if (otherProductRow){
          $(this).find('td.serviceunitcost div.input').removeClass('d-none')
          $(this).find('td.servicetotalcost div.input').removeClass('d-none')
          otherProductPresent=true
        }
        else {
          $(this).find('td.serviceunitcost div.input').addClass('d-none')
          $(this).find('td.servicetotalcost div.input').addClass('d-none')
        }
      });
      $('#productsForSale thead tr th.servicecostheader').each(function(){
        $(this).removeClass('d-none')
      });
    }
    else {
      $('#productsForSale tbody tr').each(function(){
        $(this).find('td.serviceunitcost').addClass('d-none')
        $(this).find('td.servicetotalcost').addClass('d-none')        
      });
      
      $('#productsForSale thead tr th.servicecostheader').each(function(){
          $(this).addClass('d-none')
      });
    }
  }
	var jsGenericClientIds=<?php echo json_encode($genericClientIds); ?>; 
	
  $(document).ready(function(){
		formatCurrencies();
		$('#OrderOrderDateHour').val('02');
		$('#OrderOrderDateMin').val('00');
		$('#OrderOrderDateMeridian').val('pm');
		
    if ($('#InvoiceBoolAnnulled').is(':checked')){
			displayAnnulledState(true)
		}
		else {
      displayAnnulledState(false)
		}
		//if (<?php echo ($boolInitialLoad?1:0); ?> == 1 && <?php echo $salesOrderId; ?> >0){
    //  updatePrices=false;
    //  $('#OrderSalesOrderId').trigger('change');
    //}
    //else {
      var clientId=$('#OrderThirdPartyId').val();
      if (jQuery.inArray(clientId,Object.values(jsGenericClientIds)) != -1){
        $('#OrderClientGeneric').val(1);
      }
      else {
        $('#OrderThirdPartyId').trigger('change')
      }
      if (clientId > 0){
        updatePrices=false;
      }
      calculateTotal();
    //}
		
    showServiceCosts();
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
    
    //checkCreditAuthorizedStatus();
	});
  //20210707 el estado de  crédito siempre debe corresponder con el grabado
  /*
  function checkCreditAuthorizedStatus(){
    if ($('#OrderSalesOrderId').val() == <?php echo $salesOrderIdOriginalInvoice; ?>){
      boolCreditAuthorized=<?php echo $creditAuthorizedInOriginalInvoice?1:0; ?>;
    }
    else {
      var salesOrderId=$('#OrderSalesOrderId').val();
      if (parseInt(salesOrderId)>0){
        $('#clientProcessMsg').html('Verificando estado de autorización de crédito');
        showMessageModal();
        $.ajax({
          url: '<?php echo $this->Html->url('/'); ?>salesOrders/getSalesOrderInfo/',
          data:{"salesOrderId":salesOrderId},
          dataType:'json',
          cache: false,
          type: 'POST',
          success: function (salesOrder) {
            if (adminUserIds.indexOf(parseInt(salesOrder.SalesOrder.credit_authorization_user_id)) != -1){
              boolCreditAuthorized=1;
              creditAuthorizationUserId=salesOrder.SalesOrder.credit_authorization_user_id;
              var creditApplied=salesOrder.SalesOrder.bool_credit?1:0;
              if (creditAuthorizationUserId){     
                setClientCreditData(clientId,creditChecked,creditAuthorizationUserId);
              }
              else {
                setClientCreditData(clientId,creditChecked,<?php echo $loggedUserId; ?>);
              }  
            }
            hideMessageModal();
          },
          error: function(e){
            alert(e.responseText);
            console.log(e);
            $('#clientProcessMsg').html('Se ha producido un error mientras se verificaba el estado de autorización de crédito');
            $('#modalFooter').removeClass('hidden');
          }
        });
      }
      else {
        boolCreditAuthorized=0;
        hideMessageModal()
      }  
    }    
  }
  */
</script>


<div class="orders form sales fullwidth">
<?php 
	$orderDateTime=new DateTime($orderDate);

  //pr($this->request->data);
	echo $this->Form->create('Order'); 
	echo "<fieldset>";
		echo "<legend>".__('Editar Venta')." ".$this->request->data['Order']['order_code']."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo '<div class="col-sm-4"  style="padding:5px;">';	
          echo '<h1>Venta</h1>';
					echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo $this->Form->input('order_date',['label'=>__('Sale Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
          
          //echo  $this->Form->input('inventory_display_option_id',['label'=>__('Mostrar Inventario'),'default'=>$inventoryDisplayOptionId]);
					//echo $this->Form->Submit(__('Mostrar/Esconder Inventario'),['id'=>'showinventory','name'=>'showinventory']);
          
          echo $this->Form->input('order_code',['readonly'=>'readonly','style'=>'min-width:145px;max-width:200px;font-size:1.3em;font-weight:700;']);
          //if ($userRoleId == ROLE_ADMIN){
            echo $this->Form->input('vendor_user_id',['value'=>$vendorUserId,'options'=>$users,'empty'=>[0=>'-- Vendedor --']]);
          //}
          //else {
          //  echo $this->Form->input('vendor_user_id',['value'=>$vendorUserId]);
          //}
          echo $this->Form->input('record_user_id',['value'=>$loggedUserId,'type'=>'hidden']);
          
          echo $this->Form->input('Order.sales_order_id',[
            'default'=>$salesOrderId,
            'empty'=>[0=>'-- Seleccione Orden de Venta --'],
            'style'=>'background-color:'.(count($salesOrders)>0?'yellow':'none'),
          ]);
						
          echo $this->Form->input('Invoice.bool_annulled',['type'=>'checkbox','label'=>'Anulada']);
          echo "<div id='divDueDate'>";
            echo $this->Form->input('Invoice.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>(date('Y')+1)]);
          echo "</div>";
          echo $this->Form->input('Invoice.currency_id',['default'=>CURRENCY_CS,'empty'=>['0'=>'Seleccione Moneda']]);
          echo $this->Form->input('exchange_rate',['default'=>$exchangeRateOrder,'readonly'=>'readonly']);
						
          echo $this->Form->input('Invoice.bool_iva',[
            'label'=>'Se aplica IVA',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('Invoice.bool_retention',[
            'type'=>'checkbox',
            'label'=>'Retención',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('Invoice.retention_number',['label'=>'Número Retención']);
              
          echo $this->Form->input('Invoice.cashbox_accounting_code_id',['empty'=>['0'=>'Seleccione Caja'],'class'=>'narrow','options'=>$accountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN]);
					
          echo $this->Form->input('bool_delivery',[
            'label'=>'Entrega a Domicilio?',
            'checked'=>$boolDelivery,
          ]);
          echo $this->Form->input('delivery_id',['type'=>'hidden']);
          echo '<p id="deliveryAlert" class="alert hidden">No se puede remover la entrega a domicilio porque ya está programada</p>';
          
          //echo $this->Form->input('driver_user_id',[
          //  'empty'=>[0=>'-- Conductor -- '],
          //]);
          //echo $this->Form->input('vehicle_id',[
          //  'empty'=>[0=>'-- Vehículo -- '],
          //]);  
            
        echo '</div>';
        echo '<div class="col-md-4" style="padding:5px;">';	
            echo '<h2>Cliente</h2>';
            echo $this->Form->input('third_party_id',[
              'label'=>__('Client'),
              'default'=>0,
              'empty'=>['0'=>'-- Cliente --'],
            ]);
            
            echo $this->Form->input('client_generic',['type'=>'hidden','default'=>0]);
            echo $this->Form->input('client_name',['label'=>'Nombre Cliente']);
            echo '<p>Especifica su teléfono o su correo para registrar la venta</p>';
            echo $this->Form->input('client_phone',['label'=>'Teléfono','type'=>'phone']);
            echo $this->Form->input('client_email',['label'=>'Correo','type'=>'email']);
            echo $this->Form->input('client_ruc',['label'=>'RUC']);
            echo $this->Form->input('client_type_id',['empty'=>[0=>'-- Tipo de Cliente --']]);
            echo $this->Form->input('zone_id',['empty'=>[0=>'-- Zona --']]);
            echo $this->Form->input('client_address',['label'=>'Dirección']);
            echo $this->Form->input('delivery_address',['label'=>'Dirección de entrega', 'type'=>'textarea','div'=>['class'=>('input textarea'.($this->request->data['Order']['bool_delivery'] ?'':' d-none'))]]);
            
            echo "<a href='#msgModal' role='button' class='btn btn-large btn-primary' data-toggle='modal'>Editar Cliente</a>";
            
            echo '<div id="CreditData">';
              echo '<h3>Estado de Crédito del Cliente</h3>';
              echo '<p class="notallowed" id="creditWarning"></p>';
              if ($userRoleId == ROLE_ADMIN){
                echo $this->Form->input('set_save_allowed',['id'=>'SetSaveAllowed','type'=>'checkbox','label'=>'Guardar Venta','checked'=>true]);
              }
              echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'hidden','label'=>'Guardar Venta','readonly'=>'readonly','value'=>1]);
              
              echo $this->Form->input('credit_authorization_user_id',['label'=>false,'id'=>'CreditAuthorizationUserId','type'=>'hidden','value'=>$creditAuthorizationUserId]);
              echo $this->Form->input('credit_username',['label'=>'Crédito autorizado por','id'=>'CreditUsername','value'=>($creditAuthorizationUserId > 0?$users[$creditAuthorizationUserId]:'Crédito de cliente'),'readonly'=>true,'div'=>['class'=>(array_key_exists('bool_credit',$this->request->data) && $this->request->data['bool_credit']?'':'d-none')]]);
            
              //pr($this->request->data['Invoice']);
              echo $this->Form->input('bool_credit',[
                'type'=>'checkbox',
                'label'=>'Crédito',
                'id'=>'BoolCredit',
                'default'=>$this->request->data['Invoice']['bool_credit'],
              ]);
                
              echo $this->Form->input('Client.credit_days',['label'=>'Días de Crédito','default'=>0]);
              echo $this->Form->input('Client.credit_saldo',['type'=>'hidden','label'=>false]);
              echo  '<dl class="narrow">';
                echo '<dt>'.__('Límite Crédito').'</dt>';
                echo '<dd id="ClientCreditLimit" class="CScurrency"><span class="currency">C$</span><span class="amountright">0</span></dd>';
                echo '<dt>'.__('Pago Pendiente').'</dt>';
                echo '<dd id="ClientCreditPending" class="CScurrency"><span class="currency">C$</span><span class="amountright">0</span></dd>';
              echo  '</dl>';
              echo '<table id="pagosPendientesCliente">';
                echo '<thead>';
                  echo '<tr>';
                    echo '<th>Fecha</th>';
                    echo '<th class="centered">#</th>';
                    echo '<th>Monto</th>';
                    echo '<th>Dias</th>';
                  echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                for ($i=0;$i<INVOICE_PENDING_PAYMENT_MAX;$i++){
                  echo '<tr id="pendingPayment'.$i.'" class="hidden">';
                    echo '<td class="invoiceDate">Fecha</td>';
                    echo '<td class="invoiceCode">#</td>';
                    echo '<td  class="invoiceAmount"><span class="currency"></span><span class="amountright"></span></td>';
                    echo '<td></td>';
                  echo '</tr>';
                }
                echo '</tbody>';
              echo '</table>';
            echo '</div>';             
          echo '</div>';
          
          echo '<div class="col-md-4" style="padding:5px;">';
            echo '<h2>Totales</h2>';
            echo '<div class="topright" style="font-size:18px;">';
              echo "<dl>";
                echo "<dt>Subtotal</dt>";
                echo "<dd id='subtotal' style='clear:right;'><span class='currency'>C$</span><span class='amountright'>0</span></dd>";
                echo "<dt style='clear:left;'>IVA</dt>";
                echo "<dd id='iva' style='clear:right;'><span class='currency'>C$</span><span class='amountright'>0</span></dd>";
                echo "<dt style='clear:left;'>Total</dt>";
                echo "<dd id='total' style='clear:right;'><span class='currency'>C$</span><span class='amountright'>0</span></dd>";
              echo "</dl>";
              echo "<br/>";
              echo "<dl>";
                echo "<dt style='clear:left;'>Retención</dt>";
                echo "<dd id='retention' style='clear:right;'><span class='currency'>C$</span><span class='amountright'>0</span></dd>";
              echo "</dl>";
              echo "<br/>";
            echo '</div>';	
            echo $this->Form->input('comment',['type'=>'textarea','rows'=>3]);
              
              echo "<h4>".__('Actions')."</h3>";
              echo "<ul>";
              if ($bool_client_add_permission) {
                echo "<li>".$this->Html->link(__('New Client'), ['controller' => 'third_parties', 'action' => 'crearCliente'])."</li>";
              }
              echo "</ul>";
            echo "</div>";
					echo "</div>";
        echo "</div>";  
        echo '<div class="row">';
          echo "<div class='col-sm-12'>";
            echo '<div>'; 
              echo $this->Form->Submit('Guardar e Imprimir',[
                'class'=>'saveAndPrint',
                'name'=>'saveAndPrint',
                'style'=>'width:15em;',
                'div'=>['style'=>'display:inline-block;'],
              ]);
              echo $this->Form->Submit('Guardar y Otra',[
                'class'=>'saveAndNew',
                'name'=>'saveAndNew',
                'style'=>'width:15em;',
                'div'=>['style'=>'display:inline-block;margin-left:10em;'],
              ]);           
            echo '</div>';    
            echo "<h2>Productos en Venta</h2>";
              echo '<div id="productsContainer">';
                echo '<table id="productsForSale" style="font-size:16px;">';
                  echo "<thead>";
                    echo "<tr>";
                      echo "<th>".__('Product')."</th>";
                      echo "<th style='width:170px;'>".__('Raw Material')."</th>";
                      echo "<th style='width:80px;'>".__('Quality')."</th>";
                      echo "<th class='centered narrow'>".__('Quantity Product')."</th>";
                      if (empty($this->request->data) || $this->request->data['Order']['sales_order_id'] == 0){
                        echo "<th class='servicecostheader currencyinput'>".__('Costo Unitario Otro')."</th>";
                        echo "<th class='servicecostheader currencyinput'>".__('Costo Total Otro')."</th>";
                      }
                      echo "<th class='currencyinput' style='min-width:12%;'>".__('Unit Price')."</th>";
                      echo "<th class='currencyinput' style='min-width:12%;'>".__('Total Price')."</th>";
                      echo "<th></th>";
                    echo "</tr>";
                  echo "</thead>";
                  
              echo "<tbody>";
              for ($i=0;$i<count($requestProducts);$i++) { 
                echo "<tr row='Fact_".$i."'>";
                  echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'value'=>$requestProducts[$i]['Product']['product_id'],'empty' =>array(0=>__('Choose a Product'))))."</td>";
                  echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',['label'=>false,'value'=>(array_key_exists('raw_material_id',$requestProducts[$i]['Product'])?$requestProducts[$i]['Product']['raw_material_id']:0),'empty' =>[0=>__('Choose a Raw Material')]])."</td>";
                  if (!empty($requestProducts[$i]['Product']['production_result_code_id'])){
                    echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'value'=>$requestProducts[$i]['Product']['production_result_code_id']))."</td>";
                  }
                  else {
                    echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',['label'=>false,'default'=>'0','div'=>['class'=>'d-none']])."</td>";
                  }
                  echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['product_quantity']))."</td>";
                  
                  echo "<td class='serviceunitcost'>".$this->Form->input('Product.'.$i.'.service_unit_cost',['type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['service_unit_cost']])."</td>";
                  echo "<td  class='servicetotalcost'>".$this->Form->input('Product.'.$i.'.service_total_cost',['type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['service_total_cost'],'readonly'=>'readonly'])."</td>";
                  
                  echo "<td class='productunitprice'>";
                    echo $this->Form->input('Product.'.$i.'.product_unit_cost',[
                      'label'=>false,
                      'type'=>'hidden',
                      'value'=>$requestProducts[$i]['Product']['product_unit_cost'],
                      'class'=>'productcost',
                    ]);
                    echo $this->Form->input('Product.'.$i.'.default_product_unit_price',[
                      'label'=>false,
                      'type'=>'hidden',
                      'value'=>(empty($requestProducts[$i]['Product']['default_product_unit_price'])?$requestProducts[$i]['Product']['product_unit_price']:$requestProducts[$i]['Product']['default_product_unit_price']),
                      'class'=>'defaultproductprice',
                    ]);
                    
                    echo $this->Form->input('Product.'.$i.'.product_unit_price',[
                      'label'=>false,
                      'type'=>'decimal',
                      'value'=>$requestProducts[$i]['Product']['product_unit_price'],
                      'class'=>'productprice',
                      //'before'=>'<span class=\'currency\'>C$</span>',
                    ]);
                  echo "</td>";
                  
                  echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['Product']['product_total_price'],'readonly'=>'readonly'))."</td>";
                  echo "<td><button class='removeProduct' type='button'>".__('Remove Sale Item')."</button></td>";
                  echo '<button class="showPriceSelection" type="button">Precios</button>';
                echo "</tr>";
              }
              for ($i=count($requestProducts);$i<QUOTATION_ARTICLES_MAX;$i++) { 
                if ($i==count($requestProducts)){
                  echo "<tr row='Fact_".$i."'>";
                } 
                else {
                  echo "<tr row='Fact_".$i."' class='d-none'>";
                } 
                  echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
                  echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
                  echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>PRODUCTION_RESULT_CODE_A,'readonly'=>'readonly'))."</td>";
                  echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
                  
                  echo "<td class='serviceunitcost'>".$this->Form->input('Product.'.$i.'.service_unit_cost',['type'=>'decimal','label'=>false,'default'=>0])."</td>";
                  echo "<td  class='servicetotalcost'>".$this->Form->input('Product.'.$i.'.service_total_cost',['type'=>'decimal','label'=>false,'default'=>0,'readonly'=>'readonly'])."</td>";
                  
                  echo "<td class='productunitprice'>";
                    echo $this->Form->input('Product.'.$i.'.product_unit_cost',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'productcost']);
                    echo $this->Form->input('Product.'.$i.'.default_product_unit_price',['label'=>false,'type'=>'d-none','default'=>0,'class'=>'defaultproductprice']);
                    //echo "<span class='currency'></span>";
                    echo $this->Form->input('Product.'.$i.'.product_unit_price',[
                      'label'=>false,
                      'type'=>'decimal',
                      'default'=>0,
                      'class'=>'productprice',
                      //'before'=>'<span class=\'currency\'>C$</span>',
                    ]);
                  echo "</td>";
                  echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly'])."</td>";
                  echo '<td>';
                    echo '<button class="removeProduct" type="button">'.__('Remove Sale Item').'</button>';
                    echo '<button class="showPriceSelection" type="button">Precios</button>';
                  echo '</td>';
                echo "</tr>";
              } 						
                echo "<tr class='totalrow subtotal'>";
                  echo "<td>Subtotal</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='productquantity amount right'><span></span></td>";
                  
                    echo "<td class='serviceunitcost'></td>";
                    echo "<td  class='servicetotalcost amount right'><span></span></td>";
                  
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
                echo "<tr class='iva'>";
                  echo "<td>IVA</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  
                    echo "<td class='serviceunitcost'></td>";
                    echo "<td  class='servicetotalcost'></td>";
                    
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
                echo "<tr class='totalrow total'>";
                  echo "<td>Total</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  
                    echo "<td class='serviceunitcost'></td>";
                    echo "<td  class='servicetotalcost'></td>";
                  
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
                echo "<tr class='retention'>";
                  echo "<td>Retención</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  
                    echo "<td class='serviceunitcost'></td>";
                    echo "<td  class='servicetotalcost'></td>";
                  
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                echo "</tr>";		
              echo "</tbody>";  
            echo "</table>";
          echo "</div>";  
          echo "<button class='addProduct' type='button'>".__('Add Product')."</button>	";
          echo '<div>'; 
            echo $this->Form->Submit('Guardar e Imprimir',[
              'class'=>'saveAndPrint',
              'name'=>'saveAndPrint',
              'style'=>'width:15em;',
              'div'=>['style'=>'display:inline-block;'],
            ]);
            echo $this->Form->Submit('Guardar y Otra',[
              'class'=>'saveAndNew',
              'name'=>'saveAndNew',
              'style'=>'width:15em;',
              'div'=>['style'=>'display:inline-block;margin-left:10em;'],
            ]);           
          echo '</div>';
        echo '</div>';
      echo '</div>';  
      echo '<div class="row">';
        echo '<div class="col-sm-12">';
      //if (!empty($inventoryDisplayOptionId)){	
        echo $this->InventoryCountDisplay->showInventoryTotals($otherMaterialsInventory, CATEGORY_OTHER,$plantId,['header_title'=>'Otros Productos']);
        echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory, CATEGORY_RAW,$plantId,['header_title'=>'Materia Prima']);
        if ($warehouseId != WAREHOUSE_INJECTION){
          echo $this->InventoryCountDisplay->showInventoryTotals($injectionMaterialsInventory, CATEGORY_PRODUCED,PLANT_COLINAS,['header_title'=>'Productos de Inyección (Colinas)']);
        }
        echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, CATEGORY_PRODUCED,$plantId,['header_title'=>'Productos Fabricados']);
      //}
      echo '</div>';
    echo '</div>';
  echo '</fieldset>';
  echo $this->Form->end();
    
  echo '<div id="msgModal" class="modal fade">';
    echo '<div class="modal-dialog">';
      echo '<div class="modal-content">';
        echo '<div class="modal-body">';
          echo '<div class="spinner-border text-primary" role="status">';
            echo '<span class="sr-only">Realizando cálculos ...</span>';
            
          echo '</div>';
          echo '<p id="clientProcessMsg" style="color:black">Calculando datos</p>';
        echo '</div>';
        echo '<div id="modalFooter" class="modal-footer hidden">';
          echo '<button id="closeMsg" type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        echo '</div>';
      echo '</div>';
    echo '</div>';
  echo '</div>';
  
  echo '<div id="priceModal" class="modal fade">';
		echo '<div class="modal-dialog" style="width:80%!important;;max-width:800px!important;">';
			echo '<div class="modal-content">';
				//echo '<div class="modal-header">';
				//echo '</div>';
				echo '<div class="modal-body">';
          echo $this->Form->input('PriceModal.row_id',['type'=>'hidden']);
          echo $this->Form->input('PriceModal.product_id',['class'=>'fixed','empty'=>[0=>'-- No producto --']]);
          echo $this->Form->input('PriceModal.raw_material_id',['class'=>'fixed','empty'=>[0=>'-- No materia prima --']]);
          echo $this->Form->input('PriceModal.threshold_volume',['label'=>'Volumen de Ventas','readonly'=>'true','type'=>'number','default'=>0,'class'=>'priceselector']);
          echo $this->Form->input('PriceModal.threshold_volume_price_category_id',['label'=>'Categoría Volumen Ventas','class'=>'fixed','options'=>$priceClientCategories,'default'=>PRICE_CLIENT_CATEGORY_VOLUME]);
          echo $this->Form->input('PriceModal.inventory_cost',['label'=>'Costo Según Inventario','readonly'=>true,'default'=>0,'type'=>($userRoleId === ROLE_ADMIN || $canSeeInventoryCost?'decimal':'hidden')]);
          echo '<p>Si el precio tiene fondo verde, haga clic en el precio para ocupar este precio para el producto.  Si es rojo no se puede utilizar.</p>';
          foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
            echo $this->Form->input('PriceModal.PriceClientCategory.'.$priceClientCategoryId.'.category_price',['label'=>('Precio '.$priceClientCategoryName),'type'=>'decimal','readonly'=>'true','class'=>'priceselector','default'=>0]);  
          }
          echo $this->Form->input('PriceModal.client_price',['label'=>'Específico Cliente','readonly'=>'true','type'=>'decimal','class'=>'priceselector','default'=>0]);
          echo $this->Form->input('PriceModal.minimum_price',['label'=>'Precio Mínimo','readonly'=>'true','type'=>'hidden','class'=>'priceselector','default'=>0]);
          
          echo $this->Form->input('PriceModal.selected_price',['label'=>'Precio Seleccionado','type'=>'decimal','default'=>0]);
				echo '</div>';
				echo '<div id="priceModalFooter" class="modal-footer">';
					echo '<button id="closePriceModal" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
          echo '<button id="applyPriceToProduct" type="button" class="btn btn-default">Aplicar Precio</button>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
  
  echo "<div id='editClient' class='modal fade'>";
    echo "<div class='modal-dialog'>";
      echo "<div class='modal-content'>";
        //echo $this->Form->create('EditClient', array('enctype' => 'multipart/form-data')); 
        echo "<div class='modal-header'>";
          //echo "<button type='button' class='close' data-dismiss='modal' aria-d-none='true'>&times;</button>";
          echo '<h4 class="modal-title">Editar Cliente <span id="ClientCompanyName"></span></h4>';
        echo "</div>";
        
        echo "<div class='modal-body'>";
          echo $this->Form->create('EditClient'); 
            echo "<fieldset>";
              echo $this->Form->input('id',['type'=>'d-none']);
              echo $this->Form->input('first_name',['readonly']);
              echo $this->Form->input('last_name',['readonly']);
              echo $this->Form->input('email');
              echo $this->Form->input('phone');
              echo $this->Form->input('ruc_number');
              
              echo $this->Form->input('client_type_id',['empty'=>[0=>'-- Tipo de Cliente --']]);
              echo $this->Form->input('zone_id',['empty'=>[0 => '-- Zona --']]);
              
              echo $this->Form->input('address');
            echo "</fieldset>";
          echo $this->Form->end(); 	
        echo "</div>";
        echo "<div class='modal-footer'>";
          echo "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
          echo "<button type='button' class='btn btn-primary' id='EditClientSave'>".__('Guardar Cambios')."</button>";
        echo "</div>";
        
      echo "</div>";
    echo "</div>";
  echo "</div>";
?>
</div>