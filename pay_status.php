<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Links Design -->
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - BOOKING STATUS</title>

</head>

<body class="bg-light">

  <!-- Header Design -->
  <?php require('inc/header.php'); ?>

  <div class="container">
    <div class="row">

      <div class="col-12 my-5 mb-3 px-4">
        <h2 class="fw-bold">PAYMENT STATUS</h2>
      </div>

      <?php
      $frm_data = filteration($_GET);

      if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        redirect('index.php');
      }

      $booking_q = "SELECT bo.*, bd.* FROM `booking_order` bo
          INNER JOIN `booking_details` bd ON bo.booking_id=bd.booking_id
          WHERE bo.order_id=? AND bo.user_id=? AND bo.booking_status!=?";

      $booking_res = select($booking_q, [$frm_data['order'], $_SESSION['uId'], 'pending'], 'sis');
      //$booking_fetch = mysqli_fetch_assoc($booking_res);

      // if(mysqli_num_rows($booking_res)==0){
      //   redirect('index.php');
      // }

      $booking_fetch = mysqli_fetch_assoc($booking_res);

      // if ($booking_fetch['trans_status'] == '00') {
      //     echo <<<data
      //         <div class="col-12 px-4">
      //           <p class="fw-bold alert alert-success">
      //             <i class="bi bi-check-circle-fill"></i>
      //             Payment done! Booking successful.
      //             <br><br>
      //             <a href='bookings.php'>Go to Bookings</a>
      //           </p>
      //         </div>
      //       data;
      // }
      // else{
      //   echo <<<data
      //         <div class="col-12 px-4">
      //           <p class="fw-bold alert alert-danger">
      //             <i class="bi bi-exclamation-triangle-fill"></i>
      //             Payment failed!
      //             <br><br>
      //             <a href='bookings.php'>Go to Bookings</a>
      //           </p>
      //         </div>
      //       data;
      // }

      if ($booking_fetch['trans_status'] == '00') {
        echo <<<data
            <div class="col-12 px-4">
              <p class="fw-bold alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                Payment done! Booking successful.
                <br><br>
                <a href='bookings.php'>Go to Bookings</a>
              </p>
            </div>
          data;
      }
      if (mysqli_num_rows($booking_res) == 0 || $booking_fetch['trans_status'] != '00') {
        echo <<<data
          <div class="col-12 px-4">
            <p class="fw-bold alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              Payment failed!
              <br><br>
              <a href='bookings.php'>Go to Bookings</a>
            </p>
          </div>
        data;
      }

      ?>

    </div>
  </div>


  <!-- Footer Design -->
  <?php require('inc/footer.php'); ?>

</body>

</html>