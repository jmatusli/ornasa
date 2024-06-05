<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
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
<div class="adjustments view fullwidth">
<?php 
  for ($tm=0;$tm<count($transferMovements);$tm++){ 
    $movement=$transferMovements[$tm];
    if ($movement['StockMovement']['bool_input']){
      $movement=$transferMovements[$tm];
      $transferDateTime=new DateTime($movement['StockMovement']['movement_date']);
      $transferDate=$transferDateTime->format('d-m-Y');
      $transferCode=(empty($movement['StockMovement']['transfer_code'])?"":$movement['StockMovement']['transfer_code']);
      $productName=$movement['Product']['name'];
      $productName.=(empty($movement['StockItem']['RawMaterial']['name'])?"":(" ".$movement['StockItem']['RawMaterial']['name']));
      $productName.=(empty($movement['ProductionResultCode']['code'])?"":(" ".$movement['ProductionResultCode']['code']));
      $productQuantity=$movement['StockMovement']['product_quantity'];
      $warehouseDestination=$originWarehouse=$movement['StockItem']['Warehouse']['name'];;
    }
    else {
      $warehouseOrigin=$movement['StockItem']['Warehouse']['name'];
    }
  }
  $fileName="Transferencia_Bodegas_".$transferCode.".pdf";

	echo "<h1>".__('Detalle Transferencia')." ".$transferCode."</h1>";
	echo "<div class='container-fluid'>";
    echo "<div class='row'>";
      echo '<div class="col-sm-6">';
        echo '<dl>';
          echo '<dt>Fecha transferencia</dt>';
          echo '<dd>'.$transferDate.'</dd>';
          echo '<dt>Código Transferencia</dt>';
          echo '<dd>'.$transferCode.'</dd>';
          echo '<dt>Producto</dt>';
          echo '<dd>'.$productName.'</dd>';
          echo '<dt>Cantidad</dt>';
          echo '<dd>'.$productQuantity.'</dd>';
          echo '<dt>Bodega origen</dt>';
          echo '<dd>'.$warehouseOrigin.'</dd>';
          echo '<dt>Bodega destino</dt>';
          echo '<dd>'.$warehouseDestination.'</dd>';
        echo '</dl>';
        echo '<br/>';
        echo '<p>'.$movement['StockMovement']['description'].'</p>';
			echo "</div>";
     
      echo '<div class="col-sm-6">';    
       echo "<h3>".__('Actions')."</h3>";
        echo '<ul style="list-style:none;">';
          echo "<li>".$this->Html->link(__('Guardar como pdf'), ['action' => 'pdfTransferencia','ext'=>'pdf', $transferCode,$fileName],['target'=>'_blank'])."</li>";
        echo "</ul>";
      echo '</div>';
    echo '</div>';
  echo '</div>';
?>
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div>
<?php
  if ($bool_delete_permission){
    echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Transferencia'), ['action' => 'eliminarTransferencia', $transferCode], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la venta # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $transferCode));
  }
?>
</div>