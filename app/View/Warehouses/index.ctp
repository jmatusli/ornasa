<div class="warehouses index">
<?php
	echo "<h2>".__('Warehouses')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('name')."</th>";
				echo "<th>".$this->Paginator->sort('description')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($warehouses as $warehouse){
			echo "<tr>";
				echo "<td>".h($warehouse['Warehouse']['name'])."&nbsp;</td>";
				echo "<td>".h($warehouse['Warehouse']['description'])."&nbsp;</td>";
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('action' => 'view', $warehouse['Warehouse']['id'])); 
					if ($bool_edit_permission){
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $warehouse['Warehouse']['id']));
					}
					if ($bool_delete_permission){
						// echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $warehouse['Warehouse']['id']), array(), __('Are you sure you want to delete # %s?', $warehouse['Warehouse']['id']))."
					}
				echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
	echo "<p>";
		echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
		));
	echo "</p>";
	echo "<div class='paging'>";
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	echo "</div>";
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Warehouse'), array('action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
