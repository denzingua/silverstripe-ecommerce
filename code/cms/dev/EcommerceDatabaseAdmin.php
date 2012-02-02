<?php

/**
 * One stop shop for massaging e-commerce related data
 * AND running tests.
 *
 * You can customise this menu by using the "decorating" this class
 * and adding the method: "updateEcommerceDevMenu".
 *
 * SECTIONS
 *
 * 1. ecommerce setup (default records)
 * 2. maintance
 * 3. debug
 * 4. data cleanup
 * 5. migration
 * 6. tests
 *
 * @author jeremy, nicolaas
 * @todo: work out a standard "silent" option and a display option the "display" options shows all output when running it from ecommerce/dev/
 *
 */

class EcommerceDatabaseAdmin extends Controller{

	//##############################
	// BASIC FUNCTIONS
	//##############################

	static $url_handlers = array(
		//'' => 'browse',
	);

	/**
	 * standard Silverstripe method - required
	 *
	 */
	function init() {
		parent::init();
		// We allow access to this controller regardless of live-status or ADMIN permission only
		// or if on CLI.
		// Access to this controller is always allowed in "dev-mode", or if the user is ADMIN.
		$isRunningTests = (class_exists('SapphireTest', false) && SapphireTest::is_running_test());
		$canAccess = (
			Director::isDev()
			// We need to ensure that DevelopmentAdminTest can simulate permission failures when running
			// "dev/tests" from CLI.
			|| (Director::is_cli() && !$isRunningTests)
			|| Permission::check("ADMIN")
		);
		if(!$canAccess) {
			return Security::permissionFailure($this,
				"The e-commerce development control panel is secured and you need administrator rights to access it. " .
				"Enter your credentials below and we will send you right along.");
		}
	}

	/**
	 * standard, required method
	 * @return String link for the "Controller"
	 */
	public function Link($action = null) {
		$action = ($action) ? $action : "";
		return Controller::join_links(Director::absoluteBaseURL(), 'dev/ecommerce/'.$action);
	}

	//##############################
	// 1. ECOMMERCE SETUP (DEFAULT RECORDS)
	//##############################

	protected $ecommerceSetup = array(
		"setorderidstartingnumber",
		"createecommercemembergroups",
		"ecommercedefaultrecords",
		"adddefaultecommerceproducts"
	);

	/**
	 * @return DataObjectSet list of data cleanup tasks
	 *
	 */
	function EcommerceSetup() {
		return $this->createMenuDOSFromArray($this->ecommerceSetup, $type = "EcommerceSetup");
	}

	function setorderidstartingnumber($request){
		$buildTask = new SetOrderIDStartingNumber($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	function createecommercemembergroups($request){
		$buildTask = new CreateEcommerceMemberGroups($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	function ecommercedefaultrecords($request){
		$buildTask = new EcommerceDefaultRecords($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	function adddefaultecommerceproducts($request){
		$buildTask = new AddDefaultEcommerceProducts($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	//##############################
	// 2. REGULAR MAINTENANCE
	//##############################

	protected $regularMaintenance = array(
		"clearoldcarts",
		"recalculatethenumberofproductssold",
		"addcustomerstocustomergroups"
	);

	/**
	 * @return DataObjectSet list of data cleanup tasks
	 *
	 */
	function RegularMaintenance() {
		return $this->createMenuDOSFromArray($this->regularMaintenance, $type = "RegularMaintenance");
	}

	/**
	 * executes build task
	 *
	 */
	function clearoldcarts($request) {
		$buildTask = new ClearOldCarts($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	/**
	 * executes build task
	 *
	 */
	function recalculatethenumberofproductssold($request) {
		$buildTask = new RecalculateTheNumberOfProductsSold($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}
	/**
	 * executes build task
	 *
	 */
	function addcustomerstocustomergroups($request) {
		$buildTask = new AddCustomersToCustomerGroups($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	//##############################
	// DEBUG ACTIONS
	//##############################

	protected $debugActions = array(
	);

	/**
	 * @return DataObjectSet list of data debug actions
	 *
	 */
	function DebugActions() {
		return $this->createMenuDOSFromArray($this->debugActions, $type = "debugActions");
	}


	//##############################
	// DATA CLEANUPS
	//##############################

	protected $dataCleanups = array(
		"deleteecommerceproductstask",
		"fixbrokenordersubmissiondata"
	);

	/**
	 * @return DataObjectSet list of data cleanup tasks
	 *
	 */
	function DataCleanups() {
		return $this->createMenuDOSFromArray($this->dataCleanups, $type = "DataCleanups");
	}


	/**
	 * executes build task: DeleteEcommerceProductsTask
	 *
	 */
	function deleteecommerceproductstask($request){
		$buildTask = new DeleteEcommerceProductsTask($request);
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}

	/**
	 * executes build task: FixBrokenOrderSubmissionData
	 *
	 */
	function fixbrokenordersubmissiondata($request) {
		$buildTask = new FixBrokenOrderSubmissionData();
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}


	//##############################
	// MIGRATIONS
	//##############################

	protected $migrations = array(
		"ecommercemigration"
	);

	/**
	 * @return DataObjectSet list of migration tasks
	 *
	 */
	function Migrations() {
		return $this->createMenuDOSFromArray($this->migrations, $type = "Migrations");
	}

	/**
	 * runs all the available migration tasks.
	 * TO DO:
	 * - how long does this take?
	 * - do we need a special sequence
	 */
	function runallmigrations($request){
		foreach($this->migrations as $buildTask) {
			$buildTask->run($request);
		}
		$this->displayCompletionMessage($buildTask);
	}


	/**
	 * executes build task: FixBrokenOrderSubmissionData
	 *
	 */
	function ecommercemigration($request) {
		$buildTask = new EcommerceMigration();
		$buildTask->run($request);
		$this->displayCompletionMessage($buildTask);
	}


	//##############################
	// TESTS
	//##############################

	protected $tests = array(
		'ShoppingCartTest' => 'Shopping Cart'
	);

	function Tests(){
		$dos = new DataObjectSet();
		foreach($this->tests as $class => $name){
			$dos->push(new ArrayData(array(
				'Name' => $name,
				'Class' => $class
			)));
		}
		return $dos;
	}

	function AllTests(){
		return implode(',',array_keys($this->tests));
	}


	//##############################
	// INTERNAL FUNCTIONS
	//##############################

	/**
	 * shows a "Task Completed Message" on the screen.
	 */
	public function displayCompletionMessage($buildTask, $extraMessage = '') {
		DB::alteration_message("
			------------------------------------------------------- <br />
			<strong>".$buildTask->getTitle()."</strong><br />
			".$buildTask->getDescription()." <br />
			TASK COMPLETED.<br />
			------------------------------------------------------- <br />
			$extraMessage
		");
	}

	/**
	 *
	 *@param Array $buildTasks array of build tasks
	 */
	protected function createMenuDOSFromArray($buildTasks, $type = "") {
		$this->extend("updateEcommerceDevMenu".$type, $buildTasks);
		$dos = new DataObjectSet();
		foreach($buildTasks as $buildTask) {
			$obj = new $buildTask();
			$do = new ArrayData(
				array(
					"Link" => $this->Link($buildTask),
					"Title" => $obj->getTitle(),
					"Description" => $obj->getDescription()
				)
			);
			$dos->push($do);
		}
		return $dos;
	}


}

