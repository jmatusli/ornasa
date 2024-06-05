<style>
  @media print{
		.noprint{ display:none;}
	}

	img.resize {
		width:400px; /* you can use % */
		height: auto;
	}
	
	div, span {
		font-size:1em;
	}
  .extraSmall {
    font-size:0.6em;
  }
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
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
  .red {
    color:#f00;
  }
  .redBackground{
    background-color:red!important;
    color:#fff;
  }
  
  table {
		width:100%;
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
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	$url="img/ornasa_logo.jpg";
	$imageurl=$this->App->assetUrl($url);
  
  $invoiceDateTime=new DateTime($order['Order']['order_date']);
  $invoiceCode=$order['Order']['order_code'];
  
  if ($invoice['Invoice']['bool_credit']){
		$dueDate=new DateTime($invoice['Invoice']['due_date']);
  }
	
	$output="";
	$output.="<table class='noprint'>";
		$output.="<tr>";
			$output.="<td class='extraSmall left' style='width:20%;'>
        <br/>
        <div>Dirección: Km 10.1 Carretera Nueva León 150 m arriba</div>
        <div>Tell:2299-1123</div>
        <div>administración @ornasa.com<div>
        <div>www.ornasa.com</div>
      </td>";
      $output.="<td class='bold' style='width:53.33%;'><img src='".$imageurl."' class='resize'></img></td>";		
			$output.="<td class='bold' style='width:26.66%;'>
        <div>FACTURA</div>
        <div class='red'>
          <span class='left'>No</span>
          <span class='right'>".$invoiceCode."</span>
        </div>
        <div>RUC:J0310000103860</div>
        <table class='bordered big'>
          <tr class='centered'>
              <th>Día</th>
              <th>Mes</th>
              <th>Año</th>
            </tr>
          
            <tr  class='centered'>
              <td>".$invoiceDateTime->format('d')."</td>
              <td>".$invoiceDateTime->format('m')."</td>
              <td>".$invoiceDateTime->format('Y')."</td>
            </tr>
          
        </table>  
      </td>";
		$output.="</tr>";
	$output.="</table>";
  
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:56.66%'>
        <div>
          <span class='left'>Cliente:</span>
          <span class='left'>".$order['ThirdParty']['company_name']."</span>
        </div>
         <div>
          <span class='left'>Dirección:</span>
          <span class='left'></span>
         </div>
        <div>
          <span class='left'>Teléfono:</span>
          <span class='left'>".$order['ThirdParty']['phone']."</span>
         </div>
			</td>";
      $output.="<td style='width:16.66%' class='bold'>".($invoice['Invoice']['bool_credit']?"Crédito":"Contado")."</td>";
      $output.="<td style='width:16.66%' class='bordered'>Vence:".($invoice['Invoice']['bool_credit']?($dueDate->format('d-m-Y')):"N/A")."</td>";
			
		$output.="</tr>";
	$output.="</table>";
  
	$output.="<div class='related' style='margin-top:20px;'>";
	if (!empty($summedMovements)){
		$output.="<table class='bordered big'>";
			$output.="<thead class='redBackground'>";
				$output.="<tr>";
					$output.="<th class='centered' style='width:15%'>".__('Quantity')."</th>";
          $output.="<th>Descripción</th>";
					$output.="<th class='centered' style='width:20%'>".__('Unit Price')."</th>";
					$output.="<th class='centered' style='width:22%'>".__('Total Price')."</th>";
				$output.="</tr>";
			$output.="</thead>";
			
			$totalquantity=0;
			$totalprice=0;
			$output.="<tbody>";
			foreach ($summedMovements as $summedMovement){ 
				$output.="<tr>";
          $output.="<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
          if ($summedMovement['StockMovement']['production_result_code_id']>0){
            $output.="<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
          }
          else {
            $output.="<td>".$summedMovement['Product']['name']."</td>";
          }
          $output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],4,".",",")."</span></td>";
          
          $output.="<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
          
          $totalquantity+=$summedMovement[0]['total_product_quantity'];
          $totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
          $output.="</tr>";
			}
      $output.="<tr>";
        $output.="<td rowspan='3' colspan='2'>
          <div>EL SALDO DE ESTA FACTURA TENDRÁ EL 5% DE INTERES DESPUES DE VENCIDA</div>
          <div class='bold'>
            <span class='left'>Recibido por</span>
            <span style='margin-left:50px;'>No de Identidad</span>
            <span style='margin-left:50px;'>Entregado por</span>
          </div>
          </div>
        </td>";
         $output.="<td class='bold'>SUB-TOTAL</td>";
         $output.="<td>
          <span class='currency'>C$ </span>
          <span class='amountright'>".number_format($totalprice,2,".",",")."</span>
         </td>";
        $output.="</tr>";
        $output.="<tr>";
          $output.="<td class='bold'>IVA.</td>";
          $output.="<td>
            <span class='currency'>C$ </span>
            <span class='amountright'>".number_format($invoice['Invoice']['iva_price'],2,".",",")."</span>
         </td>";
        $output.="</tr>";
         $output.="<tr>";
          $output.="<td class='bold'>TOTAL</td>";
          $output.="<td>
            <span class='currency'>C$ </span>
            <span class='amountright'>".number_format($invoice['Invoice']['total_price'],2,".",",")."</span>
         </td>";
        $output.="</tr>";
			$output.="</tbody>";
		$output.="</table>";
	}
  $output.="</div>";
  $output.="<div class='bold extraSmall'>Lit Barrios Ruc 00107024500158 A/MP/4/0072/07-2018 O.T. 6747/09-2018 - ACF/4/3649 5 B.</div>";

	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	