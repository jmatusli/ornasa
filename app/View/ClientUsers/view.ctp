<div class="clientUsers view">
<?php 
	echo "<h2>".__('Client User')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Client')."</dt>";
		echo "<dd>".$this->Html->link($clientUser['Client']['name'], array('controller' => 'clients', 'action' => 'view', $clientUser['Client']['id']))."</dd>";
		echo "<dt>".__('User')."</dt>";
		echo "<dd>".$this->Html->link($clientUser['User']['username'], array('controller' => 'users', 'action' => 'view', $clientUser['User']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Client User'), array('action' => 'edit', $clientUser['ClientUser']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Client User'), array('action' => 'delete', $clientUser['ClientUser']['id']), array(), __('Are you sure you want to delete # %s?', $clientUser['ClientUser']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Client Users'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Client User'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'clients', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'clients', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
