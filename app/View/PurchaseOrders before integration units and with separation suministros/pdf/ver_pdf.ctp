<style>
	#header {
		position:relative;
	}
	
	#header div.imagecontainer {
		width:47%;
		padding-left:3%;
		clear:none;
	}
	
	@media print{
		.noprint{ display:none;}
	}

	img.resize {
		width:400px; /* you can use % */
		height: auto;
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
  .extraSmall {
    font-size:0.6em;
  }
  
	.left {
    text-align:left;
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
		width:90%;
		margin-left:auto;
		margin-right:auto;
	}
	table.bordered {
		border-collapse:collapse; 
	}
  
  td.bordered {
    border:1px solid black;
    vertical-align:middle;
    text-align:center;
  }
  
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.9em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
  
  .noborder tr th,
  .noborder tr td
  {
    border-width:0px;
  }
  
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
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
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);

	$url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
	
  $purchaseOrderDate=date("Y-m-d",strtotime($purchaseOrder['PurchaseOrder']['purchase_order_date']));
	$purchaseOrderDateTime=new DateTime($purchaseOrderDate);
  
	$output="";
	$output.="<table class='noprint'>";
		$output.="<tr>";
			/*
      $output.="<td class='extraSmall left' style='width:20%;'>
        <br/>
        <div>Dirección: Km 10.1 Carretera Nueva León 150 m arriba</div>
        <div>Tell:2299-1123</div>
        <div>administración @ornasa.com<div>
        <div>www.ornasa.com</div>
      </td>";
      */
      $output.="<td class='bold' style='width:60%;'><img src='".$imageurl."' class='resize'></img></td>";		
			$output.="<td class='bold' style='width:40%;'>
        <table>
          <thead>
            <tr class='centered' >
              <th style='width:40%'>FECHA</th>
              <th>ORDEN</th>
            </tr>
          </thead>
          <tbody class='centered'>
            <tr>
              <td style='width:40%'>".$purchaseOrderDateTime->format('d-m-Y')."</td>
              <td>".$purchaseOrder['PurchaseOrder']['purchase_order_code']."</td>
            </tr>
          </tbody>
        </table>  
      </td>";
		$output.="</tr>";
	$output.="</table>";
  if ($purchaseOrder['PurchaseOrder']['bool_authorized']){
    $output.='<p style="text-align:center;font-size:1.3em;font-weight:700;">AUTORIZADO</p>';
    
  }
  $output.='<p style="text-align:center;">Solicitado por '.$purchaseOrder['User']['first_name'].' '.$purchaseOrder['User']['last_name'].'</p>';
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<table class='bordered'>";
		$output.="<tr>";
			$output.="<td class='centered' style='width:70%;'>PROVEEDOR</td>";
      $output.="<td class='centered' style='width:30%;'>REFERENCIA NO</td>";
    $output.="</tr>";
    $output.="<tr>";  
      $output.="<td class='bold centered big' style='width:70%;font-size:2em!important;padding-top:25px;'>".$purchaseOrder['Provider']['company_name']."</td>";      
			$output.="<td style='width:30%;'>
        <table class='noborder'>
          <tr class='centered'>
            <td>ORDEN DE COMPRA</td>
          </tr>
          <tr class='centered'>
            <td>CONDICIONES DE PAGO: ".($purchaseOrder['PurchaseOrder']['bool_credit']?"CRÉDITO":"CONTADO")."</td>
          </tr>"
          .($purchaseOrder['PurchaseOrder']['bool_credit']?"<tr class='centered'>
            <td class='bold'>".$purchaseOrder['Provider']['credit_days']." DÍAS</td>
          </tr>":"").
        "</table>  
      </td>";
		$output.="</tr>";
	$output.="</table>";
  
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  
  $output.="<table class='bordered'>";
    $output.="<tr>";
      $output.="<td style='width:10%;'>".__('CAJONES')."</td>";
      $output.="<td class='centered' style='width:35%;'>".__('DESCRIPCIÓN')."</td>";
      $output.="<td class='centered'  style='width:15%;'>".__('CANTIDAD')."</td>";
      $output.="<td class='centered' style='width:20%;'>".__('P. UNITARIO')."</td>";
      $output.="<td class='centered' style='width:20%;'>".__('P. TOTAL')."</td>";
    $output.="</tr>";
  $output.="</table>";
  
  
  
  $output.="<table class='outerborders'>";  
    if (!empty($purchaseOrder['PurchaseOrderProduct'])){
			$totalProductQuantity=0;	
			foreach ($purchaseOrder['PurchaseOrderProduct'] as $purchaseOrderProduct){ 
				$totalProductQuantity+=$purchaseOrderProduct['product_quantity'];
				if ($purchaseOrderProduct['currency_id']==CURRENCY_CS){
					$classCurrency=" class='CScurrency'";
				}
				elseif ($purchaseOrderProduct['currency_id']==CURRENCY_USD){
					$classCurrency=" class='USDcurrency'";
				}
        
        $productQuantity=$purchaseOrderProduct['product_quantity'];
        $packagingUnit=$purchaseOrderProduct['Product']['packaging_unit'];
        $productPackaging=$productQuantity + ' Uds';
        
        $numPacks=0;
        $numUnits=$productQuantity;
        if ($packagingUnit>0 && $productQuantity >= $packagingUnit){
          $numPacks=floor($productQuantity/$packagingUnit);
          $numUnits=$productQuantity%$packagingUnit;
          $productPackaging=$numPacks." Uds de empaque y ".$numUnits." Uds";
        }
        
				$output.="<tr>";
					$output.="<td class='centered' style='width:10%;'>".$numPacks."</td>";
          $output.="<td style='width:35%;'>".$purchaseOrderProduct['Product']['name'].(empty($purchaseOrderProduct['Product']['code'])?"":" (".$purchaseOrderProduct['Product']['code'].")")."</td>";
					$output.="<td class='centered' style='width:15%;'>".number_format($purchaseOrderProduct['product_quantity'],0,".",",")."</td>";
					$output.="<td style='width:20%;'><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($purchaseOrderProduct['product_unit_cost'],8,".",",")."</span></td>";
					$output.="<td><span class='currency'>".$purchaseOrder['Currency']['abbreviation']."</span><span class='right'>".number_format($purchaseOrderProduct['product_total_cost'],2,".",",")."</span></td>";
					
				$output.="</tr>";
			}
      if (count($purchaseOrder['PurchaseOrderProduct'])<10){
        for ($i=count($purchaseOrder['PurchaseOrderProduct']);$i<10;$i++){
          $output.="<tr>";
            $output.="<td class='centered' style='color:#fff'>-</td>";
            $output.="<td class='centered'>&nbsp;</td>";
            $output.="<td class='centered'>&nbsp;</td>";
            $output.="<td class='centered'>&nbsp;</td>";
            $output.="<td class='centered'>&nbsp;</td>";          
          $output.="</tr>";
        }
      }
    }
  $output.="</table>";    
  $output.="<table class='bordered'>";    
    $output.="<tr'>";
      $output.="<td class='centered' style='width:45%;'>SUBTOTAL</td>";
      $output.="<td class='centered' style='width:15%;'>IVA</td>";
      $output.="<td class='centered' style='width:40%;'>TOTAL</td>";
    $output.="</tr>";
    $output.="<tr'>";
      $output.="<td class='centered'><span>".$purchaseOrder['Currency']['abbreviation']." ".number_format($purchaseOrder['PurchaseOrder']['cost_subtotal'],2,".",",")."</span></td>";
      $output.="<td class='centered'><span>".$purchaseOrder['Currency']['abbreviation']." ".number_format($purchaseOrder['PurchaseOrder']['cost_iva'],2,".",",")."</span></td>";
      $output.="<td class='centered'><span>".$purchaseOrder['Currency']['abbreviation']." ".number_format($purchaseOrder['PurchaseOrder']['cost_total'],2,".",",")."</span></td>";
    $output.="</tr>";
  $output.="<table>";
	
	$output.="<div style='min-height:50px;height:50px;'><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  
  $output.="<table class='noborder' style='margin-top:5em;'>";
		$output.="</tr>";
    $output.="<tr style='margin-bottom:2em;'>";
			$output.="<td  class='centered' style='width:27%;'>";
				$output.="<div> </div>";
			$output.="</td>";
      $output.="<td  class='centered' style='width:40%;'>";
			$output.="</td>";
      if ($purchaseOrder['PurchaseOrder']['bool_authorized']){
      
        $output.="<td  class='centered' style='width:27%;'>";
          $output.="<div>Autorizado electrónicamente por Alejandro Ortega Tellería</div>";
        $output.="</td>";
      }
      else {
        $output.="<td  class='centered' style='width:27%;'>";
          $output.="<div>Firma digital pendiente</div>";
        $output.="</td>";
      }
      
		$output.="</tr>";
    $output.="<tr style='margin-bottom:2em;'>";
			$output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
				$output.="<div>Autorización administrativa</div>";
				$output.="<br/>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
      $output.="<td  class='centered' style='width:40%;'>";
			$output.="</td>";
      if ($purchaseOrder['PurchaseOrder']['bool_authorized']){
      
        $output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
          $output.="<div>Autorización gerente</div>";
          $output.="<br/>";
          $output.="<div>&nbsp;</div>";
        $output.="</td>";
      }
      
		$output.="</tr>";
	$output.="</table>";
	//$output.="Pdf generado el ".$currentDateTime->format("d/m/Y H:i:s");
	
	/*
	$roleName="";
	switch ($quotation['User']['role_id']){
		case ROLE_ADMIN: 
			$roleName="Gerente";
			break;
		case ROLE_ASSISTANT: 
			$roleName="Asistente Ejecutivo";
			break;
		case ROLE_SALES_EXECUTIVE: 
			$roleName="Ejecutivo de Venta";
			break;	
	}
	*/
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>