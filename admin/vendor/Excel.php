<?php
/**
 * Created by PhpStorm.
 * User: Simone
 * Date: 23/06/2015
 * Time: 10.45
 */

//if (!defined('BASEPATH')) exit('No direct script access allowed');

//require_once APPPATH."third_party/PHPExcel.php";
require_once "PHPExcel.php";
class Excel extends PHPExcel {
    public function __construct() {
        parent::__construct();
    }
}
// https://arjunphp.com/how-to-use-phpexcel-with-codeigniter/#sthash.SXfSeawb.dpuf