<div class="salesObjectives view">
<?php 
	echo "<h2>".__('Sales Objective')."</h2>";
	$objectiveDateTime=new DateTime($salesObjective['SalesObjective']['objective_date']);
	echo "<dl>";
		echo "<dt>".__('Vendedor')."</dt>";
		echo "<dd>".$this->Html->link($salesObjective['User']['username'], array('controller' => 'users', 'action' => 'view', $salesObjective['User']['id']))."</dd>";
		echo "<dt>".__('Objective Date')."</dt>";
		echo "<dd>".$objectiveDateTime->format('d-m-Y')."</dd>";
		echo "<dt>".__('Minimum Objective')."</dt>";
		echo "<dd>".h($salesObjective['SalesObjective']['minimum_objective'])."</dd>";
		echo "<dt>".__('Maximum Objective')."</dt>";
		echo "<dd>".h($salesObjective['SalesObjective']['maximum_objective'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Sales Objective'), array('action' => 'edit', $salesObjective['SalesObjective']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete Sales Objective'), array('action' => 'delete', $salesObjective['SalesObjective']['id']), array(), __('Are you sure you want to delete # %s?', $salesObjective['SalesObjective']['id']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Sales Objectives'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Sales Objective'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
