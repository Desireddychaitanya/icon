<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Email extends MY_Controller
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
        $this->load->library('user_model');
        $this->load->model('Email_Model');
        $this->load->helper('text');
    }

    public function index()
    {
        if($this->session->name == 'Admin') {
            $data['from_email']   = $this->db_model->select('email', 'admin', array('id' => $this->session->admin_id));
            $data['session_id']   = $this->session->admin_id;
            $data['session_name'] = $this->session->name;
        } else {
            $data['from_email'] = $this->db_model->select('email', 'member', array('id' => $this->session->user_id));
            $data['session_id'] = $this->session->user_id;
        }
        $data['product']        = $this->input->get('product');
        $data['work']           = $this->input->get('work');
        $data['misc']           = $this->input->get('misc');
        $data['family']         = $this->input->get('family');
        $data['design']         = $this->input->get('design');

        $data['search_mail']    = $this->input->get('search_mail');
        $data['trashMail']      = $this->input->get('trashMail');
        $data['draftMail']      = $this->input->get('draftMail');
        $data['starredMail']    = $this->input->get('starredMail');
        $data['sentMail']       = $this->input->get('sentMail');

        $data['count_unread_msg']    = $this->Email_Model->count_unread_msg($data['from_email']);
        /*
        $this->load->library('pagination');
        $config=[
            'base_url'   => base_url('Email'),
            'per_page'   => 10,
            'total_rows' => $this->Email_Model->get_count($data)
        ];
        $this->pagination->initialize($config);
        
        $config["uri_segment"] = 2;
        
        //$page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $data['per_page']   = 20;
        */
        $data['title']      = 'email';
        $data['layout']     = 'email/inbox_new.php';
        $data['inboxData']  = $this->Email_Model->get_inbox_data($data);

        $this->login->check_session() == TRUE ? $this->load->view(config_item('admin_theme'), $data) : $this->load->view(config_item('member'), $data);    
        
        //$this->load->view('templates/email/inbox',$data);
    }


    public function send_mail()
    {
        $data = $_POST;
        if (!empty($_FILES)) {

            // File upload configuration 
            $config['upload_path']   = 'axxets/email/file/post/';
            $config['allowed_types'] = '*';
            //$config['max_size']    = '100'; 
            //$config['max_width']   = '1024'; 
            //$config['max_height']  = '768'; 

            // Load and initialize upload library 
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            // Upload file to the server 
            if ($this->upload->do_upload('attachment')) {
                $fileData = $this->upload->data();
                $data['file_name'] = $fileData['file_name'];                
            }
        }
        //print_r($data);
        echo $this->Email_Model->send_mail($data);
    }

    public function showMessage() {        
        $result = $this->Email_Model->showMessage($_POST);
        echo json_encode($result);       
    }

    public function set_starred() {
        echo $this->Email_Model->set_starred($_POST);
    }

    public function set_trash_message() {
        echo $this->Email_Model->set_trash_message($_POST);
    }

    public function move_to_trash() {
        //print_r($_POST);
        echo $this->Email_Model->move_to_trash($_POST);
    }

    public function send_draft_message() {
        //print_r($_POST);
        echo $this->Email_Model->send_draft_message($_POST);
    }
}
