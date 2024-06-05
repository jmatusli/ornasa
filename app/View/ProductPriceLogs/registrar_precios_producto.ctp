<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','#ProductPriceLogClientId',function(e){	 
    $('#changeDate').trigger('click');
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
  $priceDateTimeArray=new DateTime($priceDateTime);
  $title="Registrar Precios para  Producto";
  if ($productId > 0){
    $title.=(" ".$product['Product']['name']);
  }
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".$title."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
					echo "<div class='col-sm-9 col-lg-6'>";	
            echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            //echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
            echo $this->Form->input('product_id',['value'=>$productId,'empty'=>[0=>'-- PRODUCTO --']]);
            if ($productId > 0 && $product['ProductType']['id']==PRODUCT_TYPE_BOTTLE){
              echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
            }
            else {
               echo  $this->Form->input('existence_option_id',['type'=>'hidden','default'=>$existenceOptionId]);
            }
            echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
            echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
             //$fileName=($priceDateTimeArray->format('Ymd')).'_Precios_Producto_'.$product['Product']['name'].'.xlsx';
            //echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarPreciosProducto',$fileName], ['class' => 'btn btn-primary']); 
            //echo  "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
            //echo  "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
          echo "</div>";
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices2','name'=>'savePrices2','style'=>'width:300px;']);
          echo '<br/>';
        }
        $excelOutput='';
            
        echo "<div class='col-sm-12' style='padding:5px;'>";
          echo "<h3>Precios antes de IVA para producto ".$product['Product']['name']." en fecha ".$priceDateTimeArray->format('d-m-Y')."</h3>";
              
          $priceTableHead="<thead>";
          if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
            $priceTableHead.="<tr>";
              $priceTableHead.="<th style='width:100px;'></th>";
              $priceTableHead.='<th colspan="'.count($existences['existences']['Product'][$productId]['RawMaterial']).'" style="text-align:center">'.$product['Product']['name'].'</th>'; 
            $priceTableHead.="</tr>";
            $priceTableHead.="<tr>";
              $priceTableHead.="<th style='width:100px;'>Cliente</th>";
              foreach ($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                $priceTableHead.='<th style="text-align:center">'.$rawMaterialData['abbreviation'].'</th>';
              }
            $priceTableHead.="</tr>";
          }
          else {
            $priceTableHead.="<tr>";
              $priceTableHead.="<th style='width:100px;'>Cliente</th>";
              $priceTableHead.='<th style="text-align:center">'.$product['Product']['name'].'</th>';
            $priceTableHead.="</tr>";
          }  
          $priceTableHead.="</thead>";
              
          $priceTableBodyRows="";
          //echo "client count is ".count($clientPriceArray['Client'])."<br/>";
          foreach ($priceClientCategoryPriceArray as $priceClientCategoryId=>$priceClientCategoryData){
            $priceTableBodyRows.='<tr style="background-color:'.$priceClientCategoryColors[$priceClientCategoryId].'">';
              $priceTableBodyRows.='<td style="min-width:200px;">'.$priceClientCategories[$priceClientCategoryId].'</td>';
              
              if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
                foreach ($priceClientCategoryData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                  $defaultValue=0;
                  if (array_key_exists($rawMaterialId,$priceClientCategoryData['RawMaterial'])
                  ){
                    $defaultValue=round($priceClientCategoryData['RawMaterial'][$rawMaterialId]['price'],4);
                    $priceDateTime=new DateTime($priceClientCategoryData['RawMaterial'][$rawMaterialId]['price_datetime']);
                  }
                        
                      
                  $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                  $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                  $priceTableBodyRows.='<span class="number">'.$this->Form->Input('PriceClientCategory.'.$priceClientCategoryId.'.RawMaterial.'.$rawMaterialId.'.price',[
                    'label'=>false,
                    'type'=>'decimal',
                    'value'=>$defaultValue,
                    'style'=>'width:160px;',
                    'class'=>'price',
                    'div'=>['style'=>'width:100%;text-align:center;']
                  ]).'</span>';
                  if($defaultValue > 0){
                    $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                  }
                  $priceTableBodyRows.='</td>';
                }
              }
              else {
                $defaultValue=round($priceClientCategoryData['price'],4);
                if (array_key_exists('price_datetime',$priceClientCategoryData)){
                  $priceDateTime=new DateTime($priceClientCategoryData['price_datetime']);
                }
                $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                  $priceTableBodyRows.='<span class="previousprice hidden">'.$defaultValue.'</span>';
                  $priceTableBodyRows.='<span class="number">'.$this->Form->Input('PriceClientCategory.'.$priceClientCategoryId.'.price',[
                    'label'=>false,
                    'type'=>'decimal',
                    'value'=>$defaultValue,
                    'style'=>'width:160px;',
                    'class'=>'price',
                    'div'=>['style'=>'width:100%;text-align:center;'],  
                  ]).'</span>';
                  if($defaultValue > 0){
                    $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                  }
                $priceTableBodyRows.='</td>';
              }
            $priceTableBodyRows.='</tr>';
          }
          
          foreach ($clientPriceArray as $clientId=>$clientData){
            //echo "client id is ".$clientId."<br/>";
            //pr($clientData);
            $priceTableBodyRows.='<tr>';
              $priceTableBodyRows.='<td style="min-width:200px;">'.$clients[$clientId].'</td>';
              
              if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
                foreach ($clientData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                  $priceDatetime=new DateTime($rawMaterialData['price_datetime']);
                  
                  $priceTableBodyRows.='<td '.($rawMaterialData['price']>0?" class='tooltipcontainer'":"").'>';
                  $priceTableBodyRows.='<span class="previousprice hidden">'.$rawMaterialData['price'].'</span>';
                  $priceTableBodyRows.='<span class="number">'.$this->Form->Input('Client.'.$clientId.'.RawMaterial.'.$rawMaterialId.'.price',[
                    'label'=>false,
                    'type'=>'decimal',
                    'value'=>round($rawMaterialData['price'],4),
                    'style'=>'width:160px;',
                    'class'=>'price',
                    'div'=>['style'=>'width:100%;text-align:center;']
                  ]).'</span>';
                  if($rawMaterialData['price'] > 0){
                    $priceTableBodyRows.='<span class="tooltiptext">'.($priceDatetime->format("d/m/Y")).'</span>';
                  }
                  $priceTableBodyRows.='</td>';
                }
              }
              else {
              
                $priceTableBodyRows.='<td '.($clientData['price']>0?" class='tooltipcontainer'":"").'>';
                  $priceTableBodyRows.='<span class="previousprice hidden">'.$clientData['price'].'</span>';
                  $priceTableBodyRows.='<span class="number">'.$this->Form->Input('Client.'.$clientId.'.price',[
                    'label'=>false,
                    'type'=>'decimal',
                    'value'=>round($clientData['price'],4),
                    'style'=>'width:160px;',
                    'class'=>'price',
                    'div'=>['style'=>'width:100%;text-align:center;'],  
                  ]).'</span>';
                  if($clientData['price'] > 0){
                    $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                  }
                $priceTableBodyRows.='</td>';
              }
            $priceTableBodyRows.='</tr>';
          }  
          $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";  
          /*    
          $excelTableBodyRows="";
          //echo "client count is ".count($productTypeData['Client'])."<br/>";
          foreach ($productTypeData['Client'] as $clientId=>$clientData){
            //echo "client id is ".$clientId."<br/>";
            $excelTableBodyRows.='<tr>';
              $excelTableBodyRows.='<td style="min-width:200px;">'.$clients[$clientId].'</td>';
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                  //pr($productData);
                  foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                    //echo "rawMaterialId is ".$rawMaterialId."<br/>";
            
                    $defaultValue=0;
                    if (array_key_exists($productId,$clientData['Product'])
                      && array_key_exists($rawMaterialId,$clientData['Product'][$productId]['RawMaterial'])
                    ){
                      //$defaultValue=round($clientData['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'],2);
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
                foreach ($productTypeData['ProductTypeInfo']['Product'] as $productId =>$productData){
                  //pr($productData);
                 
                  $defaultValue=0;
                  if (array_key_exists($productId,$clientData['Product'])){
                    //$defaultValue=round($clientData['Product'][$productId]['price'],2);
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
            
          $excelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$excelTableBodyRows."</tbody>";        */        
              
          $priceTable="<table id='precios_".$product['ProductType']['name']."_".$priceDateTimeArray->format('Ymd')."'>".$priceTableHead.$priceTableBody.$priceTableHead."</table>";
          echo $priceTable;
        echo "</div>";  
           
          //$excelTable="<table id='precios_".$productTypeData['ProductTypeInfo']['name']."_".$priceDateTimeArray->format('Ymd')."'>".$priceTableHead.$excelTableBody."</table>";
           //$excelOutput.= $excelTable;
           //$_SESSION['resumenPrecios'] = $excelOutput;
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Form->Submit(__('Grabar Precios de Venta'),['id'=>'savePrices','name'=>'savePrices','style'=>'width:300px;','div'=>['class'=>'submit','style'=>'clear:left']]);
        }
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
  echo "</div>";
echo "</fieldset>";
?>
</div>