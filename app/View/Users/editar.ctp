<script>	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
  
  $('body').on('change','#UserRoleId',function(){	
		var userRoleId=$(this).val();
    if (userRoleId == <?php echo ROLE_CLIENT; ?>){
      $('#UserClientId').closest('div').removeClass('hidden');
      $('.clients').addClass('hidden');
    }
    else {
      $('#UserClientId').addClass('hidden');
      $('.clients').removeClass('hidden');
    }
	});
  
  $('body').on('change','#UserFirstName',function(){	
		updateAbbreviation();
	});
  $('body').on('change','#UserLastName',function(){	
		updateAbbreviation();
	});
  function updateAbbreviation(){
    if (<?php echo $userRoleId == ROLE_ADMIN?1:0; ?> == 1){
      var firstNameThreeLetters=$('#UserFirstName').val().slice(0,3)
      var lastNameThreeLetters=$('#UserLastName').val().slice(0,3)
      $('#UserAbbreviation').val(firstNameThreeLetters + lastNameThreeLetters);
    }
  }
  
  $(document).ready(function(){
    $('#UserRoleId').trigger('change');
  });
</script>
<div class="users form fullwidth">
<?php 
  echo $this->Form->create('User'); 
	echo "<fieldset>";
		echo "<legend>".__('Edit User')."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo "<div class='col-sm-6'>";
					echo $this->Form->input('id');
          echo $this->Form->input('username');
          echo $this->Form->input('pwd',['value'=>'','required'=>false,'label'=>__('Password'),'type'=>'password']);
          echo $this->Form->input('role_id');
          echo $this->Form->input('first_name');
          echo $this->Form->input('last_name');
          echo $this->Form->input('abbreviation',['readonly'=>($userRoleId != ROLE_ADMIN)]);
          echo $this->Form->input('email');
          echo $this->Form->input('phone');
          echo $this->Form->input('bool_active',['div'=>['class'=>'checkboxleft']]);
          echo $this->Form->input('bool_show_in_list',['label'=>'Mostrar en listas','div'=>['class'=>'checkboxleft']]);
          echo $this->Form->input('bool_view_all_users',['label'=>'Puede ver todos usuarios','div'=>['class'=>'checkboxleft']]);
          
          echo $this->Form->input('client_id',['empty'=>['--Seleccione cliente--']]);
          
          echo $this->Form->Submit(__('Submit'));
        echo "</div>";
				echo "<div class='clients col-sm-6'>";
          echo '<div class="actions col-sm-12">';
          echo '<h3>Acciones</h3>';
          echo '<ul>';
            echo '<li>'.$this->Html->link(__('List Users'), ['action' => 'resumen']).'</li>';
            echo '<br/>';
            if ($userRoleId == ROLE_ADMIN) { 	
              echo '<li>'.$this->Html->link(__('List User Logs'), ['controller' => 'user_logs', 'action' => 'index']).'</li>';
            } 	
          echo '</ul>';
          echo '</div>';
          echo "<div class='clients col-sm-6'>";
            echo "<h3>Clientes Ya Asociados</h3>";
            echo '<ul style="list-style-type:none;">';
            foreach ($clients as $clientId=>$clientName){
              if (in_array($clientId,$clientListAssociatedWithUser)){
                echo "<li>".$this->Html->Link($clientName,['controller'=>'clients','action'=>'view',$clientId])."</li>";
              }
            }
            echo '</ul>';
          echo "</div>";
          echo "<div class='clients col-sm-6'>";
            echo "<h3>Asociar con Clientes</h3>";
            foreach ($clients as $clientId => $clientName){
              $clientChecked=false;
              if (in_array($clientId,$clientListAssociatedWithUser)){
                $clientChecked=true;
              }
              echo $this->Form->input('Client.'.$clientId,['type'=>'checkbox','checked'=>$clientChecked,'label'=>$clients[$clientId],'div'=>['class'=>'checkboxleftbig']]);
            }
          echo "</div>";
				echo "</div>";		
			echo "</div>";	
		echo "</div>";  
	echo "</fieldset>";
  echo $this->Form->end();
?>
</div>
