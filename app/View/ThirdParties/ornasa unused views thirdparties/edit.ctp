<div class="thirdParties form">
<?php echo $this->Form->create('ThirdParty'); ?>
	<fieldset>
		<legend><?php echo __('Edit Third Party'); ?></legend>
	<?php
		echo $this->Form->input('id',array('hidden'=>'hidden'));
		echo $this->Form->input('bool_provider');
		echo $this->Form->input('company_name');
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('email');
		echo $this->Form->input('phone');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<!--li><?php // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ThirdParty.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ThirdParty.id'))); ?></li-->
		<li><?php echo $this->Html->link(__('List Third Parties'), array('action' => 'index')); ?></li>
		<br/>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
		<?php } ?>
	</ul>
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>