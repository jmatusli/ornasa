<div class="productionRunTypes index">
<?php 	
	echo "<h2>".__('Production Run Types')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('name')."</th>";
				echo "<th>".$this->Paginator->sort('description')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($productionRunTypes as $productionRunType){
			echo "<tr>";
				echo "<td>".h($productionRunType['ProductionRunType']['name'])."</td>";
				echo "<td>".h($productionRunType['ProductionRunType']['description'])."</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('action' => 'view', $productionRunType['ProductionRunType']['id'])); 
					if ($bool_edit_permission){
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $productionRunType['ProductionRunType']['id'])); 
					}
					if ($bool_delete_permission){					
						// echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productionRunType['ProductionRunType']['id']), array(), __('Are you sure you want to delete # %s?', $productionRunType['ProductionRunType']['name'])); 
					}
				echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
	if ($bool_add_permission) { 
		echo "<li>".$this->Html->link(__('New Production Run Type'), array('action' => 'add'))."</li>";
		echo "<br/>";
	}
	if ($bool_productionrun_index_permission) { 
		echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))."</li>";
	}
	if ($bool_productionrun_add_permission) { 
		echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))."</li>";
	} 
	echo "</ul>";
?>	
</div>
