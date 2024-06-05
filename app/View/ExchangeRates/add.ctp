<div class="exchangeRates form">
<?php echo $this->Form->create('ExchangeRate'); ?>
	<fieldset>
		<legend><?php echo __('Add Exchange Rate'); ?></legend>
	<?php
		echo __($this->Form->input('application_date'),array('dateFormat'=>'DMY'));
		echo __($this->Form->input('conversion_currency_id'));
		echo __($this->Form->input('rate'));
		echo __($this->Form->input('base_currency_id',array('default'=>CURRENCY_USD)));
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
<?php echo $this->Html->Link('Cancelar',array('action'=>'add'),array( 'class' => 'btn btn-primary cancel')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Exchange Rates'), array('action' => 'index')); ?></li>
		<br/>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>