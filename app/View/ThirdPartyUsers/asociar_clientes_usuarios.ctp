<script>
  var jsClientOwners=<?php echo json_encode($clientOwners); ?>;

	$('body').on('change','.owner',function(){
		var clientId=$(this).closest('tr').attr('clientid');
    $(this).closest('tr').find('.changed').val(1);
      
    if ($(this).is(":checked")){
      $(this).closest('td').css('background-color','#46cccc');
      $(this).closest('tr').find('.association').prop('checked',true);
      if (jsClientOwners[clientId] != 0 && jsClientOwners[clientId] != <?php echo $selectedUserId; ?>){
        $(this).closest('tr').find('td[userid="'+jsClientOwners[clientId]+'"]').css('background-color','#f4aa74')
      }
    }
    else {
      $(this).closest('td').css('background-color','#ffffff');
      $(this).closest('tr').find('.association').prop('checked',false);
      if (jsClientOwners[clientId] != 0 && jsClientOwners[clientId] != <?php echo $selectedUserId; ?>){
        $(this).closest('tr').find('td[userid="'+jsClientOwners[clientId]+'"]').css('background-color','#ffffff')
      }
    }
	});
  $('body').on('change','.association',function(){
		$(this).closest('tr').find('.changed').val(1);
	});
</script>

<div class="clients asociarclientesusuarios fullwidth" style="overflow-x:auto">
<?php 
	echo $this->Form->create('ThirdPartyUser');
	echo "<fieldset>";
    echo '<legend id="formLegend">'.($selectedUserId > 0?(" Asociar Clientes con Usuario ".$users[$selectedUserId]):"Asociaciones entre Clientes y Usuarios").'</legend>';
		
    echo $this->Form->input('user_id',['label'=>'Usuario','default'=>$selectedUserId,'empty'=>[0=>'-- Todos Usuarios --']]);
		echo $this->Form->input('plant_id',['label'=>'Planta','default'=>$plantId,'empty'=>[0=>'-- Todas Plantas --']]);
    echo $this->Form->input('client_option_id',['label'=>'Mostrar Clientes','default'=>$clientOptionId]);
		
    echo $this->Form->Submit(__('Actualizar'),['id'=>'refresh','name'=>'refresh']);
		
    
    echo "<br/>";
    echo $this->Html->link(__('Guardar como Excel'),['action' => 'guardarAsociacionesClientesUsuarios'],['class' => 'btn btn-primary']); 
		echo '<p class="comment">Seleccione un usuario para asociar clientes.</p>';
    echo '<p class="comment">Cuando hay un usuario seleccionado, se puede indicar para este usuario si está el responsable principal del cliente (la primera columna con borde azul) o si está meramente asociado (segunda columna).  Si se marca la columna de responsable principal, automaticamente se seleccionará la columna de usuario asociado.</p>';
    echo '<p class="comment">Para usuarios no seleccionados se muestra si el usuario está asociado con el cliente (marcado con un X) o no (marcado con un -); si el usuario es responsable principal, el X tiene un<span style="display:inline-block;border:2px solid blue;"> borde azul </span>.</p>';
    echo '<p class="comment">Si se asigna el usuario seleccionado como responsable principal para un cliente, y este cliente ya tiene otro usuario como responsable principal, se removerá el otro usuario como responsable principal, aunque seguirá como asociado.  Se indica este cambio con un<span style="background-color:#f4aa74"> fondo anaranjado </span>para señalar el cambio.</p>';
		
    $tableHead="";
    $tableHead.="<thead>";
      if ($selectedUserId > 0){
        $tableHead.='<tr>';
          $tableHead.='<th></th>';
          $tableHead.='<th colspan="2">'.$users[$selectedUserId].'</th>';
          $tableHead.='<th colspan="'.(count($users)-1).'"></th>';
        $tableHead.='</tr>';
      }
      $tableHead.="<tr>";
        $tableHead.="<th>Cliente</th>";
        if ($selectedUserId > 0){
          $tableHead.='<th style="background-color:#f7de5c">Responsable</th>';
          $tableHead.='<th style="background-color:#f7de5c">Asociado</th>';
          //$tableHead.='<th style="background-color:#008888">'.$this->Html->link($users[$selectedUserId],['controller'=>'users','action'=>'view',$selectedUserId]).'</th>';
        }
         
        foreach ($users as $currentUserId=>$userValue){
          if ($currentUserId != $selectedUserId){
            $tableHead.="<th>".$this->Html->link($userValue,['controller'=>'users','action'=>'view',$currentUserId])."</th>";
          }
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
    $excelHead="";
    $excelHead.="<thead>";
      $excelHead.="<tr>";
        $excelHead.="<th>Cliente</th>";
        if ($selectedUserId > 0){
          $excelHead.="<th>Dueño</th>";
          $excelHead.="<th>Asociado</th>";
          //$excelHead.='<th>'.$users[$selectedUserId].'</th>';
        }
        foreach ($users as $currentUserId=>$userValue){
          if ($currentUserId !== $selectedUserId){
            $excelHead.="<th>".$userValue."</th>";
          }
        }
      $excelHead.="</tr>";
    $excelHead.="</thead>";
    
    $tableBody="<tbody>";
    
    foreach ($clients as $clientId =>$clientName){
      //if ($clientId == 68){
      //  pr( $clientUserAssociations[$clientId]);
      //}
      $tableBody.='<tr clientid="'.$clientId.'">';
        $tableBody.="<td>";
          $tableBody.=$this->Html->link($clientName,['controller'=>'thirdParties','action'=>'verCliente',$clientId]);
          $tableBody.=$this->Form->input('Client.'.$clientId.'.bool_changed',['type'=>'hidden','label'=>false,'value'=>0,'class'=>'changed']);
        $tableBody.="</td>";
        if ($selectedUserId > 0){
        
          if ($clientOwners[$clientId] == $selectedUserId){
            $tableBody.='<td style="border:2px solid blue;background-color:#46cccc;">';  
          }
          else {
            $tableBody.='<td style="border:2px solid blue;">';
          }
            $tableBody.=$this->Form->input('Client.'.$clientId.'.User.'.$selectedUserId.'.bool_owner',[
            'type'=>'checkbox',
            'label'=>false,
            'checked'=>($clientOwners[$clientId] == $selectedUserId),
            'style'=>'width:20px;',
            'class'=>'owner',
          ]);
          $tableBody.='</td>';
          $tableBody.='<td>'.$this->Form->input('Client.'.$clientId.'.User.'.$selectedUserId.'.bool_association',[
            'type'=>'checkbox',
            'label'=>false,
            'checked'=>(
              array_key_exists($clientId,$clientUserAssociations)?
              (
                array_key_exists($selectedUserId,$clientUserAssociations[$clientId]['Users'])?
                $clientUserAssociations[$clientId]['Users'][$selectedUserId]:
                false
              ):
              false
            ),
            'style'=>'width:20px;',
            'class'=>'association',
          ]);
          $tableBody.='</td>';
        }
        foreach ($users as $currentUserId=>$userValue){
          if ($currentUserId != $selectedUserId){
            if ($clientOwners[$clientId] == $currentUserId){
              $tableBody.='<td style="border:2px solid blue;text-align:center;"  userid="'.$currentUserId.'">';  
            }
            else {
              $tableBody.='<td style="text-align:center;" userid="'.$currentUserId.'">';
            }
              $tableBody.=(
                array_key_exists($clientId,$clientUserAssociations)?
              (
                array_key_exists($currentUserId,$clientUserAssociations[$clientId]['Users'])?
                (
                  $clientUserAssociations[$clientId]['Users'][$currentUserId]?
                  "X":
                  "-"
                ):
                "-"
              ):
              "-"
            );
            $tableBody.="</td>";
          }
        }
      $tableBody.="</tr>";			
		}
    
		$tableBody.="</tbody>";
    $table='<table>'.$tableHead.$tableBody.'</table>';
    echo $table;
    echo $this->Form->Submit(__('Submit'),['id'=>'submit','name'=>'submit']);	
    echo "</fieldset>";
	echo $this->Form->End();

  $excelTable='<table id="asoc_cliente_vendedor">'.$excelHead.$tableBody.'</table>';
  $_SESSION['resumenAsociaciones'] = $excelTable;

?>
</div>
