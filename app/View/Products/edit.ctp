<script>
	
  $('body').on('change','#ProductProductTypeId',function(){	
    $('#ProductProductionTypeId').val(0)
    switch($(this).children("option").filter(":selected").val()){
      case '<?php echo PRODUCT_TYPE_PREFORMA; ?>':
      case '<?php echo PRODUCT_TYPE_INJECTION_GRAIN; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_RAW; ?>)
        break;  
      case '<?php echo PRODUCT_TYPE_BOTTLE; ?>':
      case '<?php echo PRODUCT_TYPE_INJECTION_OUTPUT; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_PRODUCED; ?>)
        break;
      case '<?php echo PRODUCT_TYPE_POLINDUSTRIAS; ?>':
      case '<?php echo PRODUCT_TYPE_LOCAL; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_BOTTLES_BOUGHT; ?>)
        break;
      case '<?php echo PRODUCT_TYPE_CAP; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_ACCESORIES; ?>)
        break;  
      case '<?php echo PRODUCT_TYPE_CONSUMIBLES; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_BAGS; ?>)
        break;
      case '<?php echo PRODUCT_TYPE_ROLL; ?>':
        $('#ProductProductNatureId').val(<?php echo PRODUCT_NATURE_BAGS; ?>)
        break;
      default:
        $('#ProductProductNatureId').val(0)
    }
    $('#ProductProductNatureId').trigger('change')
  });
  
	$('body').on('change','#ProductProductNatureId',function(){	
    hidePriceAndSpecifications();
		switch($(this).children("option").filter(":selected").val()){
      case '<?php echo PRODUCT_NATURE_PRODUCED; ?>':
        $('.cost').addClass('hidden')
        $('.price').removeClass('hidden')
        $('.productiontype').removeClass('hidden')
      /*  
        if ($('#ProductProductTypeId').val() == <?php echo PRODUCT_TYPE_BOTTLE; ?>){
          $('.bottle.pet').removeClass('hidden')  
          $('.production.pet').removeClass('hidden')
        }
        if ($('#ProductProductTypeId').val() == <?php echo PRODUCT_TYPE_INJECTION_OUTPUT; ?>){
          $('.bottle.injection').removeClass('hidden')  
          $('.production.injection').removeClass('hidden')
        }
      */  
        break;
      case '<?php echo PRODUCT_NATURE_BOTTLES_BOUGHT; ?>':
        $('.cost').removeClass('hidden')
        $('.price').removeClass('hidden')
        $('.bottle:not(".production")').removeClass('hidden')
        break;
      case '<?php echo PRODUCT_NATURE_ACCESORIES; ?>':
        $('.cost').removeClass('hidden')
        $('.price').removeClass('hidden')
        $('.cap').removeClass('hidden')
        break;
      case '<?php echo PRODUCT_NATURE_RAW; ?>':
        $('.cost').removeClass('hidden')
        $('.productiontype').removeClass('hidden')
      /*  
        if ($('#ProductProductTypeId').val() == <?php echo PRODUCT_TYPE_BOTTLE; ?>){
          $('.raw.pet').removeClass('hidden')
        }
        if ($('#ProductProductTypeId').val() == <?php echo PRODUCT_TYPE_INJECTION_GRAIN; ?>){
          $('.raw.injection').removeClass('hidden')
        }
      */  
        //$('.production').removeClass('hidden')
        break;
      case '<?php echo PRODUCT_NATURE_BAGS; ?>':
        $('.cost').removeClass('hidden')
        if ($('#ProductProductTypeId').val() == <?php echo PRODUCT_TYPE_ROLL; ?>){
          $('.roll').removeClass('hidden')
        }
        else {
          $('.bag').removeClass('hidden')
        }
        break;
      default:     
    }  
  });
  
  $('body').on('change','#ProductProductionTypeId',function(){	
    if ($(this).val() == <?php echo PRODUCTION_TYPE_PET; ?>){
      if ($('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_PRODUCED; ?>){
        $('.bottle.pet').removeClass('hidden')  
        $('.production.pet').removeClass('hidden')
      }
      if ($('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_RAW; ?>){
        $('.raw.pet').removeClass('hidden')
      }
      
      $('.bottle.injection:not(.pet)').addClass('hidden')  
      $('.production.injection:not(.pet)').addClass('hidden')
      $('.raw.injection:not(.pet)').removeClass('hidden')
    }
    if ($(this).val() == <?php echo PRODUCTION_TYPE_INJECTION; ?>){
      $('.bottle.pet:not(.injection)').addClass('hidden')  
      $('.production.pet:not(.injection)').addClass('hidden')
      $('.raw.pet:not(.injection)').addClass('hidden')
      
      if ($('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_PRODUCED; ?>){
        $('.bottle.injection').removeClass('hidden')  
        $('.production.injection').removeClass('hidden')
      }
      if ($('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_RAW; ?>){
        $('.raw.injection').removeClass('hidden')
      }
    }
  });
  
  function hidePriceAndSpecifications(){
    $('.price').addClass('hidden')
    $('.bottle').addClass('hidden')
    $('.raw').addClass('hidden')
    $('.cap').addClass('hidden')
    $('.roll').addClass('hidden')
    $('.bag').addClass('hidden')
    $('.production').addClass('hidden')
    $('.productiontype').addClass('hidden')
  }
	
	$(document).ready(function(){
		$('#ProductProductNatureId').trigger('change');
    if ($('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_PRODUCED; ?> || $('#ProductProductNatureId').val() == <?php echo PRODUCT_NATURE_RAW; ?>){
      $('#ProductProductionTypeId').trigger('change');
    }
    if ($('#ProductProductionTypeId').val() == <?php echo PRODUCTION_TYPE_INJECTION; ?>){
      showRecipeData(<?php echo (array_key_exists('Product',$this->request->data) && array_key_exists('preferred_recipe_id',$this->request->data['Product'])?$this->request->data['Product']['preferred_recipe_id']:0); ?>);
    } 
  });  
  
  $('body').on('change','.ingredientQuantity div input',function(){	
    calculateIngredientTotal();
	});
  $('body').on('click','.addIngredient',function(){	
		var tableRow=$('#recipeIngredients tbody tr.d-none:first');
		tableRow.removeClass("d-none");
	});
	$('body').on('click','.removeIngredient',function(){	
		var tableRow=$(this).closest('tr').remove();
		calculateIngredientTotal();
	});	
  
  function calculateIngredientTotal(){
    var totalQuantity=0;
    var currentQuantity=0;
    $('#recipeIngredients td.ingredientQuantity div input').each(function(){
      currentQuantity=parseInt($(this).val());
      if (isNaN(currentQuantity) || currentQuantity <0){
        alert('Se debe registrar una cantidad numérica positiva')
        $(this).val(0)
      }
      else {
        totalQuantity += currentQuantity
      }
    });
    $('#recipeIngredients td.totalIngredientQuantity span.amount').text(totalQuantity)
  }
  
  $('body').on('change','.consumableQuantity div input',function(){	
    calculateConsumableTotal();
	});
  $('body').on('click','.addConsumable',function(){	
		var tableRow=$('#recipeConsumables tbody tr.d-none:first');
		tableRow.removeClass("d-none");
	});
	$('body').on('click','.removeConsumable',function(){	
		var tableRow=$(this).closest('tr').remove();
		calculateConsumableTotal();
	});	
  
  function calculateConsumableTotal(){
    var totalQuantity=0;
    var currentQuantity=0;
    $('#recipeConsumables td.consumableQuantity div input').each(function(){
      currentQuantity=parseInt($(this).val());
      if (isNaN(currentQuantity) || currentQuantity <0){
        alert('Se debe registrar una cantidad numérica positiva')
        $(this).val(0)
      }
      else {
        totalQuantity += currentQuantity
      }
    });
    $('#recipeConsumables td.totalConsumableQuantity span.amount').text(totalQuantity)
  }
  
  $('body').on('click','#saveRecipe',function(){
    var recipeProductId=<?php echo $this->request->data['Product']['id']; ?>;
    var recipeName=$('#RecipeName').val()
    var recipeDescription=$('#RecipeDescription').val()
    var recipeMillConversionProductId=$('#RecipeMillConversionProductId').val()

    var recipeItems=[]
    $('#recipeIngredients tbody tr:not(.totalrow)').each(function(){
      var ingredientProductId=parseInt($(this).find('td.rawmaterial div select').val());
      var ingredientQuantity=$(this).find('td.ingredientQuantity div input').val();
      var ingredientUnitId=parseInt($(this).find('td.unit div select').val());
      if (ingredientProductId > 0 && ingredientQuantity > 0){
        var ingredient={
          "product_id":ingredientProductId,
          "quantity":ingredientQuantity,
          "unit_id":ingredientUnitId,
        }
        recipeItems.push(ingredient)
      }
    });
    
    var recipeConsumables=[]
    $('#recipeConsumables tbody tr:not(.totalrow)').each(function(){
      var consumableProductId=parseInt($(this).find('td.consumable div select').val());
      var consumableQuantity=$(this).find('td.consumableQuantity div input').val();
      var consumableUnitId=parseInt($(this).find('td.unit div select').val());
      if (consumableProductId > 0 && consumableQuantity > 0){
        var consumable={
          "product_id":consumableProductId,
          "quantity":consumableQuantity,
          "unit_id":consumableUnitId,
        }
        recipeConsumables.push(consumable)
      }
    });
    
    var recipeReadyForSaving=1
    if (recipeName.length == 0){
      alert('Se debe especificar un nombre para la receta')
      recipeReadyForSaving=0
    }
    if (recipeItems.length == 0){
      alert('Una receta debe tener por lo menos un ingrediente')
      recipeReadyForSaving=0
    }
    if (recipeReadyForSaving){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>recipes/saveRecipe/',
        data:{"recipeId":0,"recipeProductId":recipeProductId,"recipeName":recipeName,"recipeDescription":recipeDescription,"recipeMillConversionProductId":recipeMillConversionProductId,'recipeItems':recipeItems,'recipeConsumables':recipeConsumables},
        cache: false,
        type: 'POST',
        success: function (recipeId) {
          if (!isNaN(parseInt(recipeId))){
            //get the updated dropdown list and show the latest recipe ingredients below
            //alert ('se guardó la receta número '+recipeId)
            showRecipeData(recipeId);
          }
          else {
            alert(recipeId);
          }
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
    }
  });
  
  function showRecipeData(recipeId){
    $.ajax({
      url: '<?php echo $this->Html->url('/'); ?>recipes/showRecipeSelectorAndIngredients/',
      data:{"recipeId":recipeId,"productId":<?php echo $this->request->data['Product']['id']; ?>,"referrerForm":'Product'},
      cache: false,
      type: 'POST',
      success: function (recipeData) {
        $('#recipeData').html(recipeData)
        $('#recipeModal').modal('hide');
      },
      error: function(e){
        alert(e.responseText);
        console.log(e);
      }
    });  
  }
</script>

<div class="products form fullwidth">
<?php 
  echo $this->Form->create('Product');
	echo '<fieldset>'; 
  echo '<legend>'.__('Edit Product').'</legend>';
  echo "<div class='container-fluid'>";
    echo  "<div class='row'>";
      echo "<div class='col-sm-9'>";
        echo $this->Form->input('product_type_id',['empty'=>[0=>'-- Seleccione Tipo de Producto --']]);
        echo $this->Form->input('product_nature_id',['empty'=>[0=>'-- Seleccione Naturaleza --']]);
        echo $this->Form->input('production_type_id',['default'=>0,'empty'=>[0=>'-- Seleccione Tipo de Producción --'],'class'=>'productiontype']);
        
        echo $this->Form->input('name');
        echo $this->Form->input('abbreviation');
        echo $this->Form->input('unit_id');
        echo $this->Form->input('description');
        echo $this->Form->input('bool_active',['div'=>['class'=>'div input checkbox']]);
        echo $this->Form->input('packaging_unit');
        echo $this->Form->input('accounting_code_id',['style'=>'font-size:0.9em','empty'=>[0=>'-- Seleccione cuenta contable --']]);
      echo '</div>';
      echo '<div class="col-sm-3" style="padding-left:10px">';
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul style='list-style:none;'>";
          echo "<li>".$this->Html->link(__('List Products'), ['action' => 'index'])."</li>";
          echo "<br/>";
          if ($bool_producttype_index_permission){
            echo "<li>".$this->Html->link(__('List Product Types'), ['controller' => 'product_types', 'action' => 'index'])."</li>";
          }
          if ($bool_producttype_add_permission){
            echo "<li>".$this->Html->link(__('New Product Type'), ['controller' => 'product_types', 'action' => 'add'])."</li>";
          }
        echo "</ul>";
      echo "</div>";  
    echo '</div>';    
    
     echo  "<div class='row'>";
      echo "<div class='col-sm-6'>";
        echo '<h3>Características</h3>';
        echo $this->Form->input('volume_ml_min',[
          'label'=>'Volumen min (ml)',
          'div'=>['class'=>'input number label50 raw pet'],
        ]);  
        echo $this->Form->input('volume_ml_max',[
          'label'=>'Volumen max (ml)',
          'div'=>['class'=>'input number label50 raw bottle pet'],
        ]);  
        echo $this->Form->input('weight_g',[
          'label'=>'Peso (g)',
          'div'=>['class'=>'input number label50 raw pet'],
        ]);  
        echo $this->Form->input('diameter_mm',[
          'label'=>'Diametro (mm)',
          'div'=>['class'=>'input number label50 cap'],
        ]);  
        echo $this->Form->input('width_inch',[
          'label'=>'Ancho (pulg)',
          'div'=>['class'=>'input number label50 roll bag'],
        ]);
        echo $this->Form->input('length_inch',[
          'label'=>'Largo (pulg)',
          'default'=>0,
          'div'=>['class'=>'input number label50 bag'],
        ]); 
        echo $this->Form->input('height_inch',[
          'label'=>'Alto (pulg)',
          'default'=>0,
          'div'=>['class'=>'input number label50 bag'],
        ]);                 
      echo '</div>';   
      echo "<div class='col-sm-6'>";
        echo '<h3>'.__('Warehouses').'</h3>';
        foreach ($warehouses as $warehouseId=>$warehouseName){
          echo $this->Form->input('Warehouse.'.$warehouseId.'.bool_assigned',[
            'label'=>$warehouseName,
            'type'=>'checkbox',
            'checked'=>in_array($warehouseId,array_keys($productWarehouses)),
            'div'=>['class'=>'div input checkboxleftbig'],
          ]);
        }
      echo '</div>';    
      if($isingroup){	//si es producto ingroup mostramos lista de preformas.  
	  echo "<div class='col-sm-6'>";
        echo '<h3>'.__('Transferible a ').'</h3>';
        foreach ($productspref as $productId=>$productName){
          echo $this->Form->input('Product.'.$productId.'.transferabled',[
            'label'=>$productName,
            'type'=>'checkbox',
            //'checked'=>in_array($productId,array_keys($productPreform)),
            'div'=>['class'=>'div input checkboxleftbig'],
          ]);
        }
      echo '</div>';      
    echo "</div>";
    }	  
    echo "</div>";
    
    echo '<div class="row bottle production pet injection hidden">';
      echo '<h2 style="width:100%">Parámetros de Producción</h2>';
      echo "<div class='col-sm-6 production pet injection' >";
        echo '<h3>Producción</h3>';
        //pr($this->request->data['ProductProduction']);
        echo $this->Form->input('ProductProduction.acceptable_production',[
          'label'=>'Producción Aceptable',
          'required'=>false,
          'default'=>(empty($this->request->data['ProductProduction'])?0:$this->request->data['ProductProduction'][0]['acceptable_production']),
          'div'=>['class'=>'input production pet injection hidden'],
        ]);
        echo $this->Form->input('bag_product_id',[
          'label'=>'Bolsa',
          'default'=>'0',
          'empty'=>['0'=>'Seleccione la bolsa'],
          'div'=>['class'=>'select production pet injection hidden']]);
        
        echo $this->Form->input('preferred_raw_material_id',[
          'label'=>'Preforma preferido',
          'default'=>'0',
          'empty'=>['0'=>'Seleccione la materia prima por defecto'],
          'div'=>['class'=>'select production pet hidden'],
        ]);
        echo $this->Form->input('preferred_weight_g',[
          'label'=>'Peso preferido',
          'default'=>'0',
          'empty'=>['0'=>'-- Peso preferido en gr --'],
          'div'=>['class'=>'input production pet hidden'],
        ]);
        echo '<div class="production injection">';
          echo '<a href="#recipeModal" role="button" class="btn btn-large btn-primary" data-toggle="modal">Añadir Receta</a>';
          echo '<div id="recipeData">';
          echo '</div>';
        echo '</div>';
      echo '</div>'; 
      /*
      //pr($productProductionTypes);
      echo "<div class='col-sm-3'>";
        echo '<h3>Tipo de Producción</h3>';
        foreach ($productionTypes as $productionTypeId=>$productionTypeName){
          echo $this->Form->input('ProductionType.'.$productionTypeId.'.bool_assigned',[
            'label'=>$productionTypeName,
            'type'=>'checkbox',
            'checked'=>in_array($productionTypeId,array_keys($productProductionTypes)),
            'div'=>['class'=>'div input checkboxleftbig'],
          ]);
        }
      echo '</div>'; 
      */
      echo '<div class="col-sm-6 production pet injection">';
        echo '<h3>Máquinas</h3>';
        foreach ($machines as $machineId=>$machineName){
          echo $this->Form->input('MachineProduct.'.$machineId.'.bool_assigned',[
            'label'=>$machineName,
            'type'=>'checkbox',
            'checked'=>in_array($machineId,array_keys($productMachines)),
            'div'=>['class'=>'div input checkboxleftbig'],
          ]);
        }
        
      echo '</div>'; 
    echo '</div>'; 
    
    echo  '<div class="row"  style="padding-left:20px;background-color:#eeeeee">';
      echo '<div class="col-sm-6 cost" style="padding-left:10px">';
        echo '<h3>Costo</h3>';
        echo '<div class="row" style="margin:0">';
          echo "<div class='col-sm-8'>";
            echo $this->Form->input('default_cost',[
              'label'=>'Costo preestablecido',
              'default'=>0,
              'div'=>['class'=>'input number label50'],
            ]);  
          echo "</div>";
          echo "<div class='col-sm-4'>";
            echo $this->Form->input('default_cost_currency_id',['label'=>false]);  
          echo "</div>";
        echo "</div>";
        echo  "<p class='comment'>El costo preestablecido estará utilizado en las ordenes de compra  si la moneda corresponde.</p>";
      echo '</div>';    
      echo '<div class="col-sm-6 price pet injection hidden">';
        echo '<h3>Precio</h3>';
         foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
          echo '<div class="row"  style="margin:0">';
            echo '<div class="col-sm-8">';
              echo $this->Form->input('PriceClientCategory.'.$priceClientCategoryId.'.category_price',['label'=>'Precio venta '.$priceClientCategoryName,'type'=>'decimal','default'=>$productPrices[$priceClientCategoryId],'div'=>['class'=>'input number label50']]);  
            
            echo "</div>";
            echo "<div class='priceCurrency' class='col-sm-4'>";
              echo $this->Form->input('PriceClientCategory.'.$priceClientCategoryId.'.default_price_currency_id',['label'=>false, 'value'=>CURRENCY_CS,'readonly'=>true]);  
            echo "</div>";
          echo "</div>";
        }
        echo  "<p class='comment'>El precio de venta preestablecido estará utilizado en ventas y remisiones.  En Configuración -> Precios de Productos se pueden registrar precios diferenciales para clientes específicos.</p>";
        echo $this->Form->input('threshold_volume',['label'=>'Volumen Venta','default'=>($productThresholdVolume>0?$productThresholdVolume:100000),'div'=>['class'=>'input number label50']]);  
        
      echo '</div>'; 
    echo '</div>';  
  echo "</div>";      
  
  echo '</fieldset>';
  echo $this->Form->Submit(__('Submit')); 
  echo $this->Form->end(); 
  
  
  echo '<div id="recipeModal" class="modal fade">';
		echo '<div class="modal-dialog" style="width:80%!important;max-width:800px!important;">';
			echo '<div class="modal-content">';
				//echo '<div class="modal-header">';
				//echo '</div>';
        echo '<div class="modal-body">';
          echo $this->Form->create('Recipe'); 
          echo '<legend>'.__('Add Recipe').'</legend>';
          echo '<fieldset>';
            //echo $this->Form->input('product_id',[
            //  'options'=>$injectionProducts,
            //  'empty'=>[0=>'-- Producto --']
            //]);
            echo $this->Form->input('name',['label'=>'Nombre receta','required'=>false]);
            echo $this->Form->input('description',['label'=>'Descripción receta']);
            echo $this->Form->input('mill_conversion_product_id',[
              'label'=>'Conversión molina',
              'options'=>$injectionRawMaterials,
            ]);
            
            $tableHead='';
            $tableHead.='<thead>';
              $tableHead.='<tr>';
                $tableHead.='<th>Materia Prima</th>';
                $tableHead.='<th class="centered">Cantidad</th>';
                $tableHead.='<th>Unidad</th>';
                $tableHead.='<th></th>';
              $tableHead.='</tr>';
            $tableHead.='</thead>';
            $tableRows='';
            $i=0;
            for ($i=0;$i<RECIPE_INGREDIENTS_MAX;$i++){
              $tableRow='';
              if ($i === 0){
                $tableRow.='<tr>';
              }
              else {
                $tableRow.='<tr class="d-none">';
              }
                $tableRow.='<td class="rawmaterial">'.$this->Form->input('RecipeIngredient.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$injectionRawMaterials,
                    'default'=>0,
                    'empty'=>[0=>'-- Ingrediente --'],
                    'style'=>'width:100%;font-size:0.95em;',
                  ]).'</td>';
                  $tableRow.='<td class="ingredientQuantity centered">'.$this->Form->input('RecipeIngredient.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>0,
                    
                  ]).'</span></td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeIngredient.'.$i.'.raw_material_unit_id',[
                    'label'=>false,
                    'default'=>0,
                    'options'=>$rawMaterialUnits,
                    'empty'=>[0=>'-- Unidad --'],
                  ]).'</td>';
                $tableRow.='<td><button type="button" class="removeIngredient">Remover Ingrediente</button></td>';  
                $tableRow.='</tr>';
              $tableRows.=$tableRow;
            }
              
            $totalRow='';
            $totalRow.='<tr class="totalrow">';
              $totalRow.='<td>Total</td>';
              $totalRow.='<td class="totalIngredientQuantity centered"><span class="amount">0</span></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
            $totalRow.='</tr>';
            
            $tableBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
            $ingredientsTable='<table id="recipeIngredients">'.$tableHead.$tableBody.'</table>';
            
            echo '<h2>Ingredientes de la receta</h2>';
              
            echo $ingredientsTable;
            echo '<button  type="button" class="addIngredient">Otro Ingrediente</button>';
            
            $tableHead='';
            $tableHead.='<thead>';
              $tableHead.='<tr>';
                $tableHead.='<th>Consumible</th>';
                $tableHead.='<th class="centered">Cantidad</th>';
                $tableHead.='<th>Unidad</th>';
                $tableHead.='<th></th>';
              $tableHead.='</tr>';
            $tableHead.='</thead>';
            $tableRows='';
            $i=0;
            for ($i=0;$i<RECIPE_CONSUMABLES_MAX;$i++){
              $tableRow='';
              if ($i === 0){
                $tableRow.='<tr>';
              }
              else {
                $tableRow.='<tr class="d-none">';
              }
                $tableRow.='<td class="consumable">'.$this->Form->input('RecipeConsumable.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$injectionConsumables,
                    'default'=>0,
                    'empty'=>[0=>'-- Consumible --'],
                    'style'=>'width:100%;font-size:0.95em;',
                  ]).'</td>';
                  $tableRow.='<td class="consumableQuantity centered">'.$this->Form->input('RecipeConsumable.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>0,
                  ]).'</span></td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeConsumable.'.$i.'.consumable_unit_id',[
                    'label'=>false,
                    'default'=>1,
                    'options'=>$consumableUnits,
                  ]).'</td>';
                $tableRow.='<td><button type="button" class="removeConsumable">Remover Consumible</button></td>';  
                $tableRow.='</tr>';
              $tableRows.=$tableRow;
            }
              
            $totalRow='';
            $totalRow.='<tr class="totalrow">';
              $totalRow.='<td>Total</td>';
              $totalRow.='<td class="totalConsumableQuantity centered"><span class="amount">0</span></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
            $totalRow.='</tr>';
            
            $tableBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
            $consumablesTable='<table id="recipeConsumables">'.$tableHead.$tableBody.'</table>';
            
            echo '<h2>Consumibles de la receta</h2>';
              
            echo $consumablesTable;
            echo '<button  type="button" class="addConsumable">Otro Consumible</button>';
            
            //echo $this->Form->Submit('Guardar',['class'=>'save','name'=>'save2']);
            
            echo '</fieldset>';
            echo $this->Form->end();
          echo '</div>';
          echo '<div id="priceModalFooter" class="modal-footer">';
            echo '<button id="closeRecipeModal" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
            echo '<button id="saveRecipe" type="button" class="btn btn-default">Guardar Receta</button>';
          echo '</div>';
				echo '</div>';
      echo '</div>'; 
    echo '</div>'; 
  echo '</div>';    
?>
</div>
