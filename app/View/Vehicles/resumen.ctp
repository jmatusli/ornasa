<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
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
<div class="vehicles index fullwidth">
<?php 
	echo '<h2>'.__('Vehicles').'</h2>';
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-9">';
        
        echo $this->Form->create('Report');
          echo '<fieldset>';
          /*
            echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
            echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          */
            echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          echo '</fieldset>';
        /*  
          echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
          echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
        */
        echo $this->Form->end(__('Refresh'));
      
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']);

      echo '</div>';
      echo '<div class="col-sm-3">';
        echo '<h3>'.__('Actions').'</h3>';
        echo '<ul>';
          //if ($bool_add_permission){
            echo '<li>'.$this->Html->link(__('New Vehicle'), ['action' => 'crear']).'</li>';
          //}
          /*
          echo '<br/>';
          echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
          echo '<li>'.$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
          */
        echo '</ul>';
      echo '</div>';
    echo '</div>';
    echo '<div clas="row">';  
      echo '<div class="col-sm-12">';
        $excelOutput='';
        if (empty($warehouseVehicles)){
          echo '<h2>No hay bodegas asociados con este usuario</h2>';
        }
        else {
          foreach ($warehouseVehicles as $warehouseId=>$warehouseData){
            $tableHeader='<thead>';
              $tableHeader.='<tr>';
                //$tableHeader.='<th>'.$this->Paginator->sort('warehouse_id').'</th>';
                $tableHeader.='<th style="width:35%;">'.$this->Paginator->sort('name').'</th>';
                $tableHeader.='<th style="width:35%;">'.$this->Paginator->sort('license_plate').'</th>';
                $tableHeader.='<th>'.$this->Paginator->sort('list_order').'</th>';
                //$tableHeader.='<th class="actions">'.__('Actions').'</th>';
              $tableHeader.='</tr>';
            $tableHeader.='</thead>';
            $excelHeader='<thead>';
              $excelHeader.='<tr>';
                //$excelHeader.='<th>'.$this->Paginator->sort('warehouse_id').'</th>';
                $excelHeader.='<th>'.$this->Paginator->sort('name').'</th>';
                $excelHeader.='<th>'.$this->Paginator->sort('license_plate').'</th>';
                $excelHeader.='<th>'.$this->Paginator->sort('list_order').'</th>';
                $excelHeader.='<th>'.$this->Paginator->sort('bool_active').'</th>';
              $excelHeader.='</tr>';
            $excelHeader.='</thead>';

            $tableBody='';
            $excelBody='';

            foreach ($warehouseData['Vehicle'] as $vehicle){ 
              $tableRow='';		
              //$tableRow.='<td>'.$this->Html->link($vehicle['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'detalle', $vehicle['Warehouse']['id']]).'</td>';
              $tableRow.='<td>'.$this->Html->link($vehicle['Vehicle']['name'],['action'=>'detalle',$vehicle['Vehicle']['id']]).'</td>';
              $tableRow.='<td>'.h($vehicle['Vehicle']['license_plate']).'</td>';
              $tableRow.='<td>'.h($vehicle['Vehicle']['list_order']).'</td>';
              
              $excelBody.='<tr>'.$tableRow.'<td>'.h($vehicle['Vehicle']['bool_active']).'</td></tr>';

              //$tableRow.='<td class="actions">';
              //if ($bool_edit_permission){
                //$tableRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $vehicle['Vehicle']['id']]);
              //}
              //$tableRow.='</td>';

              $tableBody.='<tr>'.$tableRow.'</tr>';
            }

            $totalRow='';
          /*  
            $totalRow.='<tr class="totalrow">';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
              $totalRow.='<td></td>';
            $totalRow.='</tr>';
          */
            $tableBody='<tbody>'.$totalRow.$tableBody.$totalRow.'</tbody>';
            $tableId='vehiculos_'.trim($warehouseData['Warehouse']['name']);
            $warehouseVehicleTable='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$tableHeader.$tableBody.'</table>';
            echo '<h2>Vehículos para bodega '.$warehouseData['Warehouse']['name'].'</h2>';
            echo $warehouseVehicleTable;
            $excelOutput.='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$excelHeader.$excelBody.'</table>';
          }
        }
      echo '</div>';
    echo '</div>';
  echo '</div>';    
	$_SESSION['resumen'] = $excelOutput;
?>
</div>