<?php
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        die("No access!");
    }

    session_start();
    //объявляем наши динамические данные тут для удобности
    $paypalEmail = "sales@hokord.com";
    $paypalURL = "https://www.paypal.com/cgi-bin/webscr";

    $phone = $_POST['phone'];

    $amount = $_POST['amount'];
    if (($amount == 0) || ($amount < 0)) $amount = 1;
    $totalPrice = 0;

    function calculationShippingMethod($firstPice, $addPrice, $productAmount) {
        $productPrice = 18 * 5;

        if ($productAmount === '') {
            alert('Enter the correct value');
        }
        $deliveryPrice = $firstPice;
        for ($i = 1; $i < $productAmount; $i++) {
            if ($productAmount > 1) {
                $deliveryPrice += $addPrice;
            }
        }

        $totalAmount = ($productAmount * $productPrice) + $deliveryPrice;
        return $totalAmount;
    };

    if ($_POST['selectDelivery'] == "delivery1") {
        $totalPrice = calculationShippingMethod(35, 25, $amount);
        $shipping = "Delivery Common";
    }
    /*if ($_POST['selectDelivery'] == "delivery2") {
        $totalPrice = calculationShippingMethod(20, 7, $amount);
        $shipping = "Delivery Express";
    }*/

    //$delivery = $_POST['selectDelivery'];

    //echo "Price = $price; TotalPrice = $totalPrice; Delivery = $delivery;\n";

    $itemName = "Test_COVID_19";
    $returnUrl = "https://rapidtestscovid19.com/index.php";
    $cancelUrl = "https://rapidtestscovid19.com/index.php";
    $notifyUrl = "https://rapidtestscovid19.com/paypal_final.php";

    $querystring = 'cmd=_notify-validate';
    $querystring .= "?business=" . urlencode($paypalEmail) . "&";
    $querystring .= "currency_code=" . urlencode('USD') . "&";
    $querystring .= "lc=" . urlencode('US') . "&";
    $querystring .= "bn=" . urlencode('YourBussiness_BuyNow_WPS_US') . "&";
    $querystring .= "no_note=" . urlencode('1') . "&";
    $querystring .= "cmd=" . urlencode('_xclick') . "&";
    
    //ид пользователя – чтоб знати при ответе Paypal кто заплатил
    $querystring .= "custom=" . urlencode($amount) . '_' . urlencode($shipping) . '_' . urlencode($phone) . "&";
    $querystring .= "item_name=" . urlencode($itemName) . "&";
    $querystring .= "amount=" . urlencode($totalPrice) . "&";

    //Добавление адресов возврата
    $querystring .= "return=" . urlencode(stripslashes($returnUrl)) . "&";
    $querystring .= "cancel_return=" . urlencode(stripslashes($cancelUrl)) . "&";
    $querystring .= "notify_url=" . urlencode($notifyUrl);

    header('location:' . $paypalURL . $querystring);
    exit();
?>