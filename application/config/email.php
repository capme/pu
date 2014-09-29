<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Email
| -------------------------------------------------------------------------
| This file lets you define parameters for sending emails.
| Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/libraries/email.html
|
*/
$config['protocol']		='smtp';
//$config['smtp_host']	='ssl://smtp.googlemail.com'; 
//$config['smtp_port']	='465'; 
$config['smtp_timeout']	='30';
//$config['smtp_user']	='marcos@velaasia.com'; 
//$config['smtp_pass']	='Marcos123';

// doni: faster using local relay
$config['smtp_host']	='localhost'; 
$config['smtp_port']	='25';

$config['mailtype'] 	= 'html';
$config['charset'] 		= 'utf-8';
$config['newline'] 		= "\r\n";


/* End of file email.php */
/* Location: ./application/config/email.php */