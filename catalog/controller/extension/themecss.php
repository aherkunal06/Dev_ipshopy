<?php
class ControllerExtensionThemecss extends Controller {

	public function index() {

		header("Content-type: text/css", true);

		$data['midbgcolor'] = $this->config->get('tmdaccount_midbgcolor');
		$data['primerybtnbgcolor'] = $this->config->get('tmdaccount_pbtncolor');
		$data['primerybtncolor'] = $this->config->get('tmdaccount_pbtntextcolor');
		
		$data['totalorders'] = $this->config->get('tmdaccount_totalorders_bg');
		$data['totalwishlist'] = $this->config->get('tmdaccount_totalwishlist_bg');
		$data['totalreward'] = $this->config->get('tmdaccount_totalreward_bg');
		$data['totaldownload'] = $this->config->get('tmdaccount_totaldownload_bg');
		$data['totaltransaction'] = $this->config->get('tmdaccount_totaltransaction_bg');
		
		$data['latestorder'] = $this->config->get('tmdaccount_latestorder_bg');
		
	
		// Dashmenu icon color css start here by radha
		echo ".menu_bg {background:".$data['midbgcolor']."!important;}";
		
		echo "#accountdashboard .accountmenu-box .totalorder .icon-box span i,
		#accountdashboard .accountmenu-box .totalorder .icon-box .iconnew{
			background:".$data['totalorders']."!important;
		}
		#accountdashboard .accountmenu-box .totalwishlist .icon-box span i,
		#accountdashboard .accountmenu-box .totalwishlist .icon-box .iconnew {
			background:".$data['totalwishlist']."!important;
		}
		#accountdashboard .accountmenu-box .totalreward .icon-box span i,
		#accountdashboard .accountmenu-box .totalreward .icon-box .iconnew {
			background:".$data['totalreward']."!important;
		}
		#accountdashboard .accountmenu-box .totaldownloads .icon-box span i,
		#accountdashboard .accountmenu-box .totaldownloads .icon-box .iconnew {
			background:".$data['totaldownload']."!important;
		}
		#accountdashboard .accountmenu-box .totaltransactions .icon-box span i,
		#accountdashboard .accountmenu-box .totaltransactions .icon-box .iconnew {
			background:".$data['totaltransaction']."!important;
		}
		";
		// Layout 2 
		echo "#accountdashboard .accountmenu-box .totalorder .icon-box  {
			border-color:".$data['totalorders']."!important;
		}
		#accountdashboard .accountmenu-box .totalwishlist .icon-box {
			border-color: ".$data['totalwishlist']."!important;
		}
		#accountdashboard .accountmenu-box .totalreward .icon-box {
			border-color: ".$data['totalreward']."!important;
		}
		#accountdashboard .accountmenu-box .totaldownloads .icon-box {
			border-color: ".$data['totaldownload']."!important;
		}
		#accountdashboard .accountmenu-box .totaltransactions .icon-box {
			border-color: ".$data['totaltransaction']."!important;
		}";
		
		// Side Bar
		$data['sidehdbarbg'] = $this->config->get('tmdaccount_sidebarbg');
		$data['sidehdbarcolor'] = $this->config->get('tmdaccount_sidebarcolor');
		$data['sidebartxcolor'] = $this->config->get('tmdaccount_sidebartcolor');
		$data['sidebartxhover'] = $this->config->get('tmdaccount_sidebarhover');
		$data['sidebarlinkbg'] = $this->config->get('tmdaccount_sidebarboxbg');
		$data['sidebarlinkbghov'] = $this->config->get('tmdaccount_sidebarboxhover');
		$data['sidebarleftborder'] = $this->config->get('tmdaccount_sidebarleftborder');
		$data['sidebaricon'] = $this->config->get('tmdaccount_sidebaricon');

		echo ".dashboard .btn-primary{color:".$data['primerybtncolor']."!important}";
		echo ".dashboard .btn-primary{background:".$data['primerybtnbgcolor']."!important;border-color:".$data['primerybtnbgcolor']."!important;text-shadow:none;}";

		echo ".accountmenu-box .table1 h3{background:".$data['latestorder']."!important}";
		echo ".dashboard1 .profile{background:".$data['sidehdbarbg']."!important;color:".$data['sidehdbarcolor']."!important;}";
		echo ".dashboard1 .list-group .fa,.dashboard .list-group .fa{color:".$data['sidebaricon']."!important;}";
		echo ".dashboard1 .profile h3,.dashboard1 .profile p{color:".$data['primerybtncolor']."!important;}";
		echo ".icons i:hover{background:".$data['sidehdbarbg']."!important;color:".$data['sidebartxcolor']."!important;}";

        echo ".efect-border li:hover .icon i,.efect-border a .icon::before,.efect-border a .icon::after ,.efect-border a::before,.efect-border a::after{background:".$data['primerybtnbgcolor']."!important;color:".$data['primerybtncolor']."!important;}";
		
		// Side Bar
		echo ".dashboard .list-group h2{background:".$data['sidehdbarbg']."!important;color:".$data['sidehdbarcolor']."!important;}";
		echo ".dashboard .list-group a,.dashboard1 .list-group a{color:".$data['sidebartxcolor']."!important}";
		echo ".dashboard .list-group a.active, .dashboard .list-group a:hover,.dashboard1 .list-group a.active, .dashboard1 .list-group a:hover{text-shadow:none !important;background:".$data['sidebarlinkbghov']."!important}";
		echo ".dashboard1 .list-group a{border-radius:0px !important;}";
		echo ".dashboard .list-group a,.dashboard1 .list-group a{border-color:".$data['sidebarleftborder']."!important;background-color:".$data['sidebarlinkbg']."!important;}";
		echo ".dashboard .list-group  a:hover,.dashboard1 .list-group  a:hover{color:".$data['sidebartxhover']."!important;}";
		echo ".dashboard .profile .image img{border:3px solid ".$data['latestorder']."!important;}";
		echo ".dashboard .profile .detail ul{border-top:1px solid ".$data['latestorder']."!important;}";
		echo ".dashboard .profile .btnedit + .btnedit{border-left:1px solid ".$data['latestorder']."!important;}";

	}
}
