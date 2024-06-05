<script>
	$('body').on('change','input[type=text]',function(){	
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
    var firstNameThreeLetters=$('#UserFirstName').val().slice(0,3)
    var lastNameThreeLetters=$('#UserLastName').val().slice(0,3)
    $('#UserAbbreviation').val(firstNameThreeLetters + lastNameThreeLetters);
  }
  
  $(document).ready(function(){
    $('#UserRoleId').trigger('change');
  });
</script>
<div class="users form">
<?php 
  echo $this->Form->create('User'); 
	echo "<fieldset>";
		echo "<legend>".__('Add User')."</legend>";
    echo "<div class='container-fluid'>";
			echo "<div class='row'>";	
				echo "<div class='col-md-6'>";				
          echo $this->Form->input('username');
          echo $this->Form->input('password');
          echo $this->Form->input('role_id',['default'=>$roleId,'empty'=>[0=>'-- Papel --']]);
          echo $this->Form->input('first_name');
          echo $this->Form->input('last_name');
          echo $this->Form->input('abbreviation');
          echo $this->Form->input('email');
          echo $this->Form->input('phone');
          
          echo $this->Form->input('bool_active',['value'=>1,'type'=>'hidden']);
          
          echo $this->Form->input('bool_show_in_list',['label'=>'Mostrar en listas','default'=>1,'div'=>['class'=>'checkboxleft']]);
          echo $this->Form->input('bool_view_all_users',['label'=>'Puede ver todos usuarios','default'=>0,'div'=>['class'=>'checkboxleft']]);
          echo $this->Form->input('client_id',['default'=>0,'empty'=>['--Seleccione cliente--'],'div'=>['id'=>'clientDiv']]);
          
          echo $this->Form->Submit(__('Submit'));
        echo "</div>";	
				echo "<div class='col-md-6 clients'>";		
          echo "<div id='ClientList' style='width:45%;float:left;clear:none;'>";
          echo "<h3>Clientes Relacionados</h3>";
          foreach ($clients as $clientId=>$clientName){
            echo $this->Form->input('Client.'.$clientId.'',['type'=>'checkbox','default'=>false,'label'=>$clientName,'div'=>['class'=>'checkboxleftbig']]);
          }
        echo "</div>";
      echo "</div>";
    echo "</div>";  
	echo "</fieldset>";
  echo $this->Form->end();
?>  
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'resumen')); ?></li>
		<br/>
		<?php if ($userrole==ROLE_ADMIN) { ?>	
		<li><?php echo $this->Html->link(__('List User Logs'), array('controller' => 'user_logs', 'action' => 'index')); ?> </li>
		<?php } ?>	
	</ul>
</div>
