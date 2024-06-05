<script>
	$('body').on('change','.finishedproduct',function(){
		calculateTotal();
	});	
	
	function calculateTotal(){
		var materialUsed=0;
		$(".finishedproduct").each(function() {
			var productAmount = parseFloat($(this).val());
			materialUsed = materialUsed + productAmount;
		});
		$('#rawUsed').val(materialUsed);
		return false;
	}
	
	$('body').on('change','#ProductionRunFinishedProductId',function(){
		var productid=$(this).children("option").filter(":selected").val();
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
	});	
	
	$(document).ready(function(){
		$('#ProductionRunProductionRunDateHour').val('08');
		$('#ProductionRunProductionRunDateMin').val('00');
		$('#ProductionRunProductionRunDateMeridian').val('am');
	});
</script>
<div class="productionRuns manipular">
<?php
	echo "<h2>Manipular Orden de Producción"." ".$requestData['ProductionRun']['production_run_code']."</h2>";
	echo "<p>Estás intentando de registrar un orden de producción con los siguientes datos:</p>";  
  echo "<dl>";
    echo "<dt>Código de Producción</dt>";
    echo "<dd>".$requestData['ProductionRun']['production_run_code']."</dd>";
    echo "<dt>Fecha de Producción</dt>";
    echo "<dd>".$requestData['ProductionRun']['production_run_date']['day']."-".$requestData['ProductionRun']['production_run_date']['month']."-".$requestData['ProductionRun']['production_run_date']['year']."</dd>";
  echo "</dl>"; 
  echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo "<div class='col-md-6'>";	
          echo "<h3>Los productos ocupados en el orden de producción son:</h3>";
          echo "<dl>";
            echo "<dt>Materia Prima</dt>";
            echo "<dd>".$requestData['ProductionRun']['raw_material_name']."</dd>";
            echo "<dt>Cantidad</dt>";
            echo "<dd>".$requestData['ProductionRun']['raw_material_quantity']."</dd>";
            echo "</dl>";
            echo "<h3>Los productos que se desean producir en el orden de producción son:</h3>";
            echo "<dl>";
            echo "<dt>Producto Fabricado</dt>";
            echo "<dd>".$requestData['ProductionRun']['finished_product_name']."</dd>";
            echo "<dt>Cantidad de Calidad A</dt>";
            echo "<dd>".$requestData['Stockitems']['A']."</dd>";
            echo "<dt>Cantidad de Calidad B</dt>";
            echo "<dd>".$requestData['Stockitems']['B']."</dd>";
            echo "<dt>Cantidad de Calidad C</dt>";
            echo "<dd>".$requestData['Stockitems']['C']."</dd>";
            
            //if ($requestData['ProductionRun']['consumible_material_quantity']>0){
            //  echo "<dt>Consumible</dt>"; 
            //  echo "<dd>".$requestData['ProductionRun']['consumible_material_name']."</dd>";
            //  echo "<dt>Cantidad de Consumible</dt>"; 
            //  echo "<dd>".$requestData['ProductionRun']['consumible_material_quantity']."</dd>";
            //}
            
          echo "</dl>";
        echo "</div>";  
        echo "<div class='col-md-6'>";  
          echo "<h3>Parámetros del orden de producción</h3>";
          echo "<dl>";
            echo "<dt>Máquina</dt>";
            echo "<dd>".$requestData['ProductionRun']['machine_name']."</dd>";
            echo "<dt>Operador</dt>";
            echo "<dd>".$requestData['ProductionRun']['operator_name']."</dd>";
            echo "<dt>Turno</dt>";
            echo "<dd>".$requestData['ProductionRun']['shift_name']."</dd>";
            echo "<dt>Medidor Final</dt>";
            echo "<dd>".$requestData['ProductionRun']['meter_finish']."</dd>";
            echo "<dt>Comentario</dt>";
            echo "<dd>".html_entity_decode($requestData['ProductionRun']['comment'])."</dd>";
          echo "</dl>";
        echo "</div>";  
      echo "</div>";  
    echo "</div>";  
	// begin logic
	$requiredA=$requestData['Stockitems']['A'];
	$requiredB=$requestData['Stockitems']['B'];
	$requiredC=$requestData['Stockitems']['C'];
	$totalRequired=$requiredA+$requiredB+$requiredC;
	
	echo "<h3>La cantidad total requerida ".$totalRequired." es mayor que la cantidad total presente en bodega ".$quantityRawMaterialInStock."</h3>";
	
	//echo "Raw Material in Stock ".$quantityRawMaterialInStock."<br/>";
	//echo "Finished C in Stock ".$quantityFinishedCInStock."<br/>";
	//echo "Finished B in Stock ".$quantityFinishedBInStock."<br/>";
	
	$manipulationAction=0;
  $reclassificationComment="";
	
	if ($totalRequired>$quantityRawMaterialInStock+MANIPULATION_MAX_PRODUCCION){
		echo "<h2>La preforma que se require para este orden de producción excede la cantidad en bodega con más de ".MANIPULATION_MAX_PRODUCCION.".  No se puede manipular el orden de producción.</h2>";
	}
	// 1. if the production run is fine without the quantity C, set corresponding message, set C to zero, and finished
	elseif (($requiredA+$requiredB)<$quantityRawMaterialInStock){
		echo "<h3>Se puede registrar la cantidad total requerida sin la cantidad C ".($requiredA+$requiredB).".</h3>";
		echo "<h2>Confirme que quiere registrar el orden de producción con la cantidad C en zero (0).</h2>";
		$manipulationAction=1;
    $reclassificationComment.="Orden de producción registrada sin la cantidad C ".($requiredA+$requiredB)." sin reclasificación.";
	}
	
	// 2. check if there were enough products of C to convert to cover for A and B
	// if alright, convert the needed quantity of C to A and B respectively
	elseif(($requiredA+$requiredB)<($quantityRawMaterialInStock+$quantityFinishedCInStock)){
		echo "<h3>Se pueden registrar la cantidades totales de A (".$requiredA.") y B (".$requiredB.") sin la cantidad de C si se reclasifican ".($requiredA+$requiredB-$quantityRawMaterialInStock)." productos de calidad C a la materia prima ".$requestData['ProductionRun']['raw_material_name'].".</h3>";
		echo "<h2>Confirme que quiere registrar el orden de producción con la cantidad C en zero (0) y reclasificar ".($requiredA+$requiredB-$quantityRawMaterialInStock)." productos de calidad C a preforma ".$requestData['ProductionRun']['raw_material_name'].".</h2>";
		$manipulationAction=2;
    $reclassificationComment.="Orden de producción registrada sin la cantidad C ".($requiredA+$requiredB)." con reclasificación de ".($requiredA+$requiredB-$quantityRawMaterialInStock)." productos de calidad C a preforma ".$requestData['ProductionRun']['raw_material_name'].".";
	}
	
	// 3. if the production run is fine without the quantity C and B, set corresponding message, set C and B to zero, and finished
	elseif ($requiredA<$quantityRawMaterialInStock){
		echo "<h3>Se puede registrar la cantidad total A ".$requiredA." sin las cantidades B y C.</h3>";
		echo "<h2>Confirme que quiere registrar el orden de producción con las cantidades B y C en zero (0).</h2>";
		$manipulationAction=3;
    $reclassificationComment.="Orden de producción registrada sin las cantidades B y C sin reclasificación.";
	}
	
	// 4. if only the quantity of A is more than what is in stock, set C and B to 0, and check if there were enough products of C to convert
	// if alright, convert the needed quantity of C
	elseif($requiredA<($quantityRawMaterialInStock+$quantityFinishedCInStock)){
		echo "<h3>Se puede registrar la cantidad total A sin las cantidades B y C ".$requiredA." si se reclasifican ".($requiredA-$quantityRawMaterialInStock)." productos de calidad C a la materia prima ".$requestData['ProductionRun']['raw_material_name'].".</h3>";
		echo "<h2>Confirme que quiere registrar el orden de producción con las cantidades B y C en zero (0) y reclasificar ".($requiredA-$quantityRawMaterialInStock)." productos de calidad C a preforma ".$requestData['ProductionRun']['raw_material_name'].".</h2>";
		$manipulationAction=4;
    $reclassificationComment.="Orden de producción registrada sin las cantidades B y C con reclasificación de ".($requiredA-$quantityRawMaterialInStock)." productos de calidad C a preforma ".$requestData['ProductionRun']['raw_material_name'].".";
	}
	
	// 5. if conversion of all materials of quality C is not enough to cover quantity A, check if there would be enough if all B and C would be converted
	// if alright, convert all of C and the needed quantity of B
	elseif($requiredA<($quantityRawMaterialInStock+$quantityFinishedCInStock+$quantityFinishedBInStock)){
		echo "<h3>Se puede registrar la cantidad total A sin las cantidades B y C ".$requiredA." si se reclasifican ".$quantityFinishedCInStock." productos de calidad C y ".($requiredA-$quantityRawMaterialInStock-$quantityFinishedCInStock)." productos de calidad B a la materia prima ".$requestData['ProductionRun']['raw_material_name'].".</h3>";
		echo "<h2>Confirme que quiere registrar el orden de producción con las cantidades B y C en zero (0) y reclasificar ".$quantityFinishedCInStock." productos de calidad C y ".($requiredA-$quantityRawMaterialInStock-$quantityFinishedCInStock)." productos de calidad B a preforma ".$requestData['ProductionRun']['raw_material_name'].".</h2>";
		$manipulationAction=5;
    $reclassificationComment.="Orden de producción registrada sin las cantidades B y C con reclasificación de ".$quantityFinishedCInStock." productos de calidad C y ".($requiredA-$quantityRawMaterialInStock-$quantityFinishedCInStock)." productos de calidad B a preforma ".$requestData['ProductionRun']['raw_material_name'].".";
	}
	
	// 6. if conversion of all B and C is not enough to cover for A, give a negative message
	else{
		echo "<h2>No hay suficientes productos en bodega para realizar el orden de producción, manipulado o no.</h2>";
	}
	
	$manipulationArray=array();
	$manipulationArray[]="No acción";
	$manipulationArray[]="Establecer cantidad para calidad C a 0";
	$manipulationArray[]="Establecer cantidades para calidad C a 0 y reclasificar producto de calidad C a preforma";
	$manipulationArray[]="Establecer cantidades para calidades B y C a 0";
	$manipulationArray[]="Establecer cantidades para calidades B y C a 0 y reclasificar producto de calidad C a preforma";
	$manipulationArray[]="Establecer cantidades para calidades B y C a 0 y reclasificar producto de calidad B y C a preforma";

  //echo "reclassification comment is ".$reclassificationComment."<br/>";
  if (!empty($requestData['ProductionRun']['comment'])){
    $newComment=$requestData['ProductionRun']['comment']."\r\n".$reclassificationComment;
  }
  else {
    $newComment=$reclassificationComment;
  }
  //echo "new comment is ".$newComment."<br/>";
  $newComment=str_replace("\r","[carriagereturn]",$newComment);
  $newComment=str_replace("\n","[newline]",$newComment);
  //echo "new comment is ".$newComment."<br/>";
	if ($manipulationAction>0){
		echo $this->Form->create('ProductionRun'); 
		echo "<fieldset>";
		//pr($requestData['ProductionRun']['production_run_date']);
    $reclassificationDate=$requestData['ProductionRun']['production_run_date']['year'].'-'.$requestData['ProductionRun']['production_run_date']['month'].'-'.$requestData['ProductionRun']['production_run_date']['day'];
    //echo "reclassificationDate is ".$reclassificationDate."<br/>";
		echo $this->Form->input('production_run_code',array('default'=>$requestData['ProductionRun']['production_run_code'],'label'=>false,'type'=>'hidden'));
		//echo $this->Form->input('production_run_date',array('value'=>$reclassificationDate,'label'=>false,'type'=>'hidden','div'=>array('id'=>'productionRunDateManipulation')));
    echo $this->Form->input('production_run_date',array('value'=>$reclassificationDate,'label'=>false,'type'=>'hidden'));
		echo $this->Form->input('machine_id',array('default'=>$requestData['ProductionRun']['machine_id'],'label'=>false,'type'=>'hidden'));
		echo $this->Form->input('operator_id',array('default'=>$requestData['ProductionRun']['operator_id'],'label'=>false,'type'=>'hidden'));
		echo $this->Form->input('shift_id',array('default'=>$requestData['ProductionRun']['shift_id'],'label'=>false,'type'=>'hidden'));
		echo $this->Form->input('meter_finish',array('default'=>$requestData['ProductionRun']['meter_finish'],'label'=>false,'type'=>'hidden'));
    echo $this->Form->input('incidence_id',array('default'=>$requestData['ProductionRun']['incidence_id'],'label'=>false,'type'=>'hidden'));
    echo $this->Form->input('comment',array('value'=>$newComment,'label'=>false,'type'=>'hidden'));
    echo $this->Form->input('reclassification_comment',array('default'=>$reclassificationComment,'label'=>false,'type'=>'hidden'));

		echo $this->Form->input('finished_product_id',array('default'=>$requestData['ProductionRun']['finished_product_id'],'label'=>false,'type'=>'hidden'));
		echo $this->Form->input('raw_material_id',array('default'=>$requestData['ProductionRun']['raw_material_id'],'label'=>false,'type'=>'hidden'));

		echo $this->Form->input('Stockitems.A',array('label'=>'A','default'=>$requestData['Stockitems']['A'],'class'=>'finishedproduct','label'=>false,'type'=>'hidden'));
		if ($manipulationAction>2){
			echo $this->Form->input('Stockitems.B',array('label'=>'B','value'=>'0','class'=>'finishedproduct','label'=>false,'type'=>'hidden'));
		}
		else {
			echo $this->Form->input('Stockitems.B',array('label'=>'B','value'=>$requestData['Stockitems']['B'],'class'=>'finishedproduct','label'=>false,'type'=>'hidden'));
		}
		echo $this->Form->input('Stockitems.C',array('label'=>'C','value'=>'0','class'=>'finishedproduct','label'=>false,'type'=>'hidden'));
    echo $this->Form->input('bag_product_id',['label'=>false,'default'=>$requestData['ProductionRun']['bag_product_id'],'type'=>'hidden']);
    echo $this->Form->input('bag_quantity_target',['label'=>false,'default'=>$requestData['ProductionRun']['bag_quantity_target'],'type'=>'hidden']);
    echo $this->Form->input('bag_quantity',['label'=>false,'default'=>$requestData['ProductionRun']['bag_quantity'],'type'=>'hidden']);
              
    //echo $this->Form->input('consumible_material_id',['label'=>false,'default'=>$requestData['ProductionRun']['consumible_material_id'],'type'=>'hidden']);
    //echo $this->Form->input('consumible_material_quantity',['label'=>false,'type'=>'hidden','default'=>$requestData['ProductionRun']['consumible_material_quantity']]);
    echo "<h3>Otros suministros</h3>";
    echo "<table id='otherConsumibles' style='font-size:13px;'>";
      echo "<thead>";
        echo "<tr>";
          echo "<th>Consumible</th>";
          echo "<th>Cantidad</th>";
          //echo "<th>Acciones</th>";
        echo "</tr>";
      echo "</thead>";
      echo "<tbody>";
      $counter=0;
      for ($c=0;$c<count($requestData['Consumibles']);$c++){ 
        if ($requestData['Consumibles'][$c]['consumible_quantity']>0){
          echo "<tr row='".$c."'>";
            echo "<td class='consumiblematerialid'>";
              echo $this->Form->input('Consumibles.'.$c.'.consumible_id',['label'=>false,'value'=>$requestData['Consumibles'][$c]['consumible_id'],'class'=>'fixed','empty'=>['0'=>'Seleccione Suministro']]);
            echo "</td>";
            echo "<td class='consumiblequantity amount'>".$this->Form->input('Consumibles.'.$c.'.consumible_quantity',['label'=>false,'type'=>'decimal','readonly'=>'readonly','value'=>$requestData['Consumibles'][$c]['consumible_quantity'],'required'=>false,'style'=>'width:100%','div'=>['style'=>'width:100%']])."</td>";
            //echo "<td>";
            //    echo "<button class='removeConsumible' type='button'>".__('Remover Suministro')."</button>";
            //    echo "<button class='addConsumible' type='button'>".__('Añadir Suministro')."</button>";
            //echo "</td>";
          echo "</tr>";
          $counter++;
        }
      }
      
        echo "<tr class='totalrow total'>";
          echo "<td>Total</td>";
          echo "<td class='consumiblequantity amount right'><span>0</span></td>";
          //echo "<td></td>";
        echo "</tr>";		
      echo "</tbody>";
    echo "</table>";
    
		
		if ($manipulationAction>2){
			echo $this->Form->input('raw_material_quantity',array('value'=>$requiredA,'id'=>'rawUsed','label'=>false,'type'=>'hidden'));
		}
		else {
			echo $this->Form->input('raw_material_quantity',array('value'=>($requiredA+$requiredB),'id'=>'rawUsed','label'=>false,'type'=>'hidden'));
		}
		//echo $this->Form->input('manipulation_action',array('value'=>$manipulationAction,'id'=>'manipulationAction','options'=>$manipulationArray,'label'=>false,'type'=>'hidden'));
    echo $this->Form->input('manipulation_action',array('value'=>$manipulationAction,'id'=>'manipulationAction','label'=>false,'type'=>'hidden'));
		if ($manipulationAction==2){
			echo $this->Form->input('reclassified_B',array('value'=>'0','label'=>false,'type'=>'hidden'));
			echo $this->Form->input('reclassified_C',array('value'=>($requiredA+$requiredB-$quantityRawMaterialInStock),'label'=>false,'type'=>'hidden'));
      //echo "reclassified B is 0<br/>";
      //echo "reclassified C is ".($requiredA+$requiredB-$quantityRawMaterialInStock)."<br/>";
		}
		elseif ($manipulationAction==4){
			echo $this->Form->input('reclassified_B',array('value'=>'0','label'=>false,'type'=>'hidden'));
			echo $this->Form->input('reclassified_C',array('value'=>($requiredA-$quantityRawMaterialInStock),'label'=>false,'type'=>'hidden'));
		}
		elseif ($manipulationAction==5){
			echo $this->Form->input('reclassified_B',array('value'=>($requiredA-$quantityRawMaterialInStock-$quantityFinishedCInStock),'label'=>false,'type'=>'hidden'));
			echo $this->Form->input('reclassified_C',array('value'=>$quantityFinishedCInStock,'label'=>false,'type'=>'hidden'));
		}
		else {
			echo $this->Form->input('reclassified_B',array('value'=>'0','label'=>false,'type'=>'hidden'));
			echo $this->Form->input('reclassified_C',array('value'=>'0','label'=>false,'type'=>'hidden'));
		}
		echo "</fieldset>";
		echo $this->Form->end(__('Confirmar acción')); 
	}
?>
</div>

<!--div class="actions productionrun">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Production Runs'), array('action' => 'index')); ?></li>
		<br/>		
		<h3><?php echo __('Configuration Options'); ?></h3>
		<li><?php echo $this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div-->

