<?php
  $tableHead='';
  $tableHead.='<thead>';
    $tableHead.='<tr>';
      $tableHead.='<th>'.__('Product').'</th>';
      $tableHead.='<th>Preforma</th>';
      $tableHead.='<th>Cantidad</th>';
      $tableHead.='<th style="width:12%;">Precio Unitario</th>';
      $tableHead.='<th style="width:12%;">Precio Total</th>';
      $tableHead.='<th>Acciones</th>';
    $tableHead.='</tr>';
  $tableHead.='</thead>';
  
  $tableBodyRows='';
	if (empty($productsForQuotation)){
    // no quotation present, code copied directly from crear orden venta externa
    for ($qp=0;$qp<30;$qp++){
      if ($qp==0){
        $tableBodyRows.='<tr row="'.$qp.'">';
      }
      else {
        $tableBodyRows.='<tr row="'.$qp.'" class="hidden">';
      }
        $tableBodyRows.='<td class="productid">';
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.product_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Producto']]);
        $tableBodyRows.='</td>';
        $tableBodyRows.='<td class="rawmaterialid">'.$this->Form->input('SalesOrderProduct.'.$qp.'.raw_material_id',array('label'=>false,'default'=>'0','empty' =>[0=>__('-- Preforma --')])).'</td>';
        $tableBodyRows.='<td class="productquantity amount">'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0]).'</td>';
        $tableBodyRows.='<td class="productunitprice">';
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.product_unit_cost',['label'=>false,'type'=>'hidden','value'=>0,'class'=>'productcost']);
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.default_product_unit_price',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'defaultproductprice']);
          $tableBodyRows.='<span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_unit_price',['label'=>false,'type'=>'decimal','default'=>0,'class'=>'productprice']);
        $tableBodyRows.='</td>';
        $tableBodyRows.='<td class="producttotalprice"><span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_total_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>0]).'</td>';
        $tableBodyRows.='<td>';
          $tableBodyRows.='<button class="removeProduct" type="button">'.__('Remover Producto').'</button>';
          $tableBodyRows.='<button class="addProduct" type="button">'.__('Add Product').'</button>';
        $tableBodyRows.='</td>';
      $tableBodyRows.='</tr>';
    }
	
  }
  else {  
		$tableBody='';
    
    for ($qp=0;$qp<count($productsForQuotation);$qp++){
      $tableBodyRows.='<tr row="'.$qp.'">';
        $tableBodyRows.='<td class="productid">';
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.product_id',[
            'label'=>false,
            'value'=>$productsForQuotation[$qp]['QuotationProduct']['product_id'],
            'empty'=>['0'=>'-- Producto --'],
            //'class'=>'fixed',
          ]);
        $tableBodyRows.='</td>';
        $tableBodyRows.='<td class="rawmaterialid">'.$this->Form->input('SalesOrderProduct.'.$qp.'.raw_material_id',[
          'label'=>false,
          'value'=>(array_key_exists('raw_material_id',$productsForQuotation[$qp]['QuotationProduct'])?$productsForQuotation[$qp]['QuotationProduct']['raw_material_id']:0),
          'type'=>(empty($productsForQuotation[$qp]['QuotationProduct']['raw_material_id'])?'hidden':'select'),
          'empty' =>[0=>__('-- Preforma --')],
          //'class' =>'fixed',
        ]).'</td>';
        $tableBodyRows.='<td class="productquantity amount">'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_quantity',[
          'label'=>false,
          'type'=>'decimal',
          'required'=>false,
          'value'=>$productsForQuotation[$qp]['QuotationProduct']['product_quantity'],
        ]).'</td>';
        $tableBodyRows.='<td class="productunitprice">';
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.product_unit_cost',['label'=>false,'type'=>'hidden','value'=>$productsForQuotation[$qp]['QuotationProduct']['product_unit_cost'],'class'=>'productcost']);
                
          $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.default_product_unit_price',[
            'label'=>false,
            'type'=>'hidden',
            'value'=>$productsForQuotation[$qp]['QuotationProduct']['product_unit_price'],
            'class'=>'defaultproductprice'
          ]);
          $tableBodyRows.='<span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_unit_price',[
            'label'=>false,
            'type'=>'decimal',
            'value'=>$productsForQuotation[$qp]['QuotationProduct']['product_unit_price'],
            'class'=>'productprice',
          ]);
        $tableBodyRows.='</td>';
        $tableBodyRows.='<td class="producttotalprice"><span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_total_price',[
          'label'=>false,
          'type'=>'decimal',
          //'readonly'=>'readonly',
          'value'=>$productsForQuotation[$qp]['QuotationProduct']['product_total_price'],
        ]).'</td>';
        $tableBodyRows.='<td>';
          $tableBodyRows.='<button class="removeProduct" type="button">'.__('Remover Producto').'</button>';
          $tableBodyRows.='<button class="addProduct" type="button">'.__('Add Product').'</button>';
          $tableBodyRows.='<button class="showPriceSelection" type="button">Precios</button>';
        $tableBodyRows.='</td>';
      $tableBodyRows.='</tr>';
    }    
    if (count($productsForQuotation) < QUOTATION_ARTICLES_MAX){
      for ($qp=count($productsForQuotation);$qp<QUOTATION_ARTICLES_MAX;$qp++){
        if ($qp==count($productsForQuotation)){
          $tableBodyRows.='<tr row="'.$qp.'">';
        }
        else {
          $tableBodyRows.='<tr row="'.$qp.'" class="hidden">';
        }
          $tableBodyRows.='<td class="productid">';
            $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.product_id',['label'=>false,'default'=>0,'empty'=>['0'=>'Seleccione Producto']]);
          $tableBodyRows.='</td>';
          $tableBodyRows.='<td class="rawmaterialid">'.$this->Form->input('SalesOrderProduct.'.$qp.'.raw_material_id',['label'=>false,'default'=>'0','empty' =>[0=>__('-- Preforma --')]]).'</td>';
          $tableBodyRows.='<td class="productquantity amount">'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_quantity',['label'=>false,'type'=>'decimal','required'=>false,'default'=>0]).'</td>';
          $tableBodyRows.='<td class="productunitprice">';
            $tableBodyRows.=$this->Form->input('SalesOrderProduct.'.$qp.'.default_product_unit_price',['label'=>false,'type'=>'hidden','default'=>0,'class'=>'defaultproductprice']);
            $tableBodyRows.='<span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_unit_price',['label'=>false,'type'=>'decimal','default'=>0,'class'=>'productprice']);
          $tableBodyRows.='</td>';
          $tableBodyRows.='<td class="producttotalprice"><span class="currency"></span>'.$this->Form->input('SalesOrderProduct.'.$qp.'.product_total_price',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>0]).'</td>';
          $tableBodyRows.='<td>';
            $tableBodyRows.='<button class="removeProduct" type="button">'.__('Remover Producto').'</button>';
            $tableBodyRows.='<button class="addProduct" type="button">'.__('Add Product').'</button>';
            $tableBodyRows.='<button class="showPriceSelection" type="button">Precios</button>';
          $tableBodyRows.='</td>';
        $tableBodyRows.='</tr>';
      }
    }  
  }

  $totalRows='';
  $totalRows.='<tr class="totalrow subtotal">';
    $totalRows.='<td>Subtotal</td>';
    $totalRows.='<td></td>';
    $totalRows.='<td class="productquantity amount right"><span></span></td>';
    $totalRows.='<td></td>';
    $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('SalesOrder.price_subtotal',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
    $totalRows.='<td></td>';
  $totalRows.='</tr>';		
    $totalRows.='<tr class="iva">';
      $totalRows.='<td>IVA</td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('SalesOrder.price_iva',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
      $totalRows.='<td></td>';
    $totalRows.='</tr>';		
    $totalRows.='<tr class="totalrow total">';
      $totalRows.='<td>Total</td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('SalesOrder.price_total',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
      $totalRows.='<td></td>';
    $totalRows.='</tr>';		
    $totalRows.='<tr class="retention">';
      $totalRows.='<td>Retención</td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td></td>';
      $totalRows.='<td class="totalprice amount right"><span class="currency"></span>'.$this->Form->input('SalesOrder.retention_amount',['label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0']).'</td>';
      $totalRows.='<td></td>';
    $totalRows.='</tr>';		
  $totalRows.='</tbody>';	

  $tableBody='<tbody style="font-size:100%;">'.$tableBodyRows.$totalRows.'</tbody>';

  echo '<table id="salesOrderProducts" style="font-size:16px;">'.$tableHead.$tableBody.'</table>';
?>
<script>
	$(document).ajaxComplete(function() {	
		$('td.productid option:not(:selected)').attr('disabled', true);
	});
</script>