<?php

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

require('inc/vnpay_php/config.php');

date_default_timezone_set("Asia/Ho_Chi_Minh");

session_start();

if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
    redirect('index.php');
}

if (isset($_POST['pay_now'])) {
    try {
        //code...


        // $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        // $vnp_Returnurl = "http://localhost/MHHotelBooking/";
        // $vnp_TmnCode = "9QHXP6U6"; //Mã website tại VNPAY 
        // $vnp_HashSecret = "559MF52FLTM167HOBXD02MVO7GXSWAPV"; //Chuỗi bí mật

        $vnp_TxnRef = 'ORD_' . $_SESSION['uId'] . random_int(11111, 9999999); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này  sang VNPAY
        $vnp_OrderInfo = 'R_' . $_SESSION['room']['id'] .' / '. 'U_' . $_SESSION['uId'];
        $vnp_OrderType = 'booking';
        $vnp_Amount = $_SESSION['room']['payment'] * 100;

        // $vnp_OrderInfo = 'Đặt cọc 50%';
        // $vnp_OrderType = 'billpayment';
        // Tính 50% tổng số tiền
        //$depositAmount = $_SESSION['room']['payment'] * 0.5;
        // Nhân với 100 vì VNPAY yêu cầu số tiền tính theo đơn vị VNĐ nhân 100
        //$vnp_Amount = $depositAmount * 100;

        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        );
        if (isset($_POST['redirect'])) {
            //echo ('alo');
            header('Location: ' . $vnp_Url);

            // Insert payment data into database
            $CUST_ID = $_SESSION['uId'];

            $frm_data = filteration($_POST);
            $vnp_Amount =  $vnp_Amount/100;
            $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `order_id`,`trans_amt`) VALUES (?,?,?,?,?,?)";
            insert($query1, [$CUST_ID, $_SESSION['room']['id'], $frm_data['checkin'], $frm_data['checkout'], $vnp_TxnRef, $vnp_Amount], 'isssss');
    
            $booking_id = mysqli_insert_id($con);
    
            $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, `total_pay`,`user_name`, `phonenum`, `address`) VALUES (?,?,?,?,?,?,?)";
            
            insert($query2, [$booking_id, $_SESSION['room']['name'], $_SESSION['room']['price'], $vnp_Amount, $frm_data['name'], $frm_data['phonenum'], $frm_data['address']], 'issssss');
            
            die();
        } else {
            echo json_encode($returnData);
        }
        
        // die();
    } catch (Throwable $th) {
        echo ($th);
        //throw $th;
    }
}

if(isset($_POST['deposit_now'])){
    try {
        //code...


        // $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        // $vnp_Returnurl = "http://localhost/MHHotelBooking/";
        // $vnp_TmnCode = "9QHXP6U6"; //Mã website tại VNPAY 
        // $vnp_HashSecret = "559MF52FLTM167HOBXD02MVO7GXSWAPV"; //Chuỗi bí mật

        $vnp_TxnRef = 'ORD_' . $_SESSION['uId'] . random_int(11111, 9999999); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này  sang VNPAY
        $vnp_OrderInfo = 'R_' . $_SESSION['room']['id'] .' / '.'U_' . $_SESSION['uId'] . ' deposit 50%';
        // $vnp_OrderType = 'booking';
        $vnp_Amount = $_SESSION['room']['payment'] * 100 ;

        // $vnp_OrderInfo = 'Đặt cọc 50%';
        $vnp_OrderType = 'booking';
        // Tính 50% tổng số tiền
        $depositAmount = $_SESSION['room']['payment'] *50;
        // Nhân với 100 vì VNPAY yêu cầu số tiền tính theo đơn vị VNĐ nhân 100
        //$vnp_Amount = $depositAmount * 100;

        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $depositAmount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        );
        if (isset($_POST['redirect'])) {
            //echo ('alo');
            header('Location: ' . $vnp_Url);

            // Insert payment data into database
            $CUST_ID = $_SESSION['uId'];

            $frm_data = filteration($_POST);
            $vnp_Amount =  $vnp_Amount/100;
            $depositAmount =  $depositAmount/100;
            $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `order_id`,`trans_amt`) VALUES (?,?,?,?,?,?)";
            insert($query1, [$CUST_ID, $_SESSION['room']['id'], $frm_data['checkin'], $frm_data['checkout'], $vnp_TxnRef, $depositAmount], 'isssss');
    
            $booking_id = mysqli_insert_id($con);
    
            $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, `total_pay`,`user_name`, `phonenum`, `address`) VALUES (?,?,?,?,?,?,?)";
            
            insert($query2, [$booking_id, $_SESSION['room']['name'], $_SESSION['room']['price'], $vnp_Amount, $frm_data['name'], $frm_data['phonenum'], $frm_data['address']], 'issssss');
            
            die();
        } else {
            echo json_encode($returnData);
        }
        // die();
    } catch (Throwable $th) {
        echo ($th);
        //throw $th;
    }
}   