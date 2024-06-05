<div class="productNatures form">
<?php	
  echo $this->Form->create('ProductNature'); 
	echo '<fieldset>';
  echo '<legend>'.__('Add Product Nature')	.'</legend>';
		echo $this->Form->input('name');
		echo $this->Form->input('short_description');
		echo $this->Form->input('long_description');
		echo $this->Form->input('list_order');
		echo $this->Form->input('hex_color');
	echo '</fieldset>';
  echo $this->Form->Submit(__('Submit'));
  echo $this->Form->end();
?>
</div>
<div class="actions">
<?php	
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Product Natures'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
?>
</div>
