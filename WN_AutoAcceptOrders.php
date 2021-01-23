<?php

/*
 *
 * Auto Accept Orders
 * Created By Rakesh Kumar(rakeshthakurpro0306@gmail.com)
 *
 * Copyrights @ www.whmcsninja.com
 * www.whmcsninja.com
 *
 * Hook version 1.0.0
 *
 * */
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

/* * *******************
  Auto Accept Orders Settings
 * ******************* */

use WHMCS\Database\Capsule;

function AutoAcceptOrders_settings() {
    $admin = Capsule::table('tbladmins')->where('roleid', 1)->first();
    return array(
        'apiuser' => $admin->id, // Don't add anything Here
        'autosetup' => false, // determines whether product provisioning is performed
        'sendregistrar' => false, // determines whether domain automation is performed
        'sendemail' => true, // sets if welcome emails for products and registration confirmation emails for domains should be sent 
        'ispaid' => true, // set to true if you want to accept only paid orders
        'paymentmethod' => array(), // set the payment method you want to accept automaticly (leave empty to use all payment methods) * example array('paypal','amazonsimplepay')
    );
}

/* * ***************** */

add_hook('AfterShoppingCartCheckout', 1, function($vars) {
    $settings = AutoAcceptOrders_settings();
    $ispaid = true;

    if ($vars['InvoiceID']) {
        $Getinvoice = localAPI('getinvoice', array(
            'invoiceid' => $vars['InvoiceID'],
                ), $settings['apiuser']);

        $ispaid = ($Getinvoice['result'] == 'success' && $Getinvoice['balance'] <= 0) ? true : false;
       /* * *******Uncomment below code if you want product to execute Module create command for products having price 0.00 ********** */
      //  if ($Getinvoice['subtotal']<= 0) {            
        //    $settings['autosetup'] = true;
       // }
    }
    /* * *******Uncomment below code if you want product to execute Module create command for Free products ********** */
//if(!$vars['InvoiceID']){
  //  $settings['autosetup']=true;
//}
/* * ****************************************** */
    if ((!sizeof($settings['paymentmethod']) || sizeof($settings['paymentmethod']) && in_array($vars['PaymentMethod'], $settings['paymentmethod'])) && (!$settings['ispaid'] || $settings['ispaid'] && $ispaid)) {
        $result = localAPI('AcceptOrder', array(
            'orderid' => $vars['OrderID'],
            'autosetup' => $settings['autosetup'],
            'sendregistrar' => $settings['sendregistrar'],
            'sendemail' => $settings['sendemail'],
                ), $settings['apiuser']);
    }
});
?>
