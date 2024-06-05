<script>
	$('body').on('change','.assignment',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>
<div class="clients asociarusariosbodegas fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('UserWarehouse');
	echo "<fieldset>";
		echo "<p class='comment'></p>";
		echo $this->Form->input('user_id',['label'=>'Usuario','default'=>$selectedUserId,'empty'=>[0=>'Seleccione Usuario']]);
		echo $this->Form->input('warehouse_id',['label'=>'Warehouse','default'=>$selectedWarehouseId,'empty'=>[0=>'Seleccione Bodega']]);
		echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		echo "<legend>".__('Asociar Usuarios con Bodegas')."</legend>";
		echo $this->Form->Submit(__('Guardar'),['id'=>'submit','name'=>'submit']);	
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesUsuariosBodegas'],['class' => 'btn btn-primary']); 
		echo "<p class='comment'>Cuando se cambia la asociación entre un usuario y una bodega, se guardarán las asociaciones de todos usuarios con esta bodega</p>";
		
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
    
    $tableBody="<tbody>";
    for ($c=0;$c<count($selectedUsers);$c++){
      //pr($selectedUsers[$c]);
      $tableBody.="<tr>";
        $tableBody.="<td>";
          $tableBody.=$this->Html->link($selectedUsers[$c]['User']['username'],['controller'=>'users','action'=>'view',$selectedUsers[$c]['User']['id']]);
          $tableBody.=$this->Form->input('User.'.$selectedUsers[$c]['User']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $tableBody.="</td>";
        if (empty($selectedUsers[$c]['Warehouses'])){
        foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('User.'.$selectedUsers[$c]['User']['id'].'.Warehouse.'.$warehouseId.'.bool_assigned',['type'=>'checkbox','label'=>false,'checked'=>false,'class'=>'assignment']);
            $tableBody.="</td>";
          }
        }
        else {
          foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
            $tableBody.="<td>";
              $tableBody.=$this->Form->input('User.'.$selectedUsers[$c]['User']['id'].'.User.'.$userId.'.bool_assigned',['type'=>'checkbox','label'=>false,'checked'=>$selectedUsers[$c]['Warehouse'][$userId],'class'=>'assignment']);
            $tableBody.="</td>";
          }
        }
      $tableBody.="</tr>";			
		}
		$tableBody.="</tbody>";
    $excelBody="</tbody>";
    $excelBody="<tbody>";
    for ($c=0;$c<count($selectedUsers);$c++){
      //pr($selectedUsers[$c]);
      $excelBody.="<tr>";
        $excelBody.="<td>";
          $excelBody.=$this->Html->link($selectedUsers[$c]['User']['username'],['controller'=>'users','action'=>'view',$selectedUsers[$c]['User']['id']]);
          $excelBody.=$this->Form->input('User.'.$selectedUsers[$c]['User']['id'].'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $excelBody.="</td>";
        if (empty($selectedUsers[$c]['Warehouse'])){
          foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
            $excelBody.="<td>0</td>";
          }
        }
        else {
          foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
            $excelBody.="<td>".($selectedUsers[$c]['Warehouse'][$warehouseId]?"1":"0")."</td>";
          }
        }
      $excelBody.="</tr>";			
		}
		$excelBody.="</tbody>";
		$table="<table cellpadding='0' cellspacing='0'>".$tableHead.$tableBody."</table>";
    echo $table;
    $excelTable="<table cellpadding='0' cellspacing='0' id='asoc_usuario_bodega'>".$excelHead.$excelBody."</table>";
    $_SESSION['resumenAsociacionesUsuariosBodegas'] = $excelTable;
   
	echo "</fieldset>";
	echo $this->Form->Submit(__('Guardar'));
	echo $this->Form->End();

?>
</div>
