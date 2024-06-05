<div class="purchaseOrderProducts view">
<?php 
	echo "<h2>".__('Purchase Order Product')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Purchase Order')."</dt>";
		echo "<dd>".$this->Html->link($purchaseOrderProduct['PurchaseOrder']['id'], array('controller' => 'purchase_orders', 'action' => 'view', $purchaseOrderProduct['PurchaseOrder']['id']))."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($purchaseOrderProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $purchaseOrderProduct['Product']['id']))."</dd>";
		echo "<dt>".__('Product Description')."</dt>";
		echo "<dd>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_description'])."</dd>";
		echo "<dt>".__('Product Quantity')."</dt>";
		echo "<dd>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_quantity'])."</dd>";
		echo "<dt>".__('Product Unit Cost')."</dt>";
		echo "<dd>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_unit_cost'])."</dd>";
		echo "<dt>".__('Product Total Cost')."</dt>";
		echo "<dd>".h($purchaseOrderProduct['PurchaseOrderProduct']['product_total_cost'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Purchase Order Product'), array('action' => 'edit', $purchaseOrderProduct['PurchaseOrderProduct']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Purchase Order Product'), array('action' => 'delete', $purchaseOrderProduct['PurchaseOrderProduct']['id']), array(), __('Are you sure you want to delete # %s?', $purchaseOrderProduct['PurchaseOrderProduct']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Purchase Order Products'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Purchase Order Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Purchase Orders'), array('controller' => 'purchase_orders', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Purchase Order'), array('controller' => 'purchase_orders', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
