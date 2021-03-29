<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class User extends CI_Controller {

        public function __construct(){
            parent::__construct();
            $this->load->library('form_validation');
            $this->load->library('session');
            $this->load->library('zip');
            $this->load->library('upload');
            $this->load->model('User_Model', 'user_model');
            $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
            $this->output->set_header("Pragma: no-cache"); 
        }

        public function get_customer_info(){
            $return_result = $this->user_model->get_customer('customers_table', $this->input->post('customer'));
            echo json_encode($return_result);
        }

        public function count($id){
            return $result = $this->user_model->get_count('orders_table', $id);
        }

        public function get_customer_order_all(){
            $customer_result = $this->user_model->get_customer('customers_table', $this->input->post('customer'));
            foreach ($customer_result as $key => $value) {
                $orders_result = $this->user_model->get_orders('orders_table', $value->id);
                $id = $value->id;
            }
            echo json_encode(['customer' => $customer_result, 'orders' => $orders_result, 'count' => $this->count($id)]);
        }

        public function get_payment_history(){//table column where
                    $this->db->where('customer_id', 2);
                    $this->db->order_by('id', 'DESC');
            $result = $this->db->get('payments_history_table')->result();
            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
            }
            echo '<pre>';
            print_r($result);
        }

        public function get_orders_limit(){
            $sql = "SELECT * FROM orders_table WHERE `customer_id` = '3' order by id DESC";
            $result = $this->db->query($sql)->result();

            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
                                $this->db->where('product_id', $value->product_id);
                $value->payments = $this->db->get('payments_table')->result();
                foreach($value->payments as $row => $payments)
                {
                                    $this->db->where('payment_id', $payments->id);
                                    $this->db->order_by('id','DESC');
                    $value->history = $this->db->get('payments_history_table')->result();
                }
            }
            echo '<pre>';
            print_r($result);
        }

        public function get_orders_by_item(){
            $orders = array();
                      $this->db->where('id', 8);
                      $this->db->order_by('id', 'DESC');
            $result = $this->db->get('orders_table')->result();

            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
                //                 $this->db->where('product_id', $value->product_id);
                // $value->payments = $this->db->get('payments_table')->result();
                $this->db->where('product_id', $value->product_id);
                $value->history = $this->db->get('payments_history_table')->result();
            }
            echo '<pre>';
            print_r($result);
        }

        public function get_customer_order(){
            $customer_result = $this->user_model->get_customer('customers_table', $this->input->post('customer'));
            foreach ($customer_result as $key => $value) {
                $orders_result = $this->user_model->get_orders_limit('orders_table', $value->id);
                $id = $value->id;
            }
            if(!empty($orders_result)){
                echo json_encode(['customer' => $customer_result, 'orders' => $orders_result['orders'], 
                            'payments' => $orders_result['payments'], 'history' => $orders_result['history']]);
            }
            else{
                echo json_encode(['customer' => $customer_result, 'orders' => '', 
                            'payments' => '', 'history' => '', 'max_id' => '']);
            }
        }

        public function get_customer_order_limit(){
            $customer_result = $this->user_model->get('customers_table', 'id', $this->input->post('customer_id'));
            $orders_result = $this->user_model->get_orders_limit('orders_table', $this->input->post('customer_id'));
            echo json_encode(['customer' => $customer_result, 'orders' => $orders_result['orders'], 
                            'payments' => $orders_result['payments'], 'history' => $orders_result['history']]);
        }

        public function get_list_order(){
            $result = $this->user_model->get_result('orders_table', $this->input->post('product'), 
                      $this->input->post('customer_id'));
            $customer_result = $this->user_model->get('customers_table', 'id', $this->input->post('customer_id'));
            echo json_encode(['orders' => $result, 'customer' => $customer_result, 'count' => $this->count($this->input->post('customer_id'))]);
        }

        public function get_product_info(){
            
            $return_result = $this->user_model->get_orders('products_table', $this->input->post('product'));
            echo json_encode($return_result);
        }

        public function get_products(){
            
            $return_result = $this->user_model->get_product('products_table', $this->input->post('product'));
            echo json_encode($return_result);
        }

        public function payment_product_info(){
            
            $return_result = $this->user_model->get('products_table', 'id', $this->input->post('id'));
            foreach ($return_result as $key => $value) {
                $result = $this->user_model->get('payments_table', 'product_id', $value->id);
            }
            echo json_encode(['payment' => $result, 'products' => $return_result]);
        }

        public function payment_info(){
            
            $result = $this->user_model->get('payments_history_table', 'id', $this->input->post('id'));
            foreach ($result as $key => $value) {
                $rem = $this->user_model->get('payments_table', 'id', $value->payment_id);
            }
            echo json_encode(['payment' => $result, 'remarks' => $rem]);
        }

        public function return_item(){
            if($this->input->post('bal') >= $this->input->post('price')){
                if($this->input->post('balance') == '0'){
                    $ord = array(
                        'remarks' => 'Paid',
                        'latest_old_order_remarks' => 'Old'
                    );
                    $where = array(
                        'customer_id' => $this->input->post('customer_id'),
                        'remarks' => 'Sold and Delivered'
                    );
                    $return_result = $this->user_model->update_order('orders_table', $ord, $where);

                    $data = array(
                        'remarks' => 'Paid'
                    );
                    $result = $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
                }

                $order_data = array(
                    'remarks' => 'Returned'
                );
                $order = $this->user_model->update('orders_table', $order_data, $this->input->post('order_id'));

                $data = array(
                    'balance' => $this->input->post('balance')
                );
                $result = $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
                if($result['update'] == TRUE){
                    $history_data = array(
                        'balance' => $this->input->post('balance')
                    );
                    $history = $this->user_model->update('payments_history_table', $history_data, $this->input->post('history_id'));
                    if($history['update'] == TRUE){
                        $product_data = array(
                            'remarks' => 'Unsold'
                        );
                        $product = $this->user_model->update('products_table', $product_data, $this->input->post('prod_id'));
                        if($product['update'] == TRUE){

                            // $order = $this->user_model->delete('orders_table', $this->input->post('order_id'));
                            // if($order['delete'] == TRUE){
                                $user_data = array(
                                    'update'    => true,
                                    'message'   => 'Product/Item were returned successfully.'
                                );
                                echo json_encode($user_data);
                            // }
                        }
                    }
                }
            }
            else{
                $data = array(
                    'balance' => $this->input->post('balance')
                );
                $result = $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
                if($result['update'] == TRUE){
                    $history_data = array(
                        'balance' => $this->input->post('balance')
                    );
                    $history = $this->user_model->update('payments_history_table', $history_data, $this->input->post('history_id'));
                    if($history['update'] == TRUE){
                        $product_data = array(
                            'remarks' => 'Unsold'
                        );
                        $product = $this->user_model->update('products_table', $product_data, $this->input->post('prod_id'));
                        if($product['update'] == TRUE){
                            $order_data = array(
                                'remarks' => 'Returned'
                            );
                            $order = $this->user_model->update('orders_table', $order_data, $this->input->post('order_id'));
                            if($order['update'] == TRUE){
                                $user_data = array(
                                    'update'    => true,
                                    'message'   => 'Product/Item were returned successfully.'
                                );
                                echo json_encode($user_data);
                            }
                        }
                    }
                }
                // $result = $this->user_model->delete('payments_table', $this->input->post('payment_id'));{
                //     if($result['delete'] == TRUE){
                //         $history = $this->user_model->delete('payments_history_table', $this->input->post('payment_id'));
                //         if($history['delete'] == TRUE){
                //             $order = $this->user_model->delete('orders_table', $this->input->post('order_id'));
                //             if($order['delete'] == TRUE){
                //                 $user_data = array(
                //                     'update'    => true,
                //                     'message'   => 'Product/Item were returned successfully.'
                //                 );
                //                 echo json_encode($user_data);
                //             }
                //         }
                //     }
                // }
            }
        }

        public function save_payment(){
            

            $data = array(
                'payment_amount' => $this->input->post('amount'), 
                'payment_method' => $this->input->post('method'),
                'balance'        => $this->input->post('balance'),
                'date_paid'      => $this->input->post('date')
            );

            $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
            if($result['update'] == TRUE){

                $data_payment = array(
                    'payment_id'     => $this->input->post('payment_id'), 
                    'customer_id'    => $this->input->post('customer_id'),
                    'amount_paid'    => $this->input->post('amount'),
                    'balance'        => $this->input->post('balance'),
                    'payment_method' => $this->input->post('method'),
                    'date_paid'      => $this->input->post('date'),
                    'remarks'        => $this->input->post('remarks'),
                );
    
                $return_result = $this->user_model->insert('payments_history_table', $data_payment);
                if($return_result['insert'] == TRUE){

                    if($this->input->post('balance') == '0'){
                        $ord = array(
                            'remarks' => 'Paid',
                            'latest_old_order_remarks' => 'Old'
                        );
                        $where = array(
                            'customer_id' => $this->input->post('customer_id'),
                            'remarks' => 'Sold and Delivered',
                            'latest_old_order_remarks' => 'Latest'
                        );
                        $return_result = $this->user_model->update_order('orders_table', $ord, $where);
                    }

                    $user_data = array(
                        'update'    => true,
                        'message'   => 'Product payment successfull!'
                    );
                    echo json_encode($user_data);
                }
                else{
                    $user_data = array(
                        'update'    => false,
                        'message'   => 'There was a problem in saving the payment!'
                    );
                    echo json_encode($user_data);
                }
            }
            else{
                $user_data = array(
                    'update'    => false,
                    'message'   => 'There was a problem in saving the payment!'
                );
                echo json_encode($user_data);
            }
        }

        public function update_payment(){
            $amount = 0;
            // $result = $this->user_model->get('payments_table', 'id', $this->input->post('payment_id'));
            // foreach ($result as $key => $value) {
            //     $amount_paid = $value->payment_amount;
            //     $balance = $value->balance;
            // }
            if($this->input->post('less') == 'true'){
                $amount = ($this->input->post('balance') - ($this->input->post('amount') - $this->input->post('payment_amount')));
            }
            if($this->input->post('greater') == 'true'){
                $amount = ($this->input->post('balance') + ($this->input->post('payment_amount') - $this->input->post('amount')));
            }

            $data_payment = array(
                'amount_paid' => $this->input->post('amount'),
                'balance'     => $amount,
                'payment_method' => $this->input->post('method'),
            );
            $where = array(
                'id'          => $this->input->post('id'),
                'payment_id'  => $this->input->post('payment_id'),
                'customer_id' => $this->input->post('customer_id'),
                'date_paid'   => $this->input->post('date')
            );

            $update = $this->user_model->update_payment_history('payments_history_table', $data_payment, $where);
            if($update['update'] == TRUE){

                $data = array(
                    'payment_amount' => $this->input->post('amount'), 
                    'payment_method' => $this->input->post('method'),
                    'balance'        => $amount,
                    'date_paid'      => $this->input->post('date'),
                    'remarks'        => $this->input->post('remarks'),
                );
    
                $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
                if($result['update'] == TRUE){

                    if($amount == '0'){
                        $ord = array(
                            'remarks' => 'Paid',
                            'latest_old_order_remarks' => 'Old'
                        );
                        $where = array(
                            'customer_id' => $this->input->post('customer_id'),
                            'remarks' => 'Sold and Delivered',
                            'latest_old_order_remarks' => 'Latest'
                        );
                        $return_result = $this->user_model->update_order('orders_table', $ord, $where);
                    }

                    $user_data = array(
                        'update'    => true,
                        'message'   => 'Product payment successfull!',
                        'balance'   => $amount
                    );
                    echo json_encode($user_data);
                }
                else{
                    $user_data = array(
                        'update'    => false,
                        'message'   => 'There was a problem in saving the payment!'
                    );
                    echo json_encode($user_data);
                }
            }
        }

        public function update_item_payment(){
            $amount = 0;
            $result = $this->user_model->get('payments_table', 'id', $this->input->post('payment_id'));
            foreach ($result as $key => $value) {
                $amount_paid = $value->payment_amount;
                $balance = $value->balance;
            }
            if($this->input->post('less') == 'true'){
                $amount = ($balance - ($this->input->post('amount') - $amount_paid));
            }
            if($this->input->post('greater') == 'true'){
                $amount = ($balance + ($amount_paid - $this->input->post('amount')));
            }

            $data = array(
                'payment_amount' => $this->input->post('amount'), 
                'payment_method' => $this->input->post('method'),
                'balance'        => $amount,
                'date_paid'      => $this->input->post('date'),
                'remarks'        => $this->input->post('remarks'),
            );

            $result = $this->user_model->update('payments_table', $data, $this->input->post('payment_id'));
            if($result['update'] == TRUE){

                $data_payment = array(
                    'amount_paid' => $this->input->post('amount'),
                    'balance'     => $amount,
                    'payment_method' => $this->input->post('method'),
                );
                $update = $this->user_model->update('payments_history_table', $data_payment, $this->input->post('id'));
                if($update['update'] == TRUE){
                    $user_data = array(
                        'update'    => true,
                        'message'   => 'Product payment successfull!',
                        'balance'   => $amount
                    );
                    echo json_encode($user_data);
                }
                else{
                    $user_data = array(
                        'update'    => false,
                        'message'   => 'There was a problem in saving the payment!'
                    );
                    echo json_encode($user_data);
                }
            }
        }

        public function save_order(){
            $amount = 0;
            for ($i = 0; $i < count($this->input->post('id')); $i++) { 
                $data = array(
                    'product_id'  => $this->input->post('id')[$i], 
                    'item_box_num'=> $this->input->post('item_box_num')[$i], 
                    'customer_id' => $this->input->post('customer_id'),
                    'amount'      => $this->input->post('price')[$i],
                    'remarks'     => $this->input->post('remarks'),
                    'latest_old_order_remarks' => 'Latest'
                );
                $result = $this->user_model->insert('orders_table', $data);
                $amount += $this->input->post('price')[$i];

                $data_update = array(
                    'remarks' => 'Sold'
                );
                $update = $this->user_model->update('products_table', $data_update, $this->input->post('id')[$i]);
            }
            
            if($result['insert'] == TRUE){
                $id = '';
                $return_result = $this->user_model->get_payment('payments_table', 'Unpaid', $this->input->post('customer_id'));
                if(!empty($return_result)){
                    foreach ($return_result as $key => $value) {
                        if($value->balance >= 0)
                        {
                            $amount += $value->balance;
                        }
                        else{
                            $bal_exp = explode('-', $value->balance);
                            $amount -= $bal_exp[1];
                        }
                        $id = $value->id;
                    }
                    $payment_data = array(
                        'customer_id' => $this->input->post('customer_id'),
                        'balance'     => $amount,
                        'remarks'     => 'Unpaid'
                    );
                    $return_result = $this->user_model->update('payments_table', $payment_data, $id);
                    if($return_result['update'] == TRUE){

                        $payments = $this->user_model->max('payments_history_table', 'payment_id', $id);
                        if(!empty($payments)){
                            foreach ($payments as $key => $payment_value) {
                                $payment_history = array(
                                    'balance' => $amount
                                );
                                $payments_result = $this->user_model->update('payments_history_table', $payment_history, $payment_value->id);
                            }
                            if($payments_result['update'] == TRUE){
                                $user_data = array(
                                    'insert'    => true,
                                    'message'   => 'Product purchase successful!'
                                );
                                echo json_encode($user_data);
                            }
                        }
                        else{
                            $user_data = array(
                                'insert'    => true,
                                'message'   => 'Product purchase successful!'
                            );
                            echo json_encode($user_data);
                        }
                    }
                }
                else{
                    $payment_data = array(
                        'customer_id' => $this->input->post('customer_id'),
                        'balance'     => $amount,
                        'remarks'     => 'Unpaid',
                        // 'latest_old_order_remarks' => 'Latest'
                    );
                    $return_result = $this->user_model->insert('payments_table', $payment_data);
                    if($return_result['insert'] == TRUE){

                        $user_data = array(
                            'insert'    => true,
                            'message'   => 'Product purchase successful!'
                        );
                        echo json_encode($user_data);
                    }
                }
            }
            else{
                $user_data = array(
                    'insert'    => false,
                    'message'   => 'There was a problem in purchasing the product!'
                );
                echo json_encode($user_data);
            }
        }

        public function add_member(){
            $data = array(
                'name'                => $this->input->post('fullname'), 
                'nick_name'           => $this->input->post('nickname'),
                'profession_business' => $this->input->post('prof_business'),
                'color'               => $this->input->post('color'),
                'size'                => $this->input->post('size'),
                'address'             => $this->input->post('address'),
                'contact'             => $this->input->post('contact')
            );
            $result = $this->user_model->insert('customers_table', $data);
            if($result['insert'] == TRUE){
                $user_data = array(
                    'insert'    => true,
                    'message'   => 'Customer information successfully save!'
                );
                echo json_encode($user_data);
            }
            else{
                $user_data = array(
                    'insert'    => false,
                    'message'   => 'There was a problem in saving customers info!'
                );
                echo json_encode($user_data);
            }
        }

        public function update_member($id){
            $data = array(
                'name'                => $this->input->post('fullname'), 
                'nick_name'           => $this->input->post('nickname'),
                'profession_business' => $this->input->post('prof_business'),
                'color'               => $this->input->post('color'),
                'size'                => $this->input->post('size'),
                'address'             => $this->input->post('address'),
                'contact'             => $this->input->post('contact')
            );
            $result = $this->user_model->update('customers_table', $data, $id);
            if($result['update'] == TRUE){
                $user_data = array(
                    'update'    => true,
                    'message'   => 'Customer information updated successfully!'
                );
                echo json_encode($user_data);
            }
            else{
                $user_data = array(
                    'update'    => false,
                    'message'   => 'There was a problem in updating customers info!'
                );
                echo json_encode($user_data);
            }
        }

        public function add_product(){
            if(isset($_FILES['file']['name'])){
                $config['upload_path']          = './uploads';
                $config['allowed_types']        = 'jpeg|jpg|png';
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('file'))
                {
                    $data = array(
                        'item_box_num' => $this->input->post('item_box_num'), 
                        'item_desc'    => $this->input->post('item_desc'),
                        'brand'        => $this->input->post('brand'),
                        'size'         => $this->input->post('size'),
                        'color'        => $this->input->post('color'),
                        'price'        => $this->input->post('price'),
                        'image_name'   => '',
                        'remarks'      => 'Unsold'
                    );
                    $result = $this->user_model->insert('products_table', $data);
                    if($result['insert'] == TRUE){
                        $user_data = array(
                            'insert'    => true,
                            'message'   => 'Item/Product is successfully save!'
                        );
                        echo json_encode($user_data);
                    }
                    else{
                        $user_data = array(
                            'insert'    => false,
                            'message'   => 'There was a problem in saving the item/product!'
                        );
                        echo json_encode($user_data);
                    }
                }
                else
                {
                    $file_name = $this->upload->data('file_name');
                    $data = array(
                        'item_box_num' => $this->input->post('item_box_num'), 
                        'item_desc'    => $this->input->post('item_desc'),
                        'brand'        => $this->input->post('brand'),
                        'size'         => $this->input->post('size'),
                        'color'        => $this->input->post('color'),
                        'price'        => $this->input->post('price'),
                        'image_name'   => $file_name,
                        'remarks'      => 'Unsold'
                    );
                    $result = $this->user_model->insert('products_table', $data);
                    if($result['insert'] == TRUE){
                        $user_data = array(
                            'insert'    => true,
                            'message'   => 'Item/Product is successfully save!'
                        );
                        echo json_encode($user_data);
                    }
                    else{
                        $user_data = array(
                            'insert'    => false,
                            'message'   => 'There was a problem in saving the item/product!'
                        );
                        echo json_encode($user_data);
                    }
                }
            }
        }

        public function update_product($id){
            if(isset($_FILES['file']['name'])){
                $config['upload_path']          = './uploads';
                $config['allowed_types']        = 'jpeg|jpg|png';
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('file'))
                {
                    $data = array(
                        'item_box_num' => $this->input->post('item_box_num'), 
                        'item_desc'    => $this->input->post('item_desc'),
                        'brand'        => $this->input->post('brand'),
                        'size'         => $this->input->post('size'),
                        'color'        => $this->input->post('color'),
                        'price'        => $this->input->post('price'),
                        'image_name'   => '',
                        'remarks'      => 'Unsold'
                    );
                    $result = $this->user_model->update('products_table', $data, $id);
                    if($result['update'] == TRUE){
                        $user_data = array(
                            'update'    => true,
                            'message'   => 'Item/Product updated successfully!'
                        );
                        echo json_encode($user_data);
                    }
                    else{
                        $user_data = array(
                            'update'    => false,
                            'message'   => 'There was a problem in updating the item/product!'
                        );
                        echo json_encode($user_data);
                    }
                }
                else
                {
                    $file_name = $this->upload->data('file_name');
                    $data = array(
                        'item_box_num' => $this->input->post('item_box_num'), 
                        'item_desc'    => $this->input->post('item_desc'),
                        'brand'        => $this->input->post('brand'),
                        'size'         => $this->input->post('size'),
                        'color'        => $this->input->post('color'),
                        'price'        => $this->input->post('price'),
                        'image_name'   => $file_name,
                        'remarks'      => 'Unsold'
                    );
                    $result = $this->user_model->update('products_table', $data, $id);
                    if($result['update'] == TRUE){
                        $user_data = array(
                            'update'    => true,
                            'message'   => 'Item/Product updated successfully!'
                        );
                        echo json_encode($user_data);
                    }
                    else{
                        $user_data = array(
                            'update'    => false,
                            'message'   => 'There was a problem in updating the item/product!'
                        );
                        echo json_encode($user_data);
                    }
                }
            }
        }

        // public function add_image(){
        //     // var_dump(realpath('./uploads'));
            
        //             $image_data = array(
        //                 'image_name' => $this->input->post('image_file')
        //             );
        //             $result = $this->user_model->update('products_table', $image_data, $id);
                    
        //             if(){
        //                 $user_data = array(
        //                     'insert'    => true,
        //                     'message'   => 'Item/Product image uploaded successfully!'
        //                 );
        //                 echo json_encode($user_data);
        //             }
        //             else{
        //                 $new_data = explode('<p>', $data);
        //                 $clean_data = explode('</p>', $new_data[1]);
        //                 $user_data = array(
        //                     'insert' => false,
        //                     'message' => $clean_data
        //                 );
        //                 echo json_encode($user_data);
        //             }
        //         }
        //     }
        // }

        // public function insert_into_table($table, $data){
        //     return $result = $this->user_model->insert($table, $data);
        // }
        public function update_account(){
            
            if(isset($_POST['user'])){
                $data = array(
                    'user_pass' => $this->hash_password($this->input->post('pass'))
                );
                $return_result = $this->user_model->update('user_account', $data, $this->input->post('user'));
                if($return_result['update'] == TRUE){
                    $session = array(
                        'logged_in'      => $return_result['update'],
                        'username'      => $this->input->post('user'),
                    );
                    $this->session->set_userdata($session);
                    $user_data = array(
                        'update'          => true,
                        'username'      => $this->input->post('user'),
                        'message'        => 'Please remember your password the next time you logged in.'
                    );
                    echo json_encode($user_data);
                }
                else{
                    $user_data = array(
                        'update'      => false,
                        'user_type'  => '',
                        'message'    => "Username or password is invalid!",
                    );
                    echo json_encode($user_data);
                }
            }
        }

        public function sign_in(){

            //default acc
            //username : system_admin
            //pass: sales-inventory-admin
            if(isset($_POST['user'])){

                $return_result = $this->user_model->sign_in('user_account', $this->input->post('user'), 
                                 $this->input->post('pass'));
                if($return_result['result'] == TRUE){
                    $session = array(
                        'logged_in'      => $return_result['result'],
                        'username'      => $return_result['username'],
                    );
                    $this->session->set_userdata($session);
                    $user_data = array(
                        'login'          => true,
                        'username'      => $return_result['username'],
                        'message'        => 'You are now logged in successfully.'
                    );
                    echo json_encode($user_data);
                }
                else{
                    $user_data = array(
                        'login'      => false,
                        'user_type'  => '',
                        'message'    => "Username or password is invalid!",
                    );
                    echo json_encode($user_data);
                }
            }
        }

        public function logout(){
            $this->session->unset_userdata(['logged_in', 'user_type', 'user_call_sign']);
            redirect('page/security/sign-in.html');
        }

        private function hash_password($password){
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        }



        // public function faculty_registration()
        // {
        //     $data = array(
        //         'school_id'    => $this->input->post('school_id'),
        //         'name'         => $this->input->post('name'),
        //         'gender'       => $this->input->post('gender'),
        //         'position'     => $this->input->post('position')
        //     );
        //     $returnedLast_inserted_id = $this->User_Model->insert('faculty_registration', $data);
        //     redirect('pages/faculty_registration/register-faculty.html/');
        // }

        // public function register(){
            
        //     if($_POST['register'] == 'register'){
                
        //         $id               = $this->input->post('id');
        //         $return_result_id = $this->User_Model->lookup_user($id);
        //         if($return_result_id == false){
        //             $data = array(
        //                 'ID_num'       => $this->input->post('id'),
        //                 'name'         => $this->input->post('name'),
        //                 'dept'         => $this->input->post('dept'),
        //                 'contact'      => $this->input->post('contact')
        //             );
        //             $returnedLast_inserted_id = $this->User_Model->insert('register_borrower', $data);
        //             if($returnedLast_inserted_id){
        //                 $user_data = array(
        //                     'last_inserted_id'   => $return_result_id,
        //                     'allowed'            => 'yes',
        //                     'msg'                => 'Borrower successfully registered.',
        //                     'registered'         => true
        //                 );
        //                 $this->session->set_userdata($user_data);
        //             }
        //             else{
        //                 $user_data = array(
        //                     'last_inserted_id'   => $return_result_id,
        //                     'allowed'            => 'not',
        //                     'msg'                => 'Something went wrong in registration.',
        //                     'registered'         => false
        //                 );
        //                 $this->session->set_userdata($user_data);
        //             } 
        //         }
        //         else{
        //             $user_data = array(
        //                 'last_inserted_id'   => $return_result_id,
        //                 'allowed'            => 'no',
        //                 'msg'                => 'School ID already taken. Please choose another one.',
        //                 'registered'         => false
        //             );
        //         }

        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('pages/register');
        //     }
        // }

        // public function login(){
        //     if($_POST['login'] == 'users'){
        //         $email              = $this->input->post('email');
        //         $password           = $this->input->post('password');
        //         $encrypted_password = md5($password);
        //         $return_result_id = $this->User_Model->sign_in($email, $encrypted_password);
        //         if($return_result_id){
        //             $user_data = array(
		// 				'user_id'   => $return_result_id,
		// 				'username'  => $email,
        //                 'msg'       => 'Welcome "'.$email.'". You are now logged in.',
		// 				'logged_in' => true
		// 			);
        //             $this->session->set_userdata($user_data);
        //         }
        //         else{
        //             $user_data = array(
		// 				'user_id'   => 0,
		// 				'username'  => $email,
        //                 'msg'       => 'User email '.'"'.$email.'"'.' with password '.'"'.$password.'"'.' does not exists!',
		// 				'logged_in' => false
		// 			);
        //             $this->session->set_userdata($user_data);
        //         }

        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('pages/login');
        //     }
        // }

        // public function register_authorize(){
        //     if($_POST['register_userauth'] == 'users_auth'){
        //         $user_id             = $this->input->post('user_id');
        //         $return_result_query = $this->User_Model->user_auth($user_id);
        //         if($return_result_query == false){
        //             $data = array(
        //                 'uid'            => $this->input->post('user_id'),
        //                 'name'           => $this->input->post('name'),
        //                 'email_pass'     => md5($this->input->post('password'))
        //             );
        //             $returnedLast_inserted_id = $this->User_Model->insert_authorize($data);
        //             if($returnedLast_inserted_id){
        //                 $user_data = array(
        //                     'user_id'   => $returnedLast_inserted_id,
        //                     'username'  => $user_id,
        //                     'msg'       => 'User Successfully registered.',
        //                     'registered'=> true,
        //                     'allowed'   => 'yes'
        //                 );
        //                 $this->session->set_userdata($user_data);
        //             }
        //             else{
        //                 $user_data = array(
        //                     'user_id'   => 0,
        //                     'username'  => $user_id,
        //                     'msg'       => 'User cannot be inserted.',
        //                     'registered'=> false,
        //                     'allowed'   => 'no'
        //                 );
        //                 $this->session->set_userdata($user_data);
        //             }
        //         }
        //         else{
        //             $user_data = array(
		// 				'user_id'   => $return_result_query,
		// 				'username'  => $user_id,
        //                 'msg'       => 'User already registered.',
        //                 'registered'=> false,
        //                 'allowed'   => 'not'
        //             );
        //         }
        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('pages/register_auth/');
        //     }
        // }

        // public function get_borrower(){
        //     if($_POST['get__borrower_data'] == 'get__borrower_data'){
        //         $id_num        = $this->input->post('id_num');
        //         $return_result = $this->User_Model->get_borrower($id_num);
        //         if($return_result){
        //             foreach ($return_result as $key) {
        //                 $borrower_data = array(
        //                     'borrower_name'      => $key->name,
        //                     'borrower_dept'      => $key->dept,
        //                     'borrower_cont'      => $key->contact,
        //                     'data_set'           => true
        //                 );
        //             }
        //         }
        //         else{
        //             $borrower_data = array(
        //                 'data_set' => false,
        //                 'msg'      => 'Borrower not registered. Register now?'
        //             );
        //         }
        //         echo json_encode($borrower_data);
        //     }
        // }

        // public function get__equipment_borrower(){
        //     if($_POST['get_data'] == 'get__equipment_borrower'){
        //         $transact_type = $this->input->post('transact_type');
        //         $action        = $this->input->post('action');
        //         $return_result = $this->User_Model->get__item_borrower($transact_type, $action);
        //     }
        //     else{
        //         $return_result = "";
        //     }
        //     echo json_encode($return_result);
        // }

        // public function get__document_borrower(){
        //     if($_POST['get_data'] == 'get__document_borrower'){
        //         $transact_type = $this->input->post('transact_type');
        //         $action        = $this->input->post('action');
        //         $return_result = $this->User_Model->get__item_borrower($transact_type, $action);
        //         echo json_encode($return_result);
        //     }
        // }

        // public function borrow_items(){
        //     if($_POST['borrow'] == 'borrow_items'){
        //         $email              = $this->input->post('uid');
        //         $password           = $this->input->post('password');
        //         $encrypted_password = md5($password);
        //         $return_result_id = $this->User_Model->sign_in($email, $encrypted_password);
        //         if($return_result_id){
        //             $data = array(
        //                 'transact_type'       => $this->input->post('transact_choice'),
        //                 'ID_num'              => $this->input->post('id_num'),
        //                 'date_borrowed'       => $this->input->post('dateborrowed'),
        //                 'date_to_returned'    => $this->input->post('dateToreturn'),
        //                 'code'                => $this->input->post('code'),
        //                 'model_num'           => $this->input->post('model__num'),
        //                 'item_name'           => $this->input->post('doc_name'),
        //                 'description'         => $this->input->post('desc'),
        //                 'quantity'            => $this->input->post('quantity'),
        //                 'auth_by_uid'          => $this->input->post('uid'),
        //                 'auth_by_pass'        => $encrypted_password,
        //                 'action_taken'        => 'not_returned',
        //                 'set_onOff_history'   => 'On'
        //             );
        //             $returnedLast_inserted_id = $this->User_Model->insert('borrow', $data);
        //             if($returnedLast_inserted_id){
        //                 $user_data = array(
        //                     'transact' => $returnedLast_inserted_id ,
        //                     'msg'       => 'Data successfully save.',
        //                     'registered' => true
        //                 );
        //             }
        //             else{
        //                 $user_data = array(
        //                     'msg'       => 'Data cannot be inserted.',
        //                     'registered'=> false
        //                 );
        //             }
        //             $this->session->set_userdata($user_data);
        //         }
        //         else{
        //             $user_data = array(
		// 				'user_id'   => 0,
		// 				'username'  => $email,
        //                 'msg'       => 'User authentication invalid!',
		// 				'registered' => false
		// 			);
        //             $this->session->set_userdata($user_data);
        //         }

        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('pages/login');
        //     }
        // }

        // public function return_items(){
        //     if($_POST['returned'] == 'return_items'){
        //         $email              = $this->input->post('uid');
        //         $password           = $this->input->post('password');
        //         $encrypted_password = md5($password);
        //         $return_result_id = $this->User_Model->sign_in($email, $encrypted_password);
        //         if($return_result_id){
        //             $id_num = $this->input->post('id_num');
        //             $code   = $this->input->post('code');
        //             $data = array(
        //                 'date_returned'      => $this->input->post('dateReturn'),
        //                 'action_taken'       => 'returned',
        //                 'set_onOff_history'  => 'Off'
        //             );
        //             $returned_query = $this->User_Model->return_borrow($id_num, $code, $data);
        //             if($returned_query){
        //                 $user_data = array(
        //                     'transact'   => 'successful' ,
        //                     'msg'        => 'Item/Document returned!',
        //                     'returned'   => true
        //                 );
        //             }
        //             else{
        //                 $user_data = array(
        //                     'transact'   => 'failed' ,
        //                     'msg'        => 'Item/Document returned failed!',
        //                     'returned'   => false
        //                 );
        //             }
        //         }
        //         else{
        //             $user_data = array(
        //                 'transact' => $return_result_id ,
        //                 'msg'       => 'Not allowed.',
        //                 'registered' => false
        //             );
        //         }
        //         echo json_encode($user_data);
        //     }
        // }

        // public function cancel_items(){
        //     if($_POST['cancelled'] == 'cancel'){
        //         $email              = $this->input->post('uid');
        //         $password           = $this->input->post('password');
        //         $encrypted_password = md5($password);
        //         $return_result_id = $this->User_Model->sign_in($email, $encrypted_password);
        //         if($return_result_id){
        //             $id_num = $this->input->post('id_num');
        //             $code   = $this->input->post('code');
        //             $data = array(
        //                 'date_returned'      => $this->input->post('dateReturn'),
        //                 'action_taken'       => 'cancel',
        //                 'set_onOff_history'  => 'Off'
        //             );
        //             $returned_query = $this->User_Model->return_borrow($id_num, $code, $data);
        //             if($returned_query){
        //                 $user_data = array(
        //                     'transact'   => 'successful' ,
        //                     'msg'        => 'Item/Document cancelled!',
        //                     'returned'   => true
        //                 );
        //             }
        //             else{
        //                 $user_data = array(
        //                     'transact'   => 'failed' ,
        //                     'msg'        => 'Item/Document cancel failed!',
        //                     'returned'   => false
        //                 );
        //             }
        //         }
        //         else{
        //             $user_data = array(
        //                 'transact' => $return_result_id ,
        //                 'msg'       => 'Not allowed.',
        //                 'registered' => false
        //             );
        //         }
        //         echo json_encode($user_data);
        //     }
        // }

        // public function get__borrowed_items(){
        //     $return_result = $this->User_Model->get__item_borrower();
        // }

        

        // public function register_client(){

        //     if ($_POST['register_client'] == 'register_client') {
        //         $name = $this->input->post('first_name')." ".$this->input->post('last_name');
        //         $data = array(
        //             'business_owners_name'     => $name,
        //             'business_name'            => $this->input->post('business_name'),
        //             'business_add'             => $this->input->post('business_add'),
        //             'owners_email'             => $this->input->post('owners_email'),
        //             'owners_contact'           => $this->input->post('contact'),
        //             'account_type'             => $this->input->post('account')
        //         );
        //         $returnedLast_inserted_id = $this->User_Model->register_clients($data);  
        //         if($returnedLast_inserted_id > 0){
        //             $user_data = array(
		// 				'last_inserted_id'   => $returnedLast_inserted_id,
        //                 'msg'                => 'Successfully registered.',
		// 				'registered'         => true
		// 			);
        //             $this->session->set_userdata($user_data);
        //         }
        //         else{
        //             $user_data = array(
		// 				'last_inserted_id'   => $returnedLast_inserted_id,
        //                 'msg'                => 'Registration went wrong.',
		// 				'registered'         => false
		// 			);
        //         }

        //         echo json_encode($user_data);          
        //     }
        //     else{
        //         redirect('registration/register');
        //     }

        // }

        // public function update_gallery()
        // {
        //     if($_POST['get_files'] == 'get_files')
        //     {
        //         $result = $this->User_Model->get_files();
        //         if($result){
        //             $output = '';
        //             foreach($result as $element){
        //                 $output .= '<tr>
        //                                 <td>
        //                                     <div id="results__service'. $element['id'] .'" class="results__service">
        //                                         <h4>'. $element['business_name'] .'</h4>
        //                                         <h5><i class="fa fa-phone"></i> '. $element['business_contact'] .'</h5>
        //                                         <h6><i class="fa fa-home"></i> '. $element['business_add'] .'</h6>
        //                                         <img class="img img-responsive" src="'. $element['image_name'] .'">
        //                                         <ul>
                        
        //                                         </ul>
        //                                         <div class="description">'. $element['pricing_description'] .'</div>
        //                                     </div>
        //                                 </td>
        //                             </tr>';
        //             }
        //         }
        //         else{
        //             $output = "empty";
        //         }
        //         echo json_encode($output); 
        //     } 
        // }

        // public function booked(){
        //     if($_POST['bookings'] == 'booked'){
        //         $data = array(
        //             'bookers_name'         => $this->input->post('name'),
        //             'contact'              => $this->input->post('contact'),
        //             'meal_type'            => $this->input->post('mealType'),
        //             'headcounts'           => $this->input->post('headCounts'),
        //             'event_add'            => $this->input->post('addressOfEvent'),
        //             'foods'                => $this->input->post('choiceOfFoods'),
        //             'amount'               => $this->input->post('amountTotal'),
        //             'payment_type'         => $this->input->post('checkbox_val'),
        //             'companyName_booked'   => $this->input->post('business_name'),
        //             'date_of_event'        => $this->input->post('dateOfEvent'),
        //             'no_of_days'           => $this->input->post('noOfDays')
        //         );
        //         $returnedLast_inserted_id = $this->User_Model->booked($data);  
        //         if($returnedLast_inserted_id > 0){
        //             $user_data = array(
        //                 'last_inserted_id'   => $returnedLast_inserted_id,
        //                 'allowed'            => 'yes',
        //                 'msg'                => 'Successfully booked.',
        //                 'booked'         => true
        //             );
        //             $this->session->set_userdata($user_data);
        //             echo("You have successfully booked. Thank you for using our service.");
        //         }
        //         else{
        //             $user_data = array(
        //                 'last_inserted_id'   => $returnedLast_inserted_id,
        //                 'allowed'            => 'not',
        //                 'msg'                => 'Something went wrong in registration.',
        //                 'booked'         => false
        //             );
        //             echo("Some technical errors while booking. Please try again.");
        //         } 
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
            
        // }

        // public function do_upload(){
        //     if(isset($_FILES['userfile'])){
        //         $extension = explode('.', $_FILES['userfile']['name']);
        //         $new_name = rand().'.'.$extension[1];
        //         $destination = './upload/'.$new_name;
        //         move_uploaded_file($_FILES['userfile']['tmp_name'], $destination);
        //         return $new_name;
        //     }
        // }

        // public function register_business(){

        //     $business_name = $this->input->post('business_name');
        //     $business_owner = $this->input->post('business_owner');
        //     $return_result_id = $this->User_Model->lookup($business_name, $business_owner);
        //     if($return_result_id){
        //         if($this->input->post('service') == 'Wedding & Debut' && $this->input->post('service') == 'Catering'){
        //             $data = array(
        //                 'business_name'         => $this->input->post('business_name'),
        //                 'business_add'          => $this->input->post('business_address'),
        //                 'business_owner'        => $this->input->post('business_owner'),
        //                 'business_contact'      => $this->input->post('business_contact'),
        //                 'pricing_description'   => $this->input->post('editor'),
        //                 'category'              => $this->input->post('service'),
        //                 'no_of_persons'         => $this->input->post('no_of_persons'),
        //                 'per_head'              => $this->input->post('per_head'),
        //                 'image_name'            => $this->do_upload()
        //             );
        //         }
        //         else{
        //             $data = array(
        //                 'business_name'         => $this->input->post('business_name'),
        //                 'business_add'          => $this->input->post('business_address'),
        //                 'business_owner'        => $this->input->post('business_owner'),
        //                 'business_contact'      => $this->input->post('business_contact'),
        //                 'pricing_description'   => $this->input->post('editor'),
        //                 'category'              => $this->input->post('service'),
        //                 'image_name'            => $this->do_upload()
        //             );
        //         }
        //         $returnedLast_inserted_id = $this->User_Model->register_business($data);  
        //         if($returnedLast_inserted_id > 0){
        //             $user_data = array(
        //                 'last_inserted_id'   => $return_result_id,
        //                 'allowed'            => 'yes',
        //                 'msg'                => 'Successfully registered.',
        //                 'registered'         => true
        //             );
        //             $this->session->set_userdata($user_data);
        //         }
        //         else{
        //             $user_data = array(
        //                 'last_inserted_id'   => $return_result_id,
        //                 'allowed'            => 'not',
        //                 'msg'                => 'Something went wrong in registration.',
        //                 'registered'         => false
        //             );
        //         } 
        //     }
        //     else{
        //         $user_data = array(
        //             'last_inserted_id'   => $return_result_id,
        //             'allowed'            => 'no',
        //             'msg'                => 'Business name and owner not yet registered',
        //             'registered'         => false
        //         );
        //     }
        //     redirect('registration/register');
        // }

        // public function fetch_business_table(){
        //     $table_result = $this->User_Model->business_table();
        //     echo json_encode($table_result);
        // }

        // public function fetch_clients_table(){
        //     $table_result = $this->User_Model->clients_table();
        //     echo json_encode($table_result);
        // }

        // public function system_user(){
        //     if($_POST['add_accounts'] == 'add_accounts'){

        //         $email_username = $this->input->post('email_username');
        //         $return_result_id = $this->User_Model->lookup_sys_user($email_username);
        //         if($return_result_id == false){
        //             $data = array(
        //                 'name'              => $this->input->post('full_name'),
        //                 'email_username'    => $this->input->post('email_username'),
        //                 'pass'              => md5($this->input->post('pass')),
        //                 'position'          => $this->input->post('position')
        //             );
        //             $returnedLast_inserted_id = $this->User_Model->register_sys_user($data);  
        //             if($returnedLast_inserted_id > 0){
        //                 $user_data = array(
        //                     'last_inserted_id'   => $return_result_id,
        //                     'allowed'            => 'yes',
        //                     'msg'                => 'Successfully registered.',
        //                     'registered'         => true
        //                 );
        //                 $this->session->set_userdata($user_data);
        //             }
        //             else{
        //                 $user_data = array(
        //                     'last_inserted_id'   => $return_result_id,
        //                     'allowed'            => 'not',
        //                     'msg'                => 'Something went wrong in registration.',
        //                     'registered'         => false
        //                 );
        //             } 
        //         }
        //         else{
        //             $user_data = array(
		// 				'last_inserted_id'   => $return_result_id,
        //                 'allowed'            => 'no',
        //                 'msg'                => 'Email or username already taken. Please choose another one.',
		// 				'registered'         => false
		// 			);
        //         }
        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('accounts/registration');
        //     }
        // }

        // public function fetch_systemUser_table(){
        //     $table_result = $this->User_Model->getSystem_user_table();
        //     echo json_encode($table_result);
        // }

        // public function clients_account_table(){
        //     if($_POST['add_client'] == 'add_client'){

        //         $name = $this->input->post('first_name')." ".$this->input->post('middle_name')." ".$this->input->post('last_name');
        //         $return_result_id = $this->User_Model->lookup_client_reg($name);
        //         if($return_result_id){

        //             $email = $this->input->post('client_email');
        //             $return_result_id = $this->User_Model->lookup_client_acc($email);
        //             if($return_result_id){
        //                 $user_data = array(
        //                     'last_inserted_id'   => $return_result_id,
        //                     'allowed'            => 'not',
        //                     'msg'                => 'Email has already taken.',
        //                     'registered'         => false
        //                 );
        //             }
        //             else{
        //                 $data = array(
        //                     'name'              => $name,
        //                     'email'             => $this->input->post('client_email'),
        //                     'pass'              => md5($this->input->post('client_pass')),
        //                 );
        //                 $returnedLast_inserted_id = $this->User_Model->register_client_acc($data);  
        //                 if($returnedLast_inserted_id > 0){
        //                     $user_data = array(
        //                         'last_inserted_id'   => $return_result_id,
        //                         'allowed'            => 'yes',
        //                         'msg'                => 'Successfully registered.',
        //                         'registered'         => true
        //                     );
        //                     $this->session->set_userdata($user_data);
        //                 }
        //                 else{
        //                     $user_data = array(
        //                         'last_inserted_id'   => $return_result_id,
        //                         'allowed'            => 'not',
        //                         'msg'                => 'Something went wrong in registration.',
        //                         'registered'         => false
        //                     );
        //                 } 
        //             }
        //         }
        //         else{
        //             $user_data = array(
		// 				'last_inserted_id'   => $return_result_id,
        //                 'allowed'            => 'notAll',
        //                 'msg'                => 'Client not yet registered.',
		// 				'registered'         => false
		// 			);
        //         }
        //         echo json_encode($user_data);
        //     }
        //     else{
        //         redirect('accounts/registration');
        //     }
        // }

        // public function fetch_clientAcc_table(){
        //     $table_result = $this->User_Model->getClient_acc_table();
        //     echo json_encode($table_result);
        // }

        // public function fetch_userAcc_table(){
        //     $table_result = $this->User_Model->getUser_acc_table();
        //     echo json_encode($table_result);
        // }

        // public function show_business(){
        //     $search_result = $this->User_Model->getBusinessList();
        //     // echo $search_result;
        //     echo json_encode($search_result);
        // }

        // public function searchByArea(){
        //     if($_POST['set_area'] == 'like_area'){
        //         $area = $this->input->post('area');
        //         $search_result = $this->User_Model->getBusinessListByArea($area);
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
            
        // }

        // public function sort_business(){
        //     if($_POST['sort'] == 'sort'){
        //         $sort_val = $this->input->post('sort_val');
        //         $search_result = $this->User_Model->getSortedBusinessListByArea($sort_val);
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
            
        // }

        // public function filterByService(){
        //     if($_POST['filter_service'] == 'filter_service'){
        //         $service = $this->input->post('service');
        //         $search_result = $this->User_Model->getFilteredBusinessList($service);
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
        // }

        // public function getResultCount(){
        //     if($_POST['result_count'] == 'result_count'){
        //         $search_result = $this->User_Model->getResultCount();
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
        // }

        // public function getResultCountBySearch(){
        //     if($_POST['result_count'] == 'result_count'){
        //         $area = $this->input->post('area');
        //         $search_result = $this->User_Model->getResultCountBySearch($area);
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
        // }

        // public function getResultCountByService(){
        //     if($_POST['result_service'] == 'result_service'){
        //         $service = $this->input->post('service');
        //         $search_result = $this->User_Model->getResultCountByService($service);
        //         echo json_encode($search_result);
        //     }
        //     else{
        //         redirect('pages/service');
        //     }
        // }

        // public function logout(){
        //     $this->session->sess_destroy();
        //     redirect('pages/login/');
        // }

        // public function getBookedTables(){
        //     $search_result = $this->User_Model->getBookedTables();
        //     echo json_encode($search_result);
        // }

        // public function borrow(){

        //     if($_POST['name'] != ""){
        //         $name = $this->input->post('name');
        //         $user_data = array(
        //             'data'               => $name,
        //             'msg'                => 'We receive the data.',
        //             'save'               => true
        //         );
        //     }
        //     echo json_encode($user_data);
        // }

    }