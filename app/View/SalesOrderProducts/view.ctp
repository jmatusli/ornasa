<div class="salesOrderProducts view">
<?php 
	echo "<h2>".__('Sales Order Product')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Sales Order')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderProduct['SalesOrder']['id'], array('controller' => 'sales_orders', 'action' => 'view', $salesOrderProduct['SalesOrder']['id']))."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $salesOrderProduct['Product']['id']))."</dd>";
		echo "<dt>".__('Product Quantity')."</dt>";
		echo "<dd>".h($salesOrderProduct['SalesOrderProduct']['product_quantity'])."</dd>";
		echo "<dt>".__('Sales Order Product Status')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderProduct['SalesOrderProductStatus']['id'], array('controller' => 'sales_order_product_statuses', 'action' => 'view', $salesOrderProduct['SalesOrderProductStatus']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Sales Order Product'), array('action' => 'edit', $salesOrderProduct['SalesOrderProduct']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Sales Order Product'), array('action' => 'delete', $salesOrderProduct['SalesOrderProduct']['id']), array(), __('Are you sure you want to delete # %s?', $salesOrderProduct['SalesOrderProduct']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Sales Order Products'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Sales Orders'), array('controller' => 'sales_orders', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order'), array('controller' => 'sales_orders', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Sales Order Product Statuses'), array('controller' => 'sales_order_product_statuses', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order Product Status'), array('controller' => 'sales_order_product_statuses', 'action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
