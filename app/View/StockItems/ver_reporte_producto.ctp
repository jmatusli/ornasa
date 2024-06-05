<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
		
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
	});

</script>
<div class="stockItems view report">
<?php 
	echo '<h1>Reporte Detalle de Movimiento de Preforma</h1>';
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));      					
    echo $this->PlantFilter->displayPlantFilter($plants, $userRoleId,$plantId);              
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	
  if ($plantId == 0){
    echo '<h1>Se debe seleccionar una planta</h1>';
  }
  else {
    $productTable="";
    $productTableHeader="";
    $productTableBody="";
    
    $totalEntrada=0;
    $totalSalida=0;
    $totalSaldo=0;
    $totalProducts=[];
    
    //pr($productData);
    if ($productData['ProductType']['product_category_id']==CATEGORY_RAW){
      
      $productTableHeader.="<thead>";
        $productTableHeader.="<tr>";
          $productTableHeader.="<th>".__('Date')."</th>";
          $productTableHeader.="<th>".__('Invoice Code')."</th>";
          $productTableHeader.="<th>".__('Provider')."</th>";
          $productTableHeader.="<th class='centered'>".__('Number of Boxes')."</th>";
          $productTableHeader.="<th class='centered'>".__('Units')."</th>";
          $productTableHeader.="<th class='centered'>".__('Exit to Production')."</th>";
          $productTableHeader.="<th class='centered'>".__('Saldo')."</th>";
          $productTableHeader.="<th class='centered'>".__('Production Run Code')."</th>";
          $productTableHeader.="<th class='separator'></th>";
      
          // generate an array that determines whether the column will be shown or not based on initial and final inventory				
          $visibleArray=[];
          
          for ($i=0;$i<3*count($allFinishedProducts);$i++){
            $visibleArray[$i]=0;
          }
          
          foreach ($thisProductOrders as $productOrder){
            foreach ($productOrder['StockMovement'] as $purchaseMovement){
              foreach ($purchaseMovement[0] as $productionMovementAndRun){
                if ($productionMovementAndRun['ProductionMovement']['product_quantity']>0){
                  for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
                    $visibleArray[$f]+=$productionMovementAndRun['ProductionRun'][0][$f];
                  }
                }
              }
            }
          }
          //pr($finishedReclassified);
          if (!empty($finishedReclassified) && count($finishedReclassified) == count($visibleArray)){
            for ($r=0;$r<count($finishedReclassified);$r++){
              $visibleArray[$r]+=$finishedReclassified[$r];
            }
          }
          for ($i=0;$i<count($visibleArray);$i++){
            if ($i%3==2){
              if ($visibleArray[$i-2]>0 || $visibleArray[$i-1]>0 || $visibleArray[$i]>0){
                $visibleArray[$i-2]=false;
                $visibleArray[$i-1]=false;
                $visibleArray[$i]=false;
              }
              else {
                $visibleArray[$i-2]=true;
                $visibleArray[$i-1]=true;
                $visibleArray[$i]=true;
              }
            }
          }
          //pr($visibleArray);
          // then add the production outcomes with a foreach
          for ($i=0;$i<count($allFinishedProducts);$i++){
            $finishedProduct=$allFinishedProducts[$i];
            if (!$visibleArray[3*$i+2]){
              $productTableHeader.="<th  class='centered' colspan='3'>".$this->Html->link($finishedProduct['Product']['name'], ['controller' => 'products', 'action' => 'view', $finishedProduct['Product']['id']])."</th>";
              
              //$productTableHeader.="<th></th>";
              //$productTableHeader.="<th></th>";
              //$productTableHeader.="<th class='miniseparator'>&nbsp;</th>";
            }
          }
        $productTableHeader.="</tr>";
      $productTableHeader.="</thead>";
      
      $productTableBody.="<tbody>";
        $productTableBody.="<tr>";
        $productTableBody.="<td>Inventario Inicial</td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td class='centered number'>".$initialStock."</td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        
        $productTableBody.="<td class='separator'></td>";
        // then add the production outcomes with a foreach
        for ($i=0;$i<count($allFinishedProducts);$i++){
          $finishedProduct=$allFinishedProducts[$i];
          if (!$visibleArray[3*$i+2]){
            foreach ($productionResultCodes as $productionResultCodeId=>$productionResultCodeCode){
              $productTableBody.="<td class='centered'>".$productionResultCodeCode."</td>";
            }
          }
        }
      $productTableBody.="</tr>";
      
      
      $productionRunIds=[];
      
      foreach ($thisProductOrders as $productOrder){
        foreach ($productOrder['StockMovement'] as $purchaseMovement){
          if ($productOrder['Order']['order_date']>=$startDate && $productOrder['Order']['order_date']<$endDatePlusOne){
            $totalEntrada+=$purchaseMovement['product_quantity'];
            $totalSaldo+=$purchaseMovement['product_quantity']*$purchaseMovement['product_unit_price'];
            $orderDateTime=new DateTime($productOrder['Order']['order_date']);
                        
            // get the purchase specific data
            $productTableBody.="<tr>";
              $productTableBody.="<td>".$orderDateTime->format('d-m-Y')."</td>";
              $productTableBody.="<td>".$productOrder['Order']['order_code']."</td>";
              $productTableBody.="<td>".$productOrder['ThirdParty']['company_name']."</td>";
              if ($purchaseMovement['Product']['packaging_unit']!=0){
                $numboxes=floor($purchaseMovement['product_quantity']/$purchaseMovement['Product']['packaging_unit']);
              }
              else {
                $numboxes="-";
              }
              $productTableBody.="<td class='centered'>".$numboxes."</td>";
              $productTableBody.="<td class='centered number'>".$purchaseMovement['product_quantity']."</td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td class='centered currency'><span>".$purchaseMovement['product_quantity']*$purchaseMovement['product_unit_price']."</span></td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td class='separator'>&nbsp;</td>";
            $productTableBody.="</tr>";
          }
          
          // get the consumption data
          foreach ($purchaseMovement[0] as $productionMovementAndRun){
            //pr ($productionMovementAndRun);
            if ($productionMovementAndRun['ProductionMovement']['product_quantity']>0 && $productionMovementAndRun['ProductionRun']['production_run_date']>=$startDate && $productionMovementAndRun['ProductionRun']['production_run_date']<$endDatePlusOne){
              $totalSalida+=$productionMovementAndRun['ProductionMovement']['product_quantity'];
              $productTableBody.="<tr>";
              
              $productionrundate=new DateTime($productionMovementAndRun['ProductionRun']['production_run_date']);
              $productTableBody.="<td>".$productionrundate->format('d-m-Y')."</td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td></td>";
              $productTableBody.="<td class='centered number'>".$productionMovementAndRun['ProductionMovement']['product_quantity']."</td>";
              $productTableBody.="<td class='centered currency'><span>".$purchaseMovement['product_unit_price']*$productionMovementAndRun['ProductionMovement']['product_quantity']."</span></td>";
              $totalSaldo-=$purchaseMovement['product_unit_price']*$productionMovementAndRun['ProductionMovement']['product_quantity'];
              $productTableBody.="<td>".$productionMovementAndRun['ProductionRun']['production_run_code']."</td>";
              $productTableBody.="<td class='separator'>&nbsp;</td>";
              
              // check if there is a production run that has been divided but for which the other stockitem is not shown
              $warningsign=false;
              $totalpartials=0;
              for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
                $totalpartials+=$productionMovementAndRun['ProductionRun'][0][$f];
              }
              if ($totalpartials>$productionMovementAndRun['ProductionMovement']['product_quantity']){
                $warningsign=true;
              }
              
              for ($f=0;$f<sizeof($productionMovementAndRun['ProductionRun'][0]);$f++){
                //if (($f/3==0) && $f>0){
                  //$productTableBody.="<td class='miniseparator'>&nbsp;</td>";
                //}
                if (!$visibleArray[$f]){
                  $productTableBody.="<td".($warningsign?" class='warning centered number'":" class='centered number'").">".$productionMovementAndRun['ProductionRun'][0][$f]."</td>";
                }
              }
              $alreadyregistered=false;
              for ($i=0;$i<count($productionRunIds);$i++){
                if ($productionRunIds[$i]==$productionMovementAndRun['ProductionRun']['id']){
                  $alreadyregistered=true;
                }
              }
              if (!$alreadyregistered){
                foreach (array_keys($productionMovementAndRun['ProductionRun'][0] + $totalProducts) as $key) {
                  $totalProducts[$key] = (isset($productionMovementAndRun['ProductionRun'][0][$key]) ? $productionMovementAndRun['ProductionRun'][0][$key] : 0) + (isset($totalProducts[$key]) ? $totalProducts[$key] : 0);
                }
              }
              $productTableBody.="</tr>";
              $productionRunIds[]=$productionMovementAndRun['ProductionRun']['id'];
            }
          }
        }
      }
      
      $productTableBody.="<tr>";
        $productTableBody.="<td>Total Reclasificado</td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td class='centered number'>".$rawReclassified."</td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        $productTableBody.="<td></td>";
        
        $productTableBody.="<td class='separator'></td>";
      
        for ($r=0;$r<count($finishedReclassified);$r++){
          if (!$visibleArray[$r]){
            $productTableBody.="<td class='centered'>".$finishedReclassified[$r]."</td>";
          }
        }
      $productTableBody.="</tr>";
      $totalrow="<tr class='totalrow'>";
        $totalrow.="<td>Total</td>";
        $totalrow.="<td></td>";
        $totalrow.="<td></td>";
        $totalrow.="<td></td>";
        $totalrow.="<td class='centered number'>".($initialStock+$totalEntrada+$rawReclassified)."</td>";
        $totalrow.="<td class='centered number'>".$totalSalida."</td>";
        $totalrow.="<td class='centered currency'><span>".$totalSaldo."</span></td>";
        $totalrow.="<td></td>";
        $totalrow.="<td class='separator'>&nbsp;</td>";

        for ($f=0;$f<sizeof($totalProducts);$f++){
          if (!$visibleArray[$f]){
            $totalrow.="<td class='centered number'>".($totalProducts[$f]+$finishedReclassified[$f])."</td>";
          }
        }
      $totalrow.="</tr>";
      
      $productTableBody="<tbody>".$totalrow.$productTableBody.$totalrow."</tbody>";
      
      $productTable="<table id='preformas_".$productData['Product']['name']."'>";
      $productTable.=$productTableHeader;
      $productTable.=$productTableBody;
      $productTable.="</table>";
    }
    
    
    echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteProductoMateriaPrima'], ['class' => 'btn btn-primary']); 

    echo '<h2>Reporte para Producto '.$productData['Product']['name'].'</h2>';
    echo $productTable; 
    
    $_SESSION['productReport'] = $productTable;
  }  
?>
</div>