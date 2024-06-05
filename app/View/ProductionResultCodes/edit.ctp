<div class="productionResultCodes form">
<?php echo $this->Form->create('ProductionResultCode'); ?>
	<fieldset>
		<legend><?php echo __('Edit Production Result Code'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('code');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ProductionResultCode.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ProductionResultCode.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Production Result Codes'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>