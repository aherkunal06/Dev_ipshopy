<?php
// Heading
$_['heading_title']          	= 'Order Export Suite';
$_['export_title']           	= 'Orders (%s)';

// Text
$_['text_list']          		 = 'Order Export Suite';
$_['text_all_store']			 = 'All Stores';
$_['text_all_stock_status']	 	 = 'All Stock Status';
$_['text_all_status']	 		 = 'All Status';
$_['text_xls']	 				 = 'XLS';
$_['text_xlsx']	 				 = 'XLSX';
$_['text_csv']	 				 = 'CSV';
$_['text_xml']	 				 = 'XML';
$_['text_json']	 				 = 'JSON';
$_['text_no_fields']	 		 = 'No Extra Field.';
$_['text_default']	 			 = 'Default';
$_['text_from']	 			 	 = 'From';
$_['text_to']	 			 	 = 'To';
$_['text_success']	 			 = 'Your Export Has Been Done. Check Your Downloads.';
$_['text_no_results']	 		 = 'No Order Records Found With Select Filters!';
$_['text_missing']	 			 = 'Missing Orders';
$_['text_all']	 			 	 = 'All';
$_['text_order_id']	 			 = 'Order ID';
$_['text_customer']	 			 = 'Customer';
$_['text_order_status']	 		 = 'Order Status';
$_['text_date_added']	 		 = 'Date Added';
$_['text_date_modified']		 = 'Date Modified';
$_['text_total']	 			 = 'Order Total';
$_['text_asc']	 			 	 = 'ASC';
$_['text_desc']	 			 	 = 'DESC';
$_['text_order']	 			 = 'Order';
$_['text_filters_note']	 			 = 'All filters are optional and useful to narrowing down the export data';

// Entry
$_['entry_order_id']			 = 'Order IDS';
$_['entry_order_status']		 = 'Order Status';
$_['entry_total']		 		 = 'Order Total';
$_['entry_customer']		 	 = 'Filter Customer';
$_['entry_date_start']		 	 = 'Date From';
$_['entry_date_end']		 	 = 'Date To';
$_['entry_limit_start']		 	 = 'Limit Start';
$_['entry_limit_end']		 	 = 'Limit End';
$_['entry_customer_group']	 	 = 'Customer Group';
$_['entry_store']	 			 = 'Store';
$_['entry_payment_method']	 	 = 'Payment Method';
$_['entry_shipping_method']	 	 = 'Shipping Method';
$_['entry_product']	 			 = 'Filter Product';
$_['entry_order_fields']		 = 'Export Order Fields';
$_['entry_extra_field']			 = 'Export Additional Fields';
$_['entry_format']				 = 'Export Format';
$_['entry_orderdetail']			 = 'Order Details';
$_['entry_customerdetail']	 	 = 'Customer Details';
$_['entry_customfields']	 	 = 'Custom Fields';
$_['entry_orderhistory']	 	 = 'Order History';
$_['entry_shippingaddress']		 = 'Shipping Address';
$_['entry_paymentaddress']	 	 = 'Payment Address';
$_['entry_totals']				 = 'Order Totals';
$_['entry_sort']	 	 	 	 = 'Sort';
$_['entry_order']	 	 	 	 = 'Order';
$_['entry_productdetail']	 	 = 'Order Products';
$_['entry_voucherdetail']	 	 = 'Order Vouchers';
$_['entry_payment_country']	 	 = 'Payment Country';
$_['entry_payment_zone']	 	 = 'Payment Zone / State';
$_['entry_payment_postcode']	 = 'Payment Postcode / Zipcode';
$_['entry_shipping_country']	 = 'Shipping Country';
$_['entry_shipping_zone']	 	 = 'Shipping Zone / State';
$_['entry_shipping_postcode']	 = 'Shipping Postcode / Zipcode';
$_['entry_invoice']	 			 = 'Invoice Number';
$_['entry_invoice_prefix']	 	 = 'Invoice Prefix';
$_['entry_language']	 	 	 = 'Language';
$_['entry_currency']	 	 	 = 'Currency';

// Button
$_['button_export']				 = 'Export Orders';
$_['button_advance_filter']		 = 'Click here to show more filters';

// Tabs
$_['tab_general'] 		= 'General';
$_['tab_support']          	 	 = 'Support';

// Help

$_['help_order_id']	 	 		 = 'Filter Multiple Order IDS <br/> (Comma Seperated) I.e: 100,101 <br/>(Within Range) I.e: 50 - 60. <br/> Both I.e: 50-60, 70-80, 100, 101';
$_['help_order_limit']	  		 = 'Set Range Of Order Limit. If Minimum Order Limit Not Given But Maximum Given Than Order Limit Export From 0 To Maximum Given.if Maximum Order Limit Not Given But Minimum Given Than Order Limit Export From Minimum Given To Unlimited. If Not Both Maximum And Minimum Not Given, Order Limit Will Be Ignored.';
$_['help_payment_method']	 	 = 'Choose Payment Methods To Filter. I.e: Cash On Delivery, Bank Transfer';
$_['help_shipping_method']	 	 = 'Choose Shipping Methods To Filter. I.e: Free Shipping, Flat Rate';


$_['help_product']				 = '(autocomplete)';
$_['help_customer']				 = '(autocomplete)';
$_['help_extra_field']			 = 'These Are Addition Columns Created In Order Table';
$_['help_format']				 = 'Select Format In Which You Want To Export Orders';

$_['help_orderdetail']			 = 'Select Yes If Want To Export Order Details';
$_['help_customerdetail']		 = 'Select Yes If Want To Export Customer Details';
$_['help_productdetail']	 	 = 'Select Yes If Want To Export Order Products Details Like Product Name, Model, Total Price, etc.';
$_['help_voucherdetail']	 	 = 'Select Yes If Want To Export Order Vouchers';
$_['help_totals']	 	 	 	 = 'Select Yes If Want To Export Order Totals';

$_['help_shippingaddress']	 	 = 'Select Yes If Want To Export  Shipping Address Used By Customer While Order';
$_['help_paymentaddress']	 	 = 'Select Yes If Want To Export Payment Address  Used By Customer While Order';
$_['help_customfields']	 	 	 = 'Select Yes If Want To Export  Custom Fields Provided By Opencart';

// Table
$_['table_order']				 = 'Order';

// PlaceHolder

// Export
$_['export_order_id']			 = 'Order ID';
$_['export_invoice_prefix']		 = 'Invoice Prefix';
$_['export_invoice_no']			 = 'Invoice Number';
$_['export_store_id']			 = 'Store ID';
$_['export_store_name']			 = 'Store Name';
$_['export_store_url']			 = 'Store URL';
$_['export_customer_id']		 = 'Customer ID';
$_['export_customer']			 = 'Customer Name';
$_['export_email']				 = 'Email';
$_['export_telephone']			 = 'Telephone';
$_['export_fax']				 = 'Fax';
$_['export_order_products']		 = 'Products';
$_['export_order_options']		 = 'Product Options';
$_['export_order_vouchers']		 = 'Order Vouchers';
$_['export_order_totals']		 = 'Total Details';
$_['export_customfields']		 = 'Custom Fields';
$_['export_paymentaddress']		 = 'Payment Address';
$_['export_paymentcustomfields'] = 'Payment Custom Fields';
$_['export_shippingaddress']	 = 'Shipping Address';
$_['export_shipping_customfields']	= 'Shipping Custom Fields';
$_['export_payment_method']		 = 'Payment Method';
$_['export_payment_code']		 = 'Payment Code';
$_['export_shipping_method']	 = 'Shipping Method';
$_['export_shipping_code']		 = 'Shipping Code';
$_['export_comment']			 = 'Comment';
$_['export_total']				 = 'Order Total';
$_['export_order_status_id']	 = 'Order Status ID';
$_['export_order_status']		 = 'Order Status';
$_['export_affiliate_id']		 = 'Affiliate ID';
$_['export_commission']			 = 'Commission';
$_['export_marketing_id']		 = 'Marketing ID';
$_['export_tracking']			 = 'Tracking';
$_['export_language_id']		 = 'Language Id';
$_['export_currency_code']		 = 'Currency';
$_['export_currency_value']		 = 'Currency Value';
$_['export_ip']					 = 'IP Address';
$_['export_forwarded_ip']		 = 'Forwarded IP';
$_['export_user_agent']			 = 'User Agent';
$_['export_accept_language']	 = 'Accept Language';
$_['export_date_added']			 = 'Date Added';
$_['export_date_modified']		 = 'Date Modified';

// Error
$_['error_warning']          	 = 'Warning: Please Check The Form Carefully For Errors!';
$_['error_permission']       	 = 'Warning: You Do Not Have Permission To Modify Order Export Suite!';
$_['error_onerequired']       	 = 'Please Select Any One Field For Export!';