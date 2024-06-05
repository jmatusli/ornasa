<?php
	define('COMPANY_NAME','Orna S.A.');

	define('CURRENCY_CS','1');
	define('CURRENCY_USD','2');
	
	define('ROLE_ADMIN','4');
	define('ROLE_ASSISTANT','5');
	define('ROLE_FOREMAN','6');
	define('ROLE_MANAGER','7');
	define('ROLE_SALES','8');
	
	define('SHIFT_MORNING','2');
	define('SHIFT_AFTERNOON','3');
	define('SHIFT_NIGHT','4');
	define('SHIFT_EXTRA','5');
	
	define('NA','N/A');
		
	define('MOVEMENT_PURCHASE','4');
	define('MOVEMENT_SALE','5');
	
	define('PRODUCT_TYPE_PREFORMA','10');
	define('PRODUCT_TYPE_CAP','9');
	define('PRODUCT_TYPE_BOTTLE','11');
	
	define('CATEGORY_RAW','1');
	define('CATEGORY_PRODUCED','2');
	define('CATEGORY_OTHER','3');
	
	define('PRODUCTION_RESULT_CODE_A','1');
	define('PRODUCTION_RESULT_CODE_B','2');
	define('PRODUCTION_RESULT_CODE_C','3');
	
	define('HOLIDAY_TYPE_SOLICITADO','1');
	define('HOLIDAY_TYPE_PROGRAMADO','2');
	define('HOLIDAY_TYPE_AUSENCIA_LABORAL','3');
	define('HOLIDAY_TYPE_FERIADO','4');
	
	define('CASH_RECEIPT_TYPE_CREDIT','1');
	define('CASH_RECEIPT_TYPE_REMISSION','2');
	define('CASH_RECEIPT_TYPE_OTHER','3');
	
	define('ACCOUNTING_CODE_CASHBOXES','4'); // accounting code 101-001
	define('ACCOUNTING_CODE_CASHBOX_MAIN','5'); // accounting code 101-001-001
	define('ACCOUNTING_CODE_BANKS','11'); // accounting code 101-003
	define('ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES','17'); // accounting code 101-004-001
	define('ACCOUNTING_CODE_INVENTORY','29'); // accounting code 101-005
	define('ACCOUNTING_CODE_INVENTORY_RAW_MATERIAL','91'); // accounting code 101-005-001
	define('ACCOUNTING_CODE_INVENTORY_FINISHED_PRODUCT','92'); // accounting code 101-005-002
	define('ACCOUNTING_CODE_INVENTORY_OTHER_MATERIAL','93'); // accounting code 101-005-003
	define('ACCOUNTING_CODE_PROVIDERS','34'); // accounting code 201-001
	define('ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR','50'); // accounting code 401
	define('ACCOUNTING_CODE_INGRESOS_VENTA','89'); // accounting code 401-001
	define('ACCOUNTING_CODE_INGRESOS_DESCUENTOS','55'); // accounting code 402
	define('ACCOUNTING_CODE_INGRESOS_OTROS','58'); // accounting code 403	
	define('ACCOUNTING_CODE_COSTS','60'); // accounting code 500
	define('ACCOUNTING_CODE_COSTOS_VENTA','61'); // accounting code 501
	define('ACCOUNTING_CODE_SPENDING_OPERATIONS','64'); // accounting code 600
	define('ACCOUNTING_CODE_GASTOS_ADMIN','65'); // accounting code 601
	define('ACCOUNTING_CODE_GASTOS_VENTA','73'); // accounting code 602
	define('ACCOUNTING_CODE_GASTOS_FINANCIEROS','74'); // accounting code 603
	define('ACCOUNTING_CODE_GASTOS_PRODUCCION','79'); // accounting code 604
	define('ACCOUNTING_CODE_GASTOS_OTROS','75'); // accounting code 605
	
	define('ACCOUNTING_CODE_RETENCIONES_POR_COBRAR','85'); // accounting code 101-004-004
	define('ACCOUNTING_CODE_IVA_POR_PAGAR','84'); // accounting code 201-002-3
	define('ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS','59'); // accounting code 403-001
	define('ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA','88'); // accounting code 403-002
	define('ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA','86'); // accounting code 602-002
	define('ACCOUNTING_CODE_GASTO_DIFERENCIA_CAMBIARIA','87'); // accounting code 603-001
	
	define('ACCOUNTING_CODE_BANKS_CS','12'); // accounting code 101-003-001
	define('ACCOUNTING_CODE_BANKS_USD','14'); // accounting code 101-003-002
	
	define('ACCOUNTING_CODE_BANK_CS','83'); // accounting code 101-003-001-001
	define('ACCOUNTING_CODE_BANK_USD','153'); // accounting code 101-003-002-001
	
	define('ACCOUNTING_CODE_ACTIVOS','1'); // accounting code 100
	define('ACCOUNTING_CODE_PASIVOS','32'); // accounting code 200
	
	define('ACCOUNTING_REGISTER_TYPE_CD','2'); 
	define('ACCOUNTING_REGISTER_TYPE_CP','3'); 
	
	define('WAREHOUSE_DEFAULT','1'); 
	define('WAREHOUSE_FINISHED','2'); 
	
	define('PRODUCTION_RUN_TYPE_BOTTE','1'); 
	
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	public $components = array(	
		'Session',
		//'DebugKit.Toolbar',
		'Acl',
        'Auth' => array(
            'authorize' => array(
                'Actions' => array('actionPath' => 'controllers')
            )
        ),
		//'AclMenu.Menu'
		/******* for original auth use
		'Auth' => array(
            'loginRedirect' => array(
                'controller' => 'locations',
                'action' => 'display',
				'home'
            ),
            'logoutRedirect' => array(
                'controller' => 'users',
                'action' => 'login'
            ),
			'authorize' =>  array('Controller')
        )
		*****************/
	);
	public $helpers = array( 
		'Html', 
		'Form', 
		'Session',
		'MenuBuilder.MenuBuilder' => array(
			'authVar' => 'user',
			'authModel' => 'User',
			'authField' => 'role_id',
		),
	);
	
	function recordUserActivity($userName,$userEvent){
		$this->request->data['UserLog']['user_id'] = $this->Auth->User('id');;
		$this->request->data['UserLog']['username'] = $userName;
		$this->request->data['UserLog']['event'] = $this->normalizeChars($userEvent);
		$this->request->data['UserLog']['created'] = date("Y-m-d H:i:s");
		
		$this->loadModel('UserLog');
		$this ->UserLog->create();
		$this->UserLog->save($this->request->data);
	}
	
	function recordUserAction($item_id=null,$action_name=null,$controller_name=null){
		
		if ($item_id==null){
			$item_id=0;
			if (!empty($this->params['pass'])){
				$item_id=$this->params['pass']['0'];
			}
		}
		if ($action_name==null){
			$action_name= $this->params['action'];
		}
		if ($controller_name==null){
			$controller_name= $this->params['controller'];
		}
		//echo "action name is ".$action_name."<br/>";
		//pr($this->params);
		//echo "controller is ".$currentController."<br/>";
		//echo "action is ".$currentAction."<br/>";
		//echo "parameter is ".$currentParameter."<br/>";
		
		$this->loadModel('UserAction');
		$userActionData=array();
		$userActionData['UserAction']['user_id']=$this->Auth->User('id');
		$userActionData['UserAction']['controller_name']=$controller_name;
		$userActionData['UserAction']['action_name']=$action_name;
		$userActionData['UserAction']['item_id']=$item_id;
		$userActionData['UserAction']['action_datetime']= date("Y-m-d H:i:s");
		$this ->UserAction->create();
		$this->UserAction->save($userActionData);
		
	}
		
    public function beforeFilter() {
		//Configure AuthComponent
		
		$this->Auth->authError = "No tiene permiso para ver este funcionalidad";
        $this->Auth->loginAction = array(
          'controller' => 'users',
          'action' => 'login'
        );
        $this->Auth->logoutRedirect = array(
          'controller' => 'users',
          'action' => 'login'
        );
		$this->Auth->loginRedirect = array(
		  'controller' => 'stock_items',
		  'action' => 'index',
		  'home'
		);
		
		$user = $this->Auth->user();
		$this->set(compact('user'));
		
		$userrole = $this->Auth->User('role_id');
		$username = $this->Auth->User('username');
		
		$userhomepage = $this->userhome($userrole);
		$this->set(compact('userrole','username','userhomepage'));
		
		$this->loadModel('ExchangeRate');
		$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$currentExchangeRate=$exchangeRate['ExchangeRate']['rate'];
		$this->set(compact('currentExchangeRate'));
	
        //$this->Auth->allow();
		/*
		if ($this->Session->check('Config.language')) {
            Configure::write('Config.language', $this->Session->read('Config.language'));
        }
		*/
		
		// Define your menu for MenuBuilder
		
    $menu = array(
      'main-menu' => array(
				array(
          'title' => __('Entries'),
          'url' => array('controller' => 'orders', 'action' => 'resumenEntradas'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'purchases',
        ),
				array(
          'title' => __('Production'),
          'url' => array('controller' => 'production_runs', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'production',
        ),
        array(
          'title' => __('Inventory'),
          'url' => array('controller' => 'stock_items', 'action' => 'inventario'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_SALES),
					'activesetter' => 'inventory',
        ),
				array(
          'title' => __('Salidas'),
          'url' => array('controller' => 'orders', 'action' => 'resumenVentasRemisiones'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES),
					'activesetter' => 'exits',
        ),
				array(
          'title' => __('Closing Dates'),
          'url' => array('controller' => 'closing_dates', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_MANAGER),
					'activesetter' => 'closingdates',
        ),
				array(
          'title' => __('Ingresos'),
          'url' => array('controller' => 'cash_receipts', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'finance',
        ),
				array(
          'title' => __('Reportes'),
          'url' => array('controller' => 'production_movements', 'action' => 'verReporteProduccionMeses'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'reports',
        ),
				array(
          'title' => __('Configuration'),
          'url' => array('controller' => 'pages', 'action' => 'display','productionconfig'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'configuration',
        ),
      ),
			'sub-menu-inventory' => array(
				array(
          'title' => __('Inventory'),
          'url' => array('controller' => 'stock_items', 'action' => 'inventario'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'inventory',
        ),
				array(
          'title' => __('Reclassify Inventory'),
          'url' => array('controller' => 'stock_items', 'action' => 'resumenReclasificaciones'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'reclassification',
        ),
				array(
          'title' => __('Transferencia entre Bodegas'),
          'url' => array('controller' => 'stock_movements', 'action' => 'transferirLote'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'transferirlote',
        ),
				array(
          'title' => __('Detalle Costo Producto'),
          'url' => array('controller' => 'stock_items', 'action' => 'detalleCostoProducto'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'detallecostoproducto',
        ),
      ),
			'sub-menu-finance' => array(
          array(
            'title' => __('Recibos de Caja'),
            'url' => array('controller' => 'cash_receipts', 'action' => 'index'),
            'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
            'activesetter' => 'cashreceipts',
          ),
          array(
            'title' => __('Cheques'),
            'url' => array('controller' => 'cheques', 'action' => 'index'),
            'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
            'activesetter' => 'cheques',
          ),
          array(
            'title' => __('Transferencias'),
            'url' => array('controller' => 'transfers', 'action' => 'index'),
            'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
            'activesetter' => 'transfers',
          ),
          array(
                    'title' => __('Tasas de Cambio'),
                    'url' => array('controller' => 'exchange_rates', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'exchangerates',
                ),
				array(
                    'title' => __('Cuentas Contables'),
                    'url' => array('controller' => 'accounting_codes', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'accountingcodes',
                ),
				array(
                    'title' => __('Tipos de Comprobante'),
                    'url' => array('controller' => 'accounting_register_types', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'accountingregistertypes',
                ),
				array(
                    'title' => __('Comprobantes'),
                    'url' => array('controller' => 'accounting_registers', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'accountingregisters',
                ),
				array(
                    'title' => __('Reportes'),
                    'url' => array('controller' => 'accounting_codes', 'action' => 'verReporteCaja'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'financereports',
					'children' => array(                
						array(
							'title' => __('Cobros de la Semana'),
							'url' => array('controller' => 'invoices', 'action' => 'verCobrosSemana'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'cobrossemana',
						),
						array(
							'title' => __('Reporte Caja'),
							'url' => array('controller' => 'accounting_codes', 'action' => 'verReporteCaja'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportingresoscaja',
						),
						array(
							'title' => __('Historial de Pagos'),
							'url' => array('controller' => 'invoices', 'action' => 'verHistorialPagos'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reporthistorialpagos',
						),
						array(
							'title' => __('Clientes Por Cobrar'),
							'url' => array('controller' => 'invoices', 'action' => 'verClientesPorCobrar'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportclientesporcobrar',
						),
						array(
							'title' => __('Estado Resultados Financieros'),
							'url' => array('controller' => 'accounting_registers', 'action' => 'verEstadoResultados'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportestadoresultados',
						),
						array(
							'title' => __('Balance General'),
							'url' => array('controller' => 'accounting_registers', 'action' => 'verBalanceGeneral'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportbalancegeneral',
						),
					),
				),
            ),
            'sub-menu-reports' => array(
                array(
                    'title' => __('Estado de Resultados'),
                    'url' => array('controller' => 'stock_items', 'action' => 'estadoResultados'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'reporteestadoresultados',
                ),
                array(
                    'title' => __('Reporte Movimientos Productos'),
                    'url' => array('controller' => 'stock_items', 'action' => 'verReporteProductos'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'reporteproductos',
					/*
					'children' => array(                
						array(
							'title' => __('Reporte de Producto Materia Prima'),
							'url' => array('controller' => 'stock_items', 'action' => 'verReporteProducto'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportproductrawmaterial',
						),
						array(
							'title' => __('Reporte de Producción de Productos Fabricados'),
							'url' => array('controller' => 'products', 'action' => 'verReporteProducto'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportfinishedproductproduction',
						),
						array(
							'title' => __('Reporte Compra Venta de Tapones'),
							'url' => array('controller' => 'stock_movements', 'action' => 'verReporteCompraVenta'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportpurchasesalecaps',
						),
					),
					*/
                ),
				array(
                    'title' => __('Producción Detallada'),
                    'url' => array('controller' => 'stock_items', 'action' => 'verReporteProduccionDetalle'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'reporteproducciondetalle',
                ),
				array(
                    'title' => __('Reporte Salidas'),
                    'url' => array('controller' => 'products', 'action' => 'viewSaleReport'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'reportesalidas',
                ),
				array(
                    'title' => __('Cierre'),
                    'url' => array('controller' => 'orders', 'action' => 'verReporteCierre'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
					'activesetter' => 'reportecierre',
                ),
				array(
                    'title' => __('Reporte Producción Supervisor'),
                    'url' => array('controller' => 'production_movements', 'action' => 'verReporteProduccionMeses'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'reporteproduccionmeses',
                ),
				array(
                    'title' => __('Venta Producto por Cliente'),
                    'url' => array('controller' => 'stock_movements', 'action' => 'verReporteVentaProductoPorCliente'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN),
					'activesetter' => 'reporteventaproductoporcliente',
                ),
            ),
			'sub-menu-configuration' => array(
				array(
                    'title' => __('Tipos de Producto'),
                    'url' => array('controller' => 'product_types', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'producttypes',
                ),
				array(
                    'title' => __('Productos'),
                    'url' => array('controller' => 'products', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'products',
                ),
				array(
                    'title' => __('Proveedores'),
                    'url' => array('controller' => 'thirdParties', 'action' => 'resumenProveedores'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'providers',
                ),
				array(
                    'title' => __('Clientes'),
                    'url' => array('controller' => 'thirdParties', 'action' => 'resumenClientes'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'clients',
                ),
				array(
                    'title' => __('Máquinas'),
                    'url' => array('controller' => 'machines', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'machines',
                ),
				array(
                    'title' => __('Operadores'),
                    'url' => array('controller' => 'operators', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'operators',
                ),
				array(
                    'title' => __('Turnos'),
                    'url' => array('controller' => 'shifts', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'shifts',
                ),
				array(
                    'title' => __('Bodegas'),
                    'url' => array('controller' => 'warehouses', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'warehouses',
                ),
                array(
                    'title' => __('Usuarios'),
                    'url' => array('controller' => 'users', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'users',
                ),
				array(
                    'title' => __('Papeles'),
                    'url' => array('controller' => 'roles', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'roles',
                ),
				array(
                    'title' => __('Permisos de Usuarios'),
                    'url' => array('controller' => 'users', 'action' => 'rolePermissions'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'rolepermissions',
                ),
				array(
                    'title' => __('Empleados'),
                    'url' => array('controller' => 'employees', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'employees',
                ),
				array(
                    'title' => __('Días de Vacaciones'),
                    'url' => array('controller' => 'employee_holidays', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'employeeholidays',
                ),
				array(
                    'title' => __('Motivos de Vacaciones'),
                    'url' => array('controller' => 'holiday_types', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'holidaytypes',
                ),
			),
        );
		$currentController= $this->params['controller'];
		$currentAction= $this->params['action'];
		$currentParameter=0;
		if (!empty($this->params['pass'])){
		
			$currentParameter=$this->params['pass']['0'];
		}
		/*
		//pr($this->params);
		echo "controller is ".$currentController."<br/>";
		echo "action is ".$currentAction."<br/>";
		echo "parameter is ".$currentParameter."<br/>";
		*/
		$sub="NA";
		$activeMenu="NA";
		$activeSub="NA";
		$activeSecond="NA";
		if (($currentAction=="index"||$currentAction=="view"||$currentAction=="add"||$currentAction=="edit") &&($currentController!="orders"&&$currentController!="third_parties")){
			switch($currentController){
				case "production_runs": 
					$activeMenu="production";
					//$activeSub="accountingcodes";
					//$sub="main-menu";
					break;
				case "closing_dates": 
					$activeMenu="closingdates";
					//$activeSub="accountingregisters";
					//$sub="left-menu-finance";
					break;
				case "cheques": 
					$activeMenu="finance";
					$activeSub="cheques";
					$sub="sub-menu-finance";
					break;
				/*case "cheque_types": 
					$activeMenu="finance";
					$activeSub="chequetypes";
					$sub="sub-menu-finance";
					break;*/
				case "transfers": 
					$activeMenu="finance";
					$activeSub="transfers";
					$sub="sub-menu-finance";
					break;
				case "cash_receipts": 
					$activeMenu="finance";
					$activeSub="cashreceipts";
					$sub="sub-menu-finance";
					break;
				case "exchange_rates": 
					$activeMenu="finance";
					$activeSub="exchangerates";
					$sub="sub-menu-finance";
					break;
				case "accounting_codes": 
					$activeMenu="finance";
					$activeSub="accountingcodes";
					$sub="sub-menu-finance";
					break;
				case "accounting_register_types": 
					$activeMenu="finance";
					$activeSub="accountingregistertypes";
					$sub="sub-menu-finance";
					break;
				case "accounting_registers": 
					$activeMenu="finance";
					$activeSub="accountingregisters";
					$sub="sub-menu-finance";
					break;
				case "product_types": 
					$activeMenu="configuration";
					$activeSub="producttypes";
					$sub="sub-menu-configuration";
					break;
				case "products": 
					$activeMenu="configuration";
					$activeSub="products";
					$sub="sub-menu-configuration";
					break;
				case "machines": 
					$activeMenu="configuration";
					$activeSub="machines";
					$sub="sub-menu-configuration";
					break;
				case "operators": 
					$activeMenu="configuration";
					$activeSub="operators";
					$sub="sub-menu-configuration";
					break;
				case "shifts": 
					$activeMenu="configuration";
					$activeSub="shifts";
					$sub="sub-menu-configuration";
					break;
				case "warehouses": 
					$activeMenu="configuration";
					$activeSub="warehouses";
					$sub="sub-menu-configuration";
					break;
				case "users": 
					$activeMenu="configuration";
					$activeSub="users";
					$sub="sub-menu-configuration";
					break;
				case "roles": 
					$activeMenu="configuration";
					$activeSub="roles";
					$sub="sub-menu-configuration";
					break;	
				case "employees": 
					$activeMenu="configuration";
					$activeSub="employees";
					$sub="sub-menu-configuration";
					break;
				case "employee_holidays": 
					$activeMenu="configuration";
					$activeSub="employeeholidays";
					$sub="sub-menu-configuration";
					break;	
				case "holiday_types": 
					$activeMenu="configuration";
					$activeSub="holidaytypes";
					$sub="sub-menu-configuration";
					break;	
			}
		}
		else if ($currentAction=="inventario"&&$currentController=="stock_items"){
			$activeMenu="inventory";
			$activeSub="inventory";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="resumenReclasificaciones"&&$currentController=="stock_items"){
			$activeMenu="inventory";
			$activeSub="reclassification";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="transferirLote"&&$currentController=="stock_movements"){
			$activeMenu="inventory";
			$activeSub="transferirlote";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="detalleCostoProducto"&&$currentController=="stock_items"){
			$activeMenu="inventory";
			$activeSub="detallecostoproducto";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="estadoResultados"&&$currentController=="stock_items"){
			$activeMenu="reports";
			$activeSub="reporteestadoresultados";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProductos"&&$currentController=="stock_items"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="stock_items"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="products"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteCompraVenta"&&$currentController=="stock_movements"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProduccionDetalle"&&$currentController=="stock_items"){
			$activeMenu="reports";
			$activeSub="reporteproducciondetalle";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="viewSaleReport"&&$currentController=="products"){
			$activeMenu="reports";
			$activeSub="reportesalidas";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteCierre"&&$currentController=="orders"){
			$activeMenu="reports";
			$activeSub="reportecierre";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProduccionMeses"&&$currentController=="production_movements"){
			$activeMenu="reports";
			$activeSub="reporteproduccionmeses";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteVentaProductoPorCliente"&&$currentController=="stock_movements"){
			$activeMenu="reports";
			$activeSub="reporteventaproductoporcliente";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verCobrosSemana" && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="cobrossemana";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verReporteCaja" && $currentController=="accounting_codes"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportingresoscaja";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verHistorialPagos" && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reporthistorialpagos";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verClientesPorCobrar" && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportclientesporcobrar";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verFacturasPorCobrar" && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportclientesporcobrar";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verEstadoResultados" && $currentController=="accounting_registers"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportestadoresultados";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verBalanceGeneral" && $currentController=="accounting_registers"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportbalancegeneral";
			$sub="sub-menu-finance";
		}
		else if (($currentAction=="resumenProveedores"||$currentAction=="crearProveedor"||$currentAction=="editarProveedor"||$currentAction=="verProveedor") && $currentController=="third_parties"){
			$activeMenu="configuration";
			$activeSub="providers";
			//$activeSecond="reportbalancegeneral";
			$sub="sub-menu-configuration";
		}
		else if (($currentAction=="resumenClientes"||$currentAction=="crearCliente"||$currentAction=="editarCliente"||$currentAction=="verCliente") && $currentController=="third_parties"){
			$activeMenu="configuration";
			$activeSub="clients";
			//$activeSecond="reportbalancegeneral";
			$sub="sub-menu-configuration";
		}
	
		
		$active=array();
		$active['activeMenu']=$activeMenu;
		$active['activeSub']=$activeSub;
		$active['activeSecond']=$activeSecond;
		//pr($sub);
		//pr($active);
        // For default settings name must be menu
        $this->set(compact('menu','active','sub'));
		
		$modificationInfo=NA;
		
		if($currentAction=="edit"||$currentAction=="view"||$currentAction=="editarCliente"||$currentAction=="editarProveedor"||$currentAction=="verCliente"||$currentAction=="verProveedor"||$currentAction=="editarVenta"||$currentAction=="editarEntrada"||$currentAction=="editarRemision"||$currentAction=="verVenta"||$currentAction=="verEntrada"||$currentAction=="verRemision"){
			$this->loadModel('UserAction');
			$userActions=$this->UserAction->find('all',array(
				'fields'=>array(
					'UserAction.action_name','UserAction.action_datetime',
					'UserAction.user_id','User.username',
				),
				'conditions'=>array(
					'UserAction.controller_name'=>$currentController,
					'UserAction.item_id'=>$currentParameter,
				),
				'order'=>'action_datetime DESC',
			));
			//pr($userActions);
			if (!empty($userActions)){
				
				$lastAction="";
				if ($userActions[0]['UserAction']['action_name']=="add"){
					$lastAction="Grabado por ";
				}
				elseif ($userActions[0]['UserAction']['action_name']=="edit"){
					$lastAction="Modificado por ";
				}
				
				$lastAction.=$userActions[0]['User']['username']." ";
				
				$actionDateTime=new DateTime($userActions[0]['UserAction']['action_datetime']);
				$lastAction.=$actionDateTime->format('d-m-Y H:i:s');
				$modificationInfo="";
				//$modificationInfo="<ul class='nav pull-right' style='position:absolute;right:300px;top:30px;'>";
				//$modificationInfo.="<div class='btn-group'>";
				//	$modificationInfo.="<a class='btn dropdown-toggle' data-toggle='dropdown' href='#'> Action<span class='caret'></span></a>";
				
				//$modificationInfo.="<ul class='nav pull-right'>";
				$modificationInfo.="<ul class='nav'>";
					$modificationInfo.="<li class='dropdown'>";
						$modificationInfo.="<a class='dropdown-toggle' data-toggle='dropdown' href='#'>";
							$modificationInfo.=$lastAction;
							$modificationInfo.="<i class='icon-angle-down'></i>";
						$modificationInfo.="</a>";
						
						if (count($userActions)>1){
							
							$modificationInfo.="<ul class='dropdown-menu'>";
							for ($i=1;$i<count($userActions);$i++){
								$actionInfo="";
								if ($userActions[$i]['UserAction']['action_name']=="add"){
									$actionInfo="Grabado por ";
								}
								elseif ($userActions[$i]['UserAction']['action_name']=="edit"){
									$actionInfo="Modificado por ";
								}
								$actionInfo.=$userActions[$i]['User']['username']." ";
								$actionDateTime=new DateTime($userActions[$i]['UserAction']['action_datetime']);
								$actionInfo.=$actionDateTime->format('d-m-Y H:i:s');
							
							
								$modificationInfo.="<li>";
									$modificationInfo.="<i class='icon-key'></i>";
									$modificationInfo.=$actionInfo;
								$modificationInfo.="</li>";
							}	
							$modificationInfo.="</ul>";
						}
						
						
					$modificationInfo.="</li>";
				$modificationInfo.="</ul>";			
				
				//$modificationInfo.="</div>";
				
			}
			
		}
		
		$this->set(compact('modificationInfo'));
		
		if (!(($currentController=='pages')&&($currentAction=='display'||$currentAction=='productionconfig'))){
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/add";		
			//pr($aco_name);
			$userid=$this->Auth->User('id');
			//pr($userid);
			if (!empty($userid)){
				$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_add_permission=false;
			}
			//echo "bool add permission is ".$bool_add_permission."<br/>";
			$this->set(compact('bool_add_permission'));
			
			
			$userid=$this->Auth->User('id');
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/edit";		
			//pr($userid);
			if (!empty($userid)){
				$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_edit_permission=false;
			}
			//echo "bool edit permission is ".$bool_edit_permission."<br/>";
			$this->set(compact('bool_edit_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/delete";		
			if (!empty($userid)){
				$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_delete_permission=false;
			}
			//echo "bool delete permission is ".$bool_delete_permission."<br/>";
			$this->set(compact('bool_delete_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/annul";		
			if (!empty($userid)){
				$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_annul_permission=false;
			}
			//echo "bool annul permission is ".$bool_annul_permission."<br/>";
			$this->set(compact('bool_annul_permission'));
		}
		
		$exchangeRateUpdateNeeded=false;
		$this->loadModel('ExchangeRate');
		$exchangeRateDuration=$this->ExchangeRate->getLatestExchangeRateDuration();
		//echo "exchange rate duration is ".$exchangeRateDuration."<br/>";
		if ($exchangeRateDuration>31){
			$exchangeRateUpdateNeeded=true;
		}
		$this->set(compact('exchangeRateUpdateNeeded'));
	//	if($exchangeRateUpdateNeeded){
	//		echo "<script>alert('Se venció la tasa de cambio, por favor introduzca la nueva tasa de cambio!');</script>";
	//	}
    }
	
	public function hasPermission($user_id,$aco_name){
		$this->loadModel('User');
		$user=$this->User->read(null,$user_id);
		//pr($user);
		//pr($aco_name);
		if (!empty($user)){
			return $this->Acl->check(array('Role'=>array('id'=>$user['User']['role_id'])),$aco_name);
		}
		else {
			return false;
		}
	}

	public function userhome($userrole){
		switch ($userrole){
			case ROLE_ADMIN:
			case ROLE_MANAGER:
				//echo "redirecting to orders!<br/>";
				return array(
					'controller' => 'orders',
					'action' => 'resumenEntradas',
					'home'
				);
				break;
			case ROLE_ASSISTANT:
				//echo "redirecting to orders!<br/>";
				return array(
					'controller' => 'invoices',
					'action' => 'verCobrosSemana',
					'home'
				);
				break;	
			case ROLE_FOREMAN:
				//echo "redirecting to production_runs!<br/>";
				return array(
					'controller' => 'production_runs',
					'action' => 'index'
				);
			default:
				//echo "redirecting to loginpage!<br/>";
				return array(
				  'controller' => 'users',
				  'action' => 'login'
				);
				break;
		}
	}
	
	public static function normalizeChars($s) {
		$replace = array(
			'ъ'=>'-', 'Ь'=>'-', 'Ъ'=>'-', 'ь'=>'-',
			'Ă'=>'A', 'Ą'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
			'Þ'=>'B',
			'Ć'=>'C', 'ץ'=>'C', 'Ç'=>'C',
			'È'=>'E', 'Ę'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
			'Ğ'=>'G',
			'İ'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
			'Ł'=>'L',
			'Ñ'=>'N', 'Ń'=>'N',
			'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
			'Ş'=>'S', 'Ś'=>'S', 'Ș'=>'S', 'Š'=>'S',
			'Ț'=>'T',
			'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
			'Ý'=>'Y',
			'Ź'=>'Z', 'Ž'=>'Z', 'Ż'=>'Z',
			'â'=>'a', 'ǎ'=>'a', 'ą'=>'a', 'á'=>'a', 'ă'=>'a', 'ã'=>'a', 'Ǎ'=>'a', 'а'=>'a', 'А'=>'a', 'å'=>'a', 'à'=>'a', 'א'=>'a', 'Ǻ'=>'a', 'Ā'=>'a', 'ǻ'=>'a', 'ā'=>'a', 'ä'=>'ae', 'æ'=>'ae', 'Ǽ'=>'ae', 'ǽ'=>'ae',
			'б'=>'b', 'ב'=>'b', 'Б'=>'b', 'þ'=>'b',
			'ĉ'=>'c', 'Ĉ'=>'c', 'Ċ'=>'c', 'ć'=>'c', 'ç'=>'c', 'ц'=>'c', 'צ'=>'c', 'ċ'=>'c', 'Ц'=>'c', 'Č'=>'c', 'č'=>'c', 'Ч'=>'ch', 'ч'=>'ch',
			'ד'=>'d', 'ď'=>'d', 'Đ'=>'d', 'Ď'=>'d', 'đ'=>'d', 'д'=>'d', 'Д'=>'D', 'ð'=>'d',
			'є'=>'e', 'ע'=>'e', 'е'=>'e', 'Е'=>'e', 'Ə'=>'e', 'ę'=>'e', 'ĕ'=>'e', 'ē'=>'e', 'Ē'=>'e', 'Ė'=>'e', 'ė'=>'e', 'ě'=>'e', 'Ě'=>'e', 'Є'=>'e', 'Ĕ'=>'e', 'ê'=>'e', 'ə'=>'e', 'è'=>'e', 'ë'=>'e', 'é'=>'e',
			'ф'=>'f', 'ƒ'=>'f', 'Ф'=>'f',
			'ġ'=>'g', 'Ģ'=>'g', 'Ġ'=>'g', 'Ĝ'=>'g', 'Г'=>'g', 'г'=>'g', 'ĝ'=>'g', 'ğ'=>'g', 'ג'=>'g', 'Ґ'=>'g', 'ґ'=>'g', 'ģ'=>'g',
			'ח'=>'h', 'ħ'=>'h', 'Х'=>'h', 'Ħ'=>'h', 'Ĥ'=>'h', 'ĥ'=>'h', 'х'=>'h', 'ה'=>'h',
			'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', 'į'=>'i', 'ĭ'=>'i', 'ı'=>'i', 'Ĭ'=>'i', 'И'=>'i', 'ĩ'=>'i', 'ǐ'=>'i', 'Ĩ'=>'i', 'Ǐ'=>'i', 'и'=>'i', 'Į'=>'i', 'י'=>'i', 'Ї'=>'i', 'Ī'=>'i', 'І'=>'i', 'ї'=>'i', 'і'=>'i', 'ī'=>'i', 'ĳ'=>'ij', 'Ĳ'=>'ij',
			'й'=>'j', 'Й'=>'j', 'Ĵ'=>'j', 'ĵ'=>'j', 'я'=>'ja', 'Я'=>'ja', 'Э'=>'je', 'э'=>'je', 'ё'=>'jo', 'Ё'=>'jo', 'ю'=>'ju', 'Ю'=>'ju',
			'ĸ'=>'k', 'כ'=>'k', 'Ķ'=>'k', 'К'=>'k', 'к'=>'k', 'ķ'=>'k', 'ך'=>'k',
			'Ŀ'=>'l', 'ŀ'=>'l', 'Л'=>'l', 'ł'=>'l', 'ļ'=>'l', 'ĺ'=>'l', 'Ĺ'=>'l', 'Ļ'=>'l', 'л'=>'l', 'Ľ'=>'l', 'ľ'=>'l', 'ל'=>'l',
			'מ'=>'m', 'М'=>'m', 'ם'=>'m', 'м'=>'m',
			'ñ'=>'n', 'н'=>'n', 'Ņ'=>'n', 'ן'=>'n', 'ŋ'=>'n', 'נ'=>'n', 'Н'=>'n', 'ń'=>'n', 'Ŋ'=>'n', 'ņ'=>'n', 'ŉ'=>'n', 'Ň'=>'n', 'ň'=>'n',
			'о'=>'o', 'О'=>'o', 'ő'=>'o', 'õ'=>'o', 'ô'=>'o', 'Ő'=>'o', 'ŏ'=>'o', 'Ŏ'=>'o', 'Ō'=>'o', 'ō'=>'o', 'ø'=>'o', 'ǿ'=>'o', 'ǒ'=>'o', 'ò'=>'o', 'Ǿ'=>'o', 'Ǒ'=>'o', 'ơ'=>'o', 'ó'=>'o', 'Ơ'=>'o', 'œ'=>'oe', 'Œ'=>'oe', 'ö'=>'oe',
			'פ'=>'p', 'ף'=>'p', 'п'=>'p', 'П'=>'p',
			'ק'=>'q',
			'ŕ'=>'r', 'ř'=>'r', 'Ř'=>'r', 'ŗ'=>'r', 'Ŗ'=>'r', 'ר'=>'r', 'Ŕ'=>'r', 'Р'=>'r', 'р'=>'r',
			'ș'=>'s', 'с'=>'s', 'Ŝ'=>'s', 'š'=>'s', 'ś'=>'s', 'ס'=>'s', 'ş'=>'s', 'С'=>'s', 'ŝ'=>'s', 'Щ'=>'sch', 'щ'=>'sch', 'ш'=>'sh', 'Ш'=>'sh', 'ß'=>'ss',
			'т'=>'t', 'ט'=>'t', 'ŧ'=>'t', 'ת'=>'t', 'ť'=>'t', 'ţ'=>'t', 'Ţ'=>'t', 'Т'=>'t', 'ț'=>'t', 'Ŧ'=>'t', 'Ť'=>'t', '™'=>'tm',
			'ū'=>'u', 'у'=>'u', 'Ũ'=>'u', 'ũ'=>'u', 'Ư'=>'u', 'ư'=>'u', 'Ū'=>'u', 'Ǔ'=>'u', 'ų'=>'u', 'Ų'=>'u', 'ŭ'=>'u', 'Ŭ'=>'u', 'Ů'=>'u', 'ů'=>'u', 'ű'=>'u', 'Ű'=>'u', 'Ǖ'=>'u', 'ǔ'=>'u', 'Ǜ'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'У'=>'u', 'ǚ'=>'u', 'ǜ'=>'u', 'Ǚ'=>'u', 'Ǘ'=>'u', 'ǖ'=>'u', 'ǘ'=>'u', 'ü'=>'ue',
			'в'=>'v', 'ו'=>'v', 'В'=>'v',
			'ש'=>'w', 'ŵ'=>'w', 'Ŵ'=>'w',
			'ы'=>'y', 'ŷ'=>'y', 'ý'=>'y', 'ÿ'=>'y', 'Ÿ'=>'y', 'Ŷ'=>'y',
			'Ы'=>'y', 'ž'=>'z', 'З'=>'z', 'з'=>'z', 'ź'=>'z', 'ז'=>'z', 'ż'=>'z', 'ſ'=>'z', 'Ж'=>'zh', 'ж'=>'zh'
		);
		return strtr($s, $replace);
	}	
	
	public function recreateStockItemLogs($id = null) {
		$this->StockItem->id = $id;
		if (!$this->StockItem->exists()) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		//$this->request->allowMethod('post', 'delete');
		$this->loadModel('StockItemLog');
		$stockItem=$this->StockItem->find('first',array(
			'conditions'=>array('StockItem.id'=>$id),
			'contain'=>array(
				'StockItemLog',
				'Product'=>array(
					'ProductType'=>array(
						'fields'=>'ProductType.product_category_id',
					)
				)
			)
		));
		//pr($stockItem);
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try{
			foreach ($stockItem['StockItemLog'] as $stockItemLog){
				//pr($stockItemLog);
				$this->StockItemLog->id=$stockItemLog['id'];
				$logsuccess=$this->StockItemLog->delete();
				if (!$logsuccess) {
					echo "problema eliminando los estados de lote";
					pr($this->validateErrors($this->StockItemLog));
					throw new Exception();
				}
			}
			$datasource->commit();
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			return false;
		}
		// recreate all stockitemlogs
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionMovement');
		
		$creationmovement=array();
		$reclassificationcreationmovement=array();
		$transfercreationmovement=array();
		$movements=array();
		$exitedrawmovements=array();
		$stockMovementUsed=false;
		$productionMovementUsed=false;
		$productionMovementAndRawExitUsed=false;
		
		$categoryid=$stockItem['Product']['ProductType']['product_category_id'];
		switch ($categoryid){
			case CATEGORY_RAW:
				$creationmovement=$this->StockMovement->find('first',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>true,
					),
					'contain'=>array(
						'StockItem',
					),
				));
				
				$movements=$this->ProductionMovement->find('all',array(
					'conditions'=>array(
						'ProductionMovement.stockitem_id'=>$id,
						'bool_input'=>true,
					),
					'contain'=>array(
						'StockItem',
					),
					'order'=>'movement_date, ProductionMovement.id',
				));
				$productionMovementUsed=true;
				$exitedrawmovements=$this->StockMovement->find('all',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>false,
					),
					'contain'=>array(
						'StockItem',
					),
					'order'=>'movement_date, StockMovement.id',
				));
				if (count($exitedrawmovements)>0){
					$productionMovementAndRawExitUsed=true;
				}
				break;
			case CATEGORY_PRODUCED:
				$creationmovement=$this->ProductionMovement->find('first',array(
					'conditions'=>array(
						'ProductionMovement.stockitem_id'=>$id,
						'bool_input'=>false,
					),
					'contain'=>array(
						'StockItem',
					),
				));
				if (empty($creationmovement)){
					$reclassificationcreationmovement=$this->StockMovement->find('first',array(
						'conditions'=>array(
							'StockMovement.stockitem_id'=>$id,
							'bool_input'=>true,
							'bool_reclassification'=>true,
						),
						'contain'=>array(
							'StockItem',
						),
					));
				}
				if (empty($reclassificationcreationmovement)){
					$transfercreationmovement=$this->StockMovement->find('first',array(
						'conditions'=>array(
							'StockMovement.stockitem_id'=>$id,
							'bool_input'=>true,
							'bool_transfer'=>true,
						),
						'contain'=>array(
							'StockItem',
						),
					));
				}
				$movements=$this->StockMovement->find('all',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>false,
					),
					'contain'=>array(
						'StockItem',
					),
					'order'=>'movement_date, StockMovement.id',
				));
				$stockMovementUsed=true;
				break;
			case CATEGORY_OTHER:
				$creationmovement=$this->StockMovement->find('first',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>true,
					),
					'contain'=>array(
						'StockItem',
					),
				));
				$movements=$this->StockMovement->find('all',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>false,
					),
					'contain'=>array(
						'StockItem',
					),
					'order'=>'movement_date, StockMovement.id',
				));
				$stockMovementUsed=true;
				break;
		}
		
		//pr($creationmovement);
		//pr($movements);
		
		$StockItemLogData=array();
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try {
			switch ($categoryid){
				case CATEGORY_RAW:
				case CATEGORY_OTHER:
					$StockItemLogData=array();
					$StockItemLogData['stockitem_id']=$id;
					$StockItemLogData['stock_movement_id']=$creationmovement['StockMovement']['id'];
					$StockItemLogData['stockitem_date']=$creationmovement['StockMovement']['movement_date'];
					$StockItemLogData['product_id']=$creationmovement['StockMovement']['product_id'];
					$StockItemLogData['product_quantity']=$creationmovement['StockMovement']['product_quantity'];
					$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
					$StockItemLogData['warehouse_id']=$creationmovement['StockItem']['warehouse_id'];
					
					$this->StockItemLog->create();
					if (!$this->StockItemLog->save($StockItemLogData)) {
						echo "problema guardando los estado de lote";
						pr($this->validateErrors($this->StockItemLog));
						throw new Exception();
					}
					break;
				case CATEGORY_PRODUCED:
					if (!empty($creationmovement)){
						$StockItemLogData=array();
						$StockItemLogData['stockitem_id']=$id;
						$StockItemLogData['production_movement_id']=$creationmovement['ProductionMovement']['id'];
						$StockItemLogData['stockitem_date']=$creationmovement['ProductionMovement']['movement_date'];
						$StockItemLogData['product_id']=$creationmovement['ProductionMovement']['product_id'];
						$StockItemLogData['product_quantity']=$creationmovement['ProductionMovement']['product_quantity'];
						$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
						$StockItemLogData['production_result_code_id']=$creationmovement['ProductionMovement']['production_result_code_id'];
						$StockItemLogData['warehouse_id']=$creationmovement['StockItem']['warehouse_id'];
						
						$this->StockItemLog->create();
						if (!$this->StockItemLog->save($StockItemLogData)) {
							pr($StockItemLogData);
							echo "problema guardando los estado de lote";
							pr($this->validateErrors($this->StockItemLog));
							throw new Exception();
						}
					}
					else {
						if (!empty($reclassificationcreationmovement)){					
							$StockItemLogData=array();
							$StockItemLogData['stockitem_id']=$id;
							$StockItemLogData['production_movement_id']=$reclassificationcreationmovement['StockMovement']['id'];
							$StockItemLogData['stockitem_date']=$reclassificationcreationmovement['StockMovement']['movement_date'];
							$StockItemLogData['product_id']=$reclassificationcreationmovement['StockMovement']['product_id'];
							$StockItemLogData['product_quantity']=$reclassificationcreationmovement['StockMovement']['product_quantity'];
							$StockItemLogData['product_unit_price']=$reclassificationcreationmovement['StockMovement']['product_unit_price'];
							$StockItemLogData['production_result_code_id']=$reclassificationcreationmovement['StockMovement']['production_result_code_id'];
							$StockItemLogData['warehouse_id']=$reclassificationcreationmovement['StockItem']['warehouse_id'];
							
							$this->StockItemLog->create();
							if (!$this->StockItemLog->save($StockItemLogData)) {
								pr($StockItemLogData);
								echo "problema guardando los estado de lote";
								pr($this->validateErrors($this->StockItemLog));
								throw new Exception();
							}
						}
						else {
							if (!empty($transfercreationmovement)){					
								$StockItemLogData=array();
								$StockItemLogData['stockitem_id']=$id;
								$StockItemLogData['production_movement_id']=$transfercreationmovement['StockMovement']['id'];
								$StockItemLogData['stockitem_date']=$transfercreationmovement['StockMovement']['movement_date'];
								$StockItemLogData['product_id']=$transfercreationmovement['StockMovement']['product_id'];
								$StockItemLogData['product_quantity']=$transfercreationmovement['StockMovement']['product_quantity'];
								$StockItemLogData['product_unit_price']=$transfercreationmovement['StockMovement']['product_unit_price'];
								$StockItemLogData['production_result_code_id']=$transfercreationmovement['StockMovement']['production_result_code_id'];
								$StockItemLogData['warehouse_id']=$transfercreationmovement['StockItem']['warehouse_id'];
								
								$this->StockItemLog->create();
								if (!$this->StockItemLog->save($StockItemLogData)) {
									pr($StockItemLogData);
									echo "problema guardando los estado de lote";
									pr($this->validateErrors($this->StockItemLog));
									throw new Exception();
								}
							}
						}
					}
					break;
			}
			$remainingQuantityStockItem=$stockItem['StockItem']['original_quantity'];		
			
			if ($productionMovementAndRawExitUsed){
				$amountrawregistered=0;
				foreach ($movements as $movement){
					for ($r=$amountrawregistered;$r<count($exitedrawmovements);$r++){
						if ($movement['ProductionMovement']['movement_date']>$exitedrawmovements[$r]['StockMovement']['movement_date']){
							$remainingQuantityStockItem-=$exitedrawmovements[$r]['StockMovement']['product_quantity'];
							
							$StockItemLogData=array();
							$StockItemLogData['stockitem_id']=$id;
							$StockItemLogData['stock_movement_id']=$exitedrawmovements[$r]['StockMovement']['id'];
							$StockItemLogData['production_movement_id']=null;
							$StockItemLogData['stockitem_date']=$exitedrawmovements[$r]['StockMovement']['movement_date'];
							$StockItemLogData['product_id']=$exitedrawmovements[$r]['StockMovement']['product_id'];
							$StockItemLogData['product_quantity']=$remainingQuantityStockItem;
							switch ($categoryid){
								case CATEGORY_RAW:
								case CATEGORY_OTHER:
									$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
									break;
								case CATEGORY_PRODUCED:
									$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
									break;
							}
							//$StockItemLogData['production_result_code_id']=$exitedrawmovement['StockMovement']['production_result_code_id'];
							$amountrawregistered++;
						}
					}
					$remainingQuantityStockItem-=$movement['ProductionMovement']['product_quantity'];
					
					$StockItemLogData=array();
					$StockItemLogData['stockitem_id']=$id;
					$StockItemLogData['stock_movement_id']=null;
					$StockItemLogData['production_movement_id']=$movement['ProductionMovement']['id'];
					$StockItemLogData['stockitem_date']=$movement['ProductionMovement']['movement_date'];
					$StockItemLogData['product_id']=$movement['ProductionMovement']['product_id'];
					$StockItemLogData['product_quantity']=$remainingQuantityStockItem;
					switch ($categoryid){
						case CATEGORY_RAW:
						case CATEGORY_OTHER:
							$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
							break;
						case CATEGORY_PRODUCED:
							$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
							break;
					}
					$StockItemLogData['production_result_code_id']=$movement['ProductionMovement']['production_result_code_id'];
					$StockItemLogData['warehouse_id']=$movement['StockItem']['warehouse_id'];

					$this->StockItemLog->create();
					if (!$this->StockItemLog->save($StockItemLogData)) {
						echo "problema guardando los estado de lote";
						pr($this->validateErrors($this->StockItemLog));
						throw new Exception();
					}
				}
				for ($k=$amountrawregistered;$k<count($exitedrawmovements);$k++){
					$remainingQuantityStockItem-=$exitedrawmovements[$k]['StockMovement']['product_quantity'];
					$StockItemLogData=array();
					$StockItemLogData['stockitem_id']=$id;
					$StockItemLogData['stock_movement_id']=$exitedrawmovements[$k]['StockMovement']['id'];
					$StockItemLogData['production_movement_id']=null;
					$StockItemLogData['stockitem_date']=$exitedrawmovements[$k]['StockMovement']['movement_date'];
					$StockItemLogData['product_id']=$exitedrawmovements[$k]['StockMovement']['product_id'];
					$StockItemLogData['product_quantity']=$remainingQuantityStockItem;
					switch ($categoryid){
						case CATEGORY_RAW:
						case CATEGORY_OTHER:
							$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
							break;
						case CATEGORY_PRODUCED:
							$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
							break;
					}
					//$StockItemLogData['production_result_code_id']=$exitedrawmovements[$k]['StockMovement']['production_result_code_id'];
					$StockItemLogData['warehouse_id']=$exitedrawmovements[$k]['StockItem']['warehouse_id'];
					
					$this->StockItemLog->create();
					if (!$this->StockItemLog->save($StockItemLogData)) {
						echo "problema guardando los estado de lote";
						pr($this->validateErrors($this->StockItemLog));
						throw new Exception();
					}
				}
			}
			else {
				foreach ($movements as $movement){
					if ($productionMovementUsed){
						$remainingQuantityStockItem-=$movement['ProductionMovement']['product_quantity'];
						
						$StockItemLogData=array();
						$StockItemLogData['stockitem_id']=$id;
						$StockItemLogData['stock_movement_id']=null;
						$StockItemLogData['production_movement_id']=$movement['ProductionMovement']['id'];
						$StockItemLogData['stockitem_date']=$movement['ProductionMovement']['movement_date'];
						$StockItemLogData['product_id']=$movement['ProductionMovement']['product_id'];
						$StockItemLogData['product_quantity']=$remainingQuantityStockItem;
						switch ($categoryid){
							case CATEGORY_RAW:
							case CATEGORY_OTHER:
								$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
								break;
							case CATEGORY_PRODUCED:
								$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
								break;
						}
						$StockItemLogData['production_result_code_id']=$movement['ProductionMovement']['production_result_code_id'];
						$StockItemLogData['warehouse_id']=$movement['StockItem']['warehouse_id'];
					}
					if ($stockMovementUsed){
						$remainingQuantityStockItem-=$movement['StockMovement']['product_quantity'];
						
						$StockItemLogData=array();
						$StockItemLogData['stockitem_id']=$id;
						$StockItemLogData['stock_movement_id']=$movement['StockMovement']['id'];
						$StockItemLogData['production_movement_id']=null;
						$StockItemLogData['stockitem_date']=$movement['StockMovement']['movement_date'];
						$StockItemLogData['product_id']=$movement['StockMovement']['product_id'];
						$StockItemLogData['product_quantity']=$remainingQuantityStockItem;
						switch ($categoryid){
							case CATEGORY_RAW:
							case CATEGORY_OTHER:
								$StockItemLogData['product_unit_price']=$creationmovement['StockMovement']['product_unit_price'];
								break;
							case CATEGORY_PRODUCED:
								if (!empty($creationmovement)){
									$StockItemLogData['product_unit_price']=$creationmovement['ProductionMovement']['product_unit_price'];
								}
								elseif (!empty($reclassificationcreationmovement)) {
									$StockItemLogData['product_unit_price']=$reclassificationcreationmovement['StockMovement']['product_unit_price'];
								}
								elseif (!empty($transfercreationmovement)) {
									$StockItemLogData['product_unit_price']=$transfercreationmovement['StockMovement']['product_unit_price'];
								}
								break;
						}
						$StockItemLogData['production_result_code_id']=$movement['StockMovement']['production_result_code_id'];
						$StockItemLogData['warehouse_id']=$movement['StockItem']['warehouse_id'];
					}
					
					$this->StockItemLog->create();
					if (!$this->StockItemLog->save($StockItemLogData)) {
						echo "problema guardando los estado de lote";
						pr($this->validateErrors($this->StockItemLog));
						throw new Exception();
					}
				}
			}
			$datasource->commit();
			return true;
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			return false;
		}
	}

	public function get_date($month, $year, $week, $day, $direction) {
		if($direction > 0)
			$startday = 1;
		else
			// t gives number of days in given month, from 28 to 31
			// mktime(hour, minute, second, month, day, year, daylightsavingtime)
			$startday = date('t', mktime(0, 0, 0, $month, 1, $year));

		$start = mktime(0, 0, 0, $month, $startday, $year);
		// N gives numberic representation of weekday 1 (for Monday) through 7 (for Sunday)
		$weekday = date('N', $start);

		if($direction * $day >= $direction * $weekday)
			$offset = -$direction * 7;
		else
			$offset = 0;

		$offset += $direction * ($week * 7) + ($day - $weekday);
		return mktime(0, 0, 0, $month, $startday + $offset, $year);
	}
	
	public function saveAccountingRegisterData($AccountingRegisterDataArray,$bool_new){
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingCode');
		$this->loadModel('Currency');
		$datasource=$this->AccountingRegister->getDataSource();
		$datasource->begin();
		try {
			//pr($AccountingRegisterDataArray);
			if ($bool_new){
				$this->AccountingRegister->create();
			}
			if (!$this->AccountingRegister->save($AccountingRegisterDataArray)) {
				pr($this->validateErrors($this->AccountingRegister));
				echo "Error al guardar el asiento contable";
				throw new Exception();
			}
			
			$accounting_register_id=$this->AccountingRegister->id;
			$accounting_register_accounting_register_type_id=$AccountingRegisterDataArray['AccountingRegister']['accounting_register_type_id'];
			$accounting_register_register_code=$AccountingRegisterDataArray['AccountingRegister']['register_code'];
			$accounting_register_concept=$AccountingRegisterDataArray['AccountingRegister']['concept'];
			$accounting_register_date=$AccountingRegisterDataArray['AccountingRegister']['register_date'];
			$accounting_register_currency_id=$AccountingRegisterDataArray['AccountingRegister']['currency_id'];
			//$linkedCurrency=$this->Currency->read(null,$accounting_register_currency_id);
			//$currency_abbreviation=$linkedCurrency['Currency']['abbreviation'];
			$currency_abbreviation="C$";
			foreach ($AccountingRegisterDataArray['AccountingMovement'] as $accountingMovement){
				//pr($accountingMovement);
				$accounting_movement_amount=0;
				$bool_debit=true;
				
				if (!empty($accountingMovement['debit_amount'])){
					$accounting_movement_amount = round($accountingMovement['debit_amount'],2);
					$bool_debit=true;
				}
				else if (!empty($accountingMovement['credit_amount'])){
					$accounting_movement_amount = round($accountingMovement['credit_amount'],2);
					$bool_debit=false;
				}
				
				$accounting_movement_code_id = $accountingMovement['accounting_code_id'];
				$accounting_movement_concept = $accountingMovement['concept'];
				
				//echo "just before the saving part of accountingmovements.<br/>";
				//echo "accounting movement code id".$accounting_movement_code_id."<br/>";
				//echo "accounting movement amount".$accounting_movement_amount."<br/>";
				if ($accounting_movement_code_id>0 && $accounting_movement_amount>0){
					$accountingCode=$this->AccountingCode->read(null,$accounting_movement_code_id);
					$accounting_movement_code_description = $accountingCode['AccountingCode']['description'];
					
					$logmessage="Registro de cuenta contable ".$accounting_movement_code_description." (Monto:".$accounting_movement_amount." ".$currency_abbreviation.") para Registro Contable ".$accounting_register_concept;
					//echo $logmessage."<br/>";
					// SAVE ACCOUNTING MOVEMENT
					$AccountingMovementItemData['accounting_register_id']=$accounting_register_id;
					$AccountingMovementItemData['accounting_code_id']=$accounting_movement_code_id;
					$AccountingMovementItemData['concept']=$accounting_movement_concept;
					
					
					$AccountingMovementItemData['amount']=$accounting_movement_amount;
					$AccountingMovementItemData['currency_id']=$accounting_register_currency_id;
					
					$AccountingMovementItemData['bool_debit']=$bool_debit;
					//echo "saved item data";
					//pr($AccountingMovementItemData);
					$this->AccountingRegister->AccountingMovement->create();
					if (!$this->AccountingRegister->AccountingMovement->save($AccountingMovementItemData)) {
						pr($this->validateErrors($this->AccountingMovement));
						echo "problema al guardar el movimiento contable";
						throw new Exception();
					}
					
					// SAVE THE USERLOG FOR ACCOUNTING MOVEMENT
					$this->recordUserActivity($this->Session->read('User.username'),$logmessage);
				}
			}			
			$datasource->commit();
			$this->Session->setFlash(__('Se guardó el comprobante.'),'default',array('class' => 'success'));
			return $accounting_register_id;
			
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('No se podía guardar el comprobante. Por favor intente de nuevo.'),'default',array('class' => 'error-message'));
			return false;
		}
	}
	
}