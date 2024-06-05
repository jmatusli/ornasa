<?php 
  if (empty($productPriceLogs)){
    echo '<h3>Este producto no tiene precios registrados para este cliente o categoría</h3>';
  }
  else {
    $tableHead='';
    $tableHead.='<thead>';
      $tableHead.='<tr>';
        $tableHead.='<th>Fecha</th>';
        $tableHead.='<th>Producto</th>';
      if ($priceClientCategoryId > 0){
        $tableHead.='<th>Categoría</th>';
      }
      if ($clientId > 0){
        $tableHead.='<th>Cliente</th>';
      }
        $tableHead.='<th>Precio</th>';
      $tableHead.='</tr>';
    $tableHead.='<thead>';
    
    $tableBodyRows='';
    foreach ($productPriceLogs as $productPriceLog){
      $priceDateTime = new DateTime($productPriceLog['ProductPriceLog']['price_datetime']);
      
      $tableRow='';
      $tableRow.='<tr>';
        $tableRow.='<td>'.$priceDateTime->format('d-m-Y H:i:s').'</td>';
        $tableRow.='<td>'.$productPriceLog['Product']['name'].(empty($productPriceLog['RawMaterial']['id'])?'':(' '.$productPriceLog['RawMaterial']['name'] )).'</td>';
      if ($priceClientCategoryId > 0){
        $tableRow.='<td>'.$this->Html->Link($productPriceLog['PriceClientCategory']['name'],['controller'=>'priceClientCategories','action'=>'detalle',$productPriceLog['PriceClientCategory']['id']]).'</td>';
      }
      if ($clientId > 0){
        $tableRow.='<td>'.$this->Html->Link($productPriceLog['Client']['company_name'],['controller'=>'thirdParties','action'=>'verCliente',$productPriceLog['Client']['id']]).'</td>';
      }  
        $tableRow.='<td class="centered"><span class="currency">'.$productPriceLog['Currency']['abbreviation'].'</span><span class="amountright">'.$productPriceLog['ProductPriceLog']['price'].'</span></td>';
      $tableRow.='</tr>';
      
      $tableBodyRows.=$tableRow;
    }
    
    $tableBody='<tbody>'.$tableBodyRows.'</tbody>';
    $priceTable='<table id="precios">'.$tableHead.$tableBody.'</table>';
    echo $priceTable;
  } 