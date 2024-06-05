<div class="purchase_estimations view fullwidth">
<?php 
	echo "<h2>".__('Purchase Estimation')." ".$purchaseEstimation['PurchaseEstimation']['purchase_estimation_code'].($purchaseEstimation['PurchaseEstimation']['bool_annulled']?
          " (Anulado)":"")."</h2>";
  $purchaseEstimationDateTime=new DateTime($purchaseEstimation['PurchaseEstimation']['purchase_estimation_date']);
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";	
      echo "<div class='col-md-8'>";
        echo "<dl>";
          echo "<dt>".__('Purchase Estimation Date')."</dt>";
          echo "<dd>".$purchaseEstimationDateTime->format('d-m-Y')."</dd>";
          echo "<dt>".__('Purchase Estimation Code')."</dt>";
          echo "<dd>".h($purchaseEstimation['PurchaseEstimation']['purchase_estimation_code'])."</dd>";
          echo "<dt>".__('Bool Annulled')."</dt>";
          echo "<dd>".($purchaseEstimation['PurchaseEstimation']['bool_annulled']?
          "Anulado":"Activo")."</dd>";
          echo "<dt>".__('Client')."</dt>";
          echo "<dd>".$this->Html->link($purchaseEstimation['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $purchaseEstimation['Client']['id']))."</dd>";
          echo "<dt>".__('Subtotal Price')."</dt>";
          echo "<dd>C$ ".h($purchaseEstimation['PurchaseEstimation']['subtotal_price'])."</dd>";
          echo "<dt>".__('Comment')."</dt>";
          echo "<dd>".str_replace(["\r\n","\r","\n"],"<br/>",$purchaseEstimation['PurchaseEstimation']['comment'])."</dd>";
        echo "</dl>";
      echo "</div>";
      echo "<div class='col-md-4'>";
          echo "<h3>".__('Actions')."</h3>";
          echo "<ul style='list-style:none;'>";
            echo "<li>".$this->Html->link(__('Edit Purchase Estimation'), array('action' => 'edit', $purchaseEstimation['PurchaseEstimation']['id']))."</li>";
            echo "<li>".$this->Form->postLink(__('Eliminar Estimación de Compras'), array('action' => 'delete', $purchaseEstimation['PurchaseEstimation']['id']), array(), __('Está seguro que quiere eliminar la estimación de compras # %s?', $purchaseEstimation['PurchaseEstimation']['purchase_estimation_code']))."</li>";
            echo "<li>".$this->Html->link(__('List Purchase Estimations'), array('action' => 'index'))."</li>";
            echo "<li>".$this->Html->link(__('New Purchase Estimation'), array('action' => 'add'))."</li>";
            if ($roleId == ROLE_ADMIN){
                    echo "<br/>";
                    echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
                    echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
                  }
          echo "</ul>";
      echo "</div>";
    echo "</div>";
    echo "<div class='col-md-12'>";
      if (!empty($purchaseEstimation['PurchaseEstimationProduct'])){
        $tableHead="";
        $tableHead.="<thead>";
          $tableHead.="<tr>";
            $tableHead.="<th>Producto</th>";
            $tableHead.="<th>Cantidad</th>";
            $tableHead.="<th class='centered'>Precio Unitario</th>";
            $tableHead.="<th class='centered'>Precio Total</th>";
            $tableHead.="<th class='centered'>Descripción</th>";
          $tableHead.="</tr>";
        $tableHead.="</thead>";
        $tableRows="";
        $countProducts=0;
        $totalPrice=0;
        foreach ($purchaseEstimation['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
          $tableRows.="<tr>";
            $countProducts+=$purchaseEstimationProduct['product_quantity'];
            $totalPrice+=$purchaseEstimationProduct['product_total_price'];
            
            $tableRows.="<td>".$purchaseEstimationProduct['Product']['name'].(empty($purchaseEstimationProduct['RawMaterial'])?"":" ".$purchaseEstimationProduct['RawMaterial']['name']).(empty($purchaseEstimationProduct['ProductionResultCode'])?"":" ".$purchaseEstimationProduct['ProductionResultCode']['code'])."</td>";
            $tableRows.="<td class='centered'>".$purchaseEstimationProduct['product_quantity']."</td>";
            $tableRows.="<td class='centered'><span class='currency'>C$ </span><span>".number_format($purchaseEstimationProduct['product_unit_price'],2,".",",")."</span></td>";
            $tableRows.="<td class='centered'><span class='currency'>C$ </span><span>".number_format($purchaseEstimationProduct['product_total_price'],2,".",",")."</span></td>";
            $tableRows.="<td>".str_replace(["\r\n","\r","\n"],"<br/>",$purchaseEstimationProduct['description'])."</td>";
          $tableRows.="</tr>";
        }
        $totalRow="";
        $totalRow.="<tr class='totalrow'>";
          $totalRow.="<td>Total</td>";
          $totalRow.="<td class='centered'>".$countProducts."</td>";
          $totalRow.="<td></td>";
          $totalRow.="<td class='centered'><span class='currency'>C$ </span><span>".number_format($totalPrice,2,".",",")."</span></td>";
          $totalRow.="<td></td>";
        $totalRow.="</tr>";
        $tableBody="<tbody>".$totalRow.$tableRows.$totalRow."</tbody>";
        echo "<table>".$tableHead.$tableBody."</table>";
      }
    echo "</div>";
  echo "</div>";	

?> 
</div>
<div class="actions">
<?php 
	
?> 
</div>
