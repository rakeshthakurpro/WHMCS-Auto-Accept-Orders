<?php 
/**
 * Auto accept whmcs order 
 * Developer : Rakesh Kumar
 * Email : whmcsninja@gmail.com
 * Website : whmcsninja.com
 *
 * Copyrights @ www.whmcsninja.com
 * www.whmcsninja.com
 *
 * Hook version 1.0.0
 *
 * */
use WHMCS\Database\Capsule;




add_hook('AfterShoppingCartCheckout', 1, function($vars) {
	$ServiceIDs = $vars['ServiceIDs'];
	foreach($ServiceIDs as $ServiceID)
	{
		 $GData = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('tblorders', 'tblhosting.orderid', '=', 'tblorders.id')
            ->where('tblhosting.id',$ServiceID)
            ->select('tblproducts.autosetup as productAutosetup','tblorders.id as orderid','tblhosting.firstpaymentamount as productAmount','tblhosting.id as serviceId')
            ->get();

            $isinvoicedata = Capsule::table('tblorders')->where('id',$GData[0]->orderid)->get();
            if($isinvoicedata)
            {
				$isinvoice = $isinvoicedata[0]->invoiceid;
				if($isinvoice)
					{
						if($GData[0]->productAutosetup == "payment")
						{
							$InvoiceStatus = Capsule::table('tblorders')
							->join('tblinvoices', 'tblorders.invoiceid', '=', 'tblinvoices.id')
							->where('tblorders.id',$GData[0]->orderid)
							->select('tblinvoices.status')
							->get();
							if($GData[0]->productAmount != "0.00")
							{
								if($InvoiceStatus[0]->status == 'Paid')
									{
									MakeAcceptOrder($GData[0]->orderid,$GData[0]->serviceId);
									}
							}

						}
					}
				else
				{
					if($GData[0]->productAutosetup == "order")
					{
						MakeAcceptOrder($GData[0]->orderid,$GData[0]->serviceId);
					}

					if($GData[0]->productAutosetup == "payment")
					{
						$InvoiceStatus = Capsule::table('tblorders')
						->join('tblinvoices', 'tblorders.invoiceid', '=', 'tblinvoices.id')
						->where('tblorders.id',$GData[0]->orderid)
						->select('tblinvoices.status')
						->get();
						if($GData[0]->productAmount != "0.00")
						{
							if($InvoiceStatus[0]->status == 'Paid')
								{
								MakeAcceptOrder($GData[0]->orderid,$GData[0]->serviceId);
								}
						}

					}
				}
            }
          

            
	}
	
});


add_hook('InvoicePaid', 1, function($vars) {
	$InvoiceID = $vars['invoiceid'];
	$GData = Capsule::table('tblorders')
	        ->join('tblhosting', 'tblorders.id', '=', 'tblhosting.orderid')
	        ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
	        ->where('tblorders.invoiceid',$InvoiceID)
	        ->select('tblproducts.autosetup as productAutosetup','tblorders.id as orderid','tblhosting.firstpaymentamount as productAmount','tblhosting.id as serviceId')
	        ->get();
    
			if($GData[0]->productAutosetup == "order")
            {
            	MakeAcceptOrder($GData[0]->orderid,$GData[0]->serviceId);
            }

            if($GData[0]->productAutosetup == "payment")
            {
	            MakeAcceptOrder($GData[0]->orderid,$GData[0]->serviceId);
            }        
    // Perform hook code here...
});

function MakeAcceptOrder($OrderID = "",$ServiceID = "")
{
	$command = 'AcceptOrder';
	$postData = array(
	    'orderid' => $OrderID,
	    'autosetup' => '1',
	    'sendemail' => '1',
	);
	 $admin = Capsule::table('tbladmins')
	            ->where('roleid', '=', 1)
	            ->get();
	    $adminUsername = $admin[0]->username;

	$results = localAPI($command, $postData, $adminUsername);
	
}


?>
