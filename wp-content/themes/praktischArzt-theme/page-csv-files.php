<?php

global $user_ID;

if (isset($_REQUEST['action'])) {

    $authorID = isset($_REQUEST['authorID']) ? $_REQUEST['authorID'] : $user_ID;
    $jobID = isset($_REQUEST['jobID']) ? $_REQUEST['jobID'] : '';
    $price = isset($_REQUEST['price']) ? $_REQUEST['price'] : 0;
    $payer = isset($_REQUEST['payer']) ? $_REQUEST['payer'] : "";
    $accountNumber = isset($_REQUEST['accountNumber']) ? $_REQUEST['accountNumber'] : "";
    $bankNumber = isset($_REQUEST['bankNumber']) ? $_REQUEST['bankNumber'] : "";
    $company_location = et_get_user_field($user_ID, 'recent_job_location');
    $order_date = date('Y-m-d');
    $total_price = $price;
    //var_dump($_REQUEST);
    //exit;
    $filename = $payer . "-" . date('dmY-His') . ".csv";

    $result = "";
    $result .="Zahlungsempfanger,Betrag, Konto, Blz, Verwendungszweck1, Verwendungszweck2";
    $result .="\n";
    $result .=$payer . "," . $total_price . "," . $accountNumber . "," . $bankNumber . "," . (isset($company_location['full_location'])?$company_location['full_location']:'') . "," . $order_date . "";
    $result .="\n";

    file_put_contents(getcwd() . "/wp-content/uploads/files/" . $filename, $result); // . $filename//BeispielCSV.csv
    //var_dump(file_get_contents(getcwd() . "/wp-content/uploads/files/" . $filename));
    //echo 'finished';
    //header('Location:' . home_url() . '/?post_type=job&p=' . $jobID);
    $returnURL = home_url() . '/?post_type=job&p=' . $jobID;
    et_write_session('job_id', $jobID);
    //Payment Zertifikat with Debit
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "payment_debit_zertifikat") {
        $method = $_REQUEST['action'];
        $returnURL = home_url() . "/zertifikat/";
        update_user_meta($authorID, 'company_certificate_pa', 1);

        et_write_session('method', $method);

        $user_certificate = get_user_meta($authorID, 'certificate_buy_date', true);
        if ($user_certificate != "") {
            update_user_meta($authorID, 'certificate_buy_date', date('Y-m-d'));
        } else {
            add_user_meta($authorID, 'certificate_buy_date', date('Y-m-d'));
        }
    }



    $message = "Sie haben eine Zahlungsaufforderung mit allen Details per E-Mail bekommen.  Sobald Ihre Zahlung bei uns eingegangen ist, wird Ihr Stellenangebot freigeschaltet.";

    do_action('et_cash_checkout', $message);
    $data = array(
        'subject' => 'Payment Debit',
        'price' => $price,
        'quantity' => 1,
        'amt' => '19%',
        'total' => $price * 1.19,
        'csv_file' => home_url() . "/csv-files/?file=" . $filename//wp-content/uploads/
    );
    do_action('et_email_payment_debit', $data);

    $response = array(
        'success' => true,
        'returnURL' => $returnURL,
    );
    echo json_encode($response);
    exit;
} else if (isset($_REQUEST['file'])) {
    $filename = $_REQUEST['file'];
    $result = file_get_contents(getcwd() . "/wp-content/uploads/files/" . $filename);
    header("Content-Type: application/csv");
    header("Content-Disposition: attachment;filename=$filename");
    echo $result;
    exit;
}
