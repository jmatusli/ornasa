<div class="stockItemReclassification form">
	<div class='col-md-8'>
	<?php echo $this->Form->create('StockItem'); ?>
		<fieldset>
			<legend><?php echo __('Transferir preformas a Ingroup'); ?></legend>
		<?php
			echo $this->Form->input('Ingrouptrans.tingroup_pref_Code',array('readonly'=>'readonly','label'=>__('Transference Code'),'default'=>$tingroupPrefCode));
			echo $this->Form->input('Ingrouptrans.tingroup_date',array('type'=>'datetime','dateFormat'=>'DMY','label'=>__('Transference Date')));
			echo $this->Form->input('Ingrouptrans.preforma_id_origen',array('class'=>'bottled','options'=>$allPreformas,'label'=>__('Preforma'),'default'=>'0','empty' =>array(0=>__('Select Preforma'))));
			echo $this->Form->input('Ingrouptrans.preforma_id_destino',array('class'=>'bottled','id'=>'preforma_id_destino','options'=>$allPrefingroup,'label'=>__('Producto Ingroup'),'default'=>'0','empty' =>array(0=>__('Seleccione Producto'))));
			echo $this->Form->input('Ingrouptrans.quantity',array('class'=>'bottled','label'=>__('Quantity'),'type'=>'number','default'=>'0'));
      echo $this->Form->input('Ingrouptrans.comment',array('type'=>'textarea','rows'=>5));
		?>
		</fieldset>
	<?php echo $this->Form->end(__('Submit')); ?>
	 
	</div>
	<div class='col-md-4'>
	<?php
		//echo $this->InventoryCountDisplay->showInventoryTotals($preformaInventory, CATEGORY_PRODUCED,$plantId,[__('Bottles')]);
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
		
		$(".back").on("click",function(){
		window.history.back()
		});
	});

</script>