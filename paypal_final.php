<?php
    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';
	require 'phpmailer/Exception.php';

    // Позволяем только POST
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
    	die("No access!");
    }

    $raw_post_data = file_get_contents('php://input');
    $raw_post_array = explode('&', $raw_post_data);
    $myPost = array();
    foreach ($raw_post_array as $keyval) {
        $keyval = explode('=', $keyval);
        if (count($keyval) == 2) {
            // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
            if ($keyval[0] === 'payment_date') {
                if (substr_count($keyval[1], '+') === 1) {
                    $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                }
            }
            $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
    }
    
	$req = 'cmd=_notify-validate';
    if (function_exists('get_magic_quotes_gpc')) {
    	$get_magic_quotes_exists = true;
    }
    foreach ($_POST as $key => $value) {
    	if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
    		$value = urlencode(stripslashes($value));
    	}else {
    		$value = urlencode($value);
    	}
    	$req .= "&$key=$value";
    }
    
	$ch = curl_init("https://www.paypal.com/cgi-bin/webscr");
	if ($ch == FALSE) {
		return false;
	}
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	
	curl_setopt($ch, CURLOPT_SSLVERSION, 6);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Connection: Close",
		"User-Agent:Sirius"
	));

	$res = curl_exec($ch);

    if (!($res)) {
        $errno = curl_errno($ch);
        $errstr = curl_error($ch);
        curl_close($ch);
        $error = "cURL error: [$errno] $errstr";
        $fh = fopen('result_error.txt', 'a');
        fwrite($fh, $error . "\r\n");
        fclose($fh);
        return false; 
    }

    $info = curl_getinfo($ch);
    $http_code = $info['http_code'];
    if ($http_code != 200) {
        $error = "PayPal responded with http code $http_code";
        $fh = fopen('result_error.txt', 'a');
        fwrite($fh, $error . "\r\n");
        fclose($fh);
        return false;
    }
    
    curl_close($ch);
    
    $tokens = explode("\r\n\r\n", trim($res));
	$res = trim(end($tokens));
    
    $fh = fopen('result_log.txt', 'a');
    fwrite($fh, $res . ' -- ' . $req . "\r\n");
    fclose($fh);
    
	if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) {
	    $fh = fopen('result_post.txt', 'a');
        fwrite($fh, print_r($_POST, true) . "\r\n");
        fclose($fh);    
	    
	    $payer_id = $_POST['payer_id'];
	    
	    $title = 'Client' . '_' . $payer_id;
	    $subject = 'Payment';

        $name = $_POST['last_name'] . ' ' . $_POST['first_name'];
        $address = $_POST['address_country'] . ' ' . $_POST['address_state'] . ' ' . $_POST['address_street'] . ' ' . $_POST['address_zip'];
        $totalPrice = $_POST['mc_gross'];
        $custom = $_POST['custom'];
        $values_custom = explode("_", $custom);
        $amount = $values_custom[0];
        $data_purchase = $_POST['payment_date'];
        $currency = $_POST['mc_currency'];
        $price_delivery = $values_custom[1];
        $phone = $values_custom[2];

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		try {
			$msg = "ok";
			$mail->isSMTP();   
			$mail->CharSet = "UTF-8";                                          
			$mail->SMTPAuth   = true;

			// Настройки вашей почты

            $mail->Host       = 'mail.rapidtestscovid19.com'; // SMTP сервера
            $mail->Username   = 'postmaster@rapidtestscovid19.com'; // Логин на почте
            $mail->Password   = 'SiriusDevOps777'; // Пароль на почте
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false; 
            $mail->Port = 25; 
			$mail->setFrom('postmaster@rapidtestscovid19.com', "$title"); // Адрес самой почты

			// Получатель письма
			$mail->addAddress("postmaster@rapidtestscovid19.com"); // Заменить на email клиента     

			// -----------------------
			// Само письмо
			// -----------------------
			$mail->isHTML(true);

			$mail->Subject = "$subject";
			$mail->Body    = "<b>Данные:</b><br>
			                  <b>Имя клиента:</b>$name<br>
			                  <b>Адрес доставки:</b>$address<br>
			                  <b>Телефон для доставки:</b>$phone<br>
			                  <b>Итоговая цена:</b>$totalPrice<br>
			                  <b>Валюта:</b>$currency<br>
			                  <b>Количество товара:</b>$amount<br>
			                  <b>Дата покупки:</b>$data_purchase<br>
			                  <b>Доставка:</b>$price_delivery<br>
                             ";

			// Проверяем отравленность сообщения
			if ($mail->send()) {
				$ok = "$msg";
			} else {
				$error = "Сообщение не было отправлено. Неверно указаны настройки вашей почты";
			}

		} catch (Exception $e) {
			echo "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
		}
	} else {
	    return false;
	}
?>