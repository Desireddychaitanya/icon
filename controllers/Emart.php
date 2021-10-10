<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emart extends MY_Controller
{
    /**
     *** Check Valid Login or display login page.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->model('earning');
    }

    public function index()
    {
      redirect(site_url('store'));
    }

    public function store()
    {   
     //print_r("heleo");die();
      $data['title'] = 'Online Premium Store';

      $user_id = $this->session->user_id;
      $name=$this->session->name;
      $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc')->order_by('id', 'ASC')->where('Status',"Selling");
      $data['data']=$this->db->get('product')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'home_banner');
      $data['home_banner1']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'product_1_v2');
      $data['product_1_v2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'product_2_v2');
      $data['product_2_v2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'product_3_v2');
      $data['product_3_v2']=$this->db->get('store_images')->result_array();

      $this->db->select('cat_id, cat_name,parent_cat,parent_cat_id,image,description')->where('parent_cat', 'women')->order_by('cat_id', 'ASC');
      $data['cat_women']=$this->db->get('product_categories')->result_array();

      $this->db->select('cat_id, cat_name,parent_cat,parent_cat_id,image,description')->where('parent_cat', 'Men')->order_by('cat_id', 'ASC');
      $data['cat_men']=$this->db->get('product_categories')->result_array();

      $this->db->select('cat_id, cat_name,parent_cat,parent_cat_id,image,description')->where('parent_cat', 'Baby & Kids')->order_by('cat_id', 'ASC');
      $data['cat_kids']=$this->db->get('product_categories')->result_array();

      /*this->db->select('prod_name,prod_price,category,brand,prod_desc')->where('id', $data['home_banner1']['prod_id']);
      array_push($home_banner1, $this->db->get('product')->result_array());*/

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'home_banner2');
      $data['home_banner2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'home_products');
      $data['home_products']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'newpost1');
      $data['newpost1']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'newpost2');
      $data['newpost2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'newpost3');
      $data['newpost3']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'midpage_banner');
      $data['midpage_banner']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'home_product2');
      $data['home_product2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'on_sale');
      $this->db->limit(1);
      $data['on_sale']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'on_sale2');
      $this->db->limit(2);
      $data['on_sale2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'hot_deal');
      $data['hot_deal']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'new_collection');
      $data['new_collection']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'new_collection2');
      $data['new_collection2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'new_collection3');
      //$this->db->limit(3);
      $data['new_collection3']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'featured_products');
      $this->db->limit(1);
      $data['featured_products']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'featured_products2');
      $this->db->limit(1);
      $data['featured_products2']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'featured_products3');
      $this->db->limit(1);
      $data['featured_products3']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'featured_products4');
      $this->db->limit(1);
      $data['featured_products4']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shopping_cart_special');
      $this->db->limit(1);
      $data['shopping_cart_special']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'newsletter_banner');
      $this->db->limit(1);
      $data['newsletter_banner']=$this->db->get('store_images')->result_array();

      $this->db->order_by('id', 'RANDOM');
      $this->db->limit(2);
      $query = $this->db->get('product');
      $data['random_product']=$query->result_array();
      //print_r($data['random_product']);die();

      $this->db->order_by('prod_name', 'RANDOM');
      $this->db->limit(1);
      $query = $this->db->get('product');
      $data['footer_product']=$query->result_array();

      $this->load->view('store/index', $data);
    }
    

    public function v2()
    {
        $this->load->view('store/index-2');
    }

    public function v3()
    {
        $this->load->view('store/index-3');
    }

    public function v4()
    {
    
        $this->load->view('store/index-4');
    }        

    public function shop()
    {
        $data['title'] = 'Shop';
        $user_id = $this->session->user_id;
        $name=$this->session->name;
        $cat_id=$_GET['cat_id'];
        $brand_id=$_GET['brand'];
        //print_r($cat_id);exit();
        //debug_log("this is the catid".$cat_id);
        $subcatid=$_GET['subcatid'];
        $parent_cat=$_GET['par_cat'];
        $selected_option=$_POST['option_value'];
        if($selected_option){
        }
        if($cat_id && $subcatid && $parent_cat)
        {
          $this->db->select('id,vendor_id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst,qty,discount,pv')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
        }
        else if($cat_id)
        {
          $this->db->select('id, prod_name, vendor_id,prod_price,image,category,brand,prod_desc,gst,qty,discount,pv')->order_by('id', 'ASC')->where(array('category' => $cat_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
          //where('category',$cat_id);
          //print_r($data);exit();
        }
        else if($subcatid)
        {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount,pv')->order_by('id', 'ASC')->where(array('sub_category' =>$subcatid,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
        }
        else if($brand_id)
        {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount')->order_by('id', 'ASC')->where(array('brand' =>$brand_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
        }
        else
        {
          $this->db->select('id, prod_name,vendor_id,prod_price,image,category,brand,prod_desc,gst,qty,discount,pv')->order_by('id', 'ASC')->where(array('status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
          //print_r($data);exit();
        }
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['category']=$this->db->get('product_categories')->result_array();

        $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
        $data['subcategory']=$this->db->get('product_sub_category')->result_array();

        $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
        $data['brand']=$this->db->get('brands')->result_array();

        $this->db->order_by('id', 'RANDOM');
        $this->db->limit(2);
        $query = $this->db->get('product');
        $data['random_product']=$query->result_array();

        $this->db->order_by('prod_name', 'RANDOM');
        $this->db->limit(1);
        $query = $this->db->get('product');
        $data['footer_product']=$query->result_array();

        $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_banner');
        $data['shop_banner']=$this->db->get('store_images')->result_array();

        $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
        $data['shop_bannerleft']=$this->db->get('store_images')->result_array();

        //vendor details
        $this->db->select('id,vendor_id,name,company_name,email,phone,status')->where('status', 'Active');
        $data['vendor_detail']=$this->db->get('vendor')->result_array();
        $this->load->view('store/shop', $data);
    }

    public function search_old()
    {
      $data['title'] = 'Search';
      $user_id = $this->session->user_id;
      $name=$this->session->name;
      $this->db->order_by('id', 'RANDOM');
      $this->db->limit(2);
      $query = $this->db->get('product');
      $data['random_product']=$query->result_array();

      $this->db->order_by('prod_name', 'RANDOM');
      $this->db->limit(1);
      $query = $this->db->get('product');
      $data['footer_product']=$query->result_array();

      $cat_id=$_GET['cat_id'];
      $subcatid=$_GET['subcatid'];
      $parent_cat=$_GET['par_cat'];
      $brand_id=$_GET['brand'];

      if($cat_id && $subcatid && $parent_cat)
      {
       $this->db->select('id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid,'status' =>"Selling"));
       $data['data']=$this->db->get('product')->result_array();
       //print_r($data);die();
      }
      else if($cat_id)
      {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where('category',$cat_id);
       $data['data']=$this->db->get('product')->result_array();
      }
      else if($brand_id)
      {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount')->order_by('id', 'ASC')->where(array('brand' =>$brand_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
      }
      else
      {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC');
       $data['data']=$this->db->get('product')->result_array();
      }

      $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
      $data['category']=$this->db->get('product_categories')->result_array();
      print_r($data['category']);exit();

      $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
      $data['subcategory']=$this->db->get('product_sub_category')->result_array();

      $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
      $data['brand']=$this->db->get('brands')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_banner');
      $data['shop_banner']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
      $data['shop_bannerleft']=$this->db->get('store_images')->result_array();

      //$data['data'] = $this->db_model->select_multi('*', 'member_profile', array('userid' => $this->session->user_id));
      $this->load->view('store/search',$data);
    }
    
     public function shopping_cart()
    {
      $data['title'] = 'Online Shopping Cart';
     $user_id = $this->session->user_id;
     $this->db->order_by('id', 'RANDOM');
     $this->db->limit(2);
     $query = $this->db->get('product');
     $data['random_product']=$query->result_array();
      
     $this->db->order_by('prod_name', 'RANDOM');
     $this->db->limit(1);
     $query = $this->db->get('product');
     $data['footer_product']=$query->result_array();

     $cat_id=$_GET['cat_id'];
     $subcatid=$_GET['subcatid'];
     $parent_cat=$_GET['par_cat'];
     $brand_id=$_GET['brand'];

     if($cat_id && $subcatid && $parent_cat)
     {
       $this->db->select('id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid,'status' =>"Selling"));
       $data['data']=$this->db->get('product')->result_array();
       //print_r($data);die();
     }
     else if($cat_id)
     {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where('category',$cat_id);
       $data['data']=$this->db->get('product')->result_array();
     }
     else if($brand_id)
        {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount')->order_by('id', 'ASC')->where(array('brand' =>$brand_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
        }
     else
     {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC');
       $data['data']=$this->db->get('product')->result_array();
     }
     $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
     $data['category']=$this->db->get('product_categories')->result_array();

     $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
     $data['subcategory']=$this->db->get('product_sub_category')->result_array();

     $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
     $data['brand']=$this->db->get('brands')->result_array();

     $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_banner');
     $data['shop_banner']=$this->db->get('store_images')->result_array();
 
     $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
     $data['shop_bannerleft']=$this->db->get('store_images')->result_array();
  
     $this->load->view('store/shopping-cart',$data);
    }

    public function shop_list()
    {
      $data['title'] = 'Shop List';
      $user_id = $this->session->user_id;
      $name=$this->session->name;
      $cat_id=$_GET['cat_id'];
      $subcatid=$_GET['subcatid'];
      $parent_cat=$_GET['par_cat'];
      $brand_id=$_GET['brand'];
      $vendor_id=$_GET['vid'];

      $this->db->order_by('id', 'RANDOM');
      $this->db->limit(2);
      $query = $this->db->get('product');
      $data['random_product']=$query->result_array();

      $this->db->order_by('prod_name', 'RANDOM');
      $this->db->limit(1);
      $query = $this->db->get('product');
      $data['footer_product']=$query->result_array();

      if($vendor_id!="")
      {
        $this->db->select('id,prod_name,prod_price,product_cost,image,image2,image3,image4,image5,category,sub_category,brand,prod_desc,gst,qty,discount,vendor_id')->order_by('id', 'ASC')->where(array('vendor_id' => $vendor_id));
        $data['vendor_product']=$this->db->get('product')->result_array();
        $this->db->select('prod_name')->where('vendor_id',$vendor_id);
        $data['vp']=$this->db->get('product')->result_array();
        //print_r($vp);die();
      }
      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shoplist_banner');
      $data['shoplist_banner']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shoplist_bannerleft');
      $data['shoplist_bannerleft']=$this->db->get('store_images')->result_array();

      if($cat_id && $subcatid && $parent_cat)
      {
       $this->db->select('id,prod_name,prod_price,product_cost,image,category,sub_category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid));
       $data['data']=$this->db->get('product')->result_array();
      }
      else if($cat_id)
      {
       $this->db->select('id, prod_name,prod_price,product_cost,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where('category',$cat_id);
       $data['data']=$this->db->get('product')->result_array();
      //print_r($data['data']);die();
      }
      else if($subcatid)
      {
       $this->db->select('id, prod_name,prod_price,product_cost,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where('sub_category',$subcatid);
       $data['data']=$this->db->get('product')->result_array();
      }
      else if($brand_id)
      {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount')->order_by('id', 'ASC')->where(array('brand' =>$brand_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
      }
      else{
      $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst')->order_by('id', 'ASC');
      $data['data']=$this->db->get('product')->result_array();
      }
      $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
      $data['category']=$this->db->get('product_categories')->result_array();
      
      $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
      $data['subcategory']=$this->db->get('product_sub_category')->result_array();

      $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
      $data['brand']=$this->db->get('brands')->result_array();
      
      $this->load->view('store/shop-list',$data);
    }

    public function version_one()
    {
        $this->load->view('store/index');   
    }

    public function blog()
    {
        $this->load->view('store/blog');
    }

    public function blog_details()
    {
        $this->load->view('store/blog-details');
    }

    public function checkout()
    {   
        $data['title'] = 'Checkout';

        $user_id = $this->session->user_id;
        $name=$this->session->name;
        $data['member_data'] = $this->db_model->select_multi('name, email, phone, address', 'member', array('id' => $user_id));
        $data['billing_address']=$this->db_model->select_multi('b_name, b_email, b_phone, b_address,b_state,b_city,b_zipcode', 'shipping_address', array('userid' => $user_id));
        $data['shipping_address']=$this->db_model->select_multi('s_name, s_email, s_phone, s_address,s_state,s_city,s_zipcode', 'shipping_address', array('userid' => $user_id));
        $this->db->order_by('id', 'RANDOM');
        $this->db->limit(2);
        $query = $this->db->get('product');
        $data['random_product']=$query->result_array();

        $this->db->order_by('prod_name', 'RANDOM');
        $this->db->limit(1);
        $query = $this->db->get('product');
        $data['footer_product']=$query->result_array();

        $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
        $data['shop_bannerleft']=$this->db->get('store_images')->result_array();

        $data['ewallet_amount']= $this->db_model->select('balance', 'wallet', array('userid' => $user_id));
        $data['repurchase_amount']=$this->db_model->select('balance', 'other_wallet', array('userid' => $user_id,'type'=>"Repurchase"));
        //print_r($repurchase_amount);die();
        $cat_id=$_GET['cat_id'];
        $subcatid=$_GET['subcatid'];
        $parent_cat=$_GET['par_cat'];
        $brand_id=$_GET['brand'];

        if($cat_id && $subcatid && $parent_cat)
        {
          $this->db->select('id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst,discount,pv')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid));
          $data['data']=$this->db->get('product')->result_array();
         }
         else if($cat_id)
         {
          $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,discount,pv')->order_by('id', 'ASC')->where('category',$cat_id);
          $data['data']=$this->db->get('product')->result_array();
         }
         else if($brand_id)
         {
          $this->db->select('id,vendor_id, prod_name,prod_price,image,category,brand,prod_desc,gst,qty,discount')->order_by('id', 'ASC')->where(array('brand' =>$brand_id,'status' => "Selling"));
          $data['data']=$this->db->get('product')->result_array();
        }
        else
        {
         $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,discount,pv')->order_by('id', 'ASC');
         $data['data']=$this->db->get('product')->result_array();
        }
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['category']=$this->db->get('product_categories')->result_array();

        $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
        $data['subcategory']=$this->db->get('product_sub_category')->result_array();

        $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
        $data['brand']=$this->db->get('brands')->result_array();
        $this->load->view('store/checkout', $data);
    }

    public function contact()
    {   
        $data['title'] = 'Contact';

        $user_id = $this->session->user_id;
        $name=$this->session->name;
        $cat_id=$_GET['cat_id'];
        $subcatid=$_GET['subcatid'];
        $parent_cat=$_GET['par_cat'];
        $selected_option=$_POST['option_value'];
        if($selected_option){
    
        }
        if($cat_id && $subcatid && $parent_cat)
        {
          $this->db->select('id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst')->order_by('id', 'ASC')->where(array('category' => $cat_id,
                                   'sub_category' =>$subcatid));
           $data['data']=$this->db->get('product')->result_array();
        }
        else if($cat_id)
        {
          $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst')->order_by('id', 'ASC')->where('category',$cat_id);
          $data['data']=$this->db->get('product')->result_array();
        }
        else if($subcatid)
        {
         $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst')->order_by('id', 'ASC')->where('sub_category',$subcatid);
         $data['data']=$this->db->get('product')->result_array();
        }
        else
        {
          $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst')->order_by('id', 'ASC');
          $data['data']=$this->db->get('product')->result_array();
        }
        $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
        $data['category']=$this->db->get('product_categories')->result_array();

        $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
        $data['subcategory']=$this->db->get('product_sub_category')->result_array();

        $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
        $data['brand']=$this->db->get('brands')->result_array();

        $this->db->order_by('id', 'RANDOM');
        $this->db->limit(2);
        $query = $this->db->get('product');
        $data['random_product']=$query->result_array();
        
        $this->db->order_by('prod_name', 'RANDOM');
        $this->db->limit(1);
        $query = $this->db->get('product');
        $data['footer_product']=$query->result_array();

        $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_banner');
        $data['shop_banner']=$this->db->get('store_images')->result_array();

        $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
        $data['shop_bannerleft']=$this->db->get('store_images')->result_array();
 
        $this->load->view('store/contact', $data);
    }
   
    public function my_account()
    {
     $this->load->view('store/my-account');
    }

    public function single_product()
    {
     $data['title'] = 'View Product';
     $this->load->helper('url');
     $currentURL = current_url();
     $user_id = $this->session->user_id;
     $product_id=$_GET['id'];
     $data['product_id']=$_GET['id'];
     $newURL=$currentURL . "?id=".$product_id;

     session_start();
     $_SESSION['sponsor_id']=$_GET['uid'];
     
     $data['data'] = $this->db->query("SELECT prod_name,vendor_id,prod_price,image,image2,image3,image4,image5, category,brand,prod_desc,gst,discount,pv, CASE WHEN qty = -1 THEN 10000 ELSE qty END as qty 
     from product where status = 'Selling' AND id = ".$product_id." order by id ASC")->result_array();
     
     $this->db->order_by('id', 'RANDOM');
     $this->db->limit(2);
     $query = $this->db->get('product');
     $data['random_product']=$query->result_array();

     $this->db->order_by('prod_name', 'RANDOM');
     $this->db->limit(1);
     $query = $this->db->get('product');
     $data['footer_product']=$query->result_array();

     $this->load->view('store/single-product',$data);
    }

    public function wishlist()
    {
      $this->load->view('store/wishlist');
    } 
    
    public function search($keyword='')
    {
      //print_r("search");die();
         $query = $this->db->get_where('product', array(//making selection
            'prod_name' => $id));
         $this->db->like('prod_name',$keyword);
         $query_product  =   $this->db->get('product');
         
         if ($query_product->num_rows() > 0) {
          
            return $query_product->result();
         }
         else 
         {
            $this->db->like('cat_name',$keyword);
            $query_cat  =   $this->db->get('product_categories');
            $cat_details= $query_cat->result();
            
            if ($query_cat->num_rows() > 0) 
            {
                foreach($cat_details as $c){
                $prod_detail=$this->db->get_where('product', array('category' => $c->cat_id));
                //print_r($prod_detail->result());die();
                return $prod_detail->result();
                }
            }
            else
            {
                $this->db->like('sub_cat_name',$keyword);
                $query_sub_cat  =   $this->db->get('product_sub_category');
                $sub_cat_details= $query_sub_cat->result();
                //print_r($sub_cat_details);die();
                if($query_sub_cat->num_rows()>0){
                    foreach($sub_cat_details as $s)
                    {
                      $prod_detail=$this->db->get_where('product', array('sub_category' => $s->sub_cat_id));
                      //print_r($prod_detail->result());die();
                      return $prod_detail->result();
                    } 
                }
                else
                {
                    return FALSE;
                }
             }

         }

    }


    public function search_keyword()
    {
      $this->load->model('earning');
      $keyword    =   $this->input->post('keyword');
      $data['results']    =   $this->search($keyword);
      $data['title'] = 'Search Products';
      
      $user_id = $this->session->user_id;
      $name=$this->session->name;
      $this->db->order_by('id', 'RANDOM');
      $this->db->limit(2);
      $query = $this->db->get('product');
      $data['random_product']=$query->result_array();

      $this->db->order_by('prod_name', 'RANDOM');
      $this->db->limit(1);
      $query = $this->db->get('product');
      $data['footer_product']=$query->result_array();

      $cat_id=$_GET['cat_id'];
      $subcatid=$_GET['subcatid'];
      $parent_cat=$_GET['par_cat'];
      if($cat_id && $subcatid && $parent_cat)
      {
       $this->db->select('id,prod_name,prod_price,image,category,sub_category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where(array('category' => $cat_id,'sub_category' =>$subcatid,'status' =>"Selling"));
       $data['data']=$this->db->get('product')->result_array();
       //print_r($data);die();
      }
      else if($cat_id)
      {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC')->where('category',$cat_id);
       $data['data']=$this->db->get('product')->result_array();
      }
      else
      {
       $this->db->select('id, prod_name,prod_price,image,category,brand,prod_desc,gst,discount,vendor_id')->order_by('id', 'ASC');
       $data['data']=$this->db->get('product')->result_array();
      }

      $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
      $data['category']=$this->db->get('product_categories')->result_array();
      
      $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
      $data['subcategory']=$this->db->get('product_sub_category')->result_array();

      $this->db->select('brand_id, brand_name')->order_by('brand_name', 'ASC');
      $data['brand']=$this->db->get('brands')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_banner');
      $data['shop_banner']=$this->db->get('store_images')->result_array();

      $this->db->select('id, banner_name, prod_id, banner_desc, image, flag')->where('flag', 'shop_bannerleft');
      $data['shop_bannerleft']=$this->db->get('store_images')->result_array();

      $this->load->view('store/search',$data);
    } 
  
    public function place_order()
    {
      $data['title'] = 'Place Order';
      session_start();
      //form elements - billing details
      $fname=$_POST['fname'];
      //print_r($fname);die();
      $address=$_POST['address'];
      $email=$_POST['email'];
      $phone=$_POST['phone'];
      $city=$_POST['city'];
      $state=$_POST['state'];
      $zip=$_POST['zip'];
      $country=$_POST['country'];
      $phone=$_POST['phone'];
      $fax=$_POST['fax'];
      $trans_id=$_POST['transactionid'];
      $address_book=$fname . " ".$lname . "<br/>" . $company . "<br/>". $address . "<br/>".$city. "<br/>" .$state. "<br/>".$country ."<br/>".$zip;

      $fname_new=$_POST['fname_new'];
      $address_new=$_POST['address_new'];
      $city_new=$_POST['city_new'];
      $state_new=$_POST['state_new'];
      $zip_new=$_POST['zip_new'];
      $payment=$_POST['payment_mode'];
      $trans_id=$_POST['transactionid'];
      $email_new=$_POST['email_new'];
      $phone_new=$_POST['phone_new'];
      $address_book_new=$fname_new . " ".$lname_new . "<br/>" . $company_new . "<br/>". $address_new . "<br/>".$city_new. "<br/>" .$state_new. "<br/>".$country_new ."<br/>".$zip_new. "<br/>".$email_new."<br/>".$phone_new;
      $address_book=strip_tags($address_book_new);
      $grandtotal=$_SESSION["grand_total"];
      $user_id = $this->session->user_id;
      $this->db->order_by('id', 'RANDOM');
      $this->db->limit(2);
      $query = $this->db->get('product');
      $random_product=$query->result_array();
      //print_r($user_id);die();
      $name=$this->session->name;

      $this->db->order_by('prod_name', 'RANDOM');
      $this->db->limit(1);
      $query = $this->db->get('product');
      $footer_product=$query->result_array();

      $this->db->select('cat_id, cat_name')->order_by('cat_name', 'ASC');
      $category=$this->db->get('product_categories')->result_array();

      $this->db->select('sub_cat_id, sub_cat_name')->order_by('sub_cat_name', 'ASC');
      $subcategory=$this->db->get('product_sub_category')->result_array();
      $ewallet_amount= $this->db_model->select('balance', 'wallet', array('userid' => $user_id));
      $repurchase_amount=$this->db_model->select('balance', 'other_wallet', array('userid' => $user_id,'type'=>"Repurchase"));

      $new_repurchase_amount = 0;
      if($payment=="repurchase")
      {
        $new_repurchase_amount=$repurchase_amount-$grandtotal;
        //print_r($new_repurchase_amount);die();
        $data = array('balance'=> $new_repurchase_amount,);
        $this->db->where(array('userid'=>$user_id, 'type'=>"Repurchase"));
        $this->db->update('other_wallet', $data);
      }
      elseif($payment=="Ewallet")
      {
       $new_ewallet_balance=$ewallet_amount-$grandtotal;
       $data = array('balance'=> $new_ewallet_balance,);
       $this->db->where('userid', $user_id);
       $this->db->update('wallet', $data);
       wallet_log($this->db->last_query()); 
      }
      else
      {
       $new_ewallet_balance=$ewallet_amount;
       $data = array('balance'=> $new_ewallet_balance,);
       $this->db->where('userid', $user_id);
       $this->db->update('wallet', $data);
       wallet_log($this->db->last_query());
      }
      if(!empty($_SESSION["shopping_cart"]))
      {
          foreach ($_SESSION["shopping_cart"] as $item):
           $netprice=($item['item_price']-($item['item_price']*($item['item_discount']/100)));
           $cost=($item['item_price']-($item['item_price']*($item['item_discount']/100)));
           $tax=$netprice - ($netprice/(1+$item["item_tax"]/100));
           $total_cost=($item['item_price']-($item['item_price']*($item['item_discount']/100)))*$item["item_quantity"];

           $prod_name = $item['item_name'];
            if(config_item('enable_variation')=="Yes" && $item['item_variant_name'])
            {
                foreach ($item['item_variant_name'] as $vkey=>$value)
                {
                  $prod_name = $prod_name.'<br>'.$value.'-'.$item['item_variant_value'][$vkey];
                }
                  //echo $prod_name;
            }
            //debug_log($prod_name);

           $item_variants = $item_variant_name.' - '.$item_variant_value;
          
              $array = array(
                    'product_id'=> $item['item_id'],
                    'userid'    => $this->session->user_id,
                    'name'      => $prod_name,
                    'qty'       => $item['item_quantity'],  
                    'cost'      => round($cost,2),
                    'tax'       => round($tax,2),
                    'total_cost'=> round($total_cost,2),
                    'date'      => date('Y-m-d H:i:s'),
                    'vendor_id' => $item["item_vendor_id"],
                    'address'   =>$address_book_new,
                    'payment'   =>$payment,
                    'bank_trans_id'=>$trans_id,
                );
                $this->db->insert('product_sale', $array);
                //debug_log($this->db->last_query());

                $order_detail  = $this->db_model->select_multi('*', 'product_sale', array('id' => $this->db->insert_id()));

                $avl_qty = $this->db->query("SELECT CASE WHEN qty = -1 THEN 0 ELSE qty END as available_qty 
                     from product where id = ".$item['item_id'])->result_array()[0]['available_qty'];

                if($avl_qty >= $item['item_quantity'])
                {
                  $new_qty=$avl_qty-$item['item_quantity'];
                  $array=array('qty' => $new_qty);
                  $this->db->where('id', $item['item_id']);
                  $this->db->update('product', $array);
                }

                ############ INVOICE ENTRY #################################

                $prod_details = $this->db_model->select_multi('prod_name, prod_price, gst,discount, qty, sold_qty', 'product', array('id' => $order_detail->product_id));

                $member_detail = $this->db_model->select_multi('name, address, phone, topup', 'member', array('id' => $order_detail->userid));

                $dd = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $order_detail->userid));

                $gettop = $member_detail->topup + ($order_detail->cost*$order_detail->qty);
                $topup  = array(
                    'topup' => $gettop,
                );
                $this->db->where('id', $order_detail->userid);
                $this->db->update('member', $topup);
                
                $invoice_name = $prod_details->prod_name;
                $user_id      = $order_detail->userid;
                $vendor_id    = $order_detail->vendor_id;
                $invoice_date = date('Y-m-d H:i:s');
                $user_type    = 'Member';
                $company_add  = config_item('company_address') . "<br/>" . config_item('company_city') .', ' . config_item('company_state') .' - ' . config_item('company_zipcode') . ', ' . config_item('company_country');
                $ship_adress  = $dd->s_name. "<br/>" .$dd->s_phone. "<br/>" .$dd->s_address. "<br/>" .$dd->s_city. "<br/>" .$dd->s_state. "-" .$dd->s_zipcode;
                $bill_add  = $dd->b_name. "<br/>" .$dd->b_phone. "<br/>" .$dd->b_address. "<br/>" .$dd->b_city. "<br/>" .$dd->b_state. "-" .$dd->b_zipcode;
                $total_amt    = $order_detail->cost*$order_detail->qty;
                $paid_amt     = $order_detail->cost*$order_detail->qty;
                $prod_detail  = $this->db_model->select_multi('*', 'product', array('id' => $order_detail->product_id));
                $item_name    = $prod_detail->prod_name;

                $price        = round($prod_detail->prod_price*(1-($prod_detail->discount/100)) / (1 + $prod_detail->gst / 100), 2);
                //$p_w_tax        = round($prod_detail->prod_price / (1 + $prod_detail->gst / 100), 2);
                $tax_rate     = $prod_detail->gst;
                $tax          = round($order_detail->cost - $price,2);
                //$tax=$order_detail->tax;
                $qty          = $order_detail->qty;

                $array  = array($item_name => $price);
                $array2 = array($item_name => $tax);
                $array3 = array($item_name => $qty);

                $array  = serialize($array);
                $array2 = serialize($array2);
                $array3 = serialize($array3);

                $params = array(
                    'order_id'         => $order_detail->id,
                    'invoice_name'     => $invoice_name,
                    'userid'           => $user_id,
                    'vendor_id'        =>$vendor_id,
                    'invoice_data'     => $array,
                    'invoice_data_tax' => $array2,
                    'invoice_data_qty' => $array3,
                    'company_address'  => $company_add,
                    'bill_to_address'  => $bill_add,
                    'ship_to_address'  => $ship_adress,
                    'total_amt'        => $total_amt,
                    'paid_amt'         => $paid_amt,
                    'date'             => $invoice_date,
                    'user_type'        => $user_type,
                );
                $this->db->insert('invoice', $params);
            ########## END ENTRY #######################################
          endforeach;
      }
      session_unset(); 
      $newdata=array('user_id' => $user_id,'name' => $name);
      $this->session->set_userdata($newdata);
      $data['new_ewallet_balance']        = $new_ewallet_balance;
      $data['new_repurchase_amount']        = $new_repurchase_amount;
      $this->load->view('store/place_order', $data);
    }

    #### Admin Functions

    public function create_order()
    {
      $pd = $this->db_model->select_multi('*','product',array('id'=>$this->input->post('prod_id')));
      $prod_id = $pd->id;
      $user_id = $this->input->post('user_id');
      $user_name = $this->input->post('user_id');
      $cost = $this->input->post('item_price');
      $qty = $this->input->post('item_qty');
      $total_cost = $cost*$qty;
      $tax = $total_cost*$pd->gst/100;
      $pay_mode = $this->input->post('pay_mode');
      
      $prod_name = $pd->prod_name;

      $wallet_balance = $this->db_model->select('balance','wallet',array('userid'=>$user_id));

      if(($pay_mode =='Ewallet') && ($wallet_balance<$total_cost)){
        $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">'.$user_id.' doesnot have sufficient wallet balance of '.config_item('currency').$total_cost.'.</div>');
                redirect('accounting/invoices');
      }

      $array = array(
            'product_id'=> $prod_id,
            'userid'    => $user_id,
            'name'      => $prod_name.' - '.$this->input->post('sale_note'),
            'qty'       => $qty,  
            'cost'      => round($cost,2),
            'tax'       => round($tax,2),
            'total_cost'=> round($total_cost,2),
            'date'      => date('Y-m-d H:i:s'),
            'vendor_id' => 0,
            'payment'   => $pay_mode,
        );

        $this->db->insert('product_sale', $array);
        //debug_log($this->db->last_query());

        $order_details  = $this->db_model->select_multi('*', 'product_sale', array('id' => $this->db->insert_id()));

        $avl_qty = $this->db->query("SELECT CASE WHEN qty = -1 THEN 0 ELSE qty END as available_qty 
                     from product where id = ".$item['item_id'])->result_array()[0]['available_qty'];

        if($avl_qty >= $qty)
        {
          $new_qty=$avl_qty-$qty;
          $array=array('qty' => $new_qty);
          $this->db->where('id', $item['item_id']);
          $this->db->update('product', $array);
        }

        if($pay_mode =='Ewallet'){

            $arra = array('balance' => ($wallet_balance - $total_cost));
            $this->db->where('userid', $user_id);
            $this->db->update('wallet', $arra);
            wallet_log($this->db->last_query());

            $this->earning->add_deduction($user_id, 'admin', $total_cost, 'Product Purchase', $prod_name,$prod_id, 'Ewallet', $order_details->id);

        }

        ############ INVOICE ENTRY #################################

        $member_detail = $this->db_model->select_multi('name, address, phone, topup', 'member', array('id' => $order_details->userid));

        $dd = $this->db_model->select_multi('*', 'shipping_address', array('userid' => $order_details->userid));

        $gettop = $member_detail->topup + ($order_details->cost*$order_details->qty);
        $topup  = array('topup' => $gettop);
        $this->db->where('id', $order_details->userid);
        $this->db->update('member', $topup);
        
        $invoice_name = $prod_name.' - '.$this->input->post('sale_note');
        $user_id      = $order_details->userid;
        $vendor_id    = $order_details->vendor_id;
        $invoice_date = date('Y-m-d H:i:s');
        $user_type    = 'Member';
        $company_add  = config_item('company_address') . "<br/>" . config_item('company_city') .', ' . config_item('company_state') .' - ' . config_item('company_zipcode') . ', ' . config_item('company_country');
        $ship_adress  = $dd->s_name. "<br/>" .$dd->s_phone. "<br/>" .$dd->s_address. "<br/>" .$dd->s_city. "<br/>" .$dd->s_state. "-" .$dd->s_zipcode;
        $bill_add  = $dd->b_name. "<br/>" .$dd->b_phone. "<br/>" .$dd->b_address. "<br/>" .$dd->b_city. "<br/>" .$dd->b_state. "-" .$dd->b_zipcode;
        $total_amt    = $order_details->total_cost;
        $paid_amt     = $order_details->total_cost;
        $item_name    = $prod_name.' - '.$this->input->post('sale_note');

        $price        = round($cost/(1 + $pd->gst/100), 2);
        //$p_w_tax        = round($pd->prod_price / (1 + $pd->gst / 100), 2);
        $tax_rate     = $pd->gst;
        $tax          = round($order_details->cost - $price,2);
        //$tax=$order_details->tax;
        $qty          = $order_details->qty;

        $array  = array($item_name => $price);
        $array2 = array($item_name => $tax);
        $array3 = array($item_name => $qty);

        $array  = serialize($array);
        $array2 = serialize($array2);
        $array3 = serialize($array3);

        $params = array(
            'order_id'         => $order_details->id,
            'invoice_name'     => $invoice_name,
            'userid'           => $user_id,
            'vendor_id'        =>$vendor_id,
            'invoice_data'     => $array,
            'invoice_data_tax' => $array2,
            'invoice_data_qty' => $array3,
            'company_address'  => $company_add,
            'bill_to_address'  => $bill_add,
            'ship_to_address'  => $ship_adress,
            'total_amt'        => $total_amt,
            'paid_amt'         => $paid_amt,
            'date'             => $invoice_date,
            'user_type'        => $user_type,
        );
        $this->db->insert('invoice', $params);

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success"> Order is successfully created </div>');
        redirect('product/pending-orders');

    }

}