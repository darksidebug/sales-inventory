<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('User_Model', 'user_model');
	}

	public function index()
	{
		redirect('page/security/sign-in.html');
	}

	public function payment($page, $id, $name, $order_id){
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}	

		$this->load->view('templates/head.html');
		$this->load->view('templates/navigation.html');
		$result['orders'] = $this->user_model->get_orders_by_item('orders_table', $order_id);
		$exp_name = explode('%20', $name);
		$result['name'] = $name = $exp_name[0].' '.$exp_name[1].' '.$exp_name[2];
		$result['id'] = $id;
		$this->load->view('pages/'.$page, $result);
		$this->load->view('templates/footer.html');
	}

	public function load($page, $id, $name, $param){
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}

		$this->load->view('templates/head.html');
		$this->load->view('templates/navigation.html');
		$result['orders'] = $this->user_model->get_payment_history('payments_history_table', $id);
		$exp_name = explode('%20', $name);
		$result['name'] = $name = $exp_name[0].' '.$exp_name[1].' '.$exp_name[2];
		$result['id'] = $id;
		$this->load->view('pages/'.$page, $result);
		$this->load->view('templates/footer.html');
	}

	public function view($page)
	{
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}

		$this->load->view('templates/head.html');
		$this->load->view('templates/navigation.html');
		if($page == 'purchase.html'){
			$result['products'] = $this->user_model->get('products_table', 'remarks', 'Unsold');
			$this->load->view('pages/'.$page, $result);
		}
		else{
			$this->load->view('pages/'.$page);
		}
		$this->load->view('templates/footer.html');
	}

	public function list($page)
	{
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}

		$this->load->view('templates/head.html');
		$this->load->view('templates/navigation.html');
		if($page == 'product-list.html'){
			$result['products'] = $this->user_model->get_list_all('products_table');
			$this->load->view('pages/'.$page, $result);
		}
		if($page == 'customers-list.html'){
			$result['customers'] = $this->user_model->get_list_all('customers_table');
			$this->load->view('pages/'.$page, $result);
		}
		$this->load->view('templates/footer.html');
	}

	public function form($page, $id, $param)
	{
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}

		$this->load->view('templates/head.html');
		$this->load->view('templates/navigation.html');
		if($param == 'add-new-customer' || $param == 'add-new-product'){
			$this->load->view('pages/'.$page);
		}
		if($param == 'update-info'){
			$web_page = explode('.', $page); 
			$web = explode('-', $web_page[0]);
			$db_table = $web[1].'s_table';
			$data['info'] = $this->user_model->get($db_table, 'id', $id);
			$this->load->view('pages/'.$page, $data);
		}
		$this->load->view('templates/footer.html');
	}

	public function security($page)
	{
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		$this->load->view('templates/head.html');
		$this->load->view('pages/'.$page);
		$this->load->view('templates/footer.html');
	}

	public function user($page)
	{
		if (!file_exists(APPPATH.'views/pages/'.$page)) {
			show_404();
		}

		if($this->session->userdata('logged_in') == FALSE || $this->session->userdata('logged_in') == ''){
			redirect('page/index');
		}

		$this->load->view('templates/head.html');
		$this->load->view('templates/navbar.html');
		$this->load->view('pages/'.$page);
		$this->load->view('templates/footer.html');
	}

	// public function view($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$title = $page;
	// 	$this->load->view('templates/head.html');
	// 	$this->load->view('templates/side-nav.html');
	// 	$this->load->view('templates/navbar.html');
	// 	$this->load->view('pages/'.$page);
	// 	$this->load->view('templates/page-footer.html');
    //     $this->load->view('templates/footer.html');
	// }

	// public function view_calendar($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/head.html');
	// 	$this->load->view('templates/side-nav.html');
	// 	$this->load->view('templates/navbar.html');
	// 	$this->load->view('pages/'.$page);
	// 	$this->load->view('templates/page-footer.html');
	// 	$this->load->view('templates/modal.html');
    //     $this->load->view('templates/footer.html');
	// }

	// public function secure($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/page-header.html');
	// 	$this->load->view('templates/page-navbar.html');
	// 	$this->load->view('pages/'.$page);
    //     $this->load->view('templates/footer.html');

	// }

	// public function lock($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/page-header.html');
	// 	$this->load->view('templates/page-navbar.html');
	// 	$this->load->view('pages/'.$page);
    //     $this->load->view('templates/footer.html');

	// }

	// public function user($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/head.html');
	// 	$this->load->view('templates/side-nav.html');
	// 	$this->load->view('templates/user-nav.html');
	// 	$this->load->view('pages/'.$page);
	// 	$this->load->view('templates/page-footer.html');
    //     $this->load->view('templates/footer.html');

	// }

	// public function register($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/page-header.html');
	// 	$this->load->view('templates/page-navbar.html');
	// 	$this->load->view('pages/'.$page);
    //     $this->load->view('templates/footer.html');

	// }

	// public function faculty_locator_chart($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}
		
	// 	$title['title'] = $page;
	// 	$data["data"] = $this->User_Model->get_log();
	// 	$this->load->view('templates/locator-head.html');
	// 	$this->load->view('pages/'.$page, $data);
    //     $this->load->view('templates/footer.html');
	// }

	// public function faculty_registration($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/page-header.html');
	// 	$this->load->view('templates/log-nav.html');
	// 	$this->load->view('pages/'.$page);
    //     $this->load->view('templates/footer.html');

	// }

	// public function log_page($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page)) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/page-header.html');
	// 	$this->load->view('templates/log-nav.html');
	// 	$this->load->view('pages/'.$page);
    //     $this->load->view('templates/footer.html');

	// }

	public function logout()
	{
		redirect('pages/secure/login.html/');
	}

	// public function faculty_locator_chart($page)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$this->load->view('templates/head.html');
	// 	$this->load->view('templates/side-nav.html');
	// 	$this->load->view('templates/navbar.html');
	// 	$this->load->view('pages/'.$page);
	// 	$this->load->view('templates/page-footer.html');
    //     $this->load->view('templates/footer.html');

	// }

	// public function register_auth($page = 'register_auth')
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$this->load->view('template/header_main');
	// 	$this->load->view('template/register_header');
    //     $this->load->view('pages/'.$page);

	// }

	// public function register_borrower($page = 'register_borrower')
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$this->load->view('template/header_main');
	// 	$this->load->view('template/register_header');
    //     $this->load->view('pages/'.$page);

	// }

	// public function main($page, $equip, $docs, $action)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$return['equip'] = $this->User_Model->get__borrowed_items($equip, $action);
	// 	$this->load->view('template/header_main', $return);
	// 	$return_result['docs'] = $this->User_Model->get__item_borrower($docs, $action);
    //     $this->load->view('pages/'.$page, $return_result);

	// }

	// public function borrow($page = 'borrow')
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$this->load->view('template/header_main');
	// 	$this->load->view('pages/modal');
    //     $this->load->view('pages/'.$page);

	// }

	// public function return($page = 'return')
	// {
	// 	$return_result = array();
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$this->load->view('template/header_main');
	// 	$this->load->view('pages/modal');
	// 	$return_result['data'] = $this->Pages->get__borrowed_items();
    //     $this->load->view('pages/'.$page, $return_result);

	// }

	// public function details($page, $id, $action_taken)
	// {
	// 	if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
	// 		show_404();
	// 	}

	// 	$registered_borrower['info'] = $this->Pages->get_borrower($id);
	// 	$this->load->view('template/header_main', $registered_borrower);
	// 	$this->load->view('pages/modal_cancel');
	// 	$this->load->view('pages/modal');
	// 	$borrower['data'] = $this->Pages->get__borrower_details($id, $action_taken);
    //     $this->load->view('pages/'.$page, $borrower);

	// }
	
}