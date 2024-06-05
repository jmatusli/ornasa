<div class="priceClientCategories form">
<?php 
  echo $this->Form->create('PriceClientCategory');
	echo '<fieldset>';
		echo '<legend>'.__('Edit Price Client Category').'</legend>';
		echo $this->Form->input('id');
		echo $this->Form->input('category_number');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	echo '</fieldset>';
  echo $this->Form->end(__('Submit')); 
?>
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Html->link(__('List Price Client Categories'), ['action' => 'resumen']).'</li>';
	echo '</ul>';
 ?>  
</div>
