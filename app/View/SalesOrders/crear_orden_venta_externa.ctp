<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<?php
  echo $this->Html->script('sales_modals');
?>
<script>
  var updatePrices=true;
  
  var jsClientRucNumbers=<?php echo json_encode($clientRucNumbers); ?>;
  var jsRawMaterialsPerProduct=<?php  echo json_encode($rawMaterialsAvailablePerFinishedProduct); ?>;
  var jsProductCategoriesPerProduct=<?php  echo json_encode($productCategoriesPerProduct); ?>;
  var jsProductTypesPerProduct=<?php echo json_encode($productTypesPerProduct); ?>;

   $('body').on('click','#closeMsg',function(e){	
    $('#modalFooter').addClass('hidden');
    hideMessageModal()
  });

  $('body').on('change','#SalesOrderWarehouseId',function(){	
    setSalesOrderCode()
  });
  $('body').on('change','#SalesOrderVendorUserId',function(){	
    setSalesOrderCode()
  });
  
  function setSalesOrderCode(){
    $('#clientProcessMsg').html('Calculando el código de la orden de venta');
		showMessageModal();
    
		var warehouseId=parseInt($('#SalesOrderWarehouseId').val());
    var userId=parseInt($('#SalesOrderVendorUserId').val());
		if (warehouseId>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>salesOrders/getSalesOrderCode/',
				data:{"warehouseId":warehouseId,"userId":userId},
				cache: false,
				type: 'POST',
				success: function (salesOrderCode) {
					$('#SalesOrderSalesOrderCode').val(salesOrderCode);
          $('#SalesOrderCrearOrdenVentaExternaForm legend').html("Crear Nueva Orden de Venta "+salesOrderCode);
          hideMessageModal()
          
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
          $('#clientProcessMsg').html('Se ha producido un error mientras se calculaba el código');
          $('#modalFooter').removeClass('hidden');
				}
			});
		}
    else {
      $('#SalesOrderSalesOrderCode').val('');
      $('#SalesOrderCrearOrdenVentaExternaForm legend').html("Crear Nueva Orden de Venta");
      hideMessageModal()
    }
	}
  
  $('body').on('change','#SalesOrderQuotationId',function(){
    setQuotationClientAndCurrencyData();
  });
  
  function setQuotationClientAndCurrencyData(){
    var quotationId=$('#SalesOrderQuotationId').val();
    if (parseInt(quotationId) > 0){
      $('#clientProcessMsg').html('Copiando los datos del cliente desde la cotización');
      showMessageModal();
      
      updatePrices=false;
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>quotations/getQuotationInfo/',
				data:{"quotationId":quotationId},
        dataType:'json',
				cache: false,
				type: 'POST',
				success: function (quotation) {
					var quotationClientId=quotation.Quotation.client_id;
          if (quotationClientId > 0){
            $('#SalesOrderClientId').val(quotationClientId)  
            $('#SalesOrderClientId').addClass('fixed');
          }
          else {
            //$('#SalesOrderClientId').val(<?php echo CLIENTS_VARIOUS; ?>);
            $('#SalesOrderClientId').val(0);
          }
          
          var boolClientGeneric = quotation.Client.bool_generic?1:0;
          
          var quotationClientName=quotation.Quotation.client_name;
          var quotationClientPhone=quotation.Quotation.client_phone;
          var quotationClientEmail=quotation.Quotation.client_email;
          //var quotationClientRuc=quotation.Quotation.client_ruc;
          var quotationClientTypeId=quotation.Quotation.client_type_id;
          var quotationZoneId=quotation.Quotation.zone_id;
          var quotationClientAddress=quotation.Quotation.client_address;
          
          $('#SalesOrderClientGeneric').val(boolClientGeneric)
          $('#SalesOrderClientName').val(quotationClientName)
          $('#SalesOrderClientPhone').val(quotationClientPhone)
          $('#SalesOrderClientEmail').val(quotationClientEmail)
          //$('#SalesOrderClientRuc').val(quotationClientRuc)
          
          //var quotationDueDate=new Date(quotation.Quotation.due_date);
          //var quotationDeliveryTime=quotation.Quotation.delivery_time;
          
          //$('#SalesOrderDueDateDay').val(('0'+quotationDueDate.getDate()).slice(-2));
          //$('#SalesOrderDueDateMonth').val(quotationDueDate);
          //$('#SalesOrderDueDateYear').val(quotationDueDate.getFullYear());
          //$('#SalesOrderDeliveryTime').val(quotationDeliveryTime)
          
          if (!boolClientGeneric){
            $('#SalesOrderClientRuc').val(jsClientRucNumbers[quotation.Quotation.client_id])
          }
          
          if (quotationClientTypeId > 0){
            $('#SalesOrderClientTypeId').val(quotationClientTypeId)
            $('#SalesOrderClientTypeId').addClass('fixed');
            $('#SalesOrderClientTypeId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#SalesOrderClientTypeId').removeClass('fixed');
            $('#SalesOrderClientTypeId option').attr('disabled', false);
            $('#SalesOrderClientTypeId').val(0)
          }
          
          if (quotationZoneId > 0){
            $('#SalesOrderZoneId').val(quotationZoneId)
            $('#SalesOrderZoneId').addClass('fixed');
            $('#SalesOrderZoneId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#SalesOrderZoneId').removeClass('fixed');
            $('#SalesOrderZoneId option').attr('disabled', false);
            $('#SalesOrderZoneId').val(0)
          }
          
          $('#SalesOrderClientAddress').val(quotationClientAddress)
          $('#SalesOrderDeliveryAddress').val(quotationClientAddress)
          
          $('#clientProcessMsg').html('Copiando los datos de moneda desde la cotización');
          var quotationCurrencyId=quotation.Quotation.currency_id;
          $('#SalesOrderCurrencyId').val(quotationCurrencyId)
          $('#SalesOrderCurrencyId').addClass('fixed');
          
          $('#clientProcessMsg').html('Copiando los datos de usuario desde la cotización');
          var quotationVendorUserId=quotation.Quotation.vendor_user_id;
          $('#SalesOrderVendorUserId').val(quotationVendorUserId);
          $('#SalesOrderVendorUserId').trigger('change');
          var creditAuthorizationUserId=quotation.Quotation.credit_authorization_user_id;
          $('#SalesOrderCreditAuthorizationUserId').val(creditAuthorizationUserId);
          
          $('#clientProcessMsg').html('Copiando los datos de IVA desde la cotización');
          var quotationBoolIva=quotation.Quotation.bool_iva;
          if (quotationBoolIva){
            $('#SalesOrderBoolIva').prop('checked', true);
          }
          else {
            $('#SalesOrderBoolIva').prop('checked', false);
          }
          
          $('#clientProcessMsg').html('Copiando los datos de retención desde la cotización');
          var quotationBoolRetention=quotation.Quotation.bool_retention;
          var quotationRetentionNumber=quotation.Quotation.retention_number;
          if (quotationBoolRetention){
            $('#SalesOrderBoolRetention').prop('checked', true);
            $('#SalesOrderRetentionAmount').parent().removeClass('hidden');
            $('#SalesOrderRetentionNumber').parent().removeClass('hidden');
          }
          else {
            $('#SalesOrderBoolRetention').prop('checked', false);
            $('#SalesOrderRetentionAmount').parent().addClass('hidden');
            $('#SalesOrderRetentionNumber').parent().addClass('hidden')
          }
          $('#SalesOrderRetentionNumber').val(quotationRetentionNumber);
          
          $('#clientProcessMsg').html('Aplicando el estado de crédito desde la cotización');
          var creditApplied=quotation.Quotation.bool_credit?1:0;
          if (creditApplied == 1){
            $('#BoolCredit').prop('checked',true);
            $('#SalesOrderBoolRetention').prop('checked',false);
            $('#SalesOrderRetentionNumber').val('');
          }
          else {
            $('#BoolCredit').prop('checked',false);
            $('#SalesOrderBoolRetention').prop('checked',false);
            $('#SalesOrderRetentionNumber').val('');
            $('#SaveAllowed').val(1);
          }
          $('#CreditData').removeClass('hidden');
          if (creditAuthorizationUserId){     
            setClientCreditData(quotationClientId,creditApplied,creditAuthorizationUserId);
          }
          else {
            setClientCreditData(quotationClientId,creditApplied,<?php echo $loggedUserId; ?>);
          }
          setQuotationProducts();
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
          $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los datos de la cotización');
          $('#modalFooter').removeClass('hidden');
				}
			});
    }
    else {
      $('#SalesOrderClientId').removeClass('fixed');
      $('#SalesOrderClientId').val(0);
      $('#SalesOrderCurrencyId').removeClass('fixed');
      $('#SalesOrderCurrencyId').val(<?php echo CURRENCY_CS; ?>);
      hideMessageModal()
    }  
  }
  
  function setQuotationProducts(){
    $('#clientProcessMsg').html('Copiando los productos desde la cotización');
    
    var quotationId=$('#SalesOrderQuotationId').val();
    var warehouseId=$('#SalesOrderWarehouseId').val();
    var currencyId=$('#SalesOrderCurrencyId').val();
    var exchangeRate=$('#SalesOrderExchangeRate').val();
    
    var salesOrderDay=$('#SalesOrderSalesOrderDateDay').children("option").filter(":selected").val();
		var salesOrderMonth=$('#SalesOrderSalesOrderDateMonth').children("option").filter(":selected").val();
		var salesOrderYear=$('#SalesOrderSalesOrderDateYear').children("option").filter(":selected").val();
    
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>quotations/getQuotationProducts/',
      data:{"quotationId":quotationId,'warehouseId':warehouseId,'currencyId':currencyId,'exchangeRate':exchangeRate,"salesOrderDay":salesOrderDay,"salesOrderMonth":salesOrderMonth,"salesOrderYear":salesOrderYear},
      cache: false,
      type: 'POST',
      success: function (quotationProducts) {
        $('#productsContainer').html(quotationProducts);
        calculateTotal();
      },
      error: function(e){
        alert(e.responseText);
        console.log(e);
        $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los productos de la cotización');
        $('#modalFooter').removeClass('hidden');
      }
    });  
  }
	$('body').on('change','#SalesOrderSalesOrderDateDay',function(){	
    //updateDueDate();
    updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});	
  $('body').on('change','#SalesOrderSalesOrderDateMonth',function(){	
		//updateDueDate();
    updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});	
  $('body').on('change','#SalesOrderSalesOrderDateYear',function(){	
		//updateDueDate();
    updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	
  function updateDueDate(){
		var salesorderdateday=$('#SalesOrderSalesOrderDateDay').val();
		var salesorderdatemonth=$('#SalesOrderSalesOrderDateMonth').val();
    var monthsOfYear=['01','02','03','04','05','06','07','08','09','10','11','12']
    salesorderdatemonth=monthsOfYear.indexOf(quotationdatemonth);
		var salesorderdateyear=$('#SalesOrderSalesOrderDateYear').val();
		var d=new Date(salesorderdateyear,salesorderdatemonth,salesorderdateday);
		var dueDate= new Date(d.getTime()+15*24*60*60*1000);		
		var duedatemonth=dueDate.getMonth();
    duedatemonth=monthsOfYear[duedatemonth]
		$('#SalesOrderDueDateDay').val(('0'+dueDate.getDate()).slice(-2));
		$('#SalesOrderDueDateMonth').val(duedatemonth);
		$('#SalesOrderDueDateYear').val(dueDate.getFullYear());
	}});
  
  function updateExchangeRate(){
    $('#clientProcessMsg').html('Actualizando la tasa de cambio');
    showMessageModal();
    
		var selectedday=$('#SalesOrderSalesOrderDateDay').children("option").filter(":selected").val();
		var selectedmonth=$('#SalesOrderSalesOrderDateMonth').children("option").filter(":selected").val();
		var selectedyear=$('#SalesOrderSalesOrderDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"selectedday":selectedday,"selectedmonth":selectedmonth,"selectedyear":selectedyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#SalesOrderExchangeRate').val(exchangerate);
        hideMessageModal()
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
        $('#clientProcessMsg').html('Se ha producido un error mientras se actualizaba la tasa de cambio');
        $('#modalFooter').removeClass('hidden');
			}
		});
	}

/*	$('body').on('change','#SalesOrderBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#salesOrderProducts').empty();
			$('#subtotal span.amountright').text('0');
		}
		else {
			$('#SalesOrderQuotationId').trigger('change');
		}
	});
*/	
/*
	$('body').on('change','#SalesOrderRemarkWorkingDaysBeforeReminder',function(){
		var working_days_before_reminder=$(this).val();
		if (working_days_before_reminder<1||working_days_before_reminder>10){
			alert("El número de días laborales debe estar entre 1 y 10");
		}
		else {
			var reminderdatemoment = addWeekdays(moment(), working_days_before_reminder);
			var reminderdateyear=moment(reminderdatemoment).format('YYYY');
			var reminderdatemonth=moment(reminderdatemoment).format('MM');
			var reminderdateday=moment(reminderdatemoment).format('DD');
			
			$('#SalesOrderRemarkReminderDateDay').val(reminderdateday);
			$('#SalesOrderRemarkReminderDateMonth').val(reminderdatemonth);
			$('#SalesOrderRemarkReminderDateYear').val(reminderdateyear);
		}		
	});
*/	

  $('body').on('change','#SalesOrderClientId',function(){	
    $('#clientProcessMsg').html('Buscando los datos del cliente');
    showMessageModal();
  
    var clientId=$('#SalesOrderClientId').val();
		if (clientId == 0){
      $('#SalesOrderClientName').val('')
      $('#SalesOrderClientGeneric').val(0)
      $('#SalesOrderClientPhone').val('')
      $('#SalesOrderClientEmail').val('')
      $('#SalesOrderClientRuc').val('')
      
      $('#SalesOrderClientTypeId').removeClass('fixed');
      $('#SalesOrderClientTypeId option').attr('disabled', false);
      $('#SalesOrderClientTypeId').val(0)
      
      $('#SalesOrderZoneId').removeClass('fixed');
      $('#SalesOrderZoneId option').attr('disabled', false);
      $('#SalesOrderZoneId').val(0)
      
      $('#SalesOrderClientAddress').val('')
      $('#SalesOrderDeliveryAddress').val('')
      
      $('#ClientCreditDays').val(0)
      $('#BoolCredit').prop('checked',false);
      $('#SalesOrderBoolRetention').prop('checked',false);
      $('#SalesOrderRetentionNumber').val('');
      $('#SaveAllowed').val(1);
      
      $('#CreditData').addClass('hidden');
      
      if (<?php echo ($boolInitialLoad?1:0); ?> != 1){
        if (updatePrices){
          updateProductPrices();
        }
        else {
          // set to true so that if the user changes the client, the prices do get updated
          // updatePrices is shut off for specific situations just before calling trigger change
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
          //var clientFirstName=clientdata.ThirdParty.first_name;
          //var clientLastName=clientdata.ThirdParty.last_name;
          
          var boolGenericClient=clientdata.ThirdParty.bool_generic?1:0;
          var clientPhone=clientdata.ThirdParty.phone;
          var clientEmail=clientdata.ThirdParty.email;
          var clientRuc=clientdata.ThirdParty.ruc_number;
          var clientTypeId=clientdata.ThirdParty.client_type_id;
          var zoneId=clientdata.ThirdParty.zone_id;
          var clientAddress=clientdata.ThirdParty.address;
          
          $('#SalesOrderClientGeneric').val(boolGenericClient)
          
          if (boolGenericClient){
            $('#SalesOrderClientName').val('')
            
            $('#SalesOrderClientPhone').val('')
            $('#SalesOrderClientEmail').val('')
            $('#SalesOrderClientRuc').val('')
            
            $('#SalesOrderClientTypeId').removeClass('fixed');
            $('#SalesOrderClientTypeId option').attr('disabled', false);
            $('#SalesOrderClientTypeId').val(0)
            
            $('#SalesOrderZoneId').removeClass('fixed');
            $('#SalesOrderZoneId option').attr('disabled', false);
            $('#SalesOrderZoneId').val(0)
            
            $('#SalesOrderClientAddress').val('')
            $('#SalesOrderDeliveryAddress').val('')
            
            $('#ClientCreditDays').val(0)
            $('#BoolCredit').prop('checked',false);
            $('#SalesOrderBoolRetention').prop('checked',false);
            $('#SalesOrderRetentionNumber').val('');
            $('#SaveAllowed').val(1);
            
            $('#CreditData').addClass('hidden');            
          }
          else {
            $('#SalesOrderClientName').val(clientCompanyName)
            
            $('#SalesOrderClientPhone').val(clientPhone)
            $('#SalesOrderClientEmail').val(clientEmail)
            $('#SalesOrderClientRuc').val(clientRuc)
            if (clientTypeId > 0){
              $('#SalesOrderClientTypeId').val(clientTypeId)
              $('#SalesOrderClientTypeId').addClass('fixed');
              $('#SalesOrderClientTypeId option:not(:selected)').attr('disabled', true);
            }
            else {
              $('#SalesOrderClientTypeId').removeClass('fixed');
              $('#SalesOrderClientTypeId option').attr('disabled', false);
              $('#SalesOrderClientTypeId').val(0)
            }
            if (zoneId > 0){
              $('#SalesOrderZoneId').val(zoneId)
              $('#SalesOrderZoneId').addClass('fixed');
              $('#SalesOrderZoneId option:not(:selected)').attr('disabled', true);
            }
            else {
              $('#SalesOrderZoneId').removeClass('fixed');
              $('#SalesOrderZoneId option').attr('disabled', false);
              $('#SalesOrderZoneId').val(0)
            }
            $('#SalesOrderClientAddress').val(clientAddress)
            $('#SalesOrderDeliveryAddress').val(clientAddress)
            var clientCreditDays=clientdata.ThirdParty.credit_days;
            var creditApplied=0
            if (clientCreditDays > 0){
              creditApplied=1
              $('#BoolCredit').prop('checked',true);
              $('#SalesOrderBoolRetention').prop('checked',false);
              $('#SalesOrderRetentionNumber').val('');
            }
            else {
              $('#BoolCredit').prop('checked',false);
              $('#SalesOrderBoolRetention').prop('checked',false);
              $('#SalesOrderRetentionNumber').val('');
              $('#SaveAllowed').val(1);
            }
          }
          var creditAuthorizationUserId=$('#SalesOrderCreditAuthorizationUserId').val();
          if (creditAuthorizationUserId){     
            setClientCreditData(clientId,creditApplied,creditAuthorizationUserId);
          }
          else {
            setClientCreditData(clientId,creditApplied,<?php echo $loggedUserId; ?>);
          }
        },
        error: function(e){
          console.log(e);
          alert(e.responseText);
          $('#clientProcessMsg').html('Se ha producido un error mientras se buscaban los datos del cliente');
          $('#modalFooter').removeClass('hidden');
        }
      });
    }
	});	
  
  $('body').on('change','#SalesOrderClientName',function(){	
    if ($('#SalesOrderClientGeneric').val()){
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
  
  $('body').on('change','#BoolCredit',function(){	
    updatePrices=false;
    var creditChecked=$(this).is(':checked')?1:0;
    var clientId=$('#SalesOrderClientId').val();
		if (clientId == 0){
      $('#BoolCredit').prop('checked',false);
    }
    else {
      var creditAuthorizationUserId=$('#SalesOrderCreditAuthorizationUserId').val();
      if (creditAuthorizationUserId){     
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
      data:{"clientId":clientId,"boolCreditApplied":creditApplied,"creditAuthorizationUserId":creditAuthorizationUserId},
      cache: false,
      type: 'POST',
      success: function (creditBlock) {
        $('#CreditData').html(creditBlock);
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
  
  $('body').on('change','#SalesOrderBoolDelivery',function(){	
    if ($(this).is(':checked')){
      $('#SalesOrderDeliveryAddress').closest('div').removeClass('d-none');
    }
    else {
      $('#SalesOrderDeliveryAddress').closest('div').addClass('d-none');
    }
  });
  
  $('body').on('change','#SalesOrderClientAddress',function(){	
    $('#SalesOrderDeliveryAddress').val($(this).val());
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
          if  (jsRawMaterialsPerProduct[productId][rawmaterialid]){
            //var newText=$(this).text()+" (A "+jsRawMaterialsPerProduct[productId][rawmaterialid][1]+" B "+jsRawMaterialsPerProduct[productId][rawmaterialid][2]+" C "+jsRawMaterialsPerProduct[productId][rawmaterialid][3]+")";
            var newText=$(this).text()+" (A "+jsRawMaterialsPerProduct[productId][rawmaterialid][1]+")";
            $(this).text(newText);
            $(this).removeAttr('disabled');
          }
          else{
            $(this).attr('disabled',true);
          }
        });
      
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').removeClass('hidden');
        //$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').removeClass('hidden');
        $('#'+affectedProductId).closest('tr').find('td.productunitprice div input.productprice').val(0);
        $('#'+affectedProductId).closest('tr').find('td.productunitprice input.defaultproductprice').val(0);
        
        hideMessageModal()
      }
      else {
        $('#clientProcessMsg').html('Escondiendo preformas');
        
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div select').val(0);
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').addClass('hidden');
        //$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').addClass('hidden');
        
        updateProductPrice($('#'+affectedProductId).closest('tr').attr('row'));
      }
		}
	});	
  
  $('body').on('change','.rawmaterialid div select',function(){	
    var rawMaterialId=$(this).val();
		var affectedRawMaterialId=$(this).attr('id');
    
    updateProductPrice($(this).closest('tr').attr('row'));
	});	
  
  function updateProductPrices(){
    $("#salesOrderProducts tbody tr:not(.totalrow):not(.hidden):not(.iva):not(.retention)").each(function() {
      updateProductPrice($(this).attr('row'))
    });
  }
  
  function updateProductPrice(rowId){
    $('#clientProcessMsg').html('Actualizando el precio del producto');
    showMessageModal();
  
    var currentRow=$('#salesOrderProducts').find("[row='" + rowId + "']");
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
    
      var clientId=$('#SalesOrderClientId').val();
    
      var selectedDay=$('#SalesOrderSalesOrderDateDay').children("option").filter(":selected").val();
      var selectedMonth=$('#SalesOrderSalesOrderDateMonth').children("option").filter(":selected").val();
      var selectedYear=$('#SalesOrderSalesOrderDateYear').children("option").filter(":selected").val();
    
      var warehouseId=$('#SalesOrderWarehouseId').val();
      
      $.ajax({
        //url: '<?php echo $this->Html->url('/'); ?>products/getProductCurrentCostAndDefaultPrice/',
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
            minimumPrice=roundToFour(productCost + 0.1);
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
          //else {
          //  //20201114 PRICE SHOULD ONLY BE SET BY APPLY PRICE TO PRODUCT
          //  //currentRow.find('td.productunitprice div input.productprice').val(selectedPrice);
          //}
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
      alert('No hay un producto seleccionado en esta fila');
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
      var currentRow=$('#salesOrderProducts').find("[row='" + $('#PriceModalRowId').val() + "']");
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
          //alert('El precio registrado para el producto '+productName+' ('+ productPrice +') está menor que el costo del producto ('+productCost+').  No se permitirá guardar la cotización porque esto constituye una venta con pérdida.');
          alert('El precio registrado para el producto '+productName+' ('+ productPrice +') no está autorizado.');
        }          
        else {
          var rawMaterialName=$(this).closest('tr').find('td.rawmaterialid div select option').filter(':selected').text()
          //alert('El precio registrado para el producto '+productName+' y materia prima '+rawMaterialName+' calidad A ('+productPrice+') está menor que el costo del producto ('+productCost+').  No se permitirá guardar la cotización porque esto constituye una venta con pérdida.');
          alert('El precio registrado para el producto '+productName+' y materia prima '+rawMaterialName+' calidad A ('+productPrice+') no está autorizado.');
        }
      }
		}
		calculateRow($(this).closest('tr').attr('row'));
	});	
	
	function calculateRow(rowId) {    
    $('#clientProcessMsg').html('Calculando el total de la fila '+rowId);
    showMessageModal();
  
		var currentrow=$('#salesOrderProducts').find("[row='" + rowId + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitprice=parseFloat(currentrow.find('td.productunitprice div input').val());
		
		var totalprice=quantity*unitprice;
		
		currentrow.find('td.producttotalprice div input').val(roundToTwo(totalprice));
    
    calculateTotal();
	}
	
	$('body').on('change','#SalesOrderBoolIva',function(){
		calculateTotal();
	});
  
  $('body').on('change','#SalesOrderBoolRetention',function(){	
		if ($(this).is(':checked')){
			$('tr.retention').removeClass('hidden');
			$('#SalesOrderRetentionNumber').parent().removeClass('hidden');
		}
		else {
			$('tr.retention').addClass('hidden');
			$('#SalesOrderRetentionNumber').parent().addClass('hidden');
		}
    calculateTotal();
	});
	
	function calculateTotal(){
    $('#clientProcessMsg').html('Calculando el total de la orden de venta');
    showMessageModal();
  
		var totalProductQuantity=0;
		var subtotalPrice=0;
		var ivaPrice=0;
    var retentionAmount=0;
		var totalPrice=0;
		$("#salesOrderProducts tbody tr:not(.hidden):not(.totalrow):not(.iva):not(.retention)").each(function() {
			var currentProductQuantity = parseFloat($(this).find('td.productquantity div input').val());
			if (!isNaN(currentProductQuantity)){
				var currentQuantity = parseFloat(currentProductQuantity);
				totalProductQuantity += currentQuantity;
			}
      
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			if (!isNaN(currentPrice)){
				subtotalPrice += currentPrice;
      //  $(this).find('td.iva div input').val(roundToTwo(0.15*currentPrice));
			//	ivaPrice+=roundToTwo(0.15*currentPrice);
			}
		});
    
    $('tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));
		
    subtotalPrice=roundToTwo(subtotalPrice)
    
		$('#subtotal span.amountright').text(subtotalPrice);
		$('tr.totalrow.subtotal td.totalprice div input').val(subtotalPrice.toFixed(2));
		
    if ($('#SalesOrderBoolIva').is(':checked')){
			ivaPrice=0.15*subtotalPrice
		}
    ivaPrice=roundToTwo(ivaPrice);
		$('#iva span.amountright').text(ivaPrice);
    $('tr.iva td.totalprice div input').val(ivaPrice.toFixed(2));	
  
    if ($('#SalesOrderBoolRetention').is(':checked')){
			retentionAmount=0.02*subtotalPrice
		}
    retentionAmount=roundToTwo(retentionAmount)
    $('#retention span.amountright').text(retentionAmount);
    $('tr.retention td.totalprice div input').val(retentionAmount.toFixed(2));	
		
    totalPrice=roundToTwo(subtotalPrice + ivaPrice);
		$('#total span.amountright').text(totalPrice);
		$('tr.totalrow.total td.totalprice div input').val(totalPrice.toFixed(2));	
		
    hideMessageModal();
    if ($('#SalesOrderClientGeneric').val() && (totalPrice>10000 || (totalPrice> 200 && $('#SalesOrderCurrencyId').val() == <?php echo CURRENCY_USD; ?>))){
      var currency = $('#SalesOrderCurrencyId').val() == <?php echo CURRENCY_USD; ?>?"US$":"C$"
      compareWithExistingClients('Esta orden de venta es para un monto de '+currency+' '+totalPrice + '; no se puede grabar el cliente como genérico.')
    }
		return false;
	}
  
  $('body').on('click','.addProduct',function(){
		var tableRow=$('#salesOrderProducts tbody tr.hidden:first');
		tableRow.removeClass("hidden");
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
		var currencyid=$('#SalesOrderCurrencyId').val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
	}
	
	$('body').on('change','#SalesOrderCurrencyId',function(){	
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
		// now update all prices
		var exchangerate=parseFloat($('#SalesOrderExchangeRate').val());
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
	});	
	
	function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
		var currencyid=$('#SalesOrderCurrencyId').children("option").filter(":selected").val();
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
		}
	}
	
	$(document).ready(function(){	
		formatCurrencies();
		//$('#SalesOrderRemarkWorkingDaysBeforeReminder').trigger('change');
    
    //$('#SalesOrderSalesOrderDateDay option:not(:selected)').attr('disabled', true);
    //$('#SalesOrderSalesOrderDateMonth option:not(:selected)').attr('disabled', true);
    //$('#SalesOrderSalesOrderDateYear option:not(:selected)').attr('disabled', true);
    
    setSalesOrderCode();
    
    if (<?php echo ($boolInitialLoad?1:0); ?> == 1 && <?php echo $quotationId; ?> >0){
      updatePrices=false;
      $('#SalesOrderQuotationId').trigger('change');
    }
    else {
      //if ($('#SalesOrderClientId').val() > 0 &&   $('#SalesOrderClientId').val() != <?php echo CLIENTS_VARIOUS; ?> ){ 
      if ($('#SalesOrderClientId').val() > 0 &&   $('#SalesOrderClientGeneric').val() != 1){ 
        updatePrices=false;
        $('#SalesOrderClientId').trigger('change');
      }
    }
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
    $('#saving').addClass('hidden');
    
	});
	
	 $('body').on('click','.save',function(e){	
    $(".save").data('clicked', true);
  });
  
  $('body').on('submit','#SalesOrderCrearOrdenVentaExternaForm',function(e){	
    if($(".save").data('clicked')){
      $('.save').attr('disabled', 'disabled');
      $("#SalesOrderCrearOrdenVentaExternaForm").fadeOut();
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
  });
</script>

<div class="salesOrders sales form fullwidth">
<?php
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la orden de venta...</p>";
    echo "</div>";
  echo "</div>";

	echo $this->Form->create('SalesOrder'); 
	echo '<fieldset style="font-size:1.2em;">';
		echo "<legend>Crear Orden de Venta Externa</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo '<div class="col-md-4" style="padding:5px;">';
					echo '<h1>Orden de Venta</h1>';
          echo $this->Form->input('sales_order_date',['type'=>'date','dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
					echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo $this->Form->Submit('Actualizar Bodega',['class'=>'updateWarehouse','name'=>'updateWarehouse']);
          
          echo $this->Form->input('sales_order_code',['readonly'=>'readonly','style'=>'min-width:165px;font-size:1.3em;font-weight:700;']);
          if ($userRoleId == ROLE_ADMIN){
            echo $this->Form->input('vendor_user_id',['default'=>$vendorUserId,'options'=>$users,'empty'=>[0=>'-- Vendedor --']]);
          }
          else {
            echo $this->Form->input('vendor_user_id',['default'=>$vendorUserId,'type'=>'hidden']);
          }
          echo $this->Form->input('record_user_id',['default'=>$loggedUserId,'type'=>'hidden']);
          
          echo $this->Form->input('quotation_id',[
            'default'=>$quotationId,
            'empty'=>[0=>'-- Seleccione Cotización --'],
            'style'=>'background-color:'.(count($quotations)>0?'yellow':'none'),
          ]);
					//echo $this->Form->input('bool_annulled',['default'=>false]);
					
          echo $this->Form->input('currency_id',['default'=>$currencyId,'type'=>'hidden']);
          echo $this->Form->input('exchange_rate',['default'=>$exchangeRateSalesOrder,'readonly'=>'readonly','type'=>'hidden']);
					echo $this->Form->input('bool_iva',[
            'label'=>'IVA',
            'checked'=>'checked',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('bool_retention',[
            'type'=>'checkbox',
            'label'=>'Retención',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('retention_number',['label'=>'Número Retención']);
          
          //echo $this->Form->input('due_date',['dateFormat'=>'DMY','minYear'=>2014, 'maxYear'=>date('Y')+1]);
          //echo $this->Form->input('delivery_time',['label'=>'Tiempo de Entrega']);
          
          echo $this->Form->input('bool_delivery',[
            'label'=>'Entrega a Domicilio?',
            'checked'=>$boolDelivery,
          ]);
          //echo $this->Form->input('driver_user_id',[
          //  'empty'=>[0=>'-- Conductor -- '],
          //]);
          //echo $this->Form->input('vehicle_id',[
          //  'empty'=>[0=>'-- Vehículo -- '],
          //]);
				echo '</div>';
				echo '<div class="col-md-4" style="padding:5px;">';
					echo '<h3>Cliente</h3>';
          echo $this->Form->input('client_id',['label'=>'Cliente Registrado','default'=>$clientId,'empty'=>($userRoleId === ROLE_CLIENT?'':[0=>'-- Seleccione Cliente --']),'type'=>($loggedUserId == 0? "hidden": "select")]);
          
          echo $this->Form->input('client_generic',['type'=>'hidden','default'=>0]);
          echo $this->Form->input('client_name',['label'=>'Nombre Cliente']);
          echo '<p>Especifica su teléfono o su correo para registrar su orden de venta</p>';
          echo $this->Form->input('client_phone',['label'=>'Teléfono','type'=>'phone']);
          echo $this->Form->input('client_email',['label'=>'Correo','type'=>'email']);
          echo $this->Form->input('client_ruc',['label'=>'RUC']);
          echo $this->Form->input('client_type_id',['default'=>0,'empty'=>[0=>'-- Tipo de Cliente --']]);
          echo $this->Form->input('zone_id',['default'=>0,'empty'=>[0=>'-- Zona --']]);
          echo $this->Form->input('client_address',['label'=>'Dirección']);
          echo $this->Form->input('delivery_address',['label'=>'Dirección de entrega', 'type'=>'textarea','div'=>['class'=>'input textarea d-none']]);
         
          echo '<div id="CreditData" class="hidden">';
            echo '<h4>Estado de Crédito del Cliente</h4>';
            echo '<p class="notallowed" id="creditWarning"></p>';
            if ($userRoleId == ROLE_ADMIN){
              echo $this->Form->input('set_save_allowed',['id'=>'SetSaveAllowed','type'=>'checkbox','label'=>'Guardar Venta','checked'=>true]);
            }
            echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'hidden','label'=>'Guardar Venta','readonly'=>'readonly','value'=>1]);
            
            echo $this->Form->input('credit_authorization_user_id',['label'=>false,'id'=>'CreditAuthorizationUserId','type'=>'hidden','value'=>$creditAuthorizationUserId]);
            echo $this->Form->input('credit_username',['label'=>'Crédito autorizado por','id'=>'CreditUsername','value'=>($creditAuthorizationUserId > 0?$users[$creditAuthorizingUserId]:'Crédito de cliente'),'readonly'=>true,'div'=>['class'=>(array_key_exists('bool_credit',$this->request->data) && $this->request->data['bool_credit']?'':'d-none')]]);
            
            echo $this->Form->input('bool_credit',['type'=>'checkbox','label'=>'Crédito','id'=>'BoolCredit']);
              
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
                echo '</tr>';
              echo '</thead>';
              echo '<tbody>';
              for ($i=0;$i<25;$i++){
                echo '<tr id="pendingPayment'.$i.'" class="hidden">';
                  echo '<td class="invoiceDate">Fecha</td>';
                  echo '<td class="invoiceCode">#</td>';
                  echo '<td  class="invoiceAmount"><span class="currency"></span><span class="amountright"></span></td>';
                echo '</tr>';
              }
              echo '</tbody>';
            echo '</table>';
          echo '</div>';  
				echo '</div>';
				
        echo '<div class="col-md-4" style="padding:5px;">';
					echo '<h3>Totales</h3>';
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
					echo "</div>";
          //echo $this->Form->input('SalesOrderRemark.user_id',['label'=>'Vendedor','value'=>$loggedUserId,'type'=>'hidden']);
					//echo $this->Form->input('SalesOrderRemark.remark_text',['rows'=>2,'label'=>'Remarca','required'=>false]);
					//echo $this->Form->input('SalesOrderRemark.working_days_before_reminder',['default'=>5]);
					//echo $this->Form->input('SalesOrderRemark.reminder_date',['type'=>'date','dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
					//echo $this->Form->input('SalesOrderRemark.action_type_id',['default'=>ACTION_TYPE_OTHER]);
				echo "</div>";
			echo "</div>";
		echo "</div>";
		echo "<div>";
      echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
      echo "<h3>Productos en Orden de Venta</h3>";
      echo "<div id='productsContainer'>";
        echo "<table id='salesOrderProducts' style='font-size:16px;'>";
          echo "<thead>";
            echo "<tr>";
              echo "<th>Producto</th>";
              //echo "<th>Unidades de Empaque</th>";
              echo "<th>Preforma</th>";
              echo "<th>Cantidad</th>";
              echo "<th style='width:12%;'>Precio Unitario</th>";
              echo "<th style='width:12%;'>Precio Total</th>";
              echo "<th>Acciones</th>";
            echo "</tr>";
          echo "</thead>";
          
          $counter=0;
          echo "<tbody style='font-size:100%;'>";
          for ($sop=0;$sop<count($requestProducts);$sop++){
            echo "<tr row='".$sop."'>";
              echo "<td class='productid'>";
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_id',['label'=>false,'value'=>$requestProducts[$sop]['SalesOrderProduct']['product_id'],'empty'=>['0'=>'Seleccione Producto']]);
                //echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_packaging_unit',['label'=>false,'value'=>0,'type'=>'hidden','class'=>'productpackagingunit']);
              echo "</td>";
              echo "<td class='rawmaterialid'>".$this->Form->input('SalesOrderProduct.'.$sop.'.raw_material_id',['label'=>false,'value'=>$requestProducts[$sop]['SalesOrderProduct']['raw_material_id'],'empty' =>[0=>__('-- Preforma --')]])."</td>";
              //echo "<td class='packagingunits'>".$this->Form->input('SalesOrderProduct.'.$sop.'.packaging_units',['label'=>false,'type'=>'text','readonly'=>'readonly'])."</td>";
              echo "<td class='productquantity amount'>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_quantity',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$sop]['SalesOrderProduct']['product_quantity'],'required'=>false])."</td>";
              echo "<td class='productunitprice'>";
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_unit_cost',['label'=>false,'type'=>'hidden','value'=>$requestProducts[$sop]['SalesOrderProduct']['product_unit_cost'],'class'=>'productcost']);               
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.default_product_unit_price',['label'=>false,'type'=>'hidden','value'=>$requestProducts[$sop]['SalesOrderProduct']['default_product_unit_price'],'class'=>'defaultproductprice']);
                echo "<span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_unit_price',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$sop]['SalesOrderProduct']['product_unit_price'],'class'=>'productprice']);
              echo "</td>";
              echo "<td class='producttotalprice'><span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_total_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','value'=>$requestProducts[$sop]['SalesOrderProduct']['product_total_price']])."</td>";
              echo "<td>";
                  echo "<button class='removeProduct' type='button'>".__('Remove Product')."</button>";
                  echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
                  echo "<button class='showPriceSelection' type='button'>Precios</button>";
              echo "</td>";
            echo "</tr>";
            $counter++;
          }
          for ($sop=$counter;$sop<QUOTATION_ARTICLES_MAX;$sop++){
            if ($sop==$counter){
              echo "<tr row='".$sop."'>";
            }
            else {
              echo "<tr row='".$sop."' class='hidden'>";
            }
              echo "<td class='productid'>";
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Producto']]);
                //echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_packaging_unit',['label'=>false,'value'=>0,'type'=>'hidden','class'=>'productpackagingunit']);
              echo "</td>";
              echo "<td class='rawmaterialid'>".$this->Form->input('SalesOrderProduct.'.$sop.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>[0=>__('-- Preforma --')]))."</td>";
              //echo "<td class='packagingunits'>".$this->Form->input('SalesOrderProduct.'.$sop.'.packaging_units',['label'=>false,'type'=>'text','readonly'=>'readonly'])."</td>";
              echo "<td class='productquantity amount'>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0])."</td>";
              echo "<td class='productunitprice'>";
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.product_unit_cost',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'productcost']);
                echo $this->Form->input('SalesOrderProduct.'.$sop.'.default_product_unit_price',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'defaultproductprice']);
                echo "<span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_unit_price',['label'=>false,'type'=>'decimal','default'=>0,'class'=>'productprice']);
              echo "</td>";
              echo "<td class='producttotalprice'><span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$sop.'.product_total_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>0])."</td>";
              echo "<td>";
                echo '<button class="removeProduct" type="button">Remover Producto</button>';
                echo '<button class="addProduct" type="button">'.__('Add Product').'</button>';
                echo '<button class="showPriceSelection" type="button">Precios</button>';
              echo "</td>";
            echo "</tr>";
          }
            echo "<tr class='totalrow subtotal'>";
              echo "<td>Subtotal</td>";
              echo "<td></td>";
              echo "<td class='productquantity amount right'><span></span></td>";
              echo "<td></td>";
              echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
              echo "<td></td>";
            echo "</tr>";		
            echo "<tr class='iva'>";
              echo "<td>IVA</td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
              echo "<td></td>";
            echo "</tr>";		
            echo "<tr class='totalrow total'>";
              echo "<td>Total</td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
              echo "<td></td>";
            echo "</tr>";		
            echo "<tr class='retention'>";
              echo "<td>Retención</td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
              echo "<td></td>";
            echo "</tr>";		
          echo "</tbody>";
        echo "</table>";
      echo '</div>';  
      echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);  
		echo "</div>";
		echo $this->Form->input('observation');
	echo "</fieldset>";
	echo '<div class="col-sm-12">';
  //if (!empty($inventoryDisplayOptionId)){	
    echo $this->InventoryCountDisplay->showInventoryTotals($otherMaterialsInventory, CATEGORY_OTHER,$plantId,['header_title'=>'Otros Productos']);
    //echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory, CATEGORY_RAW,$plantId,['header_title'=>'Materia Prima']);
    if ($warehouseId != WAREHOUSE_INJECTION){
      echo $this->InventoryCountDisplay->showInventoryTotals($injectionMaterialsInventory, CATEGORY_PRODUCED,PLANT_COLINAS,['header_title'=>'Productos de Inyección (Colinas)']);
    }
    //echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, CATEGORY_PRODUCED,$plantId,['header_title'=>'Productos Fabricados']);
    
  //}
  echo '</div>';  
  echo $this->Form->end();
  
  echo '<div id="msgModal" class="modal fade">';
		echo '<div class="modal-dialog">';
			echo '<div class="modal-content">';
				//echo '<div class="modal-header">';
				//echo '</div>';
				echo '<div class="modal-body">';
          echo '<div class="spinner-border text-primary" role="status">';
            echo '<span class="sr-only">Realizando cálculos ...</span>';
            
          echo '</div>';
          echo '<p id="clientProcessMsg">Calculando datos</p>';
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
?>
</div>
<?php 
/*
<div class="actions">
<?php
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Sales Orders'), ['action' => 'resumen')).'</li>';
		echo '<br/>';
	echo '</ul>';
?>
</div>
*/
