<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_member() == false) {
            redirect(site_url('site/login'));
        }
        
        $this->load->library('pagination');
        $this->load->library('cart');
        $this->load->model('user_model');
        $this->load->model('registration_model');
        $this->load->model('earning');
        $this->load->model('plan_model');
        $this->load->model('downline_model');
        $this->load->model('registration_model');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
    }

    public function index()
    {
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        if($this->session->role =='customer'){
            $this->load->view('member/customer/base', $data);    
        }else{
            $this->load->view(config_item('member'), $data);    
        }
    }

    public function logout()
    {
        $session_url=$_SESSION['page'];
        $this->session->sess_destroy();
        $this->session->set_flashdata('site_flash', '<div class="alert alert-info">You have been logged out !</div>');
        if($session_url)
        {
            redirect($session_url);
            //unset($_SESSION["page"]);
        }
        else
        {
            redirect(site_url('site/login'));
        }
    }

    public function App($template, $value)
    {
        $this->load->view($template.'/'.$value);
    }

    public function verified()
    {
        parse_parameters('verified');
        redirect('member');
    }


    // CORE MEMBER PARTS HERE NOW ############################################################ STARTS :
    public function news()
    {
        $config['base_url'] = site_url('member/news');
        $this->db->select('*')->from('news')->order_by('date', 'DESC');

        $data_news['news'] = $this->db->get()->result_array();

        $data_news['title'] = 'News Announcements';
        //$data['layout'] = 'ad/news.php';
        $this->load->view(config_item('member'), $data_news);

    }
    
    public function used_epin()
    {
        $config['base_url'] = site_url('member/used_epin');
        $config['per_page'] = 50;
        $config['total_rows'] = $this->db_model->count_all('epin', array(
            'status' => 'Used',
            'issue_to' => $this->session->user_id,
        ));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('t1.id, t1.epin, t1.amount, t1.used_by, t1.used_time, t1.type, t2.name')->from('epin as t1')->where(array('used_by'=>$this->session->user_id ))->or_where(array('issue_to'=>$this->session->user_id))->where(array('status'=>'Used'))
            ->join("(SELECT id, name FROM member) as t2", 'used_by = t2.id', 'LEFT')
            ->limit($config['per_page'], $page);

        $data['epin'] = $this->db->get()->result_array();

        $data['title'] = 'Used e-PINs';
        $data['layout'] = 'epin/used.php';
        $this->load->view(config_item('member'), $data);

    }

    public function unused_epin()
    {
        $config['base_url'] = site_url('member/unused_epin');
        $config['per_page'] = 50;
        $config['total_rows'] = $this->db_model->count_all('epin', array(
            'status' => 'Un-used',
            'issue_to' => $this->session->user_id,
        ));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, epin, amount, issue_to, generate_time, generate_time')->from('epin')
            ->where('status', 'Un-used')->where('issue_to', $this->session->user_id)
            ->limit($config['per_page'], $page);

        $data['epin'] = $this->db->get()->result_array();

        $data['title'] = 'Unused e-PINs';
        $data['layout'] = 'epin/unused.php';
        $this->load->view(config_item('member'), $data);

    }

    public function search_epin()
    {
        $config['base_url'] = site_url('member/search_epin');
        $config['per_page'] = 500;

        $config['total_rows'] = $this->db_model->count_all('epin', array('generated_by'=>$this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('epin')->where('generated_by',$this->session->user_id)
                 ->limit($config['per_page'], $page);

        $data['epin'] = $this->db->get()->result_array();

        $data['title']      = 'Search e-PINs';
        $data['breadcrumb'] = 'Search e-pin';
        $data['layout']     = 'epin/search_epin.php';
        $this->load->view(config_item('member'), $data);
    }

    public function transfer_epin()
    {

        $this->form_validation->set_rules('amount', 'e-PIN Amount', 'trim|required');
        $this->form_validation->set_rules('to', 'To User ID', 'trim|required');
        $this->form_validation->set_rules('qty', 'Number of e-PINs', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Transfer e-PIN';
            $data['layout'] = 'epin/transfer_epin.php';
            $this->db->select('id, plan_name, joining_fee, gst')->where(array(
                'status' => 'Selling',
                'show_on_regform' => 'Yes',
                'type !=' => 'Repurchase'
                ))->order_by('plan_name', 'ASC');
            $data['plans']   =$this->db->get('plans')->result_array();

            $data['epin'] = $this->db->query("SELECT * from epin where transfer_by like 
                '%".','.$this->session->user_id."%' order by generate_time desc")->result_array();

            debug_log($this->db->last_query());

            #$this->db->select('*')->from('epin')->where('generated_by',$this->session->user_id)->order_by('generate_time','DESC')

            $this->load->view(config_item('member'), $data);
        } else {
            $amount = $this->common_model->filter($this->input->post('amount'), 'float');
            $to = $this->common_model->filter($this->input->post('to'));
            $from = $this->session->user_id;
            $qty = $this->common_model->filter($this->input->post('qty'), 'number');

            if(!$this->db_model->check_user($to)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('member/transfer_epin');
            }

            $avl_qty = $this->db_model->count_all('epin', array(
                'issue_to' => $from,
                'amount' => $amount,
                'status' => 'Un-used',
            ));
            if ($avl_qty < $qty) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID have only ' . $avl_qty . ' Un-used epin of ' . config_item('currency') . ' ' . $amount .'<a style="color:blue;" href=' . site_url("member/topup_wallet").'> Click here</a> to Topup your wallet'. '.</div>') ;
                redirect('member/transfer_epin');
            } else {
                
                $level_sponsor_sql = "UPDATE `epin` SET `issue_to` = ".$to.", 
                    `transfer_by` = 
                        CASE 
                        WHEN CHAR_LENGTH(transfer_by) >0 THEN CONCAT(transfer_by,',',".$from.") 
                        ELSE CONCAT(',',".$from.")
                         END, 
                        `transfer_time` = '".date('Y-m-d H:i:s')."'
                        WHERE `issue_to` = ".$from." AND `amount` = ".$amount." AND `status` = 'Un-used' 
                    LIMIT ".$qty."";
                $this->db->query($level_sponsor_sql);

                debug_log($this->db->last_query());                

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">' . $qty . ' e-PIN transferred from  ' . $from . ' to ' . $this->input->post('to') . ' of ' . config_item('currency') . ' ' . $amount . '.</div>');
                redirect('member/transfer_epin');
            }
        }
    }

    public function generate_epin()
    {
        $this->form_validation->set_rules('plan_id', 'e-PIN Amount', 'trim|required');
        $this->form_validation->set_rules('userid', 'Issue to ID', 'trim|required');
        $this->form_validation->set_rules('number', 'Number of e-PINs', 'trim|required|max_length[3]');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Generate e-PIN';
            $data['layout'] = 'epin/generate.php';
             $this->db->select('id, plan_name, joining_fee, gst')->where(array(
                'status' => 'Selling',
                'show_on_regform' => 'Yes',
                'type !=' => 'Repurchase'
            ))->order_by('id', 'ASC');
            $data['plans']   =$this->db->get('plans')->result_array();

            $config['per_page'] = 500;

            $config['total_rows'] = $this->db_model->count_all('epin', array('generated_by'=>$this->session->user_id));
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $this->pagination->initialize($config);

            $this->db->select('*')->from('epin')->where('generated_by',$this->session->user_id)->order_by('generate_time','DESC')->limit($config['per_page'], $page);
            $data['epin'] = $this->db->get()->result_array();
            $this->load->view(config_item('member'), $data);
        } else {

            $userid = $this->common_model->filter($this->input->post('userid'));
            if(!$this->db_model->check_user($userid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('member/generate_epin');
            }

            $member=$this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));
            $payout=$this->db_model->select_multi('*', 'payout', array('plan_id' => $member->signup_package));
            
            $plan = $this->db_model->select_multi('*', 'plans', array('id' =>$this->common_model->filter($this->input->post('plan_id'))));
            $epin_settings = $this->db_model->select_multi('user_epin_charge, user_epin_cashback, user_epin_plus', 'payout', array('plan_id' =>$this->common_model->filter($this->input->post('plan_id'))));

            $amount = $plan->joining_fee;
            $userid = $this->common_model->filter($this->input->post('userid'));
            $qty = $this->common_model->filter($this->input->post('number'), 'number');
            $total_amt = ($amount * $qty) + ($amount * $qty)*($epin_settings->user_epin_charge/100);
            $get_user_balance = $this->db_model->select('balance', 'wallet', array('userid' => $this->session->user_id));

            if ($get_user_balance < $total_amt) {
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>You wallet donot have sufficient balance to generate $qty e-PIN. Your wallet need to have " . config_item('currency') . $total_amt .  "<br><a style='color:blue;' href=" . site_url('member/topup_wallet').">Click here</a> to Topup your wallet</div>");
                redirect('member/generate-epin');
            }

            $total_cashback = 0;
            $cashback = 0;
            if(strlen($epin_settings->user_epin_cashback)!=0){
                $key_value = explode(',', $epin_settings->user_epin_cashback);
                debug_log($key_value);
                foreach ($key_value as $key => $value) {
                    $qt_amnt = explode(':', $value);
                    debug_log($qt_amnt);
                    if($qty >= $qt_amnt[0]){
                        $cashback = $qt_amnt[1];
                    }
                }
                $total_cashback = $cashback*$qty;
            }

            debug_log($cashback);
            debug_log($total_cashback);

            $total_qty = $qty;
            $extra = 0;
            if(strlen($epin_settings->user_epin_plus)!=0){
                $key_value = explode(',', $epin_settings->user_epin_plus);
                debug_log($key_value);
                foreach ($key_value as $key => $value) {
                    $qt_plus = explode(':', $value);
                    debug_log($qt_plus);
                    if($qty >= $qt_plus[0]){
                        $extra = $qt_plus[1];
                    }
                }
                $total_qty = $qty+$extra;
            }

            debug_log($extra);
            debug_log($total_qty);

            $data = array();
            for ($i = 0; $i < $total_qty; $i++) {
                $rand = mt_rand(10000000, 99999999);
                $epin = $this->db_model->select("epin", "epin", array("epin" => $rand));
                while($epin==$rand){
                    $rand = $rand + 1;    
                    $epin = $this->db_model->select("epin", "epin", array("epin" => $rand));
                }
                $array = array(
                    'epin' => $rand,
                    'amount' => $amount,
                    'issue_to' => $userid,
                    'generated_by' => $this->session->user_id,
                    'generate_time' => date('Y-m-d H:i:s'),
                );
                array_push($data, $array);
            }
            $status = $this->db->insert_batch('epin', $data);
            debug_log($this->db->last_query());
            debug_log('Member generate Epin Insert status ');
            debug_log($status);

            if($status>0){
                $arra = array('balance' => ($get_user_balance - $total_amt),);
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('wallet', $arra);
                wallet_log($this->db->last_query());

                $this->earning->add_deduction($this->session->user_id, 'admin', $total_amt, 'ePin', 'Member ePin Generation',$member->signup_package, 'Account Transfer', '');

                $this->earning->pay_earning($this->session->user_id, '', 'Cashback from Epin Generation', 'Cashback from Epin Generation', $total_cashback, '', $plan->id);
                
                $this->earning->payout(array($this->session->user_id));

                $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>$total_qty e-PIN created successfully.</div>");
                if (trim(config_item('smtp_host')) !== "") {
                    $this->db_model->mail($this->db_model->select('email', 'member', array('id' => $userid)), 'e-PIN Issued', 'Dear Sir, <br/> e-PIN of Qty ' . $total_qty . ', has been issued to your account from user id: ' . config_item('ID_EXT') . $this->session->user_id . ' on behalf of us.<br/><br/>---<br/>Regards,<br/>' . config_item('company_name'));
                }
                redirect('member/generate_epin');    
            }else{
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>There is some issue generating the issue. Please contact support</div>");
                redirect('member/generate_epin');    
            }
        }
    }

    public function view_earning()
    {
        $config['base_url'] = site_url('member/view_earning');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('earning', array('userid' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, userid, amount, type, ref_id, date, pair_match,pair_names, secret')->from('earning')
            ->where('userid', $this->session->user_id)->order_by('id DESC', 'date DESC', 'amount asc')->limit($config['per_page'], $page);

        $data['earning'] = $this->db->get()->result_array();

        $data['title'] = 'Earnings';
        $data['layout'] = 'income/view_earning.php';
        $this->load->view(config_item('member'), $data);

    }
     public function view_level_details() 
    {
        $config['base_url'] = site_url('member/view_level_details');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('level', array('userid' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        // $this->db->select('*')->from('level')
        //     ->where(array('userid'=>$this->session->user_id , 'pid'=>1))->limit($config['per_page'], $page);

        // $data['earning'] = $this->db->get()->result_array();
        $data['earning'] = $this->db_model->select_multi('*', 'level',array('userid'=>$this->session->user_id , 'pid'=>1));
        debug_log($this->db->last_query()); 
        debug_log($data['earning']); 

        $data['title'] = 'level_details';
        $data['layout'] = 'income/view_level_details.php';
        $this->load->view(config_item('member'), $data);

    }

    public function view_deductions()
    {
        $config['base_url'] = site_url('member/view_deductions');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('deductions', array('user_id' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('deductions')
            ->where('user_id', $this->session->user_id)->order_by('id DESC', 'date DESC', 'amount asc')->limit($config['per_page'], $page);

        $data['deductions'] = $this->db->get()->result_array();

        $data['title'] = 'Deductions';
        $data['layout'] = 'income/view_deductions.php';
        $this->load->view(config_item('member'), $data);

    }

    public function view_pv()
    {
        $config['base_url'] = site_url('member/view_pv');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('pv', array('userid' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('pv')
            ->where('userid', $this->session->user_id)->order_by('id DESC', 'date DESC', 'amount asc')->limit($config['per_page'], $page);

        $data['pv'] = $this->db->get()->result_array();

        $data['title'] = 'My PV';
        $data['layout'] = 'income/view_pv.php';
        $this->load->view(config_item('member'), $data);

    }

    public function renew_account()
    {
        $epin = trim($this->input->post('renew_pin'));
        $renew_amount = trim($this->input->post('renew_amount'));
        $user_id = trim($this->input->post('user_id'));

        if (trim($epin) !== "") {
            $epin_value = $this->db_model->select('amount', 'epin', array(
                'epin' => $epin,
                'status' => 'Un-used',
            ));

            $epin_type = $this->db_model->select('type', 'epin', array(
                'epin' => $epin,
                'status' => 'Un-used',
            ));

            if ($epin_value <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered e-PIN is invalid or doesn\'t exist.</div>');
                redirect(site_url('member'));
            } else if ($epin_value < $renew_amount) {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">e-PIN value is less then than ' . config_item(currency) . $renew_amount . '.</div>');
              redirect(site_url('member'));
            } else {
              if ($epin_type == "Multi Use"):
                  $amount = $epin_value - $renew_amount;
                  if ($amount <= 0):
                      $data = array(
                          'status' => 'Used',
                          'used_by' => $user_id,
                          'used_time' => date('Y-m-d H:i:s'),
                      );
                  else:
                      $data = array(
                          'amount' => $amount,
                          'used_by' => $user_id,
                          'used_time' => date('Y-m-d H:i:s'),
                      );
                  endif;
                  $this->db->where('epin', $epin);
                  $this->db->update('epin', $data);
              else:
                  $data = array(
                      'status' => 'Used',
                      'used_by' => $user_id,
                      'used_time' => date('Y-m-d H:i:s'),
                  );
                  $this->db->where('epin', $epin);
                  $this->db->update('epin', $data);
              endif;

                $top_up = $this->db_model->select('topup', 'member', array('id' => $user_id));

                $data = array(
                    'status' => 'Active',
                    'topup' => $renew_amount + $top_up,
                );

                $this->db->where('id', $user_id);
                $this->db->update('member', $data);

                $renew_data = array(
                    'user_id' => $user_id,
                    'to_user' => 'admin',
                    'amount' => $renew_amount,
                    'type' => 'Account Renewal',
                    'payment_mode' => 'epin',
                    'transaction_id' => $epin,
                    'date' => date('Y-m-d H:i:s')
                );

                $this->db->insert('deductions', $renew_data);

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Your Account is Renewed</div>');
                redirect(site_url('member'));
            }
        }
    }

    public function topup_wallet(){
        $value= $this->uri->segment('3') ? $this->uri->segment('3') : '';
        $userid= $this->uri->segment('4') ? $this->uri->segment('4') : '';
        if(config_item('enable_pg')=='Yes'){
            redirect(site_url('member/online_deposit/'.$value .'/'.$userid));
        }else if(config_item('enable_bank_deposit')=='Yes'){
            redirect(site_url('member/bank_deposit/'.$value .'/'.$userid));
        }else{
            if(config_item('crowdfund_type')=='Manual_Peer_to_Peer'){
                redirect(site_url('member/bank_deposit/'.$value .'/'.$userid));
            }else{
                redirect(site_url('member/epin_deposit'));    
            }
            
        }
    }

    public function epin_deposit()
    {
        if (!isset($_POST['amount']) && !isset($_POST['epin'])) {
            $data['title'] = 'Fund My Wallet';
            $data['layout'] = 'wallet/epin_deposit.php';
            $this->load->view(config_item('member'), $data);
        } else {

            $epin = trim($this->input->post('epin'));
            $amount = trim($this->input->post('amount'));

            $epin_value = $this->db_model->select('amount', 'epin', array(
                'epin' => $epin,
                'status' => 'Un-used',
            ));

            if ($epin_value <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered e-PIN is invalid or doesn\'t exist.</div>');
                redirect(site_url('member/epin_deposit'));
            }
            
            $wallet_balance = $this->db_model->select('balance', 'wallet', array('userid' => $this->session->user_id));
            $this->db->where(array('userid' => $this->session->user_id));
            $this->db->update('wallet', array('balance' => $wallet_balance + $epin_value));
            wallet_log($this->db->last_query());
            wallet_log('Topup while ePin Deposit');

            $data = array(
                'status' => 'Used',
                'used_by' => $this->session->user_id,
                'used_time' => date('Y-m-d H:i:s'),
                'remarks' =>'Member Wallet Topup',
            );

            $this->db->where('epin', $epin);
            $this->db->update('epin', $data);

            $bank_details = array(
            'userid' => $this->session->user_id,
            'to_userid'=> 'Admin',
            'name'   => $this->session->name,
            'amount' => $epin_value,
            'gateway' => 'Using Epin',
            'time' =>time(),
            'purpose' => 'Epin Wallet Topup',
            'transaction_id'=>'Epin: '.$epin,
            'payment_request_id'=>'Epin: '.$epin,
            'status' => 'Completed',
            'remarks' => 'Epin Wallet Topup'
            );

            $this->db->insert('transaction', $bank_details);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund is added to your wallet.</div>');
            redirect(site_url('member/online_transactions'));
        }
    }

    public function bank_deposit()
    {
        $amount= $this->uri->segment('3') ? $this->uri->segment('3') : '';
        $to_userid= $this->uri->segment('4') ? $this->uri->segment('4') : 'Admin';
        if (!isset($_POST['amount']) && !isset($_POST['txn_no']) && $amount=='') {
            $data['title'] = 'Deposit Through Bank Transfer';
            $data['layout'] = 'wallet/bank_deposit.php';
            $data['amount']= $this->uri->segment('3') ? $this->uri->segment('3') : '';
            $this->load->view(config_item('member'), $data);
        } 
        
        else 
        {  
            if (isset($_POST['amount']) || ($amount!='' && isset($_POST['txn_no'])==""))
            {
              if(isset($_POST['amount']))
              {
                $data['amount']=$_POST['amount'];
              }
              else{
                $data['amount']=$amount;
              }
             $data['title'] = 'Fund My Wallet ';
             $data['layout'] = 'wallet/bank_details.php';
             $this->load->view(config_item('member'), $data);
            } 
            
            else if (isset($_POST['txn_no']))
            {
             $amount = trim($this->input->post('amount1'));
             $txn_no = trim($this->input->post('txn_no'));
             $payment_mode=trim($this->input->post('payment_mode'));

             if(config_item('crowdfund_type')=='Manual_Peer_to_Peer'){
                if((!(strpos($this->input->post('payment_remarks'), 'Fee') !== false)) || (!strlen($this->input->post('secret'))>0)){
                    debug_log('Manipulating the URL');
                    redirect(site_url('member'));
                }

                if(strpos($this->input->post('payment_remarks'), 'Member Fee') !== false){
                    $nl = $this->db_model->select('gift_level', 'member', array('id' => $this->session->user_id));
                    $to_id =  $this->db_model->select('level'.($nl+1), 'crowdfund_queue', array('userid' => $this->session->user_id));
                    if($to_id!=$to_userid){
                        debug_log('Manipulating the URL '.$to_id.' '.$to_userid);
                        redirect(site_url('member'));
                    }
                }

                if(strpos($this->input->post('payment_remarks'), 'Sponsor Fee') !== false){
                 $sponsor =  $this->db_model->select('sponsor', 'member',array('id' => $this->session->user_id));
                    if($sponsor!=$to_userid){
                        debug_log('Manipulating the URL '.$sponsor.' '.$to_userid);
                        redirect(site_url('member'));
                    }
                }

                if(strpos($this->input->post('payment_remarks'), 'Admin Fee') !== false){
                    if(strtolower($to_userid) != 'admin'){
                        debug_log('Manipulating the URL Admin '.$to_userid);
                        redirect(site_url('member'));
                    }
                }

             }

             if($to_userid == $this->session->user_id){
                redirect(site_url('member'));  
             }

             /*
             if((config_item('crowdfund_type')=='Manual_Peer_to_Peer') && ((!strlen($this->input->post('payment_remarks'))>0) || (!strlen($this->input->post('secret'))>0))){
                redirect(site_url('member'));
             }
             */

             if($amount!="" && $txn_no!="" )
             {

                $user_data = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));
                $bank_details = array(
                    'userid' => $this->session->user_id,
                    'to_userid'=> $to_userid,
                    'name'   => $this->session->name,
                    'email_id' => $user_data->email,
                    'phone'  => $user_data->phone,
                    'amount' => $amount,
                    'gateway' => $payment_mode,
                    'time' =>time(),
                    'purpose' => 'Bank Deposit',
                    'transaction_id'=>$txn_no,
                    'status' => 'Processing',
                    'secret' => $this->input->post('secret'),
                    'remarks' => $this->input->post('payment_remarks')
                );

                $this->db->insert('transaction', $bank_details);

                //debug_log($this->db->last_query());

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Your payment is under process!!</div>');
                redirect(site_url('member/online_transactions'));

             }
            }


        }
    }

    public function approve($id)
    {
       $td = $this->db_model->select_multi('*', 'transaction', array('id' => $id));
       if($td->status=='Processing')
       {

           if(config_item('crowdfund_type')=='Manual_Peer_to_Peer')
           {
                $md = $this->db_model->select_multi('*', 'member', array('id' => $td->userid));
                $pd = $this->db_model->select_multi('*', 'plans', array('id' => $md->signup_package));

                $upd = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));

                $cs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$md->signup_package, 'upgrade_type'=>($md->gift_level+1)));

                if(strpos($td->remarks, 'Sponsor Fee') !== false)
                {
                    $array = array('time'=>time(), 'status'=>"Completed",);
                    $this->db->where(array('id'=>$id));
                    $this->db->update('transaction', $array);

                    $this->earning->pay_earning($upd->id, $td->userid, 'level '.($md->gift_level+1).' Sponsor Income', 'User Upgrade Amount from '.$md->name, $td->amount, '', $cs->id);

                    debug_log('cycle_level ' .config_item('cycle_level'));
                    #Assign cycle upline member only if there is config cycle level is not null
                        if (config_item('cycle_level')!='') {
                            $upline_id = $this->plan_model->cycle_upline($md,$pd,$cs);
                            debug_log('$upline_id ' . $upline_id);
                        }
                         #Assign unlimited cycle upline member only if there is  config unlimited_cycle level is not null
                        elseif (config_item('unlimited_cycle_level')!=''){
                            $upline_id = $this->plan_model->unlimited_cycle_upline($md,$pd,$cs);
                            debug_log('$upline_id ' . $upline_id);
                        }
                         #Assign upline member only if there is  config cycle and config unlimeted cycle is  null
                        else{
                            $upline_id = $this->plan_model->crowdfund_upline_new($md,$pd,$cs);
                            debug_log('$upline_id ' . $upline_id); }
            
                    debug_log('$upline_id ' . $upline_id);
                    if(strlen($upline_id) > 2)
                    {
                      $update_queue = "UPDATE crowdfund_queue SET level".($md->gift_level+1)." = $upline_id WHERE userid = ".$td->userid." and pid = ".$pd->id;
                      $this->db->query($update_queue);
                      debug_log($this->db->last_query());   
                    }
                    else
                    {
                      $update_queue = "UPDATE crowdfund_queue SET level".($md->gift_level+1)." = ".config_item('top_id')." WHERE userid = ".$td->userid." and pid = ".$pd->id;
                      $this->db->query($update_queue);
                      debug_log($this->db->last_query());   
                    }

                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Payment is Approved.</div>');                 
                    
                    redirect('member/confirmed_deposit');

                }

                 debug_log('cycle_level ' .config_item('cycle_level'));

                  #pay earnings update if there is config cycle level is  null
                if (config_item('cycle_level')=='')
                {
                $this->earning->pay_earning($upd->id, $td->userid, 'level '.($md->gift_level+1).' Income', 'User Upgrade Amount from '.$md->name, $td->amount, '', $cs->id); 
                }

                #pay earnings update if there is config cycle level is not null
                else
                {
                    $cq = $this->db_model->select_multi('*','crowdfund_queue',array('pid'=>$md->signup_package, 'userid'=>$md->id));
                 debug_log($this->db->last_query());
 
                  debug_log('crowdfund_queue level 2 is equal');
                   $umd=$this->db_model->select_multi('*', 'member', array('id' => $cq->level2));
                 $this->earning->pay_earning_updated($cq->level2, $umd->name, $td->userid, 'level '.($md->gift_level+1).' Income', 'User Upgrade Amount from '.$md->name,$td->amount,'', $cs->id); 
               
                    if ($cq->level2!=config_item('top_id')) 
                    {
                     $income_countt = $this->db->query("select count(userid) as count from earning where userid = ".$cq->level2." and secret = ".$cs->id." and status = 'Paid' and type like '%level ".$umd->gift_level." Income%'")->result_array()[0]['count'];
                     debug_log($this->db->last_query());
                    }

                        debug_log($income_countt);
                        debug_log(pow($pd->max_width, config_item('cycle_level')));
                     if (($income_countt)== pow($pd->max_width, config_item('cycle_level'))) 
                    {
                        debug_log('crowdfund_queue level 2 is not equal');
                        $udata = $this->db->query(" SELECT userid FROM crowdfund_queue WHERE level2 IN (".$cq->level2.") and level1 != level2 and pid = ".$md->signup_package)->result_array()[0]; 
                        debug_log($this->db->last_query());
                        debug_log($udata);
                        $udata_id = $udata['userid'];
                        debug_log($udata_id);

                     $this->earning->cycle_earning_updated($udata_id,$td->amount,$cs->id);
                    } 
                }
 

                $this->crowd_activate($md->id,$this->session->user_id);
                debug_log('After Activate');
                debug_log($this->db->last_query());   

                $update_level = "UPDATE member SET gift_level =".($md->gift_level+1)." , last_upgrade = ".time()." WHERE id = $md->id";
                $this->db->query($update_level);
                debug_log($this->db->last_query());

                 #rank update if there is config unlimited cycle level is not null
                 if (config_item('unlimited_cycle_level')!='') {
                    if ($md->gift_level>0) {
                    $update_level = "UPDATE member SET rank = 'fire'  WHERE id = $md->id";
                    $this->db->query($update_level);
                    debug_log($this->db->last_query());
                    $this->earning->rank_process();
                    }
                }
                
                $array = array(
                'time'=> time(),'status'     => "Completed",
                );
                $this->db->where(array('id'=>$id));
                $this->db->update('transaction', $array);

                debug_log('cycle_level ' .config_item('cycle_level'));
                debug_log('unlimited_cycle_level ' .config_item('unlimited_cycle_level'));
                 #giftlevel+1 update if there is config cycle level and unlimited cycle is null
                if ((config_item('cycle_level')=='') && (config_item('unlimited_cycle_level')==''))
                {
                #Here no need to include completed status since we are updating completed after this
                $income_count = $this->db->query("select count(distinct userid) as count from transaction where to_userid = ".$upd->id." and secret = ".$cs->id." and status = 'Completed' and remarks like '%Member Fee%'")->result_array()[0]['count'];

                debug_log($this->db->last_query());
                debug_log('income_count ' . $income_count);

                debug_log('$upd->gift_level '.$upd->gift_level);
                debug_log('$md->gift_level '.$md->gift_level);

                if(($income_count >= pow($pd->max_width, $upd->gift_level))&&($upd->gift_level<($md->gift_level+2)))
                {
                    $ucs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$upd->signup_package, 'upgrade_type'=>($upd->gift_level+1)));
                    $ccs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$upd->signup_package, 'upgrade_type'=>($upd->gift_level)));

                    if($upd->id==config_item('top_id')){
                        $update_level = "UPDATE member SET gift_level =".($upd->gift_level+1)." , last_upgrade = ".time()." WHERE id = $upd->id";
                        $this->db->query($update_level);
                        debug_log($this->db->last_query());
                    }

                    elseif((!empty($ucs->id)) && (strval($ucs->admin_charge)==0) && (strval($ucs->sponsor_fee)==0)){

                        $uppd = $this->db_model->select_multi('*', 'plans', array('id' => $upd->signup_package));

                         #Assign cycle upline member only if there is config cycle level is not null
                            if (config_item('cycle_level')!='') {
                                 $upline_id = $this->plan_model->cycle_upline($upd,$uppd,$ucs);
                                  debug_log('$upline_id ' . $upline_id);
                            }
                #Assign unlimited cycle upline member only if there is  config unlimited_cycle level is not null
                            elseif (config_item('unlimited_cycle_level')!=''){
                                $upline_id = $this->plan_model->unlimited_cycle_upline($upd,$uppd,$ucs);
                                debug_log('$upline_id ' . $upline_id);
                            }
                #Assign upline member only if there is  config cycle and config unlimeted cycle is  null
                            else{
                                $upline_id = $this->plan_model->crowdfund_upline_new($upd,$uppd,$ucs);
                                debug_log('$upline_id ' . $upline_id); }
                
                        debug_log('$upline_id ' . $upline_id);
                        if(strlen($upline_id) > 2)
                        {
                          $update_queue = "UPDATE crowdfund_queue SET level".($upd->gift_level+1)." = $upline_id WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                          $this->db->query($update_queue);
                          debug_log($this->db->last_query());   
                        }
                        else
                        {
                          $update_queue = "UPDATE crowdfund_queue SET level".($upd->gift_level+1)." = ".config_item('top_id')." WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                          $this->db->query($update_queue);
                          debug_log($this->db->last_query());   
                        }    
                    }
                    elseif(($ccs->plan_upgrade=='Yes')&&($ccs->plan_new_id>0)){

                        $update_upline = "UPDATE member SET ".$upd->placement_leg."=0 where id = ".$upd->position;

                        $update_plan = "UPDATE member SET gift_level = 0 , position ='', signup_package=".$ccs->plan_new_id.", total_downline = 0, total_active=0, A=0, B=0,C=0,D=0,E=0, F=0, G=0, status = 'Inactive' WHERE id = ".$upd->id;
                        $this->db->query($update_plan);
                        debug_log($this->db->last_query());

                        $data = array('userid' => $upd->id,'pid'=>$ccs->plan_new_id);
                        $this->db->insert('crowdfund_queue', $data);   
                        debug_log($this->db->last_query());

                        $multi_array = $this->db->query('select id from member where position = '.$upd->id)->result_array();

                        if(count($multi_array)>0){
                            $iterator_array = new RecursiveIteratorIterator(new RecursiveArrayIterator($multi_array));
                            $flat_array = array();

                            foreach($iterator_array as $v) {
                                array_push($flat_array, $v);
                            }
                            $ids = implode(',',$flat_array);

                            $update_position = "UPDATE member SET position = 0 WHERE id IN (" .$ids .")";
                            $this->db->query($update_position);
                            debug_log($this->db->last_query()); 
                        }                         

                        $upd = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));

                        $ncs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$ccs->plan_new_id, 'upgrade_type'=>1));
                        $npd = $this->db_model->select_multi('*', 'plans', array('id' => $ccs->plan_new_id));

                        if((!empty($ncs->id)) && (strval($ncs->admin_charge)==0) && (strval($ncs->sponsor_fee)==0)){  
                            debug_log('$upline_id details during plan upgrade before');
                              #Assign cycle upline member only if there is config cycle level is not null
                            if (config_item('cycle_level')!='') {
                                 $upline_id = $this->plan_model->cycle_upline($upd,$npd,$ncs);
                                  debug_log('$upline_id ' . $upline_id);
                            }
                #Assign unlimited cycle upline member only if there is  config unlimited_cycle level is not null
                            elseif (config_item('unlimited_cycle_level')!=''){
                                $upline_id = $this->plan_model->unlimited_cycle_upline($upd,$npd,$ncs);
                                debug_log('$upline_id ' . $upline_id);
                            }
                #Assign upline member only if there is  config cycle and config unlimeted cycle is  null
                            else{
                                $upline_id = $this->plan_model->crowdfund_upline_new($upd,$npd,$ncs);
                                debug_log('$upline_id ' . $upline_id); }
                            debug_log('$upline_id details during plan upgrade after');
                            debug_log('$upline_id ' . $upline_id);
                            
                            if(strlen($upline_id) > 2)
                            {
                              $update_queue = "UPDATE crowdfund_queue SET level1 = $upline_id WHERE userid = ".$upd->id." and pid =".$upd->signup_package;
                              $this->db->query($update_queue);
                              debug_log($this->db->last_query());   
                            }
                            else
                            {
                              $update_queue = "UPDATE crowdfund_queue SET level1 = ".config_item('top_id')." WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                              $this->db->query($update_queue);
                              debug_log($this->db->last_query());   
                            }
                        }
                    }
                }
                }
                 #giftlevel+1 update if there is unlimited cycle is not null
                elseif (config_item('unlimited_cycle_level')!='')
                {
                #Here no need to include completed status since we are updating completed after this
                 $income_count = $this->db->query("select count(distinct userid) as count from transaction where to_userid = ".$upd->id." and secret = ".$cs->id." and status = 'Completed' and remarks like '%Level ".$upd->gift_level." Member Fee%'")->result_array()[0]['count'];
                     debug_log($this->db->last_query());
                    debug_log('income_count ' . $income_count);
                    debug_log('unlimited_cycle_level '.config_item('unlimited_cycle_level'));

                if($income_count ==  pow($pd->max_width, config_item('unlimited_cycle_level')))
                {
                    $ucs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$upd->signup_package, 'upgrade_type'=>($upd->gift_level+1)));
                    $ccs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$upd->signup_package, 'upgrade_type'=>($upd->gift_level)));

                    if($upd->id==config_item('top_id')){
                        $update_level = "UPDATE member SET gift_level =".($upd->gift_level+1)." , last_upgrade = ".time()." WHERE id = $upd->id";
                        $this->db->query($update_level);
                        debug_log($this->db->last_query());
                    }

                    elseif((!empty($ucs->id)) && (strval($ucs->admin_charge)==0) && (strval($ucs->sponsor_fee)==0)){

                        $uppd = $this->db_model->select_multi('*', 'plans', array('id' => $upd->signup_package));

                         #Assign cycle upline member only if there is config cycle level is not null
                            if (config_item('cycle_level')!='') {
                                 $upline_id = $this->plan_model->cycle_upline($upd,$uppd,$ucs);
                                  debug_log('$upline_id ' . $upline_id);
                            }
                #Assign unlimited cycle upline member only if there is  config unlimited_cycle level is not null
                            elseif (config_item('unlimited_cycle_level')!=''){
                                $upline_id = $this->plan_model->unlimited_cycle_upline($upd,$uppd,$ucs);
                                debug_log('$upline_id ' . $upline_id);
                            }
                #Assign upline member only if there is  config cycle and config unlimeted cycle is  null
                            else{
                                $upline_id = $this->plan_model->crowdfund_upline_new($upd,$uppd,$ucs);
                                debug_log('$upline_id ' . $upline_id); }
                
                        debug_log('$upline_id ' . $upline_id);
                        if(strlen($upline_id) > 2)
                        {
                          $update_queue = "UPDATE crowdfund_queue SET level".($upd->gift_level+1)." = $upline_id WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                          $this->db->query($update_queue);
                          debug_log($this->db->last_query());   
                        }
                        else
                        {
                          $update_queue = "UPDATE crowdfund_queue SET level".($upd->gift_level+1)." = ".config_item('top_id')." WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                          $this->db->query($update_queue);
                          debug_log($this->db->last_query());   
                        }    
                    }
                    elseif(($ccs->plan_upgrade=='Yes')&&($ccs->plan_new_id>0)){

                        $update_upline = "UPDATE member SET ".$upd->placement_leg."=0 where id = ".$upd->position;

                        $update_plan = "UPDATE member SET gift_level = 0 , position ='', signup_package=".$ccs->plan_new_id.", total_downline = 0, total_active=0, A=0, B=0,C=0,D=0,E=0, F=0, G=0, status = 'Inactive' WHERE id = ".$upd->id;
                        $this->db->query($update_plan);
                        debug_log($this->db->last_query());

                        $data = array('userid' => $upd->id,'pid'=>$ccs->plan_new_id);
                        $this->db->insert('crowdfund_queue', $data);   
                        debug_log($this->db->last_query());

                        $multi_array = $this->db->query('select id from member where position = '.$upd->id)->result_array();

                        if(count($multi_array)>0){
                            $iterator_array = new RecursiveIteratorIterator(new RecursiveArrayIterator($multi_array));
                            $flat_array = array();

                            foreach($iterator_array as $v) {
                                array_push($flat_array, $v);
                            }
                            $ids = implode(',',$flat_array);

                            $update_position = "UPDATE member SET position = 0 WHERE id IN (" .$ids .")";
                            $this->db->query($update_position);
                            debug_log($this->db->last_query()); 
                        }                         

                        $upd = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));

                        $ncs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$ccs->plan_new_id, 'upgrade_type'=>1));
                        $npd = $this->db_model->select_multi('*', 'plans', array('id' => $ccs->plan_new_id));

                        if((!empty($ncs->id)) && (strval($ncs->admin_charge)==0) && (strval($ncs->sponsor_fee)==0)){
                            debug_log('$upline_id details during plan upgrade before');
                             #Assign cycle upline member only if there is config cycle level is not null
                            if (config_item('cycle_level')!='') {
                                 $upline_id = $this->plan_model->cycle_upline($upd,$npd,$ncs);
                                  debug_log('$upline_id ' . $upline_id);
                            }
                #Assign unlimited cycle upline member only if there is  config unlimited_cycle level is not null
                            elseif (config_item('unlimited_cycle_level')!=''){
                                $upline_id = $this->plan_model->unlimited_cycle_upline($upd,$npd,$ncs);
                                debug_log('$upline_id ' . $upline_id);
                            }
                #Assign upline member only if there is  config cycle and config unlimeted cycle is  null
                            else{
                                $upline_id = $this->plan_model->crowdfund_upline_new($upd,$npd,$ncs);
                                debug_log('$upline_id ' . $upline_id); }
                            debug_log('$upline_id details during plan upgrade after');
                            debug_log('$upline_id ' . $upline_id);
                            
                            if(strlen($upline_id) > 2)
                            {
                              $update_queue = "UPDATE crowdfund_queue SET level1 = $upline_id WHERE userid = ".$upd->id." and pid =".$upd->signup_package;
                              $this->db->query($update_queue);
                              debug_log($this->db->last_query());   
                            }
                            else
                            {
                              $update_queue = "UPDATE crowdfund_queue SET level1 = ".config_item('top_id')." WHERE userid = ".$upd->id." and pid = ".$upd->signup_package;
                              $this->db->query($update_queue);
                              debug_log($this->db->last_query());   
                            }
                        }
                    }
                }
                }

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Payment is Approved.</div>');
                redirect('member/confirmed_deposit');                 

           }
           else
           {
                $get_fund = $this->db_model->select('balance', 'wallet', array('userid' => $td->userid));
                $new_fund = $get_fund + $td->amount;
                $array = array(
                        'balance' => $new_fund,
                    );
                $this->db->where('userid', $td->userid);
                $this->db->update('wallet', $array);
                wallet_log($this->db->last_query());
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Approved successfully.</div>'); 
           }
           
            $array = array(
                'time'           => time(),
                'status'         => "Completed",
            );
            $this->db->where(array('id'=>$id));
            $this->db->update('transaction', $array);
        }

        redirect('member/view-earning');

    }
    public function reject($id)
    {
       $user_id=$this->db_model->select('userid', 'transaction', array('id' => $id));
       
       $array = array(
            'time'           => time(),
            'status'         => "Failed",
          );
        $this->db->where(array('id'=>$id));
        $this->db->update('transaction', $array);
      
        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Bank Payment request rejected.</div>');
        redirect('member/approve_deposit');

    }

     public function approve_deposit()
    {
        $data['title']      = 'Approve Bank Payments';
        $data['breadcrumb'] = 'Approve Bank Payments';
        $data['layout']     = 'wallet/approve_deposit.php';
        $this->load->view(config_item('member'), $data);
    }

    public function confirmed_deposit()
    {
        $data['title']      = 'Confirmed Bank Payments';
        $data['breadcrumb'] = 'Confirmed Bank Payments';
        $data['layout']     = 'wallet/confirmed_deposit.php';
        $this->load->view(config_item('member'), $data);
    }

     public function online_deposit()
    {
        $amount= $this->uri->segment('3') ? $this->uri->segment('3') : $this->input->post('amount');
        $to_userid= $this->uri->segment('4') ? $this->uri->segment('4') : 'Admin';

        if (!isset($_POST['amount']) && $amount=='') {
            $data['title'] = 'Deposit Through Payment Gateway';
            $data['layout'] = 'wallet/online_deposit.php';
            $this->load->view(config_item('member'), $data);
        } 
        else{
            if (config_item('enable_pg') == "Yes") 
            {
                $user_data = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));
                $this->session->set_userdata('_user_id_', $this->session->user_id);
                $this->session->set_userdata('_user_name_', $this->session->name);
                $this->session->set_userdata('_inv_id_', rand());
                $this->session->set_userdata('_sponsor_', $user_data->sponsor);
                $this->session->set_userdata('_address_', $user_data->address);
                $this->session->set_userdata('_email_', $user_data->email);
                $this->session->set_userdata('_phone_', $user_data->phone);
                $this->session->set_userdata('_product_', 'Add Wallet Fund');
                $this->session->set_userdata('_price_', $amount);
                $this->session->set_userdata('_type_', 'wallet');
                $this->session->set_userdata('_coin_', $this->input->post('coin_wallet'));
                $this->load->config('pg');

                $this->earning->insert_into_transaction($this->session->user_id);
                redirect('gateway/payment_gateway');
            }
            else 
            {
             redirect(site_url('member/bank_deposit'));
            }
        }
    }

    public function failed_fund()
    {
        $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>Your payment is not completed. So your fund was not added.If fund is already added ignore this message.</div>");
        redirect(site_url('member/online_transactions'));
    }

    public function complete_add_fund()
    {
        $td = $this->db_model->select_multi("*", 'transaction', array(
            'userid' => $this->session->_user_id_,'amount'=>$this->session->_price_, 'transaction_id !=' => '', 'time >=' =>strtotime('now') - 3600));
        debug_log('complete_add_fund');
        debug_log($this->db->last_query());

        if(($td->status != 'Completed') && ($td->gateway != 'Coinpayments.net') && ($td != '')){
            $wallet_balance = $this->db_model->select('balance', 'wallet', array('userid' => $this->session->user_id));
            $this->db->where(array('userid' => $this->session->user_id));
            $this->db->update('wallet', array('balance' => $wallet_balance + $this->session->_price_));
            wallet_log($this->db->last_query());

            $array = array(
                'amount'         => $this->session->_price_,
                'status'         => 'Completed'
            );
            $this->db->where(array('id' => $td->id));
            $this->db->update('transaction', $array);
            //debug_log($this->db->last_query());
            
            $array = array(
                'transfer_from' => 'Admin',
                'transfer_to'   => $this->session->_user_id_,
                'amount'        => $this->session->_price_,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => 'Online Wallet Topup',
            );
            $this->db->insert('transfer_balance_records', $array);
            //debug_log($this->db->last_query());

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund is added to your wallet.</div>');
            redirect(site_url('member/online_transactions'));
        } else if($td->status == 'Completed'){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund is added to your wallet.</div>');
            redirect(site_url('member/online_transactions'));
        } else {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your Transaction Under Process. Please check after some time</div>');
            redirect(site_url('member/online_transactions'));
        }
    }


    public function my_rewards()
    {
        $config['base_url'] = site_url('member/my_rewards');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('rewards', array('userid' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, reward_id, date, paid_date, tid')->from('rewards')
            ->where('userid', $this->session->user_id)->limit($config['per_page'], $page);

        $data['rewards'] = $this->db->get()->result_array();

        $data['title'] = 'My Rewards';
        $data['layout'] = 'income/rewards.php';
        $this->load->view(config_item('member'), $data);

    }

    public function search_earning()
    {
        $data['title'] = 'Search Income';
        $data['layout'] = 'income/search_income.php';
        $this->load->view(config_item('member'), $data);
    }

    public function income_search()
    {   
        $type = $this->uri->segment(3) !='' ? $this->uri->segment(3) : $this->input->post('income_name');
        $pair_names = str_replace('%20',' ',$this->uri->segment(4));
        $startdate = $this->input->post('startdate');
        $enddate = $this->input->post('enddate');

        $this->db->select('id, userid, amount, type, ref_id, date, pair_match,pair_names')->from('earning');
        if ($type !== "All") {
            $this->db->like('type', $type,'both');
        }
        if($pair_names != ''){
            $this->db->like('pair_names', $pair_names,'both');   
        }
        $this->db->where('userid', $this->session->user_id);
        if (trim($startdate) !== "") {
            $this->db->where('date >=', $startdate);
        }
        if (trim($enddate) !== "") {
            $this->db->where('date <=', $enddate);
        }

        $this->db->order_by('id','DESC');

        $data['earning'] = $this->db->get()->result_array();
        $data['title'] = 'Search Results';
        $data['layout'] = 'income/view_earning.php';
        $this->load->view(config_item('member'), $data);

    }

    public function settings()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repass', 'Retype Password', 'trim|required|matches[newpass]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Change Password';
            $data['layout'] = 'profile/acsetting.php';
            $this->load->view(config_item('member'), $data);
        } else
          {
               if($this->input->post('oldpass') && $this->input->post('newpass'))
               {

                 $mypass = $this->db_model->select('password', 'member', array('id' => $this->session->user_id));

                    if (password_verify($this->input->post('oldpass'), $mypass) == true)
                          {
                        $array = array(
                            'password' => password_hash($this->input->post('newpass'), PASSWORD_DEFAULT),
                        );
                        $this->db->where('id', $this->session->user_id);
                        $this->db->update('member', $array);

                       if(config_item('ecomm_theme')=='gmart'){
                        $this->db->query(
                        'update tbl_users 
                        SET affiliate_password="'.$this->input->post('newpass').'" WHERE user_id = "'.$this->session->user_id.'"' );
                        }

                        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Login Password Updated Successfully!!.</div>');
                        redirect('member/settings');
                    }
                    else
                    {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                        redirect('member/settings');
                    }
               }
          }
    }

    public function settings_secure()
    {
        $this->form_validation->set_rules('oldsecure', 'Secure Password', 'trim|required');
        $this->form_validation->set_rules('newsecure', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repasssecure', 'Retype Password', 'trim|required|matches[newsecure]');

        if ($this->form_validation->run() == false)
        {
            $data['title'] = 'Change Password';
            $data['layout'] = 'profile/acsetting.php';
            $this->load->view(config_item('member'), $data);
        }
        else
        {
          if($this->input->post('oldsecure') && $this->input->post('newsecure')){
                $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

                if (password_verify($this->input->post('oldsecure'), $mypass) == true)
                {

                    $array = array(
                        'secure_password' => password_hash($this->input->post('newsecure'), PASSWORD_DEFAULT),
                    );
                    $this->db->where('id', $this->session->user_id);
                    $this->db->update('member', $array);
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Secure Password Updated Successfully.</div>');
                    redirect('member/settings');
                }
                else
                {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                    redirect('member/settings');
                }
           }

        }
    }

    public function reset_secure()
    {
        $user_id = trim($this->input->post('userid'));
        $phone = trim($this->input->post('phone'));
        $email = trim($this->input->post('email'));

        $data = $this->db_model->select_multi("name, password, phone,email", 'member', array('id' => $this->session->user_id));

        if(((!(strlen($phone)>2)) && (!(strlen($email)>2))) || ((password_verify($this->input->post('password'), $data->password) != true))){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Invalid details. Please Enter Valid Details.<br> 3 Consecutive Incorrect Attempts will block your account !!!</div>');
                  redirect(site_url('member/settings'));
        }

        if ((trim(config_item('smtp_host')) !== "") || (config_item('sms_on_join') == "Yes")) {
            
            if((strlen($phone)>2)&&($phone == $data->phone))
            {
                $randompassword=$this->common_model->randomPassword();
                $password = password_hash($randompassword, PASSWORD_DEFAULT);
                $data2 = array(
                      'secure_password' => $password,
                      'last_login_ip' => $this->input->ip_address(),
                      'last_login' => time(),
                  );
                  $this->db_model->update($data2, 'member', array('id' => $user_id));

                  $sms = "Hello " . $data->name . ", \nYou have requested for Secure Password Reset. \n Your Temporary Secure Password is: " . $randompassword . "\n".config_item('company_name');
                  $messvar="Ok";
                  $phone="91".$phone;
                  $this->common_model->sms($phone, urlencode($sms));

                  $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Success - Temporary Secure password is sent to your registered Phone Number. </div>');
                  redirect('member/settings');
            }
            elseif ((strlen($email)>2) && ($email == $data->email)) 
            {
                $randompassword=$this->common_model->randomPassword();
                $password = password_hash($randompassword, PASSWORD_DEFAULT);
                
                $sub = "Secure Password Reset";
                $msg = "Hello " . $data->name . ", <br><br>You have requested for Secure Password Reset. <br><br> Temporary Secure Password is: " . $randompassword . "<br><br>Kindly update password soon after login <br><br> Regards <br>Support Team<br>".config_item('company_name');
                $status = $this->db_model->mail($data->email, $sub, $msg);

                debug_log('Email Status '.$status);

                if($status == 'Success')
                {
                    $data2 = array(
                      'secure_password' => $password,
                      'last_login_ip' => $this->input->ip_address(),
                      'last_login' => time(),
                    );
                    $this->db_model->update($data2, 'member', array('id' => $user_id));    

                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Success - Temporary Secure password is sent to your registered Email. </div>');
                    redirect('member/settings');
                }
                else
                {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Password couldnot reset at the moment. Please try later !!!</div>');
                    redirect('member/settings');
                }
            }
            else
            {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Invalid Details. Please Enter Correct Details !!!</div>');
              redirect('member/settings');
            }
        }
        else
        {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Password couldnot reset at the moment. Please try later !!!</div>');
            redirect('member/settings');
        }


    }
    
    public function profile()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('*', 'member', array('id' => $this->session->user_id));
            $data['title'] = 'My Profile';
            $data['layout'] = 'profile/profile.php';
            $this->load->view(config_item('member'), $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {

                if($this->input->post('date_of_birth') > date('Y-m-d')){
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>You can't enter future date as Date of Birth</div>");
                redirect(site_url('member/profile'));
                }
                
                if (trim($_FILES['photo']['name'] !== "")) {
                        $this->load->library('upload');
                        if (!$this->upload->do_upload('photo')) {
                            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Photo is not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                            redirect('member/profile');
                        } else {
                            //echo "image_data";
                            $image_data = $this->upload->data();
                            $photo = $this->session->user_id .".".explode(".",$image_data['file_name'])[1];
                            //print_r($image_data);
                            unlink('uploads/profile/'.$photo);
                            move_uploaded_file($_FILES['photo']['tmp_name'], FCPATH . 'uploads/profile/'.$photo);
                            unlink('uploads/'.$image_data['file_name']);
                        }
                    }
                    //print_r($photo);die();
                     $array = array(
                    'email' => $this->input->post('my_email'),
                    'photo' => $photo,
                );
                $this->db->where('id', $this->session->user_id);
                $this->db->update('member', $array);

                $array = array(
                'address'          => $this->input->post('address'),
                'city'          => $this->input->post('city'),
                'state'          => $this->input->post('state'),
                'zip'          => $this->input->post('zip'),
                'date_of_birth'    => $this->input->post('date_of_birth'),
                );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/profile');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/profile');
            }
        }
    }

    public function shipping_address()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('s_name,s_phone,s_email,s_city,s_state,s_zipcode,s_address', 'shipping_address', array('userid' => $this->session->user_id));

            $data['title'] = 'Update Shipping Address';
            $data['layout'] = 'profile/shipping_address.php';
            $this->load->view(config_item('member'), $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {
                    $array = array(
                    'userid'=>$this->session->user_id,
                    's_name' =>$this->input->post('my_name'),
                    's_phone' =>$this->input->post('my_phone'),
                    's_email' => $this->input->post('my_email'),
                    's_city'  => $this->input->post('my_city'),
                    's_state'  => $this->input->post('my_state'),
                    's_address' => $this->input->post('my_address'),
                    's_zipcode'  => $this->input->post('my_zipcode'),
                    );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('shipping_address', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Shipping Address Updated Successfully.</div>');
                redirect('member/shipping_address');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/shipping_address');
            }
        }
    }

    public function billing_address()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('b_name,b_phone,b_email,b_city,b_state,b_zipcode,b_address', 'shipping_address', array('userid' => $this->session->user_id));
            
            $data['title'] = 'Update Billing Address';
            $data['layout'] = 'profile/billing_address.php';
            $this->load->view(config_item('member'), $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {
                    $array = array(
                    'b_name' =>$this->input->post('my_name'),
                    'b_phone' =>$this->input->post('my_phone'),
                    'b_email' => $this->input->post('my_email'),
                    'b_city'  => $this->input->post('my_city'),
                    'b_state'  => $this->input->post('my_state'),
                    'b_address' => $this->input->post('my_address'),
                    'b_zipcode'  => $this->input->post('my_zipcode'),
                    );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('shipping_address', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Billing Address Updated Successfully.</div>');
                redirect('member/billing_address');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/billing_address');
            }
        }
    }

    public function kyc()
    {
        $this->form_validation->set_rules('tax_no', 'PAN Number', 'trim|required');
        //$this->form_validation->set_rules('beneficiary_name', 'Beneficiary name', 'trim|required');
        $this->form_validation->set_rules('bank_ac_no', 'Account Number', 'trim|required');
        $this->form_validation->set_rules('bank_ifsc', 'IFSC code', 'trim|required');
        $this->form_validation->set_rules('bank_branch', 'Bank Branch', 'trim|required');
        $this->form_validation->set_rules('confirm_bank_ac_no', 'Retype Bank Account Number', 'trim|required|matches[bank_ac_no]');
        $this->form_validation->set_rules('accounttype', 'Account Type', 'trim|required');
        //$this->form_validation->set_rules('aadhar_no', 'Aadhar Number', 'max_length[12]|min_length[12]');

        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        $data['vdata']=$this->db_model->select_multi('video,photo', 'member', array('id' => $this->session->user_id));
        //print_r($data['vdata']);die();
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email,video', 'member', array('id' => $this->session->user_id));
            $data['title'] = 'KYC Compliance';
            $data['layout'] = 'profile/kyc.php';
            $this->load->view(config_item('member'), $data);
        }

        else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $aadharcard = '';
                $pancard = '';
                $cancelledcheque ='';
                if (trim($_FILES['pancard']['name'] != "")) {

                    $this->load->library('upload');

                    if (!$this->upload->do_upload('pancard')) {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">PAN card not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('member/kyc');
                    } else {
                        $image_data = $this->upload->data();
                        $pancard = $this->session->user_id ."_pancard.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['pancard']['tmp_name'], FCPATH . 'uploads/kyc/'.$pancard);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'id_proof' => $pancard,
                            'status' => "Pending"
                        );
                        $this->db->where('userid', $this->session->user_id);
                        $this->db->update('member_profile', $array);

                    }
                }

                if (trim($_FILES['aadharcard']['name'] != "")) {

                    $this->load->library('upload');

                    if (!$this->upload->do_upload('aadharcard')) {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Address Proof not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('member/kyc');
                    } else {
                        $image_data = $this->upload->data();
                        $aadharcard = $this->session->user_id ."_aadharcard.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['aadharcard']['tmp_name'], FCPATH . 'uploads/kyc/'.$aadharcard);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'add_proof' => $aadharcard,
                            'status' => "Pending"
                        );
                        $this->db->where('userid', $this->session->user_id);
                        $this->db->update('member_profile', $array);
                    }
                }
                if (trim($_FILES['cheque']['name'] != "")) {
                    
                    $this->load->library('upload');

                    if (!$this->upload->do_upload('cheque')) {

                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Cancelled Cheque not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('member/kyc');
                    } else {

                        $image_data = $this->upload->data();
                        $cancelledcheque = $this->session->user_id ."_cancelledcheque.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['cheque']['tmp_name'], FCPATH . 'uploads/kyc/'.$cancelledcheque);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'cheque' => $cancelledcheque,
                            'status' => "Pending"
                        );
                        $this->db->where('userid', $this->session->user_id);
                        $this->db->update('member_profile', $array);
                    }
                }

                #####Upload video of the registering user########
                $configVideo['upload_path'] = 'uploads/profile'; # check path is correct
                $configVideo['max_size'] = '102400';
                $configVideo['allowed_types'] = 'mp4'; # add video extenstion on here
                $configVideo['overwrite'] = FALSE;
                $configVideo['remove_spaces'] = TRUE;
                if(trim($_FILES['vupload']['name'] !== "")) 
                {
                  //debug_log($_FILES['vupload']['name']);
                  $this->load->library('upload', $configVideo);
                  if (!$this->upload->do_upload('vupload')) 
                  {
                    $this->upload->display_errors();
                    //debug_log('upload failed');
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Video is not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('member/kyc');  
                  } 
                  else 
                  {
                    $video_data = $this->upload->data();
                    $video = $this->session->user_id ."_video.".explode(".",$video_data['file_name'])[1];
                    unlink('uploads/profile/'.$video);
                    move_uploaded_file($_FILES['vupload']['tmp_name'], FCPATH . 'uploads/profile/'.$video);
                    unlink('uploads/'.$video_data['file_name']);

                    $array=array(
                        'video'=>$video,
                    );
                    $this->db->where('id', $this->session->user_id);
                    $this->db->update('member', $array);

                  }
                }
                ##################End of Uploading video of registering User############

                $array = array(
                    'tax_no' => $this->input->post('tax_no'),
                    'aadhar_no' => $this->input->post('aadhar_no'),
                    //'beneficiary_name' => $this->input->post('beneficiary_name'),
                    'bank_ac_no' => $this->input->post('bank_ac_no'),
                    'bank_name' => $this->input->post('bank_name'),
                    'bank_ifsc' => $this->input->post('bank_ifsc'),
                    'bank_branch' => $this->input->post('bank_branch'),
                    'account_type' => $this->input->post('accounttype'),
                    'status' => "Pending"
                );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);                

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/kyc');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                redirect('member/kyc');
            }
        }
    }

    public function bankdetails()
    {
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
        $this->form_validation->set_rules('bank_ac_no', 'Bank Account Number', 'trim|required');
        $this->form_validation->set_rules('confirm_bank_ac_no', 'Retype Bank Account Number', 'trim|required|matches[bank_ac_no]');
        if(strtolower(config_item('company_country'))=='india') {
        $this->form_validation->set_rules('bank_ifsc', 'IFCS Code', 'trim|required');
        }
        $this->form_validation->set_rules('bank_branch', 'Bank Branch', 'trim|required');
        $this->form_validation->set_rules('account_type', 'Account Type', 'trim|required');

        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'member', array('id' => $this->session->user_id));
            $data['title'] = 'Financial Details';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view(config_item('member'), $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    //'beneficiary_name' => $this->input->post('beneficiary_name'),
                    'bank_ac_no' => trim($this->input->post('bank_ac_no')),
                    'bank_name' => trim($this->input->post('bank_name')),
                    'bank_ifsc' => trim($this->input->post('bank_ifsc')),
                    'bank_branch' => trim($this->input->post('bank_branch')),
                    'account_type' => $this->input->post('account_type'),
                    );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);

                #debug_log('Affected Rows '.$this->db->affected_rows());
                #debug_log($this->db->last_query());

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/bankdetails');
            }
        }
    }
    public function nominee_details()
    {
        $this->form_validation->set_rules('nominee_name', 'Nominee Name', 'trim|required');
        $this->form_validation->set_rules('nominee_add', 'Nominee Address', 'trim|required');
        $this->form_validation->set_rules('nominee_relation', 'Nominee Relation', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'member', array('id' => $this->session->user_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view(config_item('member'), $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'nominee_name' => $this->input->post('nominee_name'),
                    'nominee_add' => $this->input->post('nominee_add'),
                    'nominee_relation' => $this->input->post('nominee_relation'),
                    'nominee_phone' => $this->input->post('nominee_phone'),
                );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/bankdetails');
            }
        }
    }
    public function upi()
    {
        $this->form_validation->set_rules('upi', 'upi', 'trim|required');
        
        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'member', array('id' => $this->session->user_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view(config_item('member'), $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'googlepay_no' => $this->input->post('googlepay_no'),
                    'phonepay_no' => $this->input->post('phonepay_no'),
                    'upi_id' => $this->input->post('upi'),
                    'btc_address' => $this->input->post('btc_address'),

                );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/bankdetails');
            }
        }
    }
    public function blockchain()
    {
        $this->form_validation->set_rules('btc_address', 'btc_address', 'trim|required');
        
        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'member', array('id' => $this->session->user_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view(config_item('member'), $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'btc_address' => $this->input->post('btc_address'),
                );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('member_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('member/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/bankdetails');
            }
        }

    }

    public function welcome_letter()
    {
        $data['file_data'] = file_get_contents(FCPATH . "uploads/welcome_letter.txt");
        $data['title'] = 'Welcome Letter';
        $data['layout'] = "profile/welcome_letter.php";
        $this->load->view(config_item('member'), $data);
    }

    /*public function topup()
    {
        $epin_value = $this->db_model->select('amount', 'epin', array(
            'epin' => trim($this->input->post('topup')),
            'status' => 'Un-used',
        ));
        $this->load->model('earning');
        if (config_item('fix_income') == "Yes" && $epin_value > 0 && config_item('give_income_on_topup') == "Yes") {
            $this->earning->fix_income($this->session->user_id, $this->db_model->select('sponsor', 'member', array('id' => $this->session->user_id)), $epin_value);
        } else if (config_item('fix_income') !== "Yes" && $epin_value > 0 && config_item('give_income_on_topup') == "Yes") {

            $this->earning->credit_direct_referral_income(
                $this->session->user_id,
                $this->db_model->select('sponsor', 'member', array('id' => $this->session->user_id)),
                $this->db_model->select('signup_package', 'member', array('id' => $this->session->user_id)),
                false
            );
        }
        if ($epin_value > 0) {
            $data = array(
                'topup' => $epin_value,
            );
            $this->db->where('id', $this->session->user_id);
            $this->db->update('member', $data);

            $data = array(
                'status' => 'Used',
                'used_by' => $this->session->user_id,
                'used_time' => date('Y-m-d H:i:s'),
            );
            $this->db->where('epin', trim($this->input->post('topup')));
            $this->db->update('epin', $data);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Top-uped your account.</div>');
            redirect(site_url('member'));
        } else {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered e-PIN is not valid or used.</div>');
            redirect(site_url('member'));
        }
    }
    */
    
    public function my_invoices()
    {
        $config['base_url'] = site_url('member/my_invoices');
        $config['per_page'] = 50;
        $config['total_rows'] = $this->db_model->count_all('invoice', array(
            'userid' => $this->session->fran_id,
            'user_type' => 'Franchisee',
        ));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('invoice')->where(array(
            'userid' => $this->session->user_id,
            'user_type' => 'Member',
        ))->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $data['invoice'] = $this->db->get()->result();
        $data['title'] = 'My Invoices';
        $data['breadcrumb'] = 'My Invoices';
        $data['layout'] = 'invoice/my_invoices.php';
        $this->load->view(config_item('member'), $data);
    }

    public function invoice_view($id)
    {
        $data['result'] = $this->db_model->select_multi('*', 'invoice', array('id' => $id));
        $this->load->view('member/invoice/print_invoice.php', $data);
    }

    public function tax_report()
    {
        $top_id = $this->common_model->filter($this->session->user_id);
        //debug_log($top_id);
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");
        if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
            redirect('member/tax_report/');
        }
        else
        {
            $data['title']      = 'Tax Report';
            $data['breadcrumb'] = 'Tax Report';
            $data['layout']     = 'income/tax_report.php';
            $this->db->select('*')->where(array(
                    'userid'=>$top_id, 'payout_id !=', '', 'date >=' => $sdate, 'date <=' => $edate,))->order_by('id', 'desc');
            $data['result']     = $this->db->get('tax_report')->result_array();
            $this->load->view(config_item('member'), $data);
            //redirect(site_url('member/tax_report/' . $top_id . '/' . $sdate . '/' . $edate));    
        }
    }

    public function online_transactions()
    {
        $config['base_url']   = site_url('member/online_transactions');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('transaction');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['trans']    = $this->db->get('transaction')->result();
        $data['title']      = 'Wallet Deposits';
        $data['breadcrumb'] = 'Wallet Deposits';
        $data['layout']     = 'misc/online_transactions.php';
        $this->load->view(config_item('member'), $data);
    }

    public function upgrade_id($pid,$level_id)
    {
      $userid = $this->session->user_id;
      $res = $this->db_model->select_multi('*', 'level_wise_income', array('id' => $level_id));
      $get_user_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));

      if($this->db_model->select('new_id', 'member', array('id' =>$this->session->user_id)) > 0){
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You already upgraded and canot upgrade again !!!</div>');
          redirect(site_url('member'));
      }

      if($get_user_balance < $res->upgrade){
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Your Wallet Balance is Low. Please Topup !!!</div>');
          redirect(site_url('member')); 
      }

      $this->session->set_userdata('_id_upgrade_', 'Yes');
      
      #$status = $this->registration_model->register_new($userid, $pid);
      $status = $this->registration_model->upgrade_member($userid, $pid);

      if($status == 200) {

          $username = $this->db_model->select('name', 'member', array('id' => $userid,));
          $this->earning->pay_earning('admin', $userid, 'Level Upgrade Fee', 'Level ' . $res->level_no . ' Upgrade Fee From - '.$username, $res->upgrade, '', $res->id);

          $this->earning->add_deduction($userid,'admin',$res->upgrade,'Level ' . $res->level_no . ' Upgrade Fee','Level ' . $res->level_no . ' Upgrade Fee',$res->id,'account_transfer','account_transfer');
          
          //debug_log('$get_user_balance ' . $get_user_balance);
          $arra = array('balance' => ($get_user_balance - $res->upgrade),);
          $this->db->where('userid', $userid);
          $this->db->update('wallet', $arra);
          wallet_log($this->db->last_query());

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">You successfully upgraded !!!</div>');
          redirect(site_url('member'));
      }
      else {
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some error occured while registering. please contact admin or try again.</div>');
          redirect(site_url('member'));
      }
    }


    public function activate()
    {
        $uid = $this->session->user_id;
        $md = $this->db_model->select_multi('*', 'member', array('id' => $uid));
        $pd = $this->db_model->select_multi('*', 'plans', array('id' => $md->signup_package));

        if($md->status == 'Active'){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Member Account is already Active</div>');
             redirect('member');   
        }

        $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
        
        if ($get_fund_uid < $pd->joining_fee) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You donot have sufficient balance in your wallet.</div>');
            redirect('member');
        }

        $array = array(
            'balance' => ($get_fund_uid - $pd->joining_fee),
        );
        $this->db->where('userid', $uid);
        $this->db->update('wallet', $array);
        wallet_log($this->db->last_query());

        $this->earning->add_deduction($uid, 'admin', $pd->joining_fee, 'Account Activation', 'Account Activation',$md->signup_package, 'Account Transfer', '');

        if(!$md->position>0){

          $status = $this->plan_model->get_leg_position($pd, $md->sponsor, $md->placement_leg, $md->position);

          if($status ==400){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Error Activating the User</div>');
              redirect('member');
          } else {
            $position = $status['position'];
            $placement_leg = $status['leg'];
          }

          if(!$position >0){
            //debug_log('Error getting the position');
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Error Activating the User</div>');    
            redirect('member');
          }

          if($md->role != 'customer'){
                if(($position == config_item('top_id')) && ($this->db_model->count_all('member', array('position' => config_item('top_id'))) <6)){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                } else if($position != config_item('top_id')){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                }
          }

          $data   = array(
              'position' => $position,
              'placement_leg' => $placement_leg,
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
          );
          $this->db->where('id', $md->id);
          $this->db->update('member', $data);

          $md = $this->db_model->select_multi('*', 'member', array('id' => $md->id));

          $this->registration_model->Update_after_position($md, $pd);

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');
          redirect('member');    

        } else{
          $data   = array(
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
              );
          $this->db->where('id', $md->id);
          $this->db->update('member', $data);

          $md = $this->db_model->select_multi('*', 'member', array('id' => $md->id));
          //$this->common_model->update_total_downline_id($md->id, $md->status);

          if ((config_item('joining_product') == 'Yes') && (config_item('make_join_product_entry') == "Yes") && ($md->status == 'Active'))
          {
            if($this->session->userdata('_id_upgrade_')!='Yes'){
                    $array = array(
                    'product_id' => 0,
                    'name'       => $pd->invoice_name,
                    'userid'     => $md->id,
                    'qty'        => 1,
                    'cost'       => $pd->joining_fee,
                    'date'       => date('Y-m-d H:i:s'),
                    'deliver_date'  => date('Y-m-d H:i:s'),
                    'status'     => "Completed",
                    'payment'    => "Registration Purchase",
                );

                $this->db->insert('product_sale', $array);

                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, $this->db->insert_id());    
            }
            $this->earning->credit_joining_commission($pd,$md);
          }
          else if((config_item('joining_product') == 'Yes') && (config_item('make_join_product_entry') == "No") && ($md->status == 'Active'))
          {
            if($this->session->userdata('_id_upgrade_')!='Yes'){
                $array = array(
                    'product_id' => 0,
                    'name'       => $pd->invoice_name,
                    'userid'     => $md->id,
                    'qty'        => 1,
                    'cost'       => $pd->joining_fee,
                    'date'       => date('Y-m-d H:i:s'),
                    'payment'    => "Registration Purchase",
                );

                $this->db->insert('product_sale', $array);
                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, $this->db->insert_id());
            }
          }
          else if($md->status == 'Active'){
              $this->earning->credit_joining_commission($pd,$md);
              if($this->session->userdata('_id_upgrade_')!='Yes'){
                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, 0);
              }
          }

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');
          redirect('member');    
        }

    }


    public function crowd_activate($uid,$mp='')
    {
        $md = $this->db_model->select_multi('*', 'member', array('id' => $uid));
        $pd = $this->db_model->select_multi('*', 'plans', array('id' => $md->signup_package));

        if($md->status == 'Active'){
            return 200;
        }

        if(!(strlen($md->position)>2)){

          if(strlen($mp)>2){
             #assign position as mp if there is config cycle level and unlimited cycle is null else assign position as sponsor
            $position  = ((config_item('cycle_level')=='')&&(config_item('unlimited_cycle_level')=='')) ? $mp : $md->sponsor;
            $l1_count = $this->plan_model->level_one_count($position, $pd);
            $placement_leg = 'A';
            $placement_leg = $l1_count == 1 ? 'B' : $placement_leg;
            $placement_leg = $l1_count == 2 ? 'C' : $placement_leg;
            $placement_leg = $l1_count == 3 ? 'D' : $placement_leg;
            $placement_leg = $l1_count == 4 ? 'E' : $placement_leg;
            $placement_leg = $l1_count == 5 ? 'F' : $placement_leg;
            $placement_leg = $l1_count == 6 ? 'G' : $placement_leg;

          }else{
              $status = $this->plan_model->get_leg_position($pd, $md->sponsor, $md->placement_leg, $md->position);

              if($status ==400){
                return 400;
              } else {
                $position = $status['position'];
                $placement_leg = $status['leg'];
              }

              if(!$position >0){
                return 500;
              }  
          }

          debug_log('Crowdfund Activate');
          debug_log('position '.$position);
          debug_log('leg '.$placement_leg);

          if($md->role != 'customer'){
                if(($position == config_item('top_id')) && ($this->db_model->count_all('member', array('position' => config_item('top_id'))) <6)){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                } else if($position != config_item('top_id')){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                }
           }

          $data   = array(
              'position' => $position,
              'placement_leg' => $placement_leg,
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
              );
          $this->db->where('id', $md->id);
          $this->db->update('member', $data);

          $md = $this->db_model->select_multi('*', 'member', array('id' => $md->id));

          $this->registration_model->Update_after_position($md, $pd);

          //$this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');
          //redirect('member');    
          return 200;

        } else{
          $data   = array(
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
              );
          $this->db->where('id', $md->id);
          $this->db->update('member', $data);

          $this->earning->credit_joining_commission($pd,$md);

          //$this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');
          //redirect('member');    
          return 300;
        }

    }

    public function club_members($level)
    {
        $campain_start_date = date('2020-08-03');

        $level = $this->uri->segment(3) != '' ? $this->uri->segment(3) : $level;

        $user_id = strlen($userid)>0 ? $userid : $this->session->user_id;

        //$md = $this->db_model->select_multi('*', 'member', array('id'=>$user_id));

        $this->db->select("t1.*, IFNULL(t2.cnt,0) as count, IFNULL(t3.tcnt,0) as total_count, IFNULL(t4.bcnt,0) as before_count")
                ->from('member as t1')
                ->where(array('status'=>'Active', 'id'=>$user_id))->order_by('secret', 'ASC')
                ->join("(SELECT sponsor as userid, count(sponsor) as cnt FROM member where 
                    activate_time >= '".$campain_start_date."' group by 1) as t2", 't1.id = t2.userid', 'LEFT')
                ->join("(SELECT sponsor as userid, count(sponsor) as tcnt FROM member group by 1) as t3", 't1.id = t3.userid', 'LEFT')
                ->join("(SELECT sponsor as userid, count(sponsor) as bcnt FROM member where 
                    activate_time < '".$campain_start_date."' group by 1) as t4", 't1.id = t4.userid', 'LEFT');

        $md =  $this->db->get()->result()[0];

        $p1_level = array(0=>0);
        $this->db->select('*')->from('level_wise_income')->where(array('plan_id' =>1))->order_by('level_no', 'ASC');
        $inc = $this->db->get()->result();

        foreach ($inc as $e){
            array_push($p1_level, $this->db_model->sum('direct', 'level_wise_income', array('level_no <=' => $e->level_no, 'plan_id'=>1)));
        }

        //debug_log($p1_level);

        $p2_level = array(0=>0);
        $this->db->select('*')->from('level_wise_income')->where(array('plan_id' =>2))->order_by('level_no', 'ASC');
        $inc = $this->db->get()->result();

        foreach ($inc as $e){
            array_push($p2_level, $this->db_model->sum('direct', 'level_wise_income', array('level_no <=' => $e->level_no, 'plan_id'=>2)));
        }

        $p4_level = array(0=>0);
        $this->db->select('*')->from('level_wise_income')->where(array('plan_id' =>4))->order_by('level_no', 'ASC');
        $inc = $this->db->get()->result();

        foreach ($inc as $e){
            array_push($p4_level, $this->db_model->sum('direct', 'level_wise_income', array('level_no <=' => $e->level_no, 'plan_id'=>4)));
        }

        if($level ==1){
            $secrets = $this->db_model->select('level'.$level, 'level_sponsor', array('userid' => $user_id,));
            $secrets = substr(substr($secrets, 1),0,-1);
            if($secrets != ''){
                if($md->signup_package==1){
                    if($md->before_count>0){
                        $data['members'] = $this->db->query("
                        select secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .")")->result_array();    
                    }else{
                        $this->db->query('SET @row_number = 0');
                        $data['members'] = $this->db->query("
                        select * FROM (select (@row_number:=@row_number + 1) AS num, secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .") order by activate_time ASC)t where num > ".$p1_level[$md->gift_level+1])->result_array();    
                    }
                    //debug_log($this->db->last_query());    
                }elseif($md->signup_package==2){
                    if($md->before_count>0){
                        $data['members'] = $this->db->query("
                        select secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .")")->result_array();    
                    }else{
                    $this->db->query('SET @row_number = 0');
                    $data['members'] = $this->db->query("
                    select * FROM (select (@row_number:=@row_number + 1) AS num, secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .") order by activate_time ASC)t where num >".$p2_level[$md->gift_level+1])->result_array();
                    }
                    //debug_log($this->db->last_query());    
                }else{
                    if($md->before_count>0){
                        $data['members'] = $this->db->query("
                        select secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .")")->result_array();    
                    }else{
                    $this->db->query('SET @row_number = 0');
                    $data['members'] = $this->db->query("
                    select * FROM (select (@row_number:=@row_number + 1) AS num, secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$secrets .") order by activate_time ASC)t where num >".$p4_level[$md->gift_level+1])->result_array();    
                    //debug_log($this->db->last_query());
                    }
                }
            }else{
                $data['members'] = array();
            }
        }else{
            $secrets = $this->db_model->select('level'.($level-1), 'level_sponsor', array('userid' => $user_id,));
            $secrets = substr(substr($secrets, 1),0,-1);

            //debug_log($secrets);

            $downline_secrets = '';

            foreach(explode(",",$secrets) as $secret)
            {   
                //debug_log($downline_secrets);
                //debug_log('\n');
                //debug_log($secret);
                if($secret != ''){
                    $userid = $this->db_model->select('id', 'member', array('secret'=>$secret));
                    //debug_log($userid);
                    $l1_secrets = $this->db_model->select('level1', 'level_sponsor', array('userid' => $userid,));
                    //debug_log($l1_secrets);
                    $l1_secrets = str_replace(' ', '',substr(substr($l1_secrets, 1),0,-1));
                    //debug_log($l1_secrets);

                    ////debug_log(strlen(trim($l1_secrets)));

                    if($l1_secrets != ''){
                        if(count(explode(",",$l1_secrets))==1){
                            $l1_secrets = '';
                        }
                        else if(count(explode(",",$l1_secrets))>1){
                            $temp = explode(",",$l1_secrets);
                            unset($temp[0]);
                            $l1_secrets = implode(',',$temp);
                        }
                        //debug_log($l1_secrets);
                        if(($l1_secrets != '') && ($l1_secrets != ',')){
                            $downline_secrets = $downline_secrets . ','.$l1_secrets;
                        }
                    }
                }
            }

            $downline_secrets = substr($downline_secrets, 1);

            if($downline_secrets != ''){
                $data['members'] = $this->db->query("
                select secret,id,name,activate_time,status from member WHERE status = 'Active' and secret IN (" .$downline_secrets .") order by activate_time ASC")->result_array();     
            }else{
                $data['members'] = array();
            }

        }

        $data['title']      = 'List Members';
        $data['breadcrumb'] = 'List Members';
        $data['layout']     = 'tree/list_member.php';
        $this->load->view(config_item('member'), $data);
    }
    

}

