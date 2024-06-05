<div class="operators view fullwidth">
<?php 
	echo "<h2>Reporte Producción Total</h2>";
	echo $this->Form->create('Report');
	echo "<fieldset>"; 
		echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
		echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
	echo "</fieldset>";
	echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
	echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
	echo $this->Form->end(__('Refresh')); 
?>
</div>
<!--div class='actions'>
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<?php 
		if ($userrole==ROLE_ADMIN){ 
			echo "<li>".$this->Html->link(__('Edit Operator'), array('action' => 'edit', $operator['Operator']['id']))."</li>";
		} 
		echo "<li>".$this->Form->postLink(__('Delete Operator'), array('action' => 'delete', $operator['Operator']['id']), array(), __('Are you sure you want to delete # %s?', $operator['Operator']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Operators'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Operator'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))."</li>";
		echo "<br/>";
		foreach ($otherOperators as $otherOperator){
			echo "<li>".$this->Html->link($otherOperator['Operator']['name'], array('controller' => 'Operators', 'action' => 'view',$otherOperator['Operator']['id']))."</li>";
		}
	?>
	</ul>
</div-->
<div class="related">
<?php 
	if (!empty($operators)){
		//pr($operators);
		echo "<h3>Productos fabricados en el período por operador</h3>";
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Operator')."</th>";
					foreach ($productionResultCodes as $productionResultCode){
						echo "<th class='centered'>".$productionResultCode['ProductionResultCode']['code']."</th>";
					}
				echo "</tr>";
			echo "</thead>";
			
			echo "<tbody>";
			
			$totalQuantityA=0;
			$totalQuantityB=0;
			$totalQuantityC=0;
			
			$productOverview="";
			foreach ($operators as $operator){
				$productQuantity=0;
				$productRow="";
				$productRow.="<tr>";
					$productRow.="<td>".$this->Html->link($operator['Operator']['name'], array('controller' => 'operators','action' => 'view',$operator['Operator']['id']))."</td>";
					foreach ($operator['productionresultcodes'] as $productionResultCode){
						$productRow.="<td class='centered number'>".$productionResultCode['total_produced']."</td>";
						switch ($productionResultCode['ProductionResultCode']['code']){
							case "A":
								$totalQuantityA+=$productionResultCode['total_produced'];
								break;
							case "B":
								$totalQuantityB+=$productionResultCode['total_produced'];
								break;
							case "C":
								$totalQuantityC+=$productionResultCode['total_produced'];
								break;
						}
						$productQuantity+=$productionResultCode['total_produced'];
					}
				$productRow.="</tr>";
				if ($productQuantity>0){
					$productOverview.=$productRow;
				}
			}
			
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityB."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityC."</td>";
				$totalRows.="</tr>";
				
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					if (($totalQuantityA+$totalQuantityB+$totalQuantityC)>0){
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityA/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityB/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityC/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";					
					}
				$totalRows.="</tr>";
			echo $totalRows.$productOverview.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}

	if (!empty($machines)){
		echo "<h3>Productos fabricados en el período por máquina</h3>";
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Machine')."</th>";
					foreach ($productionResultCodes as $productionResultCode){
						echo "<th class='centered'>".$productionResultCode['ProductionResultCode']['code']."</th>";
					}
				echo "</tr>";
			echo "</thead>";
			
			echo "<tbody>";
			
			$totalQuantityA=0;
			$totalQuantityB=0;
			$totalQuantityC=0;
	
			$productOverview="";
			foreach ($machines as $machine){
				$productQuantity=0;
				$productRow="";
				$productRow.="<tr>";
					$productRow.="<td>".$this->Html->link($machine['Machine']['name'], array('controller' => 'machines','action' => 'view',$machine['Machine']['id']))."</td>";
					foreach ($machine['productionresultcodes'] as $productionResultCode){
						$productRow.="<td class='centered number'>".$productionResultCode['total_produced']."</td>";
						
						switch ($productionResultCode['ProductionResultCode']['code']){
							case "A":
								$totalQuantityA+=$productionResultCode['total_produced'];
								break;
							case "B":
								$totalQuantityB+=$productionResultCode['total_produced'];
								break;
							case "C":
								$totalQuantityC+=$productionResultCode['total_produced'];
								break;
						}
						$productQuantity+=$productionResultCode['total_produced'];
					}
				$productRow.="</tr>";
				if ($productQuantity>0){
					$productOverview.=$productRow;
				}
			}
			
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityB."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityC."</td>";
				$totalRows.="</tr>";
				
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					if (($totalQuantityA+$totalQuantityB+$totalQuantityC)>0){
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityA/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityB/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityC/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";					
					}
				$totalRows.="</tr>";
			echo $totalRows.$productOverview.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}
	
	if (!empty($shifts)){
		echo "<h3>Productos fabricados en el período por turno</h3>";
		echo "<table>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Shift')."</th>";
					foreach ($productionResultCodes as $productionResultCode){
						echo "<th class='centered'>".$productionResultCode['ProductionResultCode']['code']."</th>";
					}
				echo "</tr>";
			echo "</thead>";
			
			echo "<tbody>";
			
			$totalQuantityA=0;
			$totalQuantityB=0;
			$totalQuantityC=0;
	
			
			$productOverview="";
			foreach ($shifts as $shift){
				$productQuantity=0;
				$productRow="";
				$productRow.="<tr>";
					$productRow.="<td>".$this->Html->link($shift['Shift']['name'], array('controller' => 'shifts','action' => 'view',$shift['Shift']['id']))."</td>";
					foreach ($shift['productionresultcodes'] as $productionResultCode){
						$productRow.="<td class='centered number'>".$productionResultCode['total_produced']."</td>";
						
						switch ($productionResultCode['ProductionResultCode']['code']){
							case "A":
								$totalQuantityA+=$productionResultCode['total_produced'];
								break;
							case "B":
								$totalQuantityB+=$productionResultCode['total_produced'];
								break;
							case "C":
								$totalQuantityC+=$productionResultCode['total_produced'];
								break;
						}
					$productQuantity+=$productionResultCode['total_produced'];
					}
				$productRow.="</tr>";
				if ($productQuantity>0){
					$productOverview.=$productRow;
				}
			}
			
				$totalRows="";
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Total</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityA."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityB."</td>";
					$totalRows.="<td class='centered number'>".$totalQuantityC."</td>";
				$totalRows.="</tr>";
				
				$totalRows.="<tr class='totalrow'>";
					$totalRows.="<td>Porcentajes</td>";
					if (($totalQuantityA+$totalQuantityB+$totalQuantityC)>0){
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityA/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityB/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
						$totalRows.="<td class='centered percentage'><span>".(100*$totalQuantityC/($totalQuantityA+$totalQuantityB+$totalQuantityC))."</span></td>";
					}
					else {
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";
						$totalRows.="<td class='centered percentage'><span>100</span></td>";					
					}
				$totalRows.="</tr>";
			echo $totalRows.$productOverview.$totalRows;
			echo "</tbody>";
		echo "</table>";
	}
?>

</div>
<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
		
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCurrencies();
		formatPercentages();
	});

</script>