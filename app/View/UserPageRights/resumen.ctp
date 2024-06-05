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
<div class="userPageRights index">
<?php 
	echo '<h2>'.__('User Page Rights').'</h2>';
	echo $this->Form->create('Report');
		echo '<fieldset>';
			//echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
			//echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
      echo $this->Form->input('Report.role_id',['default'=>$roleId,'empty'=>[0=>'-- Todos papeles --']]);
		echo '</fieldset>';
		//echo '<button id="previousmonth" class="monthswitcher">'.__('Previous Month').'</button>';
		//echo '<button id="nextmonth" class="monthswitcher">'.__('Next Month').'</button>';
	echo $this->Form->end(__('Refresh'));
	echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenAsignacionesDerechosIndividuales'], ['class' => 'btn btn-primary']);
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_add_permission){
			echo '<li>'.$this->Html->link(__('New User Page Right'), ['action' => 'crear']).'</li>';
		}
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Page Rights'), ['controller' => 'pageRights', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Page Right'), ['controller' => 'pageRights', 'action' => 'crear']).'</li>';
    echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Roles'), ['controller' => 'roles', 'action' => 'resumen']).'</li>';
		echo '<br/>';
    echo '<li>'.$this->Html->link(__('List Users'), ['controller' => 'users', 'action' => 'resumen']).'</li>';
		echo '<br/>';
	echo '</ul>';
?>
</div>
<div>
<?php
	$tableHeader='<thead>';
		$tableHeader.='<tr>';
			$tableHeader.='<th>'.$this->Paginator->sort('assignment_datetime').'</th>';
			$tableHeader.='<th>'.__('Page Right').'</th>';
			$tableHeader.='<th>'.__('Role').'</th>';
			$tableHeader.='<th>'.__('User').'</th>';
			$tableHeader.='<th>'.__('BoolAllowed').'</th>';
			$tableHeader.='<th>'.__('Controller').'</th>';
			$tableHeader.='<th>'.__('Action').'</th>';
			$tableHeader.='<th class="actions">'.__('Actions').'</th>';
		$tableHeader.='</tr>';
	$tableHeader.='</thead>';
	$excelHeader='<thead>';
		$excelHeader.='<tr>';
      $excelHeader.='<th>'.$this->Paginator->sort('assignment_datetime').'</th>';
			$excelHeader.='<th>'.__('Page Right').'</th>';
			$excelHeader.='<th>'.__('Role').'</th>';
			$excelHeader.='<th>'.__('User').'</th>';
			$excelHeader.='<th>'.__('BoolAllowed').'</th>';
			$excelHeader.='<th>'.__('Controller').'</th>';
			$excelHeader.='<th>'.__('Action').'</th>';
		$excelHeader.='</tr>';
	$excelHeader.='</thead>';

	$tableBody='';
	$excelBody='';

	foreach ($userPageRights as $userPageRight){ 
    
    
    
		
    $userPageRightAssignmentDatetime=new DateTime($userPageRight['UserPageRight']['assignment_datetime']);
    
    $tableRow='';		
    $tableRow.='<td>'.$userPageRightAssignmentDatetime->format('d-m-Y H:i').'</td>';
		$tableRow.='<td>'.$this->Html->link($userPageRight['PageRight']['name'], ['controller' => 'page_rights', 'action' => 'detalle', $userPageRight['PageRight']['id']]).'</td>';
		$tableRow.='<td>'.(empty($userPageRight['Role']['id'])?'-':($this->Html->link($userPageRight['Role']['name'], ['controller' => 'roles', 'action' => 'detalle', $userPageRight['Role']['id']]))).'</td>';
		$tableRow.='<td>'.(empty($userPageRight['User']['id'])?'-':($this->Html->link($userPageRight['User']['first_name'].' '.$userPageRight['User']['last_name'], ['controller' => 'users', 'action' => 'detalle', $userPageRight['User']['id']]))).'</td>';
		$tableRow.='<td>'.($userPageRight['UserPageRight']['bool_allowed']?__('Yes'):__('No')).'</td>';
		$tableRow.='<td>'.h($userPageRight['UserPageRight']['controller']).'</td>';
		$tableRow.='<td>'.h($userPageRight['UserPageRight']['action']).'</td>';

			$excelBody.='<tr>'.$tableRow.'</tr>';

			$tableRow.='<td class="actions">';				
      if ($bool_edit_permission){
          $tableRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $userPageRight['UserPageRight']['id']]);
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
	$_SESSION['resumenAsignacionesDerechosIndividuales'] = $excelOutput;
?>
</div>