<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends MY_Controller
{
    /**
     * Income Section for Admin Only
     */
    var $data;
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == FALSE && $this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');
        $this->load->config('pg');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
       // $this->load->model('Cashfree_model');
    }

    public function manage_wallet_fund()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $this->form_validation->set_rules('uid', 'User ID', 'trim|required');
        $this->form_validation->set_rules('balance', 'Wallet Balance', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Top Up Wallet';
            $data['breadcrumb'] = 'Top Up Wallet';
            $data['layout']     = 'wallet/manage_funds.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $uid     = $this->common_model->filter($this->input->post('uid'));
            #$userId=$this->db_model->select('secret', 'member', array('id' => $uid));
            #$data = array('userid' => $userId);
            
            #$this->load->view('admin/wallet/manage_funds', $data);

            if(!$this->db_model->check_user($uid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('wallet/manage_wallet_fund');
            }

            $balance = $this->input->post('balance');
            $type    = $this->input->post('submit');
            $remarks    = $this->input->post('remarks');
            
            $get_fund = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
            
            if ($type == "remove") {
                $new_fund = $get_fund - $balance;

                $data = array(
                'transfer_from' => $uid,
                'transfer_to'   => 'Admin',
                'amount'        => $balance,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => strlen($remarks)>0 ? 'Deduction by Admin<br>Remarks: '.$remarks : 'Deduction by Admin',
                );

            } else {
                $new_fund = $get_fund + $balance;

                $data = array(
                'transfer_from' => 'Admin',
                'transfer_to'   => $uid,
                'amount'        => $balance,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => strlen($remarks)>0 ? 'Topup by Admin<br>Remarks: '.$remarks : 'Topup by Admin',
                );
            }
            $this->db->insert('transfer_balance_records', $data);
            
            $array = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Wallet Balance Updated.</div>');
            $data['uid'] = $uid;
            $data['new_fund'] = $new_fund;
            redirect('wallet/wallet_transactions');
        }
    }

    public function transfer_fund()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $this->form_validation->set_rules('userid', 'User ID', 'trim|required');
        $this->form_validation->set_rules('transferid', 'Transfer ID', 'trim|required|differs[userid]');

        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Transfer Wallet Funds';
            $data['breadcrumb'] = 'Transfer Funds';
            $data['layout']     = 'wallet/transfer_funds.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $uid        = $this->common_model->filter($this->input->post('userid'));
            $transferid = $this->common_model->filter($this->input->post('transferid'));
            $balance    = $this->input->post('amount');
            $remarks    = $this->input->post('remarks');

            if(!$this->db_model->check_user($uid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Sender User ID does not exist !!!</div>') ;
                redirect('wallet/transfer_fund');
            }

            if(!$this->db_model->check_user($transferid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The To User ID does not exist !!!</div>') ;
                redirect('wallet/transfer_fund');
            }

            $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
            $get_fund_tid = $this->db_model->select('balance', 'wallet', array('userid' => $transferid));
            if ($get_fund_uid < $balance || $balance <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet.</div>');
                redirect('wallet/transfer_fund');
            }
            $new_fund = $get_fund_tid + $balance;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $transferid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            $array = array(
                'balance' => ($get_fund_uid - $balance),
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            $data = array(
                'transfer_from' => $uid,
                'transfer_to'   => $transferid,
                'amount'        => $balance,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => strlen($remarks)>0 ? 'Transfer by Admin<br>Remarks: '.$remarks : 'Transfer by Admin',
            );
            $this->db->insert('transfer_balance_records', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund Transferred Successfully.</div>');
            redirect('wallet/wallet_transactions');

        }
    }

    public function withdraw_fund()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $this->form_validation->set_rules('userid', 'User ID', 'trim|required');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Withdraw Wallet Funds';
            $data['breadcrumb'] = 'Withdraw Funds';
            $data['layout']     = 'wallet/withdraw_fund.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $uid     = $this->common_model->filter($this->input->post('userid'));

            if(!$this->db_model->check_user($uid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Sender User ID does not exist !!!</div>') ;
                redirect('wallet/withdraw_fund');
            }

            $this->db->select('t1.name, t1.status, t1.signup_package, t2.*')->from('member as t1')->where(array('t1.id'=>$uid))
            ->join("(SELECT * FROM payout) as t2", 't1.signup_package = t2.plan_id', 'LEFT');

            $payout = $this->db->get()->result()[0];
            $balance = $this->input->post('amount');

            $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
            if ($get_fund_uid < $balance || $balance <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet.</div>');
                redirect('wallet/withdraw_fund');
            }

            $array    = array(
                'balance' => $get_fund_uid - $balance,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            if($payout->repurchase_deduct>0){
                $get_balance = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
                
                $array    = array(
                    'balance' => $get_balance + ($balance*$payout->repurchase_deduct/100),
                );
                $this->db->where(array('userid'=>$uid,'type'=>'Repurchase'));
                $this->db->update('other_wallet', $array);

                $data = array(
                'transfer_from' => $uid,
                'transfer_to'   => 'Self',
                'amount'        => $balance*$payout->repurchase_deduct/100,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => 'Recharge Wallet Credit During Payout'
                );
                $this->db->insert('transfer_balance_records', $data);
                $balance = $balance - ($balance*$payout->repurchase_deduct/100);
            }

            $admin_charge = $payout->admin_charge_type=='DDP' ? $payout->admin_charge : 0;

            $amount_after_admin_charge = $balance - ($balance * $admin_charge)/100;
            $p_tax = $this->db_model->select('status', 'member_profile', array('userid' => $uid)) == 'completed' ? $payout->payout_tax : $payout->no_pan_payout_tax;

            $data = array(
                'userid' => $uid,
                'amount' => $balance,
                'admin_charge' => $admin_charge,
                'tax'          => $p_tax,
                'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * $p_tax) / 100)),
                'date'   => date('Y-m-d H:i:s'),
                'mode' => 'bank',
            );

            $this->db->insert('withdraw_request', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your request has been submitted to admin for payout.</div>');
            redirect('income/make_payment');

        }
    }

    public function withdraw_repurchase_fund()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $this->form_validation->set_rules('userid', 'User ID', 'trim|required');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Withdraw Repurchase Wallet Funds';
            $data['breadcrumb'] = 'Withdraw Funds';
            $data['layout']     = 'wallet/withdraw_repurchase_fund.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $uid     = $this->common_model->filter($this->input->post('userid'));
            $balance = $this->input->post('amount');

            if(!$this->db_model->check_user($uid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Sender User ID does not exist !!!</div>') ;
                redirect('wallet/withdraw_repurchase_fund');
            }

            $get_fund_uid = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
            if ($get_fund_uid < $balance || $balance <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet.</div>');
                redirect('wallet/withdraw_repurchase_fund');
            }

            $array    = array(
                'balance' => $get_fund_uid - $balance,
            );
            $this->db->where(array('userid' => $uid, 'type'=>'Repurchase'));
            $this->db->update('other_wallet', $array);

            //$amount_after_admin_charge = $balance - ($balance * $payout->admin_charge)/100;

            $amount_after_admin_charge = $balance - ($balance * 0)/100;

            $data = array(
                'userid' => $uid,
                'amount' => $balance,
                'admin_charge' => 0,//$payout->admin_charge,
                'tax'          => 0,//$payout->payout_tax,
                'net_paid'     => $balance,//floor(($amount_after_admin_charge- ($amount_after_admin_charge * $payout->payout_tax) / 100)),
                'date'   => date('Y-m-d H:i:s'),
            );

            $this->db->insert('withdraw_request', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your request has been submitted to admin for payout.</div>');
            redirect('wallet/withdraw_repurchase_fund');

        }
    }

    public function wallet_transactions()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        if (trim($top_id) == ""):
            $data['title']      = 'Wallet Transactions';
            $data['breadcrumb'] = 'Wallet Transactions';
            $data['layout']     = 'wallet/wallet_transactions.php';
            $this->load->view(config_item('admin_theme'), $data);
        else:
            if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
                redirect('wallet/wallet_transactions/');
            }
            redirect(site_url('wallet/wallet_transactions/' . $top_id));
        endif;
    }

    public function export()
    {
        if(isset($_POST["export"]))
        {
            $filename = 'Payout_Report_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 
            $this->db->select("t1.userid, t3.name, t1.amount,t1.admin_charge,t1.tax,t1.net_paid, t1.date,t2.tax_no, t2.bank_ac_no, t2.bank_name, t2.bank_ifsc, t2.bank_branch, t2.googlepay_no, t2.phonepay_no, t2.upi_id, 
                CASE
                    when t2.status != 'completed' THEN 'Non Compliant'
                    ELSE 'Compliant'
                END as KYC, 
                t1.status,t1.tid")->order_by('date', 'ASC')
                ->from('withdraw_request as t1')
                ->join('member_profile as t2', 't1.userid = t2.userid', 'LEFT')
                ->join('member as t3', 't1.userid = t3.id', 'LEFT');
            $data = $this->db->get()->result_array();

            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("UserID","Name","Amount","Admin Charge","Tax","Net Paid", "Withdrawal Date","PAN Number", "Bank Acc No","Bank Name","IFSC Code","Branch","GooglePay","PhonePe","UPI ID","KYC Status","Paid Status","Transaction Details"); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        }
    }

    public function withdrawl_report()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $status = $this->input->post('status') ? $this->input->post('status') : 'All' ;
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");

        if(!$this->db_model->check_user($top_id)>0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
            redirect('wallet/withdrawl_report');
        }

        if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
                redirect('wallet/withdrawl_report/');
            }
        else if(($this->input->post('top_id') != '') || ($this->input->post('status') != '') || ($this->input->post('sdate') != '') || ($this->input->post('edate') != '')) {
            $top_id = $this->input->post('top_id') >0 ?  $this->input->post('top_id') : 'All';
            redirect(site_url('wallet/withdrawl_report/' . $top_id . '/' . $status . '/' . $sdate . '/' . $edate));
        } else if (trim($top_id) == ""):
            {
                $data['title']      = 'Payout Report';
                $data['breadcrumb'] = 'Payout Report';
                $data['layout']     = 'wallet/withdrawl_report.php';
                if($status=='All'){
                    $this->db->select('userid, amount,admin_charge,tax,net_paid, date,status,tid,cashfree_referenceId')->where(array(
                    'date >=' => $sdate, 'date <=' => $edate,
                   ))->order_by('date', 'desc');    
                    $data['status'] = 'All';
                }
                else {
                    $this->db->select('userid, amount,admin_charge,tax,net_paid, date,status,tid,cashfree_referenceId')->where(array(
                    'status'=>$status, 'date >=' => $sdate, 'date <=' => $edate,
                   ))->order_by('date', 'desc');    
                    $data['status'] = $status;
                }
                $data['data'] = $this->db->get('withdraw_request')->result_array();
                debug_log($this->db->last_query());
                $this->load->view(config_item('admin_theme'), $data);
            }
        else:
            {
                $data['title']      = 'Payout Report';
                $data['breadcrumb'] = 'Payout Report';
                $data['layout']     = 'wallet/withdrawl_report.php';
                if($status=='All'){
                    $this->db->select('userid, amount,admin_charge,tax,net_paid, date,status,tid,cashfree_referenceId')->where(array(
                    'userid'=>$top_id, 'date >=' => $sdate, 'date <=' => $edate
                    ))->order_by('date', 'desc');
                } else{
                    $this->db->select('userid, amount,admin_charge,tax,net_paid, date,status,tid,cashfree_referenceId')->where(array(
                    'userid'=>$top_id, 'status' => $status, 'date >=' => $sdate, 'date <=' => $edate
                    ))->order_by('date', 'desc');
                }
                $data['data'] = $this->db->get('withdraw_request')->result_array();
                debug_log($this->db->last_query());
                $data['status'] = $status;
                //$this->load->view(config_item('admin_theme'), $data);
                redirect(site_url('wallet/withdrawl_report/' . $top_id . '/' . $status . '/' . $sdate . '/' . $edate));
            }
        endif;
    }

    public function generate_payout()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $old_password = $this->input->post('password');
        if (trim($old_password) == ""):
            $data['title']      = 'Generate Payout';
            $data['breadcrumb'] = 'Generate Payout';
            $data['layout']     = 'wallet/generate_payout.php';
            $this->load->view(config_item('admin_theme'), $data);

        else:
            $original_pass = $this->db_model->select('password', 'admin', array('id' => $this->session->admin_id));
            if (password_verify($old_password, $original_pass) == FALSE) {
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>Entered Current Password is wrong.</div>");
                redirect(site_url('wallet/generate_payout'));
            }
            ################ We will generate payout now ################

            if(config_item('enable_kyc')=='Yes'){
                $this->db->select("t1.userid as userid, t1.balance as balance")->from('wallet as t1')
                ->join("(select userid from member_profile where  status ='completed') as t2", 't1.userid = t2.userid');
                $res = $this->db->get()->result();
            } else{
                $this->db->select('userid, balance');
                $res = $this->db->get('wallet')->result();    
            }

            foreach ($res as $result) {
                $uid     = $result->userid;
                $balance = $result->balance;

                $this->db->select('t1.name, t1.status, t1.signup_package, t2.*')->from('member as t1')->where(array('t1.id'=>$uid))
                ->join("(SELECT * FROM payout) as t2", 't1.signup_package = t2.plan_id', 'LEFT');
                $payout = $this->db->get()->result()[0];

                $d_count = $this->db_model->count_all('member', array('sponsor' => $uid, 'status'=>'Active'));

                if(($balance >= $payout->min_withdraw) && ($d_count >= $payout->min_sponsor)){
                    $e       = 1;

                    $array = array(
                        'balance' => 0,
                    );
                    $this->db->where('userid', $uid);
                    $this->db->update('wallet', $array);
                    wallet_log($this->db->last_query());

                    if($payout->repurchase_deduct>0){
                    $get_balance = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
                    
                    $array    = array(
                        'balance' => $get_balance + ($balance*$payout->repurchase_deduct/100),
                    );
                    $this->db->where(array('userid'=>$uid,'type'=>'Repurchase'));
                    $this->db->update('other_wallet', $array);

                    $data = array(
                    'transfer_from' => $uid,
                    'transfer_to'   => 'Self',
                    'amount'        => $balance*$payout->repurchase_deduct/100,
                    'time'          => date('Y-m-d H:i:s'),
                    'remarks'       => 'Recharge Wallet Credit During Payout'
                    );
                    $this->db->insert('transfer_balance_records', $data);

                    $balance = $balance - ($balance*$payout->repurchase_deduct/100);
                    }

                    $admin_charge = $payout->admin_charge_type=='DDP' ? $payout->admin_charge : 0;
                    
                    $amount_after_admin_charge = $balance - ($balance * $admin_charge)/100;
                    $p_tax = $this->db_model->select('status', 'member_profile', array('userid' => $uid)) == 'completed' ? $payout->payout_tax : $payout->no_pan_payout_tax;

                    $data = array(
                        'userid' => $uid,
                        'amount' => $balance,
                        'admin_charge' => $admin_charge,
                        'tax'          => $p_tax,
                        'mode'         => 'bank',
                        'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * $p_tax) / 100)),
                        'date'   => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('withdraw_request', $data);

                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Payout Generated Successfully.</div>');
                }
              }
              if ($e !== 1) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-info">No User Id has sufficient balance, Hence No Payout Generated.</div>');
              }
            redirect('income/make-payment');

            #############################################################
        endif;
    }


    ############################## MEMBER SECTION HERE ###########################################

    public function transfer_balance()
    {
        $this->form_validation->set_rules('from', 'Sender ID', 'trim|required');
        $this->form_validation->set_rules('transferid', 'Transfer ID', 'trim|required|differs[from]');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Transfer Wallet Funds';
            $data['breadcrumb'] = 'Transfer Funds';
            $data['layout']     = 'wallet/transfer_funds.php';
            $this->load->view(config_item('member'), $data);
        } else {
            $uid        = $this->session->user_id;
            $transferid = $this->common_model->filter($this->input->post('transferid'));
            $balance    = $this->input->post('amount');
            $remarks    = $this->input->post('remarks');

            if(!$this->db_model->check_user($transferid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('wallet/transfer_balance');
            }

            $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
            $get_fund_tid = $this->db_model->select('balance', 'wallet', array('userid' => $transferid));
            if ($get_fund_uid < $balance || $balance <= 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in your wallet.</div>');
                redirect('wallet/transfer_balance');
            }
            $new_fund = $get_fund_tid + $balance;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $transferid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            $array = array(
                'balance' => ($get_fund_uid - $balance),
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());

            $data = array(
                'transfer_from' => $uid,
                'transfer_to'   => $transferid,
                'amount'        => $balance,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => strlen($remarks)>0 ? 'Transfer by Member<br>Remarks: '.$remarks : 'Transfer by Member',
            );
            $this->db->insert('transfer_balance_records', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund Transferred Successfully.</div>');
            redirect('wallet/balance_transfer_list');

        }
    }

    public function withdrawal_list()
    {
        $status = $this->input->post('status');
        $sdate  = $this->input->post('sdate');
        $edate  = $this->input->post('edate');
        if (trim($status) == ""):
            $data['title']      = 'Withdrawal Report';
            $data['breadcrumb'] = 'Withdrawal Report';
            $data['layout']     = 'wallet/withdrawl_report.php';
            $this->load->view(config_item('member'), $data);

        else:
            redirect(site_url('wallet/withdrawal_list/' . $status . '/' . $sdate . '/' . $edate));
        endif;
    }

    public function withdraw_payouts()
    {
     $this->form_validation->set_rules('amount', 'Amount', 'trim|required|greater_than[' . (0) . ']');
     if ($this->form_validation->run() == FALSE) 
     {
       $data['title']      = 'Withdraw Wallet Funds';
       $data['breadcrumb'] = 'Withdraw Funds';
       $data['layout']     = 'wallet/withdraw_fund.php';
       $this->load->view(config_item('member'), $data);
     } 
     else 
     {
       $uid     = $this->session->user_id;
       $amount = $this->input->post('amount');
       $balance = $amount;
       $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
       $mode=$this->input->post('mode');
       
       $this->db->select('t1.name, t1.status, t1.signup_package, t2.*')->from('member as t1')->where(array('t1.id'=>$uid))
          ->join("(SELECT * FROM payout) as t2", 't1.signup_package = t2.plan_id', 'LEFT');

       $payout = $this->db->get()->result()[0];

       $withdrawn_amount = $this->db_model->sum('amount','withdraw_request', array('userid'=>$uid, 'date >='=>date('Y-m-d')));

       $remaing_balance = $payout->daily_capping>0 ? ($payout->daily_capping-$withdrawn_amount) : $amount;

       if ($get_fund_uid < $balance || $balance < $payout->min_withdraw)
       {
         $min_amount = $payout->min_withdraw ;
         $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet. You have to withdraw minimum: ' . config_item('currency') . $min_amount . '</div>');
         redirect('wallet/withdraw_payouts');
       }
       else if($amount>$remaing_balance)
       {
         $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot withdraw more than daily capping: '.config_item('currency').$payout->daily_capping.'<br/>Your Eligible Amount for Today is '.config_item('currency').$remaing_balance.'</div>');
                redirect('wallet/withdraw_payouts');
       }
       if($payout->repurchase_deduct>0)
       {
        $repurchase_wallet = $balance*$payout->repurchase_deduct/100;
        $balance = $balance - $repurchase_wallet;
       }

       $p_tax = $this->db_model->select('status', 'member_profile', array('userid' => $uid)) == 'completed' ? $payout->payout_tax : $payout->no_pan_payout_tax;

       $admin_charge = $payout->admin_charge_type=='DDP' ? $payout->admin_charge : 0;

       $amount_after_admin_charge = $balance - ($balance * $admin_charge)/100;
       $netpaid=floor(($amount_after_admin_charge- ($amount_after_admin_charge *$p_tax) / 100));

       if(config_item('coinpayment_payout')=="Yes")
       {
         
         $this->load->library('coinpaymentsapi');
         $address=$this->input->post('address');
         $to_currency=$this->input->post('currency');
         $from_currency=config_item('coinpayment_currency');
         $result= $this->coinpaymentsapi->CreateWithdrawal($amount,$to_currency,$from_currency,$address);
         debug_log("from withdraw payouts");
         debug_log($result);
         // print_r($result['error']);die();
         if($result['error']!="ok")
         {
          //print_r($result['error']."not ok");die();
           $this->session->set_flashdata('common_flash', '<div class="alert alert-danger" >'.$result['error'].'</div>');
           redirect('wallet/withdraw_payouts');
         }
         elseif($result['error'] =="ok" || $result['result']['status']==0)
         {
           $new_fund = $get_fund_uid - $amount;
           $array    = array(
                          'balance' => $new_fund,
                         );
           $this->db->where('userid', $uid);
           $this->db->update('wallet', $array);
           wallet_log($this->db->last_query());

           $data = array(
                          'userid' => $uid,
                          'amount' => $balance,
                          'admin_charge' => $admin_charge,
                          'tax'          => $p_tax,
                          'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * $p_tax) / 100)),
                          'date'   => date('Y-m-d H:i:s'),
                          'status'    => 'Un-Paid',
                          'tid'=>$result['result']['id'],
                          'address'=>$address,

                      );
           $this->db->insert('withdraw_request', $data);
           debug_log($this->db->last_query());
          /* $data = array(
                                'userid'     => $uid,
                                'amount'     => $amount_after_admin_charge,
                                'tax_amount' => ($amount_after_admin_charge * $p_tax / 100),
                                'tax_percnt' => $p_tax,
                                'date'       => date('Y-m-d H:i:s'),
                               );
            $this->db->insert('tax_report', $data);
            debug_log($this->db->last_query());*/
           // debug_log("hello after withdrawl request");
            
            $user_data = $this->db_model->select_multi('name, phone, email', 'member', array('id' => $uid));
            if (config_item('sms_on_join') == "Yes"):
             $sms = "Hello " . $user_data->name . ",\n\nYour payout of " . $netpaid . " ".config_item('currency')." has been credited to your account successfully.Your UTR number is ".$result['result']['id']."  Please check your account\n\nRegards\n" . config_item('company_name');
              $messvar="Ok";
              $phone="91".$user_data->phone;
              $this->common_model->sms($phone, urlencode($sms));
            endif;
            if (trim(config_item('smtp_host')) !== "")
            {
               $this->db_model->mail($user_data->email, 'Payout Generated', 'Hi, ' . $user_data->name . ', Your payout of ' . config_item('currency') . $netpaid . ' has been credited to your account.Your UTR Number is'.$result['result']['id'].' Please check your account. <hr/>--' . config_item('company_name'));
            }
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info" >Your request has been submitted for admin for email confirmation.</div>');
            //redirect('wallet/withdraw_payouts');
         }
         else
         {
           $this->session->set_flashdata('common_flash', '<div class="alert alert-danger" >Unable to complete your request.Please contact admin</div>');
           redirect('wallet/withdraw_payouts');
         }
       }

       elseif(config_item('cashfree_enable_payout')=="Yes")
       {
         $_SESSION["cashfree_netpaid_topayamount"]=$netpaid;
         //integrate cashfree payout 
         $this->load->model('cashfree_model');
         $token = $this->cashfree_model->getToken();
         if(!$this->cashfree_model->getBeneficiary($token))
         { 
           $addBeneficiary=$this->cashfree_model->addBeneficiary($token);
         }
         $requestTransfer=$this->cashfree_model->requestTransfer($token);
         $status=$this->cashfree_model->getTransferStatus($token);
         $data = array(
                          'userid' => $uid,
                          'amount' => $balance,
                          'admin_charge' => $admin_charge,
                          'tax'          => $p_tax,
                          'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * $p_tax) / 100)),
                          'date'   => date('Y-m-d H:i:s'),
                          'status'    => 'Un-Paid',
                          'cashfree_referenceId'=>$status['data']['transfer']['referenceId'],
                          //'tid'       =>$status['data']['transfer']['utr'] , //transaction details
                      );
         $this->db->insert('withdraw_request', $data);
         debug_log($this->db->last_query());
         if($addBeneficiary==100)
         {
          $data = array(
                          'date'   => date('Y-m-d H:i:s'),
                          'status'    => 'Failed',
                          //transaction details
                         );
           $this->db->where(array('userid'=> $uid,'cashfree_referenceId'=>$status['data']['transfer']['referenceId']));
           $this->db->update('withdraw_request', $data);
           debug_log($this->db->last_query());
           $this->session->set_flashdata('common_flash', '<div class="alert alert-danger" >There was some error in adding Beneficiary. Please try with correct values</div>');
              redirect('wallet/withdraw_payouts');
         }
         if($requestTransfer==100)
         {
           $data = array(
                          'date'   => date('Y-m-d H:i:s'),
                          'status'    => 'Failed',
                          //transaction details
                         );
           $this->db->where(array('userid'=> $uid,'cashfree_referenceId'=>$status['data']['transfer']['referenceId']));
           $this->db->update('withdraw_request', $data);
           debug_log($this->db->last_query());
           $this->session->set_flashdata('common_flash', '<div class="alert alert-danger" >error in requesting transfer</div>');
           redirect('wallet/withdraw_payouts');
         }
         elseif($status['status']=="SUCCESS" && $status['subCode']=="200" )
         {
           $new_fund = $get_fund_uid - $amount;
           $array    = array(
                          'balance' => $new_fund,
                         );
           $this->db->where('userid', $uid);
           $this->db->update('wallet', $array);
           wallet_log($this->db->last_query());
           //code in income controller->pay function goes here
           $data = array(
                          'date'   => date('Y-m-d H:i:s'),
                          'status'    => 'Paid',
                          'paid_date' => date('Y-m-d H:i:s'),
                          'tid'       =>$status['data']['transfer']['utr'] , //transaction details
                         );
           $this->db->where(array('userid'=> $uid,'cashfree_referenceId'=>$status['data']['transfer']['referenceId']));
           $this->db->update('withdraw_request', $data);
           debug_log($this->db->last_query());
           $this->session->set_flashdata('common_flash', '<div class="alert alert-info" >The amount you request has been paid successfully.Please check your account</div>');
           $data = array(
                    'userid'     => $uid,
                    'amount'     => $amount_after_admin_charge,
                    'tax_amount' => ($amount_after_admin_charge * $p_tax / 100),
                    'tax_percnt' => $p_tax,
                    'date'       => date('Y-m-d H:i:s'),
                   );
            $this->db->insert('tax_report', $data);
            debug_log($this->db->last_query());
            $user_data = $this->db_model->select_multi('name, phone, email', 'member', array('id' => $uid));
            if (config_item('sms_on_join') == "Yes"):
             $sms = "Hello " . $user_data->name . ",\n\nYour payout of " . $netpaid . " ".config_item('currency')." has been credited to your account successfully.Your UTR number is ".$status['data']['transfer']['utr']."  Please check your account\n\nRegards\n" . config_item('company_name');
              $messvar="Ok";
              $phone="91".$user_data->phone;
              $this->common_model->sms($phone, urlencode($sms));
            endif;
            if (trim(config_item('smtp_host')) !== "")
            {
              $this->db_model->mail($user_data->email, 'Payout Generated', 'Hi, ' . $user_data->name . ', Your payout of ' . config_item('currency') . $netpaid . ' has been credited to your account.Your UTR Number is'.$status['data']['transfer']['utr'].' Please check your account. <hr/>--' . config_item('company_name'));
            }
         }
         else
         {
           $this->session->set_flashdata('common_flash', '<div class="alert alert-danger" >Unable to complete your request.Please contact admin</div>');
           redirect('wallet/withdraw_payouts');
         }
       } 
       else
       {
         $new_fund = $get_fund_uid - $amount;
         $array    = array(
                         'balance' => $new_fund,
                        );
         $this->db->where('userid', $uid);
         $this->db->update('wallet', $array);
         wallet_log($this->db->last_query());
         $data = array(
                'userid' => $uid,
                'amount' => $balance,
                'admin_charge' => $admin_charge,
                'tax'          => $p_tax,
                'net_paid'     => floor(($amount_after_admin_charge- ($amount_after_admin_charge * $p_tax) / 100)),
                'date'   => date('Y-m-d H:i:s'),
                'mode' =>$mode,
                 );

         $this->db->insert('withdraw_request', $data);
         debug_log($this->db->last_query());
         $this->session->set_flashdata('common_flash', '<div class="alert alert-info" >Your request has been submitted to admin for payout.</div>');
       }
       if($repurchase_wallet >0)
       {
        $get_balance = $this->db_model->select('balance', 'other_wallet', array('userid' => $uid, 'type'=>'Repurchase'));
        
        $array = array('balance' => $get_balance + $repurchase_wallet,);
        $this->db->where(array('userid'=>$uid,'type'=>'Repurchase'));
        $this->db->update('other_wallet', $array);

        $data = array(
                'transfer_from' => $uid,
                'transfer_to'   => 'Self',
                'amount'        => $repurchase_wallet,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => 'Recharge Wallet Credit During Payout'
                );
        $this->db->insert('transfer_balance_records', $data);
        debug_log($this->db->last_query());
       }
       redirect('wallet/withdraw_payouts');
     }
    }

    public function balance_transfer_list()
    {
        $data['title']      = 'Wallet Transactions';
        $data['breadcrumb'] = 'Wallet Transactions';
        $data['layout']     = 'wallet/wallet_transactions.php';
        $this->load->view(config_item('member'), $data);
    }
}