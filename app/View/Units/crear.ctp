<div class="units form">
<?php
	echo $this->Form->create('Unit'); 
	echo '<fieldset>';
		echo '<legend>'.__('Add Unit').'</legend>';
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
		echo '<li>'.$this->Html->link(__('List Units'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>
