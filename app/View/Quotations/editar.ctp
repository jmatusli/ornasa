<script>
  var updatePrices=true;
  
  var jsRawMaterialsPerProduct=<?php  echo json_encode($rawMaterialsAvailablePerFinishedProduct); ?>;
  var jsProductCategoriesPerProduct=<?php  echo json_encode($productCategoriesPerProduct); ?>;
  var jsProductTypesPerProduct=<?php echo json_encode($productTypesPerProduct); ?>;
  
  $('body').on('change','#QuotationQuotationDateDay',function(){
		updateDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});
	$('body').on('change','#QuotationQuotationDateMonth',function(){
		updateDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});
	$('body').on('change','#QuotationQuotationDateYear',function(){
		updateDueDate();
		updateExchangeRate();
    if (updatePrices){
      updateProductPrices();
    }
    else {
      updatePrices=true;
    }
	});
	function updateDueDate(){
		var quotationdateday=$('#QuotationQuotationDateDay').val();
		var quotationdatemonth=$('#QuotationQuotationDateMonth').val();
    var monthsOfYear=['01','02','03','04','05','06','07','08','09','10','11','12']
    quotationdatemonth=monthsOfYear.indexOf(quotationdatemonth);
		var quotationdateyear=$('#QuotationQuotationDateYear').val();
		var d=new Date(quotationdateyear,quotationdatemonth,quotationdateday);
		var dueDate= new Date(d.getTime()+15*24*60*60*1000);		
		var duedatemonth=dueDate.getMonth();
    duedatemonth=monthsOfYear[duedatemonth]
		$('#QuotationDueDateDay').val(('0'+dueDate.getDate()).slice(-2));
		$('#QuotationDueDateMonth').val(duedatemonth);
		$('#QuotationDueDateYear').val(dueDate.getFullYear());
	}
	function updateExchangeRate(){
		var selectedday=$('#QuotationQuotationDateDay').children("option").filter(":selected").val();
		var selectedmonth=$('#QuotationQuotationDateMonth').children("option").filter(":selected").val();
		var selectedyear=$('#QuotationQuotationDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getquotationexchangerate/',
			data:{"selectedday":selectedday,"selectedmonth":selectedmonth,"selectedyear":selectedyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#QuotationExchangeRate').val(exchangerate);
			},
			error: function(e){
				alert(e.responseText);
				console.log(e);
				alert(e.responseText);
			}
		});
	}
	
	$('body').on('change','#QuotationRemarkWorkingDaysBeforeReminder',function(){
		var working_days_before_reminder=$(this).val();
		if (working_days_before_reminder<1||working_days_before_reminder>10){
			alert("El número de días laborales debe estar entre 1 y 10");
		}
		else {
			var reminderdatemoment = addWeekdays(moment(), working_days_before_reminder);
			var reminderdateyear=moment(reminderdatemoment).format('YYYY');
			var reminderdatemonth=moment(reminderdatemoment).format('MM');
			var reminderdateday=moment(reminderdatemoment).format('DD');
			
			$('#QuotationRemarkReminderDateDay').val(reminderdateday);
			$('#QuotationRemarkReminderDateMonth').val(reminderdatemonth);
			$('#QuotationRemarkReminderDateYear').val(reminderdateyear);
		}		
	});

	$('body').on('change','#QuotationDeliveryTime',function(){
		var deliverytime=$(this).val();
		$('td.productdeliverytime div input').each(function(){
			if ($(this).val()!=null){
				$(this).val(deliverytime);
			}
		});
	});
  $('body').on('change','#QuotationBoolRejected',function(){
		if ($(this).val()==1){
			$('#QuotationRejectedReasonId').parent().removeClass('hidden');			
		}
		else {
			$('#QuotationRejectedReasonId').parent().addClass('hidden');			
		}
	});
  
  $('body').on('change','#QuotationClientId',function(){	
    $('#clientProcessMsg').html('Buscando los datos del cliente');
    showMessageModel();
    
    var clientId=$(this).val();
		if (clientId == 0){
      $('#QuotationClientName').val('')
      $('#QuotationClientGeneric').val(0)
      $('#QuotationClientPhone').val('')
      $('#QuotationClientEmail').val('')
      //$('#QuotationClientRuc').val('')
      
      $('#QuotationClientTypeId').removeClass('fixed');
      $('#QuotationClientTypeId option').attr('disabled', false);
      $('#QuotationClientTypeId').val(0)
      
      $('#QuotationZoneId').removeClass('fixed');
      $('#QuotationZoneId option').attr('disabled', false);
      $('#QuotationZoneId').val(0)
      
      $('#QuotationClientAddress').val('')
      
      $('#ClientCreditDays').val(0)
      $('#BoolCredit').prop('checked',false);
      $('#QuotationBoolRetention').prop('checked',false);
      $('#QuotationRetentionNumber').val('');
      $('#SaveAllowed').val(1);
      
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
          
          var boolGenericClient=clientdata.ThirdParty.bool_generic?1:0;
          var clientPhone=clientdata.ThirdParty.phone;
          var clientEmail=clientdata.ThirdParty.email;
          var clientRuc=clientdata.ThirdParty.ruc_number;
          
          var clientTypeId=clientdata.ThirdParty.client_type_id;
          var zoneId=clientdata.ThirdParty.zone_id;
          
          var clientAddress=clientdata.ThirdParty.address;
          $('#QuotationClientName').val(clientCompanyName)
          
          $('#QuotationClientGeneric').val(boolGenericClient)
          
          $('#QuotationClientPhone').val(clientPhone)
          $('#QuotationClientEmail').val(clientEmail)
          $('#QuotationClientRuc').val(clientRuc)
          
          if (clientTypeId > 0){
            $('#QuotationClientTypeId').val(clientTypeId)
            $('#QuotationClientTypeId').addClass('fixed');
            $('#QuotationClientTypeId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#QuotationClientTypeId').removeClass('fixed');
            $('#QuotationClientTypeId option').attr('disabled', false);
            $('#QuotationClientTypeId').val(clientTypeId)
          }
          
          if (zoneId > 0){
            $('#QuotationZoneId').val(zoneId)
            $('#QuotationZoneId').addClass('fixed');
            $('#QuotationZoneId option:not(:selected)').attr('disabled', true);
          }
          else {
            $('#QuotationZoneId').removeClass('fixed');
            $('#QuotationZoneId option').attr('disabled', false);
            $('#QuotationZoneId').val(0)
          }
          
          $('#QuotationClientAddress').val(clientAddress)
          
          var clientCreditDays=clientdata.ThirdParty.credit_days;
          var creditApplied=0
          if (clientCreditDays > 0){
            creditApplied=1
            $('#BoolCredit').prop('checked',true);
            $('#QuotationBoolRetention').prop('checked',false);
            $('#QuotationRetentionNumber').val('');
          }
          else {
            $('#BoolCredit').prop('checked',false);
            $('#QuotationBoolRetention').prop('checked',false);
            $('#QuotationRetentionNumber').val('');
            $('#SaveAllowed').val(1);
          }
          
          setClientCreditData(clientId,creditApplied);
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
  
  $('body').on('change','#QuotationClientName',function(){	
    if ($('#QuotationClientGeneric').val()){
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
    var clientId=$('#QuotationClientId').val();
		if (clientId == 0){
      $('#BoolCredit').prop('checked',false);
    }
    else {
      setClientCreditData(clientId,creditChecked)  
    }
  });
  
  $('body').on('change','#SetSaveAllowed',function(){	
    var allowSaving=$(this).is(':checked');
    $('#SaveAllowed').val(allowSaving?1:0)
  });
  
  function setClientCreditData(clientId,creditApplied){	
    $('#clientProcessMsg').html('Estableciendo el estado de crédito del cliente');
    showMessageModel();
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>thirdParties/getCreditBlock/',
      data:{"clientId":clientId,"boolCreditApplied":creditApplied},
      cache: false,
      type: 'POST',
      success: function (creditBlock) {
        $('#CreditData').html(creditBlock);
        // note that we don't run setcreditconditions as in crear venta; we do not want to hide retention data (which in crearventa only show for cash invoices, and we do not set the due date as that depends on the day of the invoice
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
        $('#modalFooter').removeClass('hidden');
      }
    });
	}
  
	$('body').on('change','.productid div select',function(){
		$('#clientProcessMsg').html('Buscando la categoría del producto para el producto seleccionado');
    showMessageModel();
  
    var rowid=$(this).closest('tr').attr('row');
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
            var newText=$(this).text()+" (A "+jsRawMaterialsPerProduct[productId][rawmaterialid][1]+")";
            $(this).text(newText);
            $(this).removeAttr('disabled');
          }
          else{
            $(this).attr('disabled',true);
          }
        });
      
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').removeClass('hidden');
        $('#'+affectedProductId).closest('tr').find('td.productunitprice div input.productprice').val(0);
        $('#'+affectedProductId).closest('tr').find('td.productunitprice input.defaultproductprice').val(0);
        
         hideMessageModal()
      }
      else {
        $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').addClass('hidden');
        updateProductPrice($('#'+affectedProductId).closest('tr').attr('row'));
      }
		}
    
		//calculateRow(rowid);
		//calculateTotal();
	});	
  
  $('body').on('change','.rawmaterialid div select',function(){	
    var rawMaterialId=$(this).val();
		var affectedRawMaterialId=$(this).attr('id');
    
    updateProductPrice($(this).closest('tr').attr('row'));
		//calculateRow($(this).closest('tr').attr('row'));
		//calculateTotal();
	});	
  
  function updateProductPrices(){
    $("#quotationProducts tbody tr:not(.totalrow):not(.hidden):not(.iva):not(.retention)").each(function() {
      updateProductPrice($(this).attr('row'))
    });
  }
  
  function updateProductPrice(rowId){
    $('#clientProcessMsg').html('Actualizando el precio del producto');
    showMessageModel();
  
    var currentRow=$('#quotationProducts').find("[row='" + rowId + "']");
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
      
      var clientId=$('#QuotationClientId').val();
      
      var selectedDay=$('#QuotationQuotationDateDay').children("option").filter(":selected").val();
      var selectedMonth=$('#QuotationQuotationDateMonth').children("option").filter(":selected").val();
      var selectedYear=$('#QuotationQuotationDateYear').children("option").filter(":selected").val();
    
      var warehouseId=$('#QuotationWarehouseId').val();
      
      $.ajax({
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
  
  // 20201114 REMOVED THIS ALLOWED THE USERS TO ESTABLISH A LOWER PRICE AND THEN CLICK ON CLOSE
  //$('body').on('change','#PriceModalSelectedPrice',function(){
  //  var selectedPrice=$(this).val();
  //  $('tr[row="'+$('#PriceModalRowId').val()+'"] td.productunitprice div input.productprice').val(selectedPrice);
  //});
  
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
      var currentRow=$('#quotationProducts').find("[row='" + $('#PriceModalRowId').val() + "']");
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
          alert('El precio registrado para el producto '+productName+' ('+ productPrice +') no está autorizado.');
        }          
        else {
          var rawMaterialName=$(this).closest('tr').find('td.rawmaterialid div select option').filter(':selected').text()
          alert('El precio registrado para el producto '+productName+' y materia prima '+rawMaterialName+' calidad A ('+productPrice+') no está autorizado.');
        }
      }
		}
		calculateRow($(this).closest('tr').attr('row'));
	});	
	
	function calculateRow(rowId) {    
		$('#clientProcessMsg').html('Calculando el total de la fila '+rowId);
    showMessageModel();
    
    var currentrow=$('#quotationProducts').find("[row='" + rowId + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitprice=parseFloat(currentrow.find('td.productunitprice div input').val());
		
		var totalprice=quantity*unitprice;
		
		currentrow.find('td.producttotalprice div input').val(roundToTwo(totalprice));
    
    calculateTotal();
	}
	
	$('body').on('change','#QuotationBoolIva',function(){
		calculateTotal();
	});
	
  $('body').on('change','#QuotationBoolRetention',function(){	
		if ($(this).is(':checked')){
			$('tr.retention').removeClass('hidden');
			$('#QuotationRetentionNumber').parent().removeClass('hidden');
		}
		else {
			$('tr.retention').addClass('hidden');
			$('#QuotationRetentionNumber').parent().addClass('hidden');
		}
    calculateTotal();
	});
  
	function calculateTotal(){
		$('#clientProcessMsg').html('Calculando el total de la cotización');
    showMessageModel();
  
    var totalProductQuantity=0;
		var subtotalPrice=0;
		var ivaPrice=0
    var retentionAmount=0;
		var totalPrice=0
		$("#quotationProducts tbody tr:not(.hidden):not(.totalrow):not(.iva):not(.retention)").each(function() {
			var currentProductQuantity = $(this).find('td.productquantity div input');
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
		
    if ($('#QuotationBoolIva').is(':checked')){
			ivaPrice=0.15*subtotalPrice
		}
    ivaPrice=roundToTwo(ivaPrice);
		$('#iva span.amountright').text(ivaPrice);
		$('tr.totalrow.iva td.totalprice div input').val(ivaPrice.toFixed(2));
		
    if ($('#QuotationBoolRetention').is(':checked')){
			retentionAmount=0.02*subtotalPrice
		}
    retentionAmount=roundToTwo(retentionAmount)
    $('#retention span.amountright').text(retentionAmount);
    $('tr.retention td.totalprice div input').val(retentionAmount.toFixed(2));	
    
    totalPrice=roundToTwo(subtotalPrice + ivaPrice);
		$('#total span.amountright').text(roundToTwo(totalPrice));
		$('tr.totalrow.total td.totalprice div input').val(totalPrice.toFixed(2));
		
		hideMessageModal();
    if ($('#QuotationClientGeneric').val() && (totalPrice>10000 || (totalPrice> 200 && $('#QuotationCurrencyId').val() == <?php echo CURRENCY_USD; ?>))){
      var currency = $('#QuotationCurrencyId').val() == <?php echo CURRENCY_USD; ?>?"US$":"C$"
      compareWithExistingClients('Esta cotización es para un monto de '+currency+' '+totalPrice + '; no se puede grabar el cliente como genérico.')
    }
		return false;
	}
  
  $('body').on('click','.addProduct',function(){
		var tableRow=$('#quotationProducts tbody tr.hidden:first');
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
		var currencyid=$('#QuotationCurrencyId').val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
	}
  
  $('body').on('change','#QuotationCurrencyId',function(){
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
		// now update all prices
		var exchangerate=parseFloat($('#QuotationExchangeRate').val());
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice/exchangerate);
				$(this).find('div input').val(newprice);
        calculateRow($(this).closest('tr').attr('row'));
			});
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice*exchangerate);
				$(this).find('div input').val(newprice);
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
		var currencyid=$('#QuotationCurrencyId').children("option").filter(":selected").val();
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
		}
	}
	
  
  var jsGenericClientIds=<?php echo json_encode($genericClientIds); ?>; 
	$(document).ready(function(){
    formatCurrencies();
    
    updateDueDate();
		updateExchangeRate();
  
		if ($('#QuotationBoolRejected').val()==1){
			$('#QuotationRejectedReasonId').parent().removeClass('hidden');			
		}
		else {
			$('#QuotationRejectedReasonId').parent().addClass('hidden');			
		}
	
		$('#QuotationRemarkUserId').addClass('fixed');
		$('#QuotationRemarkWorkingDaysBeforeReminder').trigger('change');
		
    var clientId=$('#QuotationClientId').val();
    var creditApplied=<?php echo $this->request->data['Quotation']['bool_credit']?1:0; ?>;
    //var arrayvalues=Object.values(jsGenericClientIds);
    //var inarrayresult=jQuery.inArray(clientId,Object.values(jsGenericClientIds));
    if (jQuery.inArray(clientId,Object.values(jsGenericClientIds)) != -1){
      $('#QuotationClientGeneric').val(1);
    }
    if (clientId > 0){
      updatePrices=false;
      setClientCreditData(clientId,creditApplied);
    }
    
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
  
  // inspired by https://github.com/twbs/bootstrap/issues/3902 kimsy
  // https://stackoverflow.com/questions/19506672/how-to-check-if-bootstrap-modal-is-open-so-i-can-use-jquery-validate
  var showInProgress = false;
  var hideInProgress = false;
  
  function showModal(elementId) {
      if (hideInProgress) {
      //    showModalId = elementId;
      } 
      else {
        showInProgress = true;
        $("#" + elementId).on('shown.bs.modal', showCompleted);
        $("#" + elementId).modal("show");
        
        function showCompleted() {
          showInProgress = false;
          
          $("#" + elementId).off('shown.bs.modal');
        }
      }
  };

  function hideModal(elementId) {
      hideInProgress = true;
      $("#" + elementId).on('hidden.bs.modal', hideCompleted);
      $("#" + elementId).modal("hide");

      function hideCompleted() {
          hideInProgress = false;
          
          $("#" + elementId).off('hidden.bs.modal');
      }
  };
  
  function showMessageModel(){
    if (!$('#msgModal').is(':visible') && !showInProgress){
      showModal('msgModal');
    }
  }
  function hideMessageModal(){
    $('#clientProcessMsg').html('');
    if (showInProgress){
      setTimeout(hideMessageModal, 500);
    }
    else {
      if ($('#msgModal').is(':visible')){
        hideModal('msgModal');
      }
    }
  }
  $('body').on('click','#closeMsg',function(e){	
    $('#modalFooter').addClass('hidden');
    $('#modalFooter').addClass('hidden');
    hideMessageModal()
  });
</script>
<div class="quotations form fullwidth">
<?php 
	echo $this->Form->create('Quotation'); 
	echo '<fieldset style="font-size:1.2em;">';
		echo "<legend>".__('Edit Quotation').' '.$this->request->data['Quotation']['quotation_code']."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo '<div class="col-md-4" style="padding:5px;">';
					echo $this->Form->input('id');
          echo '<h3>Cotización</h3>';
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);

          echo $this->Form->input('quotation_date',['dateFormat'=>'DMY','minYear'=>2014, 'maxYear'=>date('Y')]);
					echo $this->Form->input('exchange_rate',['default'=>$exchangeRateQuotation,'readonly'=>'readonly']);
					echo $this->Form->input('quotation_code',['readonly'=>'readonly']);
          //if ($userRoleId == ROLE_ADMIN){
            echo $this->Form->input('vendor_user_id',['value'=>$vendorUserId,'options'=>$users,'class'=>'fixed']);
          //}
          //else {
          //  echo $this->Form->input('vendor_user_id',['value'=>$loggedUserId,'type'=>'hidden']);
          //}	
          echo $this->Form->input('record_user_id',['default'=>$loggedUserId,'type'=>'hidden']);
          echo $this->Form->input('bool_annulled',['type'=>'hidden', 'value'=>0]);
          echo $this->Form->input('bool_iva',[
            'label'=>'IVA',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('bool_retention',[
            'type'=>'checkbox',
            'label'=>'Retención',
            'div'=>['class'=>'input checkbox checkboxleft'],
          ]);
          echo $this->Form->input('retention_number',['label'=>'Número Retención']);

					echo $this->Form->input('bool_rejected',array('label'=>'Estado Cotización','options'=>$rejectedOptions));
					echo $this->Form->input('rejected_reason_id',array('empty'=>array('0'=>'Seleccione Razón de Caída')));
					
          echo $this->Form->input('due_date',['dateFormat'=>'DMY','minYear'=>2014, 'maxYear'=>date('Y')+1]);
          echo $this->Form->input('delivery_time',['label'=>'Tiempo de Entrega']);
          
          echo $this->Form->Submit('Actualizar Bodega',['class'=>'updateWarehouse','name'=>'updateWarehouse']);

				echo "</div>";
				echo '<div class="col-md-4" style="padding:5px;">';
          echo '<h3>Cliente</h3>';
          echo $this->Form->input('client_id',['label'=>'Cliente Registrado','empty'=>($userRoleId === ROLE_CLIENT?"":[0=>'-- Seleccione Cliente --']),'type'=>($loggedUserId == 0? "hidden": "select")]);
					
          echo $this->Form->input('client_generic',['type'=>'hidden','default'=>0]);
          echo $this->Form->input('client_name',['label'=>'Nombre Cliente']);
          echo '<p>Especifica su teléfono o su correo para registrar su cotización</p>';
          echo $this->Form->input('client_phone',['label'=>'Teléfono','type'=>'phone']);
          echo $this->Form->input('client_email',['label'=>'Correo','type'=>'email']);
          //echo $this->Form->input('client_ruc',['label'=>'RUC']);
          echo $this->Form->input('client_type_id',['default'=>0,'empty'=>[0=>'-- Tipo de Cliente --']]);
          echo $this->Form->input('zone_id',['default'=>0,'empty'=>[0=>'-- Zona --']]);
          echo $this->Form->input('client_address',['label'=>'Dirección']);
					
          echo '<div id="CreditData" class="hidden">';
            echo $this->Form->input('save_allowed',['id'=>'SaveAllowed','type'=>'hidden','label'=>'Guardar Venta','readonly'=>'readonly','value'=>1]);
            echo $this->Form->input('bool_credit',['type'=>'checkbox','label'=>'Crédito','id'=>'BoolCredit','checked'=>false]);
          echo '</div>';  
				echo "</div>";
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
            echo $this->Form->input('currency_id',['type'=>'hidden']);
					echo "</div>";
					
					//echo $this->Form->input('QuotationRemark.user_id',array('label'=>'Vendedor','value'=>$loggedUserId,'type'=>'hidden'));
					//echo $this->Form->input('QuotationRemark.remark_text',array('rows'=>'2'));
					//echo $this->Form->input('QuotationRemark.working_days_before_reminder',array('default'=>5));
					//echo $this->Form->input('QuotationRemark.reminder_date',array('dateFormat'=>'DMY'));
					//echo $this->Form->input('QuotationRemark.action_type_id',array('default'=>ACTION_TYPE_OTHER));
					//if (!empty($quotationRemarks)){
					//	echo "<table>";
					//		echo "<thead>";
					//			echo "<tr>";
					//				echo "<th>Fecha</th>";
					//				echo "<th>Vendedor</th>";
					//				echo "<th>Remarca</th>";
					//			echo "</tr>";
					//		echo "</thead>";
					//		echo "<tbody>";							
					//		foreach ($quotationRemarks as $quotationRemark){
					//			//pr($quotationRemark);
					//			$remarkDateTime=new DateTime($quotationRemark['QuotationRemark']['remark_datetime']);
					//			echo "<tr>";
					//				echo "<td>".$remarkDateTime->format('d-m-Y H:i')."</td>";
					//				echo "<td>".$quotationRemark['User']['username']."</td>";
					//				echo "<td>".$quotationRemark['QuotationRemark']['remark_text']."</td>";
					//			echo "</tr>";
					//		}
					//		echo "</tbody>";
					//	echo "</table>";
					//}
				echo "</div>";
			echo "</div>";
		echo "</div>";
    echo "<div>";
      echo $this->Form->Submit(__('Guardar'),['class'=>'save','name'=>'save']);
      echo "<h3>Productos en Cotización</h3>";
      echo "<div id='productsContainer'>";
        echo "<table id='quotationProducts' style='font-size:16px;'>"; 
          echo "<thead>";
            echo "<tr>";
              echo "<th>".__('Product')."</th>";
              echo "<th>".__('Preforma')."</th>";
              echo "<th style='width:10%'>".__('Quantity')."</th>";
              echo "<th style='width:10%'>".__('Unit Price')."</th>";
              echo "<th>".__('Total Price')."</th>";
              echo "<th>".__('Actions')."</th>";
            echo "</tr>";
          echo "</thead>";
          echo "<tbody style='font-size:100%;'>";
          $counter=0;
          if (count($requestProducts)>0){
            for ($i=0;$i<count($requestProducts);$i++) { 
              //pr($requestProducts[$i]['QuotationProduct']);
              echo "<tr row='".$i."'>";
                echo "<td class='productid'>".$this->Form->input('QuotationProduct.'.$i.'.product_id',['label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['product_id'],'empty' =>['0'=>'-- Producto --']])."</td>";
                echo "<td class='rawmaterialid'>".$this->Form->input('QuotationProduct.'.$i.'.raw_material_id',['label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['raw_material_id'],'empty' =>[0=>'-- Preforma --']])."</td>";
                //echo "<td class='productimage'></td>";
                //echo "<td class='productdescription'>".$this->Form->textarea('QuotationProduct.'.$i.'.product_description',['label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['product_description'],'cols'=>1,'rows'=>10])."</td>";
                //echo "<td class='productdeliverytime'>".$this->Form->input('QuotationProduct.'.$i.'.delivery_time',['label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['delivery_time']])."</td>";
                echo "<td class='productquantity'>".$this->Form->input('QuotationProduct.'.$i.'.product_quantity',['type'=>'numeric','label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['product_quantity'],'required'=>false])."</td>";
                echo "<td class='productunitprice amount'>";
                  echo $this->Form->input('QuotationProduct.'.$i.'.product_unit_cost',['label'=>false,'type'=>'hidden','value'=>$requestProducts[$i]['QuotationProduct']['product_unit_cost'],'class'=>'productcost']);
                  echo $this->Form->input('QuotationProduct.'.$i.'.default_product_unit_price',[
                    'label'=>false,
                    'type'=>'hidden',
                    'value'=>(empty($requestProducts[$i]['QuotationProduct']['default_product_unit_price'])?$requestProducts[$i]['QuotationProduct']['product_unit_price']:$requestProducts[$i]['QuotationProduct']['default_product_unit_price']),
                    'class'=>'defaultproductprice'
                  ]);
                  echo $this->Form->input('QuotationProduct.'.$i.'.product_unit_price',['label'=>false,'type'=>'decimal','value'=>$requestProducts[$i]['QuotationProduct']['product_unit_price'],'class'=>'productprice']);
                echo "</td>";
                echo "<td class='producttotalprice amount'>".$this->Form->input('QuotationProduct.'.$i.'.product_total_price',['type'=>'decimal','label'=>false,'value'=>$requestProducts[$i]['QuotationProduct']['product_total_price'],'readonly'=>'readonly'])."</td>";
                //echo "<td class='hidden boolnoiva'>".$this->Form->input('QuotationProduct.'.$i.'.bool_no_iva',['label'=>false,'checked'=>$requestProducts[$i]['QuotationProduct']['bool_no_iva']])."</td>";
                //echo "<td class='booliva'>".$this->Form->input('QuotationProduct.'.$i.'.bool_iva',['label'=>false,'checked'=>$requestProducts[$i]['QuotationProduct']['bool_iva']])."</td>";
                echo "<td>";
                  echo "<button class='removeProduct' type='button'>".__('Remove Product')."</button>";
                  echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
                  echo "<button class='showPriceSelection' type='button'>Precios</button>";
                  //echo $this->Html->link(__('Ver'), ['controller' => 'products', 'action' => 'view',$requestProducts[$i]['QuotationProduct']['product_id']],['class'=>'productview','target'=>'_blank']);
                echo "</td>";
              echo "</tr>";			
              $counter++;
            }
          }
          for ($j=$counter;$j<QUOTATION_ARTICLES_MAX;$j++) { 
            if ($j==$counter){
              echo "<tr row='".$j."'>";
            } 
            else {
              echo "<tr row='".$j."' class='hidden'>";
            } 
              echo "<td class='productid'>".$this->Form->input('QuotationProduct.'.$j.'.product_id',['label'=>false,'default'=>'0','empty' =>['0'=>'-- Producto --']])."</td>";
              echo "<td class='rawmaterialid'>".$this->Form->input('QuotationProduct.'.$j.'.raw_material_id',['label'=>false,'default'=>'0','empty' =>[0=>'-- Preforma --']])."</td>";
              //echo "<td class='productimage'></td>";
              ////echo "<td class='productdescription'>".$this->Form->input('QuotationProduct.'.$j.'.product_description',['label'=>false])."</td>";
              //echo "<td class='productdescription'>".$this->Form->textarea('QuotationProduct.'.$j.'.product_description',['label'=>false,'cols'=>1,'rows'=>10])."</td>";
              //echo "<td class='productdeliverytime'>".$this->Form->input('QuotationProduct.'.$j.'.delivery_time',['label'=>false])."</td>";
              echo "<td class='productquantity amount'>".$this->Form->input('QuotationProduct.'.$j.'.product_quantity',['type'=>'numeric','label'=>false,'default'=>'0','required'=>'required'])."</td>";
              echo "<td class='productunitprice'>";
                 echo $this->Form->input('QuotationProduct.'.$j.'.product_unit_cost',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'productcost']);
                  echo $this->Form->input('QuotationProduct.'.$j.'.default_product_unit_price',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'defaultproductprice']);
                  echo $this->Form->input('QuotationProduct.'.$j.'.product_unit_price',['label'=>false,'type'=>'decimal','default'=>0,'class'=>'productprice']);
                echo "</td>";
              
              echo "<td class='producttotalprice'>".$this->Form->input('QuotationProduct.'.$j.'.product_total_price',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly'])."</td>";
              //echo "<td class='hidden boolnoiva'>".$this->Form->input('QuotationProduct.'.$j.'.bool_no_iva',['label'=>false,'default'=>0])."</td>";
              //echo "<td class='booliva'>".$this->Form->input('QuotationProduct.'.$j.'.bool_iva',['label'=>false,'default'=>true])."</td>";
              echo "<td class='productactions'>";
                echo "<button class='removeProduct' type='button'>".__('Remove Product')."</button>";
                echo "<button class='addProduct' type='button'>".__('Add Product')."</button>";
                echo "<button class='showPriceSelection' type='button'>Precios</button>";
                //echo $this->Html->link(__('Ver'), ['controller' => 'products', 'action' => 'view'],['class'=>'productview','target'=>'_blank']);
              echo "</td>";
            echo "</tr>";			
          }
          echo "<tr class='totalrow subtotal'>";
            echo "<td>Subtotal</td>";
            //echo "<td></td>";
            //echo "<td></td>";
            echo "<td></td>";
            echo "<td class='productquantity amount right'></td>";
            echo "<td></td>";
            echo "<td class='totalprice amount right'>".$this->Form->input('price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
            echo "<td></td>";
          echo "</tr>";		
          echo "<tr class='totalrow iva'>";
            echo "<td>IVA</td>";
            //echo "<td></td>";
            //echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td class='totalprice amount right'>".$this->Form->input('price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
            echo "<td></td>";
          echo "</tr>";		
          echo "<tr class='totalrow total'>";
            echo "<td>Total</td>";
            //echo "<td></td>";
            //echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td class='totalprice amount right'>".$this->Form->input('price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
            echo "<td></td>";
          echo "</tr>";		
          echo "<tr class='retention'>";
              echo "<td>Retención</td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "<td class='totalprice amount right'>".$this->Form->input('retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
              echo "<td></td>";
            echo "</tr>";		
          echo "</tbody>";
        echo "</table>";
		echo $this->Form->submit(__('Guardar'),['name'=>'save', 'class'=>'save']); 
		
		echo $this->Form->input('observation',['rows'=>'2','label'=>'Observaciones para pdf']);
		
		//echo $this->Form->input('bool_print_delivery_time',array('label'=>'Mostrar tiempo de entrega en pdf?'));
		
		//echo $this->Form->input('payment_form',array('label'=>'Forma de Pago'));
		//echo $this->Form->input('remark_delivery',array('label'=>'Remarca sobre entrega'));
		echo $this->Form->input('remark_cheque',['label'=>'Remarca sobre cheque']);
		//echo $this->Form->input('remark_elaboration',['label'=>'Remarca sobre elaboración']);
		echo $this->Form->input('text_client_signature',['label'=>'Etiqueta firma cliente']);
		echo $this->Form->input('text_authorization',['label'=>'Etiqueta autorización']);
		echo $this->Form->input('text_seal',['label'=>'Etiqueta sello']);
		echo $this->Form->input('authorization',['label'=>'Persona quien autoriza']);
		
	echo "</fieldset>";

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
		echo '<li>'.$this->Html->link(__('List Quotations'), ['action' => 'resumen')).'</li>';
		echo '<br/>';
    
		<li><?php echo $this->Html->link(__('List Clients'), ['controller' => 'clients', 'action' => 'index']); ?> </li>
		<li><?php echo $this->Html->link(__('New Client'), ['controller' => 'clients', 'action' => 'add']); ?> </li>
		<li><?php echo $this->Html->link(__('List Invoices'), ['controller' => 'invoices', 'action' => 'index']); ?> </li>
		<li><?php echo $this->Html->link(__('New Invoice'), ['controller' => 'invoices', 'action' => 'add']); ?> </li>
		<li><?php echo $this->Html->link(__('List Sales Orders'), ['controller' => 'sales_orders', 'action' => 'index']); ?> </li>
	echo '</ul>';
?>
</div>
*/