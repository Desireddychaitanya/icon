<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Insurance extends MY_Controller
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
    }

   public function index()
    {
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view('templates/single/insurance/index', $data);
    }

     public function twowheeler()
    {
        
        $data['title'] = 'Two Wheeler';
        $data['breadcrumb'] = 'Two Wheeler';
        $cityname=$this->getRto();
        $data['rto']= $this->GetRto();
        //print_r($data['rto']);die();
        $data['model']=$this->makeModelVariant_2W();
        $data['regdate']=$this->input->post('regdate');
        $regdate=$this->input->post('regdate');
        $this->load->view('templates/single/insurance/twowheeler', $data);
    }
     public function fourwheeler()
    {
        
        $data['title'] = 'Four Wheeler';
        $data['breadcrumb'] = 'Four Wheeler';
        $cityname=$this->getRto();
        //print_r($cityname);die();
        $data['rto']= $this->GetRto();
        $data['model']=$this->makeModelVariant_4W();
        $data['regdate']=$this->input->post('regdate');
        $regdate=$this->input->post('regdate');
        $this->load->view('templates/single/insurance/car', $data);
    }
    public function ComparemotorQuotes()
    {
        $data['title'] = 'Compare Motor Quotes';
        $data['breadcrumb'] = 'Compare Motor Quotes';
        $cityname=$this->getRto();
        //print_r($cityname);die();
        $data['cityname']= $this->GetRto();
        $data['model']=$this->makeModelVariant_4W();
        $rto=$this->input->post('rto');
        $rto1=$this->input->post('rto1');
        $regno=$this->input->post('regno');
        $model=$this->input->post('model');
        $model1=$this->input->post('model1');
        $model_data=$this->input->post('model')?$model:$model1;
        $rto_data=$this->input->post('rto')?$rto:$rto1;
        
        $regdate=$this->input->post('regdate');
        $mobile=$this->input->post('mobile');
        $claimmade=$this->input->post('claimmade');
        $previouspolicy_expiry=$this->input->post('previous_policy_expiry');
        $ncb=$this->input->post('ncb');
        $previous_insurer=$this->input->post('previous_insurer');
        //$cc=$this->input->post('mobile');
        $claimmade=$this->input->post('claimmade');
        $data['response']=$this->MoterQuoteRequest($rto_data,$regno,$model_data,$regdate,$mobile,$previouspolicy_expiry,$previous_insurer,$ncb,$claimmade);
        //print_r($data['response']['VPremium']['D']);
        foreach($data['response']->VPremium->D as $val)
        {
            $premiumbreakup[]=$val->PremiumBreakup;
        }
        $data['premiumbreakup']=$premiumbreakup;
        $data['vehicle_type']="4W";
        //print_r($premiumbreakup);
        $data['premiumbreakup']=$premiumbreakup;
        $data['regdate']=$regdate;
        $data['rto']=$rto;
        $data['rto1']=$rto1;
        $data['regno']=$regno;
        $data['model']=$model_data;
        $data['claimmade']=$claimmade;
        //print_r($data['response']);die();
        //print_r($data['cityname']);die();
        $this->load->view('templates/single/insurance/comparemotorquote', $data);
    }

     public function ComparemotorQuotes_2W()
    {
        $data['title'] = 'Compare Motor Quotes';
        $data['breadcrumb'] = 'Compare Motor Quotes';
        $cityname=$this->getRto();
        //print_r($cityname);die();
        $data['cityname']= $this->GetRto();
        //print_r($)
        $data['model']=$this->makeModelVariant_2W();
        $rto=$this->input->post('rto');
        $claimmade=$this->input->post('claimmade');
        $rto1=$this->input->post('rto1');
        $regno=$this->input->post('regno');
        $model=$this->input->post('model');
        $model1=$this->input->post('model1');
        $model_data=$this->input->post('model')?$model:$model1;
        $rto_data=$this->input->post('rto')?$rto:$rto1;
        
        $regdate=$this->input->post('regdate');
        $mobile=$this->input->post('mobile');
        $claimmade=$this->input->post('claimmade');
        $previouspolicy_expiry=$this->input->post('previous_policy_expiry');
        $ncb=$this->input->post('ncb');
        $previous_insurer=$this->input->post('previous_insurer');
        //$cc=$this->input->post('mobile');
        $data['response']=$this->MoterQuoteRequest_2W($rto_data,$regno,$model_data,$regdate,$mobile,$previouspolicy_expiry,$previous_insurer,$ncb,$claimmade);
        $data['regdate']=$regdate;
        $data['rto']=$rto;
         $data['rto1']=$rto1;
        $data['regno']=$regno;
        $data['model']=$model_data;
        $data['vehicle_type']="2W";
        $data['previous_insurer']=$previous_insurer;
        $data['claimmade']=$claimmade;
        //print_r($data['response']);die();
        //print_r($data['cityname']);die();
        $this->load->view('templates/single/insurance/comparemotorquote_2W', $data);
    }
    public function InsuranceBook()
    {
        $data['title'] = 'Insurance Book';
        $data['breadcrumb'] = 'Insurance Book';
    	  $image=$this->input->post('image'); 
        //print_r($image);die();
    	$data['vehicle_type']=$this->input->post('vehicle_type');
      $data['premium']=$this->input->post('premium');
      $data['referenceno']=$this->input->post('referenceno');
      $data['OrderNo']=$this->input->post('OrderNo');
      //print_r($data['referenceno']);exit();
    	$data['regno']=$this->input->post('regno');
      $data['claimmade']=$this->input->post('claimmade');
    	$data['image']=$image;

    	$rto=$this->input->post('rto');
      $rto1=$this->input->post('rto1');
    	$rto_data=$this->input->post('rto')?$rto:$rto1;
      //print_r($rto_data);exit();
    	$rto_array=explode(',',$rto_data);
    	$data['rto_code']=$rto_array[0];
    	$data['city']=$rto_array[1];
    	$data['state']=$rto_array[2];
    	$data['plan']=$this->input->post('plan');
    	$data['NCB']=$this->input->post('ncb');

    	
    	$data['model']=$this->input->post('model');
    	$data['regdate']=$this->input->post('regdate');
    	$data['insurer']=$this->input->post('insurer');
    	$data['IDV']=$this->input->post('IDV');

    	

    	$this->load->view('templates/single/insurance/insurancebook', $data);


    }

    public function MakeProposal_submit()
    {
    	//print_r("inside makeproposalsubmit");
      $user_id = $this->session->user_id;
      $first_name=$this->input->post('firstname');
      $vehicle_type=$this->input->post('vehicle_type');

      $referenceno=$this->input->post('referenceno');
      $OrderNo=$this->input->post('OrderNo');
      $lastname=$this->input->post('lastname');
      $gender=$this->input->post('gender');
      $nominee=$this->input->post('nominee');
      $nominee_relation=$this->input->post('nominee_relation');
      $nominee_age=$this->input->post('nominee_age');
      $pan=$this->input->post('pan');
      $aadhar=$this->input->post('aadhar');
      $marital_status=$this->input->post('marital_status');
      $mobile=$this->input->post('mobile');
      $email=$this->input->post('email');
      $address=$this->input->post('address');
     $pincode=$this->input->post('pincode');
     $city=$this->input->post('city');
     $state=$this->input->post('state');
     $regdate=$this->input->post('regdate');
     $regno=$this->input->post('regno');
     $nominee_relation=$this->input->post('nominee_relation');
     $nominee_age=$this->input->post('nominee_age');
     $nominee_name=$this->input->post('nominee');
     $previouspolicy=$this->input->post('policyno');
     $engineno=$this->input->post('engineno');
     $chassisno=$this->input->post('chassisno');
     $arai_approved=$this->input->post('arai_approved');	
     $manufactured_year=$this->input->post('manufactured_year');
     $financed=$this->input->post('financed');
     $voluntary_deductable=$this->input->post('voluntary_deductable');
     $lpg_value=$this->input->post('lpg_value');
     $premium=$this->input->post('premium');
     $premium_state=$this->input->post('premium_state');
     $premium_city=$this->input->post('premium_city');
     $premium_model=$this->input->post('premium_model');
     $premium_insurer=$this->input->post('premium_insurer');
     $idv=$this->input->post('idv');
     $owner_changed=$this->input->post('ownership_changed');
     $member_aai=$this->input->post('member_aai');
     //$vehicle_type="4W";
     $insurer=$this->db_model->select_multi('*', 'insurance', array('first_name' => $first_name,'last_name'=>$lastname,'email'=>$email,'phone'=>$phone,'PremiumDetails_Model'=>$premium_model,'PremiumDetails_Insurer'=>$premium_insurer,'PremiumDetails_Premium'=>$premium));
     //print_r($insurer);exit();
     if($insurer=="")
     {
        $data = array(
                'first_name' => $first_name,
                'last_name'   => $lastname,
                'gender'        => $gender,
                'phone'          => $mobile,
                'email'       => $email,
                'aadharNo'       => $aadhar,
                'PanNo'       => $pan,
                'Address'       => $address,
                'City'       => $city,
                'regno'    =>$regno,
                'State'       => $state,
                'PinCode'       => $pincode,
                'EngineNo'       => $engineno,
                'ChasisNo'       => $chassisno,
                'PremiumDetails_State'=>$premium_state,
                'PremiumDetails_City'       => $premium_city,
                'PremiumDetails_Manufacturer'       => "",
                'PremiumDetails_Model'       => $premium_model,
                'PremiumDetails_Variants'       => "",
                'PremiumDetails_Premium'       => $premium,
                'PremiumDetails_Insurer'       => $premium_insurer,
                'PremiumDetails_VehicleIDV'       => $idv,
                'PremiumDetails_VehicleType'       => "4W",
                //'status' =>"incompleted",
                'date' => date("Y-m-d H:i:s"),
                'user_id'=>$user_id,
                //'vehicle_type'=>$vehicle_type,
                //'PremiumDetails_VehicleCC'       => $pincode,
                //'PremiumDetails_VehicleIDV'       => $pincode,
                'PremiumDetails_VehicleType'       => $vehicle_type,
             );
            $this->db->insert('insurance', $data);
            debug_log($this->db->last_query());
          }
            $data['premium']=$premium;
            $data['referenceno']=$referenceno;
            $data['OrderNo']=$OrderNo;
            $data['state']=$state;
            $data['city']=$city;
            $data['make']=$premium_model;
            $data['IDV']=$idv;
            $data['insurer']=$premium_insurer;
            $data['reg_date']=$regdate;
            $data['regno']=$regno;
            $data['financed']=$financed;
            $data['arai_approved']=$arai_approved;
            $data['owner_changed']=$owner_changed;
            $data['member_aai']=$member_aai;
            $data['firstname']=$first_name;
            $data['lastname']=$last_name;
            $data['email']=$email;
            $data['mobile']=$mobile;
            $data['aadhar']=$aadhar;
            $data['pan']=$pan;
            $data['nominee_name']=$nominee_name;
            $data['nominee_relation']=$nominee_relation;
            $data['nominee_age']=$nominee_age;
            $data['pincode']=$pincode;

            $this->load->view('templates/single/insurance/proposal_confirmation_motor', $data);
    }
    public function alert()
    {
    	$data['title']="Alert";
    	$this->load->view('templates/single/insurance/alert', $data);
    }

     public function GetRto()
    {
      if (isset($_GET['term'])) 
      //if(1)
      {
     $data = $_GET['term'];
      	//$data ="AP";
      //print_r($data);die();
      $xml= "<?xml version='1.0' encoding='utf-8'?>
<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><GetRTOCodes xmlns='eZeeInsurance'><SubMerchant></SubMerchant><Data>{'GetRTOCodes':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','PortalType':'B2C'},'D':{'State':'".$data."'}}} </Data>
</GetRTOCodes>
</soap:Body>
</soap:Envelope>";

$url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
  $data = trim(curl_exec($ch));
  curl_close($ch);
  //print_r($data);die();

  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
  $result = $values[3]['value'];
  $obj = json_decode($result);
  //print_r($obj);
  //echo '<select>';
  foreach($obj->CITY->D as $val)
  {
    $cityname[]=$val->CityName;
    //echo "\n", $val->CityName;

    //echo '<option value=' . $val->CityName . '>' . $val->CityName . '</option>'; 
    //echo $val->Zone;
  }
  //echo $cityname;
  //print_r($cityname);
  /*foreach($cityname as $s){
  	echo $s. "<br/>";
  }*/
  //echo '</select>';
  //print_r($var);
  echo json_encode($cityname);
  //return($cityname);
  
  
}
}

public function makeModelVariant_2W()
{
  //$model=trim($this->input->get('term'));
  //$model='Hon';
  //print_r($_GET['term']);die();
   if (isset($_GET['term'])) {
   	//if (1) {
   		//$searchTerm ="HERO";
  $searchTerm = $_GET['term'];
  //print_r($searchTerm);die();
   $xml= "<?xml version='1.0' encoding='utf-8'?><soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><MakeModelVariant xmlns='eZeeInsurance'><Data>{'MakeModelVariant':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','Make':'".$searchTerm."','VehicleType':'2W'}}} </Data></MakeModelVariant></soap:Body></soap:Envelope>";

  $url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
  $data = trim(curl_exec($ch));
  //print_r($data);
  curl_close($ch);
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
  $result = $values[3]['value'];
  //print_r($result);exit();
  $obj = json_decode($result);
  //print_r($obj);die();
  foreach($obj->MakeModelVariant->D as $val)
  {
    $m[]=$val->Make ; 
    //echo $val->Make;
  }
  //print_r($m);die();

  
  echo json_encode($m);
  //return($m);
   }
    }

public function makeModelVariant_4W()
{
  //$model=trim($this->input->get('term'));
  //$model="Hon";
   
  if (isset($_GET['term'])) {
  $searchTerm = $_GET['term'];
   $xml= "<?xml version='1.0' encoding='utf-8'?><soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><MakeModelVariant xmlns='eZeeInsurance'><Data>{'MakeModelVariant':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','Make':'".$searchTerm."','VehicleType':'4W'}}} </Data></MakeModelVariant></soap:Body></soap:Envelope>";

  $url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
  $data = trim(curl_exec($ch));
  //print_r($data);exit();
  curl_close($ch);
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
  $result = $values[3]['value'];
  //print_r($result);exit();
  $obj = json_decode($result);
  //print_r($obj);die();
  foreach($obj->MakeModelVariant->D as $val)
  {
    $m[]=$val->Make ; 
    //echo $val->Make;
  }
  //print_r($m);die();

  
  echo json_encode($m);
  //return($m);
   }
    }
    //$this->MoterQuoteRequest($rto_data,$regno,$model_data,$regdate,$mobile,$previouspolicy_expiry,$previous_insurer,$ncb,$claimmade);
    public function MoterQuoteRequest($rto,$regno,$model,$regdate,$mobile,$previouspolicy_expiry='',$previous_insurer='',$ncb='',$claimmade='')
    {
    	/*print_r('previous insurer'.$previous_insurer);
    	print_r('previouspolicy_expiry'.$previouspolicy_expiry);
    	print_r('ncb'.$ncb);
    	print_r('claimmade'.$claimmade);
    	print_r('RTO'.$rto);
    	print_r('regdate'.$regdate);
    	print_r('model'.$model);die();*/

    	$c= date('Y');
        $y= date('Y',strtotime($regdate));
        $vehicleage=$c-$y;
        if($vehicleage<=0){
        	$vehicleage=1;
        }
	    //print_r($vehicleage);
	    //print_r($regdate);die();
	    //print_r($rto);
    	$rto_array=explode(',',$rto);
    	//print_r($rto_array);
    	//print_r($model);
    	$model_array=explode(',',$model);
    	//print_r($model_array[0]);
    	//print_r($model_array[1]);
    	//print_r($model_array[3]);
    	$vehiclecc=explode('CC',$model_array[3]);
    	//print_r($vehiclecc[1]);die();
    	$fueltype=explode('(',$vehiclecc[1]);
    	$fueltype_final=str_replace(')','',$fueltype);
    	
    	$regdate=date('d/m/Y', strtotime($regdate));
    	if($previouspolicy_expiry!=''){
    	$previouspolicy_expiry=date('d/m/Y', strtotime($previouspolicy_expiry));
    	}
    	else{
    		$previouspolicy_expiry='';
         //print_r($previouspolicy_expiry);
    	}
    	//print_r($regdate);
$xml= "<?xml version='1.0' encoding='utf-8'?><soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><MoterQuoteRequest xmlns='eZeeInsurance'><data>{'VehiclePremium':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','PortalType':'B2B'},'CarDetails':{'State':'".$rto_array[2]."','City':'".$rto_array[1]."','RegistrationNo':'".$regno."','VehicleAge':'".$vehicleage."','RegistrationYear':'".$regdate."','VehicleCC':'".$vehiclecc[0]."','VehicleIDV':'','Manufacturer':'".$model_array[0]."','Model':'".$model_array[1]."','Variants':'".$model_array[2]."','FuelType':'".$fueltype_final."','NoOfClaim':'','PolicyType':'Universal Sompo','PrvPolicyExp':'".$previouspolicy_expiry."','ClaimMadeLastYr':'".$claimmade."','VehicleType':'4W'}}}</data><subMerchant></subMerchant></MoterQuoteRequest></soap:Body></soap:Envelope>";

  $url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
  $data = trim(curl_exec($ch));
  //print_r($data);exit();
  curl_close($ch);
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
  $result = $values[3]['value'];
  //print_r($result);exit();
  $obj = json_decode($result);
  //print_r($obj);exit();
  /*foreach($obj->MakeModelVariant->D as $val)
  {
    $model[]=$val->Make . '<br/>'; 
    //echo $val->Make;
  }*/
  //$data['cityname']=$model;
  //print_r($cityname);
  return($obj);


    }

    public function MoterQuoteRequest_2W($rto,$regno,$model,$regdate,$mobile,$previouspolicy_expiry='',$previous_insurer='',$ncb='',$claimmade='')
    {
      /*print_r('previous insurer'.$previous_insurer);
      print_r('previouspolicy_expiry'.$previouspolicy_expiry);
      print_r('ncb'.$ncb);
      print_r('claimmade'.$claimmade);
      print_r('RTO'.$rto);
      print_r('regdate'.$regdate);
      print_r('model'.$model);die();*/

      $c= date('Y');
        $y= date('Y',strtotime($regdate));
        $vehicleage=$c-$y;
        if($vehicleage<=0){
          $vehicleage=1;
        }
      //print_r($vehicleage);
      //print_r($regdate);die();
      //print_r($rto);
      $rto_array=explode(',',$rto);
      //print_r($rto_array);
      //print_r($model);
      $model_array=explode(',',$model);
      //print_r($model_array[0]);
      //print_r($model_array[1]);
      //print_r($model_array);
      $vehiclecc=explode('CC',$model_array[3]);
      //print_r($vehiclecc[1]);die();
      $fueltype=explode('(',$vehiclecc[1]);
      $fueltype_final=str_replace(')','',$fueltype);
      //print_r($fueltype);
      //print_r($fueltype_final);
      $regdate=date('d/m/Y', strtotime($regdate));
      if($previouspolicy_expiry!=''){
      $previouspolicy_expiry=date('d/m/Y', strtotime($previouspolicy_expiry));
      }
      else{
        $previouspolicy_expiry='';
         //print_r($previouspolicy_expiry);
      }
      $model_array[2]=trim($model_array[2]);
      $model_array[1]=trim($model_array[1]);

      //print_r($regdate);
$xml= "<?xml version='1.0' encoding='utf-8'?><soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><MoterQuoteRequest xmlns='eZeeInsurance'><data>{'VehiclePremium':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','PortalType':'B2B'},'CarDetails':{'State':'".$rto_array[2]."','City':'".$rto_array[1]."','RegistrationNo':'".$regno."','VehicleAge':'".$vehicleage."','RegistrationYear':'".$regdate."','VehicleCC':'".$vehiclecc[0]."','VehicleIDV':'','Manufacturer':'".$model_array[0]."','Model':'".$model_array[1]."','Variants':'".$model_array[2]."','FuelType':'".$fueltype_final."','NoOfClaim':'','PolicyType':'Universal Sompo','PrvPolicyExp':'".$previouspolicy_expiry."','ClaimMadeLastYr':'".$claimmade."','VehicleType':'2W'}}}</data><subMerchant></subMerchant></MoterQuoteRequest></soap:Body></soap:Envelope>";
//print_r($xml);

  $url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
  $data = trim(curl_exec($ch));
 // print_r($data);exit();
  curl_close($ch);
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
  $result = $values[3]['value'];
 //print_r($result);exit();
  $obj = json_decode($result);
  //print_r($obj);exit();
  /*foreach($obj->MakeModelVariant->D as $val)
  {
    $model[]=$val->Make . '<br/>'; 
    //echo $val->Make;
  }*/
  //$data['cityname']=$model;
  //print_r($cityname);
  return($obj);


    }

    public function getArrayFromResponse($response) 
    {
      $xml = new SimpleXMLElement($response);
      $array = array();
      foreach($xml->cServico as $node){
        $array[] = array(
           'id' => $node->Codigo,
           'quote' => $node->Valor,
           'days' => $node->PrazoEntrega
        );
      }
      return $array;
    }
    
    

    
}