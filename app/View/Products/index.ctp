<div class="products index fullwidth">
<?php
	echo "<h1>".__('Products')."</h1>";
  
  echo '<div class="container-fluid">';
    echo '<div class="row">';
      echo '<div class="col-sm-9">';
        echo $this->Form->create('Report',['style'=>'width:100%']); 
        echo "<fieldset>";
          echo $this->Form->input('Report.product_nature_id',['value'=>$productNatureId,'empty'=>[0=>'-- Naturaleza de Producto --']]);
          echo $this->Form->input('Report.production_type_id',['value'=>$productionTypeId,'empty'=>[0=>'-- Tipo de Producción --']]);
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          
          //echo $this->Form->input('Report.product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
        echo  "</fieldset>";
        echo $this->Form->end(__('Refresh')); 
      echo '</div>';
      echo '<div class="col-sm-3">';
        echo "<h3>".__('Actions')."</h3>";
        echo '<ul style="list-style:none">';
          if ($bool_add_permission){
            echo "<li>".$this->Html->link(__('New Product'), ['action' => 'add'])."</li>";
            echo "<br/>";
          }
          if ($bool_productnature_resumen_permission){
            echo '<br/>';
            echo "<li>".$this->Html->link(__('List Product Natures'), ['controller' => 'productNatures', 'action' => 'resumen'])."</li>";
          }
          if ($bool_productnature_crear_permission){
            echo "<li>".$this->Html->link(__('New Product Nature'), ['controller' => 'productNatures', 'action' => 'crear'])."</li>";
          }
          if ($bool_producttype_index_permission){
            echo '<br/>';
            echo "<li>".$this->Html->link(__('List Product Types'), ['controller' => 'productTypes', 'action' => 'index'])."</li>";
          }
          if ($bool_producttype_add_permission){
            echo "<li>".$this->Html->link(__('New Product Type'), ['controller' => 'productTypes', 'action' => 'add'])."</li>";
          }
        echo "</ul>";
      echo '</div>';
    echo '</div>';
    echo '<p class="comment">Productos desactivados <span class="italic">aparecen en cursivo</span></p>';
    echo '<div class="row">';
      foreach ($productsByNature as $productNature){
        if (empty($productNature['Products'])){
          echo '<div class="col-sm-12">';
            echo '<h2>No hay productos de naturaleza '.$productNature['ProductNature']['name'].'</h2>';
          echo '</div>';
        }
        else {
          $productTableHead='';
          $productTableHead.='<thead>';
            $productTableHead.='<tr>'; 
              $productTableHead.='<th style="min-width:250px;">Tipo Producto</th>'; 
              $productTableHead.='<th style="min-width:400px;">Nombre</th>'; 
              $productTableHead.='<th style="min-width:180px;">'.__('Packaging Unit').'</th>'; 
            if ($productNature['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
              $productTableHead.='<th>Materia Prima Preferida</th>'; 
              $productTableHead.='<th>Bolsa</th>'; 
              $productTableHead.='<th>Producción Aceptable</th>'; 
            }  
              $productTableHead.='<th class="actions">'.__('Actions').'</th>'; 
            $productTableHead.='</tr>'; 
          $productTableHead.='</thead>'; 
          
          $productTableBody='';
          $productTableBody.='<tbody>';
          foreach ($productNature['Products'] as $product){
            $acceptableProductionValue=($product['ProductType']['ProductCategory']['id']==CATEGORY_PRODUCED?
              '-':  (empty($product['ProductProduction'])?0:$product['ProductProduction'][0]['acceptable_production']));
              
            $productTableBody.='<tr'.($product['Product']['bool_active']?"":" class='italic'").'>';
              $productTableBody.='<td>'.$this->Html->link($product['ProductType']['name'], ['controller' => 'productTypes', 'action' => 'view', $product['ProductType']['id']]).'</td>';
              $productTableBody.='<td>'.$this->Html->link($product['Product']['name'], ['action' => 'view', $product['Product']['id']]).'</td>';
              $productTableBody.='<td>'.$product['Product']['packaging_unit'].'&nbsp;</td>';
            if ($productNature['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
              $productTableBody.='<td>'.(empty($product['PreferredRawMaterial']['name'])?"-":$product['PreferredRawMaterial']['name']).'</td>';
              $productTableBody.='<td>'.(empty($product['BagProduct']['name'])?"-":$product['BagProduct']['name']).'</td>';
              $productTableBody.='<td>'.$acceptableProductionValue.'</td>';
            }
              $productTableBody.='<td class="actions">';
                if ($bool_edit_permission){ 
                  $productTableBody.=$this->Html->link(__('Edit'), ['action' => 'edit', $product['Product']['id']]); 
                }
              $productTableBody.='</td>';
            $productTableBody.='</tr>';
          }	
          $productTableBody.='</tbody>';
          echo '<div class="col-sm-12">';
            echo '<h2>Productos de naturaleza '.$productNature['ProductNature']['name'].'</h2>';
            echo '<table id="'.$productNature['ProductNature']['name'].'" style="width:100%;">'.$productTableHead.$productTableBody.'</table>';
          echo '</div>';  
        }
      }
      
      if (!empty($productsWithoutNature)){
        $productTableHead='';
        $productTableHead.='<thead>';
          $productTableHead.='<tr>'; 
            $productTableHead.='<th style="min-width:250px;">Tipo Producto</th>'; 
            $productTableHead.='<th style="min-width:400px;">Nombre</th>'; 
            $productTableHead.='<th style="min-width:180px;">'.__('Packaging Unit').'</th>'; 
            $productTableHead.='<th class="actions">'.__('Actions').'</th>'; 
          $productTableHead.='</tr>'; 
        $productTableHead.='</thead>'; 
        
        $productTableBody='';
        $productTableBody.='<tbody>';
        foreach ($productsWithoutNature as $product){
          $acceptableProductionValue=($product['ProductType']['ProductCategory']['id']==CATEGORY_PRODUCED?
            '-':  (empty($product['ProductProduction'])?0:$product['ProductProduction'][0]['acceptable_production']));
            
          $productTableBody.='<tr'.($product['Product']['bool_active']?"":" class='italic'").'>';
            $productTableBody.='<td>'.$this->Html->link($product['ProductType']['name'], ['controller' => 'productTypes', 'action' => 'view', $product['ProductType']['id']]).'</td>';
            $productTableBody.='<td>'.$this->Html->link($product['Product']['name'], ['action' => 'view', $product['Product']['id']]).'</td>';
            $productTableBody.='<td>'.$product['Product']['packaging_unit'].'&nbsp;</td>';
            $productTableBody.='<td class="actions">';
              if ($bool_edit_permission){ 
                $productTableBody.=$this->Html->link(__('Edit'), ['action' => 'edit', $product['Product']['id']]); 
              }
            $productTableBody.='</td>';
          $productTableBody.='</tr>';
        }	
        $productTableBody.='</tbody>';
        echo '<div class="col-sm-12">';
          echo '<h2>Productos sin naturaleza</h2>';
          echo '<table id="otros" style="width:100%;">'.$productTableHead.$productTableBody.'</table>';
        echo '</div>';  
      }
      
      echo '</div>';
    echo '</div>';
  echo '</div>';  
?>
</div>