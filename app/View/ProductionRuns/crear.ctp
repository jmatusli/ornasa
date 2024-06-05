<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
  function roundToEight(num) {    
    return +(Math.round(num + "e+8")  + "e-8");
  }    

  var jsProductMachines=<?php  echo json_encode($productMachines); ?>;
  
  var jsFinishedProducts=<?php  echo json_encode(array_keys($finishedProducts)); ?>;
  
  var jsProductBags=<?php  echo json_encode($productBags); ?>;
  var jsProductPackagingUnits=<?php  echo json_encode($productPackagingUnits);  ?>;
  
  var jsProductPreferredRawMaterials=<?php  echo json_encode($productPreferredRawMaterials); ?>;
  var jsProductRecipes=<?php  echo json_encode($productRecipes); ?>;
  
  var jsRecipeMillConversionProducts=<?php echo json_encode($recipeMillConversionProducts); ?>;
  var jsRawMaterialUnits=<?php echo json_encode($rawMaterialUnits); ?>;
  
  var jsRawmaterialStockItems=<?php echo json_encode($rawMaterialStockItems); ?>;
  var jsConsumableStockItems=<?php echo json_encode($consumableStockItems); ?>;
  
  $('body').on('change','#ProductionRunPlantId',function(){
		$('#refresh').trigger('click')
	});	

	$('body').on('change','#ProductionRunBoolAnnulled',function(){
		if ($(this).is(':checked')){
			$('.parameters').addClass('hidden');
		}
		else {
			$('.parameters').removeClass('hidden');
		}
	});
	
  $('body').on('change','#ProductionRunProductionTypeId',function(){
    var productionTypeId=$(this).val();
		$('#finishedProducts div.ingredients').each(function(){
      if ($(this).attr('productiontypeid') == productionTypeId){
        $(this).removeClass('d-none');
      }
      else {
        $(this).addClass('d-none');
      }
    });
	});	
  
	$('body').on('change','#ProductionRunFinishedProductId',function(){
    //the chain called is setMachine, setBagData, setRawMaterialData
    setMachine();
    setBagData();
    setRawMaterialData();
	});	
  function setMachine(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    $('#ProductionRunMachineId option').each(function(){
      var machineId=parseInt($(this).val())    
      var machineRelevantForProduct=jsProductMachines[productId].indexOf(machineId)
      if (typeof jsProductMachines[productId] != 'undefined' &&  machineRelevantForProduct >= 0){
        $(this).removeClass('d-none')
        if (jsProductMachines[productId].length == 1){
          $(this).closest('select').val(jsProductMachines[productId][0])
        }
        else {
          $(this).closest('select').val(0)
        }  
      }
      else {
        $(this).addClass('d-none')
      }
    });
  }

  function setBagData(){
    var productId=parseInt($('#ProductionRunFinishedProductId').val());
    $('#ProductionRunBagProductId').val(productId == 0?0:jsProductBags[productId]);
    $('#ProductionRunProductPackagingUnit').val(productId == 0?1:jsProductPackagingUnits[productId]);
    
    calculateBagQuantity();
  }
  
  function calculateBagQuantity(){
    var productId=parseInt($('#ProductionRunFinishedProductId').val());
    var packagingUnit=parseInt($('#ProductionRunProductPackagingUnit').val());
    var bagQuantity=0;
    if (productId > 0){  
      if (packagingUnit <=0){
        $('#bagQuantityMessage').text('No hay unidad de empaque registrada para el producto seleccionado.');
        $('#bagQuantityMessage').addClass('error');
        $('#bagQuantityMessage').removeClass('hidden');
        $('#ProductionRunBagQuantity').val(0);
      }
      else {
        $('#bagQuantityMessage').text('');
        $('#bagQuantityMessage').removeClass('error');
        $('#bagQuantityMessage').addClass('hidden');
          
        if (<?php echo ($productionTypeId === intval(PRODUCTION_TYPE_PET)?1:0) ?> === 1){
          var quantityA=parseInt($('#StockItems1').val());
          var quantityB=parseInt($('#StockItems2').val());
       
          bagQuantity += Math.ceil(quantityA/packagingUnit);
          bagQuantity += Math.ceil(quantityB/packagingUnit);
        }
        else {
          var finishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val());
          //var finishedProductQuantity=$('#StockItems1').val();
             
          bagQuantity = Math.ceil(finishedProductQuantity/packagingUnit);
        }
        $('#ProductionRunBagQuantityTarget').val(bagQuantity);
        $('#ProductionRunBagQuantity').val(bagQuantity);
      }
    }  
    else {
      $('#bagQuantityMessage').text('');
      $('#bagQuantityMessage').removeClass('error');
      $('#ProductionRunBagQuantity').val(0);
    }
  }
  
  function setRawMaterialData(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    if (productId == 0){
      $('#ProductionRunRecipeId option').each(function(){
        $(this).addClass('d-none')
      });
      $('#productionIngredients').empty();
      $('#productionConsumables').empty();
    }
    else {
      var productionTypeId=$('#ProductionRunProductionTypeId').val();
      if (productionTypeId > 0){
        if (productionTypeId == <?php echo PRODUCTION_TYPE_PET; ?>){
          setRawMaterial();
        }
        else {
          displayRelevantRecipes()
          $('#ProductionRunRecipeId').val((typeof jsProductRecipes[productId] == 'undefined'?0:jsProductRecipes[productId][0]))
          $('#ProductionRunRecipeId').trigger('change');
        }
      } 
    } 
  }
  function displayRelevantRecipes(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    $('#ProductionRunRecipeId option').each(function(){
      var recipeId=$(this).val()
      if (parseInt(recipeId) > 0){
        if (typeof jsProductRecipes[productId] == 'undefined'){
          $(this).addClass('d-none')
        }
        else {
          var recipeRelevantForProduct=jsProductRecipes[productId].indexOf(recipeId)
          if (recipeRelevantForProduct >= 0){
            $(this).removeClass('d-none')          
          }
          else {
            $(this).addClass('d-none')
          }
        }
      }            
    });          
  }
  
  function setRawMaterial(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    $('#ProductionRunRawMaterialId').val(productId == 0?0:jsProductPreferredRawMaterials[productId]);
  }
  
  $('body').on('change','#ProductionRunRecipeId',function(){
    getRecipeMillConversionProduct();
    getRecipeIngredients()
  });  
  function getRecipeMillConversionProduct(){
    var recipeId=$('#ProductionRunRecipeId').val();
    $('#ProductionRunMillConversionProductId').val(jsRecipeMillConversionProducts[recipeId])
    $('#ProductionRunMillConversionUnitId').val(jsRawMaterialUnits[jsRecipeMillConversionProducts[recipeId]])
  }
  function getRecipeIngredients(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    var recipeId=$('#ProductionRunRecipeId').val();
    var finishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val()) 
    if (recipeId > 0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>recipes/getRecipeIngredients/',
        data:{"recipeId":recipeId,"finishedProductQuantity":finishedProductQuantity},
        cache: false,
        type: 'POST',
        success: function (recipeIngredients) {
          $('#productionIngredients').html(recipeIngredients);
          getRecipeConsumables();
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
    }
    else {
      $('#productionIngredients').empty();
    }
  }
  function getRecipeConsumables(){
    var productId=parseInt($('#ProductionRunFinishedProductId').children("option").filter(":selected").val());
    var recipeId=$('#ProductionRunRecipeId').val();
    var finishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val()) 
    if (recipeId > 0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>recipes/getRecipeConsumables/',
        data:{"recipeId":recipeId,"finishedProductQuantity":finishedProductQuantity},
        cache: false,
        type: 'POST',
        success: function (recipeConsumables) {
          $('#productionConsumables').html(recipeConsumables);
          showUnitCostDetail();
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
    }
    else {
      $('#productionIngredients').empty();
    }
  }
  
  $('body').on('change','#ProductionRunFinishedProductQuantity',function(){
    // DOES NOT EXIST FOR PET!
    var totalFinishedProductQuantity=$('#ProductionRunFinishedProductQuantity').val()
    /*
    var totalQuantityA=totalFinishedProductQuantity
    $('.finishedproduct').each(function(){
      if ($(this).attr('id') != 'StockItems1'){
        var quantityOtherQuality=parseInt($(this).val())
        if (!isNaN(quantityOtherQuality) && quantityOtherQuality > 0){
          totalQuantityA-=quantityOtherQuality;
        }
      }
    });
    $('#StockItems1').val(totalQuantityA)
    */
    // result of calling getRecipeIngredients
    if ($('#Ingredientes')){
      updateIngredientQuantities()
      updateConsumableQuantities()
    }
    else {
      getRecipeIngredients()
    }
    calculateBagQuantity();
    
  });
  
  function updateIngredientQuantities(){
    var totalFinishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val())
    
    $('tr.ingredientrow td.ingredientquantity input.unitquantity').each(function(){
      var unitQuantity=parseInt($(this).val())
      $(this).closest('td').find('div input.quantity').val(totalFinishedProductQuantity*unitQuantity)  
    });
    // 20211217 updateIngredientQuantities is only called on #ProductionRunFinishedProductQuantity change, and then showUnitCostDetail is called through updateConsumableQuantities later on
    //showUnitCostDetail();
  }	
  
  $('body').on('change','#Ingredientes tr.ingredientrow td.ingredientid div select',function(){
    showUnitCostDetail();
  });
  $('body').on('change','#Ingredientes tr.ingredientrow td.ingredientquantity div input',function(){
    showUnitCostDetail();
  });
  
  $('body').on('change','tr.consumablerow td.consumableproductid div select',function(){
    showUnitCostDetail();
  });
  $('body').on('change','tr.consumablerow td.consumableproductquantity div input',function(){
    showUnitCostDetail();
  });
 
  function updateConsumableQuantities(){
    var totalFinishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val())
    
    $('td.consumablequantity input.unitquantity').each(function(){
      var unitQuantity=parseInt($(this).val())
      $(this).closest('td').find('div input.quantity').val(totalFinishedProductQuantity*unitQuantity)  
    });
    showUnitCostDetail();
  }	
  $('body').on('change','.finishedproduct',function(){
		calculateTotal();
	});	
	
	function calculateTotal(){
		var materialUsed=0;
		$(".finishedproduct").each(function() {
			var productAmount = parseInt($(this).val());
			materialUsed = materialUsed + productAmount;
		});
    if ($('#ProductionRunProductionTypeId').val() == <?php echo PRODUCTION_TYPE_PET; ?>){
      $('#rawUsed').val(materialUsed);
    }
    else {
      $('#ProductionRunFinishedProductQuantity').val(materialUsed);
      $('#ProductionRunFinishedProductQuantity').trigger('change')
    }
    calculateBagQuantity();
	}
  
  $('body').on('change','#ProductionRunBagQuantity',function(){
    var bagQuantityRequested= $(this).val();
    var bagQuantityTarget=$('#ProductionRunBagQuantityTarget').val();
    if (bagQuantityTarget>0){
      // if there is no target, there should be no restrictions
      if (Math.abs(bagQuantityTarget-bagQuantityRequested)>1){
        $(this).val(bagQuantityTarget);
        $('#bagQuantityMessage').text('La cantidad de bolsas no puede tener más que una bolsa de diferencia de '+bagQuantityTarget+'.');
        $('#bagQuantityMessage').removeClass('error');
        $('#bagQuantityMessage').removeClass('hidden');
      }
      else {
        $('#bagQuantityMessage').text('');
        $('#bagQuantityMessage').removeClass('error');
        $('#bagQuantityMessage').addClass('hidden');
      }
    }
  });
  
	$('body').on('change','.consumablequantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
    var thisRow=$(this).closest('tr');
    calculateConsumableRow($(this).closest('tr').attr('row'));
		calculateConsumableTotal();
	});	
  
  function calculateConsumableRow(rowid) {    
		var currentrow=$('#otherConsumables').find("[row='" + rowid + "']");
		var quantity=parseInt(currentrow.find('td.consumablequantity div input').val());
	}
	
	function calculateConsumableTotal(){
		var totalConsumableQuantity=0;
		$("#otherConsumables tbody tr:not(.totalrow):not(.hidden)").each(function() {
			var currentConsumableQuantity = $(this).find('td.consumablequantity div input');
			if (!isNaN(currentConsumableQuantity.val())){
				var currentQuantity = parseInt(currentConsumableQuantity.val());
				totalConsumableQuantity += currentQuantity;
			}
		});
		$('#otherConsumables tbody tr.totalrow.total td.consumablequantity span').text(totalConsumableQuantity.toFixed(0));
		// 20211217 MOLINO DOES NOT INTERFERE WITH THE CALCULATION OF THE UNIT COST, CONSUMABLES DO
    showUnitCostDetail();
		return false;
	}
	
	$('body').on('click','.addConsumable',function(){
		var tableRow=$('#otherConsumables tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeConsumable',function(){
		var tableRow=$(this).closest('tr').remove();
		calculateConsumableTotal();
	});
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
  function formatNumbers(){
		$("td.amount span.amountright").each(function(){
			if (parseInt($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0);
		});
	}	
  
  function showUnitCostDetail(){
    var totalFinishedProductQuantity=parseInt($('#ProductionRunFinishedProductQuantity').val());
    
    var rawMaterialsSubtotal=0;
    var unitCostIngredientCounter=0;
    
    $('tr.ingredientrow').each(function(){
      var ingredientId=parseInt($(this).find('td.ingredientid select').val());
      var ingredientQuantity=parseInt($(this).find('td.ingredientquantity  input.quantity').val());
      
      var ingredientTotalCost=0;
      var remainingQuantity=ingredientQuantity;
      var stockItemCounter=0;
      while (remainingQuantity > 0){
        if (typeof jsRawmaterialStockItems[ingredientId] != 'undefined' 
          &&  typeof jsRawmaterialStockItems[ingredientId]['StockItems'][stockItemCounter] != 'undefined' ){
      
          var stockItem=jsRawmaterialStockItems[ingredientId]['StockItems'][stockItemCounter]['StockItem'];
          if (parseInt(stockItem['remaining_quantity']) <= remainingQuantity){
            ingredientTotalCost+=(parseInt(stockItem['remaining_quantity'])*parseFloat(stockItem['product_unit_price']))
            remainingQuantity-=parseInt(stockItem['remaining_quantity']);
          }
          else {
            ingredientTotalCost+=(remainingQuantity*parseFloat(stockItem['product_unit_price']))
            remainingQuantity=0;
          }
          stockItemCounter++;
        }
        else {
          remainingQuantity=0;
        }
      }
      $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"]').removeClass('d-none')
      $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredientid div select').val(ingredientId)
      $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredientquantity div input').val(ingredientQuantity)
      $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredientunitcost div input').val(ingredientTotalCost/ingredientQuantity)
      if (ingredientQuantity > 0){
        $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredientunitcost div input').val(ingredientTotalCost/ingredientQuantity)
      }
      else {
        $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredientunitcost div input').val(0)
      }
      $('table#unitCostTable tr.unitcostingredient[rownumber="'+unitCostIngredientCounter+'"] td.ingredienttotalcost div input').val(ingredientTotalCost)
      
      rawMaterialsSubtotal+=ingredientTotalCost;
      
      unitCostIngredientCounter++;
    });
    $('table#unitCostTable tr.unitcostingredient').each(function(){
      if ($(this).attr('rownumber') >= unitCostIngredientCounter){
        $(this).addClass('d-none')
      }
    });
    
    $('#UnitCostIngredientSubtotal').val(rawMaterialsSubtotal);

    // 20211218 TAKE INTO ACCOUNT CONSUMABLES FOR UNIT COST
    var consumablesSubtotal=0;
    var unitCostConsumableCounter=0;
    
    $('tr.consumablerow').each(function(){
      var consumableId=parseInt($(this).find('td.consumableid select').val());
      var consumableQuantity=parseInt($(this).find('td.consumablequantity input.quantity').val());
      
      if (consumableId > 0 && consumableQuantity > 0){
        var consumableTotalCost=0;
        var remainingConsumableQuantity=consumableQuantity;
        var stockItemCounter=0;
        while (remainingConsumableQuantity > 0){
          if (typeof jsConsumableStockItems[consumableId] != 'undefined' 
            &&  typeof jsConsumableStockItems[consumableId]['StockItems'][stockItemCounter] != 'undefined' ){
        
            var stockItem=jsConsumableStockItems[consumableId]['StockItems'][stockItemCounter]['StockItem'];
            if (parseInt(stockItem['remaining_quantity']) <= remainingConsumableQuantity){
              consumableTotalCost+=(parseInt(stockItem['remaining_quantity'])*parseFloat(stockItem['product_unit_price']))
              remainingConsumableQuantity-=parseInt(stockItem['remaining_quantity']);
            }
            else {
              consumableTotalCost+=(remainingConsumableQuantity*parseFloat(stockItem['product_unit_price']))
              remainingConsumableQuantity=0;
            }
            stockItemCounter++;
          }
          else {
            remainingConsumableQuantity=0;
          }
        }
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"]').removeClass('d-none')
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductid div select').val(consumableId)
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductquantity div input').val(consumableQuantity)
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductunitcost div input').val(consumableTotalCost/consumableQuantity)
        if (consumableQuantity > 0){
          $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductunitcost div input').val(roundToEight(consumableTotalCost/consumableQuantity))
        }
        else {
          $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductunitcost div input').val(0)
        }
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproducttotalcost div input').val(consumableTotalCost)
        
        consumablesSubtotal+=consumableTotalCost;
        
        unitCostConsumableCounter++;
      }
      
    });
    $('tr.extraconsumablerow').each(function(){
      var consumableId=parseInt($(this).find('td.consumableid select').val());
      var consumableQuantity=parseInt($(this).find('td.consumablequantity input').val());
      
      if (consumableId > 0 && consumableQuantity > 0){
        var consumableTotalCost=0;
        var remainingConsumableQuantity=consumableQuantity;
        var stockItemCounter=0;
        while (remainingConsumableQuantity > 0){
          if (typeof jsConsumableStockItems[consumableId] != 'undefined' 
            &&  typeof jsConsumableStockItems[consumableId]['StockItems'][stockItemCounter] != 'undefined' ){
        
            var stockItem=jsConsumableStockItems[consumableId]['StockItems'][stockItemCounter]['StockItem'];
            if (parseInt(stockItem['remaining_quantity']) <= remainingConsumableQuantity){
              consumableTotalCost+=(parseInt(stockItem['remaining_quantity'])*parseFloat(stockItem['product_unit_price']))
              remainingConsumableQuantity-=parseInt(stockItem['remaining_quantity']);
            }
            else {
              consumableTotalCost+=(remainingConsumableQuantity*parseFloat(stockItem['product_unit_price']))
              remainingConsumableQuantity=0;
            }
            stockItemCounter++;
          }
          else {
            remainingConsumableQuantity=0;
          }
        }
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"]').removeClass('d-none')
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductid div select').val(consumableId)
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductquantity div input').val(consumableQuantity)
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductunitcost div input').val(consumableTotalCost/consumableQuantity)
        if (consumableQuantity > 0){
          $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductcost div input').val(roundToEight(consumableTotalCost/consumableQuantity))
        }
        else {
          $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproductunitcost div input').val(0)
        }
        $('table#unitCostTable tr.unitcostconsumable[rownumber="'+unitCostConsumableCounter+'"] td.consumableproducttotalcost div input').val(consumableTotalCost)
        
        consumablesSubtotal+=consumableTotalCost;
        
        unitCostConsumableCounter++;
      }
      
    });
    $('table#unitCostTable tr.unitcostconsumable').each(function(){
      if ($(this).attr('rownumber') >= unitCostConsumableCounter){
        $(this).addClass('d-none')
      }
    });
    
    $('#UnitCostConsumableSubtotal').val(consumablesSubtotal)
    
    $('#UnitCostIngredientConsumableTotal').val(rawMaterialsSubtotal+consumablesSubtotal)
    
    $('#UnitCostFinishedQuantity').val(totalFinishedProductQuantity)
    $('#UnitCostUnitCost').val(roundToFour((rawMaterialsSubtotal+consumablesSubtotal)/totalFinishedProductQuantity))
  }
  
	$(document).ready(function(){
    formatNumbers();
		
		$('#ProductionRunProductionRunDateHour').val('08');
		$('#ProductionRunProductionRunDateMin').val('00');
		$('#ProductionRunProductionRunDateMeridian').val('am');
    
    $('#saving').addClass('hidden');
    
    $('#bagQuantityMessage').addClass('hidden');
    
    calculateConsumableTotal();
    
    if ($('#ProductionRunProductionTypeId').val() != <?php echo PRODUCTION_TYPE_PET; ?>){
      displayRelevantRecipes()
    }
    
    $('#ProductionRunProductionTypeId').trigger('change');
    
    
    //$('[data-toggle="tooltip"]').tooltip();
    
    getRecipeIngredients();
	});
  
  $('body').on('click','#submit',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#ProductionRunAddForm',function(e){	
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
<div class="productionRuns form fullwidth">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la orden de producción...</p>";
    echo "</div>";
  echo "</div>";
  
  echo "<div id='mainform'>";
    echo $this->Form->create('ProductionRun',['style'=>'margin-right:0!important;width:100%']); 
    echo '<legend>'.__('New Production Run').' '.($plantId == 0 ? '':('en planta '.$plants[$plantId].' ')).($productionTypeId == 0 ? '' : 'de producción tipo '.$productionTypes[$productionTypeId].' ').($plantId == 0?'':(' '.$newProductionRunCode)).'</legend>';
    echo "<fieldset>";
      echo '<div class="container-fluid">';
        echo '<div class="row">';
          echo '<div class="col-sm-9">';
            echo '<div class="row">';  
              echo '<div class="col-sm-8">';      
                echo $this->PlantFilter->displayPlantFilter($plants, $userRoleId,$plantId);              
                echo $this->Form->input('production_run_code',['value'=>$newProductionRunCode,'readonly'=>'readonly', 'style'=>'min-width:250px;font-size:16px;']);
                echo $this->Form->input('production_run_date',['dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>(date('Y')+1)]);
                echo $this->Form->Submit('Actualizar Planta y/o Fecha',['id'=>'refresh','name'=>'refresh','style'=>'min-width:300px;']);
              echo '</div>';
              echo '<div class="col-sm-4">';      
                if ($userRoleId==ROLE_FOREMAN){	
                  echo $this->Form->input('bool_verified',['label'=>'Verificada','checked'=>false,'type'=>'hidden']);
                }
                else {
                  echo $this->Form->input('bool_verified',['label'=>'Verificada','checked'=>true]);
                }
                if ($bool_annul_permission){
                  echo $this->Form->input('bool_annulled',['label'=>'Anulada','checked'=>false]);
                }
                else {
                  echo $this->Form->input('bool_annulled',['type'=>'hidden','checked'=>false]);
                }
                echo $this->Form->input('comment',['type'=>'textarea','rows'=>5]);
              echo '</div>';
            echo '</div>';
            echo '<div class="row parameters">';
            if ($plantId == 0){
              echo '<h2>Se debe seleccionar una planta</h2';
            }
            else {
              echo "<div class='col-sm-6'>";
                echo '<h2>'.__('Producto Fabricado').'</h2>';
                echo $this->Form->input('production_type_id',[
                  'default'=>PRODUCTION_TYPE_PET,
                  'class'=>'fixed',
                ]);
                echo $this->Form->input('finished_product_id',['default'=>'0','empty'=>['0'=>'-- Producto Fabricado --']]);
                echo $this->Form->input('incidence_id',['default'=>0,'empty'=>[0=>'No incidencias']]);
              echo "</div>";
              echo "<div class='col-sm-6'>";
                echo '<h2>'.__('Production Parameters').'</h2>';
                
                echo $this->Form->input('machine_id',['default'=>0,'empty'=>[0=>'-- Máquina --']]);
                echo $this->Form->input('operator_id');
                echo $this->Form->input('shift_id');
              echo "</div>";
            }
            echo '</div>';
            if ($plantId != 0){  
              echo '<div id="finishedProducts" class="row parameters">';
                echo '<h2 style="width:100%">'.__('Proceso Producción').'</h2>';      
                if (array_key_exists(PRODUCTION_TYPE_PET,$productionTypes)){
                  echo '<div class="col-sm-12 ingredients" productiontypeid="'.PRODUCTION_TYPE_PET.'">';
                    echo '<h3>'.$productionTypes[PRODUCTION_TYPE_PET].'</h3>';
                    echo $this->Form->input('raw_material_id',['default'=>'0','empty'=>['0'=>'Seleccione Materia Prima']]);
                    foreach ($productionResultCodes as $productionResultCodeId=>$resultCode) {
                      echo $this->Form->input(
                        'StockItems.'.$productionResultCodeId,
                        [
                          'label'=>'Calidad '.$resultCode,
                          'type'=>'number',
                          'default'=>0,
                          'class'=>'finishedproduct',
                        ]
                      );
                    }
                    echo $this->Form->input('raw_material_quantity',['readonly'=>'readonly','id'=>'rawUsed']);
                  echo '</div>';                
                }
                if (array_key_exists(PRODUCTION_TYPE_INJECTION,$productionTypes)){  
                  echo '<div class="ingredients col-sm-12" productiontypeid="'.PRODUCTION_TYPE_INJECTION.'">';
                    echo '<h3>'.$productionTypes[PRODUCTION_TYPE_INJECTION].'</h3>';
                    echo $this->Form->input('recipe_id',[
                      'default'=>'0',
                      'empty'=>['0'=>'-- Receta --'],
                    ]);
                    
                    
                    echo $this->Form->input('finished_product_quantity',[
                      'label'=>'Cantidad fabricado Calidad A',
                      'type'=>'number',
                      'default'=>'1',
                      'empty'=>['0'=>'-- Receta --'],
                    ]);
                    echo '<div class="row">';
                      echo '<div class="col-sm-7">';
                        echo $this->Form->input('mill_conversion_product_id',[
                          'label'=>'Molino',
                          'default'=>'0',
                          'empty'=>['0'=>'-- Prod Molino --'],
                          'options'=>$injectionRawMaterials,
                        ]);
                        echo '</div>'; 
                        echo '<div class="col-sm-3">';
                        echo $this->Form->input('mill_conversion_product_quantity',[
                          'label'=>false,
                          'type'=>'number',
                          'default'=>0,
                          'style'=>'width:100%!important',
                        ]);
                        echo '</div>'; 
                        echo '<div class="col-sm-2">';
                        echo $this->Form->input('mill_conversion_unit',[
                          'label'=>false,
                          'readonly'=>true,
                          'type'=>'text',
                          'default'=>'gr',
                          'style'=>'width:100%!important;text-align:center',
                        ]);
                        echo '</div>';
                      echo '</div>';  
                      echo '<div class="row">';
                      echo '<div class="col-sm-10">';
                        echo $this->Form->input('waste_quantity',[
                          'label'=>'Merma',
                          'type'=>'number',
                          'default'=>0,
                        ]);
                        echo '</div>'; 
                        echo '<div class="col-sm-2">';
                        echo $this->Form->input('waste_unit',[
                          'label'=>false,
                          'readonly'=>true,
                          'type'=>'text',
                          'default'=>'gr',
                          'style'=>'width:100%!important;text-align:center',
                        ]);
                        echo '</div>';
                      echo '</div>'; 
                  echo '</div>';
                }
                echo '<div class="col-sm-12">';
                  if (array_key_exists(PRODUCTION_TYPE_INJECTION,$productionTypes)){  
                    echo '<div id="productionIngredients" productiontypeid="'.PRODUCTION_TYPE_INJECTION.'">';
                    
                    echo '</div>';
                    echo '<div id="productionConsumables" productiontypeid="'.PRODUCTION_TYPE_INJECTION.'">';
                    
                    echo '</div>';
                    
                    echo '<h3>Costo Unitario de Producto</h3>';
                    echo '<table id="unitCostTable">';
                      echo '<tr>';
                        echo '<th>Item</th>';
                        echo '<th>Nombre</th>';
                        echo '<th>Cantidad</th>';
                        echo '<th>Costo Unitario</th>';
                        echo '<th>Costo Total</th>';
                      echo '</tr>';
                      for ($i=0;$i<5;$i++){
                        echo '<tr class="unitcostingredient" rownumber="'.$i.'">';
                          echo '<td style="text-align:center;">'.($i+1).'</td>';
                          echo '<td class="ingredientid">'.$this->Form->input('UnitCost.Ingredient.'.$i.'.ingredient_id',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'options'=>$injectionRawMaterials,
                            'empty'=>['0'=>'-- Ingrediente --'],
                            'style'=>'font-size:0.85em;width:100%;',
                          ]).'</td>';
                          echo '<td class="ingredientquantity">'.$this->Form->input('UnitCost.Ingredient.'.$i.'.ingredient_quantity',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                          echo '<td class="ingredientunitcost">'.$this->Form->input('UnitCost.Ingredient.'.$i.'.ingredient_unit_cost',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                          echo '<td class="ingredienttotalcost">'.$this->Form->input('UnitCost.Ingredient.'.$i.'.ingredient_total_cost',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                        echo '</tr>';
                      }
                      echo '<tr class="totalrow unitcostingredientsubtotal">';
                        echo '<td colspan=2>Subtotal costos ingredientes</td>';
                        echo '<td> </td>';
                        echo '<td> </td>';
                        echo '<td>'.$this->Form->input('UnitCost.ingredient_subtotal',[
                          'label'=>false,
                          'default'=>0,
                          'readonly'=>true,
                          'style'=>'width:100%;text-align:right;',
                        ]).'</td>';
                      echo '</tr>';
                      for ($i=0;$i< PRODUCTION_CONSUMABLES_MAX;$i++){
                        echo '<tr class="unitcostconsumable d-none" rownumber="'.$i.'">';
                          echo '<td style="text-align:center;">Consumbible '.($i+1).'</td>';
                          echo '<td class="consumableproductid">'.$this->Form->input('UnitCost.Consumable.'.$i.'.consumable_id',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'options'=>$consumables,
                            'empty'=>['0'=>'-- consumible --'],
                            'style'=>'font-size:0.85em;width:100%;',
                          ]).'</td>';
                          echo '<td class="consumableproductquantity">'.$this->Form->input('UnitCost.Consumable.'.$i.'.consumable_quantity',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                          echo '<td class="consumableproductunitcost">'.$this->Form->input('UnitCost.Consumable.'.$i.'.consumable_unit_cost',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                          echo '<td class="consumableproducttotalcost">'.$this->Form->input('UnitCost.Consumable.'.$i.'.consumable_total_cost',[
                            'label'=>false,
                            'default'=>0,
                            'readonly'=>true,
                            'style'=>'font-size:0.95em;width:100%;text-align:right;',
                          ]).'</td>';
                        echo '</tr>';
                      }  
                      echo '<tr class="totalrow unitcostconsumablesubtotal">';
                        echo '<td colspan=2>Subtotal costos ingredientes</td>';
                        echo '<td> </td>';
                        echo '<td> </td>';
                        echo '<td>'.$this->Form->input('UnitCost.consumable_subtotal',[
                          'label'=>false,
                          'default'=>0,
                          'readonly'=>true,
                          'style'=>'width:100%;text-align:right;',
                        ]).'</td>';
                      echo '</tr>';
                      echo '<tr class="totalrow">';
                        echo '<td colspan=2>Total costos</td>';
                        echo '<td> </td>';
                        echo '<td> </td>';
                        echo '<td>'.$this->Form->input('UnitCost.ingredient_consumable_total',[
                          'label'=>false,
                          'default'=>0,
                          'readonly'=>true,
                          'style'=>'width:100%;text-align:right;',
                        ]).'</td>';
                      echo '</tr>';
                      echo '<tr>';
                        echo '<td colspan=2>Cantidad fabricado</td>';
                        echo '<td> </td>';
                        echo '<td> </td>';
                        echo '<td>'.$this->Form->input('UnitCost.finished_quantity',[
                          'label'=>false,
                          'default'=>0,
                          'readonly'=>true,
                          'style'=>'width:100%;text-align:right;',
                        ]).'</td>';
                      echo '</tr>';
                      echo '<tr class="totalrow">';
                        echo '<td colspan=2>Costo Unitario</td>';
                        echo '<td> </td>';
                        echo '<td> </td>';
                        echo '<td>'.$this->Form->input('UnitCost.unit_cost',[
                          'label'=>false,
                          'default'=>0,
                          'readonly'=>true,
                          'style'=>'width:100%;text-align:right;',
                        ]).'</td>';
                      echo '</tr>';
                    echo '</table>';
                  }
                  echo '<h3>'.__('Bolsas').'</h3>';
                  echo $this->Form->input('bag_product_id',['label'=>'Bolsa utilizada','default'=>'0','options'=>$bags,'empty'=>[0=>'Seleccione Bolsa']]);
                  echo $this->Form->input('product_packaging_unit',['label'=>'Unidad Empaque','type'=>'number','default'=>1,'readonly'=>true]);
                  echo "<span id='bagQuantityMessage'></span>";
                  echo $this->Form->input('bag_quantity_target',['label'=>false,'default'=>'0','type'=>'hidden']);
                  echo $this->Form->input('bag_quantity',['label'=>'# bolsas','default'=>'0','type'=>'number']);              
                echo "</div>";
            }
            echo '</div>';
          echo '</div>';
          echo '<div class="actions col-sm-3" style="padding-left:20px;">';
            if (!empty($rawMaterialsInventory)){
              echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,$plantId,['Preformas en bodega','style'=>'width:100%!important;']); 
            }
            echo '<h3 style="width:100%">'.__('Actions')."</h3>";
            echo "<ul>";
              echo "<li>".$this->Html->link(__('List Production Runs'), ['action' => 'resumen'])."</li>";
              echo "<h3>".__('Configuration Options')."</h3>";
              if ($bool_operator_totalproductionreport_permission) {
                echo "<li>".$this->Html->link('Reporte Producción Total', ['controller' => 'operators', 'action' => 'reporteProduccionTotal'])." </li>";
                echo "<br/>";
              }
              if ($bool_machine_index_permission){
                echo "<li>".$this->Html->link(__('List Machines'), ['controller' => 'machines', 'action' => 'resumen'])." </li>";
              }
              if ($bool_machine_add_permission){
                echo "<li>".$this->Html->link(__('New Machine'), ['controller' => 'machines', 'action' => 'crear'])." </li>";
              }
              if ($bool_operator_index_permission){
                echo "<li>".$this->Html->link(__('List Operators'), ['controller' => 'operators', 'action' => 'index'])." </li>";
              }
              if ($bool_operator_add_permission){
                echo "<li>".$this->Html->link(__('New Operator'),['controller' => 'operators', 'action' => 'add'])." </li>";
              }
              if ($bool_shift_index_permission){
                echo "<li>".$this->Html->link(__('List Shifts'),['controller' => 'shifts', 'action' => 'index'])." </li>";
              }
              if ($bool_shift_add_permission){
                echo "<li>".$this->Html->link(__('New Shift'), ['controller' => 'shifts', 'action' => 'add'])." </li>";
              }
              
            echo "</ul>";      
          echo '</div>';
        echo '</div>';      

        if ($plantId > 0){        
          echo '<div class="row parameters">';	
            echo '<div class="col-sm-12">';
              echo "<h2>Otros suministros</h3>";
              echo "<table id='otherConsumables' style='font-size:13px;'>";
                echo "<thead>";
                  echo "<tr>";
                    echo "<th>Consumable</th>";
                    echo "<th>Cantidad</th>";
                    echo "<th>Acciones</th>";
                  echo "</tr>";
                echo "</thead>";
                echo "<tbody style='font-size:1rem;'>";
                $counter=0;
                for ($c=0;$c<count($requestConsumables);$c++){
                  echo '<tr class="extraconsumablerow" row="'.$c.'">';
                    echo "<td class='consumablematerialid'>";
                      echo $this->Form->input('Consumables.'.$c.'.consumable_id',['label'=>false,'value'=>$requestConsumables['Consumables'][$c]['consumable_id'],'empty'=>['0'=>'Seleccione Suministro']]);
                    echo "</td>";
                    echo "<td class='consumablequantity amount'>".$this->Form->input('Consumables.'.$c.'.consumable_quantity',['label'=>false,'type'=>'decimal','value'=>$requestConsumables['Consumables'][$c]['consumable_quantity'],'required'=>false,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
                    echo "<td>";
                        echo "<button class='removeConsumable' type='button'>".__('Remover Suministro')."</button>";
                        echo "<button class='addConsumable' type='button'>".__('Añadir Suministro')."</button>";
                    echo "</td>";
                  echo "</tr>";
                  $counter++;
                }
                for ($c=$counter;$c < PRODUCTION_CONSUMABLES_MAX;$c++){
                  if ($c==$counter){
                    echo '<tr class="extraconsumablerow" row="'.$c.'">';
                  }
                  else {
                    echo '<tr class="extraconsumablerow" row="'.$c.'" class="hidden">';
                  }
                    echo "<td class='consumableid'>";
                      echo $this->Form->input('Consumables.'.$c.'.consumable_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Suministro']]);
                    echo "</td>";
                    echo "<td class='consumablequantity amount'>".$this->Form->input('Consumables.'.$c.'.consumable_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
                    echo "<td>";
                        echo "<button class='removeConsumable' type='button'>".__('Remover Suministro')."</button>";
                        echo "<button class='addConsumable' type='button'>".__('Añadir Suministro')."</button>";
                    echo "</td>";
                  echo "</tr>";
                }
                  echo "<tr class='totalrow total' style='font-size:13px!important;'>";
                    echo "<td>Total</td>";
                    echo "<td class='consumablequantity amount right'><span>0</span></td>";
                    echo "<td></td>";
                  echo "</tr>";		
                echo "</tbody>";
              echo "</table>";
            echo "</div>";
          echo "</div>";
        }  
        echo '</div>';      
      echo "</fieldset>";
      if ($plantId > 0){
        echo $this->Form->Submit(__('Guardar Proceso de Producción'),['id'=>'submit','name'=>'submit']); 
      }
    echo $this->Form->end(); 
  echo "</div>";
?>
</div>
<?php
$millUnitCostDetail='';
$millUnitCostDetail.='<table id="MillUnitCostDetail">';
  $millUnitCostDetail.='<tr>';
    $millUnitCostDetail.='<th>Lote id</th>';
    $millUnitCostDetail.='<th>#</th>';
    $millUnitCostDetail.='<th>Costo Unitario</th>';
    $millUnitCostDetail.='<th>Costo Total</th>';
  $millUnitCostDetail.='</tr>';
  
  for ($i=0;$i<20;$i++){
    $millUnitCostDetail.='<tr>';
      $millUnitCostDetail.='<td>'.$this->Form->input('MillUnitCostDetail.'.$i.'.stock_item_id',[
        'label'=>false,
        'default'=>'',
        'readonly'=>true,
        'style'=>'font-size:0.75em;width:100%;',
      ]).'</td>';
      $millUnitCostDetail.='<td class="millStockItemQuantity">'.$this->Form->input('MillUnitCostDetail.'.$i.'.quantity',[
        'label'=>false,
        'default'=>0,
        'readonly'=>true,
        'style'=>'font-size:0.75em;width:100%;',
      ]).'</td>';
      $millUnitCostDetail.='<td class="millStockItemUnitCost">'.$this->Form->input('MillUnitCostDetail.'.$i.'.unit_cost',[
        'label'=>false,
        'default'=>0,
        'readonly'=>true,
        'style'=>'font-size:0.75em;width:100%;',
      ]).'</td>';
      $millUnitCostDetail.='<td class="millStockItemTotalCost">'.$this->Form->input('MillUnitCostDetail.'.$i.'.total_cost',[
        'label'=>false,
        'default'=>0,
        'readonly'=>true,
        'style'=>'font-size:0.75em;width:100%;',
      ]).'</td>';
    $millUnitCostDetail.='</tr>';
  }
  
  $millUnitCostDetail.='<tr class="totalrow">';
    $millUnitCostDetail.='<td colspan="2">Total lotes utilizados</td>';
    $millUnitCostDetail.='<td> </td>';
    $millUnitCostDetail.='<td class="subtotal">'.$this->Form->input('MillUnitCostDetail.subtotal',[
      'label'=>false,
      'default'=>0,
      'readonly'=>'true',
      'style'=>'font-size:0.75em;width:100%;',
    ]).'</td>';
  $millUnitCostDetail.='</tr>';
  $millUnitCostDetail.='<tr class="totalrow">';
    $millUnitCostDetail.='<td colspan="2">Total lotes utilizados</td>';
    $millUnitCostDetail.='<td> </td>';
    $millUnitCostDetail.='<td class="averageUnitCost">'.$this->Form->input('MillUnitCostDetail.average_unit_cost',[
      'label'=>false,
      'default'=>0,
      'readonly'=>true,
      'style'=>'font-size:0.75em;width:100%;',
    ]).'</td>';
  $millUnitCostDetail.='</tr>';
$millUnitCostDetail.='</table>';