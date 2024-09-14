<?php

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

require('inc/vnpay_php/config.php');

date_default_timezone_set("Asia/Ho_Chi_Minh");

session_start();
unset($_SESSION['room']);

function regenrate_session($uid)
{
    $user_q = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$uid], 'i');
    $user_fetch = mysqli_fetch_assoc($user_q);

    $_SESSION['login'] = true;
    $_SESSION['uId'] = $user_fetch['id'];
    $_SESSION['uName'] = $user_fetch['name'];
    $_SESSION['uPic'] = $user_fetch['profile'];
    $_SESSION['uPhone'] = $user_fetch['phonenum'];
}

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
if ($secureHash == $vnp_SecureHash) {

    $slct_query = "SELECT `booking_id`, `user_id` FROM `booking_order` WHERE `order_id` = '$_GET[vnp_TxnRef]'";

    $slct_res = mysqli_query($con, $slct_query);

    if (mysqli_num_rows($slct_res) == 0) {
        redirect('index.php');
    }

    $slct_fetch = mysqli_fetch_assoc($slct_res);

    if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        regenrate_session($slct_fetch['user_id']);
    }
    if ($_GET['vnp_ResponseCode'] == '00') //GD thanh cong
    {
        // $vnp_Amount=$_GET['vnp_Amount']/100;
        // $upd_query = "
        //     DELETE `booking_order` 
        //     SET `booking_status`='booked', `trans_id`='$_GET[vnp_TxnRef]', `trans_status`='$_GET[vnp_ResponseCode]', `trans_resp_msg`='success' 
        //     WHERE `booking_id`='$slct_fetch[booking_id]'; 

        //     UPDATE `booking_details` 
        //     SET `paid`='$vnp_Amount' 
        //     WHERE `booking_id`='$slct_fetch[booking_id]'
        // ";

        // mysqli_multi_query($con, $upd_query);

        $upd_query = "UPDATE `booking_order` SET `booking_status`='booked', `trans_id`='$_GET[vnp_TxnRef]', `trans_status`='$_GET[vnp_ResponseCode]', 
            `trans_resp_msg`='success' 
             WHERE `booking_id`='$slct_fetch[booking_id]'";


        mysqli_query($con, $upd_query);

    } else {
        // $del_query1 = "DELETE FROM `booking_details` WHERE `booking_id`='$slct_fetch[booking_id]'";
        // $del_query2 = "DELETE FROM `booking_order` WHERE `booking_id`='$slct_fetch[booking_id]'";
        // mysqli_query($con, $del_query1);
        // mysqli_query($con, $del_query2);
        $upd_query = "UPDATE `booking_order` SET `booking_status`='payment failed', `trans_id`='$_GET[vnp_TxnRef]', `trans_status`='$_GET[vnp_ResponseCode]', 
            `trans_resp_msg`='failed' 
             WHERE `booking_id`='$slct_fetch[booking_id]'";

        mysqli_query($con, $upd_query);
    }
    redirect('pay_status.php?order=' . $_GET['vnp_TxnRef']);
} else {
    // echo "<span style='color:red'>Chu ky khong hop le</span>";
    redirect('index.php');
}
