<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
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
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>
<div class="adjustments index fullwidth">
<?php 
	echo "<h2>".__('Resumen Transferencias')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
			echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh'));
   echo "<div class='container-fluid'>";
      echo "<div class='row'>";
        echo "<div class='col-sm-4'>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenTransferencias'], ['class' => 'btn btn-primary']); 
        echo "</div>";
        if ($userRoleId == ROLE_ADMIN){  
          echo "<div class='col-sm-4'>";
            echo $this->Html->link(__('Registrar Transferencia de Bodega'), ['action' => 'transferirLote'],['class' => 'btn btn-primary','target'=>'blank']); 
          echo "</div>";  
        }
      echo "</div>";    
    echo "</div>";     

	$excelOutput="";
	
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('movement_date','Fecha Transferencia')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('transfer_code','# Transferencia')."</th>";
      $pageHeader.="<th>Producto</th>";
      $pageHeader.="<th>Cantidad</th>";
      $pageHeader.="<th>Bodega Origen</th>";
      $pageHeader.="<th>Bodega Destino</th>";
      //$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('movement_date','Fecha Transferencia')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('adjustment_code','# Transferencia')."</th>";
      $excelHeader.="<th>Producto</th>";
      $excelHeader.="<th>Cantidad</th>";
      $excelHeader.="<th>Bodega Origen</th>";
      $excelHeader.="<th>Bodega Destino</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	$totalQuantity=0;
  $transferCode="";
	for ($tm=0;$tm<count($transferMovements);$tm++){ 
    $movement=$transferMovements[$tm];
    if ($movement['StockMovement']['transfer_code'] != $transferCode){
      $transferDateTime=new DateTime($movement['StockMovement']['movement_date']);
      $transferDate=$transferDateTime->format('d-m-Y');
      $transferCode=(empty($movement['StockMovement']['transfer_code'])?"":$movement['StockMovement']['transfer_code']);
      $productName=$movement['Product']['name'];
      $productName.=(empty($movement['StockItem']['RawMaterial']['name'])?"":(" ".$movement['StockItem']['RawMaterial']['name']));
      $productName.=(empty($movement['ProductionResultCode']['code'])?"":(" ".$movement['ProductionResultCode']['code']));
      $productQuantity=$movement['StockMovement']['product_quantity'];
      $totalQuantity+=$productQuantity;
      if ($movement['StockMovement']['bool_input']){
        $warehouseDestination=$originWarehouse=$movement['StockItem']['Warehouse']['name'];;
      }
      else {
        $warehouseOrigin=$movement['StockItem']['Warehouse']['name'];
      }
    }
    else {
      if ($movement['StockMovement']['bool_input']){
        $warehouseDestination=$originWarehouse=$movement['StockItem']['Warehouse']['name'];;
      }
      else {
        
        $warehouseOrigin=$movement['StockItem']['Warehouse']['name'];
      }
      $pageRow='';
      $pageRow.='<td>'.$transferDate.'</td>';
      $pageRow.='<td>'.(empty($transferCode)?"-":$this->Html->Link($transferCode,['action'=>'detalleTransferencia',$transferCode])).'</td>';
      $pageRow.='<td>'.$productName.'</td>';
      $pageRow.='<td class="number"><span class="amountright">'.$productQuantity.'</span></td>';
      $pageRow.='<td>'.$warehouseOrigin.'</td>';
      $pageRow.='<td>'.$warehouseDestination.'</td>';
      
      $excelRow=$pageRow;
      $excelBody.="<tr>".$excelRow."</tr>";
          
      //$pageRow.="<td class='actions'>".$this->Form->postLink(__('Eliminar transferencia'), ['action' => 'eliminarTransferencia',$movement['StockMovement']['adjustment_code']],[], __('Está seguro que quiere  eliminar transferencia # %s?', $movement['StockMovement']['transfer_code']))."</td>";
      $pageBody.="<tr>".$pageRow."</tr>";	
    }
	}

	$pageTotalRows="";
  $pageTotalRows.="<tr class='totalrow'>";
    $pageTotalRows.="<td>Total Transferencias</td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td class='number'><span class='amountright'>".($totalQuantity)."</span></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    //$pageTotalRows.="<td></td>";
  $pageTotalRows.="</tr>";
	
	$pageBody="<tbody>".$pageTotalRows.$pageBody.$pageTotalRows."</tbody>";
	$table_id="transferencias_bodega";
	$pageOutput='<table id="'.$table_id.'">'.$pageHeader.$pageBody.'</table>';
	echo $pageOutput;
	$excelOutput.='<table id="'.$table_id.'">'.$excelHeader.$excelBody.'</table>';
	$_SESSION['resumenTransferencias'] = $excelOutput;
?>
</div>