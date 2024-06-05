<div class="accountingCodes form">
<?php echo $this->Form->create('AccountingCode'); ?>
	<fieldset>
		<legend><?php echo __('Edit Accounting Code'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('parent_id',array('default'=>$this->request->data['AccountingCode']['parent_id'],'options'=>$parentAccountingCodes,'empty'=>array('NULL'=>'RAIZ'),'label'=>'Cuenta Superior'));
		echo $this->Form->input('code');
		echo $this->Form->input('description');
		//echo $this->Form->input('bool_creditor');
		//echo $this->Form->input('lft');
		//echo $this->Form->input('rght');
		
	?>
	</fieldset>
	<?php 
		//echo $this->Form->input('bool_detail');
		$attributes=array('legend'=>false);
		$attributes=array('fieldset'=>array('class'=>'radioset'));
		$options=array (
			'1'=>'Cuenta Mayor',
			'0'=>'Cuenta Auxiliar',
		);
		
		echo $this->Form->input('bool_main',array(
			'type'=>'radio',
			'value'=>$this->request->data['AccountingCode']['bool_main'],
			'options'=>$options,
			'div'=>array('class'=>'radioset'),
		));
		$options=array (
			'0'=>'Naturaleza Deudora',
			'1'=>'Naturaleza Acreedora',
		);
		
		echo $this->Form->input('bool_creditor',array(
			'type'=>'radio',
			'value'=>$this->request->data['AccountingCode']['bool_creditor'],
			'options'=>$options,
			'div'=>array('class'=>'radioset'),
		));
	?>
<?php echo $this->Form->end(__('Submit')); ?>
<?php echo $this->Html->Link('Cancelar',array('action'=>'edit',$id),array( 'class' => 'btn btn-primary cancel')); ?>
</div>
<div class="actions">

		
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			//echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountingCode.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('AccountingCode.id')))."</li>";
		}
		echo "<li>".$this->Html->link(__('List Accounting Codes'), array('action' => 'index'))."</li>";
		if ($bool_accountingregister_index_permission){
			//echo "<li>".$this->Html->link(__('List Accounting Registers'), array('controller' => 'accounting_registers', 'action' => 'index'))."</li>";
		}
		if ($bool_accountingregister_add_permission){
			//echo "<li>".$this->Html->link(__('New Accounting Register'), array('controller' => 'accounting_registers', 'action' => 'add'))."</li>";
		}
	echo "</ul>";
?>
	
</div>
<script>
	$('body').on('change','#AccountingCodeParentId',function(){	
		var accountingcodeid=$(this).children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>accountingcodes/getaccountingcodenature/'+accountingcodeid,
			cache: false,
			type: 'GET',
			success: function (boolcreditor) {
				if (boolcreditor){
					$('#AccountingCodeBoolCreditor0').prop('checked',false);
					$('#AccountingCodeBoolCreditor1').prop('checked',true);
				}
				else {
					$('#AccountingCodeBoolCreditor0').prop('checked',true);
					$('#AccountingCodeBoolCreditor1').prop('checked',false);
				}
			},
			error: function(e){
				console.log(e);
			}
		});
	});	
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>

