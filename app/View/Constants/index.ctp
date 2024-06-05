<div class="constants index">
<?php 
	echo "<h2>".__('Constants')."</h2>";
	
  echo "<h3>Constantes son valores que se ocupan en el código para visualizar ciertos datos o realizar ciertos campos.  Declarar un nuevo constante no tiene un impacto inmediato, porque tiene que estar tomado en cuenta en el código.  Sin embargo, una vez incorporado en el código, el valor cambiará con el valor configurado.</h3>";
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Constant'), array('action' => 'add'))."</li>";
		echo "<br/>";
	echo "</ul>";
?>
</div>
<div>
<?php
  

	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('constant')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('description')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('value')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	$excelHeader="<thead>";
		$excelHeader.="<tr>";
			$excelHeader.="<th>".$this->Paginator->sort('constant')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('description')."</th>";
			$excelHeader.="<th>".$this->Paginator->sort('value')."</th>";
		$excelHeader.="</tr>";
	$excelHeader.="</thead>";

	$pageBody="";
	$excelBody="";

	foreach ($constants as $constant){ 
		$pageRow="";
    $pageRow.="<td>".$this->Html->link($constant['Constant']['constant'],['action' => 'view', $constant['Constant']['id']])."</td>";
		$pageRow.="<td>".h($constant['Constant']['description'])."</td>";
		$pageRow.="<td>".h($constant['Constant']['value'])."</td>";

    $excelBody.="<tr>".$pageRow."</tr>";

		$pageRow.="<td class='actions'>";
				if ($bool_edit_permission){
          $pageRow.=$this->Html->link(__('Edit'), ['action' => 'edit', $constant['Constant']['id']]);
        }
				//$pageRow.=->postLink(__('Delete'), array('action' => 'delete', $constant['Constant']['id']), array(), __('Are you sure you want to delete # %s?', $constant['Constant']['id']));
			$pageRow.="</td>";

		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageTotalRow="";
	$pageTotalRow.="<tr class=\'totalrow\'>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
	$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
	$excelOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$excelHeader.$excelBody."</table>";
	$_SESSION['resumen'] = $excelOutput;
?>
</div>
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