<?php
/**
 * acs-fech
 * Fetch appointment dates from the American Ciizen Services (ACS) Appointment System
 *
 * Example file
 *
 * 2023 by Christian Berkman
 * https://github.com/christianberkman/acs-fetch
 */

 require __DIR__ . '/acs-fetch.php';

 
 try{
    // ACS Fetch
    $acs = new ACSFetch();
    $acs->countryCode = "UGA";
    $acs->postCode = "KMP";
    $acs->navigate();

    // Fetch next three months
    $months = 3;
    for($i = 0; $i <= $months; $i++){
    $month = date('n', strtotime("+{$i} months", time() ) );
    $year = date('Y', strtotime("+{$i} months", time() ) );
    $acs->fetchCalendar($month, $year);
    }

    // Print array with all fetched dates
    print_r($acs->dates);

    exit;

 } catch(\Exception $e){
    echo "Caught error: {$e}" . PHP_EOL;
    exit;
 }
 

