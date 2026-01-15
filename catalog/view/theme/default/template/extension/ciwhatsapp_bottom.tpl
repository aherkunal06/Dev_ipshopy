<div class="whatsapp_handle <?php echo $shape == 'round' ? 'only_icon_whatsapp' : ''; ?> <?php echo $position; ?> <?php echo $layout; ?>">
  <div class="whstapp_noti">
    <div class="web_icon" onclick="openCiWhatsapp();"><i class="fa fa-whatsapp"></i>
      <?php if($button_text) { ?>
      <span><?php echo $button_text; ?></span>
      <?php } ?>
    </div>
    <div class="chat_main animated bounceInUp">
      <div class="full_chat">
        <div class="top_chat">
          <div class="title_chat clearfix">
            <h4 class=""><?php echo $module_title; ?></h4>

            <?php if($module_description) { ?>
            <p><?php echo $module_description; ?></p>
            <?php } ?>

            <div class="close_icon" onclick="closeCiWhatsapp();"><i class="fa fa-times"></i></div>
          </div>
        </div>
        <?php
        if($layout == 'list_work') {
          $captilize_class = (count($members) >= 4 ? 'upper3' : 'lowerin3');
        } else {
          $captilize_class = (count($members) >= 5 ? 'upper5' : 'lowerin4');
        }
        ?>
        <div class="main_user_chat <?php echo $captilize_class; ?>">
          <?php foreach($members as $member) { ?>
          <div class="single_user clearfix <?php echo $member['online_status'] ? 'online' : 'offline'; ?>">
            <a href="<?php echo $member['apilink']; ?>" <?php echo $member['online_status'] ? 'target="_blank"' : ''; ?>>
              <div class="circle_design <?php echo $member['online_status'] ? 'online' : 'offline'; ?>"></div>
              <div class="user_image">
                <div class="img_p">
                <img src="<?php echo $member['photo_thumb']; ?>" class="img-responsive" />

                <p class="status_dot"><span></span></p>
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

        </div>
      </div>
    </div>
  </div>
</div>