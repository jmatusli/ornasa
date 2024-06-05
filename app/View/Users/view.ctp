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
<div class="users view">
<?php 
	echo "<h2>".__('User')." ".$user['User']['first_name']." ".$user['User']['last_name']." (".($user['User']['bool_active']?__('Activo'):__('Desactivado')).")</h2>";
	echo $this->Form->create('Report'); 
		echo "<fieldset>"; 
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-md-12'>";
					echo $this->Form->input('Report.unassociated_display_option_id',array('label'=>__('Clientes Asociados'),'default'=>$unassociatedDisplayOptionId));
          echo $this->Form->input('Report.history_display_option_id',array('label'=>__('Mostrar Historial'),'default'=>$historyDisplayOptionId));
					
          echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
					echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
					//echo $this->Form->input('Report.currency_id',array('label'=>__('Visualizar Totales'),'options'=>$currencies,'default'=>$currencyId));
          
          echo $this->Form->input('Report.searchterm',array('label'=>__('Buscar')));
				echo "</div>";
			echo "</div>";
		echo "</div>";
		echo  "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
		echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
	echo $this->Form->end(__('Refresh')); 
	echo "</br>";

	echo "<dl>";
		echo "<dt>". __('Username')."</dt>";
		echo "<dd>". h($user['User']['username'])."</dd>";
		echo "<dt>". __('Role')."</dt>";
		echo "<dd>". $this->Html->link($user['Role']['name'], array('controller' => 'roles', 'action' => 'view', $user['Role']['id']))."</dd>";
		echo "<dt>". __('First Name')."</dt>";
		echo "<dd>". h($user['User']['first_name'])."&nbsp;</dd>";
		echo "<dt>". __('Last Name')."</dt>";
		echo "<dd>". h($user['User']['last_name'])."&nbsp;</dd>";
    echo "<dt>". __('Abbreviation')."</dt>";
		echo "<dd>". h($user['User']['abbreviation'])."&nbsp;</dd>";
		echo "<dt>". __('Email')."</dt>";
		if (!empty($user['User']['email'])){
			echo "<dd>". h($user['User']['email'])."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
		echo "<dt>". __('Phone')."</dt>";
		if (!empty($user['User']['phone'])){
			echo "<dd>". h($user['User']['phone'])."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
    echo "<dt>". __('Client')."</dt>";
    if (!empty($user['Client']['company_name'])){
			echo "<dd>". h($user['Client']['company_name'])."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
	echo "</dl>";
?>
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission) {	
      echo '<li>'.$this->Html->link(__('Edit User'), ['action' => 'editar', $user['User']['id']]).'</li>';
      echo'<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Users'), ['action' => 'resumen']).'</li>';
		if ($bool_add_permission) {
      echo '<li>'.$this->Html->link(__('New User'), ['action' => 'crear']).'</li>';
		}
		
		if ($userRoleId == ROLE_ADMIN) {
      echo'<br/>';
      echo '<li>'.$this->Html->link(__('List User Logs'), ['controller' => 'user_logs', 'action' => 'resumen',$user['User']['id']]).'</li>';
		}	
		
	echo '</ul>';
?>
</div>
<div class="related">
<?php 
	if(!empty($uniqueClients)){
    $tableHeader="";
    $tableHeader.="<thead>";
			$tableHeader.="<tr>";
				$tableHeader.="<th>".__('Name')."</th>";
				$tableHeader.="<th style='width:15%;'>".__('Email Address')."</th>";
				$tableHeader.="<th>".__('Phone')."</th>";
				$tableHeader.=($historyDisplayOptionId?"<th style='width:15%;'>Historial de Asignaciones</th>":"");
				$tableHeader.="<th># Salidas</th>";
				$tableHeader.="<th>$ Salidas</th>";
				$tableHeader.="<th class='actions'>".__('Actions')."</th>";
			$tableHeader.="</tr>";
    $tableHeader.="</thead>";
/*    
    $excelHeader="";
    $excelHeader.="<thead>";
			$excelHeader.="<tr>";
				$excelHeader.="<th>".__('Name')."</th>";
				$excelHeader.="<th style='width:15%;'>".__('Email')."</th>";
				$excelHeader.="<th>".__('Phone')."</th>";
        $excelHeader.=($historyDisplayOptionId?"<th style='width:15%;'>Historial de Asignaciones</th>":"");
				$excelHeader.="<th># Salidas</th>";
				$excelHeader.="<th>$ Salidas</th>";
			$excelHeader.="</tr>";
    $excelHeader.="</thead>";  
*/    
    $tableBody="";
    $excelBody="";
    $totalOrderQuantity=0;
    $totalOrderAmount=0;
    //pr($uniqueClients[0]);
		foreach ($uniqueClients as $client){
      
      // TODO FIX NEEDED 20180603 CHECK ON BOOL ASSIGNED REMOVED
      if ($unassociatedDisplayOptionId||(!empty($client['ClientUser'])&&$client['ClientUser'][0]['bool_assigned'])){
        $totalOrderQuantity+=count($client['Order']);
        $totalOrderAmount+=$client['Client']['order_total'];
        
        $tableRow="";
        $tableRow.="<td>".$this->Html->link($client['ThirdParty']['company_name'].($client['ThirdParty']['bool_active']?"":" (Desactivado)"), ['controller' => 'clients', 'action' => 'view', $client['ThirdParty']['id']])."</td>";
        $tableRow.="<td>".$client['ThirdParty']['email']."</td>";
        $tableRow.="<td>".$client['ThirdParty']['phone']."</td>";
        
        if ($historyDisplayOptionId){
          $tableRow.="<td>";
          foreach ($client['ClientUser'] as $clientUser){
            $assignmentDateTime=new DateTime($clientUser['assignment_datetime']);
            $tableRow.=($clientUser['bool_assigned']?"Asignado":"Desasignado")." el ".($assignmentDateTime->format('d-m-Y H:i:s'))."<br>";
          }  
          $tableRow.="</td>";
        }
        $tableRow.="<td>".count($client['Order'])."</td>";
        $tableRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$client['Client']['order_total']."</span></td>";
        
        //$excelBody.=($client['ClientUser'][0]['bool_assigned']?"<tr>":"<tr class='italic'>").$tableRow."</tr>";
        
        $tableRow.="<td class='actions'>".($bool_client_edit_permission?$this->Html->link(__('Edit'), ['controller' => 'clients', 'action' => 'edit', $client['ThirdParty']['id']]):"")."</td>";
        
        $tableBody.=(!empty($client['ClientUser'])&&$client['ClientUser'][0]['bool_assigned']?"<tr>":"<tr class='italic'>").$tableRow."</tr>";
      }  
    }

    $totalRow=$excelTotalRow="";
    $excelTotalRow.="<td>Total C$</td>";
    $excelTotalRow.="<td></td>";
    $excelTotalRow.="<td></td>";
    $excelTotalRow.=($historyDisplayOptionId?"<td></td>":"");;
    $excelTotalRow.="<td>".$totalOrderQuantity."</td>";	    
    $excelTotalRow.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalOrderAmount."</span></td>";	
    
    $totalRow=$excelTotalRow;    
    //$excelTotalRow="<tr class='totalrow'>".$excelTotalRow."</tr>";
    $totalRow.="<td></td>";	
    $totalRow="<tr class='totalrow'>".$totalRow."</tr>";
    
		$table= "<table id='clientes_asignados' cellpadding = '0' cellspacing = '0'>".$tableHeader.$totalRow.$tableBody.$totalRow."</table>";
    echo "<h3>".__('Clientes asociados con este Usuario')."</h3>";
    echo $table;
    
    //$excelOutput.="<table id='clientes_asignados' cellpadding = '0' cellspacing = '0'>".$excelHeader.$excelTotalRow.$excelBody.$excelTotalRow."</table>";
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($user['UserLog'])){
		echo "<h3>Evento de Acceso</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>Fecha</th>";
					echo "<th>Evento</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($user['UserLog'] as $userLog){
				$createdDateTime=new DateTime($userLog['created']);
				echo "<tr>";
					echo "<td>".$createdDateTime->format('d-m-Y H:i:s')."</td>";
					echo "<td>".$userLog['event']."</td>";
				echo "</tr>";
			}
			echo "</tbody>";
		echo "</table>";
	}
?>

</div>
