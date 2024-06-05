<script>
  $('body').on('change','#ReportWarehouseId',function(){
    displaySumSelector();
  });
  
  $('body').on('change','#ReportDisplayOptionId',function(){
    displayDetails();
  });
  
  function displayDetails(){
    if (parseInt($('#ReportDisplayOptionId').val()) === 1){
      $('.details').addClass('d-none');
      $('.customcolspan').each(function(){
        $(this).attr('colspan',3)
      });
    }
    else {
      $('.details').removeClass('d-none');
      $('.customcolspan').each(function(){
        $(this).attr('colspan',$(this).attr('detailcolspan'))
      });
    }
  }
  
  
  function displaySumSelector(){
    if ($('#ReportWarehouseId').val() == 0){
      $('#ReportWarehouseOptionId').closest('div').removeClass('d-none');
    }
    else {
      $('#ReportWarehouseOptionId').closest('div').addClass('d-none');
    }
  }

	function formatNumbers(){
		$("td.number span").each(function(){
      if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
    formatCSCurrencies();
		formatUSDCurrencies();
    
    displayDetails();
    displaySumSelector();
	});
</script>
<div class="stockItems view report">

<?php 
	echo "<h2>".__('Utilidades Anuales')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
    echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
    echo $this->Form->input('Report.warehouse_option_id',['label'=>'Mostrar sumas','default'=>$warehouseOptionId]);
    echo $this->Form->input('Report.display_option_id',['label'=>'Mostrar','default'=>$displayOptionId]);
    echo $this->Form->input('Report.data_option_id',['label'=>'Datos','default'=>$dataOptionId]);
    echo $this->Form->input('Report.product_nature_id',['label'=>'Naturaleza Producto','default'=>$productNatureId,'empty'=>[0=>'-- Selecciona Naturaleza Producto --'],'type'=>'hidden']);
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarUtilidadAnual'], ['class' => 'btn btn-primary']); 
	echo "<br/>";
	echo "<br/>";
  
  $utilityTables="";
  
  $utilityTable="";
    $utilitySummaryTableHeader="<thead>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th> </th>";
        foreach ($monthArray as $month){
          $utilitySummaryTableHeader.="<th class='centered hidden'>".$month['period']."</th>";
          $utilitySummaryTableHeader.="<th class='centered hidden'>".$month['period']."</th>";
          $utilitySummaryTableHeader.="<th class='centered'>".$month['period']."</th>";
          $utilitySummaryTableHeader.="<th class='centered hidden'>".$month['period']."</th>";
        }
        $utilitySummaryTableHeader.="<th> </th>";
      $utilitySummaryTableHeader.="</tr>";
      $utilitySummaryTableHeader.="<tr>";
        $utilitySummaryTableHeader.="<th>Tipo de Ventas</th>";
        foreach ($monthArray as $month){
          $utilitySummaryTableHeader.="<th class='centered hidden'>Precio</th>";
          $utilitySummaryTableHeader.="<th class='centered hidden'>Costo</th>";
          $utilitySummaryTableHeader.="<th class='centered'>Utilidad</th>";
          $utilitySummaryTableHeader.="<th class='centered hidden'>%</th>";
        }  
        $utilitySummaryTableHeader.="<th class='centered'>Utilidad Total</th>";
      $utilitySummaryTableHeader.="</tr>";
    $utilitySummaryTableHeader.="</thead>";
    
    $utilityTableRows="";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Contado</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['cash']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['cash']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['cash']-$month['totalCost']['cash'])."</span></td>";  
        if (!empty($month['totalPrice']['cash'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['cash']-$month['totalCost']['cash'])/$month['totalPrice']['cash'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }              
      }
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['cash'])?0:(100*($grandTotals['Price']['cash']-$grandTotals['Cost']['cash'])/$grandTotals['Price']['cash']))."</span></td>"; 
    $utilityTableRows.="</tr>";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Crédito</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['credit']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['credit']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['credit']-$month['totalCost']['credit'])."</span></td>";  
        if (!empty($month['totalPrice']['credit'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['credit']-$month['totalCost']['credit'])/$month['totalPrice']['credit'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }              
      }
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['credit'])?0:(100*($grandTotals['Price']['credit']-$grandTotals['Cost']['credit'])/$grandTotals['Price']['credit']))."</span></td>"; 
    $utilityTableRows.="</tr>";
    
    $utilityTableTotalRow="";
    $utilityTableTotalRow.="<tr class='totalrow'>";
      $utilityTableTotalRow.="<td>Todas Ventas</td>";  
      foreach ($monthArray as $month){
        $utilityTableTotalRow.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['all']."</span></td>";  
        $utilityTableTotalRow.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['all']."</span></td>";  
        $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['all']-$month['totalCost']['all'])."</span></td>"; 
        if (!empty($month['totalPrice']['all'])){
          $utilityTableTotalRow.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['all']-$month['totalCost']['all'])/$month['totalPrice']['all'])."</span></td>";
        }
        else {
          $utilityTableTotalRow.="<td class='centered percentage hidden'><span>0</span></td>";
        }
      }
      $utilityTableTotalRow.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['all'])?0:(100*($grandTotals['Price']['all']-$grandTotals['Cost']['all'])/$grandTotals['Price']['all']))."</span></td>"; 
    $utilityTableTotalRow.="</tr>";
    
    $utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
  $utilityTable.="<table>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
  
  echo "<h2>Utilidad de Ventas</h2>";
  echo $utilityTable;
  $utilityTables.=$utilityTable;
  
  $utilityTable="";
    
    $utilityTableRows="";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Botella</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['produced']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['produced']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['produced']-$month['totalCost']['produced'])."</span></td>";  
        if (!empty($month['totalPrice']['produced'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['produced']-$month['totalCost']['produced'])/$month['totalPrice']['produced'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }              
      }
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['produced'])?0:(100*($grandTotals['Price']['produced']-$grandTotals['Cost']['produced'])/$grandTotals['Price']['produced']))."</span></td>"; 
    $utilityTableRows.="</tr>";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Tapones</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['cap']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['cap']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['cap']-$month['totalCost']['cap'])."</span></td>";  
        if (!empty($month['totalPrice']['cap'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['cap']-$month['totalCost']['cap'])/$month['totalPrice']['cap'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }     
      } 
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['cap'])?0:(100*($grandTotals['Price']['cap']-$grandTotals['Cost']['cap'])/$grandTotals['Price']['cap']))."</span></td>";        
    $utilityTableRows.="</tr>";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Servicios</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['service']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['service']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['service']-$month['totalCost']['service'])."</span></td>";  
        if (!empty($month['totalPrice']['service'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['service']-$month['totalCost']['service'])/$month['totalPrice']['service'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }       
      }
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['service'])?0:(100*($grandTotals['Price']['service']-$grandTotals['Cost']['service'])/$grandTotals['Price']['service']))."</span></td>";     
    $utilityTableRows.="</tr>";
    $utilityTableRows.="<tr>";
      $utilityTableRows.="<td>Consumible</td>";  
      foreach ($monthArray as $month){
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['consumible']."</span></td>"; 
        $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['consumible']."</span></td>";  
        $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['consumible']-$month['totalCost']['consumible'])."</span></td>";  
        if (!empty($month['totalPrice']['consumible'])){
          $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['consumible']-$month['totalCost']['consumible'])/$month['totalPrice']['consumible'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }              
      }
      $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['consumible'])?0:(100*($grandTotals['Price']['consumible']-$grandTotals['Cost']['consumible'])/$grandTotals['Price']['consumible']))."</span></td>";
    $utilityTableRows.="</tr>";
    
    if (!empty($salesOtherProductTypes)){
      foreach ($salesOtherProductTypes as $productTypeId=>$productTypeName){
        $utilityTableRows.="<tr>";
          $utilityTableRows.="<td>".$productTypeName."</td>";  
          foreach ($monthArray as $month){
            $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".(array_key_exists($productTypeId,$month['totalPrice']['other'])?$month['totalPrice']['other'][$productTypeId]:0)."</span></td>"; 
            $utilityTableRows.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".(array_key_exists($productTypeId,$month['totalCost']['other'])?$month['totalCost']['other'][$productTypeId]:0)."</span></td>";  
            $utilityTableRows.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".(array_key_exists($productTypeId,$month['totalPrice']['other'])?($month['totalPrice']['other'][$productTypeId]-$month['totalCost']['other'][$productTypeId]):0)."</span></td>";  
            if (!empty($month['totalPrice']['other'][$productTypeId])){
              $utilityTableRows.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['other'][$productTypeId]-$month['totalCost']['other'][$productTypeId])/$month['totalPrice']['other'][$productTypeId])."</span></td>";
            }
            else {
              $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
            }              
          }
          $utilityTableRows.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['other'][$productTypeId])?0:(100*($grandTotals['Price']['other'][$productTypeId]-$grandTotals['Cost']['other'][$productTypeId])/$grandTotals['Price']['other'][$productTypeId]))."</span></td>";
        $utilityTableRows.="</tr>";
      }  
    }
    
    $utilityTableTotalRow="";
    $utilityTableTotalRow.="<tr class='totalrow'>";
      $utilityTableTotalRow.="<td>Todas Ventas</td>";  
      foreach ($monthArray as $month){
        $utilityTableTotalRow.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalPrice']['all']."</span></td>";  
        $utilityTableTotalRow.="<td class='centered CScurrency hidden'><span>C$</span><span class='amountright'>".$month['totalCost']['all']."</span></td>";  
        $utilityTableTotalRow.="<td class='centered CScurrency'><span>C$</span><span class='amountright'>".($month['totalPrice']['all']-$month['totalCost']['all'])."</span></td>"; 
        if (!empty($month['totalPrice']['all'])){
          $utilityTableTotalRow.="<td class='centered percentage hidden'><span>".(100*($month['totalPrice']['all']-$month['totalCost']['all'])/$month['totalPrice']['all'])."</span></td>";
        }
        else {
          $utilityTableRows.="<td class='centered percentage hidden'><span>0</span></td>";
        }
      }
      $utilityTableTotalRow.="<td class='centered percentage'><span>".(empty($grandTotals['Price']['all'])?0:(100*($grandTotals['Price']['all']-$grandTotals['Cost']['all'])/$grandTotals['Price']['all']))."</span></td>";
    $utilityTableTotalRow.="</tr>";
    
    $utilityTableBody="<tbody>".$utilityTableTotalRow.$utilityTableRows.$utilityTableTotalRow."</tbody>";
  $utilityTable.="<table>".$utilitySummaryTableHeader.$utilityTableBody."</table>";
  
  echo "<h2>Utilidad de Ventas por Tipo de Producto</h2>";
  echo $utilityTable;
  $utilityTables.=$utilityTable;
  
  
  
  
  
  
  
  /************************************* RAW MATERIAL UTILITY TABLES ********************************************/
  
  $rawMaterialUtilityTableHeader='';
  $rawMaterialUtilityTableHeader.='<thead>';
    $rawMaterialUtilityTableHeader.='<tr>';
      $rawMaterialUtilityTableHeader.='<th></th>';
      foreach($monthArray as $monthId=>$monthData){
        $rawMaterialUtilityTableHeader.='<th  class="centered details" colspan="'.$colspan.'">'.$monthData['period'].'</th>';
      }
      $rawMaterialUtilityTableHeader.='<th class="centered customcolspan"  detailcolspan="'.$totalColspan.'" colspan="'.$totalColspan.'">Total</th>';
      
    $rawMaterialUtilityTableHeader.='</tr>';
    
    $rawMaterialUtilityTableHeader.='<tr>';
      $rawMaterialUtilityTableHeader.='<th>Materia Prima</th>';
      foreach($monthArray as $monthId=>$monthData){        
        if ($boolShowQuantity){
          $rawMaterialUtilityTableHeader.='<th  class="centered details">Cantidad</th>';
        }
        if ($boolShowProfitCs){
          $rawMaterialUtilityTableHeader.='<th  class="centered details">Util C$</th>';
        }
        if ($boolShowProfitPercent){    
          $rawMaterialUtilityTableHeader.='<th  class="centered details">Util %</th>';
        }
      }
      if ($boolShowQuantity){
        $rawMaterialUtilityTableHeader.='<th  class="centered details">Cantidad</th>';
      }
      $rawMaterialUtilityTableHeader.='<th class="centered">Cant Promedio</th>';
      if ($boolShowProfitCs){
        $rawMaterialUtilityTableHeader.='<th  class="centered details">Util C$</th>';
      }
      $rawMaterialUtilityTableHeader.='<th class="centered">Util/Mes</th>';
      //if ($boolShowProfitPercent){    
      //  $rawMaterialUtilityTableHeader.='<th  class="centered details">Util %</th>';
      //}
      $rawMaterialUtilityTableHeader.='<th  class="centered">Util %</th>';  
      

    $rawMaterialUtilityTableHeader.='</tr>';
  $rawMaterialUtilityTableHeader.='</thead>';
  
  foreach ($warehouseArray as $currentWarehouseId=>$warehouseData){
    $rawMaterialUtilityTableRows='';     
    //pr($warehouseData['RawUtility']);  
    if (array_key_exists('RawUtility',$warehouseData) &&array_key_exists('RawMaterial',$warehouseData['RawUtility'])){
      foreach ($warehouseData['RawUtility']['RawMaterial'] as $currentRawMaterialId=>$rawMaterialData){
        //pr($rawMaterialData);
        $rawMaterialUtilityTableRow='';
        $rawMaterialUtilityTableRow.='<tr>';
          $rawMaterialUtilityTableRow.='<td>'.$this->Html->Link($rawMaterialData['Product']['name'],['controller'=>'products','action'=>'view',$currentRawMaterialId]).'</td>';
          $productTotalQuantity=0;
          $productTotalPriceCs=0;
          $productTotalProfitCs=0;
          foreach ($monthArray as $monthId=>$monthData){
            
            if (array_key_exists($monthId,$rawMaterialData['Month'])){
              $rawMaterialMonthData=$rawMaterialData['Month'][$monthId];
              //pr($rawMaterialMonthData);
              $productTotalQuantity+=$rawMaterialMonthData['quantity'];
              $productTotalProfitCs+=$rawMaterialMonthData['gain'];
              $productTotalPriceCs+=$rawMaterialMonthData['price'];
              if ($boolShowQuantity){
                $rawMaterialUtilityTableRow.='<td class="number details"><span class="amountright">'.$rawMaterialMonthData['quantity'].'</span></td>';
                //$productTotalQuantity+=$rawMaterialMonthData['quantity'];
              }
              //if ($boolShowProfitCs || $boolShowProfitPercent){
              //  $productTotalProfitCs+=$rawMaterialMonthData['gain'];
              //}  
              if ($boolShowProfitCs){
                $rawMaterialUtilityTableRow.='<td class="CScurrency details"><span class="amountright">'.$rawMaterialMonthData['gain'].'</span></td>';
              }
              if ($boolShowProfitPercent){
                $rawMaterialUtilityTableRow.='<td class="centered percentage details"><span>'.($rawMaterialMonthData['price'] == 0 ?'-':round(100*$rawMaterialMonthData['gain']/$rawMaterialMonthData['price'],2)).'</span></td>';
                //$productTotalPriceCs+=$rawMaterialMonthData['price'];
              }
            }
            else {
              if ($boolShowQuantity){
                $rawMaterialUtilityTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitCs){
                $rawMaterialUtilityTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitPercent){
                $rawMaterialUtilityTableRow.='<td class="details">-</td>';
              }
            }
          }    
          if ($boolShowQuantity){
            $rawMaterialUtilityTableRow.='<td class="totalCell details"><span class="amountright">'.$productTotalQuantity.'</span></td>';
          }
          $rawMaterialUtilityTableRow.='<td class="totalCell number"><span class="amountright">'.round($productTotalQuantity/count($monthArray),2).'</span></td>';
          if ($boolShowProfitCs){
            $rawMaterialUtilityTableRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$productTotalProfitCs.'</span></td>';
          }
          $rawMaterialUtilityTableRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($productTotalProfitCs/count($monthArray),2).'</span></td>';
          //if ($boolShowProfitPercent){
          //  $rawMaterialUtilityTableRow.='<td class="totalCell CScurrency"><span class="amountright">'.($productTotalPriceCs == 0 ?'-':round($productTotalProfitCs/$productTotalPriceCs,2)).'</span></td>';
          //}
          $rawMaterialUtilityTableRow.='<td class="totalCell centered percentage"><span>'.($productTotalPriceCs == 0 ?'-':round(100*$productTotalProfitCs/$productTotalPriceCs,2)).'</span></td>';
          
        $rawMaterialUtilityTableRow.='</tr>';
        if ($productTotalQuantity > 0 || $productTotalProfitCs > 0){   
          $rawMaterialUtilityTableRows.=$rawMaterialUtilityTableRow;        
        }     
      }
    }  
    
    $rawMaterialUtilityTableTotalRow='';
    if (!empty($rawMaterialUtilityTableRows)){
      $rawMaterialUtilityTableTotalRow.='<tr class="totalrow">';
        $rawMaterialUtilityTableTotalRow.='<td>Total C$</td>';
        //pr($warehouseData['RawMonthUtility']);
        //if ($currentWarehouseId == 3){
        //  pr($warehouseData['RawMonthUtility']);
        //}
        $totalQuantity=0;
        $totalPriceCs=0;
        $totalProfitCs=0;
        
        foreach ($warehouseData['RawMonthUtility'] as $monthId=>$monthData){
          //pr($monthData);
          
          $totalQuantity+=$monthData['quantity'];
          $totalProfitCs+=$monthData['gain'];
          $totalPriceCs+=$monthData['price'];
          
          if ($boolShowQuantity){
            $rawMaterialUtilityTableTotalRow.='<td class="number details"><span class="amountright">'.$monthData['quantity'].'</span></td>';
            //$totalQuantity+=$monthData['quantity'];
          }
          //if ($boolShowProfitCs || $boolShowProfitPercent){
          //  $totalProfitCs+=$monthData['gain'];
          //} 
          if ($boolShowProfitCs){
            $rawMaterialUtilityTableTotalRow.='<td class="CScurrency details"><span class="amountright">'.$monthData['gain'].'</span></td>';
          }
          if ($boolShowProfitPercent){
            $rawMaterialUtilityTableTotalRow.='<td class="centered percentage details"><span>'.($monthData['price'] == 0 ?'-':round(100*$monthData['gain']/$monthData['price'],2)).'</span></td>';
            //$totalPriceCs+=$monthData['price'];
          }
        }
        //echo 'total price is '.$totalPriceCs;
        //echo 'total profit is '.$totalProfitCs;
        if ($boolShowQuantity){
          $rawMaterialUtilityTableTotalRow.='<td class="totalCell number details"><span class="amountright">'.$totalQuantity.'</span></td>';
        }
        $rawMaterialUtilityTableTotalRow.='<td class="totalCell number"><span class="amountright">'.round($totalQuantity/count($monthArray),2).'</span></td>';
        if ($boolShowProfitCs){
          $rawMaterialUtilityTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$totalProfitCs.'</span></td>';
        }
        $rawMaterialUtilityTableTotalRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($totalProfitCs/count($monthArray),2).'</span></td>';
        //if ($boolShowProfitPercent){
        //  $rawMaterialUtilityTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.($totalPriceCs == 0 ?'-':round($totalProfitCs/$totalPriceCs,2)).'</span></td>';
        //}
        $rawMaterialUtilityTableTotalRow.='<td class="totalCell centered percentage"><span>'.($totalPriceCs == 0 ?'-':round(100*$totalProfitCs/$totalPriceCs,2)).'</span></td>';
        
      $rawMaterialUtilityTableTotalRow.='</tr>';
    } 
    //pr($warehouseData);
    echo '<h2>Utilidad para Preformas Utilizados (todas calidades) para '.$warehouseData['Warehouse']['name'].'</h2>';
    if (empty($rawMaterialUtilityTableRows)){
      echo '<h3>No había ventas para materia prima en esta bodega</h3>';
    }
    else {
      $rawMaterialUtilityTableBody='<tbody>'.$rawMaterialUtilityTableTotalRow.$rawMaterialUtilityTableRows.$rawMaterialUtilityTableTotalRow.'</tbody>';
    
      $rawMaterialUtilityTable='<table id="materiaprima_'.$shortWarehouses[$currentWarehouseId].'">'.$rawMaterialUtilityTableHeader.$rawMaterialUtilityTableBody.'</table>';
      
      echo $rawMaterialUtilityTable;
      
      $utilityTables.=$rawMaterialUtilityTable;
    }
  }
 
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  /************************************* PRODUCT UTILITY TABLES ********************************************/
  
  $productUtilityTableHeader='';
  $productUtilityTableHeader.='<thead>';
    $productUtilityTableHeader.='<tr>';
      $productUtilityTableHeader.='<th></th>';
      foreach($monthArray as $monthId=>$monthData){
        $productUtilityTableHeader.='<th  class="centered details" colspan="'.$colspan.'">'.$monthData['period'].'</th>';
      }
      $productUtilityTableHeader.='<th class="centered customcolspan"  detailcolspan="'.$totalColspan.'" colspan="'.$totalColspan.'">Total</th>';
      
    $productUtilityTableHeader.='</tr>';
    
    $productUtilityTableHeader.='<tr>';
      $productUtilityTableHeader.='<th>Producto</th>';
      foreach($monthArray as $monthId=>$monthData){        
        if ($boolShowQuantity){
          $productUtilityTableHeader.='<th  class="centered details">Cantidad</th>';
        }
        if ($boolShowProfitCs){
          $productUtilityTableHeader.='<th  class="centered details">Util C$</th>';
        }
        if ($boolShowProfitPercent){    
          $productUtilityTableHeader.='<th  class="centered details">Util %</th>';
        }
      }
      if ($boolShowQuantity){
        $productUtilityTableHeader.='<th  class="centered details">Cantidad</th>';
      }
      $productUtilityTableHeader.='<th class="centered">Cant Promedio</th>';
      if ($boolShowProfitCs){
        $productUtilityTableHeader.='<th  class="centered details">Util C$</th>';
      }
      $productUtilityTableHeader.='<th class="centered">Util/Mes</th>';
      //if ($boolShowProfitPercent){    
      //  $productUtilityTableHeader.='<th  class="centered details">Util %</th>';
      //}
      $productUtilityTableHeader.='<th  class="centered">Util %</th>';  
      

    $productUtilityTableHeader.='</tr>';
  $productUtilityTableHeader.='</thead>';
  
  foreach ($warehouseArray as $currentWarehouseId=>$warehouseData){
    //pr($warehouseData);
    //if ($currentWarehouseId == 3){
    //  pr($warehouseData);
    //}
    foreach ($warehouseData['ProductUtility']['ProductNature'] as $currentProductNatureId=>$productNatureData){
      //pr($productNatureData);
      $productUtilityTableRows='';      
      //if (!array_key_exists('Product',$productNatureData)){
      //  pr($productNatureData);
      //}
      foreach ($productNatureData['Product'] as $productId=>$productData){
        //pr($productData);
        $productUtilityTableRow='';
        $productUtilityTableRow.='<tr>';
          $productUtilityTableRow.='<td>'.$this->Html->Link($productData['Product']['name'],['controller'=>'products','action'=>'view',$productId]).'</td>';
          $productTotalQuantity=0;
          $productTotalPriceCs=0;
          $productTotalProfitCs=0;
          foreach ($monthArray as $monthId=>$monthData){
            
            if (array_key_exists($monthId,$productData['Month'])){
              $productMonthData=$productData['Month'][$monthId];
              //pr($productMonthData);
              $productTotalQuantity+=$productMonthData['quantity'];
              $productTotalProfitCs+=$productMonthData['gain'];
              $productTotalPriceCs+=$productMonthData['price'];
              if ($boolShowQuantity){
                $productUtilityTableRow.='<td class="number details"><span class="amountright">'.$productMonthData['quantity'].'</span></td>';
                //$productTotalQuantity+=$productMonthData['quantity'];
              }
              //if ($boolShowProfitCs || $boolShowProfitPercent){
              //  $productTotalProfitCs+=$productMonthData['gain'];
              //}  
              if ($boolShowProfitCs){
                $productUtilityTableRow.='<td class="CScurrency details"><span class="amountright">'.$productMonthData['gain'].'</span></td>';
              }
              if ($boolShowProfitPercent){
                $productUtilityTableRow.='<td class="centered percentage details"><span>'.($productMonthData['price'] == 0 ?'-':round(100*$productMonthData['gain']/$productMonthData['price'],2)).'</span></td>';
                //$productTotalPriceCs+=$productMonthData['price'];
              }
            }
            else {
              if ($boolShowQuantity){
                $productUtilityTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitCs){
                $productUtilityTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitPercent){
                $productUtilityTableRow.='<td class="details">-</td>';
              }
            }
          }    
          if ($boolShowQuantity){
            $productUtilityTableRow.='<td class="totalCell number details"><span class="amountright details">'.$productTotalQuantity.'</span></td>';
          }
          $productUtilityTableRow.='<td class="totalCell number"><span class="amountright">'.round($productTotalQuantity/count($monthArray),2).'</span></td>';
          if ($boolShowProfitCs){
            $productUtilityTableRow.='<td class="totalCell CScurrency details"><span class="amountright details">'.$productTotalProfitCs.'</span></td>';
          }
          $productUtilityTableRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($productTotalProfitCs/count($monthArray),2).'</span></td>';
          //if ($boolShowProfitPercent){
          //  $productUtilityTableRow.='<td class="totalCell CScurrency details"><span class="amountright details">'.($productTotalPriceCs == 0 ?'-':round($productTotalProfitCs/$productTotalPriceCs,2)).'</span></td>';
          //}
          $productUtilityTableRow.='<td class="totalCell centered percentage"><span>'.(100*$productTotalPriceCs == 0 ?'-':round(100*$productTotalProfitCs/$productTotalPriceCs,2)).'</span></td>';
          
        $productUtilityTableRow.='</tr>';
        if ($productTotalQuantity > 0 || $productTotalProfitCs > 0){   
          $productUtilityTableRows.=$productUtilityTableRow;        
        }
      }
      
      $productUtilityTableTotalRow='';
      if (!empty($productUtilityTableRows)){
        $productUtilityTableTotalRow.='<tr class="totalrow">';
          $productUtilityTableTotalRow.='<td>Total C$</td>';
          //pr($warehouseData['MonthUtility']);
          //if ($currentWarehouseId == 3){
          //  pr($warehouseData['MonthUtility']);
          //}
          $totalQuantity=0;
          $totalPriceCs=0;
          $totalProfitCs=0;
          
          foreach ($warehouseData['MonthUtility'] as $monthId=>$monthData){
            //pr($monthData);
            
            $monthProductNatureData=$monthData['ProductNature'][$currentProductNatureId];
            
            $totalQuantity+=$monthProductNatureData['quantity'];
            $totalProfitCs+=$monthProductNatureData['gain'];
            $totalPriceCs+=$monthProductNatureData['price'];
            
            if ($boolShowQuantity){
              $productUtilityTableTotalRow.='<td class="number details"><span class="amountright">'.$monthProductNatureData['quantity'].'</span></td>';
              //$totalQuantity+=$monthProductNatureData['quantity'];
            }
            //if ($boolShowProfitCs || $boolShowProfitPercent){
            //  $totalProfitCs+=$monthProductNatureData['gain'];
            //} 
            if ($boolShowProfitCs){
              $productUtilityTableTotalRow.='<td class="CScurrency details"><span class="amountright">'.$monthProductNatureData['gain'].'</span></td>';
            }
            if ($boolShowProfitPercent){
              $productUtilityTableTotalRow.='<td class="centered percentage details"><span>'.($monthProductNatureData['price'] == 0 ?'-':round(100*$monthProductNatureData['gain']/$monthProductNatureData['price'],2)).'</span></td>';
              //$totalPriceCs+=$monthProductNatureData['price'];
            }
          }
          if ($boolShowQuantity){
            $productUtilityTableTotalRow.='<td class="totalCell number details"><span class="amountright">'.$totalQuantity.'</span></td>';
          }
          $productUtilityTableTotalRow.='<td class="totalCell number"><span class="amountright">'.round($totalQuantity/count($monthArray),2).'</span></td>';
          if ($boolShowProfitCs){
            $productUtilityTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$totalProfitCs.'</span></td>';
          }
          $productUtilityTableTotalRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($totalProfitCs/count($monthArray),2).'</span></td>';
          //if ($boolShowProfitPercent){
          //  $productUtilityTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.($totalPriceCs == 0 ?'-':round($totalProfitCs/$totalPriceCs,2)).'</span></td>';
          //}
          $productUtilityTableTotalRow.='<td class="totalCell centered percentage"><span>'.($totalPriceCs == 0 ?'-':round(100*$totalProfitCs/$totalPriceCs,2)).'</span></td>';
          
        $productUtilityTableTotalRow.='</tr>';
      }
      
      echo '<h2>Utilidad para productos de Naturaleza (calidad A) '.$productNatures[$currentProductNatureId].' para '.$warehouseData['Warehouse']['name'].'</h2>';
      if (empty($productUtilityTableRows)){
        echo '<h3>No había ventas para productos de esta naturaleza en esta bodega</h3>';
      }
      else {
        $productUtilityTableBody='<tbody>'.$productUtilityTableTotalRow.$productUtilityTableRows.$productUtilityTableTotalRow.'</tbody>';
      
        $productUtilityTable='<table id="productos_'.$shortWarehouses[$currentWarehouseId].'_'.substr($productNatures[$currentProductNatureId],0,10).'">'.$productUtilityTableHeader.$productUtilityTableBody.'</table>';
        
        echo $productUtilityTable;
        
        $utilityTables.=$productUtilityTable;
      }
      
    }  
  }
 
  
  
  
  
  
  /************************************* CLIENT TABLE ********************************************/
  
  $clientTableHeader='';
  $clientTableHeader.='<thead>';
    $clientTableHeader.='<tr>';
      $clientTableHeader.='<th></th>';
      foreach($monthArray as $monthId=>$monthData){
        $clientTableHeader.='<th  class="centered details" colspan="'.($colspan*(count($productNatures)+($productNatureId==0?1:0))).'">'.$monthData['period'].'</th>';
      }
      $clientTableHeader.='<th class="centered customcolspan" detailcolspan="'.($productNatureId==0?($colspan*(count($productNatures)+$totalColspan)):($colspan*count($productNatures))).'" colspan="'.($productNatureId==0?($colspan*(count($productNatures)+$totalColspan)):($colspan*count($productNatures))).'">Total</th>';
    $clientTableHeader.='</tr>';
    $clientTableHeader.='<tr>';
      $clientTableHeader.='<th></th>';
      foreach($monthArray as $monthId=>$monthData){
        foreach ($productNatures as $currentProductNatureId=>$productNatureName){
          if($productNatureId == 0 || $productNatureId == $currentProductNatureId){
            $clientTableHeader.='<th * class="centered details" colspan="'.$colspan.'">'.$productNatureName.'</th>';
          }
        }
        if($productNatureId == 0){
          $clientTableHeader.='<th class="centered details" colspan="'.$colspan.'">Total</th>';
        }
      }
      foreach ($productNatures as $currentProductNatureId=>$productNatureName){
        $clientTableHeader.='<th class="centered details" colspan="'.$colspan.'">'.$productNatureName.'</th>';
      }
      if($productNatureId == 0){
        $clientTableHeader.='<th  class="centered customcolspan" detailcolspan="'.$totalColspan.'" colspan="'.$totalColspan.'">Total</th>';
      }
    $clientTableHeader.='</tr>';
    $clientTableHeader.='<tr>';
      $clientTableHeader.='<th>Cliente</th>';
      foreach($monthArray as $monthId=>$monthData){
        foreach ($productNatures as $currentProductNatureId=>$productNatureName){
          if($productNatureId == 0 || $productNatureId == $currentProductNatureId){
            if ($boolShowQuantity){
              $clientTableHeader.='<th  class="centered details">Cantidad</th>';
            }
            if ($boolShowProfitCs){
              $clientTableHeader.='<th  class="centered details">Util C$</th>';
            }
            if ($boolShowProfitPercent){    
              $clientTableHeader.='<th  class="centered details">Util %</th>';
            }  
          }
        }
        if ($productNatureId == 0){
          if ($boolShowQuantity){
            $clientTableHeader.='<th  class="centered details">Cantidad</th>';
          }
          if ($boolShowProfitCs){
            $clientTableHeader.='<th  class="centered details">Util C$</th>';
          }
          if ($boolShowProfitPercent){  
            $clientTableHeader.='<th  class="centered details">Util %</th>';
          }
        }
      }
      foreach ($productNatures as $currentProductNatureId=>$productNatureName){
        if($productNatureId == 0 || $productNatureId == $currentProductNatureId){ 
          if ($boolShowQuantity){
            $clientTableHeader.='<th  class="centered details">Cantidad</th>';
          }
          if ($boolShowProfitCs){
            $clientTableHeader.='<th  class="centered details">Util C$</th>';
          }
          if ($boolShowProfitPercent){    
            $clientTableHeader.='<th  class="centered details">Util %</th>';
          }
        }
      }
      if ($productNatureId == 0){
        if ($boolShowQuantity){
          $clientTableHeader.='<th class="centered details">Cantidad</th>';
        }
        $clientTableHeader.='<th class="centered">Cant Promedio</th>';
        if ($boolShowProfitCs){
          $clientTableHeader.='<th class="centered details">Util C$</th>';
        }
        $clientTableHeader.='<th class="centered">Util/Mes</th>';
        //if ($boolShowProfitPercent){  
        //  $clientTableHeader.='<th  class="centered details">Util %</th>';
        //}
        $clientTableHeader.='<th  class="centered">Util %</th>';
      }
    $clientTableHeader.='</tr>';
  $clientTableHeader.='</thead>';
  
  foreach ($warehouseArray as $currentWarehouseId=>$warehouseData){
    //if ($currentWarehouseId == 0){
      //pr($warehouseData['ClientUtility']);
    //}
    $clientTableRows='';
    //pr($warehouseData);
    foreach ($warehouseData['ClientUtility'] as $clientId=>$clientData){
      //pr($clientData);
      if (
        ($warehouseId > 0 && array_key_exists('Month',$clientData)) 
        ||
        ($warehouseId == 0 && !empty($clientData['Month'])) 
      ){
      
        $clientTableRow='';
        $clientTableRow.='<tr>';
          $clientTableRow.='<td>'.$this->Html->Link($clientData['Client']['name'],['controller'=>'thirdParties','action'=>'verCliente',$clientId]).'</td>';
        foreach ($monthArray as $monthId=>$monthData){
          if (array_key_exists($monthId,$clientData['Month'])){
            $clientMonthTotalQuantity=0;
            $clientMonthTotalPriceCs=0;
            $clientMonthTotalProfitCs=0;
            foreach ($clientData['Month'][$monthId]['ProductNature'] as $currentProductNatureId=>$productNatureData){
              $clientMonthTotalQuantity+=$productNatureData['quantity'];
              $clientMonthTotalProfitCs+=$productNatureData['gain'];
              $clientMonthTotalPriceCs+=$productNatureData['price'];
            
              if ($boolShowQuantity){
                $clientTableRow.='<td class="number details"><span class="amountright">'.$productNatureData['quantity'].'</span></td>';
                //$clientMonthTotalQuantity+=$productNatureData['quantity'];
              }
              //if ($boolShowProfitCs || $boolShowProfitPercent){
              //  $clientMonthTotalProfitCs+=$productNatureData['gain'];
              //}  
              if ($boolShowProfitCs){
                $clientTableRow.='<td class="CScurrency details"><span class="amountright">'.$productNatureData['gain'].'</span></td>';
              }
              if ($boolShowProfitPercent){
                $clientTableRow.='<td class="centered percentage details"><span>'.($productNatureData['price'] == 0 ?'-':round(100*$productNatureData['gain']/$productNatureData['price'],2)).'</span></td>';
                //$clientMonthTotalPriceCs+=$productNatureData['price'];
              }
            }
            if ($productNatureId == 0){
              if ($boolShowQuantity){
                $clientTableRow.='<td class="totalCell number details"><span class="amountright">'.$clientMonthTotalQuantity.'</span></td>';
              }
              if ($boolShowProfitCs){
                $clientTableRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$clientMonthTotalProfitCs.'</span></td>';
              }
              if ($boolShowProfitPercent){
                $clientTableRow.='<td class="totalCell centered percentage details"><span>'.($clientMonthTotalPriceCs == 0 ?'-':round(100*$clientMonthTotalProfitCs/$clientMonthTotalPriceCs,2)).'</span></td>';
              }
            }              
          }
          else {
            foreach ($productNatures as $currentProductNatureId=>$productNatureData){
              if ($boolShowQuantity){
                $clientTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitCs){
                $clientTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitPercent){
                $clientTableRow.='<td class="details">-</td>';
              }
            }
            if ($productNatureId == 0){
              if ($boolShowQuantity){
                $clientTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitCs){
                $clientTableRow.='<td class="details">-</td>';
              }
              if ($boolShowProfitPercent){
                $clientTableRow.='<td class="details">-</td>';
              }
            }
          }  
        }
        //pr($clientData['Total']);
        $clientTotalQuantity=0;
        $clientTotalPriceCs=0;
        $clientTotalProfitCs=0;
        foreach ($clientData['Total'] as $currentProductNatureId=>$productNatureData){
          $clientTotalQuantity+=$productNatureData['quantity'];
          $clientTotalProfitCs+=$productNatureData['gain'];
          $clientTotalPriceCs+=$productNatureData['price'];
          
          if ($boolShowQuantity){
            $clientTableRow.='<td class="number details"><span class="amountright">'.$productNatureData['quantity'].'</span></td>';
            //$clientTotalQuantity+=$productNatureData['quantity'];
          }
          //if ($boolShowProfitCs || $boolShowProfitPercent){
          //  $clientTotalProfitCs+=$productNatureData['gain'];
          //} 
          if ($boolShowProfitCs){
            $clientTableRow.='<td class="CScurrency details"><span class="amountright">'.$productNatureData['gain'].'</span></td>';
          }
          if ($boolShowProfitPercent){
            $clientTableRow.='<td class="centered percentage details"><span>'.($productNatureData['price'] == 0 ?'-':round(100*$productNatureData['gain']/$productNatureData['price'],2)).'</span></td>';
            //$clientTotalPriceCs+=$productNatureData['price'];
          }
        }  
        if ($productNatureId == 0){
          //echo 'showing total data all months';
          if ($boolShowQuantity){
            $clientTableRow.='<td class="totalCell number details"><class="amountright">'.$clientTotalQuantity.'</span></td>';
          }
          $clientTableRow.='<td class="totalCell number"><class="amountright">'.round($clientTotalQuantity/count($monthArray),2).'</span></td>';
          if ($boolShowProfitCs){
            $clientTableRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$clientTotalProfitCs.'</span></td>';
          }
          $clientTableRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($clientTotalProfitCs/count($monthArray),2).'</span></td>';
          //if ($boolShowProfitPercent){
          //  $clientTableRow.='<td class="totalCell centered percentage details"><span>'.($clientTotalPriceCs == 0 ?'-':round(100*$clientTotalProfitCs/$clientTotalPriceCs,2)).'</span></td>';
          //}
          $clientTableRow.='<td class="totalCell centered percentage"><span>'.($clientTotalPriceCs == 0 ?'-':round(100*$clientTotalProfitCs/$clientTotalPriceCs,2)).'</span></td>';
        }      
        $clientTableRow.='</tr>';
        $clientTableRows.=$clientTableRow;
      }
    }
    
    $clientTableTotalRow='';
    
    $clientTableTotalRow.='<tr class="totalrow">';
      $clientTableTotalRow.='<td>Total C$</td>';
      //pr($warehouseData['MonthUtility']);
      foreach ($warehouseData['MonthUtility'] as $monthId=>$monthData){
        //pr($monthData);
        
        $monthTotalQuantity=0;
        $monthTotalPriceCs=0;
        $monthTotalProfitCs=0;
        foreach ($monthData['ProductNature'] as $currentProductNatureId=>$productNatureData){
          $monthTotalQuantity+=$productNatureData['quantity'];
          $monthTotalProfitCs+=$productNatureData['gain'];
          $monthTotalPriceCs+=$productNatureData['price'];
        
          if ($boolShowQuantity){
            $clientTableTotalRow.='<td class="number details"><span class="amountright">'.$productNatureData['quantity'].'</span></td>';
            //$monthTotalQuantity+=$productNatureData['quantity'];
          }
          //if ($boolShowProfitCs || $boolShowProfitPercent){
          //  $monthTotalProfitCs+=$productNatureData['gain'];
          //} 
          if ($boolShowProfitCs){
            $clientTableTotalRow.='<td class="CScurrency details"><span class="amountright">'.$productNatureData['gain'].'</span></td>';
          }
          if ($boolShowProfitPercent){
            $clientTableTotalRow.='<td class="centered percentage details"><span>'.($productNatureData['price'] == 0 ?'-':round(100*$productNatureData['gain']/$productNatureData['price'],2)).'</span></td>';
            //$monthTotalPriceCs+=$productNatureData['price'];
          }
        }
        if ($productNatureId == 0){
          if ($boolShowQuantity){
            $clientTableTotalRow.='<td class="totalCell number details"><span class="amountright">'.$monthTotalQuantity.'</span></td>';
          }
          //$clientTableTotalRow.='<td class="totalCell number"><span class="amountright">'.round($monthTotalQuantity/count($monthArray),2).'</span></td>';
          if ($boolShowProfitCs){
            $clientTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$monthTotalProfitCs.'</span></td>';
          }
          //$clientTableTotalRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($monthTotalProfitCs/count($monthArray),2).'</span></td>';
          if ($boolShowProfitPercent){
            $clientTableTotalRow.='<td class="totalCell centered percentage details"><span>'.($monthTotalPriceCs == 0 ?'-':round(100*$monthTotalProfitCs/$monthTotalPriceCs,2)).'</span></td>';
          }
          //$clientTableTotalRow.='<td class="totalCell centered percentage"><span>'.($monthTotalPriceCs == 0 ?'-':round(100*$monthTotalProfitCs/$monthTotalPriceCs,2)).'</span></td>';
        }
      }
      //if ($currentWarehouseId == 0){
      //  pr($warehouseData['Total']);
      //}  
      $totalQuantity=0;
      $totalPriceCs=0;
      $totalProfitCs=0;
      foreach ($warehouseData['Total'] as $currentProductNatureId=>$productNatureData){
        $totalQuantity+=$productNatureData['quantity'];
        $totalProfitCs+=$productNatureData['gain'];
        $totalPriceCs+=$productNatureData['price'];
      
        if ($boolShowQuantity){
          $clientTableTotalRow.='<td class="number details"><span class="amountright">'.$productNatureData['quantity'].'</span></td>';
          //$totalQuantity+=$productNatureData['quantity'];
        }
        //if ($boolShowProfitCs || $boolShowProfitPercent){
        //  $totalProfitCs+=$productNatureData['gain'];
        //} 
        if ($boolShowProfitCs){
          $clientTableTotalRow.='<td class="CScurrency details"><span class="amountright">'.$productNatureData['gain'].'</span></td>';
        }
        if ($boolShowProfitPercent){
          $clientTableTotalRow.='<td class="centered percentage details"><span>'.($productNatureData['price'] == 0 ?'-':round(100*$productNatureData['gain']/$productNatureData['price'],2)).'</span></td>';
          //$totalPriceCs+=$productNatureData['price'];
        }
      }
      if ($productNatureId == 0){
        if ($boolShowQuantity){
          $clientTableTotalRow.='<td class="totalCell number details"><span class="amountright">'.$totalQuantity.'</span></td>';
        }
        $clientTableTotalRow.='<td class="totalCell number"><span class="amountright">'.round($totalQuantity/count($monthArray),2).'</span></td>';
        if ($boolShowProfitCs){
          $clientTableTotalRow.='<td class="totalCell CScurrency details"><span class="amountright">'.$totalProfitCs.'</span></td>';
        }
        $clientTableTotalRow.='<td class="totalCell CScurrency"><span class="amountright">'.round($totalProfitCs/count($monthArray),2).'</span></td>';
        //if ($boolShowProfitPercent){
        //  $clientTableTotalRow.='<td class="totalCell centered percentage details"><span>'.($totalPriceCs == 0 ?'-':round(100*$totalProfitCs/$totalPriceCs,2)).'</span></td>';
        //}
        $clientTableTotalRow.='<td class="totalCell centered percentage"><span>'.($totalPriceCs == 0 ?'-':round(100*$totalProfitCs/$totalPriceCs,2)).'</span></td>';
      }
      
    $clientTableTotalRow.='</tr>';
    
    $clientTableBody='<tbody>'.$clientTableTotalRow.$clientTableRows.$clientTableTotalRow.'</tbody>';
    
    $clientTable='<table id="clientes_'.($currentWarehouseId>0?$shortWarehouses[$currentWarehouseId]:'Todas_Bodegas').'">'.$clientTableHeader.$clientTableBody.'</table>';
    echo '<h2>Utilidad por cliente para '.$warehouseData['Warehouse']['name'].'</h2>';
    echo $clientTable;
    
    $utilityTables.=$clientTable;
  }
  
	$_SESSION['utilidadAnual'] = $utilityTables;
?>
</div>