<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Site
 */
class Tree extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_session() == FALSE && $this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->model('plan_model');
        $this->load->model('downline_model');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
    }

    public function user_tree()
    {
        if ($this->login->check_session() == FALSE) {
            redirect(site_url('site/login'));
        }
        
        $top_id = $this->common_model->filter($this->input->post('top_id'));

        if(!$this->db_model->check_user($top_id)>0){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The User ID does not exist !!!</div>') ;
            redirect('tree/user-tree');
        }

        if(config_item('id_upgrade')=='Yes')
        {
            $top_id = $this->uri->segment('3') ? $this->uri->segment('3') : config_item('top_id');

            $this->db->select('pid')->where(array('userid'=>$top_id))->order_by('pid', 'ASC');
            $multi_array = $this->db->get('level_details')->result_array();

            //debug_log($this->db->last_query());

            if(count($multi_array)>0){
                $iterator_array = new RecursiveIteratorIterator(new RecursiveArrayIterator($multi_array));
                $flat_array = array();

                foreach($iterator_array as $v) {
                    array_push($flat_array, $v);
                }
                $ids = implode(',',$flat_array);
                
                $data['plans'] = $this->db->query("SELECT group_id as id,
                    GROUP_CONCAT(plan_name SEPARATOR ', ') as plan_name
                    FROM plans
                    where group_id IN (".$ids.")
                    GROUP BY 1"
                    )->result_array();
            }

            //debug_log($this->db->last_query());

            $data['title']      = 'User Tree';
            $data['breadcrumb'] = 'Tree';
            $data['layout']     = 'tree/user_tree.php';
            $this->load->view(config_item('admin_theme'), $data);

        }
        else
        {
            $plan_id = $this->common_model->filter($this->input->post('plan'));
            if (trim($top_id) == ""):

                $this->db->select('*')->where(array('type !=' =>'Repurchase', 'id !='=>$plan_id))->order_by('id', 'ASC');
                $data['plans'] = $this->db->get('plans')->result_array();

                $data['query'] = $this->db->query("Select * from plans where type = 'Registration' and show_on_regform = 'No'");

                if(config_item('sep_tree')=='Yes'){
                    if($plan_id != ''){
                        $data['result'] = $this->db_model->select_multi('*', 'plans', array('id' => $plan_id));    
                    } else{
                        $data['result'] = $this->db->select('id,plan_name')->from('plans')->order_by('id','ASC')->limit(1)->get()->result()[0];
                    }    
                }/*elseif (($data['query']->num_rows()>0) && ($plan_id != '')) {
                    $data['result'] = $this->db->query("Select * from plans where type = 'Registration' and show_on_regform = 'No' and id=".$plan_id)->row();
                }*/

                $data['title']      = 'User Tree';
                $data['breadcrumb'] = 'Tree';
                $data['layout']     = 'tree/user_tree.php';
                $this->load->view(config_item('admin_theme'), $data);

            else:
                if (!($this->session->admin_id)) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline tree !</div>');
                    redirect('tree/user-tree/');
                }
                redirect(site_url('tree/user-tree/' . $top_id));
            endif;
        }
    }

    public function user_tree_upgrade()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $plan = $this->common_model->filter($this->input->post('plan'));
        redirect(site_url('tree/user-tree/' . $top_id.'/'.$plan));
    }

    public function level_wise_users()
    {
        $data['title']      = 'Level Wise Members';
        $data['breadcrumb'] = 'Level Wise Members';
        $data['layout']     = 'tree/level_member.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
    
    public function downline_report()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }

        $top_id = $this->common_model->filter($this->input->post('top_id'));
        if (trim($top_id) == ""):
            $data['title']      = 'Downline Report';
            $data['breadcrumb'] = 'Downline Report';
            $data['layout']     = 'tree/downlinereport.php';
            $this->load->view(config_item('admin_theme'), $data);

        else:
            if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
                redirect('tree/downline-report/');
            }
            redirect(site_url('tree/downline-report/' . $top_id));
        endif;
    }

    public function referred_list()
    {
        if ($this->login->check_session() == FALSE) {
            exit('<h3 align="center">Session Expired ! Kindly Login again..</h3>');
        }
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        if (trim($top_id) == ""):
            $data['title']      = 'Referred Member List';
            $data['breadcrumb'] = 'Referred Member List';
            $data['layout']     = 'tree/referred_list.php';
            $this->load->view(config_item('admin_theme'), $data);

        else:
            if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline Detail !</div>');
                redirect('tree/referred-list/');
            }
            redirect(site_url('tree/referred-list/' . $top_id));
        endif;
    }

    ################ MEMBER PART ########################
    public function my_tree()
    {
        if(config_item('id_upgrade')=='Yes')
        {
            $top_id = $this->uri->segment('3') ? $this->uri->segment('3') : $this->session->user_id;

            $this->db->select('pid')->where(array('userid'=>$top_id))->order_by('pid', 'ASC');
            $multi_array = $this->db->get('level_details')->result_array();

            //debug_log($this->db->last_query());

            if(count($multi_array)>0){
                $iterator_array = new RecursiveIteratorIterator(new RecursiveArrayIterator($multi_array));
                $flat_array = array();

                foreach($iterator_array as $v) {
                    array_push($flat_array, $v);
                }
                $ids = implode(',',$flat_array);
                
                $data['plans'] = $this->db->query("SELECT group_id as id,
                    GROUP_CONCAT(plan_name SEPARATOR ', ') as plan_name
                    FROM plans
                    where group_id IN (".$ids.")
                    GROUP BY 1"
                    )->result_array();
            }

            //debug_log($this->db->last_query());

            $data['title']      = 'User Tree';
            $data['breadcrumb'] = 'Tree';
            $data['layout']     = 'tree/user_tree.php';
            $this->load->view(config_item('member'), $data);

        }
        else
        {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        if (trim($top_id) == ""):
            $data['title']      = 'User Tree';
            $data['breadcrumb'] = 'Tree';
            $data['layout']     = 'tree/user_tree.php';
            $this->load->view(config_item('member'), $data);

        else:
            if (trim($this->session->user_id) !== "" && $top_id < $this->session->user_id) {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline tree !</div>');
                redirect('tree/my_tree/');
            }
            redirect(site_url('tree/my_tree/' . $top_id));
        endif;
        }
    }

    public function my_tree_upgrade()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $plan = $this->common_model->filter($this->input->post('plan'));

        $uid = $this->db_model->select('id','level_details',array('userid'=>$this->session->user_id, 'pid'=>$this->input->post('plan')));

        //debug_log($uid);
        //debug_log($this->db->last_query());

        $sid = $this->db_model->select('id','level_details',array('userid'=>$this->input->post('top_id'), 'pid'=>$this->input->post('plan')));

        //debug_log($sid);
        //debug_log($this->db->last_query());

        if($sid < $uid){
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">You cannot view upline tree !</div>');
                redirect('tree/my_tree/');
        }else{
            redirect(site_url('tree/my_tree/' . $top_id.'/'.$plan));        
        }
    }

    public function level_wise_members()
    {
        $data['title']      = 'Level Wise Members';
        $data['breadcrumb'] = 'Level Wise Members';
        $data['layout']     = 'tree/level_member.php';
        $this->load->view(config_item('member'), $data);
    }

    public function genealogy()
    {

        $data['title']      = 'Downline Report';
        $data['breadcrumb'] = 'Downline Report';
        $data['layout']     = 'tree/downlinereport.php';
        $this->load->view(config_item('member'), $data);

    }

    public function directlist()
    {
        $data['title']      = 'Referred Member List';
        $data['breadcrumb'] = 'Referred Member List';
        $data['layout']     = 'tree/referred_list.php';
        $this->load->view(config_item('member'), $data);
    }

    public function new_user($position = '', $sponsor = '')
    {
        redirect(site_url('site/register/' . $position . '/' . $sponsor));
    }


    public function alldownline()
    {
        $data['title']      = 'All Downline List';
        $data['breadcrumb'] = 'All Downline List';
        $data['layout']     = 'tree/downline_list.php';
        //echo "<pre>"; print_r($this->session->user_id); die;
        $data['mymember'] = $this->downline_model->matchin_income($this->session->user_id);

        //echo "<pre>"; print_r($data['member']); die;
        $this->load->view(config_item('member'), $data);

    }
}
