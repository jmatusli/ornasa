<div class="incidences form">
<?php echo $this->Form->create('Incidence'); ?>
	<fieldset>
		<legend><?php echo __('Edit Incidence')." ".$this->request->data['Incidence']['name']; ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
    echo $this->Form->input('list_order');
    echo $this->Form->input('bool_active',['label'=>'Activado?']);
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
