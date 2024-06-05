<div class="stockItemReclassification form">
	<div class='col-md-8'>
	<?php echo $this->Form->create('StockItem'); ?>
		<fieldset>
			<legend><?php echo __('Reclasificar Botellas B/C a Preformas'); ?></legend>
		<?php
			echo $this->Form->input('Reclass.reclassification_code',array('readonly'=>'readonly','label'=>__('Reclassification Code'),'default'=>$reclassificationCode));
			echo $this->Form->input('Reclass.reclassification_date',array('type'=>'datetime','dateFormat'=>'DMY','label'=>__('Reclassification Date')));
			echo $this->Form->input('Reclass.bottle_id',array('class'=>'bottled','options'=>$allBottles,'label'=>__('Bottle'),'default'=>'0','empty' =>array(0=>__('Select Bottle'))));
			echo $this->Form->input('Reclass.preforma_id',array('class'=>'bottled','id'=>'originPreformaId','options'=>$allPreformas,'label'=>__('Preforma'),'default'=>'0','empty' =>array(0=>__('Select Preforma'))));
			echo $this->Form->input('Reclass.original_production_result_code_id',array('class'=>'bottled','options'=>$productionResultCodes,'label'=>__('Convert from Quality'),'default'=>'0','empty' =>array(0=>__('Select Original Quality'))));
			echo $this->Form->input('Reclass.target_preforma_id',array('class'=>'bottled','id'=>'destinationPreformaId','options'=>$allPreformas,'label'=>__('Convert to Preforma'),'default'=>'0','empty' =>array(0=>__('Select Preforma'))));
			echo $this->Form->input('Reclass.quantity_bottles',array('class'=>'bottled','label'=>__('Quantity of Bottles'),'type'=>'number','default'=>'0'));
      echo $this->Form->input('Reclass.comment',array('type'=>'textarea','rows'=>5));
		?>
		</fieldset>
	<?php echo $this->Form->end(__('Submit')); ?>
	</div>
	<div class='col-md-4'>
	<?php
		echo $this->InventoryCountDisplay->showInventoryTotals($bottleInventory, CATEGORY_PRODUCED,$plantId,[__('Bottles')]);
	?>
	</div>
</div>
<script>
	$('#originPreformaId').change(function(){
		var preformaid=$(this).val();
		$('#destinationPreformaId').val(preformaid);
	});


	function showBottleRelated(){
		$(".bottled").parent().show();
		$(".produced").show();
	}
	
	function hideBottleRelated(){
		$(".bottled").parent().hide();
		$(".produced").hide();
	}
	
	$(document).ready(function(){
		showBottleRelated();
	});

</script>