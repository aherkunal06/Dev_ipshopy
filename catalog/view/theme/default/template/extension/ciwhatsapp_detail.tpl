<?php if($members) { ?>
<div class="whatsapp_products <?php echo $custom_class; ?> <?php echo $layout; ?> <?php echo $multi_inspect; ?>">
  <?php foreach($members as $member) { ?>
    <div class="single_member clearfix <?php echo $member['online_status'] ? 'online' : 'offline'; ?>">
      <a href="<?php echo $member['apilink']; ?>" <?php echo $member['online_status'] ? 'target="_blank"' : ''; ?>>
        <div class="circle_design <?php echo $member['online_status'] ? 'online' : 'offline'; ?>"></div>
        <div class="user_image">
          <div class="img_p">
            <img src="<?php echo $member['photo_thumb']; ?>" class="img-responsive">
            <div class="whatsapp_icon_here">
              <i class="fa fa-whatsapp"></i>
            </div>
            </div>
        </div>
        <div class="user_content">
          <?php if($member['department_name']) { ?>
          <h5><?php echo $member['department_name']; ?></h5>
          <?php } ?>

          <?php if($member['member_name']) { ?>
          <h4><?php echo $member['member_name']; ?></h4>
          <?php } ?>

          <?php if($member['time_text']) { ?>
          <p><?php echo $member['time_text']; ?></p>
          <?php } ?>
        </div>
      </a>
    </div>
  <?php } ?>

  <div style="clear: both;"></div>
</div>
<?php } ?>