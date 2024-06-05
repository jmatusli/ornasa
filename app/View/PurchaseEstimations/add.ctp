<script>
  $('body').on('change','#PurchaseEstimationClientId',function(){	
		getNewPurchaseEstimationCode();
	});
	$('body').on('change','#PurchaseEstimationPurchaseEstimationDateDay',function(){	
		getNewPurchaseEstimationCode();
	});
	$('body').on('change','#PurchaseEstimationPurchaseEstimationDateMonth',function(){	
		getNewPurchaseEstimationCode();
	});
	$('body').on('change','#PurchaseEstimationPurchaseEstimationDateYear',function(){	
		getNewPurchaseEstimationCode();
	});
	function getNewPurchaseEstimationCode(){
		var clientid=$('#PurchaseEstimationClientId').val();
		var clientrequestdateday=$('#PurchaseEstimationPurchaseEstimationDateDay').val();
		var clientrequestdatemonth=$('#PurchaseEstimationPurchaseEstimationDateMonth').val();
		var clientrequestdateyear=$('#PurchaseEstimationPurchaseEstimationDateYear').val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>client_requests/getnewclientrequestcode/',
			data:{"clientid":clientid,"clientrequestdateday":clientrequestdateday,"clientrequestdatemonth":clientrequestdatemonth,"clientrequestdateyear":clientrequestdateyear},
			cache: false,
			type: 'POST',
			success: function (requestcode) {
				$('#PurchaseEstimationPurchaseEstimationCode').val(requestcode);
				//alert(quotationcode);
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
	}

	$('body').on('click','#addMaterial',function(){
		var tableRow=$('#productsForRequest tbody tr.hidden:first');
		tableRow.removeClass("hidden");
	});
	$('body').on('click','.removeMaterial',function(){
		var tableRow=$(this).parent().parent().remove();
		calculateTotal();
	});	
  
  $('body').on('change','.productid div select',function(){	
		var productid=$(this).val();
		var affectedproductid=$(this).attr('id');
		if (productid>0){
			$.ajax({
				url: '<?php echo $this->Html->url('/'); ?>products/getproductcategoryid/'+productid,
				cache: false,
				type: 'GET',
				success: function (categoryid) {
					if (categoryid==<?php echo CATEGORY_PRODUCED; ?>){
						$('#'+affectedproductid).closest('tr').find('td.rawmaterialid div').removeClass('hidden');
					}
					else {
						$('#'+affectedproductid).closest('tr').find('td.rawmaterialid div').addClass('hidden');
					}
				},
				error: function(e){
					alert(e.responseText);
					console.log(e);
				}
			});
		}
	});	
  
  $('body').on('change','.productunitprice div input',function(){	
		var unitprice=$(this).val();
		var productquantity=parseFloat($(this).closest('tr').find('td.productquantity div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
	});	
	
	$('body').on('change','.productquantity div input',function(){	
		var productquantity=$(this).val();
		var unitprice=parseFloat($(this).closest('tr').find('td.productunitprice div input').val());
		$(this).closest('tr').find('td.producttotalprice div input').val(roundToTwo(unitprice*productquantity));
		calculateTotal();
	});	
	
	function calculateTotal(){
		var totalPrice=0;
		$("#productsForRequest tbody tr:not(.hidden)").each(function() {
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
		$('#PurchaseEstimationSubtotalPrice').val(roundToTwo(totalPrice));
		
		return false;
	}
	
	function formatNumbers(){
		//$("td.number span.amountright").each(function(){
    $("td.number span").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	//function formatUSDCurrencies(){
	//	$("td.USDcurrency").each(function(){
	//		
	//		if (parseFloat($(this).find('.amountright').text())<0){
	//			$(this).find('.amountright').prepend("-");
	//		}
	//		$(this).find('.amountright').number(true,2);
	//		$(this).find('.currency').text("US$");
	//	});
	//}
	
	$(document).ready(function(){
    calculateTotal();
		formatNumbers();
		formatCSCurrencies();
		//formatUSDCurrencies();
    
	});
</script>

<div class="requests form fullwidth">
<?php 
  echo $this->Form->create('PurchaseEstimation'); 
	echo "<fieldset>";
		echo "<legend>".__('Add Purchase Estimation')."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo "<div class='col-md-6'>";
          switch ($roleId){
            case ROLE_ADMIN:
              echo $this->Form->input('client_id',['default'=>$clientId,'empty'=>[0=>"--Seleccione cliente--"]]);
              echo $this->Form->Submit('Cargar compras realizadas',['id'=>'loadpurchases','name'=>'loadpurchases','style'=>'width:30em;']);
              break;
            default:
              echo $this->Form->input('client_id',['default'=>$clientId,'type'=>'hidden']);
              break;
          }
          echo $this->Form->input('purchase_estimation_date',['dateFormat'=>'DMY Hi','minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('purchase_estimation_code',['default'=>$newPurchaseEstimationCode,'class'=>'narrow']);
          echo $this->Form->input('bool_annulled');
        echo "</div>";
        echo "<div class='col-md-4'>";
          switch ($roleId){
            case ROLE_ADMIN:
              echo $this->Form->input('subtotal_price',['label'=>'Subtotal','default'=>0]);
              break;
            default:
              echo $this->Form->input('subtotal_price',['default'=>0,'type'=>'hidden']);
              break;
          
          }
          echo $this->Form->input('currency_id',['type'=>'hidden','value'=>CURRENCY_CS]);
          echo $this->Form->input('comment',['rows'=>2]);
        echo "</div>";
        echo "<div class='col-md-2'>";
          echo "<h3>".__('Actions')."</h3>";
          echo "<ul>";
            echo "<li>".$this->Html->link(__('List Purchase Estimations'), array('action' => 'index'))."</li>";
            if ($roleId == ROLE_ADMIN){
              echo "<br/>";
              echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
              echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
            }
          echo "</ul>";        
        echo "</div>";
      echo "</div>";
    echo "</div>";
	echo "</fieldset>";
  echo "<div>";
    echo "<p class='comment'>Por defecto se analizan las ventas y remisiones de los últimos 100 días.  Si un producto se compró en los últimos 30 días o si un producto se compró mas que una vez en los últimos 100 días, se apunto automáticamente el producto en la lista de la estimación, al último precio que se vendió.  Más información se halla abajo para ayudarle a modificar la estimación.</p>";
		echo "<table id='productsForRequest'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
          echo "<th>".__('Preforma')."</th>";
          echo "<th>".__('Quantity')."</th>";
          if ($roleId==ROLE_ADMIN){
            echo "<th>".__('Unit Price')."</th>";
          }
          if ($roleId==ROLE_ADMIN){
            echo "<th>".__('Total Price')."</th>";
          }
          echo "<th>".__('Description')."</th>";
					echo "<th></th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
      $counter=0;
			if (count($requestProducts)>0){
				for ($i=0;$i<count($requestProducts);$i++) { 
					//pr($requestProducts[$i]['QuotationProduct']);
					echo "<tr row='".$i."'>";
						echo "<td class='productid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_id',['label'=>false,'value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_id'],'empty' =>[0=>__('Elige un Producto')]])."</td>";
            echo "<td class='rawmaterialid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.raw_material_id',array('label'=>false,'value'=>$requestProducts[$i]['PurchaseEstimationProduct']['raw_material_id'],'empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
            echo $this->Form->input('PurchaseEstimationProduct.'.$i.'.production_result_code_id',['type'=>'hidden','label'=>false,'value'=>PRODUCTION_RESULT_CODE_A]);          
            echo "<td class='productquantity'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_quantity',['type'=>'decimal','value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_quantity'],'label'=>false])."</td>";
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'decimal','value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_unit_price'],'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'hidden','value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_unit_price'],'label'=>false])."</td>";
                break;
            }
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'decimal','value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_total_price'],'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'hidden','value'=>$requestProducts[$i]['PurchaseEstimationProduct']['product_total_price'],'label'=>false])."</td>";
                break;
            }
            echo "<td  class='description'>".$this->Form->textarea('PurchaseEstimationProduct.'.$i.'.description',['label'=>false,'value'=>$requestProducts[$i]['PurchaseEstimationProduct']['description']])."</td>";
            echo "<td><button class='removeMaterial'>".__('Remover Producto')."</button></td>";
            
					echo "</tr>";			
					$counter++;
				}
			}
      elseif (count($purchaseEstimation['estimatedProducts'])>0){
				for ($i=0;$i<count($purchaseEstimation['estimatedProducts']);$i++) {
          $purchaseEstimationProduct=$purchaseEstimation['estimatedProducts'][$i];
          //pr($purchaseEstimationProduct);
          //echo "raw material id is ".$purchaseEstimationProduct['PurchaseEstimationProduct']['raw_material_id']."<br/>";
					echo "<tr row='".$i."'>";
						echo "<td class='productid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_id',['label'=>false,'value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_id'],'empty' =>[0=>__('Elige un Producto')]])."</td>";
            echo "<td class='rawmaterialid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.raw_material_id',['label'=>false,'value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['raw_material_id'],'empty' =>[0=>__('Choose a Raw Material')]])."</td>";
            echo $this->Form->input('PurchaseEstimationProduct.'.$i.'.production_result_code_id',['type'=>'hidden','label'=>false,'value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['production_result_code_id']]);          
            echo "<td class='productquantity'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_quantity',['type'=>'decimal','value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_quantity'],'label'=>false])."</td>";
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'decimal','value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_unit_price'],'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'hidden','value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_unit_price'],'label'=>false])."</td>";
                break;
            }
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'decimal','value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_total_price'],'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'hidden','value'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['product_total_price'],'label'=>false])."</td>";
                break;
            }
            echo "<td  class='description'>".$this->Form->textarea('PurchaseEstimationProduct.'.$i.'.description',['label'=>false,'default'=>$purchaseEstimationProduct['PurchaseEstimationProduct']['description']])."</td>";
            echo "<td><button class='removeMaterial'>".__('Remover Producto')."</button></td>";
            
					echo "</tr>";			
					$counter++;
				}  
      }
			for ($j=$counter;$j<25;$j++) { 
				if ($j==$counter){
					echo "<tr row='".$j."'>";
				} 
				else {
					echo "<tr row='".$j."' class='hidden'>";
				} 
				echo "<td class='productid'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_id',['label'=>false,'default'=>'0','empty' =>[0=>__('Elige un Producto')]])."</td>";
        echo "<td class='rawmaterialid'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
        echo $this->Form->input('PurchaseEstimationProduct.'.$j.'.production_result_code_id',['type'=>'hidden','label'=>false,'default'=>PRODUCTION_RESULT_CODE_A]);          
        echo "<td class='productquantity'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_quantity',['type'=>'decimal','label'=>false])."</td>";
        switch ($roleId){
          case ROLE_ADMIN: 
            echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_unit_price',['type'=>'decimal','default'=>0,'label'=>false])."</td>";
            break;
          default:
            echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_unit_price',['type'=>'hidden','default'=>0,'label'=>false])."</td>";
            break;
        }
        switch ($roleId){
          case ROLE_ADMIN: 
            echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_total_price',['type'=>'decimal','default'=>0,'label'=>false])."</td>";
            break;
          default:
            echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$j.'.product_total_price',['type'=>'hidden','default'=>0,'label'=>false])."</td>";
            break;
        }
        echo "<td  class='description'>".$this->Form->textarea('PurchaseEstimationProduct.'.$j.'.description',['label'=>false])."</td>";
				echo "<td><button class='removeMaterial'>".__('Remover Producto')."</button></td>";
        
        
        
        
				echo "</tr>";
			}
			echo "</tbody>";
		echo "</table>";
	echo "</div>";
	echo "<button id='addMaterial' type='button'>".__('Añadir Producto')."</button>";	
  echo $this->Form->Submit(__('Submit'));
  echo $this->Form->end();
  
  echo "<div>";
    echo "<h3>Productos comprados en los últimos 100 días</h3>";
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>Producto</th>";
        $tableHead.="<th>Materia Prima</th>";
        $tableHead.="<th>Código Producción</th>";
        $tableHead.="<th class='centered'>Cantidad total vendido</th>";
        $tableHead.="<th class='centered'># Compras</th>";
        $tableHead.="<th class='centered'>Ultima compra hace X días</th>";
        $tableHead.="<th class='centered'>Ultimo precio unitario</th>";
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $tableBody="";
    $tableBody.="<tbody>";
    foreach ($purchaseEstimation['processedProducts'] as $processedProduct){
      foreach ($processedProduct as $processedRawMaterial){
        foreach ($processedRawMaterial as $fullProduct){
          $tableBody.="<tr>";
            $tableBody.="<td>".$fullProduct['productName']."</td>";
            $tableBody.="<td>".$fullProduct['rawMaterialName']."</td>";
            $tableBody.="<td>".$fullProduct['productionResultCode']."</td>";
            $tableBody.="<td class='centered number'><span>".$fullProduct['totalQuantity']."</span></td>";
            $tableBody.="<td class='centered number'><span>".$fullProduct['numberOfTimesBought']."</span></td>";
            $tableBody.="<td class='centered'>".$fullProduct['lastTimeBoughtDaysAgo']."</td>";
            $tableBody.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$fullProduct['lastUnitPricePaid']."</span></td>";
          $tableBody.="</tr>";
        }
      }
      
    }
    $tableBody.="</tbody>";
    echo "<table>".$tableHead.$tableBody."</table>";
    
  echo "</div>";
    $soldProductsTableHead="<thead>";
      $soldProductsTableHead.="<tr>";
        $soldProductsTableHead.="<th>Fecha Venta</th>";
        $soldProductsTableHead.="<th>Venta/Remisión</th>";
        $soldProductsTableHead.="<th>Producto</th>";
        $soldProductsTableHead.="<th>Materia Prima</th>";
        $soldProductsTableHead.="<th>Calidad</th>";
        $soldProductsTableHead.="<th class='centered'>Cantidad</th>";
        $soldProductsTableHead.="<th class='centered'>Precio Unitario</th>";
        $soldProductsTableHead.="<th class='centered'>Precio Total</th>";
      $soldProductsTableHead.="</tr>";
    $soldProductsTableHead.="</thead>";
    
    $totalQuantity=0;
    $totalPrice=0;
    $soldProductsTableRows="";
    foreach ($purchaseEstimation['pastPurchases'] as $pastPurchase){
      foreach ($pastPurchase['StockMovement'] as $stockMovement){
        $orderDateTime=new DateTime($pastPurchase['Order']['order_date']);
        $totalQuantity+=$stockMovement['product_quantity'];
        $totalPrice+=$stockMovement['product_total_price'];
        
        $soldProductsTableRows.="<tr>";
          $soldProductsTableRows.="<td>".$orderDateTime->format('d-m-Y')."</td>";  
          $soldProductsTableRows.="<td>".$this->Html->Link($pastPurchase['Order']['order_code'],['controller'=>'orders','action'=>($stockMovement['production_result_code_id']>2?'verRemision':'verVenta'),$pastPurchase['Order']['id']])."</td>";  
          $soldProductsTableRows.="<td>".$stockMovement['Product']['name']."</td>";  
          
          $soldProductsTableRows.="<td>".(!empty($stockMovement['StockItem']['RawMaterial'])?$stockMovement['StockItem']['RawMaterial']['name']:"")."</td>";  
          $soldProductsTableRows.="<td>".(!empty($stockMovement['ProductionResultCode'])?$stockMovement['ProductionResultCode']['code']:"")."</td>";  
          $soldProductsTableRows.="<td class='centered number'><span>".$stockMovement['product_quantity']."</span></td>";  
          $soldProductsTableRows.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$stockMovement['product_unit_price']."</span></td>";  
          $soldProductsTableRows.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$stockMovement['product_total_price']."</span></td>";  
          
        $soldProductsTableRows.="</tr>";
        
      }
    }
    $totalRow="<tr class='totalrow'>";
      $totalRow.="<td>Total</td>";  
      $totalRow.="<td></td>";  
      $totalRow.="<td></td>";  
      $totalRow.="<td></td>";  
      $totalRow.="<td></td>";  
      $totalRow.="<td class='centered number'><span>".$totalQuantity."</span></td>";  
      $totalRow.="<td></td>";  
      $totalRow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalPrice."</span></td>";  
    $totalRow.="</tr>";
    $soldProductsTableBody="<tbody>".$totalRow.$soldProductsTableRows.$totalRow."</tbody>";
    $soldProductsTable="<table cellpadding='0' cellspacing='0' id='productos_comprados'>".$soldProductsTableHead.$soldProductsTableBody."</table>";
    echo "<h3>Detalle productos comprados en los últimos 100 días</h3>";
    echo $soldProductsTable; 
  echo "</div>";
?>  
</div>