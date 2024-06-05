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
<div class="warehouseProducts index">
<?php 
	echo '<h2>'.__('Warehouse Products').'</h2>';
	echo $this->Form->create('Report');
		echo '<fieldset>';
			echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
		echo '</fieldset>';
		echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
		echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']);
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_add_permission){
			echo '<li>'.$this->Html->link(__('New Warehouse Product'), ['action' => 'crear']).'</li>';
		}
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Warehouses'), ['controller' => 'warehouses', 'action' => 'resumen']).'</li>';
		echo '<li>".$this->Html->link(__('New Warehouse'), ['controller' => 'warehouses', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'resumen']).'</li>';
		echo '<li>".$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
<div>
<?php
	$tableHeader='<thead>';
		$tableHeader.='<tr>';
			$tableHeader.='<th>'.$this->Paginator->sort('assignment_datetime').'</th>';
			$tableHeader.='<th>'.$this->Paginator->sort('warehouse_id').'</th>';
			$tableHeader.='<th>'.$this->Paginator->sort('product_id').'</th>';
			$tableHeader.='<th class="actions">'.__('Actions').'</th>';
		$tableHeader.='</tr>';
	$tableHeader.='</thead>';
	$excelHeader='<thead>';
		$excelHeader.='<tr>';
			$excelHeader.='<th>'.$this->Paginator->sort('assignment_datetime').'</th>';
			$excelHeader.='<th>'.$this->Paginator->sort('warehouse_id').'</th>';
			$excelHeader.='<th>'.$this->Paginator->sort('product_id').'</th>';
		$excelHeader.='</tr>';
	$excelHeader.='</thead>';

	$tableBody='';
	$excelBody='';

	foreach ($warehouseProducts as $warehouseProduct){ 
		$tableRow='';		$tableRow.='<td>'.h($warehouseProduct['WarehouseProduct']['assignment_datetime']).'</td>';
		$tableRow.='<td>'.$this->Html->link($warehouseProduct['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'detalle', $warehouseProduct['Warehouse']['id']]).'</td>';
		$tableRow.='<td>'.$this->Html->link($warehouseProduct['Product']['name'], ['controller' => 'products', 'action' => 'detalle', $warehouseProduct['Product']['id']]).'</td>';

			$excelBody.='<tr>'.$tableRow.'</tr>';

			$tableRow.='<td class="actions">';
				$tableRow.=$this->Html->link(__('View'), ['action' => 'detalle', $warehouseProduct['WarehouseProduct']['id']]);
		if ($bool_edit_permission){
				$tableRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $warehouseProduct['WarehouseProduct']['id']]);
		}
			$tableRow.='</td>';

		$tableBody.='<tr>'.$tableRow.'</tr>';
	}

	$totalRow='';
	$totalRow.='<tr class="totalrow">';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
		$totalRow.='<td></td>';
	$totalRow.='</tr>';

	$tableBody='<tbody>'.$totalRow.$tableBody.$totalRow.'</tbody>';
	$tableId='';
	$pageOutput='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$tableHeader.$tableBody.'</table>';
	echo $pageOutput;
	$excelOutput='<table cellpadding="0" cellspacing="0" id="'.$tableId.'">'.$excelHeader.$excelBody.'</table>';
	$_SESSION['resumen'] = $excelOutput;
?>
</div>