<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Insurance_admin extends MY_Controller
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
        $this->load->library('pagination');
    }


    public function insurance_4W()
    {
        $config['base_url']   = site_url('insurance_admin/insurance_4W');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);
        $this->db->select('*')
                 ->from('insurance')->order_by('id', 'desc');
        $this->db->limit($config['per_page'], $page);
        $data['details'] = $this->db->get()->result_array();
        $data['title']      = 'Motor Insurance Details';
        $data['breadcrumb'] = 'Motor Insurance Details';
        $data['layout']     = 'insurance/insurance_4W.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
	
	 public function insurance_health()
    {
        $config['base_url']   = site_url('insurance_admin/insurance_health');
        $config['per_page']   = 50;
        $this->pagination->initialize($config);
        $this->db->select('*')
                 ->from('health_insurance')->order_by('id', 'desc');
        $this->db->limit($config['per_page'], $page);
        $data['details'] = $this->db->get()->result_array();
        $data['title']      = 'Health Insurance Details';
        $data['breadcrumb'] = 'Health Insurance Details';
        $data['layout']     = 'insurance/insurance_health.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    //insurance_health
   
}
