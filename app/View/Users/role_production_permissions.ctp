<div class="users rolepermissions fullwidth" style="overflow-x:auto">
<?php 

	echo $this->Form->create('Permissions');
	echo "<fieldset>";
		echo "<legend>".__('Permisos')."</legend>";
		echo "<table cellpadding='0' cellspacing='0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>Controller</th>";
					echo "<th>Action</th>";
					foreach ($roles as $role){
						echo "<th>".$this->Html->link($role['Role']['name'],array('controller'=>'roles','action'=>'view',$role['Role']['name']))."</th>";
					}
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			for ($c=0;$c<count($selectedControllers);$c++){
				//pr($selectedControllers[$c]);
				echo "<tr class='totalrow'>";
					switch ($selectedControllers[$c]['Aco']['alias']){
						case "ProductionMovements":
							echo "<td>"."Producción"."</td>";
							break;
						default:
							echo "<td>".__(fromCamelCase($selectedControllers[$c]['Aco']['alias']))."</td>";
					}
					echo "<td></td>";
					foreach ($roles as $role){
						echo "<td>".$role['Role']['name']."</td>";
					}
				echo "</tr>";
        //$controllerName=$selectedControllers[$c]['Aco']['alias'];
				for ($a=0;$a<count($selectedControllers[$c]['actions']);$a++){
					//pr($selectedControllers[$c]['actions'][$a]);
          
          echo "<tr>";
            //echo "<td>".$selectedControllers[$c]['Aco']['alias']."</td>";
            echo "<td></td>";
            echo "<td>";
            switch ($selectedControllers[$c]['actions'][$a]['Aco']['alias']){
              case "index":
                echo "Resumen";
                break;
              case "view":
                echo "Vista";
                break;
              case "add":
                echo "Crear";
                break;
              case "edit":
                echo "Editar";
                break;
              case "annul":
                echo "Anular";
                break;
              case "delete":
                echo "Eliminar";
                break;
              case "viewPdf":
                echo "Pdf";
                break;
              
              case "deleteClient":
                echo "Eliminar Cliente";
                break;
              case "deleteProvider":
                echo "Eliminar Proveedor";
                break;
              case "viewSaleReport":
                echo "Reporte Salidas";
                break;  
              default:
                echo fromCamelCase($selectedControllers[$c]['actions'][$a]['Aco']['alias']);
                break;
            }
            echo "</td>";
            
            for ($r=0;$r<count($selectedControllers[$c]['actions'][$a]['rolePermissions']);$r++){
              //echo "<td></td>";
              //echo "<td>".($selectedControllers[$c]['actions'][$a]['rolePermissions'][$r]?__('Yes'):__('No'))."</td>";
              echo "<td>".$this->Form->input('Role.'.$r.'.Controller.'.$c.'.Action.'.$a,['type'=>'checkbox','label'=>false,'checked'=>$selectedControllers[$c]['actions'][$a]['rolePermissions'][$r]])."</td>";
            }
          echo "</tr>";
          
				}
			}
			
			echo "</tbody>";
		echo "</table>";
	echo "</fieldset>";
	echo $this->Form->end(__('Submit'));

	/**
	 * Converts camelCase string to have spaces between each.
	 * @param $camelCaseString
	 * @return string
	 */
	function fromCamelCase($camelCaseString) {
			$re = '/(?<=[a-z])(?=[A-Z])/x';
			$a = preg_split($re, $camelCaseString);
			return join($a, " " );
	}
?>
</div>
