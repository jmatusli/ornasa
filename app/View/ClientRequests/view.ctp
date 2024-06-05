<div class="client_requests view fullwidth">
<?php 
	echo "<h2>".__('Client Request')." ".$clientRequest['ClientRequest']['client_request_code'].($clientRequest['ClientRequest']['bool_annulled']?
          " (Anulado)":"")."</h2>";
  $clientRequestDateTime=new DateTime($clientRequest['ClientRequest']['client_request_date']);
  echo "<div class='container-fluid'>";
    echo "<div class='row'>";	
      echo "<div class='col-md-8'>";
        echo "<dl>";
          echo "<dt>".__('Client Request Date')."</dt>";
          echo "<dd>".$clientRequestDateTime->format('d-m-Y')."</dd>";
          echo "<dt>".__('Client Request Code')."</dt>";
          echo "<dd>".h($clientRequest['ClientRequest']['client_request_code'])."</dd>";
          echo "<dt>".__('Bool Annulled')."</dt>";
          echo "<dd>".($clientRequest['ClientRequest']['bool_annulled']?
          "Anulado":"Activo")."</dd>";
          echo "<dt>".__('Client')."</dt>";
          echo "<dd>".$this->Html->link($clientRequest['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $clientRequest['Client']['id']))."</dd>";
          echo "<dt>".__('Subtotal Price')."</dt>";
          echo "<dd>C$ ".h($clientRequest['ClientRequest']['subtotal_price'])."</dd>";
          echo "<dt>".__('Comment')."</dt>";
          echo "<dd>".str_replace(["\r\n","\r","\n"],"<br/>",$clientRequest['ClientRequest']['comment'])."</dd>";
        echo "</dl>";
      echo "</div>";
      echo "<div class='col-md-4'>";
          echo "<h3>".__('Actions')."</h3>";
          echo "<ul style='list-style:none;'>";
            echo "<li>".$this->Html->link(__('Edit Client Request'), array('action' => 'edit', $clientRequest['ClientRequest']['id']))."</li>";
            echo "<li>".$this->Form->postLink(__('Eliminar Pedido'), array('action' => 'delete', $clientRequest['ClientRequest']['id']), array(), __('Está seguro que quiere eliminar pedido # %s?', $clientRequest['ClientRequest']['client_request_code']))."</li>";
            echo "<li>".$this->Html->link(__('List Client Requests'), array('action' => 'index'))."</li>";
            echo "<li>".$this->Html->link(__('New Client Request'), array('action' => 'add'))."</li>";
            if ($roleId == ROLE_ADMIN){
                    echo "<br/>";
                    echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
                    echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
                  }
          echo "</ul>";
      echo "</div>";
    echo "</div>";
    echo "<div class='col-md-12'>";
      if (!empty($clientRequest['ClientRequestProduct'])){
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
        foreach ($clientRequest['ClientRequestProduct'] as $clientRequestProduct){
          $tableRows.="<tr>";
            $countProducts+=$clientRequestProduct['product_quantity'];
            $totalPrice+=$clientRequestProduct['product_total_price'];
            
            $tableRows.="<td>".$clientRequestProduct['Product']['name'].(empty($clientRequestProduct['RawMaterial'])?"":" ".$clientRequestProduct['RawMaterial']['name']).(empty($clientRequestProduct['ProductionResultCode'])?"":" ".$clientRequestProduct['ProductionResultCode']['code'])."</td>";
            $tableRows.="<td class='centered'>".$clientRequestProduct['product_quantity']."</td>";
            $tableRows.="<td class='centered'><span class='currency'>C$ </span><span>".number_format($clientRequestProduct['product_unit_price'],2,".",",")."</span></td>";
            $tableRows.="<td class='centered'><span class='currency'>C$ </span><span>".number_format($clientRequestProduct['product_total_price'],2,".",",")."</span></td>";
            $tableRows.="<td>".str_replace(["\r\n","\r","\n"],"<br/>",$clientRequestProduct['description'])."</td>";
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
