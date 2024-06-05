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
		echo "<legend>".__('Edit Provider')."</legend>";
    echo $this->Form->Submit(__('Save')); 
		echo $this->Form->input('id',['hidden'=>'hidden']);
		// echo $this->Form->input('bool_provider');
		echo $this->Form->input('plant_id',['value'=>$plantId]);
    echo $this->Form->input('company_name');
    echo $this->Form->input('provider_nature_id');
		echo $this->Form->input('accounting_code_id',['empty'=>['0'=>__('Select Accounting Code')]]);
    if ($userRoleId == ROLE_ADMIN){
      echo $this->Form->input('credit_days');
    }
    else {
      echo $this->Form->input('credit_days',['readonly'=>'readonly']);
    }
    echo $this->Form->input('credit_amount');
    echo $this->Form->input('credit_currency_id',['label'=>false,'type'=>'hidden','value'=>CURRENCY_CS]);
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('email');
		echo $this->Form->input('phone');
  echo "</fieldset>";
  echo $this->Form->Submit(__('Save')); 
  echo $this->Form->end(); 
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission) {
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ThirdParty.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ThirdParty.id')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Providers'), array('action' => 'resumenProveedores'))."</li>";
		echo "<br/>";
		if ($bool_purchase_index_permission) {
			echo "<li>".$this->Html->link(__('List Purchases'), array('controller' => 'orders', 'action' => 'resumenEntradas'))." </li>";
		}
		if ($bool_purchase_add_permission) {
			echo "<li>".$this->Html->link(__('New Purchase'), array('controller' => 'orders', 'action' => 'crearEntrada'))." </li>";
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
