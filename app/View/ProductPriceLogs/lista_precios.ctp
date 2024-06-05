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
	echo $this->Form->create('ProductPriceLog');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Lista Precios de Productos por Naturaleza')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";							
          echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
          
          echo $this->Form->input('selected_product_nature_id',['default'=>$selectedProductNatureId,'empty'=>[0=>'-- Productos de cada naturaleza --']]);
          echo $this->Form->input('existence_option_id',['default'=>$existenceOptionId]);
          
          echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
          
          $fileName=($priceDateTimeArray->format('Ymd')).'_Lista_Precios.xlsx';
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarListaPrecios',$fileName], ['class' => 'btn btn-primary']); 
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        $excelOutput='';
        foreach ($productNatures as $productNatureId=>$productNatureData){
          echo "<div class='col-sm-12' style='padding:5px;'>";
            echo '<h2>'.$productNatureData['ProductNature']['name'].'</h2>';  
            if (empty($productNatureData['existences']['Product'])){ 
              echo '<h3>No hay productos de esta naturaleza aun.</h3>';
            }
            else {
              echo '<h3>Precios antes de IVA  en fecha '.$priceDateTimeArray->format('d-m-Y').'</h3>';               
              $priceTableHead="<thead>";
                $priceTableHead.="<tr>";
                  $priceTableHead.="<th style='width:100px;'>Tipo Producto</th>";
                  $priceTableHead.="<th style='width:100px;'>Producto</th>";
                  if ($productNatureId == PRODUCT_NATURE_PRODUCED){
                    $priceTableHead.="<th style='width:100px;'>Materia Prima</th>";
                  }
                  foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
                    $priceTableHead.='<th style="text-align:center">'.$priceClientCategoryName.'</th>';
                  }
                  if ($productNatureId == PRODUCT_NATURE_PRODUCED || $productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
                    $priceTableHead.="<th style='width:100px;'>Volumen</th>";
                  }
                $priceTableHead.="</tr>";
              $priceTableHead.="</thead>";  
                
              $priceTableBodyRows="";
                
              foreach ($productNatureData['existences']['Product'] as $productId =>$productData){
                if ($productNatureId == PRODUCT_NATURE_PRODUCED){  
                  foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                    $priceTableBodyRows.='<tr>';
                      $priceTableBodyRows.='<td>'.$productData['ProductType']['name'].'</td>';
                      $priceTableBodyRows.='<td>'.$productData['name'].'</td>';
                      $priceTableBodyRows.='<td>'.$rawMaterialData['name'].'</td>';
                      foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
                        $defaultValue=0;
                        if (array_key_exists($priceClientCategoryId,$rawMaterialData['PriceClientCategory'])){
                          $defaultValue=$rawMaterialData['PriceClientCategory'][$priceClientCategoryId]['price'];
                          $priceDateTime=new DateTime($rawMaterialData['PriceClientCategory'][$priceClientCategoryId]['price_datetime']);
                        }
                          
                        $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                          $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductNature.'.$productNatureId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.PriceClientCategory.'.$priceClientCategoryId.'.price',[
                            'label'=>false,
                            'type'=>'decimal',
                            'value'=>number_format($defaultValue,4,'.',','),
                            'style'=>'width:80px;',
                            'class'=>'price',
                            'div'=>['style'=>'width:100%;text-align:center;'],  
                            'readonly'=>true
                          ]).'</span>';
                          if ($defaultValue > 0){
                            $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                          }
                        $priceTableBodyRows.='</td>';
                      }
                      $priceTableBodyRows.='<td>'.$productData['volume'].'</td>';                  
                    $priceTableBodyRows.='</tr>';  
                  }
                }  
                else {                  
                  $priceTableBodyRows.='<tr>';
                    $priceTableBodyRows.='<td>'.$productData['ProductType']['name'].'</td>';
                    $priceTableBodyRows.='<td>'.$productData['name'].'</td>';
                    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
                      $defaultValue=0;
                      if (array_key_exists($priceClientCategoryId,$productData['PriceClientCategory'])){
                        $defaultValue=$productData['PriceClientCategory'][$priceClientCategoryId]['price'];
                        $priceDateTime=new DateTime($productData['PriceClientCategory'][$priceClientCategoryId]['price_datetime']);
                      }
                    
                      $priceTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                        $priceTableBodyRows.='<span class="number">'.$this->Form->Input('ProductNature.'.$productNatureId.'.Product.'.$productId.'.PriceClientCategory.'.$priceClientCategoryId.'.price',[
                          'label'=>false,
                          'type'=>'decimal',
                          'value'=>number_format($defaultValue,4,'.',','),
                          'style'=>'width:80px;',
                          'class'=>'price',
                          'readonly'=>true,
                          'div'=>['style'=>'width:100%;text-align:center;'],  
                        ]).'</span>';
                        if($defaultValue > 0){
                          $priceTableBodyRows.='<span class="tooltiptext">'.($priceDateTimeArray->format("d/m/Y")).'</span>';
                        }
                      $priceTableBodyRows.='</td>';
                    }
                    if ($productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
                      $priceTableBodyRows.='<td>'.$productData['volume'].'</td>';                  
                    }
                  $priceTableBodyRows.='</tr>';    
                }                  
              }  
            }
                 
            $priceTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$priceTableBodyRows."</tbody>";  
            
            $tableId='precios_'.substr($productNatureData['ProductNature']['name'],0,14)."_".$priceDateTimeArray->format('Ymd');  
            $priceTable='<table id="'.$tableId.'">'.$priceTableHead.$priceTableBody.'</table>';
            echo $priceTable;
            
            
            $excelTableBodyRows="";
            foreach ($productNatureData['existences']['Product'] as $productId =>$productData){
              if ($productNatureId == PRODUCT_NATURE_PRODUCED){  
                foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                  $excelTableBodyRows.='<tr>';
                    $excelTableBodyRows.='<td>'.$productData['ProductType']['name'].'</td>';
                    $excelTableBodyRows.='<td>'.$productData['name'].'</td>';
                    $excelTableBodyRows.='<td>'.$rawMaterialData['name'].'</td>';
                    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
                      $defaultValue=0;
                      if (array_key_exists($priceClientCategoryId,$rawMaterialData['PriceClientCategory'])){
                        $defaultValue=$rawMaterialData['PriceClientCategory'][$priceClientCategoryId]['price'];
                        $priceDateTime=new DateTime($rawMaterialData['PriceClientCategory'][$priceClientCategoryId]['price_datetime']);
                      }
                        
                      $excelTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                        $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,4,'.',',').'</span>';
                      $excelTableBodyRows.='</td>';
                    }
                    $excelTableBodyRows.='<td>'.$productData['volume'].'</td>';                  
                  $excelTableBodyRows.='</tr>';  
                }
              }  
              else {                  
                $excelTableBodyRows.='<tr>';
                  $excelTableBodyRows.='<td>'.$productData['ProductType']['name'].'</td>';
                  $excelTableBodyRows.='<td>'.$productData['name'].'</td>';
                  foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
                    $defaultValue=0;
                    if (array_key_exists($priceClientCategoryId,$productData['PriceClientCategory'])){
                      $defaultValue=$productData['PriceClientCategory'][$priceClientCategoryId]['price'];
                      $priceDateTime=new DateTime($productData['PriceClientCategory'][$priceClientCategoryId]['price_datetime']);
                    }
                  
                    $excelTableBodyRows.='<td '.($defaultValue>0?" class='tooltipcontainer'":"").'>';
                      $excelTableBodyRows.='<span class="number">'.number_format($defaultValue,4,'.',',').'</span>';
                    $excelTableBodyRows.='</td>';
                  }
                  if ($productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
                    $excelTableBodyRows.='<td>'.$productData['volume'].'</td>';                  
                  }
                $excelTableBodyRows.='</tr>';    
              }                  
            }  
          
            $excelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$excelTableBodyRows."</tbody>";                
               
            $excelTable='<table id="'.$tableId.'">'.$priceTableHead.$excelTableBody.'</table>';
            $excelOutput.= $excelTable;
          echo '</div>';  
        }
      echo '</div>';        
      echo $this->Form->end();
    echo '</div>';        
  echo "</div>";
  
echo "</fieldset>";
$_SESSION['listaPrecios'] = $excelOutput;
?>
</div>