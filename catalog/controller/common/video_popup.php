<?php
class ControllerCommonVideoPopup extends Controller {
    public function index($setting = []) {
        $data['video_url'] = isset($setting['video_url']) ? $setting['video_url'] : '';
        $data['video_title'] = isset($setting['video_title']) ? $setting['video_title'] : 'watch guide';

        return $this->load->view('common/video_popup', $data);
    }
}
