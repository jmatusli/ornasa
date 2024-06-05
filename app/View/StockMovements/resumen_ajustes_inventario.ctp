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
	echo "<h2>".__('Resumen Ajustes de Inventario')."</h2>";
	echo $this->Form->create('Report');
		echo "<fieldset>";
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2019,'maxYear'=>date('Y')]);
			echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			//echo $this->Form->input('Report.currency_id',['label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId]);
			
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo "<br/>";
	echo $this->Form->end(__('Refresh'));
   echo "<div class='container-fluid'>";
      echo "<div class='row'>";
        echo "<div class='col-sm-4'>";
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']); 
        echo "</div>";
        if ($userrole===ROLE_ADMIN){  
          echo "<div class='col-sm-4'>";
            echo $this->Html->link(__('Registrar Ajustes de Inventario'), ['controller'=>'stockItems','action' => 'ajustesInventario',WAREHOUSE_DEFAULT],['class' => 'btn btn-primary','target'=>'blank']); 
          echo "</div>";  
        }
      echo "</div>";    
    echo "</div>";     

	$excelOutput="";
	
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('movement_date','Fecha Ajuste')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('adjustment_code','# Ajuste')."</th>";
      $pageHeader.="<th>Producto</th>";
      $pageHeader.="<th>Materia Prima</th>";
      $pageHeader.="<th>Calidad</th>";
      $pageHeader.="<th>Ajuste</th>";
      $pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('movement_date','Fecha Ajuste')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('adjustment_code','# Ajuste')."</th>";
      $excelHeader.="<th>Producto</th>";
      $excelHeader.="<th>Materia Prima</th>";
      $excelHeader.="<th>Calidad</th>";
      $excelHeader.="<th>Ajuste</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	$totalLowered=0;
  $totalRaised=0;
  	
	foreach ($adjustmentMovements as $movement){ 
  //pr($movement);
		$adjustmentDateTime=new DateTime($movement['StockMovement']['movement_date']);
		if (!$movement['StockMovement']['bool_input']){
      $totalLowered+=$movement['StockMovement']['product_quantity'];
    }
    else {
      $totalRaised+=$movement['StockMovement']['product_quantity'];
    }
		$pageRow="";
    $pageRow.="<td>".$adjustmentDateTime->format('d-m-Y')."</td>";
    $pageRow.="<td>".(empty($movement['StockMovement']['adjustment_code'])?"-":$movement['StockMovement']['adjustment_code'])."</td>";
    $pageRow.="<td>".$movement['Product']['name']."</td>";
    $pageRow.="<td>".(!empty($movement['StockItem']['RawMaterial']['name'])?$movement['StockItem']['RawMaterial']['name']:"-")."</td>";
    $pageRow.="<td>".(!empty($movement['ProductionResultCode']['code'])?$movement['ProductionResultCode']['code']:"-")."</td>";
    $pageRow.="<td class='number'><span class='amountright'>".($movement['StockMovement']['bool_input']?$movement['StockMovement']['product_quantity']:-$movement['StockMovement']['product_quantity'])."</span></td>";
    
    $excelRow=$pageRow;
    $excelBody.="<tr>".$excelRow."</tr>";
        
    $pageRow.="<td class='actions'>".$this->Form->postLink(__('Eliminar ajuste'), ['action' => 'eliminarAjuste',$movement['StockMovement']['adjustment_code']],[], __('Está seguro que quiere  eliminar ajuste # %s?', $movement['StockMovement']['adjustment_code']))."</td>";
    $pageBody.="<tr>".$pageRow."</tr>";	
	}

	$pageTotalRows="";
  $pageTotalRows.="<tr class='totalrow'>";
    $pageTotalRows.="<td>Total Bajado</td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td class='number'><span class='amountright'>".$totalLowered."</span></td>";
    $pageTotalRows.="<td></td>";
  $pageTotalRows.="</tr>";
  $pageTotalRows.="<tr class='totalrow'>";
    $pageTotalRows.="<td>Total Subido</td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td class='number'><span class='amountright'>".$totalRaised."</span></td>";
    $pageTotalRows.="<td></td>";
  $pageTotalRows.="</tr>";
  $pageTotalRows.="<tr class='totalrow'>";
    $pageTotalRows.="<td>Total Ajustado</td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td></td>";
    $pageTotalRows.="<td class='number'><span class='amountright'>".($totalLowered+$totalRaised)."</span></td>";
    $pageTotalRows.="<td></td>";
  $pageTotalRows.="</tr>";
	
	$pageBody="<tbody>".$pageTotalRows.$pageBody.$pageTotalRows."</tbody>";
	$table_id="ajustes_inventario";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput.="<table id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['ajustesInventario'] = $excelOutput;
?>
</div>