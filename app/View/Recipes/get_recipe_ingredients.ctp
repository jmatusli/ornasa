<?php
  if (empty($recipeIngredients)){
    // no sales order present, code copied directly from crear venta
		echo '<h4>No se encontraron ingredientes para esta receta</h4>';
	}
  else {
		$tableHead='';
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>Ingrediente</th>";
        $tableHead.="<th>Cantidad</th>";
        $tableHead.="<th>Unidad</th>";
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $tableRows='';
    $counter=0;
    if(!empty($requestIngredients['Products'])){
      foreach ($requestIngredients['Products'] as $requestItem){
        $quantityForOne=0;
        if (array_key_exists('quantity_for_one',$requestItem)){
          $quantityForOne=$requestItem['quantity_for_one'];
        }
        else {
          foreach ($recipeIngredients as $recipeItem){
            if ($recipeItem['RecipeItem']['product_id'] == $requestItem['product_id']){
              $quantityForOne= $recipeItem['RecipeItem']['quantity'];
            }
          }
        }
        if ($quantityForOne == 0){
          $quantityForOne=$requestItem['product_quantity']/$finishedProductQuantity;
        }
        $tableRow='';
        $tableRow.='<tr  class="ingredientrow">';
          $tableRow.='<td class="ingredientid">';
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.product_id',[
              'label'=>false,
              'default'=>$requestItem['product_id'],
              //'type'=>'hidden',
            ]);
            //$tableRow.=$recipeItem['Product']['name'];
          $tableRow.='</td>';
          $tableRow.='<td class="ingredientquantity">';
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.quantity_for_one',[
              //'label'=>false,
              'value'=>$quantityForOne,
              'class'=>'unitquantity',
              'type'=>'hidden',
            ]);
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.quantity',[
              'label'=>false,
              'value'=>$requestItem['product_quantity'],
              'style'=>'width:100%!important;',
              'class'=>'quantity',
            ]);
          $tableRow.='</td>';
          $tableRow.='<td>'.$this->Form->Input('RecipeItem.'.$counter.'.unit_id',[
            'label'=>false,
            'value'=>$requestItem['unit_id'],
            'class'=>'fixed',
            'options'=>$units,
          ]).'</td>';
        $tableRow.='</tr>';
        $tableRows.=$tableRow;
        $counter++;
      }
    }
    else {
      foreach ($recipeIngredients as $recipeItem){
        $tableRow='';
        $tableRow.='<tr class="ingredientrow">';
          $tableRow.='<td class="ingredientid" >';
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.product_id',[
              'label'=>false,
              'default'=>$recipeItem['RecipeItem']['product_id'],
              //'type'=>'hidden',
            ]);
            //$tableRow.=$recipeItem['Product']['name'];
          $tableRow.='</td>';
          $tableRow.='<td class="ingredientquantity">';
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.quantity_for_one',[
              //'label'=>false,
              'value'=>$recipeItem['RecipeItem']['quantity'],
              'class'=>'unitquantity',
              'type'=>'hidden',
            ]);
            $tableRow.=$this->Form->Input('RecipeItem.'.$counter.'.quantity',[
              'label'=>false,
              'value'=>$finishedProductQuantity*$recipeItem['RecipeItem']['quantity'],
              'style'=>'width:100%!important;',
              'class'=>'quantity',
            ]);
          $tableRow.='</td>';
          $tableRow.='<td>'.$this->Form->Input('RecipeItem.'.$counter.'.unit_id',[
            'label'=>false,
            'value'=>$recipeItem['RecipeItem']['unit_id'],
            'class'=>'fixed',
            'options'=>$units,
          ]).'</td>';
        $tableRow.='</tr>';
        $tableRows.=$tableRow;
        $counter++;
      }
    }    
    $tableBody='<tbody>'.$tableRows.'</tbody>';
    /*
			echo "<tbody>";
			$subtotal=0;
      $totalPrice=0;
      for ($i=0;$i<count($productsForSalesOrder);$i++) { 
        $subtotal+=round($productsForSalesOrder[$i]['SalesOrderProduct']['product_quantity']*$productsForSalesOrder[$i]['SalesOrderProduct']['product_unit_price'],2);
        if ($productsForSalesOrder[$i]['SalesOrderProduct']['currency_id'] == $currencyId){
          $unitPrice=$productsForSalesOrder[$i]['SalesOrderProduct']['product_unit_price'];
        }
        else {
          if ($currencyId == CURRENCY_USD){
            $unitPrice=$productsForSalesOrder[$i]['SalesOrderProduct']/$exchangeRate;
          }
          else {
            $unitPrice=$productsForSalesOrder[$i]['SalesOrderProduct']*$exchangeRate;
          }
        }
        $totalPrice=round($productsForSalesOrder[$i]['SalesOrderProduct']['product_quantity']*$unitPrice,2);

				echo "<tr row='Fact_".$i."'>";
          echo "<td class='productid'>".$this->Form->input('Product.'.$i.'.product_id',[
            'label'=>false,
            'value'=>$productsForSalesOrder[$i]['SalesOrderProduct']['product_id'],
            'empty' =>[0=>'-- Product --'],
            'class'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?'fixed':''),
          ])."</td>";
          echo "<td class='rawmaterialid'>".$this->Form->input('Product.'.$i.'.raw_material_id',[
            'label'=>false,
            'value'=>(array_key_exists('raw_material_id',$productsForSalesOrder[$i]['SalesOrderProduct'])?$productsForSalesOrder[$i]['SalesOrderProduct']['raw_material_id']:0),
            'type'=>(empty($productsForSalesOrder[$i]['SalesOrderProduct']['raw_material_id'])?'hidden':'select'),
            'empty' =>[0=>'-- Preforma --'],
            'class'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?'fixed':''),
          ])."</td>";
          if (!empty($productsForSalesOrder[$i]['SalesOrderProduct']['raw_material_id'])){
            echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',['label'=>false,'value'=>PRODUCTION_RESULT_CODE_A])."</td>";
          }
          else {
            echo "<td class='productionresultcodeid'>".$this->Form->input('Product.'.$i.'.production_result_code_id',['label'=>false,'default'=>'0','div'=>['class'=>'hidden']])."</td>";
          }
          echo "<td class='productquantity'>".$this->Form->input('Product.'.$i.'.product_quantity',[
            'type'=>'decimal',
            'label'=>false,
            'value'=>$productsForSalesOrder[$i]['SalesOrderProduct']['product_quantity'],
            'readonly'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
          ])."</td>";
          
          echo "<td class='serviceunitcost'>".$this->Form->input('Product.'.$i.'.service_unit_cost',[
            'type'=>'decimal',
            'label'=>false,
            //'value'=>$productsForSalesOrder[$i]['SalesOrderProduct']['service_unit_cost'],
            'default'=>0,
            'readonly'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
          ])."</td>";
          echo "<td  class='servicetotalcost'>".$this->Form->input('Product.'.$i.'.service_total_cost',[
            'type'=>'decimal',
            'label'=>false,
            //'value'=>$productsForSalesOrder[$i]['SalesOrderProduct']['service_total_cost'],
            'default'=>0,
            'readonly'=>'readonly',
          ])."</td>";
          echo "<td class='productunitprice'>";
            echo $this->Form->input('Product.'.$i.'.product_unit_cost',[
              'label'=>false,
              'type'=>'hidden',
              'value'=>$productsForSalesOrder[$i]['SalesOrderProduct']['product_unit_cost'],
              'class'=>'productcost',
            ]);
            echo $this->Form->input('Product.'.$i.'.default_product_unit_price',['label'=>false,'type'=>'hidden','value'=>$unitPrice,'class'=>'defaultproductprice']);
            //echo "<span class='currency'></span>";
            echo $this->Form->input('Product.'.$i.'.product_unit_price',[
              'label'=>false,
              'type'=>'decimal',
              'value'=>$unitPrice,
              'class'=>'productprice',
              'readonly'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
              //'before'=>'<span class=\'currency\'>C$</span>',
            ]);
          echo "</td>";
          
          echo "<td  class='producttotalprice'>".$this->Form->input('Product.'.$i.'.product_total_price',[
            'type'=>'decimal',
            'label'=>false,
            'value'=>$totalPrice,
            'readonly'=>'readonly',
            //'before'=>'<span class=\'currency\'>C$</span>'
          ])."</td>";
          echo '<td>'.($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?'':'<button class="removeMaterial">'.__('Remove Sale Item').'</button>').'</td>';
        echo "</tr>";
      }
			
        echo "<tr class='totalrow subtotal'>";
          echo "<td>Subtotal</td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td class='productquantity amount right'><span></span></td>";
          echo "<td class='serviceunitcost'></td>";
          echo "<td  class='servicetotalcost amount right'><span></span></td>";
          echo "<td></td>";
          echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
          echo "<td></td>";
        echo "</tr>";		
        echo "<tr class='iva'>";
          echo "<td>IVA</td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td class='serviceunitcost'></td>";
            echo "<td  class='servicetotalcost'></td>";
          echo "<td></td>";
          echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
          echo "<td></td>";
        echo "</tr>";		
        echo "<tr class='totalrow total'>";
          echo "<td>Total</td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td class='serviceunitcost'></td>";
          echo "<td  class='servicetotalcost'></td>";
          echo "<td></td>";
          echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
          echo "<td></td>";
        echo "</tr>";		
        echo "<tr class='retention'>";
          echo "<td>Retención</td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td></td>";
          echo "<td class='serviceunitcost'></td>";
          echo "<td  class='servicetotalcost'></td>";
          echo "<td></td>";
          echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('Order.retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'])."</td>";
          echo "<td></td>";
        echo "</tr>";
			echo "</tbody>";
    */  
		echo '<table id ="Ingredientes">'.$tableHead.$tableBody.'</table>';
	}
	
?>
<script>
  
	$(document).ajaxComplete(function() {	
		$('td div select.fixed option:not(:selected)').attr('disabled', true);
  });

</script>