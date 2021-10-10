<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HealthInsurance extends MY_Controller
{
    /**
     * Check Valid Login or display login page.
     */
    public function __construct()
    {
        parent::__construct();
        /*if ($this->login->check_session() == FALSE && $this->login->check_member() == FALSE) {
            redirect(site_url('site/login'));
        }
        $this->load->library('pagination');*/
    }

   public function index()
    {
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view('templates/single/insurance/index', $data);
    }
	 public function home()
    {
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view('templates/single/insurance/health', $data);
    }
	
	public function indexNoData()
    {
        $this->load->view('templates/single/insurance/premium', $premium);
    }
	
	public function getPlansData()
    {
		 $plans = $this->input->post('suggest');
		 //$data = '<h1>'.$data.'</h1>';
		 session_start();
		 $obj=$_SESSION['premiumDetails'];
		 $array=explode(",",$plans);
		 $i=0;
		 $data='AAA';
		 $sumInsure;
		 $k=0;
		// $suppliers;
	 if(count($array)!=0)
	{
		foreach($array as $value){
			$i=0;
			if($k!=0){
			 foreach($obj->PREMIUM->D as $val){
				 //$plansdata = $val->Supplier;
				if($i==$value)
				{
					 if(!isset($plansdata))
					 {
						$suppliers=$val->Supplier;
						$plansdata="{'Insurer':'".$val->Supplier."','Premium':'".$val->Premium."','Plan':'".$val->PlanName."','SumInsured':'".$sumInsure."'}";
					 }else{
						 $suppliers=$suppliers.",".$val->Supplier;	
						 $plansdata=$plansdata.",{'Insurer':'".$val->Supplier."','Premium':'".$val->Premium."','Plan':'".$val->PlanName."','SumInsured':'".$sumInsure."'}";
					 }
					break;
				}
				else
				{
					$i++;
				}
			 }
	}else{ 
	   $sumInsure=$value;
	   $k++;
	}  }}
	$xml= "<?xml version='1.0' encoding='utf-8'?>
<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><GetHealthAllBenefits xmlns='eZeeInsurance'>      <SubMerchant></SubMerchant><Data>{'GetHealthAllBenefits':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB'},'D':{'InsurerDetails':[".$plansdata."]}}}</Data></GetHealthAllBenefits></soap:Body></soap:Envelope>";

/*$xml= "<?xml version='1.0' encoding='utf-8'?>
<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><GetHealthAllBenefits xmlns='eZeeInsurance'>      <SubMerchant></SubMerchant><Data>{'GetHealthAllBenefits':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB'},'D':{'InsurerDetails':[{'Insurer':'Religare Health','Premium':'".$val->Premium."','Plan':'Care','SumInsured':'500000'},{'Insurer':'Star Health','Premium':'11677','Plan':'Family Health Optima','SumInsured':'500000'}]}}}</Data></GetHealthAllBenefits></soap:Body></soap:Envelope>";*/

$url = 'http://service.brokerassist.in/getdata.asmx';
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
    
  $outputdata = trim(curl_exec($ch));
  $outData;
  curl_close($ch);
  
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $outputdata, $values, $tags);
  xml_parser_free($parser);
 
  $result = $values[3]['value'];
  $obj = json_decode($result);
  
  $ageOfEntry;$ambulanceCover;$ayushTreatment;$coPayment;$dayCare;$domiciliaryTreatment;$eOpinion;$checkUp;$inPatient;$loading;$maternityCover;
  $medicalRequirement;$babyCover;$addBonus;$outPatient;$preHospitalization;$postHospitalization;$roomLimit;$namedAilments;$sumOption;$tpa;$waitingPeriod;$supplier;
  $supplierValues;
  $j=0;
  if($obj->HBENEFITS!=null){
  foreach($obj->HBENEFITS->D as $val){
	  
	  $premium=$val->Premium;
	  $j=0;
	  foreach($val->Sub as $value){
		  if($j==0){
		  $supplier=(isset($supplier))?$supplier."<td style='background-color:#3a9cfc;color: white;font-size: large;'>".$value->Supplier."<br/>".$value->Product."<br /><i class='fa fa-rupee'></i>".$premium."</td>":"<td style='background-color:#3a9cfc;color: white;font-size: large;'>".$value->Supplier."<br/>".$value->Product."<br /><i class='fa fa-rupee'></i>".$premium."</td>";
		  $j++;
		  }
		  
		  if($value->Benefit=='Age of Entry')
		  {
			  $ageOfEntry=(isset($ageOfEntry))?$ageOfEntry."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Ambulance Cover')
		  {
			 $ambulanceCover=(isset($ambulanceCover))?$ambulanceCover."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  } 
		  else if($value->Benefit=='Ayush Treatment')
		  {
			 $ayushTreatment=(isset($ayushTreatment))?$ayushTreatment."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Co Payment')
		  {
			 $coPayment=(isset($coPayment))?$coPayment."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Day care Treatment')
		  {
			 $dayCare=(isset($dayCare))?$dayCare."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Domiciliary Treatment')
		  {
			 $domiciliaryTreatment=(isset($domiciliaryTreatment))?$domiciliaryTreatment."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='E-opinion')
		  {
			 $eOpinion=(isset($eOpinion))?$eOpinion."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Free Health Check Up')
		  {
			 $checkUp=(isset($checkUp))?$checkUp."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		   else if($value->Benefit=='In-Patient Treatment')
		  {
			 $inPatient=(isset($inPatient))?$inPatient."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Loading')
		  {
			 $loading=(isset($loading))?$loading."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Maternity Cover')
		  {
			 $maternityCover=(isset($maternityCover))?$maternityCover."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Medical Requirement')
		  {
			 $medicalRequirement=(isset($medicalRequirement))?$medicalRequirement."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='New Born Baby Cover')
		  {
			 $babyCover=(isset($babyCover))?$babyCover."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		   else if($value->Benefit=='No Claim Bonus')
		  {
			 $addBonus=(isset($addBonus))?$addBonus."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Out Patient Benefit')
		  {
			 $outPatient=(isset($outPatient))?$outPatient."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Post Hospitalization')
		  {
			 $postHospitalization=(isset($postHospitalization))?$postHospitalization."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Pre Hospitalization')
		  {
			 $preHospitalization=(isset($preHospitalization))?$preHospitalization."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Room Limit')
		  {
			 $roomLimit=(isset($roomLimit))?$roomLimit."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		   else if($value->Benefit=='Specific Waiting Period for Named ailments')
		  {
			 $namedAilments=(isset($namedAilments))?$namedAilments."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Sum insured Option')
		  {
			 $sumOption=(isset($sumOption))?$sumOption."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='TPA')
		  {
			 $tpa=(isset($tpa))?$tpa."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
		  else if($value->Benefit=='Waiting period to cover Pre-Existing ailments')
		  {
			 $waitingPeriod=(isset($waitingPeriod))?$waitingPeriod."<td>".$value->{'Benefits details'}."</td>":"<td>".$value->{'Benefits details'}."</td>";
		  }
	  
  }
	  
  }}else{
	  $outData='<h1>No Data found</h1>';	  
  }
  
  $outData=(isset($outData))?$outData:"<tbody><tr><th>Benefits</th>".$supplier."</tr><tr><th>Age of Entry</th>".$ageOfEntry."</tr>
                            <tr><th>Ambulance Cover</th>".$ambulanceCover."</tr><tr><th>Ayush Treatment</th>".$ayushTreatment."</tr>
                            <tr><th>Co Payment</th>".$coPayment."</tr><tr>
                            	<th>Day care Treatment</th>".$dayCare."
                            </tr>
                             <tr>
                            	<th>Domiciliary Treatment</th>".$domiciliaryTreatment."
                            </tr>
                             <tr>
                            	<th>E-opinion</th>".$eOpinion."
                            </tr>
                             <tr>
                            	<th>Free Health Check Up</th>".$checkUp."
                            </tr>
                             <tr>
                            	<th>In-Patient Treatment</th>".$inPatient."
                            </tr>
                             <tr>
                            	<th>Loading</th>".$loading."
                            </tr>
                             <tr>
                            	<th>Maternity Cover</th>".$maternityCover."
                            </tr>
							<tr>
								<th>Medical Requirement</th>".$medicalRequirement."
							</tr>
							<tr>
								<th>New Born Baby Cover</th>".$babyCover."								
							</tr>
							<tr>
								<th>No Claim Bonus</th>".$addBonus."						
							</tr>
							<tr>
								<th>Out Patient Benefit</th>".$outPatient."					
							</tr>
							<tr>
								<th>Post Hospitalization</th>".$postHospitalization."						
							</tr>
							<tr>
								<th>Pre Hospitalization</th>".$preHospitalization."								
							</tr>
							<tr>
								<th>Room Limit</th>".$roomLimit."						
							</tr>
							<tr>
								<th>Specific Waiting Period for Named ailments</th>".$namedAilments."
							</tr>
							<tr>
								<th>Sum Insured Option</th>".$sumOption."
							</tr>
							<tr>
								<th>TPA</th>".$tpa."
							</tr>
							<tr>
								<th>Waiting period to cover Pre-Existing ailments</th>".$waitingPeriod."
							</tr>
                        </tbody>";
		
		$data = '<h1>'.$outData.'</h1>';
		//$data = '<h1>'.$xml.'</h1>';
		//$data = '<h1>'.$obj->HBENEFITS.'</h1>';
		 echo $data;
	}
	
	public function getPremiumDetails()
    {
		$data['sumInsured']=$this->input->post('sumInsure');
		 $data['members']=$this->input->post('memebers');
		 $i=$this->input->post('selectedValue');
		 $data['basicPremium']=$this->input->post('basicPremium'.$i);
		 $data['supplierName']=$this->input->post('supplierName'.$i);
		 $data['totalAmount']=$this->input->post('total'.$i);	
		 $data['planName']=$this->input->post('planName'.$i);
        $this->load->view('templates/single/insurance/addonhealth', $data);
    }
	
	 public function getPremium()
	 {
		 $sumInsured=$this->input->post('sumInsure');
		 $pincodeNumber=$this->input->post('pincode');
		 $city=$this->input->post('city');
		 $state=$this->input->post('state');
		 $age0=0;$age1=0;$age2=0;$age3=0;$age4=0;$age5=0;
		 $dob0=0;$dob1=0;$dob2=0;$dob3=0;$dob4=0;$dob5=0;
		 $personAge0=0;$personAge1=0;$personAge2=0;$personAge3=0;$personAge4=0;$personAge5=0;
		 $personDob0=0;$personDob1=0;$personDob2=0;$personDob3=0;$personDob4=0;$personDob5=0;
		 $member0="";$member1="";$member2="";$member3="";$member4="";$member5="";
		 $totalMemebers=0;
		 $totalSuppliers=0;
		 $premium['members'];
		 $premium['sumInsure']=$sumInsured;
		 $premium['city']=$city;
		 $premium['state']=$state;
		 $premium['pincodeNumber']=$pincodeNumber;
		 
		 $premium['memberDetails'];
		 $suppliers;
		for($i=0; $i<6;$i++)
		{			
			${'checkbox'.$i}=$this->input->post('checkbox'.$i);
			if(${'checkbox'.$i} =='on')
		    { 
			$today = new Datetime(date('y.m.d'));
			${'age'.$i} = $today->diff(new DateTime($this->input->post('db'.$i)))->y;
			${'dob'.$i}= date_format(new DateTime($this->input->post('db'.$i)),"d/m/y");
			${'personAge'.$i}=$this->input->post('member'.$i)."_".${'age'.$i} ;
			${'personDob'.$i}=$this->input->post('member'.$i)."_".${'dob'.$i} ;
			${'member'.$i}=$this->input->post('member'.$i)."(".${'age'.$i}."Years )";
			$totalMemebers++;
			if($premium['members']!=null)
			{
			$premium['members']=$premium['members']."<br/>".${'member'.$i};
			$premium['memberDetails']=$premium['memberDetails']."<br/><i class='fa fa-user-plus' aria-hidden='true'></i>&nbsp;<span class='o'>".$this->input->post('member'.$i)."</span>&nbsp;------".${'age'.$i}."&nbsp;Years (".${'dob'.$i}.")";
			}
			else
			{
			$premium['members']=${'member'.$i};
			$premium['memberDetails']="<span class='o'>".$this->input->post('member'.$i)."</span>&nbsp;------".${'age'.$i}."&nbsp;Years (".${'dob'.$i}.")";
			}
		   }
		}
		$premium['totalMembers']=$totalMembers;
		 
		$xml= "<?xml version='1.0' encoding='utf-8'?>
<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><HealthPremium xmlns='eZeeInsurance'><SubMerchant></SubMerchant><Data>{'HealthPremium':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB','PortalType':'B2C'},'D':{'SumInsured':'".$sumInsured."','Zone':'".$pincodeNumber."/".$city."/".$state."','Ages':{'Age0':'".$age0."','Age1':'".$age1."','Age2':'".$age2."','Age3':'".$age3."','Age4':'".$age4."','Age5':'".$age5."'},'DOB':{'DOB0':'".$dob0."','DOB1':'".$dob1."','DOB2':'".$dob2."','DOB3':'".$dob3."','DOB4':'".$dob4."','DOB5':'".$dob5."'}}}}</Data>
<Family>500000".$personAge0.",".$personAge1.",".$personAge2.",".$personAge3.",".$personAge4.",".$personAge5.",~ 560076/KARNATAKA/BANGALORE#".$personDob0."!".$personDob1."!".$personDob2."!".$personDob3."!".$personDob4."!".$personDob5."!@Health</Family>
</HealthPremium>
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
    
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $data, $values, $tags);
  xml_parser_free($parser);
 
  $result = $values[3]['value'];
  $obj = json_decode($result);    
  $data =array();
  $i=0;
  if($obj->PREMIUM->S=='Success')
		{
  foreach($obj->PREMIUM->D as $val)
  {
	 $premium['sumAssured'.$i]=$sumInsured;
	 $premium['supplier'.$i]=$val->Supplier;
	 $premium['planName'.$i]=$val->PlanName;
	 $premium['basicPremium'.$i]=$val->Premium;
	 $premium['GST'.$i]=round($val->Premium*.18);
	 $premium['imageUrl'.$i]=$val->Image;
	 $premium['transactionId']=$val->TransactionID;
	 $premium['total'.$i]=$val->Premium+round($val->Premium*.18);
	 $premium['planCode'.$i]=$val->PlanCode;
	 $benefits= array();
	 $premium['medReqDetails'.$i]='NA';
	 $premium['checkUpDetails'.$i]='NA';
	 $premium['roomLimitDetails'.$i]='NA';
	 $premium['waitingPeriodDetails'.$i]='NA';
	 $premium['claimDetails'.$i]='NA';	
	 $totalSuppliers;
	 $suppliersCount;
	 if($suppliers!=null)
	 {  
		$totalSuppliers=$totalSuppliers.",".$val->Supplier;
		
	 if (!preg_match("/{$val->Supplier}/i", $suppliers) ) {
				$suppliers=$suppliers.",".$val->Supplier;
	 }	
	 		
	}
	 else{
		 $suppliers=$val->Supplier;
		  $totalSuppliers=$val->Supplier;
	 }
	
	 
	foreach($val->BENEFITS as $benifit)
  {  
	if($benifit->Benefit=='Medical Requirement')
	{
	    $premium['medReqDetails'.$i]=$benifit->BenefitDetails;
	}
	else if($benifit->Benefit=='No Claim Bonus')
	{
	    $premium['claimDetails'.$i]=$benifit->BenefitDetails;
	}
	else if($benifit->Benefit=='Free Health Check Up')
	{
	    $premium['checkUpDetails'.$i]=$benifit->BenefitDetails;
	}
	else if($benifit->Benefit=='Room Limit')
	{
	    $premium['roomLimitDetails'.$i]=$benifit->BenefitDetails;
	}
	else if($benifit->Benefit=='Waiting period to cover Pre-Existing ailments')
	{
	    $premium['waitingPeriodDetails'.$i]=$benifit->BenefitDetails;
	}
  }	
	$premium['totalPolicies']=$i;
	$i++;
	}
	 $array=explode(",",$suppliers);
	 if(count($array)!=0)
	{
		foreach($array as $value){ 
		 if($suppliersCount!=null)
		{  
		$suppliersCount=$suppliersCount.",".$value."(".substr_count($totalSuppliers, $value).")";		
		}else{
	 			$suppliersCount=$value.$suppliersCount."(".substr_count($totalSuppliers, $value).")";	
	    }}
	}
	 $premium['suppliers']=$suppliersCount;

	 $ages="'Age0':'".$age0."','Age1':'".$age1."','Age2':'".$age2."','Age3':'".$age3."','Age4':'".$age4."','Age5':'".$age5;
	 $dobs="'DOB0':'".$dob0."','DOB1':'".$dob1."','DOB2':'".$dob2."','DOB3':'".$dob3."','DOB4':'".$dob4."','DOB5':'".$dob5;
	
	session_start();
	$_SESSION['premiumDetails'] = $obj;
	$_SESSION['memberDetails'] = $premium['memberDetails'];
	$_SESSION['ages'] = $ages;
	$_SESSION['dobs'] = $dobs;
	
    $this->load->view('templates/single/insurance/premium', $premium);  
	}
	else{
	  $this->load->view('templates/single/insurance/premium', $premium);
	}
  }
  
  
  public function getProposalData()
    {
		$addOn=$_POST['addOnSelected'];
		$buttonSeleted=$this->input->post('buttonSelected');
		$sumInsured=$this->input->post('sumInsured');
		$city=$this->input->post('city');
		$totalAmount=$this->input->post('totalAmount');
		if($buttonSeleted=='back')
		{
		session_start();
		$obj=$_SESSION['premiumDetails'];
		$premium['memberDetails']=$_SESSION['memberDetails'];
		$premium['city']=$city;
		$premium['sumInsure']=$sumInsured;	
		$members=$this->input->post('members');
		$members=str_replace("(","----->",$members);
		$members=str_replace(")","",$members);
		$premium['members']=$members;
        $i=0;
		foreach($obj->PREMIUM->D as $val)
		{
			$premium['sumAssured'.$i]=$sumInsured;
			$premium['supplier'.$i]=$val->Supplier;
			$premium['planName'.$i]=$val->PlanName;
			$premium['basicPremium'.$i]=$val->Premium;	
			$premium['transactionId']=$val->TransactionID;
			$premium['GST'.$i]=round($val->Premium*.18);
			$premium['imageUrl'.$i]=$val->Image;
			$premium['total'.$i]=$val->Premium+round($val->Premium*.18);
			$benefits= array();
			$premium['medReqDetails'.$i]='NA';
			$premium['checkUpDetails'.$i]='NA';
			$premium['roomLimitDetails'.$i]='NA';
			$premium['waitingPeriodDetails'.$i]='NA';
			$premium['claimDetails'.$i]='NA';
		foreach($val->BENEFITS as $benifit)
		{  
			if($benifit->Benefit=='Medical Requirement')
			{
				$premium['medReqDetails'.$i]=$benifit->BenefitDetails;
			}
			else if($benifit->Benefit=='No Claim Bonus')
			{
				$premium['claimDetails'.$i]=$benifit->BenefitDetails;
			}
			else if($benifit->Benefit=='Free Health Check Up')
			{
				$premium['checkUpDetails'.$i]=$benifit->BenefitDetails;
			}
			else if($benifit->Benefit=='Room Limit')
			{
				$premium['roomLimitDetails'.$i]=$benifit->BenefitDetails;
			}
			else if($benifit->Benefit=='Waiting period to cover Pre-Existing ailments')
			{
				$premium['waitingPeriodDetails'.$i]=$benifit->BenefitDetails;
			}
		}	
			$premium['totalPolicies']=$i;
			$i++;
		}
		$this->load->view('templates/single/insurance/premium', $premium); 
    }
	else{		
		$total=$this->input->post('totalAmount');	
		$addOnDetails;
		if(isset($_POST['addOnSelected'])){
		foreach($_POST['addOnSelected'] as $selected){
			$addOnDetails=$addOnDetails."<br/>".$selected;
			preg_match_all('!\d+.*!', $selected, $matches);
			$value=(int)$matches[0][0];
			$total=$total+$value;
		}}
		$premium['sumAssured']=$sumInsured;
		$premium['city']=$city;
		$premium['supplierName']=$this->input->post('supplierName');
		$premium['planName']=$this->input->post('planName');
		$premium['totalAmount']=$total;
		$premium['transactionId']=$this->input->post('transactionId');
		$members=$this->input->post('members');
		$members=str_replace("(","----->",$members);
		$members=str_replace(")","",$members);
		$premium['members']=$members;
		$premium['addOn']=$addOn;
		$premium['addOnDetails']=$addOnDetails;
		$this->load->view('templates/single/insurance/insurancebookhealthProposalInfo', $premium);
	}
	
	}
  
  public function getAddOnDetails()
    {
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
		$pincodeNumber=$this->input->post('pincodeNumber');
		$data['sumInsured']=$this->input->post('sumInsure');
		$data['members']=$this->input->post('memebers');
		$i=$this->input->post('selectedValue');
		$data['basicPremium']=$this->input->post('basicPremium'.$i);
		$data['supplierName']=$this->input->post('supplierName'.$i);
		$data['totalAmount']=$this->input->post('total'.$i);	
		$data['planName']=$this->input->post('planName'.$i);
		$data['city']=$this->input->post('city');
		$data['transactionId']=$this->input->post('transactionId');	
		 
		$sumInsure=$this->input->post('sumInsure');
		$city=$this->input->post('city');
		$state=$this->input->post('state');
		
		$premium=$this->input->post('basicPremium'.$i);
		$supplierName=$this->input->post('supplierName'.$i);
		$planName=$this->input->post('planName'.$i);
		$planCode=$this->input->post('planCode'.$i);
		$addOnPlanName=($planCode!=null)?$this->input->post('planName'.$i)."(".$planCode.")":$this->input->post('planName'.$i);
		 
		session_start();
		$ages=$_SESSION['ages'];
		$dobs=$_SESSION['dobs'];

$xml= "<?xml version='1.0' encoding='utf-8'?>
<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'><soap:Body><GetHealthAddOn xmlns='eZeeInsurance'> <SubMerchant></SubMerchant>	<SearchData>{'HealthAddons':{'CredentialDetails':{'UserID':'it@softwebtechnology.my','UserCode':'SFT00001','UserMerchant':'INFL-SOFTWEB'},'D':{'SumInsured':'".$sumInsure."','Zone':'".$pincodeNumber."/".$city."/".$state."','Product':'".$planName."','Supplier':'".$supplierName."','AddOnPlan':'".$addOnPlanName."','Premium':'".$premium."','Ages':{".$ages."'},'DOB':{".$dobs."'}}}}</SearchData></GetHealthAddOn></soap:Body></soap:Envelope>";
$url = 'http://service.brokerassist.in/getdata.asmx';

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_POST, true );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
    
  $outputdata = trim(curl_exec($ch));
  curl_close($ch);
  
  $array=explode("#",$outputdata);
  $addOn;
  
  if(count($array)!=0)
	{
		foreach($array as $value){ 
		$arrayValue=explode("~",$value);
			if($addOn!=null)
			{
			$addOn=$addOn.", ".$arrayValue[1]."  ".$arrayValue[2];		
			}
			else{
				$addOn=$arrayValue[1]."  ".$arrayValue[2];
			}
		}	
	} 
	if($addOn!="  "){
		$data['addOn']=$addOn;
	}
    $this->load->view('templates/single/insurance/addonhealth', $data); 
    }
	
	/*public function getProposalsDetails()
    {
		try{
		$soapclient = new SoapClient('http://service.brokerassist.in/getdata.asmx?wsdl');
		$param=array('SubMerchant'=>'','InsurerData'=>'it@softwebtechnology.my, SFT00001,INFL-SOFTWEB,B2B#AJay|Jain|Male|Self Employed|26/08/1982|Self- Primary Member|Malini|Wife|33|||Married|%AJay Jain$Male$SELF- PRIMARY MEMBER$Self Employed$170$70$$$$$$Married$` #9840181800$it@softwebtechnology.my$Raheja towers,No.177, Delta 4, Ground Floor, Anna Salai, Chennai - 600 002$DELHI$DELHI$10420|110088$$$$$$Raheja towers,No.177, Delta 4, Ground Floor, Anna Salai, Chennai - 600 002$DELHI$DELHI$10420|110088$##~~24082094P3X5VTDVO5','SearchData'=>'500000$0$0$37$0$0$0$110001/DELHI/DELHI$Religare Health$6726$Care Freedom$0!0!26/08/1982!0!0!0~Health','InsuranceName'=>'Religare Health');
		$response =$soapclient->GetHealthProposal($param);  
		echo $response->GetHealthProposalResult;

	}catch(Exception $e){
		echo $e->getMessage();
	}
	}	*/				
	
	public function getInsurerInfo()
    {			
        $data['addOnDetails']=$this->input->post('addOnDetails');
		$data['sumAssured']=$this->input->post('sumAssured');
		$data['city']=$this->input->post('city');
		$data['supplierName']=$this->input->post('supplierName');
		$data['planName']=$this->input->post('planName');
		$data['totalAmount']=$this->input->post('totalAmount');
		$members=$this->input->post('members');
		$members=str_replace("(","----->",$members);
		$members=str_replace(")","",$members);
		$data['members']=$members;
		$data['addOn']=$this->input->post('addOn');
		$data['propfname']=$this->input->post('propfname');
		$data['proplname']=$this->input->post('proplname');
		$data['propGender']=$this->input->post('propGender');
		$data['propOccupation']=$this->input->post('propOccupation');
		$data['propDOB']=$this->input->post('propDOB');
		$data['propRelation']=$this->input->post('propRelation');
		$data['propNominee']=$this->input->post('propNominee');
		$data['propNomineeRelation']=$this->input->post('propNomineeRelation');
		$data['propNomineeAge']=$this->input->post('propNomineeAge');
		$data['propPAN']=$this->input->post('propPAN');
		$data['propAadhar']=$this->input->post('propAadhar');
		$data['propMartial']=$this->input->post('propMartial');
		$data['transactionId']=$this->input->post('transactionId');
		
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view('templates/single/insurance/insurancebookInsurerInfo', $data);
    }
	
	public function getContactInfo()
    {
		$data['addOnDetails']=$this->input->post('addOnDetails');
		$data['sumAssured']=$this->input->post('sumAssured');
		$data['city']=$this->input->post('city');
		$data['supplierName']=$this->input->post('supplierName');
		$data['planName']=$this->input->post('planName');
		$data['totalAmount']=$this->input->post('totalAmount');
		$members=$this->input->post('members');
		$members=str_replace("(","----->",$members);
		$members=str_replace(")","",$members);
		$data['members']=$members;
		$data['addOn']=$addOn;
		$data['propfname']=$this->input->post('propfname');
		$data['proplname']=$this->input->post('proplname');
		$data['propGender']=$this->input->post('propGender');
		$data['propOccupation']=$this->input->post('propOccupation');
		$data['propDOB']=$this->input->post('propDOB');
		$data['propRelation']=$this->input->post('propRelation');
		$data['propNominee']=$this->input->post('propNominee');
		$data['propNomineeRelation']=$this->input->post('propNomineeRelation');
		$data['propNomineeAge']=$this->input->post('propNomineeAge');
		$data['propPAN']=$this->input->post('propPAN');
		$data['propAadhar']=$this->input->post('propAadhar');
		$data['propMartial']=$this->input->post('propMartial');
		$data['transactionId']=$this->input->post('transactionId');
	
		$totalInsurer=$this->input->post('totalInsurer');						  								
		
		for($i=0;$i<$totalInsurer;$i++)
		{
		$data['member'.$i]=$this->input->post('member'.$i);
		$data['fname'.$i]=$this->input->post('fname'.$i);
		$data['lname'.$i]=$this->input->post('lname'.$i);
		$data['gender'.$i]=$this->input->post('gender'.$i);
		$data['propOccupation']=$this->input->post('propOccupation');
		$data['relation'.$i]=$this->input->post('relation'.$i);
		$data['occupation'.$i]=$this->input->post('occupation'.$i);
		$data['height'.$i]=$this->input->post('height'.$i);
		$data['weight'.$i]=$this->input->post('weight'.$i);
		$data['pan'.$i]=$this->input->post('pan'.$i);
		$data['adhar'.$i]=$this->input->post('adhar'.$i);
		$data['martialStatus'.$i]=$this->input->post('martialStatus'.$i);		
		}
		$data['totalInsurer']=$this->input->post('totalInsurer');			
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
        $this->load->view('templates/single/insurance/insurancebookhealthContactInfo', $data);
    }
	
	public function verifyProposalInfo()
    {
		$data['addOnDetails']=$this->input->post('addOnDetails');
		$data['sumAssured']=$this->input->post('sumAssured');
		$data['members']=$this->input->post('members');
		$data['supplierName']=$this->input->post('supplierName');		
		$data['planName']=$this->input->post('planName');							
		$data['propfname']=$this->input->post('propfname');
		$data['proplname']=$this->input->post('proplname');
		$data['propGender']=$this->input->post('propGender');
		$data['propOccupation']=$this->input->post('propOccupation');
		$data['propDOB']=$this->input->post('propDOB');
		$data['propRelation']=$this->input->post('propRelation');
		$data['propNominee']=$this->input->post('propNominee');
		$data['propNomineeRelation']=$this->input->post('propNomineeRelation');
		$data['propNomineeAge']=$this->input->post('propNomineeAge');
		$data['propPAN']=$this->input->post('propPAN');
		$data['propAadhar']=$this->input->post('propAadhar');
		$data['propMartial']=$this->input->post('propMartial');		
		$data['address']=$this->input->post('address');
		$data['city']=$this->input->post('city');
		$data['state']=$this->input->post('state');
		$data['pincode']=$this->input->post('pincode');
		$data['mobileNumber']=$this->input->post('mobileNumber');
		$data['email']=$this->input->post('email');
		$data['policyNumber']=$this->input->post('policyNumber');
		$data['previousInsurer']=$this->input->post('previousInsurer');
		$data['policyExpiry']=$this->input->post('policyExpiry');		
		$data['GSTNumber']=$this->input->post('GSTNumber');
		$data['transactionId']=$this->input->post('transactionId');
		$data['proposalNo']=$this->input->post('proposalNo');
		$data['totalAmount']=$this->input->post('totalAmount');		
		$data['proposalPremium']=$this->input->post('proposalPremium');	

			$totalInsurer=$this->input->post('totalInsurer');						  								
		
		for($i=0;$i<$totalInsurer;$i++)
		{
		$data['member'.$i]=$this->input->post('member'.$i);
		$data['fname'.$i]=$this->input->post('fname'.$i);
		$data['lname'.$i]=$this->input->post('lname'.$i);
		$data['gender'.$i]=$this->input->post('gender'.$i);
		$data['propOccupation']=$this->input->post('propOccupation');
		$data['relation'.$i]=$this->input->post('relation'.$i);
		$data['occupation'.$i]=$this->input->post('occupation'.$i);
		$data['height'.$i]=$this->input->post('height'.$i);
		$data['weight'.$i]=$this->input->post('weight'.$i);
		$data['pan'.$i]=$this->input->post('pan'.$i);
		$data['adhar'.$i]=$this->input->post('adhar'.$i);
		$data['martialStatus'.$i]=$this->input->post('martialStatus'.$i);
		}
		$data['totalInsurer']=$this->input->post('totalInsurer');		
		
		try{
		/*$soapclient = new SoapClient('http://service.brokerassist.in/getdata.asmx?wsdl');
		$param=array('SubMerchant'=>'','InsurerData'=>'it@softwebtechnology.my, SFT00001,INFL-SOFTWEB,B2B#AJay|Jain|Male|Self Employed|26/08/1982|Self- Primary Member|Malini|Wife|33|||Married|%AJay Jain$Male$SELF- PRIMARY MEMBER$Self Employed$170$70$$$$$$Married$` #9840181800$it@softwebtechnology.my$Raheja towers,No.177, Delta 4, Ground Floor, Anna Salai, Chennai - 600 002$DELHI$DELHI$10420|110088$$$$$$Raheja towers,No.177, Delta 4, Ground Floor, Anna Salai, Chennai - 600 002$DELHI$DELHI$10420|110088$##~~1708209DY7HQFIB','SearchData'=>'500000$0$0$37$0$0$0$110001/DELHI/DELHI$Religare Health$6726$Care Freedom$0!0!26/08/1982!0!0!0~Health','InsuranceName'=>'Religare Health');*/
		//$response =$soapclient->GetHealthProposal($param);  
		//echo $response->GetHealthProposalResult;
		
		$this->load->view('templates/single/insurance/healthProposalConfirmation', $data);

	}catch(Exception $e){
		echo $e->getMessage();
	}		
        $data['title'] = 'Dashboard';
        $data['breadcrumb'] = 'dashboard';
    }
	
	public function submitHealthProposal()
    {				
    $user_id = $this->session->user_id;			
	$first_name=$this->input->post('propfname');
	$last_name=$this->input->post('proplname');
	$dob=$this->input->post('propDOB');  
	$gender=$this->input->post('propGender');  
	$phone=$this->input->post('mobileNumber');
	$email=$this->input->post('email');  
	$aadharNo=$this->input->post('propAadhar');
	$PanNo=$this->input->post('propPAN');  
	$Martial=$this->input->post('propMartial');
	$Address=$this->input->post('address');  
	$City=$this->input->post('city');
	$State=$this->input->post('state');
	$PinCode=$this->input->post('pincode');
	$Nominee=$this->input->post('propNominee');
	$NomineeAge=$this->input->post('propNomineeAge'); 
	$Relation=$this->input->post('propRelation');
	$NomineeRelation=$this->input->post('propNomineeRelation');  
	$PrevPolicyNo=$this->input->post('policyNumber');
	$PrevPolicyInsurer=$this->input->post('previousInsurer');
	$SupplierName=$this->input->post('supplierName');
	$Members=$this->input->post('members');
	$PremiumAmount=$this->input->post('totalAmount');
	$SumAssured=$this->input->post('sumAssured');
	$PlanName=$this->input->post('planName');
	$TransactionId=$this->input->post('transactionId');
	$ProposalNo=$this->input->post('proposalNo');
	$AddOnDetails=$this->input->post('addOnDetails');
				
	$data = array(				
				'first_name' => $first_name,
				'last_name' => $last_name,
				'dob' => $dob,  
				'gender' => $gender,  
				'phone' => $phone,
				'email' => $email,  
				'aadharNo' => $aadharNo,
				'PanNo' => $PanNo,
				'Martial' => $Martial,
				'Address' => $Address,  
				'City' => $City,
				'State' => $State,
				'PinCode' => $PinCode,
				'Nominee' => $Nominee,
				'NomineeAge' => $NomineeAge,
				'Relation' => $Relation,
				'NomineeRelation' => $NomineeRelation,  
				'PrevPolicyNo' =>$PrevPolicyNo,
				'PrevPolicyInsurer' => $PrevPolicyInsurer,
				'SupplierName' => $SupplierName,
				'Members' => $Members,
				'PremiumAmount' =>$PremiumAmount,
				'SumAssured' => $SumAssured,
				'PlanName' => $PlanName,
				'TransactionId'=> $TransactionId,
				'ProposalNo'=> $ProposalNo,
				'AddOnDetails'=> $AddOnDetails,
				'user_id'=>$user_id               
             );
     $this->db->insert('health_insurance', $data);
     debug_log($this->db->last_query());
     $this->load->view('templates/single/insurance/alert', $data);			
	}
	
}