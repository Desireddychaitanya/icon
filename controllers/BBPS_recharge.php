<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BBPS_recharge extends MY_Controller
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
        $this->load->library('pagination');
        // Load Stripe library 
        $this->load->library('stripe_lib'); 
        $this->load->model('payments_model');
        if($this->session->role =='customer'){
            $this->config->set_item("member",config_item('member_customer'));
        }else{
            $this->config->set_item("member",config_item('member_affiliate'));
        }
    }

    public function index()
    {
      $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://sandbox.cou.setu.co/api/bills/uat/biller-categories',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'axis-channel-id: SAISEVAK',
    'axis-client-id: 57a2d0c1-5d26-4d80-987a-4793e7d7987a',
    'axis-client-secret: 528b16b9-4f3c-497e-8539-6cfcd41810f4',
    'axis-encryption-key: axisbankaxisbank',
    'axis-salt-key: axisbankaxisbank',
    'axis-channel-password: 5f78716f-8789-4863-8127-1b67bd6b2848',
    'axis-body-channel-id: 664'
  ),
));

$outputdata = curl_exec($curl);

curl_close($curl);

$myArray = json_decode($outputdata,true);
//$myInnerArray = $myArray['statusCode'];
$myInnerArray = $myArray['data'];

foreach($myInnerArray as $element){
  $pid = $element['categoryCode'];
  $styles = $element['categoryName'];
  //echo $pid.': '.$styles.'<br />';
}

$data['myInnerArray']=$myInnerArray;

//var_dump($myInnerArray);
 // var_dump($outputdata);
 // var_dump($values);
 
 // $result = $values[3]['value'];
 // $obj = json_decode($values);

//echo $response;
//$dd=$response->data;

//var_dump($obj);

      $this->load->view('templates/bbps_recharge/'.config_item('recharge_theme'), $data );
    }

    

    public function getBillerlist($param='')
    {

      $postData='{"categoryCode": "'.$param.'"}';
      $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://sandbox.cou.setu.co/api/bills/uat/biller-list',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$postData,
  CURLOPT_HTTPHEADER => array(
    'axis-channel-id: SAISEVAK',
    'axis-client-id: 57a2d0c1-5d26-4d80-987a-4793e7d7987a',
    'axis-client-secret: 528b16b9-4f3c-497e-8539-6cfcd41810f4',
    'axis-encryption-key: axisbankaxisbank',
    'axis-salt-key: axisbankaxisbank',
    'axis-channel-password: 5f78716f-8789-4863-8127-1b67bd6b2848',
    'axis-body-channel-id: 664',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$myArray = json_decode($response,true);
$myInnerArray = $myArray['data'];

$response='<label><b>Select Biller</b></label><br /><select class="form-control" id="exampleFormControlSelect"><option>Select Biller </option>';
foreach($myInnerArray as $element){
  $response=$response.'<option value="'.$element['billerId'].'">'.$element['name'].'</option>';
}
$response=$response.'<select>';
echo $response;
}

public function getBillerFields($param1='',$param2='')
{

  $postData='{
    "billerId": "'.$param1.'",
    "categoryCode": "'.$param2.'"
  }';

  $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://sandbox.cou.setu.co/api/bills/uat/biller-fields',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$postData,
  CURLOPT_HTTPHEADER => array(
    'axis-channel-id: SAISEVAK',
    'axis-client-id: 57a2d0c1-5d26-4d80-987a-4793e7d7987a',
    'axis-client-secret: 528b16b9-4f3c-497e-8539-6cfcd41810f4',
    'axis-encryption-key: axisbankaxisbank',
    'axis-salt-key: axisbankaxisbank',
    'axis-channel-password: 5f78716f-8789-4863-8127-1b67bd6b2848',
    'axis-body-channel-id: 664',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$myArray = json_decode($response,true);
$myInnerArray = $myArray['data'];

$response='<label><b>Mobile Number</b></label><br /><input type="text" class="form-control" placeholder="Enter Mobile number" required="required" id="mobileNumber" name="mobileNumber"></input><br />';
foreach($myInnerArray as $element){
$response=$response.'<label><b>'.$element['name'].'</b></label><br /><input type="text" class="form-control" placeholder="Enter '.$element['name'].'" required="required" id="loannumber" name="'.$element['name'].'"></input>';
}
echo $response;
}

public function getBillFetchRequest($param1='',$param2='')
{

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://sandbox.cou.setu.co/api/bills/uat/bill-fetch-request',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
      "agent": {
          "app": "",
          "channel": "INT",
          "geocode": "",
          "id": "AX01AI06512391457204",
          "ifsc": "",
          "imei": "",
          "ip": "124.170.23.24",
          "mac": "48-4D-7E-CB-DB-6F",
          "mobile": "",
          "os": "",
          "postalCode": "",
          "terminalId": ""
      },
      "billerId": "MAHI00000NATIC",
      "mobileNumber":"'.strval($param1).'",
      "categoryCode": "12",
      "customerParams": [
          {
              "name": "Loan Number",
              "value": '.$param2.'
          }
      ]
  }',
    CURLOPT_HTTPHEADER => array(
      'axis-channel-id: SAISEVAK',
      'axis-client-id: 57a2d0c1-5d26-4d80-987a-4793e7d7987a',
      'axis-client-secret: 528b16b9-4f3c-497e-8539-6cfcd41810f4',
      'axis-encryption-key: axisbankaxisbank',
      'axis-salt-key: axisbankaxisbank',
      'axis-channel-password: 5f78716f-8789-4863-8127-1b67bd6b2848',
      'axis-body-channel-id: 664',
      'Content-Type: application/json'
    ),
  ));
  
  $response = curl_exec($curl);  
  curl_close($curl);
    
  $responseArray = json_decode($response,true);
  $context=$responseArray['data']['context'];

  $this->getFetchedBillRequest($context);
}

public function getFetchedBillRequest($param1='')
{
  $json = array("context" => $param1);
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://sandbox.cou.setu.co/api/bills/uat/get-fetched-bill',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_VERBOSE => true,
          CURLOPT_POST => true,
          CURLINFO_HEADER_OUT => true,
    CURLOPT_POSTFIELDS =>json_encode($json),
    CURLOPT_HTTPHEADER => array(
      'axis-channel-id: SAISEVAK',
      'axis-client-id: 57a2d0c1-5d26-4d80-987a-4793e7d7987a',
      'axis-client-secret: 528b16b9-4f3c-497e-8539-6cfcd41810f4',
      'axis-encryption-key: axisbankaxisbank',
      'axis-salt-key: axisbankaxisbank',
      'axis-channel-password: 5f78716f-8789-4863-8127-1b67bd6b2848',
      'axis-body-channel-id: 664',
      'Content-Type: application/json'
    ),
  ));
  
  $response = curl_exec($curl);
  curl_close($curl);
  $myArray = json_decode($response,true); 
  $myresponse=$myArray['data']['fetchAPIStatus'];
  $i=0;
  if($myresponse==='Active')
  {
    $billResponse='<b>Bill Date : </b>'.$myArray['data']['bill']['billDate'].'<br/><b>Bill Number : </b>'.$myArray['data']['bill']['billNumber'].'<br/><b>Due Date : </b>'.$myArray['data']['bill']['dueDate'].'<br/><b>Bill Amount :  </b>'.$myArray['data']['bill']['amount'].'<br/>';
    echo $billResponse;         
  }
  else
  {
     if($i<6)
     {
     $this->getFetchedBillRequest($param1);
     $i++;
     }
     else
     {
       echo "<p style='color:red'>No Data Found. Please try again later!!!</p>";
     }
  }
}
}