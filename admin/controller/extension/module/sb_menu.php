<?php
class ControllerExtensionModuleSbMenu extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/sb_menu');
		$this->load->model('setting/module');

		$this->document->setTitle($this->language->get('module_title'));

		if (!isset($this->request->get['module_id'])) {
			$data['apply_btn'] = false;
		} else {
			$data['apply_btn'] = true;
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('sb_menu', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			if ($this->request->post['apply']) {
				$this->session->data['success'] = $this->language->get('text_apply');
				$this->response->redirect($this->url->link('extension/module/sb_menu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));
			} else {
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['default_store'] = $this->config->get('config_name');
		$data['version'] = '3.0';

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->get['module_id'])) {
			$data['module_id'] = $this->request->get['module_id'];
		} else {
			$data['module_id'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('page_title'),
				'href' => $this->url->link('extension/module/sb_menu', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('page_title'),
				'href' => $this->url->link('extension/module/sb_menu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/sb_menu', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/sb_menu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['style'])) {
			$data['style'] = $this->request->post['style'];
		} elseif (!empty($module_info)) {
			$data['style'] = $module_info['style'];
		} else {
			$data['style'] = '';
		}

		$data['sb_module'] = array();

		if (isset($this->request->post['sb_module'])) {
			$data['sb_module'] = $this->request->post['sb_module'];
		} elseif (!empty($module_info)) {
			$data['sb_module'] = $module_info['sb_module'];
		} else {
			$data['sb_module']['store_id'][] = 0;
			$data['sb_module']['all_customers'] = 1;
			$data['sb_module']['allcats'] = 1;
			$data['sb_module']['cat_invert'] = 1;
		}

		$data['sbmenu'] = array();

		if (isset($this->request->post['sbmenu'])) {
			$data['sbmenu'] = $this->request->post['sbmenu'];
		} elseif (!empty($module_info)) {
			$data['sbmenu'] = $module_info['sbmenu'];
		} else {
			$data['sbmenu']['allcats'] = 1;
			$data['sbmenu']['allbrands'] = 1;
			$data['sbmenu']['level_2'] = 1;
			$data['sbmenu']['level_3'] = 1;
			$data['sbmenu']['item_image'] = 1;
			$data['sbmenu']['item_icon'] = 1;
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('catalog/category');
		$data['categories'] = array();

		if (!empty($this->request->post['sbmenu']['featured_category'])) {
			$categories = $this->request->post['sbmenu']['featured_category'];
		} elseif (!empty($module_info) && !empty($module_info['sbmenu']['featured_category'])) {
			$categories = $module_info['sbmenu']['featured_category'];
		} else {
			$categories = array();
		}

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => $category_info['path'] ? $category_info['path'] . ' - &gt; ' . $category_info['name'] : $category_info['name']
					);
			}
		}

		$data['catlocs'] = array();

		if (!empty($this->request->post['sb_module']['fcid'])) {
			$catlocs = $this->request->post['sb_module']['fcid'];
		} elseif (!empty($module_info) && !empty($module_info['sb_module']['fcid'])) {
			$catlocs = $module_info['sb_module']['fcid'];
		} else {
			$catlocs = array();
		}

		foreach ($catlocs as $category) {
			$category_info = $this->model_catalog_category->getCategory($category);

			if ($category_info) {
				$data['catlocs'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => $category_info['path'] ? $category_info['path'] . ' - &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = array();

		if (!empty($this->request->post['sbmenu']['featured_brands'])) {
			$manufacturers = $this->request->post['sbmenu']['featured_brands'];
		} elseif (!empty($module_info) && !empty($module_info['sbmenu']['featured_brands'])) {
			$manufacturers = $module_info['sbmenu']['featured_brands'];
		} else {
			$manufacturers = array();
		}

		foreach ($manufacturers as $brand_id) {
			$brand_info = $this->model_catalog_manufacturer->getManufacturer($brand_id);

			if ($brand_info) {
				$data['manufacturers'][] = array(
					'manufacturer_id' => $brand_info['manufacturer_id'],
					'name'            => $brand_info['name']
					);
			}
		}

		$this->load->model('sbmenu/sb_menu');
		$data['customer_groups'] = $this->model_sbmenu_sb_menu->getCustomerGroups();

		$data['sorts'] = array();

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_default_asc'),
			'value' => 'p.sort_order-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_viewed_asc'),
			'value' => 'p.viewed-DESC'
			);

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = array(
				'name'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC'
				);
		}

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_date_desc'),
			'value' => 'p.date_added-DESC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC'
			);

		$data['easings'] = array('linear', 'swing', 'easeInQuad', 'easeOutQuad', 'easeInOutQuad', 'easeInCubic', 'easeOutCubic', 'easeInOutCubic', 'easeInQuart', 'easeOutQuart', 'easeInOutQuart', 'easeInQuint', 'easeOutQuint', 'easeInOutQuint', 'easeInExpo', 'easeOutExpo', 'easeInOutExpo', 'easeInSine', 'easeOutSine', 'easeInOutSine', 'easeInCirc', 'easeOutCirc', 'easeInOutCirc', 'easeInElastic', 'easeOutElastic', 'easeInOutElastic', 'easeInBack', 'easeOutBack', 'easeInOutBack', 'easeInBounce', 'easeOutBounce', 'easeInOutBounce');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/sb_menu', $data));
	}

	public function install() {
		$image = $this->db->query("DESC `" . DB_PREFIX . "category` `sbmenu_image`");
		if (!$image->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `sbmenu_image` varchar(255) NULL AFTER image");
		}

		$icon = $this->db->query("DESC `" . DB_PREFIX . "category` `sbmenu_icon`");
		if (!$icon->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `sbmenu_icon` varchar(255) NULL AFTER image");
		}

		$content = $this->db->query("DESC `" . DB_PREFIX . "category` `sbmenu_content`");
		if (!$content->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `sbmenu_content` TEXT NOT NULL");
		}

		$brand_image = $this->db->query("DESC `" . DB_PREFIX . "manufacturer` `sbmenu_image`");
		if (!$brand_image->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `sbmenu_image` varchar(255) NULL AFTER image");
		}

		$brand_icon = $this->db->query("DESC `" . DB_PREFIX . "manufacturer` `sbmenu_icon`");
		if (!$brand_icon->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `sbmenu_icon` varchar(255) NULL AFTER image");
		}

		$brand_content = $this->db->query("DESC `" . DB_PREFIX . "manufacturer` `sbmenu_content`");
		if (!$brand_content->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `sbmenu_content` TEXT NOT NULL");
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/sb_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}