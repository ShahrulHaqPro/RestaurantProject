<?php
session_start();
?>
<?php
require_once '../config.php';
include '../inc/Header-dash.php';

$bill_id = $_GET['bill_id'];
$table_id = $_GET['table_id'];

function createNewBillRecord($table_id)
{
    global $link;

    $bill_time = date('Y-m-d H:i:s');

    $insert_query = "INSERT INTO Bills (table_id, bill_time) VALUES ('$table_id', '$bill_time')";
    if ($link->query($insert_query) === TRUE) {
        return $link->insert_id; // Return the newly inserted bill_id
    } else {
        return false;
    }
}
?>
<style>
    .side-by-side {
        display: flex;
        flex-direction: row;
        gap: 10px;
    }

    button {
        border: none;
        outline: none;
    }

    .right-side-table {
        margin-top: 47px;
    }
</style>


<div class="container">
    <div class="page-content">
        <div class="side-by-side">

            <div class="left-side-table">
                <div class="page-header">
                    <h2 class="pull-left">Food & Drinks</h2>
                </div>
                <div class="table-search-bar">
                    <form method="POST" action="#">
                        <div class="serach-input">
                            <input type="text" required="" id="search" name="search" class="form-control" placeholder="Search Food & Drinks">
                        </div>
                        <div class="search-btn">
                            <button type="submit">Search</button>
                        </div>
                        <div class="showall-btn">
                            <a href="orderItem.php?bill_id=<?php echo $bill_id; ?>&table_id=<?php echo $table_id; ?>">Show All</a>
                        </div>
                    </form>
                </div>
                <?php
                    require_once "../config.php";
                    if (isset($_POST['search'])) {
                        if (!empty($_POST['search'])) {
                            $search = $_POST['search'];

                            $query = "SELECT * FROM Menu WHERE item_type LIKE '%$search%' OR item_category LIKE '%$search%' OR item_name LIKE '%$search%' OR item_id LIKE '%$search%' ORDER BY item_id;";
                            $result = mysqli_query($link, $query);
                        } else {
                            // Default query to fetch all menu items
                            $query = "SELECT * FROM Menu ORDER BY item_id;";
                            $result = mysqli_query($link, $query);
                        }
                    } else {
                        // Default query to fetch all menu items
                        $query = "SELECT * FROM Menu ORDER BY item_id;";
                        $result = mysqli_query($link, $query);
                    }
                    $bill_id = $_GET['bill_id'];
                    if ($result) {
                        if (mysqli_num_rows($result) > 0) {
                            echo '<table class="table table-bordered table-striped">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>ID</th>";
                                        echo "<th>Item Name</th>";
                                        echo "<th>Category</th>";
                                        echo "<th>Price</th>";
                                        echo "<th>Add</th>";
                                        echo "<th>Add Mul</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                // ...

                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<tr>";
                                        echo "<td>" . $row['item_id'] . "</td>";
                                        echo "<td>" . $row['item_name'] . "</td>";
                                        echo "<td>" . $row['item_category'] . "</td>";
                                        echo "<td>" . number_format($row['item_price'], 2) . "</td>";

                                    // Check if the bill has been paid
                                        $payment_time_query = "SELECT payment_time FROM Bills WHERE bill_id = '$bill_id'";
                                        $payment_time_result = mysqli_query($link, $payment_time_query);
                                        $has_payment_time = false;

                                        if ($payment_time_result && mysqli_num_rows($payment_time_result) > 0) {
                                            $payment_time_row = mysqli_fetch_assoc($payment_time_result);
                                            if (!empty($payment_time_row['payment_time'])) {
                                                $has_payment_time = true;
                                            }
                                        }

                                        // Display the "Add to Cart" button if the bill hasn't been paid
                                        if (!$has_payment_time) {
                                            echo '<td>
                                                    <form method="get" action="addItem.php">
                                                        <input type="text" name= "table_id" value="' . $table_id . '" hidden>
                                                        <input type="text" name="item_id" value=' . $row['item_id'] . ' hidden>
                                                        <input type="number" name= "bill_id" value=' . $bill_id . ' hidden>
                                                        <input type="hidden" value=1 name="quantity" placeholder="1 to 1000" required min="1" max="1000">
                                                        <input type="hidden" name="addToCart" value="1">
                                                        <button type="submit" class="btn edit">⇧</button>';
                                            echo "</form>
                                                    </td>";


                                            echo '<td class="addMulItem">
                                                    <form method="get" action="addItemMul.php">
                                                        <input type="text" name= "table_id" value="' . $table_id . '" hidden>
                                                        <input type="text" name="item_id" value=' . $row['item_id'] . ' hidden>
                                                        <input type="number" name= "bill_id" value=' . $bill_id . ' hidden>
                                                        <input type="number" name="quantity" placeholder="1-1000" required min="1" max="1000">
                                                        <input type="hidden" name="addToCart" value="1">
                                                        <button type="submit" class="btn edit">⇨∣</button>';
                                            echo "</form>
                                                    </td>";        
                                        } else {
                                            echo '<td>Bill Paid</td>';
                                            echo '<td>Bill Paid</td>';
                                        }

                                    echo "</tr>";
                                    }

                                echo "</tbody>";
                            echo "</table>";
                        } else {
                            echo '<div class="alert alert-danger"><em>No menu items were found.</em></div>';
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    // Close connection

                ?>
            </div>

            <div class="right-side-table">
                <div class="cart-section">
                    <div class="page-header">
                        <h2>Cart</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Reduce</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to fetch cart items for the given bill_id
                            $cart_query = "SELECT bi.*, m.item_name, m.item_price FROM bill_items bi
                                        JOIN Menu m ON bi.item_id = m.item_id
                                        WHERE bi.bill_id = '$bill_id'";
                            $cart_result = mysqli_query($link, $cart_query);
                            $cart_total = 0;
                            $tax = 0.1;

                            if ($cart_result && mysqli_num_rows($cart_result) > 0) {
                                while ($cart_row = mysqli_fetch_assoc($cart_result)) {
                                    $item_id = $cart_row['item_id'];
                                    $item_name = $cart_row['item_name'];
                                    $item_price = $cart_row['item_price'];
                                    $quantity = $cart_row['quantity'];
                                    $total = $item_price * $quantity;
                                    $bill_item_id = $cart_row['bill_item_id'];
                                    $cart_total += $total;
                                    echo '<tr>';
                                    echo '<td>' . $item_id . '</td>';
                                    echo '<td>' . $item_name . '</td>';
                                    echo '<td>LKR ' . number_format($item_price, 2) . '</td>';
                                    echo '<td>' . $quantity . '</td>';
                                    echo '<td>LKR ' . number_format($total, 2) . '</td>';
                                    

                                    // Check if the bill has been paid
                                    $payment_time_query = "SELECT payment_time FROM Bills WHERE bill_id = '$bill_id'";
                                    $payment_time_result = mysqli_query($link, $payment_time_query);
                                    $has_payment_time = false;

                                    if ($payment_time_result && mysqli_num_rows($payment_time_result) > 0) {
                                        $payment_time_row = mysqli_fetch_assoc($payment_time_result);
                                        if (!empty($payment_time_row['payment_time'])) {
                                            $has_payment_time = true;
                                        }
                                    }

                                    // Display the "Delete" button if the bill hasn't been paid
                                    if (!$has_payment_time) {
                                        //reduce btn
                                        echo '<td>';
                                        echo '<div class="action-buttons" >';
                                        echo '<a id="reduceBtn" class="btn view reduceBtn" data-quantity="' . $quantity . '" href="reduceItemFromCart.php?bill_id=' . $bill_id . '&table_id=' . $table_id . '&bill_item_id=' . $bill_item_id . '&item_id=' . $item_id . '">
                                                ⇩
                                            </a>';
                                        echo '</div>';
                                        echo '</td>';

                                        //remove button
                                        echo '<td>';
                                        echo '<div class="action-buttons">';
                                        echo '<a class="btn delete" href="deleteItem.php?bill_id=' . $bill_id . '&table_id=' . $table_id . '&bill_item_id=' . $bill_item_id . '&item_id=' . $item_id . '">
                                                ✕
                                            </a>';
                                        echo '</div>';
                                        echo '</td>';
                                    } else {
                                        echo '<td>Bill Paid</td>';
                                        echo '<td>Bill Paid</td>';
                                    }
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6">No Items in Cart.</td></tr>';
                            }
                            ?>

                           <script>
                                document.querySelectorAll(".reduceBtn").forEach(btn => {
                                    const quantity = parseInt(btn.dataset.quantity);

                                    if (quantity === 1) {
                                    btn.classList.remove("view");
                                    btn.classList.add("reduce");
                                    } else if (quantity > 1) {
                                    btn.classList.remove("reduce");
                                    btn.classList.add("view");
                                    }
                                });
                            </script>


                        </tbody>
                    </table>

                    <table>
                        <tbody>
                            <tr>
                                <td><strong>Cart Total</strong></td>
                                <td>LKR <?php echo number_format($cart_total, 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cart Taxed</strong></td>
                                <td>LKR <?php echo number_format($cart_total * $tax, 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td>LKR <?php echo number_format(($tax * $cart_total) + $cart_total, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php
                        // Check if the payment time record exists for the bill
                        $payment_time_query = "SELECT payment_time FROM Bills WHERE bill_id = '$bill_id'";
                        $payment_time_result = mysqli_query($link, $payment_time_query);
                        $has_payment_time = false;

                        if ($payment_time_result && mysqli_num_rows($payment_time_result) > 0) {
                            $payment_time_row = mysqli_fetch_assoc($payment_time_result);
                            if (!empty($payment_time_row['payment_time'])) {
                                $has_payment_time = true;
                            }
                        }

                        // If payment time record exists, show the "Print Receipt" button
                        if ($has_payment_time) {
                            echo '<div>';
                                echo '<div class="alert alert-success" role="alert">
                                            Bill has already been paid.
                                    </div>';
                                echo '<br>
                                    <a href="receipt.php?bill_id=' . $bill_id . '" class="btn delete">Print Receipt</a>
                                </div>';
                        } elseif (($tax * $cart_total + $cart_total) > 0) {
                            echo '<br><a href="idValidity.php?bill_id=' . $bill_id . '" class="btn edit">Pay Bill</a>';
                        } else {
                            echo '<br><h3>Add Item To Cart to Proceed</h3>';
                        }
                    ?>
                </div>
                <br>
                <?php
                    echo '<form action="newCustomer.php" method="get">';
                        echo '<input type="hidden" name="table_id" value="' . $table_id . '">';
                        echo '<button class="btn view" type="submit" name="new_customer" value="true">New Customer</button>';
                    echo '</form>';
                ?>
            </div>
        </div>
    </div>
</div>
<style>
    .btn{
        font-size: 20px;
    }
    .reduceBtn.reduce {
        color: #ddddddff;
        background-color: #929292ff;
        text-decoration: none;
    }
    .addMulItem form{
        display:flex;
        flex-direction:row;
        width: 130px;
        gap:5px;
    }
    .addMulItem input{
        height: 30px;
        width: 60px;
        border-radius: 8px;
        padding-inline: 10px;
        justify-content: center;
        align-items: center;
    }
</style>
<?php include '../inc/dashFooter.php'; ?>