<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Controller extends CI_Controller
{


	protected $data;        // global variable that will contain all data to be sent to view
	protected $json;        // variable to hold data array if responding to request with data array
	protected $view = NULL;     // view file


	public function __construct()
	{
		parent::__construct();

		// load in sentry
		require_once( APPPATH . '/third_party/sentry-raven.php' );

		// Set the version number for layout/display - stores in config
		$this->_set_version();

		// header to stop browser caching
		if ($this->session->userdata('u_id') == 148)
		{
			header("cache-Control: no-store, no-cache, must-revalidate");
			header("cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		}

		// Make quick shortcut for checking for AJAX requests
		define('IS_AJAX', $this->input->is_ajax_request());


		// Configure default page items and load the layout
		$js = array('//code.jquery.com/jquery-1.10.2.js', 'plugins/jquery.simplemodal.1.4.4.min', '//code.jquery.com/ui/1.11.0/jquery-ui.js', 'views/default');
		$css = array('global', 'skeleton', 'default', '//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css');
		$template = 'default';

		// If print view is desired
		if ($this->input->get('print') == 'yes')
		{
			$template = 'print';
			$css[] = 'print';
			$this->layout->set_title( $this->config->item('site_name') );
		}

		// set layout
		$this->layout->set_css($css)
					 ->set_js($js)
					 ->set_template($template);


		// load cache driver (dummy driver for development environment)
		$this->load->driver('cache', array(
			'adapter' => (ENVIRONMENT === 'development') ? 'dummy' : 'file'
		));

		//nav for anyone
		$nav = $this->config->item('site_nav');
		$hubkey = $this->config->item('site_nav_hubkey');
		if ($this->auth->is_role('Admin'))
		{
			$nav[$hubkey]['Admin'] = '/admin';
		}
		if ($this->auth->is_logged_in())
		{
			$nav[$hubkey]['My orders'] = '/order';
			$nav[$hubkey]['My account'] = '/members/account';
			$nav[$hubkey]['My buying group'] = '/members/group';
			$nav[$hubkey]['Logout'] = '/home/logout';
		}
		else
		{
			$nav[$hubkey]['Login'] = '/home/login';
			$nav[$hubkey]['Register'] = '/home/register';
		}

		$this->layout->set_nav($nav);
		$this->data['nav'] = $nav;

		// Allow the profiler to be shown using the GET var if we're in dev mode
		if (ENVIRONMENT === 'development' && $this->input->get('profiler'))
		{
			$this->output->enable_profiler(TRUE);
		}

		// Load the activity log model under the alias of logger
		$this->load->model('activity_log_model', 'logger');

	}




	/**
	 * Remap the CI request, running the requested method and (auto-)loading the view
	 */
	public function _remap($method, $arguments)
	{
		if (method_exists($this, $method))
		{
			// Requested method exists in the class - run it
			call_user_func_array(array($this, $method), array_slice($this->uri->rsegments, 2));
		}
		else
		{
			call_user_func_array(array($this, 'index'), array_slice($this->uri->rsegments, 1)); //use the index
		}

		// The class function has ran, done its stuff and set $this->data vars...
		// ... now auto-load the view.
		$this->_load_view();
	}




	/**
	 * Auto-load the view based on path, or if $view is FALSE, don't!
	 */
	private function _load_view()
	{
		// Back out if we've explicitly set the view to FALSE
		if ($this->view === FALSE)
		{
			return;
		}

		// If the JSON data is set, respond with JSON data instead of whole page
		if (is_array($this->json))
		{
			$this->output->set_content_type('text/json');
			$this->output->set_output(json_encode($this->json));
			return;
		}

		// Get or automatically set the view and layout name
		$view = ($this->view === NULL)
			? $this->router->directory . $this->router->class . '/' . $this->router->method
			: $this->view;

		if (file_exists(APPPATH . '/views/' . $view . '.php'))
		{
			// Load the view in to the $view variable that will be available in the layout
			$this->data['view'] = $this->load->view($view, $this->data, TRUE);
		}
		else
		{
			// View file not found - show error message
			$this->data['view'] = $this->load->view('partials/flash_message', array(
				'type' => 'error',
				'text' => 'System error: view file <strong>/' . $view . '</strong> not found.',
			), TRUE);
		}

		// Load the variables from $this->data so they can be accessed in the layout view
		$this->load->vars($this->data);

		// Finally load the template as the final view - it should echo $view
		$this->load->view($this->layout->get_template());
	}




	private function _set_version()
	{
		if (ENVIRONMENT === 'development')
		{
			// Locally, just use time
			$num = time() . "D";
		}
		else
		{
			// Get version number from file
			$contents = file_get_contents('../.svn_revision', FALSE, NULL, -1, 3);
			$num = (int) $contents;
			$num = ($num === 0) ? time() : $num;
		}

		$this->config->set_item('version', $num);
	}


}






/**
 * Admin controller to set up specific navigation
 *
 * Extend this controller for all global administration functions.
 */
class Admin_controller extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->auth->is_role('Admin'))
		{
			if (!$this->auth->is_logged_in())
			{
				$this->flash->set('error', 'You need to logged in as an admin to use this part of the website.', TRUE);
				$this->session->set_userdata('login_redirect', $this->uri->uri_string());
				return redirect('home/login');
			}
			else
			{
			//what if they are logged in as a standard user?
				show_error('You need to be an admin to use this part of the website.');
			}
		}

		//Set nav based on the user permissions
		$nav = array(
					'Food Hub'	=>	'/',
					'Admin' => array(
									'Admin Panel' => '/admin',
										),
					'Produce' => array(
										),
					'My Account' => array(
											'My account'	=> '/members/account',
											'Logout' 		=> '/home/logout',
											),
					'Logout' => 'home/logout',
					);
		//Admin subnav based on permissions
		if ( $this->auth->is_allowed_to('view_picking_lists', 'any') )
		{
			$nav['Admin']['Picking Lists'] = '/admin/picking-lists';
		}
		if ( $this->auth->is_allowed_to('view_member_bills', 'any') )
		{
			$nav['Admin']['Members\'s Bills'] = '/admin/bills';
		}
		if ( $this->auth->is_allowed_to('view_buying_groups', 'any') )
		{
			$nav['Admin']['Buying Groups'] = '/admin/groups';
		}
		if ( $this->auth->is_allowed_to('view_members', 'any') )
		{
			$nav['Admin']['Members &amp; Staff'] = '/admin/users';
		}
		if ( $this->auth->is_allowed_to('view_reports', 'any') )
		{
			$nav['Admin']['Reports'] = '/admin/reports';
		}
		//Produce subnav based on permissions
		if ( $this->auth->is_allowed_to('manage_products', 'any') )
		{
			$nav['Produce']['Produce'] = '/admin/products';
		}
		if ( $this->auth->is_allowed_to('manage_categories', 'any') )
		{
			$nav['Produce']['Categories'] = '/admin/categories';
		}
		if ( $this->auth->is_allowed_to('manage_suppliers', 'any') )
		{
			$nav['Produce']['Producers'] = '/admin/suppliers';
		}
		if ( $this->auth->is_allowed_to('manage_seasons', 'any') )
		{
			$nav['Produce']['Seasons'] = '/admin/seasons';
		}
		//clean up the top nav
		if ( $nav['Produce'] == Array() )
		{
			unset($nav['Produce']);
		}

		$this->layout->clear_nav();
		$this->layout->set_nav($nav);
		$this->data['nav'] = $nav;

		$this->layout->set_title('Admin')->set_breadcrumb('Admin', '/admin');
	}

}
