<div class="currencies form">
<?php echo $this->Form->create('Currency'); ?>
	<fieldset>
		<legend><?php echo __('Edit Currency'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('abbreviation');
		echo $this->Form->input('full_name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
<?php echo $this->Html->Link('Cancelar',array('action'=>'edit',$id),array( 'class' => 'btn btn-primary cancel')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<!--li><?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Currency.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Currency.id'))); ?></li-->
		<li><?php echo $this->Html->link(__('List Currencies'), array('action' => 'index')); ?></li>
		<br/>
		<!--li><?php echo $this->Html->link(__('List Purchase Order Products'), array('controller' => 'purchase_order_products', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Purchase Order Product'), array('controller' => 'purchase_order_products', 'action' => 'add')); ?> </li-->
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
