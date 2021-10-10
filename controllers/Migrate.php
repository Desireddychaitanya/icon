<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller
{
    /**
     * Check Valid Login or display login page.
     */
    public function __construct()
    {
        parent::__construct();
        // load migration library
        $this->load->library('migration');
    }

    public function index()
    {

        if (!$this->migration->current())
        {
            echo 'Error' . $this->migration->error_string();
        } else {
            echo 'Migrations ran successfully!';
        }   
    }    

}