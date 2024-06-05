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
<div class="priceClientCategories index">
<?php 
	echo "<h2>".__('Price Client Categories')."</h2>";
	echo $this->Form->end(__('Refresh'));
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('New Price Client Category'), ['action' => 'crear'])."</li>";
		echo "<br/>";
	echo "</ul>";
?>
</div>
<div>
<?php
	$pageHeader="<thead>";
		$pageHeader.="<tr>";
			$pageHeader.="<th>".$this->Paginator->sort('category_number')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('name')."</th>";
			$pageHeader.="<th>".$this->Paginator->sort('description')."</th>";
			$pageHeader.="<th class='actions'>".__('Actions')."</th>";
		$pageHeader.="</tr>";
	$pageHeader.="</thead>";
	
	$pageBody="";
	
	foreach ($priceClientCategories as $priceClientCategory){ 
		$pageRow="";
    $pageRow.="<td>".h($priceClientCategory['PriceClientCategory']['category_number'])."</td>";
		$pageRow.="<td>".$this->html->link($priceClientCategory['PriceClientCategory']['name'],['action'=>'detalle',$priceClientCategory['PriceClientCategory']['id']])."</td>";
		$pageRow.="<td>".h($priceClientCategory['PriceClientCategory']['description'])."</td>";
		$pageRow.="<td class='actions'>";
      $pageRow.=$this->Html->link(__('Edit'), array('action' => 'editar', $priceClientCategory['PriceClientCategory']['id']));
    $pageRow.="</td>";
		$pageBody.="<tr>".$pageRow."</tr>";
	}

	$pageTotalRow="";
	$pageTotalRow.="<tr class='totalrow'>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
		$pageTotalRow.="<td></td>";
	$pageTotalRow.="</tr>";

	$pageBody="<tbody>".$pageTotalRow.$pageBody.$pageTotalRow."</tbody>";
	$table_id="";
	$pageOutput="<table cellpadding='0' cellspacing='0' id='".$table_id."'>".$pageHeader.$pageBody."</table>";
	echo $pageOutput;
?>
</div>
