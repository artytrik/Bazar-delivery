<?php
class ControllerExtensionModuleOcTabProducts extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/octabproducts');
				
		$this->load->model('catalog/product');
		$this->load->model('extension/module/ocproduct');

		$this->load->model('tool/image');

		$this->load->model('localisation/language');
		
		$data['code'] = $this->session->data['language'];
		
		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
		
		if(isset($setting['module_image'])) {
			$thumb = $this->model_tool_image->resize($setting['module_image'], 270, 450);
		} else {
			$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['octabs'] = array();
		foreach($setting['octab'] as $octab){
			$results = array();
			if($octab['option'] == 0) {
				if (!empty($octab['productall'])) {
					$products = array_slice($octab['productall'], 0, (int)$setting['limit']);
					foreach ($products as $product_id) {
						$results[] = $this->model_catalog_product->getProduct($product_id);
					}
				}
			} else if ($octab['option']==1){
				if($octab['cate_id']) {
					if($octab['productfrom']==1){
						$data['filter_category_id'] = $octab['cate_id'];
						$results = $this->model_catalog_product->getProducts($data);
						
					} else if($octab['productfrom']==0) {
						if (!empty($octab['productcate'])) {
							$products = array_slice($octab['productcate'], 0, (int)$setting['limit']);
							foreach ($products as $product_id) {
								$results[] = $this->model_catalog_product->getProduct($product_id);
							}
						}else{
							$results = '';
						}		
					} else {
						if ($octab['input_specific_product']==0){
							$filter_data = array(
								'filter_category_id' => $octab['cate_id'],
								'sort'  => 'p.date_added',
								'order' => 'DESC',
								'start' => 0,
								'limit' => $setting['limit'],
							);
							$results = $this->model_catalog_product->getProducts($filter_data);
								
						} else if ($octab['input_specific_product']==1){
							$filter_data = array(
							'sort'  => 'pd.name',
							'order' => 'ASC',
							'start' => 0,
							'limit' => $setting['limit']
							);
							$results = $this->model_extension_module_ocproduct->getProductSpecialsCategory($filter_data, $octab['cate_id']);		
						} else if ($octab['input_specific_product']==2){
							$results = $this->model_extension_module_ocproduct->getBestSellerProductsCategory($setting['limit'], $octab['cate_id']);				
						} else if ($octab['input_specific_product']==3){
							$results = $this->model_extension_module_ocproduct->getMostViewedProductsCategory($setting['limit'],  $octab['cate_id']);		
						} else {
							$results = $this->model_extension_module_ocproduct->getProductsDealCategory($setting['limit'],  $octab['cate_id']);	
						}
					}
				}else{
					$results = '';
				}
				
		
			} else {
				if ($octab['autoproduct']==0){

					$filter_data = array(
						'sort'  => 'p.date_added',
						'order' => 'DESC',
						'start' => 0,
						'limit' => $setting['limit']
					);
					$results = $this->model_catalog_product->getProducts($filter_data);
						
				} else if ($octab['autoproduct']==1){
					$filter_data = array(
					'sort'  => 'pd.name',
					'order' => 'ASC',
					'start' => 0,
					'limit' => $setting['limit']
					);

					$results = $this->model_catalog_product->getProductSpecials($filter_data);
						
				} else if ($octab['autoproduct']==2){
					$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
					
				} else if ($octab['autoproduct']==3){
					$results = $this->model_catalog_product->getPopularProducts($setting['limit']);		
					
				} else {
					$results = $this->model_extension_module_ocproduct->getDealProducts($setting['limit']);	
				}
			}

			$store_id = $this->config->get('config_store_id');

			$data['use_quickview'] = (int) $this->config->get('module_octhemeoption_quickview')[$store_id];
			$data['use_catalog'] = (int) $this->config->get('module_octhemeoption_catalog')[$store_id];

			$product_rotator_status = (int) $this->config->get('module_octhemeoption_rotator')[$store_id];

			/* Get new product */
			$this->load->model('catalog/product');

			$filter_data = array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => 10
			);

			$new_results = $this->model_catalog_product->getProducts($filter_data);
			/* End */
			
			
			$first_products = $this->getFirstProduts($results);
					
			$excp_frs_products = $this->getOtherExcpFirstProducts($results);
			
			$product_data = array();
			
			$first_products_data = array();
			
			$excp_frs_products_data = array();
			
			if ($excp_frs_products) {
				foreach ($excp_frs_products as $result) {
					$text_percent ='';
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$price_num = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = false;
						$price_num = false;
					}
					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$special_num = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$special = false;
						$special_num = false;
					}
					
					if ($special_num && $price_num) {
						$percent =  round(($price_num-$special_num)/$price_num*100,0);
						$text_percent =  sprintf($this->language->get('text_label_sale'), $percent);
					}
					
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}
					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}
					$date_end = false;
					if ($setting['countdown']){
						$date_end = $this->model_extension_module_ocproduct->getSpecialCountdown($result['product_id']);
						if ($date_end === '0000-00-00') {
							$date_end = false;
						}
					}
					/// Product Rotator /
	                if($product_rotator_status == 1) {
	                 $this->load->model('catalog/ocproductrotator');
	                 $this->load->model('tool/image');
	                 $product_id = $result['product_id'];
	                 $product_rotator_image = $this->model_catalog_ocproductrotator->getProductRotatorImage($product_id);
	                 if($product_rotator_image) {
					  $rotator_image = $this->model_tool_image->resize($product_rotator_image, $setting['width'],$setting['height']); 
	                 } else {
	                  $rotator_image = false;
	                 } 
	                } else {
	                 $rotator_image = false;    
	                }
	                /// End Product Rotator /
					$is_new = false;
					if ($new_results) { 
						foreach($new_results as $new_r) {
							if($result['product_id'] == $new_r['product_id']) {
								$is_new = true;
							}
						}
					}
					$excp_frs_products_data[] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'text_percent'       => $text_percent,
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'date_end'    => $date_end,
						'is_new'      => $is_new,
						'rotator_image' => $rotator_image,
						'manufacturer' => $result['manufacturer'],
						'manufacturers' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
					);
				}
			}
			
			if ($first_products) {
				foreach ($first_products as $result) {
					$text_percent ='';
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$price_num = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = false;
						$price_num = false;
					}
					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$special_num = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$special = false;
						$special_num = false;
					}
					
					if ($special_num && $price_num) {
						$percent =  round(($price_num-$special_num)/$price_num*100,0);
						$text_percent =  sprintf($this->language->get('text_label_sale'), $percent);
					}
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}
					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}
					$date_end = false;
					if ($setting['countdown']){
						$date_end = $this->model_extension_module_ocproduct->getSpecialCountdown($result['product_id']);
						if ($date_end === '0000-00-00') {
							$date_end = false;
						}
					}
					/// Product Rotator /
	                if($product_rotator_status == 1) {
	                 $this->load->model('catalog/ocproductrotator');
	                 $this->load->model('tool/image');
	                 $product_id = $result['product_id'];
	                 $product_rotator_image = $this->model_catalog_ocproductrotator->getProductRotatorImage($product_id);
	                 if($product_rotator_image) {
					  $rotator_image = $this->model_tool_image->resize($product_rotator_image, $setting['width'],$setting['height']); 
	                 } else {
	                  $rotator_image = false;
	                 } 
	                } else {
	                 $rotator_image = false;    
	                }
	                /// End Product Rotator /
					$is_new = false;
					if ($new_results) { 
						foreach($new_results as $new_r) {
							if($result['product_id'] == $new_r['product_id']) {
								$is_new = true;
							}
						}
					}
					$first_products_data[] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'text_percent'       => $text_percent,
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'date_end'    => $date_end,
						'is_new'      => $is_new,
						'rotator_image' => $rotator_image,
						'manufacturer' => $result['manufacturer'],
						'manufacturers' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
					);
				}
			}
			
			if ($results) {
				foreach ($results as $result) {
					$text_percent = '';
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$price_num = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = false;
						$price_num= false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$special_num = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$special = false;
						$special_num = false;
					}
					
					if ($special_num && $price_num) {
						$percent =  round(($price_num-$special_num)/$price_num*100,0);
						$text_percent =  sprintf($this->language->get('text_label_sale'), $percent);
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}
					$date_end = false;
					if ($setting['countdown']){
						$date_end = $this->model_extension_module_ocproduct->getSpecialCountdown($result['product_id']);
						if ($date_end === '0000-00-00') {
							$date_end = false;
						}
					}
					/// Product Rotator /
	                if($product_rotator_status == 1) {
	                 $this->load->model('catalog/ocproductrotator');
	                 $this->load->model('tool/image');
	             
	                 $product_id = $result['product_id'];
	                 $product_rotator_image = $this->model_catalog_ocproductrotator->getProductRotatorImage($product_id);
	             
	                 if($product_rotator_image) {
					  $rotator_image = $this->model_tool_image->resize($product_rotator_image, $setting['width'],$setting['height']); 
	                 } else {
	                  $rotator_image = false;
	                 } 
	                } else {
	                 $rotator_image = false;    
	                }
	                /// End Product Rotator /

					$is_new = false;
					if ($new_results) { 
						foreach($new_results as $new_r) {
							if($result['product_id'] == $new_r['product_id']) {
								$is_new = true;
							}
						}
					}
					$product_data[] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'text_percent'  => $text_percent,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'date_end'    => $date_end,
						'is_new'      => $is_new,
						'rotator_image' => $rotator_image,
						'manufacturer' => $result['manufacturer'],
						'manufacturers' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
					);
					
				}
				
			}
			$data['thumb']    = $thumb;
			if(isset($octab['tab_name'][$data['code']]['title'])) {
			$title = $octab['tab_name'][$data['code']]['title'];
			} else {
			$title = 'Tab title';
			}
			$data['octabs'][] = array(
			'products' => $product_data,
			'title'    => $title,
			'excpFrsProducts'	=> $excp_frs_products_data,
				'frsProducts'		=> $first_products_data
			);
		}
		$number_random = rand ( 1 , 1000 );
		$data['config_module'] = array(
				'name' => $setting['name'],
				'class' => $setting['class'],
				'type' => (int) $setting['type'],
				'slider' => (int) $setting['slider'],
				'auto' =>(int) $setting['auto'],
				'loop' =>(int) $setting['loop'],
				'margin' =>(int) $setting['margin'],
				'nrow' =>(int) $setting['nrow'],
				'items' =>(int) $setting['items'],
				'time' =>(int) $setting['time'],
				'speed' =>(int) $setting['speed'],
				'row' =>(int) $setting['row'],
				'navigation' =>(int) $setting['navigation'],
				'pagination' =>(int) $setting['pagination'],
				'desktop' => (int)$setting['desktop'],
				'tablet' => (int)$setting['tablet'],
				'mobile' => (int)$setting['mobile'],
				'smobile' =>(int) $setting['smobile'],
				'title_lang' => $setting['title_lang'],
				'description' => (int)$setting['description'],
				'countdown' => (int)$setting['countdown'],
				'rotator'  => (int)$setting['rotator'],
				'newlabel'  => (int)$setting['newlabel'],
				'salelabel'  => (int)$setting['salelabel'],
				'module_id' => $number_random
			);
			if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
				$data['module_description'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
				if ($data['module_description'] == '<p><br><p>') $data['module_description']= '';
		   }
		if ($data['octabs']) {
			return $this->load->view('extension/module/octabproducts', $data);
		}
		
	}
	
	public function getFirstProduts($products) {
		$trdProduct = array();
		$count = 0;
		foreach($products as $product) {
			if($count < 1) {
				$product_id = $product['product_id'];
				$trdProduct[] = $product;
			}
			$count++;
		}
		
		return $trdProduct;
	}
	
	public function getOtherExcpFirstProducts($products) {
		$excpTrdProducts = array();
		
		$count = 0;
		foreach($products as $product) {
			if($count >= 1) {
				$excpTrdProducts[] = $product;
			}
			$count++;
		}
		
		return $excpTrdProducts;
	}
	
	
}