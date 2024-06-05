<?php
  //echo 'last modifying user role id is '.$lastModifyingUserRoleId.'<br/>';
  //echo 'userroleid is '.$userRoleId.'<br/>';
  //echo 'role admin is '.ROLE_ADMIN.'<br/>';
  echo $this->Form->input('Order.permit_editing',[
    'label'=>false,
    'type'=>'hidden',
    'value'=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?0:1)
  ]);
  
  $tableHead='';
  $tableHead.='<thead>';
    $tableHead.='<tr>';
      $tableHead.='<th>'.__('Product').'</th>';
      $tableHead.='<th style="width:170px;">Materia Prima</th>';
      
      $tableHead.='<th style="width:80px;">'.__('Quality').'</th>';
      
      $tableHead.='<th class="centered narrow">Cantidad</th>';
      
      $tableHead.='<th class="servicecostheader currencyinput">Costo Unitario Otro</th>';
      $tableHead.='<th class="servicecostheader currencyinput">Costo Total Otro</th>';
      
      $tableHead.='<th class="currencyinput">'.__('Unit Price').'</th>';
      $tableHead.='<th class="currencyinput">'.__('Total Price').'</th>';
      $tableHead.='<th></th>';
    $tableHead.='</tr>';
  $tableHead.='</thead>';
  
  $tableBodyRows='';
  
	if (empty($productsForSalesOrder)){    
    for ($i=0;$i<7;$i++) { 
      if ($i == 0){
        $tableBodyRows.='<tr row="Fact_'.$i.'">';
      } 
      else {
        $tableBodyRows.='<tr row="Fact_'.$i.'" class="hidden">';
      } 
        $tableBodyRows.='<td class="productid">'.$this->Form->input('Product.'.$i.'.product_id',['label'=>false,'default'=>'0','empty' =>[0=>'-- Producto --']]).'</td>';
        $tableBodyRows.='<td class="rawmaterialid">'.$this->Form->input('Product.'.$i.'.raw_material_id',['label'=>false,'default'=>'0','empty' =>[0=>'-- Preforma --']]).'</td>';
        
        $tableBodyRows.='<td class="productionresultcodeid">'.$this->Form->input('Product.'.$i.'.production_result_code_id',['label'=>false,'default'=>PRODUCTION_RESULT_CODE_A,'readonly'=>'readonly']).'</td>';
        
        $tableBodyRows.='<td class="productquantity">'.$this->Form->input('Product.'.$i.'.product_quantity',['type'=>'decimal','label'=>false,'default'=>'0']).'</td>';
        
        $tableBodyRows.='<td class="serviceunitcost">'.$this->Form->input('Product.'.$i.'.service_unit_cost',['type'=>'decimal','label'=>false,'default'=>0]).'</td>';
        $tableBodyRows.='<td  class="servicetotalcost">'.$this->Form->input('Product.'.$i.'.service_total_cost',['type'=>'decimal','label'=>false,'default'=>0,'readonly'=>'readonly']).'</td>';
        
        $tableBodyRows.='<td class="productunitprice">';
          $tableBodyRows.=$this->Form->input('Product.'.$i.'.product_unit_cost',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'productcost']);
          $tableBodyRows.=$this->Form->input('Product.'.$i.'.default_product_unit_price',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'defaultproductprice']);
          $tableBodyRows.=$this->Form->input('Product.'.$i.'.product_unit_price',[
            'label'=>false,
            'type'=>'decimal',
            'default'=>0,
            'class'=>'productprice',
            //'before'=>'<span class=\'currency\'>C$</span>',
          ]);
        $tableBodyRows.='</td>';
        $tableBodyRows.='<td  class="producttotalprice">'.$this->Form->input('Product.'.$i.'.product_total_price',['type'=>'decimal','label'=>false,'default'=>'0','readonly'=>'readonly']).'</td>';
        $tableBodyRows.='<button class="removeProduct" type="button">'.__('Remover Producto').'</button>';

      $tableBodyRows.='</tr>';
    } 						
	}
	else {
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

      $tableBodyRows.='<tr row="Fact_'.$i.'">';
        $tableBodyRows.='<td class="productid">'.$this->Form->input("Product.".$i.".product_id",[
          "label"=>false,
          "value"=>$productsForSalesOrder[$i]["SalesOrderProduct"]["product_id"],
          "empty" =>[0=>"-- Product --"],
          "class"=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?"fixed":""),
        ]).'</td>';
        $tableBodyRows.='<td class="rawmaterialid">'.$this->Form->input("Product.".$i.".raw_material_id",[
          "label"=>false,
          "value"=>(array_key_exists("raw_material_id",$productsForSalesOrder[$i]["SalesOrderProduct"])?$productsForSalesOrder[$i]["SalesOrderProduct"]["raw_material_id"]:0),
          "type"=>(empty($productsForSalesOrder[$i]["SalesOrderProduct"]["raw_material_id"])?"hidden":"select"),
          "empty" =>[0=>"-- Preforma --"],
          "class"=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?"fixed":""),
        ]).'</td>';
        if (!empty($productsForSalesOrder[$i]["SalesOrderProduct"]["raw_material_id"])){
          $tableBodyRows.='<td class="productionresultcodeid">'.$this->Form->input("Product.".$i.".production_result_code_id",["label"=>false,"value"=>PRODUCTION_RESULT_CODE_A]).'</td>';
        }
        else {
          $tableBodyRows.='<td class="productionresultcodeid">'.$this->Form->input("Product.".$i.".production_result_code_id",["label"=>false,"default"=>"0","div"=>["class"=>"hidden"]]).'</td>';
        }
        $tableBodyRows.='<td class="productquantity">'.$this->Form->input("Product.".$i.".product_quantity",[
          "type"=>"decimal",
          "label"=>false,
          "value"=>$productsForSalesOrder[$i]["SalesOrderProduct"]["product_quantity"],
          "readonly"=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
        ]).'</td>';
        
        $tableBodyRows.='<td class="serviceunitcost">'.$this->Form->input("Product.".$i.".service_unit_cost",[
          "type"=>"decimal",
          "label"=>false,
          //"value"=>$productsForSalesOrder[$i]["SalesOrderProduct"]["service_unit_cost"],
          "default"=>0,
          "readonly"=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
        ]).'</td>';
        $tableBodyRows.='<td  class="servicetotalcost">'.$this->Form->input("Product.".$i.".service_total_cost",[
          "type"=>"decimal",
          "label"=>false,
          //"value"=>$productsForSalesOrder[$i]["SalesOrderProduct"]["service_total_cost"],
          "default"=>0,
          "readonly"=>"readonly",
        ]).'</td>';
        $tableBodyRows.='<td class="productunitprice">';
          $tableBodyRows.= $this->Form->input("Product.".$i.".product_unit_cost",[
            "label"=>false,
            "type"=>"hidden",
            "value"=>$productsForSalesOrder[$i]["SalesOrderProduct"]["product_unit_cost"],
            "class"=>"productcost",
          ]);
          $tableBodyRows.=$this->Form->input("Product.".$i.".default_product_unit_price",["label"=>false,"type"=>"hidden","value"=>$unitPrice,"class"=>"defaultproductprice"]);
          //$tableBodyRows.='<span class="currency"></span>';
          $tableBodyRows.=$this->Form->input("Product.".$i.".product_unit_price",[
            "label"=>false,
            "type"=>"decimal",
            "value"=>$unitPrice,
            "class"=>"productprice",
            "readonly"=>($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?true:false),
            //"before"=>"<span class=\"currency\">C$</span>",
          ]);
        $tableBodyRows.='</td>';
        
        $tableBodyRows.='<td  class="producttotalprice">'.$this->Form->input("Product.".$i.".product_total_price",[
          "type"=>"decimal",
          "label"=>false,
          "value"=>$totalPrice,
          "readonly"=>"readonly",
          //"before"=>"<span class=\"currency\">C$</span>"
        ]).'</td>';
        $tableBodyRows.='<td>';
          $tableBodyRows.=($lastModifyingUserRoleId == ROLE_ADMIN && $userRoleId != ROLE_ADMIN?"":'<button class="removeProduct" type="button">'.__("Remove Sale Item")."</button>");
        $tableBodyRows.='</td>';
      $tableBodyRows.='</tr>';
    }
  }
  
  $totalRows='';
  $totalRows.='<tr class="totalrow subtotal">';
    $totalRows.='<td>Subtotal</td>';
    $totalRows.='<td></td>';
    
    $totalRows.='<td></td>';
    
    $totalRows.='<td class="productquantity amount right"><span></span></td>';
    
    $totalRows.='<td class="serviceunitcost"></td>';
    $totalRows.='<td  class="servicetotalcost amount right"><span></span></td>';
    
    $totalRows.='<td></td>';
    $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('Order.price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
    $totalRows.='<td></td>';
  $totalRows.='</tr>';		
  $totalRows.='<tr class="iva">';
    $totalRows.='<td>IVA</td>';
    $totalRows.='<td></td>';

    $totalRows.='<td></td>';

    $totalRows.='<td></td>';

    $totalRows.='<td class="serviceunitcost"></td>';
    $totalRows.='<td  class="servicetotalcost"></td>';
    $totalRows.='<td></td>';
    
    $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('Order.price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
    $totalRows.='<td></td>';
  $totalRows.='</tr>';		
  $totalRows.='<tr class="totalrow total">';
    $totalRows.='<td>Total</td>';
    $totalRows.='<td></td>';
    
    $totalRows.='<td></td>';
    
    $totalRows.='<td></td>';
    
    $totalRows.='<td class="serviceunitcost"></td>';
    $totalRows.='<td  class="servicetotalcost"></td>';
    
    $totalRows.='<td></td>';
    $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('Order.price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
    $totalRows.='<td></td>';
  $totalRows.='</tr>';		
  $totalRows.='<tr class="retention">';
    $totalRows.='<td>Retención</td>';
    $totalRows.='<td></td>';
    $totalRows.='<td></td>';
    
    $totalRows.='<td></td>';
    
    $totalRows.='<td class="serviceunitcost"></td>';
    $totalRows.='<td  class="servicetotalcost"></td>';
    
    $totalRows.='<td></td>';
    $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('Order.retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
    $totalRows.='<td></td>';
  $totalRows.='</tr>';
      
  $tableBody='<tbody style="font-size:100%;">'.$tableBodyRows.$totalRows.'</tbody>';
     
  echo '<table id="productsForSale" style="font-size:16px;">'.$tableHead.$tableBody.'</table>';
?>
<script>
  var jsOtherProducts=<?php  echo json_encode($otherProducts); ?>;
  
	$(document).ajaxComplete(function() {	
		$('td div select.fixed option:not(:selected)').attr('disabled', true);
    showServiceCosts()
    if ($('#OrderPermitEditing').val() == 0){
      $('.addProduct').addClass('d-none');
    }
    else {
      $('.addProduct').removeClass('d-none');
    }
	});
  
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
        $(this).find('td.serviceunitcost').removeClass('d-none')
        $(this).find('td.servicetotalcost').removeClass('d-none')
      });  
      $('#productsForSale tbody tr:not(.totalrow):not(.hidden):not(.d-none):not(.iva):not(.retention)').each(function(){
        var productid=$(this).find('td.productid select').val()
        var otherProductRow=$.inArray(productid,jsOtherProducts) > -1;
        if (otherProductRow){
          $(this).find('td.serviceunitcost div.input').removeClass('d-none')
          $(this).find('td.servicetotalcost div.input').removeClass('d-none')
          otherProductPresent=true
        }
        else {
          $(this).find('td.serviceunitcost div.input').addClass('d-none')
          $(this).find('td.servicetotalcost div.input').addClass('d-none')
        }
      });
      $('#productsForSale thead tr th.servicecostheader').each(function(){
        $(this).removeClass('d-none')
      });
    }
    else {
      $('#productsForSale tbody tr').each(function(){
        $(this).find('td.serviceunitcost').addClass('d-none')
        $(this).find('td.servicetotalcost').addClass('d-none')        
      });
      
      $('#productsForSale thead tr th.servicecostheader').each(function(){
          $(this).addClass('d-none')
      });
    }
  }
</script>