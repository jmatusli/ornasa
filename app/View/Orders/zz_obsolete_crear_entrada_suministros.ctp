<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
  var jsUnits=<?php echo json_encode($units); ?>;
  
  $('body').on('change','#OrderWarehouseId',function(){
    $('#refresh').trigger('click');
  });
  
  $('body').on('change','#OrderOrderDateDay',function(){
    updateInvoiceDates()
  });
  $('body').on('change','#OrderOrderDateMonth',function(){
    updateInvoiceDates()
  });
  $('body').on('change','#OrderOrderDateYear',function(){
    updateInvoiceDates()
  });

  function updateInvoiceDates(){
    var dateDay=$('#OrderOrderDateDay').val();
    var dateMonth=$('#OrderOrderDateMonth').val();
    var dateYear=$('#OrderOrderDateYear').val();
  
    $('#invoicesForEntry td.invoicedate div select[id$="Day"]').val(dateDay);
    $('#invoicesForEntry td.invoicedate div select[id$="Month"]').val(dateMonth);
    $('#invoicesForEntry td.invoicedate div select[id$="Year"]').val(dateYear);
    
  }
  
  $('body').on('change','#OrderBoolEntryIva',function(){	
    var boolIva=$(this).is(':checked');
			
    $("#invoicesForEntry tbody tr:not(.totalrow)").each(function() {
      if (boolIva){
        $(this).find('td.booliva div input').prop('checked',true);
      }
      else {
        $(this).find('td.booliva div input').prop('checked',false);
      }
      if (!$(this).hasClass('d-none')){
        calculateInvoiceRow($(this).attr('row'),0);
      }
      
    });
    calculateInvoiceTotal();
	})

  $('body').on('change','#OrderPurchaseOrderId',function(){
    var purchaseOrderId=$(this).val();
    if (purchaseOrderId == 0){
      $('#OrderThirdPartyId').val(0);
      
      $('#PurchaseOrderTotalPrice').val(0);
      $("#invoicesForEntry tbody").find('tr[row="0"]').removeClass('d-none');
      
      var i=0;
      for (i=1; i < <?php echo ENTRY_ARTICLES_MAX; ?>; i++){
        $("#productsForPurchase tbody").find('tr[row="'+i+'"]').addClass('d-none');
      }
      for (i=0; i < <?php echo ENTRY_ARTICLES_MAX; ?>; i++){
        currentRow=$("#productsForPurchase tbody").find('tr[row="0"]');
        currentRow.find('td.productid div select option:not(:selected)').attr('disabled', false);
        currentRow.find('td.productid div select').val(0);
        currentRow.find('td.productquantity div input').val(0);
        currentRow.find('td.productprice div input').val(0);
      }
    }
    else {
      $.ajax({
				url: '<?php echo $this->Html->url('/'); ?>purchaseOrders/getPurchaseOrderInfo/',
				data:{"purchaseOrderId":purchaseOrderId,"boolIncludeProducts":1},
        dataType:'json',
				cache: false,
				type: 'POST',
				success: function (purchaseOrder) {
					var purchaseOrderProviderId=purchaseOrder.PurchaseOrder.provider_id;
          $('#OrderThirdPartyId option:not(:selected)').attr('disabled', false);
          $('#OrderThirdPartyId').val(purchaseOrderProviderId);  
          $('#OrderThirdPartyId option:not(:selected)').attr('disabled', true);
          $('#PurchaseOrderTotalPrice').val(purchaseOrder.PurchaseOrder.cost_subtotal);
          
          $('#OrderBoolEntryIva').prop('checked',purchaseOrder.PurchaseOrder.bool_iva);
          $("#invoicesForEntry tbody tr:not(.totalrow)").each(function() {
            $(this).find('td.booliva div input').prop('checked',purchaseOrder.PurchaseOrder.bool_iva);
          });
          
          var appliedExchangeRate = 1
          if (purchaseOrder.PurchaseOrder.currency_id == <?php echo CURRENCY_USD; ?>){
            appliedExchangeRate=purchaseOrder.PurchaseOrder.exchange_rate
          }
          loadPurchaseOrderProducts(purchaseOrder.PurchaseOrderProduct,appliedExchangeRate);
        },
				error: function(e){
					alert(e.responseText);
					console.log(e);
				}
			});  
    }
	});
  
  function loadPurchaseOrderProducts(purchaseOrderProducts, appliedExchangeRate){
    var currentRow=null;
    var i=0;
    
    var productTotalCost=0;
    for (i=0; i < purchaseOrderProducts.length; i++){
      productTotalCost=purchaseOrderProducts[i].product_total_cost * appliedExchangeRate;
         
    
      currentRow=$("#productsForPurchase tbody").find('tr[row="'+i+'"]');
      currentRow.find('td.productid div select option:not(:selected)').attr('disabled', false);
      currentRow.find('td.productid div select').val(purchaseOrderProducts[i].product_id);
      currentRow.find('td.productid div select option:not(:selected)').attr('disabled', true);
      currentRow.find('td.productquantity div input').val(purchaseOrderProducts[i]['product_quantity']);
      currentRow.find('td.productunit span.unit').text(jsUnits[purchaseOrderProducts[i]['Product']['unit_id']]);
      currentRow.find('td.productunit div input').val(purchaseOrderProducts[i]['Product']['unit_id']);
      currentRow.find('td.productprice div input').val(roundToTwo(productTotalCost));
      
      currentRow.removeClass('d-none');
    }
    for (i=purchaseOrderProducts.length; i < <?php echo ENTRY_ARTICLES_MAX; ?>; i++){
      currentRow=$("#productsForPurchase tbody").find('tr[row="'+i+'"]');
      currentRow.find('td.productid div select option:not(:selected)').attr('disabled', false);
      currentRow.find('td.productid div select').val(0);
      currentRow.find('td.productquantity div input').val(0);
      currentRow.find('td.productunit span.unit').empty();
      currentRow.find('td.productunit div input').val(0);
      currentRow.find('td.productprice div input').val(0);
      
      currentRow.addClass('d-none');
    }
    calculateProductTotal();
  }
  
   $('body').on('change','#invoicesForEntry td.invoicecode div input',function(){
		updateOrderCode();
	});
  
  function updateOrderCode(){
    var entryCode="";
    $("#invoicesForEntry tbody tr:not(.d-none):not(.totalrow)").each(function() {
      if ($(this).find('td.invoicecode div input').val().trim() != ''){
        entryCode+=$(this).find('td.invoicecode div input').val().trim().toUpperCase();
        entryCode+='_';
      }
    });
    entryCode=entryCode.slice(0, -1)  
    $('#OrderOrderCode').val(entryCode);
    
    return false;
  }
	
  $('body').on('change','#invoicesForEntry td.booliva div input',function(){
    calculateInvoiceRow($(this).closest('tr').attr('row'),1);
	});
  $('body').on('change','#invoicesForEntry td.invoicesubtotal div input',function(){
    calculateInvoiceRow($(this).closest('tr').attr('row'),1);
	});
  
  function calculateInvoiceRow(rowId,boolCalculateInvoiceTotal){
    var currentRow=$('#invoicesForEntry').find('tr[row="'+rowId+'"]');
    var subtotal=parseFloat(currentRow.find('td.invoicesubtotal div input').val());
    if (isNaN(subtotal)){
      currentRow.find('td.invoicesubtotal div input').val(0);
      alert("El subtotal tiene que ser un número, "+subtotal+" no lo es");
    }
        
    var iva=0
    if (currentRow.find('td.booliva div input').is(':checked')){
      iva=roundToTwo(0.15*subtotal);
    }
    currentRow.find('td.invoiceiva div input').val(iva)
    
    var total=subtotal+iva
    currentRow.find('td.invoicetotal div input').val(total)
    if (boolCalculateInvoiceTotal == 1){
      calculateInvoiceTotal();
    }
  }
  $('body').on('click','.removeInvoice',function(){
		var tableRow=$(this).closest('tr').remove();
		var orderCodeUpdated=updateOrderCode();
    if (!orderCodeUpdated){
      calculateInvoiceTotal();
    }
	});		
  $('body').on('click','.addInvoice',function(){
		var tableRow=$('#invoicesForEntry tbody tr.d-none:first');
		tableRow.removeClass("d-none");
    
    return false;
	});
  $('body').on('change','.productquantity',function(){
		calculateProductTotal();
	});	
	$('body').on('change','.productprice',function(){
		calculateProductTotal();
	});

	/*
  $('body').on('click','.removeMaterial',function(){
		var tableRow=$(this).parent().parent().remove();
		calculateProductTotal();
	});		
  $('body').on('click','#addMaterial',function(){
		var tableRow=$('#productsForPurchase tbody tr.d-none:first');
		tableRow.removeClass("d-none");
	});
  */
  
	function calculateTotals(){
    calculateInvoiceTotal();
    calculateProductTotal()
	}
  function calculateInvoiceTotal(){
    var subtotal=0;
    var iva=0;
    var total=0;
    
    $("#invoicesForEntry tbody tr:not(.d-none):not(.totalrow)").each(function() {
      var currentSubtotal =0;
      var currentIva=0;
      var currentTotal =0;
      
			currentSubtotal = parseFloat($(this).find('td.invoicesubtotal div input').val());
      if (isNaN(currentSubtotal)){
        $(this).find('td.invoicesubtotal div input').val(0);
        alert("El subtotal tiene que ser un número, "+currentSubtotal+" no lo es");
      }
      else {
        subtotal += currentSubtotal;
      }
      
      currentIva=parseFloat($(this).find('td.invoiceiva div input').val());
      if (isNaN(currentIva)){
        $(this).find('td.invoiceiva div input').val(0)
        alert("El iva tiene que ser un número, "+currentIva+" no lo es");
      }
      iva+=currentIva
      
      currentTotal=parseFloat($(this).find('td.invoicetotal div input').val());
      if (isNaN(currentTotal)){
        $(this).find('td.invoicetotal div input').val(0)
        alert("El subtotal tiene que ser un número, "+currentTotal+" no lo es");
      }
      total+=currentTotal
    });  
    
    $('#PurchaseOrderInvoicesSubtotalInvoices').val(subtotal);
    $('#PurchaseOrderInvoicesIvaInvoices').val(roundToTwo(iva));
    $('#PurchaseOrderInvoicesTotalInvoices').val(total);
    
    $('#OrderTotalPrice').val(subtotal);
    $('#OrderEntryCostIva').val(roundToTwo(iva));
    $('#OrderEntryCostTotal').val(total);
    
    return false;
  }
	function calculateProductTotal(){
		var totalQuantity=0;
    var subtotal=0;
    
    $("#productsForPurchase tbody tr:not(.d-none):not(.totalrow)").each(function() {
			var currentProductQuantity = parseInt($(this).find('td.productquantity div input').val());
      if (isNaN(currentProductQuantity)){
        $(this).find('td.productquantity div input').val(0);
        alert("La cantidad tiene que ser un número, "+currentProductQuantity+" no lo es");
      }
      else {
        totalQuantity += currentProductQuantity;
      }
      
      var currentProductPrice = parseFloat($(this).find('td.productprice div input').val());
			if (isNaN(currentProductPrice)){
        $(this).find('td.productprice div input').val(0);
        alert("El precio tiene que ser un número, "+currentProductPrice+" no lo es");
      }
      else {
        subtotal += currentProductPrice;
      }
		});
		subtotal=roundToTwo(subtotal)
    $('#OrderSubtotalBasedOnProducts').val(subtotal);
    $('#productsForPurchase tr.totalrow td.totalquantity div input').val(totalQuantity);
    $('#productsForPurchase tr.totalrow td.subtotal div input').val(subtotal);
    
    return false;
	}
	
	
	$(document).ready(function(){
		$('#OrderOrderDateHour').val('06');
		$('#OrderOrderDateMin').val('00');
		$('#OrderOrderDateMeridian').val('am');	

    $('#saving').addClass('hidden');    
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
	});
  
  $('body').on('click','#submit',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#OrderCrearEntradaSuministrosForm',function(e){	
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
<div class="form purchases fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la entrada de suministros...</p>";
    echo "</div>";
  echo "</div>";
  echo "<div id='mainform'>";
    echo $this->Form->create('Order'); 
    echo "<fieldset>";
      echo "<legend>Crear Nueva Entrada de Suministros</legend>";
      echo '<div class="container-fluid">';
        echo '<div class="row">';
          echo '<div class="col-sm-8">';
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
            
            echo $this->Form->input('order_date',['label'=>__('Purchase Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
            echo $this->Form->input('order_code',['label'=>'#Factura Entrada','required'=>false,'readonly'=>true]);
            echo $this->Form->input('purchase_order_id',[
              'default'=>0,
              'empty'=>[0=>'-- Seleccione Orden de Compra --'],
              'style'=>'background-color:'.(count($purchaseOrders)>0?'yellow':'none'),  
            ]);
            echo $this->Form->input('PurchaseOrder.total_price',[
              'default'=>0,
              'type'=>'hidden',
            ]);
            echo $this->Form->input('bool_purchase_order_delivery_complete',['label'=>'Entregado','default'=>1,'options'=>$purchaseOrderDeliveryOptions,'class'=>'fixed']);
            echo $this->Form->input('third_party_id',['label'=>__('Provider'),'default'=>0,'empty'=>[0=>'-  Eliga Proveedor-'],'class'=>'fixed']);
            echo $this->Form->input('bool_entry_iva',['type'=>'checkbox','label'=>'Se aplica IVA','checked'=>'checked']);
          echo '</div>';
          echo '<div class="col-sm-4" style="padding-left:50px;">';            
            echo $this->Form->input('subtotal_based_on_products',['label'=>'Subtotal Productos','readonly'=>'readonly','default'=>0]);
          
            echo $this->Form->input('total_price',['label'=>__('Costo Subtotal'),'readonly'=>'readonly','default'=>0]);
            echo $this->Form->input('entry_cost_iva',[
              'label'=>__('Costo IVA'),
              'readonly'=>'readonly',
              'default'=>0,
            ]);
            echo $this->Form->input('entry_cost_total',['label'=>__('Costo Total'),'readonly'=>'readonly','default'=>0]);
            echo "<h3>".__('Actions')."</h3>";
            echo '<ul class="dl50">';
              echo "<li>".$this->Html->link(__('Resumen Entradas Suministros'), ['action' => 'resumenEntradasSuministros'])."</li>";
              echo "<br/>";
              if ($bool_provider_index_permission) {
                echo "<li>".$this->Html->link(__('List Providers'), ['controller' => 'third_parties', 'action' => 'resumenProveedores'])."</li>";
              }
              if ($bool_provider_add_permission) {
                echo "<li>".$this->Html->link(__('New Provider'), ['controller' => 'third_parties', 'action' => 'crearProveedor'])."</li>";
              }
            echo "</ul>";
          echo '</div>';
        echo '</div>';
      echo '</div>';
      
      $purchaseOrderInvoiceTableHead='';
      $purchaseOrderInvoiceTableHead.='<thead>';
        $purchaseOrderInvoiceTableHead.='<tr>';
          $purchaseOrderInvoiceTableHead.='<th># Factura</th>';
          $purchaseOrderInvoiceTableHead.='<th style="min-width:280px;">Fecha Factura</th>';
          $purchaseOrderInvoiceTableHead.='<th style="min-width:40px;">Iva?</th>';
          $purchaseOrderInvoiceTableHead.='<th>Subtotal C$</th>';
          $purchaseOrderInvoiceTableHead.='<th>IVA C$</th>';
          $purchaseOrderInvoiceTableHead.='<th>Total C$</th>';
          $purchaseOrderInvoiceTableHead.='<th></th>';
        $purchaseOrderInvoiceTableHead.='</tr>';
      $purchaseOrderInvoiceTableHead.='</thead>';
      
      $purchaseOrderInvoiceTableRows='';
      $purchaseOrderInvoiceTableBody='<tbody>';
      for ($i = 0;$i < ENTRY_INVOICES_MAX;$i++){
        $purchaseOrderInvoiceTableRow='';
        $purchaseOrderInvoiceTableRow.='<tr row="'.$i.'" class="'.($i <= count($requestInvoices)?'':'d-none').'">';
          $purchaseOrderInvoiceTableRow.='<td class="invoicecode">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.invoice_code',[
            'label'=>false,
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['invoice_code']:'',
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td  class="invoicedate">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.invoice_date',[
            'label'=>false,
            'type'=>'date',
            'dateFormat'=>'DMY',
            'minYear'=>2014,
            'maxYear'=>date('Y'),
            'selected'=>date('Y-m-d'),
            'style'=>'font-size:0.9em',
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['invoice_date']:'',
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td  class="booliva">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.bool_iva',[
            'label'=>false,
            'type'=>'checkbox',
            'style'=>'width:90%',
            'checked'=>true,
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['bool_iva']:'',
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td class="invoicesubtotal">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.invoice_subtotal',[
            'label'=>false,
            'type'=>'decimal',
            'div'=>['style'=>'width:100%'],
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['invoice_subtotal']:0,
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td class="invoiceiva">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.invoice_iva',[
            'label'=>false,
            'type'=>'decimal',
            'div'=>['style'=>'width:100%'],
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['invoice_iva']:0,
            'readonly'=>true,
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td class="invoicetotal">'.$this->Form->Input('PurchaseOrderInvoice.'.$i.'.invoice_total',[
            'label'=>false,
            'type'=>'decimal',
            'div'=>['style'=>'width:100%'],
            'default'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['invoice_total']:0,
            'readonly'=>true,
          ]).'</td>';
          $purchaseOrderInvoiceTableRow.='<td>';
            $purchaseOrderInvoiceTableRow.='<button class="removeInvoice">Remover Factura</button>';
          $purchaseOrderInvoiceTableRow.='</td>';
        $purchaseOrderInvoiceTableRow.='</tr>';
        
        $purchaseOrderInvoiceTableRows.=$purchaseOrderInvoiceTableRow;
      }
      
      $purchaseOrderInvoiceTableTotalRow='';            
        $purchaseOrderInvoiceTableTotalRow.='<tr class="totalrow">';
        $purchaseOrderInvoiceTableTotalRow.='<td>Totales</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td></td>';
        $purchaseOrderInvoiceTableTotalRow.='<td></td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$this->Form->Input('PurchaseOrderInvoices.subtotal_invoices',[
          'label'=>false,
          'type'=>'decimal',
          'default'=>0,
          'div'=>['style'=>'width:100%'],
          'readonly'=>true,
        ]).'</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$this->Form->Input('PurchaseOrderInvoices.iva_invoices',[
          'label'=>false,
          'type'=>'decimal',
          'default'=>0,
          'div'=>['style'=>'width:100%'],
          'readonly'=>true,
        ]).'</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td>'.$this->Form->Input('PurchaseOrderInvoices.total_invoices',[
          'label'=>false,
          'type'=>'decimal',
          'default'=>0,
          'div'=>['style'=>'width:100%'],
          'readonly'=>true,
        ]).'</td>';
        $purchaseOrderInvoiceTableTotalRow.='<td></td>';
      $purchaseOrderInvoiceTableTotalRow.='</tr>';
    
      
      $purchaseOrderInvoiceTableBody='<tbody>'.$purchaseOrderInvoiceTableRows.$purchaseOrderInvoiceTableTotalRow.'</tbody>';
      echo '<h2>Facturas</h2>';
      echo '<table id="invoicesForEntry">'.$purchaseOrderInvoiceTableHead.$purchaseOrderInvoiceTableBody.'</table>';
      echo '<button class="addInvoice">Añadir Factura</button>';
      
    
    //pr($requestProducts);
    echo "<div>";      
      $productTableHead='';
      $productTableHead.='<thead>';
        $productTableHead='<tr>';
          $productTableHead='<th>'.__('Product').'</th>';
          $productTableHead='<th>'.__('Quantity').'</th>';
          $productTableHead.='<th style="width:30px;"></th>';
          $productTableHead='<th>'.__('Cost').'</th>';
          //$productTableHead='<th></th>';
        $productTableHead='</tr>';
      $productTableHead='</thead>';
      $productTableBodyRows='';
      if (!empty($requestProducts)){
        //pr($requestProducts);
        for ($i=0;$i<count($requestProducts);$i++) { 
          $productTableBodyRow='';
          $productTableBodyRow.='<tr row="'.$i.'">';
            $productTableBodyRow.='<td class="productid">'.$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>$requestProducts[$i]['Product']['product_id'],'empty' =>[0=>'-- Elige Producto --']]).'</td>';
            $productTableBodyRow.='<td class="productquantity width100">'.$this->Form->input('Product.'.$i.'.product_quantity',[
                'type'=>'decimal',
                'label'=>false,
                'default'=>$requestProducts[$i]['Product']['product_quantity'],
                'style'=>'text-align:right',
              ]).'</td>';
            $productTableBodyRow.='<td class="productunit">';
              $productTableBodyRow.='<span class="unit">'.$units[$requestProducts[$i]['Product']['unit_id']].'</span';
              $productTableBodyRow.=$this->Form->input('Product.'.$i.'.unit_id',[
                'label'=>false,
                'default'=>$requestProducts[$i]['Product']['unit_id'],
                'type'=>'hidden',
              ]);
            $productTableBodyRow.='</td>';
            
            $productTableBodyRow.='<td  class="productprice CScurrency"><span class="currency">C$</span><span class="amountright">'.$this->Form->input('Product.'.$i.'.product_price',[
              'type'=>'decimal',
              'label'=>false,
              'default'=>$requestProducts[$i]['Product']['product_price'],
            ]).'</span></td>';
            //$productTableBodyRow.='<td><button class="removeMaterial">'.__('Remove Purchase Item').'</button></td>';
          $productTableBodyRow.='</tr>';
          $productTableBodyRows.=$productTableBodyRow;
        }
      }
      
      for ($i=count($requestProducts);$i<ENTRY_ARTICLES_MAX;$i++) { 
        $productTableBodyRow='';
        //if ($i == count($requestProducts)){
        //  $productTableBodyRow.='<tr row="'.$i.'">';
        //} 
        //else {
          $productTableBodyRow.='<tr row="'.$i.'" class="d-none">';
        //} 
          $productTableBodyRow.='<td class="productid">'.$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>0,'empty'=>[0=>'-- Elige Producto --']]).'</td>';
          $productTableBodyRow.='<td class="productquantity width100">'.$this->Form->input('Product.'.$i.'.product_quantity',[
            'type'=>'decimal',
            'label'=>false,
            'default'=>0,
            'readonly'=>true,
            'style'=>'text-align:right',
          ]).'</td>';
          $productTableBodyRow.='<td class="productunit">';
          $productTableBodyRow.='<span class="unit"> </span';
          $productTableBodyRow.=$this->Form->input('Product.'.$i.'.unit_id',[
            'label'=>false,
            'default'=>0,
            'readonly'=>true,
            'type'=>'hidden',
          ]);
          $productTableBodyRow.='</td>';
          $productTableBodyRow.='<td  class="productprice CScurrency"><span class="currency">C$</span><span class="amountright">'.$this->Form->input('Product.'.$i.'.product_price',[
            'type'=>'decimal',
            'label'=>false,
            'default'=>0,
            
          ]).'</span></td>';
          //$productTableBodyRow.='<td><button class="removeMaterial">Eliminar Producto</button></td>';
        $productTableBodyRow.='</tr>';
        
        $productTableBodyRows.=$productTableBodyRow;
      }
      
      $productTableTotalRow='';
      $productTableTotalRow.='<tr class="totalrow">';
        $productTableTotalRow.='<td>Subtotal C$</td>';
        $productTableTotalRow.='<td class="totalquantity width100">'.$this->Form->input('TotalRow.product_quantity',[
          'type'=>'decimal',
          'label'=>false,
          'default'=>0,
          'readonly'=>true,
          'style'=>'text-align:right',
        ]).'</td>';
        $productTableTotalRow.='<td></td>';
        $productTableTotalRow.='<td  class="subtotal CScurrency"><span class="currency">C$</span><span class="amountright">'.$this->Form->input('TotalRow.product_price',[
          'type'=>'decimal',
          'label'=>false,
          'default'=>0,
          'readonly'=>true,
        ]).'</span></td>';
        //$productTableTotalRow.='<td></td>';
      $productTableTotalRow.='</tr>';
      $productTableBody='<tbody>'.$productTableBodyRows.$productTableTotalRow.'</tbody>';
      
      echo '<h2>Productos</h2>';
      echo '<p class="info">El precio de los productos de la orden de compra se convierte automáticamente a cordobas utilizando la tasa de cambio registrado para la fecha de la orden de compra</p>';
      echo '<table id="productsForPurchase">'.$productTableHead.$productTableBody.'</table>';
    echo "</div>";
    //echo '<button id="addMaterial" type="button">'.__('Add Purchase Item').'</button>';	
    echo "</fieldset>";
    echo $this->Form->Submit(__('Submit'),['id'=>'submit','name'=>'submit']);
    echo $this->Form->end();
  echo "</div>"; 
?>
</div>
