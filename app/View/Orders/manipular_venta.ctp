<script>
  $('body').on('submit','#OrderManipularVentaForm',function(e){
		//e.preventDefault();
    $('#submit').attr('disabled','disabled');
    return true;    
	});	
  
	$(document).ready(function(){
		$('select.fixed option:not(:selected)').attr('disabled', true);
    showServiceCosts();
	});
  var jsOtherProducts=<?php  echo json_encode($otherProducts); ?>;
  function showServiceCosts(){
    var otherProductPresent=false;
    $('#productsForSale tbody tr').each(function(){
      var productid=$(this).find('td.productid select').val()
      var otherProductRow=$.inArray(productid,jsOtherProducts) > -1;
      if (otherProductRow){
        otherProductPresent=true
      }
    });
    
    if (otherProductPresent){
      $('#productsForSale tbody tr').each(function(){
        $(this).find('td.serviceunitcost').removeClass('hidden')
        $(this).find('td.servicetotalcost').removeClass('hidden')
        var productid=$(this).find('td.productid select').val()
        var otherProductRow=$.inArray(productid,jsOtherProducts) > -1;
        if (otherProductRow){
          $(this).find('td.serviceunitcost div.input').removeClass('hidden')
          $(this).find('td.servicetotalcost div.input').removeClass('hidden')
          otherProductPresent=true
        }
        else {
          $(this).find('td.serviceunitcost div.input').addClass('hidden')
          $(this).find('td.servicetotalcost div.input').addClass('hidden')
        }
      });
      $('#productsForSale thead tr th.servicecostheader').each(function(){
        $(this).removeClass('hidden')
      });
    }
    else {
      $('#productsForSale tbody tr').each(function(){
        $(this).find('td.serviceunitcost').addClass('hidden')
        $(this).find('td.servicetotalcost').addClass('hidden')        
      });
      
      $('#productsForSale thead tr th.servicecostheader').each(function(){
          $(this).addClass('hidden')
      });
    }
  }
</script>
<div class="orders manipular fullwidth">
<?php
	echo "<h2>Manipular Venta</h2>";
	echo "<div>Estás intentando de registrar una venta con los siguientes datos:</div>";
	echo "<br/>";
	echo "<dl>";
		echo "<dt>Código de Venta</dt>";
		echo "<dd>".$requestData['Order']['order_code']."</dd>";
		echo "<dt>Fecha de Venta y Reclasificación</dt>";
		echo "<dd>".$requestData['Order']['order_date']['day']."-".$requestData['Order']['order_date']['month']."-".$requestData['Order']['order_date']['year']."</dd>";
	echo "</dl>";
	echo "<br/>";
	
	echo "<h3>Los productos que se quieren vender son:</h3>";
	echo "<br/>";
	echo "<table>";
		echo "<thead>";
			echo "<tr>";
				echo "<td>Producto</td>";
				echo "<td>Cantidad Requerida</td>";
				echo "<td>Cantidad A en Bodega</td>";
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
					if ($requestedProduct['ProductRequest']['product_quantity']>$requestedProduct['quality_A']){
						echo "<td class='redbg'>";
					}
					else {
						echo "<td class='greenbg'>";
					}
						echo $requestedProduct['quality_A'];
					echo "</td>";
					echo "<td>".$requestedProduct['quality_B']."</td>";
					echo "<td>".$requestedProduct['quality_C']."</td>";
					break;
				case CATEGORY_RAW:
				case CATEGORY_OTHER:
					echo "<td>".$requestedProduct['Product']['name']."</td>";
					echo "<td>".$requestedProduct['ProductRequest']['product_quantity']."</td>";
				
					if ($requestedProduct['ProductRequest']['product_quantity']>$requestedProduct['quantity']){
						echo "<td class='redbg'>";
					}
					else {
						echo "<td class='greenbg'>";
					}	
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
					echo "<td># Reclasificar B</td>";
					echo "<td># Reclasificar C</td>";
          echo "<td style='width:40%;'>Comentario</td>";
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
							echo "<td class='reclassifiedreclassificationb'>".$this->Form->input('ReclassificationProduct.'.$i.'.reclassification_B',array('label'=>false,'value'=>$requestedProduct['reclassification_B'],'readonly'=>'readonly'))."</td>";
							echo "<td class='reclassifiedreclassificationc'>".$this->Form->input('ReclassificationProduct.'.$i.'.reclassification_C',array('label'=>false,'value'=>$requestedProduct['reclassification_C'],'readonly'=>'readonly'))."</td>";
              echo "<td class='reclassifiedreclassificationcomment'>".$this->Form->input('ReclassificationProduct.'.$i.'.reclassification_comment',array('label'=>false,'value'=>$requestedProduct['reclassification_comment'],'readonly'=>'readonly'))."</td>";
						echo "</tr>";
						$i++;	
						break;
				}
			}
			echo "</tbody>";	
		echo "</table>";
		echo $this->Form->Submit(__('Reclasificar y Guardar Venta'),array('id'=>'submit','name'=>'submit','style'=>'width:400px;'));	
		echo "<fieldset>";
			//echo "<legend>".__('Add Sale')."</legend>";
			echo "<div class='container-fluid'>";
				echo "<div class='row'>";
					echo "<div class='col-md-8'>";	
						echo $this->Form->input('warehouse_id',array('label'=>__('Warehouse'),'value'=>$requestData['Order']['warehouse_id'],'class'=>'fixed','empty'=>array('0'=>'Todas Bodegas')));
						echo $this->Form->input('order_date',array('label'=>__('Sale Date'),'dateFormat'=>'DMY','minYear'=>2014,'maxYear'=>date('Y')));
						echo $this->Form->input('order_code',array('default'=>$requestData['Order']['order_code'],'class'=>'narrow','readonly'=>'readonly'));
						echo $this->Form->input('exchange_rate',array('default'=>$requestData['Order']['exchange_rate'],'class'=>'narrow','readonly'=>'readonly'));
						echo $this->Form->input('Invoice.bool_annulled',array('type'=>'checkbox','label'=>'Anulada','onclick'=>'return false;'));
						echo $this->Form->input('third_party_id',array('label'=>__('Client'),'default'=>'0','class'=>'fixed','empty'=>array('0'=>'Seleccione Cliente')));
            echo $this->Form->input('comment',array('type'=>'textarea','rows'=>3,'cols' => 25,'style'=>'width:60%','readonly'=>'readonly'));
						
            echo $this->Form->input('Invoice.currency_id',array('default'=>CURRENCY_CS,'empty'=>array('0'=>'Seleccione Moneda'),'class'=>'narrow fixed'));
						
						echo $this->Form->input('Invoice.bool_credit',array('type'=>'checkbox','label'=>'Crédito','onclick'=>'return false;'));
						echo "<div id='divDueDate'>";
							echo $this->Form->input('Invoice.due_date',array('type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY'));
						echo "</div>";
						echo $this->Form->input('Invoice.cashbox_accounting_code_id',array('empty'=>array('0'=>'Seleccione Caja'),'class'=>'narrow fixed','options'=>$accountingCodes,'default'=>ACCOUNTING_CODE_CASHBOX_MAIN));
						echo $this->Form->input('Invoice.bool_retention',array('type'=>'checkbox','label'=>'Retención','onclick'=>'return false;'));
						
						echo $this->Form->input('Invoice.retention_number',array('label'=>'Número Retención','readonly'=>'readonly'));
						echo $this->Form->input('Invoice.bool_IVA',array('type'=>'checkbox','label'=>'Se aplica IVA','onclick'=>'return false;'));
					echo "</div>";
					echo "<div class='col-md-4'>";
						echo "<h4>".__('Sale Price')."</h4>";			
						echo $this->Form->input('Invoice.sub_total_price',array('label'=>__('SubTotal'),'id'=>'subTotalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;'));
						echo $this->Form->input('Invoice.IVA_price',array('label'=>__('IVA'),'id'=>'ivaPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;'));
						echo $this->Form->input('Invoice.total_price',array('label'=>__('Total'),'id'=>'totalPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;'));
						echo $this->Form->input('Invoice.retention_amount',array('label'=>__('Retención'),'id'=>'retentionPrice','readonly'=>'readonly','default'=>'0','between'=>'<span class="currencyrighttop">C$ </span>','type'=>'decimal','style'=>'width:40%;'));
					echo "</div>";
					
					echo "<table id='productsForSale'>";
						echo "<thead>";
							echo "<tr>";
								echo "<th>".__('Product')."</th>";
								echo "<th>".__('Raw Material')."</th>";
								echo "<th>".__('Quality')."</th>";
								echo "<th>".__('Quantity Product')."</th>";
                
                echo "<th class='servicecostheader currencyinput'>".__('Costo Unitario Otro')."</th>";
                echo "<th class='servicecostheader currencyinput'>".__('Costo Total Otro')."</th>";
                
								echo "<th>".__('Unit Price')."</th>";
								echo "<th>".__('Total Price')."</th>";
								//echo "<th></th>";
							echo "</tr>";
						echo "</thead>";
						echo "<tbody>";
            //pr($requestData);
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
									  
                  echo "<td class='serviceunitcost'>".$this->Form->input('Product.'.$i.'.service_unit_cost',['type'=>'decimal','label'=>false,'value'=>$requestedProduct['service_unit_cost'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'])."</td>";
                  echo "<td  class='servicetotalcost'>".$this->Form->input('Product.'.$i.'.service_total_cost',['type'=>'decimal','label'=>false,'value'=>$requestedProduct['service_total_cost'],'readonly'=>'readonly','before'=>'<span class=\'currency\'>C$</span>'])."</td>";
                  
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