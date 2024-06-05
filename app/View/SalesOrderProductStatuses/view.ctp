<div class="salesOrderProductStatuses view">
<?php 
	echo "<h2>".__('Sales Order Product Status')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Status')."</dt>";
		echo "<dd>".h($salesOrderProductStatus['SalesOrderProductStatus']['status'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Sales Order Product Status'), array('action' => 'edit', $salesOrderProductStatus['SalesOrderProductStatus']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Sales Order Product Status'), array('action' => 'delete', $salesOrderProductStatus['SalesOrderProductStatus']['id']), array(), __('Are you sure you want to delete # %s?', $salesOrderProductStatus['SalesOrderProductStatus']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Sales Order Product Statuses'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order Product Status'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Sales Order Products'), array('controller' => 'sales_order_products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order Product'), array('controller' => 'sales_order_products', 'action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
<div class="related">
<?php 
	if (!empty($salesOrderProductStatus['SalesOrderProduct'])){
		echo "<h3>".__('Related Sales Order Products')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<tr>";
				echo "<th>".__('Sales Order Id')."</th>";
				echo "<th>".__('Product Id')."</th>";
				echo "<th>".__('Product Quantity')."</th>";
				echo "<th>".__('Sales Order Product Status Id')."</th>";
				echo"<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		foreach ($salesOrderProductStatus['SalesOrderProduct'] as $salesOrderProduct){
			echo "<tr>";
				echo "<td>".$salesOrderProduct['sales_order_id']."</td>";
				echo "<td>".$salesOrderProduct['product_id']."</td>";
				echo "<td>".$salesOrderProduct['product_quantity']."</td>";
				echo "<td>".$salesOrderProduct['sales_order_product_status_id']."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('controller' => 'sales_order_products', 'action' => 'view', $salesOrderProduct['id']));
					echo $this->Html->link(__('Edit'), array('controller' => 'sales_order_products', 'action' => 'edit', $salesOrderProduct['id']));
					echo $this->Form->postLink(__('Delete'), array('controller' => 'sales_order_products', 'action' => 'delete', $salesOrderProduct['id']), array(), __('Are you sure you want to delete # %s?', $salesOrderProduct['id']));
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>
</div>
