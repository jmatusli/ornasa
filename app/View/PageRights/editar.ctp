<div class="pageRights form">
<?php 
  echo $this->Form->create('PageRight'); 
	echo '<fieldset>';
		echo '<legend>'.__('Edit Page Right').'</legend>';
		echo $this->Form->input('id');
    echo $this->Form->input('code');
    echo $this->Form->input('bool_default_assignment',['label'=>'Activado por defecto para usuario', 'div'=>['class'=>['input checkbox checkboxleft']]]);
    echo $this->Form->input('name');
		echo $this->Form->input('description');
    echo $this->Form->input('pages',['rows'=>5]);
		echo $this->Form->input('list_order');
	echo '</fieldset>';
  echo $this->Form->end(__('Submit')); 
?>
</div>
<div class="actions">
<?php 	
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';

		echo '<li>'.$this->Html->link(__('List Page Rights'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>  
</div>