<?php

require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['get_bookings'])) 
{
    $frm_data = filteration($_POST);

    $query = "SELECT bo.*, bd.* FROM `booking_order` bo
            INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
            WHERE (bo.order_id LIKE ? OR bd.phonenum LIKE ? OR bd.user_name LIKE ?)
            AND (bo.booking_status=? AND bo.arrival=?) ORDER BY bo.booking_id ASC"; // LIKE search theo 3 field do

    $res = select($query, ["%$frm_data[search]%","%$frm_data[search]%","%$frm_data[search]%","booked",0],'sssss'); //result, booked=booking_status; 0=arrival
    $i = 1;
    $table_data = "";

    if(mysqli_num_rows($res)==0){
        echo"<b>No Data Found!</b>";
        exit;
    }

    while ($data = mysqli_fetch_assoc($res)) {
        $date = date("d-m-Y", strtotime($data['datentime'])); //chuyển đổi chuỗi ngày giờ
        $checkin = date("d-m-Y", strtotime($data['check_in']));
        $checkout = date("d-m-Y", strtotime($data['check_out']));

        $formatted_number1 = number_format($data['price'], 0, '.', ',');
        $formatted_number2 = number_format($data['trans_amt'], 0, '.', ',');

        $table_data .= "
                <tr>
                    <td>$i</td>
                    <td>
                        <span class='badge bg-primary'>
                            Order ID: $data[order_id]
                        </span>
                        <br>
                        <b>Name:</b> $data[user_name]
                        <br>
                        <b>Phone No:</b> $data[phonenum]
                    </td>
                    <td>
                        <b>Room:</b> $data[room_name]
                        <br>
                        <b>Price:</b> {$formatted_number1}đ
                    </td>
                    <td>
                        <b>Check-in:</b> $checkin
                        <br>
                        <b>Check-out:</b> $checkout
                        <br>
                        <b>Paid:</b> {$formatted_number2}đ
                        <br>
                        <b>Date:</b> $date
                    </td>
                    <td>
                        <button type='button' onclick='assign_room($data[booking_id])' class='btn text-white btn-sm fw-bold custom-bg shadow-none' data-bs-toggle='modal' data-bs-target='#assign-room'>
                            <i class='bi bi-check2-square'></i> Assign Room
                        </button>
                        <br>
                        <button type='button' onclick='cancel_booking($data[booking_id])' class='mt-2 btn btn-outline-danger btn-sm fw-bold shadow-none'>
                            <i class='bi bi-trash'></i> Cancel Booking
                        </button>
                    </td>
                </tr>
            ";

        $i++;
    }
    echo $table_data;
}

if (isset($_POST['assign_room']))
{
    $frm_data = filteration($_POST);

    $query = "UPDATE `booking_order` bo INNER JOIN `booking_details` bd
        ON bo.booking_id = bd.booking_id
        SET bo.arrival = ?, bo.rate_review = ?, bd.room_no = ?
        WHERE bo.booking_id = ?";
    
    $values = [1,0,$frm_data['room_no'],$frm_data['booking_id']];

    $res = update($query,$values,'iisi'); // it will update 2 rows so it will return 2

    echo ($res==2) ? 1 : 0;  //1 = true; 2 = false
}

if (isset($_POST['cancel_booking'])) {
    $frm_data = filteration($_POST);

    $query = "UPDATE `booking_order` SET `booking_status`=?, `refund`=? WHERE `booking_id`=?";
    $values = ['cancelled',0,$frm_data['booking_id']];
    $res = update($query,$values,'sii');

    echo $res;
}

