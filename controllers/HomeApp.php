<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HomeApp extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_member() == false) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');
        $this->load->library('cart');
        $this->load->model('earning');
    }

    public function index()
    {
        $data['detail'] = $this->db_model->select_multi('total_a, total_b, total_c, total_d, total_e', 'member', array('id' => $this->session->user_id));
        
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view(config_item('member'), $data);
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

    public function income($value)
    {
    	$page_data['folder_name'] = 'income';
		$page_data['page_name'] = $value;
    	$this->load->view('home/base.php',$page_data);
    }

   /* public function profile($value)
    {
         
        $page_data['folder_name'] = 'profile';
        $page_data['page_name'] = $value;
        $this->load->view('home/base.php',$page_data);
        $this->form_validation->set_rules('my_name', 'Name', 'trim|required');
        
        $data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            //print_r("hello");die();
            $data['my'] = $this->db_model->select_multi('phone,email,address', 'member', array('id' => $this->session->user_id));
            $this->load->view('home/profile/editprofile.php',$data);
            //$this->load->view('member/base', $data);
        }
        else {
                //print_r("hello");die();
                 print_r($this->input->post('my_address'));die();
                    $array = array(
                    'address' => $this->input->post('my_address'),
                    'city' => $this->input->post('my_city'),
                    'state' => $this->input->post('my_state'),
                    'zip'  => $this->input->post('my_zip'),
                    'country' =>$this->input->post('my_country'),
                    'nominee_name'=>$this->input->post('my_nominee'),
                    'nominee_relation' => $this->input->post('my_relation'),
                    'bank_ac_no'=>$this->input->post('my_account'),
                    'bank_name'=>$this->input->post('my_bankname'),
                    'bank_ifsc'=>$this->input->post('my_ifsc'),
                    'btc_address'=>$this->input->post('my_btc'),
                    'paypal' => $this->input->post('my_paypal'),
                    );
                    $this->db->where('id', $this->session->user_id);
                    $this->db->update('member', $array);

                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                    redirect('HomeApp/profile/edit_profile');
             }
    }*/
    public function edit_profile()
    {
        $page_data['folder_name'] = 'profile';
        $page_data['page_name'] = 'editprofile';
        $this->form_validation->set_rules('my_address', 'Address', 'trim|required');
        $this->form_validation->set_rules('my_city', 'City', 'trim|required');

        $page_data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $page_data['my'] = $this->db_model->select_multi('phone,email,address', 'member', array('id' => $this->session->user_id));
            $this->load->view('home/base.php',$page_data);
            
        }
        else {
                
                    $array = array(
                    'address' => $this->input->post('my_address'),
                    'city' => $this->input->post('my_city'),
                    'state' => $this->input->post('my_state'),
                    'zip'  => $this->input->post('my_zip'),
                    'country' =>$this->input->post('my_country'),
                    'nominee_name'=>$this->input->post('my_nominee'),
                    'nominee_relation' => $this->input->post('my_relation'),
                    'bank_ac_no'=>$this->input->post('my_account'),
                    'bank_name'=>$this->input->post('my_bankname'),
                    'bank_ifsc'=>$this->input->post('my_ifsc'),
                    'btc_address'=>$this->input->post('my_btc'),
                    'paypal' => $this->input->post('my_paypal'),
                    'last_update'=> date('Y-m-d H:i:s'),
                    );
                    $this->db->where('userid', $this->session->user_id);
                    $this->db->update('member_profile', $array);

                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                    redirect('HomeApp/edit_profile');
             }
    }
    public function change_loginpassword()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('confirm_pass', 'Retype Password', 'trim|required|matches[newpass]');

        if ($this->form_validation->run() == false) {
            $page_data['folder_name'] = 'profile';
            $page_data['page_name'] = 'change_loginpassword';
            $this->load->view('home/base.php', $page_data);
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
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Login Password Updated Successfully!!.</div>');
                        redirect('HomeApp/change_loginpassword');
                    }
                    else
                    {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                        redirect('HomeApp/change_loginpassword');
                    }
               }
          }
    }
    public function change_trasactionpassword()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('confirm_pass', 'Retype Password', 'trim|required|matches[newpass]');

        if ($this->form_validation->run() == false) {
            $page_data['folder_name'] = 'profile';
            $page_data['page_name'] = 'change_trasactionpassword';
            $this->load->view('home/base.php', $page_data);
        } else
          {
               if($this->input->post('oldpass') && $this->input->post('newpass'))
               {

                 $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

                    if (password_verify($this->input->post('oldpass'), $mypass) == true)
                    {
                        $array = array(
                            'secure_password' => password_hash($this->input->post('newpass'), PASSWORD_DEFAULT),
                        );
                        $this->db->where('id', $this->session->user_id);
                        $this->db->update('member', $array);
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Transaction Password Updated Successfully!!.</div>');
                        redirect('HomeApp/change_trasactionpassword');
                    }
                    else
                    {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                        redirect('HomeApp/change_trasactionpassword');
                    }
               }
          }
    }


    public function wallet($value)
    {
        $page_data['folder_name'] = 'wallet';
        $page_data['page_name'] = $value;
        $this->load->view('home/base.php',$page_data);
    }

    public function support($value)
    {
        $page_data['folder_name'] = 'support';
        $page_data['page_name'] = $value;
        $this->load->view('home/base.php',$page_data);
    }

    public function team($value)
    {
        $page_data['folder_name'] = 'team';
        $page_data['page_name'] = $value;
        $this->load->view('home/base.php',$page_data);
    }

    public function transfer()
    {
        $this->form_validation->set_rules('transferid', 'Transfer ID', 'trim|required');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $page_data['folder_name'] = 'wallet';
            $page_data['page_name'] = 'transfer';
            $this->load->view('home/base.php',$page_data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if (password_verify($this->input->post('tranpass'), $mypass) == true){
                $uid        = $this->session->user_id;
                $transferid = $this->common_model->filter($this->input->post('transferid'));
                $balance    = $this->input->post('amount');

                $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
                $get_fund_tid = $this->db_model->select('balance', 'wallet', array('userid' => $transferid));
                if ($get_fund_uid < $balance || $balance <= 0) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You donot have sufficient balance in your wallet.</div>');
                    redirect('HomeApp/transfer');
                }
                $new_fund = $get_fund_tid + $balance;
                $array    = array(
                    'balance' => $new_fund,
                );
                $this->db->where('userid', $transferid);
                $this->db->update('wallet', $array);

                $array = array(
                    'balance' => ($get_fund_uid - $balance),
                );
                $this->db->where('userid', $uid);
                $this->db->update('wallet', $array);

                $data = array(
                    'transfer_from' => $uid,
                    'transfer_to'   => $transferid,
                    'amount'        => $balance,
                    'time'          => date('Y-m-d H:i:s'),
                    'remarks'       => 'Wallet Transfer',
                );
                $this->db->insert('transfer_balance_records', $data);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund Transferred Successfully.</div>');
                redirect('HomeApp/transfer');
            } else {

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('HomeApp/transfer');

            }

        }
    }
     public function withdraw_payouts()
    {
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|greater_than[' . config_item('min_withdraw') . ']');
        
        if ($this->form_validation->run() == FALSE) {
            $page_data['folder_name'] = 'wallet';
            $page_data['page_name'] = 'withdraw_payouts';
            $this->load->view('home/base.php',$page_data);
        } else {
            $uid     = $this->session->user_id;
            $balance = $this->input->post('amount');
            //print_r($balance);die();
            $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
            if ($get_fund_uid < $balance || $balance < config_item('min_withdraw')) {
                $min_amount = config_item('min_withdraw') ;
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet. You have to withdraw minimum: ' . config_item('currency') . $min_amount . '</div>');
                redirect('HomeApp/withdraw_payouts');
            }
            else if($balance>config_item('daily_capping')){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot withdraw more than daily capping: ' . config_item('currency') . config_item('daily_capping') . '</div>');
                redirect('HomeApp/withdraw_payouts');
            }
            $new_fund = $get_fund_uid - $balance;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);

            $amount_after_admin_charge = $balance - ($balance * config_item('admin_charge'))/100;
            $data = array(
                'userid' => $uid,
                'amount' => $balance,
                'admin_charge' => config_item('admin_charge'),
                'tax'          => config_item('payout_tax'),
                'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * config_item('payout_tax')) / 100)),
                'date'   => date('Y-m-d H:i:s'),
            );

            $this->db->insert('withdraw_request', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info" >Your request has been submitted to admin for payout.</div>');
            redirect('HomeApp/withdraw_payouts');

        }
    }
    public function withdraw_status(){
        $page_data['folder_name'] = 'wallet';
        $page_data['page_name'] = 'withdraw_status';
        $this->load->view('home/base.php',$page_data);
    }

    public function send_message()
    {
        $this->form_validation->set_rules('subject', 'Transfer ID', 'trim|required');
        $this->form_validation->set_rules('message', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $page_data['folder_name'] = 'support';
            $page_data['page_name'] = 'message';
            $this->load->view('home/base.php',$page_data);
        } else {
            $array = array(
                'ticket_title'  => $this->input->post('subject'),
                'ticket_detail' => $this->input->post('message'),
                'userid'        => $this->session->user_id,
                'date'          => date('Y-m-d H:i:s'),
            );
            $this->db->insert('ticket', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">A New Ticket has been opened.</div>');
            redirect('HomeApp/support/view_message');
        }
    }

    public function respond($id)
    {
        $this->form_validation->set_rules('ticket_reply', 'Ticket Reply Message', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $page_data['detail'] = $this->db_model->select_multi('*', 'ticket', array('id' => $id));
            $page_data['folder_name'] = 'support';
            $page_data['page_name'] = 'respond';
            $this->load->view('home/base.php',$page_data);
        }
        else {
            $array = array(
                'ticket_id' => $this->input->post('ticket_id'),
                'msg_from'  => $this->session->user_id ? $this->session->user_id : 'Admin',
                'msg'       => date('Y-m-d') . '<br>'.$this->input->post('ticket_reply'),
            );

            $this->db->insert('ticket_reply', $array);

            $array = array(
                'status' => $this->session->user_id ? 'Customer Reply' : 'Waiting User Reply',
            );
            $this->db->where('id', $this->input->post('ticket_id'));
            $this->db->update('ticket', $array);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Message sent.</div>');
            redirect('HomeApp/respond/' . $this->input->post('ticket_id'));
        }
    }

    public function close($id)
    {
        $array = array(
            'status' => 'Closed',
        );
        $this->db->where('id', $id);
        $this->db->update('ticket', $array);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Ticket Marked as solved and closed..</div>');
        $this->session->user_id ? redirect('HomeApp/support/view_message') : redirect('ticket/resolved');
    }

    public function topupassociate()
    {
        $this->form_validation->set_rules('transferid', 'Transfer ID', 'trim|required');
        if ($this->form_validation->run() == FALSE) {

            $this->db->select('id, plan_name,joining_fee, gst')->where(array(
                'status' => 'Selling','show_on_regform' => 'Yes',
            ))->order_by('plan_name', 'ASC');

            $page_data['plans'] = $this->db->get('plans')->result_array();
            $page_data['folder_name'] = 'wallet';
            $page_data['page_name'] = 'topupassociate';
            $this->load->view('home/base.php',$page_data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if (password_verify($this->input->post('tranpass'), $mypass) == true){
                $uid        = $this->session->user_id;
                $transferid = $this->common_model->filter($this->input->post('transferid'));
                $plan_id = $this->input->post('plan');
                $plan_details    = $this->db_model->select_multi('*', 'plans', array('id'=>$plan_id));

                $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
                
                if ($get_fund_uid < $plan_details->joining_fee) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You donot have sufficient balance in your wallet.</div>');
                    redirect('HomeApp/topupassociate');
                }

                $array = array(
                    'balance' => ($get_fund_uid - $plan_details->joining_fee),
                );
                $this->db->where('userid', $uid);
                $this->db->update('wallet', $array);

                $data   = array(
                'status' => 'Active',
                'topup'  => $plan_details->joining_fee,
                'signup_package' => $plan_id,
                'activate_time' => date('Y-m-d H:i:s'),
                );
                $this->db->where('id', $transferid);
                $this->db->update('member', $data);


                $data = array(
                    'transfer_from' => $uid,
                    'transfer_to'   => $transferid,
                    'amount'        => $plan_details->joining_fee,
                    'time'          => date('Y-m-d H:i:s'),
                    'remarks'       => 'Account Activation',
                );
                $this->db->insert('transfer_balance_records', $data);

                $member_detail = $this->db_model->select_multi('*', 'member', array('id' => $transferid));
                $this->earning->credit_joining_commission($plan_details,$member_detail);

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Account Activated Successfully.</div>');
                redirect('HomeApp/topupassociate');
            } else {

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('HomeApp/topupassociate');

            }

        }
    }

    public function upgrade_diamond()
    {
        $uid        = $this->session->user_id;
        $amount     = 120; 
        $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
        if ($get_balance < 120) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You donot have sufficient balance in your wallet.</div>');
            redirect('HomeApp');
        }
        
        $array    = array(
            'balance' => $get_balance-120,
        );
        $this->db->where('userid', $uid);
        $this->db->update('wallet', $array);

        $array    = array(
            'rank' => 'Diamond',
            'last_upgrade' => time(),
        );
        $this->db->where('id', $uid);
        $this->db->update('member', $array);        

        $data = array(
          'user_id' => $uid,
          'to_user' => 'admin',
          'amount' => $amount,
          'type' => 'Diamond Upgrade Fee',
          'payment_mode' => 'Wallet',
          'transaction_id' => '',
          'date' => date('Y-m-d H:i:s'),
        );

        $this->db->insert('deductions', $data);

        if($uid == config_item('top_id'))
        {
            $data = array(
                'userid'     => 'Admin',
                'amount'     => 40,
                'name'       => 'Admin',
                'type'       => 'Royalty Income',
                'pair_names' => 'Senior Diamond Income',
                'ref_id'     => $uid,
                'date'       => date('Y-m-d H:i:s'),
                'pair_match' => '',
                'secret'     => $this->db_model->select('signup_package', 'member', array('id' => $uid)),
            );

            $this->db->insert('earning', $data);
        } else {
            while(1) {
                $position = $this->db_model->select('position', 'member', array('id' => $uid));
                $position = $position > 0 ? $position : config_item('top_id');
                $rank = $this->db_model->select('rank', 'member', array('id' => $position));
                $amount = $this->db_model->select('amount', 'earning', array('userid' => $position, 'type' => 'Royalty Income', 'pair_names'=>'Senior Diamond Income'));
                $amount = $amount > 0 ? $amount : 0;

                if(($rank == 'Diamond') && ($amount ==0)) {
                    $this->earning->pay_earning($position, $uid, 'Royalty Income', 'Senior Diamond Income',40, '', $this->db_model->select('signup_package', 'member', array('id' => $uid)));
                    break;
                } else if($position == config_item('top_id')) {
                    $this->earning->pay_earning($position, $uid, 'Royalty Income', 'Senior Diamond Income',40, '', $this->db_model->select('signup_package', 'member', array('id' => $uid)));
                    break;
                } else {
                    $uid = $position;
                }
            }
        }

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">You are Successfully upgraded to Diamond !!!.</div>');
        redirect('HomeApp');
    
    }


  }

