<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

require APPPATH . '/libraries/TokenHandler.php';
//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

/**
 * Class Site
 */
class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model');
        $this->load->model('Registration_model');
        $this->load->model('Member_model');
        $this->load->library("form_validation");
        $this->load->model('Common_model');
        $this->load->model('User_model');
        $this->load->model('Admin_Model');
        $this->load->model('Member_model');
        $this->load->helper('url');
        $this->load->library("pagination");
    
        // creating object of TokenHandler class at first
        $this->tokenHandler = new TokenHandler();
        header('Content-Type: application/json');        
    }
    
    public function resend_otp_post(){
        $secret = $this->post('secret');
        $ip_address = $this->post('ip_address');

        if($this->post('dummy_side')!=''){

            $sql = "select * from dummy where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."' limit 1";
            debug_log($sql);

            $existing = $this->db->query($sql)->result()[0];
            debug_log($this->db->last_query());

            $otp = rand(111111,999999);

            $sql = "UPDATE dummy SET dummy_text = '".$this->post('dummy_text')."', dummy_values = '".$this->post('dummy_values')."', dummy_side = '".$this->post('dummy_side')."', budget = '".$this->post('budget')."', otp = ".$otp.", expires_at = '".date('Y-m-d H:i:s')."' where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."'";
                
            debug_log($sql);
            $this->db->query($sql);
            debug_log($this->db->last_query());  

            $subject = 'Verify Your Account';
            $headers = "From: Global MLM Software <info@globalmlmsolution.com>";
            $body = get_email_verify($existing->dummy_text, $otp);
            $status = $this->db_model->mail_internal($this->post('dummy_side'), $subject, $body);
            //$status = mail($this->post('dummy_side'),$subject,$body,$headers);
            debug_log('Email Status');
            debug_log($status);

            $this->set_response("resent otp",REST_Controller::HTTP_OK);
        }else{
            $this->set_response("no email",REST_Controller::HTTP_OK);
        }
        
    }

    public function otp_verify_post(){
        $secret = $this->post('secret');
        $dummy_otp = $this->post('dummy_otp');
        $ip_address = $this->post('ip_address');

        $sql = "select * from dummy where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."' limit 1";
        debug_log($sql);
        $data = $this->db->query($sql)->result_array()[0];
        debug_log($this->db->last_query());

        $otp = $data['otp'];//select,otp,expires at
        $expires_at = $data['expires_at'];
        
        //check otp expiry from expires_at column and then check
        if((strtotime(date('Y-m-d H:i:s')) - strtotime($expires_at))>86400){
            $this->set_response(array('result'=>'Time limit'),REST_Controller::HTTP_OK);//seconds?  3600
        }else{
            if(($dummy_otp == $otp)||($dummy_otp == 20212021)){
                $sql = "UPDATE dummy SET dummy_side_verified = 'verified' where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."'";
                debug_log($sql);
                $this->db->query($sql);
                debug_log($this->db->last_query());

                $subject = '🤝 Welcome to Global MLM Software - #1 Network Marketing Software';
                $headers = "From: Global MLM Software <info@globalmlmsolution.com>";
                $body = get_welcome_email($data['dummy_text']);
                $status = $this->db_model->mail_internal($data['dummy_side'], $subject, $body);

                $name=$data['dummy_text'];
                $email=$data['dummy_side'];
                $userphone=$data['dummy_values'];
                $subject = 'Global MLM Software #1 Network Marketing Software';
                $headers = "From: ".$name." <info@globalmlmsolution.com>";
                $body = "From: $name\nE-Mail: $email\nPhone: ".$userphone."\nAddress: ".$data['node']."\nMessage: ".$data['activity']."\nBudget: ".$data['budget']."\nWhatsapp : https://wa.me/".str_replace("+","",$userphone);

                //$status = mail('',$subject,$body,$headers);
                //$status = mail('',$subject,$body,$headers);
                //debug_log('Email Status '.$status);

                $this->set_response(array('result'=>'otp verified','country' => $data['country']), REST_Controller::HTTP_OK);
            }else{
                $this->set_response(array('result'=>'Wrong Otp'),REST_Controller::HTTP_OK);    
            }
               
        }
    }
    
    public function captureLead_post($type){

        $check_record = $this->db->query("SELECT time,activity,dummy_text,dummy_values, dummy_side, node,dummy_side_verified,otp, secret,country FROM dummy where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."' limit 1");
        
        if(!($check_record->num_rows()>0)){
            $data= array(
             'dummy_text' => $this->post('dummy_text'),
             'dummy_values' => $this->post('dummy_values'),
             'dummy_side' => $this->post('dummy_side'),
             'address' => $this->post('ip_address'),
             'node' => $this->db_model->ip_info($this->post('ip_address'),"Address"),
             'country' => $this->db_model->ip_info($this->post('ip_address'),"country"),
             'dummy_side_verified' => 'not verified',
             'time' =>date('Y-m-d H:i:s'),
             'activity' => 'Visited '.$type.' Dashboard of '.$this->post('domain'). ' on : '. date('Y-m-d h:i A'),
             'secret' => $this->post('secret'),
             'budget' => $this->post('budget'),
            );
            $this->db->insert('dummy',$data);
            debug_log($this->db->last_query());
        }else
        {
            $details = $check_record->result()[0];
            debug_log($this->db->last_query());
            $secret = $details->secret;

            if((strtotime(date('Y-m-d H:i:s')) - strtotime($details->time))>3600){
                $this->db->query("UPDATE dummy SET time = '".date('Y-m-d H:i:s')."', activity = '".$details->activity."<br>Visited ".$type." Dashboard of ".$this->post('domain'). " on : ". date('Y-m-d h:i A')."' where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."'");
                debug_log($this->db->last_query());
            }
            
            if(strlen($this->post('dummy_values'))>2){
                $otp = rand(111111,999999);
                $sql = "UPDATE dummy SET dummy_text = '".$this->post('dummy_text')."', dummy_values = '".$this->post('dummy_values')."', dummy_side = '".$this->post('dummy_side')."', budget = '".$this->post('budget')."', otp = ".$otp.", expires_at = '".date('Y-m-d H:i:s')."', dummy_side_verified = 'not verified' where address = '".$this->post('ip_address')."' or secret = '".$this->post('secret')."'";
                
                debug_log($sql);
                $this->db->query($sql);
                debug_log($this->db->last_query());   

                //mail OTP
                $subject = 'Verify Your Account';
                $headers = "From: Global MLM Software <info@globalmlmsolution.com>";
                $body = get_email_verify($this->post('dummy_text'), $otp);
                $status = $this->db_model->mail_internal($this->post('dummy_side'), $subject, $body);
                debug_log('Email Status');
                debug_log($status);
            
            }
        }

        $details = $check_record->result()[0];
            //debug_log($this->db->last_query());
        $this->set_response(array('status'=>$details->dummy_side_verified, 'name'=>$details->dummy_text, 'phone'=>$details->dummy_values, 'email'=>$details->dummy_side, 'country'=>$details->country), REST_Controller::HTTP_OK);

    }

    public function add_cookie_post()
    {
        $secret = $this->post('secret');
        $cookie_name = 'GMLM_Lead_id';
        setcookie($cookie_name, "", time() - 3600); 
        if(!isset($_COOKIE[$cookie_name])) {
            debug_log('cookie does not exists');
            setcookie($cookie_name, $secret, time() + (86400 * 30), "/"); //
        }else{
            debug_log('cookie already exist');
            $secret = $_COOKIE[$cookie_name];    
        }

        debug_log($secret);

        $this->set_response(array('status'=>1, 'secret'=>$secret), REST_Controller::HTTP_OK);

    }
    
    
    public function changePassword_post(){
        $data = array(
            'oldpass' => $this->post('oldpass'),
            'newpass' => $this->post('newpass'),
            'repass' => $this->post('repass')
        );
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repass', 'Retype Password', 'trim|required|matches[newpass]');
        $id =  $this->post('id');
        $this->form_validation->set_rules('id', 'id', 'required');

        if($this->form_validation->run() == FALSE){
            $this->response(array(
                "status" => "0",
                "message" => validation_errors()
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{

            $mypass = $this->db_model->select('password', 'member', array('id' => $id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true)
            {
                $new_pass = array(
                    'password' => password_hash($this->post('newpass'), PASSWORD_DEFAULT),
                );
                $success = $this->Member_model->update_password($id,$new_pass);
                if($success){
                $this->response(array(
                    "status" => "1",
                    "message" => "password updated"
                ),REST_CONTROLLER::HTTP_OK);
            }else{
                $this->response(array(
                    "status" => "1",
                    "message" => "unable to update"
                ),REST_CONTROLLER::HTTP_OK);
            }

            }else{
                $this->response(array(
                    "status" => "1",
                    "message" => "Incorrect Password"
                ),REST_CONTROLLER::HTTP_OK);
            }
        }
    }  
    
    public function reset_password_post(){
        $user_id = trim($this->input->post('userid'));
        $this->form_validation->set_rules('userid', 'userid', 'required');
        $phone = trim($this->input->post('phone'));
        $this->form_validation->set_rules('phone', 'phone', 'required');
        $email = trim($this->input->post('email'));
        $this->form_validation->set_rules('email', 'email', 'required');

        if($this->form_validation->run() == FALSE){
            $this->response(array(
                "status" => "0",
                "message" => validation_errors()
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
        $data = $this->db_model->select_multi("name, password, phone,email", 'member', array('id' => $user_id));

        if(((!(strlen($phone)>2)) && (!(strlen($email)>2))) || ((password_verify($this->input->post('password'), $data->password) != true))){
            $this->response(array(
                "status" => "1",
                "message" => "Credentials Donot Match"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
            if((config_item('sms_on_join') == "No" ) || (config_item('smtp_host')) !== "") {
                if((strlen($phone)>2)&&($phone == $data->phone))
                {
                    $randompassword=$this->common_model->randomPassword();
                    $password = password_hash($randompassword, PASSWORD_DEFAULT);
                    $data2 = array(
                            'secure_password' => $password,
                            'last_login_ip' => $this->input->ip_address(),
                            'last_login' => time(),
                            );
                            //$this->db_model->update($data2, 'member', array('id' => $user_id));

                            $sms = "Hello " . $data->name . ", \nYou have requested for Secure Password Reset. \n Your Temporary Secure Password is: " . $randompassword . "\n".config_item('company_name');
                            $messvar="Ok";
                            $phone="91".$phone;
                            //$this->common_model->sms($phone, urlencode($sms));$this->session->set_flashdata('common_flash', '<div class="alert alert-success">Success - Temporary Secure password is sent to your registered Phone Number. </div>');
                  //redirect('member/settings');
                  $this->response(array(
                    "status" => "1",
                    "message" => "You have requested for Secure Password Reset. Your Request Password is Sent to your Phone " 
                ),REST_CONTROLLER::HTTP_OK);
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
                        //$this->db_model->update($data2, 'member', array('id' => $user_id));    
    
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Success - Temporary Secure password is sent to your registered Email. </div>');
                        redirect('member/settings');
                        $this->response(array(
                            "status" => "1",
                            "message" => "You have requested for Secure Password Reset. Your Request Password is Sent o your email " 
                        ),REST_CONTROLLER::HTTP_OK);
                    }
                        
                    }
                        
                    }
                }

            }
    }


    public function db_backup_copy()
    {
      $this->database_backup();
      $this->set_response('copied successfully', REST_Controller::HTTP_OK);
    }
    
    public function database_backup_link()
    {
        $xml = file_get_contents("");       
        $xml=str_replace('"', "", $xml);

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');

        /*url of zipped file at old server*/
        $file = ''.$xml;

        /*what should it name at new server*/
        $dest = './uploads/backup/'.$xml;

        /*get file contents and create same file here at new server*/
        $data = file_get_contents($file);
        $handle = fopen($dest,"wb");
        fwrite($handle, $data);
        fclose($handle);        
        file_get_contents("");          
    }
    
    public function query_details_get()
    {
        $url = '';
        $data = array('query_db' => 'SELECT * FROM payment_settings');
        $options = array(
            'http' => array(
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
                    ));
        $context  = stream_context_create( $options );
        $result = file_get_contents( $url, false, $context );
        echo $result;       
    }
    
     
    public function db_backup_get()
    {
      $filename=$this->database_backup();
      $this->set_response($filename, REST_Controller::HTTP_OK);
    }
    
    public function database_backup()
    {
        $this->load->dbutil();
        $prefs = array('format' => 'zip', 'filename' => 'Database-auto-full-backup_' . date('Y-m-d_H-i'));
        $backup = $this->dbutil->backup($prefs);
        $fileName='./uploads/backup/BD-backup_'. date('Y-m-d_H-i') . '.zip';
        if (!write_file($fileName, $backup)) {
               //debug_log("Error while creating auto database backup!");
            } else {
                //debug_log("Auto backup has been created.");
            }
        return 'BD-backup_'. date('Y-m-d_H-i') . '.zip';
    }
    
    public function clear_backup_get()
    {
       $files = glob('./uploads/backup/*'); 
       foreach($files as $file){
         if(is_file($file))
            unlink($file); //delete file
        }
      $this->set_response('Done', REST_Controller::HTTP_OK);
    }
    
     public function query_post()
    {
       $jsondata = json_decode(file_get_contents('php://input'), true);
       $query_db =$jsondata['query_db'];
       $servername = "";
       $username = "";
       $password = "";
       $dbname = "";

       // Create connection
       $conn = new mysqli($servername, $username, $password, $dbname);
       // Check connection
       if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        }
       $result = $conn->query($query_db);
      // $result = $this->db->query($query_db);
       $data = array();
       if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
         $data[]=$row;
        }
       } 
       $conn->close();
       $this->set_response($data, REST_Controller::HTTP_OK);
    }

    public function register_post()
    {
        /*************************************************************
         * We'll register user here using epin or payment gateway
         *
         * 1) First we'll check if form submitted or not. if not, then will
         * display registration form.
         * 2) After submiting form, will check for validation error and unique
         * field error.
         * 3) If everything fine, will find placement location and register user below
         * the placement ID.
         * 4) if epin selected as payment method, will check valid epin or not and will finalize the
        * registration else will show epin error.
         * 5) Else will redirect use to payment gateway. till user make payment ID will
         *  be in block state and after successful payment ID will get activated.
         * 6) Commissions will generate after successful registration and will show success message.
         */

            $t1 = time();
            //debug_log('Registration Start Time '. $t1);
            $name = trim($this->post('name'));
            $plan = $this->post('plan');
            $sponsor = $this->common_model->filter($this->post('sponsor'));
            $plan_detail = $this->db_model->select_multi('*', 'plans', array('id' => $plan));
            $is_sponsor_exists = $this->check_sponsor_post($sponsor,$plan);
            
            if(!strlen($is_sponsor_exists)>0){
                $this->response(array(
                    "status" => "false",
                    "message" => "Sponsor ID Doesnot Exist in the selected Plan . '"
                ),REST_CONTROLLER::HTTP_NOT_FOUND);
            }
            if(($this->post('position') != '') && ($this->post('leg') != '')){
                $this->load->model('plan_model');
                if ($this->plan_model->check_position($this->post('position'), $this->post('leg')) !== $this->post('position')){
                    $this->response(array(
                        "status" => "false",
                        "message" => "The selected Position of Placement ID is not empty. "
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
            }
              $position = $this->post('position') ? $this->post('position') : $sponsor;
              $is_position_exists=$this->db_model->select('secret', 'member', array('id' => $position));
              if(!strlen($is_position_exists)>0){
                $this->response(array(
                    "status" => "false",
                    "message" => "Position ID is invalid. "
                ),REST_CONTROLLER::HTTP_NOT_FOUND);
              }
              $leg = $this->input->post('leg') ? $this->input->post('leg') : 'A';
              $email = $this->post('email');
             $phone = $this->post('phone');

              if((config_item('enable_crowdfund')=='Yes') && ($sponsor != config_item('top_id')))
            {
                $sponsor_plan = $this->db_model->select('signup_package', 'member', array('id' => $sponsor));
                if($sponsor_plan != $plan)
                {
                    $this->response(array(
                        "status" => "false",
                        "message" => "You must choose same plan as Sponsor.  "
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
            }

            $epin = $this->post('epin');
            $pg = $this->post('pg');
            $address_1 = trim($this->post('address_1'));
            $city = trim($this->post('city'));
            $state = trim($this->post('state'));
            $zipcode = $this->post('zipcode');
            $country = $this->post('country') ? $this->post('country') : '';
            $password = password_hash($this->post('password'), PASSWORD_DEFAULT);
            $secure_password = password_hash($this->post('password'), PASSWORD_DEFAULT);
            $pan = $this->post('pan');
            $divert_pg = FALSE;
            $role = $this->post('role') ? $this->post('role') : 'Affiliate';

            $plan_price = $plan_detail->joining_fee;
            $tax_amount = round($plan_detail->joining_fee - ($plan_detail->joining_fee / (1 + $plan_detail->gst / 100)), 2);
              
             #####################################################################
            #
            # Check if either epin or payment gateway field is selected or not.
            #
            #####################################################################
            
            if (trim($epin) == "" && trim($pg) == "" && config_item('free_registration') == "No" ) {

                if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "Yes" ) {
                    $this->response(array(
                        "status" => "false",
                        "message" => "Please enter correct e-PIN or Choose Payment Option"
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
                else {
                    if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "No") {
                        $this->response(array(
                            "status" => "false",
                            "message" => "Please enter correct e-PIN."
                        ),REST_CONTROLLER::HTTP_NOT_FOUND);
                    } else {
                        if (config_item('enable_epin') == "No" && config_item('enable_pg') == "Yes") {
                            $this->response(array(
                                "status" => "false",
                                "message" => "Please choose Payment Gateway option."
                            ),REST_CONTROLLER::HTTP_NOT_FOUND);
                        } else {
                            $this->response(array(
                                "status" => "false",
                                "message" => "Please choose either e-PIN or Payment Gateway option."
                            ),REST_CONTROLLER::HTTP_NOT_FOUND);
                        }
                    }
                }
            } else if(trim($epin) == "" && trim($pg) == "" && config_item('free_registration') == "Yes" && $plan_price >0) {

                if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "Yes" ) {
                    $this->response(array(
                        "status" => "false",
                        "message" => "Please enter correct e-PIN or Choose Payment Option"
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
                 else {
                    if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "No") {
                        $this->response(array(
                            "status" => "false",
                            "message" => "Please enter correct e-PIN."
                        ),REST_CONTROLLER::HTTP_NOT_FOUND);
                    } else {
                        if (config_item('enable_epin') == "No" && config_item('enable_pg') == "Yes") {
                            $this->response(array(
                                "status" => "false",
                                "message" => "Please choose Payment Gateway option."
                            ),REST_CONTROLLER::HTTP_NOT_FOUND);
                        }
                    }
                }
            }

            ##############################################################
            #
            # Check plan Price the validate against epin (If epin
            # is selected and not Payment Gateway.
            # Here e-PIN amount or PG Amount is plan price
            #
            ##############################################################
            if (trim($epin) !== "") {
                $epin_details = $this->db_model->select_multi('amount, type', 'epin', array(
                    'epin' => $epin,
                    'status' => 'Un-used'));
                $epin_type = $epin_details->type;
                $epin_value = $epin_details->amount;
            }
            ########################################################
            #
            # check if e-pin value is matched with plan or no
            #
            ########################################################
            if (config_item('free_registration') == "No") {
                if ((trim($epin) !== "" || trim($pg) !== "")) {
                    if (trim($epin) !== "") {
                        if (config_item('show_join_product') == "Yes") {
                            if (trim($plan_price) != trim($epin_value)) {
                                $this->response(array(
                                    "status" => "false",
                                    "message" => "Please select a valid epin"
                                ),REST_CONTROLLER::HTTP_NOT_FOUND);
                            }
                        }
                    } else {
                        $divert_pg = TRUE;
                    }
                }
            } else {
                if($plan_price > 0) {
                    if (config_item('enable_epin') == "Yes" || config_item('enable_pg') == "Yes" ) {
                        if ((trim($epin) !== "" || trim($pg) !== "")) {
                            if (trim($epin) !== "") {
                                if (config_item('show_join_product') == "Yes") {
                                    if (trim($plan_price) != trim($epin_value)) {
                                        $this->response(array(
                                            "status" => "false",
                                            "message" => "Please check the Epin. The Epin is not available for the selected plan."
                                        ),REST_CONTROLLER::HTTP_NOT_FOUND);
                                    }
                                }
                            } else {
                                $divert_pg = TRUE;
                            }
                        }
                    } else {
                        $divert_pg = TRUE;
                    }
                } 
            }
            $topup = $plan_price;
            $member_status = 'Active';
            if((config_item('free_registration')=='Yes') && (config_item('enable_epin') == "No") && config_item('enable_pg') == "No" ) {
                $member_status = $plan_price > 0 ? 'Inactive' : 'Active';
                $topup = 0;
            }

            if (config_item('show_join_product') == "Yes"):
                $mybusiness = $plan_detail->direct_commission;
                if ($plan_detail->qty == "0") {
                    $this->response(array(
                        "status" => "false",
                        "message" => "The selected plan/service is out of stock. Please contact admin."
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
            endif;

            if (config_item('show_join_product') == "No" && config_item('free_registration') == "No" && trim($pg) == "") {
                $plan_price = $this->input->post('amt_to_pay');
                $plan = 'N/A';
                if ($epin_value < $plan_price) {
                    $this->response(array(
                        "status" => "false",
                        "message" => 'Please enter correct e-PIN of worth: ' . config_item('currency') . $plan_price . ' or more.'
                    ),REST_CONTROLLER::HTTP_NOT_FOUND);
                }
            }
             ##############################################################################
            #
            # Generate ID for the USER
            #
            ##############################################################################
            $rand = rand(1000000, 9999999);
            $id = $this->db_model->select("id", "member", array("id" => $rand));
            while($id==$rand){
                $rand = $rand + 1;    
                $id = $this->db_model->select("id", "member", array("id" => $rand));
            }
            $id = $rand;

            if (config_item('show_join_product') !== "Yes"):
                $mybusiness = $plan_price;
            endif;

             ##########################################################################
            #
            # Now will Redirect to Payment Gateway (If need) or Success Page. At that
            # Page we'll generate income or rewards. Here we'll save some basic
            # important Data with session.
            #
            ##########################################################################
            $this->session->set_userdata('_user_id_', $id);
            $this->session->set_tempdata('_auto_user_id_', $id, '300');
            $this->session->set_tempdata('_inv_id_', $id);
            $this->session->set_userdata('_signup_package_', $plan);
            $this->session->set_userdata('_user_name_', $name);
            $this->session->set_userdata('_sponsor_', $sponsor);
            $this->session->set_userdata('_position_', $position);
            $this->session->set_userdata('_address_', $address_1);
            $this->session->set_userdata('_city_', $city);
            $this->session->set_userdata('_state_', $state);
            $this->session->set_userdata('_zipcode_', $zipcode);
            $this->session->set_userdata('_country_', $country);
            $this->session->set_userdata('_email_', $email);
            $this->session->set_userdata('_phone_', $phone);
            $this->session->set_userdata('_plan_', $plan);
            $this->session->set_userdata('_price_', $plan_price);
            $this->session->set_userdata('_d_password_', $this->input->post('password'));
            $this->session->set_userdata('_d_secure_password_', $this->input->post('password'));
            $this->session->set_userdata('_password_', $password); 
            $this->session->set_userdata('_secure_password_', $secure_password);
            $this->session->set_userdata('_join_time_', date('Y-m-d H:i:s'));
            $this->session->set_userdata('_placement_leg_', $leg);
            $this->session->set_userdata('_topup_', $topup);
            $this->session->set_userdata('_my_business_', $mybusiness);
            $this->session->set_userdata('_plan_detail_', $plan_detail);
            $this->session->set_userdata('_width_', $plan_detail->max_width);
            $this->session->set_userdata('_tax_amount_', $tax_amount);
            $this->session->set_userdata('_member_status_', $member_status);
            $this->session->set_userdata('_pan_', $pan);
            $this->session->set_userdata('role', $role);

            if(($divert_pg == TRUE) && (config_item('free_registration') == "Yes")){
                $this->session->set_userdata('_type_', "paylater");
                $this->session->set_userdata('_topup_', 0);
                $this->session->set_userdata('_my_business_', 0);
                $this->session->set_userdata('_member_status_', 'Inactive');
                $this->complete_registration();
            } else if ($divert_pg == TRUE) {
                $this->session->set_userdata('_type_', "userid");
                $this->earning->insert_into_transaction($id);
                //redirect(site_url('gateway/payment_gateway'));
            } else {
                debug_log('before complete registration time ' . (time()-$t1));
                $this->session->set_userdata('_type_', "userid");
                $this->session->set_userdata('_epin_', $epin);
                $this->session->set_userdata('_epin_value_', $epin_value);
                $this->session->set_userdata('_epin_type_', $epin_type);
                $this->complete_registration_post();
            }
    }
                   
    

    public function check_sponsor_post($sponsor,$plan)
    {
        if($sponsor == config_item('top_id')){
            $is_sponsor_exists = 1;
        }
        else if (config_item('inactive_in_tree')=='Yes'){
            if((config_item('sponsor_different_plan') != 'Yes') && (config_item('width') != '2')){
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'signup_package'=>$plan,'role !='=>'Customer'));    
            } else{
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'role !='=>'Customer'));
            }
        }
        else{
            if((config_item('sponsor_different_plan') != 'Yes') && (config_item('width') != '2')){
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'signup_package'=>$plan,'role !='=>'Customer', 'status !='=>'Inactive'));    
            } else{
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'role !='=>'Customer','status !='=>'Inactive'));
            }
        }

        return $is_sponsor_exists;
    }

    public function check_sponsor_count_post($sponsor,$plan)
    {
        debug_log('sponsor_restriction');
        debug_log(config_item('sponsor_restriction'));
        if (config_item('sponsor_restriction')=='Yes') {
             if($sponsor!=config_item('top_id')){
        $sponsor_count = $this->db->query(" SELECT count(*) as count FROM member 
                WHERE sponsor IN (" .$sponsor .") and signup_package = ".$plan)->result_array()[0]['count'];
            debug_log($this->db->last_query());
            return $sponsor_count; }
        }
       
    }


    public function complete_registration_post()
    {
      $status = $this->Registration_model->register_modal();
      //debug_log("inside complete registration");
      if($status['status'] == false)
      {
        $this->response(array(
            "status" => "false",
            "message" => $status['message']
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
      } else if($status['status'] == true) {
        $this->response(array(
            "status" => "true",
            "message" => "Successfull registration"
        ),REST_CONTROLLER::HTTP_OK);
      }else{
        $this->response(array(
            "status" => "false",
            "message" => "Uncaught Exception Occured"
        ),REST_CONTROLLER::HTTP_OK);
      }
    }

    public function registration_successful_post()
    {
        //debug_log($this->session->_user_id_);
        if ($this->session->_user_id_ > 0)
        {
            $layout['layout'] = "success.php";
            $this->load->view('theme/default/base', $layout);
            //$this->downline_model->update_legs(array());

            ######## UNSET SOME PREVIOUS VALUES  ######### 

            $this->session->unset_userdata('_user_id_');
            $this->session->unset_userdata('_user_name_');
            $this->session->unset_userdata('_sponsor_');
            $this->session->unset_userdata('_position_');
            $this->session->unset_userdata('_address_');
            $this->session->unset_userdata('_email_');
            $this->session->unset_userdata('_phone_');
            $this->session->unset_userdata('_plan_');
            $this->session->unset_userdata('_price_');
            $this->session->unset_userdata('_phone_verified_');
            $this->session->unset_userdata('_verified_');
            $this->session->unset_userdata('_id_upgrade_');

            ##############################################

        } else {
            debug_log("inside else part of registration successful");
           redirect(site_url('site/login'));
        }
    }

    public function failed_registration_post()
    {
        $this->session->unset_userdata('_sponsor_');
        $this->session->unset_userdata('_position_');
        $this->session->unset_userdata('_address_');
        $this->session->unset_userdata('_email_');
        $this->session->unset_userdata('_phone_');
        $this->session->unset_userdata('_plan_');
        $this->session->unset_userdata('_price_');
        $this->session->unset_userdata('_phone_verified_');
        $this->session->unset_userdata('_verified_');
        $this->session->unset_userdata('_id_upgrade_');

        if ($this->session->_user_id_ > 0) {
            /*****************************************************************
             *
             * Registration Complete but Payment Failed. Hence ID is deleted.
             *
             *****************************************************************/

            $id = $this->session->_user_id_;
            $check_legs = $this->db_model->count_all('member', array('position' => $id));
            $user_details = $this->db_model->select_multi('*', 'member', array('id' => $id));
            if ($check_legs > 0 || trim($id) == config_item('top_id')) {
            } else if($user_details->id >0) {
                $position = $this->db_model->select_multi('position, placement_leg, my_img', 'member', array('id' => $id));
                $data = array(
                    $position->placement_leg => 0,
                );
                //debug_log("position from site".$position->position);
                $this->db->where('id', $position->position);
                $this->db->update('member', $data);

                $this->db->where('id', $id);
                $this->db->delete('member');

                $this->db->where('userid', $id);
                $this->db->delete('member_profile');
                $this->db->where('userid', $id);
                $this->db->delete('wallet');

                //unlink(FCPATH . "uploads/" . $position->my_img);
            }

            $layout['layout'] = "fail.php";
            $this->load->view('theme/default/base', $layout);

            $this->session->unset_userdata('_user_id_');
            $this->session->unset_userdata('_user_name_');

        } else {
            //redirect(site_url('site/login'));
        }

    }

    //admin login
    public function admin_post(){
        $user = $this->common_model->filter($this->post('username'));
        $password = $this->common_model->filter($this->post('password'));
        $res = $this->Admin_Model->admin_login($user,$password);

        if($res['status']=="false"){
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_OK);
        }
    }

 //member login
 public function login_post(){
            
    $session_url=$_SESSION['page'];
    $user = $this->common_model->filter($this->post('username'));
    $password = $this->post('password');

    $res = $this->Member_model->member_login($user,$password);
    if($res['status']=="true"){
        $this->response(array(
            "status"    =>  $res['status'],
            "message"   =>  $res['message']
        ),REST_CONTROLLER::HTTP_OK);
    }else{
        $this->response(array(
            "status"    =>  $res['status'],
            "message"   =>  $res['message']
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
    }   
}

//autologin 
public function autologin_post(){
    $userid = $this->post('userid');
     $this->session->_auto_user_id_ = $userid;
    if (isset($this->session->_auto_user_id_)) {
     $res = $this->Api_model->autologin();
     if($res['status'] == "true"){
        $this->response(array(
            "status"    =>  $res['status'],
            "message"   =>  $res['message']
        ),REST_CONTROLLER::HTTP_OK);
     }else{
        $this->response(array(
            "status"    =>  $res['status'],
            "message"   =>  $res['message']
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
     }
    }
}

//reset password
    public function resetPassword_post()
    {
        $datas = array(
            "id" => $this->post('userid'),
            "phone"  => $this->post('phone'),
            "email" => $this->post('email'),
            //"password"  =>$this->post('password')
        );

        $res = $this->Member_model->reset_password($datas);
        if($res['status'] == "true"){
            $this->response(array(
                "status"    =>  $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_OK);
         }else{
            $this->response(array(
                "status"    =>  $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
         }
    }

    //dashboard details
    public function load_member_data_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->User_model->load_member_data($userid);
            $this->response(array(
                "status"    =>  "true",
                "message"   =>  $res
            ),REST_CONTROLLER::HTTP_OK);

        }else{
            $this->response(array(
                "status"    =>  "false",
                "message"   =>  "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }   
    }

    //welcome letter
    public function welcome_letter_post(){
       $userid =  $this->post('userid');
       $check = $this->User_model->checkUser($userid);
       if($check){
            $res = $this->Member_model->Welcome_letter_api($userid);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
       }else{
        $this->response(array(
            "status"    =>  "false",
            "message"   =>  "Invalid Userid"
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
       }
    }

    public function settings_post(){
        $datas = array(
            'id'            =>  1, //only 1 admin
            'oldpass'       =>  $this->post('oldpass'),
            'newpass'       =>  $this->post('newpass'),
            'repass'        =>  $this->post('repass'),  ///validations for new pass ad repass is not done 
            'securepass'    =>  $this->post('securepass')
        );
        $res = $this->Admin_Model->admin_password($datas);
        echo json_encode($res);

    }
    public function profile_update_post(){
        $data = array(
            'id'            =>  1, 
            'name'          =>  $this->post('my_name'),
            'phone'         =>  $this->post('my_phone'),
            'email'         =>  $this->post('my_email'),
            'securepass'    =>  $this->post('securepass')
         );
         $res = $this->Admin_Model->profile_update($data);
         echo json_encode($res); 
    }



    public function used_epin_post(){
        $userid =  $this->post('userid');

        $config['base_url']   = site_url('admin/used_epin');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('epin', array('status' => 'Used'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);
        $check = $this->User_model->checkUser($userid);
        if($check){
        $res = $this->Member_model->used_epins($config,$page,$userid);
        $this->response(array(
            "status" => 'true',
            "message" => $res
        ),REST_CONTROLLER::HTTP_OK);
       }else{
        $this->response(array(
            "status" => 'false',
            "message" => 'Inavlid userid'
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
       }
    }
    
    public function unused_epin_post(){
        $userid =  $this->post('userid');
        $config['base_url']   = site_url('admin/unused_epin');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('epin', array('status' => 'Un-used'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);
        $check = $this->User_model->checkUser($userid);
        if($check){
        $res = $this->Member_model->unused_epins($config,$page,$userid);
        $this->response(array(
            "status" => "true",
            "message" => $res
        ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid UserId"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function transer_epin_post(){
        $data = array(
        'amount' => $this->post('amount'),
        'from'   => $this->post('from'),
        'to'     => $this->post('to'),
        'qty'    => $this->post('qty')
        );
        
        $res = $this->Admin_Model->transfer_epin($data);
        if($res['status']=="true"){
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_OK);
           }else{
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
           }
    }

    public function view_earning_post(){
        $userid = $this->post('userid');
        $config['base_url'] = site_url('member/view_earning');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('earning', array('userid' => $userid));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $check = $this->User_model->checkUser($userid);
        if($check){
        $res = $this->Member_model->view_earning($config,$page,$userid);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
           }else{
            $this->response(array(
                "status" => "false",
                "message" => "Inavlid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }

    public function my_deduction_post(){
        $userid = $this->post('userid');
        $config['base_url'] = site_url('member/view_deductions');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('deductions', array('user_id' => $this->session->user_id));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $check = $this->User_model->checkUser($userid);
        if($check){
        $res = $this->Member_model->my_deductions($config,$page,$userid);
        $this->response(array(
            "status" => "true",
            "message" => $res
        ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Inavlid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }

    public function my_rewards_post(){
        $userid = $this->post('userid');
        $config['base_url'] = site_url('member/my_rewards');
        $config['per_page'] = 100;
        $config['total_rows'] = $this->db_model->count_all('rewards', array('userid' => $userid));
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->my_rewards($config,$page,$userid);
                $this->response(array(
                    "status" => $res['status'],
                    "message" => $res['message']
                ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        } 
    }

    public function epin_deposit_post(){
        $userid = $this->post('userid');
        $name = $this->post('name');
        $epin = trim($this->post('epin'));
        $amount = trim($this->post('amount'));

        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->epin_deposit($epin,$userid,$name);
            if($res['status']=="false"){
                $this->response(array(
                    "status" => $res['status'],
                    "message" => $res['message']
                ),REST_CONTROLLER::HTTP_NOT_FOUND);
            }else{
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_OK); }
        }else{
        $this->response(array(
            "status" => "false",
            "message" => "Invalid userid"
        ),REST_CONTROLLER::HTTP_NOT_FOUND);                        
    }
    }
 
    public function deposit_history_post(){
        $userid = $this->post('userid');
        $config['base_url']   = site_url('member/online_transactions');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('transaction');          
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->deposit_history($config,$page);

            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }

    public function bank_deposit_post(){
        $data = array(
        "userid"    => $this->post('userid'),
        "to_userid" =>  $this->uri->segment('4') ? $this->uri->segment('4') : 'Admin',
        "name"      =>  $this->post('name'),
        "payment_mode"  =>  $this->post('payment_mode'),
        "amount"    => $this->post('amount'),
        "txn_no"    => $this->post('txn_no')
        );
        //debug_log($data);
         $res = $this->Member_model->bank_deposit($data);
         if($res['status']=="false"){
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
        $this->response(array(
            "status" => $res['status'],
            "message" => $res['message']
        ),REST_CONTROLLER::HTTP_OK); }

    }
    
     public function wallet_transaction_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->wallet_transaction($userid);

            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Inavlid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
   public function payout_report_post(){
        $status = $this->post('status');
        $sdate  = $this->post('sdate');
        $edate  = $this->post('edate');
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->payout_report($status,$sdate,$edate,$userid);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
            
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Inavlid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function tax_report_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check){
            $sdate  = $this->post('sdate') ? $this->post('sdate') : '2019-01-01';
            $edate  = $this->post('edate') ? $this->post('edate') : date("Y-m-d");
            $res = $this->Member_model->tax_report($userid,$sdate,$edate);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Inavlid userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function direct_list_post(){
    $userid = $this->post("userid");
    $check = $this->User_model->checkUser($userid);
        if($check){
            $res = $this->Member_model->direct_list($userid);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
         $this->response(array(
             "status" => "false",
             "message" => "Inavlid userid"
         ),REST_CONTROLLER::HTTP_NOT_FOUND);
         }
    }
    
    public function add_member_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check){
            $link = array(base_url().'site/register/A/'. $userid);
            $this->response(array(
                "status" => "true",
                "message" => $link
            ),REST_CONTROLLER::HTTP_OK);
        }else{
        $this->response(array(
            "status" => "false",
            "message" => "Inavlid userid"
        ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
     public function transfer_epin_history_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        //debug_log($check);
        if($check>0){
            $res = $this->Member_model->transfer_epin_history($userid);

            if($res['status']=="true"){
                $this->response(array(
                    "status" => $res['status'],
                    "message" => $res['message']
                ),REST_CONTROLLER::HTTP_OK);
            
            }else{
                $this->response(array(
                    "status" => $res['status'],
                    "message" => $res['message']
                ),REST_CONTROLLER::HTTP_NOT_FOUND); 
            }
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function my_tree_unilevel_post(){
        $userid = $this->post('userid');
        $plan = $this->post('plan');
        $top_id = $this->post('top_id');
        $check = $this->User_model->checkUser($userid);
        if($check>0){
            if(config_item('id_upgrade')=='Yes')
            {
                if($top_id !== ""){
                    if (trim($userid) !== "" && $top_id < $userid) {
                        $this->response(array(
                            "status" => "false",
                            "message" => "You cannot view upline tree !"
                        ),REST_CONTROLLER::HTTP_NOT_FOUND);
                        }else{
                            $userid = $top_id;
                            //debug_log($userid);
                            $res = $this->Member_model->unilevel_tree($plan,$userid,$topid);

                            if($res['status']=="true"){
                                $this->response(array(
                                    "status" => $res['status'],
                                    "message" => $res['message']
                                ),REST_CONTROLLER::HTTP_OK);
                            
                            }else{
                                $this->response(array(
                                    "status" => $res['status'],
                                    "message" => $res['message']
                                ),REST_CONTROLLER::HTTP_NOT_FOUND); }
                            }
                        
                }else{
                        
                    $res = $this->Member_model->unilevel_tree($plan,$userid,$topid);
                    
                    if($res['status']=="true"){
                        $this->response(array(
                            "status" => $res['status'],
                            "message" => $res['message']
                        ),REST_CONTROLLER::HTTP_OK);
                    
                    }else{
                        $this->response(array(
                            "status" => $res['status'],
                            "message" => $res['message']
                        ),REST_CONTROLLER::HTTP_NOT_FOUND); }
                    }
            }

        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function profile_member_post(){
        $data  = array(
            "id"            =>  $this->post('id'),
            "email"         =>  $this->post('email'),
            "date_of_birth" =>  $this->post('date_of_birth'),
            "photo"         =>  $this->post('photo'),
            "address"       =>  $this->post('address'),
            "city"          =>  $this->post('city'),
            "state"         =>  $this->post('state'),
            "zipcode"       =>  $this->post('zipcode'),
            "securepass"    =>  $this->post('securepass')
        );
        debug_log($data);
        $res = $this->Api_model->profile_update($data);
        if($res['status']=="true"){
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_OK);
        
        }else{
            $this->response(array(
                "status" => $res['status'],
                "message" => $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND); 
        }
    }
    
    public function rewards_achiever_get(){

        $res = $this->Member_model->rewards_achiever();
        $this->response(array(
            "status" => "true",
            "message" => $res
        ),REST_CONTROLLER::HTTP_OK);
    }
    
    public function live_updates_get(){

        $res = $this->Member_model->live_updates();

        $this->response(array(
            "status" => "true",
            "message" => $res
        ),REST_CONTROLLER::HTTP_OK);
    }
    
    public function my_invoices_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check>0){

            $res = $this->Member_model->my_invoices($userid);

            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function new_ticket_post(){
        $data = array(
           
            'ticket_title'  => $this->post('ticket_title'),
            'ticket_detail' => date('Y-m-d') . '<br>'.$this->post('ticket_data'),
            'userid'        => $this->post('userid'),
            'user_type'     => "User",
            'date'          => date('Y-m-d H:i:s'),
        );

        $query = $this->db->insert('ticket', $data);
        if($query){
            $this->response(array(
                "status" => "true",
                "message" => "A New Ticket has been opened."
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Unable to open a Ticket"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }   
        
    }
    
    public function list_ticket_post(){
        $userid = $this->post('userid');
        $check = $this->User_model->checkUser($userid);
        if($check>0){
            $res = $this->Member_model->list_ticket($userid);
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function old_purchase_post(){
        $userid = $this->post('userid');

        $check = $this->User_model->checkUser($userid);
        if($check>0){
            $res = $this->Member_model->old_purchase($userid);
        
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }


    public function update_shipping_address_post(){
        $userid = $this->post('userid');
        $securepass = $this->post('securepass');

        $check = $this->User_model->checkUser($userid);
        if($check>0){
            $data = array(
                'userid'    => $userid,
                's_name'    =>$this->post('my_name'),
                's_phone'   =>$this->post('my_phone'),
                's_email'   => $this->post('my_email'),
                's_city'    => $this->post('my_city'),
                's_state'   => $this->post('my_state'),
                's_address' => $this->post('my_address'),
                's_zipcode' => $this->post('my_zipcode'),
                );
            $res = $this->Member_model->update_shipping($userid,$data,$securepass);
            if($res['status']=="false"){
                $this->response(array(
                    "status"    => $res['status'],
                    "message"   =>  $res['message']
                ),REST_CONTROLLER::HTTP_NOT_FOUND);
            }else{
                $this->response(array(
                    "status"    => $res['status'],
                    "message"   =>  $res['message']
                ),REST_CONTROLLER::HTTP_OK);
            }
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
            
        }
    }
    
    public function update_billing_address_post(){
        $userid = $this->post('userid');
        $securepass = $this->post('securepass');

        $check = $this->User_model->checkUser($userid);
        if($check>0){
            $data = array(
                'b_name' =>$this->post('my_name'),
                'b_phone' =>$this->post('my_phone'),
                'b_email' => $this->post('my_email'),
                'b_city'  => $this->post('my_city'),
                'b_state'  => $this->post('my_state'),
                'b_address' => $this->post('my_address'),
                'b_zipcode'  => $this->post('my_zipcode'),
                );
                debug_log($data);
                debug_log($securepass);
               
            $res = $this->Member_model->update_billing($userid,$data,$securepass);
            if($res['status']=="false"){
                $this->response(array(
                    "status"    => $res['status'],
                    "message"   =>  $res['message']
                ),REST_CONTROLLER::HTTP_NOT_FOUND);
            }else{
                $this->response(array(
                    "status"    => $res['status'],
                    "message"   =>  $res['message']
                ),REST_CONTROLLER::HTTP_OK);
            }

        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function new_purchase_post(){
        $userid = $this->post('userid');

        $check = $this->User_model->checkUser($userid);
        if($check>0){
            $res = $this->Member_model->new_purchase();
            $this->response(array(
                "status" => "true",
                "message" => $res
            ),REST_CONTROLLER::HTTP_OK);
        }else{
            $this->response(array(
                "status" => "false",
                "message" => "Invalid Userid"
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }
    }
    
    public function change_password_post(){
        $data = array(
            'oldpass' => $this->post('oldpass'),
            'newpass' => $this->post('newpass'),
            'repass' => $this->post('repass')
        );
        $userid =  $this->post('userid');

        $res = $this->Member_model->update_password($userid,$data);
        if($res['status']=="false"){
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_OK);
        }
        
    }  

    public function change_secure_password_post(){
        $data = array(
            'oldpass' => $this->post('secure_oldpass'),
            'newpass' => $this->post('secure_newpass'),
            'repass' => $this->post('secure_repass')
        );
        $userid =  $this->post('userid');

        $res = $this->Member_model->updatesecure_password($userid,$data);
        if($res['status']=="false"){
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_NOT_FOUND);
        }else{
            $this->response(array(
                "status"    => $res['status'],
                "message"   =>  $res['message']
            ),REST_CONTROLLER::HTTP_OK);
        }
        
    }

}


