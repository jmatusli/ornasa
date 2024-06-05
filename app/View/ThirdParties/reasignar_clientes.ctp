<script>
	$('body').on('change','.powerselector',function(e){
		if ($(this).is(':checked')){
			$(this).closest('fieldset').find('td.selector input').prop('checked',true);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',true);
		}
		else {
			$(this).closest('fieldset').find('td.selector input').prop('checked',false);
			$(this).closest('fieldset').find('input.powerselector').prop('checked',false);
		}
	});
	
	$('body').on('change','#ReassignDestinyUserId',function(){	
		var destinyUsers = [];
		$('#ReassignDestinyUserId').children("option").filter(":selected").each(function() {
			destinyUsers.push($(this).val());
        });
		$('.targetuser').val($('#ReassignDestinyUserId').val());
	});
	
	$(document).ready(function(){
		
	});
</script>
<div class="clients form fullwidth">
<?php 
	echo "<h2>".__('Reasignar Clientes')."</h2>";	
	echo $this->Form->create('Reassign'); 
	echo "<fieldset>";
		echo "<legend>".__('Parámetors de Reasignación')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-md-10'>";	
					echo $this->Form->input('Reassign.origin_user_id',['label'=>__('Usuario de Origen de quien se van a transferir los Clientes'),'default'=>$originUserId,'empty'=>['0'=>__('Seleccione un Usuario')]]);
					echo $this->Form->submit('Mostrar Clientes para Vendedor de Destino',['id'=>'showclients','name'=>'showclients','style'=>'width:400px;']); 					
					echo "<p class='comment'>Indica si quiere mantener el cliente para el usuario de origen o si quiere remover el vendedor de origen.</p>";
					$keepArray=[
						'0'=>'Remover el vendedor de origen para clientes reasignados',
						'1'=>'Mantener el vendedor de origen para clientes reasignados',
					];
					echo $this->Form->input('Reassign.bool_keep_origin',['label'=>'Mantener Vendedor de Origen','default'=>$boolKeepOrigin,'options'=>$keepArray]);
					echo "<p class='comment'>Seleccione el usuario de Destino cuando va a transferir todos los clientes al mismo vendedor.</p>";
					echo "<p class='comment'>Al seleccionar uno o más usuario de Destino todos destinatarios estarán remplazados por el cliente de destino.</p>";
					//if ($userrole==ROLE_ADMIN||$userrole==ROLE_ASSISTANT) { 
						echo $this->Form->input('Reassign.destiny_user_id',['label'=>__('Ejecutivo(s) de venta a quien se van a transferir los Clientes'),'default'=>$destinyUserArray,'empty'=>['0'=>__('Seleccione un Usuario')],'multiple'=>true,'lines'=>5]);
					//}					
				echo "</div>";
				echo "<div class='col-md-2'>";
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
						if ($bool_client_index_permission){
							echo "<li>".$this->Html->link(__('List Clients'), ['action' => 'index'])."</li>";
							echo "<br/>";
						}
						if ($bool_user_index_permission){
							echo "<li>".$this->Html->link(__('List Users'), ['action' => 'index'])."</li>";
							echo "<br/>";
						}
					echo "</ul>";
			echo "</div>";
			echo "<div class='row'>";	
				echo "<div class='col-md-12'>";
				if (empty($originUserId)){
					echo "<p>No está seleccionado el vendedor de origen.</p>";
				}
				else {
					echo "<h3>Clientes Asociados con Usuario de Origen</h3>";
					if (empty($clientsAssociatedWithUser)){
						echo "<p>No hay clientes asociados con el vendedor de origen</p>";
					}
					else {
						echo $this->Form->input('Reassign.powerselector1',['class'=>'powerselector','checked'=>true,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar todos clientes','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ]]);
						//for ($u=0;$u<count($users);$u++){
						//	$userChecked=false;
						//	if (!empty($users[$u]['ClientUser'])){
						//		$userChecked=true;
						//	}
						//	//pr($users[$u]);
						//	echo $this->Form->input('User.'.$u.'.id',['type'=>'checkbox','checked'=>$userChecked,'label'=>$users[$u]['User']['first_name']." ".$users[$u]['User']['last_name'],'div'=>['class'=>'checkboxleftbig']]);
						//}
						echo "<p class='comment'>Note que puede ser que clientes ya están asignados a otros vendedores aparte del usuario de origen.</p>";
						echo "<p class='comment'>Estos vendedores no estarán modificados, y se mantendrán como vendedores para el cliente.</p>";
						echo "<table>";
							echo "<thead>";
								echo "<th>Seleccionado</th>";
								echo "<th>Cliente</th>";
								echo "<th>Vendedor de Destino</th>";
							echo "</thead>";
							echo "<tbody>";
							foreach ($clientsAssociatedWithUser as $client){
								echo "<tr>";
									echo "<td class='selector'>".$this->Form->input('Reassign.Client.'.$client['ThirdParty']['id'].'.selector',['checked'=>true,'label'=>false])."</td>";
									echo "<td>".$this->Html->link($client['ThirdParty']['company_name'],['controller'=>'thirdParties','action'=>'verCliente',$client['ThirdParty']['id']])."</td>";
									echo "<td>".$this->Form->input('Reassign.Client.'.$client['ThirdParty']['id'].'.target_user_id',['label'=>false,'default'=>0,'class'=>'targetuser','empty'=>['0'=>__('Usuario(s) de Destino')],'multiple'=>true,'lines'=>5])."</td>";
								echo "</tr>";
							}
							echo "</tbody>";
						echo "</table>";
						echo $this->Form->input('Reassign.powerselector2',['class'=>'powerselector','checked'=>true,'style'=>'width:5em;','label'=>['text'=>'Seleccionar/Deseleccionar todos clientes','style'=>'padding-left:5em;'],'format' => ['before', 'input', 'between', 'label', 'after', 'error' ]]);
					}
				}
				echo "</div>";
			echo "</div>";
		echo "</div>";		
	echo "</fieldset>";
	echo $this->Form->submit('Reasignar Clientes',['id'=>'reassign','name'=>'reassign','style'=>'width:400px;']); 
	echo $this->Form->end(); 
?>
</div>
