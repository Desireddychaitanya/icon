<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Gateway extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->cp_merchant_id=config_item('coinpaymentmerchant_id');
        $this->cp_ipn_secret=config_item('coinpayment_ipn_secret');
        $this->cp_debug_email=config_item('coinpayment_debug_email');
        $this->load->config('pg');
        $this->load->library('Coinpaymentsapi');
        $this->load->library('Stripe_lib');
        $this->load->library('Paypal_lib'); 

    }

    public function payment_gateway()
    {
        $layout['layout'] = 'pg_gateway.php';
        $this->load->view('theme/default/base', $layout);
    }
    //recharge hstm callback url
     public function hstm_callback()
    {
      $uid     = $this->session->user_id;
      debug_log('inside hstm_callback');
      debug_log(basename($_SERVER['REQUEST_URI']));
      $url_components = parse_url(basename($_SERVER['REQUEST_URI'])); 
      parse_str($url_components['query'], $data);
      $temp = explode(',',str_replace('%7D','',str_replace('hstm_callback?%7B','',str_replace('%22','',$_SERVER['REQUEST_URI']))));
      debug_log($temp);
      //print_r($temp);
      $refno=str_replace('ClientRefNo:','',$temp[0]);
      debug_log($refno);
      $trnid=str_replace('TrnID:','',$temp[1]);
      debug_log($trnid);
      $CustomerNo=str_replace('CustomerNo:','',$temp[2]);
      debug_log($CustomerNo);
      $amount=str_replace('Amount:','',$temp[3]);
      debug_log($amount);
      $status=str_replace('Status:','',$temp[5]);
      debug_log($status);
      $statusmsg=str_replace('StatusMsg:','',$temp[6]);
      debug_log($statusmsg);
       $array    = array(
                'trnid'        => $trnid,
                'status'       => $statusmsg,
                
            );
            $this->db->where('trnid', $trnid);
            $this->db->update('recharge_entry', $array);
            debug_log($this->db->last_query());
            if($status=='2' ||$status=='3' || $status='5' ){
                $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
                $new_fund = $get_fund_uid + $amount;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            debug_log($this->db->last_query());
            
            $data = array(
                'transfer_from' => 'Admin',
                'transfer_to'   => $uid,
                'amount'        => $amount,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => 'Recharge refund',
                );
            $this->db->insert('transfer_balance_records', $data);
            debug_log($this->db->last_query());
                
            }
      /*foreach ($temp as $value) 
       {
        debug_log($value);
        $t = explode(':',$value);
        //debug_log($t);
        
        $ClientRefNo=$t[0];
        $TrnID=$t[1];
        $Amount=$t[2];
        //debug_log($t[0]);
        //debug_log('<br/>');
        //debug_log($t[1]);
        //debug_log('<br/>');
        //debug_log($t[2]);
        $array    = array(
                'trnid'        => $t[1],
                'status'       => $t[5],
            );
            $this->db->where('trnid', $t[1]);
            $this->db->update('recharge_entry', $array);
            //debug_log($this->db->last_query());
       }*/
    }

    public function pay2all_callback()
    {
      $uid     = $this->session->user_id;
      debug_log('inside pay2all_callback');
      debug_log(basename($_SERVER['REQUEST_URI']));
      $url_components = parse_url(basename($_SERVER['REQUEST_URI'])); 
      parse_str($url_components['query'], $data);
      $temp = explode(',',str_replace('%7D','',str_replace('hstm_callback?%7B','',str_replace('%22','',$_SERVER['REQUEST_URI']))));
      debug_log($temp);
      //print_r($temp);
      $refno=str_replace('ClientRefNo:','',$temp[0]);
      debug_log($refno);
      $trnid=str_replace('TrnID:','',$temp[1]);
      debug_log($trnid);
      $CustomerNo=str_replace('CustomerNo:','',$temp[2]);
      debug_log($CustomerNo);
      $amount=str_replace('Amount:','',$temp[3]);
      debug_log($amount);
      $status=str_replace('Status:','',$temp[5]);
      debug_log($status);
      $statusmsg=str_replace('StatusMsg:','',$temp[6]);
      debug_log($statusmsg);
       $array    = array(
                'trnid'        => $trnid,
                'status'       => $statusmsg,
        );
      $this->db->where('trnid', $trnid);
      $this->db->update('recharge_entry', $array);
      debug_log($this->db->last_query());
      if($status=='2' ||$status=='3' || $status='5' ){
                $get_fund_uid = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
                $new_fund = $get_fund_uid + $amount;
            $array    = array(
                'balance' => $new_fund,
            );
            $this->db->where('userid', $uid);
            $this->db->update('wallet', $array);
            debug_log($this->db->last_query());
            
            $data = array(
                'transfer_from' => 'Admin',
                'transfer_to'   => $uid,
                'amount'        => $amount,
                'time'          => date('Y-m-d H:i:s'),
                'remarks'       => 'Recharge refund',
                );
            $this->db->insert('transfer_balance_records', $data);
            debug_log($this->db->last_query());
                
            }
    }

    //axis bank
    public function payment_view()
    {
      require_once APPPATH . '/third_party/AXIS/AesForJava.php';
        
        $page_data['total_price_of_checking_out'] = $total_price_of_checking_out = 10;
        $page_data['page_title'] = 'Payment Gateway';
        $type = $this->session->_type_ == 'wallet' ? 'Online Wallet Topup' : 'Registration Fee';
        //$axis = json_decode($this->db->where('id','3')->get('payment_settings')->result_array()[0]['value']);
        $encryption_key= config_item('axis_encryption_key');
        $checksum_key = "axis";
        $user_id = $this->session->user_id;
        $data['name'] = html_escape($this->input->post('first_name'));
        $data['email'] = html_escape($this->input->post('email'));
        $data['phone'] = html_escape($this->input->post('phone'));
        $data['address1'] = html_escape($this->input->post('address1'));
        $data['role'] = 'admin';
        $total_amount=$this->input->post('amount');
        debug_log("here is amount for axis");
        debug_log($total_amount);
        debug_log($data['address1']);
        $rid = rand(9999, 999999);//rand(9999, 999999);
        $td = $this->db_model->select_multi('*', 'transaction', array('userid' => $user_id,'amount'=>$total_amount,'status'=>'Started','purpose'=>$type));
        $array = array(
              'gateway'        => 'Axis Bank',
              'time'           => time(),
              'transaction_id' => $rid,
              'status'         => 'Processing',
              'remarks'        => "processing",
              );
              $this->db->where(array('id' => $td->id));
              $this->db->update('transaction', $array);
        
        
        $cid = '6204';
        $email = $data['email'];
        
        $crn = $user_id;
        $amt = $total_amount;
        $ver = '1.0';
        $typ = 'TEST';
        $cny = 'INR';
        $rtu = base_url().'gateway/success_axis';
        //$email = 'test@gmail.com';
        $ppi = $data['name'].'|'.$data['name'].'|'.$data['phone'].'|'.$email.'|'.$amt;
        //$ppi = 'First'.'|south|1234567890|'.$email.'|'.$amt;
        $re1 = 'MN';
        $re2 = '';
        $re3 = '';
        $re4 = '';
        $re5 = '';

        /*CKS= hash("sha256",CID+RID+CRN+AMT+checksum_key)*/
        $cks = hash("sha256", $cid.$rid.$crn.$amt.$checksum_key);

        $str ='CID='.$cid.'&RID='.$rid.'&CRN='.$crn.'&AMT='.$amt.'&VER='.$ver.'&TYP='.$typ.'&CNY='.$cny.'&RTU='.$rtu.'&PPI='.$ppi.'&RE1='.$re1.'&RE2=&RE3=&RE4=&RE5=&CKS='.$cks;
        debug_log("axis bank string");
        debug_log($str);

        $aesJava = new AesForJava();
        $i = $aesJava->encrypt(urldecode($str), $encryption_key, 128);
        $page_data['axis_checksum'] = $i;
        //print_r($page_data['axis_checksum']);exit();
        $this->load->view('theme/axis_checkout', $page_data);
    }
//axis api successful function
   public function success_axis()
   {        
        preg_match_all('/(\w+)=([^&]+)/', $_SERVER["QUERY_STRING"], $pairs);
        $_GET = array_combine($pairs[1], $pairs[2]);
        require_once APPPATH . '/third_party/AXIS/AesForJava.php';
        
        $encryption_key= config_item('axis_encryption_key');

        $aes = new AesForJava();
        $qStr = $aes->decrypt(urldecode($_GET['i']), $encryption_key, 128);
        $newArr = explode('&', $qStr);
        $newArr2 = explode('=', $newArr['2']);
        
        //print_r($qStr);
        $status_text = $newArr2['1'];
        debug_log("this is the status text");
        debug_log($status_text);
        //print_r($status_text);exit();
        $rid = explode('=', $newArr['6'])[1];
        $crn = explode('=', $newArr['10'])[1];
        $amt=  explode('=', $newArr['12'])[1];
        if($status_text=='success')
        {
          $td = $this->db_model->select_multi('*', 'transaction', array(
            'userid' => $crn,'amount'=>$amt, 'transaction_id' =>$rid));
            if($td->purpose=="Online Wallet Topup")
            {
                $array = array('time'  => time(),
                        'status'       => 'Completed',);
                $this->db->where(array('id'=>$td->id));
                $this->db->update('transaction', $array);
                
                 //update wallet balance
                $wallet_balance = $this->db_model->select('balance', 'wallet', array('userid' =>  $crn));
                $this->db->where(array('userid' =>$crn));
                $this->db->update('wallet', array('balance' => $wallet_balance + $amt));
                wallet_log($this->db->last_query());

                $array = array(
                          'transfer_from' => 'Admin',
                          'transfer_to'   => $crn,
                          'amount'        => $amt,
                          'time'          => date('Y-m-d H:i:s'),
                          'remarks'       => 'Online Wallet Topup',
                      );
                $this->db->insert('transfer_balance_records', $array);
                debug_log($this->db->last_query());

                redirect(site_url('member/complete_add_fund'));
                
            }
            else if($td->purpose=="Registration Fee")
            {
                $array = array(
                                'time'           => time(),
                                'status'         => 'Completed',
                                
                              );
                      $this->db->where(array('id'=>$td->id));
                      $this->db->update('transaction', $array);
                      debug_log($this->db->last_query());
                      redirect(site_url('site/complete_registration'));
            }
        }
        //print_r($rid);
        //print_r($crn);exit();
        else{
            
        }
   }
    public function stripe_checkout(){
      /*$data['email']=$this->input->post('email');
      $data['first_name']=$this->input->post('first_name');
      
      $data['item_name']=$_SESSION['item_name'];

      $data['amount']=$_SESSION['amount'];*/
      $type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';
      $data['first_name']=$this->session->_user_name_;
      $data['email']=$this->session->_email_;
      $data['item_name']=$type;
      $data['amount']=$this->session->_price_;
      //print_r($data);

      $this->load->view('theme/stripe_checkout', $data); 

    }


    public function purchase_stripe()
    { 
        $data = array(); 
         
        // Get product data from the database 
        //$product = $this->product->getRows($id); 
         
        // If payment form is submitted with token 
        //print_r($this->input->post('stripeToken'));
        if($this->input->post('stripeToken'))
        { 
            // Retrieve stripe token and user info from the posted form data 
         
            $product['name'] = $this->input->post('name');
            $product['email'] = $this->input->post('email');
            $product['card_number']=$this->input->post('card_number');
            $product['stripeToken']=$this->input->post('stripeToken');
            $product['product_name']=$this->input->post('product_name');
            
            $product['price']=$this->session->_price_;
            
            $postData= $product; 
            
            // Make payment 
            $paymentID = $this->payment($postData); 
             
            // If payment successful 
            if($paymentID){ 
                redirect('products/payment_status/'.$paymentID); 
                //print_r('inside if called');exit();
            }else{ 
                $apiError = !empty($this->stripe_lib->api_error)?' ('.$this->stripe_lib->api_error.')':''; 
                $data['error_msg'] = 'Transaction has been failed!'.$apiError; 
                //print_r('inside else called');exit();

            } 
        } 
         
        // Pass product data to the details view 
        $data['product'] = $product; 
        $this->load->view('theme/stripe_checkout', $data); 
    } 

    public function payment($postData)
    { 
      $uid = $this->session->_user_id_;
      $username=$this->session->_user_name_;
      $email=$this->session->_email_;
      $phone=$this->session->_phone_;
      $td = $this->db_model->select_multi('*', 'transaction', array(
            'userid' => $this->session->_user_id_,'amount'=>$this->session->_price_, 'email_id' =>$email, 'time >=' =>strtotime('now') - 3600));
      //print_r($td);
      //debug_log('$td'.$this->db->last_query());
        //print_r($td);exit();
      $type = $td->purpose;

      //print_r($type);exit();
      // If post data is not empty 
      if(!empty($postData))
      { 
        // Retrieve stripe token and user info from the submitted form data 
            $token  = $postData['stripeToken']; 
            //print_r($token);
            $name =  $postData['name']; 
            $email = $postData['email'];
            $price = $postData['price'];
            //$type=   $postData['product_name'];
            // Add customer to stripe 
            $customer = $this->stripe_lib->addCustomer($email, $token); 
            //print_r($customer);
            if($customer)
            { 
              // Charge a credit or a debit card 
              $charge = $this->stripe_lib->createCharge($customer->id, $product_name, $price);
              //print_r($charge);exit();
              if($charge)
              { 
                // Check whether the charge is successful 
                //print_r('charge'.$charge);exit();
                if($charge['amount_refunded'] == 0 && empty($charge['failure_code']) && $charge['paid'] == 1 && $charge['captured'] == 1)
                { 
                  // Transaction details  
                  $transactionID = $charge['balance_transaction']; 
                  $paidAmount = $charge['amount']; 
                  $paidAmount = ($paidAmount/100); 
                  $paidCurrency = $charge['currency']; 
                  $payment_status = $charge['status']; 
                  // Insert tansaction data into the database 

                  $array = array(
                      'userid'         => $uid,
                      'name'           => $username,
                      'phone'          => $phone,
                      'amount'         => $paidAmount,
                      'gateway'        => 'Stripe',
                      'time'           => time(),
                      'payment_request_id'=>$transactionID,
                      
                      'status'         => $payment_status
                  );
                  //$this->db->insert('transaction', $array);
                  //debug_log('from stripe payment function'.$this->db->last_query());
                  $sms = 'Hello ' . $this->session->_user_name_ . ', \nGreetings from ' . config_item('company_name') . '\nThe order id towards ' . $type . ' is: ' . $transactionID ;
                  $messvar='Ok';
                  $phone_text='91'.$this->session->_phone_;
                  $this->common_model->sms($phone_text, urlencode($sms));
                  debug_log('from checkout transaction id'.$orderDetails['orderId']);
                  //it was already present
                  /*$orderData = array( 
                            'product_id' => $postData['product']['id'], 
                            'buyer_name' => $name, 
                            'buyer_email' => $email, 
                            'paid_amount' => $paidAmount, 
                            'paid_amount_currency' => $paidCurrency, 
                            'txn_id' => $transactionID, 
                            'payment_status' => $payment_status 
                        ); */
                  //print_r($orderData);exit();
                  //$orderID = $this->product->insertOrder($orderData); 
                  // If the order is successful 
                  if($payment_status == 'succeeded')
                  { 
                    //print_r($payment_status);
                    if($type=='Registration Fee')
                    {
                      //print_r('$type');exit();
                      $array = array(
                                'time'           => time(),
                                'gateway'        => 'Stripe',
                                'status'         => 'Completed',
                                'transaction_id' => $transactionID,
                              );
                      $this->db->where(array('id'=>$td->id));
                      $this->db->update('transaction', $array);
                      debug_log($this->db->last_query());

                      
                      redirect(site_url('site/complete_registration'));
                    }
                    else if ($type == 'Wallet Topup')
                    {
                      $array = array(
                                'time'           => time(),
                                'gateway'        => 'Stripe',
                                'status'         => 'Completed',
                                'transaction_id' => $transactionID,
                              );
                      $this->db->where(array('id'=>$td->id));
                      $this->db->update('transaction', $array);
                      debug_log($this->db->last_query());
                      
                       //update wallet balance
                      $wallet_balance = $this->db_model->select('balance', 'wallet', array('userid' => $uid));
                      $this->db->where(array('userid' => $uid));
                      $this->db->update('wallet', array('balance' => $wallet_balance + $paidAmount));
                      wallet_log($this->db->last_query());

                      $array = array(
                          'transfer_from' => 'Admin',
                          'transfer_to'   => $uid,
                          'amount'        => $paidAmount,
                          'time'          => date('Y-m-d H:i:s'),
                          'remarks'       => 'Online Wallet Topup',
                      );
                      $this->db->insert('transfer_balance_records', $array);
                      debug_log($this->db->last_query());

                      redirect(site_url('member/complete_add_fund'));
                    }
                    //return $orderID; 
                  } 
                  else
                  {
                    if($type=='Registration Fee')
                    {
                      redirect(site_url('site/failed_registration'));
                    }
                  }
                } 
              } 
            } 
      } 
      return false; 
    } 
     
    public function payment_status($id)
    { 
        $data = array(); 
         
        // Get order data from the database 
        $order = $this->product->getOrder($id); 
         
        // Pass order data to the view 
        $data['order'] = $order; 
        $this->load->view('products/payment-status', $data); 
    } 
    public function coinpayment_status(){
      debug_log('from coinpayment status function'.$_REQUEST['txn_id']);
      redirect(site_url('site/complete_registration'));
    }

    public function coinpayment_success()
    {
        $this->session->set_userdata('_status_', 'Paid');
        $time = time();
        $userid = $this->session->_user_id_;
        $username = $this->session->_user_name_;
        $phone = $this->session->_phone_;
        $amount = $this->session->_price_;
        $email = $this->session->_email_;

        $td = $this->db_model->select_multi('*', 'transaction', array(
            'userid' => $this->session->_user_id_,'amount'=>$this->session->_price_, 'transaction_id !=' => '', 'time >=' =>strtotime('now') - 3600));
        debug_log($this->db->last_query());
        
        $type = $td->purpose;
        debug_log('from coinpayment success page'.$type);
        debug_log($this->session->_type_);
        debug_log('from coinpayment success page'.$username);
        debug_log('from coinpayment success page'.$email);
        
        debug_log($td->status);
       /* if($this->session->_type_==''){
           $this->session->_type_=$td->purpose;
           //$this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';
           $this->session->_type_ = $this->session->_type_ =='Registration Fee' ? 'userid' : 'wallet';
            debug_log($td->purpose);
            debug_log($this->session->_type_);
            debug_log($this->db->last_query());
        }*/

        if ($type == 'Registration Fee')
        {
          $array = array(
            'time'           => time(),
            'gateway'        => 'CoinPayments',
            'status'         => 'Completed',
          );
          $this->db->where(array('id'=>$td->id));
          $this->db->update('transaction', $array);
          debug_log($this->db->last_query());
          redirect(site_url('site/complete_registration'));
        }
        else if ($type == 'Wallet Topup')
        {
          redirect(site_url('member/complete_add_fund'));
        }
      redirect(site_url('member/complete_add_fund'));
    }
    public function coinpayment_fail()
    {
      redirect(site_url('site/failed_registration'));
    }
    public function coinpayment_ipn()
    {
      debug_log('coin payment ipn executed');
      
      $user_id=$_POST['user_id'];
      $user_name=$_POST['first_name'];
      $email=$_POST['email'];
      $order_total=$_POST['subtotal'];
      $txn_id = htmlentities($_POST['txn_id']);

      // HMAC Signature verified at this point, load some variables.
      $amount1 = floatval($_POST['amount1']);
      $amount2 = floatval($_POST['amount2']);
      $currency1 = $_POST['currency1'];
      $order_currency=$currency1;
      $currency2 = $_POST['currency2'];

      $ipn_type = $_POST['ipn_type'];
      $item_name = $_POST['item_name'];
      $item_number = $_POST['item_number'];

      $status = intval($_POST['status']);
      $status_text = $_POST['status_text'];

      debug_log('user id is: '.$user_id);
      debug_log('Name '.$user_name);
      debug_log('Email '.$email);
      debug_log('txn_id '.$txn_id);
  
      debug_log('amount1 '.$amount1);
      debug_log('amount2 '.$amount2);
      debug_log('currency1 '.$currency1);
      debug_log('currency2 '.$currency2);

      debug_log('order total '.$order_total);
      debug_log('status '.$status);
      debug_log('status text '.$status_text);

      if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
       $this->errorAndDie('IPN Mode is not HMAC');       
      }
      if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
        $this->errorAndDie('No HMAC signature sent.');        
      }
      $request = file_get_contents('php://input');
      if ($request === FALSE || empty($request)) {
        $this->errorAndDie('Error reading POST data');
      }
      $cp_ipn_secret=config_item('coinpayment_ipn_secret');
      $cp_merchant_id=config_item('coinpaymentmerchant_id');
      if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($cp_merchant_id)) {
       $this->errorAndDie('No or incorrect Merchant ID passed');
      }
      $hmac = hash_hmac('sha512', $request, trim($cp_ipn_secret));
      if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])){
        //if ($hmac != $_SERVER['HTTP_HMAC']) { <-- Use this if you are running a version of PHP below 5.6.0 without the hash_equals function
        $this->errorAndDie('HMAC signature does not match');
      }
      if ($ipn_type != 'button'){ 
        // Advanced Button payment
        $this->errorAndDie('IPN OK: Not a button payment');
      }
      // Check the original currency to make sure the buyer didn't change it.
      if ($currency1 != $order_currency){
        $this->errorAndDie('Original currency mismatch!');
      }
      // Check amount against order total
      if ($amount1 < $order_total) {
        $this->errorAndDie('Amount is less than order total!');
      }
      
      /*
      $user_name = 'Business';
      $email = 'business@gmlm.com';
      $order_total = '10000';
      $txn_id = '123456789';
      $status_text = 'Complete';
      $status = 100;
      */

      $td = $this->db_model->select_multi('*', 'transaction', array('name' => $user_name,'email_id'=>$email,'amount'=>$order_total,'transaction_id'=>$txn_id));
      debug_log($this->db->last_query());
      debug_log('TD Status ' .$td->status);
      debug_log('$status '.$status);

      if($td ==''){
        $this->db->query("
                 UPDATE transaction SET transaction_id='".$txn_id."'
                    WHERE id IN (
                        SELECT id FROM (
                            SELECT id FROM transaction where name ='". $user_name."' and email_id = '".$email."' and amount = ".$order_total." and status != 'Completed' and status != 'Failed' ORDER BY id DESC  LIMIT 1) tmp)");
      }

      debug_log($this->db->last_query());

      $td = $this->db_model->select_multi('*', 'transaction', array('name' => $user_name,'email_id'=>$email,'amount'=>$order_total,'transaction_id'=>$txn_id));

      if($td != ''){
        if (($status >= 100 || $status == 2) && ($td->status != 'Completed') &&($status_text == 'Complete'))
        {
          // payment is complete or queued for nightly payout, success
          debug_log('success from coinpayments_ipn function');
          $this->session->set_userdata('_status_', 'Paid');

          if($td->remarks != '')
          {
              $remarks = $td->remarks . '<br/><br/>' .  date('Y-m-d h:i A') . '<br/>' . $status_text; 
          } else {
              $remarks = date('Y-m-d h:i A') . '<br/>' . $status_text; 
          }

          $array = array(
              'gateway'        => 'Coinpayments.net',
              'time'           => time(),
              'transaction_id' => $txn_id,
              'status'         => 'Completed',
              'remarks'        => $remarks,
          );
          $this->db->where(array('id' => $td->id));
          $this->db->update('transaction', $array);
          debug_log($this->db->last_query());

          if ($td->purpose== 'Registration Fee') 
          {
            debug_log('ready to redirect to complete_registration');
            $sms = 'Hello ' . $user_name . ', \n\n Your Payment towards '.$txn_id.' is completed. Your transaction id is '.$txn_id ;
            $messvar='Ok';
            $phone_text='91'.$phone;
            $this->common_model->sms($phone_text, urlencode($sms));
          }
          else{
            $userid=$td->userid;
            $wallet_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
              $this->db->where(array('userid' => $userid));
              $this->db->update('wallet', array('balance' => $wallet_balance + $order_total));
              wallet_log($this->db->last_query());

              $array = array(
                  'transfer_from' => 'Admin',
                  'transfer_to'   => $userid,
                  'amount'        => $order_total,
                  'time'          => date('Y-m-d H:i:s'),
                  'remarks'       => 'Online Wallet Topup',
              );
              $this->db->insert('transfer_balance_records', $array);
              debug_log($this->db->last_query());
          }
        } 
        else if ($status < 0) 
        {
          //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
           //$user_name=$_POST['first_name'];
          debug_log($txn_id);
           $td = $this->db_model->select_multi('*', 'transaction', array('name' => $user_name,'email_id'=>$email,'amount'=>$order_total,'transaction_id'=>$txn_id));
            debug_log($this->db->last_query());
            debug_log('if status is < 0'.$td->status);

            if($td->remarks != '')
            {
                $remarks = $td->remarks . '<br/><br/>' .  date('Y-m-d h:i A') . '<br/>' . $status_text; 
            } else {
                $remarks = date('Y-m-d h:i A') . '<br/>' . $status_text; 
            }


            if($td->status!='Completed')
            {
              $this->session->set_userdata('_status_', 'Paid');
              $array = array(
              'gateway'        => 'Coinpayments.net',
              'time'           => time(),
              'transaction_id' => $txn_id,
              'status'         => 'Failed',
              'remarks'        => $remarks,
              );
              $this->db->where(array('id' => $td->id));
              $this->db->update('transaction', $array);
              debug_log($this->db->last_query());
            }
        } 
        else 
        {
          //payment is pending, you can optionally add a note to the order page
          if(($td->status!='Completed') && ($td->status!='Failed'))
            {
              //$this->session->set_userdata('_status_', 'Paid');

              if($td->remarks != '')
              {
                  $remarks = $td->remarks . '<br/><br/>' .  date('Y-m-d h:i A') . '<br/>' . $status_text; 
              } else {
                  $remarks = date('Y-m-d h:i A') . '<br/>' . $status_text; 
              }

              $array = array(
              'gateway'        => 'Coinpayments.net',
              'time'           => time(),
              'transaction_id' => $txn_id,
              'status'         => 'Processing',
              'remarks'        => $remarks,
              );
              $this->db->where(array('id' => $td->id));
              $this->db->update('transaction', $array);
              debug_log($this->db->last_query());
            }
        }  
      }
      //die('IPN OK');
      exit();
    }
     //coin payment function for ipn
    public function errorAndDie($error_msg) 
    {
        $cp_ipn_secret=config_item('coinpayment_ipn_secret');
        $cp_merchant_id=config_item('coinpaymentmerchant_id');
        $cp_debug_email=config_item('coinpayment_debug_email');
        if (!empty($cp_debug_email)) 
        {
            $report = 'Error: '.$error_msg.'\n\n';
            $report .= 'POST Data\n\n';
            foreach ($_POST as $k => $v) {
                $report .= '|$k| = |$v|\n';
            }
            mail($cp_debug_email, 'CoinPayments IPN Error', $report);
        }
        //die('IPN Error: '.$error_msg);
        debug_log('IPN Error: '.$error_msg);
        exit();
    }
     //cashfree payment gateway
    public function generateSignature($postData)
    {
      $secretKey = config_item('cashfree_secretkey');
      ksort($postData);
      $signatureData = '';
      foreach ($postData as $key => $value){
        $signatureData .= $key.$value;
      }
      $signature = hash_hmac('sha256', $signatureData, $secretKey,true);
      $signature = base64_encode($signature);
      return $signature;
    }
    public function checkout()
    {
      //details to insert into transaction table
      $time = time();
      $userid = $this->session->_user_id_;
      $username = $this->session->_user_name_;
      $phone = $this->session->_phone_;
      $amount = $this->session->_price_;
      $type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';


        $orderAmount = $_POST['amount'];
        $notifyUrl = site_url('gateway/notify');
        $returnUrl = site_url('gateway/return');
        $orderDetails = array();
        $orderDetails['notifyUrl'] = $notifyUrl;
        $orderDetails['returnUrl'] = $returnUrl;
        $userDetails =$this->getUserDetails($orderId);
        $order = $this->getOrderDetails($orderId);

      $orderDetails['customerName'] = $userDetails['customerName'];
      $orderDetails['customerEmail'] = $userDetails['customerEmail'];
      $orderDetails['customerPhone'] = $userDetails['customerPhone'];
      $orderDetails['orderId'] = $order['orderId'];
      $orderDetails['orderAmount'] = $order['orderAmount'];
      $orderDetails['orderNote'] = $order['orderNote'];
      $orderDetails['orderCurrency'] = $order['orderCurrency'];
      $orderDetails['appId'] = config_item('cashfree_appId');
      $orderDetails['signature'] = $this->generateSignature($orderDetails);

      $array = array(
            'userid'         => $userid,
            'name'           => $username,
            'phone'          => $phone,
            'amount'         => $amount,
            'gateway'        => 'Cashfree',
            'time'           => $time,
            'payment_request_id'=>$orderDetails['orderId'],
            'purpose'        => $type,
            'status'         => 'Started'
        );
        $this->db->insert('transaction', $array);

        $sms = 'Hello ' . $this->session->_user_name_ . ', \nGreetings from ' . config_item('company_name') . '\nThe order id towards ' . $type . ' is: ' . $orderDetails['orderId'] ;
        $messvar='Ok';
        $phone_text='91'.$this->session->_phone_;
        $this->common_model->sms($phone_text, urlencode($sms));
      debug_log('from checkout transaction id'.$orderDetails['orderId']);
      $this->load->view('theme/cashfree_checkout',$orderDetails);

    }
    public function getOrderDetails($orderId) 
    {
      return array(
              'orderId' => time(),
              'orderAmount' =>$this->session->_price_ ,
              'orderNote' => 'test order',
              'orderCurrency' => config_item('cashfree_currency')
              );
    }

    public function getUserDetails($orderId) 
    {
      $name=$this->session->_user_name_;
      $email=$this->session->_email_;
      $phone=$this->session->_phone_;
      return array('customerName' => $name,
                   'customerEmail' => $email,
                    'customerPhone' => $phone,
                  );
    }

    public function return()
    {
      debug_log('return function');
      $userid = $this->session->_user_id_;
      debug_log($this->session->_user_id_);
      $username = $this->session->_user_name_;
      $orderId = $_POST['orderId'];
      debug_log('orderid from return:'.$orderId);
      $orderAmount = $_POST['orderAmount'];
      $referenceId = $_POST['referenceId'];
      $txStatus = $_POST['txStatus'];
      $paymentMode = $_POST['paymentMode'];
      $txMsg = $_POST['txMsg'];
      $txTime = $_POST['txTime'];
      $signature = $_POST['signature'];
      $data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
      debug_log('from return'.$txStatus);
      $secretKey = config_item('cashfree_secretkey');
      $hash_hmac = hash_hmac('sha256', $data, $secretKey, true) ;
      $computedSignature = base64_encode($hash_hmac);
      $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $orderId));
      debug_log($td.'td from return');
      if($txStatus=='SUCCESS' && $td->status != 'Completed')
      {
        if ($signature == $computedSignature) 
        {
           $array = array(
                    'amount'         => $orderAmount,
                    'transaction_id' => $referenceId,
                    'status'         => 'Completed'
                );
                $this->db->where(array('payment_request_id' => $orderId));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());
                debug_log('computed signature'.$computedSignature);
                debug_log('signature'.$signature);
                if ($td->purpose == 'Wallet Topup') 
                {
                    $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
                    debug_log($get_balance.'from return');
                    debug_log($orderAmount.'from return');
                    $array    = array('balance' => $get_balance + $orderAmount,);
                    $this->db->where('userid', $userid);
                    $this->db->update('wallet', $array);
                    wallet_log('update wallet from return');
                    wallet_log($this->db->last_query());
                    //$get_topup = $this->db_model->select('topup', 'member', array('id' => $userid));

                    //$array = array('topup' => $get_topup + $orderAmount);
                    //$this->db->where('id', $userid);
                    //$this->db->update('member', $array);
                    //debug_log($this->db->last_query());

                    $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $userid,
                        'amount'        => $orderAmount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                    );
                    $this->db->insert('transfer_balance_records', $array);
                    debug_log($this->db->last_query());

                }
                $sms = 'Hello ' . $td->name . ', \n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                $messvar='Ok';
                $phone_text='91'.$td->phone;
                $this->common_model->sms($phone_text, urlencode($sms));
        
                if ($this->session->_type_ == 'userid')
                {
                  redirect(site_url('site/complete_registration'));
                }
                else if ($this->session->_type_ == 'wallet')
                {
                  redirect(site_url('member/complete_add_fund'));
                }
        }
         //echo 'your order is successful';
      }
     
      else  
      {
            if($td->status!='Completed')
            {
              $array = array(
                    'amount'         => $amount,
                    'transaction_id' => $referenceId,
                    'status'         => 'Failed'
                );
                $this->db->where(array('payment_request_id' => $orderId));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());

                $sms = 'Hello ' . $data['buyer_name'] . ', \n Your Payment towards '.$td->purpose.' didnot complete. Your transaction id is '.$time.' Please raise a support ticket incase of amount is deducted from your account' ;
                $messvar='Ok';
                $phone_text='91'.$phone;
                $this->common_model->sms($phone_text, urlencode($sms));

            }
             if ($this->session->_type_ == 'userid')
             {
               redirect(site_url('site/failed_registration'));
             }
             else if ($this->session->_type_ == 'wallet') 
              {
               redirect(site_url('member/failed_fund'));
             }
             // Reject this call
      }
    }

    public function notify()
    {
      debug_log('notify function');
      $username = $this->session->_user_name_;
      $orderId = $_POST['orderId'];
      debug_log('orderid from notify:'.$orderId);
      $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $orderId));
      $userid=$td->userid;
      debug_log('userId from notify after td'.$userid);
      $orderAmount = $_POST['orderAmount'];
      $referenceId = $_POST['referenceId'];
      $txStatus = $_POST['txStatus'];
      $paymentMode = $_POST['paymentMode'];
      $txMsg = $_POST['txMsg'];
      $txTime = $_POST['txTime'];
      $signature = $_POST['signature'];
      $data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
      debug_log('from return'.$txStatus);
      $secretKey = config_item('cashfree_secretkey');
      $hash_hmac = hash_hmac('sha256', $data, $secretKey, true) ;
      $computedSignature = base64_encode($hash_hmac);
      $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $orderId));
      if($txStatus=='SUCCESS' && $td->status != 'Completed')
      {
        if ($signature == $computedSignature) 
        {
           $array = array(
                    'amount'         => $orderAmount,
                    'transaction_id' => $referenceId,
                    'status'         => 'Completed'
                );
                $this->db->where(array('payment_request_id' => $orderId));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());
                debug_log('computed signature'.$computedSignature);
                debug_log('signature'.$signature);
                if ($td->purpose == 'Wallet Topup') 
                {
                    $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
                    debug_log($get_balance.'from notify');
                    $array    = array('balance' => $get_balance + $orderAmount,);
                    $this->db->where('userid', $userid);
                    $this->db->update('wallet', $array);
                    wallet_log('update wallet from notify');
                    wallet_log($this->db->last_query());
                    
                    //$get_topup = $this->db_model->select('topup', 'member', array('id' => $userid));
                    //$array = array('topup' => $get_topup + $orderAmount);
                    //$this->db->where('id', $userid);
                    //$this->db->update('member', $array);
                    //debug_log($this->db->last_query());

                    $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $userid,
                        'amount'        => $orderAmount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                    );
                    $this->db->insert('transfer_balance_records', $array);
                    debug_log($this->db->last_query());

                }
                $sms = 'Hello ' . $td->name . ', \n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                $messvar='Ok';
                $phone_text='91'.$td->phone;
                $this->common_model->sms($phone_text, urlencode($sms));
        
                if ($this->session->_type_ == 'userid')
                {
                  redirect(site_url('site/complete_registration'));
                }
                else if ($this->session->_type_ == 'wallet')
                {
                  redirect(site_url('member/complete_add_fund'));
                }
        }
         //echo 'your order is successful';
      }
     
      else  
      {
            if($td->status!='Completed')
            {
              $array = array(
                    'amount'         => $amount,
                    'transaction_id' => $referenceId,
                    'status'         => 'Failed'
                );
                $this->db->where(array('payment_request_id' => $orderId));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());

                $sms = 'Hello ' . $data['buyer_name'] . ', \n Your Payment towards '.$td->purpose.' didnot complete. Your transaction id is '.$time.' Please raise a support ticket incase of amount is deducted from your account' ;
                $messvar='Ok';
                $phone_text='91'.$phone;
                $this->common_model->sms($phone_text, urlencode($sms));

            }
             if ($this->session->_type_ == 'userid')
             {
               redirect(site_url('site/failed_registration'));
             }
             else if ($this->session->_type_ == 'wallet') 
             {
               redirect(site_url('member/failed_fund'));
             }
             // Reject this call
      }
    }

    //end of cashfree payment gateway integration
 
    public function status_block_io($address)
    {
        $apiKey   = config_item('api_key');
        $version  = 2; // API version
        $pin      = config_item('secret_pin');
        $block_io = new BlockIo($apiKey, $pin, $version);
        $balance  = json_encode($block_io->get_address_balance(array('addresses' => $address)));
        $balance  = json_decode($balance, true);

        $balance = $balance['data']['available_balance'] + $balance['data']['pending_received_balance'];
        if ($balance >= $this->common_model->curl('https://blockchain.info/tobtc?currency=' . trim(config_item('iso_currency')) . '&value=' . $this->session->_price_ . '')) 
        {
            $this->session->set_userdata('_status_', 'Paid');
            $array = array(
                'userid'         => $this->session->_user_id_,
                'amount'         => $this->session->_price_,
                'gateway'        => 'Block.io',
                'time'           => time(),
                'transaction_id' => $address,
            );
            $this->db->insert('transaction', $array);

            if ($this->session->_type_ == 'userid') {
                $array = array('topup' => $this->session->_price_);
                $this->db->where('id', $this->session->_user_id_);
                $this->db->update('member', $array);
            }

        }
        else {
            $array = array(
                'userid'         => $this->session->_user_id_,
                'amount'         => config_item('currency') . $this->session->_price_ . '/Payment Status Unknown',
                'gateway'        => 'Block.io',
                'time'           => time(),
                'transaction_id' => 'Paid to Wallet: ' . $address,
            );
            $this->db->insert('transaction', $array);
        }
        redirect(site_url('gateway/status/block_io'));
    }

    /*public function paypal_ipn($type)
    {
        if ($_REQUEST['payment_status'] == 'Completed') {
           /* $array   = array('dummy_text'=> 'Success',
                );
            $this->db->insert('dummy', $array);

            $amount = $this->common_model->filter($_REQUEST['mc_gross'], 'float');
            $userid = htmlentities($_REQUEST['invoice']); // userid or order id.
            $array   = array(
                'userid'         => $userid,
                'amount'         => $amount,
                'gateway'        => 'PayPal',
                'time'           => time(),
                'transaction_id' => $_REQUEST['txn_id'],
            );
            $this->db->insert('transaction', $array);
            if ($type == 'userid') {
                $array = array('topup' => $amount);
                $this->db->where('id', $userid);
                $this->db->update('member', $array);
            }
        }
        else {
           $array   = array('dummy_text'=> 'failure',
            );
            $this->db->insert('dummy', $array);
        }
    }*/
    //function paypal_form_submit()
    function buy()
    {
        // Set variables for paypal form
        $price=$this->input->post('amount');
        $plan =$this->session->_plan_;
        $type = $this->session->_type_ == 'wallet' ? 'Online Wallet Topup' : 'Registration Fee';

        //$type=$this->input->post('type');
        $first_name=$this->input->post('first_name');
        $user_id=$this->session->_user_id_;
        $name =$this->session->_user_name_;
        $phone = $this->session->_phone_;
        $sponsor = $this->session->_sponsor_;
        $email = $this->session->_email_;
        $d_password = $this->session->_d_password_;
        $password = $this->session->_password_;
        $secure_password = $this->session->_secure_password_;
        $address_1=$this->session->_address_;
        $city=$this->session->_city_;
        $state=$this->session->_state_;
        $zipcode=$this->session->_zipcode_;
        $country=$this->session->_country_;
        $placement_leg=$this->session->_placement_leg_;
        $details_arr=array("user_id"=>$user_id,"user_name"=>$name,"phone"=>$phone,"sponsor"=>$sponsor,"email"=>$email);
        $new_array=json_encode($details_arr);
        //print_r($new_array);exit();
        $details_intodb=json_decode($new_array);

        //var_dump($details_intodb);
        //print_r($details_intodb);exit();

        //print_r($name.$phone);exit();

        
        
        $array = array('amount' => $price,'gateway'=>"Paypal",);

        $this->db->where(array('payment_request_id'=>$this->session->payment_request_id));
        $this->db->update('transaction', $array);

        debug_log($this->db->last_query());
        
        
        $returnURL = base_url().'gateway/status/paypal'; //payment success url 
        $cancelURL = base_url().'gateway/paypal_cancel'; //payment cancel url 
        $notifyURL = base_url().'gateway/ipn_paypal'; //ipn url
        
        // Add fields to paypal form 
        $this->paypal_lib->add_field('return', $returnURL); 
        $this->paypal_lib->add_field('cancel_return', $cancelURL); 
        $this->paypal_lib->add_field('notify_url', $notifyURL); 
        $this->paypal_lib->add_field('item_name', $type); 
        $this->paypal_lib->add_field('first_name', $first_name); 
        $this->paypal_lib->add_field('custom', $this->session->payment_request_id);
        $this->paypal_lib->add_field('txn_id', $rand); 
        $this->paypal_lib->add_field('amount',$price); 
         
        // Render paypal form 
        $this->paypal_lib->paypal_auto_form(); 
    }

    

    function ipn_paypal()
    { 
      debug_log("ipn paypal called");
        // Retrieve transaction data from PayPal IPN POST 
        $paypalInfo = $this->input->post(); 
        debug_log($paypalInfo);
         
        if(!empty($paypalInfo))
        { 
            //Validate and get the ipn response 
            $ipnCheck = $this->paypal_lib->validate_ipn($paypalInfo); 
            debug_log("ipncheck from sowmya");
            debug_log($ipnCheck);
            // Check whether the transaction is valid 
            if($ipnCheck)
            { 
              
              $prevPayment=$this->db_model->select('userid', 'transaction', array('transaction_id' => $paypalInfo["txn_id"],'status'=>'Completed'));
                if(!$prevPayment)
                { 
                  debug_log("hello sowmya inside if");
                    // Insert the transaction data in the database 
                    $data['user_id']    = $paypalInfo["custom"]; 
                    $data['product_id']    = $paypalInfo["item_number"]; 
                    $data['txn_id']    =   $paypalInfo["txn_id"]; 
                    $data['payment_gross']    = $paypalInfo["mc_gross"]; 
                    $data['currency_code']    = $paypalInfo["mc_currency"]; 
                    $data['payer_name']    = trim($paypalInfo["first_name"].' '.$paypalInfo["last_name"], ' '); 
                    $data['payer_email']    = $paypalInfo["payer_email"]; 
                    $data['status'] = $paypalInfo["payment_status"]; 
                    $data['verify_sign']=$paypalInfo["verify_sign"]; 
                    $array = array(
                           'time'           => time(),
                           'payment_request_id'=>$data['txn_id'],
                           'status'         => $data['status'],
                           'transaction_id'=>$data['verify_sign']
                    );

                  $this->db->where(array('payment_request_id' => $data['txn_id']));
                  $this->db->update('transaction', $array);
                  debug_log('from paypal payment function sowmya'.$this->db->last_query());
                    
                } 
            } 
        } 
    }
    function paypal_success()
    { 
        // Get the transaction data 
        //print_r("success");exit();
        $paypalInfo = $this->input->post();
        //print_r($paypalInfo);exit();
         
        $productData = array(); 
        if(!empty(!empty($paypalInfo['txn_id']) && !empty($paypalInfo['mc_gross']) && !empty($paypalInfo['verify_sign']) && !empty($paypalInfo['payment_status'])))
        { 
            $item_name = $paypalInfo['item_name']; 
            $item_number = $paypalInfo['item_number']; 
            $txn_id = $paypalInfo["txn_id"]; 
            $payment_amt = $paypalInfo["mc_gross"]; 
            $currency_code = $paypalInfo["mc_currency"]; 
            $status = $paypalInfo["payment_status"]; 
             
            // Get product info from the database 
            //$productData = $this->product->getRows($item_number); 
             
            // Check if transaction data exists with the same TXN ID 
            $paymentstatus=$this->db_model->select('userid', 'transaction', array('transaction_id' => $paypalInfo["txn_id"],'status'=>'Completed'));
            if($status=='Completed')
            {
              redirect(site_url('site/complete_registration'));
            }
        }
            
    } 
  
      
     public function paypal_cancel()
     { 
        // Load payment failed view 
        redirect(site_url('site/failed_registration'));
     } 

    public function instamojo_ipn()
    {
        
        $data     = $_POST;
        $payment_id = $data['payment_id'];
        $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $data['payment_request_id']));
        $username = $data['buyer_name'] == '' ? $td->name : $data['buyer_name'];
        $phone = $data['phone'] == '' ? $td->phone : $data['phone'];
        $amount = $data['amount'];
        $userid = $td->userid;
        debug_log(' User ID ' . $td->userid);
        debug_log('Phone ' . $phone);
        
        $mac_provided = $data['mac']; // Get the MAC from the POST data
        unset($data['mac']); // Remove the MAC key from the data.
        $ver   = explode('.', phpversion());
        $major = (int)$ver[0];
        $minor = (int)$ver[1];
        
        if ($major >= 5 and $minor >= 4) {
            ksort($data, SORT_STRING | SORT_FLAG_CASE);
        }
        else {
            uksort($data, 'strcasecmp');
        }
        $mac_calculated = hash_hmac('sha1', implode('|', $data), config_item('instamojo_salt'));
        debug_log('$mac_calculated ' . $mac_calculated . ' $mac_provided ' . $mac_provided);
        debug_log('Data Status - ' . $data['status']);
        if ($mac_provided == $mac_calculated) {
            $payment_id = $data['payment_id'];
            $amount     = $data['amount'];
            if (($data['status'] == 'Credit') && ($td->status != 'Completed')){
                $array = array(
                    'amount'         => $amount,
                    'transaction_id' => $payment_id,
                    'status'         => 'Completed'
                );
                $this->db->where(array('payment_request_id' => $data['payment_request_id']));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());

                if ($td->purpose == 'Wallet Topup') {

                    $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
                    $array    = array('balance' => $get_balance + $amount,);
                    $this->db->where('userid', $userid);
                    $this->db->update('wallet', $array);
                    wallet_log($this->db->last_query());

                    //$get_topup = $this->db_model->select('topup', 'member', array('id' => $userid));
                    //$array = array('topup' => $get_topup + $amount);
                    //$this->db->where('id', $userid);
                    //$this->db->update('member', $array);
                    //debug_log($this->db->last_query());

                    $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $userid,
                        'amount'        => $amount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                    );
                    $this->db->insert('transfer_balance_records', $array);
                    debug_log($this->db->last_query());

                }

                $sms = 'Hello ' . $td->name . ', \n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                $messvar='Ok';
                $phone_text='91'.$td->phone;
                $this->common_model->sms($phone_text, urlencode($sms));

            } else if($td->status != 'Completed'){

                $array = array(
                    'amount'         => $amount,
                    'transaction_id' => $payment_id,
                    'status'         => 'Failed'
                );
                $this->db->where(array('payment_request_id' => $data['payment_request_id']));
                $this->db->update('transaction', $array);
                debug_log($this->db->last_query());

                $sms = 'Hello ' . $data['buyer_name'] . ', \n Your Payment towards '.$td->purpose.' didnot complete. Your transaction id is '.$time.' Please raise a support ticket incase of amount is deducted from your account' ;
                $messvar='Ok';
                $phone_text='91'.$phone;
                $this->common_model->sms($phone_text, urlencode($sms));
            }
        }
    }

    public function status($pg)
    {
        switch ($pg) {
            case ($pg == 'block_io'):
                break;
            case ($pg == 'paypal'): 
                debug_log(basename($_SERVER['REQUEST_URI']));
                $paypalInfo = $this->input->post();
                debug_log('Paypal Post Info');
                debug_log($paypalInfo);
                $productData = array(); 
                if(!empty(!empty($paypalInfo['txn_id']) && !empty($paypalInfo['mc_gross']) && !empty($paypalInfo['verify_sign']) && !empty($paypalInfo['payment_status'])))
               { 
                  $item_name = $paypalInfo['item_name']; 
                  $item_number = $paypalInfo['item_number']; 
                  $txn_id = $paypalInfo["txn_id"]; 
                  $payment_amt = $paypalInfo["mc_gross"]; 
                  $currency_code = $paypalInfo["mc_currency"]; 
                  $status = $paypalInfo["payment_status"]; 
                  $order_status=$paypalInfo["payment_status"]; 
                  // Get product info from the database 
                  //$productData = $this->product->getRows($item_number); 
             
                  // Check if transaction data exists with the same TXN ID

                  $td=$this->db_model->select_multi('*', 'transaction', array('payment_request_id' =>$paypalInfo["custom"] ,'purpose'=>$paypalInfo["item_name"],'amount'=>$paypalInfo["mc_gross"]));
                  debug_log("td from paypal");
                  debug_log($this->db->last_query());

                  //print_r($details);exit();
                  if($status=='Completed' && $td->status!='Completed')
                  {
                    $array = array(
                    'transaction_id' => $paypalInfo["txn_id"],
                    'status'         => 'Completed'
                    );
                    $this->db->where(array('payment_request_id' =>$paypalInfo["custom"] ,'purpose'=>$paypalInfo["item_name"],'amount'=>$paypalInfo["mc_gross"]));
                    $this->db->update('transaction', $array);
                    debug_log("status completed and before wallet");
                    debug_log($this->db->last_query());
                     if ($td->purpose == 'Online Wallet Topup') 
                    {
                      $data = $this->db_model->select_multi("*", 'member', array('id' => $td->userid));
                      debug_log($this->db->last_query());
                      session_unset();

                      $session = md5($user . time());
                      $this->session->set_userdata(array(
                          'user_id' => $data->id,
                          '_user_id_' => $data->id,
                          '_phone_' => $data->phone,
                          '_type_'  => 'wallet',
                          '_price_' => $paypalInfo["mc_gross"],
                          'email' => $data->email,
                          'name' => $data->name,
                          'ip' => $data->last_login_ip,
                          'last_login' => $data->last_login,
                          'session' => $session,
                          'role'=>$data->role,
                      ));

                      $data2 = array(
                          'last_login_ip' => $this->input->ip_address(),
                          'last_login' => time(),
                          'session' => $session,
                      );
                      $this->db_model->update($data2, 'member', array('id' => $data->id));

                      debug_log($this->db->last_query());

                      debug_log($this->session->user_id);
                      debug_log($this->session->_user_id_); 

                      $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $td->userid));
                      $array    = array('balance' => $get_balance + $td->amount,);
                      $this->db->where('userid', $td->userid);
                      $this->db->update('wallet', $array);
                      wallet_log($this->db->last_query());
                      debug_log("online wallet topup".$this->db->last_query());
                      
                      $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $td->userid,
                        'amount'        => $td->amount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                      );
                      $this->db->insert('transfer_balance_records', $array);
                      debug_log($this->db->last_query());
                    }
                    else
                    {
                      $details=$this->db_model->select('details', 'transaction', array('payment_request_id' =>$paypalInfo["custom"] ,'purpose'=>$paypalInfo["item_name"],'amount'=>$paypalInfo["mc_gross"]));

                      $vars = json_decode($details);
                      debug_log($vars);

                      $this->session->set_userdata('_user_id_', $vars->userid);
                      $this->session->set_userdata('_phone_', $vars->phone);
                      $this->session->set_userdata('_type_', $vars->_type_);
                      $this->session->set_userdata('_user_name_', $vars->name);
                      $this->session->set_userdata('_email_', $vars->email);
                      $this->session->set_userdata('_signup_package_', $vars->plan);
                      $this->session->set_userdata('_sponsor_', $vars->sponsor);
                      $this->session->set_userdata('_password_', $vars->password);
                      $this->session->set_userdata('_d_password_', $vars->password);
                      $this->session->set_userdata('_secure_password_', $vars->password);
                      $this->session->set_userdata('_city_', $vars->city);
                      $this->session->set_userdata('_address_', $vars->address);
                      $this->session->set_userdata('_state_', $vars->state);
                      $this->session->set_userdata('_zipcode_', $vars->zipcode);
                      $this->session->set_userdata('_country_', $vars->country);
                      $this->session->set_userdata('_price_', $vars->price);

                      $this->session->set_tempdata('_auto_user_id_', $vars->userid, '300');
                      $this->session->set_tempdata('_inv_id_', $vars->userid);
                      $this->session->set_userdata('_signup_package_', $vars->plan);
                      $this->session->set_userdata('_position_', $vars->position);

                      $this->session->set_userdata('_plan_', $vars->plan);
                      $this->session->set_userdata('_join_time_', date('Y-m-d H:i:s'));
                      $this->session->set_userdata('_placement_leg_', $vars->placement_leg);
                      $this->session->set_userdata('_topup_', $vars->topup);
                      $this->session->set_userdata('_my_business_', $vars->mybusiness);
                      $this->session->set_userdata('_plan_detail_', $vars->plan_detail);
                      $this->session->set_userdata('_width_', $vars->width);
                      $this->session->set_userdata('_tax_amount_', $vars->tax_amount);
                      $this->session->set_userdata('_member_status_', $vars->member_status);
                      $this->session->set_userdata('_pan_', $vars->pan);
                      $this->session->set_userdata('role', $vars->role);
                      $this->session->set_userdata('_d_password_', $vars->d_password_);
                      $this->session->set_userdata('_d_secure_password_', $vars->d_secure_password_);
                    }
                    $sms = 'Hello ' . $td->name . ',\n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                    $messvar='Ok';
                    $phone_text='91'.$td->phone;
                    $this->common_model->sms($phone_text, urlencode($sms));
                  }
                  else if($td->status != 'Completed') 
                  {
                    $array = array(
                    'transaction_id' =>  $txn_id,
                    'status'         => 'Failed'
                     );
                    $this->db->where(array('payment_request_id' =>$paypalInfo["custom"] ,'purpose'=>$paypalInfo["item_name"],'amount'=>$paypalInfo["mc_gross"]));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());
                    $sms = 'Hello ' . $td->name . ', \n Your Payment didnot complete. Your transaction id is '.$td->payment_request_id.' Please raise a support ticket incase of amount is deducted from your account' ;
                   $messvar='Ok';
                   $phone_text='91'.$td->phone;
                   $this->common_model->sms($phone_text, urlencode($sms));
                  }
               }
                break;
            case ($pg=='razorpay'):
                 $time = time();
                 $userid = $this->session->_user_id_;
                 $username = $this->session->_user_name_;
                 $phone = $this->session->_phone_;
                 $amount = $this->session->_price_;
                 $type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';

                 $array = array(
                            'userid'         => $userid,
                            'name'           => $username,
                            'phone'          => $phone,
                            'amount'         => $amount,
                            'gateway'        => 'RazorPay',
                            'time'           => $time,
                            'purpose'        => $type,
                            'payment_request_id'   =>$this->input->post('razorpay_payment_id'),
                            'status'         => 'Started',
                        );
                        $this->db->insert('transaction', $array);
                        debug_log($this->db->last_query());
                        $sms = 'Hello ' . $this->session->_user_name_ . ', \nGreetings from ' . config_item('company_name') . '\nThe Razorpay Payment ID towards ' . $type . ' is: ' . $this->input->post('razorpay_payment_id') ;
                            $messvar='Ok';
                            $phone_text='91'.$this->session->_phone_;
                            $this->common_model->sms($phone_text, urlencode($sms));
                  if (!empty($this->input->post('razorpay_payment_id')) &&
                         !empty($this->input->post('merchant_order_id'))) 
                  {
                     $razorpay_payment_id = $this->input->post('razorpay_payment_id');
                     $merchant_order_id = $this->input->post('merchant_order_id');
                     $amount=$this->input->post('amount');
                     $order_info = array('order_status_id' => $_POST['merchant_order_id']);
                     $currency_code = config_item('razorpay_currency');

                     $data = array(
                              'amount' => $amount,
                              'currency' => $currency_code,
                             );
                     $success = false;
                     $error = '';
                     try 
                     {                
                         
                         $ch = $this->razorpay_curl_handle($razorpay_payment_id, $amount);
                         //execute post
                         $result = curl_exec($ch);
                         $data = json_decode($result);
                         $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                         
                         if ($result === false) 
                         {
                             $success = false;
                             $error = 'Curl error: '.curl_error($ch);
                              
                         } 
                         else 
                         {
                             $response_array = json_decode($result, true);
                             //Check success response
                             
                             debug_log('Printing Status');
                             debug_log($response_array['status']);
                             if ($http_status === 200 and isset($response_array['error']) === false and $response_array['status']=='captured') 
                              {
                                 $success = true;
                              } 
                              else 
                              {
                                    $success = false;
                                    if (!empty($response_array['error']['code']))
                                    {
                                         $error = $response_array['error']['code'].':'.$response_array['error']['description'];
                                    } 
                                    else 
                                    {
                                         $error = 'RAZORPAY_ERROR:Invalid Response <br/>'.$result;
                                    }
                             }
                         }
                         //close connection
                         curl_close($ch);
                     } 
                     catch (Exception $e) 
                     {
                     $success = false;
                     $error = 'OPENCART_ERROR:Request to Razorpay Failed';
                     }
                     $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $razorpay_payment_id));
                     if ($success === true && $td->status!='Completed') 
                     {
                            
                            $array = array(
                                    'transaction_id' => $razorpay_payment_id,
                                    'status'         => 'Completed'
                                    );
                            $this->db->where(array('payment_request_id' => $razorpay_payment_id));
                            $this->db->update('transaction', $array);
                            debug_log($this->db->last_query());
                            $sms = 'Hello ' . $td->name . ', \n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                            $messvar='Ok';
                            $phone_text='91'.$this->session->_phone_;
                            $this->common_model->sms($phone_text, urlencode($sms));
                       
                          if(!empty($this->session->userdata('ci_subscription_keys'))) 
                          {
                            $this->session->unset_userdata('ci_subscription_keys');
                          }
                     } 
                     
                 } 
                 else 
                 {
                    echo 'An error occured. Contact site administrator, please!';
                 }

                break;
            case ($pg=='cashfree'):
            break;
            case ($pg == 'instamojo'):
                debug_log(basename($_SERVER['REQUEST_URI']));
                $url_components = parse_url(basename($_SERVER['REQUEST_URI'])); 
                parse_str($url_components['query'], $data);

                $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $data['payment_request_id']));

                if(($data['payment_status'] == 'Credit') && ($td->status != 'Completed')) 
                {
                    $array = array(
                    'transaction_id' => $data['payment_id'],
                    'status'         => 'Completed'
                    );
                    $this->db->where(array('payment_request_id' => $data['payment_request_id']));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());

                    if ($td->purpose == 'Online Wallet Topup') 
                    {
                      $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $td->userid));
                      $array    = array('balance' => $get_balance + $td->amount,);
                      $this->db->where('userid', $td->userid);
                      $this->db->update('wallet', $array);
                      wallet_log($this->db->last_query());
                      
                      //$get_topup = $this->db_model->select('topup', 'member', array('id' => $td->userid));
                      //$array = array('topup' => $get_topup + $td->amount);
                      //$this->db->where('id', $td->userid);
                      //$this->db->update('member', $array);
                      //debug_log($this->db->last_query());
                      
                      $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $td->userid,
                        'amount'        => $td->amount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                      );
                      $this->db->insert('transfer_balance_records', $array);
                      debug_log($this->db->last_query());
                    }
                    $sms = 'Hello ' . $td->name . ',\n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                    $messvar='Ok';
                    $phone_text='91'.$td->phone;
                    $this->common_model->sms($phone_text, urlencode($sms));
                } 
                else if($td->status != 'Completed') 
                {
                    $array = array(
                    'transaction_id' => $data['payment_id'],
                    'status'         => 'Failed'
                     );
                    $this->db->where(array('payment_request_id' => $data['payment_request_id']));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());
                    $sms = 'Hello ' . $td->name . ', \n Your Payment didnot complete. Your transaction id is '.$td->payment_request_id.' Please raise a support ticket incase of amount is deducted from your account' ;
                   $messvar='Ok';
                   $phone_text='91'.$td->phone;
                   $this->common_model->sms($phone_text, urlencode($sms));
                }
              break;
            case ($pg == 'openbank'):

                $td = $this->db_model->select_multi('*', 'transaction', array(
                  'userid' => $this->session->_user_id_,'amount'=>$this->session->_price_, 'id' => $this->session->_Payment_Transaction_ID_));

                $status_url = str_ireplace(array('{{payment_id}}'), array($td->payment_request_id), config_item('openbank_status_url'));

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $status_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

                $client_request_method = 'GET';
                $client_timestamp_header = time();
                $string = $client_timestamp_header.$client_request_method;

                //IMPORTANT : Remove all whitespaces and newlines
                $string = preg_replace('/\s+/', '', $string);
                debug_log($string);
            
                //Hash generation
                $REQUEST_SIGNATURE =  hash_hmac('sha256', $string, config_item('openbank_secret_key'));
                debug_log('\nrequest signature '.$REQUEST_SIGNATURE);
              
                $headers = array('Authorization:Bearer '.config_item('openbank_access_key').':'.$REQUEST_SIGNATURE.'',
                    'X-O-Timestamp:' . $client_timestamp_header . '',
                    'Content-Type:application/json');
                debug_log($headers);
                       
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    debug_log('Error:' . curl_error($ch));
                }
                curl_close($ch);
                $result_decode=json_decode($result);
                debug_log($result);
                debug_log('<br>');
                debug_log($result_decode);
                debug_log($result_decode->id);
                debug_log('<br>');

                if($result_decode->status == 'captured') 
                {
                    $array = array(
                    'gateway'        => 'Bankonnect',
                    'status'         => 'Completed'
                    );
                    $this->db->where(array('id' => $this->session->_Payment_Transaction_ID_));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());

                    if ($td->purpose == 'Online Wallet Topup') 
                    {
                      $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $td->userid));
                      $array    = array('balance' => $get_balance + $td->amount,);
                      $this->db->where('userid', $td->userid);
                      $this->db->update('wallet', $array);
                      wallet_log($this->db->last_query());
                      
                      //$get_topup = $this->db_model->select('topup', 'member', array('id' => $td->userid));
                      //$array = array('topup' => $get_topup + $td->amount);
                      //$this->db->where('id', $td->userid);
                      //$this->db->update('member', $array);
                      //debug_log($this->db->last_query());
                      
                      $array = array(
                        'transfer_from' => 'Admin',
                        'transfer_to'   => $td->userid,
                        'amount'        => $td->amount,
                        'time'          => date('Y-m-d H:i:s'),
                        'remarks'       => 'Online Wallet Topup',
                      );
                      $this->db->insert('transfer_balance_records', $array);
                      debug_log($this->db->last_query());
                    }
                    $sms = 'Hello ' . $td->name . ',\n\n Your Payment towards '.$td->purpose.' is completed. Your transaction id is '.$td->payment_request_id ;
                    $messvar='Ok';
                    $phone_text='91'.$td->phone;
                    $this->common_model->sms($phone_text, urlencode($sms));
                } 
                else if($this->session->_openbak_status_ == 'failed') 
                {
                    $array = array(
                    'gateway'        => 'Bankonnect',
                    'status'         => 'Failed'
                     );
                    $this->db->where(array('id' => $this->session->_Payment_Transaction_ID_));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());
                    $sms = 'Hello ' . $td->name . ', \n Your Payment didnot complete. Your transaction id is '.$td->payment_request_id.' Please raise a support ticket incase of amount is deducted from your account' ;
                   $messvar='Ok';
                   $phone_text='91'.$td->phone;
                   $this->common_model->sms($phone_text, urlencode($sms));
                }
                else if($this->session->_openbak_status_ == 'pending') 
                {
                    $array = array(
                      'gateway'        => 'Bankonnect',
                      'status'         => 'Processing'
                     );
                    $this->db->where(array('id' => $this->session->_Payment_Transaction_ID_));
                    $this->db->update('transaction', $array);
                    debug_log($this->db->last_query());
                    $sms = 'Hello ' . $td->name . ', \n Your Payment didnot complete. Your transaction id is '.$td->payment_request_id.' Please raise a support ticket incase of amount is deducted from your account' ;
                   $messvar='Ok';
                   $phone_text='91'.$td->phone;
                   $this->common_model->sms($phone_text, urlencode($sms));
                }
              break;
            case ($pg == 'payumoney'):
                $status = $this->input->post('status');

                if ($status == 'success') {
                    $txnid       = $this->input->post('txnid');
                    $amount      = $this->input->post('amount');
                    $productinfo = $this->input->post('productinfo');
                    $firstname   = $this->input->post('firstname');
                    $hash        = $this->input->post('hash');
                    $email       = $this->input->post('email');
                    $udf1        = $this->input->post('udf1');
                    $udf2        = $this->input->post('udf2');
                    $udf3        = $this->input->post('udf3');
                    $udf4        = $this->input->post('udf4');
                    $udf5        = $this->input->post('udf5');
                    $key         = $this->input->post('key');


                    $SALT = config_item('payumoney_salt');


                    If (isset($_POST['additionalCharges'])) {
                        $additionalCharges = $_POST['additionalCharges'];
                        $retHashSeq        = $additionalCharges . '|' . $SALT . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
                    }
                    else {
                        $retHashSeq = $SALT . '|' . $status . '|||||||||||' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;

                    }

                    $rethash = hash('sha512', $retHashSeq);


                    if ($rethash != $hash) {
                        $status = 'UnPaid';
                    }
                    else {
                        $status = 'Paid';
                        $array   = array(
                            'userid'         => $txnid,
                            'name'           => $this->session->_user_name_,
                            'phone'          => $this->session->_phone_,
                            'amount'         => $amount,
                            'gateway'        => 'payumoney',
                            'time'           => time(),
                            'transaction_id' => $txnid,
                            'status'         => 'Completed',
                        );
                        $this->db->insert('transaction', $array);
                    }
                }
                break;
        } //End of switch case
        
        $order_status = $this->db_model->select('status', 'transaction', array(
            'userid' => $this->session->_user_id_,
            'phone'  => $this->session->_phone_,));
        debug_log($this->db->last_query());
        debug_log("Order_status ".$order_status);

        $status = $order_status == 'Completed' ? 'Paid' : 'UnPaid';
        debug_log("Status after Case ".$status);
        $this->session->set_userdata('_status_', $status);
        debug_log('from gateway status '.$status);
        debug_log('from gateway type '.$this->session->_type_);

        if ($this->session->_type_ == 'userid' && $status == 'Paid') {
            redirect(site_url('site/complete_registration'));
        }
        else if ($this->session->_type_ == 'userid' && $status !== 'Paid') {
            redirect(site_url('site/failed_registration'));
        }
        else if ($this->session->_type_ == 'wallet' && $status !== 'Paid') {
            redirect(site_url('member/failed_fund'));
        }
        else if ($this->session->_type_ == 'wallet' && $status == 'Paid') {
            redirect(site_url('member/complete_add_fund'));
        }
        else {
            redirect(site_url('site/failed_registration'));
        }
    }

    public function block_io_start()
    {

        $apiKey   = config_item('api_key');
        $version  = 2; // API version
        $pin      = config_item('secret_pin');
        $block_io = new BlockIo($apiKey, $pin, $version);
        $network  = json_encode($block_io->get_current_price(array()));
        $network  = json_decode($network, true);
        if (trim(config_item('iso_currency')) !== "XBT" && trim(config_item('iso_currency')) !== "BTC") {
            $url      = "https://blockchain.info/tobtc?currency=" . trim(config_item('iso_currency')) . "&value=" . $this->session->_price_ . "";
            $btc_rate = $this->common_model->curl($url) . " " . $network['data']['network'];
        }
        else {
            $btc_rate = $this->session->_price_ . " " . $network['data']['network'];;
        }
        $data = json_encode($block_io->get_new_address());
        $data = json_decode($data, true);
        echo '<html><head><link rel="stylesheet" type="text/css" 
href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript" src="' . base_url('axxets/qrcode.js') . '"></script>
</head><body>';
        echo '<div align="center"><span class="alert alert-info">Please send ' . $btc_rate . '</span></div>';
        echo '<h2 align="center">Please Make Payment to the below Address: </h2>';
        echo '<div class="row"><div class="col-sm-3"></div><div class="col-sm-6"><h3 align="center" style="background-color: #1b8edb;
 padding: 
20px;
 color:#fff; margin:10px">
' . $data['data']['address'] . '</h3></div><div class="col-sm-3"></div></div>';
        echo '<br/><div id="qrcode" align="center"></div>
<script type="text/javascript">
var qrcode = new QRCode(document.getElementById("qrcode"), {
    text: "' . $data['data']['address'] . '",
    width: 150,
    height: 150,
    colorDark : "#000000",
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
});
</script>';
        echo '<p>&nbsp;</p><p>&nbsp;</p><div align="center">Please click on "Payment Made button after making the payment, else your payment will loose."<br/><p>&nbsp;</p><a href="' . site_url('gateway/status_block_io/' . $data['data']['address'] . '') . '" 
class="btn btn-success">Payment Made</a> <a class="btn btn-danger" href="' . site_url('gateway/status/block_io') . '">Cancel</a></div>';
        echo '</body></html>';


    }

    public function instamojo_start()
    {
        
        $time = time();
        $userid = $this->session->_user_id_;
        $username = $this->session->_user_name_;
        $phone = $this->session->_phone_;
        $amount = $this->session->_price_;
        $email = $this->session->_email_;

        if($time == '' || $userid =='' || $phone == '' || $amount == '' || $email =='') {

           $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Please check your details.</div>');
           redirect(site_url('gateway/status/instamojo'));

        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config_item('instamojo_url'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Api-Key:' . config_item('instamojo_api_key') . '',
            'X-Auth-Token:' . config_item('instamojo_auth') . '',
        ));

        $purpose = $this->session->_type_ == 'wallet' ? 'Wallet Topup - '.$this->session->_user_name_.'('.$this->session->_user_id_.')' : 'Registration Fee - '.config_item('company_name');

        $payload = Array(
            'purpose'                 => $purpose,
            'amount'                  => $amount,
            'phone'                   => $phone,
            'buyer_name'              => $username,
            'redirect_url'            => site_url('gateway/status/instamojo'),
            'webhook'                 => site_url('gateway/instamojo_ipn/'),
            'send_email'              => false,
            'send_sms'                => false,
            'email'                   => $email,
            'allow_repeated_payments' => false,
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        curl_close($ch);


        $data = json_decode($response, true);
        $site = $data['payment_request']['longurl'];

        if ($site == '') {
            echo '<p>&nbsp;</p><p>&nbsp;</p><div align="center">There are some erros with payment gateway. Please check whether you have filled phone no and email id or not. <a class="btn btn-danger" href="' . site_url('gateway/status/block_io') . '">Cancel</a></div>';

        }

        header('HTTP/1.1 301 Moved Permanently');
        header('Location:' . $site);

        if($data['payment_request']['id'] != '') {

        $type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';

        $array = array(
            'userid'         => $userid,
            'name'           => $username,
            'phone'          => $phone,
            'amount'         => $amount,
            'gateway'        => 'Instamojo',
            'time'           => $time,
            'purpose'        => $type,
            'payment_request_id'   => $data['payment_request']['id'],
            'status'         => 'Started'
        );
        $this->db->insert('transaction', $array);

        $sms = 'Hello ' . $this->session->_user_name_ . ', \nGreetings from ' . config_item('company_name') . '\nThe transaction id towards ' . $type . ' is: ' . $data['payment_request']['id'] ;
        $messvar='Ok';
        $phone_text='91'.$this->session->_phone_;
        $this->common_model->sms($phone_text, urlencode($sms));
        }

    }
    
    private function razorpay_curl_handle($payment_id, $amount)  {
        
        $url = 'https://api.razorpay.com/v1/payments/'.$payment_id.'/capture';
        $key_id = config_item('razorpaykey_id');
        $key_secret = config_item('razorpaykey_secret');
        $fields_string = 'amount=$amount';
        //cURL Request
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id.':'.$key_secret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /*curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/ca-bundle.crt');*/
        return $ch;
    }   

    public function razorpay_start()
    {
        $time = time();
        $userid = $this->session->_user_id_;
        $username = $this->session->_user_name_;
        $phone = $this->session->_phone_;
        $amount = $this->session->_price_;
        $type = $this->session->_type_ == 'wallet' ? 'Wallet Topup' : 'Registration Fee';

        $array = array(
                            'userid'         => $userid,
                            'name'           => $username,
                            'phone'          => $phone,
                            'amount'         => $amount,
                            'gateway'        => 'RazorPay',
                            'time'           => $time,
                            'purpose'        => $type,
                            'payment_request_id'   =>$razorpay_payment_id,
                            'status'         => 'Started',
                        );
                        $this->db->insert('transaction', $array);
 
        if (!empty($this->input->post('razorpay_payment_id')) &&
         !empty($this->input->post('merchant_order_id'))) 
        {
            
            $razorpay_payment_id = $this->input->post('razorpay_payment_id');
            $merchant_order_id = $this->input->post('merchant_order_id');
            $amount=$this->input->post('amount');
            $order_info = array('order_status_id' => $_POST['merchant_order_id']);
            $currency_code = config_item('razorpay_currency');

            $data = array(
                 'amount' => $amount,
                 'currency' => $currency_code,
                );
            $success = false;
            $error = '';
            try {                
                $ch = $this->razorpay_curl_handle($razorpay_payment_id, $amount);
                //execute post
               
                $result = curl_exec($ch);
                $data = json_decode($result);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if ($result === false) {
                    $success = false;
                    $error = 'Curl error: '.curl_error($ch);
                    
                } else {
                    $response_array = json_decode($result, true);
                     //Check success response
                    debug_log('Printing Status');
                    debug_log($response_array['status']);
                        if ($http_status === 200 and isset($response_array['error']) === false and $response_array['status']=='captured') {
                            $success = true;
                        } else {
                            $success = false;
                            if (!empty($response_array['error']['code'])) {
                                $error = $response_array['error']['code'].':'.$response_array['error']['description'];
                            } else {
                                $error = 'RAZORPAY_ERROR:Invalid Response <br/>'.$result;
                            }
                        }
                }
                //close connection
                curl_close($ch);
            } catch (Exception $e) {
                $success = false;
                $error = 'OPENCART_ERROR:Request to Razorpay Failed';
            }
            if ($success === true) 
            {
                
                if(!empty($this->session->userdata('ci_subscription_keys'))) 
                {
                    $this->session->unset_userdata('ci_subscription_keys');
                }
                if (!$order_info['order_status_id']) 
                {
                    redirect(site_url('site/failed_registration'));

                } 
                else 
                {
                    $td = $this->db_model->select_multi('*', 'transaction', array('payment_request_id' => $razorpay_payment_id));
                       $array = array(
                          'transaction_id' => $razorpay_payment_id,
                          'status'         => 'Completed'
                          );
                       $this->db->where(array('payment_request_id' => $razorpay_payment_id));
                       $this->db->update('transaction', $array);
                       debug_log($this->db->last_query());
                        $sms = 'Hello ' . $this->session->_user_name_ . ', \nGreetings from ' . config_item('company_name') . '\nThe Razorpay Payment ID towards ' . $type . ' is: ' . $razorpay_payment_id ;
                        $messvar='Ok';
                        $phone_text='91'.$this->session->_phone_;
                        $this->common_model->sms($phone_text, urlencode($sms));

                        redirect(site_url('site/complete_registration'));
                }
            } 
            else 
             {
                redirect(site_url('site/failed_registration'));
             }
        } 
        else 
         {
               echo 'An error occured. Contact site administrator, please!';
         }  
    }


    public function check_recharge_status(){
      debug_log('check recharge status');
      debug_log(basename($_SERVER['REQUEST_URI']));
      $url_components = parse_url(basename($_SERVER['REQUEST_URI'])); 
      parse_str($url_components['query'], $data);
      debug_log($data);
    }

}