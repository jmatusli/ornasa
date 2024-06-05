<div class="users index fullwidth">
<?php 
	echo "<h1>".__('Users')."</h1>";
  echo $this->Form->create('Report');
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-9">';
        echo "<fieldset>";
          echo $this->Form->input('Report.role_id',['label'=>'Papel','default'=>$roleId,'empty'=>[0=>'-- Seleccione Papel--']]);
        echo "</fieldset>";
      echo "<br/>";
      echo $this->Form->end(__('Refresh'));
      echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenUsuarios'], [ 'class' => 'btn btn-primary']);      
      echo '</div>';
      echo '<div class="col-sm-3">';
        echo '<h3>Acciones</h3>';
        echo '<ul>'; 
          if ($bool_add_permission) {
            echo '<li>'.$this->Html->link(__('New User'), ['action' => 'crear']).'</li>'; 
            echo '<br/>';
          }	
          if ($userRoleId == ROLE_ADMIN) {
            echo '<li>'.$this->Html->link(__('List User Logs'), ['controller' => 'user_logs', 'action' => 'index']).'</li>';
          }	
        echo '</ul>';
      echo '</div>';
    echo '</div>';
  echo '</div>';
  
		
  echo '<p class="comment">Usuarios desactivados aparecen <i>en cursivo</i></p>';
  $excelOutput='';
  if (!empty($allRoles)){
    foreach ($allRoles as $role){
      if (empty($role['User'])){
        echo '<h2>No hay usuarios con papel '.($this->Html->link($role['Role']['name'], ['controller' => 'roles', "action" => 'view', $role['Role']['id']])).'</h2>';
      }
      else {
        echo '<h2>Usuarios con papel '.($this->Html->link($role['Role']['name'], ['controller' => 'roles', 'action' => 'view', $role['Role']['id']])).'</h2>';
        $excelTableHeadRow='';
        $excelTableHeadRow.='<tr>';
          $excelTableHeadRow.='<th style="width:15%;">Usuario</th>';
          $excelTableHeadRow.='<th style="width:15%;">Nombre</th>';
          $excelTableHeadRow.='<th style="width:15%;">Apellido</th>';
          $excelTableHeadRow.='<th style="width:10%;">Abreviación</th>';
          $excelTableHeadRow.='<th style="width:15%;">Correo electrónico</th>';
          $excelTableHeadRow.='<th style="width:15%;">Teléfono</th>';
          
          $tableHeadRow=$excelTableHeadRow;
          $tableHeadRow.='<th class="actions">'. __("Actions").'</th>';
        $excelTableHeadRow.='</tr>';
        $tableHeadRow.='</tr>';
        $excelTableHead='<thead>'.$excelTableHeadRow.'</thead>';
        $tableHead='<thead>'.$tableHeadRow.'</thead>';
        
        $excelTableBodyRows=$tableBodyRows='';
        foreach ($role['User'] as $user){
          $excelTableBodyRow=$tableBodyRow='';
          if ($user['bool_active']){
            $tableBodyRow.='<tr>';
          }
          else {
            $tableBodyRow.='<tr class="italic">';
          }
            $tableBodyRow.='<td>'. $this->Html->link($user['username'],['action'=>'view',$user['id']]).'</td>';
            $tableBodyRow.='<td>'. h($user['first_name']).'&nbsp;</td>';
            $tableBodyRow.='<td>'. h($user['last_name']).'&nbsp;</td>';
            $tableBodyRow.='<td>'. h($user['abbreviation']).'&nbsp;</td>';
            $tableBodyRow.='<td>'. h($user['email']).'&nbsp;</td>';
            $tableBodyRow.='<td>'. h($user['phone']).'&nbsp;</td>';
            $tableBodyRow.='<td class="actions">';
              if ($bool_edit_permission){
                $tableBodyRow.=$this->Html->link(__('Edit'), ['action' => 'editar', $user['id']]); 
              } 
            $tableBodyRow.='</td>';
          $tableBodyRow.='</tr>';
          $tableBodyRows.=$tableBodyRow;
        
          $excelTableBodyRow.='<tr>';
            $excelTableBodyRow.='<td>'. h($user['username']).'</td>';
            $excelTableBodyRow.='<td>'. h($user['first_name']).'&nbsp;</td>';
            $excelTableBodyRow.='<td>'. h($user['last_name']).'&nbsp;</td>';
            $excelTableBodyRow.='<td>'. h($user['email']).'&nbsp;</td>';
            $excelTableBodyRow.='<td>'. h($user['phone']).'&nbsp;</td>';
          $excelTableBodyRow.='</tr>';
          $excelTableBodyRows.=$excelTableBodyRow;
        }
        $excelTableBody='<tbody>'.$excelTableBodyRows.'</tbody>';
        $tableBody='<tbody>'.$tableBodyRows.'</tbody>';
        
        $excelTable='<table cellpadding="0" cellspacing="0" id="'.$role['Role']['name'].'">'.$excelTableHead.$excelTableBody.'</table>';
        $table='<table id="'.$role['Role']['name'].'">'.$tableHead.$tableBody.'</table>';
        
        echo $table;
        $excelOutput.=$excelTable;
      }  
    }
  }
  
  $_SESSION['resumenUsuarios'] = $excelOutput;
?>
</div>
