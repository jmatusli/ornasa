<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
	
	$(document).ready(function(){
  
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});

</script>

<div class="client_requests index fullwidth">
<?php 
	echo "<h2>".__('Client Requests')."</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-6'>";				
        echo $this->Form->create('Report');
          echo "<fieldset>";
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
            if ($roleId==ROLE_ADMIN){
              echo $this->Form->input('Report.client_id',['default'=>$clientId,'empty'=>[0=>"--Seleccione cliente--"]]);
            }
          echo "</fieldset>";
          echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
          echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
        echo $this->Form->end(__('Refresh'));
        echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumen'), array( 'class' => 'btn btn-primary'));
        echo "</div>";
			echo "<div class='col-md-4'>";			
      echo "</div>";
      echo "<div class='col-md-2'>";			
        echo "<h3>".__('Actions')."</h3>";
        echo "<ul>";
          echo "<li>".$this->Html->link(__('New Client Request'), array('action' => 'add'))."</li>";
          if ($roleId == ROLE_ADMIN){
            echo "<br/>";
            echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
            echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
          }
        echo "</ul>"; 
      echo "</div>";
		echo "</div>";
	echo "</div>";
  
  if(!empty($clientRequests)){
    $pageHeader="<thead>";
      $pageHeader.="<tr>";
        $pageHeader.="<th>".$this->Paginator->sort('client_request_date')."</th>";
        $pageHeader.="<th>".$this->Paginator->sort('client_request_code')."</th>";
        $pageHeader.="<th>".$this->Paginator->sort('client_id')."</th>";
        if ($roleId == ROLE_ADMIN){
          $pageHeader.="<th>".$this->Paginator->sort('subtotal_price','Subtotal')."</th>";
        }
        $pageHeader.="<th>".$this->Paginator->sort('comment')."</th>";
        $pageHeader.="<th class='actions'>".__('Actions')."</th>";
      $pageHeader.="</tr>";
    $pageHeader.="</thead>";
    $excelHeader="<thead>";
      $excelHeader.="<tr>";
        $excelHeader.="<th>Fecha Pedido</th>";
        $excelHeader.="<th>Número</th>";
        $excelHeader.="<th>Cliente</th>";
        if ($roleId == ROLE_ADMIN){
          $excelHeader.="<th>Subtotal</th>";
        }
        $excelHeader.="<th>Comentario</th>";
      $excelHeader.="</tr>";
    $excelHeader.="</thead>";

    $pageBody="";
    $excelBody="";
    $totalPrice=0;
    foreach ($clientRequests as $clientRequest){ 
      $requestDateTime= new DateTime($clientRequest['ClientRequest']['client_request_date']);
      $totalPrice+=$clientRequest['ClientRequest']['subtotal_price'];
      $pageRow="";
      $pageRow.="<td>".$requestDateTime->format('d-m-Y')."</td>";
      $pageRow.="<td>".$this->Html->link($clientRequest['ClientRequest']['client_request_code'].($clientRequest['ClientRequest']['bool_annulled']?" (Anulado)":""), ['action' => 'view', $clientRequest['ClientRequest']['id']])."</td>";
      switch ($roleId){
        case ROLE_ADMIN:
          $pageRow.="<td>".$this->Html->link($clientRequest['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'view', $clientRequest['Client']['id']))."</td>";
          break;
        default:
          $pageRow.="<td>".$clientRequest['Client']['company_name']."</td>";
          break;
      }
      if ($roleId == ROLE_ADMIN){
        $pageRow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".h($clientRequest['ClientRequest']['subtotal_price'])."</span></td>";
      }
      $pageRow.="<td>".h($clientRequest['ClientRequest']['comment'])."</td>";
      
    $excelBody.="<tr>".$pageRow."</tr>";
        $pageRow.="<td class='actions'>";
          if ($bool_edit_permission){
            $pageRow.=$this->Html->link(__('Edit'), array('action' => 'edit', $clientRequest['ClientRequest']['id']));  
          }
          //$pageRow.=->postLink(__('Delete'), array('action' => 'delete', $clientRequest['ClientRequest']['id']), array(), __('Are you sure you want to delete # %s?', $clientRequest['ClientRequest']['id']));
        $pageRow.="</td>";

      $pageBody.="<tr ".($clientRequest['ClientRequest']['bool_annulled']?" class='italic'":"").">".$pageRow."</tr>";
    }

    $pageTotalRow="";
    $pageTotalRow.="<tr class='totalrow'>";
      $pageTotalRow.="<td>TOTAL</td>";
      $pageTotalRow.="<td></td>";
      $pageTotalRow.="<td></td>";
      if ($roleId==ROLE_ADMIN){
        $pageTotalRow.="<td class='centered CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalPrice."</span></td>";
      }
      $pageTotalRow.="<td></td>";
      $pageTotalRow.="<td></td>";
    $pageTotalRow.="</tr>";

    $pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
    $table_id="pedidos";
    $pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
    echo $pageOutput;
    $excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
    $_SESSION['resumenPedidos'] = $excelOutput;
  }
  else {
    echo "<h3>No se realizaron pedidos para este período</h3>";
  }
?>
</div>
