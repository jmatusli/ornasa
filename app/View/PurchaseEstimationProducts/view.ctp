<div class="requestProducts view">
<?php 
	echo "<h2>".__('Request Product')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($requestProduct['RequestProduct']['name'])."</dd>";
		echo "<dt>".__('Request')."</dt>";
		echo "<dd>".$this->Html->link($requestProduct['Request']['id'], array('controller' => 'requests', 'action' => 'view', $requestProduct['Request']['id']))."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($requestProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $requestProduct['Product']['id']))."</dd>";
		echo "<dt>".__('Product Unit Price')."</dt>";
		echo "<dd>".h($requestProduct['RequestProduct']['product_unit_price'])."</dd>";
		echo "<dt>".__('Product Quantity')."</dt>";
		echo "<dd>".h($requestProduct['RequestProduct']['product_quantity'])."</dd>";
		echo "<dt>".__('Product Total Price')."</dt>";
		echo "<dd>".h($requestProduct['RequestProduct']['product_total_price'])."</dd>";
		echo "<dt>".__('Production Result Code')."</dt>";
		echo "<dd>".$this->Html->link($requestProduct['ProductionResultCode']['code'], array('controller' => 'production_result_codes', 'action' => 'view', $requestProduct['ProductionResultCode']['id']))."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($requestProduct['RequestProduct']['description'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Request Product'), array('action' => 'edit', $requestProduct['RequestProduct']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Request Product'), array('action' => 'delete', $requestProduct['RequestProduct']['id']), array(), __('Are you sure you want to delete # %s?', $requestProduct['RequestProduct']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Request Products'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Request Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Requests'), array('controller' => 'requests', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Request'), array('controller' => 'requests', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Production Result Codes'), array('controller' => 'production_result_codes', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Production Result Code'), array('controller' => 'production_result_codes', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
