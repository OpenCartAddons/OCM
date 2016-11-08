<?php
class ControllerAccountExtension extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/extension', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/extension');

		$this->document->setTitle($data['heading_title'));

		$this->load->model('account/extension');

		$this->getList();
	}

	public function add() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/extension', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/extension');

		$this->document->setTitle($data['heading_title'));

		$this->load->model('account/extension');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_extension->addExtension($this->request->post);
			
			$this->session->data['success'] = $data['text_add');

			// Add to activity log
			if ($this->config->get('config_customer_activity')) {
				$this->load->model('account/activity');

				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
				);

				$this->model_account_activity->addActivity('extension_add', $activity_data);
			}

			$this->response->redirect($this->url->link('account/extension', '', true));
		}

		$this->getForm();
	}

	public function edit() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/extension', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/extension');

		$this->document->setTitle($data['heading_title'));

		$this->load->model('account/extension');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_extension->editExtension($this->request->get['extension_id'], $this->request->post);

			$this->session->data['success'] = $data['text_edit');

			// Add to activity log
			if ($this->config->get('config_customer_activity')) {
				$this->load->model('account/activity');

				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
				);

				$this->model_account_activity->addActivity('extension_edit', $activity_data);
			}

			$this->response->redirect($this->url->link('account/extension', '', true));
		}

		$this->getForm();
	}

	protected function getList() {
		$data = array();
		$data = array_merge($data, $this->load->language('account/extension'));
		
		$data['breadcrumbs'] = array();    
		$data['breadcrumbs'][] = array(
			'text' => $data['text_home'],
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['text_account'],
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['heading_title'],
			'href' => $this->url->link('account/extension', '', true)
		);
		
		$data['success']		= isset($this->session->data['success']) ? $this->session->data['success'] : '';
		$data['error_warning'] 	= isset($this->error['warning']) ? $this->error['warning'] : '';

		$data['extensions'] = array();

		$results = $this->model_account_extension->getExtensions();

		foreach ($results as $result) {
			$data['extensions'][] = array(
				'extension_id' 	=> $result['extension_id'],
				'name'    		=> $result['name'],
				'status'     	=> ($result['status']) ? $data['text_enabled'] : $data['text_disabled'],
				'date_added'    => date($data['date_format_short'], strtotime($result['date_added'])),
				'view'     		=> $this->url->link('extension/extension', 'extension_id=' . $result['extension_id'], true),
				'edit'     		=> $this->url->link('account/extension/edit', 'extension_id=' . $result['extension_id'], true)
			);
		}

		$data['add'] = $this->url->link('account/extension/add', '', true);
		$data['back'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/extension_list', $data));
	}

	protected function getForm() {
		$data = array();
		$data = array_merge($data, $this->load->language('account/extension'));
    
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $data['text_home'],
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['text_account'],
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['heading_title'],
			'href' => $this->url->link('account/extension', '', true)
		);

		if (!empty($this->request->get['extension_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $data['text_edit_extension'],
				'href' => $this->url->link('account/extension/add', '', true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $data['text_edit_extension'],
				'href' => $this->url->link('account/extension/edit', '', true)
			);
		}
		
		if (!empty($this->error)) {
			foreach ($this->error as $key => $value) {
				$data['error_' . $key] = $value;
			}
		}
		
		if (!isset($this->request->get['extension_id'])) {
			$data['action'] = $this->url->link('account/extension/add', '', true);
		} else {
			$data['action'] = $this->url->link('account/extension/edit', 'extension_id=' . $this->request->get['extension_id'], true);
		}

		if (isset($this->request->get['extension_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$extension_info = $this->model_account_extension->getExtension($this->request->get['extension_id']);
		}
		
		$fields = array('name', 'description', 'category', 'license', 'license_period', 'price', 'price_renewal', 'tag', 'thumbnail', 'banner', 'image', 'documentation', 'changelog', 'download', 'status', 'update');
		foreach ($fields as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (!empty($extension_info[$field])) {
				$data[$field] = $extension_info[$field];
			} else {
				$data[$field] = '';
			}
		}
		
		$this->load->model('extension/category');
		$data['categories'] 		= $this->model_extension_category->getCategories(0);
		
		$data['versions'] 			= explode(',', $this->config->get('config_extension_versions'));
		$data['license_types'] 		= explode(',', $this->config->get('config_extension_license_types'));
		$data['license_periods']	= explode(',', $this->config->get('config_extension_license_periods'));

		$data['back'] 				= $this->url->link('account/extension', '', true);

		$data['column_left'] 		= $this->load->controller('common/column_left');
		$data['column_right'] 		= $this->load->controller('common/column_right');
		$data['content_top'] 		= $this->load->controller('common/content_top');
		$data['content_bottom'] 	= $this->load->controller('common/content_bottom');
		$data['footer'] 			= $this->load->controller('common/footer');
		$data['header'] 			= $this->load->controller('common/header');


		$this->response->setOutput($this->load->view('account/extension_form', $data));
	}

	protected function validateForm() {
		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > $this->config->get('config_extension_name'))) {
			$this->error['name'] = $data['error_name'];
		}
		
		if ($this->request->post['price'] < $this->config->get('config_extension_price')) {
			$this->error['price'] = $data['error_price'];
		}
		
		foreach (array('description', 'category', 'license', 'license_period', 'price_renewal', 'download') as $param) {
			if (empty($this->request->post[$param])) {
				$this->error[$param] = $data['error_' . $param];
			}
		}
		
		$tags = explode(',', $this->request->post['tag']);
		if (count($tags) > $this->config->get('config_extension_tag_count')) {
			$this->error['tags'] = sprintf($data['error_tag_count'], $this->config->get('config_extension_tag_count'));
		} else {
			foreach ($tags as $tag) {
				if ((utf8_strlen(trim($tag)) < 1) || (utf8_strlen(trim($tag)) > $this->config->get('config_extension_tag_char'))) {
					$this->error['tag'] = sprintf($data['error_tag_char'], $this->config->get('config_extension_tag_char'));
					break;
				}
			}
		}

		return !$this->error;
	}
}