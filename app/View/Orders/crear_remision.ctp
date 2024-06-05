<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
	$('body').on('change','#CashReceiptBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#productsForRemission').addClass('hidden');
		}
		else {
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#productsForRemission').removeClass('hidden');
		}
	});	
	
	$('body').on('change','#CashReceiptCurrencyId',function(){	
		var currencyid=$(this).children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
		calculateTotal();
	});	
	
	$('body').on('click','.addProduct',function(){	
		var tableRow=$('#productsForRemission tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeProduct',function(){	
		var tableRow=$(this).parent().parent().remove();
		calculateTotal();
	});	
  
  var jsRawMaterialsPerProduct=<?php  echo json_encode($rawMaterialsAvailablePerFinishedProduct); ?>;
	
	$('body').on('change','.productid div select',function(){	
		var productid=$(this).val();
		var affectedProductId=$(this).attr('id');
    
		if (productid>0){
      $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div select option').each(function(){
        var rawmaterialid=$(this).val();
        var colonPosition=$(this).text().indexOf(" (A");
        if (colonPosition>-1){
          var newText=$(this).text().substring(0,colonPosition);
          $(this).text(newText);
        }
        if  (jsRawMaterialsPerProduct[productid][rawmaterialid]){
          var newText=$(this).text()+" (A "+jsRawMaterialsPerProduct[productid][rawmaterialid][1]+" B "+jsRawMaterialsPerProduct[productid][rawmaterialid][2]+" C "+jsRawMaterialsPerProduct[productid][rawmaterialid][3]+")";
          $(this).text(newText);
          $(this).removeAttr('disabled');
        }
        else{
          $(this).attr('disabled',true);
        }
      });
      $('#'+affectedProductId).closest('tr').find('td.rawmaterialid div').removeClass('d-none');
      $('#'+affectedProductId).closest('tr').find('td.productionresultcodeid div').removeClass('d-none');
    
			updateproductpackagingunit(productid,affectedProductId);
		}
	});
	
	function updateproductpackagingunit(productid,affectedProductId){
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>products/getproductpackagingunit/'+productid,
			cache: false,
			type: 'GET',
			success: function (packagingunit) {
				$('#'+affectedProductId).closest('tr').find('td.productpackagingunit').text(packagingunit);
				updatepackunits($('#'+affectedProductId).attr('id'),0);
			},
			error: function(e){
				$('#client_data').html(e.responseText);
				console.log(e);
			}
		});
	}
	
	$('body').on('change','.productunitprice div input',function(){	
		var unitprice=$(this).val();
		var productquantity=parseFloat($(this).closest('tr').find('td.productquantity div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
	});	
	
	$('body').on('change','.productquantity div input',function(){	
		var productquantity=$(this).val();
		var unitprice=parseFloat($(this).closest('tr').find('td.productunitprice div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
		updatepackunits(0,$(this).attr('id'));
	});	
	
	function updatepackunits(productid,quantityid){
		var productquantity=0;
		var productpackingunit=0;
		if (productid.length>0){
			productquantity=$('#'+productid).closest('tr').find('td.productquantity div input').val();
			productpackingunit=$('#'+productid).closest('tr').find('td.productpackagingunit').text();
		}
		if (quantityid.length>0){
			productquantity=$('#'+quantityid).val();
			productpackingunit=$('#'+quantityid).closest('tr').find('td.productpackagingunit').text();
		}
		
		if (isNaN(productpackingunit)){
			productpackingunit=0;
		}
		
		var packunits=0;
		var remainder=0;
		var extraunits=0;
		var packingtext="";
		if (productpackingunit>0){
			packunits=Math.floor(productquantity/productpackingunit);
			remainder=productquantity-productpackingunit*packunits;
			extraunits=remainder%productpackingunit;
			if (packunits>0){
				packingtext=packunits+" emp + "+extraunits+" unds";
			}
			else {
				packingtext=""+extraunits+" unds";
			}
		}
		else {
			packingtext=""+productquantity+" unds";
		}
		
		if (productid.length>0){
			$('#'+productid).closest('tr').find('td.packagingunits').text(packingtext);
		}
		if (quantityid.length>0){
			$('#'+quantityid).closest('tr').find('td.packagingunits').text(packingtext);
		}
	}
	
	function calculateTotal(){
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		var totalPrice=0;
    var totalProductQuantity=0;
    
		$("#productsForRemission tbody tr:not(.hidden):not(.totalrow)").each(function() {
      var currentProductQuantity = parseFloat($(this).find('td.productquantity div input').val());
			if (!isNaN(currentProductQuantity)){
				totalProductQuantity += currentProductQuantity;
			}
    
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
    
    $('tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));  
    
    totalPrice=roundToTwo(totalPrice)
    
		$('#totalPrice').val(totalPrice);
		$('tr.totalrow.subtotal td.totalprice div input').val(totalPrice.toFixed(2));
    
		return false;
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
	
  $('body').on('change','#CashReceiptReceiptDateDay',function(){	
		updateExchangeRate();
	});	
  $('body').on('change','#CashReceiptReceiptDateMonth',function(){	
		updateExchangeRate();
	});	
  $('body').on('change','#CashReceiptReceiptDateYear',function(){	
		updateExchangeRate();
	});	
	function updateExchangeRate(){
		var orderday=$('#CashReceiptReceiptDateDay').children("option").filter(":selected").val();
		var ordermonth=$('#CashReceiptReceiptDateMonth').children("option").filter(":selected").val();
		var orderyear=$('#CashReceiptReceiptDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"receiptday":orderday,"receiptmonth":ordermonth,"receiptyear":orderyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#OrderExchangeRate').val(exchangerate);
			},
			error: function(e){
				$('#productsForSale').html(e.responseText);
				console.log(e);
			}
		});
	}
	
	$(document).ready(function(){
		$('#OrderOrderDateHour').val('02');
		$('#OrderOrderDateMin').val('00');
		$('#OrderOrderDateMeridian').val('pm');
		
		if ($('#CashReceiptBoolAnnulled').is(':checked')){
			$('#CashReceiptCurrencyId').parent().addClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().addClass('hidden');
			$('#productsForRemission').addClass('hidden');
			$('.addProduct').addClass('hidden');
		}
		else {
			$('#CashReceiptCurrencyId').parent().removeClass('hidden');
			$('#CashReceiptCashboxAccountingCodeId').parent().removeClass('hidden');
			$('#productsForRemission').removeClass('hidden');
			$('.addProduct').removeClass('hidden');
		}
		
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		if (currencyid==1){
			$('span.currency').text('C$ ');
			$('span.currencyrighttop').text('C$ ');
		}
		else if (currencyid==2){
			$('span.currency').text('US$ ');
			$('span.currencyrighttop').text('US$ ');
		}
     $('#saving').addClass('hidden');
	});
  
  $('body').on('click','#submit',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#OrderCrearRemisionForm',function(e){	
    if($("#submit").data('clicked'))
    {
      $('#submit').attr('disabled', 'disabled');
      $("#mainform").fadeOut();
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

<div class="orders form sales fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la remisión...</p>";
    echo "</div>";
  echo "</div>";

	$orderDateTime=new DateTime($orderDate);
	
	echo $this->Form->create('Order'); 
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Crear nueva Remisión (Recibo de Caja)')."</legend>";
		echo "<div class='container-fluid'>";	
      echo "<div class='row'>";
        echo "<div class='col-sm-8'>";		
          echo "<div class='row'>";
            echo "<div class='col-sm-8'>";		
              echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
              echo $this->Form->Submit(__('Actualizar Bodega'),array('id'=>'refresh','name'=>'refresh'));
              //echo  $this->Form->input('inventory_display_option_id',array('label'=>__('Mostrar Inventario'),'default'=>$inventoryDisplayOptionId));
              //echo $this->Form->Submit(__('Mostrar/Esconder Inventario'),array('id'=>'showinventory','name'=>'showinventory'));

              echo $this->Form->input('order_date',array('label'=>__('Sale Date'),'dateFormat'=>'DMY'));
              echo $this->Form->input('order_code',array('default'=>$newCashReceiptCode,'class'=>'narrow','readonly'=>'readonly'));
              echo $this->Form->input('exchange_rate',array('default'=>$exchangeRateOrder,'class'=>'narrow','readonly'=>'readonly'));
              echo $this->Form->input('CashReceipt.bool_annulled',array('type'=>'checkbox','label'=>'Anulada'));
              echo $this->Form->input('third_party_id',array('label'=>__('Client'),'default'=>'0','empty'=>array('0'=>'Seleccione Cliente')));
              echo $this->Form->input('comment',array('type'=>'textarea','rows'=>3));
              echo $this->Form->input('CashReceipt.currency_id',array('default'=>CURRENCY_CS,'empty'=>array('0'=>'Seleccione Moneda'),'class'=>'narrow'));
              echo $this->Form->input('CashReceipt.cash_receipt_type_id',array('default'=>CASH_RECEIPT_TYPE_REMISSION,'class'=>'narrow','label'=>'Tipo de Recibo','div'=>array('hidden'=>'hidden')));
              echo $this->Form->input('CashReceipt.cashbox_accounting_code_id',array('empty'=>array('0'=>'Seleccione Caja'),'class'=>'narrow','options'=>$accountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN));
              echo $this->Form->input('CashReceipt.concept',array('default'=>' '));
              echo $this->Form->input('CashReceipt.observation',array('type'=>'textarea', 'rows' => 3, 'cols' => 25,'style'=>'width:60%'));
            echo "</div>";
            echo "<div class='col-sm-4'>";
              echo "<h4>".__('Remission Price')."</h4>";
              echo $this->Form->input('CashReceipt.amount',array('label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal'));
              //echo "<h4>".__('Actions')."</h4>";
              //echo "<ul>";
              //if ($bool_client_add_permission) {
              //	echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
              //}
              //echo "</ul>";
            echo "</div>";
          echo "</div>";  
        echo "</div>";  
        echo "<div class='col-md-4'>";
        //if (!empty($inventoryDisplayOptionId)){	
          echo $this->InventoryCountDisplay->showInventoryTotals($finishedMaterialsInventory, CATEGORY_PRODUCED,$plantId,['header_title'=>'Productos Fabricados','production_result_codes'=>[PRODUCTION_RESULT_CODE_B,PRODUCTION_RESULT_CODE_C]]);
        //}
        echo "</div>";  
      echo "</div>";  
      echo "<div class='row'>";
        echo "<div class='col-sm-12'>";		
          echo "<table id='productsForRemission'>";
            echo "<thead>";
              echo "<tr>";
                echo "<th>".__('Product')."</th>";
                echo "<th>".__('Raw Material')."</th>";
                echo "<th style='width:80px;'>".__('Quality')."</th>";
                echo "<th class='centered narrow'>".__('Quantity Product')."</th>";
                echo "<th class='currencyinput'>".__('Unit Price')."</th>";
                echo "<th class='currencyinput'>".__('Subtotal')."</th>";
                echo "<th class='hidden'>Unidad Empaque</th>";
                echo "<th class='centered narrow'>".__('Empaques')."</th>";
                echo "<th></th>";
              echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            for ($i=0;$i<count($requestProducts);$i++) { 
              if ($i<=$productCount){
                echo "<tr>";
              } 
              else {
                echo "<tr class='hidden'>";
              } 
              echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
              echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
              echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>PRODUCTION_RESULT_CODE_B,'readonly'=>'readonly'))."</td>";
              echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
              echo "<td class='productunitprice'>".$this->Form->input('Product.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'default'=>'0','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
              echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
              echo "<td  class='productpackagingunit hidden'>0</td>";
              echo "<td  class='packagingunits'></td>";
              
              echo "<td><button class='removeMaterial'>".__('Remove Sale Item')."</button></td>";
              echo "</tr>";
            }
            for ($i=count($requestProducts);$i<25;$i++) { 
              if ($i==count($requestProducts)){
                echo "<tr>";
              } 
              else {
                echo "<tr class='hidden'>";
              } 
              echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Product'))))."</td>";
              echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
              echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>PRODUCTION_RESULT_CODE_B,'readonly'=>'readonly'))."</td>";
              echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'default'=>'0'))."</td>";
              echo "<td class='productunitprice'>".$this->Form->input('Product.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'default'=>'0','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
              echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
              echo "<td  class='productpackagingunit hidden'>0</td>";
              echo "<td  class='packagingunits'></td>";
              
              echo "<td><button class='removeProduct'>".__('Remove Sale Item')."</button></td>";
              echo "</tr>";
            }
              echo "<tr class='totalrow subtotal'>";
                  echo "<td>Total</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                  echo "<td class='productquantity amount right'><span></span></td>";
                  echo "<td></td>";
                  echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
                  echo "<td></td>";
                  echo "<td></td>";
                echo "</tr>";		
                
            echo "</tbody>";
          echo "</table>";
          echo "<button class='addProduct' type='button'>".__('Add Product')."</button>	";
        echo "</div>";
      echo "</div>";
			echo "<div class='row'>";
				echo $this->Form->Submit(__('Submit'),array('id'=>'submit','name'=>'submit'));
        echo $this->Form->end();
			echo "</div>";
		echo "</div>";
	echo "</fieldset>";
	echo "</div>";
?>
</div>
 
