<script>
	function formatNumbers(){
		$("td.number").each(function(){
			$(this).number(true,2);
		});
	}
	
	function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatPercentages();
		formatCurrencies();
	});
</script>
<div class="products view fullwidth">
<?php 
    echo "<div class='container-fluid'>";
    echo  "<div class='row' style='margin:0'>";
      echo "<div class='col-sm-8 col-lg-9 style='clear:none;'>";
        echo  "<div class='row'>";
          //pr($product);
          echo "<h2>".__('Product')." ".$product['Product']['name']." (".($product['Product']['bool_active']?"Activo":"Desactivado").")</h2>";
          echo $this->Form->create('Report'); 
          echo "<fieldset>";
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
          echo "</fieldset>";
          echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
          echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
          echo $this->Form->end(__('Refresh')); 
        echo "</div>";
        echo  "<div class='row'>";
          echo "<dl>";
            echo "<dt>". __('Name')."</dt>";
            echo "<dd>". h($product['Product']['name'])."</dd>";
            echo "<dt>". __('Abbreviation')."</dt>";
            echo "<dd>". h($product['Product']['abbreviation'])."</dd>";
            echo "<dt>". __('Unit')."</dt>";
            echo "<dd>". h($product['Unit']['name'])."</dd>";
            echo "<dt>". __('Description')."</dt>";
            echo "<dd>".(empty($product['Product']['description'])?"-":h($product['Product']['description']))."</dd>";
            
            echo "<dt>". __('Product Type')."</dt>";
            echo "<dd>". $this->Html->link($product['ProductType']['name'], ['controller' => 'productTypes', 'action' => 'view', $product['ProductType']['id']])."</dd>";
            echo "<dt>". __('Product Nature')."</dt>";
            echo "<dd>". $this->Html->link($product['ProductNature']['name'], ['controller' => 'productNatures', 'action' => 'detalle', $product['ProductNature']['id']])."</dd>";
            if (in_array($product['ProductNature']['id'],[PRODUCT_NATURE_RAW,PRODUCT_NATURE_PRODUCED])){
              echo "<dt>". __('Production Type')."</dt>";
              echo "<dd>". $this->Html->link($product['ProductionType']['name'], ['controller' => 'productionTypes', 'action' => 'detalle', $product['ProductionType']['id']])."</dd>";
            }
            echo "<dt>". __('Packaging Unit')."</dt>";
            echo "<dd>". h($product['Product']['packaging_unit'])."</dd>";
          
            echo "<dt>".__('Accounting Code')."</dt>";
            echo "<dd>".(empty($product['AccountingCode']['code'])?"-":($this->Html->Link($product['AccountingCode']['code']." ".$product['AccountingCode']['description'],['controller'=>'accounting_codes','action'=>'view',$product['AccountingCode']['id']])))."</dd>";
          echo '</dl>';  
        echo "</div>";
      echo "</div>";
      echo "<div class='col-sm-3 col-lg-2 actions'>";
				echo "<h3>".__('Actions')."</h3>"; 
        echo "<ul style='list-style:none;'>";
          if ($bool_edit_permission){
            echo "<li>".$this->Html->link(__('Edit Product'), array('action' => 'edit', $product['Product']['id']))."</li>";
          }
          if ($bool_delete_permission){
            //echo "<li>".$this->Form->postLink(__('Delete'), array('action' => 'delete', $product['Product']['id']), array(), __('Are you sure you want to delete # %s?', $product['Product']['id']))."</li>";
          }
          echo "<li>".$this->Html->link(__('List Products'), array('action' => 'index'))."</li>";
          if ($bool_add_permission){
            echo "<li>".$this->Html->link(__('New Product'), array('action' => 'add'))."</li>";
          }
          echo "<br/>";
          echo "<li>".$this->Html->link(__('Precios de este Producto'), ['controller'=>'productPriceLogs','action' => 'registrarPreciosProducto',$id])."</li>";
          
          echo "<br/>";
          if ($bool_producttype_index_permission){
            echo "<li>".$this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index'))."</li>";
          }
          if ($bool_producttype_add_permission){
            echo "<li>".$this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add'))."</li>";
          }
        echo "</ul>";
      echo '</div>';  
    echo '</div>';   
    
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo '<h3>Características</h3>';
        echo "<dl>";
        if ($product['ProductNature']['id'] == PRODUCT_NATURE_RAW && $product['ProductionType']['id'] == PRODUCTION_TYPE_PET){
          echo '<dt>Volumen min (ml)</dt>';
          echo '<dd>'.$product['Product']['volume_ml_min'].'</dd>';
        }
        if (
          ($product['ProductNature']['id'] == PRODUCT_NATURE_RAW && $product['ProductionType']['id'] == PRODUCTION_TYPE_PET) ||
          ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED && $product['ProductionType']['id'] == PRODUCTION_TYPE_PET) ||
          $product['ProductNature']['id'] == PRODUCT_NATURE_BOTTLES_BOUGHT 
        ){
          echo '<dt>Volumen max (ml)</dt>';
          echo '<dd>'.$product['Product']['volume_ml_max'].'</dd>';
        }
        if ($product['ProductNature']['id'] == PRODUCT_NATURE_RAW && $product['ProductionType']['id'] == PRODUCTION_TYPE_PET){
          echo '<dt>Peso (g)</dt>';
          echo '<dd>'.$product['Product']['weight_g'].'</dd>';
        }
        if ($product['ProductNature']['id'] == PRODUCT_NATURE_ACCESORIES){
          echo '<dt>Diametro (mm)</dt>';
          echo '<dd>'.$product['Product']['diameter_mm'].'</dd>';
        }
        if ($product['ProductNature']['id'] == PRODUCT_NATURE_BAGS && $product['ProductType']['id'] == PRODUCT_TYPE_ROLL){
          echo '<dt>Ancho (pulgadas)</dt>';
          echo '<dd>'.$product['Product']['width_inch'].'</dd>';
        }
        if ($product['ProductNature']['id'] == PRODUCT_NATURE_BAGS){
          echo '<dt>Ancho (pulgadas)</dt>';
          echo '<dd>'.$product['Product']['width_inch'].'</dd>';
          echo '<dt>Largo (pulgadas)</dt>';
          echo '<dd>'.$product['Product']['length_inch'].'</dd>';
          echo '<dt>Alto (pulgadas)</dt>';
          echo '<dd>'.$product['Product']['height_inch'].'</dd>';
        }
        echo "</dl>";
      echo '</div>'; 
      echo '<div class="col-sm-6">';
       if (!empty($productWarehouses)){ 
        echo '<h3>'.__('Warehouses').'</h3>';
        echo '<ul style="list-style:none;">';
        foreach ($productWarehouses as $warehouseId=>$warehouseName){
          echo '<li>'.$warehouseName.'</li>';
        }
        echo "</ul>";
      }  
      echo '</div>';  
    echo '</div>';     
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
      if ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
        echo '<h3>Características</h3>';
        echo "<dl>";
          echo '<dt>Producción Acceptable)</dt>';
          echo '<dd>'.(empty($product['ProductProduction'])?0:$product['ProductProduction'][0]['acceptable_production']).'</dd>';
          echo '<dt>Bolsa Preferida</dt>';
          echo '<dd>'.$product['BagProduct']['name'].'</dd>';
          if ($product['ProductionType']['id'] == PRODUCTION_TYPE_PET){
            echo '<dt>Preforma preferida</dt>';
            echo '<dd>'.$product['PreferredRawMaterial']['name'].'</dd>';
            echo '<dt>Peso preferido (gr)</dt>';
            echo '<dd>'.$product['Product']['preferred_weight_g'].'</dd>';
          }
        echo "</dl>";
      }
      echo '</div>'; 
    /*  
      echo '<div class="col-sm-6">';
      if (!empty($productProductionTypes)){ 
        echo '<h3>'.__('ProductionTypes').'</h3>';
        echo '<ul style="list-style:none;">';
        foreach ($productProductionTypes as $productionTypeId=>$productionTypeName){
          echo '<li>'.$productionTypeName.'</li>';
        }
        echo "</ul>";
      }
      echo '</div>';  
    */  
    echo '</div>';  

    
    echo '<div class="row">';
      echo '<div class="col-sm-6">';
        echo '<h3>Costo</h3>';
        echo '<dl>';    
          echo "<dt>".__('Costo preestablecido')."</dt>";
          echo "<dd>".(empty($product['Product']['default_cost'])?"-":($product['DefaultCostCurrency']['abbreviation']." ".number_format($product['Product']['default_cost'],2,'.',',')))."</dd>";
        echo '</dl>';    
        
      echo '</div>'; 
      if (in_array($product['ProductNature']['id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES])){
        
        echo '<div class="col-sm-6">';
          if ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
            echo '<h3>Precios de Categoría para preforma preferida '.$product['PreferredRawMaterial']['name'].'</h3>';
          }
          else {
            echo '<h3>Precios de Categoría</h3>';
          }
          echo '<dl>';    
          foreach ($priceClientCategories as $priceClientCategoryId =>$priceClientCategoryName){
            echo '<dt>Precio de Categoría '.$priceClientCategoryName.'</dt>';
            echo '<dd>C$ '.number_format($priceClientCategoryPrices[$priceClientCategoryId],4,'.',',').'</dd>';
          }
          echo '</dl>';   
          echo '<br/>';
          //pr($product['ProductThresholdVolume']);
          echo '<h3>Volumen de Venta</h3>';
          echo '<dl>';    
            echo '<dt>Volumen de Venta</dt>';
            echo '<dd>'.(empty($product['ProductThresholdVolume'][0]['id'])?0:$product['ProductThresholdVolume'][0]['threshold_volume']).'</dd>';
          echo '</dl>';   

        echo '</div>';  
      }  
    echo '</div>';     

  echo "</div>";
?>	
</div>
<div class="related">
<?php 
	if (!empty($product['ProductProduction'])){
		echo "<h3>".'Historial de Valores de Producción Aceptable'."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Application Date')."</th>";
					echo "<th class='centered'>".__('Cantidad Aceptable')."</th>";
				echo "</tr>";
			echo "</thead>";		
			echo "<tbody>";
			foreach ($product['ProductProduction'] as $productProduction){
				$applicationDateTime=new DateTime($productProduction['application_date']);
				echo "<tr>";
					echo "<td>".$applicationDateTime->format('d-m-Y')."</td>";
					echo "<td class='centered number'>".$productProduction['acceptable_production']."</td>";
				echo "</tr>";
			}
				
			echo "</tbody>";
		echo "</table>";
	}
?>
</div>
<div class="related">
<?php 
  $totalQuantity=0;
	if (!empty($product['StockMovement'])){
		$table="";
		$table.="<table cellpadding = '0' cellspacing = '0'>";
			$table.="<thead>";
				$table.="<tr>";
					$table.="<th>".__('Entry Date')."</th>";
					$table.="<th>".__('Entry')."</th>";
					$table.="<th class='centered'>".__('Quantity')."</th>";
				$table.="</tr>";
			$table.="</thead>";
			$tableRows="";
      foreach ($ordersForProductInPeriod as $order){
        if ($order['Order']['stock_movement_type_id']==MOVEMENT_PURCHASE){
          $entryDateTime=new DateTime($order['Order']['order_date']);
          $tableRows.="<tr>";
            $tableRows.="<td>".$entryDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$this->Html->link($order['Order']['order_code'], ['controller' => 'orders', 'action' => 'verEntrada', $order['Order']['id']])."</td>";
            $quantityEntered=0;
            foreach ($order['StockMovement'] as $stockMovement){
              if ($stockMovement['product_quantity']>0 && $stockMovement['bool_input']){
                $totalQuantity+=$stockMovement['product_quantity'];
                $quantityEntered+=$stockMovement['product_quantity'];
              }
            }    
            $tableRows.="<td class='centered number'>".$quantityEntered."</td>";
          $tableRows.="</tr>";
        }
      }
				$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
					$totalRow.="<td></td>";
					$totalRow.="<td class='centered number'>".$totalQuantity."</td>";
				$totalRow.="</tr>";
			
			$table.="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
		$table.="</table>";
		if ($totalQuantity>0){
			echo "<h3>".__('Entradas')."</h3>";
			echo $table;
		}
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($product['ProductionMovement'])){
    //echo "product category id is ".$product['ProductType']['product_category_id']."<br>";
		$tableHead="";
    $tableHead.="<thead>";
      $tableHead.="<tr>";
        $tableHead.="<th>".__('Production Run Date')."</th>";
        $tableHead.="<th>".__('Production Run')."</th>";
        
        if ($product['ProductType']['product_category_id']==CATEGORY_RAW){
          $tableHead.="<th class='centered'>".__('Cantidad Utilizado')."</th>";
          
        }
        elseif ($product['ProductType']['product_category_id']==CATEGORY_PRODUCED){
          $tableHead.="<th class='centered'>A</th>";
          $tableHead.="<th class='centered'>B</th>";
          $tableHead.="<th class='centered'>C</th>";
        }
      $tableHead.="</tr>";
    $tableHead.="</thead>";
		
    $tableRows="";
    if ($product['ProductType']['product_category_id']==CATEGORY_RAW){
      $totalQuantity=0;
    }
    elseif ($product['ProductType']['product_category_id']==CATEGORY_PRODUCED){
      $totalQuantityA=0;
      $totalQuantityB=0;
      $totalQuantityC=0;
    }
    //pr($product);
    //pr($productionRunsForProductInPeriod);
    foreach ($productionRunsForProductInPeriod as $productionRun){
      //pr($productionRun);
      $productionRunDateTime=new DateTime($productionRun['ProductionRun']['production_run_date']);
      $tableRows.="<tr>";
        $tableRows.="<td>".$productionRunDateTime->format('d-m-Y')."</td>";
        $tableRows.="<td>".$this->Html->link($productionRun['ProductionRun']['production_run_code'], array('controller' => 'production_runs', 'action' => 'view', $productionRun['ProductionRun']['id']))."</td>";
        if ($product['ProductType']['product_category_id']==CATEGORY_RAW){
          $quantityUsed=0;
          foreach ($productionRun['ProductionMovement'] as $productionMovement){
            //echo "the product quantity used is ".$productionMovement['product_quantity']."<br>";
            $totalQuantity+=$productionMovement['product_quantity'];  
            $quantityUsed+=$productionMovement['product_quantity'];
          }
          $tableRows.="<td class='centered number'>".$quantityUsed."</td>";
        }
        elseif ($product['ProductType']['product_category_id']==CATEGORY_PRODUCED){
          $quantityUsedA=0;
          $quantityUsedB=0;
          $quantityUsedC=0;
          foreach ($productionRun['ProductionMovement'] as $productionMovement){
            switch ($productionMovement['production_result_code_id']){
              case PRODUCTION_RESULT_CODE_A:
                $totalQuantityA+=$productionMovement['product_quantity'];  
                $quantityUsedA+=$productionMovement['product_quantity'];
                break;
              case PRODUCTION_RESULT_CODE_B:
                $totalQuantityB+=$productionMovement['product_quantity'];  
                $quantityUsedB+=$productionMovement['product_quantity'];
                break;  
              case PRODUCTION_RESULT_CODE_C:
                $totalQuantityC+=$productionMovement['product_quantity'];  
                $quantityUsedC+=$productionMovement['product_quantity'];
                break;
            }              
          }
          $tableRows.="<td class='centered number'>".$quantityUsedA."</td>";
          $tableRows.="<td class='centered number'>".$quantityUsedB."</td>";
          $tableRows.="<td class='centered number'>".$quantityUsedC."</td>";
        }
      $tableRows.="</tr>";
    }
    
      $totalRow="";
      $totalRow.="<tr class='totalrow'>";
        $totalRow.="<td>Total</td>";
        $totalRow.="<td></td>";
        if ($product['ProductType']['product_category_id']==CATEGORY_RAW){
          $totalRow.="<td class='centered number'>".$totalQuantity."</td>";
        }
        elseif ($product['ProductType']['product_category_id']==CATEGORY_PRODUCED){
          $totalRow.="<td class='centered number'>".$totalQuantityA."</td>";
          $totalRow.="<td class='centered number'>".$totalQuantityB."</td>";
          $totalRow.="<td class='centered number'>".$totalQuantityC."</td>";
        }
      $totalRow.="</tr>";
    
    if ($product['ProductType']['product_category_id'] == CATEGORY_PRODUCED){
      $totalQuantity=0;
      $totalQuantity+=$totalQuantityA;
      $totalQuantity+=$totalQuantityB;
      $totalQuantity+=$totalQuantityC;
    }
    if ($totalQuantity>0){  
      echo "<table cellpadding = '0' cellspacing = '0'>".$tableHead."<tbody>".$totalRow.$tableRows.$totalRow."</tbody></table>";
    }
	}
?>
</div>
<div class="related">
<?php 
	if (!empty($product['StockMovement'])){
    if ($product['ProductType']['product_category_id'] != CATEGORY_PRODUCED){
      $totalQuantity=0;
    }
    elseif ($product['ProductType']['product_category_id'] == CATEGORY_PRODUCED){
      $totalQuantityA=0;
      $totalQuantityB=0;
      $totalQuantityC=0;
    }
    $table="";
		$table.="<table cellpadding = '0' cellspacing = '0'>";
			$table.="<thead>";
				$table.="<tr>";
					$table.="<th>".__('Fecha Salida')."</th>";
					$table.="<th>".__('Salida')."</th>";
          $table.='<th>Cliente</th>';
          if ($product['ProductType']['product_category_id']!=CATEGORY_PRODUCED){
            $table.="<th class='centered'>".__('Cantidad Utilizado')."</th>";
            
          }
          else{
            $table.="<th class='centered'>A</th>";
            $table.="<th class='centered'>B</th>";
            $table.="<th class='centered'>C</th>";
          }
				$table.="</tr>";
			$table.="</thead>";
			$tableRows="";
      foreach ($ordersForProductInPeriod as $order){
        if ($order['Order']['stock_movement_type_id']==MOVEMENT_SALE){
          $saleDateTime=new DateTime($order['Order']['order_date']);
          $tableRows.="<tr>";
            $tableRows.="<td>".$saleDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$this->Html->link($order['Order']['order_code'], array('controller' => 'orders', 'action' => 'verEntrada', $order['Order']['id']))."</td>";
            $tableRows.='<td>'.(
              $order['ThirdParty']['bool_generic']?
              $order['Order']['client_name']:
              $this->Html->link(
                $order['ThirdParty']['company_name'], [
                  'controller' => 'thirdParties', 
                  'action' => 'verCliente', 
                  $order['Order']['third_party_id']
                ]
              )
            ).'</td>';
            
            
            if ($product['ProductType']['product_category_id']!=CATEGORY_PRODUCED){
              $quantityExited=0;
              foreach ($order['StockMovement'] as $stockMovement){
                if ($stockMovement['product_quantity']>0 && !$stockMovement['bool_reclassification']){
                  $totalQuantity+=$stockMovement['product_quantity'];
                  $quantityExited+=$stockMovement['product_quantity'];
                }
              }    
              $tableRows.="<td class='centered number'>".$quantityExited."</td>";
            }
            else{
              $quantityExitedA=0;
              $quantityExitedB=0;
              $quantityExitedC=0;
              foreach ($order['StockMovement'] as $stockMovement){
                if ($stockMovement['product_quantity']>0 && !$stockMovement['bool_reclassification']){
                  switch ($stockMovement['production_result_code_id']){
                    case PRODUCTION_RESULT_CODE_A:
                      $totalQuantityA+=$stockMovement['product_quantity'];  
                      $quantityExitedA+=$stockMovement['product_quantity'];
                      break;
                    case PRODUCTION_RESULT_CODE_B:
                      $totalQuantityB+=$stockMovement['product_quantity'];  
                      $quantityExitedB+=$stockMovement['product_quantity'];
                      break;  
                    case PRODUCTION_RESULT_CODE_C:
                      $totalQuantityC+=$stockMovement['product_quantity'];  
                      $quantityExitedC+=$stockMovement['product_quantity'];
                      break;
                  }
                }                
              }
              $tableRows.="<td class='centered number'>".$quantityExitedA."</td>";
              $tableRows.="<td class='centered number'>".$quantityExitedB."</td>";
              $tableRows.="<td class='centered number'>".$quantityExitedC."</td>";
            }
            
          $tableRows.="</tr>";
        }
      }
      	$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
					$totalRow.="<td></td>";
          $totalRow.="<td></td>";
          if ($product['ProductType']['product_category_id']!=CATEGORY_PRODUCED){
            $totalRow.="<td class='centered number'>".$totalQuantity."</td>";
					}
          else{
            $totalRow.="<td class='centered number'>".$totalQuantityA."</td>";
            $totalRow.="<td class='centered number'>".$totalQuantityB."</td>";
            $totalRow.="<td class='centered number'>".$totalQuantityC."</td>";
          }					
				$totalRow.="</tr>";
			
			$table.="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
		$table.="</table>";
    if ($product['ProductType']['product_category_id']==CATEGORY_PRODUCED){
      $totalQuantity=0;
      $totalQuantity+=$totalQuantityA;
      $totalQuantity+=$totalQuantityB;
      $totalQuantity+=$totalQuantityC;
    }
    if ($totalQuantity>0){  		
			echo "<h3>".__('Ventas y Remisiones')."</h3>";
			echo $table;
		}
	}
?>
</div>

<div class="related">
<?php 
  $startDateTime= new DateTime($startDate);

  $boolCapsReclassification=false;
  $boolBottlesReclassification=false;
  $boolRawReclassification=false;

  $capReclassificationTable="";
	if (!empty($reclassificationsCaps)){
		$capReclassificationTable="<table id='tapones_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$capReclassificationTable.="<thead>";
				$capReclassificationTable.="<tr>";
					$capReclassificationTable.="<th>".__('Movement Date')."</th>";
					$capReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$capReclassificationTable.="<th>".__('Original Product')."</th>";
					$capReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$capReclassificationTable.="<th>".__('Destination Product')."</th>";
					$capReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $capReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$capReclassificationTable.="</tr>";			
			$capReclassificationTable.="</thead>";
			
			$capReclassificationTable.="<tbody>";
			foreach ($reclassificationsCaps as $reclassificationCaps){				
        if ($reclassificationCaps['origin_product_id']==$id || $reclassificationCaps['destination_product_id']==$id){
          $boolCapsReclassification=true;
          $movementDate=new DateTime($reclassificationCaps['movement_date']);
          $capReclassificationTable.="<tr>";
            $capReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
            $capReclassificationTable.="<td>".$reclassificationCaps['reclassification_code']."</td>";
            $capReclassificationTable.="<td>".$this->Html->link($reclassificationCaps['origin_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationCaps['origin_product_id']))."</td>";
            $capReclassificationTable.="<td class='centered number negative'>".$reclassificationCaps['origin_product_quantity']."</td>";
            $capReclassificationTable.="<td>".$this->Html->link($reclassificationCaps['destination_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationCaps['destination_product_id']))."</td>";
            $capReclassificationTable.="<td class='centered number'>".$reclassificationCaps['destination_product_quantity']."</td>";
            $capReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationCaps['comment']))."</td>";
          $capReclassificationTable.="</tr>";			
        }
			}
			$capReclassificationTable.="</tbody>";
		$capReclassificationTable.="</table>";
	}
  
	$bottleReclassificationTable="";
	if (!empty($reclassificationsBottles)){
		$bottleReclassificationTable="<table id='envase_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$bottleReclassificationTable.="<thead>";
				$bottleReclassificationTable.="<tr>";
					$bottleReclassificationTable.="<th>".__('Movement Date')."</th>";
					$bottleReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$bottleReclassificationTable.="<th>".__('Original Product')."</th>";
					$bottleReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$bottleReclassificationTable.="<th>".__('Destination Product')."</th>";
					$bottleReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $bottleReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$bottleReclassificationTable.="</tr>";			
			$bottleReclassificationTable.="</thead>";
			
			$bottleReclassificationTable.="<tbody>";
			//pr ($reclassificationsBottles);
			foreach ($reclassificationsBottles as $reclassificationBottles){
        if ($reclassificationBottles['origin_product_id']==$id || $reclassificationBottles['destination_product_id']==$id){
          $boolBottlesReclassification=true;
          $movementDate=new DateTime($reclassificationBottles['movement_date']);
          $bottleReclassificationTable.="<tr>";
            $bottleReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
            $bottleReclassificationTable.="<td>".$reclassificationBottles['reclassification_code']."</td>";
            $bottleReclassificationTable.="<td>".$this->Html->link($reclassificationBottles['origin_product_name']."_".$reclassificationBottles['origin_production_result_code']." (".$reclassificationBottles['origin_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationBottles['origin_product_id']))."</td>";
            $bottleReclassificationTable.="<td class='centered number negative'>".$reclassificationBottles['origin_product_quantity']."</td>";
            $bottleReclassificationTable.="<td>".$this->Html->link($reclassificationBottles['destination_product_name']."_".$reclassificationBottles['destination_production_result_code']." (".$reclassificationBottles['destination_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationBottles['destination_product_id']))."</td>";
            $bottleReclassificationTable.="<td class='centered number'>".$reclassificationBottles['destination_product_quantity']."</td>";
            $bottleReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationBottles['comment']))."</td>";
          $bottleReclassificationTable.="</tr>";			
        }
      }
			$bottleReclassificationTable.="</tbody>";
		$bottleReclassificationTable.="</table>";
	}
	
  $preformaReclassificationTable="";
	if (!empty($reclassificationsPreformas)){
		$preformaReclassificationTable="<table id='envase_preforma_".$startDateTime->format('Y')."_".$startDateTime->format('m')."'>";
			$preformaReclassificationTable.="<thead>";
				$preformaReclassificationTable.="<tr>";
					$preformaReclassificationTable.="<th>".__('Movement Date')."</th>";
					$preformaReclassificationTable.="<th>".__('Reclassification Code')."</th>";
					$preformaReclassificationTable.="<th>".__('Original Product')."</th>";
					$preformaReclassificationTable.="<th class='centered'>".__('Quantity Used')."</th>";
					$preformaReclassificationTable.="<th>".__('Destination Product')."</th>";
					$preformaReclassificationTable.="<th class='centered'>".__('Quantity Created')."</th>";
          $preformaReclassificationTable.="<th class='centered'>".__('Comment')."</th>";
				$preformaReclassificationTable.="</tr>";			
			$preformaReclassificationTable.="</thead>";
			
			$preformaReclassificationTable.="<tbody>";
			//pr ($reclassificationsBottles);
			foreach ($reclassificationsPreformas as $reclassificationPreformas){
        if ($reclassificationPreformas['origin_product_id']==$id || $reclassificationPreformas['destination_product_id']==$id){
          $boolRawReclassification=true;        
          $movementDate=new DateTime($reclassificationPreformas['movement_date']);
          $preformaReclassificationTable.="<tr>";
            $preformaReclassificationTable.="<td>".$movementDate->format('d-m-Y')."</td>";
            $preformaReclassificationTable.="<td>".$reclassificationPreformas['reclassification_code']."</td>";
            $preformaReclassificationTable.="<td>".$this->Html->link($reclassificationPreformas['origin_product_name']."_".$reclassificationPreformas['origin_production_result_code']." (".$reclassificationPreformas['origin_raw_material_name'].")", array('controller' => 'products', 'action' => 'view', $reclassificationPreformas['origin_product_id']))."</td>";
            $preformaReclassificationTable.="<td class='centered number negative'>".$reclassificationPreformas['origin_product_quantity']."</td>";
            $preformaReclassificationTable.="<td>".$this->Html->link($reclassificationPreformas['destination_product_name'], array('controller' => 'products', 'action' => 'view', $reclassificationPreformas['destination_product_id']))."</td>";
            $preformaReclassificationTable.="<td class='centered number'>".$reclassificationPreformas['destination_product_quantity']."</td>";
            $preformaReclassificationTable.="<td>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$reclassificationPreformas['comment']))."</td>";
          $preformaReclassificationTable.="</tr>";			
        }
      }
			$preformaReclassificationTable.="</tbody>";
		$preformaReclassificationTable.="</table>";
	}

  
  if ($boolCapsReclassification) {
    echo "<h3>Reclasificación de tapones</h3>";
    echo $capReclassificationTable;
  }
  if ($boolBottlesReclassification) {
    echo "<h3>Reclasificación de botellas</h3>";
    echo $bottleReclassificationTable;
  }
  if ($boolRawReclassification) {
    echo "<h3>Reclasificación de preformas</h3>";
    echo $preformaReclassificationTable;
  }
?>
</div>

<div class="related">
<?php 
/*
	if (!empty($product['StockMovement'])){
    $totalQuantity=0;
    $table="";
		$table.="<table cellpadding = '0' cellspacing = '0'>";
			$table.="<thead>";
				$table.="<tr>";
					$table.="<th>".__('Fecha Reclasificación')."</th>";
					$table.="<th>".__('Reclasificación')."</th>";
          //$table.="<th>".__('Origen')."</th>";
          //$table.="<th>".__('Destino')."</th>";
          $table.="<th class='centered'>".__('Cantidad Utilizado')."</th>";
          $table.="<th class='centered'>".__('Cantidad Reclasificado')."</th>";
          $table.="<th>".__('Comment')."</th>";
				$table.="</tr>";
			$table.="</thead>";
			$tableRows="";
      foreach ($product['StockMovement'] as $stockMovement){
        if ($stockMovement['product_quantity']>0 && $stockMovement['bool_reclassification']){
          //pr($stockMovement);
          $movementDateTime=new DateTime($stockMovement['movement_date']);
          $tableRows.="<tr>";
            $tableRows.="<td>".$movementDateTime->format('d-m-Y')."</td>";
            $tableRows.="<td>".$stockMovement['reclassification_code']."</td>";
            if ($stockMovement['bool_input']){
              //$tableRows.="<td></td>";
              //$tableRows.="<td>".$stockMovement['Origin']['Product']['name']."</td>";
              $tableRows.="<td>-".$stockMovement['product_quantity']."</td>";
              $tableRows.="<td></td>";
              $totalQuantity-=$stockMovement['product_quantity'];
            }
            else {
              //$tableRows.="<td>".$stockMovement['Origin']['Product']['name']."</td>";
              $tableRows.="<td></td>";
              $tableRows.="<td>".$stockMovement['product_quantity']."</td>";
              $totalQuantity+=$stockMovement['product_quantity'];
            }
            $tableRows.="<td>".$stockMovement['comment']."</td>";
          $tableRows.="</tr>";
        }
        //echo "total quantity is ".%totalQuantity."<br>";
      }
      	$totalRow="";
				$totalRow.="<tr class='totalrow'>";
					$totalRow.="<td>Total</td>";
					$totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td></td>";
          $totalRow.="<td class='centered number'>".$totalQuantity."</td>";
          $totalRow.="<td></td>";
				$totalRow.="</tr>";
			
			$table.="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
		$table.="</table>";
    if (!empty($tableRows)){  		
			//echo "<h3>".__('Reclasificaciones')."</h3>";
			//echo $table;
		}
	}
*/  
?>
</div>
