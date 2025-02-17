<?php
include_once 'top_header.php';
require 'utilities/timecalc.php';
require 'max_bid_price.php';
require 'bid_status.php';
?>

<?php
// Check to make sure the id parameter is specified in the URL
$item_id = "";
$has_session = "";
$user_id = "";
if (isset($_SESSION['userid'])) {
    $has_session = true;
    $user_id = mysqli_real_escape_string($db_conn, $_SESSION['userid']);
}
$watching = false;
if (isset($_GET['id'])) {
    $item_id = mysqli_real_escape_string($db_conn, $_GET['id']);
    $_SESSION['itemid'] = $item_id;
    // Prepare statement and execute, prevents SQL injection
    $detail_query = "SELECT * FROM item JOIN category ON item.category_ID = category.category_ID JOIN users ON item.seller_ID = users.user_ID WHERE item_ID = '$item_id'";
    // Fetch the product from the database and return the result as an Array
    $detail_result = mysqli_query($db_conn, $detail_query);
    // Check if the product exists (array is not empty)
    if (mysqli_num_rows($detail_result) === 0) {
        // Simple error to display if the id for the product doesn't exists (array is empty)
        echo 'Product does not exist!';
    } else {
        $item_row = mysqli_fetch_assoc($detail_result);
        $_SESSION["item_detail"] = $item_row;
    }
    // Keep track of how many times the particular user views the particular product page
    $recommendation_exist_query = "SELECT COUNT(*) as count FROM view_history WHERE item_ID = '$item_id' 
    AND buyer_ID = '$user_id'";
    $recommendation_exist_result = mysqli_query($db_conn, $recommendation_exist_query);
    $row = mysqli_fetch_assoc($recommendation_exist_result);
    if ($row['count'] == 0) {
        $add_query = "INSERT INTO view_history (buyer_ID, item_ID, view_times)
        VALUES ('$user_id', '$item_id', 1)";
        mysqli_query($db_conn, $add_query);
    } else {
        $update_query = "UPDATE view_history SET view_times = view_times + 1 WHERE
        buyer_ID = '$user_id' AND item_ID = '$item_id'";
        mysqli_query($db_conn, $update_query);
    }
    // watch item query
    $watch_query = "SELECT * FROM watchlist WHERE buyer_ID = '$user_id' AND item_ID = '$item_id'";
    $watch_result = mysqli_query($db_conn, $watch_query);
    if(mysqli_num_rows($watch_result) > 0){
        $watching = true;
    }
}
?>

<!-- product detail page -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css">
    </link>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </link>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/6cc5131127.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css" />
    <title>Document</title>
</head>

<body>
    <div class="container mt-5 mb-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="images p-3">
                                <div class="text-center p-4"> <img id="main-image" src="pictures/<?= $item_row['picture'] ?>" width="350" /></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="product p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center"> <i class="fa fa-long-arrow-left"></i> <span class="ml-1"><a href="index.php"> Back</a></span> </div> <i class="fa fa-shopping-cart text-muted"></i>
                                </div>
                                <div class="mt-4 mb-3"> <span class="text-uppercase text-muted brand"><?= $item_row['category'] ?></span>
                                    <h5 class="text-uppercase"><?= $item_row['item_name'] ?></h5>
                                    <div class="price d-flex flex-row align-items-center">
                                        <span class="act-price">Start from &pound;<?= $item_row['starting_price'] ?></span>
                                    </div>
                                </div>
                                <hr>
                                <p><b>Seller:</b> <?= $item_row['firstname'] . " " . $item_row['lastname']  ?></p>
                                <p><b>Condition:</b> <?= $item_row['cond'] ?></p>
                                <?php $bid_status = bidStatus($item_row); ?>
                                <p><b>Bid status:</b> <?= $bid_status ?></p>

                                <?php if ($bid_status === "Ongoing") {
                                ?>
                                    <!-- add timer display -->
                                    <div class="row">
                                        <div class="col-md-auto">
                                            <b>Time left:</b>
                                        </div>
                                        <div class="col-md-auto">
                                            <?= timeleft($item_row) ?>
                                        </div>
                                    </div>
                                <?php }; ?>
                                <hr>
                                <p class="about"><?= $item_row['pro_desc'] ?></p>
                                <hr>

                                <div class="sizes mt-4">
                                    <h6 class="text-uppercase">Start bidding</h6>
                                    <?php if ($bid_status === "Ongoing") { ?>
                                        <div class="row">
                                            <div class="col-md-auto">
                                                Current Bid(&pound;):
                                            </div>
                                            <div class="col-md-auto">
                                                <?php echo maxBidQuery($item_row['item_ID'], $item_row['starting_price']); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <form action=<?php if (isset($_POST['edit-bid'])) {
                                                        echo "edit_bid.php";
                                                    } else {
                                                        echo "create_bid.php";
                                                    } ?> method="POST">
                                        <div class="row">
                                            <div class="col-5">
                                                <input class="pricebox" type="number" name=<?php if (isset($_POST['edit-bid'])) {
                                                                                                echo "new_bid_price";
                                                                                            } else {
                                                                                                echo "bid_price";
                                                                                            } ?> value="bid_price" min=<?= maxBidQuery($item_row['item_ID'], $item_row['starting_price']) ?> placeholder="&pound; Bid price" required>
                                            </div>
                                            <?php if ($bid_status === "Ongoing") { ?>
                                                <div class="col-4 bid-sub">
                                                    <?php if (isset($_POST['edit-bid'])) { ?>
                                                        <button class="btn btn-outline-success search-but mr-2 px-4" type="submit" name="update-bid">Update Bid</button>
                                                    <?php } else { ?>
                                                        <button class="btn btn-outline-success search-but mr-2 px-4" type="submit" name="submit-bid">Submit Bid</button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </form>
                                </div>
                                <!-- <form id="wl" action="watchlist.php" method='post'> -->
                                <div class="cart mt-4 align-items-center  <?php if ($bid_status == "Ongoing") {
                                                                                echo "watchlist";
                                                                            } ?>">
                                    <input type="hidden" name="item_ID" value="<?= $item_row['item_ID'] ?>">
                                    <!-- case 1 -->
                                    <div id="watch_nowatch" <?php if ($has_session && $watching) echo ('style="display: none"'); ?>>
                                        <!-- if user is logged in and hasn't add product to watchlist yet -->
                                        <button class="btn btn-outline-dark mr-2 px-4" type="button" name="watchlist" id="watchlist" onclick="addToWatchlist()"><i class="fa fa-heart text-muted ic"></i><span class="w">Watch this item</span></button>
                                    </div>
                                    <!-- case 2 -->
                                    <div id="watch_watching" <?php if (!$has_session || !$watching) echo ('style="display: none"'); ?>>
                                        <button type="button" class="btn btn-primary mr-2 px-4" onclick="removeFromWatchlist()">Unwatch this item</button>
                                        <p style="color:red;">Watching...</p>                                   
                                    </div>
                                </div>
                                <!-- </form> -->
                                <hr>
                                <p><b>Return: </b>No returns accepted</p>
                                <p><b>Delivery: </b> Royal Mail Service / DHL</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <?php if ($bid_status === "Ongoing") { ?>
            <button type="button" class="collapsible"><b>+ See Bid Price Movement</b></button>
            <div class="content row align-items-center">
                <div class="table-wrap col">
                    <table class="table table-hover">
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col" style="width:15%"># Buyer</th>
                                <th scope="col" style="width:25%">Bid Price(&pound;)</th>
                                <th scope="col" style="width:30%">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $price_query = "SELECT * FROM bidding WHERE item_ID = '$item_id'";
                            $price_result = mysqli_query($db_conn, $price_query);
                            $graph_arr = array();
                            if (mysqli_num_rows($price_result) > 0) {
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($price_result)) {
                                    $num_time = strtotime($row["bidding_date"] . " " . $row["bidding_time"]);
                                    $graph_arr[] = array("x" => $num_time * 1000, "y" => $row["bid_price"]); ?>
                                    <tr>
                                        <td><?php echo $i ?></td>
                                        <td><?php echo $row["bid_price"] ?></td>
                                        <td><?php echo $row["bidding_date"] . " " . $row["bidding_time"] ?></td>
                                    </tr>

                            <?php $i++;
                                }
                            } ?>
                            <?php
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col grp">
                    <?php if (count($graph_arr) == 0) {
                        echo "No bid for this item!";
                    } elseif (count($graph_arr) >= 2) { ?>
                        <div id="chartContainer" style="height: 300px; width: 100%;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <script>
        // control collapse section
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
        // graph function
        window.onload = function() {

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: "Price Increment VS. Time",
                    fontSize: 20
                },
                axisY: {
                    title: "Bid Price in GBP",
                    fontSize: 16,
                    valueFormatString: "#,##0.##",
                    prefix: "£"
                },
                data: [{
                    type: "line",
                    markerSize: 7,
                    xValueFormatString: "YYYY-MM-DD",
                    yValueFormatString: "£#,##0.##",
                    xValueType: "dateTime",
                    dataPoints: <?php echo json_encode($graph_arr, JSON_NUMERIC_CHECK); ?>
                }]
            });

            chart.render();
        }
    </script>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>

</html>

<?php include_once 'bottom_footer.php'; ?>
<script src="./watchlist_button.js"></script>