<div class="salesOrderRemarks view">
<?php 
	echo "<h2>".__('Sales Order Remark')."</h2>";
	echo "<dl>";
		echo "<dt>".__('User')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderRemark['User']['username'], array('controller' => 'users', 'action' => 'view', $salesOrderRemark['User']['id']))."</dd>";
		echo "<dt>".__('Sales Order')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderRemark['SalesOrder']['sales_order_code'], array('controller' => 'sales_orders', 'action' => 'view', $salesOrderRemark['SalesOrder']['id']))."</dd>";
		echo "<dt>".__('Remark Datetime')."</dt>";
		echo "<dd>".h($salesOrderRemark['SalesOrderRemark']['remark_datetime'])."</dd>";
		echo "<dt>".__('Remark Text')."</dt>";
		echo "<dd>".h($salesOrderRemark['SalesOrderRemark']['remark_text'])."</dd>";
		echo "<dt>".__('Action Type')."</dt>";
		echo "<dd>".$this->Html->link($salesOrderRemark['ActionType']['name'], array('controller' => 'action_types', 'action' => 'view', $salesOrderRemark['ActionType']['id']))."</dd>";
		echo "<dt>".__('Working Days Before Reminder')."</dt>";
		echo "<dd>".h($salesOrderRemark['SalesOrderRemark']['working_days_before_reminder'])."</dd>";
		echo "<dt>".__('Reminder Date')."</dt>";
		echo "<dd>".h($salesOrderRemark['SalesOrderRemark']['reminder_date'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Sales Order Remark'), array('action' => 'edit', $salesOrderRemark['SalesOrderRemark']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Sales Order Remark'), array('action' => 'delete', $salesOrderRemark['SalesOrderRemark']['id']), array(), __('Are you sure you want to delete # %s?', $salesOrderRemark['SalesOrderRemark']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Sales Order Remarks'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order Remark'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Sales Orders'), array('controller' => 'sales_orders', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Order'), array('controller' => 'sales_orders', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Action Types'), array('controller' => 'action_types', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Action Type'), array('controller' => 'action_types', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
