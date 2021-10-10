<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_vendor() == false) {
            redirect(site_url('site/vendor_login'));
        }
        $this->load->library('pagination');
        $this->load->library('cart');
        $this->load->helper('file_helper');
    }

    public function index()
    {
        //$data['title'] = 'Dashboard';
        //$data['breadcrumb'] = 'dashboard';
        //print_r($this->session->userdata('vendor_id'));exit();
        //$this->session->set_flashdata('vendor_id', $this->session->userdata('vendor_id'));
        $vendor_id=$this->session->userdata('vendor_id');
        redirect('../gmart/admin/pagesvendor/vendor_dashboard/'.$vendor_id);
        //redirect(base_url() . 'gmart/page/dashboard', 'refresh');
        //$this->load->view(config_item('vendor'));
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
                'vendor_id' => $this->session->vendor_id,
                
            );
            $this->db->insert('product_categories', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Created Successfully.</div>');
            redirect('vendor/manage_cat');
        } else {
            $config['base_url']   = site_url('vendor/manage_cat');
            $config['per_page']   = 50;
            $config['total_rows'] = $this->db_model->count_all('product_categories');
            $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $this->pagination->initialize($config);

            $this->db->select('cat_id, cat_name, parent_cat, image, description')->from('product_categories')->where('vendor_id',$this->session->vendor_id)
                     ->order_by('cat_name', 'DESC')->limit($config['per_page'], $page);

           $data['cat'] = $this->db->get()->result_array();
            //print_r($data['cat']); die();

            $this->db->select('parent_cat_id, parent_cat_name,brand_id');
            $data['parents'] = $this->db->get('product_parent_category')->result_array();
            $this->db->select('cat_id, cat_name,parent_cat')->where('vendor_id',$this->session->vendor_id);
            $data['category'] = $this->db->get('product_categories')->result_array();
            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category')->where('vendor_id',$this->session->vendor_id);
            $data['subcategory'] = $this->db->get('product_sub_category')->result_array();
            $this->db->select('brand_id, brand_name');
            $data['brand'] = $this->db->get('brands')->result_array();

            $data['title']      = 'Manage Product Categories';
            $data['breadcrumb'] = 'Product Categories';
            $data['layout']     = 'product/categories.php';
            $this->load->view('product_vendor/base', $data);

        }
    }

    public function add_brand()
    {
        $this->form_validation->set_rules('brand_name', 'Brand Name', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Add / Edit Brands';
            $data['layout']     = 'product/add_brand.php';
            $data['breadcrumb'] = 'Add / Edit Brands';
            $this->db->select('brand_id, brand_name,brand_image');
            $data['brand'] = $this->db->get('brands')->result_array();
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $brand_name        = $this->input->post('brand_name');
            $image             = 'default.jpg';
            if (trim($_FILES['img']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('vendor/add_brand');
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

                $data = array(
                       'brand_name'       => $brand_name,
                       //'brand_description'=> $description,
                       'brand_image'           => $image,
                );
                $this->db->insert('brands', $data);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Brand Added Successfully.</div>');
                redirect('vendor/add_brand');
            }
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
                'vendor_id' =>$this->session->vendor_id,
                
            );
            $this->db->insert('product_sub_category', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">SubCategory added Successfully.</div>');
            redirect('vendor/manage_cat');
        } else {
            $config['base_url']   = site_url('vendor/manage_cat');
            $config['per_page']   = 50;
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
            $this->load->view('product_vendor/base', $data);

        }
    }


    public function add_product()
    {
        $this->form_validation->set_rules('prod_name', 'Product Name', 'trim|required');
        $this->form_validation->set_rules('category', 'Product Category', 'trim|required');
        $this->form_validation->set_rules('prod_price', 'Product Price', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title']      = 'Add Product / Service';
            $data['breadcrumb'] = 'Add Product / Service';
            $data['layout']     = 'product/add_product.php';

            $this->db->select('cat_id, cat_name,parent_cat')->where('vendor_id',$this->session->vendor_id)->order_by('cat_name', 'ASC');
            $data['parents'] = $this->db->get('product_categories')->result_array();

            $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
            $data['brands'] = $this->db->get('brands')->result_array();

            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category,cat_id')->where('vendor_id',$this->session->vendor_id)->order_by('sub_cat_name', 'ASC');
            $data['subcat'] = $this->db->get('product_sub_category')->result_array();

            $this->db->select('id, flag_name')->order_by('flag_name', 'ASC');
            $data['flags'] = $this->db->get('flag')->result_array();

            $this->db->select('*')->where(array('type' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->load->view('product_vendor/base', $data);
        } else {
            //$product_cost     = $this->common_model->filter($this->input->post('product_cost'), 'float');
            $prod_name        = $this->input->post('prod_name');
            $plan_id          = $this->input->post('plan_id');
            $discount         = $this->input->post('discount');
            $category         = $this->input->post('category');
            $this->db->select('parent_cat_id');
            $this->db->where('cat_id', $category);
            $q = $this->db->get('product_categories');
            $parent_category = $q->result_array();
            $sub_category= $this->input->post('sub_category');            
            $brand        = $this->input->post('brand');
            //print_r($brand);die();
            $prod_price       = $this->common_model->filter($this->input->post('prod_price'), 'float');
            //$dealer_price       = $this->common_model->filter($this->input->post('dealer_price'), 'float');
            //$old_price       = $this->common_model->filter($this->input->post('old_price'), 'float');
            $prod_desc        = $this->input->post('prod_desc');
            $qty        = $this->input->post('qty');
            //$pv        = $this->db_model->select('pv', 'plans', array('id' => $plan_id));
            //$pv        = $this->input->post('pv') ? $this->input->post('pv') : 0;
            $gst              = $this->common_model->filter($this->input->post('gst'), 'float');
            $image            = 'default.jpg';
            $pro_name = $this->db_model->select('prod_name', 'product', array('prod_name' => $prod_name));


            if (trim($_FILES['img']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('vendor/add_product');
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
                    move_uploaded_file($_FILES['img']['tmp_name'], FCPATH . 'uploads/products/'.$image);
                    unlink('uploads/'.$image_data['file_name']);
                    $image = 'products/'.$image;
                }
            }
            if (trim($_FILES['img2']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img2')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/add_product');
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image2 = $image_data['file_name'];
                    move_uploaded_file($_FILES['img2']['tmp_name'], FCPATH . 'uploads/products/'.$image2);
                    unlink('uploads/'.$image_data['file_name']);
                    $image2 = 'products/'.$image2;
                }
            }
            if (trim($_FILES['img3']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img3')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/add_product');
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image3 = $image_data['file_name'];
                    move_uploaded_file($_FILES['img3']['tmp_name'], FCPATH . 'uploads/products/'.$image3);
                    unlink('uploads/'.$image_data['file_name']);
                    $image3 = 'products/'.$image3;
                }
            }
            if (trim($_FILES['img4']['name']) !== "") 
            {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img4')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/add_product');
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image4 = $image_data['file_name'];
                    move_uploaded_file($_FILES['img4']['tmp_name'], FCPATH . 'uploads/products/'.$image4);
                    unlink('uploads/'.$image_data['file_name']);
                    $image4 = 'products/'.$image4;
                }
            }
            if (trim($_FILES['img5']['name']) !== "") {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img5')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/add_product');
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image5 = $image_data['file_name'];
                    move_uploaded_file($_FILES['img5']['tmp_name'], FCPATH . 'uploads/products/'.$image5);
                    unlink('uploads/'.$image_data['file_name']);
                    $image5 = 'products/'.$image5;
                }
            }


            $data = array(
                'prod_name'       => $prod_name,
                'category'        => $category,
                'plan_id'         => $plan_id,
               // 'product_cost'    => $product_cost,
                 'brand'           => $brand,
                'discount'        => $discount,
                'prod_desc'       => $prod_desc,
                'vendor_id'       => $this->session->vendor_id,
                'qty'             => $qty,
                'sub_category'    => $sub_category,
                'parent_category' => $parent_category['0']['parent_cat_id'],
                'gst'             => $gst,
                'prod_price'    =>$prod_price,
                'image'           => $image,
                'image'           => $image,
                'image2'           => $image2,
                'image3'           => $image3,
                'image4'           => $image4,
                'image5'           => $image5,
                'display_product' => "Yes",
                'status'          => 'Selling',
            );
            
           
            $this->db->insert('product', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Product Added Successfully.</div>');
            redirect('vendor/manage_products');
            
        }
    }

    public function category()
    {
        $type = $this->uri->segment(3);
        $id   = $this->uri->segment(4);

        switch ($type) {
            case $type == "edit":
                redirect('vendor/category_edit/' . $id);
                break;
            case $type == "remove":
                $this->db->where('cat_id', $id);
                $this->db->delete('product_categories');
                $this->session->set_flashdata("common_flash", "<div class='alert alert-success'>Category deleted successfully.</div>");
                redirect('vendor/manage_cat');

        }

    }

    public function manage_sub_category()
    {
        $data['title']      = 'View / Edit Sub Category';
        $data['breadcrumb'] = 'View / Edit Sub Category';
        $data['layout']     = 'product/manage_sub_category.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('sub_cat_name', 'ASC')->where('vendor_id',$this->session->vendor_id);
        $data['sub_cat'] = $this->db->get('product_sub_category')->result_array();
        $this->load->view('product_vendor/base', $data);
    }

    public function manage_category()
    {
        $data['title']      = 'View / Edit Sub Category';
        $data['breadcrumb'] = 'View / Edit Sub Category';
        $data['layout']     = 'product/manage_category.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('cat_name', 'ASC')->where('vendor_id',$this->session->vendor_id);
        $data['cat'] = $this->db->get('product_categories')->result_array();
        $this->load->view('product_vendor/base', $data);
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
            $this->load->view('product_vendor/base', $data);
        } else {
            $this->db->where('cat_id', $this->input->post('id'));
            $data = array(
                'cat_name'    => $this->input->post('cat_name'),
                'parent_cat'  => $this->input->post('parent_cat'),
                'description' => $this->input->post('description'),
            );
            $this->db->update('product_categories', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Updated Successfully.</div>');
            redirect('vendor/manage_cat');
        }

    }

    public function parent_category()
    {
        $this->form_validation->set_rules('parent_name', 'Parent Category Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']      = 'Add Parent Category';
            $data['breadcrumb'] = 'Add Parent Category';
            $data['layout']     = 'product/categories.php';
            $this->load->view('product_vendor/base', $data);
        } else {
              $parent_name        = $this->input->post('parent_name');
              $brand_id=$this->input->post('brand_id');
               $data = array(
                'parent_cat_name' => $parent_name,
                'brand_id'    => $brand_id,
                );
            $this->db->insert('product_parent_category', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Parent Category added  Successfully.</div>');
            redirect('vendor/manage_cat');
        }

    }


    public function manage_products()
    {
        $data['title']      = 'View / Edit Products';
        $data['breadcrumb'] = 'View / Edit Products';
        $data['layout']     = 'product/manage_products.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->db->select('*')->order_by('prod_name', 'ASC')->where('vendor_id',$this->session->vendor_id);
        $data['prod'] = $this->db->get('product')->result_array();
        $this->load->view('product_vendor/base', $data);
    }
    public function view($id)
    {
        $product_data = $this->db_model->select_multi('*', 'product', array('id' => $id));

        $data['title']      = 'Product Detail';
        $data['breadcrumb'] = 'Manage Products';
        $data['layout']     = 'product/view_product.php';
        $data['data']       = $product_data;
        $this->load->view('product_vendor/base', $data);
    }

    public function edit($id)
    {
        $this->form_validation->set_rules('prod_name', 'Product Name', 'trim|required');
        $this->form_validation->set_rules('category', 'Product Category', 'trim|required');
        $this->form_validation->set_rules('product_cost', 'Product cost', 'trim|required');
        if ($this->form_validation->run() == false) {
            $product_data       = $this->db_model->select_multi('*', 'product', array('id' => $id . $this->input->post('id')));
            $data['product_name']=$this->db_model->select('prod_name', 'product', array('id' => $id));
            $data['title']      = 'Edit Product';
            $data['breadcrumb'] = 'Manage Products';
            $data['layout']     = 'product/edit_product.php';
            $data['data']       = $product_data;
            $this->db->select('sub_cat_id, sub_cat_name,parent_category,category,cat_id')->where('vendor_id',$this->session->vendor_id)->order_by('sub_cat_name', 'ASC');
            $data['subcat'] = $this->db->get('product_sub_category')->result_array();

             $this->db->select('cat_id, cat_name,parent_cat')->where('vendor_id',$this->session->vendor_id)->order_by('cat_name', 'ASC');
            $data['parents']    = $this->db->get('product_categories')->result_array();

            $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
            $data['brands'] = $this->db->get('brands')->result_array();

            $this->db->select('*')->where(array('type' =>'Repurchase'))->order_by('id', 'ASC');
            $data['plans'] = $this->db->get('plans')->result_array();

            $this->load->view('product_vendor/base', $data);
        } else {
            $prod_old_name= $this->db_model->select('prod_name', 'product', array('id' => $id));
            //$product_cost     = $this->common_model->filter($this->input->post('product_cost'), 'float');
            $discount= $this->common_model->filter($this->input->post('discount'), 'float');
            $prod_name        = $this->input->post('prod_name');
            $plan_id        = $this->input->post('plan_id');
            $category         = $this->input->post('category');
            $sub_category= $this->input->post('sub_category');            
            $brand        = $this->input->post('brand');
            $prod_price       = $this->common_model->filter($this->input->post('prod_price'), 'float');
            //$dealer_price       = $this->common_model->filter($this->input->post('dealer_price'), 'float');
            //$old_price       = $this->common_model->filter($this->input->post('old_price'), 'float');
            $prod_desc        = $this->input->post('prod_desc');
            $qty        = $this->input->post('qty');
            //$pv        = $this->input->post('pv');
            $gst       = $this->common_model->filter($this->input->post('gst'), 'float');
            if($this->db_model->select('id','product', array('prod_name'=>$prod_name)) != $this->input->post('id')){
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Product name already exists!! </div>');
                redirect('product/edit/'.$id);
            }

            $image = $old_img          = $this->db_model->select('image', 'product', array('id' => $this->input->post('id')));
            if (trim($_FILES['img']['name'] !== "")) {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('vendor/edit_product/' . $id . $this->input->post('id'));
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
                        unlink(FCPATH . '/uploads/' . $old_img);
                        move_uploaded_file($_FILES['img']['tmp_name'], FCPATH . 'uploads/products/'.$image);
                        unlink('uploads/'.$image_data['file_name']);
                        $image = 'products/'.$image;
                    }
                }
            }
            if (trim($_FILES['img2']['name'] !== "")) {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img2')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/edit_product/' . $id . $this->input->post('id'));
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image2 = $image_data['file_name'];
                    if (trim($image_data['file_name']) !== "") {
                        unlink(FCPATH . '/uploads/' . $old_img);
                        move_uploaded_file($_FILES['img2']['tmp_name'], FCPATH . 'uploads/products/'.$image2);
                        unlink('uploads/'.$image_data['file_name']);
                        $image2 = 'products/'.$image2;
                    }
                }
            }
            if (trim($_FILES['img3']['name'] !== "")) {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img3')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/edit_product/' . $id . $this->input->post('id'));
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image3 = $image_data['file_name'];
                    if (trim($image_data['file_name']) !== "") {
                        unlink(FCPATH . '/uploads/' . $old_img);
                        move_uploaded_file($_FILES['img3']['tmp_name'], FCPATH . 'uploads/products/'.$image3);
                        unlink('uploads/'.$image_data['file_name']);
                        $image3 = 'products/'.$image3;
                    }
                }
            }
            if (trim($_FILES['img4']['name'] !== "")) {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img4')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/edit_product/' . $id . $this->input->post('id'));
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image4 = $image_data['file_name'];
                    if (trim($image_data['file_name']) !== "") {
                        unlink(FCPATH . '/uploads/' . $old_img);
                        move_uploaded_file($_FILES['img4']['tmp_name'], FCPATH . 'uploads/products/'.$image4);
                        unlink('uploads/'.$image_data['file_name']);
                        $image4 = 'products/'.$image4;
                    }
                }
            }
            if (trim($_FILES['img5']['name'] !== "")) {

                $this->load->library('upload');

                if (!$this->upload->do_upload('img5')) {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Image not uploaded. Also select category.<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('product/edit_product/' . $id . $this->input->post('id'));
                } else {
                    $image_data               = $this->upload->data();
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $image_data['full_path']; //get original image
                    $config['maintain_ratio'] = true;
                    $config['width']          = 600;
                    $config['height']         = 500;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $image5 = $image_data['file_name'];
                    if (trim($image_data['file_name']) !== "") {
                        unlink(FCPATH . '/uploads/' . $old_img);
                        move_uploaded_file($_FILES['img5']['tmp_name'], FCPATH . 'uploads/products/'.$image5);
                        unlink('uploads/'.$image_data['file_name']);
                        $image5 = 'products/'.$image5;
                    }
                }
            }
            $data = array(
                'prod_name'       => $prod_name,
                'plan_id'         => $plan_id,
                'category'        => $category,
                'prod_price'    =>$prod_price,
                'brand'           => $brand,
                //'product_cost'    => $product_cost,
                'discount'        => $discount,
                'prod_desc'       => $prod_desc,
                'qty'             => $qty,
                'sub_category'    => $sub_category,
                'gst'             => $gst,
                'vendor_id'       => $this->session->vendor_id,
                'image'           => $image,
                'image2'           => $image2,
                'image3'           => $image3,
                'image4'           => $image4,
                'image5'           => $image5,
                'display_product' => "Yes",
                'status'          => 'Selling',

            );
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('product', $data);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Product Updated successfully.</div>');
            redirect('vendor/manage_products');

        }
    }
    public function remove($id)
    {
        $count = $this->db_model->count_all('product_sale', array(
            'product_id' => $id,
            'status'     => 'Processing',
        ));
        if ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Product Cannot be deleted as there are ' . $count . ' Un-Delivered Orders.</div>');
            redirect('vendor/manage_products');
        } else {
            $img = $this->db_model->select('image', 'product', array('id' => $id));
            $this->db->where('id', $id);
            $this->db->delete('product');
            if($img != 'default.jpg')
            {
                unlink(FCPATH . '/uploads/' . $img);
            }
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Product Deleted successfully.</div>');

            redirect('vendor/manage_products');
        }
    }
    public function delete_sub_category($id)
    {
        $count = $this->db_model->count_all('product', array(
            'sub_category' => $id,
            
        ));
        if ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Sub Category Cannot be deleted as there are ' . $count . ' products with that sub category</div>');
            redirect('vendor/manage_sub_category');
        } else {
            
            $this->db->where('sub_cat_id', $id);
            $this->db->delete('product_sub_category');
            
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Sub Category Deleted successfully.</div>');

            redirect('vendor/manage_sub_category');
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
            redirect('vendor/manage_category');
        }
        if ($count > 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Category Cannot be deleted as there are ' . $count . ' products with that category</div>');
            redirect('vendor/manage_category');
        } else {
            
            $this->db->where('cat_id', $id);
            $this->db->delete('product_categories');
            
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Category Deleted successfully.</div>');

            redirect('vendor/manage_category');
        }
    }
    public function search_product()
    {
        $data['title']      = 'Search Product';
        $data['breadcrumb'] = 'Search Product';
        $data['layout']     = 'product/search_product.php';
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['parents'] = $this->db->get('product_categories')->result_array();
        $this->load->view('product_vendor/base', $data);
    }
    public function search()
    {
        $category   = $this->input->post('category');
        //print_r($category);die();
        $pname      = $this->input->post('pname');
        $status     = $this->input->post('status');
        $is_sign_up = $this->input->post('is_sign_up');

        $this->db->select('id, prod_name,category, prod_price, gst, image, qty, sold_qty, display_product')
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
            $this->db->where('display_product', $is_sign_up);
        }
        $data['prod']       = $this->db->get('product')->result_array();
        $data['title']      = 'Search Results';
        $data['breadcrumb'] = 'Search Products';
        $data['layout']     = 'product/manage_products.php';
        $this->load->view('product_vendor/base', $data);
    }

    public function import()
    {
        $data = array();
        $memData = array();
        
        // If import request is submitted
        if($this->input->post('importSubmit'))
        {
            // Form field validation rules

            $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');
            
            // Validate submitted form data
            if($this->form_validation->run() == true){
                $insertCount = $updateCount = $rowCount = $notAddCount = 0;
                
                // If file uploaded
                if(is_uploaded_file($_FILES['file']['tmp_name']))
                {
                    // Load CSV reader library
                    $this->load->library('CSVReader');
                    
                    // Parse data from CSV file
                    $csvData = $this->csvreader->parse_csv($_FILES['file']['tmp_name']);

                    
                    // Insert/update CSV data into database
                    if(!empty($csvData))
                    {

                     foreach($csvData as $row)
                     { 
                       $rowCount++;
                       // Prepare data for DB insertion
                       //& $row['shipping_charge'] && $row['shipping_tax']&& $row['vendor_id']
                       if($row['prod_name'] && $row['plan_id'] && $row['vendor_id'] && $row['prod_price'] && $row['discount'] && $row['gst'] && $row['prod_desc'] && $row['parent_category'] &&  $row['category'] && $row['sub_category'] && $row['qty'] && $row['brand'] )
                       {  
                         $this->db->select('parent_cat_id')->where(array('parent_cat_name' => $row['parent_category']));
                         $parent_cat_id=$this->db->get('product_parent_category')->result_array();

                         $this->db->select('cat_id')->where(array('cat_name' => $row['category']));
                         $category_id=$this->db->get('product_categories')->result_array();
                         //print_r($category_id['0']['cat_id']);die();
                        
                         $this->db->select('sub_cat_id')->where(array('sub_cat_name' => $row['sub_category']));
                         $sub_cat_id=$this->db->get('product_sub_category')->result_array();

                          $this->db->select('brand_id')->where(array('brand_name' => $row['brand']));
                         $brand_id=$this->db->get('brands')->result_array();
                         //print_r($sub_cat_id['0']['sub_cat_id']);die();
                         if($category_id && $sub_cat_id)
                         {
                           $memData = array(
                                'prod_name' => $row['prod_name'],
                                'plan_id' => $row['plan_id'],
                                'vendor_id' => $row['vendor_id'],
                                'prod_price' => $row['prod_price'],
                                'discount'    =>  $row['discount'],
                                'gst' => $row['gst'],
                                'prod_desc' => $row['prod_desc'],
                                'parent_category' =>$parent_cat_id['0']['parent_cat_id'],
                                'category' => $category_id['0']['cat_id'],
                                'sub_category' => $sub_cat_id['0']['sub_cat_id'],
                                'brand'           => $brand_id['0']['brand_id'],
                                'qty' => $row['qty'],
                            );
                            $this->db->insert('product',$memData);
                           //check whether if the product name is already present in the product table
                           /*$product_name= $this->db_model->select('prod_name', 'product', array('prod_name' => $row['prod_name']));
                            if($product_name=="")
                            {
                              $this->db->insert('product',$memData);
                            }*/
                         }
                         else{
                          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Incorrect Category or subcategory</div>');
                           redirect('product/add_product');
                         }
                        }
                        else
                        {
                          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Headers donot match/Incorrect Category or subcategory.</div>');
                           redirect('vendor/add_product');
                        }
                       }
                     $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Products Added Successfully.</div>');
                     redirect('vendor/add_product');
                     // Status message with imported data count
                     $notAddCount = ($rowCount - ($insertCount + $updateCount));
                     $successMsg = 'Product imported successfully. Total Rows ('.$rowCount.') | Inserted ('.$insertCount.') | Updated ('.$updateCount.') | Not Inserted ('.$notAddCount.')';
                     $this->session->set_userdata('success_msg', $successMsg);
                    }
                    else{
                     $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Your CSV File is empty!.</div>');
                      redirect('vendor/add_product');
                    }
                }else{
                    $this->session->set_userdata('error_msg', 'Error on file upload, please try again.');
                    redirect('vendor/add_product');
                }
            }else{
                $this->session->set_userdata('error_msg', 'Invalid file, please select only CSV file.');
                redirect('vendor/add_product');
            }
        }
        //redirect('vendor');
        
    }

       /*
     * Callback function to check file value and type during validation
     */
    public function file_check($str){
        $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ""){
            $mime = get_mime_by_extension($_FILES['file']['name']);
            $fileAr = explode('.', $_FILES['file']['name']);
            $ext = end($fileAr);
            if(($ext == 'csv') && in_array($mime, $allowed_mime_types)){
                return true;
            }else{
                $this->form_validation->set_message('file_check', 'Please select only CSV file to upload.');
                return false;
            }
        }else{
            $this->form_validation->set_message('file_check', 'Please select a CSV file to upload.');
            return false;
        }
    }

    public function pending_orders()
    {
        $config['base_url']   = site_url('vendor/pending-orders');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('product_sale', array('status' => 'Processing'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $data['title']      = 'Pending Orders';
        $data['breadcrumb'] = 'Pending Orders';
        $data['layout']     = 'product/orders.php';
        $this->db->where(array('status' => "Processing",'vendor_id'=>$this->session->vendor_id))->order_by('date', 'ASC')->limit($config['per_page'], $page);
        $data['orders'] = $this->db->get('product_sale')->result();
        $this->load->view('product_vendor/base', $data);
    }

    public function mark_delivered()
    {
        $orderid = $this->input->post('mark_deliverid');
        $tdetail = $this->input->post('sdetail');
        debug_log('$orderid ' . $orderid . ' $tdetail ' . $tdetail);

        $before_tid = $this->db_model->select('tid', 'product_sale', array('id' => $orderid));

        if($before_tid != '')
        {
            $after_tid = $before_tid . "<br/><br/>" .  date('Y-m-d') . "<br/> Delivery Notes:<br/>" . $tdetail; 
        } else {
            $after_tid = date('Y-m-d') . "<br/> Delivery Notes:<br/>" . $tdetail; 
        }
        
        $data = array(
            'status'       => 'Delivered',
            'tid'          => $after_tid,
        );
        $this->db->where('id', $orderid);
        $this->db->update('product_sale', $data);
        
        //debug_log($this->db->last_query());
        
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Order successfully marked as Delivered.</div>');
        redirect('vendor/pending_orders');
    }

    public function remove_order($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('product_sale');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Order Deleted successfully.</div>');
        redirect('vendor/all_orders');
    }

    public function delivered()
    {
        $config['base_url']   = site_url('vendor/delivered');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('product_sale', array('status' => 'Delivered'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $data['title']      = 'Delivered Orders';
        $data['breadcrumb'] = 'Delivered Orders';
        $data['layout']     = 'product/delivered.php';
        $this->db->where(array('status' => "Delivered",'vendor_id'=>$this->session->vendor_id))->order_by('date', 'ASC')->limit($config['per_page'], $page);
        $data['orders'] = $this->db->get('product_sale')->result();
        $this->load->view('product_vendor/base', $data);
    }
    public function completed_orders()
    {

        $config['base_url']   = site_url('vendor/completed-orders');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('product_sale', array('status' => 'Completed'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $data['title']      = 'Completed Orders';
        $data['breadcrumb'] = 'Completed Orders';
        $data['layout']     = 'product/completed_orders.php';
        $this->db->where(array('status' => "Completed",'vendor_id'=>$this->session->vendor_id))->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $data['orders'] = $this->db->get('product_sale')->result();
        $this->load->view('product_vendor/base', $data);
    }
    public function all_orders()
    {

        $config['base_url']   = site_url('vendor/all-orders');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('product_sale');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $data['title']      = 'All Orders';
        $data['breadcrumb'] = 'List All Orders';
        $data['layout']     = 'product/all_orders.php';
        $this->db->where(array('vendor_id'=>$this->session->vendor_id))->order_by('date', 'ASC')->limit($config['per_page'], $page);
        $data['orders'] = $this->db->get('product_sale')->result();
        $this->load->view('product_vendor/base', $data);
    }
    public function view_order($id)
    {

        $data['layout'] = 'product/view_order.php';
        $data['orders'] = $this->db_model->select_multi('*', 'product_sale', array('id' => $id));
        $this->load->view('product_vendor/base', $data);
    }
    public function shipped()
    {
        $orderid = $this->input->post('shippedid');
        $tdetail = $this->input->post('sdetail');

        debug_log('$orderid ' . $orderid . ' $tdetail ' . $tdetail);

        $before_tid = $this->db_model->select('tid', 'product_sale', array('id' => $orderid));

        if($before_tid != '')
        {
            $after_tid = $before_tid . "<br/><br/>" .  date('Y-m-d') . "<br/>Consignment Number:<br/>" . $tdetail; 
        } else {
            $after_tid = date('Y-m-d') . "<br/> Consignment Number:<br/>" . $tdetail; 
        }
        
        $data = array(
            'tid'          => $after_tid,
        );
        $this->db->where('id', $orderid);
        $this->db->update('product_sale', $data);

        debug_log($this->db->last_query());

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Shipping Details Updated successfully.</div>');
        redirect('vendor/pending_orders');


    }

    public function logout()
    {
        $session_url=$_SESSION['page'];
        $this->session->sess_destroy();
        $this->session->set_flashdata('site_flash', '<div class="alert alert-info">You have been logged out !</div>');
        if($session_url)
        {
            redirect($session_url);
            //unset($_SESSION["page"]);
        }
        else
        {
            redirect(site_url('site/vendor_login'));
        }
    }

    public function App($template, $value)
    {
        $this->load->view($template.'/'.$value);
    }


    //support
    public function new_ticket()
    {
        $this->form_validation->set_rules('ticket_title', 'Ticket Title', 'trim|required');
        $this->form_validation->set_rules('ticket_data', 'Ticket Data', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title']  = 'New Support Request';
            $data['layout'] = 'support/new.php';
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $array = array(
                'ticket_title'  => $this->input->post('ticket_title'),
                'ticket_detail' => $this->input->post('ticket_data'),
                'vendor_id'        => $this->session->vendor_id,
                'user_type'     => "Vendor",
                'date'          => date('Y-m-d H:i:s'),
            );
            $this->db->insert('ticket', $array);

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">A New Ticket has been opened.</div>');
            redirect('vendor/old-Supports');
        }
    }

    public function old_Supports()
    {
        $this->load->library('pagination');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('ticket', array('vendor_id' => $this->session->vendor_id));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);
        $this->db->select('*');
        $this->db->where(array('vendor_id' => $this->session->vendor_id));
        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);
        $data['data']   = $this->db->get('ticket')->result();
        $data['layout'] = 'support/all_ticket.php';
        $this->load->view('product_vendor/base', $data);
    }

     public function view_ticket($id)
    {
        $this->form_validation->set_rules('ticket_reply', 'Ticket Reply Message', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['detail'] = $this->db_model->select_multi('*', 'ticket', array('id' => $id));
            $data['layout'] = 'support/ticket_view.php';
            $folder         = $this->session->user_id ? 'member' : 'admin';
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $array = array(
                'ticket_id' => $this->input->post('ticket_id'),
                'msg_from'  => $this->session->vendor_id ? $this->session->vendor_id : 'Admin',
                'msg'       => date('Y-m-d') . '<br>'. $this->input->post('ticket_reply'),
            );

            $this->db->insert('ticket_reply', $array);

            $array = array(
                'status' => $this->session->vendor_id ? 'Vendor Reply' : 'Waiting User Reply',
            );
            $this->db->where('id', $this->input->post('ticket_id'));
            $this->db->update('ticket', $array);
            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Message sent.</div>');
            redirect('vendor/view_ticket/' . $this->input->post('ticket_id'));
        }

    }

    public function close($id)
    {
        $array = array(
            'status' => 'Closed',
        );
        $this->db->where('id', $id);
        $this->db->update('ticket', $array);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Ticket Marked as solved and closed..</div>');
        $this->session->vendor_id ? redirect('vendor/old-Supports') : redirect('vendor/resolved');
    }   

     public function resolved()
    {
        $this->load->library('pagination');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('ticket', array('status' => 'Closed'));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);
        $this->db->select('*');
        $this->db->where(array('status' => 'Closed'));
        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);
        $data['data']   = $this->db->get('ticket')->result();
        $data['layout'] = 'support/resolved.php';
        $this->load->view(config_item('admin_theme'), $data);

    }


    // CORE MEMBER PARTS HERE NOW ############################################################ STARTS :
    public function news()
    {
        $config['base_url'] = site_url('member/news');
        $this->db->select('*')->from('news')->order_by('date', 'DESC');

        $data_news['news'] = $this->db->get()->result_array();

        $data_news['title'] = 'News Announcements';
        //$data['layout'] = 'ad/news.php';
        $this->load->view('member/base', $data_news);

    }
    public function settings()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $this->form_validation->set_rules('newpass', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repass', 'Retype Password', 'trim|required|matches[newpass]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Change Password';
            $data['layout'] = 'profile/acsetting.php';
            $this->load->view('product_vendor/base', $data);
        } else
          {
               if($this->input->post('oldpass') && $this->input->post('newpass'))
               {

                 $mypass = $this->db_model->select('password', 'vendor', array('vendor_id' => $this->session->vendor_id));

                    if (password_verify($this->input->post('oldpass'), $mypass) == true)
                    {
                        $array = array(
                            'password' => password_hash($this->input->post('newpass'), PASSWORD_DEFAULT),
                        );
                        $this->db->where('vendor_id', $this->session->vendor_id);
                        $this->db->update('vendor', $array);
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Login Password Updated Successfully!!.</div>');
                        redirect('vendor/settings');
                    }
                    else
                    {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                        redirect('vendor/settings');
                    }
               }
          }
    }

    public function settings_secure()
    {
        $this->form_validation->set_rules('oldsecure', 'Secure Password', 'trim|required');
        $this->form_validation->set_rules('newsecure', 'New Password', 'trim|required');
        $this->form_validation->set_rules('repasssecure', 'Retype Password', 'trim|required|matches[newsecure]');

        if ($this->form_validation->run() == false)
        {
            $data['title'] = 'Change Password';
            $data['layout'] = 'profile/acsetting.php';
            $this->load->view('product_vendor/base', $data);
        }
        else
        {
          if($this->input->post('oldsecure') && $this->input->post('newsecure')){
                $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));

                if (password_verify($this->input->post('oldsecure'), $mypass) == true)
                {

                    $array = array(
                        'secure_password' => password_hash($this->input->post('newsecure'), PASSWORD_DEFAULT),
                    );
                    $this->db->where('vendor_id', $this->session->vendor_id);
                    $this->db->update('vendor', $array);
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Secure Password Updated Successfully.</div>');
                    redirect('vendor/settings');
                }
                else
                {
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                    redirect('vendor/settings');
                }
           }

        }
    }

    public function reset_secure()
    {
      $vendor_id = trim($this->input->post('vendor_id'));
      $phone = trim($this->input->post('phone'));
      $data = $this->db_model->select_multi("name, password, phone", 'vendor', array('vendor_id' => $this->session->vendor_id));

      if ((password_verify($this->input->post('password'), $data->password) == true) && ($phone == $data->phone))
      {
        $randompassword=$this->common_model->randomPassword();
        $password = password_hash($randompassword, PASSWORD_DEFAULT);
        $data2 = array(
              'secure_password' => $password,
              'last_login_ip' => $this->input->ip_address(),
              'last_login' => time(),
          );
          $this->db_model->update($data2, 'vendor', array('vendor_id' => $vendor_id));

          $sms = "Hello " . $data->name . ", \nYou have requested for Secure Password Reset. \n Your Temporary Secure Password is: " . $randompassword . "\n".config_item('company_name');
          $messvar="Ok";
          $phone="91".$phone;
          $this->common_model->sms($phone, urlencode($sms));

          $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Success - Temporary Secure password is sent to your registered Phone Number. </div>');
          redirect('vendor/settings');
      }
      else
      {
          $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Invalid Details. Please Enter Correct Details !!!</div>');
          redirect('vendor/settings');
      }

    }
    
    public function profile()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'vendor', array('vendor_id' => $this->session->vendor_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone,email,address', 'vendor', array('vendor_id' => $this->session->vendor_id));
            $data['title'] = 'My Profile';
            $data['layout'] = 'profile/profile.php';
            $this->load->view('product_vendor/base', $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {
                if (trim($_FILES['photo']['name'] !== "")) {
                        $this->load->library('upload');
                        if (!$this->upload->do_upload('photo')) {
                            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Photo is not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                            redirect('vendor/profile');
                        } else {
                            $image_data = $this->upload->data();
                            $photo = $this->session->vendor_id .".".explode(".",$image_data['file_name'])[1];
                            unlink('uploads/profile/'.$photo);
                            move_uploaded_file($_FILES['photo']['tmp_name'], FCPATH . 'uploads/profile/'.$photo);
                            unlink('uploads/'.$image_data['file_name']);
                        }
                    }
                     $array = array(
                    'email' => $this->input->post('my_email'),
                    'address' => $this->input->post('my_address'),
                    'photo' => $photo,
                );
                $this->db->where('vendor_id', $this->session->vendor_id);
                $this->db->update('vendor', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('vendor/profile');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('vendor/profile');
            }
        }
    }

    public function shipping_address()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('s_name,s_phone,s_email,s_city,s_state,s_zipcode,s_address', 'shipping_address', array('userid' => $this->session->user_id));

            $data['title'] = 'Update Shipping Address';
            $data['layout'] = 'profile/shipping_address.php';
            $this->load->view('member/base', $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {
                    $array = array(
                    'userid'=>$this->session->user_id,
                    's_name' =>$this->input->post('my_name'),
                    's_phone' =>$this->input->post('my_phone'),
                    's_email' => $this->input->post('my_email'),
                    's_city'  => $this->input->post('my_city'),
                    's_state'  => $this->input->post('my_state'),
                    's_address' => $this->input->post('my_address'),
                    's_zipcode'  => $this->input->post('my_zipcode'),
                    );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('shipping_address', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Shipping Address Updated Successfully.</div>');
                redirect('member/shipping_address');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/shipping_address');
            }
        }
    }

    public function billing_address()
    {
        $this->form_validation->set_rules('oldpass', 'Current Password', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $this->session->user_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('b_name,b_phone,b_email,b_city,b_state,b_zipcode,b_address', 'shipping_address', array('userid' => $this->session->user_id));
            
            $data['title'] = 'Update Billing Address';
            $data['layout'] = 'profile/billing_address.php';
            $this->load->view('member/base', $data);
        } else {

            $mypass = $this->db_model->select('secure_password', 'member', array('id' => $this->session->user_id));

            if ((password_verify($this->input->post('oldpass'), $mypass) == true)) {
                    $array = array(
                    'b_name' =>$this->input->post('my_name'),
                    'b_phone' =>$this->input->post('my_phone'),
                    'b_email' => $this->input->post('my_email'),
                    'b_city'  => $this->input->post('my_city'),
                    'b_state'  => $this->input->post('my_state'),
                    'b_address' => $this->input->post('my_address'),
                    'b_zipcode'  => $this->input->post('my_zipcode'),
                    );
                $this->db->where('userid', $this->session->user_id);
                $this->db->update('shipping_address', $array);

                //$this->session->set_userdata('name', $this->input->post('my_name'));
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Billing Address Updated Successfully.</div>');
                redirect('member/billing_address');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('member/billing_address');
            }
        }
    }

    public function kyc()
    {
        $this->form_validation->set_rules('tax_no', 'PAN Number', 'trim|required');
        $this->form_validation->set_rules('aadhar_no', 'Aadhar Number', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'vendor_profile', array('vendor_id' => $this->session->vendor_id));
        $data['vdata']=$this->db_model->select_multi('photo,video', 'vendor', array('vendor_id' => $this->session->vendor_id));
        //print_r($data['vdata']);die();
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'vendor', array('vendor_id' => $this->session->vendor_id));
            $data['title'] = '';
            $data['layout'] = 'profile/kyc.php';
            $this->load->view('product_vendor/base', $data);
        }

        else {

            $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));

            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $aadharcard = '';
                $pancard = '';
                $cancelledcheque ='';
                if (trim($_FILES['pancard']['name'] != "")) {

                    $this->load->library('upload');

                    if (!$this->upload->do_upload('pancard')) {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">PAN card not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('vendor/kyc');
                    } else {
                        $image_data = $this->upload->data();
                        $pancard = $this->session->vendor_id ."_pancard.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['pancard']['tmp_name'], FCPATH . 'uploads/kyc/'.$pancard);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'id_proof' => $pancard,
                            'status' => "Pending"
                        );
                        $this->db->where('vendor_id', $this->session->vendor_id);
                        $this->db->update('vendor_profile', $array);

                    }
                }

                if (trim($_FILES['aadharcard']['name'] != "")) {

                    $this->load->library('upload');

                    if (!$this->upload->do_upload('aadharcard')) {
                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Address Proof not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('vendor/kyc');
                    } else {
                        $image_data = $this->upload->data();
                        $aadharcard = $this->session->vendor_id ."_aadharcard.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['aadharcard']['tmp_name'], FCPATH . 'uploads/kyc/'.$aadharcard);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'add_proof' => $aadharcard,
                            'status' => "Pending"
                        );
                        $this->db->where('vendor_id', $this->session->vendor_id);
                        $this->db->update('vendor_profile', $array);
                    }
                }
                if (trim($_FILES['cheque']['name'] != "")) {
                    
                    $this->load->library('upload');

                    if (!$this->upload->do_upload('cheque')) {

                        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Cancelled Cheque not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                        redirect('vendor/kyc');
                    } else {

                        $image_data = $this->upload->data();
                        $cancelledcheque = $this->session->vendor_id ."_cancelledcheque.".explode(".",$image_data['file_name'])[1];
                        move_uploaded_file($_FILES['cheque']['tmp_name'], FCPATH . 'uploads/kyc/'.$cancelledcheque);
                        unlink('uploads/'.$image_data['file_name']);

                        $array = array(
                            'cheque' => $cancelledcheque,
                            'status' => "Pending"
                        );
                        $this->db->where('vendor_id', $this->session->vendor_id);
                        $this->db->update('vendor_profile', $array);
                    }
                }

                $array = array(
                    'tax_no' => $this->input->post('tax_no'),
                    'aadhar_no' => $this->input->post('aadhar_no'),
                    'status' => "Pending"
                );
                $this->db->where('vendor_id', $this->session->vendor_id);
                $this->db->update('vendor_profile', $array);                


                #####Upload video of the registering user########
                $configVideo['upload_path'] = 'uploads/profile'; # check path is correct
                $configVideo['max_size'] = '102400';
                $configVideo['allowed_types'] = 'mp4'; # add video extenstion on here
                $configVideo['overwrite'] = FALSE;
                $configVideo['remove_spaces'] = TRUE;
                if(trim($_FILES['vupload']['name'] !== "")) 
                {
                  //debug_log($_FILES['vupload']['name']);
                  $this->load->library('upload', $configVideo);
                  if (!$this->upload->do_upload('vupload')) 
                  {
                    $this->upload->display_errors();
                    debug_log('upload failed');
                    $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Video is not uploaded..<br/>' . $this->upload->display_errors() . '</div>');
                    redirect('vendor/kyc');  
                  } 
                  else 
                  {
                            $video_data = $this->upload->data();
                            $video = $this->session->vendor_id ."_video.".explode(".",$video_data['file_name'])[1];
                            unlink('uploads/profile/'.$video);
                            move_uploaded_file($_FILES['vupload']['tmp_name'], FCPATH . 'uploads/profile/'.$video);
                            unlink('uploads/'.$video_data['file_name']);

                            $array=array(
                                'video'=>$video,
                            );
                            $this->db->where('vendor_id', $this->session->vendor_id);
                            $this->db->update('vendor', $array);

                  }
                }
            ##################End of Uploading video of registering User############


            

            $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('vendor/kyc');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Current Password" is wrong.</div>');
                redirect('vendor/kyc');
            }
        }
    }

    public function bankdetails()
    {
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
        $this->form_validation->set_rules('bank_ac_no', 'Bank Account Number', 'trim|required');
        $this->form_validation->set_rules('bank_ifsc', 'IFCS Code', 'trim|required');
        $this->form_validation->set_rules('bank_branch', 'Bank Branch', 'trim|required');
        $this->form_validation->set_rules('accounttype', 'Account Type', 'trim|required');

        $data['data'] = $this->db_model->select_multi('*', 'vendor_profile', array('vendor_id' => $this->session->vendor_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'vendor', array('vendor_id' => $this->session->vendor_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'bank_ac_no' => $this->input->post('bank_ac_no'),
                    'bank_name' => $this->input->post('bank_name'),
                    'bank_ifsc' => $this->input->post('bank_ifsc'),
                    'bank_branch' => $this->input->post('bank_branch'),
                    'account_type' => $this->input->post('accounttype'),
                    );
                $this->db->where('vendor_id', $this->session->vendor_id);
                $this->db->update('vendor_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('vendor/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('vendor/bankdetails');
            }
        }
    }
    public function nominee_details()
    {
        $this->form_validation->set_rules('nominee_name', 'Nominee Name', 'trim|required');
        $this->form_validation->set_rules('nominee_add', 'Nominee Address', 'trim|required');
        $this->form_validation->set_rules('nominee_relation', 'Nominee Relation', 'trim|required');
        $data['data'] = $this->db_model->select_multi('*', 'vendor_profile', array('vendor_id' => $this->session->vendor_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'vendor', array('vendor_id' => $this->session->vendor_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'nominee_name' => $this->input->post('nominee_name'),
                    'nominee_add' => $this->input->post('nominee_add'),
                    'nominee_relation' => $this->input->post('nominee_relation'),
                );
                $this->db->where('vendor_id', $this->session->vendor_id);
                $this->db->update('vendor_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('vendor/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('vendor/bankdetails');
            }
        }
    }
    public function upi()
    {
        $this->form_validation->set_rules('upi', 'upi', 'trim|required');
        
        $data['data'] = $this->db_model->select_multi('*', 'vendor_profile', array('vendor_id' => $this->session->vendor_id));
        if ($this->form_validation->run() == false) {
            $data['my'] = $this->db_model->select_multi('phone, email', 'vendor', array('vendor_id' => $this->session->vendor_id));
            $data['title'] = '';
            $data['layout'] = 'profile/bankdetails.php';
            $this->load->view('product_vendor/base', $data);
        }
        else {
            $mypass = $this->db_model->select('secure_password', 'vendor', array('vendor_id' => $this->session->vendor_id));
            if (password_verify($this->input->post('oldpass'), $mypass) == true) {
                $array = array(
                    'googlepay_no' => $this->input->post('googlepay_no'),
                    'phonepay_no' => $this->input->post('phonepay_no'),
                    'upi_id' => $this->input->post('upi'),
                    'btc_address' => $this->input->post('btc_address'),

                );
                $this->db->where('vendor_id', $this->session->vendor_id);
                $this->db->update('vendor_profile', $array);
                $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Profile Updated Successfully.</div>');
                redirect('vendor/bankdetails');
            } else {
                $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">The entered "Secure Password" is wrong.</div>');
                redirect('vendor/bankdetails');
            }
        }
    }

    public function welcome_letter()
    {
        $data['file_data'] = file_get_contents(FCPATH . "uploads/welcome_letter.txt");
        $data['title'] = 'Welcome Letter';
        $data['layout'] = "profile/welcome_letter.php";
        $this->load->view('member/base', $data);
    }

   
      //accounting functions
    public function add_invoice()
    {
        $invoice_name = $this->input->post('invoice_name');
        //print_r($invoice_name);die();
        $user_id      = $this->input->post('user_id');
        $invoice_date = $this->input->post('invoice_date');
        $user_type    = $this->input->post('user_type');
        $company_add  = $this->input->post('company_add');
        $bill_add     = $this->input->post('bill_add');
        $total_amt    = $this->input->post('total_amt');
        $paid_amt     = $this->input->post('paid_amt');

        $no     = 0;
        $array  = array();
        $array2 = array();
        $array3 = array();
        foreach ($_POST['item_name'] as $name) {
            if ($name !== "") {
                $item_name = htmlentities($name);
                $price     = $_POST['item_price'][$no];
                $tax       = $_POST['item_tax'][$no];
                $qty       = $_POST['item_qty'][$no] ? $_POST['item_qty'][$no] : 1;

                $array  += array($item_name => $price);
                $array2 += array($item_name => $tax);
                $array3 += array($item_name => $qty);
            }
            $no++;
        }
        $array  = serialize($array);
        $array2 = serialize($array2);
        $array3 = serialize($array3);
        $params = array(
            'invoice_name'     => $invoice_name,
            'userid'           => $user_id,
            'invoice_data'     => $array,
            'invoice_data_tax' => $array2,
            'invoice_data_qty' => $array3,
            'company_address'  => $company_add,
            'bill_to_address'  => $bill_add,
            'total_amt'        => $total_amt,
            'paid_amt'         => $paid_amt,
            'date'             => $invoice_date,
            'user_type'        => $user_type,
        );
        $this->db->insert('invoice', $params);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Invoice Created successfully.</div>');

        redirect('vendor/invoices');
    }

    public function add_purchase()
    {
        $bill_no   = $this->input->post('bill_no');
        $date      = $this->input->post('date');
        $supplier  = $this->input->post('supplier');
        $bill_amt  = $this->input->post('bill_amt');
        $paid_amt  = $this->input->post('paid_amt');
        $bill_copy = "";

        if (trim($_FILES['copy']['name']) !== "") {
            $bill_copy = time() . "+" . $_FILES['copy']['name'];
            move_uploaded_file($_FILES['copy']['tmp_name'], FCPATH . "uploads/" . $bill_copy);
        }
        $no    = 0;
        $array = array();
        foreach ($_POST['item_name'] as $name) {
            if ($name !== "") {
                $item_name = htmlentities($name);
                $price     = $_POST['item_price'][$no];

                $array += array($item_name => $price);
            }
            $no++;
        }
        $array  = serialize($array);
        $params = array(
            'bill_no'        => $bill_no,
            'date'           => $date,
            'supplier'       => $supplier,
            'bill_amt'       => $bill_amt,
            'paid_amt'       => $paid_amt,
            'purchased_data' => $array,
            'bill_copy'      => $bill_copy,
        );
        $this->db->insert('purchase', $params);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Purchase Entry Created successfully.</div>');

        redirect('vendor/purchase');
    }

    public function invoice_view($id)
    {
        $data['result'] = $this->db_model->select_multi('*', 'invoice', array('id' => $id));
        $this->load->view('product_vendor/accounting/print_invoice.php', $data);
    }


    public function purchase_view($id)
    {
        $data['result']     = $this->db_model->select_multi('*', 'purchase', array('id' => $id));
        $data['title']      = 'Purchase Detail';
        $data['breadcrumb'] = 'Purchase Detail';
        $data['layout']     = 'accounting/purchase_view.php';
        $this->load->view('product_vendor/base', $data);
    }

    public function transactionlogs()
    {
        $config['base_url']   = site_url('vendor/transactionlogs');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('transaction');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('transaction')->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $data['result']     = $this->db->get()->result();
        $data['title']      = 'Transaction Logs';
        $data['breadcrumb'] = 'Transaction Logs';
        $data['layout']     = 'accounting/transactionlogs.php';
        $this->load->view('product_vendor/base', $data);
    }


    public function remove_tlog($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('transaction');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Record Deleted successfully.</div>');

        redirect('vendor/transactionlogs');
    }

    public function remove_invoice($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('invoice');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Invoice Deleted successfully.</div>');

        redirect('vendor/invoices');
    }

    public function remove_purchase($id)
    {
        $bill_copy = $this->db_model->select('bill_copy', 'purchase', array('id' => $id));
        unlink(FCPATH . "uploads/" . $bill_copy);
        $this->db->where('id', $id);
        $this->db->delete('purchase');
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Purchase Record/Bill Deleted successfully.</div>');

        redirect('vendor/purchase');
    }


    public function invoices()
    {
        $config['base_url']   = site_url('vendor/invoices');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('invoice');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);//where(array('status' => 'Closed'))
        
        //$this->db->from('invoice')->where(array('invoice_name'=>'Purchase Invoice','vendor_id'=>$this->session->vendor_id))->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $this->db->from('invoice')->where(array('vendor_id'=>$this->session->vendor_id))->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $data['invoice']    = $this->db->get()->result();
        //print_r($data['invoice']);die();
        $data['title']      = 'Invoices';
        $data['breadcrumb'] = 'Invoices';
        $data['layout']     = 'accounting/invoices.php';
        $this->load->view('product_vendor/base', $data);

    }

    public function invoice_add_fund()
    {
        $array = array(
            'paid_amt' => $this->input->post('paid_amt') + $this->db_model->select('paid_amt', 'invoice', array('id' => $this->input->post('id'))),
        );
        $this->db->where('id', $this->input->post('id'));
        $this->db->update('invoice', $array);

        $data = array(
            'userid'         => $this->input->post('id'),
            'amount'         => $this->input->post('paid_amt'),
            'gateway'        => 'Invoice',
            'time'           => time(),
            'transaction_id' => 'Manual Entry',
        );
        $this->db->insert('transaction', $data);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Fund added to invoice successfully.</div>');

        redirect('vendor/invoices');
    }

    public function bill_add_fund()
    {
        $array = array(
            'paid_amt' => $this->input->post('paid_amt') + $this->db_model->select('paid_amt', 'purchase', array('id' => $this->input->post('id'))),
        );
        $this->db->where('id', $this->input->post('id'));
        $this->db->update('purchase', $array);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Balance Paid against bill successfully.</div>');

        redirect('vendor/purchase');
    }
   
    public function search_invoice()
    {

        if (trim($this->input->post('userid')) !== "") {
            $this->db->where('userid', $this->input->post('userid'));
        }
        if (trim($this->input->post('sdate')) !== "") {
            $this->db->where('date >=', $this->input->post('sdate'));
        }
        if (trim($this->input->post('edate')) !== "") {
            $this->db->where('date <=', $this->input->post('edate'));
        }

        $this->db->from('invoice')->order_by('id', 'DESC');
        $data['invoice']    = $this->db->get()->result();
        $data['title']      = 'Invoices';
        $data['breadcrumb'] = 'Invoices';
        $data['layout']     = 'accounting/invoices.php';
        $this->load->view('product_vendor/base', $data);
    }

    public function search_purchase()
    {

        if (trim($this->input->post('billno')) !== "") {
            $this->db->where('bill_no', $this->input->post('billno'));
        }
        if (trim($this->input->post('sdate')) !== "") {
            $this->db->where('date >=', $this->input->post('sdate'));
        }
        if (trim($this->input->post('edate')) !== "") {
            $this->db->where('date <=', $this->input->post('edate'));
        }

        $this->db->from('purchase')->order_by('id', 'DESC');
        $data['bills']      = $this->db->get()->result();
        $data['title']      = 'Purchase Records';
        $data['breadcrumb'] = 'Purchases';
        $data['layout']     = 'accounting/purchase.php';
        $this->load->view('product_vendor/base', $data);
    }

    public function accounting()
    {

        $data['title']      = 'Accounting';
        $data['breadcrumb'] = 'Accounting';
        $data['layout']     = 'accounting/accounting_dash.php';
        $this->load->view('product_vendor/base', $data);
    }


    public function purchase()
    {
        $config['base_url']   = site_url('vendor/purchase');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('purchase');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('purchase')->order_by('id', 'DESC')->limit($config['per_page'], $page);
        $data['bills']      = $this->db->get()->result();
        $data['title']      = 'Purchase Records';
        $data['breadcrumb'] = 'Purchases';
        $data['layout']     = 'accounting/purchase.php';
        $this->load->view('product_vendor/base', $data);
    
    

    }  
   
    public function producttax_report()
    {
        $top_id = $this->common_model->filter($this->input->post('top_id'));
        $sdate  = $this->input->post('sdate') ? $this->input->post('sdate') : '2019-01-01';
        $edate  = $this->input->post('edate') ? $this->input->post('edate') : date("Y-m-d");
        //debug_log($top_id);
        if (trim($top_id) == "")
        {
             
            $data['title']      = 'Product Sale Tax Report';
            $data['breadcrumb'] = 'Product Sale Tax Report';
            $data['layout']     = 'wallet/producttax_report.php';
            $this->db->select('id,userid,vendor_id, amount, tax_amount, tax_percnt,date, transaction_id')->where(array(
                    'transaction_id !='=>'', 'date >=' => $sdate, 'date <=' => $edate,'vendor_id'=>$this->session->vendor_id))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('tax_report')->result_array();
            //print_r($data['report']);die();
            $this->load->view('product_vendor/base', $data);
        }
        else
        {

            $data['title']      = 'Product Sale Tax Report';
            $data['breadcrumb'] = 'Product Sale Tax Report';
            $data['layout']     = 'wallet/producttax_report.php';
            $this->db->select('id,vendor_id, amount, tax_amount, tax_percnt,date')->where(array(
                    'vendor_id'=>$top_id, 'transaction_id !='=>'', 'date >=' => $sdate, 'date <=' => $edate,'vendor_id'=>$this->session->vendor_id))->order_by('id', 'DESC');
            $data['report']     = $this->db->get('tax_report')->result_array();
            //print_r($data['report']);die();
            //$this->load->view(config_item('admin_theme'), $data);
            redirect(site_url('vendor/producttax_report/' . $top_id . '/' . $sdate . '/' . $edate));
        }
            
    }
    public function export_product_details()
    {
        if(isset($_POST["export_product_details"]))
        {
            $filename = 'Product_details_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 

            /*$this->db->select('t1.id, t3.tax_no, t1.amount, t1.tax_amount, t1.tax_percnt, t1.date,t1.transaction_id')
                    ->order_by('t1.id', 'desc')
                    ->from('tax_report as t1')
                    ->where('t1.transaction_id !=' ,'')
                    ->join('member as t2', 't1.userid = t2.id', 'LEFT')
                    ->join('member_profile as t3', 't1.userid = t3.userid', 'LEFT');
            $data = $this->db->get()->result_array();*/
            $data=array(array("",'',$this->session->vendor_id,'','','','','','',''));
            
            //$data=array('','',$this->session->vendor_id,'','','','','','','','');
           
          
            // file creation 
            $file = fopen('php://output', 'w');

            $header = array('prod_name','plan_id','vendor_id','prod_price','discount','gst','prod_desc','brand','parent_category','category','sub_category','qty'); 
            fputcsv($file, $header);
            
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        }
    }

    public function export_sale_tax()
    {
        if(isset($_POST["export_sale_tax"]))
        {
            $filename = 'Product_Sale_Tax_Report_'.date('Y-m-d').'.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            // get data 

            $this->db->select('t1.userid, t2.name, t3.tax_no, t1.amount, t1.tax_amount, t1.tax_percnt, t1.date,t1.transaction_id')
                    ->order_by('t1.id', 'desc')
                    ->from('tax_report as t1')
                    ->where('t1.transaction_id !=' ,'')
                    ->join('member as t2', 't1.userid = t2.id', 'LEFT')
                    ->join('member_profile as t3', 't1.userid = t3.userid', 'LEFT');
            $data = $this->db->get()->result_array();

            // file creation 
            $file = fopen('php://output', 'w');

            $header = array('User ID','Name','PAN No','Total Amount','Tax Amount','Tax Percentage','Date','Details'); 
            fputcsv($file, $header);
            foreach ($data as $key=>$line){ 
                fputcsv($file,$line); 
            }
            fclose($file); 
            exit; 
        }
    }

    public function online_transactions()
    {
        $config['base_url']   = site_url('vendor/online_transactions');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('transaction');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($config['per_page'], $page);

        $data['trans']    = $this->db->get('transaction')->result();
        $data['title']      = 'Online Transaction';
        $data['breadcrumb'] = 'Online Transaction';
        $data['layout']     = 'misc/online_transactions.php';
        $this->load->view(config_item('admin_theme'), $data);
    }
}

