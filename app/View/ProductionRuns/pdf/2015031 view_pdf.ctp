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
	$output="";
	$output.="<div class='cheques view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>ORDEN DE PRODUCCIÓN # ".$productionRun['ProductionRun']['production_run_code']."</div>";
	$productionRunDate= new DateTime($productionRun['ProductionRun']['production_run_date']); 
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$productionRunDate->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Producto: <span class='underline'>".$productionRun['FinishedProduct']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Cantidad Total: <span class='underline'>".number_format($productionRun['ProductionRun']['raw_material_quantity'],0,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Máquina: <span class='underline'>".$productionRun['Machine']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Operador: <span class='underline'>".$productionRun['Operator']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Turno: <span class='underline'>".$productionRun['Shift']['name']."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Consumo Energia: <span class='underline'>".round($energyConsumption,2)."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";

	if (!empty($productionRun['ProductionMovement'])){
		$rawrows="";
		$finishedrows="";
		$totalPrice=0;
		$totalProducts=0;
		$totalUsed=0;
		$totalRemaining=0;
		foreach ($productionRun['ProductionMovement'] as $productionMovement):
			if ($productionMovement['bool_input']){
				if ($productionMovement['product_quantity']>0){
					$rawrows.="<tr>";
					//$rawrows.="<td>".$productionMovement['StockItem']['name']."</td>";
					$rawrows.="<td>".$productionRun['RawMaterial']['name']."</td>";
					$rawrows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
					$rawrows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],2,".",",")."</td>";
					$rawrows.="<td class='centered'>".number_format($productionMovement['StockItem']['remaining_quantity'],0,".",",")."</td>";
					$totalUsed+=$productionMovement['product_quantity'];
					$totalRemaining+=$productionMovement['StockItem']['remaining_quantity'];
					$rawrows.="</tr>";
				}
			}
			else {
				$resultquality="";
				switch ($productionMovement['StockItem']['production_result_code_id']){
					case 1:
						$resultquality="A";
						break;
					case 2:
						$resultquality="B";
						break;
					case 3:
						$resultquality="C";
						break;
					default:
						$resultquality=$productionMovement['StockItem']['production_result_code_id'];
						
				}	
				
				$finishedrows.="<tr>";
				//$finishedrows.="<td>".$productionMovement['StockItem']['name']."</td>";
				$finishedrows.="<td>".$productionRun['FinishedProduct']['name']."</td>";
				$finishedrows.="<td class='centered'>".number_format($productionMovement['product_quantity'],0,".",",")."</td>";
				$finishedrows.="<td class='centered'>".$resultquality."</td>";
				$finishedrows.="<td class='centered'>C$ ".number_format($productionMovement['product_unit_price'],2,".",",")."</td>";
				$totalPriceProduct=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
				$finishedrows.="<td class='centered'>C$ ".number_format($totalPriceProduct,2,".",",")."</td>";
				$finishedrows.="<td>".$productionRun['RawMaterial']['name']."</td>";
				$finishedrows.="</tr>";
				$totalPrice+=$totalPriceProduct;
				$totalProducts+=$productionMovement['product_quantity'];
			}
		endforeach;
		$rawrows.="<tr class='totalrow'>";
			$rawrows.="<td>Total</td>";
			$rawrows.="<td class='centered'>".number_format($totalUsed,0,".",",")."</td>";
			$rawrows.="<td></td>";
			$rawrows.="<td class='centered'>".number_format($totalRemaining,0,".",",")."</td>";
		$rawrows.="</tr>";
		$finishedrows.="<tr class='totalrow'>";
			$finishedrows.="<td>Total</td>";
			$finishedrows.="<td class='centered'>".number_format($totalProducts,0,".",",")."</td>";
			$finishedrows.="<td class='centered'></td>";
			$finishedrows.="<td class='centered'></td>";
			$finishedrows.="<td class='centered'>C$ ".number_format($totalPrice,4,".",",")."</td>";
			$finishedrows.="<td></td>";
		$finishedrows.="</tr>";
				
		$outputraw="<h3>".__('Raw Materials used in Production Run')."</h3>";
		$outputraw.="<table cellpadding = '0' cellspacing = '0'>";
			$outputraw.="<thead>";
				$outputraw.="<tr>";
					$outputraw.="<th>".__('Identificación Lote')."</th>";
					$outputraw.="<th class='centered'>".__('Used Quantity')."</th>";
					$outputraw.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputraw.="<th class='centered'>".__('Remaining Quantity')."</th>";
				$outputraw.="</tr>";
			$outputraw.="</thead>";
			$outputraw.="<tbody>";
				$outputraw.=$rawrows;
			$outputraw.="</tbody>";
		$outputraw.="</table>";
		
		$outputproduced="<h3>".__('Products Produced in Production Run')."</h3>";
		$outputproduced.="<table cellpadding = '0' cellspacing = '0'>";
			$outputproduced.="<thead>";
				$outputproduced.="<tr>";
					$outputproduced.="<th>".__('Name')."</th>";
					$outputproduced.="<th class='centered'>".__('Produced Quantity')."</th>";
					$outputproduced.="<th class='centered'>".__('Quality')."</th>";
					$outputproduced.="<th class='centered'>".__('Unit Cost')."</th>";
					$outputproduced.="<th class='centered'>".__('Total Cost')."</th>";
					$outputproduced.="<th>".__('Raw Material')."</th>";
				$outputproduced.="</tr>";
			$outputproduced.="</thead>";
			$outputproduced.="<tbody>";
				$outputproduced.=$finishedrows;
			$outputproduced.="</tbody>";
		$outputproduced.="</table>";
		
	}
	$output=$output.$outputraw.$outputproduced;

	$output.="</div>"; 

	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	