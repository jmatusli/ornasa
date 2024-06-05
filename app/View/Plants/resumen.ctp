<div class="plants index">
<?php
	echo "<h2>".__('Plants')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('name')."</th>";
				echo "<th>".$this->Paginator->sort('description')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($plants as $plant){
			echo "<tr>";
				echo '<td>'.$this->Html->link($plant['Plant']['name'], ['action' => 'detalle', $plant['Plant']['id']]).'</td>' ;
				echo "<td>".h($plant['Plant']['description'])."&nbsp;</td>";
				echo "<td class='actions'>";
					if ($bool_edit_permission){
						echo $this->Html->link(__('Edit'), ['action' => 'editar', $plant['Plant']['id']]);
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
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Plant'), ['action' => 'crear'])."</li>";
	echo "</ul>";
?>
</div>
