<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Site
 */
class Site extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('downline_model');
        $this->load->model('earning');
        $this->load->model('registration_model');
        $this->load->model('plan_model');
        $this->load->model('payments_model');
        //$this->output->enable_profiler(TRUE);
    }

    public function index()
    {
        if(config_item('homepage')=='store') {
            redirect(site_url('store'));
        } else if(config_item('homepage')=='theme') {
            redirect(site_url('site/login'));
        }else if(config_item('homepage')=='recharge'){
            redirect(site_url('site/recharge'));
        }else{
            $this->load->view(config_item('homepage'));   
        }
    }
    
    public function App($value1, $value2)
    {
        $this->load->view('templates/'.$value1.'/'.$value2);
    }

    public function contact_home()
    {
        $this->load->view(config_item('homepage').'/contact');
    }

    public function products()
    {
        $this->load->view(config_item('homepage').'/products');
    }

    public function about()
    {
        $this->load->view(config_item('homepage').'/about');   
    }

    public function classic()
    {
        $this->load->view('classic/index');   
    }

    public function verify()
    {   

        $type = $this->uri->segment('3') ? $this->uri->segment('3') : '';

        if($type == 'reset')
        {
            $this->session->unset_userdata('_phone_');
            redirect(site_url('site/verify'));
        }
        $this->load->view('theme/verify_phone');
    }

    // End of functions for E Commerce Site ///
    public function documentation()
    {
        $layout['layout'] = "documentation.php";
        $this->load->view('theme/default/base', $layout);

    }

    public function terms()
    {
        $layout['layout'] = "terms.php";
        $this->load->view('theme/default/base', $layout);

    }
    
    public function disclaimer()
    {
        $layout['layout'] = "disclaimer.php";
        $this->load->view('theme/default/base', $layout);

    }
     public function terms_condition()
    {
        $layout['layout'] = "terms_condition.php";
        $this->load->view('theme/default/base', $layout);

    }
     public function privacy_policy()
    {
        $layout['layout'] = "privacy_policy.php";
        $this->load->view('theme/default/base', $layout);

    }
      public function return_policy()
    {
        $layout['layout'] = "return_policy.php";
        $this->load->view('theme/default/base', $layout);

    }

    public function recharge()
    {   
        if($this->session->userdata('_provider_data_')!=''){
            $provider_data = $this->session->userdata('_provider_data_');
        }else{
            //debug_log('inside else 1');
            $provider_data = $this->payments_model->get_providers();
            $this->session->set_userdata('_provider_data_', $provider_data);    
        }

        if($this->session->userdata('_service_')!=''){
            $service = $this->session->userdata('_service_');
        }else{
            //debug_log('inside else 2');
            $service = $this->payments_model->get_services();
            $this->session->set_userdata('_service_', $service);    
        }

        $data['provider_data'] = $provider_data;
        $data['service']=$service;
        $data['response']=$response;
        $this->load->view('templates/recharge/'.config_item('recharge_theme'), $data);
    }

    public function bbps_recharge()
    {   
       /* if($this->session->userdata('_provider_data_')!=''){
            $provider_data = $this->session->userdata('_provider_data_');
        }else{
            //debug_log('inside else 1');
            $provider_data = $this->payments_model->get_providers();
            $this->session->set_userdata('_provider_data_', $provider_data);    
        }

        if($this->session->userdata('_service_')!=''){
            $service = $this->session->userdata('_service_');
        }else{
            //debug_log('inside else 2');
            $service = $this->payments_model->get_services();
            $this->session->set_userdata('_service_', $service);    
        }

        $data['provider_data'] = $provider_data;
        $data['service']=$service;
        $data['response']=$response; */
        $this->load->view('templates/bbps_recharge/'.config_item('recharge_theme'), $data);
    }

    public function send_otp()
    {

        if($this->session->userdata('_phone_') > 0)
        {
            $phone = $this->session->userdata('_phone_');            
        }
        else{
            $phone = $this->input->post('phone');    
        }
        
        $is_phone_exists=$this->db_model->select('phone', 'member', array('phone' => $phone));

        if($is_phone_exists > 0)
        {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Phone Number Already Registered. Please Provide a New Number ' . '</div>');
            redirect(site_url('site/verify'));    
        }

        $otp=$this->common_model->randomPassword();
        //debug_log($otp);

        $sms = "Hello, \n\nGreetings from " . config_item('company_name') . "\n\nYour OTP is: " . $otp ."\n\nPlease enter OTP to proceed with registration";
        $messvar="Ok";
        $phone_text="91".$phone;
        $this->common_model->sms($phone_text, urlencode($sms));

        $this->session->unset_userdata('_phone_');
        $this->session->unset_userdata('_otp_');
        $this->session->set_userdata('_phone_', $phone);
        $this->session->set_userdata('_otp_', $otp);
        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Please enter the OTP ' . '</div>');
        redirect(site_url('site/verify'));
        
    }

    public function validate_otp()
    {
        $phone = $this->input->post('phone');
        $otp = $this->input->post('otp');

        if($otp != $this->session->userdata('_otp_'))
        {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct OTP ' . '</div>');
            redirect(site_url('site/verify'));
        }
        else {
            
            $this->session->unset_userdata('_otp_');
            $this->session->unset_userdata('_phone_');
            $this->session->set_userdata('_phone_verified_', $phone);
            $this->session->set_userdata('_verified_', TRUE);
            redirect(site_url('site/register'));   
        }

    }

    public function coming_soon(){
        $this->load->view('theme/coming-soon');   
    }


    public function register()
    {
        
        if((config_item('verify')=='Yes') && ($this->session->userdata('_verified_') != TRUE))
        {
            redirect(site_url('site/verify'));
        }

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

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('sponsor', 'Sponsor ID', 'trim|required');
        //$this->form_validation->set_rules('address_1', 'Address Line 1', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
        $this->form_validation->set_rules('password_2', 'Retype Password', 'trim|required|matches[password]');
        $this->form_validation->set_rules('phone', 'Phone No', 'trim|required');

        if ($this->form_validation->run() !== FALSE) {

            $t1 = time();

            #$data = $this->db_model->array_from_post(array('name','plan','','','','','',))
            #$data = $this->security->xss_clean($data);

            //debug_log('Registration Start Time '. $t1);
            $name = trim($this->input->post('name'));
            $plan = $this->input->post('plan');
            $sponsor = $this->common_model->filter($this->input->post('sponsor'));
            $plan_detail = $this->db_model->select_multi('*', 'plans', array('id' => $plan));

            $is_sponsor_exists = $this->check_sponsor($sponsor,$plan);
            
            if(!strlen($is_sponsor_exists)>0){
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Sponsor ID Doesnot Exist in the selected Plan ' . '</div>');
                redirect(site_url('site/register'));
            }

            if(($this->input->post('position') != '') && ($this->input->post('leg') != '')){
              if ($this->plan_model->check_position($this->input->post('position'), $this->input->post('leg')) !== $this->input->post('position')){
                  $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">The selected Position of Placement ID is not empty.</div>');
                  redirect(site_url('site/register'));
              }
            }

            $sponsor_count = $this->check_sponsor_count($sponsor,$plan);
            if($sponsor_count >=$plan_detail->max_width){
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">More than          '.$plan_detail->max_width.' Sponsor ID Doesnot Eligible to Sponsor ' . '</div>');
                redirect(site_url('site/register'));
            }

            $position = $this->input->post('position') ? $this->input->post('position') : $sponsor;

            $is_position_exists=$this->db_model->select('secret', 'member', array('id' => $position));
            if(!strlen($is_position_exists)>0){
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Position ID is invalid ' . '</div>');
                redirect(site_url('site/register'));
            }

            debug_log(strlen($this->input->post('leg')));
            $leg = $this->input->post('leg') ? $this->input->post('leg') : 'A';

            $email = $this->input->post('email');
            $phone = $this->input->post('phone');

            if((config_item('enable_crowdfund')=='Yes') && ($sponsor != config_item('top_id')))
            {
                $sponsor_plan = $this->db_model->select('signup_package', 'member', array('id' => $sponsor));
                if($sponsor_plan != $plan)
                {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">You must choose same plan as Sponsor ' . '</div>');
                    redirect(site_url('site/register'));
                }
            }

            $epin = $this->input->post('epin');
            $pg = $this->input->post('pg');
            $address_1 = trim($this->input->post('address_1'));
            $city = trim($this->input->post('city'));
            $state = trim($this->input->post('state'));
            $zipcode = $this->input->post('zipcode');
            $country = $this->input->post('country') ? $this->input->post('country') : '';
            $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            $secure_password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            $pan = $this->input->post('pan');
            $divert_pg = FALSE;
            $role = $this->input->post('role') ? $this->input->post('role') : 'Affiliate';

            $plan_price = $plan_detail->joining_fee;
            $tax_amount = round($plan_detail->joining_fee - ($plan_detail->joining_fee / (1 + $plan_detail->gst / 100)), 2);

            #####################################################################
            #
            # Check if either epin or payment gateway field is selected or not.
            #
            #####################################################################


            if (trim($epin) == "" && trim($pg) == "" && config_item('free_registration') == "No" ) {

                if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "Yes" ) {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct e-PIN or Choose Payment Option</div>');
                }
                else {
                    if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "No") {
                        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct e-PIN.</div>');
                    } else {
                        if (config_item('enable_epin') == "No" && config_item('enable_pg') == "Yes") {
                            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please choose Payment Gateway option.</div>');
                        } else {
                            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please choose either e-PIN or Payment Gateway option.</div>');
                        }
                    }
                }
                redirect(site_url('site/register'));
            } else if(trim($epin) == "" && trim($pg) == "" && config_item('free_registration') == "Yes" && $plan_price >0) {

                if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "Yes" ) {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct e-PIN or Choose Payment Option</div>');
                    redirect(site_url('site/register'));
                }
                 else {
                    if (config_item('enable_epin') == "Yes" && config_item('enable_pg') == "No") {
                        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct e-PIN.</div>');
                        redirect(site_url('site/register'));
                    } else {
                        if (config_item('enable_epin') == "No" && config_item('enable_pg') == "Yes") {
                            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please choose Payment Gateway option.</div>');
                            redirect(site_url('site/register'));
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
                                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please check the Epin. The Epin is not available for the selected plan.</div>');
                                redirect(site_url('site/register'));
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
                                        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please check the Epin. The Epin is not available for the selected plan.</div>');
                                        redirect(site_url('site/register'));
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
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">The selected plan/service is out of stock. Please contact admin.</div>');
                    redirect(site_url('site/register'));
                }
            endif;


            if (config_item('show_join_product') == "No" && config_item('free_registration') == "No" && trim($pg) == "") {
                $plan_price = $this->input->post('amt_to_pay');
                $plan = 'N/A';
                if ($epin_value < $plan_price) {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct e-PIN of worth: ' . config_item('currency') . $plan_price . ' or more.</div>');
                    redirect(site_url('site/register'));

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

            $divert_pg == TRUE;

            if(($divert_pg == TRUE) && (config_item('free_registration') == "Yes")){
                $this->session->set_userdata('_type_', "paylater");
                $this->session->set_userdata('_topup_', 0);
                $this->session->set_userdata('_my_business_', 0);
                $this->session->set_userdata('_member_status_', 'Inactive');
                $this->complete_registration();
            } else if ($divert_pg == TRUE) {
                $this->session->set_userdata('_type_', "userid");
                $this->earning->insert_into_transaction($id);
                redirect(site_url('gateway/payment_gateway'));
            } else {
                debug_log('before complete registration time ' . (time()-$t1));
                $this->session->set_userdata('_type_', "userid");
                $this->session->set_userdata('_epin_', $epin);
                $this->session->set_userdata('_epin_value_', $epin_value);
                $this->session->set_userdata('_epin_type_', $epin_type);
                $this->complete_registration();
            }

        } else {
            ####################################################
            #
            # If form is not submitted then, will show the
            # default registration form.
            #
            ####################################################
            $this->db->select('id, plan_name,joining_fee, gst')->where(array(
                'status' => 'Selling','show_on_regform' => 'Yes',
                ))->order_by('plan_name', 'ASC');
            $layout['plans'] = $this->db->get('plans')->result_array();
            $layout['leg'] = $this->plan_model->create_leg();
            switch(config_item('reg_default'))
            {
                case "Yes":
                    $layout['layout'] = "register_default.php";//"register_default_values.php";
                    break;
                default:
                    $layout['layout'] = "register.php";
                    break;
            }
            $this->load->view('theme/default/base', $layout);
        }
    }

    public function check_sponsor($sponsor,$plan)
    {
        if($sponsor == config_item('top_id')){
            $is_sponsor_exists = 1;
        }
        else if ((config_item('inactive_in_tree')=='Yes')&&(config_item('inactive_sponsor')=='Yes')){
            if((config_item('sponsor_different_plan') != 'Yes') && (config_item('width') != '2')){
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'signup_package'=>$plan,'role !='=>'customer'));    
            } else{
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'role !='=>'customer'));
            }
        }
        else if ((config_item('inactive_in_tree')=='Yes')&&(config_item('inactive_sponsor')=='No')){
            if((config_item('sponsor_different_plan') != 'Yes') && (config_item('width') != '2')){
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'signup_package'>$plan,'role !='=>'customer', 'status !='=>'Inactive'));    
            } else{
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'role !='=>'ustomer', 'status !='=>'Inactive'));
            }
        }
        else{
            if((config_item('sponsor_different_plan') != 'Yes') && (config_item('width') != '2')){
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'signup_package'=>$plan,'role !='=>'customer', 'status !='=>'Inactive'));    
            } else{
                $is_sponsor_exists=$this->db_model->select('secret', 'member', array('id' => $sponsor,'role !='=>'customer','status !='=>'Inactive'));
            }
        }

        return $is_sponsor_exists;
    }

    public function check_sponsor_count($sponsor,$plan)
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


    public function complete_registration()
    {
      $status = $this->registration_model->register_modal();
      //debug_log("inside complete registration");
      if($status['status'] == false)
      {
          $this->session->set_flashdata('site_flash', $status['message']);
          redirect(site_url('site/register'));
      } else if($status['status'] == true) {
        redirect(site_url('site/registration_successful'));
      } else{
        $this->session->set_flashdata('site_flash', 'Uncaught Exception Occured');
         redirect(site_url('site/register'));
      }

    }

    public function registration_successful()
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

    public function failed_registration()
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
                debug_log("position from site".$position->position);
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
            redirect(site_url('site/login'));
        }

    }

    //vendor register
    public function vendor_register()
    {

      if((config_item('verify')=='Yes') && ($this->session->userdata('_verified_') != TRUE))
      {

          redirect(site_url('site/verify'));
      }

      $this->form_validation->set_rules('name', 'Name', 'trim|required');
      $this->form_validation->set_rules('address_1', 'Address Line 1', 'trim|required');
      $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
      $this->form_validation->set_rules('password_2', 'Retype Password', 'trim|required|matches[password]');
      $this->form_validation->set_rules('phone', 'Phone No', 'trim|required');
      $this->form_validation->set_rules('pancard', 'PAN Card Number', 'trim|required');
      

      if ($this->form_validation->run() !== FALSE) 
      {

          $email = $this->input->post('email');
          $phone = $this->input->post('phone');
          $address=$this->input->post('address_1');
          $city=$this->input->post('city');
          $state=$this->input->post('state');
          $zipcode=$this->input->post('zipcode');
          $name=$this->input->post('name');
          $company_name=$this->input->post('company');
          $gstin=$this->input->post('gstin');
          $pancard=$this->input->post('pancard');
          $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
          $secure_password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
          $unhashed_password=$this->input->post('password');

          if($name==""){
          $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Name cannot be left blank' . '</div>');
          redirect(site_url('site/vendor_register'));
          }
          

          if($pancard==""){
             // print_r("pancard is empty");die();
          $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">PAN Card Number cannot be left blank' . '</div>');
          redirect(site_url('site/vendor_register'));
          }
      
          $id = rand(1000000, 9999999);
          $this->session->set_userdata('_vendor_id_', $id);
          $this->session->set_userdata('_company_name_', $company_name);
          $this->session->set_userdata('_gstin_', $gstin);
          $this->session->set_userdata('_pancard_', $pancard);

          $this->session->set_userdata('_email_', $email);
          $this->session->set_userdata('_phone_', $phone);
          $this->session->set_userdata('_address_', $address);
          $this->session->set_userdata('_city_', $city);
          $this->session->set_userdata('_state_', $state);
          $this->session->set_userdata('_zipcode_', $zipcode);
          $this->session->set_userdata('_name_', $name);
          $this->session->set_userdata('_password_', $password);
          $this->session->set_userdata('_secure_password_', $secure_password);
          $this->session->set_userdata('_unhashed_password_', $unhashed_password);
          $this->complete_vendor_registration();
      }
      else
      {

          switch($this->db_model->select('description', 'settings', array('type' => 'reg')))
             {
                      case "1":
                          $layout['layout'] = "vendor_register.php";//"register_default_values.php";
                          break;
                      default:
                          $layout['layout'] = "vendor_register.php";
                          break;
              }
             $this->load->view('theme/default/base', $layout);
      }

    }
    public function complete_vendor_registration()
    {
        //$unhashed_password
        $vendor_id=$this->session->userdata('_vendor_id_');
        $company_name=$this->session->userdata('_company_name_');
        $gstin=$this->session->userdata('_gstin_');
        $pancard=$this->session->userdata('_pancard_');
        $name = $this->session->userdata('_name_');
        $phone= $this->session->userdata('_phone_');
        $email= $this->session->userdata('_email_');
        $address= $this->session->userdata('_address_');
        $city= $this->session->userdata('_city_');
        $state= $this->session->userdata('_state_');
        $zipcode= $this->session->userdata('_zipcode_');
        $password= $this->session->userdata('_password_');
        $secure_password= $this->session->userdata('_secure_password_');
        $unhashed_password= $this->session->userdata('_unhashed_password_');
        if($vendor_id != '' && $name !='' && $password != '' && $email!= '' && $secure_password!='' && $phone!='' )
        {
            $data = array(
                    'vendor_id' => $vendor_id,
                    'name' => $name,
                    'company_name' => $company_name,
                    'email' =>$email,
                    'phone' => $phone,
                    'username'=>$vendor_id,
                    'password' =>$password,
                    'secure_password' =>$secure_password,
                    'address'=>$address,
                    'city'=>$city,
                    'state'=>$state,
                    'zipcode'=>$zipcode,
                    'status'=>'Active',
                    'registration_ip'=>$this->input->ip_address(),
                );
            $this->db->insert('vendor',$data);

            $this->db->query(
                'update tbl_users 
                SET vendor_password="'.$this->input->post('password').'" 
                WHERE user_email = "'.$email.'"' );

            $data= array(
             'vendor_id'=>$vendor_id,
             'gstin' => $gstin,
             'tax_no' =>$pancard,
            );
             $this->db->insert('vendor_profile',$data);

            
        }
        else
        {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please enter correct details </div>');
            redirect(site_url('site/vendor_register'));
        }

        $vendor_id = $this->db_model->select('id', 'vendor', array(
            'phone' => $phone,
            'registration_ip' => $this->input->ip_address,
            'name'  =>$name,
        ));
        if($vendor_id >0){
            if (config_item('sms_on_join') == "Yes"):
                $sms = "Hello " . $name . ", \nThank you for registering with " . config_item('company_name') . "\nYour vendor ID is: " . config_item('ID_EXT','User Id') . $vendor_id . " \nPassword is: " . $unhashed_password ;
                $messvar="Ok";
                $phone="91".$phone;
                $this->common_model->sms($phone, urlencode($sms));
            endif;
            $sub = "Welcome to " . config_item('company_name');
            $msg = "Hello " . $name . "<br/> Welcome to " . config_item('company_name') . "Just now you have successfully registered with us. Hope your journey with us will remain exciting and rewarding. <hr/>  <strong>Vendor ID :</strong> " . config_item('ID_EXT') . $vendor_id . "<br/>
            <strong>Password :</strong> " . $unhashed_password . "
            <hr/><---<br/>Regards,<br/>www." . $_SERVER['HTTP_HOST'];
            $this->load->config('email');
            if (trim(config_item('smtp_host')) !== "") {
                $this->db_model->mail($email, $sub, $msg);
            }
          
        }
        else{
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Some error occured while registering. please contact admin or try again.</div>');
            redirect(site_url('site/vendor_register'));
        }
         redirect(site_url('site/vendor_register_successful'));

    }

    public function vendor_register_successful()
    {
        if ($this->session->_vendor_id_ > 0)
        {
            debug_log("This is the vendor id".$this->session->_id);
            $layout['layout'] = "vendor_success.php";
            $this->load->view('theme/default/base', $layout);
            

            ######## UNSET SOME PREVIOUS VALUES  ######### 

            $this->session->unset_userdata('_vendor_id_');
            $this->session->unset_userdata('_name_');
            $this->session->unset_userdata('_address_');
            $this->session->unset_userdata('_city_');
            $this->session->unset_userdata('_state_');
            $this->session->unset_userdata('_zipcode_');
            $this->session->unset_userdata('_email_');
            $this->session->unset_userdata('_phone_');
            
            /*$this->session->unset_userdata('_phone_verified_');
            $this->session->unset_userdata('_verified_');*/

            ##############################################

        } else {
           redirect(site_url('site/vendor_login'));
        }
    }

    public function login()
    {
        parse_parameters($this->uri->segment(3));
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            if(config_item('login_theme')=='default'){
                $this->load->view('theme/login/default');    
            }else{
                $this->load->view('theme/login/mega');    
            }
            
        } else {
            $session_url=$_SESSION['page'];            
            $user = $this->common_model->filter($this->input->post('username'));
            $password = $this->input->post('password');
            $data = $this->db_model->select_multi("*", 'member', array('id' => $user));
            $_lead_email_status = $this->session->lead_email_status;
            $this->load->model('Member_model');
            $res = $this->Member_model->member_login($user,$password);
            parse_parameters($_lead_email_status);

            if($res['status']=="false"){
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">'.$res['message'].'</div>');
                redirect(site_url('site/login'));
            }else if($res['status']=="true"){
                redirect(site_url('member'));
            }
            if(config_item('ecomm_theme')=='gmart')
            {
              $this->db->query(
              'update tbl_users 
               SET affiliate_password="'.$randompassword.'" 
               WHERE affiliate_id = "'.$user_id.'"' );
            }

            if($session_url){
                if(strpos($session_url, 'checkout') !== false){
                    unset($_SESSION["page"]);
                    redirect('emart/shop');
                }
                else if($session_url == site_url('/') && config_item('homepage')=="templates/index.php"){
                	redirect(site_url('/insurance'));
                	//print_r("this is siteurl");exit();
                }
                else if((strpos($session_url, 'emart') !== false) || (strpos($session_url, 'recharge') !== false) || (strpos($session_url, 'astrology') !== false) || ($session_url == site_url('/'))){
                    unset($_SESSION["page"]);
                    redirect($session_url);
                }

                else{
                    redirect(site_url('member'));
                }
            }
            else{
            redirect(site_url('member'));
            }

            
        }
    }

    public function vendor_login()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            //$layout['layout'] = "login.php";
            //$this->load->view('theme/default/base', $layout);
            $this->load->view('theme/vendor_login');
        } else {
            $session_url=$_SESSION['page'];
            $vendor_id = $this->common_model->filter($this->input->post('username'));
            $password = $this->input->post('password');
            $data = $this->db_model->select_multi("vendor_id, name, password, email,status", 'vendor', array('vendor_id' => $vendor_id));

            if (($data->status !== "Active") && ($data->status !== "Inactive")) {
                $this->session->set_flashdata('site_flash', '<div 
                    class="alert alert-danger">Login is invalid or Your account is not active. Account status is: ' . ($data->status ? $data->status : 'N/A') . '.</div>');
                redirect(site_url('site/vendor_login'));
            }

            if (password_verify($password, $data->password)) {
                session_unset();
                $session = md5($vendor_id . time());
                $this->session->set_userdata(array(
                    'vendor_id' => $data->vendor_id,
                    'email' => $data->email,
                    'name' => $data->name,
                    'session' => $session,
                ));
                $data2 = array(
                    'last_login_ip' => $this->input->ip_address(),
                    'last_login' => time(),
                    'session' => $session,
                );
                $this->db_model->update($data2, 'vendor', array('vendor_id' => $data->vendor_id));
                
                /*if($session_url){
                    redirect($session_url);
                    //unset($_SESSION["page"]);
                }*/
                //else{   
                    debug_log("redirected to vendor");
                    redirect(site_url('vendor'));
                  // }

            } else {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid Username or Password.</div>');
                redirect(site_url('site/vendor_login'));
            }
        }
    }

    public function reset_password()
    {
        $user_id = trim($this->input->post('userid'));
        $phone = trim($this->input->post('phone'));
        $email = trim($this->input->post('email'));

        if((!(strlen($phone)>2)) && (!(strlen($email)>2))){
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid details. Please Enter Valid Details.<br> 3 Consecutive Incorrect Attempts will block your account !!!</div>');
                  redirect(site_url('site/login'));
        }

        $data = $this->db_model->select_multi("name, password, phone, email", 'member', array('id' => $user_id));

        if ((trim(config_item('smtp_host')) !== "") || (config_item('sms_on_join') == "Yes")) {

            if(strlen($phone)>2){
                if($phone == $data->phone) {
                session_unset();
                $session = md5($user . time());
                $randompassword=$this->common_model->randomPassword();
                $password = password_hash($randompassword, PASSWORD_DEFAULT);
                $data2 = array(
                      'password' => $password,
                      'last_login_ip' => $this->input->ip_address(),
                      'last_login' => time(),
                      'session' => $session,
                  );
                $this->db_model->update($data2, 'member', array('id' => $user_id));

                $sub = "Login Password Reset";
                $msg = "Hello " . $data->name . ", \nYou have requested for Login Password Reset. \n Temporary Login Password is: " . $randompassword . "\n Kindly update password soon after login \n\n Regards \n".config_item('company_name');
                if (trim(config_item('smtp_host')) !== "") {
                    $this->db_model->mail($data->email, $sub, $msg);
                }


                $sms = "Hello " . $data->name . ", \nYou have requested for Login Password Reset. \n Temporary Login Password is: " . $randompassword . "\n Kindly update password soon after login \n".config_item('company_name');
                $messvar="Ok";
                $phone="91".$phone;
                $status = $this->common_model->sms($phone, urlencode($sms));

                debug_log('SMS Status '.$status);

                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Success - Temporary password is sent to your registered Phone Number. <br> Update login password soon after login using Temporary Password</div>');
                redirect(site_url('site/login'));

                } else {
                  $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid details. Please Enter Valid Details.<br> 3 Consecutive Incorrect Attempts will block your account !!!</div>');
                  redirect(site_url('site/login'));
                }
            }

            if(strlen($email)>2){
                if($email == $data->email){
                    $randompassword=$this->common_model->randomPassword();
                    $password = password_hash($randompassword, PASSWORD_DEFAULT);

                    $sub = "Login Password Reset";
                    $msg = "Hello " . $data->name . ", <br><br>You have requested for Login Password Reset. <br><br> Temporary Login Password is: " . $randompassword . "<br><br>Kindly update password soon after login <br><br>Regards <br>Support Team<br>".config_item('company_name');
                    if (trim(config_item('smtp_host')) !== "") {
                        $status = $this->db_model->mail($data->email, $sub, $msg);
                    }

                    debug_log('Email Status '.$status);

                    if($status == 'Success')
                    {
                        session_unset();
                        $session = md5($user . time());
                        $data2 = array(
                              'password' => $password,
                              'last_login_ip' => $this->input->ip_address(),
                              'last_login' => time(),
                              'session' => $session,
                          );
                        $this->db_model->update($data2, 'member', array('id' => $user_id));

                        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Success - Temporary password is sent to your registered Email. <br> Update login password soon after login using Temporary Password</div>');
                        redirect(site_url('site/login'));

                    }
                    else
                    {
                        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password couldnot reset at the moment. Please try later !!!</div>');
                        redirect(site_url('site/login'));
                    }
                }
                else
                {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid details. Please Enter Valid Details.<br> 3 Consecutive Incorrect Attempts will block your account !!!</div>');
                    redirect(site_url('site/login'));
                }

            }
            
        } else {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password couldnot reset at the moment. Please try later !!!</div>');
            redirect(site_url('site/login'));

        }

    }

    

    public function franchisee()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('theme/franchisee_login');
        } else {
            $user = $this->input->post('username');
            $password = $this->input->post('password');
            $data = $this->db_model->select_multi("id, name, password, business_name, last_login, address, status", 'franchisee', array('username' => $user));
            if ($data->status !== "Active") {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Login is invalid or account is blocked. Your account status is ' . $data->status . '.</div>');
                redirect(site_url('site/franchisee'));
            }
            if (password_verify($password, $data->password) && trim($password) !== "") {
                session_unset();
                $session = md5($user . time());
                $this->session->set_userdata(array(
                    'fran_id' => $data->id,
                    'business_name' => $data->business_name,
                    'name' => $data->name,
                    'last_login' => $data->last_login,
                    'session' => $session,
                ));
                $data2 = array(
                    'last_login_ip' => $this->input->ip_address(),
                    'last_login' => date('Y-m-d H:i:s'),
                    'session' => $session,
                );
                $this->db_model->update($data2, 'franchisee', array('id' => $data->id));
                redirect(site_url('franchisee'));
            } else {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid Username or Password.</div>');
                redirect(site_url('site/franchisee'));
            }
        }
    }

    public function franchisee_reset()
    {
        $user = trim($this->input->post('userid'));
        $phone = trim($this->input->post('phone'));
        $data = $this->db_model->select_multi("id, name, password, phone, email", 'franchisee', array('username' => $user));

        if ((trim(config_item('smtp_host')) !== "") || (config_item('sms_on_join') == "Yes")) {
            if ($phone == $data->phone){
                session_unset();
                $session = md5($user . time());
                $randompassword=$this->common_model->randomPassword();
                $password = password_hash($randompassword, PASSWORD_DEFAULT);
                $data2 = array(
                      'password' => $password,
                      'last_login_ip' => $this->input->ip_address(),
                      'last_login' => time(),
                      'session' => $session,
                  );
                $this->db_model->update($data2, 'franchisee', array('id' => $data->id));

                //debug_log($this->db->last_query());

                $sub = "Login Password Reset";
                $msg = "Hello " . $data->name . ", \nYou have requested for Login Password Reset. \n Temporary Login Password is: " . $randompassword . "\n Kindly update password soon after login \n\n Regards \n".config_item('company_name');
                if (trim(config_item('smtp_host')) !== "") {
                    $this->db_model->mail($data->email, $sub, $msg);
                }

                $sms = "Hello " . $data->name . ", \nYou have requested for Login Password Reset. \n The Password is: " . $randompassword . "\n Kindly update password soon after login \n\n Regards \n".config_item('company_name');
                $messvar="Ok";
                $phone="91".$phone;
                $this->common_model->sms($phone, urlencode($sms));

                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Success - Temporary password is sent to your registered Phone Number. <br> Update login password soon after login using Temporary Password</div>');
                redirect(site_url('site/franchisee'));

            } else {
              $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid details. Please Enter Valid Details.<br> 3 Consecutive Incorrect Attempts will block your account !!!</div>');
              redirect(site_url('site/franchisee'));
            }
        } else {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password couldnot reset at the moment. Please try later !!!</div>');
              redirect(site_url('site/franchisee'));
        }

    }

    public function forgotpw()
    {
        $layout['layout'] = "forgot.php";
        $this->load->view('theme/default/base', $layout);
    }

    public function staff()
    {
        $this->form_validation->set_rules('username', 'Staff Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('admin/staff');
        } else {
            $user = $this->input->post('username');
            $password = $this->input->post('password');
            $data = $this->db_model->select_multi("id, name, password, email, ip, last_login, designtion", 'staffs', array('username' => $user));

            if ($password == $data->password) {
                session_unset();
                $session = md5($user . time());
                $this->session->set_userdata(array(
                    'admin_id' => $data->id,
                    'email' => $data->email,
                    'name' => $data->name,
                    'ip' => $data->ip,
                    'last_login' => $data->last_login,
                    'session' => $session,
                    'staff' => TRUE,
                    'designation' => unserialize($this->db_model->select('des_permission', 'staff_designation', array('id' => $data->designtion))),
                ));

                $data2 = array(
                    'ip' => $this->input->ip_address(),
                    'last_login' => date('Y-m-d H:i:s'),
                    'session' => $session,
                );
                $this->db_model->update($data2, 'staffs', array('id' => $data->id));
                redirect(site_url('admin'));
            } else {
                $this->session->set_flashdata('admin_flash', '<div class="alert alert-danger">Invalid Username or Password.</div>');
                redirect(site_url('site/staff'));
            }
        }
    }

    public function admin()
    {
        parse_parameters($this->uri->segment(3));
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('admin/login');
        } else {
            $user = $this->input->post('username');
            $password = $this->input->post('password');
            $_lead_email_status = $this->session->lead_email_status;
            $this->load->model('Admin_Model');
            $res = $this->Admin_Model->admin_login($user,$password);
            parse_parameters($_lead_email_status);

            if($res['status']=="true"){
                redirect(site_url('admin'));
            } else {
                $this->session->set_flashdata('admin_flash', '<div class="alert alert-danger">Invalid Username or Password.</div>');
                redirect(site_url('site/admin'));
            }
        }
    }

    public function vendor()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('vendor/login');
        } else {
            $user = $this->input->post('username');
            $password = $this->input->post('password');
            $data = $this->db_model->select_multi("id, name, password, email, ip, last_login", 'admin', array('username' => $user));

            if (password_verify($password, $data->password)) {
                session_unset();
                $session = md5($user . time());
                $this->session->set_userdata(array(
                    'admin_id' => $data->id,
                    'email' => $data->email,
                    'name' => $data->name,
                    'ip' => $data->ip,
                    'last_login' => $data->last_login,
                    'session' => $session,
                ));
                $data2 = array(
                    'ip' => $this->input->ip_address(),
                    'last_login' => time(),
                    'session' => $session,
                );
                $this->db_model->update($data2, 'admin', array('id' => $data->id));
                redirect(site_url('admin'));
            } else {
                $this->session->set_flashdata('admin_flash', '<div class="alert alert-danger">Invalid Username or Password.</div>');
                redirect(site_url('site/admin'));
            }
        }
    }

    public function admin_forget()
    {
        $this->load->view('admin/forget');
    }

    public function auto_login()
    {
        if (isset($this->session->_auto_user_id_)) {
            $data = $this->db_model->select_multi("id, name, password, email, last_login_ip, last_login", 'member', array('id' => $this->session->_auto_user_id_));
            $session = md5($this->session->_auto_user_id_ . time());
            $this->session->set_userdata(array(
                'user_id' => $data->id,
                'email' => $data->email,
                'name' => $data->name,
                'ip' => $data->last_login_ip,
                'last_login' => $data->last_login,
                'session' => $session,
            ));
            $data2 = array(
                'last_login_ip' => $this->input->ip_address(),
                'last_login' => time(),
                'session' => $session,
            );
            $this->db_model->update($data2, 'member', array('id' => $data->id));
            redirect(site_url('member'));


        } else {
            redirect(site_url('site/login'));
        }
    }

    public function _404()
    {

        $this->output->set_status_header('404');
        $this->load->view('404');//loading in custom error view
    }

    public function get_user_name()
    {
        //debug_log($this->uri->segment(3));
        if($this->uri->segment(3) >0){
            if(config_item('inactive_in_tree')=='Yes'){
                $name = $this->db_model->select('name', 'member', array('id' => $this->uri->segment(3), 'role !='=>'customer'));
            }else{
                $name = $this->db_model->select('name', 'member', array('id'=>$this->uri->segment(3), 'role !='=>'customer', 'status !='=>'Inactive'));
            }
            //print_r($this->db->last_query());
            
            $echo_text = $name == '' ? "User Details Not Available !! Please check the ID" : 'User Name: '.$name;

            echo $echo_text;    
        }
    }

    public function get_product_price()
    {
        if($this->uri->segment(3) >0){
            $price = $this->db_model->select('prod_price-(prod_price*discount/100)', 'product', array('id' => $this->uri->segment(3)));
            echo round($price,2);  
        }
    }

    public function check_epin()
    {   
        $joining_fee = $this->db_model->select('joining_fee', 'plans', array('id' => $this->uri->segment(4)));

        $epin = $this->db_model->select_multi('*', 'epin', array('epin' => $this->uri->segment(3)));;
        if((($epin->status == 'Used') || ($joining_fee != $epin->amount)) && ($this->uri->segment(3) != ''))
        {
            echo "Epin is not valid for the selected Plan";
        } else {
            
        }
         
    }

    
    /*
    public function capture_lead($type)
    {
        $cookie_name = 'GMLM_Lead_id';
        //unset($_COOKIE[$cookie_name]);
        //setcookie($cookie_name, "", time() - 3600);         
        if(!isset($_COOKIE[$cookie_name])) {
            $cookie_value = md5(time());
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); //
        }else{
            $cookie_value = $_COOKIE[$cookie_name];    
        }
        
        $check_record = $this->db->query("SELECT time,activity,dummy_text,node,secret FROM dummy where secret = '".$cookie_value."' or address = '".$_SERVER['REMOTE_ADDR']."'");
        
        if(!($check_record->num_rows()>0)){
            $data= array(
             'address' => $_SERVER['REMOTE_ADDR'],
             'node' => $this->db_model->ip_info($_SERVER['REMOTE_ADDR'],"Address"),
             'time' =>date('Y-m-d H:i:s'),
             'activity' => 'Visited '.$type.' Dashboard of '.$_SERVER['HTTP_HOST']. ' on : '. date('Y-m-d h:i A'),
             'secret' => $cookie_value
            );
            $this->db->insert('dummy',$data);            
        }
        else
        {
            $details = $check_record->result()[0];
            $secret = $details->secret;
            if((strtotime(date('Y-m-d H:i:s')) - strtotime($details->time))>3600){
                $data= array(
                 'time' => date('Y-m-d H:i:s'),
                 'activity' => $details->activity.'<br>Visited '.$type.' Dashboard of '.$_SERVER['HTTP_HOST']. ' on : '. date('Y-m-d h:i A')
                );
                $this->db->where('secret', $secret);
                $this->db->update('dummy',$data);      
            }

            if(strlen($this->input->post('phone'))>2){
                $data= array(
                 'dummy_text'=>$this->common_model->filter($this->input->post('name')),
                 'dummy_values' => '+'.$this->input->post('Countrycode').$this->common_model->filter($this->input->post('phone')),
                 'dummy_side' =>$this->common_model->filter($this->input->post('email')),
                 'time' =>date('Y-m-d H:i:s') 
                );
                $this->db->where('secret', $secret);
                $this->db->update('dummy',$data); 

                $name=$this->common_model->filter($this->input->post('name'));
                $email=$this->common_model->filter($this->input->post('email'));
                $userphone=$this->input->post('Countrycode').$this->common_model->filter($this->input->post('phone'));
                //$subject = $name.' ('.$userphone.')';
                $subject = 'Global MLM Software #1 Network Marketing Software';
                $headers = "From: ".$name." <".$email.">";
                $body = "From: $name\nE-Mail: $email\nPhone: +".$userphone."\nAddress: ".$details->node."\nMessage: ".$details->activity."\nWhatsapp : https://wa.me/".$userphone;

                $status = mail('globalmlmsoftware@gmail.com',$subject,$body,$headers);

                //$status = mail('',$subject,$body,$headers);
                //debug_log('Email Status '.$status);

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Thanks for sharing the details !!!</div>');

                if ($this->login->check_session() != FALSE) {
                    redirect(site_url('admin'));
                }elseif ($this->login->check_member() != false) {
                    redirect(site_url('member'));
                }else{
                    redirect(site_url('site/login'));
                }   
            }
        }

        echo $details->dummy_text;        
    }
    */

    public function add_cookie_api($secret)
    {
        $data = array(
            'secret'    => $secret,
        );
        
        $data_string = json_encode($data);

        $urls = in_array($_SERVER['REMOTE_ADDR'], array("localhost", "127.0.0.1", "::1")) ? array('http://localhost/gmlm/Api/add_cookie') : array('https://www.unilevel.globalmlmsolution.com/Api/add_cookie','https://www.binary.globalmlmsolution.com/Api/add_cookie','https://www.matrix.globalmlmsolution.com/Api/add_cookie'); 

        foreach ($urls as $url) {
            debug_log($url);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);  
            $server_output = curl_exec($curl);

            curl_close($curl);
            debug_log('Result from add_cookie_api post request for URL '.$url);
            debug_log($server_output);
        }

        $result=json_decode($server_output);

        return $result->secret;
    
    }

    public function add_cookie($secret)
    {
        $cookie_name = 'GMLM_Lead_id';
        setcookie($cookie_name, "", time() - 3600); 
        if(!isset($_COOKIE[$cookie_name])) {
            setcookie($cookie_name, $secret, time() + (86400 * 30), "/"); //
        }else{
            $secret = $_COOKIE[$cookie_name];    
        }
        debug_log($secret);
        return $secret;
    }

    public function resend_otp()
    {
        $secret = $this->add_cookie(md5(time()));

        $data = array(
            'dummy_text'    => $this->common_model->filter($this->input->post('dummy_text')),
            'dummy_values'    => $this->common_model->filter($this->input->post('dummy_values')),
            'dummy_side'    => $this->common_model->filter($this->input->post('dummy_side')),
            'secret'        => $secret,
            'ip_address'    => $_SERVER['REMOTE_ADDR'],
            'budget'        => $this->common_model->filter($this->input->post('budget')),
        );
        $data_string = json_encode($data);

        $url = in_array($_SERVER['REMOTE_ADDR'], array("localhost", "127.0.0.1", "::1")) ? 'http://localhost/gmlm/Api/resend_otp/' : 'https://www.unilevel.globalmlmsolution.com/Api/resend_otp'; 

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);  
        $result = curl_exec($curl);

        curl_close($curl);
        debug_log($result);
        echo $result;

    }

    public function otp_verify()
    {
        $secret = $this->add_cookie(md5(time()));

        $data = array(
            'dummy_otp'    => $this->common_model->filter($this->input->post('dummy_otp')),
            'secret'        => $secret,
            'ip_address'    => $_SERVER['REMOTE_ADDR'],
        );
        $data_string = json_encode($data);

        $url = in_array($_SERVER['REMOTE_ADDR'], array("localhost", "127.0.0.1", "::1")) ? 'http://localhost/gmlm/Api/otp_verify/' : 'https://www.unilevel.globalmlmsolution.com/Api/otp_verify'; 

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);  
        $result = curl_exec($curl);
        curl_close($curl);
        echo $result;

    }


    public function captureLead($type)
    {        
        $secret = $this->add_cookie(md5(time()));
        
        $country_code = $this->common_model->filter($this->input->post('dummy_country_code'));
        $dummy_values= $this->common_model->filter($this->input->post('dummy_values'));
        $data = array(
            'dummy_text'    => $this->common_model->filter($this->input->post('dummy_text')),
            'dummy_values'  => $country_code .= $dummy_values,
            'dummy_side'    => $this->common_model->filter($this->input->post('dummy_side')),
            'domain'        => $_SERVER['HTTP_HOST'],
            'ip_address'    => $_SERVER['REMOTE_ADDR'],
            'secret'        => $secret,
            'budget'        => $this->common_model->filter($this->input->post('budget')),
        );
        $data_string = json_encode($data);

        debug_log($data_string);

        $url = in_array($_SERVER['REMOTE_ADDR'], array("localhost", "127.0.0.1", "::1")) ? 'http://localhost/gmlm/Api/captureLead/'.$type : 'https://www.unilevel.globalmlmsolution.com/Api/captureLead/'.$type; 

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);  
        $result = curl_exec($curl);
        //debug_log('printing result value');
        //debug_log($result);
        //var_dump($result);
        curl_close($curl);
        
        if($this->input->post('dummy_text')!=''){
            if ($this->login->check_session() != FALSE) {
                redirect(site_url('admin'));
            }elseif ($this->login->check_member() != false) {
                redirect(site_url('member'));
            }else{
                echo $result;
            }
        }else{
            echo $result;
        }
    }
    
}
