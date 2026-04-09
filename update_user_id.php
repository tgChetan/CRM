<?php
$con = mysqli_connect("localhost","tglevel_support","Tglevels@123$","tglevel_support");
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
$user_id = $_GET['user_id'];
$phone = $_GET['phone'];
$check_user_id_exists = mysqli_query($con,"SELECT * FROM sb_users_data WHERE slug='user_id' AND value='$user_id'");
if(mysqli_num_rows($check_user_id_exists)==0)
{
	$get_user_id = mysqli_query($con,"SELECT * FROM sb_users_data WHERE slug='phone' AND value='$phone'");
	$row = mysqli_fetch_assoc($get_user_id);
	$crm_user_id = $row['user_id'];
	
	mysqli_query($con,"INSERT INTO `sb_users_data`(`user_id`, `slug`, `name`, `value`) VALUES ('$crm_user_id','user_id','user id','$user_id')"); 
}
?>