<?php

/**

 * OpenCart 3.x Backend Ecommerce Feed  application

 * 

 * @package Mega Feed Pro

 * @author Andras Kato <developer@newcms.hu>

 * @link https://www.newcms.hu/

 * @copyright NewCart and NewCMS Software LTD. 2004-2021 (https://www.newcms.hu)

 * @license https://www.www.newcms.hu/oc3/license/

 * @version 1.2.7 Pro

 * 

 * Attention!

 * In case you want to use a nulled software version, we will detect it due to the built-in protection and you will be prosecuted!

 * DO NOT USE NULLED SOFTWARE BETTER USE ORIGINAL LICENSED SOFTWARE! 

 * 

 */

 

class ModelExtensionFeedMegaFeedPro extends Model

{

	public function getMegaToken()

	{

		return substr(sha1($_SERVER['SERVER_NAME'] . '$@/digital-wolf-645787'), 0, 30);

	}

}

