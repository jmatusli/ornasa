<div class="units form">
<?php
	echo $this->Form->create('Unit'); 
	echo '<fieldset>';
		echo '<legend>'.__('Edit Unit').'</legend>';
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('abbreviation');
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('Unit.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('Unit.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Units'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>
