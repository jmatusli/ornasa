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
	});
  
</script>

<div class="thirdParties form providers">
<?php 
  echo $this->Form->create('ThirdParty'); 
	echo "<fieldset>";
		echo "<legend>".__('Add Provider')."</legend>";
    echo $this->Form->Submit(__('Save')); 
    // first the intention was to just hide the input, but then the containing div still took up space
    // instead the input was not printed at all in the form and the value was set in the ThirdPartiesController
    // echo $this->Form->input('bool_provider',['hidden'=>'hidden','value'=>'1','label'=>false]);
    echo $this->Form->input('plant_id',['value'=>$plantId,'empty'=>[0=>'-- Planta --']]);
    echo $this->Form->input('company_name');
    echo $this->Form->input('bool_active',['default'=>true]);
    echo $this->Form->input('provider_nature_id',['default'=>PROVIDER_NATURE_RAW]);
    //echo $this->Form->input('accounting_code_id',['default'=>0,'empty'=>['0'=>__('Select Accounting Code')]]);
    echo $this->Form->input('accounting_code_id',['default'=>$newProviderCode,'class'=>'fixedselection','empty'=>['0'=>__('Select Accounting Code')]]);
    if ($userRoleId == ROLE_ADMIN){
      echo $this->Form->input('credit_days',['default'=>0]);
    }
    else {
      echo $this->Form->input('credit_days',['default'=>0,'readonly'=>'readonly']);
    }
    echo $this->Form->input('credit_amount');
    echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
    echo $this->Form->input('first_name');
    echo $this->Form->input('last_name');
    echo $this->Form->input('email');
    echo $this->Form->input('phone');
    echo $this->Form->input('address');
    echo $this->Form->input('ruc_number');
  echo "</fieldset>";
  echo $this->Form->Submit(__('Save')); 
  echo $this->Form->end(); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Providers'), ['action' => 'resumenProveedores'])."</li>";
		echo "<br/>";
		if ($bool_purchase_index_permission) {
			echo "<li>".$this->Html->link(__('List Purchases'), ['controller' => 'orders', 'action' => 'resumenEntradas'])." </li>";
		}
		if ($bool_purchase_add_permission) {
			echo "<li>".$this->Html->link(__('New Purchase'), ['controller' => 'orders', 'action' => 'crearEntrada'])." </li>";
		}
    if ($bool_purchase_order_index_permission) {
      echo "<br/>";
      echo "<li>".$this->Html->link(__('List Purchase Orders'), ['controller' => 'purchase_orders', 'action' => 'resumen'])." </li>";
    }
    if ($bool_purchase_order_add_permission) {
      echo "<li>".$this->Html->link(__('New Purchase Order'), ['controller' => 'purchase_orders', 'action' => 'crear'])." </li>";
    }
	echo "</ul>";
?>
</div>
<script>
	$(document).ready(function(){
		$('select.fixedselection option:not(:selected)').attr('disabled', true);
	});
</script>