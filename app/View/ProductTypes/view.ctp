<div class="productTypes view fullwidth">
<?php 
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo "<div class='col-sm-9'>";	
        echo "<h2>".__('Product Type')." ".$productType['ProductType']['name']."</h2>";
        echo "<dl>";
          echo "<dt>".__('Name')."</dt>";
          echo "<dd>".h($productType['ProductType']['name'])."</dd>";
          echo "<dt>".__('Description')."</dt>";
          echo "<dd>".(empty($productType['ProductType']['description'])?"-":$productType['ProductType']['description'])."</dd>";
          echo "<dt>".__('Accounting Code')."</dt>";
          echo "<dd>".(empty($productType['AccountingCode']['code'])?"-":($this->Html->Link($productType['AccountingCode']['code']." ".$productType['AccountingCode']['description'],['controller'=>'accounting_codes','action'=>'view',$productType['AccountingCode']['id']])))."</dd>";	
        echo "</dl>";
      echo "</div>";
      echo "<div class='col-sm-3'>";	
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
        if ($bool_edit_permission){
          echo "<li>".$this->Html->link(__('Edit Product Type'), ['action' => 'edit', $productType['ProductType']['id']])."</li>";
          echo "<br/>";
        }
        if ($bool_delete_permission) { 
          //echo "<li>".$this->Form->postLink(__('Delete'), ['action' => 'delete', $productType['ProductType']['id']], [], __('Are you sure you want to delete # %s?', $productType['ProductType']['name']))."</li>";
          echo "<br/>";
        }
        echo "<li>".$this->Html->link(__('List Product Types'), ['action' => 'index'])."</li>";
        if ($bool_add_permission) { 
          echo "<li>".$this->Html->link(__('New Product Type'), ['action' => 'add'])."</li>";
        }
        echo "<br/>";
        if ($bool_product_index_permission) { 
          echo "<li>".$this->Html->link(__('List Products'), ['controller' => 'products', 'action' => 'index'])."</li>";
        }
        if ($bool_product_add_permission) { 
          echo "<li>".$this->Html->link(__('New Product'), ['controller' => 'products', 'action' => 'add'])."</li>";
        } 
        echo "</ul>";
      echo "</div>";
    echo "</div>";
    echo "<div class='row'>";
      echo "<div class='col-sm-12'>";	
        
        if (!empty($productType['Product'])){
          echo "<h3>".__('Related Products for Product Type')."</h3>";
          
          echo "<table cellpadding = '0' cellspacing = '0'>";
          echo "<tr>"; 
            echo"<th>".__('Name')."</th>";
            echo "<th class='actions'>".__('Actions')."</th>";
          echo "</tr>";
          foreach ($productType['Product'] as $product){
            echo "<tr>";
              echo "<td>".$product['name']."</td>";
              echo "<td class='actions'>";
                echo $this->Html->link(__('View'), ['controller' => 'products', 'action' => 'view', $product['id']]);
                if ($userrole != ROLE_FOREMAN){ 
                  echo $this->Html->link(__('Edit'), ['controller' => 'products', 'action' => 'edit', $product['id']]);
                }
              echo "</td>";
            echo "</tr>";
          }
          echo "</table>";
        }
      echo "</div>";
    echo "</div>";
  echo "</div>";    
?>