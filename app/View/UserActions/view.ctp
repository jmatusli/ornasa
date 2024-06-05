<div class="userActions view">
<?php 
	echo "<h2>".__('User Action')."</h2>";
	echo "<dl>";
		echo "<dt>".__('User')."</dt>";
		echo "<dd>".$this->Html->link($userAction['User']['id'], array('controller' => 'users', 'action' => 'view', $userAction['User']['id']))."</dd>";
		echo "<dt>".__('Controller Name')."</dt>";
		echo "<dd>".h($userAction['UserAction']['controller_name'])."</dd>";
		echo "<dt>".__('Action Name')."</dt>";
		echo "<dd>".h($userAction['UserAction']['action_name'])."</dd>";
		echo "<dt>".__('Item Number')."</dt>";
		echo "<dd>".h($userAction['UserAction']['item_number'])."</dd>";
		echo "<dt>".__('Action Datetime')."</dt>";
		echo "<dd>".h($userAction['UserAction']['action_datetime'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit User Action'), array('action' => 'edit', $userAction['UserAction']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete User Action'), array('action' => 'delete', $userAction['UserAction']['id']), array(), __('Are you sure you want to delete # %s?', $userAction['UserAction']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List User Actions'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User Action'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
