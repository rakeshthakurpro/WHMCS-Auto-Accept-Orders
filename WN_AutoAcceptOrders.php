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
function settings(){
    $admin = Capsule::table('tbladmins')->where('roleid', 1)->first();
    return array(
        'admin' => $admin->id, // Don't add anything Here
        'autosetup' => false, // determines whether product provisioning is performed
        'sendregistrar' => false, // determines whether domain automation is performed
        'sendemail' => true, // sets if welcome emails for products and registration confirmation emails for domains should be sent 
        'ispaid' => true, // set to true if you want to accept only paid orders
       
    );
}
function get_order($invoiceid) {
    $order = Capsule::table('tblorders')->where('invoiceid', $invoiceid)->first();
    return array('id' => $order->id, 'status' => $order->status); // Don't add anything Here);
}

/*Hook code execute if invoice is paid from admin area */

add_hook('InvoicePaid', 1, function($vars) {
    $settings = settings();
    $order = get_order($vars['invoiceid']);

    if ($order['status'] == 'Pending') {
        $result = localAPI('AcceptOrder', array('orderid' => $order['id']), $settings['admin']);
    }
});

/*Below Code Invoked while invoice paid from clientarea during order Process */
add_hook('AfterShoppingCartCheckout', 1, function($vars) {
    $settings = settings();
    
    $order = get_order($vars['InvoiceID']);
    $ispaid = $settings['ispaid'];
    $autosetup=$settings['autosetup'];

    $Getinvoice = localAPI('GetInvoice', array('invoiceid' => $vars['InvoiceID'],), $settings['admin']);
    $ispaid = ($Getinvoice['result'] == 'success' && $Getinvoice['balance'] <= 0) ? true : false;
    
    /*     * *******Uncomment below code if you want product to execute Module create command for products having price 0.00 ********** */
    
    //$autosetup=($Getinvoice['result'] == 'success' && $Getinvoice['balance'] <= 0) ? true : false;

    /*     * *******Uncomment below code if you want product to execute Module create command for Free products ********** */
    
//if(!$vars['InvoiceID']){
    //  $autosetup = true;
//}
    /*     * ****************************************** */

    if ($ispaid) {
        $result = localAPI('AcceptOrder', array('orderid' => $order['id'],'autosetup' => $autosetup,'sendemail' => true, ), $admin);
    }
});
?>
