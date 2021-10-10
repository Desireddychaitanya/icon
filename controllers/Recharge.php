<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recharge extends MY_Controller
{
    /**
     * Check Valid Login or display login page.
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == FALSE && $this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');
        // Load Stripe library 
        $this->load->library('stripe_lib'); 
        $this->load->model('payments_model');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
    }

    public function records()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }

        if (trim($this->input->post('userid')) !== "") {
            $where = array(
                'userid' => $this->input->post('userid'),
            );
            $config['total_rows'] = $this->db_model->count_all('recharge_entry', $where);
        } else {
            $config['total_rows'] = $this->db_model->count_all('recharge_entry');
        }

        $config['base_url']   = site_url('recharge/records');
        $config['per_page']   = 500000;
        
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('recharge_entry')->order_by('id', 'DESC')->limit($config['per_page'], $page);
        if (trim($this->input->post('userid')) !== "") {
            $this->db->where(array('userid' =>$this->input->post('userid')));
        }

        $data['rcg']        = $this->db->get()->result();
        $data['title']      = 'Recharge Records';
        $data['breadcrumb'] = 'Recharge Records';
        $data['layout']     = 'recharge/records.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function remove_record($id)
    {
       if ($this->login->check_session() == FALSE) 
       {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $this->db->where('id', $id);
        $this->db->delete('recharge_entry');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Record Deleted successfully.</div>');

        redirect('recharge/records');
    }

    public function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }

    public function getOperator($provider, $operator){
      $operator_name = '';
      if($provider=='pay1all'){
        $operator_name = $operator =='2' ? 'Airtel' : $operator_name;
        $operator_name = $operator =='11' ? 'JIO' : $operator_name;
        $operator_name = $operator =='6' ? 'Idea' : $operator_name;
        $operator_name = $operator =='23' ? 'Vodafone' : $operator_name;
        $operator_name = $operator =='4' ? 'BSNL Top Up' : $operator_name;
        $operator_name = $operator =='5' ? 'BSNL Special Recharge' : $operator_name;

        $operator_name = $operator =='33' ? 'BSNL PostPaid Mobile' : $operator_name;
        $operator_name = $operator =='34' ? 'AIRTEL PostPaid Mobile' : $operator_name;
        $operator_name = $operator =='35' ? 'Idea Postpaid Mobile' : $operator_name;
        $operator_name = $operator =='36' ? 'Vodafone Postpaid Mobile' : $operator_name;
        $operator_name = $operator =='38' ? 'Reliance JIO Postpaid Mobile' : $operator_name;

        $operator_name = $operator =='24' ? 'Airtel DTH' : $operator_name;
        $operator_name = $operator =='25' ? 'DISH TV' : $operator_name;
        $operator_name = $operator =='26' ? 'Reliance BIG TV' : $operator_name;
        $operator_name = $operator =='27' ? 'SUN Direct' : $operator_name;
        $operator_name = $operator =='28' ? 'Tata Sky' : $operator_name;
        $operator_name = $operator =='29' ? 'Videocon D2H' : $operator_name;  
      }
      return $operator_name;
    }


    public function recharge_hstm($param)
    {
    	  $uid     = $this->session->user_id;
        $amount  = $this->input->post('amount');
        debug_log("recharge_hstm" . $_SESSION['page']);
        //print_r("isnide recharge function");exit();
        //print_r($_SESSION['page']);exit();
        $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
        if ($get_fund_uid < $amount || $amount <= 0) 
        {
          debug_log("going inside get_fund if");
        	//print_r($get_fund_uid);die();
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet.</div>');
            redirect($_SESSION['page']);
        }
        $time = time();
        $provider = '';
        $apikey=config_item('recharge_hstm_api');
        $registered_mobile_no=config_item('registered_mobile_no');
        $reference_no=time();
         switch ($param) 
         {
                case ($param == "mobile"):
                
                $mno          = $this->input->post('mobno');
                $operator     = trim($this->input->post('operator'));
                //print_r($operator);
                $recharge_type     = $this->input->post('stv');
                 
                   
                $customerno=$this->session->user_id;            
                $service_type = "Mobile";
                
               
                  //debug_log($recharge_url);
                $recharge_url = str_ireplace(array('{{MobileNo}}', '{{APIKey}}', '{{REFNO}}','{{ServiceCode}}','{{ConsumerNo}}', '{{CustomerMobileNo}}','{{AMOUNT}}','{{IsSTV}}'), array($registered_mobile_no, $apikey,$reference_no,$operator,$mno, $mno,$amount,$recharge_type), config_item('recharge_hstm_url'));
                debug_log("Recharge URL ".$recharge_url);
                //exit();
                $response = $this->common_model->curl("https://www.rechargedaddy.in/RDRechargeAPI/RechargeAPI.aspx?MobileNo=".$registered_mobile_no."&APIKey=".$apikey."&REQTYPE=RECH&REFNO=".$reference_no."&SERCODE=".$operator."&CUSTNO=".$mno."&REFMOBILENO=".$mno."&AMT=".$amount."&STV=".$recharge_type."&RESPTYPE=JSON");
                //$response = $this->common_model->curl($recharge_url);
                //print_r($response);exit();
                ## SET Your recharge URL as provided in your API doc with given parameters.
                
                //$response = $this->common_model->curl($recharge_url);
                break;
                case ($param == "dth"):
            
                $sub_no       = $this->input->post('sub_no');
                $operator     = $this->input->post('operator');
                $service_type = "DTH";
                $mno=$this->input->post('mobno');
                $recharge_type=0;
                $pincode="563128";
                $lat="13.0175";
                $lon="77.9396";
                $recharge_type="0";

               /* print_r($sub_no);
                print_r($operator);
                print_r($mno);	*/

                $response = $this->common_model->curl("https://www.rechargedaddy.in/RDRechargeAPI/RechargeAPI.aspx?MobileNo=".$registered_mobile_no."&APIKey=".$apikey."&REQTYPE=RECH&REFNO=".$reference_no."&SERCODE=".$operator."&CUSTNO=".$sub_no."&REFMOBILENO=".$mno."&AMT=".$amount."&STV=".$recharge_type."&RESPTYPE=JSON");
                //print_r($response);exit();

                ## SET Your recharge URL as provided in your API doc with given parameters.
                
                break;

                case ($param == "utility"):
                $sub_no       = $this->input->post('customer');
                $operator     = $this->input->post('operator');
                $param_type     = $this->input->post('param_type');
                $mno     = $this->input->post('mobno');
                //$acc_no    = $this->input->post('mobno');
                $service_type = "Utility";
                $pincode="563128";
                $lat="13.0175";
                $lon="77.9396";

                $response = $this->common_model->curl("https://www.rechargedaddy.in/RDRechargeAPI/RechargeAPI.aspx?MobileNo=".$registered_mobile_no."&APIKey=".$apikey."&REQTYPE=BILLPAY&REFNO=".$reference_no."&SERCODE=".$operator."&CUSTNO=".$sub_no."&REFMOBILENO=".$mno."&AMT=".$amount."&STV=".$recharge_type."&PCODE=".$pincode."&LAT=".$lat."&LONG=".$lon."&RESPTYPE=JSON");
                //print_r($response);exit();

                
                ## SET Your recharge URL as provided in your API doc with given parameters.
                
                
                break;

                case ($param == "landline"):
                $std_no       = $this->input->post('customer');
                $operator     = $this->input->post('operator');
                $param_type     = $this->input->post('param_type');
                $mno     = $this->input->post('mobno');
                $acc_no    = $this->input->post('acc_no');
                $service_type = "LANDLINE";
                $pincode="563128";
                $lat="13.0175";
                $lon="77.9396";

                $response = $this->common_model->curl("https://www.rechargedaddy.in/RDRechargeAPI/RechargeAPI.aspx?MobileNo=".$registered_mobile_no."&APIKey=".$apikey."&REQTYPE=BILLPAY&REFNO=".$reference_no."&SERCODE=".$operator."&CUSTNO=".$acc_no."&REFMOBILENO=".$mno."&AMT=".$amount."&STV=".$recharge_type."&FIELD1=".$std_no."&PCODE=".$pincode."&LAT=".$lat."&LONG=".$lon."&RESPTYPE=JSON");
                //print_r($response);exit();

                
                ## SET Your recharge URL as provided in your API doc with given parameters.
                
                
                break;
            }

        ## Configure Json Response or plain text response as per your API doc.
        $result = json_decode($response);
        debug_log("this is the result of json_decode".$result);
        //print_r($result);exit();
        $error  = $result->ERROR;
        $msg = $result->STATUSMSG;
        $status_code = $result->STATUSCODE;

        //$trnid  = $param == "mobile" ? $result->OPTRANSID : $result->TID;
        $trnid  = $result->REFNO;

        debug_log($result);
        debug_log($error);
        debug_log($msg);
        debug_log($status_code);
        debug_log($trnid);

        $status = "FAILED";
        $status = ($status_code == 0) || ($status_code == 1) ? "SUCCESS" : $status; 
        //$status = $status_code == "2" ? "Processing" : $status;
        $status = $status_code == 2 ? "Failed" : $status;
        $status = $status_code == 3 ? "Pending" : $status;

        $operator_name = $operator;
        $error = 0;

        if($error != 0) {
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some Error Occured while doing Recharge: ' . $msg . '.</div>');
          redirect($_SESSION['page']);
        } else {
          if ($status == "SUCCESS") {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Your Recharge is Successfull. Transaction Id: ' . $trnid . '</div>');
          } else if($status == 'Processing') {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your Recharge is Being Processed. Transaction Id: ' . $trnid . '</div>');    
          } else{
              $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">'.$msg. '!Transaction Id: ' . $trnid . '</div>');
          }

          $array = array(
              'userid'       => $this->session->user_id,
              'service_type' => $service_type,
              'recharge_no'  => $mno . $sub_no,
              'amount'       => $amount,
              'time'         => $time,
              'status'       => $status,
              'area'         => 'NA',
              'operator'     => $operator_name,
              'trnid'        => $trnid,
              'Ref_mob_no'   => $mno!=""? $mno : "",
          );
          $this->db->insert('recharge_entry', $array);
          debug_log($this->db->last_query());

          if(($status == "SUCCESS") || ($status == "Processing")) {
            $new_fund = $get_fund_uid - $amount;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            wallet_log($this->db->last_query());
          }
        }
        redirect($_SESSION['page']);

    }

    /*public function hstm_callback()
    {
      debug_log('inside hstm_callback');
      debug_log(basename($_SERVER['REQUEST_URI']));
      $url_components = parse_url(basename($_SERVER['REQUEST_URI'])); 
                parse_str($url_components['query'], $data);
      $clientRef_no=$_GET['ClientRefNo'];
      $status=$_GET['Status'];
      $status_msg=$_GET['StatusMsg'];
      $TrnID=$_GET['TrnID'];
      $OprID=$_GET['OprID'];
      $DP=$_GET['DP'];
      $DR=$_GET['DR'];
      debug_log("this is from hstm callback".$status);
    }*/

    public function recharge($param)
    {
        $uid     = $this->session->user_id;
        $amount       = $this->input->post('amount');        

        $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
        if ($get_fund_uid < $amount || $amount <= 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">User donot have sufficient balance in his/her wallet.</div>');
            redirect($_SESSION['page']);
        }

        $time = time();
        $provider = '';
        $provider = $this->getDomain(config_item('recharge_api'))=='pay1all.in' ? 'pay1all' : $provider;
        $provider = $this->getDomain(config_item('recharge_api'))=='pay2all.in' ? 'pay2all' : $provider;
        debug_log($provider);

        $accesstoken=$this->payments_model->get_accesstoken();

        switch ($param) {

            case ($param == "mobile"):
                $mno          = $this->input->post('mno');
                $operator     = explode('|',$this->input->post('operator'))[0];
                $operator_name = explode('|',$this->input->post('operator'))[1];
                $service_type = "Mobile";
                
                ## SET Your recharge URL as provided in your API doc with given parameters.
                if($provider == 'pay1all'){
                  $recharge_url = str_ireplace(array('{{mobile}}', '{{amount}}', '{{opt}}', '{{txnid}}'), array($mno, $amount,$operator,$time), config_item('recharge_api'));
                  //debug_log($recharge_url);
                  $response = $this->common_model->curl($recharge_url);
                }
                else if($provider=='pay2all')
                {
                  $recharge_url = str_ireplace(array('{{number}}','{{amount}}','{{provider_id}}', '{{client_id}}'), array($mno,$amount,$operator,$time), config_item('recharge_api'));
                  $ch = curl_init();
                  $url="https://api.pay2all.in/v1/payment/recharge";
                  $post = ['number' => $mno,
                           'amount' => $amount,
                           'provider_id'  => $operator,
                           'client_id' =>$time];
        
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 $headers = array("Authorization:Bearer ".$accesstoken,
                                   "Accept:application/json");
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
                 $response = curl_exec($ch);
                 debug_log($response);
                 curl_close($ch);
                }
                break;
            case ($param == "dth"):
                $sub_no       = $this->input->post('sub_no');
                $operator     = explode('|',$this->input->post('operator'))[0];
                $operator_name = explode('|',$this->input->post('operator'))[1];
                $service_type = "DTH";

                ## SET Your recharge URL as provided in your API doc with given parameters.
                if($provider == 'pay1all')
                {
                  $recharge_url = str_ireplace(array('{{mobile}}', '{{amount}}', '{{opt}}', '{{txnid}}'), array($sub_no, $amount,$operator,$time), config_item('recharge_api'));
                  //debug_log($recharge_url);
                  $response = $this->common_model->curl($recharge_url);
                }
                else if($provider=='pay2all')
                {
                  $ch = curl_init();
                  $url="https://api.pay2all.in/v1/payment/recharge";
                  $post = ['number' => $sub_no,
                           'amount' => $amount,
                           'provider_id'  => $operator,
                           'client_id' =>$time];
        
                 curl_setopt($ch, CURLOPT_URL, $url);
        
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 $headers = array("Authorization:Bearer ".$accesstoken,
                                   "Accept:application/json");
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
                 $response = curl_exec($ch);
                 debug_log($response);
                 //$response = json_decode($result, true);
                 //print_r($response);exit();
                 curl_close($ch);
                }
                break;

            case ($param == "electricity"):
                $sub_no       = $this->input->post('customer');
                $operator     = $this->input->post('operator');
                $operator_name = $this->input->post('operator_name');
                $service_type = "ELECTRICITY";
                
                ## SET Your recharge URL as provided in your API doc with given parameters.
                if($provider == 'pay1all')
                {
                  $recharge_url = str_ireplace(array('{{mobile}}', '{{amount}}', '{{opt}}', '{{txnid}}'), array($sub_no, $amount,$operator,$time), config_item('recharge_api'));
                  //debug_log($recharge_url);
                  $response = $this->common_model->curl($recharge_url);
                }
                else if($provider=='pay2all')
                {
                  $ch = curl_init();
                  $url="https://api.pay2all.in/v1/payment/recharge";
                  $post = ['number' => $sub_no,
                           'amount' => $amount,
                           'provider_id'  => $operator,
                           'client_id' =>$time];
        
                 curl_setopt($ch, CURLOPT_URL, $url);
        
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 $headers = array("Authorization:Bearer ".$accesstoken,
                                   "Accept:application/json");
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
                 $response = curl_exec($ch);
                 //print_r($response);exit();
                 debug_log($response);
                 curl_close($ch);
                }
                break;
              }

        ## Configure Json Response or plain text response as per your API doc.
        $result = json_decode($response);
        $error  = $result->ERROR;
        $msg = $result->message;
        $status_code = $result->status;
        $trnid  = $result->report_id;
        //print_r($result);exit();
        //print_r($_SESSION['page']);exit();
        debug_log($result);
        debug_log($error);
        debug_log($msg);
        debug_log($status_code);
        debug_log($trnid);

        if(strlen($status_code)==0){
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some Error Occured while doing Recharge: </div>');
          //print_r($status_code);exit();
          redirect($_SESSION['page']);
        }

        $status = "FAILED";
        //print_r($status);exit();
        #Reason for using '0' instead of 0 because, when $status_code is empty, $status_code==0 will be True 
        $status = (($status_code == '0') || ($status_code == '1')) ? "SUCCESS" : $status; 
        $status = $status_code == '2' ? "Failed" : $status;
        $status = $status_code == '3' ? "Pending" : $status;
        $error = 0;

       // print_r($error);exit();
        if($error != 0) {
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some Error Occured while doing Recharge: ' . $msg . '.</div>');
          redirect($_SESSION['page']);
          //print_r($error);exit();
        } else {
          if ($status == "SUCCESS") {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Your Recharge is Successfull. Transaction Id: ' . $trnid . '</div>');
          } else if($status == 'Processing') {
              $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your Recharge is Being Processed. Transaction Id: ' . $trnid . '</div>');    
          } else{
              $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Your Recharge FAILED. Reason :'.$msg.' <br/>Transaction Id: ' . $trnid . '</div>');
          }
          //print_r($error);exit();

          $array = array(
              'userid'       => $this->session->user_id,
              'service_type' => $service_type,
              'recharge_no'  => $mno . $sub_no,
              'amount'       => $amount,
              'time'         => $time,
              'status'       => $status,
              'area'         => 'NA',
              'operator'     => $operator_name,
              'trnid'        => $trnid,
             );
          //print_r($array);exit();
          //print_r($trnid);exit();
          $this->db->insert('recharge_entry', $array);
          debug_log($this->db->last_query());
          //print_r($trnid);exit();
          if(($status == "SUCCESS") || ($status == "Processing")) {
            
            $new_fund = $get_fund_uid - $amount;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            //wallet_log($this->db->last_query());
            //print_r($array);exit();
          }
          //print_r($trnid);exit();
        }
        redirect($_SESSION['page']);
         //redirect("http://localhost/unilevel");
        //redirect("http://localhost/unilevel/Recharge/recharge/electricity");
    }

    //pay2all
    public function electricity()
    {
       $uid     = $this->session->user_id;
       

       $operator     = explode('|',$this->input->post('operator'))[0];
       $operator_name = explode('|',$this->input->post('operator'))[1];
       $customer       = $this->input->post('customer');

       if((strlen($operator)>0)&&(strlen($customer)>0)){
          $electricity_response=$this->payments_model->electricity_details($operator,$customer); 
       }else{
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Please Provide Valid Customer Number !!! </div>');
          redirect($_SESSION['page']);   
       }

       if($electricity_response['status']==2){
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There are no pending dues for the details entered !!! </div>');
          redirect($_SESSION['page']);   
       }

       if($electricity_response['message']!="Success"){
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Please Provide Valid Customer Number !!! </div>');
          redirect($_SESSION['page']);   
       }

       $data['operator']=$operator;
       $data['operator_name']=$operator_name;
       $data['electricity_response']=$electricity_response;
       //print_r($data['electricity_response']);exit();
       $this->load->view('templates/recharge/electricity_data',$data);

    }
    public function electricity_recharge()
    {
                $sub_no       = $this->input->post('customer');
                $operator     = $this->input->post('operator');
                //print_r($sub_no);
                //print_r($operator);
                $operator_name = $this->input->post('operator_name');
                $amount       = $this->input->post('amount');        
                $service_type = "ELECTRICITY";
                $time = time();
                $provider = '';
                $provider = $this->getDomain(config_item('recharge_api'))=='pay1all.in' ? 'pay1all' : $provider;
                $provider = $this->getDomain(config_item('recharge_api'))=='pay2all.in' ? 'pay2all' : $provider;
                debug_log($provider);
                $accesstoken=$this->payments_model->get_accesstoken();
                //print_r($accesstoken);exit();
                        //print_r($sub_no);exit();
                
                ## SET Your recharge URL as provided in your API doc with given parameters.
                if($provider == 'pay1all')
                {
                  $recharge_url = str_ireplace(array('{{mobile}}', '{{amount}}', '{{opt}}', '{{txnid}}'), array($sub_no, $amount,$operator,$time), config_item('recharge_api'));
                  //debug_log($recharge_url);
                  $response = $this->common_model->curl($recharge_url);
                }
                else if($provider=='pay2all')
                {
                  $ch = curl_init();
                  $url="https://api.pay2all.in/v1/payment/recharge";
                  $post = ['number' => $sub_no,
                           'amount' => $amount,
                           'provider_id'  => $operator,
                           'client_id' =>$time];
        
                 curl_setopt($ch, CURLOPT_URL, $url);
        
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 $headers = array("Authorization:Bearer ".$accesstoken,
                                   "Accept:application/json");
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
                 $response = curl_exec($ch);
                 //print_r($response);exit();
                 debug_log($response);
                 curl_close($ch);
                }
                
              ## Configure Json Response or plain text response as per your API doc.
              $result = json_decode($response);
              //print_r($result);exit();
              $error  = $result->ERROR;
              $msg = $result->message;
              $status_code = $result->status;
              $trnid  = $result->report_id;
              //print_r($result);exit();
              //print_r($_SESSION['page']);exit();
              debug_log($result);
              debug_log($error);
              debug_log($msg);
              debug_log($status_code);
              debug_log($trnid);

              if(strlen($status_code)==0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some Error Occured while doing Recharge: </div>');
                //print_r($status_code);exit();
                redirect('recharge/electricity_success');
              }

              $status = "FAILED";
              //print_r($status);exit();
              #Reason for using '0' instead of 0 because, when $status_code is empty, $status_code==0 will be True 
              $status = (($status_code == '0') || ($status_code == '1')) ? "SUCCESS" : $status; 
              $status = $status_code == '2' ? "Failed" : $status;
              $status = $status_code == '3' ? "Pending" : $status;
              $error = 0;

             // print_r($error);exit();
              if($error != 0) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Some Error Occured while doing Recharge: ' . $msg . '.</div>');
                redirect('recharge/electricity_success');
                //print_r($error);exit();
              } else {
                if ($status == "SUCCESS") {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Your Recharge is Successfull. Transaction Id: ' . $trnid . '</div>');
                } else if($status == 'Processing') {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Your Recharge is Being Processed. Transaction Id: ' . $trnid . '</div>');    
                } else{
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Your Recharge FAILED. Reason :'.$msg.' <br/>Transaction Id: ' . $trnid . '</div>');
                }
                //print_r($error);exit();

                $array = array(
                    'userid'       => $this->session->user_id,
                    'service_type' => $service_type,
                    'recharge_no'  => $mno . $sub_no,
                    'amount'       => $amount,
                    'time'         => $time,
                    'status'       => $status,
                    'area'         => 'NA',
                    'operator'     => $operator_name,
                    'trnid'        => $trnid,
                   );
                //print_r($array);exit();
                //print_r($trnid);exit();
                $this->db->insert('recharge_entry', $array);
                debug_log($this->db->last_query());
                //print_r($trnid);exit();
                if(($status == "SUCCESS") || ($status == "Processing")) {
                  
                  $new_fund = $get_fund_uid - $amount;
                  $array    = array(
                      'balance' => $new_fund,
                  );
                  $this->db->where('userid', $uid);
                  $this->db->update('wallet', $array);
                  //wallet_log($this->db->last_query());
                  //print_r($array);exit();
                }
                //print_r($trnid);exit();
              }
             redirect('recharge/electricity_success');

    }
    public function electricity_success(){
            $data['title']      = 'Add News';
            //$data['layout']     = 'ad/add_news.php';
            $data['breadcrumb'] = 'Add / Edit News';
            //$this->load->view(config_item('admin_theme'), $data);
            $this->load->view('templates/recharge/electricity_success',$data);

    }
   
   //pay2all
    public function electricity_verification($operator,$customer){

      $sub_no       = $customer;
      $operator     = $operator;
      $service_type = "ELECTRICITY";
      $accesstoken=$this->payments_model->get_accesstoken();

      $ch = curl_init();
      $url="https://api.pay2all.in/v1/payment/verification";
      $post = ['number' => $sub_no,
                 'provider_id'  => $operator];

      curl_setopt($ch, CURLOPT_URL, $url);

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $headers = array("Authorization:Bearer ".$accesstoken,
                         "Accept:application/json");
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
      $response = curl_exec($ch);
      curl_close($ch);
      $result = json_decode($response);
      return $result;
    }

    public function new_recharge()
    {
        $data['title']      = 'New Recharge';
        $data['breadcrumb'] = 'New Recharge';
        $data['layout']     = 'recharge/new_recharge.php';
        $this->load->view(config_item('member'), $data);
    }

    public function old_recharges()
    {
        $where                = array(
            'userid' => $this->session->user_id,
        );
        $config['base_url']   = site_url('recharge/old-recharges');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('recharge_entry', $where);
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('recharge_entry')->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $this->db->where($where);

        $data['rcg']        = $this->db->get()->result();
        $data['title']      = 'My Recharges';
        $data['breadcrumb'] = 'My Recharges';
        $data['layout']     = 'recharge/old_recharge.php';
        $this->load->view(config_item('member'), $data);
    }
}