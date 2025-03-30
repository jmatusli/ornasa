<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','.genericthreshold',function(){
    var thresholdValue=parseInt($(this).val());
    if (!isNaN(thresholdValue) && thresholdValue > 0){
      $(this).closest('div.productType').find('table tbody tr td div input.threshold').val(thresholdValue);
    }
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
      alert('Se registraron las volumenes de venta')
    <?php } ?>
	});
</script>

<div class="products form fullwidth">
<?php 
  $title="Volumenes de Venta de Productos";
  
	echo $this->Form->create('Product');
	echo "<fieldset id='mainform'>";
		echo "<legend>".$title."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
          echo "<div class='row'>";
            echo "<div class='col-sm-12 col-lg-6'>";	
              echo $this->Form->input('volume_datetime',['label'=>__('Date'),'type'=>'datetime','default'=>$volumeDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
              echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
              echo $this->Form->input('product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
              echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
              echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
              echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
            echo "</div>";
          echo "</div>";  
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Form->Submit(__('Grabar Volumenes de Venta'),['class'=>'savePrices','style'=>'width:300px;']);
        }
        $volumeDateTime=new DateTime($volumeDateTime);
		
		 //pr($productTypes);exit;
        foreach ($productTypes as $productTypeId=>$productTypeData){
          //pr($productTypeData);
          
          $tableHead='';
          $tableHead.='<thead>';
            $tableHead.='<tr>';
              $tableHead.='<th>Producto</th>';
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                $tableHead.='<th>Preforma</th>';
              }
              $tableHead.='<th>Volumen</th>';
              $tableHead.='<th>Precio Categoría listado</th>';
              $tableHead.='<th>Precio Categoría 2</th>';
              $tableHead.='<th>Precio Volumen</th>';
            $tableHead.='</tr>';
          $tableHead.='</thead>';
          
          $tableBody='';
          $tableBody.='<tbody>';
          foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
            //pr($productData);
            if ($productTypeId == PRODUCT_TYPE_BOTTLE){
              foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                //pr($rawMaterialData);
                $tableBody.='<tr>'; 
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.product_id',['label'=>false,'default'=>$productId,'class'=>'fixed']).'</td>';
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.raw_material_id',['label'=>false,'default'=>$rawMaterialId,'class'=>'fixed']).'</td>';
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.threshold_volume',['label'=>false,'type'=>'number','default'=>$rawMaterialData['threshold_volume'],'class'=>'threshold']).'</td>';
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price_category_one',['label'=>false,'type'=>'decimal','value'=>$rawMaterialData['price_category_one'],'readonly'=>true]).'</td>';
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price_category_two',['label'=>false,'type'=>'decimal','value'=>$rawMaterialData['price_category_two'],'readonly'=>true]).'</td>';
                  $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.RawMaterial.'.$rawMaterialId.'.price_category_volume',['label'=>false,'type'=>'decimal','value'=>$rawMaterialData['price_category_three']]).'</td>';
                $tableBody.='</tr>';
              }
            
            }
            else {
              $tableBody.='<tr>'; 
                $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.product_id',['label'=>false,/* 'type'=>'number', */'default'=>$productId,'class'=>'fixed']).'</td>';
         
                
                $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.threshold_volume',['label'=>false,'type'=>'number','default'=>$productData['threshold_volume'],'class'=>'threshold']).'</td>';
				      
                $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.price_category_one',['label'=>false,'type'=>'number','default'=>$productData['price_category_one'],'class'=>'threshold']).'</td>';
				
                $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.price_category_one',['label'=>false,'type'=>'decimal','readonly'=>true]).'</td>';
                $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.price_category_two',['label'=>false,'type'=>'decimal','readonly'=>true]).'</td>';
               /* $tableBody.='<td>'.$this->Form->Input('ProductType.'.$productTypeId.'.Product.'.$productId.'.price_category_volume',['label'=>false,'type'=>'decimal']).'</td>';*/
              $tableBody.='</tr>';
            }            
          }
          $tableBody.='</tbody>';
          
          $table='<table>'.$tableHead.$tableBody.'</table>';
          echo '<div class="productType">';
            echo "<h2>Volumenes de producto y precios de volumen para línea de producto ".$productTypeData['ProductType']['name']." en fecha ".$volumeDateTime->format('d-m-Y')."</h2>";
            
            echo '<p style="background-color:#c0fbc0;">Solo se consideran precios en o anterior a la fecha seleccionado.  El precio vigente para el producto aparece en verde; esto puede ser el precio grabado para el cliente en el día seleccionado, un precio grabado para el cliente en el día anterior, o un precio grabado en categoría de precios.</p>';
            echo '<p class="info">Un precio 0 significa que aun no se registró un precio para esta fecha</p>';
            if ($existenceOptionId == SHOW_EXISTING){
              echo '<p class="info">Los productos que se muestran son productos que existen en la bodega seleccionada.  La cantidad que se muestra es el total de todas bodegas de calidades A, B y C.  Aunque es posible que no haya productos de calidad A, se asume que si hay productos de calidad B y C esto signifa que aun se están produciendo estos productos.  </p>';
            }
            echo $this->Form->input('ProductType.'.$productTypeId.'.generic_threshold_volume',['label'=>'Volumen genérico','type'=>'number','default'=>0,'class'=>'genericthreshold']);
            echo '<p>Si se especifica el volumen genérico, este volumen se asigna a cada producto de este tipo.  Todos valores anteriormente existentes estarán remplazados al guardar (a partir de la fecha establecida).</p>';
            echo $table;
          echo '</div>';  
        }

        if ($userRoleId == ROLE_ADMIN){
          echo $this->Form->Submit(__('Grabar Volumenes de Venta'),['class'=>'savePrices','style'=>'width:300px;','div'=>['class'=>'submit','style'=>'clear:left']]);
        }
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>