<div class="productTypes index">
<?php 	
	echo "<h2>".__('Product Types')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('name')."</th>";
				echo "<th>".$this->Paginator->sort('description')."</th>";
				echo "<th>".$this->Paginator->sort('product_category_id')."</th>";
				echo "<th>".$this->Paginator->sort('accounting_code_id')."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($productTypes as $productType){
			echo "<tr>";
				echo "<td>".h($productType['ProductType']['name'])."</td>";
				echo "<td>".h($productType['ProductType']['description'])."</td>";
				echo "<td>".h($productType['ProductCategory']['name'])."</td>";
				if (!empty($productType['AccountingCode']['code'])){
					echo "<td>".$this->Html->link($productType['AccountingCode']['code']." ".$productType['AccountingCode']['description'],array('controller'=>'accounting_codes','action'=>'view',$productType['AccountingCode']['id']))."</td>";
				}
				else {
					echo "<td>-</td>";
				}
				echo "<td class='actions'>";
					echo $this->Html->link(__('View'), array('action' => 'view', $productType['ProductType']['id'])); 
					if ($bool_edit_permission){
						echo $this->Html->link(__('Edit'), array('action' => 'edit', $productType['ProductType']['id'])); 
					}
					if ($bool_delete_permission){					
						// echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productType['ProductType']['id']), array(), __('Are you sure you want to delete # %s?', $productType['ProductType']['id'])); 
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
		echo "<li>".$this->Html->link(__('New Product Type'), array('action' => 'add'))."</li>";
		echo "<br/>";
	}
	if ($bool_product_index_permission) { 
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
	}
	if ($bool_product_add_permission) { 
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
	} 
	echo "</ul>";
?>	
</div>
