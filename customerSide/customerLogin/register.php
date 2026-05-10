<?php
    require_once '../config.php';
    session_start();

    // Define variables and initialize empty values
    $email = $member_name = $password = $phone_number = "";
    $email_err = $member_name_err = $password_err = $phone_number_err = "";

    // Check submition
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email.";
        } else if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email. Ex: johndoe@email.com";
        } else {
            $email = trim($_POST["email"]);
        }

        $selectCreatedEmail = "SELECT email from Accounts WHERE email = ?";

        if($stmt = $link->prepare($selectCreatedEmail)){
            $stmt->bind_param("s", $_POST['email']);

            $stmt->execute();

            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $email_err = "This email is already registered.";
            } else {
                $email = trim($_POST["email"]);
            }
            $stmt->close();
        }

        // Validate member name
        if (empty(trim($_POST["member_name"]))) {
            $member_name_err = "Please enter your member name.";
        } else {
            $member_name = trim($_POST["member_name"]);
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter a password.";
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Validate phone number
        if (empty(trim($_POST["phone_number"]))) {
            $phone_number_err = "Please enter your phone number.";
        } else if(!is_numeric(trim($_POST['phone_number']))){
            $phone_number_err = "Only enter numeric values!";
        } else {
            $phone_number = trim($_POST["phone_number"]);
        }

        // Check input errors 
        if (empty($email_err) && empty($member_name_err) && empty($password_err) && empty($phone_number_err)) {
            mysqli_begin_transaction($link);

            $sql_accounts = "INSERT INTO Accounts (email, password, phone_number, register_date) VALUES (?, ?, ?, NOW())";
            if ($stmt_accounts = mysqli_prepare($link, $sql_accounts)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt_accounts, "sss", $param_email, $param_password, $param_phone_number);

                // Set parameters
                $param_email = $email;
                $param_password = $password;
                $param_phone_number = $phone_number;
            }

            if (mysqli_stmt_execute($stmt_accounts)) {
                // last inserted account_id
                $last_account_id = mysqli_insert_id($link);

                // Prepare an insert statement for Memberships table
                $sql_memberships = "INSERT INTO Memberships (member_name, points, account_id) VALUES (?, ?, ?)";
                if ($stmt_memberships = mysqli_prepare($link, $sql_memberships)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt_memberships, "sii", $param_member_name, $param_points, $last_account_id);

                    // Set parameters for Memberships table
                    $param_member_name = $member_name;
                    $param_points = 0; // initial value for points

                    if (mysqli_stmt_execute($stmt_memberships)) {
                        mysqli_commit($link);

                        // Rredirect to the login page
                        header("location: register_process.php");
                        exit;

                    } else {
                        // Rollback
                        mysqli_rollback($link);
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($stmt_memberships);
                }

            } else {
                // Rollback
                mysqli_rollback($link);
                echo "Oops! Something went wrong. Please try again later.";
            }
                mysqli_stmt_close($stmt_accounts);
        }
    }
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="authent.css">
    <title>Register</title>
</head>
<body>
    <section>
        <div class="form_div">

            <div class="form_header">

                <h2>Sign Up</h2>

                <div class="form_header_button">
                    <h5><a href="../home/home.php#hero">HOME</a></h5>
                </div>

            </div>
            
            <div class="wrapper">

                <form id="authentication_form" action="register.php" method="post">

                    <label for="uName">Name:</label>
                    <span ><?php echo $member_name_err; ?></span>
                    <input type="text" id="uName" name="member_name" placeholder="Enter your name">
                    

                    <label for="Email">Email:</label>
                    <span ><?php echo $email_err; ?></span>
                    <input type="email" id="email" name="email" placeholder="Enter your email">
                    
                    <label for="pNum">Phone No:</label>
                    <span ><?php echo $phone_number_err; ?></span>
                    <input type="number" id="pNum" name="phone_number" placeholder="Enter your Mobile Number">
                    

                    <label for="password">Password:</label>
                    <span ><?php echo $password_err; ?></span>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    

                    <input type="submit" value="Register" name="register">

                    <p>By registering, I gree to the <a href="">terms</a> and <a href="">conditions</a>.</p>

                </form>
            
                <p>Alredy have an account? <a href="login.php">Login</a>.</p>
                
            </div>
        </div>
    </section>
</body>
</html>