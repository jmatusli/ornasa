<div class="stockItemReclassification form">
	<div class='col-md-8'>
	<?php echo $this->Form->create('StockItem'); ?>
		<fieldset>
			<legend><?php echo __('Reclasificar Tapones'); ?></legend>
		<?php
			echo $this->Form->input('Reclass.reclassification_code',array('readonly'=>'readonly','label'=>__('Reclassification Code'),'default'=>$reclassificationCode));
			echo $this->Form->input('Reclass.reclassification_date',array('type'=>'datetime','dateFormat'=>'DMY','label'=>__('Reclassification Date')));
			
			echo $this->Form->input('Reclass.original_cap_id',array('class'=>'capped','options'=>$allCaps,'label'=>__('Original Cap Color'),'default'=>'0','empty' =>array(0=>__('Select Original Cap Color'))));
			echo $this->Form->input('Reclass.target_cap_id',array('class'=>'capped','options'=>$allCaps,'label'=>__('Target Cap Color'),'default'=>'0','empty' =>array(0=>__('Select Target Cap Color'))));
			echo $this->Form->input('Reclass.quantity_caps',array('class'=>'capped','label'=>__('Quantity of Caps'),'type'=>'number','default'=>'0'));
      echo $this->Form->input('Reclass.comment',array('type'=>'textarea','rows'=>5));
		?>
		</fieldset>
	<?php echo $this->Form->end(__('Submit')); ?>
	</div>
	<div class='col-md-4'>
	<?php
		//pr($capInventory);
		echo $this->InventoryCountDisplay->showInventoryTotals($capInventory, CATEGORY_OTHER,$plantId,['header_title'=>__('Caps')]);
	?>
	</div>
</div>
<script>
	function showCapRelated(){
		$(".capped").parent().show();
		$(".other").show();
	}
	
	function hideCapRelated(){
		$(".capped").parent().hide();
		$(".other").hide();
	}
	
	$(document).ready(function(){
		showCapRelated();
	});

</script>