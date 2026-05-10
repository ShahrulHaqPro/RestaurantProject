<?php
require_once "../config.php";



$bill_id = $_GET['bill_id'];
$table_id = $_GET['table_id'] ?? null;

// Handle search
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = $_POST['search'];
    $query = "SELECT * FROM Menu 
              WHERE item_type LIKE '%$search%' 
              OR item_category LIKE '%$search%' 
              OR item_name LIKE '%$search%' 
              OR item_id LIKE '%$search%' 
              ORDER BY item_id;";
} else {
    $query = "SELECT * FROM Menu ORDER BY item_id;";
}
$result = mysqli_query($link, $query);
?>



<!-- MENU TABLE -->
<div class="left-side-table">
    <div class="page-header">
        <h2 class="pull-left">Food & Drinks</h2>
    </div>

    <form method="POST" action="">
        <input type="text" name="search" placeholder="Search Food & Drinks" class="form-control" required>
        <button type="submit">Search</button>
        <a href="orderItem.php?bill_id=<?php echo $bill_id; ?>&table_id=<?php echo $table_id; ?>">Show All</a>
    </form>

    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        echo '<table class="table table-bordered table-striped">';
        echo "<thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Add</th>
                </tr>
              </thead><tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['item_id']}</td>";
            echo "<td>{$row['item_name']}</td>";
            echo "<td>{$row['item_category']}</td>";
            echo "<td>" . number_format($row['item_price'], 2) . "</td>";

            // Check bill payment status
            $payment_time_query = "SELECT payment_time FROM Bills WHERE bill_id = '$bill_id'";
            $payment_time_result = mysqli_query($link, $payment_time_query);
            $has_payment_time = false;
            if ($payment_time_result && mysqli_num_rows($payment_time_result) > 0) {
                $payment_time_row = mysqli_fetch_assoc($payment_time_result);
                if (!empty($payment_time_row['payment_time'])) {
                    $has_payment_time = true;
                }
            }

            if (!$has_payment_time) {
                echo "<td>
                        <form method='get' action='updateCart.php'>
                            <input type='hidden' name='bill_id' value='$bill_id'>
                            <input type='hidden' name='table_id' value='$table_id'>
                            <input type='hidden' name='item_id' value='{$row['item_id']}'>
                            <input type='hidden' name='action' value='add'>
                            <button type='submit' class='btn edit'>+</button>
                        </form>
                      </td>";
            } else {
                echo "<td>Bill Paid</td>";
            }

            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo '<div class="alert alert-danger">No menu items found.</div>';
    }
    ?>
</div>

<!-- CART TABLE -->
<div class="right-side-table">
    <div class="cart-section">
        <div class="page-header"><h2>Cart</h2></div>
        <table>
            <thead>
                <tr>
                    <th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cart_query = "SELECT bi.*, m.item_name, m.item_price 
                               FROM bill_items bi
                               JOIN Menu m ON bi.item_id = m.item_id
                               WHERE bi.bill_id = '$bill_id'";
                $cart_result = mysqli_query($link, $cart_query);
                $cart_total = 0; $tax = 0.1;

                if ($cart_result && mysqli_num_rows($cart_result) > 0) {
                    while ($cart_row = mysqli_fetch_assoc($cart_result)) {
                        $total = $cart_row['item_price'] * $cart_row['quantity'];
                        $cart_total += $total;
                        echo "<tr>
                                <td>{$cart_row['item_name']}</td>
                                <td>LKR " . number_format($cart_row['item_price'], 2) . "</td>
                                <td>{$cart_row['quantity']}</td>
                                <td>LKR " . number_format($total, 2) . "</td>
                                <td>
                                    <form style='display:inline;' method='get' action='updateCart.php'>
                                        <input type='hidden' name='bill_id' value='$bill_id'>
                                        <input type='hidden' name='table_id' value='$table_id'>
                                        <input type='hidden' name='item_id' value='{$cart_row['item_id']}'>
                                        <input type='hidden' name='action' value='increase'>
                                        <button type='submit' class='btn edit'>+</button>
                                    </form>
                                    <form style='display:inline;' method='get' action='updateCart.php'>
                                        <input type='hidden' name='bill_id' value='$bill_id'>
                                        <input type='hidden' name='table_id' value='$table_id'>
                                        <input type='hidden' name='item_id' value='{$cart_row['item_id']}'>
                                        <input type='hidden' name='action' value='decrease'>
                                        <button type='submit' class='btn delete'>-</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo '<tr><td colspan="5">No Items in Cart</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <!-- Totals -->
        <table>
            <tr><td><strong>Cart Total</strong></td><td>LKR <?php echo number_format($cart_total, 2); ?></td></tr>
            <tr><td><strong>Tax</strong></td><td>LKR <?php echo number_format($cart_total * $tax, 2); ?></td></tr>
            <tr><td><strong>Grand Total</strong></td><td>LKR <?php echo number_format($cart_total * (1+$tax), 2); ?></td></tr>
        </table>
    </div>
</div>
