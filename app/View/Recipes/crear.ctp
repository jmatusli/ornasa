<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
  var jsProducts=<?php  echo json_encode(array_keys($products)); ?>;
  /* var jsProductProductionTypes=<?php //echo json_encode($productProductionTypes); ?>; */
  var jsRawMaterials=<?php  echo json_encode(array_keys($rawMaterials)); ?>;
  /* var jsRawMaterialProductionTypes=<?php  //echo json_encode($rawMaterialProductionTypes); ?>; */
  var jsRawMaterialUnits=<?php  echo json_encode($rawMaterialUnits); ?>;
  
  var jsConsumables=<?php  echo json_encode(array_keys($consumables)); ?>;
  var jsConsumableUnits=<?php  echo json_encode($consumableUnits); ?>;

  $('body').on('change','#RecipeWarehouseId',function(){
		$('#refresh').trigger('change')
	});	
/*  
  $('body').on('change','#RecipeProductId',function(){	
    var productId=parseInt($(this).val())
    //var previousProductionTypeId=$('#RecipeProductionTypeId').val()
    /var newProductionTypeId=0
    
    //if (productId > 0 && $.inArray(productId,jsProducts) > -1){
    //  newProductionTypeId=jsProductProductionTypes[productId]
    //}
    
    //if (previousProductionTypeId != newProductionTypeId){
    //  $('#RecipeProductionTypeId').removeClass('fixed')
    //  $('#RecipeProductionTypeId').val(newProductionTypeId)
    //  $('#RecipeProductionTypeId').addClass('fixed')
    //  
    //  
    //  $('#RecipeProductionTypeId').trigger('change');
    //}
  });
*/  

  function displayRecipeContainer(){
    if ($('#RecipeProductionTypeId').val() == 0){
      $('#noproductiontypeid').removeClass('d-none');
      $('#recipeItemContainer').addClass('d-none');
      
      $('td.rawmaterial div select option').removeClass('d-none');
      $('td.rawmaterial div select').val(0);
      $('td.consumable div select option').removeClass('d-none');
      $('td.consumable div select').val(0);
      $('td.quantity div input').val(0);
      $('td.unit div select').val(0);
      $('td.totalquantity div input').val(0);
    }
    else {
      $('#noproductiontypeid').addClass('d-none');
      $('#recipeItemContainer').removeClass('d-none');
    }
  }
  $('body').on('change','#RecipeProductionTypeId',function(){	
    var productionTypeId=$(this).children("option:selected").val()
    if (parseInt(productionTypeId) > 0){
      $('#selectProductionType').trigger('click');
    }
  });
  
  $('body').on('change','.rawmaterial div select',function(){	
    $(this).closest('tr').find('td.unit div select').val(jsRawMaterialUnits[$(this).children("option:selected").val()])
	});
  
  $('body').on('change','#recipeItems .quantity div input',function(){	
    calculateIngredientTotal();
	});
  $('body').on('click','.addIngredient',function(){	
		var tableRow=$('#recipeItems tbody tr.d-none:first');
		tableRow.removeClass("d-none");
	});
	$('body').on('click','.removeIngredient',function(){	
		var tableRow=$(this).closest('tr').remove();
		calculateIngredientTotal();
	});	
  
  function calculateIngredientTotal(){
    var totalQuantity=0;
    var currentQuantity=0;
    $('#recipeItems td.quantity div input').each(function(){
      currentQuantity=parseInt($(this).val());
      if (isNaN(currentQuantity) || currentQuantity <0){
        alert('Se debe registrar una cantidad numérica positiva')
        $(this).val(0)
      }
      else {
        totalQuantity += currentQuantity
      }
    });
    $('#recipeItems td.totalquantity span.amount').text(totalQuantity)
  }
  
  $('body').on('change','.consumable div select',function(){	
    $(this).closest('tr').find('td.unit div select').val(jsConsumableUnits[$(this).children("option:selected").val()])
	});
  
  $('body').on('change','recipeConsumables .quantity div input',function(){	
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
    $('#recipeConsumables td.quantity div input').each(function(){
      currentQuantity=parseInt($(this).val());
      if (isNaN(currentQuantity) || currentQuantity <0){
        alert('Se debe registrar una cantidad numérica positiva')
        $(this).val(0)
      }
      else {
        totalQuantity += currentQuantity
      }
    });
    $('#recipeConsumables td.totalquantity span.amount').text(totalQuantity)
  }
  
  $(document).ready(function(){
    /*if ($('#RecipeProductId').val() > 0){
      $('#RecipeProductId').trigger('change')
    }
    else {
      $('#RecipeProductionTypeId').trigger('change')
    }
    */
    
    displayRecipeContainer();
    
    $('select.fixed option:not(:selected)').attr('disabled', true);
	
    $('#saving').addClass('d-none');
	});

  $('body').on('click','.save',function(e){	
    $(".save").data('clicked', true);
  });
  
  $('body').on('submit','#RecipeCrearForm',function(e){	
    if($(".save").data('clicked')){
      $('.save').attr('disabled', 'disabled');
      $("#RecipeCrearForm").fadeOut();
      $("#saving").removeClass('d-none');
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
<div class="recipes form fullwidth">
<?php
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la receta...</p>";
    echo "</div>";
  echo "</div>";

	echo $this->Form->create('Recipe'); 
  echo '<legend>'.__('Add Recipe').'</legend>';
	echo '<fieldset>';
    echo '<div class="container-fluid">';
      echo '<div class="row">';
        echo '<div class="col-sm-9">';      
          echo $this->Form->input('production_type_id',[
            'value'=>$productionTypeId,
            'empty'=>[0=>'-- Tipo de Producción --'],
          ]);
          echo $this->Form->Submit('Productos para tipo de producción',['id'=>'selectProductionType','name'=>'selectProductionType','style'=>'min-width:400px;']);
          
          echo $this->Form->input('product_id',['empty'=>[0=>'-- Producto --']]);
          echo $this->Form->input('name',['label'=>'Nombre receta','required'=>false]);
          echo $this->Form->input('description',['label'=>'Descripción receta']);
          echo $this->Form->input('mill_conversion_product_id',['empty'=>[0=>'-- Producto Conversión Molina --']]);
        echo '</div>';
        echo '<div class="col-sm-3" style="pading-left:10px;">';
          echo '<h3>'.__('Actions').'</h3>';
          echo '<ul style="list-style:none;">';
            echo '<li>'.$this->Html->link(__('List Recipes'), ['action' => 'resumen']).'</li>';
          echo '</ul>';
        echo '</div>';
      echo '</div>';
      echo '<div class="row">';
        echo '<div class="col-sm-12">';      
          echo '<div id="noproductiontypeid">';      
            echo '<h2>Se debe seleccionar un tipo de producción</h2>';
          echo '</div>';
          echo '<div id="recipeItemContainer">';      
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
            if (!empty($requestItems)){
              foreach ($requestItems as $requestItem){
                $tableRow='';
                $tableRow.='<tr>';
                  $tableRow.='<td class="rawmaterial">'.$this->Form->input('RecipeItem.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$rawMaterials,
                    'default'=>$requestItem['product_id'],
                    'empty'=>[0=>'-- Ingrediente --'],
                  ]).'</td>';
                  $tableRow.='<td class="quantity centered">'.$this->Form->input('RecipeItem.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>$requestItem['quantity'],
                  ]).'</td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeItem.'.$i.'.unit_id',[
                    'label'=>false, 
                    'default'=>$requestItem['unit_id'],
                    'empty'=>[0=>'-- Unidad --']
                  ]).'</td>';
                $tableRow.='<td><button class="removeIngredient">Remover Ingrediente</button></td>';  
                $tableRow.='</tr>';
                $tableRows.=$tableRow;
                
                $i++;
              }
            }
            for ($i=count($requestItems);$i<RECIPE_INGREDIENTS_MAX;$i++){
              $tableRow='';
              if ($i==count($requestItems)){
                $tableRow.='<tr>';
              }
              else {
                $tableRow.='<tr class="d-none">';
              }
                $tableRow.='<td class="rawmaterial">'.$this->Form->input('RecipeItem.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$rawMaterials,
                    'default'=>0,
                    'empty'=>[0=>'-- Ingrediente --'],
                  ]).'</td>';
                  $tableRow.='<td class="quantity centered">'.$this->Form->input('RecipeItem.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>0,
                  ]).'</span></td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeItem.'.$i.'.unit_id',[
                    'label'=>false,
                    'default'=>0,
                    'empty'=>[0=>'-- Unidad --'],
                  ]).'</td>';
                $tableRow.='<td><button type="button" class="removeIngredient">Remover Ingrediente</button></td>';  
                $tableRow.='</tr>';
              $tableRows.=$tableRow;
            }
            
            $totalRow='';
            $totalRow.='<tr class="totalrow">';
              $totalRow.='<td>Total</td>';
              $totalRow.='<td class="totalquantity"><span class="amount right">0</span></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
            $totalRow.='</tr>';
            
            $tableBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
            $ingredientsTable='<table id="recipeItems">'.$tableHead.$tableBody.'</table>';
            
            echo '<h2>Ingredientes de la receta</h2>';
            echo $this->Form->Submit('Guardar',['class'=>'save','name'=>'save1']);

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
            if (!empty($requestConsumables)){
              foreach ($requestConsumables as $requestConsumable){
                $tableRow='';
                $tableRow.='<tr>';
                  $tableRow.='<td class="consumable">'.$this->Form->input('RecipeConsumable.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$consumables,
                    'default'=>$requestConsumable['product_id'],
                    'empty'=>[0=>'-- Consumible --'],
                  ]).'</td>';
                  $tableRow.='<td class="quantity centered">'.$this->Form->input('RecipeConsumable.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>$requestConsumable['quantity'],
                  ]).'</td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeConsumable.'.$i.'.unit_id',[
                    'label'=>false, 
                    'default'=>$requestConsumable['unit_id'],
                  ]).'</td>';
                $tableRow.='<td><button class="removeConsumable">Remover Consumible</button></td>';  
                $tableRow.='</tr>';
                $tableRows.=$tableRow;
                
                $i++;
              }
            }
            for ($i=count($requestConsumables);$i<RECIPE_CONSUMABLES_MAX;$i++){
              $tableRow='';
              if ($i==count($requestConsumables)){
                $tableRow.='<tr>';
              }
              else {
                $tableRow.='<tr class="d-none">';
              }
                $tableRow.='<td class="consumable">'.$this->Form->input('RecipeConsumable.'.$i.'.product_id',[
                    'label'=>false,
                    'options'=>$consumables,
                    'default'=>0,
                    'empty'=>[0=>'-- Consumible --'],
                  ]).'</td>';
                  $tableRow.='<td class="quantity centered">'.$this->Form->input('RecipeConsumable.'.$i.'.quantity',[
                    'label'=>false,
                    'type'=>'decimal',
                    'default'=>0,
                  ]).'</span></td>';
                  $tableRow.='<td class="unit">'.$this->Form->input('RecipeConsumable.'.$i.'.unit_id',[
                    'label'=>false,
                    'default'=>1,
                  ]).'</td>';
                $tableRow.='<td><button type="button" class="removeConsumable">Remover Consumible</button></td>';  
                $tableRow.='</tr>';
              $tableRows.=$tableRow;
            }
            
            $totalRow='';
            $totalRow.='<tr class="totalrow">';
              $totalRow.='<td>Total</td>';
              $totalRow.='<td class="totalquantity"><span class="amount right">0</span></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
            $totalRow.='</tr>';
            
            $tableBody='<tbody>'.$tableRows.$totalRow.'</tbody>';
            $consumablesTable='<table id="recipeConsumables">'.$tableHead.$tableBody.'</table>';
            
            echo '<h2>Consumibles de la receta</h2>';
            echo $this->Form->Submit('Guardar',['class'=>'save','name'=>'save2']);

            echo $consumablesTable;
            echo '<button  type="button" class="addConsumable">Otro Consumible</button>';
            
            echo $this->Form->Submit('Guardar',['class'=>'save','name'=>'save3']);

          echo '</div>';
          
        echo '</div>';
      echo '</div>';
    echo '</div>';    
	echo '</fieldset>';
  
	echo $this->Form->end();
?>
</div>