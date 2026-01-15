<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" class="mp-content">
 <div class="page-header">
  <div class="container-fluid">
   <div class="pull-right">
    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-warning"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
  </div>
   <h1><?php echo $heading_title; ?></h1>
   <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
   </ul>
  </div>
 </div>
 <div class="container-fluid">
  <?php if ($error_warning) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
   <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <div class="panel panel-default beforenotice">
		<div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-download"></i> <?php echo $text_list; ?></h3>
		</div>
		<div class="panel-body" id="form-order-export">
			<div class="alert alert-info"><i class="fa fa-info-circle"></i> <strong><?php echo $text_filters_note; ?></strong></div>
			<ul class="nav nav-tabs" style="position: relative;">
        <li class="active"><a href="#tab-general" data-toggle="tab"><i class="fa fa-cog"></i> <span><?php echo $tab_general; ?></span></a></li>
        <li><a href="#tab-support" data-toggle="tab"><i class="fa fa-thumbs-up"></i> <span><?php echo $tab_support; ?></span></a></li>
      </ul>
      <div class="tab-content">
	      <div class="tab-pane active" id="tab-general">
	      	<div class="row">
						<div class="col-sm-8">
							<div class="well">
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-orderid"><?php echo $entry_order_id; ?></label>
											<input type="text" name="find_order_id" value="" placeholder="<?php echo $entry_order_id; ?>" id="input-orderid" class="form-control" />
											<div class="help"><?php echo $help_order_id; ?></div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-total"><?php echo $entry_total; ?></label>
											<input type="text" name="find_total" value="" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-date-start"><?php echo $entry_date_start; ?></label>
											<div class="input-group date">
												<input type="text" name="find_date_start" value="" placeholder="<?php echo $entry_date_start; ?> : 2021-03-06" data-date-format="YYYY-MM-DD" id="input-date-start" class="form-control" />
												<span class="input-group-btn">
												<button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
												</span></div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-date-end"><?php echo $entry_date_end; ?></label>
											<div class="input-group date">
												<input type="text" name="find_date_end" value="" placeholder="<?php echo $entry_date_end; ?> : 2021-03-16" data-date-format="YYYY-MM-DD" id="input-date-end" class="form-control" />
												<span class="input-group-btn">
												<button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
												</span></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-limit-start"><?php echo $entry_limit_start; ?></label>
											<input type="text" name="find_limit_start" value="" placeholder="<?php echo $entry_limit_start; ?> : 0" id="input-limit-start" class="form-control" />
											<div class="help"><?php echo $help_order_limit; ?></div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-limit-end"><?php echo $entry_limit_end; ?></label>
											<input type="text" name="find_limit_end" value="" placeholder="<?php echo $entry_limit_end; ?> : 500" id="input-limit-end" class="form-control" />
											<div class="help"><?php echo $help_order_limit; ?></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
											<select name="find_order_status" id="input-order-status" class="form-control" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<option value="0"><?php echo $text_missing; ?></option>
												<?php foreach ($order_statuses as $order_status) { ?>
												<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-customer-group"><?php echo $entry_customer_group; ?></label>
											<select class="form-control" name="find_customer_group" id="input-customer-group" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($customer_groups as $customer_group) { ?>
												<option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-payment"> <?php echo $entry_payment_method; ?></label>
											<select name="find_payment_method[]" id="input-payment" class="form-control" multiple="multiple">
												<?php foreach ($payment_methods as $payment_method) { ?>
												<option value="<?php echo $payment_method['code']; ?>"><?php echo $payment_method['heading_title']; ?></option>
												<?php } ?>
											</select>
											<div class="help"><?php echo $help_payment_method; ?></div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-shipping"><?php echo $entry_shipping_method; ?></label>
											<select name="find_shipping_method[]" id="input-shipping" class="form-control" multiple="multiple">
												<?php foreach ($shipping_methods as $shipping_method) { ?>
												<option value="<?php echo $shipping_method['code']; ?>"><?php echo $shipping_method['heading_title']; ?></option>
												<?php } ?>
											</select>
											<div class="help"><?php echo $help_shipping_method; ?></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="input-store"><?php echo $entry_store; ?></label>
											<select name="find_store_id" id="input-store" class="form-control">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($stores as $store) { ?>
												<option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-sort"><?php echo $entry_sort; ?></label>
											<select name="find_sort" id="input-sort" class="form-control">
												<?php foreach ($sorts as $sort) { ?>
												<option value="<?php echo $sort['value']; ?>"><?php echo $sort['text']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-order"><?php echo $entry_order; ?></label>
											<select name="find_order" id="input-order" class="form-control">
												<option value="ASC"><?php echo $text_asc; ?></option>
												<option value="DESC"><?php echo $text_desc; ?></option>
											</select>
										</div>
									</div>
								</div>
								</div>
						</div>
						<div class="col-sm-4">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><label class="control-label" for="input-product"><i class="fa fa-tags"></i><?php echo $entry_product; ?></label></h3>
								</div>
								<div class="panel-body">
									<input type="text" name="product_name" value="" placeholder="<?php echo $entry_product; ?>" id="input-product" class="form-control" />
									<div id="export-product" class="well well-sm"></div>
									<div class="help"><?php echo $help_product; ?></div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><label class="control-label" for="input-customer"><i class="fa fa-user"></i><?php echo $entry_customer; ?></label></h3>
								</div>
								<div class="panel-body">
									<input type="text" name="customer_name" value="" placeholder="<?php echo $entry_customer; ?>" id="input-customer" class="form-control" />
									<div id="export-customer" class="well well-sm"></div>
									<div class="help"><?php echo $help_customer; ?></div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<p><button class="btn btn-success btn-sm" id="button-advance-filter"><i class="fa fa-search"></i>	<?php echo $button_advance_filter; ?></button></p>
						</div>
					</div>
					<div class="row" style="display: none;" id="advance-filters">
						<div class="col-sm-12">
							<div class="well">
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-payment-country-id"><?php echo $entry_payment_country; ?></label>
											<select name="find_payment_country_id" id="input-payment-country-id" class="form-control" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($countries as $country) { ?>
												<option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-payment-zone-id"><?php echo $entry_payment_zone; ?></label>
											<select name="find_payment_zone_id" id="input-payment-zone-id" class="form-control" data-live-search="true"></select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-payment-postcode"><?php echo $entry_payment_postcode; ?></label>
											<input type="text" name="find_payment_postcode" id="input-payment-postcode" class="form-control" value="" />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-language-id"><?php echo $entry_language; ?></label>
											<select name="find_language_id" id="input-language-id" class="form-control" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($languages as $language) { ?>
												<option value="<?php echo $language['language_id']; ?>"><?php echo $language['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-shipping-country-id"><?php echo $entry_shipping_country; ?></label>
											<select name="find_shipping_country_id" id="input-shipping-country-id" class="form-control" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($countries as $country) { ?>
												<option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-shipping-zone-id"><?php echo $entry_shipping_zone; ?></label>
											<select name="find_shipping_zone_id" id="input-shipping-zone-id" class="form-control" data-live-search="true"></select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-shipping-postcode"><?php echo $entry_shipping_postcode; ?></label>
											<input type="text" name="find_shipping_postcode" id="input-shipping-postcode" class="form-control" value="" />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-currency-id"><?php echo $entry_currency; ?></label>
											<select name="find_currency_id" id="input-currency-id" class="form-control" data-live-search="true">
												<option value=""><?php echo $text_all; ?></option>
												<?php foreach ($currencies as $currency) { ?>
												<option value="<?php echo $currency['currency_id']; ?>"><?php echo $currency['code']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-invoice-prefix"><?php echo $entry_invoice_prefix; ?></label>
											<input type="text" name="find_invoice_prefix" id="input-invoice-prefix" class="form-control" value="" />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label" for="input-invoice"><?php echo $entry_invoice; ?></label>
											<input type="text" name="find_invoice" id="input-invoice" class="form-control" value="" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-7">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><i class="fa fa-shopping-cart"></i><?php echo $entry_order_fields; ?></h3>
								</div>
								<div class="panel-body">
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-orderdetail"><?php echo $entry_orderdetail; ?></label>
												<div class="">
													<div id="input-orderdetail" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_orderdetail" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_orderdetail" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_orderdetail; ?></div>
												</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-customerdetail"><?php echo $entry_customerdetail; ?></label>
												<div class="">
													<div id="input-customerdetail" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_customerdetail" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_customerdetail" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_customerdetail; ?></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-productdetail"><?php echo $entry_productdetail; ?></label>
												<div class="">
													<div id="input-productdetail" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_productdetail" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_productdetail" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_productdetail; ?></div>
												</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-voucherdetail"><?php echo $entry_voucherdetail; ?></label>
												<div class="">
													<div id="input-voucherdetail" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_voucherdetail" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_voucherdetail" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_voucherdetail; ?></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-totals"><?php echo $entry_totals; ?></label>
												<div class="">
													<div id="input-totals" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_ordertotals" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_ordertotals" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_totals; ?></div>
												</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-shippingaddress"><?php echo $entry_shippingaddress; ?></label>
												<div class="">
													<div id="input-shippingaddress" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_shippingaddress" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_shippingaddress" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_shippingaddress; ?></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-paymentaddress"><?php echo $entry_paymentaddress; ?></label>
												<div class="">
													<div id="input-paymentaddress" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_paymentaddress" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_paymentaddress" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_paymentaddress; ?></div>
												</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group mp-buttons">
												<label class="control-label" for="input-customerdetail"><?php echo $entry_customfields; ?></label>
												<div class="">
													<div id="input-customerdetail" class="btn-group btn-group-justified" data-toggle="buttons">
														<label class="btn btn-primary active"><input type="radio" name="find_customfields" value="1" checked="checked" /> <?php echo $text_yes; ?></label>
														<label class="btn btn-primary"><input type="radio" name="find_customfields" value="0" /> <?php echo $text_no; ?></label>
													</div>
													<div class="help"><?php echo $help_customfields; ?></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php if ($extrafields) { ?>
						<div class="col-sm-5">
							<?php foreach ($extrafields as $extrafield) { ?>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><label class="control-label"><i class="fa fa-list"></i><?php echo $entry_extra_field; ?></label></h3>
									<div class="help"><?php echo $help_extra_field; ?></div>
								</div>
								<div class="panel-body">
									<?php $okfields = $extrafield['fields']; ?>

									<?php foreach (array_chunk($okfields, ceil(count($okfields) / 2)) as $extrafield_name) { ?>
									<div class="row">
										<div class="col-sm-12">
											<div class="checkbox">
											<?php foreach ($extrafield_name as $extrafield_values) { ?>
												<label class="checkbox-inline"><input type="checkbox" name="find_extrafields[]" value="<?php echo $extrafield['tablename']; ?>::<?php echo $extrafield_values; ?>" /> <?php echo $extrafield_values; ?></label>
											<?php } ?>
											</div>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
							<?php } ?>
						</div>
						<?php } ?>
						<div class="col-sm-5">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><label class="control-label" for="export-format"><i class="fa fa-file-o"></i><?php echo $entry_format; ?></label></h3>
								</div>
								<div class="panel-body">
									<div class="form-group mp-buttons">
										<select name="find_format" class="form-control" id="export-format">
										<option value="xls"><?php echo $text_xls; ?></option>
										<option value="xlsx"><?php echo $text_xlsx; ?></option>
										<option value="csv"><?php echo $text_csv; ?></option>
										<option value="xml"><?php echo $text_xml; ?></option>
										<option value="json"><?php echo $text_json; ?></option>
										</select>
										<div class="help"><?php echo $help_format; ?></div>
									</div>
									<div class="buttons exports">
										<button type="button" class="btn btn-success btn-block" id="exporter-order"><i class="fa fa-download" aria-hidden="true"></i> <?php echo $button_export; ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
	      </div>
	      <div class="tab-pane" id="tab-support">
        	<div class="bs-callout bs-callout-info">
	          <h4>ModulePoints <?php echo $heading_title; ?></h4>
	          <center><strong><?php echo $heading_title; ?> - Version 2.0 </strong></center> <br/>
	          <p><?php echo $heading_title; ?> v2 comes with new features and few bug fixes. With this extension you have multiple filters and many export fields, so you get only data which you required. It's a upgraded version of "Old <?php echo $heading_title; ?>" extension with various new features and bug fixes.</p>
	        </div>
        	<fieldset>
            <div class="form-group">
              <div class="col-md-12 col-xs-12">
                <h4 class="text-mpsuccess text-center"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Thanks For Choosing Our Extension</h4>
                 <ul class="list-group">
                  <li class="list-group-item clearfix">Installed Version <span class="badge"><i class="fa fa-gg" aria-hidden="true"></i> V.2.0</span></li>
                </ul>
                <h4 class="text-mpsuccess text-center"><i class="fa fa-phone" aria-hidden="true"></i> Please Contact Us In Case Any Issue OR Give Feedback!</h4>
                <ul class="list-group">
                  <li class="list-group-item clearfix">support@modulepoints.com <span class="badge"><a href="mailto:support@modulepoints.com?Subject=Request Support: <?php echo $heading_title; ?> Extension"><i class="fa fa-envelope"></i> Contact Us</a></span></li>
                </ul>
              </div>
            </div>
          </fieldset>
        </div>
      </div>
   </div>
  </div>
 </div>
 <div class="mpteam"></div>
<script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
<script type="text/javascript"><!--
$(function() {
	// Exporter Order
	$('#exporter-order').click(function() {
		$.ajax({
			url: 'index.php?route=<?php echo $isdir_extension; ?>mpimporterexporter/export_order/export&<?php echo $get_token; ?>=<?php echo $token; ?>',
			type: 'post',
			data: $('#form-order-export input[type=\'text\'], #form-order-export input[type=\'hidden\'], #form-order-export select, #form-order-export input[type=\'checkbox\']:checked, #form-order-export input[type=\'radio\']:checked').serialize(),
			dataType: 'json',
			beforeSend: function() {
				$('.alert-danger, .alert-success').remove();
				$('#exporter-order').button('loading');
				$('.mpteam').after('<div class="modal-backdrop in mpteam_loader"></div><div class="loader mpteam_loader"></div>');
			},
			complete: function() {
				$('#exporter-order').button('reset');
				$('.mpteam_loader').remove();
			},
			success: function(json) {
				if(json['error']) {
					$('.beforenotice').before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+ json['error'] +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}

				if(json['href']) {
					window.location = json['href'];
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				if(xhr.responseText) {
					$('.beforenotice').before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+ xhr.responseText +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
			}
		});
	});


	// Product
	$('input[name=\'product_name\']').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/product/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' + encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['name'],
							value: item['product_id']
						}
					}));
				}
			});
		},
		select: function(item) {
			$('input[name=\'product_name\']').val('');

			$('#export-product' + item['value']).remove();

			$('#export-product').append('<div id="export-product' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="find_product[]" value="' + item['value'] + '" /></div>');
		}
	});
	$('#export-product').delegate('.fa-minus-circle', 'click', function() {
		$(this).parent().remove();
	});


	// Customer
	$('input[name=\'customer_name\']').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=customer/customer/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' + encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['name'],
							value: item['name']
						}
					}));
				}
			});
		},
		select: function(item) {
			$('input[name=\'customer_name\']').val('');

			$('#export-customer' + item['value']).remove();

			$('#export-customer').append('<div id="export-customer' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="find_customer[]" value="' + item['value'] + '" /></div>');
		}
	});
	$('#export-customer').delegate('.fa-minus-circle', 'click', function() {
		$(this).parent().remove();
	});


	// Date
	$('.date').datetimepicker({
		pickTime: false
	});

	$('select[name=\'find_payment_country_id\']').on('change', function() {
		$.ajax({
			url: 'index.php?route=localisation/country/country&<?php echo $get_token; ?>=<?php echo $token; ?>&country_id=' + this.value,
			dataType: 'json',
			beforeSend: function() {
				$('select[name=\'find_payment_country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
			},
			complete: function() {
				$('.fa-spin').remove();
			},
			success: function(json) {
				html = '<option value="" selected="selected"><?php echo $text_all; ?></option>';

				if (json['zone'] && json['zone'] != '') {
					for (i = 0; i < json['zone'].length; i++) {
	  			html += '<option value="' + json['zone'][i]['zone_id'] + '"';
						html += '>' + json['zone'][i]['name'] + '</option>';
					}
				} else {
					html += '<option value="0"><?php echo $text_none; ?></option>';
				}

				$('select[name=\'find_payment_zone_id\']').html(html);
			}
		});
	});

	$('select[name=\'find_payment_country_id\']').trigger('change');

	$('select[name=\'find_shipping_country_id\']').on('change', function() {
		$.ajax({
			url: 'index.php?route=localisation/country/country&<?php echo $get_token; ?>=<?php echo $token; ?>&country_id=' + this.value,
			dataType: 'json',
			beforeSend: function() {
				$('select[name=\'find_shipping_country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
			},
			complete: function() {
				$('.fa-spin').remove();
			},
			success: function(json) {
				html = '<option value="" selected="selected"><?php echo $text_all; ?></option>';

				if (json['zone'] && json['zone'] != '') {
					for (i = 0; i < json['zone'].length; i++) {
	  			html += '<option value="' + json['zone'][i]['zone_id'] + '"';
						html += '>' + json['zone'][i]['name'] + '</option>';
					}
				} else {
					html += '<option value="0"><?php echo $text_none; ?></option>';
				}

				$('select[name=\'find_shipping_zone_id\']').html(html);
			}
		});
	});

	$('select[name=\'find_shipping_country_id\']').trigger('change');

	$('#button-advance-filter').click(function() {
		$('#advance-filters').slideDown(200);

		$('#button-advance-filter').attr('disabled', true);
	});
});
//--></script>
</div>
<?php echo $footer; ?>
