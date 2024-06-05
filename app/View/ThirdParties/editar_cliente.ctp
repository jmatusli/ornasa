<script>
  $('body').on('change','#ThirdPartyCreditDays',function(){
    setDisplayCreditAmount();
  });

  function setDisplayCreditAmount(){
    var creditDays=0;
    if (!isNaN($('#ThirdPartyCreditDays').val())){
      creditDays=parseInt($('#ThirdPartyCreditDays').val());
    }
    if (creditDays>0){
      $('#ThirdPartyCreditAmount').closest('div').show();
     
    }
    else {
      $('#ThirdPartyCreditAmount').val(0);
      $('#ThirdPartyCreditAmount').closest('div').hide();
     
    }
  }
  
  $(document).ready(function(){
		setDisplayCreditAmount();	
    <?php if ($userrole!=ROLE_ADMIN&&$userrole!=ROLE_ASSISTANT) { ?>
      $('#VendorList').addClass('hidden');
    <?php } ?>
	});
  
</script>
<div class="thirdParties fullwidth">
<?php 
	echo $this->Form->create('ThirdParty');
	echo "<fieldset>";
		echo "<legend>".__('Edit Client')." ".$this->request->data['ThirdParty']['company_name']."</legend>";
    echo "<div class='container-fluid'>";
      echo '<div class="row">';	
        echo "<div class='col-sm-4'>";
          echo '<h3>Datos de cliente</h3>';
          echo $this->Form->input('id',['hidden'=>'hidden']);
          // echo $this->Form->input('bool_provider');
          //echo $this->Form->input('plant_id',['value'=>$plantId]);
          echo $this->Form->input('company_name');
          echo $this->Form->input('bool_active');
          echo $this->Form->input('bool_generic',['label'=>'Cliente genérico','div'=>['class'=>'div input checkbox checkboxleftbig']]);
           echo $this->Form->input('price_client_category_id',['empty'=>['0'=>'-- Categoría Precio --']]);
          echo $this->Form->input('accounting_code_id',['empty'=>['0'=>__('Select Accounting Code')]]);
          if ($userRoleId==ROLE_ADMIN){
            echo $this->Form->input('credit_days');
          }
          else {
            echo $this->Form->input('credit_days',['readonly'=>'readonly']);
          }
          echo $this->Form->input('credit_amount');
          echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
          echo $this->Form->input('expiration_rate',['label'=>'Tasa de expiración (%)']);
          //echo "<div class='input text'><label for='LocationId'>Nombre</label>".$combobox->create('location_id', '/thirdParties/autoComplete', ['comboboxTitle' => "View Locations"])."</div>"; 
          
          echo $this->Form->input('client_type_id',['empty'=>[0=>'-- Tipo de Cliente --']]);
          echo $this->Form->input('zone_id',['empty'=>[0=>'-- Zona --']]);
        
          echo $this->Form->input('first_name');
          echo $this->Form->input('last_name');
          echo $this->Form->input('email');
          echo $this->Form->input('phone');
          echo $this->Form->input('address');
          echo $this->Form->input('ruc_number');
        echo "</div>";
        echo '<div class="col-sm-3">';		
          echo "<h3>Usuarios Ya Asociados</h3>";
          if (empty($usersAssociatedWithClient)){
            echo "<p>No hay usuarios asociados con este cliente aun</p>";
          }
          else {
            echo "<ul>";
            foreach ($usersAssociatedWithClient as $user){
              echo "<li>".$this->Html->Link((!empty($user['User']['first_name'])?$user['User']['first_name']." ".$user['User']['last_name']:$user['User']['username']),array('controller'=>'user','action'=>'view',$user['User']['id']))."</li>";
            }
            echo "</ul>";
          }
          echo "<div id='VendorList'>";
            echo "<h3>Vendedores Relacionados</h3>";
            for ($u=0;$u<count($users);$u++){
              $userChecked=false;
              if (!empty($users[$u]['ThirdPartyUser'])){
                //pr($users[$u]['ThirdPartyUser']);
                $userChecked=$users[$u]['ThirdPartyUser'][0]['bool_assigned'];
              }
              echo $this->Form->input('User.'.$u.'.id',[
                'type'=>'checkbox',
                'checked'=>$userChecked,
                'label'=>(!empty($users[$u]['User']['first_name'])?$users[$u]['User']['first_name']." ".$users[$u]['User']['last_name']:$users[$u]['User']['username']),
                'div'=>['class'=>'checkboxleftbig'],
              ]);
            }
          echo '</div>';	  
        echo '</div>';
        echo '<div class="col-sm-3">';		
          echo '<div id="PlantList">';
            echo "<h3>Plantas Asociadas</h3>";
            //pr($plants);
            for ($p=0;$p<count($plants);$p++){     
              $plantChecked=false;
              if (!empty($plants[$p]['PlantThirdParty'])){
                //pr($plants[$p]['PlantThirdParty']);
                $plantChecked=$plants[$p]['PlantThirdParty'][0]['bool_assigned'];
              }
              echo $this->Form->input('Plant.'.$p.'.id',[
                'type'=>'checkbox',
                'checked'=>$plantChecked,
                'label'=>$plants[$p]['Plant']['name'],
                'div'=>['class'=>'checkboxleftbig'],
              ]);
            }
          echo '</div>';	  
        echo '</div>';
        echo '<div class="col-sm-2">';
          echo '<h3>'.__('Actions').'</h3>';
          echo '<ul style="list-style:none;">';
          if ($userRoleId == ROLE_ADMIN){
            echo '<li>'.$this->Html->link(__('Precios de Productos para Cliente'), ['controller' => 'productPriceLogs', 'action' => 'registrarPreciosCliente',$this->request->data['ThirdParty']['id']]).'</li>';
          }              
            echo "<li>".$this->Html->link(__('List Clients'), array('action' => 'resumenClientes'))."</li>";
            echo "<br/>";
          if ($bool_saleremission_index_permission) {
            echo '<li>'.$this->Html->link(__('Todas Ventas y Remisiones'), ['controller' => 'orders', 'action' => 'resumenVentasRemisiones']).'</li>';
          }
          if ($bool_sale_add_permission) {
            echo "<li>".$this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'crearVenta'))."</li>";
          }
          if ($bool_remission_add_permission) {
            echo "<li>".$this->Html->link(__('New Remission'), array('controller' => 'orders', 'action' => 'crarRemision'))."</li>";
          }
          echo "</ul>";
        echo '</div>';        
			echo '</div>';
       echo '<div class="row">';		
        echo '<div class="col-sm-6">';		
          echo $this->Form->Submit('Solo Grabar Cliente (Sin grabar precios)',['id'=>'guardarCliente','name'=>'guardarCliente','style'=>'min-width:400px;']); 
        echo '</div>';  
        echo '<div class="col-sm-6">';		
          echo $this->Form->Submit('Grabar Cliente y Grabar Precios de Producto',['id'=>'guardarClienteYPrecios','name'=>'guardarClienteYPrecios','style'=>'min-width:400px;']); 
        echo '</div>';
      echo '</div>';      
    echo '</div>';	
	echo "</fieldset>";

	echo $this->Form->end(); 
?>
</div>
