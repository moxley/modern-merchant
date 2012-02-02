<?php
/**
 * Mock version of the PayPal Name-Value-Pair API
 */

$vals = array(
    'TIMESTAMP'       => date('Y-m-dTH:i:sZ'),
    'CORRELATIONID'   => uniqid(''), //'45588912cdba2',
    'ACK'             => 'Success',
    'VERSION'         => '85.0',
    'BUILD'           => '2488002',
    'L_ERRORCODE0'    => 'banana',
    'L_SHORTMESSAGE0' => 'shorty',
    'L_LONGMESSAGE0'  => 'longy',
    'L_SEVERITYCODE0' => '',
    'AMT'             => '5.00',
    'CURRENCYCODE'    => 'USD'
);

$pairs = array();
foreach ($vals as $key=>$value) {
  $pairs[] = urlencode($key) . '=' . urlencode($value);
}

echo implode('&', $pairs);
