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
	
  $nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	
  $url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
  
  for ($tm=0;$tm<count($transferMovements);$tm++){ 
    $movement=$transferMovements[$tm];
    if ($movement['StockMovement']['bool_input']){
      $movement=$transferMovements[$tm];
      $transferDateTime=new DateTime($movement['StockMovement']['movement_date']);
      $transferDate=$transferDateTime->format('d-m-Y');
      $transferCode=(empty($movement['StockMovement']['transfer_code'])?"":$movement['StockMovement']['transfer_code']);
      $productName=$movement['Product']['name'];
      $productName.=(empty($movement['StockItem']['RawMaterial']['name'])?"":(" ".$movement['StockItem']['RawMaterial']['name']));
      $productName.=(empty($movement['ProductionResultCode']['code'])?"":(" ".$movement['ProductionResultCode']['code']));
      $productQuantity=$movement['StockMovement']['product_quantity'];
      $warehouseDestination=$originWarehouse=$movement['StockItem']['Warehouse']['name'];;
    }
    else {
      $warehouseOrigin=$movement['StockItem']['Warehouse']['name'];
    }
  }
  
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
        $header.="<div><span>".strtoupper("transferencia entre bodegas")."</span></div>";
        $header.="<div><span>No ".$transferCode."</span></div>";
			$header.="</td>";
		$header.="</tr>";
	
	$header.="</table>";
  $phone='Teléfonos: '.COMPANY_PHONE.' '.COMPANY_CELL_MOVISTAR.' '.COMPANY_CELL_CLARO;
  $header.='<div style="text-align:center"><span style="margin-right:10px;">Dirección: '.COMPANY_ADDRESS.'</span><span>'.$phone.'</span></div>';
  
	$output='';
  $output.=$header;
	$output.="<div class='rounded pagecentered'>";
		$output.="<div style='width:50%;clear:left;'>Fecha: <span>".$transferDate."</span></div>";
		$output.="<div style='width:50%;clear:right;display:inline-block;'>Transferencia No.: <span>".$transferCode."</span></div>";
		$output.="<table style='width:100%'>";
      $output.="<tr>";
        $output.="<td style='width:50%;'>Producto</td>";
        $output.="<td style='width:50%;'>".$productName."</td>";
      $output.="</tr>";
      $output.="<tr>";
        $output.="<td style='width:50%;'>Cantidad</td>";
        $output.="<td style='width:50%;'>".$productQuantity."</td>";
      $output.="</tr>";
      $output.="<tr>";
        $output.="<td style='width:50%;'>Bodega origen</td>";
        $output.="<td style='width:50%;'>".$warehouseOrigin."</td>";
      $output.="</tr>";
      $output.="<tr>";
        $output.="<td style='width:50%;'>Bodega destino</td>";
        $output.="<td style='width:50%;'>".$warehouseDestination."</td>";
      $output.="</tr>";
    $output.="</table>";
	$output.="</div>";
	$output.='<div>';
    $output.='<p>'.$movement['StockMovement']['description'].'</p>';
  $output.='</div>';
	$output.="<div style='min-height:50px;height:50px;'><span class='bold '>&nbsp;</span></div>";
	$output.='<div><span class="bold">&nbsp;</span></div>';
  $output.='<div>';
  
  
    $output.='<table class="noborder">';
      $output.='<tbody>';
      $output.='<tr style="background-color:red;">';
        $output.="<td class='centered' style='border-top:solid 1px block;width:27%;'>";
          $output.="<div>Autorización administrativa</div>";
          $output.="<br/>";
          $output.="<div>&nbsp;</div>";
        $output.="</td>";
        $output.="<td  class='centered' style='width:40%;'>";
        $output.="</td>";
        $output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
          $output.="<div>Autorización gerente</div>";
          $output.="<br/>";
          $output.="<div>&nbsp;</div>";
        $output.="</td>";
      $output.="</tr>";
      $output.='</tbody>';
    $output.="</table>";
	$output.='</div>';
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>