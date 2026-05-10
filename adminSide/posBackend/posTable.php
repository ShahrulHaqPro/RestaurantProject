<?php
session_start();
require_once 'checkIfLoggedIn.php';
?>
<?php
include '../inc/Header-dash.php';
require_once '../config.php';
?>

<style>
    .indicator-section{
        width: 100%;
    }
    .indication-rows {
        display: flex;
        flex-wrap:wrap;
        justify-content: center;
        flex-direction: row;
        gap: 15px;
        margin-bottom: 20px;
        height: fit-content;
        padding-block: 10px;
    }

    .indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 30px;
        color: white;
        font-weight: bold;
        border-radius: 5px;
    }

    .indicator.available {
        background-color: var(--deep-green);
    }

    .indicator.occupied {
        background-color: var(--deep-red);
    }

    .indicator.nobill {
        background-color: var(--deep-ash);
    }

    .indicator.reserved {
        background-color: var(--deep-yellow);
    }

    /**-------------------------------------------- */

    
    .table-section {
        width: 100%;
    }

    .table {
        width: 120px;
        height: 120px;
        margin: 5px;
        border-radius: 8px;
        float: left;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: 0.3s;
        
    }
    .table:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .table h4{
        color: white;
        margin-top: 2px;
    }
    
</style>

<div class="container">
    <div id="POS-Content" class="page-content" > 
        <div class="indicator-section">
            <div class="indication-rows">
                <div class="indicator available">Available</div>
                <div class="indicator occupied">Seated</div>
                <div class="indicator nobill">No Bill</div>
                <div class="indicator reserved">Reserved</div>
            </div>
        </div>
        <div class="tables-section">
            <?php
                // Fetch all tables from the database
                $query = "SELECT * FROM Restaurant_Tables ORDER BY table_id;";
                $result = mysqli_query($link, $query);
                $table = array("", "", "");
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        $table_id = $row['table_id'];
                        $capacity = $row['capacity'];
                        

                        $sqlBill = "SELECT bill_id FROM Bills WHERE table_id = $table_id ORDER BY bill_time DESC LIMIT 1";
                        $result1 = $link->query($sqlBill);
                        $latestBillData = $result1->fetch_assoc();
                        
                        // Check if the table is reserved for the selected time
                        date_default_timezone_set('Asia/Colombo');
                        $selectedDate = date("Y-m-d");
                        $endTime = date("H:i:s");

                        // Calculate the end time of the 20-minute range
                        $startTime = date("H:i:s", strtotime($endTime) - (20 * 60));
                        // Check if there's a reservation within the 20-minute range
                        $reservationQuery = "SELECT * FROM reservations WHERE table_id = $table_id AND reservation_date = '$selectedDate' AND reservation_time BETWEEN '$startTime' AND '$endTime'";
                        $reservationResult = mysqli_query($link, $reservationQuery);
                        
                        //Show all reservations
                        if ($latestBillData) {
                            $latestBillID = $latestBillData['bill_id'];

                            $sqlBillItems = "SELECT * FROM bill_items WHERE bill_id = $latestBillID";
                            $result2 = $link->query($sqlBillItems);
                            if ($result2 && mysqli_num_rows($result2) > 0) {
                                $billItemColor = 'var(--deep-red)';
                            } else {
                                $billItemColor = 'var(--deep-green)';
                            }

                            $paymentTimeQuery = "SELECT payment_time FROM Bills WHERE bill_id = $latestBillID";
                            $paymentTimeResult = $link->query($paymentTimeQuery);
                            $hasPaymentTime = false;

                            if ($paymentTimeResult && $paymentTimeResult->num_rows > 0) {
                                $paymentTimeRow = $paymentTimeResult->fetch_assoc();
                                if (!empty($paymentTimeRow['payment_time'])) {
                                    $hasPaymentTime = true;
                                }
                            }

                            $box_color = $hasPaymentTime ? 'var(--deep-green)' : $billItemColor;

                        } else {
                            $latestBillID = null;
                            $box_color = 'var(--deep-ash)';
                        }

                        if ($reservationResult && mysqli_num_rows($reservationResult) > 0) {
                            // Table is reserved
                            echo '<div class="table" style="background-color: var(--deep-yellow);">
                                    <a href="orderItem.php?bill_id=' . $latestBillID . '&table_id=' . $table_id . '"> 
                                        <h4>Table: ' . $table_id . ' </h4>
                                        <h4>Capacity: ' . $capacity . ' </4>
                                        <h4>Bill ID: ' . $latestBillID . ' </h4>
                                    </a>
                                </div>';
                        } else {
                            echo '<div class="table" style="background-color: ' . $box_color . ';" >
                                    <a href="orderItem.php?bill_id=' . $latestBillID . '&table_id=' . $table_id . '">
                                        <h4>Table: ' . $table_id . ' </h4>
                                        <h4>Capacity: ' . $capacity . ' </h4>
                                        <h4>Bill ID: ' . $latestBillID . ' </h4>
                                    </a>
                                </div>';
                        }
                    }
                } else {
                    echo "Error fetching tables: " . mysqli_error($link);
                }
            ?>
        </div>
    </div>
</div>

<?php include '../inc/dashFooter.php' ?>
