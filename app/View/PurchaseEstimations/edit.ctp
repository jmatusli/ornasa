<script>
/*
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
		var purchaseestimationdateday=$('#PurchaseEstimationPurchaseEstimationDateDay').val();
		var purchaseestimationdatemonth=$('#PurchaseEstimationPurchaseEstimationDateMonth').val();
		var purchaseestimationdateyear=$('#PurchaseEstimationPurchaseEstimationDateYear').val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>purchase_estimations/getnewpurchaseestimationcode/',
			data:{"clientid":clientid,"purchaseestimationdateday":purchaseestimationdateday,"purchaseestimationdatemonth":purchaseestimationdatemonth,"purchaseestimationdateyear":purchaseestimationdateyear},
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
*/
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
						//$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').removeClass('hidden');
					}
					else {
						$('#'+affectedproductid).closest('tr').find('td.rawmaterialid div').addClass('hidden');
						//$('#'+affectedproductid).closest('tr').find('td.productionresultcodeid div').addClass('hidden');
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
		//var currencyid=$('#InvoiceCurrencyId').children("option").filter(":selected").val();
		var totalPrice=0;
		$("#productsForRequest tbody tr:not(.hidden)").each(function() {
			var currentPrice = parseFloat($(this).find('td.producttotalprice div input').val());
			totalPrice = totalPrice + currentPrice;
		});
		$('#PurchaseEstimationSubtotalPrice').val(roundToTwo(totalPrice));
		
		
		return false;
	}
	
	$(document).ready(function(){
		
	});
</script>
<div class="purchase_estimations form fullwidth">
<?php 
  echo $this->Form->create('PurchaseEstimation'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit Purchase Estimation')." ".$this->request->data['PurchaseEstimation']['purchase_estimation_code']."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo "<div class='col-md-6'>";
          echo $this->Form->input('id');
          switch ($roleId){
            case ROLE_ADMIN:
              echo $this->Form->input('client_id',['empty'=>[0=>"--Seleccione cliente--"]]);
              break;
            default:
              echo $this->Form->input('client_id',['type'=>'hidden']);
              break;
          }
          echo $this->Form->input('purchase_estimation_date',['dateFormat'=>'DMY Hi','minYear'=>2014,'maxYear'=>date('Y')]);
          echo $this->Form->input('purchase_estimation_code',['class'=>'narrow']);
          echo $this->Form->input('bool_annulled');
        echo "</div>";
        echo "<div class='col-md-4'>";
          switch ($roleId){
            case ROLE_ADMIN:
              echo $this->Form->input('subtotal_price',['label'=>'Subtotal']);
              break;
            default:
              echo $this->Form->input('subtotal_price',['type'=>'hidden']);
              break;
          
          }
          echo $this->Form->input('currency_id',['type'=>'hidden']);
          echo $this->Form->input('comment',['rows'=>2]);
        echo "</div>";
        echo "<div class='col-md-2'>";
          echo "<h3>".__('Actions')."</h3>";
          echo "<ul style='list-style:none;'>";
            if ($bool_delete_permission){
              echo "<li>".$this->Form->postLink(__('Eliminar Estimación de Compras'), array('action' => 'delete', $this->Form->value('PurchaseEstimation.id')), array(), __('Está seguro que quiere eliminar la estimación de compras # %s?', $this->Form->value('PurchaseEstimation.purchase_estimation_code')))."</li>";
            }
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
	 echo "<div>";
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
						echo "<td class='productid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_id',['label'=>false,'default'=>'0','empty' =>[0=>__('Elige un Producto')]])."</td>";
            echo "<td class='rawmaterialid'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>array(0=>__('Choose a Raw Material'))))."</td>";
            echo $this->Form->input('PurchaseEstimationProduct.'.$i.'.production_result_code_id',['type'=>'hidden','label'=>false,'default'=>PRODUCTION_RESULT_CODE_A]);          
            echo "<td class='productquantity'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_quantity',['type'=>'decimal','label'=>false])."</td>";
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'decimal','default'=>0,'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='productunitprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_unit_price',['type'=>'hidden','default'=>0,'label'=>false])."</td>";
                break;
            }
            switch ($roleId){
              case ROLE_ADMIN: 
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'decimal','default'=>0,'label'=>false])."</td>";
                break;
              default:
                echo "<td  class='producttotalprice'>".$this->Form->input('PurchaseEstimationProduct.'.$i.'.product_total_price',['type'=>'hidden','default'=>0,'label'=>false])."</td>";
                break;
            }
            echo "<td  class='description'>".$this->Form->textarea('PurchaseEstimationProduct.'.$i.'.description',['label'=>false])."</td>";
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
  echo "</fieldset>";
 
  echo $this->Form->end();
?>
</div>
