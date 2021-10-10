<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Astrology extends CI_Controller
{
    /**
     *** Check Valid Login or display login page.
         */
    public function __construct(){
        parent::__construct();
        $this->load->library('pagination');
        //$this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->library('VedicRishiClient');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('language');
        //$this->load->model('earning');
    }


    public function index(){
      //debug_log('Currently loaded libs: '.implode(', ', $this->loader->_ci_classes())); 

      //redirect(site_url('astrology/astro'));
            

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** PANCHANG FUNCTIONS ****************************************************
        $data['responseData37'] = $vedicRishi->getAdvancedPanchangSunrise($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData37Next'] = $vedicRishi->getAdvancedPanchangSunrise($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $this->load->view('astrology/astro',$data);
       $this->load->view('astrology/inc/footer');
    }
    
    public function astro_home(){
      

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client


       //***************************************** PANCHANG FUNCTIONS ****************************************************
        $data['responseData37'] = $vedicRishi->getAdvancedPanchangSunrise($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData37Next'] = $vedicRishi->getAdvancedPanchangSunrise($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $this->load->view('astrology/astro',$data);
        $this->load->view('astrology/inc/footer');
    }

    public function free_kundali(){
      # code...
        $this->load->view('astrology/free_kundali');
        $this->load->view('astrology/inc/footer');
    }

    public function gemstone_input(){

      # code...
        $this->load->view('astrology/gemstone_form');
        $this->load->view('astrology/inc/footer');
    }

    public function rudraksha_input(){

      # code...
        $this->load->view('astrology/rudraksha_form');
        $this->load->view('astrology/inc/footer');
    }

    public function kalsarpa_dasha_input(){
      # code...

        $this->load->view('astrology/kalsarpa-dosha_form');
                $this->load->view('astrology/inc/footer');

    }

    public function Sadhesati_dosha_input(){

      # code...

        $this->load->view('astrology/sadhesati_form');
                $this->load->view('astrology/inc/footer');

    }

    public function pitri_dasha_input(){

      # code...

        $this->load->view('astrology/pitra-dosh_form');
                $this->load->view('astrology/inc/footer');

    }

    public function manglik_dosha_input(){

      # code...

        $this->load->view('astrology/manglik_form');
                $this->load->view('astrology/inc/footer');

    }


    public function astro_latitude($place){
      $auth = "https://api.mapbox.com/geocoding/v5/mapbox.places/".$place.".json?access_token=pk.eyJ1Ijoic293bXlha25zaGFybWEiLCJhIjoiY2tnbnFqbDk5MDlxNzMwczE4Zm16MGgzdiJ9.kipSLq1yQLlvI5kYuPQJUQ";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $auth);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      $response = json_decode(curl_exec($ch),true);
      curl_close($ch);
      $latitude=$response['features'][0]['center'][1];
      //print_r($latitude);
      //print_r($longitude);
      return $latitude;
    }

    public function astro_longitude($place){
      $auth = "https://api.mapbox.com/geocoding/v5/mapbox.places/".$place.".json?access_token=pk.eyJ1Ijoic293bXlha25zaGFybWEiLCJhIjoiY2tnbnFqbDk5MDlxNzMwczE4Zm16MGgzdiJ9.kipSLq1yQLlvI5kYuPQJUQ";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $auth);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      $response = json_decode(curl_exec($ch),true);
      curl_close($ch);
      $longitude=$response['features'][0]['center'][0];
     // print_r($latitude);
      //print_r($longitude);
      return $longitude;
    }

    public function basic_astro(){

       

        $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place'=>$this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
       //print_r($latitude);
       //print_r($longitude);
       //exit();

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

      // instantiate VedicRishiClient class
     $vedicRishi = new VedicRishiClient($userId, $apiKey);
        $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client
       //print_r($language1);
    // call horoscope functions of Vedic Rishi Client

      //*****************Basic Astro****************//$vedicRishi->getBirthDetails
      $data['responseData'] = $this->vedicrishiclient->getBirthDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
      $data['responseData1'] = $vedicRishi->getAstroDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData2'] = $vedicRishi->getPlanetsDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData3'] = $vedicRishi->getPlanetsExtendedDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData4'] = $vedicRishi->getPlanetsTropicalDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData5'] = $vedicRishi->getGeoDetails('pune', 5);

      $data['responseData6'] = $vedicRishi->getTimezone('Asia/Kolkata', 'false');
      //print_r($responseData);exit();
      $this->load->view('astrology/kundli_report', $data);
              $this->load->view('astrology/inc/footer');
    }

        public function data_female(){

      $this->load->view('astrology/female_kundli_matching');
              $this->load->view('astrology/inc/footer');
    }

    public function data_male(){


        $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place'=>$this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
       // $this->session->set_userdata('femaleData', $data);
      $this->load->view('astrology/male_kundli_matching',$data);
              $this->load->view('astrology/inc/footer');
    }

    public function match_making(){

      $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);

      $maleBirthData = array(
              'name' =>  $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place' => $this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5//$this->input->post('timezone'),
              // 'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
      $femaleBirthData = array(
              'name' => $_SESSION['Name'],
              'date' =>  $_SESSION['Date'],
              'month' => $_SESSION['Month'],
              'year' => $_SESSION['Year'],
              'hour' => $_SESSION['Hour'],
              'minute' => $_SESSION['Minute'],
              'second' => $_SESSION['Second'],
              'place'=>$_SESSION['Place'],
              'latitude' => $_SESSION['Latitude'],
              'longitude' =>$_SESSION['Longitude'],
              'timezone' => 5.5//$this->input->post('timezone'),
              // 'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      // print_r($femaleBirthData);
      // print_r($maleBirthData);

      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];
      $data['details']=json_encode(array('maleBirthData'=>$maleBirthData,'femaleBirthData'=>$femaleBirthData));


      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));

      $data['responseData50'] = $vedicRishi->matchBirthDetails($maleBirthData, $femaleBirthData);
      $data['responseData51'] = $vedicRishi->matchPlanetDetails($maleBirthData, $femaleBirthData);
      $data['responseData52'] = $vedicRishi->matchAstroDetails($maleBirthData, $femaleBirthData);
      $data['responseData53'] = $vedicRishi->matchAshtakootPoints($maleBirthData, $femaleBirthData);
      $data['responseData54'] = $vedicRishi->getMatchMakingReport($maleBirthData, $femaleBirthData);
      $data['responseData55'] = $vedicRishi->getMatchManglikReport($maleBirthData, $femaleBirthData);
      $data['responseData56'] = $vedicRishi->matchObstructions($maleBirthData, $femaleBirthData);
      $data['responseData57'] = $vedicRishi->getMatchSimpleReport($maleBirthData, $femaleBirthData);
       
       $this->load->view('astrology/kundli_matching_report', $data, $maleBirthData, $femaleBirthData);
       $this->load->view('astrology/inc/footer');
    }


    public function Ashtakvarga(){
       

       // make some dummy data in order to call vedic rishi api
       $data = array(
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'latitude' => 5.5,//$this->input->post('latitude'),
              'longitude' =>6.7, //$this->input->post('longitude'),
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
       //$this->session->set_userdata($data);
      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

         //*****************Ashtakvarga****************//
         $data['responseData7'] = $vedicRishi->getAshtakvargaDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[3]);

         $data['responseData8'] = $vedicRishi->getSarvashtakDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
         $this->load->view('templates/single/insurance/twowheeler', $data);
                 $this->load->view('astrology/inc/footer');
    }

    public function vimshottari_Dasha_input(){

      # code...

        $this->load->view('astrology/vimshottari_dasha_form');
                $this->load->view('astrology/inc/footer');

    }

    public function vimshottari_dasha(){
       

       $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place'=>$this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

         //*****************Vimshottari Dasha****************//
        $data['responseData9'] = $vedicRishi->getCurrentVimDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData10'] = $vedicRishi->getCurrentVimDashaAll($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $data['responseData11'] = $vedicRishi->getMajorVimDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
         $this->load->view('astrology/vimshottari_dasha', $data);
                 $this->load->view('astrology/inc/footer');
    }

    public function yogini_Dasha_input(){

      # code...

        $this->load->view('astrology/yogini_dasha_form');
                $this->load->view('astrology/inc/footer');

    }
     

    public function yogini_Dasha(){
       

          $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place'=>$this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

         //*****************Yogini Dasha****************//
        $data['responseData12'] = $vedicRishi->getCurrentYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $data['responseData13'] = $vedicRishi->getMajorYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $data['responseData14'] = $vedicRishi->getSubYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $this->load->view('astrology/yogini_dasha', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function char_dasha_input(){

      # code...

        $this->load->view('astrology/char_dasha_form');
                $this->load->view('astrology/inc/footer');

    }

    public function char_dasha(){
       

       $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'place'=>$this->input->post('birthplace'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

         //*****************Char Dasha****************//
        $data['responseData15'] = $vedicRishi->getCurrentCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $data['responseData16'] = $vedicRishi->getMajorCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $data['responseData17'] = $vedicRishi->getSubCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $signName[3]);

        $data['responseData18'] = $vedicRishi->getSubSubCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $signName[4], $signName[2]);

         $this->load->view('astrology/char_dasha', $data);
                 $this->load->view('astrology/inc/footer');
    }

    public function kalsarpa_dasha(){
       

       // make some dummy data in order to call vedic rishi api
             $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        //*****************Kalsarpa Dasha****************//
      $data['responseData19'] = $vedicRishi->getKalsarpaDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       // $this->session->set_userdata($data);
         $this->load->view('astrology/kalsarpa-dosha',$data);
                 $this->load->view('astrology/inc/footer');
    }


    public function pitri_dasha(){
          
       

         $place=$this->input->post('birthplace');
       // $longitude=astro_longitude($place);
        //$latitude=astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        //*****************Pitri Dasha****************//
       $data['responseData20'] = $vedicRishi->getPitriDoshaReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
//$this->session->set_userdata($data);
         $this->load->view('astrology/pitra-dosh', $data);
                 $this->load->view('astrology/inc/footer');
    }

    public function Sadhesati_dosha(){
           
        

               $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            ); 
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        //*****************Sadhesati Dosha****************//
       $data['responseData201'] = $vedicRishi->getSadhesatiLifeDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData202'] = $vedicRishi->getSadhesatiCurrentStatus($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $data['responseData203'] = $vedicRishi->getSadhesatiRemedies($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

     //  $this->session->set_userdata($data);
        $this->load->view('astrology/sadhesati', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function manglik_dosha(){
      
        $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //*****************Manglik Dosha****************//
       $data['responseData21'] = $vedicRishi->getManglikDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //$this->session->set_userdata($data);
        $this->load->view('astrology/manglik', $data);
                $this->load->view('astrology/inc/footer');
    }

   public function Aries(){
      $userId = "614806";
      $apiKey = "f3de721efdb41e0c426f1a2e701f12e6";

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'aries',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
        $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/aries');
               $this->load->view('astrology/inc/footer');
    }
    
    public function Taurus(){
        

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'taurus',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
       $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/taurus');
               $this->load->view('astrology/inc/footer');
    }
    public function Gemini()
    {
                     

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'gemini',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
     // $this->load->view('astrology/gemini');
               $this->load->view('astrology/inc/footer');
    }
    public function Cancer()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'cancer',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
        $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
     // $this->load->view('astrology/cancer');
               $this->load->view('astrology/inc/footer');
    }
    public function Leo()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'leo',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/leo');
               $this->load->view('astrology/inc/footer');
    }
    public function Virgo()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'virgo',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
     // $this->load->view('astrology/virgo');
               $this->load->view('astrology/inc/footer');
    }
    public function Libra()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'libra',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/libra');
               $this->load->view('astrology/inc/footer');
    }
    public function Scorpio()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'scorpio',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/scorpio');
               $this->load->view('astrology/inc/footer');
    }
    public function Sagittarius()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'sagittarius',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/sagittarius');
               $this->load->view('astrology/inc/footer');
    }
    public function Capricorn()
    {
                       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'capricorn',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/capricorn');
               $this->load->view('astrology/inc/footer');
    }
    public function Aquarius()
    {
                 

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'aquarius',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
      $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/aquarius');
               $this->load->view('astrology/inc/footer');
    }
    public function Pisces()
    {
                 

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'pisces',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** ZODIAC FUNCTIONS ****************************************************
        $data['responseData45'] = $vedicRishi->getYesterdaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData46'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $data['responseData47'] = $vedicRishi->getTomorrowsPrediction($data['zodiacSign'], $data['timezone']);
       $this->load->view('astrology/zodiacPrediction',$data);
      //$this->load->view('astrology/pisces');
               $this->load->view('astrology/inc/footer');
    }
    public function horoscope_chart()
    {
       

        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //*****************Horoscope Charts****************//
       $data['responseData22'] = $vedicRishi->getHoroChartById($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $chartId[4]);

       $data['responseData23'] = $vedicRishi->getExtendedHoroChartById($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $chartId[5]);

        $this->load->view('astrology/horoscope', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function suggestions_remedies_gemstone()
    {
       

        $place=$this->input->post('birthplace');
       // $longitude=astro_longitude($place);
       // $latitude=astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
       //print_r($latitude);
       //print_r($longitude);
       //exit();
      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //*****************Suggestions and Remedies****************//
       $data['responseData24'] = $vedicRishi->getBasicGemSuggestion($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

//$this->session->set_userdata($data);
        $this->load->view('astrology/gemstone', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function suggestions_remedies_rudraksha()
    {
       
                $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //*****************Suggestions and Remedies****************//

       $data['responseData25'] = $vedicRishi->getRudrakshaSuggestion($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

 // $this->session->set_userdata($data);
        $this->load->view('astrology/rudraksha', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function suggestions_remedies_puja()
    {
          
       // make some dummy data in order to call vedic rishi api
               $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //*****************Suggestions and Remedies****************//

       $data['responseData26'] = $vedicRishi->getPujaSuggestion($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

//$this->session->set_userdata($data);
        $this->load->view('astrology/puja', $data);
                $this->load->view('astrology/inc/footer');
    }

     public function ascendant_input()
    {
      $this->load->view('astrology/Ascendant_form');
              $this->load->view('astrology/inc/footer');
    }

    public function general_reports()
    {
         
       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //***************************************** GENERAL REPORTS FUNCTIONS ****************************************************
       $data['responseData27'] = $vedicRishi->getGeneralHouseReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[6]);

       $data['responseData28'] = $vedicRishi->getGeneralRashiReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[1]);

       $data['responseData29'] = $vedicRishi->getAscendantReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $data['responseData30'] = $vedicRishi->getNakshatraReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);


        $this->load->view('astrology/Ascendant-report', $data);
                $this->load->view('astrology/inc/footer');
    }

    public function nakshatra_input(){
      
       $this->load->view('astrology/nakshatra_form');
               $this->load->view('astrology/inc/footer');
     }

    public function nakshatra_prediction()
    {
       
       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'place'=>$place,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //****************************Nakshatra Prediction**********************//
       $data['responseData31'] = $vedicRishi->getDailyNakshatraPrediction($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData27'] = $vedicRishi->getGeneralHouseReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[6]);

       $data['responseData28'] = $vedicRishi->getGeneralRashiReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[1]);

       $data['responseData29'] = $vedicRishi->getAscendantReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $data['responseData30'] = $vedicRishi->getNakshatraReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);


        $this->load->view('astrology/nakshatra_result', $data);
        $this->load->view('astrology/inc/footer');
    }

    public function timezone()
    {
       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

        
       //****************************Nakshatra Prediction**********************//
       //****************************Timezone Wth DST**********************//
       //date formate -> mm-dd-yyyy
       $date = $data['month'].'-'.$data['date'].'-'.$data['year'];
       $data['timezoneData'] = $vedicRishi->timezoneWithDst($date, $data['latitude'], $data['longitude']);
       $this->load->view('templates/single/insurance/twowheeler', $data);
               $this->load->view('astrology/inc/footer');
    }


        public function panchang(){

       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** PANCHANG FUNCTIONS ****************************************************

       $data['responseData32'] = $vedicRishi->getBasicPanchang($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData32Next'] = $vedicRishi->getBasicPanchang($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData33'] = $vedicRishi->getPlanetPanchang($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
      $data['responseData33Next'] = $vedicRishi->getPlanetPanchang($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData34'] = $vedicRishi->getBasicPanchangSunrise($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
      $data['responseData34Next'] = $vedicRishi->getBasicPanchangSunrise($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData35'] = $vedicRishi->getPlanetPanchangSunrise($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData35Next'] = $vedicRishi->getPlanetPanchangSunrise($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData36'] = $vedicRishi->getAdvancedPanchang($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
      $data['responseData36Next'] = $vedicRishi->getAdvancedPanchang($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      $data['responseData37'] = $vedicRishi->getAdvancedPanchangSunrise($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData37Next'] = $vedicRishi->getAdvancedPanchangSunrise($data['NextDate'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $this->load->view('astrology/panchang', $data);
               $this->load->view('astrology/inc/footer');

    }

     public function numerology_input(){

      $this->load->view('astrology/numerology_form');
              $this->load->view('astrology/inc/footer');
     }

     public function numerology(){

      

       // make some dummy data in order to call vedic rishi api
     
       // make some dummy data in order to call vedic rishi api
       $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));

         //***************************************** NUMEROLOGY FUNCTIONS ****************************************************
       $data['responseData38'] = $vedicRishi->getNumeroTable($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData39'] = $vedicRishi->getNumeroReport($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData40'] = $vedicRishi->getNumeroFavTime($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData41'] = $vedicRishi->getNumeroPlaceVastu($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData42'] = $vedicRishi->getNumeroFastsReport($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData43'] = $vedicRishi->getNumeroFavLord($data['date'], $data['month'],  $data['year'], $data['name']);

        $data['responseData44'] = $vedicRishi->getNumeroFavMantra($data['date'], $data['month'],  $data['year'], $data['name']);

      $this->load->view('astrology/numerology_result', $data);
              $this->load->view('astrology/inc/footer');
     }

     public function ZodiacPredict(){
                  

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'zodiacSign' => 'aries',
              'date' =>  $curDate['mday'],
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** PANCHANG FUNCTIONS ****************************************************
        $data['responseData45'] = $vedicRishi->getTodaysPrediction($data['zodiacSign'], $data['timezone']);
        $this->load->view('astrology/zodiacPrediction',$data);
                $this->load->view('astrology/inc/footer');
     }

     public function Hora_muhurta(){
       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** PANCHANG FUNCTIONS ****************************************************

       $data['responseData48'] = $vedicRishi->getHoraMuhurta($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $this->load->view('astrology/hora_muhurta', $data);
               $this->load->view('astrology/inc/footer');

    }

         public function Chaughadiya_muhurta(){
       

       // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        //$longitude=$this->astro_longitude($place);
        //$latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);    

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'latitude' => 15.57283,//$loc['latitude'],//15.5817,//$latitude
              'longitude' =>72.88261,//$loc['longitude'],//72.8863,//$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );

      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));
         // call horoscope functions of Vedic Rishi Client

       //***************************************** PANCHANG FUNCTIONS ****************************************************

       $data['responseData49'] = $vedicRishi->getChaughadiyaMuhurta($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $this->load->view('astrology/chaughadiya_muhurta', $data);
               $this->load->view('astrology/inc/footer');
    }
    public function about_Us(){
      # code...
        $this->load->view('astrology/about_us');
        $this->load->view('astrology/inc/footer');
    }

    public function contact_Us(){
      # code...
        $this->load->view('astrology/contact_us');
        $this->load->view('astrology/inc/footer');
    }

    public function contact_submit(){
      # code...
        $data = array(
          'name' =>$this->input->post('Name'), 
          'email' =>$this->input->post('email'), 
          'phone' =>$this->input->post('phone'), 
          'message' =>$this->input->post('message')
        );

        $this->load->library('email');

        $config['mailtype'] = "html";

        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'localhost',
            'smtp_port' => 465,
            'smtp_user' => 'xxx',
            'smtp_pass' => 'xxx',
            'mailtype'  => 'html', 
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");

        $this->email->initialize($config);

        $this->email->from($this->input->post('email'), 'Blabla');
        $list = array('mohit9deshmukh@gmail.com');
        $this->email->to($list);
        $this->email->reply_to($this->input->post('email'), 'website');
        $this->email->subject('This is an email test');
        $this->email->message($this->input->post('message'));
        $this->email->send();

        if($this->email->send()){
          $this->session->set_flashdata("response","Congragulation Email Send Successfully.");
            redirect('astrology/contact_Us');
        }else{
          $this->session->set_flashdata("response",$this->email->print_debugger());
          // If any error come, its run
          redirect('astrology/contact_Us');
        }
  }
  public function kundli_pdf(){

        $this->load->view('astrology/kundli-pdf');
                $this->load->view('astrology/inc/footer');

    }
    public function kundli_print_form(){

        $this->load->view('astrology/print_astroReport_form');
                $this->load->view('astrology/inc/footer');

    }
    public function kundli_print(){
             // make some dummy data in order to call vedic rishi api
              $place=$this->input->post('birthplace');
        $longitude=$this->astro_longitude($place);
        $latitude=$this->astro_latitude($place);
       // make some dummy data in order to call vedic rishi api
        $curDate=getdate(date("U"));

        $tz=timezone_open("Asia/Kolkata");
        $loc=timezone_location_get($tz);
        $user_id= $this->input->post('user');  

        $data = array(
              'name' => $this->input->post('Name'),
              'date' =>  $this->input->post('date'),
              'month' => $this->input->post('month'),
              'year' => $this->input->post('year'),
              'hour' => $this->input->post('hour'),
              'minute' => $this->input->post('min'),
              'second' => $this->input->post('sec'),
              'user' => $user_id,
              'place'=>$place,
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
        $dataToday = array(
              'name' => $this->input->post('Name'),
              'date' =>  $curDate['mday'],
              'NextDate' =>  $curDate['mday']+1,
              'month' => $curDate['mon'],
              'weekday'=>$curDate['weekday'],
              'year' => $curDate['year'],
              'hour' => $curDate['hours'],
              'minute' => $curDate['minutes'],
              'second' => $curDate['seconds'],
              'place'=>$place,
              'latitude' => $latitude,
              'longitude' =>$longitude,
              'timezone' => 5.5,//$this->input->post('timezone'),
              'prediction_timezone' => 7.8//$this->input->post('prediction_timezone') // Optional. Only For Transit Prediction API
            );
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $purchase_id="KPDF".substr(str_shuffle($permitted_chars), 0, 8);

        $astroPurchaseData = array(
          'user_id' =>$user_id,
          'purchase_id'=>$purchase_id,
          'purchase_name'=>'Kundli_PDF',
          'purchase_price'=>499.00,
          'birth_day' =>  $this->input->post('date'),
          'birth_month' => $this->input->post('month'),
          'birth_year' => $this->input->post('year'),
          'birth_hour' => $this->input->post('hour'),
          'birth_min' => $this->input->post('min'),
          'birth_sec' => $this->input->post('sec'),
          'txn_date'=>date('Y-m-d h:i:s A'),
          'status'=>'Paid'
        );


      //planet name will be used for the planet ashtakvarga
      $planetName = ["sun", "moon", "mars", "mercury", "jupiter", "venus", "saturn" , "ascendant"];

      //sign name
      $signName = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];

      //chart Id to calculate horoscope chart
      $chartId = ['chalit','SUN','MOON','D1','D2','D3','D4','D5','D7','D8','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];

       // instantiate VedicRishiClient class
       $vedicRishi = new VedicRishiClient($userId, $apiKey);
       $this->vedicrishiclient->setLanguage($this->input->post('lang'));

       //*****************Basic Astro****************//$vedicRishi->getBirthDetails
      $data['responseData'] = $this->vedicrishiclient->getBirthDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
      $data['responseData1'] = $vedicRishi->getAstroDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       //-----------------Vimshottrari DAHSA-------------------
        $data['responseData9'] = $vedicRishi->getCurrentVimDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData10'] = $vedicRishi->getCurrentVimDashaAll($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData11'] = $vedicRishi->getMajorVimDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //-----------------Yogini DAHSA-------------------
        $data['responseData12'] = $vedicRishi->getCurrentYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData13'] = $vedicRishi->getMajorYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData14'] = $vedicRishi->getSubYoginiDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //-----------------CHAR DAHSA-------------------
               $data['responseData15'] = $vedicRishi->getCurrentCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData16'] = $vedicRishi->getMajorCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData17'] = $vedicRishi->getSubCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $signName[3]);
        $data['responseData18'] = $vedicRishi->getSubSubCharDasha($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $signName[4], $signName[2]);

        //*****************Kalsarpa Dasha****************//
      $data['responseData19'] = $vedicRishi->getKalsarpaDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      //*****************Pitri Dasha****************//
       $data['responseData20'] = $vedicRishi->getPitriDoshaReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

      //*****************Manglik Dosha****************//
       $data['responseData21'] = $vedicRishi->getManglikDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //*****************Suggestions and Remedies****************//
       $data['responseData24'] = $vedicRishi->getBasicGemSuggestion($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       //*****************Suggestions and Remedies****************//

       $data['responseData25'] = $vedicRishi->getRudrakshaSuggestion($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       

       //****************************Nakshatra Prediction**********************//
       $data['responseData31'] = $vedicRishi->getDailyNakshatraPrediction($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData27'] = $vedicRishi->getGeneralHouseReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[6]);

       $data['responseData28'] = $vedicRishi->getGeneralRashiReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone'], $planetName[1]);

       $data['responseData29'] = $vedicRishi->getAscendantReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $data['responseData30'] = $vedicRishi->getNakshatraReport($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //***************************************** PANCHANG FUNCTIONS ****************************************************
        $data['responseData37'] = $vedicRishi->getAdvancedPanchangSunrise($dataToday['NextDate'], $dataToday['month'], $dataToday['year'], $dataToday['hour'], $dataToday['minute'], $dataToday['latitude'], $dataToday['longitude'], $dataToday['timezone']);
        $data['responseData37Next'] = $vedicRishi->getAdvancedPanchangSunrise($dataToday['NextDate'], $dataToday['month'], $dataToday['year'], $dataToday['hour'], $dataToday['minute'], $dataToday['latitude'], $dataToday['longitude'], $dataToday['timezone']);

        //***************************************** NUMEROLOGY FUNCTIONS ****************************************************
       $data['responseData38'] = $vedicRishi->getNumeroTable($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData39'] = $vedicRishi->getNumeroReport($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData40'] = $vedicRishi->getNumeroFavTime($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData41'] = $vedicRishi->getNumeroPlaceVastu($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData42'] = $vedicRishi->getNumeroFastsReport($data['date'], $data['month'], $data['year'], $data['name']);

       $data['responseData43'] = $vedicRishi->getNumeroFavLord($data['date'], $data['month'],  $data['year'], $data['name']);

        $data['responseData44'] = $vedicRishi->getNumeroFavMantra($data['date'], $data['month'],  $data['year'], $data['name']);

        //*******************MUHURTAS***********************
        $data['responseData49'] = $vedicRishi->getChaughadiyaMuhurta($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
        $data['responseData48'] = $vedicRishi->getHoraMuhurta($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        //*****************Sadhesati Dosha****************//
       $data['responseData201'] = $vedicRishi->getSadhesatiLifeDetails($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);
       $data['responseData202'] = $vedicRishi->getSadhesatiCurrentStatus($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

       $data['responseData203'] = $vedicRishi->getSadhesatiRemedies($data['date'], $data['month'], $data['year'], $data['hour'], $data['minute'], $data['latitude'], $data['longitude'], $data['timezone']);

        $walletBalance=$this->db_model->select_multi('balance', 'wallet', array('userid' => $user_id));
        /*$walletBalance=$this->db->query('select balance from wallet where userid="'
                    .$user_id.'"')->row();*/
        $balance=$walletBalance->balance;
  
        $astroData=$this->db_model->select_multi('*', 'astro', array('user_id' => $user_id));
        /*$astroData=$this->db->query('select * from astro where user_id="'
                    .$user_id.'"')->row();*/

        if ($user_id) {

          if ($astroData->status == 'Paid' && $astroData->user_id == $user_id) {

            $this->load->view('astrology/print_astroReport',$data);

          }else if ($balance>=499 && ($astroData->status == 'Unpaid' || !$astroData->status)) {
            
            $new_balance=$balance-499;

            /*$this->db->query(
                'update wallet 
                SET balance="'.$new_balance.'"
                WHERE userid = "'.$user_id.'"' );*/

            $this->db_model->update(array('balance' => $new_balance), 'wallet', array('userid' => $user_id));

            $this->db->insert('astro', $astroPurchaseData);

          $this->load->view('astrology/print_astroReport',$data);

          }else {

              $this->session->set_flashdata("response","Insufficient Balance.");
              redirect('astrology/kundli_print_form');
          }
               
        }
                  
    }

}