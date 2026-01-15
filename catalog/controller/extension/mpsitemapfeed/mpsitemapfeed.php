<?php
class ControllerExtensionMpsitemapfeedMpsitemapfeed extends Controller {

	use mpsitemapfeed\trait_mpsitemapfeed_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpsitemapfeed($registry);

		if (!file_exists($this->path_application . 'sitemaps')){
			mkdir($this->path_application . 'sitemaps');
		}

		$this->load->model('tool/image');

		$this->load->model($this->extension_path . 'mpsitemapfeed/mpsitemapfeed');

		if (VERSION <= '2.2.0.0') {
			$this->model_extension_mpsitemapfeed_mpsitemapfeed = &$this->model_mpsitemapfeed_mpsitemapfeed;
		}

		$this->total_language = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getTotalLanguages();

		if (!$this->is_multilingual) {
			$this->config->set('module_mpsitemapfeed_product_multilangurl', 0);
			$this->config->set('module_mpsitemapfeed_category_multilangurl', 0);
			$this->config->set('module_mpsitemapfeed_manufacturer_multilangurl', 0);
			$this->config->set('module_mpsitemapfeed_information_multilangurl', 0);
			$this->config->set('module_mpsitemapfeed_j3_blogpost_multilangurl', 0);
			$this->config->set('module_mpsitemapfeed_j3_blogcategory_multilangurl', 0);
			// $this->config->set('module_mpsitemapfeed_blogauthor_multilangurl', 0);
		}
	}

	public function index() {
		if ($this->config->get('module_mpsitemapfeed_status')) {
			$xml = new \DOMDocument('1.0', 'UTF-8');

			$files = glob($this->path_application . 'sitemaps/*.xml');

			foreach ($files as $file) {
				unlink($file);
			}

			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;

			$this->getsiteindexxsl();
			$this->getsitemapxsl();

			//creating an xslt adding processing line
			$xslt = $xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.$this->server . 'sitemaps/sitemapindex.xsl"');

			//adding it to the xml
			$xml->appendChild($xslt);

			$xml_sitemapindex = $xml->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'sitemapindex');
			$xml->appendChild($xml_sitemapindex);
			$xml_sitemapindex->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
			$xml_sitemapindex->setAttributeNS('http://www.sitemaps.org/schemas/sitemap/0.9' ,'xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd');

			if ($this->config->get('module_mpsitemapfeed_product_status')) {
				$chunk_items = $this->product();

				for ($i=0;$i<=$chunk_items;$i++) {
					$xml_sitemap = $xml->createElement("sitemap");
					$xml_sitemapindex->appendChild($xml_sitemap);
					$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/product-sitemap'. ($i ? $i : '')  . '.xml'));
					$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
				}
			}

			if ($this->config->get('module_mpsitemapfeed_category_status')) {
				$this->category();
				$xml_sitemap = $xml->createElement("sitemap");
				$xml_sitemapindex->appendChild($xml_sitemap);
				$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/category-sitemap.xml'));
				$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
			}

			if ($this->config->get('module_mpsitemapfeed_manufacturer_status')) {
				$chunk_items = $this->manufacturer();
				for ($i=0;$i<=$chunk_items;$i++) {
					$xml_sitemap = $xml->createElement("sitemap");
					$xml_sitemapindex->appendChild($xml_sitemap);
					$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/manufacturer-sitemap'. ($i ? $i : '')  . '.xml'));
					$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
				}
			}

			if ($this->config->get('module_mpsitemapfeed_information_status')) {
				$this->information();
				$xml_sitemap = $xml->createElement("sitemap");
				$xml_sitemapindex->appendChild($xml_sitemap);
				$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/information-sitemap.xml'));
				$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
			}

			if ($this->config->get('module_mpsitemapfeed_j3_blogpost_status')) {
				$this->j3blogpost();
				$xml_sitemap = $xml->createElement("sitemap");
				$xml_sitemapindex->appendChild($xml_sitemap);
				$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/j3-blogpost-sitemap.xml'));
				$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
			}

			if ($this->config->get('module_mpsitemapfeed_j3_blogcategory_status')) {
				$this->j3blogcategory();
				$xml_sitemap = $xml->createElement("sitemap");
				$xml_sitemapindex->appendChild($xml_sitemap);
				$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/j3-blogcategory-sitemap.xml'));
				$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
			}

			if ($this->config->get('module_mpsitemapfeed_custom_link_status')) {
				$this->customlink();
				$xml_sitemap = $xml->createElement("sitemap");
				$xml_sitemapindex->appendChild($xml_sitemap);
				$xml_sitemap->appendChild($xml->createElement("loc", $this->server . 'sitemaps/customlink-sitemap.xml'));
				$xml_sitemap->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime(date('Y-m-d')))));
			}

			$file_name = 'sitemaps/main_sitemap.xml';
			$file_to_save = $this->path_application . $file_name;

			$xml->save($file_to_save);

			$output = file_get_contents($file_to_save);

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function generateXML($type) {
		$xml = new \DOMDocument('1.0', 'UTF-8');

		$xml->preserveWhiteSpace = false;
		$xml->formatOutput=true;

		//creating an xslt adding processing line
		$xslt = $xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'. $this->server  . 'sitemaps/sitemap.xsl"');

		//adding it to the xml
		$xml->appendChild($xslt);

		$xml_urlset = $xml->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
		$xml->appendChild($xml_urlset);
		$xml_urlset->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

		if ($this->config->get('module_mpsitemapfeed_'. $type  . '_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
			$xml_urlset->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
		}

		return array(
			&$xml,
			&$xml_urlset
		);
	}

	public function product() {
		if ($this->config->get('module_mpsitemapfeed_product_status')) {
			$products = [];
			$f= [];
			if ($this->config->get('module_mpsitemapfeed_product_ids')) {
				$f['product_ids'] = $this->config->get('module_mpsitemapfeed_product_ids');
			}

			$product_total = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getTotalProducts($f);

			$page = 0;
			$limit = $this->config->get('module_mpsitemapfeed_limit');
			for ($i=0; $i < $product_total; $i = $i + $limit) {
				$page++;
				$f = ['start' => ($page - 1) * $limit, 'limit' => $limit];

				if ($f['start'] < 0) {
					$f['start'] = 0;
				}
				if ($f['limit'] < 0) {
					$f['limit'] = $this->config->get('module_mpsitemapfeed_limit');
				}

				if ($this->config->get('module_mpsitemapfeed_product_ids')) {
					$f['product_ids'] = $this->config->get('module_mpsitemapfeed_product_ids');
				}

				$products = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getProducts($f);

				list($xml,$xml_urlset) = $this->generateXML('product');

				foreach ($products as $product) {

					$xml_url = $xml->createElement("url");
					$xml_urlset->appendChild($xml_url);

					$xml_url->appendChild($xml->createElement("loc", $this->url->link('product/product', 'product_id=' . $product['product_id'])));
					$old_language_id = (int)$this->config->get('config_language_id');
					if ($this->config->get('module_mpsitemapfeed_product_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
						foreach ($this->getLanguages() as $language) {
							$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
							$xml_url->appendChild($xml_link);
							$xml_link->setAttributeNS('', 'rel', 'alternate');
							$xml_link->setAttributeNS('', 'hreflang', $language['code']);

							$this->config->set('config_language_id', $language['language_id']);

							$link = $this->server . 'index.php?route=product/product&product_id=' . $product['product_id'];

							$link = $this->load->controller('startup/seo_url/rewrite', $link);

							$xml_link->setAttributeNS('', 'href', $link);
						}

						$this->config->set('config_language_id', $old_language_id);
					}

					$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_product_frequency')));

					$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_product_priority')));

					$xml_url->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime($product['date_modified']))));

					if ($product['image']) {
						if ($this->config->get('module_mpsitemapfeed_image_status')) {
							$product_images = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getProductImages($product['product_id']);
							array_unshift($product_images, array('image' => $product['image']));

							foreach ($product_images as $product_image) {
								$xml_image = $xml->createElement("image:image");
								$xml_url->appendChild($xml_image);

								if ($this->config->get('module_mpsitemapfeed_resize_image')) {
									$xml_image->appendChild($xml->createElement("image:loc", $this->model_tool_image->resize($product['image'], (int)$this->config->get('module_mpsitemapfeed_image_width'), (int)$this->config->get('module_mpsitemapfeed_image_height'))));
								} else {
									$xml_image->appendChild($xml->createElement("image:loc", $this->server . $product_image['image']));
								}
							}
						}
					}
				}

				$file_name = 'sitemaps/product-sitemap'. (($page - 1) ? ($page - 1) : '')  . '.xml';
				$file_to_save = $this->path_application . $file_name;

				$xml->save($file_to_save);
			}

			return ($page - 1);
		}
	}

	public function category() {
		if ($this->config->get('module_mpsitemapfeed_category_status')) {
			list($xml,$xml_urlset) = $this->generateXML('category');

			if ($this->config->get('module_mpsitemapfeed_category_ids')) {
				$category_ids = $this->config->get('module_mpsitemapfeed_category_ids');
				foreach ($category_ids as $category_id) {
					$category_datas = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getCategory($category_id);
					if ($category_datas) {
						$this->getCategories($category_id, $xml, $xml_urlset);
					}
				}
			} else {
				$this->getCategories(0, $xml, $xml_urlset);
			}

			$file_name = 'sitemaps/category-sitemap.xml';
			$file_to_save = $this->path_application . $file_name;

			$xml->save($file_to_save);
		}
	}

	public function manufacturer() {
		if ($this->config->get('module_mpsitemapfeed_manufacturer_status')) {
			$manufacturers = [];
			$f= [];
			if ($this->config->get('module_mpsitemapfeed_manufacturer_ids')) {
				$f['manufacturer_ids'] = $this->config->get('module_mpsitemapfeed_manufacturer_ids');
			}

			$manufacturer_total = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getTotalManufacturers($f);

			$page = 0;
			$limit = $this->config->get('module_mpsitemapfeed_limit');
			for ($i=0; $i < $manufacturer_total; $i = $i + $limit) {
				$page++;
				$f = ['start' => ($page - 1) * $limit, 'limit' => $limit];

				if ($f['start'] < 0) {
					$f['start'] = 0;
				}
				if ($f['limit'] < 0) {
					$f['limit'] = $this->config->get('module_mpsitemapfeed_limit');
				}

				if ($this->config->get('module_mpsitemapfeed_manufacturer_ids')) {
					$f['manufacturer_ids'] = $this->config->get('module_mpsitemapfeed_manufacturer_ids');
				}

				$manufacturers = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getManufacturers($f);

				list($xml,$xml_urlset) = $this->generateXML('manufacturer');

				foreach ($manufacturers as $manufacturer) {
					$xml_url = $xml->createElement("url");
					$xml_urlset->appendChild($xml_url);

					$xml_url->appendChild($xml->createElement("loc", $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'])));
					$old_language_id = (int)$this->config->get('config_language_id');
					if ($this->config->get('module_mpsitemapfeed_manufacturer_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
						foreach ($this->getLanguages() as $language) {
							$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
							$xml_url->appendChild($xml_link);
							$xml_link->setAttributeNS('', 'rel', 'alternate');
							$xml_link->setAttributeNS('', 'hreflang', $language['code']);
							$link = $this->server . 'index.php?route=product/manufacturer/info&manufacturer_id=' . $manufacturer['manufacturer_id'];
							$link = $this->load->controller('startup/seo_url/rewrite', $link);
							$xml_link->setAttributeNS('', 'href', $link);
						}
						$this->config->set('config_language_id', $old_language_id);
					}

					$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_manufacturer_frequency')));

					$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_manufacturer_priority')));

					if ($this->config->get('module_mpsitemapfeed_image_status') && !empty($manufacturer['image']) && file_exists(DIR_IMAGE . $manufacturer['image'])) {
						$xml_image = $xml->createElement("image:image");
						$xml_url->appendChild($xml_image);

						if ($this->config->get('module_mpsitemapfeed_resize_image')) {
							$xml_image->appendChild($xml->createElement("image:loc", $this->model_tool_image->resize($manufacturer['image'], (int)$this->config->get('module_mpsitemapfeed_image_width'), (int)$this->config->get('module_mpsitemapfeed_image_height'))));
						} else {
							$xml_image->appendChild($xml->createElement("image:loc", $this->server . $manufacturer['image']));
						}
					}
				}

				$file_name = 'sitemaps/manufacturer-sitemap'. (($page - 1) ? ($page - 1) : '')  . '.xml';
				$file_to_save = $this->path_application . $file_name;

				$xml->save($file_to_save);
			}

			return ($page - 1);
		}
	}

	public function j3blogpost() {
		if ($this->config->get('module_mpsitemapfeed_j3_blogpost_status')) {
			$products = [];
			$f= [];
			if ($this->config->get('module_mpsitemapfeed_j3_blogpost_ids')) {
				$f['blogpost_ids'] = $this->config->get('module_mpsitemapfeed_j3_blogpost_ids');
			}

			$blogpost_total = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getTotalJ3BlogPosts($f);

			$page = 0;
			$limit = $this->config->get('module_mpsitemapfeed_limit');
			for ($i=0; $i < $blogpost_total; $i = $i + $limit) {
				$page++;
				$f = ['start' => ($page - 1) * $limit, 'limit' => $limit];

				if ($f['start'] < 0) {
					$f['start'] = 0;
				}
				if ($f['limit'] < 0) {
					$f['limit'] = $this->config->get('module_mpsitemapfeed_limit');
				}

				if ($this->config->get('module_mpsitemapfeed_j3_blogpost_ids')) {
					$f['blogpost_ids'] = $this->config->get('module_mpsitemapfeed_j3_blogpost_ids');
				}

				$blogposts = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getJ3BlogPosts($f);

				list($xml,$xml_urlset) = $this->generateXML('j3_blogpost');

				foreach ($blogposts as $blogpost) {
					$xml_url = $xml->createElement("url");
					$xml_urlset->appendChild($xml_url);

					$xml_url->appendChild($xml->createElement("loc", $this->url->link('journal3/blog/post', 'journal_blog_post_id=' . $blogpost['post_id'])));
					$old_language_id = (int)$this->config->get('config_language_id');
					if ($this->config->get('module_mpsitemapfeed_j3_blogpost_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
						foreach ($this->getLanguages() as $language) {
							$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
							$xml_url->appendChild($xml_link);
							$xml_link->setAttributeNS('', 'rel', 'alternate');
							$xml_link->setAttributeNS('', 'hreflang', $language['code']);

							$this->config->set('config_language_id', $language['language_id']);

							$link = $this->server . 'index.php?route=journal3/blog/post&journal_blog_post_id=' . $blogpost['post_id'];

							$link = $this->load->controller('startup/seo_url/rewrite', $link);

							$xml_link->setAttributeNS('', 'href', $link);
						}
						$this->config->set('config_language_id', $old_language_id);
					}

					$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_j3_blogpost_frequency')));

					$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_j3_blogpost_priority')));

					$xml_url->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime($blogpost['date_updated']))));

					if ($blogpost['image']) {
						if ($this->config->get('module_mpsitemapfeed_image_status')) {
							$xml_image = $xml->createElement("image:image");
							$xml_url->appendChild($xml_image);

							if ($this->config->get('module_mpsitemapfeed_resize_image')) {
								$xml_image->appendChild($xml->createElement("image:loc", $this->model_tool_image->resize($blogpost['image'], (int)$this->config->get('module_mpsitemapfeed_image_width'), (int)$this->config->get('module_mpsitemapfeed_image_height'))));
							} else {
								$xml_image->appendChild($xml->createElement("image:loc", $this->server . $blogpost['image']));
							}
						}
					}
				}

				$file_name = 'sitemaps/blogpost-sitemap'. (($page - 1) ? ($page - 1) : '')  . '.xml';
				$file_to_save = $this->path_application . $file_name;

				$xml->save($file_to_save);
			}

			return ($page - 1);
		}
	}

	public function j3blogcategory() {
		if ($this->config->get('module_mpsitemapfeed_j3_blogcategory_status')) {
			$blogcategorys = [];
			$f= [];
			if ($this->config->get('module_mpsitemapfeed_j3_blogcategory_ids')) {
				$f['blogcategory_ids'] = $this->config->get('module_mpsitemapfeed_j3_blogcategory_ids');
			}

			$blogcategory_total = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getTotalJ3BlogCategories($f);

			$page = 0;
			$limit = $this->config->get('module_mpsitemapfeed_limit');
			for ($i=0; $i < $blogcategory_total; $i = $i + $limit) {
				$page++;
				$f = ['start' => ($page - 1) * $limit, 'limit' => $limit];

				if ($f['start'] < 0) {
					$f['start'] = 0;
				}
				if ($f['limit'] < 0) {
					$f['limit'] = $this->config->get('module_mpsitemapfeed_limit');
				}

				if ($this->config->get('module_mpsitemapfeed_j3_blogcategory_ids')) {
					$f['blogcategory_ids'] = $this->config->get('module_mpsitemapfeed_j3_blogcategory_ids');
				}

				$blogcategorys = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getJ3BlogCategory($f);

				list($xml,$xml_urlset) = $this->generateXML('j3_blogcategory');

				foreach ($blogcategorys as $blogcategory) {

					$xml_url = $xml->createElement("url");
					$xml_urlset->appendChild($xml_url);

					$xml_url->appendChild($xml->createElement("loc", $this->url->link('journal3/blog', 'journal_blog_category_id=' . $blogcategory['category_id'])));
					$old_language_id = (int)$this->config->get('config_language_id');
					if ($this->config->get('module_mpsitemapfeed_j3_blogcategory_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
						foreach ($this->getLanguages() as $language) {
							$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
							$xml_url->appendChild($xml_link);
							$xml_link->setAttributeNS('', 'rel', 'alternate');
							$xml_link->setAttributeNS('', 'hreflang', $language['code']);

							$this->config->set('config_language_id', $language['language_id']);

							$link = $this->server . 'index.php?route=journal3/blog&journal_blog_category_id=' . $blogcategory['category_id'];

							$link = $this->load->controller('startup/seo_url/rewrite', $link);

							$xml_link->setAttributeNS('', 'href', $link);
						}

						$this->config->set('config_language_id', $old_language_id);
					}

					$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_j3_blogcategory_frequency')));

					$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_j3_blogcategory_priority')));

					//$xml_url->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime($blogcategory['date_updated']))));
					if ($blogcategory['image']) {
						if ($this->config->get('module_mpsitemapfeed_image_status')) {
							$xml_image = $xml->createElement("image:image");
							$xml_url->appendChild($xml_image);

							if ($this->config->get('module_mpsitemapfeed_resize_image')) {
								$xml_image->appendChild($xml->createElement("image:loc", $this->model_tool_image->resize($blogcategory['image'], (int)$this->config->get('module_mpsitemapfeed_image_width'), (int)$this->config->get('module_mpsitemapfeed_image_height'))));
							} else {
								$xml_image->appendChild($xml->createElement("image:loc", $this->server . $blogcategory['image']));
							}
						}
					}
				}

				$file_name = 'sitemaps/blogcategory-sitemap'. (($page - 1) ? ($page - 1) : '')  . '.xml';
				$file_to_save = $this->path_application . $file_name;

				$xml->save($file_to_save);
			}

			return ($page - 1);
		}
	}

	public function information() {
		if ($this->config->get('module_mpsitemapfeed_information_status')) {
			list($xml,$xml_urlset) = $this->generateXML('information');

			$informations = [];
			if ($this->config->get('module_mpsitemapfeed_information_ids')) {
				$information_ids = $this->config->get('module_mpsitemapfeed_information_ids');
				foreach ($information_ids as $information_id) {
					$information_datas = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getInformation($information_id);
					if ($information_datas) {
						$informations[] = $information_datas;
					}
				}
			} else {
				$informations = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getInformations();
			}

			foreach ($informations as $information) {
				$xml_url = $xml->createElement("url");
				$xml_urlset->appendChild($xml_url);

				$xml_url->appendChild($xml->createElement("loc", $this->url->link('information/information', 'information_id=' . $information['information_id'])));
				$old_language_id = (int)$this->config->get('config_language_id');
				if ($this->config->get('module_mpsitemapfeed_information_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {

					foreach ($this->getLanguages() as $language) {
						$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
						$xml_url->appendChild($xml_link);
						$xml_link->setAttributeNS('', 'rel', 'alternate');
						$xml_link->setAttributeNS('', 'hreflang', $language['code']);
						$this->config->set('config_language_id', $language['language_id']);
						$link = $this->server . 'index.php?route=information/information&information_id=' . $information['information_id'];
						$link = $this->load->controller('startup/seo_url/rewrite', $link);
						$xml_link->setAttributeNS('', 'href', $link);
					}
					$this->config->set('config_language_id', $old_language_id);
				}

				$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_information_frequency')));

				$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_information_priority')));
			}

			$file_name = 'sitemaps/information-sitemap.xml';
			$file_to_save = $this->path_application . $file_name;

			$xml->save($file_to_save);
		}
	}

	// 06-02-2022: 19:13 = main $xml te $xml_urlset to pass by reference kita.. chk krna jkr koi error aundi ya nhi..
	// pass by reference da fyda eh rhu k.. object di copy nhi bnu jdo function calling honi te memory ch har vari jagah nhi lwe ga har vri funtion call ch.
	protected function getCategories($parent_id, $xml, $xml_urlset, $current_path = '') {
		$results = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getCategories($parent_id);

		$result = $this->model_extension_mpsitemapfeed_mpsitemapfeed->getCategory($parent_id);

		if ($result) {
			if (!$current_path) {
				$current_path = $result['category_id'];
			}
			$this->getCategory($result, $xml, $xml_urlset, $current_path);
		}
		if ($results) {
			foreach ($results as $result) {
				if ($current_path) {
					$new_path = $current_path . '_' . $result['category_id'];
				} else {
					$new_path = $result['category_id'];
				}
				$this->getCategories($result['category_id'], $xml, $xml_urlset, $new_path);
			}
		}
	}

	protected function getCategory($result, $xml, $xml_urlset, $new_path = '') {
		$xml_url = $xml->createElement("url");
		$xml_urlset->appendChild($xml_url);

		$xml_url->appendChild($xml->createElement("loc", $this->url->link('product/category', 'path=' . $new_path)));
		$old_language_id = (int)$this->config->get('config_language_id');
		if ($this->config->get('module_mpsitemapfeed_category_multilangurl') && ($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
			foreach ($this->getLanguages() as $language) {
				$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
				$xml_url->appendChild($xml_link);
				$xml_link->setAttributeNS('', 'rel', 'alternate');
				$xml_link->setAttributeNS('', 'hreflang', $language['code']);
				$this->config->set('config_language_id', $language['language_id']);
				$link = $this->server . 'index.php?route=product/category&path=' . $new_path;
				$link = $this->load->controller('startup/seo_url/rewrite', $link);
				$xml_link->setAttributeNS('', 'href', $link);
			}
			$this->config->set('config_language_id', $old_language_id);
		}

		$xml_url->appendChild($xml->createElement("changefreq", $this->config->get('module_mpsitemapfeed_category_frequency')));

		$xml_url->appendChild($xml->createElement("priority", $this->config->get('module_mpsitemapfeed_category_priority')));

		$xml_url->appendChild($xml->createElement("lastmod", date('Y-m-d h:i:s', strtotime($result['date_modified']))));

		if ($this->config->get('module_mpsitemapfeed_image_status') && !empty($result['image']) && file_exists(DIR_IMAGE . $result['image'])) {
			$xml_image = $xml->createElement("image:image");
			$xml_url->appendChild($xml_image);

			if ($this->config->get('module_mpsitemapfeed_resize_image')) {
				$xml_image->appendChild($xml->createElement(
					"image:loc",
					$this->model_tool_image->resize($result['image'], (int)$this->config->get('module_mpsitemapfeed_image_width'), (int)$this->config->get('module_mpsitemapfeed_image_height'))
				));
			} else {
				$xml_image->appendChild($xml->createElement("image:loc", $this->server . $result['image']));
			}
		}
	}

	public function customlink() {
		if ($this->config->get('module_mpsitemapfeed_custom_link_status')) {
			$xml = new \DOMDocument('1.0', 'UTF-8');

			$xml->preserveWhiteSpace = false;
			$xml->formatOutput=true;

			//creating an xslt adding processing line
			$xslt = $xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'. $this->server  . 'sitemaps/sitemap.xsl"');

			//adding it to the xml
			$xml->appendChild($xslt);

			$xml_urlset = $xml->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
			$xml->appendChild($xml_urlset);
			$xml_urlset->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
			$xml_urlset->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

			$custom_links = $this->config->get('module_mpsitemapfeed_custom_link');

			$chunk_custom_links = array_chunk($custom_links, $this->config->get('module_mpsitemapfeed_limit'));

			foreach ($custom_links as $custom_link) {
				$xml_url = $xml->createElement("url");
				$xml_urlset->appendChild($xml_url);

				if (!isset($custom_link['url'][(int)$this->config->get('config_language_id')])) {
					continue;
				}
				$xml_url->appendChild($xml->createElement("loc", $custom_link['url'][(int)$this->config->get('config_language_id')]));

				if (($this->total_language > 1 || true) && $this->config->get('config_seo_url')) {
					foreach ($this->getLanguages() as $language) {
						if ($custom_link['url'][$language['language_id']]) {
							$xml_link = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'link');
							$xml_url->appendChild($xml_link);
							$xml_link->setAttributeNS('', 'rel', 'alternate');
							$xml_link->setAttributeNS('', 'hreflang', $language['code']);
							$xml_link->setAttributeNS('', 'href', $custom_link['url'][$language['language_id']]);
						}
					}
				}

				$xml_url->appendChild($xml->createElement("changefreq", $custom_link['frequency']));

				$xml_url->appendChild($xml->createElement("priority", $custom_link['priority']));
			}

			$file_name = 'sitemaps/customlink-sitemap.xml';
			$file_to_save = $this->path_application . $file_name;

			$xml->save($file_to_save);
		}
	}

	protected function getsiteindexxsl() {
		if (is_file($this->path_application . 'sitemaps/sitemapindex.xsl')){
			$output = file_get_contents($this->path_application . 'sitemaps/sitemapindex.xsl');
		} else {
			$content = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:template match="/">
			  <html xmlns="http://www.w3.org/1999/xhtml">
			  <head>
			      <title>XML Sitemap Feed</title>
			      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			      <style type="text/css">body{font-family:Arial;font-size:12pt;}table thead tr th{background-color:#eee;}td{font-size:12px;padding: 6px 12px;text-align:left;}th{font-size:12px;padding: 6px 12px;text-align:center;}a{color:#2f2f2f}tr:nth-child(2n){background-color:#eee;}</style>
			    </head>
			  <body>
			  <h2>XML Sitemap Feed</h2>
			    <table cellpadding="5">
			      <thead>
			        <tr>
			          <th>#</th>
			          <th>Sitemap</th>
			          <th>Modified</th>
			        </tr>
			      </thead>
			      <tbody>
			      <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
			      <tr>
			        <td><xsl:value-of select="position()"/></td>
			        <td><xsl:variable name="loc" select="sitemap:loc"/><a href="{$loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
			        <td><xsl:value-of select="sitemap:lastmod"/></td>
			      </tr>
			      </xsl:for-each>
			      </tbody>
			    </table>
			  </body>
			  </html>
			</xsl:template>
			</xsl:stylesheet>';

			file_put_contents($this->path_application . 'sitemaps/sitemapindex.xsl', $content);
			$output = file_get_contents($this->path_application . 'sitemaps/sitemapindex.xsl');
		}

		return $output;
	}

	protected function getsitemapxsl() {
		if (is_file($this->path_application . 'sitemaps/sitemap.xsl')){
			$output = file_get_contents($this->path_application . 'sitemaps/sitemap.xsl');
		} else {
			$content = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:template match="/">
			  <html xmlns="http://www.w3.org/1999/xhtml">
			  <head>
			      <title>XML Sitemap Feed</title>
			      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			      <style type="text/css">body{font-family:Arial;font-size:12pt;}table thead tr th{background-color:#eee;}td{font-size:12px;padding: 6px 12px;text-align:left;}th{font-size:12px;padding: 6px 12px;text-align:center;}a{color:#2f2f2f}tr:nth-child(2n){background-color:#eee;}</style>
			    </head>
			  <body>
			  <h2>XML Sitemap Feed</h2>
			    <table cellpadding="5">
			      <thead>
			        <tr>
			          <th>#</th>
			          <th>URL</th>
			          <th>Images</th>
			          <th>Priority</th>
			          <th>Frequency</th>
			          <th>Modified</th>
			        </tr>
			      </thead>
			      <tbody>
			      <xsl:for-each select="sitemap:urlset/sitemap:url">
			      <tr>
			        <td><xsl:value-of select="position()"/></td>
			        <td><xsl:variable name="loc" select="sitemap:loc"/><a href="{$loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
			        <td><xsl:value-of select="count(image:image)"/></td>
			        <td><xsl:value-of select="concat(sitemap:priority*100,\'%\')"/></td>
			        <td style="text-transform:capitalize;"><xsl:value-of select="sitemap:changefreq"/></td>
			        <td><xsl:value-of select="sitemap:lastmod"/></td>
			      </tr>
			      </xsl:for-each>
			      </tbody>
			    </table>
			  </body>
			  </html>
			</xsl:template>
			</xsl:stylesheet>';

			file_put_contents($this->path_application . 'sitemaps/sitemap.xsl', $content);
			$output = file_get_contents($this->path_application . 'sitemaps/sitemap.xsl');
		}

		return $output;
	}
}
