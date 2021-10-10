<?php 

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

/**
 * Class Site
 */
class User extends MY_Controller
{
    public function __construct()
    {
		//header('Access-Control-Allow-Origin: *');
		//header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        parent::__construct();		
    }
	
	 public function loginUser()
	 {
		 echo 'hi';
	 }

    public function login()
    {
	
	   $jsondata = json_decode(file_get_contents('php://input'), true);
	    //debug.log(1);
	   
	   $user =$jsondata['username'];
	   $password=$jsondata['password'];
	   
       $data = $this->db_model->select_multi("id, name, password, email, last_login_ip, last_login, status", 'member', array('id' => $user));
        if (($data->status !== "Active") && ($data->status !== "Inactive") && ($data->status !== "Suspend")) {				
		   $response['message'] = 'Login is invalid or Your account is not active. Account status is: ' . ($data->status ? $data->status : 'N/A');
		}

        if (password_verify($password, $data->password)) {
			//$response['id']=$data->id;
			//$response['username']=$data->name;
			//$response['password']=$data->password;
			//$response['firstName']="rohini";
			//$response['lastName']="manjunath";
			//$response['token']='fake-jwt-token';
			$response['message']="valid User";
			
			//echo 'User IP - '.$_SERVER['REMOTE_ADDR'];
			
			if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
   // echo $ip;
	
	$ip_server = $_SERVER['SERVER_ADDR']; 
	
	//echo $_SERVER['SERVER_NAME'];
	
	//echo $_SERVER['HTTP_HOST'];
  
// Printing the stored address 
//echo "Server IP Address is: $ip_server"; 
			
			echo json_encode($response);		
			header('Content-Type: application/json', true, 200);

        } else {
				$response['message']="Invalid Username or Password.";
				echo json_encode($response);
				header('Content-Type: application/json', true, 400);
        }
    }
}