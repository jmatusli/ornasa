<style>
	#header {
		position:relative;
	}
	
	img.resize {
		height: auto;
		width:200px;
	}
	
	img.smallimage {
		height: auto;
		width:75px;
	}
	
	#header #headertext {
		width:50%;
		position:absolute;
		height:auto;
		vertical-align:bottom;
		bottom:0em;
		right:0em;
		margin-bottom:0;
		text-align:center;
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
	.verysmall {
		font-size:0.8em;
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

	$url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
  $currentDateTime=new DateTime();
	
	$quotationDate=date("Y-m-d",strtotime($quotation['Quotation']['quotation_date']));
	$quotationDateTime=new DateTime($quotationDate);
	$dueDate=date("Y-m-d",strtotime($quotation['Quotation']['due_date']));
	$dueDateTime=new DateTime($dueDate);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	
  $output="";
  
	$output.="<div id='headertext'>";
		$output.="<div><span class='right' style='font-size:0.7em;display:inline-block;float:right;'>Pdf generado el ".$currentDateTime->format("d/m/Y H:i:s")."</span></div>";
  $output.="</div>";    
  
	$output.="<table>";
		$output.="<tr>";
      $output.="<td class='title bold left'>";
				$output.="<div><span><img src='".$imageurl."' class='resize'></img></span></div>";
			$output.="</td>";
			$output.="<td class='bold right'>";
        $output.="<div><span>".strtoupper("Cotizacion")."</span></div>";
        $output.="<div><span>No ".$quotation['Quotation']['quotation_code']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td class='centered'>";
				$output.='<div style="text-align:center;"><span>RUC No.: '.COMPANY_RUC.'</span></div>';
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	$phone='Teléfonos: '.COMPANY_PHONE.' '.COMPANY_CELL_MOVISTAR.' '.COMPANY_CELL_CLARO;
  if (!empty($quotation['VendorUser']['phone'])){
    $phone='Teléfonos: '.COMPANY_PHONE.' '.$quotation['VendorUser']['phone'];
  }
  $output.='<div style="text-align:center"><span style="margin-right:10px;">Dirección: '.COMPANY_ADDRESS.' </span> |<span>'.$phone.'</span></div>';
  //$output.="<div>".COMPANY_URL." &#183; ".COMPANY_MAIL."</div>";
  //$output.="<div class='separator'>&nbsp;</div>";
  
  $output.="<div id='header'>";
  
	$output.="</div>";		
	$output.="<div class='separator'>&nbsp;</div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<div class='rounded pagecentered'>";
    $output.="<table style='width:100%'>";
      $output.="<tbody>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Cliente</td>";
          $output.="<td style='width:25%;'>".$quotation['Quotation']['client_name']."</td>";
          $output.="<td style='width:25%;'>Fecha</td>";
          $output.="<td style='width:25%;'>".$quotationDateTime->format('d')." de ".monthOfYear($quotationDateTime->format('m'))." ".$quotationDateTime->format('Y')."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Dirección</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['Quotation']['client_address'])?"-":$quotation['Quotation']['client_address'])."</td>";
          $output.="<td style='width:25%;'>Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['VendorUser']['first_name'])?$quotation['VendorUser']['username']:($quotation['VendorUser']['first_name'].' '.$quotation['VendorUser']['last_name']))."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Tel Cliente</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['Quotation']['client_phone'])?"-":$quotation['Quotation']['client_phone'])."</td>";
          $output.="<td style='width:25%;'>Tel Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['VendorUser']['phone'])?"-":$quotation['VendorUser']['phone'])."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Correo Cliente</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['Quotation']['client_mail'])?"-":$quotation['Quotation']['client_mail'])."</td>";
          $output.="<td style='width:25%;'>Correo Vendedor</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['VendorUser']['email'])?"-":$quotation['VendorUser']['email'])."</td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td style='width:25%;'>Grabado por</td>";
          $output.="<td style='width:25%;'>".(empty($quotation['RecordUser']['first_name'])?$quotation['RecordUser']['username']:($quotation['RecordUser']['first_name'].' '.$quotation['RecordUser']['last_name']))."</td>";
          //$output.="<td style='width:25%;'>RUC</td>";
          //$output.="<td style='width:25%;'>".(empty($quotation['Quotation']['client_ruc'])?"-":$quotation['Quotation']['client_ruc'])."</td>";
          if ($quotation['Quotation']['bool_credit']){
            $output.="<td style='width:25%;'>Autorización Crédito</td>";
            $output.="<td style='width:25%;'>".(empty($quotation['CreditAuthorizationUser']['id'])?'Autorizado por configuración de cliente':($quotation['CreditAuthorizationUser']['first_name'].' '.$quotation['CreditAuthorizationUser']['last_name']))."</td>";
          }
          
        $output.="</tr>";
      $output.="</tbody>";
    $output.="</table>";
    
	$output.="</div>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	
	if (!empty($quotation['QuotationProduct'])){
		$output.="<table  class='bordered'>";
			$output.="<tr>";
				$output.="<th style='width:30%;'>Nombre</th>";
        $output.="<th style='width:10%;'>".__('Cantidad')."</th>";
				//if ($quotation['Quotation']['bool_print_images']){
				//	$output.="<th style='width:10%;'>".__('Imagen')."</th>";
				//}
				//if (!$quotation['Quotation']['bool_print_images'] && !$quotation['Quotation']['bool_print_delivery_time']){
				//	$output.="<th>".__('Descripción del Trabajo')."</th>";
				//}
				//else {
				//	if ($quotation['Quotation']['bool_print_images'] && $quotation['Quotation']['bool_print_delivery_time']){
				//		$output.="<th>".__('Descripción del Trabajo')."</th>";
				//	}
				//	else {
				//		$output.="<th>".__('Descripción del Trabajo')."</th>";
				//	}
				//}
				//if ($quotation['Quotation']['bool_print_delivery_time']){
				//	$output.="<th style='width:10%;'>".__('T.de Entrega')."</th>";
				//}
				$output.="<th style='width:12%;'>Bultos</th>";
        $output.="<th style='width:15%;'>".__('P.Unitario')."</th>";
				$output.="<th style='width:18%;'>".__('Sub-Total')."</th>";
			$output.="</tr>";
		$totalProductQuantity=0;	
		foreach ($quotation['QuotationProduct'] as $quotationProduct){
      //print_r($quotationProduct['Product']);
			$totalProductQuantity+=$quotationProduct['product_quantity'];
			if ($quotationProduct['currency_id']==CURRENCY_CS){
				$classCurrency=" class='CScurrency'";
			}
			elseif ($quotationProduct['currency_id']==CURRENCY_USD){
				$classCurrency=" class='USDcurrency'";
			}
			$output.="<tr>";
				$output.="<td class='centered' style='width:30%;'>".$quotationProduct['Product']['name'].(empty($quotationProduct['QuotationProduct']['raw_material_id'])?"-":(" ".$quotationProduct['RawMaterial']['name']))."</td>";
        $output.="<td class='centered' style='width:10%;'>".number_format($quotationProduct['product_quantity'],0,".",",")."</td>";
        
				//if ($quotation['Quotation']['bool_print_images']){
				//	if (!empty($quotationProduct['Product']['url_image'])){
				//		$url=$quotationProduct['Product']['url_image'];
				//		$productimage=$this->App->assetUrl($url);
				//		$output.="<td class='centered' style='width:10%;'><img src='".$productimage."' class='smallimage'></img></td>";
				//	}
				//	else {
				//		$output.="<td class='centered' style='width:10%;'></td>";
				//	}
				//}
				//if (!$quotation['Quotation']['bool_print_images'] && !$quotation['Quotation']['bool_print_delivery_time']){
				//	$output.="<td style='width:50%;padding:3px;' >".str_replace("\n","<br/>",$quotationProduct['Product']['name'])."</th>";
				//}
				//else {
				//	if ($quotation['Quotation']['bool_print_images'] && $quotation['Quotation']['bool_print_delivery_time']){
				//		$output.="<td style='width:30%;padding:3px;'>".$quotationProduct['Product']['name']."</th>";
				//	}
				//	else {
				//		$output.="<td style='width:40%;padding:3px;'>".$quotationProduct['Product']['name']."</th>";
				//	}
				//}
				//if ($quotation['Quotation']['bool_print_delivery_time']){
				//	$output.="<td style='width:10%;'>".$quotationProduct['delivery_time']."</td>";
				//}
        
        $output.="<td class='centered' style='width:10%;'>".number_format(floor($quotationProduct['product_quantity']/$quotationProduct['Product']['packaging_unit']),0,".",",")."</td>";
        
        $output.="<td><span class='currency' style='width:18%;'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_unit_price'],4,".",",")."</span></td>";
				$output.="<td><span class='currency' style='width:18%;'>".$quotation['Currency']['abbreviation']."</span><span class='right'>".number_format($quotationProduct['product_total_price'],2,".",",")."</span></td>";
			$output.="</tr>";
		}
    $output.="</table>";
    $output.="<table>";
			$output.="<tr>";
				
        $output.="<td class='noleftbottomborder' style='min-width:70%;width:70%;'> </td>";
				$output.="<td style='min-width:12%;width:12%;'>
          <span class='bold'>Subtotal</span>
        </td>";
				$output.="<td>
          <div>
            <span class='currency' style='width:30%' >".$quotation['Currency']['abbreviation']."</span>
            <span class='right' style='display:inline-block;width:70%;text-align:right;' >".number_format($quotation['Quotation']['price_subtotal'],2,".",",")."</span>
        </td>";
			$output.="</tr>";
      $output.="<tr>";
				
      $output.="<td class='noleftbottomborder' style='min-width:70%;width:70%;'> </td>";
				$output.="<td style='min-width:12%;width:12%;'>
          <span class='bold'>IVA</span>
        </td>";
				$output.="<td>
          <div>
            <span class='currency' style='width:30%' >".$quotation['Currency']['abbreviation']."</span>
            <span class='right' style='display:inline-block;width:70%;text-align:right;' >".number_format($quotation['Quotation']['price_iva'],2,".",",")."</span>
        </td>";
			$output.="</tr>";
      $output.="<tr>";
      $output.="<td class='noleftbottomborder' style='min-width:70%;width:70%;'> </td>";
				$output.="<td style='min-width:12%;width:12%;'>
          <span class='bold'>Total</span>
        </td>";
				$output.="<td>
          <div>
            <span class='currency' style='width:30%' >".$quotation['Currency']['abbreviation']."</span>
            <span class='right' style='display:inline-block;width:70%;text-align:right;' >".number_format($quotation['Quotation']['price_total'],2,".",",")."</span>
        </td>";
			$output.="</tr>";
      $output.="<tr>";
        $output.="<td class='noleftbottomborder' style='min-width:70%;width:70%;'> </td>";
				$output.="<td style='min-width:12%;width:12%;'>
          <span class='bold'>Retención</span>
        </td>";
				$output.="<td>
          <div>
            <span class='currency' style='width:30%' >".$quotation['Currency']['abbreviation']."</span>
            <span class='right' style='display:inline-block;width:70%;text-align:right;' >".number_format($quotation['Quotation']['retention_amount'],2,".",",")."</span>
        </td>";
        
			$output.="</tr>";
    $output.="</table>";
	}
  if (!empty($quotation['QuotationImage'])){
    //$output.=print_r($quotation['QuotationImage'],true);
    foreach ($quotation['QuotationImage'] as $quotationImage){
          //$output.=print_r($quotationImage,true);
          $url=$quotationImage['url_image'];
          
          $productImage=$this->App->assetUrl($url);
          $output.="<div ><img src='".$productImage."'></img></div>";
    }
  }
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$dueDate=new DateTime ($quotation['Quotation']['due_date']);
  

	/*
	$output.="<div style='padding:0em 2em;'>";
		$output.="<div>Condiciones</div>";
		$output.="<ul>";
			$output.="<li>".$quotation['Quotation']['remark_delivery']."</li>";
			$output.="<li>La forma de pago será ".$quotation['Quotation']['payment_form']."</li>";
			$output.="<li>".$quotation['Quotation']['remark_cheque']."</li>";
			$output.="<li>Validez de la cotización ".$validityQuotation." días (fecha de vencimiento: ".$dueDate->format('d-m-Y').")</li>";
			$output.="<li>".$quotation['Quotation']['remark_elaboration']."</li>";
		$output.="</ul>";
	$output.="</div>";
	*/
	$output.="<table class='pagecentered'>";
		$output.="<tr>";
			$output.="<td style='width:20%;'>Observaciones</td>";
			$output.="<td style='width:80%;'>".$quotation['Quotation']['observation']."</td>";
		$output.="</tr>";
	$output.="</table>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	
	$roleName="";
	switch ($quotation['VendorUser']['role_id']){
		case ROLE_ADMIN: 
			$roleName="Gerente";
			break;
		case ROLE_ASSISTANT: 
			$roleName="Asistente Ejecutivo";
			break;
    case ROLE_MANAGER: 
			$roleName="Admin";
			break;
    case ROLE_FACTURACION: 
			$roleName="Facturación";
			break;  
		case ROLE_SALES: 
			$roleName="Ejecutivo de Venta";
			break;	
	}
  
  $output.="<table  style='height:15mm;font-size:0.9rem;'>";
      $output.="<tr>";
        $output.="<td class='centered' style='width:50mm;'>
          <div>
            <span class='centered'>_______________________________</span>
          </div>
          <div>  
            <span class='centered'>".$quotation['VendorUser']['first_name']." ".$quotation['VendorUser']['last_name']."</span>
          </div>
          <div>  
            <span class='centered'>".$roleName."</span>
          </div>
          <div>  
            <span class='centered'>".COMPANY_NAME."</span>
          </div>        
        </td>";
        $output.="<td class='centered'style='width:50mm;'>
          <div>
            <span class='centered'>_______________________________</span>
          </div>
          <div class='centered'>  
            <span class='centered'>Cliente</span>
          </div>        
        </td>";
     $output.="</tbody>";
    $output.="</table>";  
    
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
	