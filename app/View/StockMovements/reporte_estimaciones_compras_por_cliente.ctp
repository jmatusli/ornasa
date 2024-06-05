<script>
  function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}

  $(document).ready(function(){
    formatNumbers();
		
    $('select.fixed option:not(:selected)').attr('disabled', true);
  });

</script>

<div class="stockMovements view report">

<?php 
	
	
  $clientTotals=[];
  
  $salesTableHeader="";
	$salesTableHeader.="<thead>";
		$salesTableHeader.="<tr>";
			$salesTableHeader.="<th></th>";
			foreach($soldProducts as $product){
        $salesTableHeader.="<th class='centered' colspan=".count($product['RawMaterials']).">".($userRoleId != ROLE_SALES?$this->Html->link($product['Product']['name'],['controller'=>'products','action'=>'view',$product['Product']['id']]):$product['Product']['name'])."</th>";
			}
			$salesTableHeader.="<th>".__('Total')."</th>";
		$salesTableHeader.="</tr>";
    $salesTableHeader.="<tr>";
			$salesTableHeader.="<th>".__('Cliente')."</th>";
			foreach($soldProducts as $product){
        foreach ($product['RawMaterials'] as $rawMaterial){
          $salesTableHeader.="<th class='centered'>".($userRoleId != ROLE_SALES?$this->Html->link($rawMaterial['Product']['name'],['controller'=>'products','action'=>'view',$rawMaterial['Product']['id']]):$rawMaterial['Product']['name'])."</th>";
        }
			}
			$salesTableHeader.="<th>".__('Total')."</th>";
		$salesTableHeader.="</tr>";
	$salesTableHeader.="</thead>";
	
	$salesTableBody="";
	$productRawMaterialTotals=[];
	foreach($soldProducts as $product){
    foreach ($product['RawMaterials'] as $rawMaterial){
      $productRawMaterialTotals[]=0;
    }
	}
	$totalAllClients=0;
	foreach ($buyingClients as $client){
    $productCounter=0;
    $totalProductQuantityForClient=0;
    foreach ($client['quantities'] as $quantity){
      $productRawMaterialTotals[$productCounter]+=$quantity['product_quantity_average'];
      $totalProductQuantityForClient+=$quantity['product_quantity_average'];
      $productCounter++;
    }
    $totalAllClients+=$totalProductQuantityForClient;    
  	$clientTotals[$client['ThirdParty']['id']]=$totalProductQuantityForClient;
    
    $salesTableBodyRow="";
		$salesTableBodyRow.="<tr>";
			$salesTableBodyRow.="<td>".($userRoleId != ROLE_SALES?
        $this->Html->link($client['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $client['ThirdParty']['id']]):
        $client['ThirdParty']['company_name']
      )."</td>";
			foreach ($client['quantities'] as $quantity){
        $salesTableBodyRow.="<td class='centered number'>".$quantity['product_quantity_average']."</td>";
      }
      $salesTableBodyRow.="<td class='centered number bold'>".$totalProductQuantityForClient."</td>";
		$salesTableBodyRow.="</tr>";
    $salesTableBody.=$salesTableBodyRow;
	}
  
  $totalInventory=0;
  foreach($soldProducts as $product){
    foreach ($product['RawMaterials'] as $rawMaterial){
      $totalInventory+=$rawMaterial['Product']['quantity_inventory'];
    }
  }  
	
  $totalRow="";
  $totalRow.="<tr class='totalrow'>";
		$totalRow.="<td>Inventario</td>";
    foreach($soldProducts as $product){
      foreach ($product['RawMaterials'] as $rawMaterial){
        $totalRow.="<td class='centered number'>".$rawMaterial['Product']['quantity_inventory']."</td>";
      }
    }
		$totalRow.="<td class='centered number bold'>".$totalInventory."</td>";
	$totalRow.="</tr>";
	$totalRow.="<tr class='totalrow'>";
		$totalRow.="<td>Promedio x mes todos clientes</td>";
		foreach ($productRawMaterialTotals as $productTotal){
			$totalRow.="<td class='centered number'>".$productTotal."</td>";
		}
		$totalRow.="<td class='centered number bold'>".$totalAllClients."</td>";
	$totalRow.="</tr>";
  $salesTableBody=$totalRow.$salesTableBody.$totalRow;
	
	$salesTable='<table id="venta_producto_por_cliente">'.$salesTableHeader.$salesTableBody.'</table>';
  
  
  echo '<h1>Estimaciones de Compras por Cliente basado en ventas de '.NUMBER_OF_MONTHS.' meses</h1>';
  
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-8">';
        echo $this->Form->create('Report'); 
          echo "<fieldset>";		
            echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Inicio Estimaciones'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userRoleId != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y'),'class'=>'fixed']);
            echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('Fin Estimaciones'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userRoleId != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y'),'class'=>'fixed']);
          echo "</fieldset>";
          //if ($userRoleId != ROLE_SALES){  
          //  echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
          //  echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
          //}
        echo $this->Form->end(__('Refresh')); 
        if ($userRoleId != ROLE_SALES){  
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteEstimacionesComprasPorCliente'], ['class' => 'btn btn-primary']); 
        }
      echo '</div>';
      echo '<div class="col-sm-4">';
      $summaryTable='';
      if (!empty($clientTotals)){
        $summaryTableBodyRows='';
        foreach ($clientTotals as $clientId=>$clientTotal){
          $summaryTableBodyRows.='<tr>';
            $summaryTableBodyRows.='<td>'.($userRoleId != ROLE_SALES?
              $this->Html->link($buyingClientList[$clientId], ['controller' => 'third_parties', 'action' => 'verCliente', $clientId]):
              $buyingClientList[$clientId]
            ).'</td>';
            $summaryTableBodyRows.='<td class="centered number">'.$clientTotal.'</td>';
          $summaryTableBodyRows.='</tr>';
        }
        $summaryTableTotalRow='';
        $summaryTableTotalRow.='<tr class="totalrow">';
            $summaryTableTotalRow.='<td>Todos Clientes</td>';
            $summaryTableTotalRow.='<td class="centered number">'.$totalAllClients.'</td>';
          $summaryTableTotalRow.='</tr>';
        $summaryTable='<table id="resumen_ejecutivo">'.$summaryTableTotalRow.$summaryTableBodyRows.$summaryTableTotalRow.'</table>';
      }
      echo $summaryTable;
      echo '</div>';
    echo '</div>';
  echo '</div>';
  
  
	echo $salesTable; 
	$_SESSION['reporteVentaProductoPorCliente'] = $summaryTable.$salesTable;
?>
</div>