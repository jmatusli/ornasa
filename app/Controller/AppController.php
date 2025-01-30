<?php
  define('COMPANY_NAME','Ornasa');
	define('COMPANY_URL','www.ornasa.com');
	define('COMPANY_MAIL','gerencia@ornasa.com');
	define('COMPANY_ADDRESS','10.1 Carretera nueva a León, 150 mts arriba');
	define('COMPANY_PHONE','2299-1123');
  define('COMPANY_CELL_MOVISTAR','8582-4750');
  define('COMPANY_CELL_CLARO','5792-0808');
	define('COMPANY_RUC','J0310000103860');
  
	define('CURRENCY_CS','1');
	define('CURRENCY_USD','2');
	
	define('ROLE_ADMIN','4');
	define('ROLE_ASSISTANT','5');
	define('ROLE_FOREMAN','6');
	define('ROLE_MANAGER','7');
	define('ROLE_SALES','8');
  define('ROLE_ACCOUNTING','9');
  define('ROLE_CLIENT','10');
  define('ROLE_OPERATIONS','11');
  define('ROLE_FACTURACION','12');
  define('ROLE_DRIVER','13');
	
	define('SHIFT_MORNING','2');
	define('SHIFT_AFTERNOON','3');
	define('SHIFT_NIGHT','4');
	define('SHIFT_EXTRA','5');
	
	define('NA','N/A');
		
	define('MOVEMENT_PURCHASE','4');
	define('MOVEMENT_SALE','5');
  define('MOVEMENT_PURCHASE_CONSUMIBLES','6');
  
  define('PLANT_SANDINO','1');
	define('PLANT_COLINAS','2');
  
  define('PRODUCTION_TYPE_PET','1');
	define('PRODUCTION_TYPE_INJECTION','2');
  define('PRODUCTION_TYPE_FILLING','3');
	
	define('PRODUCT_TYPE_PREFORMA','10');
	define('PRODUCT_TYPE_CAP','9');
	define('PRODUCT_TYPE_BOTTLE','11');
  define('PRODUCT_TYPE_ROLL','12');
  define('PRODUCT_TYPE_CONSUMIBLES','13');
  define('PRODUCT_TYPE_SERVICE','14');
  define('PRODUCT_TYPE_POLINDUSTRIAS','15');
  define('PRODUCT_TYPE_IMPORT','15');
  define('PRODUCT_TYPE_LOCAL','16');
  
  define('PRODUCT_TYPE_INJECTION_GRAIN','17');
  define('PRODUCT_TYPE_INJECTION_OUTPUT','18');
  
  define('PRODUCT_TYPE_BAGS','19');
  
  define('PRODUCT_NATURE_PRODUCED','1');
	define('PRODUCT_NATURE_BOTTLES_BOUGHT','2');
  define('PRODUCT_NATURE_ACCESORIES','3');
  define('PRODUCT_NATURE_RAW','4');
  define('PRODUCT_NATURE_BAGS','5');
  
	define('CATEGORY_RAW','1');
	define('CATEGORY_PRODUCED','2');
	define('CATEGORY_OTHER','3');
  define('CATEGORY_CONSUMIBLE','4');
	
	define('PRODUCTION_RESULT_CODE_A','1');
	define('PRODUCTION_RESULT_CODE_B','2');
	define('PRODUCTION_RESULT_CODE_C','3');
  
  define('PRODUCTION_RESULT_CODE_MILL','4');
  define('PRODUCTION_RESULT_CODE_WASTE','5');
  
  define('PRODUCT_BAG','50');
  
  define('PRODUCT_POLI_GALON','60');
  define('PRODUCT_POLI_LITRO','66');
	define('PRODUCT_POLI_GALON_5','69');
  
  define('PRODUCT_SERVICE_OTHER','57');
  
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
  define('WAREHOUSE_LOST','3'); 
  define('WAREHOUSE_INJECTION','4'); 
	
	define('CLIENTS_VARIOUS','9');
  
  define('CLIENT_TYPE_CHEMICAL','1');
  define('CLIENT_TYPE_LICOR','2');
  define('CLIENT_TYPE_LAB','3');
  
	define('ACTION_TYPE_CALL','1');
	define('ACTION_TYPE_VISIT','2');
	define('ACTION_TYPE_OTHER','3');
  
  define('PRICE_CLIENT_CATEGORY_GENERAL','1');
  define('PRICE_CLIENT_CATEGORY_TWO','2');
  define('PRICE_CLIENT_CATEGORY_VOLUME','3');
  
  define('PURCHASE_ORDER_STATE_AWAITING_AUTHORIZATION','1');
  define('PURCHASE_ORDER_STATE_AUTHORIZED','2');
  define('PURCHASE_ORDER_STATE_CONFIRMED_WITH_CLIENT','3');
  //define('PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY','4');
  define('PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY','5');
  
	define('ENTRY_ARTICLES_MAX','25');
  define('ENTRY_INVOICES_MAX','10');
  define('RECIPE_INGREDIENTS_MAX','10');
  define('RECIPE_CONSUMABLES_MAX','10');
  define('QUOTATION_ARTICLES_MAX','30');
  define('PRODUCTION_CONSUMABLES_MAX','3');
  
	define('PROVIDER_NATURE_RAW','1');
  define('PROVIDER_NATURE_CONSUMABLE','2');
  define('PROVIDER_NATURE_RAW_CONSUMABLE','3');
  
  define('PLACEHOLDER_ID','-100');
  
  define('UNIT_UNIT','1');
  define('UNIT_GRAM','2');
  
  define('INVOICE_PENDING_PAYMENT_MAX',15);
  
  define('DELIVERY_STATUS_UNASSIGNED',1);
  define('DELIVERY_STATUS_PROGRAMMED',2);
  define('DELIVERY_STATUS_DELIVERED',3);
  
  
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
    //pr($user);
		
		$userRoleId = $userrole = $this->Auth->User('role_id');
		$username = $this->Auth->User('username');
		
    //echo "user role is ".$userrole."<br/>";
		$userhomepage = $this->userhome($userrole);
		$this->set(compact('userrole','username','userhomepage','userRoleId'));
		
    $this->loadModel('Constant');
    $companyNameConstant=$this->Constant->find('first',[
      'conditions'=>[
        'Constant.constant'=>'NOMBRE_COMPANIA'
      ]
    ]);
    if (!defined('COMPANY_NAME')){
      define('COMPANY_NAME',$companyNameConstant['Constant']['value']);
    }
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
      'main-menu' => [
				[
          'title' => __('Entries'),
          'url' => ['controller' => 'orders', 'action' => 'resumenEntradas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'purchases',
        ],
				[
          'title' => __('Production'),
          'url' => ['controller' => 'productionRuns', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_ACCOUNTING],
					'activesetter' => 'production',
        ],
        [
          'title' => __('Inventory'),
          'url' => ['controller' => 'stockItems', 'action' => 'inventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_SALES,ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'inventory',
        ],
				[
          'title' => __('Salidas'),
          'url' => ['controller' => 'orders', 'action' => 'resumenVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES,ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'exits',
        ],
        [
          'title' => __('Deliveries'),
          'url' => ['controller' => 'deliveries', 'action' => 'resumen'],
					'permissions'=>[ROLE_DRIVER],
					'activesetter' => 'exits',
        ],
				[
          'title' => __('Ingresos'),
          'url' => ['controller' => 'cashReceipts', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'finance',
        ],
				[
          'title' => __('Reportes'),
          'url' => ['controller' => 'productionMovements', 'action' => 'verReporteProduccionMeses'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'reports',
        ],
        [
          'title' => __('Reportes'),
          'url' => ['controller' => 'stockItems', 'action' => 'estadoResultados'],
					'permissions'=>[ROLE_ACCOUNTING],
					'activesetter' => 'reports',
        ],
        [
          'title' => __('Reportes'),
          'url' => ['controller' => 'stockMovements', 'action' => 'verReporteVentaProductoPorCliente'],
					'permissions'=>[ROLE_ASSISTANT,ROLE_MANAGER],
					'activesetter' => 'reports',
        ],
        [
          'title' => __('Reportes'),
          'url' => ['controller' => 'stockMovements', 'action' => 'verReporteVentaProductoPorCliente'],
					'permissions'=>[ROLE_SALES],
          'activesetter' => 'reports',
				],	
				[
          'title' => __('Configuration'),
          'url' =>['controller' => 'pages', 'action' => 'display','productionconfig'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'configuration',
        ],
        //CLIENT MENUS
        [
          'title' => __('Ordenes de Venta'),
          'url' => ['controller' => 'salesOrders', 'action' => 'crearOrdenVentaExterna'],
					'permissions'=>[ROLE_CLIENT],
					'activesetter' => 'client',
        ],
        [
          'title' => __('Cuentas x Pagar'),
          'url' => ['controller' => 'invoices', 'action' => 'verCuentasPorPagar'],
        	'permissions'=>[ROLE_CLIENT],
					'activesetter' => 'cuentasporpagar',
        ],
        /*
        [
          'title' => __('Estimaciones de Compras'),
          'url' => ['controller' => 'purchaseEstimations', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'purchaseestimations',
        ],
        [
          'title' => __('Cuentas por pagar'),
          'url' => ['controller' => 'invoices', 'action' => 'verCuentasPorPagar'],
					'permissions'=>[ROLE_ADMIN,ROLE_CLIENT],
					'activesetter' => 'cuentasporpagar',
        ],
        [
          'title' => __('Compras realizadas'),
          'url' => ['controller' => 'orders', 'action' => 'resumenComprasRealizadas'],
					'permissions'=>[ROLE_ADMIN,ROLE_CLIENT],
					'activesetter' => 'comprasrealizadas',
        ],
        [
          'title' => __('Pedidos nuevos'),
          'url' => ['controller' => 'clientRequests', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_CLIENT],
					'activesetter' => 'clientrequests'
        ],
        */
      ],
      'sub-menu-entries' => [
        [
          'title' => __('Purchase Orders'),
          'url' => ['controller' => 'purchaseOrders', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_ACCOUNTING],
					'activesetter' => 'purchaseorders',
        ],
        [
          'title' => __('Entries'),
          'url' => ['controller' => 'orders', 'action' => 'resumenEntradas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'entries',
        ],
        //[
        //  'title' => 'Entradas de Suministros',
        //  'url' => ['controller' => 'orders', 'action' => 'resumenEntradasSuministros'],
				//	'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
				//	'activesetter' => 'entriesConsumibles',
        //],
        [
          'title' => 'Proveedores por Pagar',
          'url' => ['controller' => 'orders', 'action' => 'verProveedoresPorPagar'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'proveedoresPorPagar',
        ],
        [
          'title' => 'Entradas Canceladas',
          'url' => ['controller' => 'orders', 'action' => 'resumenEntradasPagadas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'entradaspagadas',
        ],
      ],
      'sub-menu-production' => [
        [
          'title' => __('Production Runs'),
          'url' => ['controller' => 'productionRuns', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_ACCOUNTING],
					'activesetter' => 'productionruns',
        ],
        [
          'title' => __('Recipes'),
          'url' => ['controller' => 'recipes', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'recipes',
        ],
				[
          'title' => __('Incidences'),
          'url' => ['controller' => 'incidences', 'action' => 'resumenIncidencias'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'incidences',
        ],
        [
          'title' => __('Reporte Incidencias'),
          'url' => ['controller' => 'incidences', 'action' => 'reporteIncidencias'],
					'permissions'=>[ROLE_ADMIN,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'reportincidences',
        ],
      ],
			'sub-menu-inventory' => [
				[
          'title' => __('Inventory'),
          'url' => ['controller' => 'stockItems', 'action' => 'inventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'inventory',
        ],
        [
          'title' =>'Comprobante Ajustes',
          'url' => ['controller' => 'stockItems', 'action' => 'comprobanteAjustesInventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING,ROLE_MANAGER],
					'activesetter' => 'adjustmentsVoucher',
        ],
        [
          'title' => __('Ajustes Inventario'),
          'url' => ['controller' => 'stockMovements', 'action' => 'resumenAjustesInventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'adjustmentsInventory',
        ],
				array(
          'title' => __('Reclassify Inventory'),
          'url' => array('controller' => 'stockItems', 'action' => 'resumenReclasificaciones'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ACCOUNTING),
					'activesetter' => 'reclassification',
        ),
				[
          'title' => __('Transferencias entre Bodegas'),
          'url' => ['controller' => 'stockMovements', 'action' => 'resumenTransferencias'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'transferenciasBodegas',
        ],
		array(
          'title' => __('Transferencia entre Productos'),
          'url' => array('controller' => 'stockItems', 'action' => 'resumenTransferenciasProductos'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'transferenciasProductos',
        ),
				[
          'title' => __('Detalle Costo Producto'),
          'url' => ['controller' => 'stockItems', 'action' => 'detalleCostoProducto'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'detallecostoproducto',
        ],
        [
          'title' => __('cuadrar Estado de Lotes'),
          'url' => ['controller' => 'stockItems', 'action' => 'cuadrarEstadosDeLote'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'cuadrarestadodelotes',
        ],
        [
          'title' => 'Desactivar Lotes',
          'url' => ['controller' => 'stockItems', 'action' => 'desactivarLotes'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'desactivarlotes',
        ],
      ],
      'sub-menu-exits' => [
				[
          'title' => __('Cotizaciones'),
          'url' => ['controller' => 'quotations', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'quotations',
        ],
        [
          'title' => __('Ordenes de Venta'),
          'url' => ['controller' => 'salesOrders', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'salesorders',
        ],
        [
          'title' => __('Ventas y Remisiones'),
          'url' => ['controller' => 'orders', 'action' => 'resumenVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'exits',
        ],
        [
          'title' => __('Deliveries'),
          'url' => ['controller' => 'deliveries', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'deliveries',
        ],
        [
          'title' => 'Facturas por Vendedor',
          'url' => ['controller' => 'orders', 'action' => 'facturasPorVendedor'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
					'activesetter' => 'facturasporvendedor',
        ],
        [
          'title' => __('Estimaciones de Compras'),
          'url' => ['controller' => 'stockMovements', 'action' => 'reporteEstimacionesComprasPorCliente'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING,ROLE_MANAGER],
					'activesetter' => 'reporteestimacionescomprasporcliente',
        ],
				[
          'title' => __('Descuadre Subtotales Suma Productos'),
          'url' => ['controller' => 'orders', 'action' => 'resumenDescuadresSubtotalesSumaProductosVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'descuadresubtotalessumaproductos',
        ],
        [
          'title' => __('Descuadre Redondeo Totales'),
          'url' => ['controller' => 'orders', 'action' => 'resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'descuadreredondeototales',
        ],
      ],
			'sub-menu-finance' => array(
        [
          'title' => __('Recibos de Caja'),
          'url' => ['controller' => 'cashReceipts', 'action' => 'index'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING,ROLE_FACTURACION],
          'activesetter' => 'cashreceipts',
        ],
        [
          'title' => __('Cheques'),
          'url' => ['controller' => 'cheques', 'action' => 'index'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'cheques',
        ],
        [
          'title' => __('Depósitos'),
          'url' => ['controller' => 'transfers', 'action' => 'resumenDepositos'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING,ROLE_FACTURACION],
          'activesetter' => 'deposits',
        ],
        [
          'title' => __('Transferencias'),
          'url' => ['controller' => 'transfers', 'action' => 'index'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'transfers',
        ],
        [
          'title' => __('Tasas de Cambio'),
          'url' => ['controller' => 'exchangeRates', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'exchangerates',
        ],
				[
          'title' => __('Cuentas Contables'),
          'url' => ['controller' => 'accountingCodes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'accountingcodes',
        ],
				[
          'title' => __('Tipos de Comprobante'),
          'url' => ['controller' => 'accountingRegisterTypes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'accountingregistertypes',
        ],
				[
          'title' => __('Comprobantes'),
          'url' => ['controller' => 'accountingRegisters', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'accountingregisters',
        ],
				array(
          'title' => __('Reportes'),
          'url' => array('controller' => 'accountingCodes', 'action' => 'verReporteCaja'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
					'activesetter' => 'financereports',
					'children' => array(                
						array(
							'title' => __('Cobros de la Semana'),
							'url' => array('controller' => 'invoices', 'action' => 'verCobrosSemana'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'cobrossemana',
						),
						array(
							'title' => __('Reporte Caja'),
							'url' => array('controller' => 'accountingCodes', 'action' => 'verReporteCaja'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportingresoscaja',
						),
						array(
							'title' => __('Historial de Pagos'),
							'url' => array('controller' => 'invoices', 'action' => 'verHistorialPagos'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reporthistorialpagos',
						),
						array(
							'title' => __('Clientes Por Cobrar'),
							'url' => array('controller' => 'invoices', 'action' => 'verClientesPorCobrar'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportclientesporcobrar',
						),
						array(
							'title' => __('Estado Resultados Financieros'),
							'url' => array('controller' => 'accountingRegisters', 'action' => 'verEstadoResultados'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportestadoresultados',
						),
						array(
							'title' => __('Balance General'),
							'url' => array('controller' => 'accountingRegisters', 'action' => 'verBalanceGeneral'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportbalancegeneral',
						),
					),
				),
      ),
      'sub-menu-reports' => [
        [
          'title' => __('Estado de Resultados'),
          'url' => array('controller' => 'stockItems', 'action' => 'estadoResultados'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
          'activesetter' => 'reporteestadoresultados',
        ],
        [
          'title' => __('Utilidad Anual'),
          'url' => ['controller' => 'stockItems', 'action' => 'utilidadAnual'],
          'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
          'activesetter' => 'reporteutilidadanual',
        ],
        [
          'title' => __('Reporte Movimientos Productos'),
          'url' => array('controller' => 'stockItems', 'action' => 'verReporteProductos'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
					'activesetter' => 'reporteproductos',
					/*
					'children' => array(                
						array(
							'title' => __('Reporte de Producto Materia Prima'),
							'url' => array('controller' => 'stockItems', 'action' => 'verReporteProducto'),
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
							'url' => array('controller' => 'stockMovements', 'action' => 'verReporteCompraVenta'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER),
							'activesetter' => 'reportpurchasesalecaps',
						),
					),
					*/
        ],
				[
          'title' => __('Producción Detallada'),
          'url' => array('controller' => 'stockItems', 'action' => 'verReporteProduccionDetalle'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
					'activesetter' => 'reporteproducciondetalle',
        ],
				[
          'title' => __('Reporte Salidas'),
          'url' => array('controller' => 'products', 'action' => 'viewSaleReport'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
					'activesetter' => 'reportesalidas',
        ],
				[
          'title' => __('Cierre'),
          'url' => ['controller' => 'orders', 'action' => 'verReporteCierre'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reportecierre',
        ],
				array(
          'title' => __('Reporte Producción Supervisor'),
          'url' => array('controller' => 'production_movements', 'action' => 'verReporteProduccionMeses'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_ACCOUNTING),
					'activesetter' => 'reporteproduccionmeses',
        ),
				[
          'title' => __('Venta Producto por Cliente'),
          'url' => ['controller' => 'stockMovements', 'action' => 'verReporteVentaProductoPorCliente'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reporteventaproductoporcliente',
        ],
        [
          'title' => __('Precios por Factura'),
          'url' => ['controller' => 'productPriceLogs', 'action' => 'reportePreciosPorFactura'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'reportepreciosporfactura',
        ],
      ],
      'sub-menu-configuration' => [
				[
          'title' => __('Tipos de Producto'),
          'url' => ['controller' => 'productTypes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'producttypes',
        ],
        [
          'title' => 'Asociar Tipos de Producto Plantas',
          'url' => ['controller' => 'plantProductTypes', 'action' => 'asociarPlantasTiposDeProducto'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'asociarplantastiposdeproducto',
        ],
				[
          'title' => __('Productos'),
          'url' => ['controller' => 'products', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'products',
        ],
        [
          'title' => 'Volumen Ventas',
          'url' => ['controller' => 'products', 'action' => 'volumenesVentas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'volumenesventas',
        ],
        [
          'title' => 'Naturaleza Producto',
          'url' => ['controller' => 'productNatures', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'productnatures',
        ],
        [
          'title' => __('Registrar Precios'),
          'url' => ['controller' => 'productPriceLogs', 'action' => 'resumenPrecios'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES,ROLE_ACCOUNTING],
					'activesetter' => 'resumenprecios',
        ],
        [
          'title' => __('Lista Precios'),
          'url' => ['controller' => 'productPriceLogs', 'action' => 'listaPrecios'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'listaprecios',
        ],
        [
          'title' => 'Categorías de Precios de Clientes',
          'url' => ['controller' => 'priceClientCategories', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'priceclientcategories',
        ],
        [
          'title' => 'Asociar Categorías Precios Clientes',
          'url' => ['controller' => 'priceClientCategories', 'action' => 'asociarClientesCategoriasDePrecio'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES,ROLE_ACCOUNTING],
					'activesetter' => 'asociarclientescategoriasdeprecio',
        ],
				[
          'title' => __('Proveedores'),
          'url' => ['controller' => 'thirdParties', 'action' => 'resumenProveedores'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'providers',
        ],
        [
          'title' => 'Asociar Proveedores Plantas',
          'url' => ['controller' => 'plantThirdParties', 'action' => 'asociarPlantasProveedores'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'asociarplantasproveedores',
        ],
				[
          'title' => __('Client Types'),
          'url' => ['controller' => 'clientTypes', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'clienttypes',
        ],
        [
          'title' => __('Clientes'),
          'url' => ['controller' => 'thirdParties', 'action' => 'resumenClientes'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING,ROLE_MANAGER],
					'activesetter' => 'clients',
        ],
        [
          'title' => 'Asociar Clientes Plantas',
          'url' => ['controller' => 'plantThirdParties', 'action' => 'asociarPlantasClientes'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'asociarplantasclientes',
        ],
        [
          'title' => __('Asociar Clientes y Usuarios'),
          'url' => ['controller' => 'thirdPartyUsers', 'action' => 'asociarClientesUsuarios'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING,ROLE_MANAGER],
					'activesetter' => 'asociarclientesusuarios',
        ],
				[
          'title' => __('Reasignar Clientes'),
          'url' => ['controller' => 'thirdParties', 'action' => 'reasignarClientes'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'reasignarclientes',
        ],
				[
          'title' => __('Máquinas'),
          'url' => ['controller' => 'machines', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'machines',
        ],
				[
          'title' => __('Operadores'),
          'url' => ['controller' => 'operators', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'operators',
        ],
				[
          'title' => __('Turnos'),
          'url' => ['controller' => 'shifts', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'shifts',
        ],
        [
          'title' => __('Tipos de Producción'),
          'url' => ['controller' => 'productionTypes', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'productiontypes',
        ],
				
        [
          'title' => __('Vehicles'),
          'url' => ['controller' => 'vehicles', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'vehicles',
        ],
        
        [
          'title' => __('Plantas'),
          'url' => ['controller' => 'plants', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'plants',
        ],
        [
          'title' => __('Asociar Usuarios con Plantas'),
          'url' => ['controller' => 'userPlants', 'action' => 'asociarUsuariosPlantas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'asociarusuariosplantas',
        ],
        
        [
          'title' => __('Bodegas'),
          'url' => ['controller' => 'warehouses', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'warehouses',
        ],
        [
          'title' => __('Asociar Usuarios con Bodegas'),
          'url' => ['controller' => 'userWarehouses', 'action' => 'asociarUsuariosBodegas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'asociarusuariosbodegas',
        ],
        
        [
          'title' => __('Usuarios'),
          'url' => ['controller' => 'users', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'users',
        ],
        [
          'title' => __('Logs de Usuario'),
          'url' => ['controller' => 'userLogs', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'userlogs',
        ],
				[
          'title' => __('Papeles'),
          'url' => ['controller' => 'roles', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'roles',
        ],
				[
          'title' => __('Permisos de Ventas'),
          'url' => ['controller' => 'users', 'action' => 'rolePermissions'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'rolepermissions',
        ],
        [
          'title' => __('Permisos de Producción'),
          'url' => ['controller' => 'users', 'action' => 'roleProductionPermissions'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'roleproductionpermissions',
        ],
        [
          'title' => __('Permisos de Finanzas'),
          'url' => ['controller' => 'users', 'action' => 'roleFinancePermissions'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'rolefinancepermissions',
        ],
        [
          'title' => __('Permisos de Config'),
          'url' => ['controller' => 'users', 'action' => 'roleConfigPermissions'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'roleconfigpermissions',
        ],
        [
          'title' => __('Derechos Individuales'),
          'url' => ['controller' => 'pageRights', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'pagerights',
        ],
        [
          'title' => __('Asignar Derechos'),
          'url' => ['controller' => 'userPageRights', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'userpagerights',
        ],
				[
          'title' => __('Empleados'),
          'url' => ['controller' => 'employees', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'employees',
        ],
				[
          'title' => __('Días de Vacaciones'),
          'url' => ['controller' => 'employeeHolidays', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'employeeholidays',
        ],
				[
          'title' => __('Motivos de Vacaciones'),
          'url' => ['controller' => 'holidayTypes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'holidaytypes',
        ],
        [
          'title' => __('Constants'),
          'url' => ['controller' => 'constants', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'constants',
        ],
        [
          'title' => __('Units'),
          'url' => ['controller' => 'units', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'units',
        ],
        [
          'title' => __('Zones'),
          'url' => ['controller' => 'zones', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'zones',
        ],
        [
          'title' => __('Payment Modes'),
          'url' => ['controller' => 'paymentModes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ACCOUNTING],
					'activesetter' => 'paymentmodes',
        ],
			],
      /*
      'sub-menu-clients' => [
        [
         'title' => __('Crear Orden de Venta'),
          'url' => ['controller' => 'salesOrders', 'action' => 'crearOrdenVentaExterna'],
					'permissions'=>[ROLE_CLIENT],
					'activesetter' => 'salesorders',
        ],
        [
          'title' => __('Cuentas x Pagar'),
          'url' => ['controller' => 'invoices', 'action' => 'verCuentasPorPagar'],
        	'permissions'=>[ROLE_CLIENT],
					'activesetter' => 'vercuentasporpagar',
        ],
      ],
      */    
    );
		$currentController= $this->params['controller'];
		$currentAction= $this->params['action'];
		$currentParameter=0;
    $this->set(compact('currentController','currentAction','currentParameter'));
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
		if (($currentAction=="index"||$currentAction=="view"||$currentAction=="add"||$currentAction=="edit") &&($currentController!="orders"&&$currentController!="thirdParties")){
			switch($currentController){
				case "purchaseOrders": 
					$activeMenu="purchases";
					$activeSub="purchaseorders";
					$sub="sub-menu-entries";
					break;
        case "productionRuns": 
					$activeMenu="production";
					$activeSub="productionruns";
					$sub="sub-menu-production";
					break;
				case "closingDates": 
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
				case "cashReceipts": 
					$activeMenu="finance";
					$activeSub="cashreceipts";
					$sub="sub-menu-finance";
					break;
				case "exchangeRates": 
					$activeMenu="finance";
					$activeSub="exchangerates";
					$sub="sub-menu-finance";
					break;
				case "accountingCodes": 
					$activeMenu="finance";
					$activeSub="accountingcodes";
					$sub="sub-menu-finance";
					break;
				case "accountingRegisterTypes": 
					$activeMenu="finance";
					$activeSub="accountingregistertypes";
					$sub="sub-menu-finance";
					break;
				case "accountingRegisters": 
					$activeMenu="finance";
					$activeSub="accountingregisters";
					$sub="sub-menu-finance";
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
				case "productTypes": 
					$activeMenu="configuration";
					$activeSub="producttypes";
					$sub="sub-menu-configuration";
					break;
				case "products": 
					$activeMenu="configuration";
					$activeSub="products";
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
				case "employeeHolidays": 
					$activeMenu="configuration";
					$activeSub="employeeholidays";
					$sub="sub-menu-configuration";
					break;	
				case "holidayTypes": 
					$activeMenu="configuration";
					$activeSub="holidaytypes";
					$sub="sub-menu-configuration";
					break;	
        case "constants": 
					$activeMenu="configuration";
					$activeSub="constants";
					$sub="sub-menu-configuration";
					break;	  
        case "paymentModes": 
					$activeMenu="configuration";
					$activeSub="paymentmodes";
					$sub="sub-menu-configuration";
					break;  
        case "purchaseEstimations": 
					$activeMenu="purchaseestimations";
					//$activeSub="holidaytypes";
					//$sub="sub-menu-configuration";
					break;	
				case "client_requests": 
					$activeMenu="clientrequests";
					//$activeSub="holidaytypes";
					//$sub="sub-menu-configuration";
					break;	
			}
		}
    else if ($currentAction=="resumen" || $currentAction=="crear" || $currentAction=="editar" || $currentAction=="ver" || $currentAction=="detalle") {
      switch ($currentController){
        case "clientTypes":
          $activeMenu="configuration";
          $activeSub="clienttypes";
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
				case "pageRights":
          $activeMenu="configuration";
          $activeSub="pagerights";
          $sub="sub-menu-configuration";
		      break;
        case "productionRuns": 
					$activeMenu="production";
					$activeSub="productionruns";
					$sub="sub-menu-production";
					break;  
        case "recipes":
          $activeMenu="production";
          $activeSub="recipes";
          $sub="sub-menu-production";
		      break;
        case "priceClientCategories":
          $activeMenu="configuration";
          $activeSub="priceclientcategories";
          $sub="sub-menu-configuration";
		      break;
        case "productionTypes":
          $activeMenu="configuration";
					$activeSub="productiontypes";
					$sub="sub-menu-configuration";
          break;
        case "productNatures":
          $activeMenu="configuration";
					$activeSub="productnatures";
					$sub="sub-menu-configuration";
          break;          
        case "purchaseOrders":
          $activeMenu="purchases";
					$activeSub="purchaseorders";
					$sub="sub-menu-entries";
          break;
        case "quotations":
          $activeMenu="exits";
					$activeSub="quotations";
					$sub="sub-menu-exits";
          break;
        case "salesOrders":
          $activeMenu="exits";
					$activeSub="salesorders";
					$sub="sub-menu-exits";
          break;
        case "users": 
					$activeMenu="configuration";
					$activeSub="users";
					$sub="sub-menu-configuration";
					break;
        case "userPageRights":
          $activeMenu="configuration";
          $activeSub="userpagerights";
          $sub="sub-menu-configuration";
		      break;
        case "userLogs": 
					$activeMenu="configuration";
					$activeSub="userlogs";
					$sub="sub-menu-configuration";
					break;  
        case "units": 
					$activeMenu="configuration";
					$activeSub="units";
					$sub="sub-menu-configuration";
					break;    
        case "vehicles":
          $activeMenu="configuration";
          $activeSub="vehicles";
          $sub="sub-menu-configuration";
		      break;   
        case "zones": 
					$activeMenu="configuration";
					$activeSub="zones";
					$sub="sub-menu-configuration";
					break;  
        case "plants": 
					$activeMenu="configuration";
					$activeSub="plants";
					$sub="sub-menu-configuration";
					break;    
        case "deliveries": 
					$activeMenu="exits";
					$activeSub="deliveries";
					$sub="sub-menu-exits";
					break;     
      }
    }
    else if ($userRoleId != ROLE_CLIENT && ($currentAction == "crearOrdenVentaExterna" || $currentAction == "editarOrdenVentaExterna") ) {
      switch ($currentController){
        case "salesOrders":
          $activeMenu="exits";
					$activeSub="salesorders";
					$sub="sub-menu-exits";
          break;
      }
    }
    else if (($currentAction=="resumenEntradas"||$currentAction=="crearEntrada"||$currentAction=="editarEntrada"||$currentAction=="verEntrada") && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="entries";
			$sub="sub-menu-entries";
		}
    //else if (($currentAction=="resumenEntradasSuministros"||$currentAction=="crearEntradaSuministros"||$currentAction=="editarEntradaSuministros"||$currentAction=="verEntradaSuministros") && $currentController=="orders"){
		//	$activeMenu="purchases";
		//	$activeSub="entriesConsumibles";
		//	$sub="sub-menu-entries";
		//}
    else if (($currentAction=="verProveedoresPorPagar"||$currentAction=="verFacturasPorPagar") && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="proveedoresPorPagar";
			$sub="sub-menu-entries";
		}
    else if ($currentAction=="resumenEntradasPagadas" && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="entradaspagadas";
			$sub="sub-menu-entries";
		}
    else if ($currentAction=="verPagoEntradas" && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="verpagoentradas";
			$sub="sub-menu-entries";
		}
    else if ($currentAction=="resumenComprasRealizadas"&&$currentController=="orders"){
			$activeMenu="comprasrealizadas";
			//$activeSub="comprasrealizadas";
			//$sub="sub-menu-production";
		}
    else if ($currentAction=="resumenIncidencias"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="incidences";
			$sub="sub-menu-production";
		}
    else if ($currentAction=="verIncidencia"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="incidences";
			$sub="sub-menu-production";
		}
    else if ($currentAction=="crearIncidencia"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="incidences";
			$sub="sub-menu-production";
		}
    else if ($currentAction=="editarIncidencia"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="incidences";
			$sub="sub-menu-production";
		}
    else if ($currentAction=="reporteIncidencias"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="reportincidences";
			$sub="sub-menu-production";
		}
		else if ($currentAction=="inventario"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="inventory";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="ajustesInventario" && $currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="adjustmentsInventory";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="comprobanteAjustesInventario" && $currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="adjustmentsVoucher";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="resumenAjustesInventario" && $currentController=="stockMovements"){
			$activeMenu="inventory";
			$activeSub="adjustmentsInventory";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="resumenReclasificaciones"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="reclassification";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="resumenTransferenciasProductos"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="transferenciasProductos";
			$sub="sub-menu-inventory";
		}
		else if (in_array($currentAction,["resumenTransferencias","detalleTransferencia","transferirLote"]) && $currentController=="stockMovements"){
			$activeMenu="inventory";
			$activeSub="transferenciasBodegas";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="detalleCostoProducto"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="detallecostoproducto";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="cuadrarEstadosDeLote"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="cuadrarestadodelotes";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="desactivarLotes" && $currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="desactivarlotes";
			$sub="sub-menu-inventory";
		} 
    else if (($currentAction=="resumenVentasRemisiones"||$currentAction=="crearVenta"||$currentAction=="editarVenta"||$currentAction=="verVenta"||$currentAction=="crearRemision"||$currentAction=="editarRemision"||$currentAction=="verRemision") && $currentController=="orders"){
          $activeMenu="exits";
					$activeSub="exits";
					$sub="sub-menu-exits";
    }
    else if ($currentAction == "facturasPorVendedor" && $currentController=="orders"){
          $activeMenu="exits";
					$activeSub="facturasporvendedor";
					$sub="sub-menu-exits";
    }
    else if ($currentAction=="reporteEstimacionesComprasPorCliente" && $currentController=="stockMovements"){
			$activeMenu="exits";
			$activeSub="reporteestimacionescomprasporcliente";
			$sub="sub-menu-exits";
		}
    else if ($currentAction=="resumenDescuadresSubtotalesSumaProductosVentasRemisiones"&&$currentController=="orders"){
			$activeMenu="exits";
			$activeSub="descuadresubtotalessumaproductos";
			$sub="sub-menu-exits";
		}
    else if ($currentAction=="resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones"&&$currentController=="orders"){
			$activeMenu="exits";
			$activeSub="descuadreredondeototales";
			$sub="sub-menu-exits";
		}
		else if ($currentAction=="estadoResultados"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteestadoresultados";
			$sub="sub-menu-reports";
		}
    else if ($currentAction=="utilidadAnual"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteutilidadanual";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProductos"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="products"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteCompraVenta"&&$currentController=="stockMovements"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProduccionDetalle"&&$currentController=="stockItems"){
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
		else if ($currentAction=="verReporteProduccionMeses"&&$currentController=="productionMovements"){
			$activeMenu="reports";
			$activeSub="reporteproduccionmeses";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="reportePreciosPorFactura"&&$currentController=="productPriceLogs"){
			$activeMenu="reports";
			$activeSub="reportepreciosporfactura";
			$sub="sub-menu-reports";
		}
    else if ($currentAction=="verReporteVentaProductoPorCliente"&&$currentController=="stockMovements"){
			$activeMenu="reports";
			$activeSub="reporteventaproductoporcliente";
			$sub="sub-menu-reports";
		}
    else if (($currentAction=="resumenDepositos"||$currentAction=="crearDeposito"||$currentAction=="editarDeposito"||$currentAction=="verDeposito") && $currentController=="transfers"){
			$activeMenu="finance";
			$activeSub="deposits";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verCobrosSemana" && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="cobrossemana";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verReporteCaja" && $currentController=="accountingCodes"){
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
			$activeSecond="cuentasporpagar";
			$sub="sub-menu-finance";
		}
    else if ($userRoleId == ROLE_CLIENT && $currentAction=="crearOrdenVentaExterna" && $currentController=="salesOrder"){
			$activeMenu="salesorders";
      //$activeSub="salesorders";
      //$sub="sub-menu-clients";
		}
    else if ($currentAction=="verCuentasPorPagar" && $currentController=="invoices"){
			$activeMenu="cuentasporpagar";
			//$activeSub="cuentasporpagar";
			//$activeSecond="cuentasporpagar";
			//$sub="sub-menu-finance";
		}
		else if ($currentAction=="verEstadoResultados" && $currentController=="accountingRegisters"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportestadoresultados";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verBalanceGeneral" && $currentController=="accountingRegisters"){
			$activeMenu="finance";
			$activeSub="financereports";
			$activeSecond="reportbalancegeneral";
			$sub="sub-menu-finance";
		}
    else if ($currentAction == "asociarPlantasTiposDeProducto"  && $currentController=="plantProductTypes"){
			$activeMenu="configuration";
			$activeSub="asociarplantastiposdeproducto";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "volumenesVentas" && $currentController=="products"){
			$activeMenu="configuration";
			$activeSub="volumenesventas";
			$sub="sub-menu-configuration";
		}
    else if (($currentAction == "resumenPrecios" || $currentAction =="registrarPreciosCliente" || $currentAction == "registrarPreciosProducto") && $currentController=="productPriceLogs"){
			$activeMenu="configuration";
			$activeSub="resumenprecios";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "listaPrecios" && $currentController=="productPriceLogs"){
			$activeMenu="configuration";
			$activeSub="listaprecios";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "asociarClientesCategoriasDePrecio" && $currentController=="priceClientCategories"){
			$activeMenu="configuration";
			$activeSub="asociarclientescategoriasdeprecio";
			$sub="sub-menu-configuration";
		}
    else if (($currentAction=="resumenProveedores"||$currentAction=="crearProveedor"||$currentAction=="editarProveedor"||$currentAction=="verProveedor") && $currentController=="thirdParties"){
			$activeMenu="configuration";
			$activeSub="providers";
			//$activeSecond="reportbalancegeneral";
			$sub="sub-menu-configuration";
		}
		else if (($currentAction=="resumenClientes" || $currentAction=="crearCliente" || $currentAction=="registrarCliente" || $currentAction=="convertirCliente" ||$currentAction=="editarCliente"||$currentAction=="verCliente") && $currentController=="thirdParties"){
			$activeMenu="configuration";
			$activeSub="clients";
			//$activeSecond="reportbalancegeneral";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "asociarPlantasProveedores"  && $currentController=="plantThirdParties"){
			$activeMenu="configuration";
			$activeSub="asociarplantasproveedores";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "asociarPlantasClientes"  && $currentController=="plantThirdParties"){
			$activeMenu="configuration";
			$activeSub="asociarplantasclientes";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction=="asociarClientesUsuarios" && $currentController=="thirdParties"){		
			$activeMenu="configuration";
			$activeSub="asociarclientesusuarios";
			$sub="sub-menu-configuration";
		}
		else if ($currentAction=="reasignarClientes" && $currentController=="thirdParties"){		
			$activeMenu="configuration";
			$activeSub="reasignarclientes";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction=="asociarUsuariosPlantas" && $currentController=="userPlants"){		
			$activeMenu="configuration";
			$activeSub="asociarusuariosplantas";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction=="asociarUsuariosBodegas" && $currentController=="userWarehouses"){		
			$activeMenu="configuration";
			$activeSub="asociarusuariosbodegas";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "rolePermissions" && $currentController == "users"){		
			$activeMenu="configuration";
			$activeSub="rolepermissions";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "roleProductionPermissions" && $currentController == "users"){		
			$activeMenu="configuration";
			$activeSub="roleproductionpermissions";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "roleFinancePermissions" && $currentController == "users"){		
			$activeMenu="configuration";
			$activeSub="rolefinancepermissions";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction == "roleConfigPermissions" && $currentController == "users"){		
			$activeMenu="configuration";
			$activeSub="roleconfigpermissions";
			$sub="sub-menu-configuration";
		}
	
		
		$active=[];
		$active['activeMenu']=$activeMenu;
		$active['activeSub']=$activeSub;
		$active['activeSecond']=$activeSecond;
		//pr($sub);
		//pr($active);
    // For default settings name must be menu
    $this->set(compact('menu','active','sub'));
		
		$modificationInfo=NA;
		$addActions=["add","crear","crearCliente","crearProveedor","crearEntrada","crearVenta","crearRemision" ,"crearDeposito" ,"crearOrdenVentaExterna"];
    $editActions=["edit","editar","editarCliente","editarProveedor","editarEntrada","editarVenta","editarRemision" ,"editarDeposito" ,"editarOrdenVentaExterna"];
    $viewActions=["view","ver","detalle","verCliente","verProveedor","verEntrada","verVenta","verRemision","verDeposito"];
		if(in_array($currentAction,$editActions) || in_array($currentAction,$viewActions)){
			$this->loadModel('UserAction');
			$userActions=$this->UserAction->find('all',[
				'fields'=>[
					'UserAction.action_name','UserAction.action_datetime',
					'UserAction.user_id',
				],
				'conditions'=>[
					'UserAction.controller_name'=>$currentController,
					'UserAction.item_id'=>$currentParameter,
				],
        'contain'=>[
          'User'=>[
            'fields'=>['first_name','last_name','username'],
          ],
        ],
				'order'=>'action_datetime DESC',
			]);
			//pr($userActions);
			if (!empty($userActions)){
				$lastAction="";
        $actionName=$userActions[0]['UserAction']['action_name'];
        $userName=empty($userActions[0]['User']['first_name'])?$userActions[0]['User']['username']:($userActions[0]['User']['first_name']." ".$userActions[0]['User']['last_name']);
        
        if (in_array($actionName,$addActions)){
					$lastAction="Grabado por ";
				}
				elseif (in_array($actionName,$editActions)){
          $lastAction="Modificado por ";
				}
				
        $lastAction.=$userName." ";
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
                $userName=empty($userActions[$i]['User']['first_name'])?$userActions[$i]['User']['username']:($userActions[$i]['User']['first_name']." ".$userActions[$i]['User']['last_name']);
        
                //pr($userActions[$i]);
								if (in_array($userActions[$i]['UserAction']['action_name'],$addActions)){
									$actionInfo="Grabado por ";
								}
								elseif (in_array($userActions[$i]['UserAction']['action_name'],$editActions)){
									$actionInfo="Modificado por ";
								}
								$actionInfo.=$userName." ";
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
		
    $loggedUserId=$userid=$this->Auth->User('id');
		if (!(($currentController=='pages')&&($currentAction=='display'|| $currentAction == 'productionconfig'  || $currentAction == 'alertaAsociacionAusente'))){
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/add";		
			$userId=$this->Auth->User('id');
			//pr($userid);
			if (!empty($userid)){
				$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_add_permission='0';
			}
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/crear";		
			$userid=$this->Auth->User('id');
			//pr($userid);
			if (!empty($userid)){
				$bool_crear_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_crear_permission='0';
			}
			$bool_add_permission=$bool_add_permission || $bool_crear_permission;
      $this->set(compact('bool_add_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/edit";		
			//pr($userid);
			if (!empty($userid)){
				$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_edit_permission='0';
			}
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/editar";		
			//pr($userid);
			if (!empty($userid)){
				$bool_editar_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_editar_permission='0';
			}
			$bool_edit_permission=$bool_edit_permission || $bool_editar_permission;
      $this->set(compact('bool_edit_permission'));
			
      
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/delete";		
			if (!empty($userid)){
				$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_delete_permission='0';
			}
			//echo "bool delete permission is ".$bool_delete_permission."<br/>";
			$this->set(compact('bool_delete_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/annul";		
			if (!empty($userid)){
				$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_annul_permission='0';
			}
			//echo "bool annul permission is ".$bool_annul_permission."<br/>";
			$this->set(compact('bool_annul_permission'));
		}
		
		$exchangeRateUpdateNeeded='0';
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
  
    //pr($user);
    
    if ($loggedUserId > 0){
      $this->loadModel('UserPlant');
      $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
      //pr($plants);
      
      if (count($plants) == 1){
        $plantId=array_keys($plants)[0];
      }
      if (count($plants) == 0 
        && (($currentController == 'orders' && ($currentAction =='resumenVentasRemisiones' || $currentAction =='crearVenta')) || $currentController == 'salesOrders' || $currentController == 'stockItems')
      ){  
        return $this->redirect(['controller'=>'pages','action' => 'display','alertaAsociacionAusente']);
      }
      
      $this->loadModel('UserWarehouse');
      $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
      //pr($warehouses);
      
      if (count($warehouses) == 1){
        $warehouseId=array_keys($warehouses)[0];
      }
      //pr($warehouses);
      /*
      foreach ($warehouses as $warehouseId=>$warehouseName){
        $priceUpdateNeeded[$enterpriseId]='0';
        
        $latestFuelProductPriceLog=$this->ProductPriceLog->getLatestFuelProductPriceLog($enterpriseId);
        //pr($latestFuelProductPriceLog);
        $fuelPriceExpiration=$latestFuelProductPriceLog['ProductPriceLog']['duration'];
        //pr($fuelPriceExpiration);
        if ($fuelPriceExpiration>6){
          $priceUpdateNeeded[$enterpriseId]=true;
        }
        
        $inventoryMeasurementCorrectionNeeded[$enterpriseId]='0';
        $inventoryMeasurementStatus=$this->TankMeasurement->getCurrentInventoryTankMeasurementStatus($enterpriseId);
        //pr($inventoryMeasurementStatus);
        if ($inventoryMeasurementStatus['measurements_present'] && !$inventoryMeasurementStatus['adjustments_present'] && $inventoryMeasurementStatus['week_day']>0){
          $inventoryMeasurementCorrectionNeeded[$enterpriseId]=true;
        }
      }
      */
      
      if (count($warehouses) == 0 
        //&& !($currentController=='pages' && $currentAction == 'display' && $currentParameter == 'alertaAsociacionAusente') 
        //&& !($currentController == 'users' && $currentAction == 'logout')){
        && (($currentController == 'orders' && ($currentAction =='resumenVentasRemisiones' || $currentAction =='crearVenta')) || $currentController == 'salesOrders' || $currentController == 'stockItems')
      ){  
        return $this->redirect(['controller'=>'pages','action' => 'display','alertaAsociacionAusente']);
      }
    }
    //pr($warehouses);
    $this->set(compact('plants'));
    $this->set(compact('plantId'));
    $this->set(compact('warehouses'));
    $this->set(compact('warehouseId'));
      
  }
	
	public function hasPermission($user_id,$aco_name){
		$this->loadModel('User');
		
    $user=$this->User->find('first',[
      'conditions'=>['User.id'=>$user_id],
      'recursive'=>-1,
    ]);
		//pr($user);
		//pr($aco_name);
		
    if (empty($user)){
      return false;
    }  
		return $this->Acl->check(['Role'=>['id'=>$user['User']['role_id']]],$aco_name);
	}

	public function userhome($userrole){
    //pr($userrole);
		switch ($userrole){
			case ROLE_ADMIN:
			case ROLE_MANAGER:
      case ROLE_ACCOUNTING:
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
				//echo "redirecting to productionRuns!<br/>";
				return array(
					'controller' => 'productionRuns',
					'action' => 'index'
				);
        break;
      case ROLE_DRIVER:
        //echo "redirecting to deliveries!<br/>";
				return [
					'controller' => 'deliveries',
					'action' => 'resumen'
				];
        break;  
      case ROLE_SALES:
				//echo "redirecting to productionRuns!<br/>";
				return array(
					'controller' => 'stock_items',
					'action' => 'inventario'
				);
        break;
      case ROLE_CLIENT:
				return [
					'controller' => 'salesOrders',
					'action' => 'crearOrdenVentaExterna'
				];
        break;  
      case ROLE_FACTURACION:
				//echo "redirecting to productionRuns!<br/>";
				return array(
					'controller' => 'orders',
					'action' => 'resumenVentasRemisiones'
				);
        break;
			default:
        //echo "userrole is".$userrole."<br>";
				//echo "redirecting to loginpage!<br/>";
				return [
				  'controller' => 'users',
				  'action' => 'login'
				];
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
    $adjustmentCreationMovement=[];
		$movements=array();
		$exitedrawmovements=array();
		$stockMovementUsed='0';
		$productionMovementUsed='0';
		$productionMovementAndRawExitUsed='0';
		
		$categoryid=$stockItem['Product']['ProductType']['product_category_id'];
		switch ($categoryid){
			case CATEGORY_PRODUCED:
				$creationmovement=$this->ProductionMovement->find('first',array(
					'conditions'=>array(
						'ProductionMovement.stockitem_id'=>$id,
						'bool_input'=>'0',
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
        if (empty($transfercreationmovement)){
					$adjustmentCreationMovement=$this->StockMovement->find('first',[
						'conditions'=>[
							'StockMovement.stockitem_id'=>$id,
							'bool_input'=>true,
							'bool_adjustment'=>true,
						],
						'contain'=>[
							'StockItem',
						],
					]);
				}
				$movements=$this->StockMovement->find('all',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>'0',
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
						'bool_input'=>'0',
					),
					'contain'=>array(
						'StockItem',
					),
					'order'=>'movement_date, StockMovement.id',
				));
				$stockMovementUsed=true;
				break;
      case CATEGORY_RAW:
      case CATEGORY_CONSUMIBLE:
      default:
				$creationmovement=$this->StockMovement->find('first',array(
					'conditions'=>array(
						'StockMovement.stockitem_id'=>$id,
						'bool_input'=>true,
					),
					'contain'=>array(
						'StockItem',
					),
				));
        // when raw or consumible are produced during injection production
        if (empty($creationmovement)){
          $creationmovement=$this->ProductionMovement->find('first',[
            'conditions'=>[
              'ProductionMovement.stockitem_id'=>$id,
              'bool_input'=>true,
            ],
            'contain'=>[
              'StockItem',
            ],
          ]);
        }
				
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
						'bool_input'=>'0',
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
		}
		
		//pr($creationmovement);
		//pr($movements);
		
		$StockItemLogData=[];
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try {
			switch ($categoryid){
        case CATEGORY_RAW:
				case CATEGORY_CONSUMIBLE:
        case CATEGORY_OTHER:
          if (array_key_exists('StockMovement',$creationmovement)){
            $StockItemLogData=[
              'stockitem_id'=>$id,
              'stock_movement_id'=>$creationmovement['StockMovement']['id'],
              'stockitem_date'=>$creationmovement['StockMovement']['movement_date'],
              'product_id'=>$creationmovement['StockMovement']['product_id'],
              'product_quantity'=>$creationmovement['StockMovement']['product_quantity'],
              'product_unit_price'=>$creationmovement['StockMovement']['product_unit_price'],
              'warehouse_id'=>$creationmovement['StockItem']['warehouse_id'],
            ];
					}
          else {
            $StockItemLogData=[
              'stockitem_id'=>$id,
              'stock_movement_id'=>$creationmovement['ProductionMovement']['id'],
              'stockitem_date'=>$creationmovement['ProductionMovement']['movement_date'],
              'product_id'=>$creationmovement['ProductionMovement']['product_id'],
              'product_quantity'=>$creationmovement['ProductionMovement']['product_quantity'],
              'product_unit_price'=>$creationmovement['ProductionMovement']['product_unit_price'],
              'warehouse_id'=>$creationmovement['StockItem']['warehouse_id'],
            ];
            
          }
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
						elseif (!empty($transfercreationmovement)){					
              $stockItemLogData=[
                'stockitem_id'=>$id,
                'production_movement_id'=>$transfercreationmovement['StockMovement']['id'],
                'stockitem_date'=>$transfercreationmovement['StockMovement']['movement_date'],
                'product_id'=>$transfercreationmovement['StockMovement']['product_id'],
                'product_quantity'=>$transfercreationmovement['StockMovement']['product_quantity'],
                'product_unit_price'=>$transfercreationmovement['StockMovement']['product_unit_price'],
                'production_result_code_id'=>$transfercreationmovement['StockMovement']['production_result_code_id'],
                'warehouse_id'=>$transfercreationmovement['StockItem']['warehouse_id'],
              ];
              $this->StockItemLog->create();
              if (!$this->StockItemLog->save($stockItemLogData)) {
                pr($stockItemLogData);
                echo "problema guardando los estado de lote";
                pr($this->validateErrors($this->StockItemLog));
                throw new Exception();
              }
						}
            elseif (!empty($adjustmentCreationMovement)){					
              $stockItemLogData=[
                'stockitem_id'=>$id,
                'production_movement_id'=>$adjustmentCreationMovement['StockMovement']['id'],
                'stockitem_date'=>$adjustmentCreationMovement['StockMovement']['movement_date'],
                'product_id'=>$adjustmentCreationMovement['StockMovement']['product_id'],
                'product_quantity'=>$adjustmentCreationMovement['StockMovement']['product_quantity'],
                'product_unit_price'=>$adjustmentCreationMovement['StockMovement']['product_unit_price'],
                'production_result_code_id'=>$adjustmentCreationMovement['StockMovement']['production_result_code_id'],
                'warehouse_id'=>$adjustmentCreationMovement['StockItem']['warehouse_id'],
              ];
              $this->StockItemLog->create();
              if (!$this->StockItemLog->save($stockItemLogData)) {
                pr($stockItemLogData);
                echo "problema guardando los estado de lote";
                pr($this->validateErrors($this->StockItemLog));
                throw new Exception();
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
                case CATEGORY_CONSUMIBLE:
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
            case CATEGORY_CONSUMIBLE:
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
            case CATEGORY_CONSUMIBLE:
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
              case CATEGORY_CONSUMIBLE:
							case CATEGORY_OTHER:
								$StockItemLogData['product_unit_price']=(array_key_exists('StockMovement',$creationmovement)?$creationmovement['StockMovement']['product_unit_price']:$creationmovement['ProductionMovement']['product_unit_price']);
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
              case CATEGORY_CONSUMIBLE:
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
	
	public function saveAccountingRegisterData($accountingRegisterDataArray,$bool_new){
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingCode');
		$this->loadModel('Currency');
    
		$datasource=$this->AccountingRegister->getDataSource();
		$datasource->begin();
		try {
			//pr($accountingRegisterDataArray);
			if ($bool_new){
				$this->AccountingRegister->create();
			}
			if (!$this->AccountingRegister->save($accountingRegisterDataArray)) {
				pr($this->validateErrors($this->AccountingRegister));
				echo "Error al guardar el asiento contable";
				throw new Exception();
			}
			
			$accountingRegisterId=$this->AccountingRegister->id;
      
      //echo "accountingRegisterId is ".$accountingRegisterId."<br/>";
      
			$accounting_register_accounting_register_type_id=$accountingRegisterDataArray['AccountingRegister']['accounting_register_type_id'];
			$accounting_register_register_code=$accountingRegisterDataArray['AccountingRegister']['register_code'];
			$accounting_register_concept=$accountingRegisterDataArray['AccountingRegister']['concept'];
			$accounting_register_date=$accountingRegisterDataArray['AccountingRegister']['register_date'];
			$accounting_register_currency_id=$accountingRegisterDataArray['AccountingRegister']['currency_id'];
			//$linkedCurrency=$this->Currency->read(null,$accounting_register_currency_id);
			//$currency_abbreviation=$linkedCurrency['Currency']['abbreviation'];
			$currency_abbreviation="C$";
			foreach ($accountingRegisterDataArray['AccountingMovement'] as $accountingMovement){
				//pr($accountingMovement);
				$accounting_movement_amount=0;
				$bool_debit=true;
				
				if (!empty($accountingMovement['debit_amount'])){
					$accounting_movement_amount = round($accountingMovement['debit_amount'],2);
					$bool_debit=true;
				}
				else if (!empty($accountingMovement['credit_amount'])){
					$accounting_movement_amount = round($accountingMovement['credit_amount'],2);
					$bool_debit='0';
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
					$accountingMovementItemData['accounting_register_id']=$accountingRegisterId;
					$accountingMovementItemData['accounting_code_id']=$accounting_movement_code_id;
					$accountingMovementItemData['concept']=$accounting_movement_concept;
					
					
					$accountingMovementItemData['amount']=$accounting_movement_amount;
					$accountingMovementItemData['currency_id']=$accounting_register_currency_id;
					
					$accountingMovementItemData['bool_debit']=$bool_debit;
					//echo "saving item data";
					//pr($accountingMovementItemData);
					$this->AccountingRegister->AccountingMovement->create();
					if (!$this->AccountingRegister->AccountingMovement->save($accountingMovementItemData)) {
						pr($this->validateErrors($this->AccountingMovement));
						echo "problema al guardar el movimiento contable";
						throw new Exception();
					}
					//echo "saved the movement";
					// SAVE THE USERLOG FOR ACCOUNTING MOVEMENT
					$this->recordUserActivity($this->Session->read('User.username'),$logmessage);
				}
			}			
			$datasource->commit();
			$this->Session->setFlash(__('Se guardó el comprobante.'),'default',['class' => 'success']);
			return $accountingRegisterId;
			
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('No se podía guardar el comprobante. Por favor intente de nuevo.'),'default',['class' => 'error-message']);
      pr($e);
			return false;
		}
	}
	
}