<?php

// Error handling
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


// Slim handling
require 'vendor/autoload.php';

$app = new \Slim\App;
$version = "v1";

// Define app routes

// Master account details
$app->get('/v1/m_acc_details', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/app/p-manage-payment-accounts/api/getaccountlist", $username, $password, "all");
});

// Payment accounts
$app->get('/v1/payment_accounts', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/api/g-payments/accounts?types=pg", $username, $password, "all");
});

$app->get('/v1/payment_account_details', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');
 	$account_number = $request->getParam('acc_number');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/api/g-payments/accounts?types=pg", $username, $password, $account_number);
});

$app->get('/v1/payment_account_transactions', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');
 	$account_number = $request->getParam('acc_number');

	echo getDetailsWithURL("payment_account_transactions", $username, $password, $account_number);
});

// Saving accounts
$app->get('/v1/saving_accounts', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/api/savings-arrangements/savings-accounts", $username, $password, "all");
});

$app->get('/v1/saving_account_details', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');
 	$account_number = $request->getParam('acc_number');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/api/savings-arrangements/savings-accounts", $username, $password, $account_number);
});

$app->get('/v1/saving_account_transactions', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');
    $account_number = $request->getParam('acc_number');

	echo getDetailsWithURL("saving_account_transactions", $username, $password, $account_number);
});

// Creditions
$app->get('/v1/debit_details', function ($request, $response, $args) {
 	$username = $request->getParam('username');
 	$password = $request->getParam('password');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/particulier/betalen/details-en-instellingen/pasgebruik/api/debitcard", $username, $password, "all");
});

$app->get('/v1/daily_limit', function ($request, $response, $args) {
	$username = $request->getParam('username');
 	$password = $request->getParam('password');

	echo getDetailsWithURL("https://bankieren.mijn.ing.nl/api/dailypaymentlimit/limit", $username, $password, "all");
});

// Page contents
function getDetailsWithURL($url, $user, $pass, $request) {

	$login = request('https://mijn.ing.nl/internetbankieren/SesamLoginServlet');
	preg_match_all ('/<input[^>]+name="([^"]+)"/',$login,$inputs);

	for ($i=0; $i < 2; $i++) { 
		request('https://mijn.ing.nl/internetbankieren/SesamLoginServlet',$inputs[1][0] . '=' . $user . '&' . $inputs[1][1] . '=' . $pass . '&' . $inputs[1][2] . '=off');
		$requestURL = $url;
		if (!strpos($url, "https")) {
			$requestURL = "https://bankieren.mijn.ing.nl/api/g-payments/accounts?types=pg";
		} 
		if (!strpos(request($requestURL), "Inloggen Mijn ING")) {

			if ($request == "all") {
				// Get all data, non encoded
				$data = decode (request ($url));
			} else if ($request == "encoded") {
				// Get all data, encoded
				$data = request ($url);
			} else if (strpos($request, "ING")) {
				if ($url == "payment_account_transactions") {
					$allAccounts = decode (request ("https://bankieren.mijn.ing.nl/api/g-payments/accounts?types=pg"));
					$json = json_decode($allAccounts, true);
					for ($i=0; $i < count($json["accounts"]); $i++) { 
						if ($json["accounts"][$i]["accountNumber"] == $request) {
							// Gather data unique to account, so we can request the transactions.
							$ean = $json["accounts"][$i]["ean"];
							$agreementType = $json["accounts"][$i]["agreementType"];

							// Get the transactions.

							$transactionsURL = "https://bankieren.mijn.ing.nl/api/reporting/transactions?a=$agreementType&ean=$ean";
							$data = decode ( request ($transactionsURL));

							// To make sure that this method only gets called once, we break here.
						}
					}
				} else if ($url == "saving_account_transactions") {
					$allAccounts = decode (request ("https://bankieren.mijn.ing.nl/api/savings-arrangements/savings-accounts"));
					$json = json_decode($allAccounts, true);
					for ($i=0; $i < count($json["accounts"]); $i++) { 
						if ($json["accounts"][$i]["accountNumber"] == $request) {
							// Gather data unique to account, so we can request the transactions.
							$accountID = $json["accounts"][$i]["id"];

							// Get the transactions.

							$transactionsURL = "https://bankieren.mijn.ing.nl/api/savings-arrangements/savings-accounts/$accountID/transactions";
							$data = decode ( request ($transactionsURL));

							// To make sure that this method only gets called once, we break here.
						}
					}
				} else {
					$allReturnedData = decode ( (request ($url)));
					$json = json_decode($allReturnedData, true);
					for ($i=0; $i < count($json["accounts"]); $i++) { 
						if ($json["accounts"][$i]["accountNumber"] == $request) {
							$data = $json["accounts"][$i];
							$data["success"] = true;
							$data = json_encode($data);
						}
					}

				}
			}
		}
	}

	if (!isset($data)) {
		$data = array('success' => false,
		'message' => 'Failed the request or wrong data.');
		$data = json_encode($data);
	}

	return $data;
}

// JSON functions
function decode($str) { 

	$str = str_replace(")]}',", "", $str);
	$str = json_decode($str, true);
	$str["success"] = true;
	$str = json_encode($str);
    return stripslashes($str);
}

// Page request
function request($url,$post = false) {
    $curl = curl_init($url);
    if ($post)
        curl_setopt ($curl,CURLOPT_POSTFIELDS,$post);
    curl_setopt ($curl,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt ($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt ($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt ($curl,CURLOPT_COOKIEFILE,'cookie.txt');
    curl_setopt ($curl,CURLOPT_COOKIEJAR,'cookie.txt');
    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
}

// Run app
$app->run();

?>