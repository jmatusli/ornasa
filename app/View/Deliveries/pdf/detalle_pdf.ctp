<style>
	#header {
		position:relative;
	}
	
	#header div.imagecontainer {
		width:25%;
		padding-left:3%;
		clear:none;
	}
	
	img.resize {
		height: auto;
		width:150px;
	}
	
	img.smallimage {
		height: auto;
		width:75px;
	}
	
  #header div.companyinfo {
		width:40%;
    position:absolute;
		clear:none;
    float:left;
    vertical-align:top;
    left:30%;
    top:0;
    text-align:center;
    font-size:0.9em;	
	}
  
	#header #headertext {
		width:20%;
		height:auto;
		vertical-align:top;
		top:0;
		right:0;
		text-align:right;
		font-size:0.85em;		
	}
	
	div.separator {
		border-bottom:4px solid #000000;
	}
	
	div.background {
		position:relative;
	}

	div, span {
		font-size:1em;
	}
	.title{
		font-size:2.5em;
	}
	.big{
		font-size:1.5em;
	}
	.small {
		font-size:0.9em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	
	div.pagecentered {
		width:90%;
		margin-left:auto;
		margin-right:auto;
	}
	
	div.rounded {
		padding:1em;
		border:solid #000000 1px;
		-moz-border-radius: 20px;
		-webkit-border-radius: 20px;
		border-radius: 20px;
	}
	
	div.rounded>div {
		display:block;
		clear:left;
	}
	div.rounded>div:not(:first-child) {
		display:inline-block;
	}
	
	table {
		width:100%;
		border-spacing:0;
	}
	
	table.pagecentered {
		width:90%;
		margin-left:auto;
		margin-right:auto;
	}
	
	table.bordered {
		border-collapse:collapse; 
	}
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.8em;
		border-width:2px;
		border-style:solid;
		
		border-color:#000000;
		vertical-align:top;
	}
	td.noleftbottomborder {
		border:0px!important;
		border-color:#FFFFFF;
	}
	td span.right
	{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}	
</style>
<?php
  function monthOfYear($monthString){
    switch($monthString){
      case "01":
        return "Enero";
      case "02":
        return "Febrero";
      case "03":
        return "Marzo";
      case "04":
        return "Abril";
      case "05":
        return "Mayo";
      case "06":
        return "Junio";
      case "07":
        return "Julio";
      case "08":
        return "Agosto";
      case "09":
        return "Septiembre";
      case "10":
        return "Octubre";
      case "11":
        return "Noviembre";
      case "12":
        return "Diciembre";
    }
  }

	$currentDateTime=new DateTime();
	
  $salesOrderDate=$salesOrder['SalesOrder']['sales_order_date'];
	$salesOrderDateTime=new DateTime($salesOrderDate);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	$url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
  
  $header="";
  
	$header.="<div id='headertext'>";
		$header.="<div><span class='right' style='font-size:0.7em;display:inline-block;float:right;'>Pdf generado el ".$currentDateTime->format("d/m/Y H:i:s")."</span></div>";
  $header.="</div>";    
  
	$header.="<table>";
		$header.="<tr>";
      $header.="<td class='title bold left'>";
				$header.="<div><span><img src='".$imageurl."' class='resize'></img></span></div>";
			$header.="</td>";
			$header.="<td class='bold right'>";
        $header.="<div><span>".strtoupper("orden de venta")."</span></div>";
        $header.="<div><span>No ".$salesOrder['SalesOrder']['sales_order_code']."</span></div>";
			$header.="</td>";
		$header.="</tr>";
	
	$header.="</table>";
  $phone='Teléfonos: '.COMPANY_PHONE.' '.COMPANY_CELL_MOVISTAR.' '.COMPANY_CELL_CLARO;
  if (!empty($salesOrder['User']['phone'])){
    $phone='Teléfonos: '.COMPANY_PHONE.' '.$salesOrder['User']['phone'];
  }
  $header.='<div style="text-align:center"><span style="margin-right:10px;">Dirección: '.COMPANY_ADDRESS.'</span><span>'.$phone.'</span></div>';
  
	$output='';
  $output.=$header;
	$output.="<div class='rounded pagecentered'>";
		$output.="<div style='width:50%;clear:left;'>Fecha: <span>".$salesOrderDateTime->format('d-m-Y')."</span></div>";
		$output.="<div style='width:50%;clear:right;display:inline-block;'>Orden No.: <span>".$salesOrder['SalesOrder']['sales_order_code']."</span></div>";

		
		
		 $output.="<table style='width:100%'>";
      $output.="<tbody>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Cliente</td>";
          //$output.="<td style='width:25%;'>".$salesOrder['Client']['company_name']."</td>";
          $output.="<td style='width:25%;'>".$salesOrder['SalesOrder']['client_name']."</td>";
          $output.="<td style='width:25%;'>Fecha</td>";
          $output.="<td style='width:25%;'>".$salesOrderDateTime->format('d')." de ".monthOfYear($salesOrderDateTime->format('m'))." ".$salesOrderDateTime->format('Y')."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Dirección</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['SalesOrder']['client_address'])?"-":$salesOrder['SalesOrder']['client_address'])."</td>";
          $output.="<td style='width:25%;'>Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['VendorUser']['first_name'])?$salesOrder['VendorUser']['username']:($salesOrder['VendorUser']['first_name'].' '.$salesOrder['VendorUser']['last_name']))."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Tel Cliente</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['SalesOrder']['client_phone'])?"-":$salesOrder['SalesOrder']['client_phone'])."</td>";
          $output.="<td style='width:25%;'>Tel Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['VendorUser']['phone'])?"-":$salesOrder['VendorUser']['phone'])."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Correo Cliente</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['SalesOrder']['client_email'])?"-":$salesOrder['SalesOrder']['client_email'])."</td>";
          $output.="<td style='width:25%;'>Correo Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['VendorUser']['email'])?"-":$salesOrder['VendorUser']['email'])."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>RUC</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['SalesOrder']['client_ruc'])?"-":$salesOrder['SalesOrder']['client_ruc'])."</td>";
          $output.="<td style='width:25%;'>Grabado por</td>";
          $output.="<td style='width:25%;'>".(empty($salesOrder['RecordUser']['first_name'])?$salesOrder['RecordUser']['username']:($salesOrder['RecordUser']['first_name'].' '.$salesOrder['RecordUser']['last_name']))."</td>";
        $output.="</tr>";
        if (!empty($salesOrder['Vehicle']['id']) || !empty($salesOrder['DriverUser']['id'])){
          $output.="<tr>";
          if (!empty($salesOrder['Vehicle']['id'])){
            $output.='<td style="width:25%;">'.__('Vehicle').'</td>';
            $output.="<td style='width:25%;'>".(empty($salesOrder['Vehicle']['id'])?'-':$salesOrder['Vehicle']['name'])."</td>";
          }
          if (!empty($salesOrder['DriverUser']['id'])){  
            $output.='<td style="width:25%;">'.__('Driver User').'</td>';
            $output.="<td style='width:25%;'>".(empty($salesOrder['DriverUser']['first_name'])?'':($salesOrder['DriverUser']['first_name'].' '.$salesOrder['DriverUser']['last_name']))."</td>";
          }
          $output.="</tr>";
        }
        if ($salesOrder['SalesOrder']['bool_credit']){
          $output.="<tr>";
            $output.="<td style='width:25%;'>Autorización Crédito</td>";
            $output.="<td style='width:25%;'>".(empty($salesOrder['CreditAuthorizationUser']['id'])?'Autorizado por configuración de cliente':($salesOrder['CreditAuthorizationUser']['first_name'].' '.$salesOrder['CreditAuthorizationUser']['last_name']))."</td>";
          $output.="</tr>";
        }
        
        
      $output.="</tbody>";
    $output.="</table>";

	$output.="</div>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";

	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	if (!empty($salesOrder['SalesOrderProduct'])){
		$output.="<h3>".__('Productos de esta Orden de Venta')."</h3>";
		$output.="<table class='bordered'>";
			$output.="<tr>";
				$output.="<th style='width:10%;'>".__('Cantidad')."</th>";
				$output.="<th style='width:15%;'>Producto</th>";
        $output.="<th style='width:12%;'>Preforma</th>";
				$output.="<th style='width:15%;'>".__('P.Unitario')."</th>";
				$output.="<th style='width:18%;'>".__('Sub-Total')."</th>";
			$output.="</tr>";
		$totalProductQuantity=0;
		foreach ($salesOrder['SalesOrderProduct'] as $salesOrderProduct){
			$totalProductQuantity+=$salesOrderProduct['product_quantity'];
			if ($salesOrderProduct['currency_id']==CURRENCY_CS){
				$classCurrency=" class='CScurrency'";
			}
			elseif ($salesOrderProduct['currency_id']==CURRENCY_USD){
				$classCurrency=" class='USDcurrency'";
			}
			$output.="<tr>";
				$output.="<td class='centered' style='width:10%;'>".number_format($salesOrderProduct['product_quantity'],0,".",",")."</td>";
				$output.="<td style='width:50%;'>".$salesOrderProduct['Product']['name']."</td>";
        $output.="<td style='width:50%;'>".(empty($salesOrderProduct['RawMaterial']['id'])?"-":$salesOrderProduct['RawMaterial']['name'])."</td>";
				$output.="<td  style='width:15%;' ".$classCurrency."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amount right'>".number_format($salesOrderProduct['product_unit_price'],4,".",",")."</span></td>";
				$output.="<td style='width:15%;' ".$classCurrency."><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='amount right'>".number_format($salesOrderProduct['product_total_price'],2,".",",")."</span></td>";
			$output.="</tr>";
		}
			$output.="<tr>";
				$output.="<td class='centered bold'>".$totalProductQuantity."</td>";
				$output.="<td class='noleftbottomborder'></td>";
        $output.="<td class='noleftbottomborder'></td>";
				$output.="<td><span class='bold'>Subtotal</span></td>";
        $output.="<td><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($salesOrder['SalesOrder']['price_subtotal'],2,".",",")."</span></td>";
				
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
        $output.="<td><span class='bold'>IVA</span></td>";
				
        $output.="<td><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($salesOrder['SalesOrder']['price_iva'],2,".",",")."</span></td>";
			$output.="</tr>";
			$output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
        $output.="<td><span class='bold'>Total</span></td>";
				$output.="<td><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($salesOrder['SalesOrder']['price_total'],2,".",",")."</span></td>";
			$output.="</tr>";
      $output.="<tr>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
				$output.="<td class='noleftbottomborder'></td>";
        $output.="<td><span class='bold'>Retención</span></td>";
				$output.="<td><span class='currency'>".$salesOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($salesOrder['SalesOrder']['retention_amount'],2,".",",")."</span></td>";
			$output.="</tr>";
		$output.="</table>";

    $output.="<table class='pagecentered'>";
			$output.="<tr>";
				$output.="<td style='width:40%;'>Observación</td>";
				$output.="<td style='width:60%;'>".$salesOrder['SalesOrder']['observation']."</td>";
			$output.="</tr>";
		$output.="</table>";
    
	}
  
  	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
    $output.="<div><span class='bold '>&nbsp;</span></div>";
  
    $output.='<div style="padding:2em 2em;width:100%;">';
      $output.='<div style="text-align:center">Condiciones</div>';
      $output.='<table>';
        $output.='<tbody>';
          $output.='<tr>';      
            $output.='<td style="width:50%;text-align:right;">Cuenta BANPRO C$</td>';
            $output.='<td>10012209580647</td>';
          $output.='</tr>';
          $output.='<tr>';
            $output.='<td style="width:50%;text-align:right;">Cuenta BAC C$</td>';
            $output.='<td>362208605</td>';
          $output.='</tr>';
          $output.='<tr>';
            $output.='<td style="width:50%;text-align:right;">Elaborar cheque a nombre de </td>';
            $output.='<td>Grupo ORNA S.A.</td>';
          $output.='</tr>';
        $output.='</tbody>';
      $output.='</table>';
    $output.='</div>';
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>