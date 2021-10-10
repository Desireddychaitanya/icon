<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
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
        $this->load->model('downline_model');
        $this->load->model('earning');
        $this->load->model('plan_model');
        $this->load->model('registration_model');
    }

    public function login_member($id){

        $data     = $this->db_model->select_multi("*", 'member', array('id' => $id));
        $session = md5($user . time());
        $this->session->set_userdata(array(
                                         'user_id'    => $data->id,
                                         'email'      => $data->email,
                                         'name'       => $data->name,
                                         'ip'         => $data->last_login_ip,
                                         'last_login' => $data->last_login,
                                         'session'    => $session,
                                         'role'       => $data->role
                                     ));
        $data2 = array(
            'last_login_ip' => $this->input->ip_address(),
            'last_login'    => time(),
            'session'       => $session,
        );
        $this->db_model->update($data2, 'member', array('id' => $data->id));
        redirect(site_url('member'));
    }

    public function view_members()
    {
        $order_by = config_item('member_order_by');
        if ($this->form_validation->run() == false) {   
            $this->db_model->select('plan_name', 'plans',array('id' =>"1"));
            $data['prod_name'] = $this->db->get('plans')->result_array();
            
            $this->db->select('*')->from('member')->where('status !=', 'Inactive')->order_by($order_by, 'desc');
            $this->db->limit($config['per_page'], $page);
            $data['members'] = $this->db->get()->result_array();
            $data['title']      = 'List Members';
            $data['breadcrumb'] = 'List Members';
            $data['layout']     = 'member/list_member.php';
            $data['parents']    = $this->db->get('product_categories')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        }
        else{
                $prod_name=$this->input->post('plan_name');
                $prod_id=$this->db_model->select('id', 'product', array('prod_name' => $prod_name)); 
                $config['per_page']   = 2;
                $config['total_rows'] = $this->db_model->count_all('member');
                $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $this->pagination->initialize($config);
                $this->db->select('*')->from('member')->where('signup_package',$prod_id)->order_by($order_by, 'ASC');
                $this->db->limit($config['per_page'], $page);
                $data['members'] = $this->db->get()->result_array();
                $this->load->view(config_item('admin_theme'), $data);
            }
    }
    

    public function view_level_members($userid, $level)
    {
        $userid = $this->uri->segment(3) != '' ? $this->uri->segment(3) : $userid;
        $level = $this->uri->segment(4) != '' ? $this->uri->segment(4) : $level;

        $secrets = $this->db_model->select('level'.$level, 'level_sponsor', array('userid' => $userid,));
        $secrets = substr(substr($secrets, 1),0,-1);

        $data['members'] = $this->db->query("Select * FROM member WHERE status = 'Active' and secret IN (" .$secrets .")")->result_array();
        //$data['members'] = $this->db->get()->result_array();
        $data['title']      = 'List Members';
        $data['breadcrumb'] = 'List Members';
        $data['layout']     = 'member/list_member.php';
        $this->load->view(config_item('admin_theme'), $data);
    }


    public function approve_kyc($id,$userid)
    {
        $config['base_url']   = site_url('users/approve_kyc');
        $config['per_page']   = 500000;
        $this->pagination->initialize($config);

        $data   = array(
                'status' => "completed",
            );
            $this->db->where('userid', $userid);
            $this->db->update('member_profile', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">KYC Approved!!!</div>');
        redirect('users/approved_kyc');
    }
    /*public function reject_kyc($id,$userid)
    {
        $config['base_url']   = site_url('users/reject_kyc');
        $config['per_page']   = 500000;
        $this->pagination->initialize($config);

        $data   = array(
                'status' => "incompleted",

            );
            $this->db->where('userid', $userid);
            $this->db->update('member_profile', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">KYC Rejected!!!</div>');
        redirect('users/rejected_kyc');
    }*/

    public function reject_kyc()
    {
        $config['base_url']   = site_url('users/reject_kyc');
        $config['per_page']   = 500000;
        $this->pagination->initialize($config);
        $userid=$this->input->post('userid');
        $comments=$this->input->post('comments');
        $data   = array(
                'status' => "incompleted",
                'comment_admin'=> $comments,
                );
            $this->db->where('userid',$userid);
            $this->db->update('member_profile', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">KYC Rejected!!!</div>');
        redirect('users/rejected_kyc');
    }

    public function approved_kyc()
    {
        $config['base_url']   = site_url('users/approved_kyc');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('member_profile',array('status'=>'completed'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')
                 ->from('member_profile')->where('status','completed')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['members'] = $this->db->get()->result_array();
        $data['title']      = 'Approved KYC';
        $data['breadcrumb'] = 'Approved KYC';
        $data['layout']     = 'member/approved_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function rejected_kyc()
    {
        $config['base_url']   = site_url('users/rejected_kyc');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('member_profile',array('status'=>'incompleted'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')
                 ->from('member_profile')->where('status','incompleted')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['members'] = $this->db->get()->result_array();
        $data['title']      = 'Rejected KYC';
        $data['breadcrumb'] = 'Rejected KYC';
        $data['layout']     = 'member/rejected_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
 
    public function pending_kyc()
    {
        $config['base_url']   = site_url('users/pending_kyc');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('member_profile', array('status'=>'Pending'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id,userid, tax_no, id_proof, add_proof, aadhar_no,status,cheque,bank_ac_no,bank_ifsc')
                 ->from('member_profile')->where('status','Pending')->order_by('id', 'ASC');
        $this->db->limit($config['per_page'], $page);
        $data['members'] = $this->db->get()->result_array();
        $data['title']      = 'Pending KYC';
        $data['breadcrumb'] = 'Pending KYC';
        $data['layout']     = 'member/pending_kyc.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function user_detail($id)
    {

        $data['data'] = $this->db_model->select_multi('*', 'member', array('id' => $id));

        $data['title']      = 'Member Detail';
        $data['breadcrumb'] = 'Member Detail';
        $data['layout']     = 'member/view_detail.php';
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
                $this->earning->reg_earning($userid, $this->db_model->select('sponsor', 'member', array('id' => $userid)), $this->db_model->select('signup_package', 'member', array('id' => $userid)));
            }

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Top-uped User account.</div>');
            redirect('users/topup-member');
        }
    }*/

    public function blocked_members()
    {
        $config['base_url']   = site_url('users/view_members');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('earning', array('status' => 'Block'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('member')->where(array('status' => 'Block'));

        $this->db->limit($config['per_page'], $page);

        $data['members'] = $this->db->get()->result_array();

        $data['title']      = 'Blocked Members';
        $data['breadcrumb'] = 'Blocked Members';
        $data['layout']     = 'member/blocked_member.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function latest_members()
    {
        $config['base_url']   = site_url('users/view_members');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('earning');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);
        $order_by = config_item('member_order_by');
        $this->db->select('*')->from('member')->where('status !=', 'Inactive')->order_by($order_by, 'DESC');

        $this->db->limit($config['per_page'], $page);

        $data['members'] = $this->db->get()->result_array();

        $data['title']      = 'Latest Members';
        $data['breadcrumb'] = 'Latest Members';
        $data['layout']     = 'member/latest_member.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function activate_members(){
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('member', array('status'=>'Inactive'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('member')->where('status', 'Inactive')->order_by('secret', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['members'] = $this->db->get()->result_array();

        $data['title']      = 'Activate Member';
        $data['breadcrumb'] = 'Activate Member';
        $data['layout']     = 'member/activate_member.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function activate($id){

        $md = $this->db_model->select_multi('*', 'member', array('id' => $id));
        $pd = $this->db_model->select_multi('*', 'plans', array('id' => $md->signup_package));

        if($md->status == 'Active'){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-info">Member Account is already Active !!</div>');
             redirect('users/activate_members');   
        }

        if(!$md->position>0){

          $status = $this->plan_model->get_leg_position($pd, $md->sponsor, $md->placement_leg, $md->position);
          debug_log($status);

          if($status ==400){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Error Activating the User</div>');
          } else {
            $position = $status['position'];
            $placement_leg = $status['leg'];
          }

          if(!$position>0){
            debug_log('Error getting the position');
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Error Activating the User</div>');
            redirect('users/activate_members');    
          }

          if($md->role != 'customer'){
                if(($position == config_item('top_id')) && ($this->db_model->count_all('member', array('position' => config_item('top_id'))) <6)){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                } else if($position != config_item('top_id')){
                    $data = array($placement_leg => $md->id,);
                    $this->db->where('id', $position);
                    $this->db->update('member', $data);    
                }
           }
          
          $data   = array(
              'position' => $position,
              'placement_leg' => $placement_leg,
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
              );
          $this->db->where('id', $md->id);
          $this->db->update('member', $data);

          $this->earning->add_deduction($md->id, 'admin', $pd->joining_fee, 'Account Activation', 'Account Activation',$md->signup_package, 'Activation by Admin', '');

          $md = $this->db_model->select_multi('*', 'member', array('id' => $id));

          $this->registration_model->Update_after_position($md, $pd);

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');    

        } else{
          $data   = array(
              'status' => 'Active',
              'activate_time' => date('Y-m-d H:i:s'),
              'topup'  => $pd->joining_fee,
              );
          $this->db->where('id', $id);
          $this->db->update('member', $data);

          $md = $this->db_model->select_multi('*', 'member', array('id' => $id));
          //$this->common_model->update_total_downline_id($md->id, $md->status);
          
          if ((config_item('joining_product') == 'Yes') && (config_item('make_join_product_entry') == "Yes") && ($md->status == 'Active'))
          {
            if($this->session->userdata('_id_upgrade_')!='Yes'){
                    $array = array(
                    'product_id' => 0,
                    'name'       => $pd->invoice_name,
                    'userid'     => $md->id,
                    'qty'        => 1,
                    'cost'       => $pd->joining_fee,
                    'date'       => date('Y-m-d H:i:s'),
                    'deliver_date'  => date('Y-m-d H:i:s'),
                    'status'     => "Completed",
                    'payment'    => "Registration Purchase",
                );

                $this->db->insert('product_sale', $array);

                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, $this->db->insert_id());    
            }
            $this->earning->credit_joining_commission($pd,$md);
          }
          else if((config_item('joining_product') == 'Yes') && (config_item('make_join_product_entry') == "No") && ($md->status == 'Active'))
          {
            if($this->session->userdata('_id_upgrade_')!='Yes'){
                $array = array(
                    'product_id' => 0,
                    'name'       => $pd->invoice_name,
                    'userid'     => $md->id,
                    'qty'        => 1,
                    'cost'       => $pd->joining_fee,
                    'date'       => date('Y-m-d H:i:s'),
                    'payment'    => "Registration Purchase",
                );

                $this->db->insert('product_sale', $array);
                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, $this->db->insert_id());
            }
          }
          else if($md->status == 'Active'){
              $this->earning->credit_joining_commission($pd,$md);
              if($this->session->userdata('_id_upgrade_')!='Yes'){
                $this->earning->add_invoice($md->id, $pd->id, $pd->joining_fee, 0);
              }
          }

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Successfully Activated User account.</div>');    
        }
        
        redirect('users/activate_members');

    }

    public function edit_user($id)
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('activate_time', 'Date of Join', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone No', 'trim|required');
        if ($this->form_validation->run() == TRUE) {

            if($this->input->post('date_of_birth') > date('Y-m-d')){
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>You can't enter future date as Date of Birth</div>");
            redirect(site_url('users/edit_user/'.$id));
            }

            $name      = $this->input->post('name');
            $email     = $this->input->post('email');
            $phone     = $this->input->post('phone');
            $activate_time = $this->input->post('activate_time');
            $password  = $this->input->post('password');
            $status    = $this->input->post('status');
            $array     = array(
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'activate_time' => $activate_time,
                'status'    => $status,
            );
            if (trim($password) !== "") {
                $array = $array + array('password' => password_hash($password, PASSWORD_DEFAULT));
            }
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('member', $array);

            $array = array(
                'address'          => $this->input->post('address'),
                'city'          => $this->input->post('city'),
                'state'          => $this->input->post('state'),
                'zip'          => $this->input->post('zip'),
                'date_of_birth'    => $this->input->post('date_of_birth'),
                'tax_no'           => $this->input->post('tax_no'),
                'aadhar_no'        => $this->input->post('aadhar_no'),
                'gstin'            => $this->input->post('gstin'),
                'googlepay_no'        => $this->input->post('googlepay_no'),
                'phonepay_no'        => $this->input->post('phonepay_no'),
                'upi_id'        => $this->input->post('upi_id'),
                'bank_name'        => $this->input->post('bank_name'),
                'bank_ac_no'       => $this->input->post('bank_ac_no'),
                'bank_ifsc'        => $this->input->post('bank_ifsc'),
                'bank_branch'      => $this->input->post('bank_branch'),
                'btc_address'      => $this->input->post('btc_address'),
                'nominee_name'     => $this->input->post('nominee_name'),
                'nominee_add'      => $this->input->post('nominee_add'),
                'nominee_relation' => $this->input->post('nominee_relation'),
            );
            $this->db->where('userid', $this->input->post('id'));
            $this->db->update('member_profile', $array);

            $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>User has been updated.</div>");
            redirect(site_url('users/view_members'));
        }
        else {
            $data['data']    = $this->db_model->select_multi('*', 'member', array('id' => $id));
            $data['profile'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $id));

            $data['title']      = 'Edit Member';
            $data['breadcrumb'] = 'Edit Member';
            $data['layout']     = 'member/edit_member.php';
            $this->load->view(config_item('admin_theme'), $data);
        }
    }

    public function search($p_id='')
    {
        $plan_id   = $this->input->post('plan_id') > 0 ? $this->input->post('plan_id') : '';
        $plan_id   = $p_id > 0 ? $p_id : $plan_id;
        $phone     = $this->input->post('phone');
        $email     = $this->input->post('email');
        $sponsor   = $this->common_model->filter($this->input->post('sponsor'));
        $userid    = $this->common_model->filter($this->input->post('userid'));
        $username=$this->common_model->filter($this->input->post('name'));
        $startdate = $this->input->post('startdate');
        $enddate   = $this->input->post('enddate');

        $order_by = config_item('free_registration')=='Yes' ? 'activate_time':'secret';

        if(trim($userid) !== ""){
            if(!$this->db_model->check_user($userid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('users/search_user');
            }
            $this->db->where('id', $userid);
        }else{
            $this->db->where('status !=', 'Inactive');
        }
        
        $this->db->select('*')->from('member as t1')->where('t1.id !=',config_item('top_id'))->order_by($order_by, 'DESC');
        
        if (trim($plan_id) !== "") {
            $this->db->join('(select userid, pid from level_details where e_status = 1 and pid ='.$plan_id.') as t2', 't1.id = t2.userid','INNER JOIN');
        }
        if (trim($phone) !== "") {
            $this->db->where('phone', $phone);
        }
        if (trim($username) !== "") {
            $this->db->where('name', $username);
        }
        if (trim($startdate) !== "") {
            $this->db->where('cast(activate_time as DATE) >=', $startdate);
        }
        if (trim($enddate) !== "") {
            $this->db->where('cast(activate_time as DATE) <=', $enddate);
        }
        if (trim($email) !== "") {
            $this->db->where('email', $email);
        }
        if (trim($sponsor) !== "") {
            $this->db->where('sponsor', $sponsor);
        }

        $data['members'] = $this->db->get()->result_array();

        $data['title']      = 'Search Results';
        $data['breadcrumb'] = 'Search Results';
        $data['layout']     = 'member/list_member.php';
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function search_user()
    {
        $this->db->select('*')->where(array('type !=' =>'Repurchase'))->order_by('id', 'ASC');
        $data['plans'] = $this->db->get('plans')->result_array();

        $data['title']      = 'Search Member';
        $data['breadcrumb'] = 'Search Member';
        $data['layout']     = 'member/search_user.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function remove_member($id)
    {
        $check_legs = $this->db_model->count_all('member', array('position' => $id));
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

    public function view_rank_achievers($rank)
    {
        $order_by = config_item('member_order_by');
        $rank = str_replace('%20',' ',$rank);
        $this->db->select('*')->where(array('rank' => $rank))->order_by($order_by,'ASC');
        $data['members'] = $this->db->get('member')->result_array();
        
        $data['title']      = 'Rank Achievers';
        $data['breadcrumb'] = 'Rank Achievers';
        $data['layout']     = 'member/list_member.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

     public function export_list_member()
    {
       if(isset($_POST["export_list_member"]))
        {
            //print_r("hello");die();
            $filename = 'member_details_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data date("Y-m-d h:i A",t1.time) as date
            $this->db->select('t1.id, t1.name, t1.sponsor,t1.signup_package,t1.email,t1.phone,t2.address,t2.city,t2.state,t2.zip,t2.country,t1.join_time,t1.status')
                    ->order_by('t1.id', 'ASC')
                    ->from('member as t1')
                    ->join('member_profile as t2', 't1.id = t2.userid');
            $data = $this->db->get()->result_array();
            //print_r($data[0]['time']);die();
            // file creation 
            $file = fopen('php://output', 'w');

            $header = array("Id","Name","Sponsor","signup_package","Email","Phone","Address","City","State","Zip","Country","Join Time","Status"); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        } else {
            redirect('member/list_member');
        }
    }



}
