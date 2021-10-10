<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plan extends MY_Controller
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
        $this->load->library('pagination');
    }

    public function index()
    {
        $data['title']      = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function add_plan()
    {   
        $this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required');
        $this->form_validation->set_rules('joining_fee', 'Joining Fee', 'trim|required');
        $this->form_validation->set_rules('direct_income', 'Direct Referral Commission', 'trim|required');
        //debug_log($this->common_model->filter($this->input->post('joining_fee'), 'float'));
        //debug_log($this->input->post('plan_name'));
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Create Plan';
            $data['breadcrumb'] = 'Create Plan';
            $data['layout']     = 'plan/add_plan.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {

            $query = $this->db->query("SELECT * FROM plans where type = 'Registration' ");
            if(($query->num_rows()==0)&&($this->input->post('plan_type')=='Repurchase')){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Please create a Registration Plan before creating Repurchase Plan !!!</div>');
                redirect('plan/add_plan');
            }

            $joining_fee = $this->common_model->filter($this->input->post('joining_fee'), 'float');
            $auto_pool = $this->input->post('auto_pool') ? $this->input->post('auto_pool') : 'No';
            $max_width        = $this->input->post('max_width');
            $type             = $this->input->post('plan_type') ? $this->input->post('plan_type') : 'Registration';

            #Code to validate if there is already a plan created for a given joining fee and other conditions

            if((config_item('sep_tree')=='No') && ($type != 'Repurchase')){
                $query = $this->db->query("SELECT * FROM plans where joining_fee = ".$joining_fee." and auto_pool = '".$auto_pool."' and max_width = ".$max_width);
                if($query->num_rows()>0){
                    debug_log($this->db->last_query());    
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Please choose a different plan as there is a plan with same Joining Fee !!!</div>');
                    redirect('plan/add_plan');
                }
            }

            $plan_name        = $this->input->post('plan_name');
            $pro_name = $this->db_model->select('plan_name', 'plans', array('plan_name' => $plan_name));
            if($pro_name){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Plan name already exists!! </div>');
                redirect('plan/add_plan');
            }
            
            $gst              = $this->common_model->filter($this->input->post('gst'), 'float');

            if(($auto_pool=='Yes') && (($max_width < 2) || ($max_width >7))){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Width must be more than 2 and less than or equal to 5 for Auto Pool Registration !! </div>');
                redirect('plan/add_plan');
            }

            $image           = 'default.jpg';
            $direct_income   = $this->input->post('direct_income');//$this->common_model->filter(, 'float');

            $first_pair_ratio=$this->input->post('first_pair_ratio') ? $this->input->post('first_pair_ratio') : '1:2/2:1';
            $second_pair_ratio=$this->input->post('second_pair_ratio') ? $this->input->post('second_pair_ratio') : '1:1';
            $first_pair_commission=$this->common_model->filter($this->input->post('first_pair_commission'),'float');
            $second_pair_commission=$this->common_model->filter($this->input->post('second_pair_commission'), 'float');
            $sponsor_match_commission =$this->common_model->filter($this->input->post('sponsor_match_commission'), 'float');
            $sponsor_pair_match =$this->common_model->filter($this->input->post('sponsor_pair_match'), 'float');
            $payout_frequency=$this->input->post('payout_frequency') ? $this->input->post('payout_frequency') : 'daily';
            $carry_forward=$this->input->post('carry_forward') ? $this->input->post('carry_forward') : 'no';
            $capping         = $this->input->post('capping') ? $this->input->post('capping') :0;
            $cappingamount = $this->common_model->filter($this->input->post('cappingamount'), 'float');
            $fix_income = $this->common_model->filter($this->input->post('fix_income'));
            $roi             = $this->common_model->filter($this->input->post('roi'), 'float');
            $roi_frequency   = $this->input->post('roi_frequency') ? $this->input->post('roi_frequency') : '1';
            $roi_limit       = $this->input->post('roi_limit') ? $this->input->post('roi_limit') : '0';

            $invoice_name        = $this->input->post('invoice_name') != '' ? $this->input->post('invoice_name') : $this->input->post('plan_name');
            $plan_desc        = $this->input->post('plan_desc');
            $category         = $this->input->post('category');
            $enable_recurring_fee = $this->input->post('enable_recurring_fee') ? $this->input->post('enable_recurring_fee') : '0';
            $recurring_fee = $this->input->post('recurring_fee') ? $this->input->post('recurring_fee') : '0';
            $recurring_duration = $this->input->post('recurring_duration') ? $this->input->post('recurring_duration') : '0';
            $pv               = $this->input->post('pv') ? $this->input->post('pv') : 0;

            if($roi_limit > ((1.2*$joining_fee) + $recurring_fee))
            {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Total ROI cannot be greater than 120% of joining_fee!! </div>');
                redirect('plan/add_plan');
            }


            $config_ref_comm = $this->input->post('config_ref_comm') ? $this->input->post('config_ref_comm') : 'amount';
            $ref_plan        = $this->input->post('ref_plan') ? $this->input->post('ref_plan') : 'joining';
            $pay_ref_lev     = $this->input->post('pay_ref_lev') ? $this->input->post('pay_ref_lev') : 'onlyref';
            $r_level1        = $this->input->post('r_level1') ? $this->input->post('r_level1') : 0;
            $r_level2        = $this->input->post('r_level2') ? $this->input->post('r_level2') : 0;
            $r_level3        = $this->input->post('r_level3') ? $this->input->post('r_level3') : 0;
            $r_level4        = $this->input->post('r_level4') ? $this->input->post('r_level4') : 0;
            $r_level5        = $this->input->post('r_level5') ? $this->input->post('r_level5') : 0;
            $r_level6         = $this->input->post('r_level6') ? $this->input->post('r_level6') : 0;
            $r_level7         = $this->input->post('r_level7') ? $this->input->post('r_level7') : 0;
            $r_level8         = $this->input->post('r_level8') ? $this->input->post('r_level8') : 0;
            $r_level9         = $this->input->post('r_level9') ? $this->input->post('r_level9') : 0;
            $r_level10 = $this->input->post('r_level10') ? $this->input->post('r_level10') : 0;
            $r_level11 = $this->input->post('r_level11') ? $this->common_model->filter($this->input->post('r_level11'), 'float') : 0;
            $r_level12 = $this->input->post('r_level12') ? $this->common_model->filter($this->input->post('r_level12'), 'float') : 0;
            $r_level13 = $this->input->post('r_level13') ? $this->common_model->filter($this->input->post('r_level13'), 'float') : 0;
            $r_level14 = $this->input->post('r_level14') ? $this->common_model->filter($this->input->post('r_level14'), 'float') : 0;
            $r_level15 = $this->input->post('r_level15') ? $this->common_model->filter($this->input->post('r_level15'), 'float') : 0;
            
            $config_comm = $this->input->post('config_comm') ? $this->input->post('config_comm') : 'amount';
            $self_purchase_comm=$this->common_model->filter($this->input->post('self_purchase_comm'), 'float');
            $p_level1   = $this->common_model->filter($this->input->post('p_level1'), 'float');
            $p_level2         = $this->common_model->filter($this->input->post('p_level2'), 'float');
            $p_level3        = $this->common_model->filter($this->input->post('p_level3'), 'float');
            $p_level4         = $this->common_model->filter($this->input->post('p_level4'), 'float');
            $p_level5         = $this->common_model->filter($this->input->post('p_level5'), 'float');
            $p_level6         = $this->common_model->filter($this->input->post('p_level6'), 'float');
            $p_level7         = $this->common_model->filter($this->input->post('p_level7'), 'float');
            $p_level8         = $this->common_model->filter($this->input->post('p_level8'), 'float');
            $p_level9        = $this->common_model->filter($this->input->post('p_level9'), 'float');
            $p_level10        = $this->common_model->filter($this->input->post('p_level10'), 'float');
            $p_level11        = $this->common_model->filter($this->input->post('p_level11'), 'float');
            $p_level12        = $this->common_model->filter($this->input->post('p_level12'), 'float');
            $p_level13        = $this->common_model->filter($this->input->post('p_level13'), 'float');
            $p_level14        = $this->common_model->filter($this->input->post('p_level14'), 'float');
            $p_level15        = $this->common_model->filter($this->input->post('p_level15'), 'float');

            $guest_level1 = $this->common_model->filter($this->input->post('guest_level1'), 'float');
            $guest_level2 = $this->common_model->filter($this->input->post('guest_level2'), 'float');
            $guest_level3 = $this->common_model->filter($this->input->post('guest_level3'), 'float');
            $guest_level4 = $this->common_model->filter($this->input->post('guest_level4'), 'float');
            $guest_level5 = $this->common_model->filter($this->input->post('guest_level5'), 'float');
            $level_income = $this->common_model->filter($this->input->post('level_income'), 'float');
            $plan_desc        = $this->input->post('plan_desc');
            $show_on_reg_form = $this->input->post('join_form');

            if ($show_on_reg_form !== "Yes") {
                $show_on_reg_form = "No";
            }

            if (trim($_FILES['img']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('plan/add_plan');
                } else {
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
            else
            {
               $image = 'default.jpg' ;
            }
            $data = array(
                'plan_name'       => $plan_name,
                'joining_fee'      => $joining_fee,
                'plan_description'       => $plan_desc,
                'gst'             => $gst,
                'max_width'        => $max_width,
                'direct_commission'   => $direct_income,
                'type'                => $type,
                'auto_pool'           => $auto_pool,

                'pv'                   => $pv,
                'first_pair_ratio'=>$first_pair_ratio,
                'second_pair_ratio' =>$second_pair_ratio,
                'first_pair_commission' =>$first_pair_commission,
                'second_pair_commission' =>$second_pair_commission,
                'sponsor_match_commission' =>$sponsor_match_commission,
                'sponsor_pair_match' =>$sponsor_pair_match,
                'capping'         => $capping,
                'cappingamount'   => $cappingamount,
                'payout_frequency' => $payout_frequency,
                'carry_forward'   => $carry_forward,
                "fix_income"       => $fix_income,
                "roi"               => $roi,
                "roi_frequency" => $roi_frequency,
                "roi_limit" => $roi_limit,
                'enable_recurring_fee' => $enable_recurring_fee,
                'recurring_fee'        => $recurring_fee,
                'recurring_duration'   => $recurring_duration,

                'config_ref_comm' => $config_ref_comm,
                'ref_plan' => $ref_plan,
                'pay_ref_lev' => $pay_ref_lev,
                'ref_level1_comm'=>$r_level1,
                'ref_level2_comm' =>$r_level2,
                'ref_level3_comm' =>$r_level3,
                'ref_level4_comm' =>$r_level4,
                'ref_level5_comm' =>$r_level5,
                'ref_level6_comm' =>$r_level6,
                'ref_level7_comm' =>$r_level7,
                'ref_level8_comm' =>$r_level8,
                'ref_level9_comm' =>$r_level9,
                'ref_level10_comm' =>$r_level10,
                'ref_level11_comm' =>$r_level11,
                'ref_level12_comm' =>$r_level12,
                'ref_level13_comm' =>$r_level13,
                'ref_level14_comm' =>$r_level14,
                'ref_level15_comm' =>$r_level15,
                'config_comm' => $config_comm,
                'self_product_purchase_comm' =>$self_purchase_comm,
                'product_pur_level1_comm' =>$p_level1,
                'product_pur_level2_comm' =>$p_level2,
                'product_pur_level3_comm' =>$p_level3,
                'product_pur_level4_comm' =>$p_level4,
                'product_pur_level5_comm' =>$p_level5,
                'product_pur_level6_comm' =>$p_level6,
                'product_pur_level7_comm' =>$p_level7,
                'product_pur_level8_comm' =>$p_level8,
                'product_pur_level9_comm' =>$p_level9,
                'product_pur_level10_comm' =>$p_level10,
                'product_pur_level11_comm' =>$p_level11,
                'product_pur_level12_comm' =>$p_level12,
                'product_pur_level13_comm' =>$p_level13,
                'product_pur_level14_comm' =>$p_level14,
                'product_pur_level15_comm' =>$p_level15,
                'guest_pcommission_level1' =>$guest_level1,
                'guest_pcommission_level2' =>$guest_level2,
                'guest_pcommission_level3' =>$guest_level3,
                'guest_pcommission_level4' =>$guest_level4,
                'guest_pcommission_level5' =>$guest_level5,
                'level_income'  => $level_income,
                'plan_image'           => $image,
                'show_on_regform'=>$show_on_reg_form,
                'qty'             => "-1",
                'status'          => 'Selling',
                'invoice_name'    => $invoice_name,

            );
            $this->db->insert('plans', $data);

            $insert_id = $this->db->insert_id();

            $query = $this->db->query("SELECT * FROM plans where id=1");
            if($query->num_rows()==0){
                $this->db->query("Update plans set id=1 where id=".$insert_id);
                debug_log($this->db->last_query());
            }

            ((config_item('sep_tree')=='Yes') || ($auto_pool == "Yes") || ($type == "Repurchase")) ? $this->db->query("Update plans set group_id = ".$insert_id." where id=".$insert_id) : $this->db->query("Update plans set group_id = 1 where id=".$insert_id); 

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Plan created successfully.</div>');
            redirect('plan/manage_plans');
        }
    }

    public function manage_plans()
    {
        $data['title']      = 'View / Edit Plans';
        $data['breadcrumb'] = 'View / Edit Plans';
        $data['layout']     = 'plan/manage_plans.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('id', 'ASC');
        if(config_item('enable_product')=='Yes'){
            $data['prod'] = $this->db->get('plans')->result_array();    
        }else{
            $this->db->where(array('type !='=>'Repurchase'));
            $data['prod'] = $this->db->get('plans')->result_array();    
        }
        
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function view($id)
    {
        $product_data = $this->db_model->select_multi('*', 'plans', array('id' => $id));

        $data['title']      = 'Plan Detail';
        $data['breadcrumb'] = 'Manage plans';
        $data['layout']     = 'plan/view_plan.php';
        $data['data']       = $product_data;
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function edit($id)
    {
        $this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required');
        $this->form_validation->set_rules('joining_fee', 'Joining Fee', 'trim|required');
        $this->form_validation->set_rules('direct_income', 'Direct Referral Commission', 'trim|required');
        if ($this->form_validation->run() == false) {
            $product_data       = $this->db_model->select_multi('*', 'plans', array('id' => $id . $this->input->post('id')));
            $data['product_name']= $product_data->plan_name;
            $data['title']      = 'Edit Plan';
            $data['breadcrumb'] = 'Manage Plans';
            $data['layout']     = 'plan/edit_plan.php';
            $data['data']       = $product_data;
            $data['parents']    = $this->db->get('product_categories')->result_array();
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $plan_name        = $this->input->post('plan_name');
            $joining_fee      = $this->common_model->filter($this->input->post('joining_fee'), 'float');
            $gst              = $this->common_model->filter($this->input->post('gst'), 'float');
            $max_width        = $this->input->post('max_width');
            $auto_pool = $this->input->post('auto_pool') ? $this->input->post('auto_pool') : 'No';

            if(($auto_pool=='Yes') && (($max_width < 2) || ($max_width >7))){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Width must be more than 2 and less than or equal to 5 for Auto Pool Registration !! </div>');
                redirect('plan/edit/'.$id);
            }

            $image           = 'default.jpg';
            $direct_income   = $this->input->post('direct_income');//$this->common_model->filter($this->input->post('direct_income'), 'float');

            $first_pair_ratio=$this->input->post('first_pair_ratio') ? $this->input->post('first_pair_ratio') : '1:2/2:1';
            $second_pair_ratio=$this->input->post('second_pair_ratio') ? $this->input->post('second_pair_ratio') : '1:1';
            $first_pair_commission=$this->common_model->filter($this->input->post('first_pair_commission'),'float');
            $second_pair_commission=$this->common_model->filter($this->input->post('second_pair_commission'), 'float');
            $sponsor_match_commission=$this->common_model->filter($this->input->post('sponsor_match_commission'), 'float');
            $sponsor_pair_match=$this->common_model->filter($this->input->post('sponsor_pair_match'), 'float');
            $payout_frequency=$this->input->post('payout_frequency') ? $this->input->post('payout_frequency') : 'daily';
            $carry_forward=$this->input->post('carry_forward') ? $this->input->post('carry_forward') : 'no';
            $capping         = $this->input->post('capping') ? $this->input->post('capping') : 0;
            $cappingamount = $this->common_model->filter($this->input->post('cappingamount'), 'float');
            $fix_income = $this->common_model->filter($this->input->post('fix_income'));
            $roi             = $this->common_model->filter($this->input->post('roi'), 'float');
            $roi_frequency   = $this->input->post('roi_frequency') ? $this->input->post('roi_frequency') : '1';
            $roi_limit       = $this->input->post('roi_limit') ? $this->input->post('roi_limit') : '0';
            $invoice_name        = $this->input->post('invoice_name') != '' ? $this->input->post('invoice_name') : $this->input->post('plan_name');
            $plan_desc        = $this->input->post('plan_desc');
            $category         = $this->input->post('category');
            $enable_recurring_fee = $this->input->post('enable_recurring_fee') ? $this->input->post('enable_recurring_fee') : '0';
            $recurring_fee = $this->input->post('recurring_fee') ? $this->input->post('recurring_fee') : '0';
            $recurring_duration = $this->input->post('recurring_duration') ? $this->input->post('recurring_duration') : '0';
            $pv               = $this->input->post('pv') ? $this->input->post('pv') : 0;
            

            if($roi_limit > ((1.2*$joining_fee) + $recurring_fee))
            {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Total ROI cannot be greater than 120% of joining_fee!! </div>');
                redirect('plan/edit/'.$id);
            }

            $config_ref_comm = $this->input->post('config_ref_comm') ? $this->input->post('config_ref_comm') : 'amount';
            $ref_plan        = $this->input->post('ref_plan') ? $this->input->post('ref_plan') : 'joining';
            $pay_ref_lev     = $this->input->post('pay_ref_lev') ? $this->input->post('pay_ref_lev') : 'onlyref';
            $r_level1        = $this->input->post('r_level1') ? $this->input->post('r_level1') : 0;
            $r_level2        = $this->input->post('r_level2') ? $this->input->post('r_level2') : 0;
            $r_level3        = $this->input->post('r_level3') ? $this->input->post('r_level3') : 0;
            $r_level4        = $this->input->post('r_level4') ? $this->input->post('r_level4') : 0;
            $r_level5        = $this->input->post('r_level5') ? $this->input->post('r_level5') : 0;
            $r_level6         = $this->input->post('r_level6') ? $this->input->post('r_level6') : 0;
            $r_level7         = $this->input->post('r_level7') ? $this->input->post('r_level7') : 0;
            $r_level8         = $this->input->post('r_level8') ? $this->input->post('r_level8') : 0;
            $r_level9         = $this->input->post('r_level9') ? $this->input->post('r_level9') : 0;
            $r_level10 = $this->input->post('r_level10') ? $this->input->post('r_level10') : 0;
            $r_level11 = $this->input->post('r_level11') ? $this->common_model->filter($this->input->post('r_level11'), 'float') : 0;
            $r_level12 = $this->input->post('r_level12') ? $this->common_model->filter($this->input->post('r_level12'), 'float') : 0;
            $r_level13 = $this->input->post('r_level13') ? $this->common_model->filter($this->input->post('r_level13'), 'float') : 0;
            $r_level14 = $this->input->post('r_level14') ? $this->common_model->filter($this->input->post('r_level14'), 'float') : 0;
            $r_level15 = $this->input->post('r_level15') ? $this->common_model->filter($this->input->post('r_level15'), 'float') : 0;
            
            $config_comm = $this->input->post('config_comm') ? $this->input->post('config_comm') : 'amount';
            $self_purchase_comm=$this->common_model->filter($this->input->post('self_purchase_comm'), 'float');
            $p_level1   = $this->common_model->filter($this->input->post('p_level1'), 'float');
            $p_level2         = $this->common_model->filter($this->input->post('p_level2'), 'float');
            $p_level3         = $this->common_model->filter($this->input->post('p_level3'), 'float');
            $p_level4         = $this->common_model->filter($this->input->post('p_level4'), 'float');
            $p_level5         = $this->common_model->filter($this->input->post('p_level5'), 'float');
            $p_level6         = $this->common_model->filter($this->input->post('p_level6'), 'float');
            $p_level7         = $this->common_model->filter($this->input->post('p_level7'), 'float');
            $p_level8         = $this->common_model->filter($this->input->post('p_level8'), 'float');
            $p_level9         = $this->common_model->filter($this->input->post('p_level9'), 'float');
            $p_level10        = $this->common_model->filter($this->input->post('p_level10'), 'float');
            $p_level11        = $this->common_model->filter($this->input->post('p_level11'), 'float');
            $p_level12        = $this->common_model->filter($this->input->post('p_level12'), 'float');
            $p_level13        = $this->common_model->filter($this->input->post('p_level13'), 'float');
            $p_level14        = $this->common_model->filter($this->input->post('p_level14'), 'float');
            $p_level15        = $this->common_model->filter($this->input->post('p_level15'), 'float');

            $guest_level1 = $this->common_model->filter($this->input->post('guest_level1'), 'float');
            $guest_level2 = $this->common_model->filter($this->input->post('guest_level2'), 'float');
            $guest_level3 = $this->common_model->filter($this->input->post('guest_level3'), 'float');
            $guest_level4 = $this->common_model->filter($this->input->post('guest_level4'), 'float');
            $guest_level5 = $this->common_model->filter($this->input->post('guest_level5'), 'float');
            $level_income = $this->common_model->filter($this->input->post('level_income'), 'float');
            $plan_desc        = $this->input->post('plan_desc');
            $show_on_reg_form = $this->input->post('join_form');

            if ($show_on_reg_form !== "Yes") {
                $show_on_reg_form = "No";
            }

            $image      =  $old_img    = $this->input->post('image');

            if (trim($_FILES['img']['name'] !== "")) {

                //debug_log('inside image');

                $this->load->library('upload');

                if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. ' . $this->upload->display_errors() . '</div>');
                    redirect('plan/edit/' . $this->input->post('id'));
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image = $image_data['file_name'];
                    if (trim($image_data['file_name']) !== "") {
                        if(trim($old_img) != 'default.jpg')
                        {
                            unlink(FCPATH . '/uploads/' . $old_img);
                        }
                        
                    }
                }
            }

            $data = array(
                'plan_name'       => $plan_name,
                'joining_fee'      => $joining_fee,
                'plan_description'       => $plan_desc,
                'gst'             => $gst,
                'max_width'        => $max_width,
                'direct_commission'   => $direct_income,
                //'auto_pool'           => $auto_pool,
                'pv'                   => $pv,
                'first_pair_ratio'=>$first_pair_ratio,
                'second_pair_ratio' =>$second_pair_ratio,
                'first_pair_commission' =>$first_pair_commission,
                'second_pair_commission' =>$second_pair_commission,
                'sponsor_match_commission' =>$sponsor_match_commission,
                'sponsor_pair_match' =>$sponsor_pair_match,
                'capping'         => $capping,
                'cappingamount'   => $cappingamount,
                'payout_frequency' => $payout_frequency,
                'carry_forward'   => $carry_forward,
                "fix_income"       => $fix_income,
                "roi"               => $roi,
                "roi_frequency" => $roi_frequency,
                "roi_limit" => $roi_limit,
                'enable_recurring_fee' => $enable_recurring_fee,
                'recurring_fee'        => $recurring_fee,
                'recurring_duration'   => $recurring_duration,

                'config_ref_comm' => $config_ref_comm,
                'ref_plan' => $ref_plan,
                'pay_ref_lev' => $pay_ref_lev,
                'ref_level1_comm'=>$r_level1,
                'ref_level2_comm' =>$r_level2,
                'ref_level3_comm' =>$r_level3,
                'ref_level4_comm' =>$r_level4,
                'ref_level5_comm' =>$r_level5,
                'ref_level6_comm' =>$r_level6,
                'ref_level7_comm' =>$r_level7,
                'ref_level8_comm' =>$r_level8,
                'ref_level9_comm' =>$r_level9,
                'ref_level10_comm' =>$r_level10,
                'ref_level11_comm' =>$r_level11,
                'ref_level12_comm' =>$r_level12,
                'ref_level13_comm' =>$r_level13,
                'ref_level14_comm' =>$r_level14,
                'ref_level15_comm' =>$r_level15,
                'config_comm' => $config_comm,
                'self_product_purchase_comm' =>$self_purchase_comm,
                'product_pur_level1_comm' =>$p_level1,
                'product_pur_level2_comm' =>$p_level2,
                'product_pur_level3_comm' =>$p_level3,
                'product_pur_level4_comm' =>$p_level4,
                'product_pur_level5_comm' =>$p_level5,
                'product_pur_level6_comm' =>$p_level6,
                'product_pur_level7_comm' =>$p_level7,
                'product_pur_level8_comm' =>$p_level8,
                'product_pur_level9_comm' =>$p_level9,
                'product_pur_level10_comm' =>$p_level10,
                'product_pur_level11_comm' =>$p_level11,
                'product_pur_level12_comm' =>$p_level12,
                'product_pur_level13_comm' =>$p_level13,
                'product_pur_level14_comm' =>$p_level14,
                'product_pur_level15_comm' =>$p_level15,
                'guest_pcommission_level1' =>$guest_level1,
                'guest_pcommission_level2' =>$guest_level2,
                'guest_pcommission_level3' =>$guest_level3,
                'guest_pcommission_level4' =>$guest_level4,
                'guest_pcommission_level5' =>$guest_level5,
                'level_income'             => $level_income,
                'plan_image'           => $image,
                'show_on_regform'=>$show_on_reg_form,
                'invoice_name'   => $invoice_name,
            );
            
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('plans', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Plan Updated successfully.</div>');
            redirect('plan/manage_plans');
            
        }
    }

    public function remove($id)
    {
        $count = $this->db_model->count_all('product_sale', array(
            'product_id' => $id,
            'status'     => 'Processing',
        ));

        $count_plan_enroll = $this->db_model->count_all('member', array(
            'signup_package' => $id,
        ));

        if ($count_plan_enroll > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Plan Cannot be deleted as there are ' . $count_plan_enroll . ' members who have enrolled for this plan.</div>');
            redirect('plan/manage_plans');
        } else {
            $img = $this->db_model->select('plan_image', 'plans', array('id' => $id));
            $this->db->where('id', $id);
            $this->db->delete('plans');
            if($img != 'default.jpg')
            {
                unlink(FCPATH . '/uploads/' . $img);
            }
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Plan Deleted successfully.</div>');
            redirect('plan/manage_plans');
        }
    }

    public function add_repurchase()
    {
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Set Target Income';
            $data['breadcrumb'] = 'Set Target Income';
            $data['layout']     = 'plan/add_repurchase.php';
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $type               = $this->input->post('income_type');
            $income_name        = $this->input->post('income_name');
            $based_on           = $this->input->post('based_on') ? $this->input->post('based_on') : 'PV';
            $config_comm        = $this->input->post('config_comm') ? $this->input->post('config_comm') : 'amount';
            $left_value         = $this->input->post('left_value') ? $this->input->post('left_value') : 0;
            $right_value        = $this->input->post('right_value') ? $this->input->post('right_value') : 0;
            $mypv               = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $downline        = $this->input->post('downline') ? $this->input->post('downline') : 0;
            $self_purchase_comm =  $this->input->post('self_purchase_comm') ? $this->common_model->filter($this->input->post('self_purchase_comm'), 'float') : 0;
            $direct_commission   = $this->input->post('direct_commission') ? $this->common_model->filter($this->input->post('direct_commission'), 'float') : 0;
            $binary_matching   = $this->input->post('binary_matching') ? $this->common_model->filter($this->input->post('binary_matching'), 'float') : 0;
            $amount            = $this->input->post('amount') ? $this->common_model->filter($this->input->post('amount'), 'float') : 0;
            $p_level1   = $this->common_model->filter($this->input->post('p_level1'), 'float');
            $p_level2         = $this->common_model->filter($this->input->post('p_level2'), 'float');
            $p_level3        = $this->common_model->filter($this->input->post('p_level3'), 'float');
            $p_level4         = $this->common_model->filter($this->input->post('p_level4'), 'float');
            $p_level5         = $this->common_model->filter($this->input->post('p_level5'), 'float');
            $p_level6         = $this->common_model->filter($this->input->post('p_level6'), 'float');
            $p_level7         = $this->common_model->filter($this->input->post('p_level7'), 'float');
            $p_level8         = $this->common_model->filter($this->input->post('p_level8'), 'float');
            $p_level9        = $this->common_model->filter($this->input->post('p_level9'), 'float');
            $p_level10        = $this->common_model->filter($this->input->post('p_level10'), 'float');
            $p_level11        = $this->common_model->filter($this->input->post('p_level11'), 'float');
            $p_level12        = $this->common_model->filter($this->input->post('p_level12'), 'float');
            $p_level13        = $this->common_model->filter($this->input->post('p_level13'), 'float');
            $p_level14        = $this->common_model->filter($this->input->post('p_level14'), 'float');
            $p_level15        = $this->common_model->filter($this->input->post('p_level15'), 'float');

            $pro_name = $this->db_model->select('income_name', 'flexible_income', array('income_name' => $income_name));
            
            $data = array(
                'type'  =>  $type,
                'income_name'     => $income_name,
                'based_on'  => $based_on,
                'A'      => $left_value,
                'B'     => $right_value,
                'mypv'  => $mypv,
                'downline' => $downline,
                'config_comm'     => $config_comm,
                'amount'        => $amount,
                'self_product_purchase_comm' => $self_purchase_comm,
                'direct_commission'   => $direct_commission,
                'binary_matching' => $binary_matching,
                'product_pur_level1_comm' =>$p_level1,
                'product_pur_level2_comm' =>$p_level2,
                'product_pur_level3_comm' =>$p_level3,
                'product_pur_level4_comm' =>$p_level4,
                'product_pur_level5_comm' =>$p_level5,
                'product_pur_level6_comm' =>$p_level6,
                'product_pur_level7_comm' =>$p_level7,
                'product_pur_level8_comm' =>$p_level8,
                'product_pur_level9_comm' =>$p_level9,
                'product_pur_level10_comm' =>$p_level10,
                'product_pur_level11_comm' =>$p_level11,
                'product_pur_level12_comm' =>$p_level12,
                'product_pur_level13_comm' =>$p_level13,
                'product_pur_level14_comm' =>$p_level14,
                'product_pur_level15_comm' =>$p_level15,
            );
            if($pro_name){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Income name already exists!! </div>');
            redirect('plan/add_repurchase');
            }
            else{
            $this->db->insert('flexible_income', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Repurchase Income Added successfully.</div>');
            redirect('plan/manage_repurchase');

            }
        }
    }

    public function manage_repurchase()
    {
        $data['title']      = 'View / Edit Target Income';
        $data['breadcrumb'] = 'View / Edit Target Income';
        $data['layout']     = 'plan/manage_repurchase.php';
        $this->db->select('*')->order_by('id', 'ASC');
        $data['repurchase'] = $this->db->get('flexible_income')->result_array();
        $this->load->view(config_item('admin_theme'), $data);

    }

    public function view_ri($id)
    {
        $plan_data = $this->db_model->select_multi('*', 'flexible_income', array('id' => $id));

        $data['title']      = 'Target Income Detail';
        $data['breadcrumb'] = 'Manage Target Income';
        $data['layout']     = 'plan/view_repurchase.php';
        $data['data']       = $plan_data;
        $this->load->view(config_item('admin_theme'), $data);
    }

    public function edit_ri($id)
    {
        $this->form_validation->set_rules('income_name', 'Income Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Edit Target Income';
            $data['breadcrumb'] = 'Edit Target Income';
            $data['layout']     = 'plan/edit_repurchase.php';
            $repurchase       = $this->db_model->select_multi('*', 'flexible_income', array('id' => $id));
            $data['data'] = $repurchase;
            $this->load->view(config_item('admin_theme'), $data);
        } else {
            $type               = $this->input->post('income_type');
            $income_name        = $this->input->post('income_name');
            $based_on           = $this->input->post('based_on') ? $this->input->post('based_on') : 'PV';
            $config_comm        = $this->input->post('config_comm') ? $this->input->post('config_comm') : 'amount';
            $left_value         = $this->input->post('left_value') ? $this->input->post('left_value') : 0;
            $right_value        = $this->input->post('right_value') ? $this->input->post('right_value') : 0;
            $mypv               = $this->input->post('mypv') ? $this->input->post('mypv') : 0;
            $downline        = $this->input->post('downline') ? $this->input->post('downline') : 0;
            $self_purchase_comm =  $this->input->post('self_purchase_comm') ? $this->common_model->filter($this->input->post('self_purchase_comm'), 'float') : 0;
            $direct_commission   = $this->input->post('direct_commission') ? $this->common_model->filter($this->input->post('direct_commission'), 'float') : 0;
            $binary_matching   = $this->input->post('binary_matching') ? $this->common_model->filter($this->input->post('binary_matching'), 'float') : 0;
            $amount   = $this->input->post('amount') ? $this->common_model->filter($this->input->post('amount'), 'float') : 0;
            $p_level1   = $this->common_model->filter($this->input->post('p_level1'), 'float');
            $p_level2         = $this->common_model->filter($this->input->post('p_level2'), 'float');
            $p_level3        = $this->common_model->filter($this->input->post('p_level3'), 'float');
            $p_level4         = $this->common_model->filter($this->input->post('p_level4'), 'float');
            $p_level5         = $this->common_model->filter($this->input->post('p_level5'), 'float');
            $p_level6         = $this->common_model->filter($this->input->post('p_level6'), 'float');
            $p_level7         = $this->common_model->filter($this->input->post('p_level7'), 'float');
            $p_level8         = $this->common_model->filter($this->input->post('p_level8'), 'float');
            $p_level9        = $this->common_model->filter($this->input->post('p_level9'), 'float');
            $p_level10        = $this->common_model->filter($this->input->post('p_level10'), 'float');
            $p_level11        = $this->common_model->filter($this->input->post('p_level11'), 'float');
            $p_level12        = $this->common_model->filter($this->input->post('p_level12'), 'float');
            $p_level13        = $this->common_model->filter($this->input->post('p_level13'), 'float');
            $p_level14        = $this->common_model->filter($this->input->post('p_level14'), 'float');
            $p_level15        = $this->common_model->filter($this->input->post('p_level15'), 'float');

            $pro_name = $this->db_model->select('income_name', 'flexible_income', array('income_name' => $income_name));
            
            $data = array(
                'type'  =>  $type,
                'income_name'     => $income_name,
                'based_on'  => $based_on,
                'A'      => $left_value,
                'B'     => $right_value,
                'mypv'  => $mypv,
                'downline' => $downline,
                'config_comm'     => $config_comm,
                'amount'        => $amount,
                'self_product_purchase_comm' => $self_purchase_comm,
                'direct_commission'   => $direct_commission,
                'binary_matching' => $binary_matching,
                'product_pur_level1_comm' =>$p_level1,
                'product_pur_level2_comm' =>$p_level2,
                'product_pur_level3_comm' =>$p_level3,
                'product_pur_level4_comm' =>$p_level4,
                'product_pur_level5_comm' =>$p_level5,
                'product_pur_level6_comm' =>$p_level6,
                'product_pur_level7_comm' =>$p_level7,
                'product_pur_level8_comm' =>$p_level8,
                'product_pur_level9_comm' =>$p_level9,
                'product_pur_level10_comm' =>$p_level10,
                'product_pur_level11_comm' =>$p_level11,
                'product_pur_level12_comm' =>$p_level12,
                'product_pur_level13_comm' =>$p_level13,
                'product_pur_level14_comm' =>$p_level14,
                'product_pur_level15_comm' =>$p_level15,
            );

            //check if entered plan name has spaces
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('flexible_income', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Target Income Updated successfully.</div>');
            redirect('plan/manage_repurchase');
        }
    }

    public function remove_ri($id)
    {
        
        $this->db->where('id', $id);
        $this->db->delete('flexible_income');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Target Income Deleted successfully.</div>');
        redirect('plan/manage_repurchase');
        
    }

    public function search()
    {
        $category   = $this->input->post('category');
        $pname      = $this->input->post('pname');
        $status     = $this->input->post('status');
        $is_sign_up = $this->input->post('is_sign_up');

        $this->db->select('id, prod_name, prod_price, gst, image, qty, sold_qty, show_on_regform')
            ->order_by('prod_name', 'ASC');
        if ($category !== "All") {
            $this->db->where('category', $category);
        }
        if (trim($pname) !== "") {
            $this->db->like('prod_name', $pname);
        }
        if ($status !== "All") {
            $this->db->where('status', $status);
        }
        if ($is_sign_up !== "All") {
            $this->db->where('show_on_regform', $is_sign_up);
        }
        $data['prod']       = $this->db->get('product')->result_array();
        $data['title']      = 'Search Results';
        $data['breadcrumb'] = 'Search plans';
        $data['layout']     = 'plan/manage_plans.php';
        $this->load->view(config_item('admin_theme'), $data);
    }

}
