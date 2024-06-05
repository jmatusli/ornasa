<div class="closingDates form">
<?php echo $this->Form->create('ClosingDate'); ?>
	<fieldset>
		<legend><?php echo __('Add Closing Date'); ?></legend>
	<?php
		echo $this->Form->input('name');
		
		echo $this->Form->input('closing_date',array('type'=>'date','dateFormat'=>'DMY','default'=>$proposedClosingDate));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Closing Dates'), array('action' => 'index')); ?></li>
	</ul>
</div>
<script>
	function proposeName(){
		var proposedName = "Cierre "+$('#ClosingDateClosingDateMonth option:selected').text()+" "+$('#ClosingDateClosingDateYear option:selected').text();
		$('#ClosingDateName').val(proposedName);
	}
	
	$('#ClosingDateClosingDateMonth').change(function(){
		proposeName();
	});
	
	$('#ClosingDateClosingDateYear').change(function(){
		proposeName();
	});
	
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});

	$(document).ready(function(){
		proposeName();
	});
</script>