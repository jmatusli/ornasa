<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	$(document).ready(function(){
		formatNumbers();
	});
</script>
<div class="stockMovements view kardex fullwidth">
<?php 
  echo '<div>';
    echo '<h1>Kardex '.$product['Product']['name'].($rawMaterialId > 0?(" ".$rawMaterial['Product']['name']):"").'</h1>'; 
    echo '<h2>Bodega '.$warehouses[$warehouseId].'</h2>'; 
    //echo 'warehouse id is '.$warehouseId.'<br/>';
    //echo 'userRoleId is '.$userRoleId.'<br/>';
    echo $this->Form->create('Report'); 
      echo '<fieldset>';
        echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
        echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
        echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
      echo '</fieldset>';
      echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
      echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
    echo $this->Form->end(__('Refresh')); 
  echo '</div>';
  echo '<div>';
  if (empty($warehouseId)){
    echo '<h2>Se debe seleccionar la bodega para ver inventario y movimientos relevantes</h2>';
  }
  else {  
    //pr ($originalInventory);
    
    $inventoryTableHeader='';
    $inventoryTableHeader.='<thead>';
      $inventoryTableHeader.='<tr>';
        $inventoryTableHeader.='<th class="orderdate">'.__('Date').'</th>';
        $inventoryTableHeader.='<th>Tipo</th>';
        $inventoryTableHeader.='<th>'.__('Proveedor o Cliente').'</th>';
        $inventoryTableHeader.='<th>Número orden</th>';
        $inventoryTableHeader.='<th class="centered" colspan="'.(count($originalInventory)*count($originalInventory['entry'])).'">'.$this->Html->link($product['Product']['name'], ['controller' => 'products', 'action' => 'view', $product['Product']['id']]).'</th>';
      $inventoryTableHeader.='</tr>';
    $inventoryTableHeader.='</thead>';

    $tableRows='';
    $tableRows.='<tr style="font-weight:bold">';
      $tableRows.='<td></td>';
      $tableRows.='<td></td>';
      $tableRows.='<td></td>';
      $tableRows.='<td>Inventario Inicial</td>';
      foreach ($originalInventory['entry'] as $productionResultCodeId => $entryValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $tableRows.='<td class="centered number">'.$entryValue.'</td>';
        }
      }
      $tableRows.='<td class="centered number">'.$originalInventory['entry']['total'].'</td>';
      foreach ($originalInventory['exit'] as $productionResultCodeId => $exitValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $tableRows.='<td class="centered number">'.$exitValue.'</td>';
        }
      }
      $tableRows.='<td class="centered number">'.$originalInventory['exit']['total'].'</td>';
      foreach ($originalInventory['adjustment'] as $productionResultCodeId => $adjustmentValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $tableRows.='<td class="centered number">'.$adjustmentValue.'</td>';
        }
      }
      $tableRows.='<td class="centered number">'.$originalInventory['adjustment']['total'].'</td>';
      foreach ($originalInventory['saldo'] as $productionResultCodeId => $saldoValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $tableRows.='<td class="centered number">'.$saldoValue.'</td>';
        }
      }
      $tableRows.='<td class="centered number">'.$originalInventory['saldo']['total'].'</td>';
    $tableRows.='</tr>';
    
    $currentSaldo=$originalInventory['saldo'];
    // pr($resultMatrix);
    // pr($currentSaldo);
    foreach($resultMatrix as $row){
      // pr($row);
      $rowDateTime=new DateTime($row['date']);
      foreach ($currentSaldo as $productionResultCodeId => $saldoValue){
        if (array_key_exists($productionResultCodeId,$row['entry'])){
          $currentSaldo[$productionResultCodeId]+=$row['entry'][$productionResultCodeId];
        }
        if (array_key_exists($productionResultCodeId,$row['exit'])){
          $currentSaldo[$productionResultCodeId]-=$row['exit'][$productionResultCodeId];
        }
        if (array_key_exists($productionResultCodeId,$row['adjustment'])){
          if ($row['adjustment']['total'] > 0){
            if ($row['bool_input']){
              $currentSaldo[$productionResultCodeId]+=$row['adjustment'][$productionResultCodeId];
            }
            else {
              $currentSaldo[$productionResultCodeId]-=$row['adjustment'][$productionResultCodeId];
            }
          }            
        }
      }
      //pr($currentSaldo);
      
      
      switch($row['type']){
        case 'Orden de Producción':
        case 'Reclasificación':
        case 'Transferencia':
        case 'Ajuste':
          break;
        default:
          $providerAction=($row['providerbool']?"verProveedor":"verCliente");
          $orderAction=($row['entrybool']?"verEntrada":($row['issale']?"verVenta":"verRemision"));
      }
      $tableRows.='<tr>';
        $tableRows.='<td class="orderdate">'.$rowDateTime->format('d-m-Y').'</td>';
        $tableRows.='<td>'.$row['type'].'</td>';
        switch($row['type']){
          case 'Orden de Producción':
            $tableRows.='<td>-</td>';
            $tableRows.='<td>'.$this->Html->Link($row['productionRunCode'],['controller'=>'productionRuns','action'=>'detalle',$row['productionRunId']]).'</td>';
            break;
          case 'Reclasificación':
            $tableRows.='<td>-</td>';
            $tableRows.='<td>'.$row['reclassificationCode'].'</td>';
            break;
          case 'Transferencia':
            $tableRows.='<td>-</td>';
            $tableRows.='<td>'.$row['transferCode'].'</td>';
            break;
          case 'Ajuste':
            $tableRows.='<td>-</td>';
            $tableRows.='<td>'.$row['adjustmentCode'].'</td>';
            break;
          default:
            $tableRows.='<td>'.$this->Html->Link($row['providerclient'],['controller'=>'third_parties','action'=>$providerAction,$row['providerid']]).'</td>';
            $tableRows.='<td>'.$this->Html->Link($row['invoicecode'],['controller'=>'orders','action'=>$orderAction,$row['invoiceid']]).'</td>';
        }
        
        foreach ($currentSaldo as $productionResultCodeId => $saldoValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            if (array_key_exists($productionResultCodeId,$row['entry']) && $row['entry'][$productionResultCodeId] !=0){
              $tableRows.='<td class="centered number" style="font-weight:bold;">'.$row['entry'][$productionResultCodeId].'</td>';
            }
            else {
              $tableRows.='<td class="centered" style="font-weight:bold;">-</td>';
            }
          }
        }
        $tableRows.='<td class="centered'.($row['entry']['total'] == 0?"":" number").'" style="font-weight:bold;">'.($row['entry']['total'] == 0?"-":$row['entry']['total']).'</td>';
        
        foreach ($currentSaldo as $productionResultCodeId => $saldoValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            if (array_key_exists($productionResultCodeId,$row['exit']) && $row['exit'][$productionResultCodeId] !=0){
              $tableRows.='<td class="centered number" style="font-weight:bold;">'.$row['exit'][$productionResultCodeId].'</td>';
            }
            else {
              $tableRows.='<td class="centered" style="font-weight:bold;">-</td>';
            }
          }
        }
        $tableRows.='<td class="centered'.($row['exit']['total'] == 0?"":" number").'" style="font-weight:bold;">'.($row['exit']['total'] == 0?"-":$row['exit']['total']).'</td>';
        
        foreach ($currentSaldo as $productionResultCodeId => $saldoValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            if (array_key_exists($productionResultCodeId,$row['adjustment']) && $row['adjustment'][$productionResultCodeId] !=0){
              $tableRows.='<td class="centered number" style="font-weight:bold;">'.$row['adjustment'][$productionResultCodeId].'</td>';
            }
            else {
              $tableRows.='<td class="centered" style="font-weight:bold;">-</td>';
            }
          }
        }
        $tableRows.='<td class="centered'.($row['adjustment']['total'] == 0?"":" number").'" style="font-weight:bold;">'.($row['adjustment']['total'] == 0?"-":$row['adjustment']['total']).'</td>';
        
        foreach ($currentSaldo as $productionResultCodeId => $saldoValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            $tableRows.='<td class="centered number" style="font-weight:bold;">'.$saldoValue.'</td>';
          }
        }
        $tableRows.='<td class="centered number" style="font-weight:bold;">'.$currentSaldo['total'].'</td>';
      $tableRows.='</tr>';
    }
    
    $totalRow='';
    $totalRow.='<tr class="totalrow" style="font-weight:bold;">';
      $totalRow.='<td>Total</td>';
      $totalRow.='<td></td>';
      $totalRow.='<td></td>';
      $totalRow.='<td>Inventario Final</td>';
      foreach ($currentInventory['entry'] as $productionResultCodeId => $entryValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $totalRow.='<td class="centered number">'.$entryValue.'</td>';
        }
      }
      $totalRow.='<td class="centered number">'.$currentInventory['entry']['total'].'</td>';
      foreach ($currentInventory['exit'] as $productionResultCodeId => $exitValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $totalRow.='<td class="centered number">'.$exitValue.'</td>';
        }
      }
      $totalRow.='<td class="centered number">'.$currentInventory['exit']['total'].'</td>';
      foreach ($currentInventory['adjustment'] as $productionResultCodeId => $adjustmentValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $totalRow.='<td class="centered number">'.$adjustmentValue.'</td>';
        }
      }
      $totalRow.='<td class="centered number">'.$currentInventory['adjustment']['total'].'</td>';
      foreach ($currentInventory['saldo'] as $productionResultCodeId => $saldoValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
          $totalRow.='<td class="centered number">'.$saldoValue.'</td>';
        }
      }
      $totalRow.='<td class="centered number">'.$currentInventory['saldo']['total'].'</td>';
    $totalRow.='</tr>';
    $colSpan=1;
    
    
    foreach ($currentInventory['entry'] as $productionResultCodeId => $entryValue){
      if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
        $colSpan+=1;
      }
    }  
    $inventoryTableBody='<tbody>';
      $inventoryTableBody.='<tr>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td class="centered" style="border:1px solid black;" colspan="'.$colSpan.'">Entrada</td>';
        $inventoryTableBody.='<td class="centered" style="border:1px solid black;" colspan="'.$colSpan.'">Salida</td>';
        $inventoryTableBody.='<td class="centered" style="border:1px solid black;" colspan="'.$colSpan.'">Ajuste</td>';
        $inventoryTableBody.='<td class="centered" style="border:1px solid black;" colspan="'.$colSpan.'">Saldo</td>';
      $inventoryTableBody.='</tr>';
    if ($colSpan > 1){  
      $inventoryTableBody.='<tr>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        $inventoryTableBody.='<td></td>';
        foreach ($currentInventory['entry'] as $productionResultCodeId => $entryValue){
        if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            $inventoryTableBody.='<td class="centered">'.$productionResultCodes[$productionResultCodeId].'</td>';
          }
        }
        $inventoryTableBody.='<td class="centered">Total</td>';
        foreach ($currentInventory['exit'] as $productionResultCodeId => $exitValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            $inventoryTableBody.='<td class="centered">'.$productionResultCodes[$productionResultCodeId].'</td>';
          }
        }
        $inventoryTableBody.='<td class="centered">Total</td>';
        foreach ($currentInventory['adjustment'] as $productionResultCodeId => $adjustmentValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            $inventoryTableBody.='<td class="centered">'.$productionResultCodes[$productionResultCodeId].'</td>';
          }
        }
        $inventoryTableBody.='<td class="centered">Total</td>';
        foreach ($currentInventory['saldo'] as $productionResultCodeId => $saldoValue){
          if ($productionResultCodeId != 'total' && $productionResultCodeId !=0){
            $inventoryTableBody.='<td class="centered">'.$productionResultCodes[$productionResultCodeId].'</td>';
          }
        }
        $inventoryTableBody.='<td class="centered">Total</td>';
      $inventoryTableBody.='</tr>';
    }  
      $inventoryTableBody.=$totalRow.$tableRows.$totalRow;
    $inventoryTableBody.='</tbody>';
   
    $inventoryTable='<table id="'.substr("kardex_".$product['Product']['name'],0,30).'">'.$inventoryTableHeader.$inventoryTableBody.'</table>';
      
    echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarKardex',$product['Product']['name']], ['class' => 'btn btn-primary']); 
    echo "<br/>";

    echo $inventoryTable; 
    $_SESSION['kardex'] = $inventoryTable;
  }
  echo '</div>';
?>
</div>