<div class="operators index">
<?php 
	echo '<h2>'.__('Operators').'</h2>';
	echo '<table cellpadding="0" cellspacing="0">';
    echo '<thead>';
      echo '<tr>';
        echo '<th>'.$this->Paginator->sort('plant_id',__('Plant')).'</th>';
        echo '<th>'.$this->Paginator->sort('name').'</th>';
        echo '<th>'.$this->Paginator->sort('bool_active').'</th>';
        echo '<th class="actions">'.__('Actions').'</th>';
      echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($operators as $operator){
      if ($operator['Operator']['bool_active']){
        echo "<tr>";
      }
      else {
        echo "<tr class='italic'>";
      }
        echo "<td>".$this->Html->link($operator['Plant']['name'],['controller'=>'plants','action'=>'detalle',$operator['Plant']['id']])."</td>";
          
        echo "<td>".$this->Html->link($operator['Operator']['name'], ['action' => 'view', $operator['Operator']['id']])."</td>";
        echo "<td>".($operator['Operator']['bool_active']?__('Active'):__('Inactive'))."</td>";
        echo "<td class='actions'>";
          if ($bool_edit_permission){
            echo $this->Html->link(__('Edit'), ['action' => 'edit', $operator['Operator']['id']]); 
          }
          if ($bool_delete_permission){
            // echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $operator['Operator']['id']), array(), __('Are you sure you want to delete # %s?', $operator['Operator']['id'])); 
          }
        echo "</td>";
      echo "</tr>";
    }
    echo '</tbody>';
	echo '</table>';
?>  
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) {
			echo "<li>".$this->Html->link(__('New Operator'), array('action' => 'add'))."</li>";
			echo "<br/>";
		}
		if ($bool_productionrun_index_permission) {
			echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))." </li>";
		}
		if ($bool_productionrun_add_permission) {
			echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>
