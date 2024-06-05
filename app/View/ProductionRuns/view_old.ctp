<?php 
	echo "<div class='productionRuns view'>";
	$outputheader="";
	$outputheader.="<h2>".__('Production Run')."</h2>";
	$outputheader.="<dl>";
	$outputheader.="<!--dt>".__('Id')."</dt-->";
	$outputheader.="<!--dd>";
	$outputheader.="".h($productionRun['ProductionRun']['id'])."&nbsp;";
	$outputheader.="</dd-->";
	$outputheader.="<dt>".__('Production Run Code')."</dt>";
	$outputheader.="<dd>";
	$outputheader.=h($productionRun['ProductionRun']['production_run_code'])."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Date')."</dt>";
	$outputheader.="<dd>";
	$productionrundate= new DateTime($productionRun['ProductionRun']['production_run_date']); 
	$outputheader.=$productionrundate->format('d-m-Y'); 
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Product')."</dt>";
	$outputheader.="<dd>";
	$outputheader.=$this->Html->link($productionRun['FinishedProduct']['name'], array('controller' => 'products', 'action' => 'view', $productionRun['FinishedProduct']['id']))."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Product Quantity')."</dt>";
	$outputheader.="<dd>";
	$outputheader.="".h($productionRun['ProductionRun']['raw_material_quantity'])."&nbsp;";
	$outputheader.="</dd>";
	
	$outputheader.="<dt>".__('Machine')."</dt>";
	$outputheader.="<dd>";
	$outputheader.=$this->Html->link($productionRun['Machine']['name'], array('controller' => 'machines', 'action' => 'view', $productionRun['Machine']['id']))."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Operator')."</dt>";
	$outputheader.="<dd>";
	$outputheader.=$this->Html->link($productionRun['Operator']['name'], array('controller' => 'operators', 'action' => 'view', $productionRun['Operator']['id']))."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Shift')."</dt>";
	$outputheader.="<dd>";
	$outputheader.="".$this->Html->link($productionRun['Shift']['name'], array('controller' => 'shifts', 'action' => 'view', $productionRun['Shift']['id']))."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<dt>".__('Energy Use')."</dt>";
	$outputheader.="<dd>";
	$outputheader.=h($productionRun['ProductionRun']['meter_finish']-$productionRun['ProductionRun']['meter_start'])."&nbsp;";
	$outputheader.="</dd>";
	$outputheader.="<!--dt>".__('Created')."</dt-->";
	$outputheader.="<!--dd>";
	$outputheader.=h($productionRun['ProductionRun']['created'])."&nbsp;";
	$outputheader.="</dd-->";
	$outputheader.="<!--dt>".__('Modified')."</dt-->";
	$outputheader.="<!--dd>";
	$outputheader.="h($productionRun['ProductionRun']['modified'])."&nbsp;";
	$outputheader.="</dd-->";
	$outputheader.=</dl>";
	echo $outputheader;
	$namepdf="Compra_".$productionRun['ProductionRun']['production_run_code'].".pdf";
	echo "<div class='righttop'>".$this->Html->link(__('Guardar como pdf'), array('action' => 'downloadPdf',$namepdf),array( 'class' => 'btn btn-primary'))."</div>";
	echo "</div>";
	echo $this->InventoryCountDisplay->showInventoryTotals($rawMaterialsInventory,CATEGORY_RAW,'Preformas en bodega'); 
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Production Run'), array('action' => 'edit', $productionRun['ProductionRun']['id'])); ?> </li>
		
		<!--li><?php // echo $this->Form->postLink(__('Delete Production Run'), array('action' => 'delete', $productionRun['ProductionRun']['id']), array(), __('Are you sure you want to delete # %s?', $productionRun['ProductionRun']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Production Runs'), array('action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Production Run'), array('action' => 'add')); ?> </li>
		<?php } ?>
		<br/>
		<li><?php echo $this->Html->link(__('List Stock Items'), array('controller' => 'stock_items', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<!--li><?php // echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li-->
		<?php } ?>
		<?php /* ?>
		<h3><?php echo __('Configuration Options'); ?></h3>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Machines'), array('controller' => 'machines', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Machine'), array('controller' => 'machines', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Operators'), array('controller' => 'operators', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Operator'), array('controller' => 'operators', 'action' => 'add')); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Shifts'), array('controller' => 'shifts', 'action' => 'index')); ?> </li>
		<?php if ($userrole!=ROLE_FOREMAN){ ?>
		<li><?php echo $this->Html->link(__('New Shift'), array('controller' => 'shifts', 'action' => 'add')); ?> </li>
		<?php } ?>
		<?php */ ?>
		
	</ul>
</div>
<div class="related">
<?php 
	if (empty($productionRun['ProductionMovement'])):
		$outputraw="";
		$outputraw.="<h3>".__('Raw Materials scheduled for Production Run')."</h3>";
		$outputraw.="<table cellpadding = "0" cellspacing = "0">";
		$outputraw.="<tr>";
		$outputraw.="<th>".__('Id')."</th>";
		$outputraw.="<th>".__('Name')."</th>";
		$outputraw.="<th class='centered'>".__('Quantity Present')."</th>";
		$outputraw.="<th class='centered'>".__('Used Quantity')."</th>";
		$outputraw.="<th class='centered'>".__('Remaining Quantity')."</th>";
		$outputraw.="<!--th class="actions">".__('Actions')."</th-->";
		$outputraw.="</tr>";
		foreach ($usedRawMaterials as $usedRawMaterial): 
			$outputraw.="<tr>";
			$outputraw.="<td>".$usedRawMaterial['id']."</td>";
			$outputraw.="<td>".$usedRawMaterial['name']."</td>";
			$outputraw.="<td class='centered'>".$usedRawMaterial['quantity_present']."</td>";
			$outputraw.="<td class='centered'>".$usedRawMaterial['quantity_used']."</td>";
			$outputraw.="<td class='centered'>".$usedRawMaterial['quantity_remaining']."</td>";
			$outputraw.="</tr>";
		endforeach; 
		$outputraw.="</table>";
	endif;

	if (!empty($productionRun['ProductionMovement'])):
		$rawrows="";
		$finishedrows="";
		$totalPrice=0;
		$totalProducts=0;
		$totalUsed=0;
		$totalRemaining=0;
		foreach ($productionRun['ProductionMovement'] as $productionMovement):
			if ($productionMovement['bool_input']){
				if ($productionMovement['product_quantity']>0){
					$rawrows.="<tr>";
					$rawrows.="<td>".$productionMovement['StockItem']['name']."</td>";
					$rawrows.="<td class='centered'>".$productionMovement['product_quantity']."</td>";
					$rawrows.="<td class='centered'>".$productionMovement['product_unit_price']." C$</td>";
					$rawrows.="<td class='centered'>".$productionMovement['StockItem']['remaining_quantity']."</td>";
					$totalUsed+=$productionMovement['product_quantity'];
					$totalRemaining+=$productionMovement['StockItem']['remaining_quantity'];
					$rawrows.="</tr>";
				}
			}
			else {
				$resultquality="";
				switch ($productionMovement['StockItem']['production_result_code_id']){
					case 1:
						$resultquality="A";
						break;
					case 2:
						$resultquality="B";
						break;
					case 3:
						$resultquality="C";
						break;
					default:
						$resultquality=$productionMovement['StockItem']['production_result_code_id'];
						
				}	
				
				$finishedrows.="<tr>";
				$finishedrows.="<td>".$productionMovement['StockItem']['name']."</td>";
				$finishedrows.="<td class='centered'>".$productionMovement['product_quantity']."</td>";
				$finishedrows.="<td class='centered'>".$resultquality."</td>";
				$finishedrows.="<td class='centered'>".$productionMovement['product_unit_price']." C$</td>";
				$totalPriceProduct=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
				$finishedrows.="<td class='centered'>".round($totalPriceProduct)." C$</td>";
				$finishedrows.="<td>".$productionRun['RawMaterial']['name']."</td>";
				$finishedrows.="</tr>";
				$totalPrice+=$totalPriceProduct;
				$totalProducts+=$productionMovement['product_quantity'];
			}
		endforeach;
		$rawrows.="<tr class='totalrow'>";
		$rawrows.="<td>Total</td>";
		$rawrows.="<td class='centered'>".$totalUsed."</td>";
		$rawrows.="<td></td>";
		$rawrows.="<td class='centered'>".$totalRemaining."</td>";
		$rawrows.="</tr>";
		$finishedrows.="<tr class='totalrow'>";
		$finishedrows.="<td>Total</td>";
		$finishedrows.="<td class='centered'>".$totalProducts."</td>";
		$finishedrows.="<td class='centered'></td>";
		$finishedrows.="<td class='centered'></td>";
		$finishedrows.="<td class='centered'>".round($totalPrice)." C$</td>";
		$finishedrows.="<td></td>";
		$finishedrows.="</tr>";
				
	?>
	<h3><?php echo __('Raw Materials used in Production Run'); ?></h3>
	<table cellpadding = "0" cellspacing = "0">
		<thead>
			<tr>
				<th><?php echo __('Identificación Lote'); ?></th>
				<th class='centered'><?php echo __('Used Quantity'); ?></th>
				<th class='centered'><?php echo __('Unit Cost'); ?></th>
				<th class='centered'><?php echo __('Remaining Quantity'); ?></th>
				<!--th class="actions"><?php echo __('Actions'); ?></th-->
			</tr>
		</thead>
		<tbody>
	<?php echo $rawrows; ?>
		</tbody>
	</table>
	<h3><?php echo __('Products Produced in Production Run'); ?></h3>
	<table cellpadding = "0" cellspacing = "0">
		<thead>
			<tr>
				<th><?php echo __('Name'); ?></th>
				<th class='centered'><?php echo __('Produced Quantity'); ?></th>
				<th class='centered'><?php echo __('Quality'); ?></th>
				<th class='centered'><?php echo __('Unit Cost'); ?></th>
				<th class='centered'><?php echo __('Total Cost'); ?></th>
				<th><?php echo __('Raw Material'); ?></th>
				<!--th class="actions"><?php echo __('Actions'); ?></th-->
			</tr>
		</thead>
		<tbody>
	<?php echo $finishedrows; ?>
		</tbody>
	</table>
<?php endif; ?>
	<!--div class="actions">
		<ul>
			<!--li><?php // echo $this->Html->link(__('New Stock Item'), array('controller' => 'stock_items', 'action' => 'add')); ?> </li-->
		</ul>
	</div-->
</div>
