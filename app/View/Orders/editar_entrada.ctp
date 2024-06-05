<script>
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
	});

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
        currentRow.find('td.productunit div input').val(0);
        currentRow.find('td.productunit div input').addClass('d-none');
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
      currentRow.find('td.productunit input').val(purchaseOrderProducts[i]['Product']['unit_id']);
      currentRow.find('td.productprice div input').val(roundToTwo(productTotalCost));
      
      currentRow.removeClass('d-none');
    }
    for (i=purchaseOrderProducts.length; i < <?php echo ENTRY_ARTICLES_MAX; ?>; i++){
      currentRow=$("#productsForPurchase tbody").find('tr[row="'+i+'"]');
      currentRow.find('td.productid div select option:not(:selected)').attr('disabled', false);
      currentRow.find('td.productid div select').val(0);
      currentRow.find('td.productquantity div input').val(0);
      currentRow.find('td.productunit span.unit').empty();
      currentRow.find('td.productunit input').val(0);
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
    
    var total=roundToTwo(subtotal+iva)
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
    $('#productsForPurchase tbody tr.totalrow td.totalquantity div input').val(totalQuantity);
    $('#productsForPurchase tbody tr.totalrow td.subtotal div input').val(subtotal);
    
    return false;
	}
	
	$(document).ready(function(){
    calculateTotals();
  
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>
<div class="purchases fullwidth">
<?php 
	echo $this->Form->create('Order'); 
	echo "<fieldset>";
    echo "<legend>".__('Edit Purchase')." ".$this->request->data['Order']['order_code']."</legend>";
    echo '<div class="container-fluid">';
      echo '<div class="row">';
        echo '<div class="col-sm-8">';  
        
        echo $this->Form->input('id');
        echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId,['class'=>'fixed']);
        //echo $this->Form->Submit(__('Actualizar Bodega'),['id'=>'refresh','name'=>'refresh']);
        echo $this->Form->input('order_date',['label'=>__('Purchase Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')]);
        echo $this->Form->input('order_code',['label'=>'#Factura Entrada']);
        // NUNCA SE DEBERÍA PODER CAMBIAR ORDEN DE  COMPRA PORQUE REQUIERE ELIMINAR TODOS PRODUCTOS 
        echo $this->Form->input('purchase_order_id',[
          'empty'=>[0=>'-- Seleccione Orden de Compra --'],
          //'class'=>($deletabilityData['boolDeletable']?'':'fixed'),
          'class'=>'fixed',
        ]);
        echo $this->Form->input('bool_purchase_order_delivery_complete',['label'=>'Entregado','options'=>$purchaseOrderDeliveryOptions]);
        echo $this->Form->input('third_party_id',['label'=>__('Provider'),'empty'=>[0=>'-  Eliga Proveedor-'],'class'=>'fixed']);
        echo $this->Form->input('bool_entry_iva',['type'=>'checkbox','label'=>'Se aplica IVA']);
      echo '</div>';
      echo '<div class="col-sm-4">';
        echo $this->Form->input('subtotal_based_on_products',['label'=>'Subtotal Productos','readonly'=>'readonly','default'=>0,'type'=>'number']);
        echo $this->Form->input('total_price',['label'=>__('Costo Subtotal'),'readonly'=>'readonly']);
        echo $this->Form->input('entry_cost_iva',['label'=>__('Costo IVA'),'readonly'=>'readonly']);
        echo $this->Form->input('entry_cost_total',['label'=>__('Costo Total'),'readonly'=>'readonly']);
        echo "<h3>".__('Actions')."</h3>";
        echo '<ul style="list-style-type:none;">';
          if ($bool_delete_permission){
            //echo "<!--li>".$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('Order.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('Order.id')))."</li-->";
          }
          echo "<li>".$this->Html->link(__('List Purchases'), ['action' => 'resumenEntradas'])."</li>";
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
      //pr ($requestInvoices);
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
            'checked'=>array_key_exists($i,$requestInvoices)?$requestInvoices[$i]['PurchaseOrderInvoice']['bool_iva']:'',
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
      
    
  
    echo "<div>";
      $productTableHead='';
      $productTableHead.='<thead>';
        $productTableHead.='<tr>';
          $productTableHead.='<th>'.__('Product').'</th>';
          $productTableHead.='<th>'.__('Quantity').'</th>';
          $productTableHead.='<th style="width:30px;">Und</th>';
          $productTableHead.='<th>'.__('Cost').'</th>';
          //$productTableHead.='<th></th>';
        $productTableHead.='</tr>';
      $productTableHead.='</thead>';
      
			$productTableBodyRows='';
      if (!empty($requestProducts)){
        //pr($requestProducts);
        for ($i=0;$i<count($requestProducts);$i++) { 
          $productTableBodyRow='';
          $productTableBodyRow.='<tr row="'.$i.'">';
            $productTableBodyRow.='<td class="productid">'.$this->Form->input('Product.'.$i.'.product_id',[
              'label'=>false,
              'default'=>$requestProducts[$i]['Product']['product_id'],
              'empty' =>[0=>'-- Elige Producto --'],
              'class'=>'fixed',
            ]).'</td>';
            $productTableBodyRow.='<td class="productquantity width100">'.$this->Form->input('Product.'.$i.'.product_quantity',[
                'type'=>'decimal',
                'label'=>false,
                'default'=>$requestProducts[$i]['Product']['product_quantity'],
                'style'=>'text-align:right',
                'readonly'=>true,
              ]).'</td>';
            $productTableBodyRow.='<td class="productunit">';
              //echo "unit id is ".$requestProducts[$i]['Product']['unit_id']."<br/>";
              $productTableBodyRow.='<span class="unit">'.(empty($requestProducts[$i]['Product']['unit_id'])?'Und':$units[$requestProducts[$i]['Product']['unit_id']]).'</span>';
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
              'readonly'=>true,
            ]).'</span></td>';
            //$productTableBodyRow.='<td><button class="removeMaterial">'.__('Remove Purchase Item').'</button></td>';
          $productTableBodyRow.='</tr>';
          $productTableBodyRows.=$productTableBodyRow;
        }
      }
      
      for ($i=count($requestProducts);$i<ENTRY_ARTICLES_MAX;$i++) { 
        $productTableBodyRow='';
        $productTableBodyRow.='<tr row="'.$i.'" class="d-none">';
          $productTableBodyRow.='<td class="productid">'.$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>0,'empty'=>[0=>'-- Elige Producto --']]).'</td>';
          $productTableBodyRow.='<td class="productquantity width100">'.$this->Form->input('Product.'.$i.'.product_quantity',[
            'type'=>'decimal',
            'label'=>false,
            'default'=>0,
            'readonly'=>true,
            'style'=>'text-align:right',
          ]).'</td>';
          $productTableBodyRow.='<td class="productunit">';
          $productTableBodyRow.='<span class="unit"> </span>';
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
      if (!$deletabilityData['boolDeletable']){
        //$warning=$deletabilityData['message'];
        $warning='';
        if (!empty($deletabilityData['productionRuns'])){
          $warning.="  Los productos ya se ocuparon en procesos de producción ";
          $productionRunCounter=0;
          foreach ($deletabilityData['productionRuns'] as $productionRunId=>$productionRunData){
            $productionRunCounter++;
            $productionRunDateTime=new DateTime($productionRunData['production_run_date']);
            $warning.=$this->Html->Link(($productionRunData['production_run_code'].' ('.$productionRunDateTime->format('d-m-Y').')'),['controller'=>'productionRuns','action'=>'detalle',$productionRunId]);
            $warning.=($productionRunCounter < count($deletabilityData['productionRuns'])?', ':'.  ');
          }
        }
        if (!empty($deletabilityData['orders'])){
          $warning.="  Los productos ya se ocuparon en ventas ";
          $orderCounter=0;
          foreach ($deletabilityData['orders'] as $orderId=>$orderData){
            $orderCounter++;
            $orderDateTime=new DateTime($orderData['order_date']);
            $warning.=$this->Html->Link(($orderData['order_code'].' ('.$orderDateTime->format('d-m-Y').')'),['controller'=>'orders','action'=>'verVenta',$orderId]);
            $warning.=($orderCounter < count($deletabilityData['orders'])?', ':'.  ');
          }
        }
        if (!empty($deletabilityData['transfers'])){
          $warning.="  Los productos ya se ocuparon en transferencias ";
          $transferCounter=0;
          foreach ($deletabilityData['transfers'] as $transferData){
            $transferCounter++;
            $transferDateTime=new DateTime($transferData['transfer_date']);
            $warning.=$transferData['transfer_code'].' ('.$transferDateTime->format('d-m-Y').')';
            $warning.=($transferCounter < count($deletabilityData['transfers'])?', ':'.  ');
          }
        }
        echo '<h3>'.$warning.'</h3>';
      }
      echo '<p class="info">El precio de los productos de la orden de compra se convierte automáticamente a cordobas utilizando la tasa de cambio registrado para la fecha de la orden de compra</p>';
      echo '<table id="productsForPurchase">'.$productTableHead.$productTableBody.'</table>';  
				
    echo "</div>";
    echo "</fieldset>";
    echo $this->Form->Submit(__('Guardar Entrada'),['id'=>'submit','name'=>'submit']); 
    echo $this->Form->end(); 
  echo '</div>';  
?>
</div>
