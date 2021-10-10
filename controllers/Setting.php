<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends MY_Controller
{
    /**
     * Check Valid Login or display login page.
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == false) {
            redirect(site_url('site/admin'));
        }
        $this->load->model('plan_model');
    }

    public function common_setting()
    {
        $this->form_validation->set_rules('smtp_host', 'SMTP Host', 'trim|required');
        $this->form_validation->set_rules('smtp_user', 'SMTP User', 'valid_email|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Common Setting';
            $data['breadcrumb'] = 'Common Setting';
            $data['layout']     = 'setting/common_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {

            $file = '<?php
// Email Setting Start

$config[\'protocol\'] = \'smtp\';
$config[\'smtp_host\'] = \'' . $this->input->post('smtp_host') . '\';
$config[\'smtp_user\'] = \'' . $this->input->post('smtp_user') . '\';
$config[\'smtp_pass\'] = \'' . $this->input->post('smtp_pass') . '\';
$config[\'smtp_port\'] = \'' . $this->input->post('smtp_port') . '\';
$config[\'smtp_crypto\'] = \'ssl\';
$config[\'mailtype\'] = \'html\';
            ';
            file_put_contents(APPPATH . 'config/email.php', $file);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
            redirect('setting/common_setting');

        }
    }

    public function dev()
    {
        $this->form_validation->set_rules('width', 'Width', 'required');
        if ($this->form_validation->run() == false) {

            if($this->db_model->select('description', 'settings', array('type' => 'dev_mode')) =='1'){
                $data['title']      = 'Developer Setting';
                $data['breadcrumb'] = 'Developer Setting';
                $data['layout']     = 'setting/dev_setting.php';
                $this->load->view(config_item('admin_theme'), $data);    
            }else{
                redirect('admin');
            }            
        }
        else {

            $leg = $this->input->post('width') == 2 ? 2 : 1;
            $width = $this->input->post('width');
            $enable_crowdfund = $this->input->post('enable_crowdfund');
            $level_income = $this->input->post('level_income');
            $level_income_sponsor_carry = $this->input->post('level_income_sponsor_carry');
            $target_income = $this->input->post('target_income');
            $autopool_registration = $this->input->post('autopool_registration');
            $show_leg_choose = $leg == 2 ? 'Yes' : 'No';
            $show_placement_id = $this->input->post('show_placement_id');
            $enable_product = $this->input->post('enable_product');
            $joining_product = $this->input->post('joining_product');
            $enable_repurchase = $this->input->post('enable_repurchase');
            $enable_pv = $this->input->post('enable_pv');
            $reg_default = $this->input->post('reg_default');
            $login_default = $reg_default == 'No' ? 'No' :$this->input->post('login_default');
            $enable_variation = $this->input->post('enable_variation');
            $crowdfund_type = $this->input->post('crowdfund_type');
            $enable_epin = $this->input->post('enable_epin');
            $enable_pg = $this->input->post('enable_pg');
            $free_registration = $this->input->post('free_registration') ? $this->input->post('free_registration') : 'No';
            $enable_kyc = $this->input->post('enable_kyc');
            $enable_invoice = $this->input->post('enable_invoice');
            $sep_tree = $this->input->post('sep_tree');
            $enable_crypto = $this->input->post('enable_crypto') ? $this->input->post('enable_crypto') : 'No';
            $enable_upi = $this->input->post('enable_upi');
            $enable_backup = $this->input->post('enable_backup') ? $this->input->post('enable_backup') : 'No';
            $enable_bank_deposit = $this->input->post('enable_bank_deposit');
            $login_theme = $this->input->post('login_theme') != '' ? $this->input->post('login_theme') :config_item('login_theme');
            $extend_kpi = $this->input->post('extend_kpi') ? $this->input->post('extend_kpi') : 'No';
            $inactive_in_tree = $this->input->post('inactive_in_tree') ? $this->input->post('inactive_in_tree') : 'No';
            $root_sponsor_unlimited = 'No';
            $id_upgrade = $this->input->post('id_upgrade');
            $server_type = $this->input->post('server_type');
            $sponsor_restriction = $this->input->post('sponsor_restriction');
            $inactive_sponsor = $this->input->post('inactive_sponsor')? $this->input->post('inactive_sponsor') : 'No';

            $width = $enable_crowdfund == "Yes" ? 3 : $width;

            if($enable_crowdfund=='Yes'){
                $array = array('gift_level'=> 1,);
                $this->db->where(array('id'=>config_item('top_id'),'gift_level <'=> 1));
                $this->db->update('member', $array);
            }


            $autopool_registration = $enable_crowdfund == 'Yes' ? "Yes" : $autopool_registration;
            $autopool_registration = $width > 2 ? $autopool_registration : 'No';

            $crowdfund_type = $enable_crowdfund == 'Yes' ? $crowdfund_type : 'Automatic';
            $free_registration = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'Yes' : $free_registration;

            $enable_epin = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'No' : $enable_epin;
            $enable_pg = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'No' : $enable_pg;
            $enable_kyc = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'No' : $enable_kyc;
            $enable_invoice = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'No' : $enable_invoice;
            $enable_bank_deposit = $crowdfund_type == 'Manual_Peer_to_Peer' ? 'No' : $enable_bank_deposit;            

            $level_income = $enable_crowdfund == "Yes" ? 'No' : $level_income;

            $show_leg_choose = $width != 2 ? 'No' : $show_leg_choose;
            $show_leg_choose = $width == 2 ? 'Yes' : $show_leg_choose;
            $show_leg_choose = $autopool_registration == 'Yes' ? 'No' : $show_leg_choose;
            $show_leg_choose = $enable_crowdfund =='Yes' ? 'No' : $show_leg_choose;

            $show_placement_id = $width != 2 ? 'No' : $show_placement_id; 
            $show_placement_id = $autopool_registration == 'Yes' ? 'No' :$show_placement_id;
            $show_placement_id = $enable_crowdfund =='Yes' ? 'No' : $show_placement_id;

            $enable_repurchase = $enable_crowdfund =='Yes' ? 'No' : $enable_repurchase;
            $enable_product    = $enable_crowdfund =='Yes' ? 'No' : $enable_product; 
            $joining_product   = $enable_crowdfund =='Yes' ? 'No' : $joining_product;

            $cur = $this->input->post('cur') == 'INR' ? 'fa fa-inr' : 'fa fa-usd';
            $mega_cur = $this->input->post('cur') == 'INR' ? 'mdi mdi-currency-inr' : 'mdi mdi-currency-usd';

            $sep_tree = $autopool_registration == 'Yes' ? 'Yes' : $sep_tree;
            $sep_tree = $enable_crowdfund == 'Yes' ? 'Yes' : $sep_tree;
            $sep_tree = $width == 2 ? 'No' : $sep_tree;

            $sponsor_different_plan = $sep_tree == 'No' ? 'Yes' : $this->input->post('sponsor_different_plan');

            if($enable_pg == 'Yes'){
                $enable_coinpayments = $this->input->post('enable_coinpayments');
                //$enable_crypto = $enable_coinpayments=='Yes' ? $enable_crypto : 'No';
                $coinpayment_payout = $enable_coinpayments=='Yes' ? $this->input->post('coinpayment_payout') : 'No';
                $enable_cashfree = $this->input->post('enable_cashfree');
                $cashfree_enable_payout = $enable_cashfree=='Yes' ? $this->input->post('cashfree_enable_payout') : 'No';
                $cashfree_enable_payout = $coinpayment_payout == 'Yes' ? 'No' : $cashfree_enable_payout;
                $enable_bankonnect = $this->input->post('enable_bankonnect');
            }else{
                $enable_coinpayments = 'No';
                $coinpayment_payout = 'No';
                $enable_cashfree = 'No';
                $cashfree_enable_payout = 'No';
                $enable_bankonnect = 'No';
            }

            if((config_item('enable_paypal') != 'Yes') && (config_item('enable_instamojo') != 'Yes')  && ($enable_coinpayments == 'No') && ($enable_bankonnect == 'No')){
                $enable_pg = 'No';
            }

            $make_join_product_entry = $joining_product == 'Yes' ? 'Yes' : 'No';
            $enable_product = $joining_product == 'Yes' ? 'Yes' : $enable_product;

            #$target_income = $enable_crowdfund == 'Yes' ? 'No' : $target_income;
            #$target_income = $autopool_registration == 'Yes' ? 'No' : $target_income;

            $enable_variation = $enable_product == 'Yes' ? $enable_variation : 'No';
            $enable_pv = $enable_product == 'Yes' ? $enable_pv : 'No';
            $level_income_sponsor_carry = $level_income == 'Yes' ? $level_income_sponsor_carry : 'No';
            $enable_repurchase = $enable_product =='Yes' ? $enable_repurchase:'No';
            $target_income = $enable_pv =='Yes' ? $target_income : 'No';

            #Update Placing Inactive Users in Tree based on other preferences
            $inactive_in_tree = $free_registration == 'Yes' ? $inactive_in_tree : 'No';
            $inactive_in_tree = $width >2 ? $inactive_in_tree : 'No';
            $inactive_in_tree = $autopool_registration == "Yes" ? 'No' : $inactive_in_tree;
            $inactive_in_tree = $enable_crowdfund == "Yes" ? 'No' : $inactive_in_tree;

            #Update login for member order setting
            $member_order_by = 'secret';
            $member_order_by = $free_registration == 'Yes' ? 'activate_time' : $member_order_by;
            $member_order_by = $inactive_in_tree == "Yes" ? 'secret' : $member_order_by;
            $extend_kpi = $enable_crowdfund == 'Yes' ? 'No' : $extend_kpi;

            $id_upgrade = $sep_tree == 'Yes' ? $id_upgrade : 'No';
            $member_order_by = $id_upgrade == 'Yes' ? 'activate_time' : $member_order_by;

            $reg_default = $server_type == 'Production' ? 'No' : $reg_default;
            $login_default = $server_type == 'Production' ? 'No' : $reg_default;


            $file = '<?php
defined(\'BASEPATH\') OR exit(\'Exit ?\');

$config[\'width\'] = \'' . $width . '\';
$config[\'leg\'] = \'' . $leg . '\';
$config[\'autopool_registration\'] = \'' . $autopool_registration . '\';
$config[\'show_leg_choose\'] = \'' . $show_leg_choose . '\';
$config[\'show_placement_id\'] = \'' . $show_placement_id . '\';
$config[\'enable_crowdfund\'] = \'' . $enable_crowdfund . '\';
$config[\'crowdfund_type\'] = \'' . $crowdfund_type . '\';
$config[\'level_income\'] = \'' . $level_income . '\';
$config[\'level_income_sponsor_carry\'] = \'' . $level_income_sponsor_carry . '\';
$config[\'enable_epin\'] = \'' . $enable_epin . '\';
$config[\'enable_bank_deposit\'] = \'' . $enable_bank_deposit . '\';
$config[\'diable_tree\'] = \'' . $this->input->post('diable_tree') . '\';
$config[\'cur\'] = \'' . $cur . '\';
$config[\'mega_cur\'] = \'' . $mega_cur . '\';
$config[\'disable_registration\'] = \'' . $this->input->post('disable_registration') . '\';
$config[\'free_registration\'] = \'' . $free_registration . '\';
$config[\'inactive_in_tree\'] = \'' . $inactive_in_tree . '\';
$config[\'member_order_by\'] = \'' . $member_order_by . '\';
$config[\'root_sponsor_unlimited\'] = \'' . $root_sponsor_unlimited . '\';
$config[\'enable_reward\'] = \'' . $this->input->post('enable_reward') . '\';
$config[\'enable_news\'] = \'' . $this->input->post('enable_news') . '\';
$config[\'sep_tree\'] = \'' . $sep_tree . '\';
$config[\'id_upgrade\'] = \'' . $id_upgrade . '\';
$config[\'sponsor_different_plan\'] = \'' . $sponsor_different_plan . '\';

$config[\'enable_upi\'] = \'' . $enable_upi . '\';
$config[\'enable_crypto\'] = \'' . $enable_crypto . '\';
$config[\'enable_pg\'] = \'' . $enable_pg . '\';
$config[\'enable_bankonnect\'] = \'' . $enable_bankonnect . '\';
$config[\'enable_coinpayments\'] = \'' . $enable_coinpayments . '\';
$config[\'coinpayment_payout\'] = \'' . $coinpayment_payout . '\';
$config[\'coinpayment_currency\'] = \'' . $this->input->post('coinpayment_currency') . '\';
$config[\'enable_cashfree\'] = \'' . $enable_cashfree . '\';
$config[\'cashfree_enable_payout\'] = \'' . $cashfree_enable_payout . '\';
$config[\'cashfree_currency\'] = \'' . $this->input->post('cashfree_currency') . '\';
$config[\'enable_kyc\'] = \'' . $enable_kyc . '\';
$config[\'enable_invoice\'] = \'' . $enable_invoice . '\';
$config[\'enable_product\'] = \'' . $enable_product . '\';
$config[\'enable_variation\'] = \'' . $enable_variation . '\';
$config[\'enable_repurchase\'] = \'' .$enable_repurchase. '\';
$config[\'joining_product\'] = \'' . $joining_product . '\';
$config[\'make_join_product_entry\'] = \'' . $make_join_product_entry . '\';
$config[\'enable_pv\'] = \'' . $enable_pv . '\';
$config[\'target_income\'] = \'' .$target_income. '\';
$config[\'server_type\'] = \'' .$server_type. '\';
$config[\'reg_default\'] = \'' .$reg_default. '\';
$config[\'login_default\'] = \'' .$login_default. '\';
$config[\'enable_backup\'] = \'' .$enable_backup. '\';
$config[\'login_theme\'] = \'' .$login_theme. '\';
$config[\'extend_kpi\'] = \'' .$extend_kpi. '\'; 
$config[\'sponsor_restriction\'] = \'' .$sponsor_restriction. '\'; 
$config[\'inactive_sponsor\'] = \'' .$inactive_sponsor. '\'; 
';
            file_put_contents(APPPATH . 'config/settings.php', $file);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
            redirect('setting/dev');

        }
    }

    public function front_end_setting()
    {
        $this->form_validation->set_rules('logo', 'logo', 'trim');
        if ($this->form_validation->run() == false) {

            $data['title']      = 'Front End Setting';
            $data['breadcrumb'] = 'Front End Setting';
            $data['layout']     = 'setting/front_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {



            $original = $this->db_model->select('password', 'admin', array('id' => $this->session->admin_id));

            if(password_verify(trim($this->input->post('password')), $original) == FALSE) {
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>Please enter correct password!!! </div>");
                redirect(site_url('setting/front_end_setting'));
            }

            //print_r("hello");die();
            if (trim($_FILES['logo']['name'] !== "")) {
                unlink(FCPATH . 'axxets/client/logo.png');
                move_uploaded_file($_FILES['logo']['tmp_name'], FCPATH . 'axxets/client/logo.png');
            }
            if (trim($_FILES['logo_light']['name'] !== "")) {
                unlink(FCPATH . '/axxets/client/logo_light.png');
                move_uploaded_file($_FILES['logo_light']['tmp_name'], FCPATH . 'axxets/client/logo_light.png');
            }
            if (trim($_FILES['logo_dark']['name'] !== "")) {
                unlink(FCPATH . '/axxets/client/logo_dark.png');
                move_uploaded_file($_FILES['logo_dark']['tmp_name'], FCPATH . 'axxets/client/logo_dark.png');
            }
            if (trim($_FILES['favicon']['name'] !== "")) {
                move_uploaded_file($_FILES['favicon']['tmp_name'], FCPATH . 'axxets/client/favicon.ico');
            }
            if (trim($_FILES['image1']['name'] !== "")) {
                move_uploaded_file($_FILES['image1']['tmp_name'], FCPATH . 'axxets/client/slider1.jpg');
            }
            if (trim($_FILES['image2']['name'] !== "")) {
                move_uploaded_file($_FILES['image2']['tmp_name'], FCPATH . 'axxets/client/slider2.jpg');
            }
            if (trim($_FILES['image3']['name'] !== "")) {
                move_uploaded_file($_FILES['image3']['tmp_name'], FCPATH . 'axxets/client/slider3.jpg');
            }
            if (trim($_FILES['image4']['name'] !== "")) {
                move_uploaded_file($_FILES['image4']['tmp_name'], FCPATH . 'axxets/client/slider4.jpg');
            }
            if (trim($_FILES['image5']['name'] !== "")) {
                move_uploaded_file($_FILES['image5']['tmp_name'], FCPATH . 'axxets/client/slider5.jpg');
            }
                        $file = '<?php
defined(\'BASEPATH\') OR exit(\'Exit ?\');

$config[\'company_name\'] = "' . trim($this->input->post('company_name')) . '";
$config[\'email\'] = "' . trim($this->input->post('email')) . '";
$config[\'phone\'] = "' . trim($this->input->post('phone')) . '";
$config[\'phone_2\'] = "' . trim($this->input->post('phone_2')) . '";
$config[\'company_address\'] = "' . trim($this->input->post('company_address')) . '";
$config[\'company_city\'] = "' . trim($this->input->post('company_city')) . '";
$config[\'company_state\'] = "' . trim($this->input->post('company_state')) . '";
$config[\'company_country\'] = "' . trim($this->input->post('company_country')) . '";
$config[\'company_zipcode\'] = "' . trim($this->input->post('company_zipcode')) . '";
$config[\'currency\'] = "' . trim($this->input->post('currency')) . '"; # Sitewide currency
$config[\'iso_currency\'] = "' . trim($this->input->post('iso_currency')) . '"; # ISO Code of currency
$config[\'company_pan\'] = "' . trim($this->input->post('company_pan')) . '";
$config[\'company_gst\'] = "' . trim($this->input->post('company_gst')) . '";
$config[\'bank_name\'] = "' . trim($this->input->post('bank_name')) . '";
$config[\'account_name\'] = "' . trim($this->input->post('account_name')) . '";
$config[\'account_no\'] = "' . trim($this->input->post('account_no')) . '";
$config[\'ifsc\'] = "' . trim($this->input->post('ifsc')) . '";
$config[\'branch\'] = "' . trim($this->input->post('branch')) . '";
$config[\'accounttype\'] = "' . trim($this->input->post('accounttype')) . '";
$config[\'company_upi\'] = "' . trim($this->input->post('company_upi')) . '";
$config[\'googlepay_no\'] = "' . trim($this->input->post('googlepay_no')) . '";
$config[\'phonepay_no\'] = "' . trim($this->input->post('phonepay_no')) . '";
$config[\'facebook\'] = "' . trim($this->input->post('facebook')) . '";
$config[\'twitter\'] = "' . trim($this->input->post('twitter')) . '";
$config[\'linkedin\'] = "' . trim($this->input->post('linkedin')) . '";
$config[\'about_us\'] = "' . trim(str_replace('"',"",$this->input->post('about_us'))) . '";
$config[\'google_map\'] = "' . trim($this->input->post('google_map')) . '";
';

            if (file_put_contents(APPPATH . 'config/company.php', $file)) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
                redirect('setting/front_end_setting');
            }

            

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Details Updated Successfully</div>');
                        redirect('setting/front_end_setting');
        }
    }

    public function advance_setting()
    {
        $this->form_validation->set_rules('dev_pass', 'Developer Password', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Advance Setting';
            $data['breadcrumb'] = 'Advance Setting';
            $data['layout']     = 'setting/advance_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            if (trim($this->input->post('dev_pass')) !== trim(config_item('dev_pass'))) {

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Developer Password is Wrong</div>');
                redirect('setting/advance_setting');
            }

            $file = '<?php
defined(\'BASEPATH\') OR exit(\'Exit ?\');

$config[\'enable_epin\'] = "' . $this->input->post('enable_epin') . '";
$config[\'make_join_product_entry\'] = "' . $this->input->post('make_join_product_entry') . '";
$config[\'disable_registration\'] = "' . $this->input->post('disable_registration') . '";
####################### MODULE SETTING ##############################
$config[\'enable_reward\'] = "' . $this->input->post('enable_reward') . '";
$config[\'enable_coupon\'] = "' . $this->input->post('enable_coupon') . '";
$config[\'enable_product\'] = "' . $this->input->post('enable_product') . '";
$config[\'enable_ecom\'] = "' . $this->input->post('enable_ecom') . '";
$config[\'enable_franchisee\'] = "' . $this->input->post('enable_franchisee') . '";
$config[\'enable_repurchase\'] = "' . $this->input->post('enable_repurchase') . '";
$config[\'enable_pg\'] = "' . $this->input->post('enable_pg') . '"; # Payment Gateway
$config[\'sms_on_join\'] = "' . $this->input->post('sms_on_join') . '";
$config[\'sms_api\'] = "' . $this->input->post('sms_api') . '";
## Format: https://apiurl.com?no={{phone}}&msg={{msg}}&other_parameters.
####################### DEFAULT SETTING ##############################
$config[\'leg\'] = "1"; # 1, 2, 3, 4, 5(eg: for binary plan leg is 2)
$config[\'top_id\'] = "1001";
$config[\'show_leg_choose\'] = "No"; ## Whether to show placement ID box at registration
$config[\'show_placement_id\'] = "No"; ## Whether to show select position option or not
$config[\'enable_gap_commission\'] = "No";
$config[\'ID_EXT\'] = "' . $this->input->post('id_ext') . '"; # ID Extension eg: DM1001
$config[\'show_join_product\'] = "' . $this->input->post('show_join_product') . '";
$config[\'autopool_registration\'] = "No";
$config[\'free_registration\'] = "' . $this->input->post('free_registration') . '";
$config[\'enable_topup\'] = "' . $this->input->post('enable_topup') . '";
$config[\'give_income_on_topup\'] = "' . $this->input->post('give_income_on_topup') . '";
$config[\'fix_income\'] = "' . $this->input->post('fix_income') . '";
$config[\'enable_ad_incm\'] = "' . $this->input->post('enable_ad_incm') . '";
$config[\'enable_survey\'] = "' . $this->input->post('enable_survey') . '";
$config[\'enable_recharge\'] = "' . $this->input->post('enable_recharge') . '";
$config[\'enable_help_plan\'] = "' . $this->input->post('enable_help_plan') . '";
$config[\'enable_investment\'] = "' . $this->input->post('enable_investment') . '"; 
## This will convert existing software to a investment plan software and will turn off many features.
$config[\'investment_mode\'] = "' . $this->input->post('investment_mode') . '"; ## AUTO, EPIN, MANUAL
$config[\'email\'] = "' . $this->input->post('email') . '";
                       $config[\'phone\'] = "' . $this->input->post('phone') . '";
                       $config[\'facebook\'] = "' . $this->input->post('facebook') . '";
                       $config[\'twitter\'] = "' . $this->input->post('twitter') . '";
                       $config[\'linkedin\'] = "' . $this->input->post('linkedin') . '";
';

            if (file_put_contents(APPPATH . 'config/global.php', $file)) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
                redirect('setting/advance_setting');
            }

        }
    }

    public function payout_setting()
    {
        $this->form_validation->set_rules('payout_tax', 'Payout Tax', 'trim|required');
        //$this->form_validation->set_rules('min_withdraw', 'Minimum Withdraw Amount', 'greater_than[0]|required');
        if ($this->form_validation->run() == false) {
          $this->db->select('*');
          $data['result']     = $this->db->get('payout')->result();

          $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
          $data['plans'] = $this->db->get('plans')->result_array();

          $data['title']      = 'Payout Setting';
          $data['breadcrumb'] = 'Payout Setting';
          $data['layout']     = 'setting/payout_setting.php';
          $this->load->view(config_item('admin_theme'), $data);
        }
        else {

          if($this->db_model->select('id', 'payout', array('plan_id' => $this->input->post('plan_id'))) >0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">There is already a Payout configured for the Plan ID '.$this->input->post('plan_id').' .Either delete / update the existing </div>');
            redirect('setting/payout_setting');
          }

           $payout_tax = $this->input->post('payout_tax') >0 ? $this->input->post('payout_tax') : 0;

           $array = array(
          'plan_id'           => $this->input->post('plan_id'),
          'payout_tax'        => $payout_tax,
          'no_pan_payout_tax' => $this->input->post('no_pan_payout_tax') >0 ? $this->input->post('no_pan_payout_tax') : $payout_tax,
          'admin_charge'      => $this->input->post('admin_charge') >0 ? $this->input->post('admin_charge') : 0,
          'admin_charge_type'      => $this->input->post('admin_charge_type'),
          'user_withdraw'     => $this->input->post('user_withdraw') != '' ? $this->input->post('user_withdraw') : 'Yes',
          'min_withdraw'      => $this->input->post('min_withdraw') >0 ? $this->input->post('min_withdraw') : 1,
          'daily_capping'     => $this->input->post('daily_capping') >0 ? $this->input->post('daily_capping') : 1000,
          'min_sponsor'       => $this->input->post('min_sponsor') >0 ? $this->input->post('min_sponsor') : 0,
          'repurchase_deduct' => $this->input->post('repurchase_deduct') >0 ? $this->input->post('repurchase_deduct') : 0,
          'fund_transfer'   => $this->input->post('fund_transfer') != '' ? $this->input->post('fund_transfer') : 'No',
          'user_epin'         => $this->input->post('user_epin') != '' ? $this->input->post('user_epin') : 'Yes',
          'user_epin_charge'  => $this->input->post('user_epin_charge') >0 ? $this->input->post('user_epin_charge') : 0,
          'user_epin_cashback' => $this->input->post('user_epin') == 'Yes' ? $this->input->post('user_epin_cashback') : '',
          'user_epin_plus' => $this->input->post('user_epin') == 'Yes' ? $this->input->post('user_epin_plus') : '',
          'payout_frequency'  => $this->input->post('payout_frequency') >0 ? $this->input->post('payout_frequency') : 0,
          'payout_start'      => $this->input->post('payout_start') != '' ? $this->input->post('payout_start') : '00:00:00',
          'payout_end'      => $this->input->post('payout_end') != '' ? $this->input->post('payout_end') : '24:00:00',
          );

          $this->db->insert('payout', $array);

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
          redirect('setting/payout_setting');

        }
    }

    public function edit_payout_setting($id)
    {
        $this->form_validation->set_rules('payout_tax', 'Payout Tax', 'trim|required');
        
        if ($this->form_validation->run() == false) {
          $data['result']     = $this->db_model->select_multi('*', 'payout', array('id' => $id,));

          $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
          $data['plans'] = $this->db->get('plans')->result_array();

          $data['title']      = 'Payout Setting';
          $data['breadcrumb'] = 'Payout Setting';
          $data['layout']     = 'setting/edit_payout_setting.php';
          $this->load->view(config_item('admin_theme'), $data);
        }
        else {

           $payout_tax = $this->input->post('payout_tax') >0 ? $this->input->post('payout_tax') : 0;

           $array = array(
          'payout_tax'        => $payout_tax,
          'no_pan_payout_tax' => $this->input->post('no_pan_payout_tax') >0 ? $this->input->post('no_pan_payout_tax') : $payout_tax,
          'admin_charge'      => $this->input->post('admin_charge') >0 ? $this->input->post('admin_charge') : 0,
          'admin_charge_type'      => $this->input->post('admin_charge_type'),
          'user_withdraw'     => $this->input->post('user_withdraw') != '' ? $this->input->post('user_withdraw') : 'Yes',
          'min_withdraw'      => $this->input->post('min_withdraw') >0 ? $this->input->post('min_withdraw') : 1,
          'daily_capping'     => $this->input->post('daily_capping') >0 ? $this->input->post('daily_capping') : 1000,
          'min_sponsor'       => $this->input->post('min_sponsor') >0 ? $this->input->post('min_sponsor') : 0,
          'repurchase_deduct' => $this->input->post('repurchase_deduct') >0 ? $this->input->post('repurchase_deduct') : 0,
          'fund_transfer'   => $this->input->post('fund_transfer') != '' ? $this->input->post('fund_transfer') : 'No',
          'user_epin'         => $this->input->post('user_epin') != '' ? $this->input->post('user_epin') : 'Yes',
          'user_epin_charge'  => $this->input->post('user_epin_charge') >0 ? $this->input->post('user_epin_charge') : 0,
          'user_epin_cashback' => $this->input->post('user_epin') == 'Yes' ? $this->input->post('user_epin_cashback') : '',
          'user_epin_plus'    => $this->input->post('user_epin') == 'Yes' ? $this->input->post('user_epin_plus') : '',
          'payout_frequency'  => $this->input->post('payout_frequency') >0 ? $this->input->post('payout_frequency') : 0,
          'payout_start'      => $this->input->post('payout_start') != '' ? $this->input->post('payout_start') : '00:00:00',
          'payout_end'      => $this->input->post('payout_end') != '' ? $this->input->post('payout_end') : '24:00:00',
          );

          $this->db->where('id', $this->input->post('id'));
          $this->db->update('payout', $array);

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Updated Successfully</div>');
          redirect('setting/payout_setting');

        }
    }

    public function remove_payout_setting($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payout');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Setting of Income Deleted Successfully</div>');
        redirect('setting/payout_setting');
    }

    public function reward_setting()
    {
        $this->form_validation->set_rules('reward_name', 'Reward Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->db->select('*');
            $data['result']     = $this->db->get('reward_setting')->result();
            $data['leg']        = $this->plan_model->create_leg();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Reward Setting';
            $data['breadcrumb'] = 'Reward Setting';
            $data['layout']     = 'setting/reward_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $type               = $this->input->post('reward_type');
            $reward_name     = $this->input->post('reward_name');
            $reward_duration = $this->input->post('reward_duration');
            $direct = $this->input->post('direct')>0 ? $this->input->post('direct') : 0;
            $based_on      = $this->input->post('based_on') ? $this->input->post('based_on') : 'Member';
            $plan_id =  $this->input->post('plan_id');

            $pro_name = $this->db_model->select('reward_name', 'reward_setting', array('reward_name' => $reward_name));
            if($pro_name){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Reward name already exists!! Please choose different name !!</div>');
            redirect('setting/reward_setting');
            }

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;
            $total_downline = $this->input->post('total_downline') ? $this->input->post('total_downline') : $a+$b+$c+$d+$e ;
            $mypv               = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $level_no              = $this->input->post('level_no') ? $this->input->post('level_no') : 1;
            $total_member_level = $this->input->post('total_member_level') ? $this->input->post('total_member_level') : 0;

            $array = array(
                'plan_id'         => $plan_id,
                'type'            => $type,
                'reward_name'     => $reward_name,
                'reward_duration' => $reward_duration,
                'based_on'        => $based_on,
                'A'               => $a,
                'B'               => $b,
                'C'               => $c,
                'D'               => $d,
                'E'               => $e,
                'mypv'            => $mypv,
                'total_downline'  => $total_downline,
                'direct'          => $direct,
                'level_no'           => $level_no,
                'total_member_level' => $total_member_level,
            );
            $this->db->insert('reward_setting', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Reward Settings Saved Successfully</div>');
            redirect('setting/reward-setting');

        }
    }

    public function remove_reward($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('reward_setting');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Reward Deleted Successfully</div>');
        redirect('setting/reward-setting');
    }

    public function edit_reward($id)
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('reward_name', 'Reward Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['result']     = $this->db_model->select_multi('*', 'reward_setting', array('id' => $id));
            $data['leg']        = $this->plan_model->create_leg();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Edit Reward';
            $data['breadcrumb'] = 'Edit Reward';
            $data['layout']     = 'setting/edit_reward.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $type               = $this->input->post('reward_type');
            $reward_name     = $this->input->post('reward_name');
            $reward_duration = $this->input->post('reward_duration');
            $direct          = $this->input->post('direct') > 0 ? $this->input->post('direct') : 0;
            $based_on      = $this->input->post('based_on') ? $this->input->post('based_on') : 'Member';
            

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;
            $total_downline = $this->input->post('total_downline') ? $this->input->post('total_downline') : $a+$b+$c+$d+$e ;
            $mypv               = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $level_no              = $this->input->post('level_no') ? $this->input->post('level_no') : 1;
            $total_member_level = $this->input->post('total_member_level') ? $this->input->post('total_member_level') : 0;

            $array = array(
                'type'            => $type,
                'reward_name'     => $reward_name,
                'reward_duration' => $reward_duration,
                'based_on'        => $based_on,
                'A'               => $a,
                'B'               => $b,
                'C'               => $c,
                'D'               => $d,
                'E'               => $e,
                'mypv'            => $mypv,
                'total_downline'  => $total_downline,
                'direct'          => $direct,
                'level_no'           => $level_no,
                'total_member_level' => $total_member_level,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('reward_setting', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Reward Updated Successfully</div>');
            redirect('setting/reward-setting');
        }
    }

    public function cms()
    {
        redirect('../wp-admin');
    }

    public function welcome_letter()
    {
        $this->form_validation->set_rules('welcome_letter', 'Welcome Letter Content', 'required|trim');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Edit Welcome Letter';
            $data['breadcrumb'] = 'Edit Welcome Letter';
            $data['layout']     = 'setting/edit_welcomeletter.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            file_put_contents(FCPATH . '/uploads/welcome_letter.txt', $_POST['welcome_letter']);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Welcome Letter Updated Successfully</div>');
            redirect('setting/welcome_letter');
        }
    }

    public function payment_gateway()
    {
        $this->form_validation->set_rules('paypal_email', 'All', 'trim');
        if ($this->form_validation->run() == false) {
            $this->load->config('pg');
            $data['title']      = 'Manage Payment Gateways';
            $data['breadcrumb'] = 'Manage Payment Gateways';
            $data['layout']     = 'setting/pg_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $file = '<?php
defined(\'BASEPATH\') OR exit(\'Can we play bubu together ?\');

$config[\'enable_paypal\'] = "' . $this->input->post('enable_paypal') . '";
$config[\'paypal_email\'] = "' . $this->input->post('paypal_email') . '";
$config[\'paypal_currency\'] = "' . $this->input->post('paypal_currency') . '";

$config[\'enable_instamojo\'] = "' . $this->input->post('enable_instamojo') . '";
$config[\'instamojo_api_key\'] = "' . $this->input->post('instamojo_api_key') . '";
$config[\'instamojo_auth\'] = "' . $this->input->post('instamojo_auth') . '";
$config[\'instamojo_salt\'] = "' . $this->input->post('instamojo_salt') . '";

$config[\'enable_payumoney\'] = "' . $this->input->post('enable_payumoney') . '";
$config[\'payumoney_key\'] = "' . $this->input->post('payumoney_key') . '";
$config[\'payumoney_salt\'] = "' . $this->input->post('payumoney_salt') . '";

$config[\'enable_block_io\'] = "' . $this->input->post('enable_block_io') . '";
$config[\'api_key\'] = "' . $this->input->post('api_key') . '";
$config[\'secret_pin\'] = "' . $this->input->post('secret_pin') . '";


$config[\'enable_coinpayments\'] = "' . $this->input->post('enable_coinpayments') . '";
$config[\'pub_key\'] = "' . $this->input->post('pub_key') . '";
$config[\'private_key\'] = "' . $this->input->post('private_key') . '";
$config[\'mrcnt_id\'] = "' . $this->input->post('mrcnt_id') . '";
$config[\'secret\'] = "' . $this->input->post('secret') . '";
';
            file_put_contents(APPPATH . 'config/pg.php', $file);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Payment Gateway Updated Successfully</div>');
            redirect('setting/payment_gateway');
        }
    }

    public function export()
    {
        $data['title']      = 'Export & Import Tools';
        $data['breadcrumb'] = 'Export & Import Tools';
        $data['layout']     = 'setting/export_import.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function export_db()
    {
        $data['title']      = 'Backup Database';
        $data['breadcrumb'] = 'Export & Backup Database';
        $data['layout']     = 'setting/export_to_sql.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function import_db()
    {
        if ($_FILES['file']['name'] == "") {
            $data['title']      = 'Import Database';
            $data['breadcrumb'] = 'Import & Restore Database';
            $data['layout']     = 'setting/import_from_sql.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $templine = '';
            move_uploaded_file($_FILES['file']['tmp_name'], FCPATH . 'sql_backup.txt');
            $lines = file(FCPATH . 'sql_backup.txt');
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '') continue;
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $this->db->query($templine);
                    $templine = '';
                }
            }
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Database Imported Successfully.</div>');
            redirect('setting/import_db');
        }
    }

    public function export_final()
    {
        $this->load->dbutil();
        $backup = $this->dbutil->backup();
        $this->load->helper('download');
        force_download(FCPATH . 'sql_backup.gz', $backup);
    }

    public function excel()
    {
        $this->form_validation->set_rules('table', 'Table', 'trim|required');
        if ($this->form_validation->run() == true) {
            $this->load->dbutil();
            $query = $this->db->query("SELECT * FROM " . $_POST['table']);
            $data  = $this->dbutil->csv_from_result($query);
            $this->load->helper('download');
            force_download(FCPATH . 'sql_backup.csv', $data);
        }
        else {
            $data['title']      = 'Export to Excel';
            $data['breadcrumb'] = 'Export Database to Excel';
            $data['layout']     = 'setting/export_to_excel.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
    }

    public function marketing()
    {
        $data['title']      = 'Marketing Toools';
        $data['breadcrumb'] = 'Marketing Toools';
        $data['layout']     = 'setting/marketing_tools.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function sms_marketing()
    {
        $this->form_validation->set_rules('msg', 'Message Content', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'SMS Marketing';
            $data['breadcrumb'] = 'SMS Marketing';
            $data['layout']     = 'setting/sms_marketing.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $phoneNO = $this->input->post('phone_no');
            if ($this->input->post('all') == "1") {
                $phoneNO = '';
                $this->db->select('phone');
                $res = $this->db->get('member')->result();
                foreach ($res as $phone) {
                    $phoneNO = $phoneNO . "," . $phone->phone;
                }
                $phoneNO = substr($phoneNO, 1);
            }
            $this->common_model->sms($phoneNO, $this->input->post('msg'));

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">SMS Sent Successfully.</div>');
            redirect('setting/sms_marketing');
        }
    }

    public function email_marketing()
    {
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required');
        $this->form_validation->set_rules('msg', 'Message Content', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Email Marketing';
            $data['breadcrumb'] = 'Email Marketing';
            $data['layout']     = 'setting/email_marketing.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {

            $email_id = $this->input->post('email_id');
            if ($this->input->post('all') == "1") {
                $email_id = '';
                $this->db->select('email');
                $res = $this->db->get('member')->result();
                foreach ($res as $email) {
                    $email_id = $email_id . "," . $email->email;
                }
                $email_id = substr($email_id, 1);
            }

            if (trim(config_item('smtp_host')) !== "") {
                $this->db_model->mail($email_id, $this->input->post('subject'), $this->input->post('msg'));
            }

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">e-Mail Sent Successfully.</div>');
            redirect('setting/email_marketing');
        }
    }

    public function main_screen()
    {
        $data['title']      = 'System Setting';
        $data['breadcrumb'] = 'System Setting';
        $data['layout']     = 'setting/main_screen.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    private function clear_member_data()
    {
            $this->db->truncate('admin_expense');
            $this->db->truncate('ads');
            $this->db->truncate('ad_user');
            $this->db->delete('binarydata', array('user_id !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE binarydata AUTO_INCREMENT 1");
            $this->db->delete('brands', array('brand_id !=' => '1'));
            $this->db->query("ALTER TABLE brands AUTO_INCREMENT 1");
            $this->db->truncate('coupon');
            $this->db->truncate('coupon_categories');
            $this->db->truncate('crowdfund_queue');
            $this->db->truncate('coupon');

            $this->db->query("DELETE from deductions where type = 'Registration Fee' and payment_mode = 'epin'");
            $this->db->query("ALTER TABLE deductions AUTO_INCREMENT 1");

            $this->db->truncate('donations');
            $this->db->truncate('donation_package');
            $this->db->truncate('earning');
            $this->db->truncate('earning_carry_forward');

            $this->db->query("UPDATE epin set status = 'Un-used' where remarks = 'Registration Fee'");
            $this->db->query("UPDATE epin set remarks = '' where remarks = 'Registration Fee'");
            
            $this->db->truncate('fix_income');
            $this->db->truncate('flexible_income');
            $this->db->truncate('franchisee');
            $this->db->truncate('franchisee_stock');
            $this->db->truncate('franchisee_stock_sale_bill');
            $this->db->truncate('gap_commission_setting');
            $this->db->truncate('investments');
            $this->db->truncate('investment_pack');
            $this->db->truncate('invoice');
            $this->db->truncate('insurance');
            $this->db->truncate('health_insurance');
            //$this->db->delete('level_details', array('userid !=' => config_item('top_id')));  
            $this->db->delete('member', array('id !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE member AUTO_INCREMENT 1");
            $this->db->delete('member_profile', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE member_profile AUTO_INCREMENT 1");
            $this->db->truncate('news');
            $this->db->delete('other_wallet', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE other_wallet AUTO_INCREMENT 1");
            $this->db->truncate('payout');
            $this->db->truncate('pending_wallet');
            $this->db->truncate('product_sale');
            $this->db->truncate('purchase');
            $this->db->truncate('pv');
            $this->db->truncate('rank_system');
            $this->db->truncate('recharge_entry');
            $this->db->truncate('rewards');
            $this->db->truncate('reward_setting');
            $this->db->truncate('salary');
            $this->db->delete('shipping_address', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE shipping_address AUTO_INCREMENT 1");
            $this->db->truncate('staffs');
            $this->db->truncate('staff_designation'); 
            $this->db->truncate('store_images');
            $this->db->truncate('survey');
            $this->db->truncate('survey_user');
            $this->db->truncate('tax_report');
            $this->db->truncate('ticket');
            $this->db->truncate('ticket_reply');
            
            $this->db->delete('wallet', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE wallet AUTO_INCREMENT 1");
            $this->db->truncate('withdraw_request');
            $this->db->truncate('vendor');
            $this->db->truncate('vendor_profile');

            $array = array(
                'left_leg'     => ',',
                'right_leg'    => ',',
                'left_paid'    => ',',
                'right_paid'   => ',',
                'left_unpaid'  => ',',
                'right_unpaid' => ',',
                'flushed'      => ',',
                'first_pair_paid'=> 0,
            );
            
            $this->db->where('user_id', config_item('top_id'));
            $this->db->update('binarydata', $array);

            $this->db->delete('level', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE level AUTO_INCREMENT 1");

            $array = array(
                'level1'     => 0,
                'level2'     => 0,
                'level3'     => 0,
                'level4'     => 0,
                'level5'     => 0,
                'level6'     => 0,
                'level7'     => 0,
                'level8'     => 0,
                'level9'     => 0,
                'level10'    => 0,
                'level11'    => 0,
                'level12'    => 0,
                'level13'    => 0,
                'level14'    => 0,
                'level15'    => 0,
                'level16'    => 0,
                'level17'    => 0,
                'level18'    => 0,
                'level19'    => 0,
                'level20'    => 0,
            );
            
            $this->db->where('userid', config_item('top_id'));
            $this->db->update('level', $array);

            $this->db->delete('level_details', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE level_details AUTO_INCREMENT 1");
            
            $array = array(
                'level1'     => ',',
                'level2'     => ',',
                'level3'     => ',',
                'level4'     => ',',
                'level5'     => ',',
                'level6'     => ',',
                'level7'     => ',',
                'level8'     => ',',
                'level9'     => ',',
                'level10'    => ',',
                'level11'    => ',',
                'level12'    => ',',
                'level13'    => ',',
                'level14'    => ',',
                'level15'    => ',',
                'level16'    => ',',
                'level17'    => ',',
                'level18'    => ',',
                'level19'    => ',',
                'level20'    => ',',
            );
            
            $this->db->where('userid', config_item('top_id'));
            $this->db->update('level_details', $array);

            $this->db->delete('level_sponsor', array('userid !=' => config_item('top_id')));
            $this->db->query("ALTER TABLE level_sponsor AUTO_INCREMENT 1");

            $this->db->where('userid', config_item('top_id'));
            $this->db->update('level_sponsor', $array);

            $this->db->query("UPDATE level_details SET position = NULL,total_downline = 0, total_active = 0, A = 0, B=0, total_a = 0, total_a_active=0, total_b = 0, total_b_active = 0, rank = 'Member',gift_level=0 where userid = ".config_item('top_id'));

            $array = array(
                'direct_income' => 0,
                'level_income'  => 0,
                'binary_income' => 0,
            );
            $this->db->update('fix_income', $array);

            $array = array(
                'balance' => 0,
            );
            $this->db->update('wallet', $array);
            $this->db->update('other_wallet', $array);

            $array = array(
                'sponsor'               => '',
                'position'              => '',
                'signup_package'        => 1,
                'plan_gid'              => 1,
                'usertime'              => time(),
                'A'                     => 0,
                'B'                     => 0,
                'C'                     => 0,
                'D'                     => 0,
                'E'                     => 0,
                'F'                     => 0,
                'G'                     => 0,
                'total_downline'        => 0,
                'total_active'          => 0,
                'total_a'               => 0,
                'total_a_active'        => 0,
                'total_b'               => 0,
                'total_b_active'        => 0,
                'paid_a'                => 0,
                'paid_b'                => 0,
                'mypv'                  => 0,
                'downline_pv'           => 0,
                'total_a_pv'            => 0,
                'total_b_pv'            => 0,
                'paid_a_matching_incm'  => 0,
                'paid_b_matching_incm'  => 0,
                'total_a_matching_incm' => 0,
                'total_b_matching_incm' => 0,
                'my_business'           => 0,
                'total_a_investment'    => 0,
                'total_b_investment'    => 0,
                'topup'                 => 2,
                'gift_level'            => 0,
                'rank'                  => 'Member',
                'new_id'                => '',
                'role'                  => 'Affiliate'
            );
            $this->db->update('member', $array);

            if(config_item('enable_crowdfund')=='Yes'){
                $array = array('gift_level'=> 1,);
                $this->db->update('member', $array);
                $this->db->update('level_details', $array);
            }


            ############### END CLEARNING #############################

            $dir         = FCPATH . "uploads";
            $leave_files = array('default.jpg', 'favicon.ico', 'logo.png', 'load.gif', 'welcome_letter.txt', 'kyc', 'placeholder.png', 'csv_template.csv', 'default_old.jpg', 'upi.png');

            foreach (glob("$dir/*") as $file) {
                if (!in_array(basename($file), $leave_files))
                    unlink($file);
            }

            $dir         = FCPATH . "uploads/kyc";
            
            foreach (glob("$dir/*") as $file) {
                if (!in_array(basename($file), $leave_files))
                    unlink($file);
            }

            $dir         = FCPATH . "uploads/profile";
            
            foreach (glob("$dir/*") as $file) {
                if (!in_array(basename($file), $leave_files))
                    unlink($file);
            }

            $fh = fopen(FCPATH .'.debug.log', 'w' );
            fclose($fh);

            $fh = fopen(FCPATH .'.debug_repo.log', 'w' );
            fclose($fh);

            $fh = fopen(FCPATH .'.wallet.log', 'w' );
            fclose($fh);

            $array = array(
                'userid' => $this->session->admin_id,
                'log'    => 'Database Cleared',
                'ip'     => $this->input->ip_address(),
                'time'   => date('Y-m-d H:i:s'),
            );
            $this->db->insert('logs', $array);

            if(config_item('enable_crowdfund')=='Yes'){
                $array = array('gift_level'=> 1,);
                $this->db->update('member', $array);
            }
    }

    private function clear_plan_data()
    {
        $this->db->delete('plans', array('type !='=>'Repurchase'));
        $this->db->query("ALTER TABLE plans AUTO_INCREMENT 1");
        $this->db->query("update product set sold_qty =0, qty=-1");
        $this->db->truncate('level_upgrade');
        $this->db->truncate('level_wise_income');
        $this->db->truncate('epin');
        $this->db->truncate('deductions');
        $this->db->truncate('transaction');
        $this->db->truncate('transfer_balance_records');
    }

    private function clear_product_data()
    {
        $this->db->truncate('plans');
        $this->db->truncate('product');
        $this->db->delete('product_categories', array('cat_id !=' => '1'));
        $this->db->query("ALTER TABLE product_categories AUTO_INCREMENT 1");
        $this->db->delete('product_parent_category', array('parent_cat_id !=' => '1'));
        $this->db->query("ALTER TABLE product_parent_category AUTO_INCREMENT 1");
        $this->db->delete('product_sub_category', array('sub_cat_id !=' => '1'));
        $this->db->query("ALTER TABLE product_sub_category AUTO_INCREMENT 1");
        $this->db->truncate('product_variant');

        $dir         = FCPATH . "uploads/products";
        $leave_files = array('default.jpg', 'favicon.ico', 'logo.png', 'load.gif', 'welcome_letter.txt', 'kyc', 'placeholder.png', 'csv_template.csv', 'default_old.jpg', 'upi.png');
            
        foreach (glob("$dir/*") as $file) {
            if (!in_array(basename($file), $leave_files))
                unlink($file);
        }

    }


    public function clear_database()
    {
        $this->form_validation->set_rules('password', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('agree', 'Agree Box', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Clear/Reset Database';
            $data['breadcrumb'] = 'Clear/Reset Database';
            $data['layout']     = 'setting/clear_database.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $original_pass = $this->db_model->select('password', 'admin', array('id' => $this->session->admin_id));
            if (trim($original_pass) == "" || trim($this->input->post('password')) !== trim(config_item('dev_pass'))) {

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Entered Password is wrong.</div>');
                redirect('setting/clear_database');
            }
            ####### Will Clear Database Values Here ###################

            $type = $this->input->post('type');

            if($type =='earnings_today'){
                $this->db->query("DELETE FROM earning WHERE cast(date as DATE) >= ".date('Y-m-d'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Today Earnings Deleted </div>');
            }
            elseif ($type == 'earnings_all') {
                $this->db->truncate('earning');
                $this->db->truncate('deductions');
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Today Earnings Deleted </div>');
            }
            elseif ($type == 'member') {
                $this->clear_member_data();
            }
            elseif ($type == 'registration') {
                $this->clear_member_data();
                $this->clear_plan_data();
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Details Successfully Cleared</div>');
            }
            elseif ($type == 'all') {
                $this->clear_member_data();
                $this->clear_plan_data();
                $this->clear_product_data();
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Database Cleared Successfully.</div>');
            }

            redirect('setting/clear_database');
        }
    }

    public function backup_tables()
    {
        $original_pass = $this->db_model->select('password', 'admin', array('id' => $this->session->admin_id));
        if (trim($original_pass) == "" || trim($this->input->post('password')) !== trim(config_item('dev_pass'))) {

            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Entered Password is wrong.</div>');
            redirect('setting/clear_database');
        }
        ####### Table Backup taken Here ###################

        $type = $this->input->post('type');

        if($type == 'member'){
            $this->db->truncate('member_temp');
            $this->db->query("insert into member_temp (select * from member order by id ASC)");

            $this->db->truncate('member_profile_temp');
            $this->db->query("insert into member_profile_temp (select * from member_profile)");

            /*$this->db->query("
                            drop table if exists member_profile_temp;
                            create table member_profile_temp 
                            as 
                            select * from member_profile;"
                            );*/
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Backup tables created Successfully !!!.</div>');
        }

        redirect('setting/clear_database');
    }


    public function custom_query()
    {
        $query = $this->input->post('query');

        if (strpos(strtolower($query), 'drop') === false) {
            $status = $this->db->query($query);
            wallet_log($this->db->last_query());
            if(config_item('reg_default')=='Yes'){
                if($status->num_rows()>0){
                    debug_log($status->result_array());
                }    
            }
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Query Executed Successfully !!!.</div>');
        }

        redirect('setting/clear_database');

    }

    public function rank_setting()
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('rank_name', 'Rank Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->db->select('*');
            $data['result']     = $this->db->get('rank_system')->result();
            $data['leg']        = $this->plan_model->create_leg();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Member Rank Setting';
            $data['breadcrumb'] = 'Member Rank Setting';
            $data['layout']     = 'setting/rank_setting.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $type          = $this->input->post('rank_type');
            $rank_name     = $this->input->post('rank_name');
            $rank_duration = $this->input->post('rank_duration');
            $based_on      = $this->input->post('based_on') ? $this->input->post('based_on') : 'Member';
            $plan_id =  $this->input->post('plan_id');

            $pro_name = $this->db_model->select('rank_name', 'rank_system', array('rank_name' => $rank_name));
            if($pro_name){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Rank name already exists!! Please choose different name !!</div>');
            redirect('setting/rank_setting');
            }

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;
            $total_downline  = $this->input->post('total_downline') ? $this->input->post('total_downline') : $a+$b+$c+$d+$e;
            $direct  = $this->input->post('direct')>0 ? $this->input->post('direct'):0;
            $mypv = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $level_no              = $this->input->post('level_no') ? $this->input->post('level_no') : 1;
            $total_member_level = $this->input->post('total_member_level') ? $this->input->post('total_member_level') : 0;
            $downline_rank = $this->input->post('downline_rank');

            $array = array(
                'plan_id'       => $plan_id,
                'type'          => $type,
                'rank_name'     => $rank_name,
                'rank_duration' => $rank_duration,
                'based_on'      => $based_on,
                'A'             => $a,
                'B'             => $b,
                'C'             => $c,
                'D'             => $d,
                'E'             => $e,
                'mypv'          => $mypv,
                'total_downline'=> $total_downline,
                'direct'        => $direct,
                'level_no'         => $level_no,
                'total_member_level' => $total_member_level,
                'downline_rank' => $downline_rank,
            );
            $this->db->insert('rank_system', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Member Rank Settings Saved Successfully</div>');
            redirect('setting/rank-setting');

        }
    }

    public function remove_rank($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('rank_system');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Rank Setting has been Deleted Successfully</div>');
        redirect('setting/rank-setting');
    }


    public function edit_rank($id)
    {
        $this->load->model('plan_model');
        $this->form_validation->set_rules('rank_name', 'Rank Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['result']     = $this->db_model->select_multi('*', 'rank_system', array('id' => $id));
            $data['leg']        = $this->plan_model->create_leg();

            $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $data['title']      = 'Update Member Rank Setting';
            $data['breadcrumb'] = 'Update Member Rank Setting';
            $data['layout']     = 'setting/edit_rank.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $type          = $this->input->post('rank_type');
            $rank_name     = $this->input->post('rank_name');
            $rank_duration = $this->input->post('rank_duration');
            $based_on      = $this->input->post('based_on') ? $this->input->post('based_on') : 'Member';

            $a = $this->input->post('A') ? $this->input->post('A') : 0;
            $b = $this->input->post('B') ? $this->input->post('B') : 0;
            $c = $this->input->post('C') ? $this->input->post('C') : 0;
            $d = $this->input->post('D') ? $this->input->post('D') : 0;
            $e = $this->input->post('E') ? $this->input->post('E') : 0;
            $total_downline  = $this->input->post('total_downline') ? $this->input->post('total_downline') : $a+$b+$c+$d+$e;
            $direct  = $this->input->post('direct')>0 ? $this->input->post('direct'):0;
            $mypv = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $level_no              = $this->input->post('level_no') ? $this->input->post('level_no') : 1;
            $total_member_level = $this->input->post('total_member_level') ? $this->input->post('total_member_level') : 0;
            $downline_rank = $this->input->post('downline_rank');

            $array = array(
                'type'          => $type,
                'rank_name'     => $rank_name,
                'rank_duration' => $rank_duration,
                'based_on'      => $based_on,
                'A'             => $a,
                'B'             => $b,
                'C'             => $c,
                'D'             => $d,
                'E'             => $e,
                'mypv'          => $mypv,
                'total_downline'=> $total_downline,
                'direct'        => $direct,
                'level_no'         => $level_no,
                'total_member_level' => $total_member_level,
                'downline_rank' => $downline_rank,
            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('rank_system', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Rank Settings Updated Successfully</div>');
            redirect('setting/rank-setting');

        }
    }


    //binary settings for inserting and displaying binary level and matching commission
     public function binary_setting()
    {

        $this->form_validation->set_rules('first_matching_income', 'First Matching Income', 'numeric|required');
        $this->form_validation->set_rules('second_matching_income', 'Second Matching Income', 'numeric|required');
        if ($this->form_validation->run() == false) {
           $data['result']     = $this->db->get('level_new')->result();
            $data['title']      = 'Binary Setting';
            $data['breadcrumb'] = 'Binary Setting';
            $data['layout']     = 'setting/binary_setting.php';
            $data['prod_name']=$this->db->get('product')->result_array();
            $data['products']=$this->db->get('product')->result();

            $this->load->view(config_item('admin_theme'), $data);


          //print_r($var['prod_name']);
           //$this->load->view('admin/setting/binary_setting', $data);
        }
        else {
            $package     = $this->input->post('package');
            if($this->input->post('product_name')){
            $product_name = $this->input->post('product_name');
            }
            else{
                $product_name="";
            }
            $first_matching_ratio = $this->input->post('first_matching_ratio');
            $second_matching_ratio = $this->input->post('second_matching_ratio');
            $first_matching_income = $this->input->post('first_matching_income');
            $second_matching_income = $this->input->post('second_matching_income');
            $levels = $this->input->post('new_level');
            $commission=$this->input->post('new_commission');
            //print_r($commission);
            //$level="1";
            //print_r($level);
            $details=$this->db->get('level_new')->result_array();
            /*foreach($details as $detail){
                $prod_name=$detail['product'];
            if( $prod_name!=$product_name && $product_name=="")
            {*/
            //foreach($levels as $level){
                $i=1;
                foreach($commission as $comm){
                //$commission=$this->input->post('new_commission')
                  $array = array(
                'commission' =>$comm,
                'first_pair_commission'  => $first_matching_income,
                'second_pair_commission' => $second_matching_income,
                'first_matching_ratio'   => $first_matching_ratio,
                'second_matching_ratio'  => $second_matching_ratio,
                'product'               =>  $product_name,
                'level'               =>    $i,

                );
                 $this->db->insert('level_new', $array);
                 $i = $i+1;
              }
          //}//level foreach

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Binary Settings Saved Successfully</div>');
            //redirect('Setting/binary_setting');
            redirect(site_url('Setting/binary_setting'));
            /*}
            else{

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Package you selected already exists!!</div>');
                redirect('Setting/binary_setting');
                }
            }//foreach*/
        }
    }

    //editing binary settings
    public function edit_binary_settings($id)
    {
        //echo $id;
        $this->form_validation->set_rules('first_matching_income', 'First Matching Income', 'numeric|required');
        $this->form_validation->set_rules('second_matching_income', 'Second Matching Income', 'numeric|required');
        if ($this->form_validation->run() == false) {
            $data['result']     = $this->db->get('level_new')->result();
            $data['title']      = 'Update Binary Setting';
            $data['breadcrumb'] = 'Update Binary Setting';
            $data['layout']     = 'setting/edit_binary_settings.php';
            $data['prod_name']=$this->db->get('product')->result_array();
            $data['products']=$this->db->get('product')->result();
            $data['product_name']=$this->db_model->select('prod_name', 'product', array('id' => $id));

            $this->db->select('level, commission')->where(array(
                'product' => $data['product_name'],
                ))->order_by('level', 'ASC');
            $data['level_detail'] = $this->db->get('level_new')->result_array();

            //$data['level_detail']=$this->db_model->select_multi('level, commission', 'level_new', array('product' =>$data['product_name'],));
            //print_r($data['level_detail']);

            //echo "hi";
            $this->load->view(config_item('admin_theme'), $data);

        }
        else {
            //echo "hello";
            $package     = $this->input->post('package');
            if($this->input->post('product_name')){
            $product_name = $this->input->post('product_name');
            }
            else{
                $product_name="";
            }
            $first_matching_ratio = $this->input->post('first_matching_ratio');
            $second_matching_ratio = $this->input->post('second_matching_ratio');
            $first_matching_income = $this->input->post('first_matching_income');
            $second_matching_income = $this->input->post('second_matching_income');
            $levels = $this->input->post('new_level');
            $commission=$this->input->post('new_commission');

            $details=$this->db->get('level_new')->result_array();
            //for checking whether level record for that package/product exists or not
            $this->db->select('level, commission')->where(array(
                'product' => $product_name,
               ))->order_by('level', 'ASC');
            $level['level_detail'] = $this->db->get('level_new')->result_array();

            //if the levels for that package already exists in the table then first delete the existing records and then insert
            if($level['level_detail']){
                //delete the levels that is already exists
                $this->db->where('product', $product_name);
                $this->db->delete('level_new');

                //now insert the freshly entered values into the database
                $i=1;
                foreach($commission as $comm){
                //$commission=$this->input->post('new_commission')
                  $array = array(
                'commission' =>$comm,
                'first_pair_commission'  => $first_matching_income,
                'second_pair_commission' => $second_matching_income,
                'first_matching_ratio'   => $first_matching_ratio,
                'second_matching_ratio'  => $second_matching_ratio,
                'product'               =>  $product_name,
                'level'               =>    $i,

                );
                 if(is_numeric($comm)){
                    $this->db->insert('level_new', $array);
                    $i = $i+1;
                  }
                  else{
                     $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Only Numbers are allowed!!</div>');
                     redirect('setting/edit_binary_settings');
                 }
              }
            }
            //if for the package/product the records are not present in the level_new table then directly insert the details into the table
            else{
                $i=1;
                foreach($commission as $comm){
                //$commission=$this->input->post('new_commission')
                  $array = array(
                'commission' =>$comm,
                'first_pair_commission'  => $first_matching_income,
                'second_pair_commission' => $second_matching_income,
                'first_matching_ratio'   => $first_matching_ratio,
                'second_matching_ratio'  => $second_matching_ratio,
                'product'               =>  $product_name,
                'level'               =>    $i,

                );
                  if(is_numeric($comm)){
                    $this->db->insert('level_new', $array);
                    $i = $i+1;
                  }
                  else{
                     $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Only Numbers are allowed</div>');
                     redirect('setting/edit_binary_settings');
                 }
              }
            }



          //}//level foreach

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Binary Settings Saved Successfully</div>');
            redirect(site_url('Setting/binary_setting'));

            //redirect('Setting/binary_setting');
            /*}
            else{

                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Package you selected already exists!!</div>');
                redirect('Setting/binary_setting');
                }
            }//foreach*/
        }
    }

    //remove binary settings from the database
    public function remove_binary_settings($prod_name)
    {
       // echo $prod_name;
        $this->db->where('product', $prod_name);
        $this->db->delete('level_new');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Setting of Binary Deleted Successfully</div>');
        redirect('setting/binary_setting');
    }


}
