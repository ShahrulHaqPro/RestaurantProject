<?php
require_once '../config.php';

// Start the session
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Page</title>
    <style>
        * {
            scrollbar-width: none;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: black;
        }

        .nav-link:hover {
            color: goldenrod;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .section-div {
            box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;
            max-width: 600px;
            min-height: fit-content;
            padding: 30px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        select,
        input,
        textarea {
            border: none;
            outline: none;
            width: 200px;
            background-color: #d3d2d2;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .short-input input {
            width: 100px;
        }

        .label {
            width: 200px;
        }

        .add-form-div .serach-input {
            display: flex;
            flex-direction: row;
            margin-left: 3px;
        }

        .add-form-div .serach-input select {
            margin-left: 3px;
        }

        .btn {
            width: fit-content;
            border: none;
            text-decoration: none;
            padding: 10px;
            color: beige;
            border-radius: 8px;
            text-align: center;
        }

        .btn-light {
            background-color: #1E90FF;
        }

        .btn-light:hover {
            background-color: #87CEFA;
        }
    </style>
</head>

<body>
    <?php
    $reservationStatus = $_GET['reservation'] ?? null;
    $message = '';
    if ($reservationStatus === 'success') {
        $message = "Reservation successful";
        $reservation_id = $_GET['reservation_id'] ?? null;
        echo '<a class="nav-link" href="../home/home.php#hero">' .
            '<h1 style="font-family: Copperplate; color: whitesmoke;">Central Ceylon</h1>' .
            '<span class="sr-only"></span>
                  </a>';
        echo '<script>alert("Table Successfully Reserved. Click OK to view your reservation receipt."); window.location.href = "reservationReceipt.php?reservation_id=' . $reservation_id . '";</script>';
    }
    $head_count = $_GET['head_count'] ?? 1;
    ?>

    <!-- page content --------------------------------------------------------------------------- -->
    <div class="container">
        <div>
            <a class="nav-link" href="../home/home.php#hero">
                <h1>Central Ceylon</h1>
            </a>
        </div>

        <div class="page-content">
            <div id="Search Table" class="section-div left-column">
                <div class="page-header">
                    <h2>Search for Time</h2>
                </div>

                <div class="table-search-bar add-form">
                    <form id="reservation-form" method="GET" action="availability.php">
                        <table class="add-form-table">
                            <tr class="serach-input">
                                <td class="label">
                                    <label for="reservation_date">Select Date</label>
                                </td>
                                <td>
                                    <input class="" type="date" id="reservation_date" name="reservation_date" required>
                                </td>
                            </tr>

                            <tr class="serach-input">
                                <td class="label">
                                    <label for="reservation_time">Available Reservation Times</label>
                                </td>
                                <td>
                                    <?php
                                    $availableTimes = array();
                                    for ($hour = 5; $hour <= 22; $hour++) {
                                        for ($minute = 0; $minute < 60; $minute += 60) {
                                            $time = sprintf('%02d:%02d:00', $hour, $minute);
                                            $availableTimes[] = $time;
                                        }
                                    }
                                    echo '<select name="reservation_time" id="reservation_time" class="" >';
                                    echo '<option value="" selected disabled>Select a Time</option>';
                                    foreach ($availableTimes as $time) {
                                        echo "<option  value='$time'>$time</option>";
                                    }
                                    echo '</select>';
                                    if (isset($_GET['message'])) {
                                        $message = $_GET['message'];
                                        echo "<p>$message</p>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <tr class="search-btn">
                                <td>
                                    <input type="number" id="head_count" name="head_count" value=1 hidden required>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-light" name="submit">Search</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div id="insert-reservation-into-table" class="section-div right-column">
                <div class="page-header">
                    <h2>Make Reservation</h2>
                </div>

                <div class="table-search-bar add-form">
                    <form id="reservation-form" method="POST" action="insertReservation.php">
                        <table class="add-form-table">
                            <tr class="serach-input">
                                <td class="label">
                                    <label for="customer_name">Customer Name</label>
                                </td>
                                <td>
                                    <input class="" type="text" id="customer_name" name="customer_name" placeholder="T.K. Perera" required>
                                </td>
                            </tr>
                            <?php
                            $defaultReservationDate = $_GET['reservation_date'] ?? date("Y-m-d");
                            $defaultReservationTime = $_GET['reservation_time'] ?? "13:00:00";
                            ?>

                            <tr class="serach-input">
                                <td class="label">
                                    <label for="reservation_date">Reservation Date</label>
                                </td>
                                <td class="short-input">
                                    <input type="date" id="reservation_date" name="reservation_date" value="<?= $defaultReservationDate ?>" readonly required>
                                    <input type="time" id="reservation_time" name="reservation_time" value="<?= $defaultReservationTime ?>" readonly required>
                                </td>
                            </tr>
                        </table>
                        <div class="add-form-div">
                            <div class="serach-input tr">
                                <div class="label">
                                    <label for="table_id_reserve">Available Tables</label>
                                </div>
                                <div class="td">
                                    <select class="" name="table_id" id="table_id_reserve" required>
                                        <option value="" selected disabled>Select a Table</option>
                                        <?php
                                        $table_id_list = $_GET['reserved_table_id'];
                                        $head_count = $_GET['head_count'] ?? 1;
                                        $reserved_table_ids = explode(',', $table_id_list);
                                        $select_query_tables = "SELECT * FROM restaurant_tables WHERE capacity >= '$head_count'";
                                        if (!empty($reserved_table_ids)) {
                                            $reserved_table_ids_string = implode(',', $reserved_table_ids);
                                            $select_query_tables .= " AND table_id NOT IN ($reserved_table_ids_string)";
                                        }
                                        $result_tables = mysqli_query($link, $select_query_tables);
                                        $resultCheckTables = mysqli_num_rows($result_tables);
                                        if ($resultCheckTables > 0) {
                                            while ($row = mysqli_fetch_assoc($result_tables)) {
                                                echo '<option value="' . $row['table_id'] . '">For ' . $row['capacity'] . ' people. (Table Id: ' . $row['table_id'] . ')    </option>';
                                            }
                                        } else {
                                            echo '<option disabled>No tables available, please choose another time.</option>';
                                            echo '<script>alert("No reservation tables found for the selected time. Please choose another time.");</script>';
                                        }
                                        ?>
                                    </select>
                                    <input type="number" id="head_count" name="head_count" value="<?= $head_count ?>" required hidden>
                                </div>
                            </div>
                        </div>
                        <table class="add-form-table">
                            <tr class="serach-input">
                                <td class="label">
                                    <label for="special_request">Special request</label>
                                </td>
                                <td>
                                    <textarea class="" id="special_request" name="special_request">Need a Child chair</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div>
                                        <button type="submit" class="btn btn-light" type="submit" name="submit">Make Reservation</button>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- page content end --------------------------------------------------------------------------- -->
    <script>
        const viewDateInput = document.getElementById("reservation_date");
        const makeDateInput = document.getElementById("reservation_date");

        viewDateInput.addEventListener("change", function() {
            makeDateInput.value = this.value;
        });
    </script>
</body>

</html>