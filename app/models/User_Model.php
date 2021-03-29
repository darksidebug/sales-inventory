<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class User_Model extends CI_Model {

        public function insert($table, $data){
            $result = $this->db->insert($table, $data);
            if($result){
                return ['insert' => TRUE, 'id' => $this->db->insert_id()];
            }
            else{
                return ['insert' => FALSE, 'id' => ''];
            }
        }

        public function sign_in($table, $username, $pass)
        {
                      $this->db->where('username', $username);
            $result = $this->db->get($table);
            if($result->num_rows() > 0){

                foreach ($result->result() as $row => $value) {
                    
                    if($this->hash_verify($pass, $value->user_pass))
                    {
                        return ['result' => TRUE, 'username' => $value->username];
                    }
                }
            }
            else{
                return ['result' => FALSE, 'user-type' => '', 'user_call_sign' => '', 'chapter' => ''];
            }
        }

        // public function get_prod($table){
        //             // $this->db->where($column, $id);
        //     $sql = "SELECT * FROM $table WHERE remarks != 'Sold' ";
        //     $result = $this->db->query($sql)->result();
        //     return $result;
  
        // }

        public function get($table, $column, $id){
                      $this->db->where($column, $id);
            $result = $this->db->get($table)->result();
            return $result;
            
        }

        public function get_max($table, $column){
            $this->db->select_max($column);
            $result = $this->db->get($table)->result();
            return $result;
            
        }

        public function update_order($table, $data, $column_where){
            $this->db->where('customer_id', $column_where['customer_id']);
            $this->db->where('latest_old_order_remarks', 'Latest');
            $this->db->where('remarks', $column_where['remarks']);
            $this->db->set($data);
            $result = $this->db->update($table);
            if($result){
                return ['update' => TRUE];
            }
            else{
                return ['update' => FALSE];
                return FALSE;
            }
        }

        public function update_payment_history($table, $data, $column_where){
            $this->db->where('id', $column_where['id']);
            $this->db->where('payment_id', $column_where['payment_id']);
            $this->db->where('customer_id', $column_where['customer_id']);
            $this->db->where('date_paid', $column_where['date_paid']);
            $this->db->set($data);
            $result = $this->db->update($table);
            if($result){
                return ['update' => TRUE];
            }
            else{
                return ['update' => FALSE];
                return FALSE;
            }
        }

        public function delete($table, $id){
                    $this->db->where('id', $id);
            $result = $this->db->delete($table);
            if($result){
                return ['delete' => TRUE];
            }
            else{
                return ['delete' => FALSE];
                return FALSE;
            }
        }

        public function update($table, $data, $id){
                      $this->db->where('id', $id);
                      $this->db->set($data);
            $result = $this->db->update($table);
            if($result){
                return ['update' => TRUE];
            }
            else{
                return ['update' => FALSE];
                return FALSE;
            }
        }

        public function get_product_info($table, $id){
            $orders = array();
                      $this->db->where('customer_id', $id);
            $result = $this->db->get($table)->result();

            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
                                $this->db->where('product_id', $value->product_id);
                $value->payments = $this->db->get('payments_table')->result();
            }
            return $result;
        }

        public function get_payment_history($table, $id){
                        $this->db->where('customer_id', $id);
                        $this->db->order_by('id', 'DESC');
            $result = $this->db->get($table)->result();
            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
            }
            return $result;
        }

        public function get_list_all($table){
                      $this->db->order_by('id', 'DESC');
            $result = $this->db->get($table)->result();
            return $result;
        }

        public function get_orders($table, $id){
            $orders = array();
                      $this->db->where('customer_id', $id);
                      $this->db->order_by('id', 'DESC');
            $result = $this->db->get($table)->result();

            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
                                $this->db->where('product_id', $value->product_id);
                $value->payments = $this->db->get('payments_table')->result();
            }
            return $result;
        }

        public function get_orders_by_item($table, $id){
            $orders = array();
                      $this->db->where('id', $id);
            $result = $this->db->get('orders_table')->result();

            foreach ($result as $key => $value) {
                                $this->db->where('id', $value->product_id);
                $value->products = $this->db->get('products_table')->result();
                                $this->db->where('product_id', $value->product_id);
                                $this->db->order_by('id', 'DESC');
                $value->history = $this->db->get('payments_history_table')->result();
            }
            return $result;
        }

        public function get_count($table, $id){
                      $this->db->where('customer_id', $id);
            $result = $this->db->get($table)->num_rows();
            return $result;
        }

        public function get_payment($table, $remarks, $cutomer_id){
                    $this->db->where('remarks', $remarks);
                    $this->db->where('customer_id', $cutomer_id);
            $result = $this->db->get('payments_table')->result();
            return $result;
        }

        public function max($table, $column, $value){
            $sql_max = "SELECT MAX(id) as id FROM $table WHERE $column = '".$value."' ";
            $max = $this->db->query($sql_max)->result();
            return $max;
        }

        public function get_orders_limit($table, $id){
            $sql = "SELECT * FROM $table WHERE `customer_id` = '".$id."' and latest_old_order_remarks = 'Latest' order by id DESC";
            $result = $this->db->query($sql)->result();

            if(!empty($result))
            {
                foreach ($result as $key => $value) {
                                    $this->db->where('id', $value->product_id);
                                    // $this->db->where('latest_old_order_remarks', 'Latest');
                    $value->products = $this->db->get('products_table')->result();

                    $sql1 = "SELECT * FROM payments_table WHERE `customer_id` = '".$id."' and remarks != 'Paid' or remarks != 'Full Payment' ";
                    $payment = $this->db->query($sql1)->result();

                    foreach($payment as $row => $payments)
                    {
                                    $this->db->where('payment_id', $payments->id);
                                    $this->db->order_by('id','DESC');
                        $history = $this->db->get('payments_history_table')->result();
                        
                        foreach ($history as $key => $history_payments) {
                            $sql_max = "SELECT MAX(id) as max_id FROM payments_history_table WHERE `payment_id` = '".$payments->id."' ";
                            $history_payments->max = $this->db->query($sql_max)->result();
                        }
                        
                    }
                }
                return ['orders' => $result, 'payments' => $payment, 'history' => $history];
            }
            else{
                return ['orders' => '', 'payments' => '', 'history' => ''];
            }
        }

        public function get_customer($table, $value){
            $sql = "SELECT * FROM $table WHERE `name` LIKE '%$value%' OR `nick_name` LIKE '%$value%' order by id DESC ";
            $query = $this->db->query($sql);
            return $query->result();
        }

        public function get_product($table, $value){
            $sql = "SELECT * FROM $table WHERE `item_box_num` LIKE '%$value%' OR `item_desc` LIKE '%$value%' order by id DESC ";
            $query = $this->db->query($sql);
            return $query->result();
        }

        public function get_result($table, $value, $id){
            $sql = "SELECT * FROM $table WHERE `item_box_num` LIKE '".$value."%' order by id DESC ";
            $result = $this->db->query($sql)->result();

            foreach ($result as $key => $value) {
                $sql = "SELECT * FROM products_table WHERE `item_box_num` = '".$value->item_box_num."' ";
                $value->products = $this->db->query($sql)->result();
                                   $this->db->where('product_id', $value->id);
                                   $this->db->where('customer_id', $id);
                $value->payments = $this->db->get('payments_table')->result();
            }
            return $result;
        }

        // public function user_auth($user_id){
        //     $this->db->where('uid', $user_id);
        //     $result = $this->db->get('users');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function lookup_user($id){
        //     $this->db->where('ID_num', $id);
        //     $result = $this->db->get('register_borrower');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function sign_in($email, $pass){
        //     $this->db->where('uid', $email);
        //     $this->db->where('email_pass', $pass);

        //     $result = $this->db->get('users');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function get__item_borrower($transact_type, $action){
        //     $this->db->select('*');
        //     $this->db->from('register_borrower');
        //     $this->db->group_by('borrow.ID_num','borrow.transact_type', 'borrow.auth_by_uid');
        //     $this->db->join('borrow', 'borrow.ID_num = register_borrower.ID_num');
        //     $this->db->where('transact_type', $transact_type);
        //     $this->db->where('action_taken', $action);
        //     $query = $this->db->get();
        //     if(!empty($query)){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function get__borrowed_items($transact_type, $action){
        //     $this->db->select('*');
        //     $this->db->from('register_borrower');
        //     $this->db->group_by('borrow.ID_num','borrow.transact_type', 'borrow.auth_by_uid');
        //     $this->db->join('borrow', 'borrow.ID_num = register_borrower.ID_num');
        //     $this->db->where('transact_type', $transact_type);
        //     $this->db->where('action_taken', $action);
        //     $query = $this->db->get();
        //     if(!empty($query)){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function get__details($id){
        //     $this->db->where('ID_num', $id);
        //     $query = $this->db->get('register_borrower');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function query_authorize($user_id, $pass){
        //     $this->db->where('uid', $user_id);
        //     $this->db->where('email_pass', $pass);

        //     $result = $this->db->get('users');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function insert_borrowed_items($data){
        //     $this->db->insert('borrow', $data);
        //     return $this->db->insert_id();
        // }

        // public function return_borrow($id_num, $code, $data){
        //     $this->db->where('ID_num', $id_num);
        //     $this->db->where('code', $code);
        //     $this->db->set($data);
        //     $query = $this->db->update('borrow'); 
        //     if($query){
        //         return true;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function log_out($school_id, $data){
        //     $this->db->where('school_id', $school_id);
        //     $this->db->set($data);
        //     $query = $this->db->update("faculty_log"); 
        //     if($query){
        //         return true;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function get_log(){
        //     $query = $this->db->get('faculty_log');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        private function hash_verify($password, $hashed_password){
            return password_verify($password, $hashed_password);
        }

        // public function get__borrower_details($id){
        //     $this->db->where('ID_num', $id);
        //     $query = $this->db->get('register_borrower');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        public function get__borrower_details($id, $action_taken){
            $this->db->select('*');
            $this->db->from('register_borrower');
            $this->db->join('borrow', 'borrow.ID_num = register_borrower.ID_num');
            $this->db->where('register_borrower.ID_num', $id); 
            $this->db->where('borrow.action_taken', $action_taken);
            $query_result = $this->db->get();
            return $query_result->result();
        }

        // public function lookup($business_name, $business_owner){
        //     $this->db->where('business_owners_name', $business_owner);
        //     $this->db->where('business_name', $business_name);

        //     $result = $this->db->get('business_owners_table');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function registered_business($business_name){
        //     $this->db->where('business_name', $business_name);

        //     $result = $this->db->get('business_registration_table');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function register_business($data){
        //     $this->db->insert('business_registration_table', $data);
        //     return $this->db->insert_id();
        // }

        // public function business_table(){
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
            
        // }

        // public function clients_table(){
        //     $query = $this->db->get('business_owners_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
            
        // }

        // public function lookup_sys_user($email_username){
        //     $this->db->where('email_username', $email_username);

        //     $result = $this->db->get('system_user_table');
        //     if($result->num_rows() == 1){
        //         return $result->row(0)->id;
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function register_sys_user($data){
        //     $this->db->insert('system_user_table', $data);
        //     return $this->db->insert_id();
        // }

        // public function getSystem_user_table(){
        //     $query = $this->db->get('system_user_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function lookup_client_reg($name){
        //     $this->db->where('business_owners_name', $name);
        //     $query = $this->db->get('business_owners_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function lookup_client_acc($email){
        //     $this->db->where('email', $email);
        //     $query = $this->db->get('clients_account_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function register_client_acc($data){
        //     $this->db->insert('clients_account_table', $data);
        //     return $this->db->insert_id();
        // }

        // public function booked($data){
        //     $this->db->insert('bookings', $data);
        //     return $this->db->insert_id();
        // }

        // public function getClient_acc_table(){
        //     $query = $this->db->get('clients_account_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getUser_acc_table(){
        //     $query = $this->db->get('users_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getBusinessList(){
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function get_files()
        // {
        //     $query = $this->db->get("business_registration_table");
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getBusinessListByArea($area){
        //     $this->db->like('business_add', $area);
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getSortedBusinessListByArea($sort){
        //     $this->db->order_by('business_name', $sort);
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getFilteredBusinessList($service){
        //     $this->db->where('category', $service);
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

        // public function getResultCount(){
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->num_rows();
        //     }
        //     else{
        //         return 0;
        //     }
        // }

        // public function getResultCountBySearch($area){
        //     $this->db->like('business_add', $area);
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->num_rows();
        //     }
        //     else{
        //         return 0;
        //     }
        // }

        // public function getResultCountByService($service){
        //     $this->db->like('category', $service);
        //     $query = $this->db->get('business_registration_table');
        //     if($query->num_rows() > 0){
        //         return $query->num_rows();
        //     }
        //     else{
        //         return 0;
        //     }
        // }

        // public function getBookedTables(){
        //     $query = $this->db->get('bookings');
        //     if($query->num_rows() > 0){
        //         return $query->result();
        //     }
        //     else{
        //         return false;
        //     }
        // }

    }