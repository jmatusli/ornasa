<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>
<div class="clients asociarusariosplantas fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('UserPlant');
	echo "<fieldset>";
		echo $this->Form->input('role_id',['default'=>$selectedRoleId,'empty'=>[0=>'-- Seleccione Papel --']]);
    echo $this->Form->input('user_id',['label'=>'Usuario','default'=>$selectedUserId,'empty'=>[0=>'-- Seleccione Usuario --']]);
		echo $this->Form->input('plant_id',['label'=>'Plant','default'=>$selectedPlantId,'empty'=>[0=>'-- Seleccione Planta --']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Usuarios con Plantas')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesUsuariosPlantas'],['class' => 'btn btn-primary']); 
		echo "<p class='comment'>Cuando se cambia la asociación entre un usuario y una planta, se guardarán las asociaciones de todos usuarios con esta planta</p>";
    
    $excelOutput='';
    
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Users')."</th>";
        foreach ($selectedPlants as $plantId=>$plantValue){
          $tableHead.="<th>".$this->Html->link($plantValue,['controller'=>'plants','action'=>'detalle',$plantId])."</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.="<th>".__('User')."</th>";
        foreach ($selectedPlants as $plantId=>$plantValue){
          $excelHead.="<th>".$plantValue."</th>";
        }
      $excelHead.="</tr>";
    $excelHead.="</thead>";
    
    
    foreach ($selectedRoles as $selectedRole){
      if (empty($selectedRole['User'])){
        echo '<h2>No hay usuarios (seleccionados) para papel '.$selectedRole['Role']['name'].'</h2>';
      }
      else {
        echo '<h2>Asignaciones para papel '.$selectedRole['Role']['name'].'</h2>';
      
        $tableBody="<tbody>";
        for ($u=0;$u<count($selectedRole['User']);$u++){
          //pr($selectedRole['User'][$u]);
          $tableBody.="<tr>";
            $tableBody.="<td>";
              $tableBody.=$this->Html->link($selectedRole['User'][$u]['username'],['controller'=>'users','action'=>'view',$selectedRole['User'][$u]['id']]);
              $tableBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
            $tableBody.="</td>";
            if (empty($selectedRole['User'][$u]['Plant'])){
            foreach ($selectedPlants as $plantId=>$plantValue){
                $tableBody.="<td>";
                  $tableBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.Plant.'.$plantId.'.bool_assigned',[
                    'type'=>'checkbox',
                    'label'=>false,
                    'checked'=>false,
                    'class'=>'assignment',
                  ]);
                $tableBody.="</td>";
              }
            }
            else {
              foreach ($selectedPlants as $plantId=>$plantValue){
                $tableBody.="<td>";
                  $tableBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.Plant.'.$plantId.'.bool_assigned',[
                    'type'=>'checkbox',
                    'label'=>false,
                    'checked'=>$selectedRole['User'][$u]['Plant'][$plantId],
                    'class'=>'assignment',
                  ]);
                $tableBody.="</td>";
              }
            }
          $tableBody.="</tr>";			
        }
        $tableBody.="</tbody>";
        $excelBody="</tbody>";
        $excelBody="<tbody>";
        for ($u=0;$u<count($selectedRole['User']);$u++){
          //pr($selectedRole['User'][$u]);
          $excelBody.="<tr>";
            $excelBody.="<td>";
              $excelBody.=$this->Html->link($selectedRole['User'][$u]['username'],['controller'=>'users','action'=>'view',$selectedRole['User'][$u]['id']]);
              $excelBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
            $excelBody.="</td>";
            if (empty($selectedRole['User'][$u]['Plant'])){
              foreach ($selectedPlants as $plantId=>$plantValue){
                $excelBody.="<td>0</td>";
              }
            }
            else {
              foreach ($selectedPlants as $plantId=>$plantValue){
                $excelBody.="<td>".($selectedRole['User'][$u]['Plant'][$plantId]?"1":"0")."</td>";
              }
            }
          $excelBody.="</tr>";			
        }
        $excelBody.="</tbody>";
        $table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
        echo $table;
        $excelOutput.="<table cellpadding='0' cellspacing='0' id='asoc_usuario_planta'>".$excelHead.$excelBody."</table>";
      }
    }  
    $_SESSION['resumenAsociacionesUsuariosPlantas'] = $excelOutput;
   
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
