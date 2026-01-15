<?php

class ModelExtensionFeedMergadoMarketingPack extends Model {

  public function getMergadoToken() {
    return substr( sha1($_SERVER['SERVER_NAME']. '$@/digital-wolf-645787'), 0, 30);
  }

}
