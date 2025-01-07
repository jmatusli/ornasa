<div class="stockItemReclassification form">
	<div class='col-md-8'>
	<?php echo $this->Form->create('StockItem'); ?>
		<fieldset>
			<legend><?php echo __('Transferir Ingroup a preformas'); ?></legend>
		<?php
			echo $this->Form->input('Ingrouptrans.tingroup_pref_Code',array('readonly'=>'readonly','label'=>__('Transference Code'),'default'=>$tingroupPrefCode));
			echo $this->Form->input('Ingrouptrans.tingroup_date',array('type'=>'datetime','dateFormat'=>'DMY','label'=>__('Transference Date')));
			
			echo $this->Form->input('Ingrouptrans.preforma_id_origen',array('class'=>'','id'=>'preforma_id_origen','options'=>$allPrefingroup,'label'=>__('Producto Ingroup'),'default'=>'0','empty' =>array(0=>__('Seleccione Producto'))));
			echo $this->Form->input('Ingrouptrans.preforma_id_destino',array('class'=>'','options'=>$allPreformas,'label'=>__('Preforma'),'default'=>'0','empty' =>array(0=>__('Select Preforma'))));

			echo $this->Form->input('Ingrouptrans.quantity',array('class'=>'','label'=>__('Quantity'),'type'=>'number','default'=>'0'));
      echo $this->Form->input('Ingrouptrans.comment',array('type'=>'textarea','rows'=>5));
		?>
		</fieldset>
		<div class='row'>
		<div class='col-md-12'>
		 
		<div class='col-md-4'>
	<?php echo $this->Form->end(__('Submit')); ?>
	</div>
	</div>
	</div>
	
	</div>
	<div class='col-md-4'>
	<?php
		//echo $this->InventoryCountDisplay->showInventoryTotals($ingroupInventory, CATEGORY_PRODUCED,$plantId,[__('Bottles')]);
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