<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Site
 */
class Ads extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == FALSE && $this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');
    }

    public function get_ads_sub_types() {
        $data = $_POST;
        $ads_sub_type   = $this->db_model->select('sub_type', 'ads_type', array('type' => $data['ads_type']));
        echo $ads_sub_type;
    }

    public function manage_ads()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $data['manage_ads']         = $this->db_model->select('ads_set_member', 'ads_settings', array('id' => 2));
        $member_details             = $this->db_model->select_multi('signup_package, activate_time', 'member', array('id' => $this->session->user_id));
        $plan_details               = $this->db_model->select_multi('ads_types, duration', 'plans', array('id' => $member_details->signup_package));
        $data['plan_duration']      = $plan_details->duration;
        $data['plan_activate_time'] = $member_details->activate_time;
        $config['base_url']         = site_url('ads/manage_ads');
        $data['search_ads_type']    = $this->input->post('search_ads_type');
        $data['search_ads_sub_type'] = $this->input->post('search_ads_sub_type');
        $this->load->model('earning');
        $data['ads_types']          = $this->earning->get_all_ads_types($plan_details->ads_types);
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('ads_new');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $data['ads_timer']          = $this->db_model->select_multi('ads_set_admin, ads_value', 'ads_settings', array('id' => 1));
        $data['ads_days']           = $this->db_model->select_multi('ads_set_admin, ads_value', 'ads_settings', array('id' => 3));
        $data['session_id'] = $this->session->admin_id;
        $this->db->select('*');
        $this->db->from('ads_new');
        if (isset($data['search_ads_type']) && $data['search_ads_type'] != "") {
            $this->db->where('ads_type=', $data['search_ads_type']);
        }
        if (isset($data['search_ads_sub_type']) && $data['search_ads_sub_type'] != "") {
            $this->db->where('ads_sub_type=', $data['search_ads_sub_type']);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['ads_new'] = $this->db->get()->result();

        $data['title']      = 'Manage Ads';
        $data['breadcrumb'] = 'Manage Ads';
        $data['layout']     = 'ad/listads.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function update()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $ad_title      = $this->input->post('ad_title');
        $ad_html       = addslashes($_POST['ad_html']);
        $level_earning = $this->input->post('level_earning');
        $expiry_date   = $this->input->post('expiry_date');
        $type          = $this->input->post('type');

        $array = array(
            'ad_title'      => $ad_title,
            'ad_html'       => $ad_html,
            'level_earning' => $level_earning,
            'expiry_date'   => $expiry_date,
            'type'          => $type,
        );
        $this->db->where('id', $this->input->post('id'));
        $this->db->update('ads', $array);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Ads Updated Successfully.</div>');
        redirect('ads/manage_ads');
    }

    public function create()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $ad_title      = $this->input->post('ad_title');
        $ad_html       = addslashes($_POST['ad_html']);
        $level_earning = $this->input->post('level_earning');
        $expiry_date   = $this->input->post('expiry_date');
        $type          = $this->input->post('type');

        $array = array(
            'ad_title'      => $ad_title,
            'ad_html'       => $ad_html,
            'level_earning' => $level_earning,
            'expiry_date'   => $expiry_date,
            'type'          => $type,
        );

        $this->db->insert('ads', $array);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Ads Created Successfully.</div>');
        redirect('ads/manage_ads');
    }

    public function preview($id)
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $data = $this->db_model->select_multi('ad_html, type', 'ads', array('id' => $id));
        if ($data->type == "url") {
            redirect(trim($data->ad_html));
        } else {
            echo stripslashes($data->ad_html);
        }
    }


    public function remove($id)
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $this->db->where('id', $id);
        $this->db->delete('ads');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Ad Deleted successfully.</div>');

        redirect('ads/manage_ads');
    }

    public function edit($id)
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        $data['ads'] = $this->db_model->select_multi('*', 'ads', array('id' => $id));

        $data['title']      = 'Edit Ad';
        $data['breadcrumb'] = 'Edit Ad';
        $data['layout']     = 'ad/editad.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function achievers()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Yuk !! Go and have a bath..</h3>');
        }
        if (trim($this->input->post('userid')) !== "") {
            $where = array('userid' => $this->common_model->filter($this->input->post('userid')));
        } else {
            $where = 1;
        }
        $config['base_url']   = site_url('ads/achievers');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('ad_user', $where);
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, ad_id, userid, date')->from('ad_user')->order_by('id', 'DESC')
            ->limit($config['per_page'], $page);

        if (trim($this->input->post('userid')) !== "") {
            $this->db->where(array('userid' => $this->common_model->filter($this->input->post('userid'))));
        }

        $data['ads']        = $this->db->get()->result();
        $data['title']      = 'People Who Viewed your Ads';
        $data['breadcrumb'] = 'Ad Viewers';
        $data['layout']     = 'ad/ad_viewer.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

    ################ MEMBER #############################
    public function myads()
    {
        $config['base_url']   = site_url('ads/myads');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('ads');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('id, ad_title, expiry_date')->from('ads')->order_by('id', 'DESC')
            ->limit($config['per_page'], $page);

        $data['ads']        = $this->db->get()->result();
        $data['title']      = 'My Ads';
        $data['breadcrumb'] = 'My Ads';
        $data['layout']     = 'ad/myads.php';
        $this->load->view(config_item('member'), $data);
    }

    public function ad_view($id)
    {
        $data = $this->db_model->select_multi('ad_html, level_earning, type', 'ads', array('id' => $id));
        if (trim($data->level_earning) !== "") {
            $this->load->model('earning');
            $this->earning->advt_level($this->session->user_id, $id, $data->level_earning);
        }
        if ($data->type == "url") {
            redirect(trim($data->ad_html));
        } else {

            echo stripslashes($data->ad_html);
        }
    }
}
