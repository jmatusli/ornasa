<?php 
	class ProductionRunDisplayHelper extends AppHelper {
		var $helpers = ['Html','Paginator']; // include the html helper
		
		function productionRunTableContents($productionRuns,$boolIncludeIncidences=false,$userRole){
			$tableHeader="";
      $tableHeader.="<thead>";
        $tableHeader.="<tr>";
          if ($boolIncludeIncidences){
            $tableHeader.="<th>".$this->Paginator->sort('incidence_id','Incidencia')."</th>";
          }
          $tableHeader.="<th>".$this->Paginator->sort('production_run_code','Código')."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('production_run_date','Fecha')."</th>";
          $tableHeader.="<th>Comentario</th>";
          $tableHeader.="<th>".$this->Paginator->sort('FinishedProduct.name',__('Product'))."</th>";
          $tableHeader.="<th>".$this->Paginator->sort('RawMaterial.name')."</th>";
          $tableHeader.="<th class='centered'>".$this->Paginator->sort('raw_material_quantity')."</th>";
          $tableHeader.="<th class='centered'>".__('Cantidad A')."</th>";
          if($userRole!=ROLE_FOREMAN) {
            //$tableHeader.="<th class='centered'>".__('Value A')."</th>";
          }
          $tableHeader.="<th class='centered'>".__('Cantidad Producido')."</th>";
          if($userRole!=ROLE_FOREMAN) {
            //$tableHeader.="<th class='centered'>".__('Value Produced')."</th>";
          }
        $tableHeader.="</tr>";
      $tableHeader.="</thead>";

      $tableRows="";
        $totalQuantityA=0;
        $totalQuantityB=0;
        $totalQuantityC=0;
        $totalValueA=0;
        $totalValueB=0;
        $totalValueC=0;
        $totalQuantityAll=0;
        $totalValueAll=0;
        foreach ($productionRuns as $productionRun){
          //pr($productionRun);
          $quantityA=0;
          $quantityB=0;
          $quantityC=0;
          $valueA=0;
          $valueB=0;
          $valueC=0;
          $quantityAll=0;
          $valueAll=0;
         
          foreach ($productionRun['ProductionMovement'] as $productionMovement){
            if ($productionMovement['production_run_id']==$productionRun['ProductionRun']['id']&& !$productionMovement['bool_input']){
              switch ($productionMovement['production_result_code_id']){
                case 1:
                  $quantityA+=$productionMovement['product_quantity'];						
                  $valueA+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
                  break;
                case 2:
                  $quantityB+=$productionMovement['product_quantity'];
                  $valueB+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
                  break;
                case 3:
                  $quantityC+=$productionMovement['product_quantity'];
                  $valueC+=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
                  break;
              }
            }
          }
          $quantityAll=$quantityA+$quantityB+$quantityC;
          $valueAll=$valueA+$valueB+$valueC;
          $totalQuantityA+=$quantityA;
          $totalQuantityB+=$quantityB;
          $totalQuantityC+=$quantityC;
          $totalValueA+=$valueA;
          $totalValueB+=$valueB;
          $totalValueC+=$valueC;
          $totalQuantityAll+=$quantityAll;
          $totalValueAll+=$valueAll;
          
          $acceptableProductionValue=0;
          //pr($productionRun['FinishedProduct']);
          if (!empty($productionRun['FinishedProduct']['ProductProduction'])){						
            $acceptableProductionValue=$productionRun['FinishedProduct']['ProductProduction'][0]['acceptable_production'];
            if(date('w', strtotime($productionRun['ProductionRun']['production_run_date'])) == 6){
              $acceptableProductionValue=$acceptableProductionValue/2;
            }
          }
          $boolMarkGreen=false;
          
          //echo "acceptableProductionValue is ".$acceptableProductionValue."<br/>";
          //echo "quantityA is ".$quantityA."<br/>";
          if ($acceptableProductionValue>0){
            if ($quantityA>=$acceptableProductionValue){
              $boolMarkGreen=true;
            }
          }

          $tableRow="";
          $productionRunDateTime=new DateTime($productionRun['ProductionRun']['production_run_date']);
		
          $tableRow.="<tr".($productionRun['ProductionRun']['bool_annulled']?" class='italic'":"").($productionRun['Shift']['id']==SHIFT_NIGHT?" style='background-color:#888888 !important;'":"").">";
            if ($boolIncludeIncidences){
              $tableRow.="<td>".$this->Html->link($productionRun['Incidence']['name'], array('controller'=>'incidences','action' => 'view', $productionRun['Incidence']['id']))."</td>";
            }
            $tableRow.="<td>".$this->Html->link($productionRun['ProductionRun']['production_run_code'].($productionRun['ProductionRun']['bool_annulled']?" (Anulada)":""), array('controller'=>'productionRuns','action' => 'detalle', $productionRun['ProductionRun']['id']))."</td>";
            $tableRow.="<td>".$productionRunDateTime->format('d-m-Y')."</td>";
            $tableRow.="<td>".($productionRun['ProductionRun']['comment'])."</td>";
            $tableRow.="<td>".$this->Html->link($productionRun['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'verReporteProducto', $productionRun['FinishedProduct']['id']))."</td>";
            $tableRow.="<td>".$this->Html->link($productionRun['RawMaterial']['name'], array('controller' => 'stock_items', 'action' => 'verReporteProducto', $productionRun['RawMaterial']['id']))."</td>";
            $tableRow.="<td class='centered number'>".$productionRun['ProductionRun']['raw_material_quantity']."</td>";
            if ($boolMarkGreen){
              if(date('w', strtotime($productionRun['ProductionRun']['production_run_date'])) == 6){
                $tableRow.="<td class='centered number darkgreentext bold'>".$quantityA."</td>";
              }
              else {
                $tableRow.="<td class='centered number greentext bold'>".$quantityA."</td>";
              }
            }
            else {
              $tableRow.="<td class='centered number'>".$quantityA."</td>";
            }
            if($userRole!=ROLE_FOREMAN) {
              //$tableRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueA."</span></td>";
            }
            $tableRow.="<td class='centered number'><span class='amountright'>".$quantityAll."</span></td>";
            if($userRole!=ROLE_FOREMAN) {
              //$tableRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$valueAll."</td>";
            }

          $tableRow.="</tr>";
          $tableRows.=$tableRow;
        } 
        $totalRows="";
        $totalRows.="<tr class='totalrow'>";
          $totalRows.="<td>Total</td>";
          if ($boolIncludeIncidences){
            $totalRows.="<td></td>";
          }
          $totalRows.="<td></td>";
          $totalRows.="<td></td>";
          $totalRows.="<td></td>";
          $totalRows.="<td class='centered number'>".$totalQuantityAll."</td>";
          
          $totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
          if($userRole!=ROLE_FOREMAN) {
            //$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueA."</span></td>";
          }
          $totalRows.="<td class='centered number'>".$totalQuantityAll."</td>";
          if($userRole!=ROLE_FOREMAN) {
            //$totalRows.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalValueAll."</span></td>";
          }
          $totalRows.="<td></td>";
        $totalRows.="</tr>";
        $totalRows.="<tr class='totalrow'>";
          $totalRows.="<td>Porcentajes</td>";
          if ($boolIncludeIncidences){
            $totalRows.="<td></td>";
          }
          $totalRows.="<td></td>";
          $totalRows.="<td></td>";
          $totalRows.="<td></td>";
          $totalRows.="<td></td>";
          $totalRows.="<td class='centered'>".($totalQuantityAll>0?number_format(100*$totalQuantityA/$totalQuantityAll,2,".",","):"-")." %</td>";
          if($userRole!=ROLE_FOREMAN) {
            //$totalRows.="<td></td>";
          }
          $totalRows.="<td></td>";
          if($userRole!=ROLE_FOREMAN) {
            //$totalRows.="<td></td>";
          }
          $totalRows.="<td></td>";
        $totalRows.="</tr>";
        $tableBody="<tbody>".$totalRows.$tableRows.$totalRows."</tbody>";      
      
			return $tableHeader.$tableBody;
		}
	}
?>