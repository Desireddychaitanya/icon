<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');
        $this->load->library('cart');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
    }

    public function new_purchase()
    {
        $this->db->select('cat_id,cat_name,description,image');
        $data['categories'] = $this->db->get('product_categories')->result();
        $this->db->select('id,prod_name,image,round(prod_price - prod_price*discount/100,2) as prod_price')->where('status', 'Selling')->group_by('id','prod_name','image')->limit(10);
        $data['product_top'] = $this->db->get('product')->result();
        debug_log($this->db->last_query());
        $data['title']       = 'Select a Category Below: ';
        $data['layout']      = 'shop/buy.php';
        $this->load->view(config_item('member'), $data);
    }

    public function show_products()
    {
        $this->db->select('id,prod_name,image,round(prod_price - prod_price*discount/100,2) as prod_price')->
        $this->db->where(array(
                             'status'   => 'Selling',
                             'category' => $this->uri->segment(3),
                         ))->group_by('id','prod_name','image');
        $data['product'] = $this->db->get('product')->result();
        $data['title']   = 'Select a Product Below: ';
        $data['layout']  = 'shop/buy.php';
        $this->load->view(config_item('member'), $data);
    }

    public function buy_2($product_id)
    {
        $product_data = $this->db_model->select_multi('prod_name, qty, gst, round(prod_price - prod_price*discount/100,2) as prod_price', 'product', array('id' => $product_id));

        if ($product_data->qty == 0) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Stock has less qty.</div>');
            redirect('cart/new_purchase');
        }
        $datas                          = array(
            'id'    => $product_id,
            'qty'   => 1,
            'gst'   =>$product_data->gst,
            'price' => $product_data->prod_price,
            'tax_amount' => round($product_data->prod_price - ($product_data->prod_price /(1+$product_data->gst / 100)),2),
            'name'  => $product_data->prod_name,
            'gst'   => round($product_data->prod_price - ($product_data->prod_price /(1+$product_data->gst / 100)),2),
        );
        $this->cart->product_name_rules = '[:print:]';
        $this->cart->insert($datas);
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Item Added to Cart. Want to purchase more ?.</div>');
        redirect('cart/pre_checkout');
    }
    function mypdf()
    {
     $this->load->library('pdf');
     $this->pdf->load_view('mypdf');
     $this->pdf->render();
     $this->pdf->stream("welcome.pdf");
   }

    public function pre_checkout()
    {
        $data['title']  = 'My Cart';
        $data['layout'] = 'shop/pre_checkout.php';
        $this->load->view(config_item('member'), $data);
    }

    public function update()
    {
        $i = 0;
        foreach ($this->cart->contents() as $item) {
            $qty1 = count($this->input->post('qty'));
            for ($i = 0; $i < $qty1; $i++) {
                $data = array(
                    'rowid' => $_POST['rowid'][$i],
                    'qty'   => $_POST['qty'][$i],
                );
                $this->cart->update($data);
            }

        }
        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Cart Updated.</div>');
        redirect('cart/pre_checkout');

    }

    function checkout()
    {
        //$name=$this->input->post('my_name');
        //print_r($name);die();
        $get_balance = $this->db_model->select('balance', 'wallet', array('userid' => $this->session->user_id));
        if ($get_balance < $this->cart->total()) {
            $this->session->set_flashdata('common_flash', '<div class="alert alert-danger">Your Wallet donot have sufficient fund to complete this purchase. Wallet need to have atleast: ' . config_item('currency') . $this->cart->total() . '</div>');
           // redirect('cart/pre_checkout');
            redirect('member/topup-wallet');
        }

        $data = array(
            'balance' => ($get_balance - $this->cart->total()),
        );

        $this->db->where('userid', $this->session->user_id);
        $this->db->update('wallet', $data);
        wallet_log($this->db->last_query());
        if ($cart = $this->cart->contents()) {
            foreach ($cart as $item):

                $array = array(
                    'product_id' => $item['id'],
                    'userid'     => $this->session->user_id,
                    'qty'        => $item['qty'],
                    'cost'       => $item['price'],
                    'tax'        => $item['gst'],
                    'date'       => date('Y-m-d H:i:s'),
                    'payment'    => 'Ewallet',
                    'total_cost' => $item['price'] * $item['qty'], 
                );
                $this->db->insert('product_sale', $array);

                $order_detail  = $this->db_model->select_multi('*', 'product_sale', array('id' => $this->db->insert_id()));

                $avl_qty = $this->db->query("SELECT CASE WHEN qty = -1 THEN 0 ELSE qty END as available_qty 
                     from product where id = ".$item['id'])->result_array()[0]['available_qty'];
                
                if($avl_qty >= $item['qty'])
                {
                  $new_qty=$avl_qty-$item['qty'];
                  $array=array('qty' => $new_qty);
                  $this->db->where('id', $item['id']);
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

        $this->session->set_flashdata('common_flash', '<div class="alert alert-success">Thank you for Purchasing with us</div>');
        redirect('cart/checkout_complete');
    }

    public function checkout_complete()
    {
        $data['title']  = 'Invoice';
        $data['layout'] = 'shop/checkout_complete.php';
        $this->load->view(config_item('member'), $data);
    }

    public function invoices()
    {
        $config['base_url']   = site_url('cart/invoices');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('invoice');
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->from('invoice')->order_by('id', 'DESC')->where('userid',$this->session->user_id )->limit($config['per_page'], $page);
        $data['invoice']    = $this->db->get()->result();
        //print_r($data['invoice']);die();
        $data['title']      = 'Invoices';
        $data['breadcrumb'] = 'Invoices';
        $data['layout']     = 'invoice/invoices.php';
        $this->load->view(config_item('member'), $data);

    }

    public function old_purchase()
    {
        $config['base_url']   = site_url('cart/old_purchase');
        $config['per_page']   = 50;
        $config['total_rows'] = $this->db_model->count_all('product_sale', array('userid' => $this->session->user_id));
        $page                 = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);

        $this->db->select('*')->from('product_sale')
                 ->where('userid', $this->session->user_id)->limit($config['per_page'], $page);

        $data['data']   = $this->db->get()->result();
        $data['title']  = 'My Old Purchases';
        $data['layout'] = 'shop/my_purchases.php';
        $this->load->view(config_item('member'), $data);

    }
}
