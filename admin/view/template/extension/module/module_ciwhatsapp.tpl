<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ciwhatsapp" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-check-circle"></i> <?php echo $button_save; ?></button>
        <a href="<?php echo $setting; ?>" data-toggle="tooltip" title="<?php echo $button_setting; ?>" class="btn btn-danger"><i class="fa fa-cog"></i></a>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
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
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account" class="form-horizontal">
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
            <div class="col-sm-10">
              <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
              <?php if ($error_name) { ?>
              <div class="text-danger"><?php echo $error_name; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group select-form required">
            <label class="col-sm-2 control-label"><?php echo $entry_member; ?></label>
            <div class="col-sm-10">
              <div class="row">
                <?php if($all_layout_members) { ?>
                  <?php foreach ($all_layout_members as $all_layout_member) { ?>
                  <label class="col-sm-4">
                    <?php
                    if (in_array($all_layout_member['member_id'], $members)) {
                      $checked = 'checked="checked"';
                      $active = 'active';
                    } else {
                      $checked = '';
                      $active = '';
                    }
                    ?>
                    <div class="module_member <?php echo $active; ?> ">
                      <img src="<?php echo $all_layout_member['photo_thumb']; ?>" class="img-responsive img-circle" />
                      <?php echo $all_layout_member['member_name']; ?>

                      <input type="checkbox" name="member[]" value="<?php echo $all_layout_member['member_id']; ?>" <?php echo $checked; ?> />
                    </div>
                  </label>
                  <?php } ?>
                <?php } else { ?>
                  <div class="col-sm-12">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $info_member; ?></div>
                  </div>
                <?php } ?>
              </div>

              <?php if ($error_member) { ?>
              <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_member; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
              </div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_module_layout; ?></label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default <?php echo $design_layout == 'single_line_layout' ? 'active' : ''; ?> ">
                  <input name="design_layout" <?php echo $design_layout == 'single_line_layout' ? 'checked="checked"' : ''; ?> autocomplete="off" value="single_line_layout" type="radio" /><?php echo $text_single_line_layout; ?>
                </label>
                <label class="btn btn-default <?php echo $design_layout == 'multi_line_layout' ? 'active' : ''; ?> ">
                  <input name="design_layout" <?php echo $design_layout == 'multi_line_layout' ? 'checked="checked"' : ''; ?> autocomplete="off" value="multi_line_layout" type="radio" /><?php echo $text_multi_line_layout; ?>
                </label>

              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default <?php echo $status ? 'active' : ''; ?> ">
                  <input name="status" <?php echo $status ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_enabled; ?>
                </label>
                <label class="btn btn-default <?php echo !$status ? 'active' : ''; ?>">
                  <input name="status" <?php echo !$status ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"><?php echo $text_disabled; ?>
                </label>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<style type="text/css">
.select-form input {
  opacity: 0;
}
.module_member {
  background: #c9c9c9;
  color: #383737;
  font-size: 18px;
  padding: 30px 5px;
  margin-bottom: 15px;
  border-radius: 5px;
  cursor: pointer;
  text-align: center;
  min-height: 140px;
}
.module_member img{
  margin: auto;
}
.module_member.active {
    background: #b4e7ff;
}
</style>
<script type="text/javascript">
$('.module_member').click(function() {

  if($(this).find('input').prop("checked")) {
    $(this).addClass('active');
  } else {
    $(this).removeClass('active');
  }
});
</script>
<?php echo $footer; ?>