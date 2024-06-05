<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>
<div class="clients asociarusariosbodegas fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('UserWarehouse');
	echo "<fieldset>";
		echo $this->Form->input('role_id',['default'=>$selectedRoleId,'empty'=>[0=>'-- Seleccione Papel --']]);
    echo $this->Form->input('user_id',['label'=>'Usuario','default'=>$selectedUserId,'empty'=>[0=>'-- Seleccione Usuario --']]);
		echo $this->Form->input('warehouse_id',['label'=>'Warehouse','default'=>$selectedWarehouseId,'empty'=>[0=>'-- Seleccione Bodega --']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Usuarios con Bodegas')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesUsuariosBodegas'],['class' => 'btn btn-primary']); 
		echo "<p class='comment'>Cuando se cambia la asociación entre un usuario y una bodega, se guardarán las asociaciones de todos usuarios con esta bodega</p>";
    
    $excelOutput='';
    
    $tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Users')."</th>";
        foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
          $tableHead.="<th>".$this->Html->link($warehouseValue,['controller'=>'warehouses','action'=>'view',$warehouseId])."</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.="<th>".__('User')."</th>";
        foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
          $excelHead.="<th>".$warehouseValue."</th>";
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
            if (empty($selectedRole['User'][$u]['Warehouse'])){
            foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
                $tableBody.="<td>";
                  $tableBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.Warehouse.'.$warehouseId.'.bool_assigned',[
                    'type'=>'checkbox',
                    'label'=>false,
                    'checked'=>false,
                    'class'=>'assignment',
                  ]);
                $tableBody.="</td>";
              }
            }
            else {
              foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
                $tableBody.="<td>";
                  $tableBody.=$this->Form->input('User.'.$selectedRole['User'][$u]['id'].'.Warehouse.'.$warehouseId.'.bool_assigned',[
                    'type'=>'checkbox',
                    'label'=>false,
                    'checked'=>$selectedRole['User'][$u]['Warehouse'][$warehouseId],
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
            if (empty($selectedRole['User'][$u]['Warehouse'])){
              foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
                $excelBody.="<td>0</td>";
              }
            }
            else {
              foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
                $excelBody.="<td>".($selectedRole['User'][$u]['Warehouse'][$warehouseId]?"1":"0")."</td>";
              }
            }
          $excelBody.="</tr>";			
        }
        $excelBody.="</tbody>";
        $table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
        echo $table;
        $excelOutput.="<table cellpadding='0' cellspacing='0' id='asoc_usuario_bodega'>".$excelHead.$excelBody."</table>";
      }
    }  
    $_SESSION['resumenAsociacionesUsuariosBodegas'] = $excelOutput;
   
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
