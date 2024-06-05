<div class="productNatures form fullwidth">
<?php	
  echo $this->Form->create('ProductNature'); 
	echo '<fieldset>';
    echo '<legend>'.__('Edit Product Nature')	.'</legend>';
		echo $this->Form->input('id');
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
/*
  echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('ProductNature.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('ProductNature.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Product Natures'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
*/
?>
</div>
