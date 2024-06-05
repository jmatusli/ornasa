<div class="incidences form">
<?php echo $this->Form->create('Incidence'); ?>
	<fieldset>
		<legend><?php echo __('Add Incidence'); ?></legend>
	<?php
		echo $this->Form->input('name');
    echo $this->Form->input('list_order',['default'=>1]);
    echo $this->Form->input('bool_active',['label'=>'Activado?','default'=>true,'type'=>'hidden']);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
    <li><?php echo $this->Html->link(__('List Incidences'), array('action' => 'resumenIncidencias')); ?></li>
	</ul>
</div>
