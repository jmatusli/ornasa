<div class="machineProducts view">
<?php 
	echo "<h2>".__('Machine Product')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Machine')."</dt>";
		echo "<dd>".$this->Html->link($machineProduct['Machine']['name'], array('controller' => 'machines', 'action' => 'view', $machineProduct['Machine']['id']))."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($machineProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $machineProduct['Product']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Machine Product'), array('action' => 'edit', $machineProduct['MachineProduct']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Machine Product'), array('action' => 'delete', $machineProduct['MachineProduct']['id']), array(), __('Are you sure you want to delete # %s?', $machineProduct['MachineProduct']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Machine Products'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Machine Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
