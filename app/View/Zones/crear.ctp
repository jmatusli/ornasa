<div class="zones form">
<?php	
  echo $this->Form->create('Zone'); 
	echo '<fieldset>';
    echo '<legend>'.__('Add Zone').'</legend>';
		echo $this->Form->input('code');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('list_order');
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Zones'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>