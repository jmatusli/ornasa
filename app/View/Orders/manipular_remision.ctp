<script>
  
  $('body').on('submit','#OrderManipularRemisionForm',function(e){
		//e.preventDefault();
    $('#submit').attr('disabled','disabled');
    return true;    
	});	
/*
	$('body').on('change','.finishedproduct',function(){
		calculateTotal();
	});	
	
	function calculateTotal(){
		var currencyid=$('#CashReceiptCurrencyId').children("option").filter(":selected").val();
		var totalPrice=0;
		$("#productsForRemission tbody tr:not(.hidden)").each(function() {
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
		$('#totalPrice').val(roundToTwo(totalPrice));
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
*/
</script>
<div class="remisiones manipular">
<?php
	echo "<h2>Manipular Remisión "." ".$requestData['Order']['order_code']."</h2>";
	echo "<p>Estás intentando de registrar una remisión con los siguientes datos:</p>";  
  echo "<dl>";
    echo "<dt>Código de Remisión</dt>";
    echo "<dd>".$requestData['Order']['order_code']."</dd>";
    echo "<dt>Fecha de Remisión y Reclasificación</dt>";
    echo "<dd>".$requestData['Order']['order_date']['day']."-".$requestData['Order']['order_date']['month']."-".$requestData['Order']['order_date']['year']."</dd>";
  echo "</dl>"; 
  //pr($requestData);
  echo "<h3>Los productos que se quieren remitir son:</h3>";
	echo "<br/>";
	echo "<table>";
		echo "<thead>";
			echo "<tr>";
				echo "<td>Producto</td>";
				echo "<td>Cantidad Requerida</td>";
				echo "<td>Cantidad B en Bodega</td>";
				echo "<td>Cantidad C en Bodega</td>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($requestedProducts as $requestedProduct){
			//pr($requestedProduct);
			echo "<tr>";
			switch ($requestedProduct['ProductType']['product_category_id']){
				case CATEGORY_PRODUCED:
					echo "<td>".$requestedProduct['Product']['name']." ".$requestedProduct['RawMaterial']['name']." Calidad ".$productionResultCodes[$requestedProduct['ProductRequest']['production_result_code_id']]."</td>";
					echo "<td>".$requestedProduct['ProductRequest']['product_quantity']."</td>";
					echo "<td class=".(($requestedProduct['ProductRequest']['production_result_code_id'] == PRODUCTION_RESULT_CODE_B && $requestedProduct['ProductRequest']['product_quantity']>$requestedProduct['quality_B'])?'redbg':'greenbg').">";
						echo $requestedProduct['quality_B'];
					echo "</td>";
					echo "<td class=".(($requestedProduct['ProductRequest']['production_result_code_id'] == PRODUCTION_RESULT_CODE_C && $requestedProduct['ProductRequest']['product_quantity']>$requestedProduct['quality_C'])?'redbg':'greenbg').">";
						echo $requestedProduct['quality_C'];
					echo "</td>";
					break;
				case CATEGORY_RAW:
				case CATEGORY_OTHER:
					echo "<td>".$requestedProduct['Product']['name']."</td>";
					echo "<td>".$requestedProduct['ProductRequest']['product_quantity']."</td>";
          echo "<td class=".(($requestedProduct['ProductRequest']['product_quantity']>$requestedProduct['quantity'])?'redbg':'greenbg').">";
						echo $requestedProduct['quantity'];
					echo "</td>";
					echo "<td>N/A</td>";
					echo "<td>N/A</td>";
					break;
			}
			echo "</tr>";
		}
		echo "</tbody>";	
	echo "</table>";
	//pr($requestData);	
	if ($boolReclassificationPossible){
		echo $productSummary;
		echo "<br/>";
		echo $this->Form->create('Order');
		echo "<h3>Productos a Reclasificar </h3>";
		echo "<table id='reclasificaciones'>";
			echo "<thead>";
				echo "<tr>";
					echo "<td>Producto</td>";
					echo "<td>Materia Prima</td>";
					echo "<td># Reclasificar C</td>";
          echo "<td style='width:50%;'>Comentario</td>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody style='font-size:0.8em;'>";
			$i=0;
			foreach ($requestedProducts as $requestedProduct){
				switch ($requestedProduct['ProductType']['product_category_id']){
					case CATEGORY_PRODUCED:
						echo "<tr>";
							echo "<td class='reclassifiedproductid'>".$this->Form->input('ReclassificationProduct.'.$i.'.product_id',array('label'=>false,'value'=>$requestedProduct['Product']['id'],'class'=>'fixed','options'=>$products))."</td>";
							echo "<td class='reclassifiedrawmaterialid'>".$this->Form->input('ReclassificationProduct.'.$i.'.raw_material_id',array('label'=>false,'value'=>$requestedProduct['RawMaterial']['id'],'class'=>'fixed','options'=>$rawMaterials))."</td>";
							echo "<td class='reclassifiedreclassificationc'>".$this->Form->input('ReclassificationProduct.'.$i.'.reclassification_C',array('label'=>false,'value'=>$requestedProduct['reclassification_C'],'readonly'=>'readonly'))."</td>";
              echo "<td class='reclassifiedreclassificationcomment'>".$this->Form->input('ReclassificationProduct.'.$i.'.reclassification_comment',array('label'=>false,'value'=>$requestedProduct['reclassification_comment'],'readonly'=>'readonly'))."</td>";
						echo "</tr>";
						$i++;	
						break;
				}
			}
			echo "</tbody>";	
		echo "</table>";
		echo $this->Form->Submit(__('Reclasificar y Guardar Remisión'),array('id'=>'submit','name'=>'submit','style'=>'width:400px;'));	
		echo "<fieldset>";
			//echo "<legend>".__('Add Sale')."</legend>";
			echo "<div class='container-fluid'>";
				echo "<div class='row'>";
					echo "<div class='col-md-8'>";	
						echo $this->Form->input('warehouse_id',array('label'=>__('Warehouse'),'value'=>$requestData['Order']['warehouse_id'],'class'=>'fixed','empty'=>array('0'=>'Todas Bodegas')));
						echo $this->Form->input('order_date',array('label'=>__('Remission Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')));
						echo $this->Form->input('order_code',array('default'=>$requestData['Order']['order_code'],'class'=>'narrow','readonly'=>'readonly'));
						echo $this->Form->input('exchange_rate',array('default'=>$requestData['Order']['exchange_rate'],'class'=>'narrow','readonly'=>'readonly'));
						echo $this->Form->input('CashReceipt.bool_annulled',array('type'=>'checkbox','label'=>'Anulada','onclick'=>'return false;'));
						echo $this->Form->input('third_party_id',array('label'=>__('Client'),'default'=>'0','class'=>'fixed','empty'=>array('0'=>'Seleccione Cliente')));
            echo $this->Form->input('comment',array('type'=>'textarea','rows'=>3,'cols' => 25,'style'=>'width:60%','readonly'=>'readonly'));
    
						echo $this->Form->input('CashReceipt.currency_id',array('empty'=>array('0'=>'Seleccione Moneda'),'class'=>'narrow fixed'));
						
            echo $this->Form->input('CashReceipt.cash_receipt_type_id',array('class'=>'narrow fixed','label'=>'Tipo de Recibo','div'=>array('type'=>'hidden')));
						echo $this->Form->input('CashReceipt.cashbox_accounting_code_id',array('empty'=>array('0'=>'Seleccione Caja'),'class'=>'narrow fixed','options'=>$accountingCodes));
						echo $this->Form->input('CashReceipt.concept',array('readonly'=>'readonly'));
						echo $this->Form->input('CashReceipt.observation',array('type'=>'textarea', 'rows' => 3, 'cols' => 25,'style'=>'width:60%','readonly'=>'readonly'));
					echo "</div>";
					echo "<div class='col-md-4'>";
						echo "<h4>".__('Remission Price')."</h4>";			
						echo $this->Form->input('CashReceipt.amount',array('label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;'));
          echo "</div>";
					
					echo "<table id='productsForRemission'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>".__('Product')."</th>";
								echo "<th>".__('Raw Material')."</th>";
								echo "<th style='width:80px;'>".__('Quality')."</th>";
									echo "<th class='centered narrow'>".__('Quantity Product')."</th>";
									echo "<th class='currencyinput'>".__('Unit Price')."</th>";
									echo "<th class='currencyinput'>".__('Subtotal')."</th>";
								//echo "<th></th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
						for ($i=0;$i<count($requestData['Product']);$i++) { 
							//pr($requestData['Product'][$i]);
							if ($requestData['Product'][$i]['product_quantity']>0){
								$requestedProduct=$requestData['Product'][$i];
								echo "<tr>";
									echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',array('label'=>false,'value'=>$requestedProduct['product_id'],'class'=>'fixed','empty' =>array(0=>__('Choose a Product'))))."</td>";
									echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',array('label'=>false,'value'=>$requestedProduct['raw_material_id'],'class'=>'fixed','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
									if (!empty($requestProducts[$i]['Product']['production_result_code_id'])){
										echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'value'=>$requestedProduct['Product']['production_result_code_id'],'class'=>'fixed'))."</td>";
									}
									else {
										echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',array('label'=>false,'default'=>'0','div'=>array('class'=>'hidden')))."</td>";
									}
									echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',array('type'=>'decimal','label'=>false,'value'=>$requestedProduct['product_quantity'],'readonly'=>'readonly'))."</td>";
									echo "<td class='productunitprice'>".$this->Form->input('Product.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'value'=>$requestedProduct['product_unit_price'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
									echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'value'=>$requestedProduct['product_total_price'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'))."</td>";
									//echo "<td><button class='removeMaterial'>".__('Remove Sale Item')."</button></td>";
								echo "</tr>";
							}
						}
					
						echo "</tbody>";
					echo "</table>";
				echo "</div>";
			echo "</div>";
		echo "</fieldset>";
    echo $this->Form->end();
	}
	else {
		echo $reasonForNoReclassificationPossible;
	}
?>
</div>