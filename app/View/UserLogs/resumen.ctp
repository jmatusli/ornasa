<script>
	$(document).ready(function(){
	
  });
</script>

<div class="userLogs resumen fullwidth">
<?php 
	echo "<h1>Logs de usuario</h1>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-sm-8 col-lg-6'>";		
        echo $this->Form->create('Report');
          echo "<fieldset>";
            echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2020,'maxYear'=>date('Y')]);
            echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2020,'maxYear'=>date('Y')]);
            echo "<br/>";
            //echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
            if ($userRoleId == ROLE_ADMIN) { 
              echo $this->Form->input('Report.user_id',['label'=>'Usuario','default'=>$userId,'empty'=>['0'=>'-- Todos Usuarios --']]);
            }
            else {
              echo $this->Form->input('Report.user_id',['label'=>__('Mostrar Usuario'),'options'=>$users,'default'=>$userId]);
            }
          echo "</fieldset>";
          echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
          echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
        echo "<br/>";	
        echo $this->Form->submit(__('Refresh'),['name'=>'refresh', 'id'=>'refresh','div'=>['class'=>'submit']]); 
        echo "<br/>";	
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], [ 'class' => 'btn btn-primary']);
      echo "</div>";
      echo "<div class='col-sm-4 col-lg-6'>";		
        //echo "<h3>".__('Actions')."</h3>";
        //echo '<ul style="list-style-type:none">';
        //echo "</ul>";
      echo "</div>";
    echo "</div>";
  echo "</div>";  
	$startDateTime=new DateTime($startDate);
	$endDateTime=new DateTime($endDate);
?> 
</div>
<div class='actions'>
<?php 
	
?>
</div>
<div style='clear:left;'>
<?php
  echo "<br/>";
  
  $tableHeader="";
  $tableHeader.="<thead>";
    $tableHeader.="<tr>";
      $tableHeader.="<th>Usuario</th>";
      $tableHeader.="<th>Fecha de log</th>";
      $tableHeader.="<th>Log Evento</th>";
    $tableHeader.="</tr>";
  $tableHeader.="</thead>";
      
  $pageBody="";
  
  foreach ($userLogs as $userLog){ 
    $eventDateTime=new DateTime($userLog['UserLog']['created']);
      
    $pageRow="";
    $pageRow.="<td>".$userLog['UserLog']['username']."</td>";
    $pageRow.="<td>".$eventDateTime->format('d-m-Y H:i:s')."</td>";
    
    $pageRow.="<td>".$userLog['UserLog']['event']."</td>";
    
    $pageBody.="<tr>".$pageRow."</tr>";
      
    
  }
  $pageBody="<tbody>".$pageBody."</tbody>";
  $table_id="Logs de Usuario";
  $userLogTable="<table id='".$table_id."'>".$tableHeader.$pageBody."</table>";
  echo $userLogTable;
  echo $this->Form->end(); 


  $_SESSION['resumen'] = $userLogTable;
  
?>
</div>