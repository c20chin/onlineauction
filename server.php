<?php

//session_start();
//Initialising variable
$email = "";
$password = "";
//error message
$errors = array();
//connect to db
$db = mysqli_connect('localhost', 'root', 'root', 'online-auction') or die('could not connect to database');
//Register users
if (isset($_POST['reg_user'])) {
    $firstname = mysqli_real_escape_string($db, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($db, $_POST['lastname']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $addressline_1 = mysqli_real_escape_string($db, $_POST['addressline_1']);
    $addressline_2 = mysqli_real_escape_string($db, $_POST['addressline_2']);
    $postal_code = mysqli_real_escape_string($db, $_POST['postal_code']);
    $phone_number = mysqli_real_escape_string($db, $_POST['phone_number']);
    $user_type = mysqli_real_escape_string($db, $_POST['user_type']);

    //form validation
    if (empty($firstname)) {
        array_push($errors, "First name is required");
    };
    if (empty($lastname)) {
        array_push($errors, "Last name is required");
    };
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    };
    if ($password_1 != $password_2) {
        array_push($errors, "Passwords do not match");
    }
    if (empty($addressline_1)) {
        array_push($errors, "Address is required");
    };
    if (empty($postal_code)) {
        array_push($errors, "Postal code is required");
    };
    if (empty($phone_number)) {
        array_push($errors, "Phone number is required");
    };
    if (empty($user_type)) {
        array_push($errors, "User type is required");
    };

    //check db for existing user with same username

    $user_check_query = "SELECT * FROM users WHERE email= '$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['email'] === $email) {
            array_push($errors, "Email already registered.");
        }
    }

    //Register the user if no error
    if (count($errors) === 0) {
        $password = md5($password_1); //encrypt the password
        $query = "INSERT INTO users (firstname, lastname, email, password, addressline_1, addressline_2, postal_code, phone_number, user_type) 
        VALUES ('$firstname','$lastname','$email','$password','$addressline_1', '$addressline_2', '$postal_code', '$phone_number', '$user_type'
        )";
        mysqli_query($db, $query);
        session_start();
        $_SESSION['email'] = $email;

        header('location: index.php');
        exit();
    }
}


?>
