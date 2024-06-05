<style>
  tr.exactclient td.clientname {
    background-color:#a8e6f4;
  }
  
  td.proposed {
    background-color:#d9f4a8;
  }
  td.minority {
    background-color:#f2d091;
  }
  td.empty,
  td.empty input {
    background-color:#888888;
  }
  
  td.modified {
    background-color:yellow;
  }
  
  .switch {
    position: relative;
    display: inline-block;
    width: 320px!important;
    height: 34px;
  }

  /* Hide default HTML checkbox */
  .switch input {
    opacity: 0;
    width: 100%!important;
    height: 0;
  }

  /* The slider */
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
    line-height:34px;
    text-align:center;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }

  input:checked + .slider {
    background-color: #2196F3;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
  }

  input:checked + .slider:before {
    -webkit-transform: translateX(274px);
    -ms-transform: translateX(274px);
    transform: translateX(274px);
  }

  /* Rounded sliders */
  .slider.round {
    border-radius: 34px;
  }

  .slider.round:before {
    border-radius: 50%;
  }
  
</style>
<script>
  var jsRegisteredClients=<?php echo json_encode($similarRegisteredClients); ?>;
  
  $('body').on('click','tr:not(.totalrow) td.clientphone',function(){
    setClientPhone($(this).find('div input').val());
  });
  function setClientPhone(clientPhone){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.clientphone').text(clientPhone);
      if (clientPhone == "<?php echo $proposedClientData['client_phone']; ?>"){
        $(this).find('td.clientphone').removeClass('modified');
      }
      else {
        $(this).find('td.clientphone').addClass('modified');
      }
    });
    $('#ThirdPartyPhone').val(clientPhone);
  }
  
  $('body').on('click','tr:not(.totalrow) td.clientemail',function(){
    setClientEmail($(this).find('div input').val());
  });
  function setClientEmail(clientEmail){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.clientemail').text(clientEmail);
      if (clientEmail == "<?php echo $proposedClientData['client_email']; ?>"){
        $(this).find('td.clientemail').removeClass('modified');
      }
      else {
        $(this).find('td.clientemail').addClass('modified');
      }
    });
    $('#ThirdPartyEmail').val(clientEmail);
  }
  
  $('body').on('click','tr:not(.totalrow) td.clientaddress',function(){
    setClientAddress($(this).find('div textarea').val());
  });
  function setClientAddress(clientAddress){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.clientaddress').text(clientAddress);
      if (clientAddress == "<?php echo $proposedClientData['client_address']; ?>"){
        $(this).find('td.clientaddress').removeClass('modified');
      }
      else {
        $(this).find('td.clientaddress').addClass('modified');
      }
    });
    $('#ThirdPartyAddress').val(clientAddress);
  }
  
  $('body').on('click','tr:not(.totalrow) td.clientruc',function(){
    setClientRuc($(this).find('div input').val());
  });
  function setClientRuc(clientRuc){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.clientruc').text(clientRuc);
      if (clientRuc == "<?php echo $proposedClientData['client_email']; ?>"){
        $(this).find('td.clientruc').removeClass('modified');
      }
      else {
        $(this).find('td.clientruc').addClass('modified');
      }
    });
    $('#ThirdPartyRucNumber').val(clientRuc);
  }
  
  $('body').on('click','tr:not(.totalrow) td.clienttypeid',function(){
    setClientType($(this).find('div select').val(),$(this).find('div select option:selected').text());
  });
  function setClientType(clientTypeId,clientTypeName){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.clienttypeid').text(clientTypeName);
      if (clientTypeId == "<?php echo $proposedClientData['client_type_id']; ?>"){
        $(this).find('td.clienttypeid').removeClass('modified');
      }
      else {
        $(this).find('td.clienttypeid').addClass('modified');
      }
    });
    $('#ThirdPartyClientTypeId').val(clientTypeId);
  }
  
  $('body').on('click','tr:not(.totalrow) td.zoneid',function(){
    setZone($(this).find('div select').val(),$(this).find('div select option:selected').text());
  });
  function setZone(zoneId,zoneName){
    $('table#conversion').find('tr.totalrow').each(function(){
      $(this).find('td.zoneid').text(zoneName);
      if (zoneId == "<?php echo $proposedClientData['client_type_id']; ?>"){
        $(this).find('td.zoneid').removeClass('modified');
      }
      else {
        $(this).find('td.zoneid').addClass('modified');
      }
    });
    $('#ThirdPartyZoneId').val(zoneId);
  }
  
  $('body').on('change','input.thirdPartySelector',function(){
    var clientId=$(this).val()
    if (clientId == 0){
      $('#ExistingClientCompanyName').val('');
      $('#ExistingClientPhone').val('');
      $('#ExistingClientEmail').val('');
      $('#ExistingClientAddress').val('');
      $('#ExistingClientRucNumber').val('');
      $('#ExistingClientClientTypeId').val(0);
      $('#ExistingClientZoneId').val(0);
      $('#existingClient').addClass('d-none');
    }
    else {
      var existingClient=jsRegisteredClients[clientId].ThirdParty
      $('#ExistingClientCompanyName').val(existingClient.company_name);
      $('#ExistingClientPhone').val(existingClient.phone);
      $('#ExistingClientEmail').val(existingClient.email);
      $('#ExistingClientAddress').val(existingClient.address);
      $('#ExistingClientRucNumber').val(existingClient.ruc_number);
      $('#ExistingClientClientTypeId').val(existingClient.client_type_id);
      $('#ExistingClientZoneId').val(existingClient.zone_id);
      $('#existingClient').removeClass('d-none');
    }
  });
  
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
  
  function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
  
  $(document).ready(function(){
		formatNumbers();
    setDisplayCreditAmount();	
    if (<?php echo in_array($userRoleId,[ROLE_ADMIN,ROLE_ASSISTANT])?1:0; ?> == 0) {
      $('#VendorList').addClass('hidden');
    }
    //$('select.fixed option:not(:selected)').attr('disabled', true);
	});
  
</script>
<div class="thirdParties convertirCliente fullwidth">
<?php 

  if (empty($clientName)){
    echo '<h1>No se especificó nombre de cliente genérico para convertir, no conversión disponible</h2>';      
  }
  elseif (empty($clientOrders)){
    echo '<h1>No se encontraron ventas para clientes genéricos con nombre '.$clientName.', no conversión disponible</h2>';      
  }
  else {
    echo $this->Form->create('ThirdParty',['style'=>'width:100%;']);
    echo "<fieldset>";
      echo '<legend>Convertir cliente genérico '.$clientName.' a cliente registrado</legend>';
      echo '<div class="container-fluid">';
      if (!empty($similarClientOrders)){  
        echo '<div class="row">';	
          echo '<div class="col-sm-12">';
            echo '<h2>Clientes registrados con un nombre similar</h2>';
            
          echo '</div>';
        echo '</div>';
        echo '<div class="row">';	
          foreach ($similarClientOrders as $similarClientName =>$similarClientData){
            echo '<div class="col-sm-3" style="padding:2px;">';
              echo '<h3>Ventas para cliente similar '.$similarClientName.'</h3>';
              $similarClientOrderTableBodyRows=''; 
              $similarClientSubtotal=0;
              //pr($similarClientData);
              foreach ($similarClientData['Orders'] as $similarClientOrder){
                $similarOrderDateTime=new Datetime($similarClientOrder['Order']['order_date']);
                $similarClientSubtotal+=$similarClientOrder['Order']['total_price'];
                $similarClientOrderTableBodyRows.='<tr>';
                  $similarClientOrderTableBodyRows.='<td>'.$similarOrderDateTime->format('d-m-Y').'</td>';
                  $similarClientOrderTableBodyRows.='<td>'.$similarClientOrder['Order']['order_code'].'</td>';
                  $similarClientOrderTableBodyRows.='<td class="number"><span class="currency">C$</span><span class="amountright">'.$similarClientOrder['Order']['total_price'].'</span></td>';
                $similarClientOrderTableBodyRows.='</tr>';
                  
              }
              $similarClientOrderTableTotalRow='';
              $similarClientOrderTableTotalRow.='<tr class="totalrow">';
                $similarClientOrderTableTotalRow.='<td>Subtotal</td>';
                $similarClientOrderTableTotalRow.='<td></td>';
                $similarClientOrderTableTotalRow.='<td class="number"><span class="currency">C$</span><span class="amountright">'.$similarClientSubtotal.'</span></td>';
              $similarClientOrderTableTotalRow.='</tr>';
              $similarClientOrderTableBody='<tbody>'.$similarClientOrderTableTotalRow.$similarClientOrderTableBodyRows.$similarClientOrderTableTotalRow.'</tbody>';
              $similarClientOrderTable='<table id="'.$similarClientName.'" style="">'.$similarClientOrderTableBody.'</table>';
              echo $similarClientOrderTable;
              echo '<label class="switch">';
                echo $this->Form->Input('SimilarClient.'.$similarClientName,[
                  'label'=>false,
                  'div'=>false,
                  'type'=>'checkbox',
                  
                ]);
                echo '<span class="slider round">Incluir</span>';
              echo '</label>';
            echo '</div>';
          }
          echo '<div class="col-sm-12">';
            echo $this->Form->Submit('Aplicar clientes similares seleccionados',['id'=>'applySimilarClients','name'=>'applySimilarClients','style'=>'width:480px;']);
          echo '</div>';  
        echo '</div>';
      }
    $clientConversionTableHead='';
    $clientConversionTableHead.='<thead>';
      $clientConversionTableHead.='<tr>';
        $clientConversionTableHead.='<th>Nombre cliente</th>';
        $clientConversionTableHead.='<th style="width:8%;">Teléfono</th>';
        $clientConversionTableHead.='<th>Email</th>';
        $clientConversionTableHead.='<th>Dirección</th>';
        $clientConversionTableHead.='<th>No. Ruc</th>';
        $clientConversionTableHead.='<th>Tipo Cliente</th>';
        $clientConversionTableHead.='<th>Zona</th>';
        $clientConversionTableHead.='<th style="min-width:220px;">Factura</th>';
      $clientConversionTableHead.='</tr>';
    $clientConversionTableHead.='</thead>';
    
    $clientConversionTableBodyRows='';
    $invoiceSubtotal=0;
    foreach ($clientOrders as $clientOrder){
      $clientOrderDateTime=new DateTime($clientOrder['Order']['order_date']);
      $invoiceSubtotal+=$clientOrder['Order']['total_price'];
      $clientConversionTableBodyRows.='<tr class="'.($clientOrder['Order']['client_name'] === $clientName?"exactclient":"derivedclient").'">';
        $clientConversionTableBodyRows.='<td class="clientname">'.$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_name',[
          'label'=>false,
          'readonly'=>true,
          'value'=>$clientOrder['Order']['client_name'],
        ]).'</td>';
        $clientConversionTableBodyRows.='<td class="clientphone'.(empty($clientOrder['Order']['client_phone'])?' empty':($clientOrder['Order']['client_phone'] == $proposedClientData['client_phone']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_phone',[
            'label'=>false,
            'readonly'=>true,
            'value'=>$clientOrder['Order']['client_phone'],
          ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td class="clientemail'.(empty($clientOrder['Order']['client_email'])?' empty':($clientOrder['Order']['client_email'] == $proposedClientData['client_email']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_email',[
            'label'=>false,
            'readonly'=>true,
            'value'=>$clientOrder['Order']['client_email'],
          ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td class="clientaddress'.(empty($clientOrder['Order']['client_address'])?' empty':($clientOrder['Order']['client_address'] == $proposedClientData['client_address']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=empty($clientOrder['Order']['client_address'])?
            $this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_address',[
              'label'=>false,
              'readonly'=>true,
              'type'=>'text',
            ]):
            $this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_address',[
              'label'=>false,
              'readonly'=>true,
              'value'=>$clientOrder['Order']['client_address'],
              'style'=>'font-size:0.7rem',
              'type'=>'textarea',
              'rows'=>2,
            ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td class="clientruc'.(empty($clientOrder['Order']['client_ruc'])?' empty':($clientOrder['Order']['client_ruc'] == $proposedClientData['client_ruc']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_ruc',[
            'label'=>false,
            'readonly'=>true,
            'value'=>$clientOrder['Order']['client_ruc'],
          ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td class="clienttypeid'.(empty($clientOrder['Order']['client_type_id'])?' empty':($clientOrder['Order']['client_type_id'] == $proposedClientData['client_type_id']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_type_id',[
            'label'=>false,
            'class'=>'fixed',
            'value'=>$clientOrder['Order']['client_type_id'],
            'style'=>'font-size:0.8rem',
          ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td class="zoneid'.(empty($clientOrder['Order']['zone_id'])?' empty':($clientOrder['Order']['zone_id'] == $proposedClientData['zone_id']?' proposed':' minority')).'">';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.zone_id',[
            'label'=>false,
            'class'=>'fixed',
            'value'=>$clientOrder['Order']['zone_id'],
            'style'=>'font-size:0.8rem',
          ]);
        $clientConversionTableBodyRows.='</td>';
        $clientConversionTableBodyRows.='<td>';
          $clientConversionTableBodyRows.=$this->Form->input('ClientOrder.'.$clientOrder['Order']['order_code'].'.client_order',[
            'label'=>false,
            'readonly'=>true,
            'type'=>'text',
            'value'=>$clientOrder['Order']['order_code'].' ('.$clientOrderDateTime->format('d-m-Y').') C$ '.$clientOrder['Order']['total_price'],
            'style'=>'font-size:0.8rem',
          ]);
        $clientConversionTableBodyRows.='</td>';
      $clientConversionTableBodyRows.='</tr>';
    }
    
    $clientConversionTableTotalRow='';
    $clientConversionTableTotalRow.='<tr class="totalrow">';
      $clientConversionTableTotalRow.='<td>DATOS A GRABAR</td>';
      $clientConversionTableTotalRow.='<td class="clientphone">'.$proposedClientData['client_phone'].'</td>';
      $clientConversionTableTotalRow.='<td class="clientemail">'.$proposedClientData['client_email'].'</td>';
      $clientConversionTableTotalRow.='<td class="clientaddress">'.$proposedClientData['client_address'].'</td>';
      $clientConversionTableTotalRow.='<td class="clientruc">'.$proposedClientData['client_ruc'].'</td>';
      $clientConversionTableTotalRow.='<td class="clienttypeid">'.$clientTypes[$proposedClientData['client_type_id']].'</td>';
      $clientConversionTableTotalRow.='<td class="zoneid">'.$zones[$proposedClientData['zone_id']].'</td>';
      $clientConversionTableTotalRow.='<td class="number"><span class="currency">C$</span><span class="amountright"> '.$invoiceSubtotal.'</span></td>';
    $clientConversionTableTotalRow.='</tr>';
    $clientConversionTableBody='<tbody style="font-size:0.75rem;">'.$clientConversionTableTotalRow.$clientConversionTableBodyRows.$clientConversionTableTotalRow.'</tbody>';
    $clientConversionTable='<table id="conversion">'.$clientConversionTableHead.$clientConversionTableBody.'</table>';

    
        echo '<div class="row">';	
          echo '<div class="col-sm-12">';	
            echo '<h2>Ventas registrados para cliente genérico exacto</h2>';
            echo $clientConversionTable;
          echo '</div>';
        echo '</div>';
        echo '<div class="row">';	
          echo '<div class="col-sm-12">';	
            if (empty($similarRegisteredClientNames)){
              echo '<h3>No hay ningun cliente registrado con un nombre parecido a '.$clientName.'.  Un nuevo cliente estará creado</h3>';
              echo $this->Form->input('ThirdParty.id',[
                'type'=>'hidden',
                'value'=>0,
              ]);
            }
            else {
              echo '<h3>Hay clientes registrados con un nombre parecido a '.$clientName.'.  Indica si quiere ocupar el cliente ya existente para convertir o si quiere un cliente nuevo</h3>';
              $registeredClientOptions=[0=>'Crear cliente nuevo']+$similarRegisteredClientNames;
              //pr($registeredClientOptions);
              echo '<div class="row">';
                echo '<div class="col-sm-6">';
                  echo $this->form->input('ThirdParty.id',[
                    'legend'=>false,
                    'type'=>'radio',
                    'default'=>0,
                    'options'=>$registeredClientOptions,
                    'style'=>'width:5%;',
                    'class'=>'thirdPartySelector',
                    'div'=>['class'=>'input radio radioset'],
                  ]);
                echo '</div>';
                echo '<div id="existingClient" class="col-sm-6 d-none">';
                  echo '<h4>Cliente existente</h4>';
                  echo $this->Form->input('ExistingClient.company_name',[]);
                  echo $this->Form->input('ExistingClient.phone',[]);
                  echo $this->Form->input('ExistingClient.email',[]);
                  echo $this->Form->input('ExistingClient.address',[]);
                  echo $this->Form->input('ExistingClient.ruc_number',[]);
                  echo $this->Form->input('ExistingClient.client_type_id',['options'=>$clientTypes]);
                  echo $this->Form->input('ExistingClient.zone_id',['options'=>$zones]);
                echo '</div>';
              echo '</div>';
            }
          echo '</div>';
        echo '</div>';  
        echo '<div class="row">';	
          echo '<div class="col-sm-6">';	
            echo '<h3>Datos de cliente</h3>';
            echo $this->Form->input('company_name',['default'=>$clientName,]);
            echo $this->Form->input('bool_active',['default'=>true]);
            echo $this->Form->input('bool_generic',['label'=>'Cliente genérico','default'=>false, 'div'=>['class'=>'div input checkbox checkboxleftbig']]);
            echo $this->Form->input('price_client_category_id',['default'=>1,'empty'=>['0'=>'-- Categoría Precio --']]);
            echo $this->Form->input('accounting_code_id',[
              'default'=>$newClientAccountingCode,
              'class'=>'fixed',
              'empty'=>['0'=>'-- Cuenta Contable --'],
            ]);
            if ($userRoleId == ROLE_ADMIN){
              echo $this->Form->input('credit_days',['default'=>0]);
            }
            else {
              echo $this->Form->input('credit_days',['default'=>0,'readonly'=>'readonly']);
            }
            echo $this->Form->input('credit_amount');
            echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
            
            echo $this->Form->input('expiration_rate',['label'=>'Tasa de expiración (%)','default'=>5]);
            
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('phone',['default'=>$proposedClientData['client_phone']]);
            echo $this->Form->input('email',['default'=>$proposedClientData['client_email']]);
            echo $this->Form->input('address',['type'=>'textarea','default'=>$proposedClientData['client_address']]);
            echo $this->Form->input('ruc_number',['default'=>$proposedClientData['client_ruc']]);
            echo $this->Form->input('client_type_id',['default'=>$proposedClientData['client_type_id'],'empty'=>[0=>'-- Tipo de Cliente --']]);
            echo $this->Form->input('zone_id',['default'=>$proposedClientData['zone_id'],'empty'=>[0=>'-- Zona --']]);
          echo '</div>';	
          echo '<div class="col-sm-3">';		
            echo '<div id="VendorList">';
              echo '<h3>Vendedores Asociados</h3>';
              for ($u=0;$u<count($users);$u++){
                echo $this->Form->input('User.'.$u.'.id',[
                  'type'=>'checkbox',
                  'default'=>false,
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
                echo $this->Form->input('Plant.'.$p.'.id',[
                  'type'=>'checkbox',
                  'default'=>false,
                  'label'=>$plants[$p]['Plant']['name'],
                  'div'=>['class'=>'checkboxleftbig'],
                ]);
              }
            echo '</div>';	  
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
      echo "</div>";
    echo "</fieldset>";
   
    echo $this->Form->end(); 
  }
?>
</div>
<?php 
/*
echo '<div class="actions">';
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Clients'), array('action' => 'resumenClientes'))."</li>";
		echo "<br/>";
		if ($bool_saleremission_index_permission) {
			echo "<li>".$this->Html->link(__('Todas Ventas y Remisiones'), array('controller' => 'orders', 'action' => 'resumenVentasRemisiones'))."</li>";
		}
		if ($bool_sale_add_permission) {
			echo "<li>".$this->Html->link(__('New Sale'), array('controller' => 'orders', 'action' => 'crearVenta'))."</li>";
		}
		if ($bool_remission_add_permission) {
			echo "<li>".$this->Html->link(__('New Remission'), array('controller' => 'orders', 'action' => 'crarRemision'))."</li>";
		}
	echo "</ul>";
echo '</div>';  
*/
?>