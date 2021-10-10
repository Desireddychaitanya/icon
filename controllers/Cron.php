<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Cron extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('earning');
		$this->load->model('downline_model');
		$this->load->model('custom_income');
	}

	public function index()
	{
		
		if(config_item('roi_income')=='Yes'){
			$this->status();
			debug_log('Status Updated');
			$this->roi();
			debug_log('ROI Credited');
		}
		
		$this->update_wallet();
		debug_log('Wallet Updated');
		$this->update_leg();
		debug_log('Leg Count Updated');

		if(config_item('target_income')=='Yes'){
			$this->target_reach_income();
			debug_log('Flexi Income Updated');	
		}
		
		if(config_item('level_income')=='Yes'){
			//$this->level_completion_income();
			//debug_log('Level Wise Income Updated');
			//$this->single_leg_income();
			//debug_log('Single Leg Income Updated');	
		}
		
		if(config_item('roi_income')=='Yes'){
			$this->earning->level_completion_roi_income();
			debug_log('Level Wise ROI Income Updated');
		}

		if(config_item('enable_reward')=='Yes'){
			$this->reward();
			debug_log('Rewards Updated');
			$this->rank_update();
			debug_log('Rank Updated');
		}

		if(config_item('enable_royalty')=='Yes'){
			debug_log('Royalty Income Updated');
		}

		$this->update_wallet();
		debug_log('Wallet Updated');

		debug_log('Cron Executed Successfully');
		
	}

	public function calibrate_users()
	{
		$users = $this->db->query('SELECT userid, count(*) as cnt FROM `level_details` GROUP by 1 having cnt <5')->result();
		$pids = $this->db->query('SELECT distinct id FROM `plans`')->result();

		foreach ($users as $user) {
			$userid = $user->userid;
			foreach ($pids as $pid) {
				$query = $this->db->query("SELECT id FROM level_details where userid = $userid and pid = $pid");
            	if(!$query->num_rows()>0){
            			$this->yourfuncton($userid,$pid);
            	}	
			}
		}
	}




	public function register()
	{
		$this->downline_model->simulate_registration();
	}

	public function globalcart(){
		if(config_item('enable_club_income')=='Yes'){
			debug_log('Globalcart Club Income Started');
			$this->custom_income->global_club_income();
			debug_log('Globalcart Club Income Updated');
		}
	}
	public function icon()
	{
		$cjm_t1 = time();
		//$this->earning->credit_binary_commission_all();
		 $this->custom_income->icon_income();
		  debug_log('After complete of icon' . (time()-$cjm_t1));
	}

	public function credit_binary()
	{
		$this->earning->credit_binary_commission_all();
	}

	public function update_binary_position($from, $to, $leg)
	{
		$this->downline_model->update_binary_position($from, $to, $leg);
	}

	public function calibrate_tree()
	{
		debug_log('calibrate_tree');
		$this->downline_model->calibrate_tree();
	}

	public function calibrate_total_downline()
	{
		debug_log('calibrate_total_downline');
		$this->downline_model->calibrate_total_downline(array());
	}

	public function calibrate_level_details()
	{
		debug_log('calibrate_level_details');
		$this->downline_model->calibrate_level_details();	
	}

	public function mission_arogyam()
	{
		$this->custom_income->mission_arogyam_group_income();
		$this->custom_income->mission_arogyam_club_income();
	}

	private function nwi()
	{
		if(config_item('ideal_plan') == 'Yes') {
			//$this->earning->idle_non_working_income();
			//$this->earning->idle_rank_income();	
			//$this->earning->p2n_rank_income();
		}
		
	}		

	private function status()
	{
		$count_plan_renewal = $this->db_model->count_all('plans', array('recurring_fee >' => 0));

		if (0 < $count_plan_renewal) {
			$this->earning->update_status();
		}
	}

	public function update_leg()
	{
		$this->downline_model->update_legs(array());
	}

	public function custom_income()
	{
		$this->custom_income->index();
	}

	public function target_reach_income()
	{
		
		$this->earning->target_reach_income();
	}

	public function level_completion_income($pd='')
	{
		if(config_item('width') != 1)
		{
		$this->earning->level_completion_income($pd);
		}
	}

	public function single_leg_income()
	{
		if(config_item('width')==1)
		{
			$this->earning->single_leg_income();		
		}
	}

	//insert into transaction table when user clicks on pay using coinpayment button
	public function insert_into_transaction($uid)
	{
		//if details are already inserted into the table dont insert it again
		debug_log("inside insert into transaction");
		//print_r("die");die();
		debug_log($this->session->_type_);
		$type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';
		debug_log($type);
		if($type=="Registration Fee")
		{
			debug_log("registration fee topup");
			$td = $this->db_model->select_multi("*", 'transaction', array('userid' =>$this->session->_user_id_,'email_id'=>$this->session->_email_,'purpose'=>$type));
			debug_log($this->db->last_query());
			debug_log($td->userid);
            if($td->userid =="")
            {
		       $array = array(
                        'userid'         => $this->session->_user_id_,
                        'name'           => $this->session->_user_name_,
                        'email_id'       =>$this->session->_email_,
                        'amount'         => $this->session->_price_,
                        'gateway'        => 'Coinpayments.net',
                        'time'           => time(),
                        'purpose'        => $type,
                        'status'         => "Started",
                        );
               $this->db->insert('transaction', $array);
               debug_log($this->db->last_query());
            }
            else{
    	        debug_log("td is not null");
                }
        }
        else
        {
        	//$time=date('Y-m-d H:i:s',1589546894 );
        	//debug_log($time);
        	//$td = $this->db_model->select_multi("*", 'transaction', array('userid' =>$this->session->_user_id_,'email_id'=>$this->session->_email_,'purpose'=>$type,'Status'=>"Started"));
        	//debug_log("else part where topup  option");
        	//if($td->userid=="")
        	//{
  	          $array = array(
              'userid'         => $this->session->_user_id_,
              'name'           => $this->session->_user_name_,
              'email_id'       => $this->session->_email_,
              'amount'         => $this->session->_price_,
              'gateway'        => 'Coinpayments.net',
              'time'           => time(),
              'purpose'        => $type,
              'status'         => "Started",
               );
              $this->db->insert('transaction', $array);
              debug_log($this->db->last_query());
            //} 
        } 
	}


	/*private function binary_payout()
	{
		$count_product_binary = $this->db_model->count_all('product', array('matching_income >' => 0));
		$count_fix_binary = $this->db_model->select('binary_income', 'fix_income', array('1 >' => 0));
		$count_invst_binary = $this->db_model->select('matching_income', 'investment_pack', array(0));
		if ((0 < $count_product_binary) || (0 < $count_fix_binary) || (0 < $count_invst_binary)) {
			$this->db->select('id,total_a,total_b,paid_a,paid_b,signup_package,mypv,total_a_matching_incm,total_b_matching_incm, total_c_matching_incm, paid_a_matching_incm, paid_b_matching_incm')->from('member')->where('topup >', '0')->where('total_a >', 0)->where('total_b >', 0)->where('paid_a <', 'total_a', false)->where('paid_b <', 'total_b', false);
			$data = $this->db->get()->result();

			foreach ($data as $result) {
				$this->load->model('earning');
				$data2 = array('total_a' => $result->total_a, 'total_b' => $result->total_b, 'paid_a' => $result->paid_a, 'paid_b' => $result->paid_b, 'signup_package' => $result->signup_package, 'mypv' => $result->mypv, 'total_a_matching_incm' => $result->total_a_matching_incm, 'total_b_matching_incm' => $result->total_b_matching_incm, 'total_c_matching_incm' => $result->total_c_matching_incm, 'paid_a_matching_incm' => $result->paid_a_matching_incm, 'paid_b_matching_incm' => $result->paid_b_matching_incm);
				$this->earning->process_binary($result->id, $data2);
			}
		}
	}*/

	public function roi()
	{
		$count_product_roi = $this->db_model->count_all('plans', array('roi >' => 0));

		if (0 < $count_product_roi) {
			$this->earning->roi_earning();
		}
	}


	public function reward()
	{
		$this->load->model('earning');
		$this->earning->reward_process();
	}

	public function rank_update()
	{
		$this->load->model('earning');
		$this->earning->rank_process();
	}

	public function investment()
	{
		$this->load->model('investment');
		$this->investment->generate();
	}

	public function update_wallet()
	{
		$this->load->model('earning');
		$this->earning->payout(array());	
	}

	/*public function admin_topup()
	{
		redirect('users/topup-member');
	}*/

	public function member()
	{
		redirect('member');
	}

	public function generate_payout()
	{
		redirect('wallet/generate-payout');
	}

	public function complete_registration()
	{
		$this->load->dbutil();
		$this->dbutil->optimize_database();
		redirect('site/complete_registration');
	}

	public function check_user()
	{
		$user = trim($this->input->post('user'));

		if (0 < $this->db_model->count_all('franchisee', array('username' => $user))) {
			echo '<span style="color: red; background-color: moccasin">The Username is Taken !</span>';
		}
		else {
			echo '<span style="color: green; background-color: #d6e9c6">The Username is Available !</span>';
		}
	}

	public function get_stock_qty()
	{
		$fran_id = $this->input->post('fran_id');
		$prod_name = $this->input->post('prod');
		$prodid = $this->db_model->select('id', 'product', array('prod_name' => $prod_name));
		$qty = $this->db_model->select('available_qty', 'franchisee_stock', array('franchisee_id' => $fran_id, 'product_id' => $prodid));

		if ($qty == '') {
			$qty = 0;
		}

		echo $qty;
	}

	public function get_products()
	{
		$data = trim($this->input->get('term'));
		$this->db->select('prod_name')->from('product')->where('status', 'Selling')->where('qty !=', '0')->like('prod_name', $data, 'BOTH');
		$data = $this->db->get()->result();

		foreach ($data as $val) {
			$res[] = $val->prod_name;
		}

		echo json_encode($res);
	}

	public function get_own_products()
	{
		$data = trim($this->input->get('term'));
		$this->db->select('id')->from('product')->like('prod_name', $data, 'BOTH');
		$data = $this->db->get()->result();

		foreach ($data as $val) {
			$res[] = $val->prod_name;
		}

		echo json_encode($res);
	}

	public function get_wallet_balance($uid)
	{
		$uid = $this->common_model->filter($uid);
		$balance = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
		echo $balance;
	}

	public function get_repurchase_balance($uid)
	{
		$uid = $this->common_model->filter($uid);
		$balance = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
		echo $balance;
	}

	public function dev_mode($type)
	{
		if($type=='0')
		{
			$data = array('description' => '0',);
        	$this->db->where('type', 'dev_mode');
        	$this->db->update('settings', $data);
		}
		else if($type == '1')
		{
			$data = array('description' => '1',);
        	$this->db->where('type', 'dev_mode');
        	$this->db->update('settings', $data);
		}
	}

	public function get_lead_details($key, $type)
    {
        
    	$this->db->select('*')->from('dummy');
        if ($key !== "") {
            $this->db->where('dummy_side_verified', 'verified');   
        }
        if($type != ''){
         	$this->db->where('country !=', 'India');   
        }
        $this->db->order_by('id','DESC');

        $output = $this->db->get()->result_array();

        /*$this->db->query("
					select * from dummy order by id desc
				")->result_array();*/

        $col_names = array("id","dummy_text","dummy_values","dummy_side","dummy_side_verified","address","node","activity","time");
        $dis_names = array("id","Name","Phone","Email","Verified","IP Address","Address","Activity","Recent Visit");
        screen_log($this->db_model->printTable($output,$col_names,$dis_names));
    }

	public function database_backup()
    {
        // Auto Backup every 7 days
        /*if ((config_item('automatic_database_backup') == 'on') && time() > (config_item('last_autobackup') + 7 * 24 * 60 * 60)) {*/
            $this->load->dbutil();
            $prefs = array('format' => 'zip', 'filename' => 'Database-auto-full-backup_' . date('Y-m-d_H-i'));
            $backup = $this->dbutil->backup($prefs);
            if (!write_file('./uploads/backup/BD-backup_' . date('Y-m-d_H-i') . '.zip', $backup)) {
                debug_log("Error while creating auto database backup!");
            } else {
                /*$input_data['last_autobackup'] = time();
                foreach ($input_data as $key => $value) {
                    $data = array('value' => $value);
                    $this->db->where('config_key', $key)->update('tbl_config', $data);
                    $exists = $this->db->where('config_key', $key)->get('tbl_config');
                    if ($exists->num_rows() == 0) {
                        $this->db->insert('tbl_config', array("config_key" => $key, "value" => $value));
                    }
                }*/
                debug_log("Auto backup has been created.");

            }
        #}
        return TRUE;
    }

    //mark completed
	public function deliver()
    {
        //print_r("deliver");exit();
        $orderid = $this->input->post('deliverid');
        //print_r($orderid);exit();
        $tdetail = $this->input->post('tdetail');

        $before_tid = $this->db_model->select('tid', 'tbl_order_items', array('id' => $orderid));

        if($before_tid != '')
        {
            $after_tid = $before_tid . "<br/><br/>" .  date('Y-m-d') . "<br/> Notes:<br/>" . $tdetail; 
        } 
        else 
        {
            $after_tid = date('Y-m-d') . "<br/> Notes: " . $tdetail; 
        }

        $data = array(
            'pro_order_status'       => '5',
            //'deliver_date' => date('Y-m-d H:i:s'),
            'tid'          => $after_tid,
        );
        $this->db->where('id', $orderid);
        $this->db->update('tbl_order_items', $data);

        $order_detail  = $this->db_model->select_multi('*', 'tbl_order_items', array('id' => $orderid));

        $product_id = $this->db_model->select('product_id', 'tbl_order_items', array('id' => $orderid));
        debug_log($this->db->last_query());

        if($product_id == 0)
        {   

         $plan_detail = $this->db_model->select_multi('*', 'plans', array('invoice_name' => $order_detail->name));
        $member_detail = $this->db_model->select_multi('*', 'member', array('id' => $order_detail->user_id));
        $this->earning->credit_joining_commission($plan_detail,$member_detail);

        }
        else {
            $prod_details = $this->db_model->select_multi('product_title, selling_price,delivery_charge, max_unit_buy', 'tbl_product', array('id' => $product_id));
            //print_r($prod_details);exit();

            /*if ($prod_details->qty !== "-1")
            {
                $array = array('qty' => ($prod_details->qty - $order_detail->qty));
                $this->db->where('id', $product_id);
                $this->db->update('product', $array);
            } else {}*/

            $array = array('max_unit_buy' => ($prod_details->max_unit_buy - $order_detail->product_qty));
            $this->db->where('id', $product_id);
            $this->db->update('tbl_product', $array);
            debug_log($this->db->last_query());
            //print_r($this->db->last_query());
            $plan = $this->db_model->select('plan_id', 'tbl_product', array('id' => $product_id));
            debug_log($this->db->last_query());
            $pv = $this->db_model->select('pv', 'tbl_product', array('id' => $product_id));
            $this->earning->credit_product_comm($order_detail->user_id,$plan,$product_id, $pv*$order_detail->product_qty,($order_detail->product_price-$order_detail->tax)*$order_detail->product_qty, $order_detail->product_qty, 'Repurchase Commission');

            if(config_item('width')==2)
            {
                $this->downline_model->update_legs(array());
                $this->earning->target_reach_income();
            }

            $this->earning->payout(array());


            ############ INVOICE ENTRY #################################

            if(!$this->db_model->select('id', 'invoice', array('order_id'=>$order_detail->id))>0){
                debug_log($this->db->last_query());
                $member_detail = $this->db_model->select_multi('name, address, phone, topup', 'member', array('id' => $order_detail->user_id));
                $dd = $this->db_model->select_multi('*', 'tbl_addresses', array('user_id' => $order_detail->user_id));

                $gettop = $member_detail->topup + ($order_detail->product_price*$order_detail->product_qty);
                $topup  = array(
                    'topup' => $gettop,
                );
                $this->db->where('id', $order_detail->user_id);
                $this->db->update('member', $topup);
                
                $invoice_name = $prod_details->product_title;
                $user_id      = $order_detail->user_id;
                $vendor_id    = $order_detail->vendor_id;
                $invoice_date = date('Y-m-d H:i:s');
                $user_type    = 'Member';
                $company_add  = config_item('company_address') . "<br/>" . config_item('company_city') .', ' . config_item('company_state') .' - ' . config_item('company_zipcode') . ', ' . config_item('company_country');
                $ship_adress  = $dd->name. "<br/>" .$dd->mobile_no. "<br/>" .$dd->road_area_colony. "<br/>" .$dd->city. "<br/>" .$dd->state. "-" .$dd->pincode;
                $bill_add  = $dd->name. "<br/>" .$dd->mobile_no. "<br/>" .$dd->road_area_colony. "<br/>" .$dd->city. "<br/>" .$dd->state. "-" .$dd->pincode;
                $total_amt    = $order_detail->product_price*$order_detail->product_qty;
                $paid_amt     = $order_detail->product_price*$order_detail->product_qty;
                $prod_detail  = $this->db_model->select_multi('*', 'tbl_product', array('id' => $order_detail->product_id));
                $item_name    = $prod_detail->product_title;

                //$price        = round($prod_detail->selling_price*(1-($prod_detail->discount/100)) / (1 + $prod_detail->gst / 100), 2);
                $price=$prod_detail->selling_price;
                //$p_w_tax        = round($prod_detail->prod_price / (1 + $prod_detail->gst / 100), 2);
                $tax_rate     = "0";
                $tax="0";
                //$tax_rate     = $prod_detail->gst;
                //$tax          = round($order_detail->cost - $price,2);
                //$tax=$order_detail->tax;
                $qty          = $order_detail->product_qty;

                $array  = array($item_name => $price);
                $array2 = array($item_name => $tax);
                $array3 = array($item_name => $qty);

                $array  = serialize($array);
                $array2 = serialize($array2);
                $array3 = serialize($array3);

                $params = array(
                    'invoice_name'     => $invoice_name,
                    'userid'           => $user_id,
                    'vendor_id'        =>$vendor_id,
                    'invoice_data'     => $array,
                    'invoice_data_tax' => $array2,
                    'invoice_data_qty' => $array3,
                    'company_address'  => $company_add,
                    'bill_to_address'  => $bill_add,
                    'ship_to_address'  => $ship_adress,
                    'total_amt'        => $total_amt,
                    'paid_amt'         => $paid_amt,
                    'date'             => $invoice_date,
                    'user_type'        => $user_type,
                );
                $this->db->insert('invoice', $params);
            }

            if($order_detail->tax > 0)
            {     //($values["item_price"]-($values["item_price"]*($values["item_discount"]/100)))*$values["item_quantity"]
                $taxdata=array(
                     'userid'=>$order_detail->user_id,
                     'invoice_id' =>  $this->db_model->select('id', 'invoice', array('order_id'=>$order_detail->id)),
                     'amount'=>($prod_details->prod_price-($prod_details->prod_price*($prod_details->discount/100)))*$order_detail->qty,
                     'tax_amount' =>$order_detail->tax*$order_detail->qty, 
                     //'vendor_id'=> $order_detail->vendor_id,
                     'tax_percnt' =>$prod_details->gst,
                     'date' =>date('Y-m-d H:i:s'),
                     'transaction_id'=>$prod_details->prod_name . ': Order ID - ' . $orderid,
                 );
                $this->db->insert('tax_report', $taxdata);
            }

        }

        ########## END ENTRY #######################################
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Order Marked as Delivered successfully.</div>');
        redirect('product/pending_orders');
    }

    
}

defined('BASEPATH') || true;

?> 
	