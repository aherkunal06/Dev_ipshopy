<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_green_warning) { ?>
    <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_green_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

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
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-page" class="form-horizontal">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-key"></i> <?php echo $legend_key; ?></h3>
        </div>
        <div class="panel-body">
          <div class="form-group <?php echo $print_form ? '' : 'hide'; ?>">
            <label class="col-sm-2 control-label">Print for Testing</label>
            <div class="col-sm-10">
              <label class="radio-inline">
                  <input type="radio" name="print_form" value="1" <?php echo $print_form ? 'checked="checked"' : ''; ?> /> Yes
              </label>

              <label class="radio-inline">
                  <input type="radio" name="print_form" value="0" <?php echo !$print_form ? 'checked="checked"' : ''; ?>/> No
              </label>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label"><?php echo $entry_key; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="module_ciwhatsapp_key" value="<?php echo $module_ciwhatsapp_key; ?>" class="form-control" />
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-success"><i class="fa fa-send"></i> <?php echo $button_submit; ?></button>
                </span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">&nbsp;</label>
            <div class="col-sm-6">
              <h4 style="font-style: italic;"><a target="_blank" href="https://www.codinginspect.com/index.php?route=api/license"><b><i class="fa fa-key"></i>  Click here to Get License Key...</b></a></h4>
            </div>
          </div>

        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-info-circle"></i> <?php echo $legend_about; ?></h3>
        </div>
        <div class="panel-body text-center">
          <h4 style="font-weight: 600;">Current Extension Version: 3.0</h4>
          <br>
          <h5><a target="_blank" href="https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=<?php echo $extension_id; ?>" class="btn btn-primary"><i class="fa fa-link"></i> Purchase Whatsapp Chat Manager</a></h5>
        </div>
      </div>
    </form>
  </div>
</div>
<?php echo $footer; ?>