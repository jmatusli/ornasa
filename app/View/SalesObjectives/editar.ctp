<div class="salesObjectives form">
<?php echo $this->Form->create('SalesObjective'); ?>
	<fieldset>
		<legend><?php echo __('Edit Sales Objective'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id',array('label'=>'Vendedor'));
		echo $this->Form->input('objective_date',array('dateFormat'=>'DMY'));
		echo $this->Form->input('minimum_objective');
		echo $this->Form->input('maximum_objective');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('SalesObjective.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('SalesObjective.id')))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Sales Objectives'), array('action' => 'index'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))." </li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))." </li>";
	echo "</ul>";
?>
</div>
