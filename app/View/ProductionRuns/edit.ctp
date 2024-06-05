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
          $('#content').html(e.responseText);
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
          $('#content').html(e.responseText);
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
		var quantity=parseFloat(currentrow.find('td.consumablequantity div input').val());
	}
	
	function calculateConsumableTotal(){
		var totalConsumableQuantity=0;
		$("#otherConsumables tbody tr:not(.totalrow):not(.hidden)").each(function() {
			var currentConsumableQuantity = $(this).find('td.consumablequantity div input');
			if (!isNaN(currentConsumableQuantity.val())){
				var currentQuantity = parseFloat(currentConsumableQuantity.val());
				totalConsumableQuantity += currentQuantity;
			}
		});
		$('#otherConsumables tbody tr.totalrow.total td.consumablequantity span').text(totalConsumableQuantity.toFixed(0));
		
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
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0);
		});
	}	
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
	
	$(document).ready(function(){
		formatNumbers();
		
    if ($('#ProductionRunBoolAnnulled').is(':checked')){
			$('.parameters').addClass('hidden');
		}
		else {
			$('.parameters').removeClass('hidden');
		}
		$('select.fixed option:not(:selected)').attr('disabled', true);
    
    $('#bagQuantityMessage').addClass('hidden');
    
    // DO NOT CALCULATE BAGQUANTITY AS WE WANT TO KEEP THE AMOUNT ALREADY REGISTERED
    //calculateBagQuantity();
    
    calculateConsumableTotal();
	});
</script>
<div class="productionRuns form edit">
<?php 
	echo $this->Form->create('ProductionRun'); 

	echo "<fieldset>";
		echo "<legend>".__('Edit Production Run')." ".$this->request->data['ProductionRun']['production_run_code']."</legend>";
		echo $this->Form->input('id',array('hidden'=>'hidden'));
		echo $this->Form->input('production_run_code',array('readonly'=>'readonly'));
		echo $this->Form->input('production_run_date',array('dateFormat'=>'DMY','minYear'=>2012,'maxYear'=>(date('Y')+1)));
		echo $this->Form->Submit('Actualizar Inventario para fecha',array('id'=>'refresh','name'=>'refresh'));
		
		echo "<div class='container-fluid'>";
      echo "<div class='row'>";	
        echo "<div class='col-md-6 parameters'>";
        if ($roleId==ROLE_FOREMAN){	
          echo $this->Form->input('bool_verified',array('type'=>'hidden'));
        }
        else {
          echo $this->Form->input('bool_verified',array('label'=>'Verificada'));
        }
        if ($bool_annul_permission){
          if (!empty($boolEditable)){
            echo $this->Form->input('bool_annulled',array('label'=>'Anulada'));
          }
          else {
            echo "<p>No se puede anular la orden de producción porque ya hay salidas con los productos fabricados</p>";
            echo $this->Form->input('bool_annulled',array('label'=>'Anulada','onclick'=>'return false;'));
          }
        }
        else {
          echo $this->Form->input('bool_annulled',array('type'=>'hidden'));
        }
        echo "</div>";
				echo "<div class='col-md-6 parameters'>";
          echo $this->Form->input('production_type_id');
          echo $this->Form->input('incidence_id',['empty'=>[0=>'No incidencias']]);
        echo "</div>";
      echo "</div>";
			echo "<div class='row'>";	
				echo "<div class='col-md-6 parameters' >";
					echo "<!-- FINISHED PRODUCTS OUT -->";
					echo "<h3>".__('Finished Products')."</h3>";
					echo "<div id='finished_products'>";
						echo $this->Form->input('bool_editable',array('value'=>$boolEditable,'type'=>'hidden'));
						echo $this->Form->input('reason_for_non_editable',array('value'=>$reasonForNonEditable,'type'=>'hidden'));
						if (!empty($boolEditable)){
							//pr($this->request->data);
							echo $this->Form->input('finished_product_id',array('empty'=>array('0'=>'Seleccione Producto Fabricado')));
							echo $this->Form->input('raw_material_id',array('empty'=>array('0'=>'Seleccione Materia Prima')));
					
							$quantityOut=0;
							foreach ($resultCodes as $resultCode) {
								if(!empty($this->request->data['ProductionMovement'])){
									foreach ($this->request->data['ProductionMovement'] as $outputMovement){
										if ($resultCode['ProductionResultCode']['id']==$outputMovement['production_result_code_id']){
											$quantityOut=$outputMovement['product_quantity'];
										}
									}
								}
								echo $this->Form->input(
									'Stockitems.'.$resultCode['ProductionResultCode']['code'],
									array(
										'label'=>__('Quantity of quality ').$resultCode['ProductionResultCode']['code'],
										'type'=>'number',
										'default'=>$quantityOut,
										'class'=>'finishedproduct',
									)
								);
							}
              echo $this->Form->input('raw_material_quantity',array('readonly'=>'readonly','id'=>'rawUsed'));
              echo $this->Form->input('bag_product_id',['label'=>'Bolsa utilizada','options'=>$consumables,'empty'=>[0=>'Seleccione Bolsa']]);
              echo "<span id='bagQuantityMessage'></span>";
              echo $this->Form->input('bag_quantity_target',['label'=>false,'default'=>$this->request->data['ProductionRun']['bag_quantity'],'type'=>'hidden']);
              echo $this->Form->input('bag_quantity',['label'=>'# bolsas','type'=>'number']);
              //echo $this->Form->input('consumable_material_id',['label'=>'Suministro Adicional','default'=>'0','empty'=>['0'=>'Seleccione Suministro']]);
              //echo $this->Form->input('consumable_material_quantity',['label'=>'Cantidad de Suministro','type'=>'number','default'=>0]);
              
              echo "<h3>Otros suministros</h3>";
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
                  echo "<tr row='".$c."'>";
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
                for ($c=$counter;$c<30;$c++){
                  if ($c==$counter){
                    echo "<tr row='".$c."'>";
                  }
                  else {
                    echo "<tr row='".$c."' class='hidden'>";
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
                  echo "<tr class='totalrow total'>";
                    echo "<td>Total</td>";
                    echo "<td class='consumablequantity amount right'><span>0</span></td>";
                    echo "<td></td>";
                  echo "</tr>";		
                echo "</tbody>";
              echo "</table>";
						}
						else {
							echo "<p class='warning'>".$reasonForNonEditable."</p>";
							echo $this->Form->input('finished_product_id',array('empty'=>array('0'=>'Seleccione Producto Fabricado'),'class'=>'fixed'));
							echo $this->Form->input('raw_material_id',array('empty'=>array('0'=>'Seleccione Materia Prima'),'class'=>'fixed'));
					
							$quantityOut=0;
							foreach ($resultCodes as $resultCode) {
								if(!empty($this->request->data['ProductionMovement'])){
									foreach ($this->request->data['ProductionMovement'] as $outputMovement){
										if ($resultCode['ProductionResultCode']['id']==$outputMovement['production_result_code_id']){
											$quantityOut=$outputMovement['product_quantity'];
										}
									}
								}
								echo $this->Form->input(
									'Stockitems.'.$resultCode['ProductionResultCode']['code'],
									array(
										'label'=>__('Quantity of quality ').$resultCode['ProductionResultCode']['code'],
										'type'=>'number',
										'default'=>$quantityOut,
										'readonly'=>'readonly',
										'class'=>'finishedproduct',
									)
								);
							}
              
              echo $this->Form->input('raw_material_quantity',array('readonly'=>'readonly','id'=>'rawUsed'));
              echo $this->Form->input('bag_product_id',['label'=>'Bolsa utilizada','options'=>$consumables,'class'=>'fixed','empty'=>[0=>'Seleccione Bolsa']]);

              echo "<span id='bagQuantityMessage'></span>";
              echo $this->Form->input('bag_quantity_target',['label'=>false,'default'=>$this->request->data['ProductionRun']['bag_quantity'],'type'=>'hidden']);
              echo $this->Form->input('bag_quantity',['label'=>'# bolsas','type'=>'number','readonly'=>'readonly']);
              //echo $this->Form->input('consumable_material_id',['label'=>'Suministro Adicional','default'=>'0','empty'=>['0'=>'Seleccione Suministro'],'class'=>'fixed']);
              //echo $this->Form->input('consumable_material_quantity',['label'=>'Cantidad de Suministro','type'=>'number','default'=>0,'readonly'=>'readonly']);
              
              echo "<h3>Otros suministros</h3>";
              echo "<table id='otherConsumables' style='font-size:13px;'>";
                echo "<thead>";
                  echo "<tr>";
                    echo "<th>Consumable</th>";
                    echo "<th>Cantidad</th>";
                    //echo "<th>Acciones</th>";
                  echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                $counter=0;
                for ($c=0;$c<count($requestConsumables);$c++){
                  echo "<tr row='".$c."'>";
                    echo "<td class='consumablematerialid'>";
                      echo $this->Form->input('Consumables.'.$c.'.consumable_id',['label'=>false,'value'=>$requestConsumables['Consumables'][$c]['consumable_id'],'class'=>'fixed','empty'=>['0'=>'Seleccione Suministro']]);
                    echo "</td>";
                    echo "<td class='consumablequantity amount'>".$this->Form->input('Consumables.'.$c.'.consumable_quantity',['label'=>false,'type'=>'decimal','readonly'=>'readonly','value'=>$requestConsumables['Consumables'][$c]['consumable_quantity'],'required'=>false,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
                    //echo "<td>";
                        echo "<button class='removeConsumable' type='button'>".__('Remover Suministro')."</button>";
                        echo "<button class='addConsumable' type='button'>".__('Añadir Suministro')."</button>";
                    echo "</td>";
                  echo "</tr>";
                  $counter++;
                }
                
                  echo "<tr class='totalrow total'>";
                    echo "<td>Total</td>";
                    echo "<td class='consumablequantity amount right'><span>0</span></td>";
                    //echo "<td></td>";
                  echo "</tr>";		
                echo "</tbody>";
              echo "</table>";
						}
						
					echo "</div>";
				echo "</div>";
				echo "<div class='col-md-6 parameters'>";
					echo "<h3>".__('Production Parameters')."</h3>";
					echo $this->Form->input('machine_id');
					echo $this->Form->input('operator_id');
					echo $this->Form->input('shift_id');
					//echo $this->Form->input('meter_start',array('type'=>'number'));
					echo $this->Form->input('meter_finish',array('type'=>'number'));
          echo $this->Form->input('comment',array('type'=>'textarea','rows'=>5));
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</fieldset>";
	echo $this->Form->Submit(__('Submit'),array('id'=>'submit','name'=>'submit')); 
	echo $this->Form->end(); 		
?>
</div>
<div class="actions productionrun">
<?php  
	echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,'Preformas en bodega');  
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			//echo "<li><?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ProductionRun.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ProductionRun.id')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Production Runs'), array('action' => 'resumen'))."</li>";
		
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
		
		
	echo "</ul>";
?>
</div>