<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','#ProductPriceLogClientId',function(e){	 
    $('#changeDate').trigger('click');
  });
   
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
	
	$(document).ready(function(){
    $('select.fixed option:not(:selected)').attr('disabled', true);
    <?php if ($boolSaved){ ?>
      alert('Se registraron los precios de venta')
    <?php } ?>
	});
</script>

<div class="productpricelogs form fullwidth">
<?php 
  $title="Registrar Precios de Productos";
  if ($clientId>0){
    $title.=(" ".$client['ThirdParty']['company_name']);
  }
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".$title."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-12 col-lg-6'>";	
            echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            //echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
            echo $this->Form->input('product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
            echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
            echo $this->Form->input('client_id',['label'=>'Cliente','default'=>$clientId,'empty'=>[0=>'-- Cliente --']]);
            
            echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            //echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
            //echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
      if ($clientId == 0){
        echo '<h2>Primero seleccione cliente</h2>';
      }
      else {
        echo "<div class='row'>";
          if ($userRoleId == ROLE_ADMIN){
            echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices2','name'=>'savePrices2','style'=>'width:300px;']);
          }
          $priceDateTime=new DateTime($priceDateTime);
          foreach ($productTypes as $productTypeId=>$productTypeData){
            if (!empty($productTypeData['existences']['Product'])){ 
              //pr($productTypeData['existences']['Product']);
              echo "<div class='col-sm-12' style='padding:5px;'>";
              echo "<h3>Precios antes de IVA para línea de producto ".$productTypeData['ProductType']['name']." en fecha ".$priceDateTime->format('d-m-Y').(array_key_exists($clientId,$clients)?(" para cliente seleccionado ".$clients[$clientId]):"")."</h3>";
              echo '<p style="background-color:#c0fbc0;">Solo se consideran precios en o anterior a la fecha seleccionado.  El precio vigente para el producto aparece en verde; esto puede ser el precio grabado para el cliente en el día seleccionado, un precio grabado para el cliente en el día anterior, o un precio grabado en categoría de precios.</p>';
              echo '<p class="info">Un precio 0 significa que aun no se registró un precio para esta fecha</p>';
              if ($existenceOptionId == SHOW_EXISTING){
                echo '<p class="info">Los productos que se muestran son productos que existen en la bodega seleccionada.  La cantidad que se muestra es el total de todas bodegas de calidades A, B y C.  Aunque es posible que no haya productos de calidad A, se asume que si hay productos de calidad B y C esto signifa que aun se están produciendo estos productos.  </p>';
              }
              
              $priceTableHead="<thead>";
                $priceTableHead.="<tr>";
                  if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                    $priceTableHead.="<th style='width:100px;'>Botella</th>";
                    $priceTableHead.="<th style='width:100px;'>Preforma</th>";
                    $priceTableHead.="<th style='width:100px;'>Cantidad ABC en inventario</th>";
                  }
                  else {
                    $priceTableHead.="<th style='width:100px;'>".__('Product')."</th>";
                    $priceTableHead.="<th style='width:100px;'>Cantidad en inventario</th>";
                  }
                  
                  $priceTableHead.='<th style="width:100px;'.($pricePresentForClient?"background-color:#c0fbc0;":"").'">'.__('Precio').'</th>';
                  $priceTableHead.="<th style='width:100px;'>".__('Fecha precio antes de esta fecha')."</th>";
                  $priceTableHead.='<th style="width:100px;'.($previousPricePresentForClient?"background-color:#c0fbc0;":"").'">'.__('Precio anterior (vigente) Cliente').'</th>';
                  $priceTableHead.='<th style="width:100px;'.(!$pricePresentForClient && !$previousPricePresentForClient?"background-color:#c0fbc0;":"").'">'.__('Precio vigente Categoría').'</th>';
                $priceTableHead.="</tr>";
              $priceTableHead.="</thead>";
              
              $priceTableBodyRows="";
              foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
                $firstRow=true;
                if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                  foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                    $productPrice=round($rawMaterialData['price'],4);
                    
                    $previousProductPrice=round($rawMaterialData['previous_price'],4);
                    $formattedPreviousProductPriceLogDateTime="N/A";
                    if ($rawMaterialData['previous_price_present']){
                      $previousProductPriceLogDateTime=new DateTime($rawMaterialData['previous_price_datetime']);
                      $formattedPreviousProductPriceLogDateTime=$previousProductPriceLogDateTime->format('d-m-Y H:i:s');
                    }
                    
                    $categoryProductPrice=round($rawMaterialData['category_price'],4);
                    $formattedCategoryProductPriceLogDateTime="N/A";
                    if ($rawMaterialData['price_client_category_id'] >0){
                      $categoryProductPriceLogDateTime=new DateTime($rawMaterialData['category_price_datetime']);
                      $formattedCategoryProductPriceLogDateTime=$categoryProductPriceLogDateTime->format('d-m-Y H:i:s');
                    }
                    
                    $priceTableBodyRows.="<tr productid=".$productId.">";
                      $priceTableBodyRows.="<td>";
                        $priceTableBodyRows.=$this->Html->link($productData['name'],['controller'=>'products','action'=>'view',$productId]);
                        if ($firstRow){
                         $priceTableBodyRows.=$this->Form->Input('Product.'.$productId.'.product_type_id',['label'=>false,'type'=>'hidden','value'=>$productTypeId]);
                          $firstRow=false;
                        } 
                        
                      $priceTableBodyRows.="</td>";
                      $priceTableBodyRows.="<td>".$this->Html->link($rawMaterialData['name'],['controller'=>'products','action'=>'view',$rawMaterialId])."</td>";
                      
                      $priceTableBodyRows.="<td>".$rawMaterialData['remaining']."</td>";
                      $priceTableBodyRows.='<td'.($rawMaterialData['price_present']?' style="background-color:#c0fbc0;"':'').'>'.$this->Form->Input('Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price',[
                        'label'=>false,
                        'type'=>'decimal',
                        'value'=>$productPrice,
                      ]).'</td>';
                      $priceTableBodyRows.="<td>".$formattedPreviousProductPriceLogDateTime."</td>";
                      $priceTableBodyRows.='<td'.($productPrice == 0 && $rawMaterialData['previous_price_present']?' style="background-color:#c0fbc0;"':'').'>'.($previousProductPrice == 0?"-":("C$ " .number_format($previousProductPrice,4,'.',','))).'</td>';
                      
                      $priceTableBodyRows.='<td'.($productPrice == 0 && $previousProductPrice == 0?' style="background-color:'.($rawMaterialData['price_client_category_id'] > 0?$priceClientCategoryColors[$rawMaterialData['price_client_category_id']]:'#c0fbc0').'"':'').'>'.($categoryProductPrice == 0?"-":("C$ " .number_format($categoryProductPrice,4,'.',','))).'</td>';
                    $priceTableBodyRows.="</tr>";
                  }
                }
                else {
                  $productPrice=round($productData['price'],4);
                    
                  $previousProductPrice=round($productData['previous_price'],4);
                  $formattedPreviousProductPriceLogDateTime="N/A";
                  if ($productData['previous_price_present']){
                    $previousProductPriceLogDateTime=new DateTime($productData['previous_price_datetime']);
                    $formattedPreviousProductPriceLogDateTime=$previousProductPriceLogDateTime->format('d-m-Y H:i:s');
                  }
                  
                  $categoryProductPrice=round($productData['category_price'],4);
                  $formattedPreviousProductPriceLogDateTime="N/A";
                  if ($productData['price_client_category_id']){
                    $previousProductPriceLogDateTime=new DateTime($productData['category_price_datetime']);
                    $formattedPreviousProductPriceLogDateTime=$previousProductPriceLogDateTime->format('d-m-Y H:i:s');
                  }
                  
                  $priceTableBodyRows.="<tr productid=".$productId.">";
                    $priceTableBodyRows.="<td>";
                      $priceTableBodyRows.=$this->Html->link($productData['name'],['controller'=>'products','action'=>'view',$productId]);
                      if ($firstRow){
                       $priceTableBodyRows.=$this->Form->Input('Product.'.$productId.'.product_type_id',['label'=>false,'type'=>'hidden','value'=>$productTypeId]);
                        $firstRow=false;
                      } 
                      
                    $priceTableBodyRows.="</td>";
                    $priceTableBodyRows.="<td>".$productData['remaining']."</td>";
                    $priceTableBodyRows.='<td'.($productData['price_present']?' style="background-color:#c0fbc0;"':'').'>'.$this->Form->Input('Product.'.$productId.'.price',[
                      'label'=>false,
                      'type'=>'decimal',
                      'value'=>$productPrice,
                    ]).'</td>';
                    $priceTableBodyRows.="<td>".$formattedPreviousProductPriceLogDateTime."</td>";
                    $priceTableBodyRows.='<td'.($productPrice == 0 && $productData['previous_price_present']?' style="background-color:#c0fbc0;"':'').'>'.($previousProductPrice == 0?"-":("C$ " .number_format($previousProductPrice,4,'.',','))).'</td>';
                    
                    $priceTableBodyRows.='<td'.($productPrice == 0 && $previousProductPrice == 0?' style="background-color:'.($productData['price_client_category_id'] > 0?$priceClientCategoryColors[$productData['price_client_category_id']]:'#c0fbc0').'"':'').'>'.($categoryProductPrice == 0?"-":("C$ " .number_format($categoryProductPrice,4,'.',','))).'</td>';
                  $priceTableBodyRows.="</tr>";
                }
              }  
                
              $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";                  
              //pr($productTypeData);  
              $priceTable="<table id='precios_".$productTypeData['ProductType']['name']."_".$priceDateTime->format('Ymd')."'>".$priceTableHead.$priceTableBody."</table>";
               echo $priceTable;
               echo "</div>";  
            }
          }
          if ($userRoleId == ROLE_ADMIN){
            echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices','name'=>'savePrices','style'=>'width:300px;','div'=>['class'=>'submit','style'=>'clear:left']]);
          }
        echo "</div>";     
      }      
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>