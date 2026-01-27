<style>
	div, span {
		font-size:0.9em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	table {
		font-size:0.8em;
	}
	
	pre {
		font-size:0.5em;
	}
	
	div.centered,
	td.centered,
	th.centered
	{
		text-align:center;
	}
	
	table.grid th, table.grid td{
		border:1px solid black;
	}
	
	div.right{
		text-align:right;
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
	.bordered tr td,
	.bordered tr.totalrow td,
	{
		border-width:1px;
		border-style:solid;
		border-color:#000000;
	}
	
	.bordered tr td{
		border-width:0 1px;
	}
	
	table {
		width:100%;
	}
	
	img.resize {
		width:200px; /* you can use % */
		height: auto;
	}
</style>
<?php
	$inventoryDate=date("Y-m-d",strtotime($inventoryDate));
	$inventoryDateTime=new DateTime($inventoryDate);
	$nowDate=date('Y-m-d');
	$nowDateTime=new DateTime($nowDate);
	
	$output="";
	
	$url="img/ornasa_logo.jpg";
	//$imageurl=$this->App->assetUrl($url);
	//$imageurl = $this->Html->url($url, true);
	//$output.="<div>".$imageurl."</div";
	$output.="<table>";
		$output.="<tr>";
			$output.="<td class='bold' style='width:30%;'><img src='".$url."' class='resize'></img></td>";		
			$output.="<td class='centered big' style='width:40%;'>".strtoupper(COMPANY_NAME)."<br/>CONTROL DE INVENTARIO<br/>".$inventoryDateTime->format('d-m-Y')."</td>";
			$output.="<td class='bold' style='width:30%;'>MANAGUA, ".$nowDateTime->format('d-m-Y')."</td>";
		$output.="</tr>";
	$output.="</table>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
  $output.="<h2>Preformas</h2>";
  
  $rawMaterialTable="<table class='grid'>";
    $rawMaterialTable.="<thead>";
      $rawMaterialTable.="<tr>";
        $rawMaterialTable.="<th>".__('Producto')."</th>";
        $rawMaterialTable.="<th>Cantidad Sistema</th>";
				$rawMaterialTable.="<th>Cantidad Física</th>";
      $rawMaterialTable.="</tr>";
    $rawMaterialTable.="</thead>";
    $rawMaterialTable.="<tbody>";
  
    $quantitypreformas=0; 
    foreach ($preformas as $stockItem){
      $remaining="";
      if ($stockItem['0']['Remaining']!=""){
        $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
        $packagingunit=$stockItem['Product']['packaging_unit'];
        // if there are products and the value of packaging unit is not 0, show the number of packages
        if ($packagingunit!=0){
          $numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
          $leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
          $remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
          if ($leftovers >0){
            $remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
          }
          else {
            $remaining.=")";
          }
        }
        $quantitypreformas+=$stockItem['0']['Remaining'];
      }
      else {
        $remaining= "0";
      }
      
      
			if ($remaining>0){
        $rawMaterialTable.= "<tr>";
          $rawMaterialTable.= "<td>".$stockItem['Product']['name']."</td>";
          $rawMaterialTable.="<td class='centered'>".$remaining."</td>";
          $rawMaterialTable.="<td class='centered'></td>";       
        $rawMaterialTable.= "</tr>";        
      }
      
    }
    $totalRow="";
    $totalRow.= "<tr class='totalrow'>";
      $totalRow.= "<td>Total</td>";
      $totalRow.="<td class='centered number'>".$quantitypreformas."</td>";
			$totalRow.="<td></td>";
    $totalRow.= "</tr>";
    $rawMaterialTable.=$totalRow;
    $rawMaterialTable.= "</tbody>";
  $rawMaterialTable.= "</table>";
  $output.=$rawMaterialTable;
   
	$output.="<h2>Botellas</h2>";
	$finishedMaterialTable= "<table class='grid'>";
		$finishedMaterialTable.= "<thead>";
			$finishedMaterialTable.= "<tr>";
				$finishedMaterialTable.= "<th>Materia Prima</th>";
				$finishedMaterialTable.= "<th>Producto</th>";
				$finishedMaterialTable.= "<th>Sistema A</th>";
				$finishedMaterialTable.= "<th>Sistema B</th>";
				$finishedMaterialTable.= "<th>Cantidad A Física</th>";
				$finishedMaterialTable.= "<th>Cantidad B Física</th>";
			$finishedMaterialTable.= "</tr>";
		$finishedMaterialTable.= "</thead>";
		$finishedMaterialTable.="<tbody>";

		$quantitybotellasA=0; 
		$quantitybotellasB=0; 
		$quantitybotellas=0; 
		foreach ($productos as $stockItem){
			$average="";
			$remainingA=0;
			$remainingB=0;
			$remaining="";
			$packagingunit=$stockItem['Product']['packaging_unit'];
			
			if ($stockItem['0']['Remaining_A']!=""){
				$remainingA= number_format($stockItem['0']['Remaining_A'],0,".",","); 
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0 && $stockItem['0']['Remaining_A']!=0){
					$numberpackagingunitsA=floor($stockItem['0']['Remaining_A']/$packagingunit);
					$leftoversA=$stockItem['0']['Remaining_A']-$numberpackagingunitsA*$packagingunit;
					$remainingA .= " (".$numberpackagingunitsA." ".__("emps");
					if ($leftoversA >0){
						$remainingA.= " + ".$leftoversA.")";
					}
					else {
						$remainingA.=")";
					}
				}
				$quantitybotellasA+=$stockItem['0']['Remaining_A'];
			}
			else {
				$remainingA= "0";
				$totalvalueA="0";
			}
			if ($stockItem['0']['Remaining_B']!=""){
				$remainingB= number_format($stockItem['0']['Remaining_B'],0,".",","); 
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0 && $stockItem['0']['Remaining_B']!=0){
					$numberpackagingunitsB=floor($stockItem['0']['Remaining_B']/$packagingunit);
					$leftoversB=$stockItem['0']['Remaining_B']-$numberpackagingunitsB*$packagingunit;
					$remainingB .= " (".number_format($numberpackagingunitsB,0,".",",")." ".__("emps");
					if ($leftoversB >0){
						$remainingB.= " + ".number_format($leftoversB,0,".",",").")";
					}
					else {
						$remainingB.=")";
					}
				}
				$quantitybotellasB+=$stockItem['0']['Remaining_B'];
			}
			else {
				$remainingB= "0";
			}
			
			$remaining=$remainingA+$remainingB;
			
			$quantitybotellas+=$remaining;
			if ($remaining>0){
				$finishedMaterialTable.="<tr>";
					$finishedMaterialTable.="<td>".$stockItem['RawMaterial']['name']."</td>";
					$finishedMaterialTable.="<td>".$stockItem['Product']['name']."</td>";
					$finishedMaterialTable.="<td class='centered'>".$remainingA."</td>";
					$finishedMaterialTable.="<td class='centered'>".$remainingB."</td>";
					$finishedMaterialTable.="<td></td>";
					$finishedMaterialTable.="<td></td>";
					//$finishedMaterialTable.="<td class='totalcolumn centered number'>".$remaining."</td>";
					
				$finishedMaterialTable.="</tr>";
			}
		}
			$finishedMaterialTable.="<tr class='totalrow'>";
				$finishedMaterialTable.="<td>Total</td>";
				$finishedMaterialTable.="<td></td>";
				 
				
				$finishedMaterialTable.="<td class='centered number'>".$quantitybotellasA."</td>";
				$finishedMaterialTable.="<td class='centered number'>".$quantitybotellasB."</td>";
				$finishedMaterialTable.="<td></td>";
				$finishedMaterialTable.="<td></td>";
				//$finishedMaterialTable.="<td class='centered number'>".$quantitybotellas."</td>";
				
			$finishedMaterialTable.="</tr>";
		$finishedMaterialTable.="</tbody>";
	$finishedMaterialTable.="</table>";
	
	$output.=$finishedMaterialTable;

  $output.="<br/>";
  $output.="<br/>";
  $output.="<br/>";
  $output.="<br/>";
	$output.="<h2>Tapones</h2>";
	
	$capTable="<table class='grid'>";
		$capTable.="<thead>";
			$capTable.="<tr>";
				$capTable.="<th>Producto</th>";
				
				$capTable.="<th>Cantidad Sistema</th>";
				$capTable.="<th style='width:20%'>Cantidad Física</th>";
			$capTable.="</tr>";
		$capTable.="</thead>";
		$capTable.="<tbody>";

		$valuetapones=0;
		$quantitytapones=0; 
		
		foreach ($tapones as $stockItem){
			$remaining="";
			if ($stockItem['0']['Remaining']!=""){
				$remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
				$packagingunit=$stockItem['Product']['packaging_unit'];
				// if there are products and the value of packaging unit is not 0, show the number of packages
				if ($packagingunit!=0){
					$numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
					$leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
					$remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
					if ($leftovers >0){
						$remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
					}
					else {
						$remaining.=")";
					}
				}
				$quantitytapones+=$stockItem['0']['Remaining'];
			}
			else {
				$remaining= "0";
			}
			if ($remaining>0){
				$capTable.="<tr>";
					$capTable.="<td>".$stockItem['Product']['name']."</td>";
					
					$capTable.="<td class='centered'>".$remaining."</td>";
					$capTable.="<td class='centered' style='width:20%'></td>";
				$capTable.="</tr>";
			}
		}
      $capTable.="<tr class='totalrow'>";
        $capTable.="<td>Total</td>";
				$capTable.="<td class='centered number'>".$quantitytapones."</td>";
				$capTable.="<td style='width:20%'></td>";
			$capTable.="</tr>";
		$capTable.="</tbody>";
	$capTable.="</table>";
	$output.=$capTable;
  
  foreach ($otherProducts as $otherProductCategory){
    if (!empty($otherProductCategory['ProductLines'])){
      //echo "<h2>Líneas de Categoría ".$otherProductCategory['ProductCategory']['name']."</h2>";
      foreach ($otherProductCategory['ProductLines'] as $otherProductType){
        
        $otherMaterialTable="<table class='grid'>";
          $otherMaterialTable.="<thead>";
            $otherMaterialTable.="<tr>";
              $otherMaterialTable.="<th>".__('Product')."</th>";
              $otherMaterialTable.="<th>Cantidad Sistema</th>";
              $otherMaterialTable.="<th style='width:20%'>Cantidad Física</th>";
            $otherMaterialTable.="</tr>";
          $otherMaterialTable.="</thead>";
          $otherMaterialTable.="<tbody>";

          $quantityProducts=0; 
          $tableRows="";
          foreach ($otherProductType['Products'] as $stockItem){
            $remaining="";
            if ($stockItem['0']['Remaining']!=""){
              $remaining= number_format($stockItem['0']['Remaining'],0,".",","); 
              $packagingunit=$stockItem['Product']['packaging_unit'];
              // if there are products and the value of packaging unit is not 0, show the number of packages
              if ($packagingunit!=0){
                $numberpackagingunits=floor($stockItem['0']['Remaining']/$packagingunit);
                $leftovers=$stockItem['0']['Remaining']-$numberpackagingunits*$packagingunit;
                $remaining .= " (".number_format($numberpackagingunits,0,".",",")." ".__("packaging units");
                if ($leftovers >0){
                  $remaining.= " ".__("and")." ".number_format($leftovers,0,".",",")." ".__("leftover units").")";
                }
                else {
                  $remaining.=")";
                }
              }
              $quantityProducts+=$stockItem['0']['Remaining'];
            }
            else {
              $remaining= "0";
             
            }
            if ($remaining!="0"){
              $tableRows.="<tr>";
                $tableRows.="<td>".$stockItem['Product']['name']."</td>";
                $tableRows.="<td class='centered'>".$remaining."</td>";
                $tableRows.="<td class='centered' style='width:20%'></td>";
              $tableRows.="</tr>";
            }
          }
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";
              $totalRow.="<td class='centered number'>".$quantityProducts."</td>";
              $totalRow.="<td class='centered' style='width:20%'></td>";
            $totalRow.="</tr>";
            $otherMaterialTable.=$tableRows.$totalRow;
          $otherMaterialTable.="</tbody>";
        $otherMaterialTable.="</table>";
        $output.="<h2>".$otherProductType['ProductType']['name']."</h2>";
        $output.=$otherMaterialTable;
      }
    }
  }  
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	/*
	$footer="";
	$footer.="<table style='width:100%'>";
		$footer.="<tr style='border:0px;'>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Elaborado</td>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Firma Empleado</td>";
			$footer.="<td align='center' class='underline' style='border:0px;width:33.3%'>Autorizado</td>";
		$footer.="</tr>";
	$footer.="</table>";
	$output.=$footer;
	*/
	$output.="</div>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>