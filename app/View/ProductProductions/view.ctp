<div class="productProductions view">
<?php 
	echo "<h2>".__('Product Production')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Application Date')."</dt>";
		echo "<dd>".h($productProduction['ProductProduction']['application_date'])."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($productProduction['Product']['name'], array('controller' => 'products', 'action' => 'view', $productProduction['Product']['id']))."</dd>";
		echo "<dt>".__('Acceptable Production')."</dt>";
		echo "<dd>".h($productProduction['ProductProduction']['acceptable_production'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Product Production'), array('action' => 'edit', $productProduction['ProductProduction']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Product Production'), array('action' => 'delete', $productProduction['ProductProduction']['id']), array(), __('Are you sure you want to delete # %s?', $productProduction['ProductProduction']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Product Productions'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product Production'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
