<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');


// // init configuration
// $env = parse_ini_file('.env');
// define('CLIENT_ID', $env['CLIENT_ID']);
// define('CLIENT_SECRET', $env['CLIENT_SECRET']);
// define('REDIRECT_URI', $env['REDIRECT_URI']);
// $clientID = CLIENT_ID;
// $clientSecret = CLIENT_SECRET;
// $redirectUri = REDIRECT_URI;

// // create Client Request to access Google API
// $client = new Google_Client();
// $client->setClientId($clientID);
// $client->setClientSecret($clientSecret);
// $client->setRedirectUri($redirectUri); 
// $client->addScope("email");
// $client->addScope("profile");

// // authenticate code from Google OAuth Flow

// if (isset($_GET['code'])) {
//   $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
//   if (isset($token['access_token']) && !empty($token['access_token'])) {
//     $client->setAccessToken($token['access_token']);

//     // get profile info
//     $google_oauth = new Google\Service\Oauth2($client);
//     $google_account_info = $google_oauth->userinfo->get();
//     $email =  $google_account_info->email;
//     $name =  $google_account_info->name;

//     header('Location: rooms.php');
//     exit;
//   }
//   // now you can use this profile info to create account in your website and make user logged in.
// } else {
//   header('Location:' . $client->createAuthUrl());
//   exit;
// }
// Initialize the session
session_start();
// Update the following variables
$env = parse_ini_file('.env');
define('CLIENT_ID', $env['CLIENT_ID']);
define('CLIENT_SECRET', $env['CLIENT_SECRET']);
define('REDIRECT_URI', $env['REDIRECT_URI']);
$google_oauth_client_id = CLIENT_ID;
$google_oauth_client_secret = CLIENT_SECRET;
$google_oauth_redirect_uri = REDIRECT_URI;
$google_oauth_version = 'v3';
// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => $google_oauth_client_id,
        'client_secret' => $google_oauth_client_secret,
        'redirect_uri' => $google_oauth_redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);
    // Make sure access token is valid
    if (isset($response['access_token']) && !empty($response['access_token'])) {
        // Execute cURL request to retrieve the user info associated with the Google account
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/' . $google_oauth_version . '/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        $response = curl_exec($ch);
        curl_close($ch);
        $profile = json_decode($response, true);
        // Make sure the profile data exists
        if (isset($profile['email'])) {
            $google_name_parts = [];
            $google_name_parts[] = isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
            $google_name_parts[] = isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';
            // Authenticate the user
            $u_exist = select("SELECT * FROM `user_cred` WHERE `email` =?  LIMIT 1", [$profile['email']], "s");
            if (mysqli_num_rows($u_exist) == 0) {
                try {
                    $query = "INSERT INTO `user_cred`(`name`, `email`, `is_verified`) VALUES (?,?,?)";

                    $values = [implode(' ', $google_name_parts), $profile['email'], 1];
                    if (insert($query, $values, 'sss')) {
                        $u_exist =select("SELECT * FROM `user_cred` WHERE `email` =?  LIMIT 1", [$profile['email']], "s");
                        if (isset($u_exist)) {
                            $u_fetch = mysqli_fetch_assoc($u_exist);
                            session_start();
                            $_SESSION['login'] = true;
                            $_SESSION['uId'] = $u_fetch['id'];
                            $_SESSION['uName'] = $u_fetch['name'];
                            $_SESSION['uPic'] = $u_fetch['profile'];
                            $_SESSION['uPhone'] = $u_fetch['phonenum'];
                            header('Location: index.php');
                            exit;
                        }
                    } else {
                        echo 'ins_failed';
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                // echo 'Please register before!';
            } else {
                $u_fetch = mysqli_fetch_assoc($u_exist);
                if ($u_fetch['is_verified'] == 0) {
                    echo"<script>alert('Email is not verified!')</script>";
                    redirect('index.php');
                } else if ($u_fetch['status'] == 0) {
                    echo 'inactive';
                } else {
                    session_start();
                    $_SESSION['login'] = true;
                    $_SESSION['uId'] = $u_fetch['id'];
                    $_SESSION['uName'] = $u_fetch['name'];
                    $_SESSION['uPic'] = $u_fetch['profile'];
                    $_SESSION['uPhone'] = $u_fetch['phonenum'];
                    header('Location: index.php');
                    exit;
                }
            }
            //         session_regenerate_id();

            // $_SESSION['google_name'] = implode(' ', $google_name_parts);
            // $_SESSION['google_picture'] = isset($profile['picture']) ? $profile['picture'] : '';
            // // Redirect to profile page
            // header('Location: index.php');
            // exit;
        } else {
            exit('Please try Register before Login by Google!');
        }
    } else {
        exit('Invalid access token! Please try again later!');
    }
} else {
    // Define params and redirect to Google Authentication page
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    header('Location: https://accounts.google.com/o/oauth2/auth?' . http_build_query($params));
    exit;
}
