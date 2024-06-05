<div class="priceClientCategories view">
<?php 
	echo "<h2>".__('Price Client Category')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Category Number')."</dt>";
		echo "<dd>".h($priceClientCategory['PriceClientCategory']['category_number'])."</dd>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($priceClientCategory['PriceClientCategory']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($priceClientCategory['PriceClientCategory']['description'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
  if ($bool_edit_permission){
		echo "<li>".$this->Html->link(__('Edit Price Client Category'), ['action' => 'editar', $priceClientCategory['PriceClientCategory']['id']])."</li>";
    echo "<br/>";
  }
  if ($bool_delete_permission){
		echo "<li>".$this->Form->postLink(__('Delete Price Client Category'), ['action' => 'delete', $priceClientCategory['PriceClientCategory']['id']], [], __('Está seguro que quiere eliminar la categoría de precios # %s?', $priceClientCategory['PriceClientCategory']['name']))."</li>";
    echo "<br/>";
  }
		echo "<li>".$this->Html->link(__('List Price Client Categories'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Price Client Category'), ['action' => 'crear'])."</li>";
		echo "<br/>";
	echo "</ul>";
?> 
</div>
