<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_vendor extends MY_Controller
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
    }

    public function login_vendor($id){

        $data     = $this->db_model->select_multi("vendor_id, name, password, email, last_login_ip, last_login, status", 'vendor', array('vendor_id' => $id));
        $session = md5($user . time());
        $this->session->set_userdata(array(
                                         'vendor_id'    => $data->vendor_id,
                                         'email'      => $data->email,
                                         'name'       => $data->name,
                                         'ip'         => $data->last_login_ip,
                                         'last_login' => $data->last_login,
                                         'session'    => $session,
                                     ));
        $data2 = array(
            'last_login_ip' => $this->input->ip_address(),
            'last_login'    => time(),
            'session'       => $session,
        );
        $this->db_model->update($data2, 'vendor', array('vendor_id' => $data->vendor_id));
        redirect(site_url('vendor'));
   }

    public function view_vendors()
    {
        
        if ($this->form_validation->run() == false) {   
            
            $this->db->select('*')->from('vendor')->order_by('id', 'desc');
            $this->db->limit($config['per_page'], $page);
            $data['vendors'] = $this->db->get()->result_array();
            $data['title']      = 'List Vendors';
            $data['breadcrumb'] = 'List Vendors';
            $data['layout']     = 'product_vendor/list_vendor.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else{
                
                $config['per_page']   = 2;
                $config['total_rows'] = $this->db_model->count_all('vendor');
                $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $this->pagination->initialize($config);

                $this->db->select('*')
                         ->from('vendor')->order_by('id', 'ASC');

                $this->db->limit($config['per_page'], $page);
                $data['vendors'] = $this->db->get()->result_array();
                print_r($data['vendors']);die();
                $this->load->view(config_item('admin_theme'), $data);
            }
    }

    public function vendor_detail($id)
    {
       $data['data'] = $this->db_model->select_multi('id,vendor_id, name, email, phone,address, city,state,zipcode, last_login, last_login_ip, registration_time, registration_ip,photo,video', 'vendor', array('vendor_id' => $id));

        $data['title']      = 'Vendor Detail';
        $data['breadcrumb'] = 'Vendor Detail';
        $data['layout']     = 'product_vendor/view_detail.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
    public function edit_vendor($id)
    {

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('join_time', 'Date of Join', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone No', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        if ($this->form_validation->run() == TRUE) {//
            //print_r("if");die();
            $name      = $this->input->post('name');
            $email     = $this->input->post('email');
            $phone     = $this->input->post('phone');
            $address   = $this->input->post('address');
            $join_time = $this->input->post('join_time');
            $password  = $this->input->post('password');
            $status    = $this->input->post('status');
            $array     = array(
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'address'   => $address,
                'registration_time' => $join_time,
                'status'    => $status,
            );
            if (trim($password) !== "") {
                $array = $array + array('password' => password_hash($password, PASSWORD_DEFAULT));
            }
            //print_r($array);die();
            $this->db->where('vendor_id', $id);
            $this->db->update('vendor', $array);

            $array = array(
                'tax_no'           => $this->input->post('tax_no'),
                'date_of_birth'    => $this->input->post('birthdate'),
                'gstin'            => $this->input->post('gstin'),
                'aadhar_no'        => $this->input->post('aadhar_no'),
                'bank_name'        => $this->input->post('bank_name'),
                'bank_ac_no'       => $this->input->post('bank_ac_no'),
                'bank_ifsc'        => $this->input->post('bank_ifsc'),
                'bank_branch'      => $this->input->post('bank_branch'),
                'btc_address'      => $this->input->post('btc_address'),
                'nominee_name'     => $this->input->post('nominee_name'),
                'nominee_add'      => $this->input->post('nominee_add'),
                'nominee_relation' => $this->input->post('nominee_relation'),
            );
            $this->db->where('vendor_id', $id);
            $this->db->update('vendor_profile', $array);

            $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>Vendor has been updated.</div>");
            redirect(site_url('Manage_vendor/view_vendors'));
        }
        else {
            $data['data']    = $this->db_model->select_multi('vendor_id, name, email, phone, address, registration_time, status', 'vendor', array('vendor_id' => $id));
            $data['profile'] = $this->db_model->select_multi('*', 'vendor_profile', array('vendor_id' => $id));

            $data['title']      = 'Edit Vendor';
            $data['breadcrumb'] = 'Edit Vendor';
            $data['layout']     = 'product_vendor/edit_vendor.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
    }
    
    public function approve_kyc($id,$vendor_id)
    {
        $config['base_url']   = site_url('Manage_vendor/approve_kyc');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);

        $data   = array(
                'status' => "completed",
            );
            $this->db->where('vendor_id', $vendor_id);
            $this->db->update('vendor_profile', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">KYC Approved!!!</div>');
        redirect('Manage_vendor/approved_kyc');
    }
    public function reject_kyc($id,$vendor_id)
    {
        $config['base_url']   = site_url('Manage_vendor/reject_kyc');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);

        $data   = array(
                'status' => "incompleted",
            );
            $this->db->where('vendor_id', $vendor_id);
            $this->db->update('vendor_profile', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">KYC Rejected!!!</div>');
        redirect('Manage_vendor/rejected_kyc');
    }

    public function approved_kyc()
    {
        $config['base_url']   = site_url('manage_vendor/approved_kyc');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);

        $this->db->select('id,vendor_id, tax_no, id_proof, add_proof, aadhar_no,status')
                 ->from('vendor_profile')->where('status','completed')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['vendors'] = $this->db->get()->result_array();
        $data['title']      = 'Approved KYC';
        $data['breadcrumb'] = 'Approved KYC';
        $data['layout']     = 'product_vendor/approved_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function rejected_kyc()
    {
        $config['base_url']   = site_url('manage_vendor/rejected_kyc');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);

        $this->db->select('id,vendor_id, tax_no, id_proof, add_proof, aadhar_no,status')
                 ->from('vendor_profile')->where('status','incompleted')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['vendors'] = $this->db->get()->result_array();
        $data['title']      = 'Rejected KYC';
        $data['breadcrumb'] = 'Rejected KYC';
        $data['layout']     = 'product_vendor/rejected_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
 
    public function pending_kyc()
    {
        $config['base_url']   = site_url('Manage_vendor/pending_kyc');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('earning');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('vendor_id,id, tax_no, id_proof, add_proof, aadhar_no,status,cheque')
                 ->from('vendor_profile')->where('status','Pending')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['vendors'] = $this->db->get()->result_array();
        $data['title']      = 'Pending KYC';
        $data['breadcrumb'] = 'Pending KYC';
        $data['layout']     = 'product_vendor/pending_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);

    }
    /*public function topup_member(){

        $this->form_validation->set_rules('userid', 'User ID', 'trim|required');
        $this->form_validation->set_rules('amt', 'Top Up Amount', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Top Up Member';
            $data['breadcrumb'] = 'Top Up Member';
            $data['layout']     = 'member/topup.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
        else {
            $epin_value = $this->input->post('amt');
            $userid = $this->common_model->filter($this->input->post('userid'));
            $data   = array(
                'topup' => $epin_value,
            );
            $this->db->where('id', $userid);
            $this->db->update('member', $data);
            $this->load->model('earning');
            if (config_item('fix_income') == "Yes" && $epin_value > 0 && config_item('give_income_on_topup') == "Yes") {
                $this->earning->fix_income($userid, $this->db_model->select('sponsor', 'member', array('id' => $userid)), $epin_value);
            }
            else if (config_item('fix_income') !== "Yes" && $epin_value > 0 && config_item('give_income_on_topup') == "Yes") {
                $this->earning->credit_direct_referral_income($userid, $this->db_model->select('sponsor', 'member', array('id' => $userid)), $this->db_model->select('signup_package', 'member', array('id' => $userid)));
            }

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Top-uped User account.</div>');
            redirect('users/topup-member');
        }
    }*/

    

    public function latest_vendors()
    {
        $config['base_url']   = site_url('Manage_vendor/view_vendors');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('earning');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')
                 ->from('vendor')->order_by('id', 'DESC');

        $this->db->limit($config['per_page'], $page);

        $data['vendors'] = $this->db->get()->result_array();

        $data['title']      = 'Latest Vendors';
        $data['breadcrumb'] = 'Latest Vendors';
        $data['layout']     = 'product_vendor/latest_vendor.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function activate_members(){
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('member', array('status !='=>'Active'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('member')->where('status !=', 'Active')->order_by('join_time', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['members'] = $this->db->get()->result_array();

        $data['title']      = 'Activate Member';
        $data['breadcrumb'] = 'Activate Member';
        $data['layout']     = 'member/activate_member.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function activate($id){

        $member_detail = $this->db_model->select_multi('*', 'member', array('id' => $id));
        $plan_detail = $this->db_model->select_multi('*', 'plans', array('id' => $member_detail->signup_package));

        $data   = array(
                'status' => 'Active',
                'activate_time' => date('Y-m-d H:i:s'),
                'topup'  => $plan_detail->joining_fee,
        );
        $this->db->where('id', $id);
        $this->db->update('member', $data);

        $this->earning->credit_joining_commission($plan_detail,$member_detail);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');
        redirect('users/activate_members');

    }
    public function search()
    {
        $phone     = $this->input->post('phone');
        $email     = $this->input->post('email');
        $vendor_id    = $this->common_model->filter($this->input->post('vendor_id'));
        $name=$this->common_model->filter($this->input->post('name'));
        $this->db->select('*')->from('vendor')->order_by('name', 'ASC');
        if (trim($phone) !== "") {
            $this->db->where('phone', $phone);
        }
        if (trim($vendor_id) !== "") {
            $this->db->where('vendor_id', $vendor_id);
        }
        if (trim($name) !== "") {
            $this->db->where('name', $name);
        }
        
        if (trim($email) !== "") {
            $this->db->where('email', $email);
        }
        

        $data['vendors'] = $this->db->get()->result_array();

        $data['title']      = 'Search Results';
        $data['breadcrumb'] = 'Search Results';
        $data['layout']     = 'product_vendor/list_vendor.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function search_vendor()
    {
        $data['title']      = 'Search Vendor';
        $data['breadcrumb'] = 'Search Vendor';
        $data['layout']     = 'product_vendor/search_vendor.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function remove_vendor($id)
    {
        $check_legs = $this->db_model->count_all('vendor', array('position' => $id));
        if ($check_legs > 0 || trim($id) == config_item('top_id')) {

            $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>User Cannot be deleted as there are other users below this user.</div>");
            redirect(site_url('users/view_members'));
        }
        else {
            $position = $this->db_model->select_multi('position, placement_leg, my_img', 'member', array('id' => $id));
            $data     = array(
                $position->placement_leg => 0,
            );
            $this->db->where('id', $position->position);
            $this->db->update('member', $data);

            $this->db->where('id', $id);
            $this->db->delete('member');

            $this->db->where('userid', $id);
            $this->db->delete('member_profile');
            $this->db->where('userid', $id);
            $this->db->delete('wallet');

            unlink(FCPATH . "uploads/" . $position->my_img);
            $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>User has been deleted from database.</div>");
            redirect(site_url('users/view_members'));
        }

    }
}
