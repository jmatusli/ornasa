<div class="productProductions form fullwidth">
<?php echo $this->Form->create('ProductProduction'); ?>
	<fieldset>
		<legend><?php echo __('Editar Producción Aceptable para Producto'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('application_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('product_id');
		echo $this->Form->input('acceptable_production',array('label'=>'Producción Aceptable'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<!--div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ProductProduction.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ProductProduction.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Product Productions'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div-->
