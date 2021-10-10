<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller
{
    /**
     * Check Valid Login or display login page.
     */

    public function __construct()
    {
        parent::__construct();

        if ($this->login->check_session() == FALSE) {
            redirect(site_url('site/admin'));
        }
        if (config_item('install_date') !== FALSE) {
            if (strtotime(config_item('install_date')) + 864000 < time()) {
                redirect(site_url('cron/a_e'));
            }
        }
        $this->load->library('pagination');
        $this->load->library('user_model');

    }

    public function index()
    {
        $data['title']      = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';

        $data = $this->user_model->load_admin_data();

        $this->load->view(config_item('admin_theme'), $data);
    }

    public function logout()
    {
        $this->session->sess_destroy();
        if(isset($this->session->designation)){
         redirect(site_url('site/staff'));
        }
        else{
        redirect(site_url('site/admin'));
        }
    }

    public function verified()
    {
        parse_parameters('verified');
        redirect('admin');
    }

    public function update_theme()
    {
        debug_log($this->uri->segment(3));
        debug_log($this->uri->segment(4));
        $this->uri->segment(3) == 'stack' ? $this->config->set_item('stack_theme_id', $this->uri->segment(4)) : 1;
        debug_log(config_item('stack_theme_id'));
        redirect(site_url('admin'));
    }

    // CORE ADMIN PARTS HERE NOW ############################################################ STARTS :

    public function setting()
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email ID', 'valid_email');
        $this->form_validation->set_rules('password', 'Old Password', 'required');
        $this->form_validation->set_rules('securepass', 'Secure Password', 'required');
        if ($this->form_validation->run() == FALSE) {
            $data['result']     = $this->db_model->select_multi('name, email', 'admin', array('id' => $this->session->admin_id));
            $data['title']      = 'Account Setting';
            $data['breadcrumb'] = 'Account Setting';
            $data['layout']     = 'setting/account.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $name          = $this->input->post('name');
            $email         = $this->input->post('email');
            $old_password  = $this->input->post('password');
            $old_sec_password = $this->input->post('securepass');
            $new_password  = $this->input->post('newpass');
            $original = $this->db_model->select_multi('password, secure_password', 'admin', array('id' => $this->session->admin_id));
            
            if(strlen($new_password)<5){
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>Password lenght must be minimum 6 characters !!!</div>");
                redirect(site_url('admin/setting'));
            }

            if((password_verify($old_password, $original->password) == FALSE) || (password_verify($old_sec_password, $original->secure_password) == FALSE)) {
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>Please enter correct password!!! </div>");
                redirect(site_url('admin/setting'));
            }

            $array = array(
                'name'     => $name,
                'email'    => $email,
                'password' => password_hash($new_password, PASSWORD_DEFAULT),
            );

            $this->db->where('id', $this->session->admin_id);
            $this->db->update('admin', $array);
            $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>Detail updated successfully.</div>");
            redirect(site_url('admin/setting'));
        }
    }

    public function profile()
    {
        $this->form_validation->set_rules('securepass', 'Secure Password', 'trim|required');

        $data['data'] = $this->db_model->select_multi('*', 'admin', array('id' => $this->session->admin_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('*', 'admin', array('id' => $this->session->admin_id));
            $data['title'] = 'My Profile';
            $data['layout'] = 'profile/profile.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'admin', array('id' => $this->session->admin_id));

            if(password_verify($this->input->post('securepass'), $mypass) == true){
                
                $array = array(
                    'name' => $this->input->post('my_name'),
                    'phone' => $this->input->post('my_phone'),
                    'email' => $this->input->post('my_email'),
                );
                $this->db->where('id', $this->session->admin_id);
                $this->db->update('admin', $array);

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('admin/profile');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('admin/profile');
            }
        }
    }

    public function settings()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repass', 'Retype Password', 'trim|required|matches[newpass]');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Change Password';
            $data['layout'] = 'profile/acsetting.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {

            $mypass = $this->db_model->select('password', 'admin', array('id' => 1));

            if (password_verify($this->input->post('oldpass'), $mypass) == true) {

                $array = array(
                    'password' => password_hash($this->input->post('newpass'), PASSWORD_DEFAULT),
                );
                $this->db->where('id', 1);
                $this->db->update('admin', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Settings Saved Successfully.</div>');
                redirect('admin/settings');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                redirect('admin/settings');
            }
        }
    }

    public function add_expense()
    {
        $ename   = $this->input->post('ename');
        $eamount = $this->input->post('eamount');
        $edetail = $this->input->post('edetail');
        $edate   = $this->input->post('edate');

        $data = array(
            'expense_name' => $ename,
            'amount'       => $eamount,
            'detail'       => $edetail,
            'date'         => $edate,
        );

        $this->db->insert('admin_expense', $data);
        $this->session->set_flashdata("other_flash", "<div class='alert alert-success'>Expense Added</div>");
        redirect(site_url('admin#expense'));
    }

    public function add_news()
    {
        $this->form_validation->set_rules('location', 'Location', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Add News';
            $data['layout']     = 'ad/add_news.php';
            $data['breadcrumb'] = 'Add / Edit News';
            $this->load->view(config_item('admin_theme'), $data);
            
        }
        else {
            //$subject        = $this->input->post('subject');
            $content        = $this->input->post('content',FALSE);
            $location       = $this->input->post('location');
            //print_r($content);die();
            //$new_content=htmlentities($content);
            $new_content=html_escape($content);
            //print_r($new_content);die();
            $data = array(
                       //'subject'       => $subject,
                       'content'       => $content,
                       'subject'      => $location,
                       'date'          =>date('Y-m-d H:i:s'),
                );
                $this->db->insert('news', $data);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">News added Successfully.</div>');
                redirect('admin/add_news');
            }
    }

    public function manage_news()
    {
        $data['title']      = 'View / Edit News';
        $data['breadcrumb'] = 'View / Edit News';
        $data['layout']     = 'ad/manage_news.php';
        $this->db->select('id,subject,content,date')->order_by('date', 'DESC');
        $data['parents'] = $this->db->get('news')->result_array();
        $this->load->view(config_item('admin_theme'), $data);

    }
     /*public function edit_news($id)
    {
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required');
        if ($this->form_validation->run() == false) {
            $news      = $this->db_model->select_multi('*', 'news', array('id' => $id);
            $data['title']      = 'Edit News';
            $data['breadcrumb'] = 'Manage News';
            $data['layout']     = 'ad/edit_news.php';
            $data['data']       = $news;
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $subject        = $this->input->post('subject');
            $content        = $this->input->post('content');
            $data = array(
                'subject'       => $prod_name,
                'plan_id'         => $plan_id,
                );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('product', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Product Updated successfully.</div>');
            redirect('product/manage_products');

        }
    }*/

    public function view_news($id)
    {
        $news_data = $this->db_model->select_multi('*', 'news', array('id' => $id));
        $data['title']      = 'News Details';
        $data['breadcrumb'] = 'News';
        $data['layout']     = 'ad/view_news.php';
        $data['data']       = $news_data;
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function remove_news($id)
    {
        //$news = $this->db_model->select('image', 'store_images', array('id' => $id));
        $this->db->where('id', $id);
        $this->db->delete('news');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">News deleted Successfully.</div>');

        redirect('admin/manage_news');
    }


    public function generate_epin()
    {
        $this->form_validation->set_rules('amount', 'e-PIN Amount', 'trim|required');
        $this->form_validation->set_rules('userid', 'Issue to ID', 'trim|required');
        $this->form_validation->set_rules('number', 'Number of e-PINs', 'trim|required|max_length[3]');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Generate e-PIN';
            $data['breadcrumb'] = 'e-pin';
            $data['layout']     = 'epin/generate.php';
            $this->db->select('id, plan_name, joining_fee, gst')->where(array(
                'status' => 'Selling',
                'show_on_regform' => 'Yes',
            ))->order_by('plan_name', 'ASC');
            $data['products']   =$this->db->get('plans')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        } else {

            $userid = $this->common_model->filter($this->input->post('userid'));

            if(!$this->db_model->check_user($userid)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('admin/generate_epin');
            }

            $amount = $this->common_model->filter($this->input->post('amount'), 'float');
            $qty    = $this->common_model->filter($this->input->post('number'), 'number');

            $data = array();
            for ($i = 0; $i < $qty; $i++) {
                $rand = mt_rand(10000000, 99999999);
                $epin = $this->db_model->select("epin", "epin", array("epin" => $rand));
                while($epin==$rand){
                    $rand = $rand + 1;    
                    $epin = $this->db_model->select("epin", "epin", array("epin" => $rand));
                }
                $array = array(
                    'epin'          => $rand,
                    'amount'        => $amount,
                    'issue_to'      => $userid,
                    'generate_time' => date('Y-m-d H:i:s'),
                    'type'          => $this->input->post('type'),
                );
                array_push($data, $array);
            }
            $status = $this->db->insert_batch('epin', $data);
            //debug_log($this->db->last_query());
            //debug_log('Admin Epin Insert status ');
            //debug_log($status);

            if($status>0){
                $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>$qty e-PIN created successfully.</div>");
                if (trim(config_item('smtp_host')) !== "") {
                    $this->db_model->mail($this->db_model->select('email', 'member', array('id' => $userid)), 'e-PIN Issued', 'Dear Sir, <br/> e-PIN of Qty ' . $qty . ', has been issued to your account from us.<br/><br/>---<br/>Regards,<br/>' . config_item('company_name'));
                }
                redirect('admin/unused_epin');    
            }else{
                $this->session->set_flashdata("common_flash", "<div class='alert alert-danger'>There is some issue generating the ePins. Please try again later !!!</div>");
                redirect('admin/generate_epin');    
            }
            

        }

    }

    public function epin()
    {
        $type = $this->uri->segment(3);
        $id   = $this->uri->segment(4);
        if($this->db_model->select('status','epin', array('id'=>$id)) != 'Used'){
            switch ($type) {
                case $type == "edit":
                    redirect('admin/epin_edit/' . $id);
                    break;
                case $type == "remove":                   
                    $this->db->where('id', $id);
                    $this->db->delete('epin');  
                    debug_log($this->db->last_query());  
                    $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>e-PIN deleted successfully.</div>");
                    redirect($_SERVER['HTTP_REFERER']);
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function epin_edit()
    {
        $this->form_validation->set_rules('amount', 'e-PIN Amount', 'trim|required');
        $this->form_validation->set_rules('userid', 'User ID', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Edit e-PIN';
            $data['breadcrumb'] = 'Edit e-pin';
            $data['layout']     = 'epin/edit.php';
            $data['data']       = $this->db_model->select_multi('id, epin, amount, issue_to, used_by, status', 'epin', array('id' => $this->uri->segment(3)));
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $amount = $this->input->post('amount');
            $userid = $this->common_model->filter($this->input->post('userid'));
            $status = $this->input->post('status');
            $id     = $this->input->post('id');
            $used_by = $this->input->post('used_by');


            $data = array(
                'amount'   => $amount,
                'issue_to' => $userid,
                'used_by'  => $used_by,
                'status'   => $status,
                'used_time' => date('Y-m-d H:i:s'),
                'remarks'  => 'Used by Updted by Admin',
            );

            $this->db->where('id', $id);
            $this->db->update('epin', $data);
            $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>e-PIN Updated successfully.</div>");
            redirect('admin/epin_edit/' . $id);
        }

    }

    public function unused_epin()
    {

        $config['base_url']   = site_url('admin/unused_epin');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('epin', array('status' => 'Un-used'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, epin, amount, issue_to, generate_time, generate_time, type')->from('epin')
                 ->where('status', 'Un-used')->limit($config['per_page'], $page);

        $data['epin'] = $this->db->get()->result_array();

        $data['title']      = 'Unused e-PINs';
        $data['breadcrumb'] = 'Un-used e-pin';
        $data['layout']     = 'epin/unused.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function used_epin()
    {

        $config['base_url']   = site_url('admin/used_epin');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('epin', array('status' => 'Used'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('t1.id, t1.epin, t1.amount, t1.used_by, t1.used_time, t1.type, t2.name')->from('epin as t1')->where('status', 'Used')
                 ->join("(SELECT id, name FROM member) as t2", 'used_by = t2.id', 'LEFT')
                 ->limit($config['per_page'], $page);

        $data['epin'] = $this->db->get()->result_array();

        $data['title']      = 'Used e-PINs';
        $data['breadcrumb'] = 'Used e-pin';
        $data['layout']     = 'epin/used.php';
        $this->load->view(config_item('admin_theme'), $data);
    }


    public function search_epin()
    {
        $config['base_url'] = site_url('admin/search_epin');
        $config['per_page'] = 50000;

        if (isset($_POST['uid'])) {

            if(!$this->db_model->check_user($_POST['uid'])>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
                redirect('admin/search_epin');
            }
            $this->session->set_userdata('_uid', $this->common_model->filter($this->input->post('uid')));
        }
        if (isset($_POST['epin'])) {
            $this->session->set_userdata('_epin', $this->input->post('epin'));
        }

        if (!isset($_POST['uid']) && !isset($_POST['epin']) && $this->uri->segment(3) == "" && ($_SERVER['HTTP_REFERER'] !== $config['base_url'] . "/2")) {
            $this->session->unset_userdata('_epin');
            $this->session->unset_userdata('_uid');
        }

        $this->db->select('id')->from('epin');
        $this->session->userdata('_uid') ? $this->db->where('issue_to', $this->session->userdata('_uid')) : '';
        $this->session->userdata('_epin') ? $this->db->where('epin', $this->session->userdata('_epin')) : '';

        $config['total_rows'] = $this->db->count_all_results();

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('epin')->order_by('id','DESC')->limit($config['per_page'], $page);
        $this->session->userdata('_uid') ? $this->db->where('issue_to', $this->session->userdata('_uid')) : '';
        $this->session->userdata('_epin') ? $this->db->where('epin', $this->session->userdata('_epin')) : '';

        $data['epin'] = $this->db->get()->result_array();


        $data['title']      = 'Search e-PINs';
        $data['breadcrumb'] = 'Search e-pin';
        $data['layout']     = 'epin/search_epin.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function transfer_epin()
    {
        $this->form_validation->set_rules('amount', 'e-PIN Amount', 'trim|required');
        $this->form_validation->set_rules('from', 'From User ID', 'trim|required');
        $this->form_validation->set_rules('to', 'To User ID', 'trim|required');
        $this->form_validation->set_rules('to', 'To User ID', 'trim|required|differs[from]');
        $this->form_validation->set_rules('qty', 'Number of e-PINs', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Transfer e-PIN';
            $data['breadcrumb'] = 'Transfer e-pin';
            $data['layout']     = 'epin/transfer_epin.php';

            $this->db->select('id, plan_name, joining_fee, gst')->where(array(
                'status' => 'Selling',
                'show_on_regform' => 'Yes',
                'type !=' => 'Repurchase'
                ))->order_by('plan_name', 'ASC');
            $data['products']   =$this->db->get('plans')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
            
        } else {
            $amount = $this->common_model->filter($this->input->post('amount'), 'float');
            $from   = $this->common_model->filter($this->input->post('from'));
            $to     = $this->common_model->filter($this->input->post('to'));
            $qty    = $this->common_model->filter($this->input->post('qty'), 'number');

            if(!$this->db_model->check_user($from)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The Sender User ID does not exist !!!</div>') ;
                redirect('admin/transfer_epin');
            }

            if(!$this->db_model->check_user($to)>0){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The To User ID does not exist !!!</div>') ;
                redirect('admin/transfer_epin');
            }

            $avl_qty = $this->db_model->count_all('epin', array(
                'issue_to' => $from,
                'amount'   => $amount,
                'status'   => 'Un-used',
            ));
            if ($avl_qty < $qty) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID have only ' . $avl_qty . ' Un-used epin of ' . config_item('currency') . ' ' . $amount . '.</div>');
                redirect('admin/transfer_epin');
            } else {
                
                $level_sponsor_sql = "UPDATE `epin` SET `issue_to` = ".$to.", 
                    `transfer_by` = 
                        CASE 
                        WHEN CHAR_LENGTH(transfer_by) >0 THEN CONCAT(transfer_by,',',".$from.") 
                        ELSE CONCAT(',',".$from.")
                         END, 
                        `transfer_time` = '".date('Y-m-d H:i:s')."'
                        WHERE `issue_to` = ".$from." AND `amount` = ".$amount." AND `status` = 'Un-used' 
                    LIMIT ".$qty."";
                $this->db->query($level_sponsor_sql);

                debug_log($this->db->last_query());                

                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">' . $qty . ' e-PIN transferred from  ' . $this->input->post('from') . ' to ' . $this->input->post('to') . ' of ' . config_item('currency') . ' ' . $amount . '.</div>');
                redirect('admin/transfer_epin');
            }
        }
    }

    public function manage_cat()
    {
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

        if ($this->form_validation->run() !== FALSE) {

            $image =  'default.jpg';
            echo trim($_FILES['img']['name']);
            $parent_cat_id=$this->input->post('parent_category');

            $this->db->select('parent_cat_name');
            $this->db->where('parent_cat_id', $parent_cat_id);
            $q = $this->db->get('product_parent_category');
            $data = $q->result_array();
           
            $data = array(
                'cat_name'    => $this->input->post('category_name'),
                 'parent_cat' => $data[0]['parent_cat_name'],
                'parent_cat_id' =>$this->input->post('parent_category'),
                
            );
            $this->db->insert('product_categories', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Created Successfully.</div>');
            redirect('admin/manage_cat');
        } else {
            $config['base_url']   = site_url('admin/manage_cat');
            $config['per_page']   = 500000;
            $config['total_rows'] = $this->db_model->count_all('product_categories');
            $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $this->pagination->initialize($config);

            $this->db->select('cat_id, cat_name, parent_cat, image, description')->from('product_categories')
                     ->order_by('cat_name', 'DESC')->limit($config['per_page'], $page);

           $data['cat'] = $this->db->get()->result_array();
            //print_r($data['cat']); die();

            $this->db->select('parent_cat_id, parent_cat_name,brand_id');
            $data['parents'] = $this->db->get('product_parent_category')->result_array();
            $this->db->select('cat_id, cat_name,parent_cat');
            $data['category'] = $this->db->get('product_categories')->result_array();
            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category');
            $data['subcategory'] = $this->db->get('product_sub_category')->result_array();
            $this->db->select('brand_id, brand_name');
            $data['brand'] = $this->db->get('brands')->result_array();

            $data['title']      = 'Manage Product Categories';
            $data['breadcrumb'] = 'Product Categories';
            $data['layout']     = 'product/categories.php';
            $this->load->view(config_item('admin_theme'), $data);

        }
    }

     public function manage_sub_category()
    {
        $data['title']      = 'View / Edit Sub Category';
        $data['breadcrumb'] = 'View / Edit Sub Category';
        $data['layout']     = 'product/manage_sub_category.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('sub_cat_name', 'ASC');
        $data['sub_cat'] = $this->db->get('product_sub_category')->result_array();
        $this->load->view(config_item('admin_theme'), $data);
    }
    public function manage_category()
    {
        $data['title']      = 'View / Edit Sub Category';
        $data['breadcrumb'] = 'View / Edit Sub Category';
        $data['layout']     = 'product/manage_category.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('cat_name', 'ASC');
        $data['cat'] = $this->db->get('product_categories')->result_array();
        $this->load->view(config_item('admin_theme'), $data);
    }
    
    public function sub_category()
    {
        $this->form_validation->set_rules('subcategory_name', 'Subcategory Name', 'trim|required');

        if ($this->form_validation->run() !== FALSE) {
            $parent_category_names=$this->input->post('parent_category_names');
            
            $ret = explode('-', $parent_category_names);
            $category_name=$ret[0];
            $parent_cat_name=$ret[1];
            
            $this->db->select('cat_id');
            $this->db->where(array('cat_name' => $category_name,
                                   'parent_cat'=> $parent_cat_name));
            $q = $this->db->get('product_categories');
           $data = $q->result_array();

             //echo($data[0]['cat_id']);die();
            //print_r($cat_id);die();
             $data = array(
                'sub_cat_name'    => $this->input->post('subcategory_name'),
                'category'  => $category_name,
                'parent_category'  => $parent_cat_name,
                'cat_id'     =>$data[0]['cat_id'],
                
            );
            $this->db->insert('product_sub_category', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">SubCategory added Successfully.</div>');
            redirect('admin/manage_cat');
        } else {
            $config['base_url']   = site_url('admin/manage_cat');
            $config['per_page']   = 500000;
            $config['total_rows'] = $this->db_model->count_all('product_categories');
            $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $this->pagination->initialize($config);

            /*$this->db->select('id, cat_name, parent_cat, image, description')->from('product_categories')
                     ->order_by('cat_name', 'DESC')->limit($config['per_page'], $page);

            $data['cat'] = $this->db->get()->result_array();*/
            //print_r($data['cat']); die();

            $this->db->select('parent_cat_id, parent_cat_name');
            $data['parents'] = $this->db->get('product_parent_category')->result_array();
            $this->db->select('cat_id, cat_name,parent_cat');
            $data['category'] = $this->db->get('product_categories')->result_array();
            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category');
            $data['subcategory'] = $this->db->get('product_sub_category')->result_array();
            $this->db->select('brand_id, brand_name');
            $data['brand'] = $this->db->get('brands')->result_array();

            $data['title']      = 'Manage Product Categories';
            $data['breadcrumb'] = 'Product Categories';
            $data['layout']     = 'product/categories.php';
            $this->load->view(config_item('admin_theme'), $data);

        }
    }
    public function delete_brand($id)
    {
        $count = $this->db_model->count_all('product', array(
            'brand' => $id,
        ));
        $count_product_parent_cat = $this->db_model->count_all('product_parent_category', array(
            'brand_id' => $id,));
        if($count_product_parent_cat>0)
        {
         $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Brand Cannot be deleted as there are ' . $count_product_parent_cat . ' Parent Category/s under the brand</div>');
            redirect('product/add_brand');

        }
        elseif ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Brand Cannot be deleted as there are ' . $count . ' products with that Brand</div>');
            redirect('product/add_brand');
        }
        else 
        {
            
            $this->db->where('brand_id', $id);
            $this->db->delete('brands');
            
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Brand Deleted successfully.</div>');

            redirect('product/add_brand');
        }
    }

    public function edit_brand($id)
    {
        $this->form_validation->set_rules('brand_name', 'Brand name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->db->select('*');
            $this->db->where(array('brand_id' => $id,));
            $q = $this->db->get('brands');
            $data = $q->result_array();
            //print_r($data);die();
            //$brand       = $this->db_model->select_multi('*', 'brands', array('brand_id' => $id . $this->input->post('id')));
            $data['title']      = 'Edit Brand';
            $data['breadcrumb'] = 'Manage Brand';
            $data['layout']     = 'product/edit_brand.php';
            $data['data']       = $data;
            //print_r($data['data']);die();
            $this->load->view(config_item('admin_theme'), $data);
        } else 
         {
            $brand_name        = $this->input->post('brand_name');
            $image            = 'default.jpg';
            if (trim($_FILES['img']['name']) !== "") 
            {
              $this->load->library('upload');
              if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/add_brand');
                } 
               else 
               {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image = $image_data['file_name'];
               }
            }

                $data = array(
                       'brand_name'       => $brand_name,
                       //'brand_description'=> $description,
                       'brand_image'           => $image,
                );
                 $this->db->where('brand_id', $id);
                 $this->db->update('brands', $data);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Brand updated Successfully.</div>');
                redirect('product/add_brand');
         }
    }

     public function delete_sub_category($id)
    {
        $count = $this->db_model->count_all('product', array(
            'sub_category' => $id,
            
        ));
        if ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Sub Category Cannot be deleted as there are ' . $count . ' products with that sub category</div>');
            redirect('admin/manage_sub_category');
        } else {
            
            $this->db->where('sub_cat_id', $id);
            $this->db->delete('product_sub_category');
            
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Sub Category Deleted successfully.</div>');

            redirect('admin/manage_sub_category');
        }
    }

    public function delete_category($id)
    {
        $count = $this->db_model->count_all('product', array(
            'category' => $id,
            
        ));
        $count_subcat=$this->db_model->count_all('product_sub_category', array(
            'cat_id' => $id,));
        if($count_subcat>0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Category Cannot be deleted as there are ' . $count_subcat . ' Sub Category/s with that category</div>');
            redirect('admin/manage_category');
        }
        if ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Category Cannot be deleted as there are ' . $count . ' products with that category</div>');
            redirect('admin/manage_category');
        } else {
            
            $this->db->where('cat_id', $id);
            $this->db->delete('product_categories');
            
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Deleted successfully.</div>');

            redirect('admin/manage_category');
        }
    }
    //add flag
    public function add_flag()
    {
        $this->form_validation->set_rules('flag_name', 'Flag Name', 'trim|required');

        if ($this->form_validation->run() !== FALSE) {
            $flag_names=$this->input->post('flag_name');
            $data = array(
                'flag_name'    => $this->input->post('flag_name'),
                );
            $this->db->insert('flag', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Flag added Successfully.</div>');
            redirect('admin/manage_cat');
        } else {
            $config['base_url']   = site_url('admin/manage_cat');
            $config['per_page']   = 500000;
            $config['total_rows'] = $this->db_model->count_all('product_categories');
            $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $this->pagination->initialize($config);
            $this->db->select('parent_cat_id, parent_cat_name');
            $data['parents'] = $this->db->get('product_parent_category')->result_array();
            $this->db->select('cat_id, cat_name,parent_cat');
            $data['category'] = $this->db->get('product_categories')->result_array();
            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category');
            $data['subcategory'] = $this->db->get('product_sub_category')->result_array();
            $this->db->select('brand_id, brand_name');
            $data['brand'] = $this->db->get('brands')->result_array();

            $data['title']      = 'Manage Product Categories';
            $data['breadcrumb'] = 'Product Categories';
            $data['layout']     = 'product/categories.php';
            $this->load->view(config_item('admin_theme'), $data);

        }
    }

    public function category()
    {
        $type = $this->uri->segment(3);
        $id   = $this->uri->segment(4);

        switch ($type) {
            case $type == "edit":
                redirect('admin/category_edit/' . $id);
                break;
            case $type == "remove":
                $this->db->where('cat_id', $id);
                $this->db->delete('product_categories');
                $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>Category deleted successfully.</div>");
                redirect('admin/manage_cat');

        }

    }

    public function category_edit()
    {
        $this->form_validation->set_rules('cat_name', 'Category Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Edit Category';
            $data['breadcrumb'] = 'Edit Category';
            $data['layout']     = 'product/edit_category.php';
            $data['data']       = $this->db_model->select_multi('id, cat_name, parent_cat, description', 'product_categories', array('id' => $this->uri->segment(3)));
            $this->db->select('id, parent_cat');
            $data['parents'] = $this->db->get('product_categories')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $this->db->where('cat_id', $this->input->post('id'));
            $data = array(
                'cat_name'    => $this->input->post('cat_name'),
                'parent_cat'  => $this->input->post('parent_cat'),
                'description' => $this->input->post('description'),
            );
            $this->db->update('product_categories', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Updated Successfully.</div>');
            redirect('admin/manage_cat');
        }

    }

    public function parent_category()
    {
        $this->form_validation->set_rules('parent_name', 'Parent Category Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Add Parent Category';
            $data['breadcrumb'] = 'Add Parent Category';
            $data['layout']     = 'product/categories.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
              $parent_name        = $this->input->post('parent_name');
              $brand_id=$this->input->post('brand_id');
               $data = array(
                'parent_cat_name'    => $parent_name,
                'brand_id'    => $brand_id,
                
            );
            $this->db->insert('product_parent_category', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Parent Category added  Successfully.</div>');
            redirect('admin/manage_cat');
        }

    }

    public function expense()
    {
        $config['base_url']   = site_url('admin/expense');
        $config['per_page']   = 500000;
        $config['total_rows'] = $this->db_model->count_all('admin_expense');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['expense']    = $this->db->get('admin_expense')->result();
        $data['title']      = 'Manage Expenses';
        $data['breadcrumb'] = 'Manage Expenses';
        $data['layout']     = 'misc/expenses.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function expense_remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('admin_expense');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Expense Entry Deleted Successfully.</div>');
        redirect('admin/expense');
    }

}
