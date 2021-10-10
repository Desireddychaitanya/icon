<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Income extends MY_Controller
{
    /**
     * Income Section for Admin Only
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == FALSE) {
            redirect(site_url('site/admin'));
        }
        $this->load->library('pagination');
        $this->load->model('earning');
        $this->load->model('plan_model');
    }

    public function view_earning()
    {
        $config['base_url']   = site_url('income/view_earning');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('earning');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, userid, name, amount, type, ref_id, date, pair_names')->from('earning')->order_by('id DESC', 'date DESC', 'amount asc')->limit($config['per_page'], $page);
        
        $data['earning'] = $this->db->get()->result_array();
        $data['title']      = 'Earnings';
        $data['breadcrumb'] = 'View Earnings';
        $data['layout']     = 'income/view_earning.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function view_deductions()
    {
        $config['base_url']   = site_url('income/view_deductions');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('earning');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select("t1.*, t2.name")->from('deductions as t1')
                ->join("(SELECT id,name FROM member) as t2", 't1.user_id = t2.id', 'LEFT')
                ->where(array('t1.type !='=>'Registration Fee', 't1.type !='=>'Admin Charge', 'CHAR_LENGTH(t1.text)>'=>0))->order_by('t1.id DESC', 't1.date DESC', 't1.amount asc')
                ->limit($config['per_page'], $page);
        
        $data['earning'] = $this->db->get()->result_array();
        $data['title']      = 'Member Deductions';
        $data['breadcrumb'] = 'View Deductions';
        $data['layout']     = 'income/view_deductions.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function view_wallet()
    {
        $config['base_url']   = site_url('income/view_wallet');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('wallet');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select("t1.secret, t1.id as userid, t1.name, t2.balance as amount")->from('member as t1')->where(array('status'=>'Active'))
                ->join("(SELECT userid, balance FROM wallet) as t2", 't1.id = t2.userid', 'LEFT')
                ->order_by('secret', 'ASC')
                ->limit($config['per_page'], $page);
        
        $data['wallet'] = $this->db->get()->result_array();
        $data['title']      = 'Member Wallet Summary';
        $data['breadcrumb'] = 'View Wallet Balance';
        $data['layout']     = 'income/view_wallet.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function export_wallet()
    {
        if(isset($_POST["export_wallet"]))
        {
            //print_r("hello");die();
            $filename = 'member_wallet_summary_'.date('Y-m-d H:i').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data date("Y-m-d h:i A",t1.time) as date
            $this->db->select("t1.id as userid, t1.name, t2.balance as amount")->from('member as t1')->where(array('status'=>'Active'))
                ->join("(SELECT userid, balance FROM wallet) as t2", 't1.id = t2.userid', 'LEFT')
                ->order_by('secret', 'ASC')
                ->limit($config['per_page'], $page);
            $data = $this->db->get()->result_array();
            //print_r($data[0]['time']);die();
            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("Userid","Name","Wallet Balance"); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        } else {
            redirect('income/export_wallet');
        }
    }

    public function view_summary()
    {
        $config['base_url']   = site_url('income/view_summary');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('member');
        //$page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        //$this->pagination->initialize($config);

        $this->db->select("t1.id, t1.name, IFNULL(t2.amt,0) as earning, t3.balance, IFNULL(t4.rec,0) as received, IFNULL(t5.st,0) sent, IFNULL(t6.tp,0) as topup, IFNULL(t7.pd,0) as paid, IFNULL(t8.pg,0) as pending, IFNULL(t9.ea,0) as epin_amount, IFNULL(t10.dc,0) as deductions, (IFNULL(t3.balance,0) + IFNULL(t10.dc,0) - IFNULL(t2.amt,0) - IFNULL(t4.rec,0) + IFNULL(t5.st,0) - IFNULL(t6.tp,0) + IFNULL(t7.pd,0) + IFNULL(t8.pg,0) + IFNULL(t9.ea,0)) as diff ")->from('member as t1')->where(array('status'=>'Active'))
                ->join("(SELECT userid, sum(amount) as amt FROM earning group by 1) as t2", 't1.id = t2.userid', 'LEFT')
                ->join("(SELECT userid, balance FROM wallet) as t3", 't1.id = t3.userid', 'LEFT')
                ->join("(SELECT transfer_to, sum(amount) as rec FROM transfer_balance_records where (remarks IS NULL or remarks != 'Online Wallet Topup') and (transfer_from != transfer_to) group by 1) as t4", 't1.id = t4.transfer_to', 'LEFT')
                ->join("(SELECT transfer_from, sum(amount) as st FROM transfer_balance_records where (remarks IS NULL or remarks != 'Online Wallet Topup') and (transfer_from != transfer_to)  group by 1) as t5", 't1.id = t5.transfer_from', 'LEFT')
                ->join("(SELECT userid, sum(amount) as tp FROM transaction where (purpose = 'Wallet Topup' or purpose = 'Bank Deposit') and (status = 'Completed') group by 1) as t6", 't1.id = t6.userid', 'LEFT')
                ->join("(SELECT userid, sum(amount) as pd FROM withdraw_request where status = 'Paid' group by 1) as t7", 't1.id = t7.userid', 'LEFT')
                ->join("(SELECT userid, sum(amount) as pg FROM withdraw_request where status = 'Un-Paid' or status = 'Hold' group by 1) as t8", 't1.id = t8.userid', 'LEFT')->order_by('diff', 'DESC', 'secret', 'ASC')
                ->join("(SELECT generated_by, sum(amount) as ea FROM epin group by 1) as t9", 't1.id = t9.generated_by', 'LEFT')
                ->join("(SELECT user_id, sum(amount) as dc FROM deductions where type != 'Registration Fee' and type != 'ePin' group by 1) as t10", 't1.id = t10.user_id', 'LEFT')
                ->order_by('diff', 'DESC', 'secret', 'ASC');

        $data['members'] = $this->db->get()->result_array();

        debug_log($this->db->last_query());

        $data['title']      = 'Summary';
        $data['breadcrumb'] = 'View Summary';
        $data['layout']     = 'income/view_summary.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function view_pv()
    {
        $config['base_url']   = site_url('income/view_pv');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('pv');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('pv')->order_by('id DESC', 'date DESC', 'amount asc')->limit($config['per_page'], $page);
        
        $data['pv'] = $this->db->get()->result_array();
        $data['title']      = 'View PV';
        $data['breadcrumb'] = 'View PV';
        $data['layout']     = 'income/view_pv.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function search_earning()
    {
        $data['title']      = 'Search Earnings';
        $data['breadcrumb'] = 'Search Earnings';
        $data['layout']     = 'income/search_income.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function search()
    {
        $type = $this->uri->segment(3) !='' ? $this->uri->segment(3) : $this->input->post('income_name');
        $pair_names = str_replace('%20',' ',$this->uri->segment(4));
        $userid      = $this->common_model->filter($this->input->post('userid'));
        $startdate   = $this->input->post('startdate');
        $enddate     = $this->input->post('enddate');

        if(trim($userid) !== ""){
            if(!$this->db_model->check_user($userid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('income/search_earning');
            }
            $this->db->where('userid', $userid);
        }

        $this->db->select('id, userid, name, amount, type, ref_id, date, pair_match,pair_names')->from('earning');
        if ($type !== "All") {
            $this->db->like('type', $type,'both');
        }
        if($pair_names != ''){
            $this->db->where('pair_names', $pair_names);   
        }
        if (trim($startdate) !== "") {
            $this->db->where('date >=', $startdate);
        }
        if (trim($enddate) !== "") {
            $this->db->where('date <=', $enddate);
        }

        $this->db->order_by('id','DESC');

        $data['earning']    = $this->db->get()->result_array();

        debug_log($this->db->last_query());
        
        $data['title']      = 'Search Results';
        $data['breadcrumb'] = 'Search Earnings';
        $data['layout']     = 'income/view_earning.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function edit_earning($id)
    {
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['data']       = $this->db_model->select_multi('id, amount, date, status', 'earning', array(
                'id',
                $id,
            ));
            $data['title']      = 'Edit Earning';
            $data['breadcrumb'] = 'Edit Earning';
            $data['layout']     = 'income/edit_earning.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $date   = $this->input->post('date');
            $status = $this->input->post('status');
            $amount = $this->common_model->filter($this->input->post('amount'), 'float');

            $data = array(
                'amount' => $amount,
                'date'   => $date,
                'status' => $status,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('earning', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Earning Detail Updated successfully.</div>');
            redirect('income/view_earning');


        }
    }

    public function remove_earning($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('earning');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Earning Record Deleted successfully.</div>');
        redirect('income/view_earning');
    }

    public function search_rewards()
    {
        $this->db->select('id, reward_id, userid, date, status, paid_date, tid')->from('rewards');
        if (trim($this->input->post('user_id')) !== "") {
            $this->db->where('userid', $this->input->post('user_id'));
        }
        if ($this->input->post('status') !== "All") {
            $this->db->where('status', $this->input->post('status'));
        }
        if (trim($this->input->post('sdate')) !== "") {
            $this->db->where('date >=', $this->input->post('sdate'));
        }
        if (trim($this->input->post('edate')) !== "") {
            $this->db->where('date <=', $this->input->post('edate'));
        }
        $data['data']       = $this->db->get()->result();
        $data['title']      = 'Search Achieved Rewards';
        $data['breadcrumb'] = 'Search Achieved Rewards';
        $data['layout']     = 'income/list_rewards.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function reward_search_form()
    {
        $data['title']      = 'Search Achieved Rewards';
        $data['breadcrumb'] = 'Search Achieved Rewards';
        $data['layout']     = 'income/search_rewards.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function pay_rewards()
    {
        $this->db->select('id, reward_id, userid, date, status, paid_date, tid')->from('rewards')->order_by('status', 'DESC');
        #$this->db->where('status', 'Pending');
        $data['data']       = $this->db->get()->result();
        $data['title']      = 'Pay Achieved Rewards';
        $data['breadcrumb'] = 'Pay Achieved Rewards';
        $data['layout']     = 'income/list_rewards.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function printout_withdraw_list1()
    {
        $this->db->select('userid, amount')->where('status', 'Un-Paid');
        $data = $this->db->get('withdraw_request')->result();
        echo '<html><head><link rel="stylesheet" type="text/css" 
href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript" src="' . base_url('axxets/qrcode.js') . '"></script>
</head><body>
<table class="table table-striped" style="max-width: 800px" align="center">
<tr style="background-color: #5b9bd1; color:#fff">
<td>S.N.</td>
<td>User Detail</td>
<td>Amount</td>
</tr>
';
        $sn = 1;
        foreach ($data as $e) {
            $total += $e->amount;
            echo '<tr>
<td>' . $sn++ . '</td>
<td>';
            ?>
            <?php
            echo $this->db_model->select('name', 'member', array('id' => $e->userid)) . "<br/>";
            $data = $this->db_model->select_multi('bank_ac_no, bank_name, bank_ifsc, bank_branch, btc_address, tcc_address', 'member_profile', array('userid' => $e->userid));
            echo $data->bank_name ? '<strong>Bank Name:</strong> ' . $data->bank_name . '<br/>' : '';
            echo $data->bank_ac_no ? '<strong>A/C No:</strong> ' . $data->bank_ac_no . '<br/>' : '';
            echo $data->bank_ifsc ? '<strong>IFSC:</strong> ' . $data->bank_ifsc . '<br/>' : '';
            echo $data->bank_branch ? '<strong>Bank Branch:</strong> ' . $data->bank_branch . '<br/>' : '';
            echo $data->btc_address ? '<strong>BTC Add:</strong> ' . $data->btc_address . '<br/>' : '';
            ?>
            <?php
            echo '</td><td>' . config_item('currency') . ($e->amount - ($e->amount * config_item('payout_tax') / 100)) . '</td>';
            echo '</tr>';
        }
        echo '<tr style="background-color: #5b9bd1; color:#fff">
<td></td>
<td align="right">Total Payable</td>
<td>' . config_item('currency') . number_format($total) . '</td>
</tr>';
    }

    public function make_payment()
    {
        $this->db->select("t1.name, t2.bank_ac_no, t2.bank_name,t2.bank_ifsc,t3.net_paid,t3.date,concat('Payout for ',t1.id) as remarks")
                    ->from('member as t1')
                    ->where('t3.status' ,'Un-Paid')
                    ->join('member_profile as t2', 't1.id = t2.userid')
                    ->join('withdraw_request as t3', 't1.id = t3.userid')
                    ->order_by('t3.id','ASC');
        $data['bank'] = $this->db->get()->result();

        $data['title']      = 'Fund Withdrawl Request';
        $data['breadcrumb'] = 'Withdrawl List';
        $data['layout']     = 'income/makepayment.php';
        $this->load->view(config_item('admin_theme'), $data);
    }



    public function bank_payment()
    {
        $data['title']      = 'Pending Bank Payments';
        $data['breadcrumb'] = 'Deposit List';
        $data['layout']     = 'income/bank_payment.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
    public function hold_payments()
    {
        $data['title']      = 'Hold Payments';
        $data['breadcrumb'] = 'Hold Payments';
        $data['layout']     = 'income/hold_payments.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function approve($id)
    {
       $transaction = $this->db_model->select_multi('*', 'transaction', array('id' => $id));
       $user_id = $transaction->userid;
       $amount = $transaction->amount;

       if(config_item('crowdfund_type')=='Manual_Peer_to_Peer')
       {
            $md = $this->db_model->select_multi('*', 'member', array('id' => $user_id));
            $pd = $this->db_model->select_multi('*', 'plans', array('id' => $md->signup_package));
            $cs = $this->db_model->select_multi('*','level_upgrade',array('plan_id'=>$md->signup_package, 'upgrade_type'=>($md->gift_level+1)));

            $this->earning->pay_earning('admin',$user_id, 'level ' . ($md->gift_level+1) . ' - Admin Fee', 'Admin Fee from '.$md->name, $amount, '', $cs->id);

            #Assign upline member only if there is no sponsor fee to be paid
            if(!($cs->sponsor_fee>0))
            {
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
                  $update_queue = "UPDATE crowdfund_queue SET level".($md->gift_level+1)." = $upline_id WHERE userid = $user_id and pid = ".$pd->id;
                  $this->db->query($update_queue);
                  debug_log($this->db->last_query());   
                }
                else
                {
                  $update_queue = "UPDATE crowdfund_queue SET level".($md->gift_level+1)." = ".config_item('top_id')." WHERE userid = $user_id  and pid = ".$pd->id;
                  $this->db->query($update_queue);
                  debug_log($this->db->last_query());   
                }
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Assigned Upline ID to make payment.</div>');     
            }
            else
            {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Admin Fee has been approved !!!</div>');        
            }
       }
       else
       {
            $get_fund = $this->db_model->select('balance', 'wallet', array('userid' => $user_id));
            $new_fund = $get_fund + $amount;
            $array = array(
                    'balance' => $new_fund,
                );
            $this->db->where('userid', $user_id);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Amount credited to wallet successfully.</div>'); 
       }
        
        $array = array(
            'time'           => time(),
            'status'         => "Completed",
        );
        $this->db->where(array('id'=>$id));
        $this->db->update('transaction', $array);

        redirect('income/all_transactions');

    }

    public function reject($id)
    {
       $user_id=$this->db_model->select('userid', 'transaction', array('id' => $id));
       //print_r($user_id);die();
       // update transaction table with status= "Completed"
       $array = array(
            'time'           => time(),
            'status'         => "Failed",
          );
          $this->db->where(array('id'=>$id));
          $this->db->update('transaction', $array);

      
        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Bank Payment request rejected.</div>');
        redirect('income/bank_payment');

    }
    public function completed_payments()
    {
        $data['title']      = 'Completed Bank Payments';
        $data['breadcrumb'] = 'Bank List';
        $data['layout']     = 'income/completed_bank_payments.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
    public function pay()
    {
        $payid   = $this->input->post('payid');
        debug_log("pay id".$payid);
        $tdetail = $this->input->post('tdetail');
        $amount  = $this->db_model->select_multi('*', 'withdraw_request', array('id' => $payid));
        $admin_charge = ($amount->amount * $amount->admin_charge / 100);
        $amount_after_admin_charge = $amount->amount - $admin_charge;
        $net_paid = floor($amount_after_admin_charge - ($amount_after_admin_charge *$amount->tax  / 100));
        $md = $this->db_model->select_multi('name, phone, email', 'member', array('id' => $amount->userid));
        
        $remarks = $tdetail != '' ? $tdetail . "<br/><br/>" .  date("Y-m-d h:i A") . "<br/>" . $tdetail : date("Y-m-d h:i A") . "<br/>" . $tdetail; 

        $data = array(
            'status'    => 'Paid',
            'paid_date' => date('Y-m-d H:i:s'),
            'tid'       => $tdetail, //transaction details
        );
        $this->db->where('id', $payid);
        $this->db->update('withdraw_request', $data);

        $data = array(
            'userid'     => $amount->userid,
            'amount'     => $amount_after_admin_charge,
            'payout_id'  => $payid,
            'tax_amount' => ($amount_after_admin_charge * $amount->tax / 100),
            'tax_percnt' => $amount->tax,
            'date'       => date('Y-m-d H:i:s'),
        );
        $this->db->insert('tax_report', $data);

        $this->earning->pay_earning('admin',$amount->userid, 'Admin Charge for the Payout', 'Admin Charge from '.$md->name, $admin_charge, '', $payid);

        if (config_item('sms_on_join') == "Yes"):
                $sms = "Hello " . $md->name . ",\n\nYour payout of " . $net_paid . " Rupees has been generated and paid. Please check your account\n\nRegards\n" . config_item('company_name');
                $messvar="Ok";
                $phone="91".$md->phone;
                $this->common_model->sms($phone, urlencode($sms));
            endif;

        if (trim(config_item('smtp_host')) !== "") {
            $this->db_model->mail($md->email, 'Payout Generated', 'Hi, ' . $md->name . ', Your payout of ' . config_item('currency') . $net_paid . ' has been generated and paid. Please check your account. <hr/>--' . config_item('company_name'));
        }

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Marked as Paid successfully.</div>');
        redirect('income/make_payment');
    }

    private function block_io_pay()
    {
        $apiKey   = config_item('api_key');
        $version  = 2; // API version
        $pin      = config_item('secret_pin');
        $block_io = new BlockIo($apiKey, $pin, $version);
        $this->db->select('userid,amount')->from('withdraw_request')->where('status', 'Un-Paid');
        $data  = $this->db->get()->result();
        $names = array();
        foreach ($data as $e) {
            $address = $this->db_model->select('btc_address', 'member_profile', array('userid' => $e->userid));
            if (strlen($address) > 10) {
                $wallet_add .= "," . $address;

                if (trim(config_item('iso_currency')) !== "XBT" && trim(config_item('iso_currency')) !== "BTC") {
                    $url      = "https://blockchain.info/tobtc?currency=" . trim(config_item('iso_currency')) . "&value=" . $e->amount . "";
                    $btc_rate = $this->common_model->curl($url);
                } else {
                    $btc_rate = $e->amount;
                }
                $amount .= "," . $btc_rate;
            } else {
                $names = array_merge($names, array($e->userid));
                $this->session->set_userdata('skip_autopay', 'BTC Address');
            }
        }
        if (strlen($wallet_add) > 10) {
            $wallet_add = substr($wallet_add, 1);
            $amount     = substr($amount, 1);
            $data       = json_encode($block_io->withdraw(array(
                'amounts'      => '' . $amount . '',
                'to_addresses' => '' . $wallet_add . '',
            )));
            $data       = json_decode($data, TRUE);
        }
        if ($data['status'] == "success") {
            $this->db->where_not_in('userid', $names);
            $this->db->where('status', 'Un-Paid');
            $this->db->update('withdraw_request', array('status' => 'Paid'));
            $status = "success";
        } else {
            $status = "danger";
        }

        return $status;

    }

    public function payall_gateway()
    {
        $this->load->config('pg');
        if ($this->uri->segment(3) == "accept") {
            if (config_item('payment_api') == "Block.io") {
                $return = $this->block_io_pay();
            }

            $data['title']      = 'Autopay';
            $data['breadcrumb'] = 'Autopay';
            $data['layout']     = 'income/autopay_status.php';
            $data['status']     = $return;
            $this->load->view(config_item('admin_theme'), $data);

        } else {
            $data['title']           = 'Make Payment';
            $data['breadcrumb']      = 'Withdrawl List';
            $data['layout']          = 'income/autopay.php';
            $data['payable_balance'] = $this->db_model->sum('amount', 'withdraw_request', array('status' => 'Un-Paid'));
            $this->load->view(config_item('admin_theme'), $data);
        }
    }

    public function reward_pay()
    {
        $payid   = $this->input->post('payid');
        $tdetail = $this->input->post('tdetail');

        $data = array(
            'status'    => 'Delivered',
            'paid_date' => date('Y-m-d H:i:s'),
            'tid'       => $tdetail,
        );
        $this->db->where('id', $payid);
        $this->db->update('rewards', $data);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Marked as Paid successfully.</div>');
        redirect('income/pay-rewards');
    }

    public function hold($id)
    {
        $data = array(
            'status' => 'Hold',
        );
        $this->db->where('id', $id);
        $this->db->update('withdraw_request', $data);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Hold the payment  successfully.</div>');
        redirect('income/make_payment');
    }

    public function unhold($id)
    {
        $data = array(
            'status' => 'Un-Paid',
        );
        $this->db->where('id', $id);
        $this->db->update('withdraw_request', $data);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Un-Hold the payment  successfully.</div>');
        redirect('income/make_payment');
    }

    public function unpay($id)
    {
        $data = array(
            'status' => 'Un-Paid',
        );
        $this->db->where('id', $id);
        $this->db->update('withdraw_request', $data);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Un-Paid the payment  successfully.</div>');
        redirect('income/make_payment');
    }

    public function remove($id)
    {
        $get_fund_uid    = $this->db_model->select_multi('userid,amount', 'withdraw_request', array('id' => $id));
        $uid = $get_fund_uid->userid;
        $balance = $get_fund_uid->amount;
        $get_old_balance = $this->db_model->select('balance', 'wallet', array('userid' => $get_fund_uid->userid));

        $member=$this->db_model->select_multi('*', 'member', array('id' => $uid));
        $payout=$this->db_model->select_multi('*', 'payout', array('plan_id' => $member->signup_package));

        if($payout->repurchase_deduct>0){
            $get_balance = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
            
            $array    = array(
                'balance' => $get_balance - (($balance/(1-$payout->repurchase_deduct/100))-$balance),
            );
            $this->db->where(array('userid'=>$uid,'type'=>'Repurchase'));
            $this->db->update('other_wallet', $array);                

            $this->db->where(array('transfer_from'=>$uid, 'transfer_to'=>'Self', 'amount'=>(($balance/(1-$payout->repurchase_deduct/100))-$balance)));
            $this->db->delete('transfer_balance_records');            

            $balance = $balance/(1-$payout->repurchase_deduct/100);
        }


        $new_fund        = $balance + $get_old_balance;
        $array           = array(
            'balance' => $new_fund,
        );
        $this->db->where('userid', $uid);
        $this->db->update('wallet', $array);
        wallet_log($this->db->last_query());


        $this->db->where('id', $id);
        $this->db->delete('withdraw_request');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-warning">Removed the payment  record and refunded the balance to User Wallet successfully.</div>');
        redirect('income/make_payment');
    }

 //remove from tax_report
    public function tax_remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tax_report');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Tax Report deleted successfully.</div>');
        redirect('income/tax_report');
    }


    public function reward_remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('rewards');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-warning">Removed the reward  record successfully.</div>');
        redirect('income/search-rewards');
    }

    public function search_payout()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $status = $this->input->post('status');
        $sdate  = $this->input->post('sdate');
        $edate  = $this->input->post('edate');
        if (trim($top_id) == ""):
            $data['title']      = 'Withdrawal Report';
            $data['breadcrumb'] = 'Withdrawal Report';
            $data['layout']     = 'income/search_payout.php';
            $this->load->view(config_item('admin_theme'), $data);

        else:

            redirect(site_url('income/search_payout/' . $top_id . '/' . $status . '/' . $sdate . '/' . $edate));
        endif;
    }

    
      public function export_withdrawl_request()
    {
       if(isset($_POST["export_withdrawl_request"]))
        {
            //print_r("hello");die();
            $filename = 'bank_details_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 
            $this->db->select("t1.name, t2.bank_ac_no, t2.bank_name,t2.bank_ifsc,t3.net_paid,t3.date,concat('Payout for ',t1.id) as remarks")
                    ->from('member as t1')
                    ->where('t3.status' ,'Un-Paid')
                    ->join('member_profile as t2', 't1.id = t2.userid')
                    ->join('withdraw_request as t3', 't1.id = t3.userid')
                    ->order_by('t3.id','ASC');
            $data = $this->db->get()->result_array();
            //print_r($data);die();
            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("Beneficiary Name","Beneficiary Acc No","Bank Name","IFSC Code","Amount","Date","remarks"); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        } else {
            redirect('income/make_payment/');
        }
    }


public function export_all_transactions()
    {
       if(isset($_POST["export_all_transactions"]))
        {
            //print_r("hello");die();
            $filename = 'bank_details_'.date('Y-m-d').'.csv';
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Type: application/csv; ");
            // get data date("Y-m-d h:i A",t1.time) as date
            $this->db->select('t1.userid, t1.to_userid, t1.name, t1.phone,t1.amount,t1.gateway, t1.transaction_id, t1.payment_request_id,CAST(FROM_UNIXTIME(t1.time) as DATE) as date, t1.status')
                    ->from('transaction as t1');
            $data = $this->db->get()->result_array();
            //print_r($data[0]['time']);die();
            // file creation
            $file = fopen('php://output', 'w');
            $header = array("User Id","To User Id","Name","Phone","Amount","Gateway","Transaction Id","Payment Request Id","Date","Status");
            fputcsv($file, $header);
            foreach ($data as $key=>$line){
                fputcsv($file,$line);
            }
            fclose($file);
            exit;
        } else {
            redirect('income/all_transactions/');
        }
    }


     public function export()
    {
        if(isset($_POST["export"]))
        {
            $filename = 'Tax_Report_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 
            $this->db->select('t1.userid, t2.name, t3.tax_no, t1.amount, t1.tax_amount, t1.tax_percnt, t1.date')
                    ->order_by('t1.id', 'desc')
                    ->from('tax_report as t1')
                    ->where('t1.payout_id !=' ,'')
                    ->join('member as t2', 't1.userid = t2.id', 'LEFT')
                    ->join('member_profile as t3', 't1.userid = t3.userid', 'LEFT');
            $data = $this->db->get()->result_array();

            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("User ID","Name","PAN No","Payout(After Admin Fee)","Tax Amount","Tax Percentage","Date"); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        } else {
            redirect('income/tax_report/');
        }
    }

    public function export_sale_tax()
    {
        if(isset($_POST["export_sale_tax"]))
        {
            $filename = 'Product_Sale_Tax_Report_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 
            $this->db->select("t1.userid, t2.name,t3.state, t1.invoice_id,CAST(t1.date as DATE) as date,t1.tax_percnt,
                (t1.amount-t1.tax_amount) as taxable_amount,t1.tax_amount,
                CASE
                    when t3.state != 'Karnataka' THEN ROUND(t1.tax_amount,2)
                    ELSE 0
                END as IGST,
                CASE
                    when t3.state != 'Karnataka' THEN 0
                    ELSE ROUND(t1.tax_amount/2,2)
                END as CGST,
                CASE
                    when t3.state != 'Karnataka' THEN 0
                    ELSE ROUND(t1.tax_amount/2,2)
                END as SGST,
                 t1.amount,t1.transaction_id")
                    ->order_by('t1.id', 'desc')
                    ->from('tax_report as t1')
                    ->where('t1.transaction_id !=' ,'')
                    ->join('member as t2', 't1.userid = t2.id', 'LEFT')
                    ->join('member_profile as t3', 't1.userid = t3.userid', 'LEFT');
            $data = $this->db->get()->result_array();
            //debug_log($this->db->last_query());

            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("User ID","Name","State","Invoice No","Date","Tax(%)","Taxable Value","Tax Amount","IGST","CGST","SGST","Total Amount","Details");  
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        }
    }

     public function export_vendor_sale()
    {

        if(isset($_POST["export_vendor_sale"]))
        {
            debug_log("hi");
            $filename = 'Vendor_Sale_Report_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 
            $this->db->select('t1.vendor_id, t2.name, t3.prod_name, t1.cost,t1.qty,t1.total_cost,t1.status,CAST(t1.date as DATE) as date')
                    ->order_by('t1.id', 'desc')
                    ->from('product_sale as t1')
                    ->where('t1.vendor_id !=' ,'')
                    ->join('vendor as t2', 't1.vendor_id = t2.vendor_id', 'LEFT')
                    ->join('product as t3', 't1.product_id = t3.id', 'LEFT');
            $data = $this->db->get()->result_array();
            debug_log($this->db->last_query());

            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("Vendor ID","Vendor Name","Product Name","Product Cost","Qty", "Total Cost","Status", "Date");  
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        }
    }



    public function tax_report()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));        
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");
        if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
            redirect('wallet/withdrawl_report/');
        } else if(($this->input->post('top_id') != '') || ($this->input->post('sdate') != '') || ($this->input->post('edate') != '')) {
            //print_r("hello");die();
            $top_id = $this->input->post('top_id') >0 ?  $this->input->post('top_id') : 'All';
            redirect(site_url('income/tax_report/' . $top_id . '/' . $sdate . '/' . $edate));
        }
        else if (trim($top_id) == "")
        {
            //print_r($top_id);die();
            $data['title']      = 'Tax Report';
            $data['breadcrumb'] = 'Tax Report';
            $data['layout']     = 'income/tax_report.php';
            $this->db->select('id,userid, amount, tax_amount, tax_percnt,date')->where(array(
                    'payout_id !=', '', 'date >=' => $sdate, 'date <=' => $edate,))->order_by('date', 'desc');
            $data['result']     = $this->db->get('tax_report')->result_array();
            //print_r($data['result']);die();
            $this->load->view(config_item('admin_theme'), $data);
        }
        else
        {
            print_r("hello");die();
            $data['title']      = 'Tax Report';
            $data['breadcrumb'] = 'Tax Report';
            $data['layout']     = 'income/tax_report.php';
            $this->db->select('id,userid, amount, tax_amount, tax_percnt,date')->where(array(
                    'userid'=>$top_id, 'payout_id !=', '', 'date >=' => $sdate, 'date <=' => $edate,))->order_by('date', 'desc');
            $data['result']     = $this->db->get('tax_report')->result_array();
            print_r($data['result']);die();
            //$this->load->view(config_item('admin_theme'), $data);
            redirect(site_url('income/tax_report/' . $top_id . '/' . $sdate . '/' . $edate));    
        }
            
    }

    public function producttax_report()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");
        debug_log($top_id);
        if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
            redirect('wallet/withdrawl_report/');
        } else if(($this->input->post('top_id') != '') || ($this->input->post('sdate') != '') || ($this->input->post('edate') != '')) {
            $top_id = $this->input->post('top_id') >0 ?  $this->input->post('top_id') : 'All';
            redirect(site_url('income/producttax_report/' . $top_id . '/' . $sdate . '/' . $edate));
        }
        else if (trim($top_id) == "")
        {
            $data['title']      = 'Product Sale Tax Report';
            $data['breadcrumb'] = 'Product Sale Tax Report';
            $data['layout']     = 'income/producttax_report.php';
            $this->db->select('*')->where(array(
                    'transaction_id !='=>'', 'date >=' => $sdate, 'date <=' => $edate,))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('tax_report')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        }
        else
        {
            $data['title']      = 'Product Sale Tax Report';
            $data['breadcrumb'] = 'Product Sale Tax Report';
            $data['layout']     = 'income/producttax_report.php';
            $this->db->select('*')->where(array(
                    'userid'=>$top_id, 'transaction_id !='=>'', 'date >=' => $sdate, 'date <=' => $edate,))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('tax_report')->result_array();
            //$this->load->view(config_item('admin_theme'), $data);
            redirect(site_url('income/producttax_report/' . $top_id . '/' . $sdate . '/' . $edate));
        }
            
    }

    public function vendor_sale_report()
    {
        $vendor_id = $this->common_model->filter($this->input->post('vendor_id'));
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");
        debug_log($vendor_id);
        if (trim($this->session->vendor_id) !== "" && $vendor_id < $this->session->vendor_id) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
            redirect('income/vendor_sale_report/');
        } else if(($this->input->post('vendor_id') != '') || ($this->input->post('sdate') != '') || ($this->input->post('edate') != '')) {
            //print_r($vendor_id);die();
            $vendor_id = $this->input->post('vendor_id') >0 ?  $this->input->post('vendor_id') : 'All';
            //print_r($vendor_id);die();
            redirect(site_url('income/vendor_sale_report/' . $vendor_id . '/' . $sdate . '/' . $edate));
        }
        else if (trim($vendor_id) == "")
        {
            $data['title']      = 'Vendor Product Sale Report';
            $data['breadcrumb'] = 'Vendor Product Sale Report';
            $data['layout']     = 'income/vendor_sale_report.php';
            $this->db->select('*')->where(array(
                    'date >=' => $sdate, 'date <=' => $edate,))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('product_sale')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        }
        else
        {
            $data['title']      = 'Vendor Product Sale Report';
            $data['breadcrumb'] = 'Vendor Product Sale Report';
            $data['layout']     = 'income/vendor_sale_report.php';
            $this->db->select('*')->where(array(
                    'vendor_id'=>$vendor_id,  'date >=' => $sdate, 'date <=' => $edate,))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('prouct_sale')->result_array();
            //$this->load->view(config_item('admin_theme'), $data);
            redirect(site_url('income/vendor_sale_report/' . $vendor_id . '/' . $sdate . '/' . $edate));
        }
            
    }

    public function flexible_income()
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->select('id, income_name, income_duration, amount');
            $data['result']     = $this->db->get('flexible_income')->result();
            $data['leg']        = $this->plan_model->create_leg();
            $data['title']      = 'Advance Income Setting';
            $data['breadcrumb'] = 'Advance Income Setting';
            $data['layout']     = 'setting/flexible_income.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $amount          = $this->input->post('amount');
            $based_on        = $this->input->post('based_on');

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;

            $array = array(
                'income_name'     => $income_name,
                'income_duration' => $income_duration,
                'amount'          => $amount,
                'based_on'        => $based_on,
                'A'               => $a,
                'B'               => $b,
                'C'               => $c,
                'D'               => $d,
                'E'               => $e,
            );
            $this->db->insert('flexible_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/flexible-income');

        }
    }

    public function set_level_wise()
    {
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        $this->form_validation->set_rules('total_member', 'Total Members', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->select('*');
            $data['result']     = $this->db->get('level_wise_income')->result();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->db->select('*')->where(array('type !=' =>'Repurchase', 'auto_pool'=>'Yes'))->order_by('id', 'ASC');
            $data['new_plans'] = $this->db->get('plans')->result_array();

            if(config_item('enable_board')=='Yes'){

                $this->db->select('*')->where(array('type !=' =>'Repurchase', 'auto_pool'=>'Yes', 'show_on_regform !='=>'Yes'))->order_by('id', 'ASC');
                $data['new_plans'] = $this->db->get('plans')->result_array();

                $data['title']      = 'Configure Board';
                $data['breadcrumb'] = 'Configure Board';
            }
            elseif(config_item('width')=='1'){
                $data['title']      = 'Single Leg Income Setting';
                $data['breadcrumb'] = 'Single Leg Income Setting';
            } else{
                $data['title']      = 'Level Wise Income Setting';
                $data['breadcrumb'] = 'Level Wise Income Setting';
            }
            $data['layout']     = 'setting/set_level_wise.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            
            if($this->db_model->select('id', 'level_wise_income', array('plan_id' => $this->input->post('plan_id'), 'level_no' => $this->input->post('level_no'))) >0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There is already a Level Completion Income configured for the Plan ID '.$this->input->post('plan_id').' and Level Number '.$this->input->post('level_no').' Either delete / update the existing </div>');
            redirect('income/set_level_wise');
            }


            $plan_id =  $this->input->post('plan_id');
            $income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $amount          = $this->input->post('amount') ? $this->input->post('amount') : 0;
            $level_no        = $this->input->post('level_no');
            $total_member    = $this->input->post('total_member');
            $direct          = $this->input->post('direct') ? $this->input->post('direct') : 0;
            $upgrade         = $this->input->post('upgrade_amount') ? $this->input->post('upgrade_amount') : 0;
            $new_id          = $this->input->post('new_id');
            $plan_new_id          = $this->input->post('plan_new_id');
            $auto_upgrade          = $this->input->post('auto_upgrade') ? $this->input->post('auto_upgrade') : 'Yes';
            $roi          = $this->input->post('roi');
            $roi_frequency          = $this->input->post('roi_frequency');
            $roi_limit          = $this->input->post('roi_limit');
            $recurring_fee          = $this->input->post('recurring_fee');

            $array = array(
                'plan_id'         => $plan_id,
                'income_name'     => $income_name,
                'income_duration' => $income_duration,
                'amount'          => $amount,
                'level_no'        => $level_no,
                'total_member'    => $total_member,
                'direct'          => $direct,
                'upgrade'         => $upgrade,
                'new_id'          => $new_id,
                'plan_new_id'     => $plan_new_id,
                'auto_upgrade'    => $auto_upgrade,
                'roi'             => $roi,
                'roi_frequency'   => $roi_frequency,
                'roi_limit'       => $roi_limit,
                'recurring_fee'   => $recurring_fee,
            );
            $this->db->insert('level_wise_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/set_level_wise');

        }
    }

    public function edit_level_wise_income($id)
    {
        $this->form_validation->set_rules('total_member', 'Total Members', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['result']     = $this->db_model->select_multi('*', 'level_wise_income', array('id' => $id,));

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->db->select('*')->where(array('type !=' =>'Repurchase', 'auto_pool'=>'Yes'))->order_by('id', 'ASC');
            $data['new_plans'] = $this->db->get('plans')->result_array();

            if(config_item('enable_board')=='Yes'){
                $data['title']      = 'Edit Board Settings';
                $data['breadcrumb'] = 'Edit Board Settings';
            }
            elseif(config_item('width')=='1'){
                $data['title']      = 'Edit Single Leg Income Setting';
                $data['breadcrumb'] = 'Edit Single Leg Income Setting';
            }
            else{
                $data['title']      = 'Edit Level Wise Income Setting';
                $data['breadcrumb'] = 'Edit Level Wise Income Setting';
            }

            $data['layout']     = 'setting/edit_level_wise.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            //$plan_id =  $this->input->post('plan_id');
            //$income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $amount          = $this->input->post('amount');
            //$level_no        = $this->input->post('level_no');
            $total_member    = $this->input->post('total_member');
            $direct          = $this->input->post('direct') ? $this->input->post('direct') : 0;
            $upgrade         = $this->input->post('upgrade_amount') ? $this->input->post('upgrade_amount') : 0;
            $new_id          = $this->input->post('new_id');
            $plan_new_id          = $this->input->post('plan_new_id');
            $auto_upgrade          = $this->input->post('auto_upgrade') ? $this->input->post('auto_upgrade') : 'Yes';
            $roi          = $this->input->post('roi');
            $roi_frequency          = $this->input->post('roi_frequency');
            $roi_limit          = $this->input->post('roi_limit');
            $recurring_fee          = $this->input->post('recurring_fee');

            $array = array(
                'income_duration' => $income_duration,
                'amount'          => $amount,
                'total_member'    => $total_member,
                'direct'          => $direct,
                'upgrade'         => $upgrade,
                'new_id'          => $new_id,
                'plan_new_id'     => $plan_new_id,
                'auto_upgrade'    => $auto_upgrade,
                'roi'             => $roi,
                'roi_frequency'   => $roi_frequency,
                'roi_limit'       => $roi_limit,
                'recurring_fee'   => $recurring_fee,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('level_wise_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Updated Successfully</div>');
            redirect('income/set_level_wise');

        }
    }

    public function view_level_achievers()
    {
        $type = $this->uri->segment('3');
        $secret = $this->uri->segment('4');
        if($type =='0'){
            if(config_item('width')=='1'){
                $this->db->select('*')->where(array('type' => 'Single Leg Income','secret'=>$secret))->order_by('id','DESC');        
            }else{
                $this->db->select('*')->where(array('type' => 'Level Completion Income','secret'=>$secret))->order_by('id','DESC');        
            }
        
        $data['result'] = $this->db->get('earning')->result_array();
        }else{
            $this->db->select('*')->where(array('type' => 'Level Upgrade Fee','secret'=>$secret))->order_by('id','DESC');
            $data['result'] = $this->db->get('earning')->result_array();
        }
        $data['title']      = 'Level Achievers';
        $data['breadcrumb'] = 'Level Achievers';
        $data['layout']     = 'income/view_level_achievers.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function set_single_leg_income()
    {
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->select('*');
            $data['result']     = $this->db->get('level_wise_income')->result();
            $data['title']      = 'Single Leg Income Setting';
            $data['breadcrumb'] = 'Single Leg Income Setting';
            $data['layout']     = 'setting/set_single_leg_income.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $level_no        = $this->input->post('level_no');
            $total_member    = $this->input->post('total_member');
            $direct          = $this->input->post('direct');
            $amount          = $this->input->post('amount');
            $upgrade         = $this->input->post('upgrade');


            $array = array(
                'income_name'     => $income_name,
                'income_duration' => $income_duration,
                'level_no'        => $level_no,
                'total_member'    => $total_member,
                'direct'          => $direct,
                'amount'          => $amount,
                'upgrade'         => $upgrade,
            );
            $this->db->insert('level_wise_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/set_single_leg_income');

        }


    }


    public function edit_single_leg_income($id)
    {
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['result']     = $this->db_model->select_multi('*', 'level_wise_income', array(
                'id' => $id,
            ));
            $data['title']      = 'Edit Single Leg Income Setting';
            $data['breadcrumb'] = 'Edit Single Leg Income Setting';
            $data['layout']     = 'setting/edit_single_leg_income.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $level_no        = $this->input->post('level_no');;
            $total_member    = $this->input->post('total_member');
            $direct          = $this->input->post('direct');
            $amount          = $this->input->post('amount');
            $upgrade         = $this->input->post('upgrade');

            $array = array(
                'income_name'     => $income_name,
                'income_duration' => $income_duration,
                'level_no'        => $level_no,
                'total_member'    => $total_member,
                'direct'          => $direct,
                'amount'          => $amount,
                'upgrade'         => $upgrade,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('level_wise_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Updated Successfully</div>');
            redirect('income/set_single_leg_income');

        }
    }

    public function crowdfund_settings()
    {
        $this->form_validation->set_rules('upgrade_amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            
            $this->db->select('*')->order_by('plan_id ASC')->order_by('upgrade_type ASC');
            $data['result']     = $this->db->get('level_upgrade')->result();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->db->select('*')->where(array('type !=' =>'Repurchase', 'auto_pool'=>'Yes'))->order_by('id', 'ASC');
            $data['new_plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Configure Crowdfund Income';
            $data['breadcrumb'] = 'Configure Crowdfund Income';
            $data['layout']     = 'setting/crowdfund_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {

            if($this->db_model->select('id', 'level_upgrade', array('plan_id' => $this->input->post('plan_id'), 'upgrade_type' => $this->input->post('upgrade_type'))) >0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There is already a Crowd Fund Income configured for the Plan ID '.$this->input->post('plan_id').' and Level Number '.$this->input->post('upgrade_type').' Either delete / update the existing </div>');
            redirect('income/crowdfund_settings');
            }

            $plan_id =  $this->input->post('plan_id');  
            $upgrade_type = $this->input->post('upgrade_type');
            $admin_charge = $this->input->post('admin_charge');
            $upgrade_amount = $this->input->post('upgrade_amount');
            $admin_charge_time = $this->input->post('admin_charge_time');
            $upgrade_amount_time = $this->input->post('upgrade_amount_time');
            $sponsor_fee = $this->input->post('sponsor_fee') ? $this->input->post('sponsor_fee') :0;
            $sponsor_fee_time = $this->input->post('sponsor_fee_time');
            $plan_upgrade = $this->input->post('plan_upgrade') ? $this->input->post('plan_upgrade') : 'No';
            $plan_new_id = $this->input->post('plan_new_id') ? $this->input->post('plan_new_id') : 0;

            $array = array(
                'plan_id'        => $plan_id,
                'upgrade_type'   => $upgrade_type,
                'admin_charge'   => $admin_charge,
                'admin_charge_time' => $admin_charge_time > 0 ? $admin_charge_time : 24,
                'upgrade_amount' => $upgrade_amount,
                'upgrade_amount_time' => $upgrade_amount_time > 0 ? $upgrade_amount_time : 24,
                'sponsor_fee'       => $sponsor_fee,
                'sponsor_fee_time'  => $sponsor_fee_time >0 ? $sponsor_fee_time : 24,
                'plan_upgrade'      => $plan_upgrade,
                'plan_new_id'       => $plan_upgrade == 'Yes' ? $plan_new_id : 0,
            );
            $this->db->insert('level_upgrade', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Crowdfund Settings Updated Successfully</div>');
            redirect('income/crowdfund_settings');
        }
    }

    public function edit_crowdfund_settings($id)
    {
        $this->form_validation->set_rules('upgrade_amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            
            $data['result']     = $this->db_model->select_multi('*', 'level_upgrade', array('id' => $id,));

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->db->select('*')->where(array('type !=' =>'Repurchase', 'auto_pool'=>'Yes'))->order_by('id', 'ASC');
            $data['new_plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Edit Crowdfund Income';
            $data['breadcrumb'] = 'Edit Crowdfund Income';
            $data['layout']     = 'setting/edit_crowdfund_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }else {

            if($this->db_model->select('id', 'level_upgrade', array('plan_id' => $this->input->post('plan_id'), 'upgrade_type' => $this->input->post('upgrade_type'))) >0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There is already a Crowd Fund Income configured for the Plan ID '.$this->input->post('plan_id').' and Level Number '.$this->input->post('upgrade_type').' Either delete / update the existing </div>');
            redirect('income/crowdfund_settings');
            }

            $plan_id =  $this->input->post('plan_id');  
            $upgrade_type = $this->input->post('upgrade_type');
            $admin_charge = $this->input->post('admin_charge');
            $upgrade_amount = $this->input->post('upgrade_amount');
            $admin_charge_time = $this->input->post('admin_charge_time');
            $upgrade_amount_time = $this->input->post('upgrade_amount_time');
            $sponsor_fee = $this->input->post('sponsor_fee') ? $this->input->post('sponsor_fee') :0;
            $sponsor_fee_time = $this->input->post('sponsor_fee_time');
            $plan_upgrade = $this->input->post('plan_upgrade') ? $this->input->post('plan_upgrade') : 'No';
            $plan_new_id = $this->input->post('plan_new_id') ? $this->input->post('plan_new_id') : 0;

            $array = array(
                'admin_charge'   => $admin_charge,
                'admin_charge_time' => $admin_charge_time > 0 ? $admin_charge_time : 24,
                'upgrade_amount' => $upgrade_amount,
                'upgrade_amount_time' => $upgrade_amount_time > 0 ? $upgrade_amount_time : 24,
                'sponsor_fee'       => $sponsor_fee,
                'sponsor_fee_time'  => $sponsor_fee_time >0 ? $sponsor_fee_time : 24,
                'plan_upgrade'      => $plan_upgrade,
                'plan_new_id'       => $plan_upgrade == 'Yes' ? $plan_new_id : 0,
            );
            $this->db->where('id', $id);
            $this->db->update('level_upgrade', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Crowdfund Settings Updated Successfully</div>');
            redirect('income/crowdfund_settings');
        } 
    }

    public function gap_commission_setting()
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->select('id,income_name, amount, total_pv');
            $data['result']     = $this->db->get('gap_commission_setting')->result();
            $data['leg']        = $this->plan_model->create_leg();
            $data['title']      = 'Gap Commission Setting';
            $data['breadcrumb'] = 'Gap Commission Setting';
            $data['layout']     = 'setting/gap_commission_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name = $this->input->post('income_name');
            $amount      = $this->input->post('amount');

            $e = $this->input->post('total_pv') ? $this->input->post('total_pv') : 0;

            $array = array(
                'income_name' => $income_name,
                'amount'      => $amount,
                'total_pv'    => $e,
            );
            $this->db->insert('gap_commission_setting', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/gap-commission-setting');

        }
    }

    public function income_setting()
    {
        $this->form_validation->set_rules('id', 'Id', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->select('direct_income, level_income, binary_income');
            $data['result']     = $this->db->get('fix_income')->row();
            $data['title']      = 'General Income Setting';
            $data['breadcrumb'] = 'General Income Setting';
            $data['layout']     = 'setting/income_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $direct_income = $this->input->post('direct_income');
            $level_income  = $this->input->post('level_income');
            $binary_income = $this->input->post('binary_income');

            $array = array(
                'direct_income' => $direct_income,
                'level_income'  => $level_income,
                'binary_income' => $binary_income,
            );
            // $this->db->where('id', $this->input->post('id'));
            $this->db->update('fix_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/income-setting');

        }
    }

    public function edit_flexi_income($id)
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['result']     = $this->db_model->select_multi('id, income_name, income_duration, A, B, C, D, E, amount, based_on', 'flexible_income', array('id' => $id));
            $data['leg']        = $this->plan_model->create_leg();
            $data['title']      = 'Update Advance Income';
            $data['breadcrumb'] = 'Update Advance Income';
            $data['layout']     = 'setting/edit_flexible_income.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name     = $this->input->post('income_name');
            $income_duration = $this->input->post('income_duration');
            $amount          = $this->input->post('amount');
            $based_on        = $this->input->post('based_on');

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;

            $array = array(
                'income_name'     => $income_name,
                'income_duration' => $income_duration,
                'amount'          => $amount,
                'based_on'        => $based_on,
                'A'               => $a,
                'B'               => $b,
                'C'               => $c,
                'D'               => $d,
                'E'               => $e,
            );
            echo $this->input->post('id');
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('flexible_income', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/flexible-income');

        }
    }

    public function edit_gap_commission_setting($id)
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->db->where('id', $id);
            $this->db->select('id,income_name, amount, total_pv');
            $data['result']     = $this->db->get('gap_commission_setting')->row();
            $data['title']      = 'Gap Commission Setting';
            $data['breadcrumb'] = 'Gap Commission Setting';
            $data['layout']     = 'setting/edit_gap_commission_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $income_name = $this->input->post('income_name');
            $amount      = $this->input->post('amount');

            $e = $this->input->post('total_pv') ? $this->input->post('total_pv') : 0;

            $array = array(
                'income_name' => $income_name,
                'amount'      => $amount,
                'total_pv'    => $e,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('gap_commission_setting', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Income Settings Saved Successfully</div>');
            redirect('income/gap-commission-setting');

        }
    }


    public function remove_level_wise_income($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('level_wise_income');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Setting of Income Deleted Successfully</div>');
        redirect('income/set_level_wise');
    }

    public function remove_level_upgrade($id)
    {
        $query = $this->db->query("SELECT id FROM transaction where secret = ".$id);
        if($query->num_rows()>0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There are tranactions associated with this settings. This can only be modified and canot be removed !!!</div>');
            redirect('income/crowdfund_settings');
        }    
        $this->db->where('id', $id);
        $this->db->delete('level_upgrade');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Level Upgrade Income Deleted Successfully</div>');
        redirect('income/crowdfund_settings');
    }

    public function remove_flexi_income($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('flexible_income');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Setting of Income Deleted Successfully</div>');
        redirect('income/flexible-income');
    }

    public function remove_gap_income($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('gap_commission_setting');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Setting of Income Deleted Successfully</div>');
        redirect('income/gap-commission-setting');
    }

    public function all_transactions()
    {
        $config['base_url']   = site_url('admin/all_transactions');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('transaction');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['trans']    = $this->db->get('transaction')->result();
        $data['title']      = 'All Transaction';
        $data['breadcrumb'] = 'All Transaction';
        $data['layout']     = 'income/all_transactions.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
}

?>