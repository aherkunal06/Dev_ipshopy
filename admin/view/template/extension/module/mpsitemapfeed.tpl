<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" class="mp-content">
  <div class="container-fluid">
    <div class="page-header">
      <div class="pull-right">
        <button type="submit" form="form-mpsitemapfeed" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
        <button type="submit" onclick="$('#stay_here').val(1)" form="form-mpsitemapfeed" data-toggle="tooltip" title="<?php echo $button_stay_here; ?>" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $button_stay_here; ?></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-warning"><i class="fa fa-times"></i> <?php echo $button_cancel; ?></a></div>
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
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> <?php echo $success; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    <?php } ?>

    <?php /* // module events starts */ ?>
    <?php if ($disable_events) { ?>
    <div class="activate_evs">
      <div class="alert alert-warning"><?php echo $text_disable_events; ?></div>
      <script type="text/javascript">
        $('.btn_activate_evs').on('click', function() {
          let $this = $(this);
          $.ajax({
            url: 'index.php?route=<?php echo $extension_path; ?>module/mpsitemapfeed/activateEvents&<?php echo $get_token; ?>=<?php echo $token; ?>',
            type: 'get',
            data: 'ae=1',
            dataType: 'json',
            beforeSend: function() {
              $('.alert-dismissible').remove();
              $this.button('loading');
            },
            complete: function() {
              $this.button('reset');
            },
            success: function(json) {
              if (json['success']) {
                $this.parent('.alert').after('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> '+ json['success'] +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                setTimeout(() => {
                  $('.activate_evs').remove();
                }, 5000);
              }
              if (json['error']) {
                if (json['error']['warning']) {
                  $this.parent('.alert').after('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> '+ json['error']['warning'] +' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
              }
            },
            error: function(xhr, ajaxOptions, thrownError) {
              if (xhr.responseText) {
              }
            }
          });
        });
      </script>
    </div>
    <?php } ?>
    <?php /* // module events ends */ ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
        <div class="pull-right">
          <select name="store_id" onchange="location=this.value" class="form-control">
            <?php foreach ($stores as $store) { ?>
              <option value="<?php echo $store['href']; ?>" <?php if ($store['store_id'] == $store_id) { ?>selected="selected"<?php } ?>><?php echo $store['name']; ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-mpsitemapfeed" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-product" data-toggle="tab"><?php echo $tab_product; ?></a></li>
            <li><a href="#tab-category" data-toggle="tab"><?php echo $tab_category; ?></a></li>
            <li><a href="#tab-manufacturer" data-toggle="tab"><?php echo $tab_manufacturer; ?></a></li>
            <li><a href="#tab-information" data-toggle="tab"><?php echo $tab_information; ?></a></li>
            <li><a href="#tab-custom_page" data-toggle="tab"><?php echo $tab_custom_page; ?></a></li>
            <li <?php if(!$j3_active) { ?>class="hide"<?php } ?>><a href="#tab-j3-blogpost" data-toggle="tab"><?php echo $tab_j3_blogpost; ?></a></li>
            <li <?php if(!$j3_active) { ?>class="hide"<?php } ?>><a href="#tab-j3-category" data-toggle="tab"><?php echo $tab_j3_category; ?></a></li>
            <li><a href="#tab-support" data-toggle="tab"><?php echo $tab_support; ?></a></li>
          </ul>
          <div class="tab-content">
            <input type="hidden" name="stay_here" id="stay_here" value="0">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group mp-buttons">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-5">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_status) ? 'active' : '';  ?>">
                      <input type="radio" name="module_mpsitemapfeed_status" value="1" <?php echo (!empty($module_mpsitemapfeed_status)) ? 'checked="checked"' : '';  ?> />
                      <?php echo $text_enabled; ?>
                    </label>
                    <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_status) ? 'active' : '';  ?>">
                      <input type="radio" name="module_mpsitemapfeed_status" value="0" <?php echo (empty($module_mpsitemapfeed_status)) ? 'checked="checked"' : '';  ?> />
                      <?php echo $text_disabled; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-url"><?php echo $entry_url; ?></label>
                <div class="col-sm-5">
                  <div class="help"><?php echo $help_url; ?></div>
                  <div id="input-url" class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <td><?php echo $column_store_name; ?></td>
                          <td><?php echo $column_url; ?></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($stores as $store) { ?>
                        <tr>
                          <td><?php echo $store['name']; ?></td>
                          <td><a href="<?php echo $store['sitemap_url']; ?>" target="_blank"><?php echo $store['sitemap_url']; ?></a> <br/><?php echo $store['sitemap_url']; ?> </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_limit; ?></label>
                <div class="col-sm-5">
                  <input type="text" id="input-limit" name="module_mpsitemapfeed_limit" class="form-control" placeholder="<?php echo $entry_limit; ?>" value="<?php echo $module_mpsitemapfeed_limit; ?>">
                  <div class="help"><?php echo $help_limit; ?></div>
                  <?php if ($error_limit) { ?>
                  <div class="text-danger"><?php echo $error_limit; ?></div>
                  <?php } ?>
                </div>
              </div>
              <fieldset>
                  <legend><?php echo $tab_image; ?></legend>
                  <div class="form-group mp-buttons">
                    <label class="col-sm-2 control-label" for="input-image-status"><?php echo $entry_status; ?></label>
                    <div class="col-sm-5">
                      <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_image_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_image_status" value="1" <?php echo (!empty($module_mpsitemapfeed_image_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_enabled; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_image_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_image_status" value="0" <?php echo (empty($module_mpsitemapfeed_image_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_disabled; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_image_status; ?></div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-resize-image"><?php echo $entry_resize_image; ?></label>
                    <div class="col-sm-5 mp-buttons">
                      <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_resize_image) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_resize_image" value="1" <?php echo (!empty($module_mpsitemapfeed_resize_image)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_resize_image) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_resize_image" value="0" <?php echo (empty($module_mpsitemapfeed_resize_image)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_resize_image; ?></div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-image_width"><?php echo $entry_image_width; ?></label>
                    <div class="col-sm-5">
                      <input type="text" name="module_mpsitemapfeed_image_width" id="input-image_width" class="form-control" placeholder="<?php echo $entry_image_width; ?>" value="<?php echo $module_mpsitemapfeed_image_width; ?>">
                      <div class="help"><?php echo $help_width; ?></div>
                      <?php if ($error_width) { ?>
                      <div class="text-danger"><?php echo $error_width; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-image_height"><?php echo $entry_image_height; ?></label>
                    <div class="col-sm-5">
                      <input type="text" name="module_mpsitemapfeed_image_height" id="input-image_height" class="form-control" placeholder="<?php echo $entry_image_height; ?>" value="<?php echo $module_mpsitemapfeed_image_height; ?>">
                      <div class="help"><?php echo $help_height; ?></div>
                      <?php if ($error_height) { ?>
                      <div class="text-danger"><?php echo $error_height; ?></div>
                      <?php } ?>
                    </div>
                  </div>
              </fieldset>
            </div>
            <div class="tab-pane" id="tab-product">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_product_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_product_status" value="1" <?php echo (!empty($module_mpsitemapfeed_product_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_product_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_product_status" value="0" <?php echo (empty($module_mpsitemapfeed_product_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                    <div class="help"><?php echo $help_product_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-product-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_product_multilangurl" id="input-product-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_product_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-product_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_product_frequency" id="input-product_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_product_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-product_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_product_priority" id="input-product_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_product_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-product"><span data-toggle="tooltip" title="<?php echo $help_product; ?>"><?php echo $entry_product; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="product" value="" placeholder="<?php echo $entry_product; ?>" id="input-product" class="form-control" />
                    <div id="product-product" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($products as $product) { ?>
                      <div id="product-product<?php echo $product['product_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $product['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_product_ids[]" value="<?php echo $product['product_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#product-product div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_product; ?></div>
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-category">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-category-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_category_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_category_status" value="1" <?php echo (!empty($module_mpsitemapfeed_category_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_category_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_category_status" value="0" <?php echo (empty($module_mpsitemapfeed_category_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_category_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-category-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_category_multilangurl" id="input-category-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_category_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-category_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_category_frequency" id="input-category_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_category_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-category_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_category_priority" id="input-category_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_category_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
                    <div id="category-category" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($categories as $category) { ?>
                      <div id="category-category<?php echo $category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $category['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_category_ids[]" value="<?php echo $category['category_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#category-category div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_category; ?></div>
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-manufacturer">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-manufacturer-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_manufacturer_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_manufacturer_status" value="1" <?php echo (!empty($module_mpsitemapfeed_manufacturer_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_manufacturer_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_manufacturer_status" value="0" <?php echo (empty($module_mpsitemapfeed_manufacturer_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_manufacturer_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-manufacturer-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_manufacturer_multilangurl" id="input-manufacturer-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_manufacturer_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-manufacturer_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_manufacturer_frequency" id="input-category_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_manufacturer_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-manufacturer_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_manufacturer_priority" id="input-manufacturer_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_manufacturer_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-manufacturer"><span data-toggle="tooltip" title="<?php echo $help_manufacturer; ?>"><?php echo $entry_manufacturer; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="manufacturer" value="" placeholder="<?php echo $entry_manufacturer; ?>" id="input-manufacturer" class="form-control" />
                    <div id="manufacturer-manufacturer" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($manufacturers as $manufacturer) { ?>
                      <div id="manufacturer-manufacturer<?php echo $manufacturer['manufacturer_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $manufacturer['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_manufacturer_ids[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#manufacturer-manufacturer div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_manufacturer; ?></div>
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-information">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-information-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_information_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_information_status" value="1" <?php echo (!empty($module_mpsitemapfeed_information_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_information_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_information_status" value="0" <?php echo (empty($module_mpsitemapfeed_information_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_information_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-information-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_information_multilangurl" id="input-information-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_information_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-information_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_information_frequency" id="input-information_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_information_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-information_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_information_priority" id="input-information_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_information_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-information"><span data-toggle="tooltip" title="<?php echo $help_information; ?>"><?php echo $entry_information; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="information" value="" placeholder="<?php echo $entry_information; ?>" id="input-information" class="form-control" />
                    <div id="information-information" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($informations as $information) { ?>
                      <div id="information-information<?php echo $information['information_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $information['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_information_ids[]" value="<?php echo $information['information_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#information-information div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_information; ?></div>
                  </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-custom_page">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-5 mp-buttons">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                      <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_custom_link_status) ? 'active' : '';  ?>">
                        <input type="radio" name="module_mpsitemapfeed_custom_link_status" value="1" <?php echo (!empty($module_mpsitemapfeed_custom_link_status)) ? 'checked="checked"' : '';  ?> />
                        <?php echo $text_yes; ?>
                      </label>
                      <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_custom_link_status) ? 'active' : '';  ?>">
                        <input type="radio" name="module_mpsitemapfeed_custom_link_status" value="0" <?php echo (empty($module_mpsitemapfeed_custom_link_status)) ? 'checked="checked"' : '';  ?> />
                        <?php echo $text_no; ?>
                      </label>
                    </div>
                    <div class="help"><?php echo $help_custom_link_status; ?></div>
                </div>
              </div>
              <fieldset>
                  <legend><?php echo $tab_links; ?></legend>
                  <div class="table-responsive">
                    <table id="custom_links" class="table table-striped table-bordered table-hover">
                      <thead>
                        <tr>
                          <td class="text-left" style="width:50%;"><?php echo $entry_link; ?>
                          <div class="help"><?php echo $help_link; ?></div>
                          </td>
                          <td class="text-left"><?php echo $entry_frequency; ?>
                          <div class="help"><?php echo $help_frequency; ?></div>
                          </td>
                          <td class="text-left"><?php echo $entry_priority; ?>
                          <div class="help"><?php echo $help_priority; ?></div>
                          </td>
                          <td class="text-left"><?php echo $entry_sort_order; ?>
                          <div class="help"><?php echo $help_sort_order; ?></div>
                          </td>
                          <td class="text-left"><?php echo $entry_status; ?>
                          <div class="help"><?php echo $help_custom_links_status; ?></div>
                          </td>
                          <td></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $custom_link_row = 0; ?>
                        <?php foreach ($module_mpsitemapfeed_custom_links as $module_mpsitemapfeed_custom_link) { ?>
                        <tr id="custom_link-row<?php echo $custom_link_row; ?>">
                          <td class="text-left">
                            <?php foreach($languages as $language) { ?>
                                <div class="input-group">
                                    <span class="input-group-addon"><img src="<?php echo $language['lang_flag']; ?>" title="<?php echo $language['name']; ?>" /></span>
                                    <input type="text" name="module_mpsitemapfeed_custom_link[<?php echo $custom_link_row; ?>][url][<?php echo $language['language_id']; ?>]" value="<?php echo isset($module_mpsitemapfeed_custom_link['url'][$language['language_id']]) ? $module_mpsitemapfeed_custom_link['url'][$language['language_id']] : ''; ?>" placeholder="<?php echo $entry_link; ?>" class="form-control" />
                                </div>
                            <?php } ?>
                            </td>
                          <td class="text-left">
                            <select name="module_mpsitemapfeed_custom_link[<?php echo $custom_link_row; ?>][frequency]" id="input-category_frequency<?php echo $custom_link_row; ?>" class="form-control">
                                <?php foreach($frequencies as $frequency) { ?>
                                  <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_custom_link['frequency']) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                                <?php } ?>
                           </select>
                          </td>
                         <td class="text-left">
                          <select name="module_mpsitemapfeed_custom_link[<?php echo $custom_link_row; ?>][priority]" id="input-category_priority<?php echo $custom_link_row; ?>" class="form-control">
                                <?php foreach($priorities as $priority) { ?>
                                  <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_custom_link['priority']) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                                <?php } ?>
                             </select>
                          </td>
                          <td class="text-left"><input type="text" name="module_mpsitemapfeed_custom_link[<?php echo $custom_link_row; ?>][sort_order]" value="<?php echo $module_mpsitemapfeed_custom_link['sort_order']; ?>" placeholder="<?php echo $entry_sort_order; ?>" class="form-control" /></td>
                          <td class="text-left"><select name="module_mpsitemapfeed_custom_link[<?php echo $custom_link_row; ?>][status]" class="form-control"><option value="1"><?php echo $text_enabled; ?></option><option value="0"><?php echo $text_disabled; ?></option></select></td>
                          <td class="text-left"><button type="button" onclick="$('#custom_link-row<?php echo $custom_link_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $custom_link_row++; ?>
                        <?php } ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="5"></td>
                          <td class="text-left"><button type="button" onclick="addCustomLink();" data-toggle="tooltip" title="<?php echo $button_link_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
            </fieldset>
            </div>
            <div class="tab-pane <?php if(!$j3_active) { ?>hide<?php } ?>" id="tab-j3-blogpost">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_j3_blogpost_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_j3_blogpost_status" value="1" <?php echo (!empty($module_mpsitemapfeed_j3_blogpost_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_j3_blogpost_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_j3_blogpost_status" value="0" <?php echo (empty($module_mpsitemapfeed_j3_blogpost_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_blogpost_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-j3-blogpost-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogpost_multilangurl" id="input-j3-blogpost-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_j3_blogpost_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogpost_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogpost_frequency" id="input-blogpost_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_j3_blogpost_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogpost_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogpost_priority" id="input-blogpost_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_j3_blogpost_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogpost"><span data-toggle="tooltip" title="<?php echo $help_blogpost; ?>"><?php echo $entry_blogpost; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="blogpost" value="" placeholder="<?php echo $entry_blogpost; ?>" id="input-blogpost" class="form-control" />
                    <div id="blogpost-blogpost" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($blogposts as $blogpost) { ?>
                      <div id="blogpost-blogpost<?php echo $blogpost['post_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $blogpost['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_j3_blogpost_ids[]" value="<?php echo $blogpost['post_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#blogpost-blogpost div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_blogpost; ?></div>
                  </div>
                </div>
              </div>
              <div class="tab-pane <?php if(!$j3_active) { ?>hide<?php } ?>" id="tab-j3-category">
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogcategory-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-5 mp-buttons">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo !empty($module_mpsitemapfeed_j3_blogcategory_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_j3_blogcategory_status" value="1" <?php echo (!empty($module_mpsitemapfeed_j3_blogcategory_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_yes; ?>
                        </label>
                        <label class="btn btn-primary <?php echo empty($module_mpsitemapfeed_j3_blogcategory_status) ? 'active' : '';  ?>">
                          <input type="radio" name="module_mpsitemapfeed_j3_blogcategory_status" value="0" <?php echo (empty($module_mpsitemapfeed_j3_blogcategory_status)) ? 'checked="checked"' : '';  ?> />
                          <?php echo $text_no; ?>
                        </label>
                      </div>
                      <div class="help"><?php echo $help_blogcategory_status; ?></div>
                  </div>
                </div>
                <div class="form-group <?php if (!$is_multilingual) { ?>hide<?php } ?>">
                  <label class="col-sm-2 control-label" for="input-j3-category-multilangurl"><?php echo $entry_multilangurl; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogcategory_multilangurl" id="input-j3-category-multilangurl" class="form-control">
                      <?php if ($module_mpsitemapfeed_j3_blogcategory_multilangurl) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_multilangurl; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogcategory_frequency"><?php echo $entry_frequency; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogcategory_frequency" id="input-blogcategory_frequency" class="form-control">
                      <?php foreach($frequencies as $frequency) { ?>
                          <option value="<?php echo $frequency['key']; ?>" <?php if($frequency['key'] == $module_mpsitemapfeed_j3_blogcategory_frequency) { ?>selected="selected"<?php } ?>><?php echo $frequency['text']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_frequency; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogcategory_priority"><?php echo $entry_priority; ?></label>
                  <div class="col-sm-5">
                    <select name="module_mpsitemapfeed_j3_blogcategory_priority" id="input-blogcategory_priority" class="form-control">
                      <?php foreach($priorities as $priority) { ?>
                          <option value="<?php echo $priority; ?>" <?php if($priority == $module_mpsitemapfeed_j3_blogcategory_priority) { ?>selected="selected"<?php } ?>><?php echo $priority; ?></option>
                      <?php } ?>
                    </select>
                    <div class="help"><?php echo $help_priority; ?></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-blogcategory"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
                  <div class="col-sm-5">
                    <input type="text" name="blogcategory" value="" placeholder="<?php echo $entry_category; ?>" id="input-blogcategory" class="form-control" />
                    <div id="blogcategory-blogcategory" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($blogcategories as $blogcategory) { ?>
                      <div id="blogcategory-blogcategory<?php echo $blogcategory['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $blogcategory['name']; ?>
                        <input type="hidden" name="module_mpsitemapfeed_j3_blogcategory_ids[]" value="<?php echo $blogcategory['category_id']; ?>" />
                      </div>
                      <?php } ?>
                    </div>
                    <a onclick="$('#blogcategory-blogcategory div').remove();"><?php echo $text_remove_all; ?></a>
                    <div class="help"><?php echo $help_select_blogcategory; ?></div>
                  </div>
                </div>
              </div>
            <div class="tab-pane" id="tab-support">
              <div class="bs-callout bs-callout-info">
                <h4>ModulePoints <?php echo $heading_title; ?></h4>
                <center><strong><?php echo $heading_title; ?> </strong></center> <br/>
              </div>
              <fieldset>
                <div class="form-group">
                  <div class="col-md-12 col-xs-12">
                    <h4 class="text-mpsuccess text-center"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Thanks For Choosing Our Extension</h4>
                    <h4 class="text-mpsuccess text-center"><i class="fa fa-phone" aria-hidden="true"></i>Kindly Write Us At Support Email For Support</h4>
                    <ul class="list-group">
                      <li class="list-group-item clearfix">support@modulepoints.com <span class="badge"><a href="mailto:support@modulepoints.com?Subject=Request Support: <?php echo $heading_title; ?> Extension"><i class="fa fa-envelope"></i> Contact Us</a></span></li>
                    </ul>
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <style type="text/css">
  .inliner .inline { float: left; margin-bottom: 15px; }
  .inliner .inline.full {float:  none; margin-bottom: 15px; }
  .inliner .inline .control-label { padding: 0; }
  .inline .control-label { text-align: left; }
  </style>
  <script><!--
  $('.mtabs').each(function() {
    $(this).find('a:first').tab('show')
  });

  $('select[name="module_mpsitemapfeed_match_products"]').on('change', function() {
    $('.matching_to').hide('');
    $('.matching_to.'+this.value).show('');
    // console.log('.matching_to.'+this.value);
  });

  $('select[name="module_mpsitemapfeed_carousel"]').on('change', function() {
    console.log(this.value == "1");
    if (this.value == "1") {
      $('.carousel_config').show('');
    } else {
      $('.carousel_config').hide('');
    }
  });

  // $('select[name="module_mpsitemapfeed_match_products"]').trigger('change');


  //--></script>
  <script type="text/javascript"><!--
// Manufacturer
$('input[name=\'manufacturer\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/manufacturer/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['manufacturer_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'manufacturer\']').val('');

    $('#manufacturer-manufacturer' + item['value']).remove();

    $('#manufacturer-manufacturer').append('<div id="manufacturer-manufacturer' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_manufacturer_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#manufacturer-manufacturer').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});

// Category
$('input[name=\'category\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/category/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['category_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'category\']').val('');

    $('#category-category' + item['value']).remove();

    $('#category-category').append('<div id="category-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_category_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#category-category').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});

// Information
$('input[name=\'information\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=extension/module/mpsitemapfeed/informationAutocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['information_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'information\']').val('');

    $('#information-information' + item['value']).remove();

    $('#information-information').append('<div id="information-information' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_information_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#information-information').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});
//--></script>
<script type="text/javascript"><!--

// Product
$('input[name=\'product\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/product/autocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
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
  'select': function(item) {
    $('input[name=\'product\']').val('');

    $('#product-product' + item['value']).remove();

    $('#product-product').append('<div id="product-product' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_product_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#product-product').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});
//--></script>
<script type="text/javascript"><!--
// Blog post
$('input[name=\'blogpost\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=extension/module/mpsitemapfeed/blogpostAutocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['post_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'blogpost\']').val('');

    $('#blogpost-blogpost' + item['value']).remove();

    $('#blogpost-blogpost').append('<div id="blogpost-blogpost' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_j3_blogpost_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#blogpost-blogpost').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});

// Blog category
$('input[name=\'blogcategory\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=extension/module/mpsitemapfeed/blogcategoryAutocomplete&<?php echo $get_token; ?>=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['category_id']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('input[name=\'blogcategory\']').val('');

    $('#blogcategory-blogcategory' + item['value']).remove();

    $('#blogcategory-blogcategory').append('<div id="blogcategory-blogcategory' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="module_mpsitemapfeed_j3_blogcategory_ids[]" value="' + item['value'] + '" /></div>');
  }
});

$('#blogcategory-blogcategory').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});
//--></script>
<script type="text/javascript"><!--
var custom_link_row = <?php echo $custom_link_row; ?>;

function addCustomLink() {
  html  = '<tr id="custom_link-row' + custom_link_row + '">';
  html += '  <td class="text-left">';
  <?php foreach($languages as $language) { ?>
  html += '    <div class="input-group">';
  html += '        <span class="input-group-addon"><img src="<?php echo $language['lang_flag']; ?>" title="<?php echo $language['name']; ?>" /></span>';
  html += '        <input type="text" name="module_mpsitemapfeed_custom_link[' + custom_link_row + '][url][<?php echo $language['language_id']; ?>]" value="" placeholder="<?php echo $entry_link; ?>" class="form-control" />';
  html += '    </div>';
  <?php } ?>
  html += '</td>';
  html += '  <td class="text-left">';
  html += '<select name="module_mpsitemapfeed_custom_link[' + custom_link_row + '][frequency]" id="input-category_frequency" class="form-control">';
        <?php foreach($frequencies as $frequency) { ?>
  html += ' <option value="<?php echo $frequency['key']; ?>"><?php echo $frequency['text']; ?></option>';
        <?php } ?>
  html += '    </select>';
  html += '</td>';
  html += '  <td class="text-left">';
  html += '<select name="module_mpsitemapfeed_custom_link[' + custom_link_row + '][priority]" id="input-category_priority" class="form-control">';
        <?php foreach($priorities as $priority) { ?>
  html += ' <option value="<?php echo $priority; ?>"><?php echo $priority; ?></option>';
        <?php } ?>
  html += '    </select>';
  html += '</td>';
  html += '  <td class="text-left"><input type="text" name="module_mpsitemapfeed_custom_link[' + custom_link_row + '][sort_order]" value="" placeholder="<?php echo $entry_sort_order; ?>" class="form-control" /></td>';
  html += '  <td class="text-left"><select name="module_mpsitemapfeed_custom_link[' + custom_link_row + '][status]" class="form-control"><option value="1"><?php echo $text_enabled; ?></option><option value="0"><?php echo $text_disabled; ?></option></select></td>';
  html += '  <td class="text-left"><button type="button" onclick="$(\'#custom_link-row' + custom_link_row  + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
  html += '</tr>';

  $('#custom_links tbody').append(html);

  custom_link_row++;
}
//--></script>
</div>
<?php echo $footer; ?>