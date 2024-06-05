<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.views
 * @since         CakePHP(tm) v 1.2.0.5234
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
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
<div class="<?php echo $pluralVar; ?> index">
<?php 
echo "<?php \n"; 
	echo "\techo '<h2>'.__('{$pluralHumanName}').'</h2>';\n"; 
	
	echo "\techo \$this->Form->create('Report');\n";
	echo "\t\techo '<fieldset>';\n";
	echo "\t\t\techo \$this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>\$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);\n";
	echo "\t\t\techo \$this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>\$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);\n";
	echo "\t\techo '</fieldset>';\n";
	echo "\t\techo '<button id=\"previousmonth\" class=\"monthswitcher\">'.__('Previous Month').'</button>';\n";
	echo "\t\techo '<button id=\"nextmonth\" class=\"monthswitcher\">'.__('Next Month').'</button>';\n";
	echo "\techo \$this->Form->end(__('Refresh'));\n";
	echo "\techo \$this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumen'], ['class' => 'btn btn-primary']);\n"; 
echo "?> \n"; 	
?>
</div>
<div class="actions">
<?php
echo "<?php \n"; 
	echo "\techo '<h3>'.__('Actions').'</h3>';\n";
	echo "\techo '<ul>';\n";
    echo "\t\tif (\$bool_add_permission){\n";
		echo "\t\t\techo '<li>'.\$this->Html->link(__('New " . $singularHumanName . "'), ['action' => 'crear']).'</li>';\n";
    echo "\t\t}\n";
		echo "\t\techo '<br/>';\n";
		$done = [];
		foreach ($associations as $type => $data) {
			foreach ($data as $alias => $details) {
				if ($details['controller'] != $this->name && !in_array($details['controller'], $done)) {
					echo "\t\techo '<li>'.\$this->Html->link(__('List " . Inflector::humanize($details['controller']) . "'), ['controller' => '{$details['controller']}', 'action' => 'resumen']).'</li>';\n";
					echo "\t\techo '<li>'.\$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), ['controller' => '{$details['controller']}', 'action' => 'crear']).'</li>';\n";
					$done[] = $details['controller'];
				}
			}
		}
	echo "\techo '</ul>';\n";
echo "?>\n"; 
?>
</div>
<div>
<?php
	echo "<?php\n";
		echo "\t\$tableHeader='<thead>';\n"; 			
			echo "\t\t\$tableHeader.='<tr>';\n"; 
				foreach ($fields as $field){
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\t\$tableHeader.='<th>'.\$this->Paginator->sort('{$field}').'</th>';\n"; 
					}
				}
				echo "\t\t\t\$tableHeader.='<th class=\"actions\">'.__('Actions').'</th>';\n"; 
			echo "\t\t\$tableHeader.='</tr>';\n"; 
		echo "\t\$tableHeader.='</thead>';\n"; 
		echo "\t\$excelHeader='<thead>';\n"; 			
			echo "\t\t\$excelHeader.='<tr>';\n"; 
				foreach ($fields as $field){
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\t\$excelHeader.='<th>'.\$this->Paginator->sort('{$field}').'</th>';\n"; 
					}
				}
			echo "\t\t\$excelHeader.='</tr>';\n"; 
		echo "\t\$excelHeader.='</thead>';\n\n"; 
		
		echo "\t\$tableBody='';\n"; 			
		echo "\t\$excelBody='';\n\n"; 			
			
		echo "\tforeach (\${$pluralVar} as \${$singularVar}){ \n";
      echo "\t\t\$tableRow='';";
			foreach ($fields as $field) {
				$isKey = false;
				if (!empty($associations['belongsTo'])) {
					foreach ($associations['belongsTo'] as $alias => $details) {
						if ($field === $details['foreignKey']) {
							if (!($field=='id'||$field=='created'||$field=='modified')){
								$isKey = true;
								echo "\t\t\$tableRow.='<td>'.\$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'detalle', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]).'</td>';\n";
								break;
							}
						}
					}
				}
				if ($isKey !== true) {
					if (!($field=='id'||$field=='created'||$field=='modified')){
						echo "\t\t\$tableRow.='<td>'.h(\${$singularVar}['{$modelClass}']['{$field}']).'</td>';\n";
					}
				}
			}
			echo "\n\t\t\t\$excelBody.='<tr>'.\$tableRow.'</tr>';\n\n";
			
			echo "\t\t\t\$tableRow.='<td class=\"actions\">';\n";
			echo "\t\t\t\t\$tableRow.=\$this->Html->link(__('View'), ['action' => 'detalle', \${$singularVar}['{$modelClass}']['{$primaryKey}']]);\n";
      echo "\t\tif (\$bool_edit_permission){\n";
      echo "\t\t\t\t\$tableRow.=\$this->Html->link(__('Edit'), ['action' => 'editar', \${$singularVar}['{$modelClass}']['{$primaryKey}']]);\n";
      echo "\t\t}\n";
			echo "\t\t\t\$tableRow.='</td>';\n";
		echo "\n\t\t\$tableBody.='<tr>'.\$tableRow.'</tr>';\n";	
		echo "\t}\n\n";
		echo "\t\$totalRow='';\n";
		echo "\t\$totalRow.='<tr class=\"totalrow\">';\n";
		foreach ($fields as $field) {
			echo "\t\t\$totalRow.='<td></td>';\n";
		}	
		echo "\t\$totalRow.='</tr>';\n\n";
		
		echo "\t\$tableBody='<tbody>'.\$totalRow.\$tableBody.\$totalRow.'</tbody>';\n";
		echo "\t\$tableId='';\n";
		echo "\t\$pageOutput='<table cellpadding=\"0\" cellspacing=\"0\" id=\"'.\$tableId.'\">'.\$tableHeader.\$tableBody.'</table>';\n";
		echo "\techo \$pageOutput;\n";
		echo "\t\$excelOutput='<table cellpadding=\"0\" cellspacing=\"0\" id=\"'.\$tableId.'\">'.\$excelHeader.\$excelBody.'</table>';\n";
		echo "\t\$_SESSION['resumen'] = \$excelOutput;\n";
		
		//echo "\techo \"<p>\";\n";
		//echo "\t\techo \$this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));"; 
		//echo "\techo \"</p>\";\n";
		//echo "\techo \"<div class='paging'>\";\n";
			//echo "\t\techo \$this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));\n";
			//echo "\t\techo \$this->Paginator->numbers(array('separator' => ''));\n";
			//echo "\t\techo \$this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));\n";
		//echo "\techo \"</div>\";\n";
	echo "?>\n"; 
?>
</div>