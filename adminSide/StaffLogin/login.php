<?php 
session_start();
if(isset($_SESSION['logged_account_id'])) {
    header("Location: ../panel/pos-panel.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../customerSide/customerLogin/authent.css">
    <title>Login</title>
</head>
<body>
    <section id="signup">
        <div class="form_div">
            
            <div class="form_header">

                <h2>Staff Login</h2>

                <div class="switch_login">
                    
                    <div class="form_header_button_off">
                        <h5><a href="../../customerSide\customerLogin\login.php">Member</a></h5>
                    </div>

                    <div class="form_header_button">
                        <h5><a href="">Staff</a></h5>
                    </div>

                </div>
                
            </div>
                
            <div class="wrapper">

                <?php 

                if(!empty($login_err)){
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }

                ?>

                <form id="authentication_form" action="login_process.php" method="post">
                    <label for="account_id">Staff Id:</label>
                    <input type="number" id="account_id" name="account_id" placeholder="Enter your ID" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>

                    <div id="form_middle_div">

                        <div id="saveLogin">
                            <input type="checkbox" name="save_login" id="saveLogin_checkbox">
                            <label for="saveLogin_checkbox">Save my login.</label>
                        </div>

                    </div>
                    
                    <input type="submit" value="Login">
                </form>
                <!-- <div style="display:flex; justify-content:space-between;"> -->
                    <!-- <p>Staff Only</p> -->
                    <p><a href="../../customerSide/home/home.php">Home</a></p>
                <!-- </div> -->
                
            </div>
        </div>
    </section>
</body>
</html>