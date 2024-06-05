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
<div class="<?php echo $pluralVar; ?> form">
<?php 
  echo "<?php";
  echo "\n\techo \$this->Form->create('{$modelClass}'); \n"; 
	echo "\techo '<fieldset>';\n";
	echo "\t\techo '<legend>'.".printf("__('%s %s')", Inflector::humanize($action), $singularHumanName).".'</legend>';\n";

		foreach ($fields as $field) {
			if (strpos($action, 'add') !== false && $field === $primaryKey) {
				continue;
			} 
      elseif (!in_array($field, array('created', 'modified', 'updated'))) {
				echo "\t\techo \$this->Form->input('{$field}');\n";
			}
		}
		if (!empty($associations['hasAndBelongsToMany'])) {
			foreach ($associations['hasAndBelongsToMany'] as $assocName => $assocData) {
				echo "\t\techo \$this->Form->input('{$assocName}');\n";
			}
		}
  echo "\techo '</fieldset>';\n";
	echo "\techo \$this->Form->Submit(__('Submit'));\n";
  echo "\techo \$this->Form->end();\n"; 
  echo "?>\n";
?>
</div>
<div class="actions">
<?php 
  echo "<?php\n";
	echo "\techo '<h3>'.__('Actions').'</h3>';\n";
	echo "\techo '<ul>';\n";
  if (strpos($action, 'add') === false){
		echo "\t\techo '<li>'.\$this->Form->postLink(__('Delete'), ['action' => 'delete', \$this->Form->value('{$modelClass}.{$primaryKey}')], [], __('Are you sure you want to delete # %s?', \$this->Form->value('{$modelClass}.{$primaryKey}'))).'</li>';\n";
  }
	echo "\t\techo '<li>'.\$this->Html->link(__('List " . $pluralHumanName . "'), ['action' => 'resumen']).'</li>';\n";
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
