<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
  .verysmall {
		font-size:0.75em;
	}
	.big{
		font-size:1.5em;
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
  //pr($remainingPreformaProductionRunDate);  
	$output="";
  $nowDateTime= new DateTime(); 
  $output.="<div class='right verysmall'>".$nowDateTime->format('d-m-Y H:i:s')."</div>";
  $output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
  $output.="<div class='centered big bold'>ORDEN DE PRODUCCIÓN # ".$productionRun['ProductionRun']['production_run_code']."</div>";
  $productionRunDateTime= new DateTime($productionRun['ProductionRun']['production_run_date']); 
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$productionRunDateTime->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
  $output.="</table>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:50%'>";
			$output.="<div>PRODUCTO: <span class='underline'>".$productionRun['FinishedProduct']['name']."</span></div>";
			$output.="</td>";
      $output.="<td style='width:50%'>";
			$output.="<div>MÁQUINA: <span class='underline'>".$productionRun['Machine']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:50%'>";
			$output.="<div>CANTIDAD TOTAL UTILIZADA: <span class='underline'>".number_format($productionRun['ProductionRun']['raw_material_quantity'],0,".",",")."</span></div>";
			$output.="</td>";
      $output.="<td style='width:50%'>";
			$output.="<div>OPERADOR: <span class='underline'>".$productionRun['Operator']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
    foreach($usedConsumablesArray as $bagId=>$bagData){
      $output.="<tr>";
        $output.="<td style='width:50%'>";
        $output.="<div>CANTIDAD ".$bagData['consumable_name'].": <span class='underline'>".number_format($bagData['product_quantity'],0,".",",")."</span></div>";
        $output.="</td>";
        $output.="<td style='width:50%'>";
        $output.="<div></div>";
        $output.="</td>";
      $output.="</tr>";
    }
		$output.="<tr>";
      $output.="<td style='width:50%'>";
      $output.='<div>'.(empty($productionRun['RawMaterial']['id'])?"Preforma N/A":("QUEDA ".$productionRun['RawMaterial']['name']." ".$productionRunDateTime->format('d-m-Y').": <span class='underline'>".number_format($remainingPreformaProductionRunDate[0][0]['Remaining'],0,".",","))).'</span></div>';
			$output.="</td>";
			$output.="<td style='width:50%'>";
			$output.="<div>TURNO: <span class='underline'>".$productionRun['Shift']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
    //pr($remainingBagProductionRunDate);
    $output.="<tr>";
      $output.="<td style='width:50%'>";
			$output.="<div>QUEDA DE BOLSA ".$productionRunDateTime->format('d-m-Y').": <span class='underline'>".number_format($remainingBagProductionRunDate[0][0]['Remaining'],0,".",",")."</span></div>";
			$output.="</td>";
			$output.="<td style='width:50%'>";
			$output.="<div>INCIDENCIAS: <span class='underline'>".(empty($productionRun['Incidence']['name'])?'NO INCIDENCIAS':$productionRun['Incidence']['name'])."</span></div>";
			$output.="</td>";
		$output.="</tr>";
  $output.="</table>";
  $output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<table>"; 
		$output.="<tr>";
			$output.="<td style='width:100%'>";
      if (!empty($productionRun['ProductionRun']['comment'])){
        $output.="<div>".__('Comment').": <p>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$productionRun['ProductionRun']['comment']))."</p></div>";
      }
      else {
        $output.="<div>".__('Comment').": -</div>";
      }
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";

	if (!empty($productionRun['ProductionMovement'])){
		$finishedrows="";
    $unitCost=0;
    $totalPrice=0;
    $totalProducts=0;
    $quantityA=0;
    $quantityB=0;
    $quantityC=0;
    
		foreach ($productionRun['ProductionMovement'] as $productionMovement){
			if (!$productionMovement['bool_input']){
				$resultquality="";
				switch ($productionMovement['StockItem']['production_result_code_id']){
					case 1:
						$resultquality="A";
            $quantityA=number_format($productionMovement['product_quantity'],0,".",",");
						break;
					case 2:
						$resultquality="B";
            $quantityB=number_format($productionMovement['product_quantity'],0,".",",");
						break;
					case 3:
						$resultquality="C";
            $quantityC=number_format($productionMovement['product_quantity'],0,".",",");
						break;
					default:
						$resultquality=$productionMovement['StockItem']['production_result_code_id'];
				}
        $unitCost=number_format($productionMovement['product_unit_price'],2,".",",");
        $totalPrice+=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
				$totalProducts+=$productionMovement['product_quantity'];
      }    
		}		
    $finishedrows.="<tr>";
      $finishedrows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
      $finishedrows.="<td class='centered'>".$quantityA."</td>";
      $finishedrows.="<td class='centered'>".$quantityB."</td>";
      $finishedrows.="<td class='centered'>".$quantityC."</td>";
      $finishedrows.="<td class='centered'>".number_format($totalProducts,0,".",",")."</td>";
      if($userrole!=ROLE_FOREMAN) {
        $finishedrows.="<td class='centered'>C$ ".$unitCost."</td>";
        $finishedrows.="<td class='centered'>C$ ".number_format($totalPrice,4,".",",")."</td>";
      }
    $finishedrows.="</tr>";
				
		$outputproduced="<h3>".__('Products Produced in Production Run')." ".$productionRun['FinishedProduct']['name']." ".$productionRun['RawMaterial']['name']."</h3>";
		$outputproduced.="<table cellpadding = '0' cellspacing = '0'>";
			$outputproduced.="<thead>";
				$outputproduced.="<tr>";
					$outputproduced.="<th>".__('Name')."</th>";
          $outputproduced.="<th class='centered'>A</th>";
          $outputproduced.="<th class='centered'>B</th>";
          $outputproduced.="<th class='centered'>C</th>";
					$outputproduced.="<th class='centered'>".__('Produced Quantity')."</th>";
					
          if($userrole!=ROLE_FOREMAN) {
            $outputproduced.="<th class='centered'>".__('Unit Cost')."</th>";
            $outputproduced.="<th class='centered'>".__('Total Cost')."</th>";
					}
				$outputproduced.="</tr>";
			$outputproduced.="</thead>";
			$outputproduced.="<tbody>";
				$outputproduced.=$finishedrows;
			$outputproduced.="</tbody>";
		$outputproduced.="</table>";
	}
  $output=$output.$outputproduced;

  $output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<table style='width:100%'>";
		$output.="<tr style='margin-bottom:2em;'>";
			$output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
				$output.="<div>Operador</div>";
				$output.="<br/>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
      $output.="<td  class='centered' style='width:5%;'>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
			$output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
				$output.="<div>Resp. Bodega</div>";
				$output.="<br/>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
      $output.="<td  class='centered' style='width:5%;'>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
			$output.="<td  class='centered' style='border-top:solid 1px block;width:27%;'>";
				$output.="<div>Administración</div>";
				$output.="<br/>";
				$output.="<div>&nbsp;</div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	