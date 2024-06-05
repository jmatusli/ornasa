<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  var jsProductTypes=<?php echo json_encode($productTypeList); ?>;
  var jsPriceClientCategories=<?php echo json_encode($priceClientCategories); ?>;
  var jsClients=<?php echo json_encode($clients); ?>;
  var jsProducts=<?php echo json_encode($products); ?>;
  var jsRawMaterials=<?php echo json_encode($rawMaterials); ?>;
  
  $('body').on('change','#ProductPriceLogClientId',function(e){	 
    $('#changeDate').trigger('click');
  });

  $('body').on('click','.price',function(e){	 
    var productTypeId=parseInt(($(this).attr('producttypeid')))
    
    var priceClientCategoryId=0;
    var clientId=0;
    
    var productId=parseInt($(this).attr('productid'));
    var rawMaterialId=0;
    
    if ($(this).hasClass('category')){
      priceClientCategoryId=parseInt($(this).attr('priceclientcategoryid'));
      $('#ModalPriceClientCategory').closest('div').removeClass('d-none');
      $('#ModalClient').closest('div').addClass('d-none');
    }
    if ($(this).hasClass('client')){
      clientId=parseInt($(this).attr('clientid'));
      $('#ModalPriceClientCategory').closest('div').addClass('d-none');
      $('#ModalClient').closest('div').removeClass('d-none');
    }
    if (productTypeId === <?php echo PRODUCT_TYPE_BOTTLE; ?>){
      rawMaterialId=parseInt($(this).attr('rawmaterialid'));
      $('#ModalRawMaterial').closest('div').removeClass('d-none');
    }
    else {
      $('#ModalRawMaterial').closest('div').addClass('d-none');
    }
    
    $('#ModalProductTypeId').val(productTypeId);
    $('#ModalPriceClientCategoryId').val(priceClientCategoryId);
    $('#ModalClientId').val(clientId);
    $('#ModalProductId').val(productId);
    $('#ModalRawMaterialId').val(rawMaterialId);
    
    $('#ModalProductType').val(jsProductTypes[productTypeId]);
    $('#ModalPriceClientCategory').val(jsPriceClientCategories[priceClientCategoryId]);
    $('#ModalClient').val(jsClients[clientId]);
    $('#ModalProduct').val(jsProducts[productId]);
    $('#ModalRawMaterial').val(jsRawMaterials[rawMaterialId]);
    
    $('#ModalPrice').val(parseFloat($(this).val()));
    
    $('#priceModal').modal('show');
  });
  
  $('body').on('click','.savePrice',function(){
    var priceClientCategoryId=$('#ModalPriceClientCategoryId').val();
    var clientId=$('#ModalClientId').val();
    
    var productId=$('#ModalProductId').val();
    var rawMaterialId=$('#ModalRawMaterialId').val();
    
    var userId=<?php echo $loggedUserId; ?>;
    var price=$('#ModalPrice').val();
    
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>productPriceLogs/saveProductPriceLog/',
      data:{
        "priceClientCategoryId":priceClientCategoryId,
        "clientId":clientId,
        "productId":productId,
        "rawMaterialId":rawMaterialId,
        'userId':userId,
        'price':price
      },
      cache: false,
      type: 'POST',
      success: function (productPriceLogId) {
        $('#priceModal').modal('hide');
        if (!isNaN(parseInt(productPriceLogId))){
          window.location.reload()
        }
        else {
          alert(productPriceLogId);
        }
      },
      error: function(e){
        $('#priceModal').modal('hide');
        alert(e.responseText);
        console.log(e);
      }
    });
  
  });

  $('body').on('change','.price',function(){	
    var previousPrice=parseFloat($(this).closest('td').find('span.previousprice').text());
    var newPrice=parseFloat($(this).val());
    if (isNaN(newPrice)){
      $(this).val(0);
      newPrice=0;
    }
    if (Math.abs(previousPrice-newPrice) > 0.001){
      $(this).css('background-color','#8aeecf');
    }
    else {
      $(this).css('background-color','#ffffff');
    }
	});	
   
	function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	function roundToThree(num) {    
		return +(Math.round(num + "e+3")  + "e-3");
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
  
  function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span.amountright").each(function(){
			$(this).number(true,2);
			$(this).parent().find('span.currency').text("C$");
		});
	}
  
  $('body').on('click','.goToPriceDeletion',function(){
    $('#priceModal').modal('hide');
    
    var priceClientCategoryId=$('#ModalPriceClientCategoryId').val();
    var clientId=$('#ModalClientId').val();
    var productId=$('#ModalProductId').val();
    var rawMaterialId=$('#ModalRawMaterialId').val();
    var userId=<?php echo $loggedUserId; ?>;
    
    $('#PriceDeletionModalPriceClientCategoryId').val(priceClientCategoryId)
    $('#PriceDeletionModalClientId').val(clientId)
    $('#PriceDeletionModalProductId').val(productId)
    $('#PriceDeletionModalRawMaterialId').val(rawMaterialId)
    $('#PriceDeletionModalUserId').val(userId)
    
    if (priceClientCategoryId > 0){
      $('#PriceDeletionModalPriceClientCategory').closest('div').removeClass('d-none');
      $('#PriceDeletionModalPriceClientCategory').val(jsPriceClientCategories[priceClientCategoryId])
    }
    else {
      $('#PriceDeletionModalPriceClientCategory').closest('div').addClass('d-none');
    }
    if (clientId > 0){
      $('#PriceDeletionModalClient').closest('div').removeClass('d-none');
      $('#PriceDeletionModalClient').val(jsClients[clientId])
    }
    else {
      $('#PriceDeletionModalClient').closest('div').addClass('d-none');
    }
    $('#PriceDeletionModalProduct').val(jsProducts[productId])
    $('#PriceDeletionModalRawMaterial').val(jsRawMaterials[rawMaterialId])
    
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>productPriceLogs/getPriceTableForProduct/',
      data:{
        "priceClientCategoryId":priceClientCategoryId,
        "clientId":clientId,
        "productId":productId,
        "rawMaterialId":rawMaterialId,
      },
      cache: false,
      type: 'POST',
      success: function (priceTable) {
        $('#PriceTableContainer').html(priceTable);
      },
      error: function(e){
        alert(e.responseText);
        console.log(e);
      }
    });
    
    $('#priceDeletionModal').modal('show');
  });  
  
  $('body').on('click','.deletePrices',function(){
    var priceClientCategoryId=$('#PriceDeletionModalPriceClientCategoryId').val();
    var clientId=$('#PriceDeletionModalClientId').val();
    var productId=$('#PriceDeletionModalProductId').val();
    var rawMaterialId=$('#PriceDeletionModalRawMaterialId').val();
    var userId=<?php echo $loggedUserId; ?>;
    
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>productPriceLogs/deletePricesForProduct/',
      data:{
        "priceClientCategoryId":priceClientCategoryId,
        "clientId":clientId,
        "productId":productId,
        "rawMaterialId":rawMaterialId,
        "userId":userId,
      },
      dataType:'json',
      cache: false,
      type: 'POST',
      success: function (deletionResult) {
        if (deletionResult['boolSuccess']){
          alert('Los precios se eliminaron.');
          window.location.reload()
        }
        else {
          alert('Había un problema eliminando los precios.  '+deletionResult['errorMessage']);
          $('#priceDeletionModal').modal('hide');
        }
      },
      error: function(e){
        alert(e.responseText);
        console.log(e);
        $('#priceDeletionModal').modal('hide');
      }
    });
  });
	
	$(document).ready(function(){
    $('select.fixed option:not(:selected)').attr('disabled', true);
    <?php if ($boolSaved){ ?>
      alert('Se registraron los precios de venta')
    <?php } ?>
	});
</script>

<div class="productpricelogs form fullwidth">
<?php 
  $priceDateTimeArray=new DateTime($priceDateTime);
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Resumen Precios de Productos')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";							
          echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          //echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
          echo $this->Form->input('product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
          echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
          echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
          //echo $this->Form->input('client_id',['label'=>'Cliente','default'=>$clientId]);
          
          echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
          
          $fileName=($priceDateTimeArray->format('Ymd')).'_Resumen_Precios.xlsx';
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenPrecios',$fileName], ['class' => 'btn btn-primary']); 
          //echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
          //echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        //if ($totalFields < 1000){
        //  if ($userRoleId == ROLE_ADMIN){
        //    echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices2','name'=>'savePrices2','style'=>'width:300px;']);
        //  }
        //}
        //else {
        //  echo '<p class="warning">El número total de campos ('.$totalFields.') está mayor que 1000, por tal razón el servidor no permite guardar los precios directamente desde aquí.  Utiliza las páginas de precio por cliente o precio por producto para grabar precios.</p>';
        //}
        $excelOutput='';
        foreach ($productTypes as $productTypeId=>$productTypeData){
          if (!empty($productTypeData['existences']['Product'])){ 
            echo "<div class='col-sm-12' style='padding:5px;overflow-x:auto;'>";
              echo "<h3>Precios antes de IVA para línea de producto ".$productTypeData['ProductType']['name']." en fecha ".$priceDateTimeArray->format('d-m-Y')."</h3>";
              
              echo '<p class="info">Sólo se muestran los clientes para quienes <span class="tooltipcontainer">se registraron precios para productos de este tipo<span class="tooltiptext grand">Este incluye clientes para los cuales se registró el precio y después se restableció a cero</span></span>.  Para añadir otro cliente, haga clic en Registrar Precios para otro cliente</p>';
              echo $this->Html->link('Registrar Precios para otro Cliente',['action'=>'registrarPreciosCliente'],['class'=>'btn btn-primary','style'=>'float:left;']);
              
              $priceTableHead="<thead>";
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'></th>";
                  foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                    $priceTableHead.='<th colspan="'.count($productData['RawMaterial']).'" style="text-align:center">'.($this->Html->link($productData['name'],['action'=>'registrarPreciosProducto',$productId])).'</th>';
                    
                  }
                $priceTableHead.="</tr>";
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'>Cliente</th>";
                  foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                    foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                      $priceTableHead.='<th style="text-align:center">'.$rawMaterialData['abbreviation'].'</th>';
                    }
                    
                  }
                $priceTableHead.="</tr>";
              }
              else {
                $priceTableHead.="<tr>";
                if (!empty($productTypeData['Client'])){
                  $priceTableHead.="<th style='width:100px;'>Cliente</th>";
                }
                else {
                  $priceTableHead.="<th style='width:100px;'>Categoría</th>";
                }
                foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                  $priceTableHead.='<th style="text-align:center">'.$productData['name'].'</th>';
                  
                }               
                $priceTableHead.="</tr>";
              }  
              $priceTableHead.="</thead>";
              
              $priceTableBodyRows="";
              
              foreach ($productTypeData['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
                $priceTableBodyRows.='<tr style="background-color:'.$priceClientCategoryColors[$priceClientCategoryId].'">';
                  $priceTableBodyRows.='<td  style="min-width:200px;background-color:'.$priceClientCategoryColors[$priceClientCategoryId].';">'.$priceClientCategories[$priceClientCategoryId].'</td>';
                  if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                    foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                      foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                        $defaultValue=0;
                        if (
                          array_key_exists($productId,$priceClientCategoryData['Product']) &&
                          array_key_exists('RawMaterial',$priceClientCategoryData['Product'][$productId]) &&
                          array_key_exists($rawMaterialId,$priceClientCategoryData['Product'][$productId]['RawMaterial'])
                        ){
                          $defaultValue=$priceClientCategoryData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'];
                          $priceDateTime=new DateTime($priceClientCategoryData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']);
                        }
                        
                        $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                          $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                          $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductType.'.$productTypeId.'.PriceClientCategory.'.$priceClientCategoryId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price',[
                            'label'=>false,
                            'type'=>'decimal',
                            'value'=>$defaultValue,
                            'style'=>'width:80px;',
                            'class'=>'price category',
                            'readonly'=>true,
                            'producttypeid'=>$productTypeId,
                            'priceclientcategoryid'=>$priceClientCategoryId,
                            'productid'=>$productId,
                            'rawmaterialid'=>$rawMaterialId, 'div'=>['style'=>'width:100%;text-align:center;'],  
                          ]).'</span>';
                          if($defaultValue > 0){
                            $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                          }
                        $priceTableBodyRows.='</td>';
                      }
                    }
                  }
                  else {
                    foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                      $defaultValue=0;
                      if (array_key_exists($productId,$priceClientCategoryData['Product'])){
                        $defaultValue=$priceClientCategoryData['Product'][$productId]['price'];
                        $priceDateTime=new DateTime($priceClientCategoryData['Product'][$productId]['price_datetime']);
                      }
                      
                      $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                        $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                        $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductType.'.$productTypeId.'.PriceClientCategory.'.$priceClientCategoryId.'.Product.'.$productId.'.price',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>$defaultValue,
                          'style'=>'width:80px;',
                          'class'=>'price category',
                          'readonly'=>true,
                          'producttypeid'=>$productTypeId,
                          'priceclientcategoryid'=>$priceClientCategoryId,
                          'productid'=>$productId,
                          'div'=>['style'=>'width:100%;text-align:center;'],  
                        ]).'</span>';
                        if($defaultValue > 0){
                          $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                        }
                      $priceTableBodyRows.='</td>';
                    }
                    
                  }
                  $priceTableBodyRows.='</tr>';
              }
              
              if (!empty($productTypeData['Client'])){
                foreach ($productTypeData['Client'] as $clientId=>$clientData){
                  $priceTableBodyRows.='<tr>';
                    $priceTableBodyRows.='<td  style="min-width:200px;background-color:'.$priceClientCategoryColors[$clientPriceClientCategories[$clientId]].';">'.($boolRegistrarPreciosCliente?($this->Html->Link($clients[$clientId],['action'=>'registrarPreciosCliente',$clientId])):$clients[$clientId]).'</td>';
                    if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                      foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                        foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                          $defaultValue=0;
                          if (
                            array_key_exists($productId,$clientData['Product']) &&
                            array_key_exists('RawMaterial',$clientData['Product'][$productId]) &&
                            array_key_exists($rawMaterialId,$clientData['Product'][$productId]['RawMaterial'])
                          ){
                            $defaultValue=$clientData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'];
                            $priceDateTime=new DateTime($clientData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']);
                          }  
                          $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                            $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                            $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductType.'.$productTypeId.'.Client.'.$clientId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price',[
                              'label'=>false,
                              'type'=>'decimal',
                              'value'=>$defaultValue,
                              'style'=>'width:80px;',
                              'class'=>'price client',
                              'readonly'=>true,
                              'producttypeid'=>$productTypeId,
                              'clientid'=>$clientId,
                              'productid'=>$productId,
                              'rawmaterialid'=>$rawMaterialId, 
                              'div'=>['style'=>'width:100%;text-align:center;'],  
                            ]).'</span>';
                            if($defaultValue > 0){
                              $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                            }
                          $priceTableBodyRows.='</td>';
                        }
                      }
                    }
                    else {
                      foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                        $defaultValue=0;
                        if (array_key_exists($productId,$clientData['Product'])){
                          $defaultValue=$clientData['Product'][$productId]['price'];
                          $priceDateTime=new DateTime($clientData['Product'][$productId]['price_datetime']);
                        }
                        
                        $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                          $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                          $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductType.'.$productTypeId.'.Client.'.$clientId.'.Product.'.$productId.'.price',[
                            'label'=>false,
                            'type'=>'decimal',
                            'value'=>$defaultValue,
                            'style'=>'width:80px;',
                            'class'=>'price client',
                            'readonly'=>true,
                            'producttypeid'=>$productTypeId,
                            'clientid'=>$clientId,
                            'productid'=>$productId,
                            'div'=>['style'=>'width:100%;text-align:center;'],  
                          ]).'</span>';
                          if($defaultValue > 0){
                            $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                          }
                        $priceTableBodyRows.='</td>';
                      }
                    }
                    $priceTableBodyRows.='</tr>';
                }
              }    
               
              $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";  
              
              
              $excelTableBodyRows="";
              
              foreach ($productTypeData['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
                $excelTableBodyRows.='<tr>';
                  $excelTableBodyRows.='<td style="min-width:200px;">'.$priceClientCategories[$priceClientCategoryId].'</td>';
                  if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                    foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                      foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                        $defaultValue=0;
                        if (
                          array_key_exists($productId,$priceClientCategoryData['Product']) &&
                          array_key_exists('RawMaterial',$priceClientCategoryData['Product'][$productId]) &&
                          array_key_exists($rawMaterialId,$priceClientCategoryData['Product'][$productId]['RawMaterial'])
                        ){
                          $defaultValue=$priceClientCategoryData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'];
                        }
                        
                        $excelTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                          $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,4,'.',',').'</span>';
                        $excelTableBodyRows.='</td>';
                      }
                    }
                  }
                  else {
                    foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                      $defaultValue=0;
                      if (array_key_exists($productId,$priceClientCategoryData['Product'])){
                        $defaultValue=$priceClientCategoryData['Product'][$productId]['price'];
                      }
                      
                      $excelTableBodyRows.='<td>';
                        $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,4,'.',',').'</span>';
                      $excelTableBodyRows.='</td>';
                    }
                    
                  }
                  $excelTableBodyRows.='</tr>';
              }
              if (!empty($productTypeData['Client'])){
                foreach ($productTypeData['Client'] as $clientId=>$clientData){
                  $excelTableBodyRows.='<tr>';
                    $excelTableBodyRows.='<td style="min-width:200px;">'.$clients[$clientId].'</td>';
                    if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                      foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                        foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                          $defaultValue=0;
                          if (
                            array_key_exists($productId,$clientData['Product']) &&
                            array_key_exists('RawMaterial',$clientData['Product'][$productId]) &&
                            array_key_exists($rawMaterialId,$clientData['Product'][$productId]['RawMaterial'])
                          ){
                            $defaultValue=$clientData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'];
                            $priceDateTime=new DateTime($clientData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']);
                          }
                          
                        
                          $excelTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                            $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,2,'.',',').'</span>';
                          $excelTableBodyRows.='</td>';
                        }
                      }
                    }
                    else {
                      foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
                        $defaultValue=0;
                        if (array_key_exists($productId,$clientData['Product'])){
                          $defaultValue=$clientData['Product'][$productId]['price'];
                          $priceDateTime=new DateTime($clientData['Product'][$productId]['price_datetime']);
                        }
                        
                        $excelTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                          $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,2,'.',',').'</span>';
                        $excelTableBodyRows.='</td>';
                      }
                      
                    }
                    $excelTableBodyRows.='</tr>';
                }
              }  
              $excelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$excelTableBodyRows."</tbody>";                
             $tableId='precios_'.substr($productTypeData['ProductType']['name'],0,14)."_".$priceDateTimeArray->format('Ymd');  
            $priceTable='<table id="'.$tableId.'">'.$priceTableHead.$priceTableBody.$priceTableHead.'</table>';
            echo $priceTable;
            echo "</div>";  
           
            $excelTable='<table id="'.$tableId.'">'.$priceTableHead.$excelTableBody.'</table>';
            $excelOutput.= $excelTable;
          }
        }
      
        $_SESSION['resumenPrecios'] = $excelOutput;
        //if ($totalFields < 1000){
        //  if ($userRoleId == ROLE_ADMIN){
        //    echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices','name'=>'savePrices','style'=>'width:300px;']);
        //  }
        //}
        //else {
        //  echo '<p class="warning">El número total de campos ('.$totalFields.') está mayor que 1000, por tal razón el servidor no permite guardar los precios directamente desde aquí.  Utiliza las páginas de precio por cliente o precio por producto para grabar precios.</p>';
        //}
        echo "</div>";        
      echo "</div>";
    echo "</div>";
  echo "</fieldset>";
  echo $this->Form->end();
      
  echo '<div id="priceModal" class="modal fade">';
		echo '<div class="modal-dialog" style="width:80%!important;max-width:800px!important;">';
			echo '<div class="modal-content">';
				//echo '<div class="modal-header">';
				//echo '</div>';
        echo '<div class="modal-body" style="overflow-y:scroll;color:black;">';
          echo $this->Form->create('Modal'); 
          echo '<legend>'.__('Registrar Precio').'</legend>';
          echo '<fieldset>';
            echo $this->Form->input('product_type_id',['type'=>'hidden']);
            echo $this->Form->input('price_client_category_id',['type'=>'hidden']);
            echo $this->Form->input('client_id',['type'=>'hidden']);
            echo $this->Form->input('product_id',['type'=>'hidden']);
            echo $this->Form->input('raw_material_id',['type'=>'hidden']);  
            echo $this->Form->input('user_id',['value'=>$loggedUserId,'type'=>'hidden']);   
            
            echo $this->Form->input('product_type',['readonly'=>true,'type'=>'text']);
            echo $this->Form->input('price_client_category',['readonly'=>true,'type'=>'text']);
            echo $this->Form->input('client',['readonly'=>true,'type'=>'text']);
            echo $this->Form->input('product',['readonly'=>true,'type'=>'text']);
            echo $this->Form->input('raw_material',['readonly'=>true,'type'=>'text']);  
            echo $this->Form->input('user',['readonly'=>true,'value'=>$users[$loggedUserId],'type'=>'text']); 
            
            echo $this->Form->input('price',['type'=>'decimal']);
            
          echo '</fieldset>';
          echo $this->Form->end();
            if ($userRoleId == ROLE_ADMIN){
              echo '<button class="goToPriceDeletion" type="button" class="btn btn-danger">Eliminar precios</button>';
            }
            echo '<button id="closePriceModal" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
            echo '<button class="savePrice" type="button" class="btn btn-default">Guardar Precio</button>';
          echo '</div>';
        echo '</div>';
        echo '<div id="priceModalFooter" class="modal-footer">';
        if ($userRoleId == ROLE_ADMIN){
          echo '<button class="goToPriceDeletion" type="button" class="btn btn-danger">Eliminar precios</button>';
        }
          echo '<button id="closePriceModal" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
          echo '<button class="savePrice" type="button" class="btn btn-default">Guardar Precio</button>';
        echo '</div>';
      echo '</div>'; 
    echo '</div>'; 
  echo '</div>';  
  if ($userRoleId == ROLE_ADMIN){
    echo '<div id="priceDeletionModal" class="modal fade">';
      echo '<div class="modal-dialog" style="width:80%!important;max-width:1280px!important;">';
        echo '<div class="modal-content">';
          //echo '<div class="modal-header">';
          //  echo '<h1>Eliminar precios</h1>';
          //echo '</div>';
          echo '<div class="modal-body" style="overflow-y:scroll;color:black;">';
            echo $this->Form->create('PriceDeletionModal'); 
            echo '<legend>'.__('Eliminar historial de Precios').'</legend>';
            echo '<fieldset style="line-height:1em;">';
              echo $this->Form->input('price_client_category_id',['type'=>'hidden']);
              echo $this->Form->input('client_id',['type'=>'hidden']);
              echo $this->Form->input('product_id',['type'=>'hidden']);
              echo $this->Form->input('raw_material_id',['type'=>'hidden']);  
              echo $this->Form->input('user_id',['value'=>$loggedUserId,'type'=>'hidden']);   
              
              echo $this->Form->input('price_client_category',['readonly'=>true,'type'=>'text','style'=>'font-size:0.9em;']);
              echo $this->Form->input('client',['readonly'=>true,'type'=>'text','style'=>'font-size:0.9em;']);
              echo $this->Form->input('product',['readonly'=>true,'type'=>'text','style'=>'font-size:0.9em;']);
              echo $this->Form->input('raw_material',['readonly'=>true,'type'=>'text','style'=>'font-size:0.9em;']);  
              //echo $this->Form->input('user',['readonly'=>true,'value'=>$users[$loggedUserId],'type'=>'text','style'=>'font-size:0.9em;']); 
              
              echo '<p>Al eliminar estos precios, el precio quedará en cero.</p>';
              echo '<p>Peligro!  Cuando se eliminan los precios, desaparecerán completamente del sistema.  Únicamente quedará grabado la información de referencia para analisis, pero esta información no estará disponible en pantalla.</p>';
              echo '<p>Las cotizaciones, ordenes de venta y facturas que ocuparon estos precios hasta este punto no estarán modificados.</p>';
              
              
              echo '<h2>Precios que serán eliminados para producto </h2>';
              
              echo '<div id="PriceTableContainer">';
              echo '</div>';
              
              
            echo '</fieldset>';
            echo $this->Form->end();
            echo '<button class="deletePrices" type="button" class="btn btn-danger">Eliminar Precios</button>';
          echo '</div>';
          echo '<div id="priceDeletionModalFooter" class="modal-footer">';
            echo '<button id="closePriceDeletionModal" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
            echo '<button class="deletePrices" type="button" class="btn btn-danger">Eliminar Precios</button>';
          echo '</div>';
        echo '</div>'; 
      echo '</div>'; 
    echo '</div>';  
  }
?>
</div>