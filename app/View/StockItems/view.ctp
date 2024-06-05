<div class="stockItems view">
<?php 
  $stockItemCreatedDateTime=new DateTime($stockItem['StockItem']['created']);
  $stockItemModifiedDateTime=new DateTime($stockItem['StockItem']['modified']);

  echo '<h1>Lote '.$stockItem['Product']['name'].'</h1>';
  echo  '<h2>Creado '.($stockItemCreatedDateTime->format('d-m-Y H:i')).' ('.$stockItem['Warehouse']['name'].')</h2>';
	echo '<dl class="dl100">';
		echo '<dt>'.__('Name').'</dt>';
		echo '<dd>'.h($stockItem['StockItem']['name']).'</dd>';
		echo '<dt>'.__('Description').'</dt>';
		echo '<dd>'.h($stockItem['StockItem']['description']).'</dd>';
		echo '<dt>'.__('Product').'</dt>';
		echo '<dd>'.$this->Html->link($stockItem['Product']['name'], ['controller' => 'products', 'action' => 'view', $stockItem['Product']['id']]).'</dd>';
		echo '<dt>'.__('Warehouse').'</dt>';
		echo '<dd>'.$this->Html->link($stockItem['Warehouse']['name'], ['controller' => 'warehouses', 'action' => 'detalle', $stockItem['Warehouse']['id']]).'</dd>';
		echo '<dt>'.__('Unit Price').'</dt>';
		echo '<dd>'.h($stockItem['StockItem']['product_unit_price']).'</dd>';
		echo '<dt>'.__('Original Quantity').'</dt>';
		echo '<dd>'.h($stockItem['StockItem']['original_quantity']).'</dd>';
		echo '<dt>'.__('StockItem Remaining Quantity').'</dt>';
		echo '<dd>'.h($stockItem['StockItem']['remaining_quantity']).'</dd>';
		if (!empty($stockItem['ProductionResultCode']['code']) != ""){
      echo '<dt>'.__('Production Result Code').'</dt>';
      echo '<dd>'.$this->Html->link($stockItem['ProductionResultCode']['code'], ['controller' => 'productionResultCodes', 'action' => 'view', $stockItem['ProductionResultCode']['id']]).'</dd>';
		}
		echo '<dt>'.__('Created').'</dt>';
		echo '<dd>'.$stockItemCreatedDateTime->format('d-m-Y H:i').'</dd>';
		echo '<dt>'.__('Modified').'</dt>';
		echo '<dd>'.$stockItemModifiedDateTime->format('d-m-Y H:i').'</dd>';
	echo '</dl>';
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if($userRoleId == ROLE_ADMIN) {
      echo '<li>'.$this->Html->link(__('Edit Stock Item'), ['action' => 'edit', $stockItem['StockItem']['id']]).' </li>';
		}
		echo '<li>'.$this->Html->link(__('List Stock Items'), ['action' => 'index']).' </li>';
		echo '<li>'.$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'index']).' </li>';
		if($userRoleId == ROLE_ADMIN) {
      echo '<li>'.$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'add']).' </li>';
		}
		echo '<li>'.$this->Html->link(__('List Production Movements'), ['controller' => 'productionMovements', 'action' => 'index']).' </li>';
		echo '<li>'.$this->Html->link(__('List Stock Movements'), ['controller' => 'stockMovements', 'action' => 'index']).' </li>';
		
	echo '</ul>';
?>
</div>

<div class="related">
<?php 
	
  if (!empty($stockItem['ProductionMovement'])){
    switch ($stockItem['Product']['ProductType']['product_category_id']){
      case CATEGORY_RAW:
        echo "<h3>".__('Used in Production Runs')."</h3>";
        break;
      case CATEGORY_PRODUCED:
        echo "<h3>".__('Produced in Production Run')."</h3>";
        break;
      default:
        echo "<h3>".__('Related Production Movements')."</h3>";
    }
    echo '<table cellpadding = "0" cellspacing = "0">';
      echo '<thead>';
        echo '<tr>';
          echo '<th>'.__('Movement Date').'</th>';
          echo '<th>'.__('Name').'</th>';
          echo '<th>'.__('Production Run Code').'</th>';
          echo '<th>'.__('Product Unit Price').'</th>';
          echo '<th>';
            switch ($stockItem['Product']['ProductType']['product_category_id']){
              case CATEGORY_RAW:
                echo __('Product Quantity Used');
                break;
              case CATEGORY_PRODUCED:
                echo __('Product Quantity Produced');
                break;
              default:
                echo __('Product Quantity');
            }
          echo '</th>';
          echo '<th>'.__('Total Price').'</th>';
        echo '</tr>';
      echo '</thead>';
  
      $quantityProducts=0; 
      $valueProducts=0; 
      echo '<tbody>';
      foreach ($stockItem['ProductionMovement'] as $productionMovement){
        $productionMovementDateTime=new DateTime($productionMovement['movement_date']);
        if ($productionMovement['bool_input']){
          $valueProducts+=$productionMovement['product_unit_price']*$productionMovement['product_quantity']; 
          $quantityProducts+=$productionMovement['product_quantity']; 
        }
        else {
          $valueProducts-=$productionMovement['product_unit_price']*$productionMovement['product_quantity']; 
          $quantityProducts-=$productionMovement['product_quantity']; 
        }
        echo '<tr>';
          echo '<td>'.$productionMovementDateTime->format('d-m-Y').'</td>';
          echo '<td>'.$productionMovement['name'].'</td>';
          echo '<td>'.$this->Html->Link($productionMovement['ProductionRun']['production_run_code'],['controller'=>'productionRuns','action'=>'detalle',$productionMovement['ProductionRun']['id']]).'</td>';
          echo '<td>'.round($productionMovement['product_unit_price'],2).'</td>';
          echo '<td>'.$productionMovement['product_quantity'].'</td>';
          echo '<td>'.round($productionMovement['product_quantity']*$productionMovement['product_unit_price'],0).'</td>';
        echo '</tr>';
      }
        echo '<tr class="totalrow">';
          echo '<td>Total</td>';
          echo '<td></td>';
          echo '<td></td>';
          echo '<td></td>';
          echo '<td>'.round($quantityProducts,2).'</td>';
          echo '<td>'.round($valueProducts,0).'</td>';
        echo '</tr>';
      echo '</tbody>';
    echo '</table>';
  }
  if (!empty($stockItem['StockMovement'])){
    switch ($stockItem['Product']['ProductType']['product_category_id']){
      case CATEGORY_RAW:
        echo "<h3>".__('Acquired in Purchase')."</h3>";
        break;
      case CATEGORY_PRODUCED:
        echo "<h3>".__('Exited in Exit Order')."</h3>";
        break;
      default:
        echo "<h3>".__('Related Stock Movements')."</h3>";
    }
	
    echo '<table cellpadding = "0" cellspacing = "0">';
      echo '<thead>';
        echo '<tr>';
          echo '<th>';
            switch ($stockItem['Product']['ProductType']['product_category_id']){
              case CATEGORY_RAW:
                echo __('Purchase Date');
                break;
              case CATEGORY_PRODUCED:
                echo __('Exit Date');
                break;
              default:
                echo __('Movement Date');
            }
          echo '</th>';
          echo '<th>'.__('Name').'</th>';
          echo '<th>'.__('Order Code').'</th>';
          echo '<th>'.__('Product Unit Price').'</th>';
          echo '<th>';
            switch ($stockItem['Product']['ProductType']['product_category_id']){
              case CATEGORY_RAW:
                echo __('Product Quantity Bought');
                break;
              case CATEGORY_PRODUCED:
                echo __('Product Quantity Sold');
                break;
              default:
                echo __('Product Quantity');
            }
          echo '</th>';
          echo '<th>'.__('Total Price').'</th>';
        echo '</tr>';
      echo '</thead>';
      
      $valueProducts=0;
      $quantityProducts=0; 
      echo '<tbody>';
      foreach ($stockItem['StockMovement'] as $stockMovement){
        $stockMovementDateTime=new DateTime($stockMovement['movement_date']);
        if ($stockMovement['bool_input']){
          $quantityProducts+=$stockMovement['product_quantity']; 
          $valueProducts+=$stockMovement['product_quantity']*$stockMovement['product_unit_price']; 
        }
        else {
          $quantityProducts-=$stockMovement['product_quantity']; 
          $valueProducts-=$stockMovement['product_quantity']*$stockMovement['product_unit_price']; 
        }
        echo '<tr>';
          echo '<td>'.$stockMovementDateTime->format('d-m-Y').'</td>';
          echo '<td>'.$stockMovement['name'].'</td>';
          echo '<td>';
          if (!empty($stockMovement['Order'])){
            echo $stockMovement['Order']['order_code'];
          }
          elseif (!empty($stockMovement['reclassification_code'])){
            echo "Reclasificación ".$stockMovement['reclassification_code'];
          }
          elseif (!empty($stockMovement['transfer_code'])){
            echo "Transferencia ".$stockMovement['transfer_code'];
          }
          elseif (!empty($stockMovement['adjustment_code'])){
            echo "Ajuste ".$stockMovement['adjustment_code'];
          }
          else {
            echo "-";
          }  
          echo '</td>';
          echo '<td>'.round($stockMovement['product_unit_price'],2).'</td>';
          echo '<td>'.$stockMovement['product_quantity'].'</td>';
          echo '<td>'.round($stockMovement['product_quantity']*$stockMovement['product_unit_price'],0).'</td>';
        echo '</tr>';
      }
        echo '<tr class="totalrow">';
          echo '<td>Total</td>';
          echo '<td></td>';
          echo '<td></td>';
          echo '<td></td>';
          echo '<td>'.round($quantityProducts,2).'</td>';
          echo '<td>'.round($valueProducts,0).'</td>';
          echo '<td></td>';
        echo '</tr>';
      echo '</tbody>';
    echo '</table>';
  }
?>
</div>
