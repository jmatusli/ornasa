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

<div class="stockMovements view report">

<?php 
	echo "<h2>Venta de Producto por Cliente</h2>";
	echo $this->Form->create('Report'); 
		echo "<fieldset>";		
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
      echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
      if ($userRoleId == ROLE_ADMIN){
        echo $this->Form->input('Report.user_id',[
          'label'=>'Usuario',
          'value'=>$userId,
          'empty'=>[0=>'-- Seleccione Usuario --'],
        ]);
      }
      else {
        echo $this->Form->input('Report.user_id',[
          'default'=>$userId,
          'type'=>'hidden',
        ]);
      }
      echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
		echo "</fieldset>";
    if ($userrole != ROLE_SALES){  
      echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
      echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
      }
	echo $this->Form->end(__('Refresh')); 
  if ($userrole != ROLE_SALES){  
    echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarReporteVentaProductoPorCliente'], ['class' => 'btn btn-primary']); 
  }
	$salesTableHeader="";
	$salesTableHeader.="<thead>";
		$salesTableHeader.="<tr>";
			$salesTableHeader.="<th></th>";
			foreach($soldProducts as $product){
        //pr($product);
        $salesTableHeader.="<th class='centered' colspan=".count($product['RawMaterials']).">".($userrole != ROLE_SALES?$this->Html->link($product['Product']['name'],['controller'=>'products','action'=>'view',$product['Product']['id']]):$product['Product']['name'])."</th>";
			}
			$salesTableHeader.="<th>".__('Total')."</th>";
		$salesTableHeader.="</tr>";
    $salesTableHeader.="<tr>";
			$salesTableHeader.="<th>".__('Cliente')."</th>";
			foreach($soldProducts as $product){
        if (empty($product['RawMaterials'])){
          $salesTableHeader.="<th class='centered'>-</th>";
        }
        else {
          foreach ($product['RawMaterials'] as $rawMaterial){
            $salesTableHeader.="<th class='centered'>".($userrole != ROLE_SALES?$this->Html->link($rawMaterial['Product']['name'],['controller'=>'products','action'=>'view',$rawMaterial['Product']['id']]):$rawMaterial['Product']['name'])."</th>";
          }
        }
			}
			$salesTableHeader.="<th>".__('Total')."</th>";
		$salesTableHeader.="</tr>";
	$salesTableHeader.="</thead>";
	
	$salesTableBody="";
	$productRawMaterialTotals=[];
	foreach($soldProducts as $product){
    if (empty($product['RawMaterials'])){
       $productRawMaterialTotals[]=0;
    }
    else {
      foreach ($product['RawMaterials'] as $rawMaterial){
        $productRawMaterialTotals[]=0;
      }
    }
	}
	$totalAllClients=0;
	foreach ($buyingClients as $client){
  	$salesTableBodyRow="";
		$salesTableBodyRow.="<tr>";
			$salesTableBodyRow.="<td>".($userrole != ROLE_SALES?$this->Html->link($client['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verCliente', $client['ThirdParty']['id']]):$client['ThirdParty']['company_name'])."</td>";
					
      $productCounter=0;
			$totalProductQuantityForClient=0;
      foreach ($client['quantities'] as $quantity){
        $salesTableBodyRow.="<td class='centered number'>".$quantity['product_quantity']."</td>";
        $productRawMaterialTotals[$productCounter]+=$quantity['product_quantity'];
        $totalProductQuantityForClient+=$quantity['product_quantity'];
        $productCounter++;
      }
      $salesTableBodyRow.="<td class='centered number bold'>".$totalProductQuantityForClient."</td>";
			$totalAllClients+=$totalProductQuantityForClient;
    $salesTableBodyRow.="</tr>";
    $salesTableBody.=$salesTableBodyRow;
	}
	$totalRow="";
	$totalRow.="<tr class='totalrow'>";
		$totalRow.="<td>Total</td>";
		foreach ($productRawMaterialTotals as $productTotal){
			$totalRow.="<td class='centered number'>".$productTotal."</td>";
		}
		$totalRow.="<td class='centered number bold'>".$totalAllClients."</td>";
	$totalRow.="</tr>";
	$salesTableBody=$totalRow.$salesTableBody.$totalRow;
	
	$salesTable="<table id='venta_producto_por_cliente'>".$salesTableHeader.$salesTableBody."</table>";
		
	echo $salesTable; 
	$_SESSION['reporteVentaProductoPorCliente'] = $salesTable;
?>
</div>