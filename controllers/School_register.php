<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('application/libraries/PayPal-PHP-SDK/vendor/autoload.php');

class School_register extends MY_Controller
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
        //$this->load->library('pagination');

        $this->load->model('School_model',  'school_model');

        $this->config->load('school_api');

        $this->load->library('Paypal_lib');
    }

   public function index()
    {    
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';		
    //	$response = file_get_contents('https://www.karthikeyaschool.com/schoolsoftware/api/packages');
        $response = file_get_contents($this->config->item('package_api'));
		$response = json_decode($response);
		$data['pakages']=$response;
		
        //$currency = file_get_contents('https://www.karthikeyaschool.com/schoolsoftware/api/currency');
        $currency = file_get_contents($this->config->item('currency_api'));
		$currency = json_decode($currency);
		
        //$payments = file_get_contents('https://www.karthikeyaschool.com/schoolsoftware/api/payments');
        $payments = file_get_contents($this->config->item('payments_api'));
        $payments = json_decode($payments);
        
        //$package_settings = file_get_contents('https://www.karthikeyaschool.com/schoolsoftware/api/package_settings');
        $package_settings = file_get_contents($this->config->item('package_settings_api'));
		$package_settings = json_decode($package_settings);
        $data['package_settings']=$package_settings;
		
		$data['paypal_status']=$payments->paypal_status;
		$data['stripe_status']=$payments->stripe_status;
		
		if(isset($payments->publish_key))
		{
			$data['publish_key']=$payments->publish_key;
		}		
        $data['currency']=$currency;

        $data['couponurl']=$this->config->item('validate_coupon_api');

        $walletBalance=$this->db_model->select_multi('balance', 'wallet', array('userid' => $this->session->user_id));
        
        $balance=$walletBalance->balance;

        $data['balance']=$balance;

        /*$balancedata = array(
            'balance' => 5000.00,
        );
        $this->school_model->update_balance($balancedata);*/
		
        $this->load->view('templates/school/index', $data);

       //redirect('http://localhost/schoolsoftware/login', $data);
    }
	
	public function validate_registeration(){

		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phonne', 'Phone', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('state', 'State', 'required');
		$this->form_validation->set_rules('country', 'Country', 'required');
		$this->form_validation->set_rules('schl_name', 'School Name', 'required');
		$this->form_validation->set_rules('schl_address', 'School Address', 'required');
	//	$this->form_validation->set_rules('pakage', 'Pakage', 'required');

		if ($this->form_validation->run() == FALSE)
        {
			$school_id = file_get_contents($this->config->item('school_id_api'));
		    $school_id = json_decode($school_id);
			
        	$response = file_get_contents($this->config->item('package_api'));
		    $response = json_decode($response);
		    $data['pakages']=$response;
			
			$settings = file_get_contents($this->config->item('settings_api'));
		    $settings = json_decode($settings);
            $data['settings']=$settings;

            $currency = file_get_contents($this->config->item('currency_api'));
		    $currency = json_decode($currency);
            $data['currency']=$currency;

            $package_settings = file_get_contents($this->config->item('package_settings_api'));
		    $package_settings = json_decode($package_settings);
            $data['package_settings']=$package_settings;
            
            $data['couponurl']=$this->config->item('validate_coupon_api');  

            $this->load->view('school_register',$data);
        }
        else
        {	
          $duplication_status = file_get_contents($this->config->item('email_validation_api').html_escape($this->input->post('email')));
		  
			if($duplication_status==='true'){

                if($this->input->post('payment')=='wallet'){
                    $walletBalance=$this->db_model->select_multi('balance', 'wallet', array('userid' => $this->session->user_id));
                    $balance=$walletBalance->balance;
                    $data = array(
                        'balance' => ($balance - $total_price_of_checking_out),
                    );
                    $total_amount=$this->input->post('total_value');
                    $pakag_name = $this->input->post('package');
        		    
                    $valid= true;
                    if($total_amount!="")
                    {
                       if($balance<$total_amount)
                       {
                        $valid= false;
                       }
                    }else if($pakag_name=='predefinedPackage')
                    {
                        $pakag_id = $this->input->post('pakage');
                        $pakage = file_get_contents($this->config->item('package_id_api').$pakag_id);
                        $pakage = json_decode($pakage);
                        if($balance<$pakage->cost)
                        {
                            $valid= false;
                        }
                    }else if($pakag_name=='customizedPackage')
                    {
                        $noOfDays = $this->input->post('noOfDays');
        		      $noOfParents = $this->input->post('noOfParents');
        		      $noOfTeachers = $this->input->post('noOfTeachers');
        		      $noOfStudents = $this->input->post('noOfStudents');
        		      $noOfLibrarians = $this->input->post('noOfLibrarians');
        		      $noOfAccountants = $this->input->post('noOfAccountants');
                      
                      $settings = file_get_contents($this->config->item('customPackage_settings_api'));
                      $package_settings = json_decode($settings);            
        		      
        		      $totalAmount=$noOfDays*(($noOfParents*$package_settings->AmountPerParent)+($noOfTeachers*$package_settings->AmountPerTeacher)+($noOfStudents*$package_settings->AmountPerStudent)+($noOfLibrarians*$package_settings->AmountPerLibrarian)+($noOfAccountants*$package_settings->AmountPerAccountant));
                      if($balance<$totalAmount)
                      {
                        $valid= false;
                      }
                    }
                    if($valid===false)
                    {
                        $this->session->set_flashdata('error',"Wallet balance is Low to purchase the package. Please topup the wallet/ use another payment option.");
			            redirect('school_register');
                    }
                   }  
                   
                    $pakag_name = $this->input->post('package');
        		    if($pakag_name=='predefinedPackage')
        		    {
            		   $pakag_id = $this->input->post('pakage');
        		    }
        		    else
        		    {
        		      $noOfDays = $this->input->post('noOfDays');
        		      $noOfParents = $this->input->post('noOfParents');
        		      $noOfTeachers = $this->input->post('noOfTeachers');
        		      $noOfStudents = $this->input->post('noOfStudents');
        		      $noOfLibrarians = $this->input->post('noOfLibrarians');
        		      $noOfAccountants = $this->input->post('noOfAccountants');
                      
                      $settings = file_get_contents($this->config->item('customPackage_settings_api'));
                      $package_settings = json_decode($settings);            
        		      
        		      $totalAmount=$noOfDays*(($noOfParents*$package_settings->AmountPerParent)+($noOfTeachers*$package_settings->AmountPerTeacher)+($noOfStudents*$package_settings->AmountPerStudent)+($noOfLibrarians*$package_settings->AmountPerLibrarian)+($noOfAccountants*$package_settings->AmountPerAccountant));
                      
                      $data = array("name" => html_escape($this->input->post('schl_name'))."_custom_package",
					"student_number" => $noOfStudents,
					"teacher_number"=>$noOfTeachers,
					"accountant_number" => $noOfAccountants,
					"librarian_number" => $noOfLibrarians,
					"parents_number" => $noOfParents,
                    "cost" => $totalAmount,
                    "duration" => $noOfDays,
                    "school_id" => 0,
                    "created_at" => date('Y-m-d'),
                    "updated_at" => date('Y-m-d'),
                    "menu_allowed" => '["119"]'
                    );
                    
                    $postdata = json_encode($data);
					
					$curl = curl_init($this->config->item('add_package_api'));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$postdata);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $pakag_id = curl_exec($curl);
                    curl_close($curl);
        		      
        		    }
					
                    $pakage = file_get_contents($this->config->item('package_id_api').$pakag_id);
                    $pakage = json_decode($pakage);
		   
					$settings = file_get_contents($this->config->item('settings_api'));
		            $settings = json_decode($settings);
    				
    				$payment_id = rand(9999, 999999);
    				$user_crn = rand(9999, 999999);					
					
					$data = array("name" => html_escape($this->input->post('schl_name')),
					"address" => html_escape($this->input->post('schl_address')),
					"phone"=>html_escape($this->input->post('phonne')),
					"state" => html_escape($this->input->post('state')),
					"country" => html_escape($this->input->post('country')),
					"payment_type" => html_escape($this->input->post('payment')),
                    "pakage_id" =>  $pakag_id ,
                    "referral_company" => 'MLM'                   
					);

					$postdata = json_encode($data);
					
					$curl = curl_init($this->config->item('add_school_api'));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$postdata);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $school_id = curl_exec($curl);
                    curl_close($curl);                

                    $total_amount=$this->input->post('total_value');
                    $discount_amount=$this->input->post('discount_value');

                    if($total_amount!="")
                    {
                        $payment['total_amount'] = $total_amount;
                        $amount_didcount=$pakage->cost;
                        $discount_amount= $pakage->cost-$total_amount;
                        $page_data['total_price_of_checking_out'] = $total_price_of_checking_out = $total_amount;
                    }else
                    {
                        $payment['total_amount'] = $pakage->cost;
                        $discount_amount= 0;
                        $page_data['total_price_of_checking_out'] = $total_price_of_checking_out = $pakage->cost;
                    }          
					
					$paymentData = array("pakage_id" =>  $pakag_id,
					"payment_id" => $payment_id,
                    "school_id"=>$school_id,
                    "referral_company" => 'MLM',
                    "total_amount"=>$total_price_of_checking_out,
                    "discount"=> $discount_amount,
					"payment_type" => html_escape($this->input->post('payment'))
                    );
                    
                    $transaction_id=$payment_id;

					$paymentPostdata = json_encode($paymentData);
					
					$curl = curl_init($this->config->item('add_payment_api'));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$paymentPostdata);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $payment_inserted_id = curl_exec($curl);
                    curl_close($curl);	
                    
                    $transaction_data['userid']=$this->session->user_id;
                    $transaction_data['name']=html_escape($this->input->post('name'));
                    $transaction_data['email_id']=html_escape($this->input->post('email'));
                    $transaction_data['phone']=html_escape($this->input->post('phonne'));
                    $transaction_data['amount']=$total_price_of_checking_out;
                    $transaction_data['gateway']=html_escape($this->input->post('payment'));
                    $transaction_data['time']=date('Y-m-d');
                    $transaction_data['purpose']="School registration";
                    $transaction_data['transaction_id']=$transaction_id;
                    $transaction_data['payment_request_id']=$payment_id;
                    $transaction_data['status']="Started";
                    $transaction_data['remarks']="School registration";
                    
                    $this->school_model->add_transaction($transaction_data);
					
					$userData = array("school_id" => $school_id,
					"name" => html_escape($this->input->post('name')),
					"email"=> html_escape($this->input->post('email')),
					"password" => sha1($this->input->post('password')),
                    "phone" => html_escape($this->input->post('phonne')),
                    "gst_no" => html_escape($this->input->post('gst_no')),
					"axis_rid" => $payment_id,
					"axis_crn"=> $user_crn
					);

					$userPostdata = json_encode($userData);
					
					$curl = curl_init($this->config->item('add_user_api'));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$userPostdata);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $user_id = curl_exec($curl);
                    curl_close($curl);
    
                    require_once APPPATH . '/third_party/AXIS/AesForJava.php';
                   
                    $page_data['page_title'] = 'Payment Gateway';
				    $payments = file_get_contents($this->config->item('payments_api'));
					$payments = json_decode($payments);
		
                    $encryption_key=$payments->encryption_key;
                    if($this->input->post('payment')=='wallet'){
                        $walletBalance=$this->db_model->select_multi('balance', 'wallet', array('userid' => $this->session->user_id));
                        $balance=$walletBalance->balance;
                        $data = array(
                            'balance' => ($balance - $total_price_of_checking_out),
                        );
                        $this->school_model->update_balance($data);
                        $this->success_payment($pakag_id,$school_id,$payment_id);
                        $this->session->set_flashdata('flash_message', 'Payment Successfully Done Please Login !!!');
                        redirect('school_register', 'refresh');
                    }                 
                    else if($this->input->post('payment')=='axis'){
                        $encryption_key="axisbank12345678";
                    $checksum_key = "mivY";				
                    $cid = '5727';
                    $rid = $payment_id;
                    $crn = $user_crn;
                    $amt = $total_price_of_checking_out;
                    $ver = '1.0';
                    $typ = 'PRD';
                    $cny = 'INR';
                    $rtu = base_url().'school_register/success_axis';
                    $ppi = html_escape($this->input->post('name')).' '.html_escape($this->input->post('name')).'|'.html_escape($this->input->post('schl_address')).'|'.html_escape($this->input->post('phonne')).'|'.html_escape($this->input->post('email')).'|'.$amt;
                    $re1 = 'MN';
                    $re2 = '';
                    $re3 = '';
                    $re4 = '';
                    $re5 = '';
            
                    $cks = hash("sha256", $cid.$rid.$crn.$amt.$checksum_key);
            
                    $str ='CID='.$cid.'&RID='.$rid.'&CRN='.$crn.'&AMT='.$amt.'&VER='.$ver.'&TYP='.$typ.'&CNY='.$cny.'&RTU='.$rtu.'&PPI='.$ppi.'&RE1='.$re1.'&RE2=&RE3=&RE4=&RE5=&CKS='.$cks;
            
                    $aesJava = new AesForJava();
                    $i = $aesJava->encrypt(urldecode($str), $encryption_key, 128);
                    $page_data['axis_checksum'] = $i;
                    $this->load->view('payment/index', $page_data);
                    }
                    else if($this->input->post('payment')=='paypal')
                    {
                        $returnURL = base_url().'school_register/success_paypal'; //payment success url 
                        $cancelURL = base_url().'school_register/paypal_cancel'; //payment cancel url 
        
                        // Add fields to paypal form 
                        $this->paypal_lib->add_field('return', $returnURL); 
                        $this->paypal_lib->add_field('cancel_return', $cancelURL); 
                        $this->paypal_lib->add_field('notify_url', $cancelURL); 
                        $this->paypal_lib->add_field('pakag_id', $pakag_id); 
                        $this->paypal_lib->add_field('school_id',$school_id); 
                        $this->paypal_lib->add_field('item_name',$pakag_id);
                        $this->paypal_lib->add_field('item_number',$school_id);
                        $this->paypal_lib->add_field('custom', $rid);
                        $this->paypal_lib->add_field('txn_id', $rid); 
                        $this->paypal_lib->add_field('amount',$total_price_of_checking_out); 
         
                        // Render paypal form 
                        $this->paypal_lib->paypal_auto_form(); 
                    } 
			
         	}else{
			$this->session->set_flashdata('error',"Email is already exist.. Please use different email id.");
			redirect('school_register');
		}
        }
		
    }
    
    public function paypal_cancel()
	{
        
	    $this->session->set_flashdata('error','Paypal payment is Failed.. Please try again later');
		redirect('school_register');
    }
    
    public function success_paypal(){
    	$paypalInfo = $this->input->post();
        if(!empty(!empty($paypalInfo['txn_id']) && !empty($paypalInfo['mc_gross']) && !empty($paypalInfo['verify_sign']) && !empty($paypalInfo['payment_status'])))
        { 
            $pakag_id = $paypalInfo['item_name'];
            $school_id = $paypalInfo['item_number']; 
            $rid = $paypalInfo["custom"];
            $status = $paypalInfo["payment_status"]; 
                  
            if($status=='Completed')
            {
                /*$pakageStripe = $this->db->where('id',$pakag_id)->get('pakage')->row();
                $today = strtotime(date('Y-m-d'));
			    $expire_date = date('Y-m-d',strtotime('+'.$pakageStripe->duration.' days',$today));
                $data = array('sub_status'=>1,'sub_expire'=>$expire_date);
                $this->db->where(array('id'=>$school_id));
		        $this->db->update('schools', $data);
		        $data = array('pakage_active' => 1);
                $this->db->where(array('payment_id'=>$rid));
                $this->db->update('payment', $data);*/

                $pakage = file_get_contents($this->config->item('package_id_api').$pakag_id);
                $pakage = json_decode($pakage);
        
                $schoolData = array("package_id" =>  $pakag_id,
                "school_id" => $school_id,
                "package_duration" => $pakage->	duration			
                );

				$schoolPostData = json_encode($schoolData);
					
				$curl = curl_init($this->config->item('update_school_api'));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$schoolPostData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $school_id = curl_exec($curl);
                curl_close($curl);

                $paymentData = array("rid" =>  $rid);

			    $paymentPostData = json_encode($paymentData);
					
				$curl = curl_init($this->config->item('update_payment_api'));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$paymentPostData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $payment_id = curl_exec($curl);
                curl_close($curl);

                $this->school_model->update_transaction($rid,'Completed');	

                $this->session->set_flashdata('flash_message', 'Payment Successfully Done Please Login !!!');
                redirect('school_register', 'refresh');
            }
            else
            {
                $this->school_model->update_transaction($rid,'Failed');
                 $this->session->set_flashdata('error_message',"Payment $status, Please try again.");
                 redirect('school_register', 'refresh');
            }
        }
    }
    
    public function success_payment($pakag_id,$school_id,$payment_id){
       
        $pakage = file_get_contents($this->config->item('package_id_api').$pakag_id);
        $pakage = json_decode($pakage);

        $schoolData = array("package_id" =>  $pakag_id,
        "school_id" => $school_id,
        "package_duration" => $pakage->	duration			
        );

    $schoolPostData = json_encode($schoolData);
        
    $curl = curl_init($this->config->item('update_school_api'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$schoolPostData);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $school_id = curl_exec($curl);
    curl_close($curl);

    $paymentData = array("rid" =>  $payment_id);

    $paymentPostData = json_encode($paymentData);
        
    $curl = curl_init($this->config->item('update_payment_api'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$paymentPostData);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $payment_id = curl_exec($curl);
    curl_close($curl);

    $this->school_model->update_transaction($payment_id,'Completed');
    }

	public function success_axis(){        
        preg_match_all('/(\w+)=([^&]+)/', $_SERVER["QUERY_STRING"], $pairs);
        $_GET = array_combine($pairs[1], $pairs[2]);
        require_once APPPATH . '/third_party/AXIS/AesForJava.php';
        $axis = json_decode($this->db->where('id','3')->get('payment_settings')->result_array()[0]['value']);
        $encryption_key= $axis[0]->axis_encryption_key;

        $aes = new AesForJava();
        $qStr = $aes->decrypt(urldecode($_GET['i']), $encryption_key, 128);
        $newArr = explode('&', $qStr);
        $newArr2 = explode('=', $newArr['2']);
        
        $status_text = $newArr2['1'];
        $rid = explode('=', $newArr['6'])[1];
        $crn = explode('=', $newArr['10'])[1];
        
        if(strlen($rid)>0){
            $school_id = $this->db->where(array('axis_crn'=>$crn, 'axis_rid'=>$rid))->get('users')->result_array()[0]['school_id'];
            $package_id = $this->db->where(array('id'=>$school_id))->get('schools')->result_array()[0]['pakage_id'];
        }
        
        if($status_text == 'success'){
            
           /* $pakageStripe = $this->db->where('id',$package_id)->get('pakage')->row();
            $today = strtotime(date('Y-m-d'));
			$expire_date = date('Y-m-d',strtotime('+'.$pakageStripe->duration.' days',$today));
            
            $data = array('sub_status'=>1,'sub_expire'=>$expire_date);
            
            $this->db->where(array('id'=>$school_id));
		    $this->db->update('schools', $data);
		    
		    $data = array('pakage_active' => 1);
            $this->db->where(array('payment_id'=>$rid));
            $this->db->update('payment', $data); */
            
            $pakage = file_get_contents($this->config->item('package_id_api').$package_id);
        $pakage = json_decode($pakage);

        $schoolData = array("package_id" =>  $package_id,
        "school_id" => $school_id,
        "package_duration" => $pakage->	duration			
        );

				$schoolPostData = json_encode($schoolData);
					
				$curl = curl_init($this->config->item('update_school_api'));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$schoolPostData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $school_id = curl_exec($curl);
                curl_close($curl);

                $paymentData = array("rid" =>  $rid);

			    $paymentPostData = json_encode($paymentData);
					
				$curl = curl_init($this->config->item('update_payment_api'));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$paymentPostData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $payment_id = curl_exec($curl);
                curl_close($curl);

                $this->school_model->update_transaction($rid,'Completed');

            $this->session->set_flashdata('flash_message', 'Payment Successfully Done Please Login !!!');		
			
			redirect('school_register', 'refresh');

        }else{
            $status = $status_text;
            
           /* if(strlen($rid)>0){
                $this->db->where(array('axis_crn'=>$crn, 'axis_rid'=>$rid));
    		    $this->db->delete('users');
    		    
    		    $this->db->where(array('id'=>$school_id));
    		    $this->db->delete('schools');
            
            }*/
            
            $this->session->set_flashdata('error_message',"Payment $status, Please try again.");
            redirect('school_register', 'refresh');
        }
        
    }

    public function email_varification(){
		
        $email   = $_POST['email'];
        
        $duplication_status = file_get_contents($this->config->item('email_validation_api').html_escape($email));
		  
        if($duplication_status==='true'){
             echo true;
        }
        else{
            echo 0;
        }

	/*	$duplicate_email_check = $this->db->get_where('users', array('email' => $email));
			if ($duplicate_email_check->num_rows() > 0) {
				echo 0;
			}else {
				
				echo true;
			}*/
		
	}
	
}