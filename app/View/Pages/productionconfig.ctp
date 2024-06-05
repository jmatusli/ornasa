<?php
	
	echo "<div id='configoptions'>";
	echo "<h1>".__('Configuration Options')."</h1>";
	
	if($userrole!=ROLE_FOREMAN){
    echo '<div class="container-fluid">';
      echo '<div class="row">';
        echo "<div class='col-md-4'>";
          echo $this->Html->image("product.jpg", ["alt" => "Product",'url' => ['controller' => 'products','action' => 'index']]); 
          echo "<h2>".__('Products')."</h2>";
          echo "<div>".$this->Html->link(__('Product Types'), ['controller' => 'productTypes', 'action' => 'index'])."</div>";
          if ($userrole == ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('Asociar Tipos de Producto con Plantas'), ['controller' => 'plantProductTypes', 'action' => 'asociarPlantasTiposDeProducto'])."</div>";
          }
          echo "<div>".$this->Html->link(__('Products'), ['controller' => 'products', 'action' => 'index'])."</div>";
          echo "<div>".$this->Html->link('Volumenes de Ventas', ['controller' => 'products', 'action' => 'volumenesVentas'])."</div>";
          echo "<div>".$this->Html->link(__('Registrar Precios'), ['controller' => 'productPriceLogs', 'action' => 'resumenPrecios'])."</div>";
          if ($userRoleId==ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('Lista Precios'), ['controller' => 'productPriceLogs', 'action' => 'listaPrecios'])."</div>";
            echo "<div>".$this->Html->link(__('Naturaleza de Producto'), ['controller' => 'productNatures', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link(__('Categorías Precios de Clientes'), ['controller' => 'priceClientCategories', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link('Asociar Clientes con Categorías de Precios',['controller' => 'priceClientCategories', 'action' => 'asociarClientesCategoriasDePrecio'])."</div>";
          
          }
          echo "<h2>".__('Proveedores y clientes')."</h2>";
          echo "<div>".$this->Html->link(__('Providers'), ['controller' => 'thirdParties', 'action' => 'resumenProveedores'])."</div>";
          if (in_array($userRoleId,[ROLE_ADMIN,ROLE_ACCOUNTING])){
            echo "<div>".$this->Html->link(__('Asociar Proveedores y Plantas'), ['controller' => 'plantThirdParties', 'action' => 'asociarPlantasProveedores'])."</div>";
            echo "<div>".$this->Html->link(__('Client Types'), ['controller' => 'clientTypes', 'action' => 'resumen'])."</div>";
          }
          echo "<div>".$this->Html->link(__('Clients'), ['controller' => 'thirdParties', 'action' => 'resumenClientes'])."</div>";
          if ($userRoleId == ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('Asociar Clientes y Plantas'), ['controller' => 'plantThirdParties', 'action' => 'asociarPlantasClientes'])."</div>";
            
          }
          if (in_array($userRoleId,[ROLE_ADMIN,ROLE_ACCOUNTING,ROLE_MANAGER])){
            echo "<div>".$this->Html->link(__('Asociar Clientes y Usuarios'), ['controller' => 'thirdPartyUsers', 'action' => 'asociarClientesUsuarios'])."</div>";
            echo "<div>".$this->Html->link(__('Reasignar Clientes'), ['controller' => 'thirdParties', 'action' => 'reasignarClientes'])."</div>";
          }  
        echo '</div>';
      }
      echo "<div class='col-md-4'>";
        echo $this->Html->image("production.jpg", ["alt" => "Production",'url' => ['controller' => 'production_runs','action' => 'index']]); 
        echo "<h2>".__('Production')."</h2>";
        echo "<div>".$this->Html->link(__('Machines'), ['controller' => 'machines', 'action' => 'resumen'])."</div>";
        echo "<div>".$this->Html->link(__('Operators'), ['controller' => 'operators', 'action' => 'index'])."</div>";
        echo "<div>".$this->Html->link(__('Shifts'), ['controller' => 'shifts', 'action' => 'index'])."</div>";
        if ($userRoleId == ROLE_ADMIN){
          echo "<div>".$this->Html->link(__('Tipos de Producción'), ['controller' => 'productionTypes', 'action' => 'resumen'])."</div>";
        }
        
        if ($userRoleId == ROLE_ADMIN){
          echo '<h2>Logística</h2>';
          echo '<div>'.$this->Html->link('Vehículos', ['controller' => 'vehicles', 'action' => 'resumen']).'</div>';
        }
        
        
        if ($userRoleId == ROLE_ADMIN){
          echo '<h2>Plantas</h2>';
          echo "<div>".$this->Html->link(__('Plants'), ['controller' => 'plants', 'action' => 'resumen'])."</div>";
          echo "<div>".$this->Html->link(__('Asociar Usuarios con Plantas'), ['controller' => 'userPlants', 'action' => 'asociarUsuariosPlantas'])."</div>";
        }
        
        echo '<h2>Bodegas</h2>';
        echo "<div>".$this->Html->link(__('Warehouses'), ['controller' => 'warehouses', 'action' => 'index'])."</div>";
        if ($userRoleId == ROLE_ADMIN){
          echo "<div>".$this->Html->link(__('Asociar Usuarios con Bodegas'), ['controller' => 'userWarehouses', 'action' => 'asociarUsuariosBodegas'])."</div>";
        }
        
        
        
      echo "</div>";
      
      if($userRoleId!=ROLE_FOREMAN){
        echo "<div class='col-md-4'>";
          echo $this->Html->image("user.jpg", ["alt" => "User",'url' => ['controller' => 'users','action' => 'index']]); 
          echo "<h2>".__('Users')."</h2>";
          if ($userRoleId == ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('User Management'), ['controller' => 'users', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link('Logs de Usurario', ['controller' => 'userLogs', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link(__('Papeles de Usuarios'), ['controller' => 'roles', 'action' => 'index'])."</div>";
            echo "<div>".$this->Html->link(__('Permisos de Usuarios (Ventas)'), ['controller' => 'users', 'action' => 'rolePermissions'])."</div>";
            echo "<div>".$this->Html->link(__('Permisos de Usuarios (Producción)'), ['controller' => 'users', 'action' => 'roleProductionPermissions'])."</div>";
            echo "<div>".$this->Html->link(__('Permisos de Usuarios (Finanzas)'), ['controller' => 'users', 'action' => 'roleFinancePermissions'])."</div>";
            echo "<div>".$this->Html->link(__('Permisos de Usuarios (Configuración)'), ['controller' => 'users', 'action' => 'roleConfigPermissions'])."</div>";
            echo "<div>".$this->Html->link('Derechos Individuales', ['controller' => 'pageRights', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link('Asignar Derechos Individuales', ['controller' => 'userPageRights', 'action' => 'resumen'])."</div>";
          }
          echo "<h2>".__('Employees')."</h2>";
          echo "<div>".$this->Html->link(__('Employees'), ['controller' => 'employees', 'action' => 'index'])."</div>";
          echo "<div>".$this->Html->link(__('Employee Holidays'), ['controller' => 'employeeHolidays', 'action' => 'index'])."</div>";
          if ($userrole==ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('Motivos de Vacaciones'), ['controller' => 'holidayTypes', 'action' => 'index'])."</div>";
          }
          if (in_array($userRoleId,[ROLE_ADMIN])){
            echo "<h2>".__('Otros')."</h2>";
            echo "<div>".$this->Html->link(__('Constants'), ['controller' => 'constants', 'action' => 'index'])."</div>";
            echo "<div>".$this->Html->link(__('Units'), ['controller' => 'units', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link(__('Zones'), ['controller' => 'zones', 'action' => 'resumen'])."</div>";
            echo "<div>".$this->Html->link(__('Payment Modes'), ['controller' => 'paymentModes', 'action' => 'index'])."</div>";
          }
          if ($userrole==ROLE_ADMIN){
            echo "<div>".$this->Html->link(__('Closing Dates'), ['controller' => 'closingDates', 'action' => 'index'])."</div>";
          }
        echo "</div>";
      }
      echo '</div>';
    echo '</div>';
	echo '</div>';
