<?php
require_once '../config.php';

// if (isset($_GET['bill_item_id'], $_GET['bill_id'])) {
//     $bill_item_id = $_GET['bill_item_id'];
//     $table_id = $_GET['table_id'];
//     $item_id = $_GET['item_id'];
//     $bill_id = $_GET['bill_id'];
    
    
    // // $bill_id = $_POST['bill_id'];
    // // $item_id = $_POST['item_id'];

    // Check if the item already exists in the bill_items table
    // $existingItemQuery = "SELECT quantity FROM Bill_Items WHERE bill_item_id = $bill_item_id";
    // $existingItemResult = mysqli_query($link, $existingItemQuery);


//     if (mysqli_num_rows($existingItemResult) > 1) {

//         // Reduce the item with the given bill_item_id
//         $reduce_query = "UPDATE bill_items SET quantity = quantity - 1 WHERE bill_item_id = $bill_item_id";
        

//         if (mysqli_query($link, $reduce_query)) {
//             // Redirect
//             $update_kitchen_sql = "UPDATE Kitchen SET quantity = quantity - 1 WHERE item_id = '$item_id'";
//             if (mysqli_query($link, $update_kitchen_sql)) {
//                 echo "Reduced 1 successfully from Kitchen table.";
//             } else {
//                 echo "Error Reducing record: " . mysqli_error($link);
//             }

            
//             header("Location: orderItem.php?bill_id={$_GET['bill_id']}&reduced=1&table_id={$table_id}");
//             exit();
//         } else {
//             // Redirect
//             echo "Error reducing item: " . mysqli_error($link);
//             header("Location: orderItem.php?bill_id={$_GET['bill_id']}&reducing_error=1&table_id={$table_id}");
//             exit();
//         }


//     }
    
// } else {
//     // Redirect
//     echo "bill_item_id not provided.";
//     header("Location: orderItem.php?bill_id={$_GET['bill_id']}&table_id={$table_id}");
//     exit();
// }
?>

<?php
require_once '../config.php';

if (
    isset($_GET['bill_item_id'], $_GET['bill_id'], $_GET['table_id'], $_GET['item_id'])
) {
    $bill_item_id = (int) $_GET['bill_item_id'];
    $bill_id      = (int) $_GET['bill_id'];
    $table_id     = (int) $_GET['table_id'];
    $item_id      = (int) $_GET['item_id'];

    // Get current quantity
    $checkSql = "SELECT quantity FROM bill_items WHERE bill_item_id = $bill_item_id";
    $checkRes = mysqli_query($link, $checkSql);

    if (mysqli_num_rows($checkRes) === 1) {
        $row = mysqli_fetch_assoc($checkRes);

        if ($row['quantity'] > 1) {

            // Start transaction
            mysqli_begin_transaction($link);

            try {
                // Reduce bill_items
                $reduceBillItem = "
                    UPDATE bill_items 
                    SET quantity = quantity - 1 
                    WHERE bill_item_id = $bill_item_id
                ";

                if (!mysqli_query($link, $reduceBillItem)) {
                    throw new Exception(mysqli_error($link));
                }

                // Reduce kitchen
                $reduceKitchen = "
                    UPDATE kitchen 
                    SET quantity = quantity - 1 
                    WHERE item_id = $item_id
                ";

                if (!mysqli_query($link, $reduceKitchen)) {
                    throw new Exception(mysqli_error($link));
                }

                // Commit if both succeed
                mysqli_commit($link);

                header("Location: orderItem.php?bill_id=$bill_id&table_id=$table_id&reduced=1");
                exit();

            } catch (Exception $e) {
                mysqli_rollback($link);
                echo "Error: " . $e->getMessage();
            }

        } else {
            // Quantity is 1 → you may want to delete instead
            header("Location: orderItem.php?bill_id=$bill_id&table_id=$table_id&min_qty=1");
            exit();
        }

    } else {
        echo "Bill item not found.";
    }

} else {
    echo "Required parameters missing.";
}
?>
