<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ciwhatsapp" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-check-circle"></i> <?php echo $button_save; ?></button>
        <a href="<?php echo $extensions; ?>" data-toggle="tooltip" title="<?php echo $button_extensions; ?>" class="btn btn-danger"><i class="fa fa-puzzle-piece"></i></a>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
      </div>
      <h2><?php echo $heading_title; ?></h2>
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

    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

    <?php if($action_enable_events) { ?>
    <div class="alert alert-warning inspect-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $info_disabled_events; ?> <button type="button" class="btn btn-primary btn-sm button-enable-event" onclick="enableEvents();"><i class="fa fa-cog"></i> <?php echo $button_enable_event; ?></button></div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-whatsapp"></i><?php echo $text_edit; ?></h3>
        <div class="pull-right">
          <select name="store_id" onchange="window.location = 'index.php?route=extension/module/ciwhatsapp_setting&<?php echo $module_token; ?>=<?php echo $ci_token; ?>&store_id='+ this.value;">
            <?php foreach ($stores as $store) { ?>
              <option value="<?php echo $store['store_id']; ?>" <?php echo $store['store_id'] == $store_id ? 'selected="selected"' : ''; ?>><?php echo $store['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-ciwhatsapp" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-control" data-toggle="tab"><i class="fa fa-cog"></i> <?php echo $tab_control; ?></a></li>
            <li><a href="#tab-language" data-toggle="tab"><i class="fa fa-language"></i> <?php echo $tab_language; ?></a></li>
            <li><a href="#tab-member" data-toggle="tab"><i class="fa fa-user"></i> <?php echo $tab_supporting_member; ?></a></li>
            <li><a href="#tab-design" data-toggle="tab"><i class="fa fa-eye-slash"></i> <?php echo $tab_design; ?></a></li>
            <li><a href="#tab-support" data-toggle="tab"><i class="fa fa-life-ring" aria-hidden="true"></i> <?php echo $tab_support; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-control">
              <fieldset>
                <legend class="legend"><i class="fa fa-puzzle-piece"></i> <?php echo $legend_module_setting; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_status ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_status" <?php echo $ciwhatsapp_setting_status ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_enabled; ?>
                      </label>
                      <label class="btn btn-default <?php echo !$ciwhatsapp_setting_status ? 'active' : ''; ?>">
                        <input name="ciwhatsapp_setting_status" <?php echo !$ciwhatsapp_setting_status ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"><?php echo $text_disabled; ?>
                      </label>
                    </div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <legend class="legend legend_margin"><i class="fa fa-clock-o"></i> <?php echo $legend_timezone; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_timezone; ?></label>
                  <div class="col-sm-5">
                    <select name="ciwhatsapp_setting_timezone" class="form-control">
                      <?php foreach($timezones as $timezone) { ?>
                        <?php if($timezone['value'] == $ciwhatsapp_setting_timezone) { ?>
                          <option value="<?php echo $timezone['value']; ?>" selected="selected"><?php echo $timezone['text']; ?></option>
                        <?php } else { ?>
                          <option value="<?php echo $timezone['value']; ?>"><?php echo $timezone['text']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <legend class="legend legend_margin"><i class="fa fa-download"></i> <?php echo $legend_widget_bottom; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_device; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo in_array('desktop', $ciwhatsapp_setting_device) ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_device[]" <?php echo in_array('desktop', $ciwhatsapp_setting_device) ? 'checked="checked"' : ''; ?> autocomplete="off" value="desktop" type="checkbox"><?php echo $text_desktop; ?>
                      </label>
                      <label class="btn btn-default <?php echo in_array('mobile', $ciwhatsapp_setting_device) ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_device[]" <?php echo in_array('mobile', $ciwhatsapp_setting_device) ? 'checked="checked"' : ''; ?> autocomplete="off" value="mobile" type="checkbox"><?php echo $text_mobile; ?>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_shape; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_shape == 'square' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_shape" <?php echo $ciwhatsapp_setting_shape == 'square' ? 'checked="checked"' : ''; ?> autocomplete="off" value="square" type="radio"><?php echo $text_square; ?>
                      </label>
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_shape == 'round' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_shape" <?php echo $ciwhatsapp_setting_shape == 'round' ? 'checked="checked"' : ''; ?> autocomplete="off" value="round" type="radio"><?php echo $text_round; ?>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_position; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_position == 'left_side' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_position" <?php echo $ciwhatsapp_setting_position == 'left_side' ? 'checked="checked"' : ''; ?> autocomplete="off" value="left_side" type="radio"><?php echo $text_left_side; ?>
                      </label>
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_position == 'right_side' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_position" <?php echo $ciwhatsapp_setting_position == 'right_side' ? 'checked="checked"' : ''; ?> autocomplete="off" value="right_side" type="radio"><?php echo $text_right_side; ?>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_layout; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_layout == 'list_work' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_layout" <?php echo $ciwhatsapp_setting_layout == 'list_work' ? 'checked="checked"' : ''; ?> autocomplete="off" value="list_work" type="radio"><?php echo $text_list_work; ?>
                      </label>
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_layout == 'grid_work' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_layout" <?php echo $ciwhatsapp_setting_layout == 'grid_work' ? 'checked="checked"' : ''; ?> autocomplete="off" value="grid_work" type="radio"><?php echo $text_grid_work; ?>
                      </label>
                    </div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <legend class="legend legend_margin"><i class="fa fa-list"></i> <?php echo $legend_widget_detailpage; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_device; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo in_array('desktop', $ciwhatsapp_setting_detailpage_device) ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_detailpage_device[]" <?php echo in_array('desktop', $ciwhatsapp_setting_detailpage_device) ? 'checked="checked"' : ''; ?> autocomplete="off" value="desktop" type="checkbox"><?php echo $text_desktop; ?>
                      </label>
                      <label class="btn btn-default <?php echo in_array('mobile', $ciwhatsapp_setting_detailpage_device) ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_detailpage_device[]" <?php echo in_array('mobile', $ciwhatsapp_setting_detailpage_device) ? 'checked="checked"' : ''; ?> autocomplete="off" value="mobile" type="checkbox"><?php echo $text_mobile; ?>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_product_layout; ?></label>
                  <div class="col-sm-10">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_detailpage_layout == 'single_line_layout' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_detailpage_layout" <?php echo $ciwhatsapp_setting_detailpage_layout == 'single_line_layout' ? 'checked="checked"' : ''; ?> autocomplete="off" value="single_line_layout" type="radio"><?php echo $text_single_line_layout; ?>
                      </label>
                      <label class="btn btn-default <?php echo $ciwhatsapp_setting_detailpage_layout == 'multi_line_layout' ? 'active' : ''; ?> ">
                        <input name="ciwhatsapp_setting_detailpage_layout" <?php echo $ciwhatsapp_setting_detailpage_layout == 'multi_line_layout' ? 'checked="checked"' : ''; ?> autocomplete="off" value="multi_line_layout" type="radio"><?php echo $text_multi_line_layout; ?>
                      </label>
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="tab-pane" id="tab-language">
              <fieldset>
                <legend class="legend"><i class="fa fa-language"></i> <?php echo $legend_language; ?></legend>
                <ul class="nav nav-tabs" id="language">
                  <?php foreach ($languages as $language) { ?>
                  <li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                  <?php } ?>
                </ul>
                <div class="tab-content">
                  <?php foreach ($languages as $language) { ?>
                  <div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
                    <div class="form-group required">
                      <label class="col-sm-2 control-label"><?php echo $entry_title; ?></label>
                      <div class="col-sm-10">
                        <input type="text" name="ciwhatsapp_setting_description[<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($ciwhatsapp_setting_description[$language['language_id']]['title']) ? $ciwhatsapp_setting_description[$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label"><?php echo $entry_description; ?></label>
                      <div class="col-sm-10">
                        <textarea name="ciwhatsapp_setting_description[<?php echo $language['language_id']; ?>][description]" placeholder="<?php echo $entry_description; ?>" id="input-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($ciwhatsapp_setting_description[$language['language_id']]) ? $ciwhatsapp_setting_description[$language['language_id']]['description'] : ''; ?></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label"><?php echo $entry_button_text; ?></label>
                      <div class="col-sm-10">
                        <input type="text" name="ciwhatsapp_setting_description[<?php echo $language['language_id']; ?>][button_text]" value="<?php echo isset($ciwhatsapp_setting_description[$language['language_id']]['button_text']) ? $ciwhatsapp_setting_description[$language['language_id']]['button_text'] : ''; ?>" placeholder="<?php echo $entry_button_text; ?>" class="form-control" />
                      </div>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="tab-pane" id="tab-design">
              <fieldset>
                <legend class="legend"><i class="fa fa-paint-brush"></i> <?php echo $legend_color; ?></legend>
                <div class="form-group">
                  <label class="col-sm-3 control-label"><?php echo $entry_theme_background; ?></label>
                  <div class="col-sm-3">
                    <input type="text" name="ciwhatsapp_setting_color[theme_background]" value="<?php echo !empty($ciwhatsapp_setting_color['theme_background']) ? $ciwhatsapp_setting_color['theme_background'] : '#03b948'; ?>" class="form-control color-picker" />
                  </div>
                  <div class="col-sm-2">
                    <div class="preview"></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label"><?php echo $entry_theme_font; ?></label>
                  <div class="col-sm-3">
                    <input type="text" name="ciwhatsapp_setting_color[theme_font]" value="<?php echo !empty($ciwhatsapp_setting_color['theme_font']) ? $ciwhatsapp_setting_color['theme_font'] : '#ffffff'; ?>" class="form-control color-picker" />
                  </div>
                  <div class="col-sm-2">
                    <div class="preview"></div>
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <legend class="legend"><i class="fa fa-desktop"></i> <?php echo $legend_css; ?></legend>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_css; ?></label>
                  <div class="col-sm-5">
                    <textarea name="ciwhatsapp_setting_css" rows="10" class="form-control"><?php echo $ciwhatsapp_setting_css; ?></textarea>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="tab-pane" id="tab-member">
              <div class="row">
                <div class="col-sm-4 col-xs-12 col-md-3">
                  <ul class="nav nav-pills nav-stacked" id="member">
                  <?php $member_row = 0; ?>
                    <?php foreach($members as $member) { ?>
                    <li class="member-li"><a href="#tab-member<?php echo $member_row; ?>" data-toggle="tab"><i class="fa fa-minus-circle" onclick="$('a[href=\'#tab-member<?php echo $member_row; ?>\']').parent().remove(); $('#tab-member<?php echo $member_row; ?>').remove(); $('#member a:first').tab('show');"></i> <?php echo (!empty($member['member_name']) ? $member['member_name'] : $tab_member .'-' . ($member_row + (int)1)); ?> <i class="fa fa-arrows pull-right" aria-hidden="true"></i></a></li>
                    <?php $member_row++; ?>
                    <?php } ?>
                  </ul>
                  <ul class="nav nav-pills nav-stacked addmember-button">
                    <li><button type="button" class="btn btn-default btn-block" onclick="addMember();"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php echo $button_add_member; ?></button></li>
                  </ul>
                </div>
                <div class="col-sm-8 col-xs-12 col-md-9">
                  <div class="tab-content" id="tab-content">
                    <?php $member_row = 0; ?>
                    <?php foreach($members as $key => $member) { ?>
                    <div class="tab-pane" id="tab-member<?php echo $member_row; ?>">
                      <div class="member-info" id="member-info-<?php echo $member_row; ?>">
                        <fieldset>
                          <legend class="legend_margin"><i class="fa fa-info-circle"></i> <?php echo $legend_member; ?></legend>
                          <div class="form-group required">
                            <label class="control-label"><?php echo $entry_member_name; ?></label>
                              <input type="hidden" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][member_id]" value="<?php echo $member['member_id']; ?>" class="form-control" />
                              <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][member_name]" value="<?php echo $member['member_name']; ?>" class="form-control" />
                              <?php if (!empty($error_member_name[$key])) { ?>
                              <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_member_name[$key]; ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                              </div>
                              <?php } ?>
                          </div>
                          <div class="form-group required">
                            <label class="control-label"><?php echo $entry_member_number; ?></label>
                              <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][member_number]" value="<?php echo $member['member_number']; ?>" class="form-control" />
                              <?php if (!empty($error_member_number[$key])) { ?>
                              <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_member_number[$key]; ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                              </div>
                              <?php } ?>
                          </div>
                          <div class="form-group hide">
                            <label class="control-label"><?php echo $entry_sort_order; ?></label>
                              <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][sort_order]" value="<?php echo $member['sort_order']; ?>" class="form-control member-sortorder" />
                          </div>
                          <div class="form-group">
                            <label class="control-label"><?php echo $entry_photo; ?></label>
                            <div class=""><a href="" id="thumb-photo<?php echo $member_row; ?>" data-toggle="image" class="img-thumbnail img-circle"><img src="<?php echo $member['photo_thumb']; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
                              <input type="hidden" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][photo]" value="<?php echo $member['photo']; ?>" id="input-photo<?php echo $member_row; ?>" />
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><?php echo $entry_department_name; ?></label>
                            <div class="">
                              <?php foreach ($languages as $language) { ?>
                              <div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>
                                <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][description][<?php echo $language['language_id']; ?>][department_name]" value="<?php echo isset($member['description'][$language['language_id']]['department_name']) ? $member['description'][$language['language_id']]['department_name'] : ''; ?>" placeholder="<?php echo $entry_department_name; ?>" class="form-control" />
                              </div>
                              <?php } ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><?php echo $entry_greeting_message; ?></label>
                            <div class="">
                              <?php foreach ($languages as $language) { ?>
                              <div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>
                                <textarea rows="12" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][description][<?php echo $language['language_id']; ?>][greeting_message]" class="form-control"><?php echo isset($member['description'][$language['language_id']]['greeting_message']) ? $member['description'][$language['language_id']]['greeting_message'] : ''; ?></textarea>
                              </div>
                              <?php } ?>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <legend class="legend_margin"><i class="fa fa-clock-o"></i> <?php echo $legend_availability; ?></legend>
                          <div class="form-group">
                            <label class="control-label"><?php echo $entry_member_status; ?></label>
                            <div class="">
                              <div class="btn-group grou_member_status_<?php echo $member_row; ?>" data-toggle="buttons" data-row="<?php echo $member_row; ?>">
                                <label class="btn btn-default member_status <?php echo $member['status'] == 'online' ? 'active' : ''; ?>">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][status]" <?php echo $member['status'] == 'online' ? 'checked="checked"' : ''; ?> autocomplete="off" value="online" type="radio"><?php echo $text_online; ?>
                                </label>
                                <label class="btn btn-default member_status <?php echo $member['status'] == 'online_schedule' ? 'active' : ''; ?> ">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][status]" <?php echo $member['status'] == 'online_schedule' ? 'checked="checked"' : ''; ?> autocomplete="off" value="online_schedule" type="radio"><?php echo $text_online_schedule; ?>
                                </label>
                                <label class="btn btn-default member_status <?php echo $member['status'] == 'offline' ? 'active' : ''; ?> ">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][status]" <?php echo $member['status'] == 'offline' ? 'checked="checked"' : ''; ?> autocomplete="off" value="offline" type="radio"><?php echo $text_offline; ?>
                                </label>
                                <label class="btn btn-default member_status <?php echo $member['status'] == 'hide' ? 'active' : ''; ?> ">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][status]" <?php echo $member['status'] == 'hide' ? 'checked="checked"' : ''; ?> autocomplete="off" value="hide" type="radio"><?php echo $text_hide; ?>
                                </label>
                              </div>
                            </div>
                          </div>
                          <div class="form-group group_weekdays_<?php echo $member_row; ?> group_weekdays <?php echo $member['status'] == 'online_schedule' ? '' : 'hide'; ?>">
                            <div class="table-responsive">
                              <table class="table table-hover">
                                <thead>
                                  <tr>
                                    <th style="width: 30%"><?php echo $column_status; ?></th>
                                    <th style="width: 30%"><?php echo $column_weekday; ?></th>
                                    <th style="width: 20%"><?php echo $column_start_time; ?></th>
                                    <th style="width: 20%"><?php echo $column_end_time; ?></th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php foreach($weekdays as $key => $weekday) { ?>
                                  <tr>
                                    <td>
                                      <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default btn-sm <?php echo !empty($member['weekday'][$key]['status']) ? 'active' : ''; ?> ">
                                          <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][weekday][<?php echo $key; ?>][status]" <?php echo !empty($member['weekday'][$key]['status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_enabled; ?>
                                        </label>
                                        <label class="btn btn-default btn-sm <?php echo empty($member['weekday'][$key]['status']) ? 'active' : ''; ?> ">
                                          <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][weekday][<?php echo $key; ?>][status]" <?php echo empty($member['weekday'][$key]['status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"><?php echo $text_disabled; ?>
                                        </label>
                                      </div>
                                    </td>
                                    <td class="weekday_name"><b><?php echo $weekday; ?></b></td>
                                    <td>
                                      <div class="input-group time">
                                        <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][weekday][<?php echo $key; ?>][start_time]" value="<?php echo isset($member['weekday'][$key]['start_time']) ? $member['weekday'][$key]['start_time'] : ''; ?>" class="form-control" />
                                        <span class="input-group-btn">
                                          <button type="button" class="btn btn-default"><i class="fa fa-clock-o"></i></button>
                                        </span>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="input-group time">
                                        <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][weekday][<?php echo $key; ?>][end_time]" value="<?php echo isset($member['weekday'][$key]['end_time']) ? $member['weekday'][$key]['end_time'] : ''; ?>" class="form-control" />
                                        <span class="input-group-btn">
                                          <button type="button" class="btn btn-default"><i class="fa fa-clock-o"></i></button>
                                        </span>
                                      </div>
                                    </td>
                                  </tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="group_time_<?php echo $member_row; ?>">
                            <div class="form-group">
                              <label class="control-label">
                                <?php echo $entry_time_text_status; ?>
                                </label>
                              <div class="">
                                <div class="btn-group" data-toggle="buttons">
                                  <label class="btn btn-default <?php echo !empty($member['time_text_status']) ? 'active' : ''; ?> ">
                                    <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][time_text_status]" <?php echo !empty($member['time_text_status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_show; ?>
                                  </label>
                                  <label class="btn btn-default <?php echo empty($member['time_text_status']) ? 'active' : ''; ?> ">
                                    <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][time_text_status]" <?php echo empty($member['time_text_status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"><?php echo $text_hide; ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label"><?php echo $entry_time_text; ?>
                                <a class="btn btn-info btn-xs time_text_system_value" data-row="<?php echo $member_row; ?>"><i class="fa fa-cog"></i> <?php echo $entry_time_text_system_value; ?></a>
                              </label>
                              <div class="">
                                <?php foreach ($languages as $language) { ?>
                                <div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>
                                  <input type="text" name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][description][<?php echo $language['language_id']; ?>][time_text]" value="<?php echo isset($member['description'][$language['language_id']]['time_text']) ? $member['description'][$language['language_id']]['time_text'] : ''; ?>" placeholder="<?php echo $entry_time_text; ?>" class="form-control group_time_text_<?php echo $member_row; ?>" />
                                </div>
                                <?php } ?>
                              </div>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <legend class="legend_margin"><i class="fa fa-desktop"></i> <?php echo $legend_widget_member; ?></legend>
                          <div class="form-group">
                            <label class="control-label"><?php echo $entry_widget_member; ?></label>
                            <div class="">
                              <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default <?php echo in_array('bottom', $member['page_status']) ? 'active' : ''; ?> ">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][page_status][]" <?php echo in_array('bottom', $member['page_status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="bottom" type="checkbox"><?php echo $text_widget_bottom; ?>
                                </label>
                                <label class="btn btn-default <?php echo in_array('product', $member['page_status']) ? 'active' : ''; ?> group_anotherwidget_<?php echo $member_row; ?> <?php echo $member['status'] == 'hide' || $member['status'] == 'offline' ? 'hide' : ''; ?>">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][page_status][]" <?php echo in_array('product', $member['page_status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="product" type="checkbox"><?php echo $text_widget_product; ?>
                                </label>
                                <label class="btn btn-default <?php echo in_array('layout_pages', $member['page_status']) ? 'active' : ''; ?> group_anotherwidget_<?php echo $member_row; ?> <?php echo $member['status'] == 'hide' || $member['status'] == 'offline' ? 'hide' : ''; ?>">
                                  <input name="ciwhatsapp_setting_member[<?php echo $member_row; ?>][page_status][]" <?php echo in_array('layout_pages', $member['page_status']) ? 'checked="checked"' : ''; ?> autocomplete="off" value="layout_pages" type="checkbox"><?php echo $text_widget_layout_pages; ?>
                                </label>
                              </div>
                            </div>
                          </div>
                        </fieldset>

                      </div>
                    </div>
                    <?php $member_row++; ?>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane text-center" id="tab-support">
              <div class="support-wrap">
                <div class="text-right profile-buttons">
                  <a href="https://www.opencart.com/index.php?route=marketplace/extension&sort=rating&filter_member=CodingInspect" target="_blank" class="btn btn-primary"><i class="fa fa-opencart"></i> More Extensions</a>
                </div>

                <div class="ci-support-icon">
                  <i class="fa fa-life-ring" aria-hidden="true"></i>
                </div>
                <div class="ciinfo">
                  <h4>For any type of support Please contact us at</h4>
                  <h3>codinginspect@gmail.com</h3>
                </div>
              </div>
              <br>
              <br>
              <div class="rating-wrap">
                <div class="text-right rating-buttons">
                  <a href="https://www.opencart.com/index.php?route=account/rating" target="_blank" class="btn btn-primary" data-toggle="tooltip" title=" Rate on Opencart Extension"><i class="fa fa-opencart"></i></a>
                  <a href="https://www.youtube.com/watch?v=5naoyOMyk5w" target="_blank" class="btn btn-danger" data-toggle="tooltip" title=" See Video How to rate an extension"><i class="fa fa-play"></i></a>
                </div>
                <div class="">
                  <h4>Please rate our extension for Opencart Marketplace</h4>
                  <div class="rating">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <i class="fa fa-star" aria-hidden="true"></i>
                  </div>
                  <h3>Opencart.com >> Account >> Rate Your Downloads</h3>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>

<script type="text/javascript"><!--
$('#language a:first').tab('show');

$('#member li:first-child a').tab('show');
//--></script>
<script type="text/javascript"><!--
$('#member a:first').tab('show');
var member_row = <?php echo $member_row; ?>;

function addMember() {
  var html = '';
  html += '<div class="tab-pane" id="tab-member'+ member_row +'">';
    html += '<div class="member-info" id="member-info-'+ member_row +'">';
      html += '<fieldset>';
        html += '<legend class="legend_margin"><i class="fa fa-info-circle"></i> <?php echo $legend_member; ?></legend>';
        html += '<div class="form-group required">';
          html += '<label class="control-label"><?php echo $entry_member_name; ?></label>';
            html += '<input type="hidden" name="ciwhatsapp_setting_member['+ member_row +'][member_id]" value="'+ (member_row + parseInt(1)) + '" class="form-control" />';
            html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][member_name]" value="" class="form-control" />';
        html += '</div>';
        html += '<div class="form-group required">';
          html += '<label class="control-label"><?php echo $entry_member_number; ?></label>';
            html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][member_number]" value="" class="form-control" />';
        html += '</div>';
        html += '<div class="form-group hide">';
          html += '<label class="control-label"><?php echo $entry_sort_order; ?></label>';
            html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][sort_order]" value="'+ member_row +'" class="form-control member-sortorder" />';
        html += '</div>';
        html += '<div class="form-group">';
          html += '<label class="control-label"><?php echo $entry_photo; ?></label>';
          html += '<div class=""><a href="" id="thumb-photo'+ member_row +'" data-toggle="image" class="img-thumbnail img-circle"><img src="<?php echo $placeholder; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>';
            html += '<input type="hidden" name="ciwhatsapp_setting_member['+ member_row +'][photo]" value="" id="input-photo'+ member_row +'" />';
          html += '</div>';
        html += '</div>';
        html += '<div class="form-group">';
          html += '<label class="control-label"><?php echo $entry_department_name; ?></label>';
          html += '<div class="">';
            <?php foreach ($languages as $language) { ?>
            html += '<div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>';
              html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][description][<?php echo $language['language_id']; ?>][department_name]" value="" placeholder="<?php echo $entry_department_name; ?>" class="form-control" />';
            html += '</div>';
            <?php } ?>
          html += '</div>';
        html += '</div>';
        html += '<div class="form-group">';
          html += '<label class="control-label"><?php echo $entry_greeting_message; ?></label>';
          html += '<div class="">';
            <?php foreach ($languages as $language) { ?>
            html += '<div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>';
              html += '<textarea name="ciwhatsapp_setting_member['+ member_row +'][description][<?php echo $language['language_id']; ?>][greeting_message]" class="form-control"></textarea>';
            html += '</div>';
            <?php } ?>
          html += '</div>';
        html += '</div>';
      html += '</fieldset>';
      html += '<fieldset>';
        html += '<legend class="legend_margin"><i class="fa fa-clock-o"></i> <?php echo $legend_availability; ?></legend>';
        html += '<div class="form-group">';
          html += '<label class="control-label"><?php echo $entry_member_status; ?></label>';
          html += '<div class="">';
            html += '<div class="btn-group grou_member_status_'+ member_row +'" data-toggle="buttons" data-row="'+ member_row +'">';
              html += '<label class="btn btn-default member_status active">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][status]" checked="checked" autocomplete="off" value="online" type="radio"><?php echo $text_online; ?>';
              html += '</label>';
              html += '<label class="btn btn-default member_status">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][status]" autocomplete="off" value="online_schedule" type="radio"><?php echo $text_online_schedule; ?>';
              html += '</label>';
              html += '<label class="btn btn-default member_status">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][status]"autocomplete="off" value="offline" type="radio"><?php echo $text_offline; ?>';
              html += '</label>';
              html += '<label class="btn btn-default member_status ">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][status]"autocomplete="off" value="hide" type="radio"><?php echo $text_hide; ?>';
              html += '</label>';
            html += '</div>';
          html += '</div>';
        html += '</div>';
        html += '<div class="form-group group_weekdays_'+ member_row +' group_weekdays hide">';
          html += '<div class="table-responsive">';
            html += '<table class="table table-hover">';
              html += '<thead>';
                html += '<tr>';
                  html += '<th style="width: 30%"><?php echo $column_status; ?></th>';
                  html += '<th style="width: 30%"><?php echo $column_weekday; ?></th>';
                  html += '<th style="width: 20%"><?php echo $column_start_time; ?></th>';
                  html += '<th style="width: 20%"><?php echo $column_end_time; ?></th>';
                html += '</tr>';
              html += '</thead>';
              html += '<tbody>';
                <?php foreach($weekdays as $key => $weekday) { ?>
                html += '<tr>';
                  html += '<td>';
                    html += '<div class="btn-group" data-toggle="buttons">';
                      html += '<label class="btn btn-default btn-sm active">';
                        html += '<input name="ciwhatsapp_setting_member['+ member_row +'][weekday][<?php echo $key; ?>][status]" checked="checked" autocomplete="off" value="1" type="radio"><?php echo $text_enabled; ?>';
                      html += '</label>';
                      html += '<label class="btn btn-default btn-sm">';
                        html += '<input name="ciwhatsapp_setting_member['+ member_row +'][weekday][<?php echo $key; ?>][status]" autocomplete="off" value="0" type="radio"><?php echo $text_disabled; ?>';
                      html += '</label>';
                    html += '</div>';
                  html += '</td>';
                  html += '<td class="weekday_name"><b><?php echo $weekday; ?></b></td>';
                  html += '<td>';
                    html += '<div class="input-group time">';
                      html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][weekday][<?php echo $key; ?>][start_time]" value="10:00 AM" class="form-control" />';
                      html += '<span class="input-group-btn">';
                        html += '<button type="button" class="btn btn-default"><i class="fa fa-clock-o"></i></button>';
                      html += '</span>';
                    html += '</div>';
                  html += '</td>';
                  html += '<td>';
                    html += '<div class="input-group time">';
                      html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][weekday][<?php echo $key; ?>][end_time]" value="06:00 PM" class="form-control" />';
                      html += '<span class="input-group-btn">';
                        html += '<button type="button" class="btn btn-default"><i class="fa fa-clock-o"></i></button>';
                      html += '</span>';
                    html += '</div>';
                  html += '</td>';
                html += '</tr>';
                <?php } ?>
              html += '</tbody>';
            html += '</table>';
          html += '</div>';
        html += '</div>';
        html += '<div class="group_time_'+ member_row +'">';
          html += '<div class="form-group">';
            html += '<label class="control-label"><?php echo $entry_time_text_status; ?></label>';
            html += '<div class="">';
              html += '<div class="btn-group" data-toggle="buttons">';
                html += '<label class="btn btn-default active">';
                  html += '<input name="ciwhatsapp_setting_member['+ member_row +'][time_text_status]" checked="checked" autocomplete="off" value="1" type="radio"><?php echo $text_show; ?>';
                html += '</label>';
                html += '<label class="btn btn-default">';
                  html += '<input name="ciwhatsapp_setting_member['+ member_row +'][time_text_status]" autocomplete="off" value="0" type="radio"><?php echo $text_hide; ?>';
                html += '</label>';
              html += '</div>';
            html += '</div>';
          html += '</div>';
          html += '<div class="form-group">';
            html += '<label class="control-label"><?php echo $entry_time_text; ?>';
              html += '<a class="btn btn-info btn-xs time_text_system_value" data-row="'+ member_row +'"><i class="fa fa-cog"></i> <?php echo $entry_time_text_system_value; ?></a>';
            html += '</label>';
            html += '<div class="">';
              <?php foreach ($languages as $language) { ?>
              html += '<div class="input-group"><span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>';
                html += '<input type="text" name="ciwhatsapp_setting_member['+ member_row +'][description][<?php echo $language['language_id']; ?>][time_text]" value="<?php echo $sys_always_online; ?>" placeholder="<?php echo $entry_time_text; ?>" class="form-control group_time_text_'+ member_row +'" />';
              html += '</div>';
              <?php } ?>
            html += '</div>';
          html += '</div>';
        html += '</div>';
      html += '</fieldset>';
      html += '<fieldset>';
        html += '<legend class="legend_margin"><i class="fa fa-desktop"></i> <?php echo $legend_widget_member; ?></legend>';
        html += '<div class="form-group">';
          html += '<label class="control-label"><?php echo $entry_widget_member; ?></label>';
          html += '<div class="">';
            html += '<div class="btn-group" data-toggle="buttons">';
              html += '<label class="btn btn-default active">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][page_status][]" checked="checked" autocomplete="off" value="bottom" type="checkbox"><?php echo $text_widget_bottom; ?>';
              html += '</label>';
              html += '<label class="btn btn-default group_anotherwidget_'+ member_row +'">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][page_status][]" autocomplete="off" value="product" type="checkbox"><?php echo $text_widget_product; ?>';
              html += '</label>';
              html += '<label class="btn btn-default group_anotherwidget_'+ member_row +'">';
                html += '<input name="ciwhatsapp_setting_member['+ member_row +'][page_status][]" autocomplete="off" value="layout_pages" type="checkbox"><?php echo $text_widget_layout_pages; ?>';
              html += '</label>';
            html += '</div>';
          html += '</div>';
        html += '</div>';
      html += '</fieldset>';

    html += '</div>';
  html += '</div>';

  $('#tab-member #tab-content').append(html);

  $('#member').append('<li class="member-li"><a href="#tab-member' + member_row + '" data-toggle="tab"><i class="fa fa-minus-circle" onclick=" $(\'#member a:first\').tab(\'show\'); $(\'a[href=\\\'#tab-member' + member_row + '\\\']\').parent().remove(); $(\'#tab-member' + member_row + '\').remove();"></i> <?php echo $tab_member; ?>-'+ (member_row + parseInt(1))  +' <i class="fa fa-arrows pull-right" aria-hidden="true"></i></a></li>');

  $('#member a[href=\'#tab-member' + member_row + '\']').tab('show');

  $('[data-toggle=\'tooltip\']').tooltip({
    container: 'body',
    html: true
  });

  $('.time').datetimepicker({
    pickDate: false,
    format : 'hh:mm A'
  });

  member_row++;
}
//--></script>
<script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
<script type="text/javascript"><!--
$('.time').datetimepicker({
  pickDate: false,
  format : 'hh:mm A'
});
//--></script>

<script type="text/javascript"><!--
$(document).delegate('.member_status', 'click', function() {

  var data_row = $(this).parent().attr('data-row');

  if($(this).find('input').val() == 'online_schedule') {
    $('.group_weekdays_'+ data_row).removeClass('hide');
  } else {
    $('.group_weekdays_'+ data_row).addClass('hide');
  }


  if($(this).find('input').val() == 'hide') {
    $('.group_time_'+ data_row).addClass('hide');
  } else {
    $('.group_time_'+ data_row).removeClass('hide');

  }

  if($(this).find('input').val() == 'hide' || $(this).find('input').val() == 'offline') {
    $('.group_anotherwidget_'+ data_row).addClass('hide');
  } else {
    $('.group_anotherwidget_'+ data_row).removeClass('hide');
  }
});

$(document).delegate('.time_text_system_value', 'click', function() {

  var data_row = $(this).attr('data-row');

    if($('.grou_member_status_'+ data_row).find('.active').find('input').val() == 'online') {
      var text_value = '<?php echo $sys_always_online; ?>';
    }

    if($('.grou_member_status_'+ data_row).find('.active').find('input').val() == 'online_schedule') {
      var text_value = '<?php echo $sys_online_schedule; ?>';
    }

    if($('.grou_member_status_'+ data_row).find('.active').find('input').val() == 'offline') {
      var text_value = '<?php echo $sys_offline; ?>';
    }

    if(text_value) {
      $('.group_time_text_'+ data_row).val(text_value);
    }
});
//--></script>
<script type="text/javascript">
  var element = null;
  $('.color-picker').ColorPicker({
    curr : '',
    onShow: function (colpkr) {
      $(colpkr).fadeIn(500);
      return false;
    },
    onHide: function (colpkr) {
      $(colpkr).fadeOut(500);
    return false;
    },
    onSubmit: function(hsb, hex, rgb, el) {
      $(el).val('#'+hex);
      $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
      $(this).ColorPickerSetColor(this.value);
    },
    onChange: function (hsb, hex, rgb) {
      element.curr.parent().next().find('.preview').css('background', '#' + hex);
      element.curr.val('#'+hex);
    }
  }).bind('keyup', function(){
    $(this).ColorPickerSetColor(this.value);
  }).click(function(){
    element = this;
    element.curr = $(this);
  });

  $.each($('.color-picker'),function(key,value) {
    $(this).parent().next().find('.preview').css({'background': $(this).val()});
  });

//--></script>
<script type="text/javascript"><!--
$(document).ready(function() {
  $("#member").sortable({
    cursor: "move",
    stop: function() {
      $('#member .member-li').each(function() {
        $($(this).find('a').attr('href')).find('.member-sortorder').val(($(this).index() + 1));
      });
    }
  });
});
//--></script>

<?php if($action_enable_events) { ?>
<script type="text/javascript">
function enableEvents() {
  $.ajax({
    url: '<?php echo $action_enable_events; ?>',
    dataType: 'json',
    beforeSend: function() {
      $('.button-enable-event').attr('disabled', true);
    },
    complete: function() {
      $('.button-enable-event').attr('disabled', false);
    },
    success: function(json) {
      $('.inspect-warning, .inspect-danger, .inspect-success').remove();

      if(json['warning']) {
        $('.container-fluid > .panel').before('<div class="alert alert-danger inspect-alert"><i class="fa fa-exclamation-circle"></i> ' + json['warning'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
      }

      if(json['success']) {
        location = json['success'];
      }
    }
  });
}
</script>
<?php } ?>

<style type="text/css">
.support-wrap { background: #eee none repeat scroll 0 0; border-bottom: 2px solid #ffc00d; border-radius: 10px; position: relative; padding: 35px 35px; width: 100%; }

.support-wrap .ciinfo{ display: inline-block; }

.support-wrap .profile-buttons { position: absolute; right: 20px; top: 20px; }

.support-wrap .ci-support-icon{ font-size: 50px; display: inline-block; padding-right: 20px; }



.rating-wrap{ background: #eee none repeat scroll 0 0; border-bottom: 2px solid #ffc00d; border-radius: 10px; position: relative; padding: 35px 15px 15px; width: 100%; }

.rating-wrap .rating-buttons { position: absolute; right: 20px; top: 20px; }
.rating-wrap .rating-buttons i { font-size: 20px; }

.rating-wrap .rating { margin: 20px; }

.rating-wrap i.fa-star{ font-size: 20px; display: inline-block; padding-right: 5px; color: #ffc00d; }
</style>
</div>
<?php echo $footer; ?>