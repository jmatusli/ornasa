<div class="closingDates index">
	<h2><?php echo __('Closing Dates'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo __('Name'); ?></th>
			<th><?php echo __('Closing Date'); ?></th>
			<th><?php echo __('Created'); ?></th>
			<th><?php echo __('Modified'); ?></th>
			<!--th class='actions'><?php echo __('Actions'); ?></th-->
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach ($closingDates as $closingDate){
		echo "<tr>";
		echo "<td>".$closingDate['ClosingDate']['name']."</td>";
		$fechaCierre=new DateTime($closingDate['ClosingDate']['closing_date']);
		echo "<td>".$fechaCierre->format('d-m-Y')."</td>";
		$createdDate = new DateTime($closingDate['ClosingDate']['created']); 
		echo "<td>".$createdDate->format('d-m-Y')."</td>";
		$modifiedDate = new DateTime($closingDate['ClosingDate']['modified']); 
		echo "<td>".$modifiedDate->format('d-m-Y')."</td>";
		echo "<!--td class='actions'>";
		//echo $this->Html->link(__('Edit'), array('action' => 'edit', $closingDate['ClosingDate']['id'])); \
		echo"</td-->";
		echo "</tr>";
	} 
	?>
	</tbody>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Closing Date'), array('action' => 'add')); ?></li>
	</ul>
</div>
