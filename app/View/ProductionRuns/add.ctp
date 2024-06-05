<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
	$('body').on('change','.finishedproduct',function(){
		calculateTotal();
	});	

	$('body').on('change','#ProductionRunBoolAnnulled',function(){
		if ($(this).is(':checked')){
			$('.parameters').addClass('hidden');
		}
		else {
			$('.parameters').removeClass('hidden');
		}
	});
	
	$('body').on('change','#ProductionRunFinishedProductId',function(){
    $('#submit').attr('disabled', 'disabled');
		var productid=$(this).children("option").filter(":selected").val();
    if (productid>0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>products/getRawMaterialId/'+productid,
        cache: false,
        type: 'GET',
        success: function (rawmaterialid) {
          $('#ProductionRunRawMaterialId').val(rawmaterialid);
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
      
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>products/getmachines/'+productid,
        cache: false,
        type: 'GET',
        success: function (machineoptions) {
          $('#ProductionRunMachineId').html(machineoptions);
          $('#submit').removeAttr("disabled")
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
    }
    calculateBagQuantity();
    setBagProductId();
	});	
	
	function calculateTotal(){
		var materialUsed=0;
		$(".finishedproduct").each(function() {
			var productAmount = parseFloat($(this).val());
			materialUsed = materialUsed + productAmount;
		});
		$('#rawUsed').val(materialUsed);
    
    calculateBagQuantity();
    //setBagProductId();
	}
  
  function calculateBagQuantity(){
    $('#submit').attr('disabled', 'disabled');
    var bagQuantity=0;
		
    var quantityA=$('#StockitemsA').val();
    var quantityB=$('#StockitemsB').val();
    var productId=$('#ProductionRunFinishedProductId').val();
    
    var packagingUnit=0;
    if (productId>0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>products/getproductpackagingunit/'+productId,
        cache: false,
        type: 'GET',
        success: function (packagingunit) {
          if (packagingunit <=0){
            $('#bagQuantityMessage').text('No hay unidad de empaque registrada para el producto seleccionado.');
            $('#bagQuantityMessage').addClass('error');
            $('#bagQuantityMessage').removeClass('hidden');
            $('#ProductionRunBagQuantity').val(0);
          }
          else {
            $('#bagQuantityMessage').text('');
            $('#bagQuantityMessage').removeClass('error');
            $('#bagQuantityMessage').addClass('hidden');
            
            bagQuantity += Math.ceil(quantityA/packagingunit);
            bagQuantity += Math.ceil(quantityB/packagingunit);
          
            $('#ProductionRunBagQuantityTarget').val(bagQuantity);
            $('#ProductionRunBagQuantity').val(bagQuantity);
          }
          $('#submit').removeAttr("disabled")
        },
        error: function(e){
          $('#content').html(e.responseText);
          console.log(e);
        }
      });
    }
    else {
      $('#bagQuantityMessage').text('');
      $('#bagQuantityMessage').removeClass('error');
      $('#ProductionRunBagQuantity').val(0);
    }
  }
  
  function setBagProductId(){
    var productId=$('#ProductionRunFinishedProductId').val();
    
    if (productId>0){
      $.ajax({
        url: '<?php echo $this->Html->url('/'); ?>products/getbagproductid/'+productId,
        cache: false,
        type: 'GET',
        success: function (bagproductid) {
          if (bagproductid >0){
            $('#ProductionRunBagProductId').val(bagproductid);
          }
        },
        error: function(e){
          alert(e.responseText);
          console.log(e);
        }
      });
    }
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
  
  /*
  $('body').on('change','#ProductionRunFinishedProductId',function(){
    checkAcceptability();
  });
  $('body').on('change','#StockitemsA',function(){
    checkAcceptability();
  });
  function checkAcceptability(){
    var finishedproductid=$('#ProductionRunFinishedProductId').val();
		var productionday=$('#ProductionRunProductionRunDateDay').val();
		var productionmonth=$('#ProductionRunProductionRunDateMonth').val();
		var productionyear=$('#ProductionRunProductionRunDateYear').val();
    var quantitya=$('#StockitemsA').children("option").filter(":selected").val();
		
		if (finishedproductid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>production_runs/checkacceptability/',
				data:{"finishedproductid":finishedproductid,"productionday":productionday,"productionmonth":productionmonth,"productionyear":productionyear,"quantitya":quantitya},
				cache: false,
				type: 'POST',
				success: function (boolAcceptable) {
					if (boolAcceptable){
            $('#ProductionRunIncidenceId').attr('required',true);
          }
          else {
            $('#ProductionRunIncidenceId').removeAttr('required');
          }
				},
				error: function(e){
					//console.log(e);
					alert(e.responseText);
				}
			});
		}
    else {
      $('#ProductionRunIncidenceId').removeAttr('required');
    }
  }
  */
  
	$('body').on('change','.consumiblequantity',function(){
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
    var thisRow=$(this).closest('tr');
    calculateConsumibleRow($(this).closest('tr').attr('row'));
		calculateConsumibleTotal();
	});	
  
  function calculateConsumibleRow(rowid) {    
		var currentrow=$('#otherConsumibles').find("[row='" + rowid + "']");
		var quantity=parseFloat(currentrow.find('td.consumiblequantity div input').val());
	}
	
	function calculateConsumibleTotal(){
		var totalConsumibleQuantity=0;
		$("#otherConsumibles tbody tr:not(.totalrow):not(.hidden)").each(function() {
			var currentConsumibleQuantity = $(this).find('td.consumiblequantity div input');
			if (!isNaN(currentConsumibleQuantity.val())){
				var currentQuantity = parseFloat(currentConsumibleQuantity.val());
				totalConsumibleQuantity += currentQuantity;
			}
		});
		$('#otherConsumibles tbody tr.totalrow.total td.consumiblequantity span').text(totalConsumibleQuantity.toFixed(0));
		
		return false;
	}
	
	$('body').on('click','.addConsumible',function(){
		var tableRow=$('#otherConsumibles tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});

	$('body').on('click','.removeConsumible',function(){
		var tableRow=$(this).closest('tr').remove();
		calculateConsumibleTotal();
	});
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
  function formatNumbers(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0);
		});
	}	
  
	$(document).ready(function(){
    formatNumbers();
		
		$('#ProductionRunProductionRunDateHour').val('08');
		$('#ProductionRunProductionRunDateMin').val('00');
		$('#ProductionRunProductionRunDateMeridian').val('am');
    
    $('#saving').addClass('hidden');
    
    $('#bagQuantityMessage').addClass('hidden');
    
    calculateConsumibleTotal();
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
<div class="productionRuns form">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando la orden de producción...</p>";
    echo "</div>";
  echo "</div>";
  
  echo "<div id='mainform'>";
    echo $this->Form->create('ProductionRun'); 
    echo "<fieldset>";
      echo "<legend>".__('New Production Run')."</legend>";
        echo $this->Form->input('production_run_code',['default'=>$newProductionRunCode,'readonly'=>'readonly']);
        echo $this->Form->input('production_run_date',['dateFormat'=>'DMY','minYear'=>2012,'maxYear'=>(date('Y')+1)]);
        echo $this->Form->Submit('Actualizar Inventario para fecha',['id'=>'refresh','name'=>'refresh']);
        
        
      echo "<div class='container-fluid'>";
        echo "<div class='row'>";	
          echo "<div class='col-md-6 parameters'>";
          if ($roleId==ROLE_FOREMAN){	
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
          echo "</div>";
          echo "<div class='col-md-6 parameters'>";
            echo $this->Form->input('production_type_id',['default'=>PRODUCTION_TYPE_PET]);
            echo $this->Form->input('incidence_id',['default'=>0,'empty'=>[0=>'No incidencias']]);
          echo "</div>";
        echo "</div>";
          
        echo "<div class='row'>";	
          echo "<div class='col-md-6 parameters'>";  
            echo "<!-- FINISHED PRODUCTS OUT -->";
            echo "<h3>".__('Finished Products')."</h3>";
            echo "<div id='finished_products'>";
              echo $this->Form->input('finished_product_id',['default'=>'0','empty'=>['0'=>'Seleccione Producto Fabricado']]);
              echo $this->Form->input('raw_material_id',['default'=>'0','empty'=>['0'=>'Seleccione Materia Prima']]);
              foreach ($resultCodes as $resultCode) {
                echo $this->Form->input(
                  'Stockitems.'.$resultCode['ProductionResultCode']['code'],
                  [
                    'label'=>__('Quantity of quality ').$resultCode['ProductionResultCode']['code'],
                    'type'=>'number',
                    'default'=>0,
                    'class'=>'finishedproduct',
                  ]
                );
              }
              echo $this->Form->input('raw_material_quantity',['readonly'=>'readonly','id'=>'rawUsed']);
              echo $this->Form->input('bag_product_id',['label'=>'Bolsa utilizada','default'=>'0','options'=>$consumibles,'empty'=>[0=>'Seleccione Bolsa']]);
              echo "<span id='bagQuantityMessage'></span>";
              echo $this->Form->input('bag_quantity_target',['label'=>false,'default'=>'0','type'=>'hidden']);
              echo $this->Form->input('bag_quantity',['label'=>'# bolsas','default'=>'0','type'=>'number']);
              //echo $this->Form->input('consumible_material_id',['label'=>'Suministro Adicional','default'=>'0','empty'=>['0'=>'Seleccione Suministro']]);
              //echo $this->Form->input('consumible_material_quantity',['label'=>'Cantidad de Suministro','type'=>'number','default'=>0]);
              
              
             echo "<h3>Otros suministros</h3>";
              echo "<table id='otherConsumibles' style='font-size:13px;'>";
                echo "<thead>";
                  echo "<tr>";
                    echo "<th>Consumible</th>";
                    echo "<th>Cantidad</th>";
                    echo "<th>Acciones</th>";
                  echo "</tr>";
                echo "</thead>";
                echo "<tbody style='font-size:1rem;'>";
                $counter=0;
                for ($c=0;$c<count($requestConsumibles);$c++){
                  echo "<tr row='".$c."'>";
                    echo "<td class='consumiblematerialid'>";
                      echo $this->Form->input('Consumibles.'.$c.'.consumible_id',['label'=>false,'value'=>$requestConsumibles['Consumibles'][$c]['consumible_id'],'empty'=>['0'=>'Seleccione Suministro']]);
                    echo "</td>";
                    echo "<td class='consumiblequantity amount'>".$this->Form->input('Consumibles.'.$c.'.consumible_quantity',['label'=>false,'type'=>'decimal','value'=>$requestConsumibles['Consumibles'][$c]['consumible_quantity'],'required'=>false,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
                    echo "<td>";
                        echo "<button class='removeConsumible' type='button'>".__('Remover Suministro')."</button>";
                        echo "<button class='addConsumible' type='button'>".__('Añadir Suministro')."</button>";
                    echo "</td>";
                  echo "</tr>";
                  $counter++;
                }
                for ($c=$counter;$c<30;$c++){
                  if ($c==$counter){
                    echo "<tr row='".$c."'>";
                  }
                  else {
                    echo "<tr row='".$c."' class='hidden'>";
                  }
                    echo "<td class='consumibleid'>";
                      echo $this->Form->input('Consumibles.'.$c.'.consumible_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Suministro']]);
                    echo "</td>";
                    echo "<td class='consumiblequantity amount'>".$this->Form->input('Consumibles.'.$c.'.consumible_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
                    echo "<td>";
                        echo "<button class='removeConsumible' type='button'>".__('Remover Suministro')."</button>";
                        echo "<button class='addConsumible' type='button'>".__('Añadir Suministro')."</button>";
                    echo "</td>";
                  echo "</tr>";
                }
                  echo "<tr class='totalrow total' style='font-size:13px!important;'>";
                    echo "<td>Total</td>";
                    echo "<td class='consumiblequantity amount right'><span>0</span></td>";
                    echo "<td></td>";
                  echo "</tr>";		
                echo "</tbody>";
              echo "</table>";
            echo "</div>";
          echo "</div>";
          echo "<div class='col-md-6 parameters'>";
            echo "<h3>".__('Production Parameters')."</h3>";
            echo $this->Form->input('machine_id');
            echo $this->Form->input('operator_id');
            echo $this->Form->input('shift_id');
            //echo $this->Form->input('meter_start',array('type'=>'number'));
            echo $this->Form->input('meter_finish',['type'=>'number','default'=>0]);
            echo $this->Form->input('comment',['type'=>'textarea','rows'=>5]);
          echo "</div>";
        echo "</div>";
      echo "</div>";
    echo "</fieldset>";
    echo $this->Form->Submit(__('Guardar Orden de Producción'),['id'=>'submit','name'=>'submit']); 
    echo $this->Form->end(); 
  echo "</div>";
?>
</div>

<div class="actions productionrun">
<?php  
	echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,'Preformas en bodega'); 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Production Runs'), array('action' => 'index'))."</li>";
		echo "<h3>".__('Configuration Options')."</h3>";
		if ($bool_operator_totalproductionreport_permission) {
			echo "<li>".$this->Html->link('Reporte Producción Total', array('controller' => 'operators', 'action' => 'reporteProduccionTotal'))." </li>";
			echo "<br/>";
		}
		if ($bool_producttype_index_permission){
			echo "<li>".$this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index'))." </li>";
		}
		if ($bool_producttype_add_permission){
			echo "<li>".$this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add'))." </li>";
		}
		if ($bool_machine_index_permission){
			echo "<li>".$this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index'))." </li>";
		}
		if ($bool_machine_add_permission){
			echo "<li>".$this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add'))." </li>";
		}
		if ($bool_operator_index_permission){
			echo "<li>".$this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index'))." </li>";
		}
		if ($bool_operator_add_permission){
			echo "<li>".$this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add'))." </li>";
		}
		if ($bool_shift_index_permission){
			echo "<li>".$this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index'))." </li>";
		}
		if ($bool_shift_add_permission){
			echo "<li>".$this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add'))." </li>";
		}
		
	echo "</ul>";
?>	
</div>