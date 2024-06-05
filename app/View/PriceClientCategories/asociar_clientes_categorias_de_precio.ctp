<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script-->
<?php
  echo $this->Html->css('toggle_switch.css');
?>
<script>
  $('body').on('change','#ProductPriceLogClientId',function(e){	 
    $('#changeDate').trigger('click');
  });

  function roundToTwo(num) {    
		return +(Math.round(num + "e+2")  + "e-2");
	}
	$('#content').keypress(function(e) {
		if(e.which == 13) { // Checks for the enter key
			e.preventDefault(); // Stops IE from triggering the button to be clicked
		}
	});
	
	$('div.decimal input').click(function(){
		if ($(this).val()=="0"){
			$(this).val("");
		}
	});
  
	$(document).ready(function(){
    $('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>

<div class="productpricelogs form fullwidth">
<?php 
  echo $this->Form->create('ThirdParty');
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Asociar Clientes con Categorías de Precios')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='row'>";
				echo "<div class='col-sm-12'>";	
        /*
          echo $this->Form->input('price_datetime',['label'=>__('Date'),'default'=>$priceDateTime,'dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
          //echo $this->Form->input('currency_id',['label'=>'Precios','default'=>CURRENCY_CS,'class'=>'fixed']);
          echo $this->Form->input('product_category_id',['label'=>__('Categoría de Producto'),'value'=>$productCategoryId,'empty'=>[0=>'--TODAS CATEGORÍAS DE PRODUCTO--']]);
          echo  $this->Form->input('existence_option_id',['label'=>__('Existencia'),'default'=>$existenceOptionId]);
          echo $this->Form->input('user_id',['label'=>false,'default'=>$loggedUserId,'type'=>'hidden']);
          
          echo $this->Form->Submit(__('Cambiar Selección'),['id'=>'changeDate','name'=>'changeDate','style'=>'width:300px;']);
        */  
          $fileName='Asociaciones_Clientes_Categorías_Precios.xlsx';
          echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarAsociacionesClientesCategoriasDePrecio',$fileName], ['class' => 'btn btn-primary']); 
        echo "</div>";
      echo "</div>"; 
      echo "<div class='row'>";
        echo $this->Form->Submit(__('Grabar Categorías de Precio para Clientes'),['id'=>'saveAssociations','name'=>'saveAssociations','style'=>'width:300px;']);
        $excelOutput='';
        echo "<div class='col-sm-12' style='padding:5px;'>";
          $clientCategoryTableHead="<thead>";
            $clientCategoryTableHead.="<tr>";
              $clientCategoryTableHead.='<th style="width:100px;">Cliente</th>';
              $clientCategoryTableHead.='<th class="centered">Categoría Precio</th>';
            $clientCategoryTableHead.="</tr>";
          $clientCategoryTableHead.="</thead>";
              
          $clientCategoryTableBodyRows="";
          foreach ($clientCategories as $clientId=>$categoryId){
            $clientCategoryTableBodyRows.='<tr>';
              $clientCategoryTableBodyRows.='<td>'.$clients[$clientId].'</td>';
              $clientCategoryTableBodyRows.='<td class="centered">'.$this->Form->Input('Client.'.$clientId.'.PriceClientCategory',[
                //'label'=>false,
                'legend'=>false,
                'type'=>'radio',
                'options'=>$priceClientCategories,
                'value'=>$clientCategories[$clientId],
                'div'=>['class'=>'clientCategoryRadio'],  
              ]).'</td>';
            $clientCategoryTableBodyRows.='</tr>';
          }
          $clientCategoryTableBody="<tbody>".$clientCategoryTableBodyRows."</tbody>";  
          
          $excelTableBodyRows="";
          foreach ($clientCategories as $clientId=>$categoryId){
            $excelTableBodyRows.='<tr>';
              $excelTableBodyRows.='<td>'.$clients[$clientId].'</td>';
              $excelTableBodyRows.='<td class="centered">'.$priceClientCategories[$clientCategories[$clientId]].'</td>';
            $excelTableBodyRows.='</tr>';
          }
          $excelTableBody="<tbody class='nomarginbottom' style='font-size:0.9em'>".$excelTableBodyRows."</tbody>";                
            
        $tableId='clientes_categorías_precio';  
        $clientCategoryTable='<table id="'.$tableId.'">'.$clientCategoryTableHead.$clientCategoryTableBody.'</table>';
        echo $clientCategoryTable;
        echo "</div>";  
         
        $excelTable='<table id="'.$tableId.'">'.$clientCategoryTableHead.$excelTableBody.'</table>';
        $excelOutput.= $excelTable;
        
        $_SESSION['asociacionesClientesCategoriasDePrecio'] = $excelOutput;
        if ($userRoleId == ROLE_ADMIN){
          echo $this->Form->Submit(__('Grabar Categorías de Precio para Clientes'),['id'=>'saveAssociations','name'=>'saveAssociations','style'=>'width:300px;','div'=>['class'=>'submit','style'=>'clear:left']]);
        }
      echo "</div>";        
      echo $this->Form->end();
    echo "</div>";
      
    
  echo "</div>";
echo "</fieldset>";
?>
</div>