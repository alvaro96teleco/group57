<htrml>
<body>

<!-- TOP BANNER -->
<img border="0" src="amazon_topbanner.png" width="100%">

<!-- DB CONNECTION -->
<?php 
    //connect with mysql
    $conn = mysqli_connect("localhost", "root", "");
    if(!$conn) {
        die ("Error connecting to MySQL: " . mysqli_error($conn));
    }
    //select amazon database
    $db_select_success =  mysqli_select_db($conn, "amazon");

    if(!$db_select_success) {
        die ("Error selecting database: ".mysqli_error($conn));
    } else {
	    //echo "MySQL database: amazon selected. <br/>";
    }
?>

<!-- USER DETAILS -->
<div>
<?php
$query = usersDetails();
//fetch the userID and country
$result = mysqli_query($conn, $query);
echo "User Details: "; 
echo "</br>";
while ($row = mysqli_fetch_array($result)){

    echo "User ID: " . $row["UserID"];
    echo "</br>";
    echo "Country: " . $row["Country"];
}
?>
</div>

<!-- ORDER SUMMARY -->
<div>
<?php
echo "Orders Summary:";
echo "</br>";
$query = OrdersID();
// fetch ordersID
$result = mysqli_query($conn, $query);
$sumOrders = 0;
$bookQuantity = 0;
// this nested while sum one each iteration(transaction) giving us the amount
// of orders and sum the book quantity of each order
while ($row = mysqli_fetch_array($result)){
    $sumOrders++;
    $query2 = transQuantity($row["ORID"]);
    // fetch quantity
    $result2 = mysqli_query($conn, $query2);
    while ($row2 = mysqli_fetch_array($result2)){
        $bookQuantity += $row2["suma"]; 
    }
     
}
echo $sumOrders;
echo "</br>";
echo $bookQuantity;
?>
</div>
</div>

<!-- ORDER DETAILS  -->
<div>
<table>
<?php
$query = OrdersID();
//fetch the ordersID
$result = mysqli_query($conn, $query);
while ($row3 = mysqli_fetch_array($result)){

    $query3 = getOrderDetails($row3["ORID"]);
    //fetch year, totalpay, discount and orderID
    $result3 = mysqli_query($conn, $query3);
    
    while($row4 = mysqli_fetch_array($result3)){
        echo "<tr bgcolor='gray'>";
        echo "<td>Order placed: " . $row4["YEAROR"] . "</td>";
        echo "<td>Total: " . $row4["TOTALPAY"] . "</td>";
        echo "<td>";
        // if there are no discount, it is not displayed
        if ($row4["DISCOUNT"] != 0){
            echo "Discount: " . $row4["DISCOUNT"];
            
        }
        echo "</td>";
        echo "<td>Order ID: " . $row4["ORDID"] . "</td>";
        echo "</tr>";
    }
    

    /*BOOK DETAILS*/ 
    $query4 = getBookDetails($row3["ORID"]);
    //fetch tittle, ISBN, price, genre, and ordered price from a book
    $result4 = mysqli_query($conn, $query4);
    while($row5 = mysqli_fetch_array($result4)){
        echo "<tr>";
        echo "<td><img border='0' src=" . $row5["IMAGEURL"] . "></td>";
        echo "<td>";
        echo $row5["TITTLE"];
        echo "</br>";
        echo $row5["ISBN"];
        echo "</br>";
        echo $row5["PRICE"];
        echo "</td>";
        echo "<td>";
        echo $row5["GENRE"];
        echo "</td>";
        echo "<td>";
        echo $row5["TOTALBOOKPRICE"];
        echo "</td>";
        echo "</tr>";   
    }  
}
?>
</table>
</div>

<!-- BOTTOM BANNER  -->
<img border="0" src="amazon_bottombanner.png" width="100%">

<!-- FORM  -->
<!-- put an userID to see his order history -->
<div>
<form action="amazon.php" method="post">
Query Userid: <input type="text" name="userID">
<input type="submit">
</form>
</div>
</body>
</html>

<!-- QUERIES FUNCTIONS  -->
<?php
//each function return an string which is a slq query
function usersDetails(){
    return "SELECT * FROM Users WHERE UserID = '$_POST[userID]'";
}
function OrdersID(){
    return "SELECT Orders.orderID AS ORID FROM Users, Orders WHERE Users.userID = Orders.UserID AND Users.userID = '$_POST[userID]'";
}
function transQuantity($orderID){
    return "SELECT sum(Quantity) AS suma FROM Trans WHERE OrderID = " . $orderID;
}
function getOrderDetails($orderID){
    return "SELECT Orders.year AS YEAROR, Orders.totalpay AS TOTALPAY, (Orders.totalpay - sum(Trans.Quantity * Books.UnitPrice)) * -1 AS DISCOUNT, Orders.OrderID AS ORDID FROM Orders, Trans, Books WHERE (Orders.OrderID = Trans.OrderID AND Trans.ISBN = Books.ISBN) AND Orders.OrderID = " . $orderID;  
}
function getBookDetails($orderID){
    return "SELECT Books.ImageURL AS IMAGEURL, Books.title AS TITTLE, Books.ISBN AS ISBN, Books.Unitprice AS PRICE, Books.Genre AS GENRE, (Books.unitprice * Trans.quantity) AS TOTALBOOKPRICE
    FROM Trans, Books
    WHERE Trans.ISBN = Books.ISBN AND Trans.OrderID = " . $orderID;
}
?>