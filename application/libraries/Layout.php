<?php

class Layout {

	protected $_CI; // instance of ci framework

	protected $_template; // location and filename of template

	protected $_view; // location and filename of view

	protected $_title = array(); // value of <title></title>

	protected $_breadcrumbs = array(); // breadcrumbs

	protected $_js = array(); // array of javascript files to load in view

	protected $_css = array(); // array of css files to load in view

	protected $_ts = 232;

	protected $_meta = array(); // array of metadata to load in view

	protected $_nav = array(); // array of navigation links


	public function __construct()
	{
		// set instance of ci framework
		$this->_CI =& get_instance();
	}




	/**
	 * sets the value of $_template
	 *
	 * @param string $template		Set the template view file name to be used
	 */
	public function set_template($template)
	{
		$this->_template = $template;
		return $this;
	}




	/**
	 * returns the value of $_template including the path to the templates folder
	 *
	 * @return string
	 */
	public function get_template()
	{
		return 'templates/' . $this->_template;
	}




	/**
	 * Return only the name part of the template being used, without folder prefixed to it.
	 *
	 * @return string
	 */
	public function get_template_name()
	{
		return $this->_template;
	}




	/**
	 * Sets the value of $_view - the main view file to load for the content
	 */
	public function set_view($view)
	{
		$this->_view = $view;
		return $this;
	}




	/**
	 * Returns the value of $_view
	 */
	public function get_view()
	{
		return $this->_view;
	}




	/**
	 * Set array element to $_title
	 */
	public function set_title($title)
	{
		// is $title an array?
		if (is_array($title))
		{
			// loop through each title element and add it to $_title
			foreach($title as $title_element)
			{
				$this->_title[] = $title_element;
			}
		}
		else
		{
			$this->_title[] = $title;
		}

		return $this;
	}




	/**
	 * set title to a blank array
	 */
	public function clear_title()
	{
		$this->_title = array();
		return $this;
	}




	/**
	 * Takes $_title and returns it as a formatted string ready to use in <title></title>
	 */
	public function get_title($delimiter = '-')
	{
		// puts the array in reverse order so it reads like a breadcrumb
		$title_elements = array_reverse($this->_title);

		// glues $_title together and places the value of $delimiter in between each element of the array
		return implode(' ' . $delimiter . ' ', $title_elements);
	}




	/**
	 * Returns protected title array
	 */
	public function get_title_array()
	{
		return $this->_title;
	}




	/**
	 * Returns the last title in the $_title array
	 */
	public function get_last_title()
	{
		return end($this->_title);
	}




	// Set array element to $_js
	function set_js($js, $dir = 'scripts/')
	{
		// If the supplied $js var is not an array, make an array from it first
		if ( ! is_array($js))
		{
			$js = array($js);
		}

		// Loop through each $js element and add it to $_js
		foreach ($js as $js_element)
		{
			// Check if the file being added is remote or not.
			// Valid remote formats: http://example.com/file.js -OR- //example.com/file.js
			$remote = (preg_match('/^(http:|\/\/)/', $js_element));

			// Add the appropriate path'd file to array
			$this->_js[] = ($remote) ? $js_element : $dir . $js_element . '.js';
		}

		// return layout object
		return $this;
	}




	/**
	 * Returns HTML string of <script> tags used to load javascript files in view
	 */
	function get_js()
	{
		$html = '';

		// loop over each js source
		foreach ($this->_js as $js_src)
		{
			// set source with local scripts directory
			$html .= '<script src="' . $js_src . '?t=' . $this->get_ts() . '"></script>' . "\n";
		}

		return $html;
	}




	/**
	 * Sets the js array to a blank array
	 */
	function clear_js()
	{
		$this->_js = array();
		return $this;
	}




	/**
	 * Set array element to $_css
	 */
	function set_css($css, $dir = 'css/')
	{
		// If the supplied $css var is not an array, make an array from it first
		if ( ! is_array($css))
		{
			$css = array($css);
		}

		// Loop through each $css element and add it to $_css
		foreach($css as $css_element)
		{
			// Check if the file being added is remote or not.
			// Valid remote formats: http://example.com/file.js -OR- //example.com/file.js
			$remote = (preg_match('/^(http:|\/\/)/', $css_element));

			// Add the appropriate path'd file to array
			$this->_css[] = ($remote) ? $css_element : $dir . $css_element . '.css';
		}

		return $this;
	}




	/**
	 * Returns html used to load css files in view
	 */
	function get_css()
	{
		$html = '';

		foreach ($this->_css as $css_src)
		{
			$html .= '<link href="' . $css_src . '?t=' . $this->get_ts() . '" rel="stylesheet" type="text/css">' . "\n";
		}

		return $html;
	}




	/**
	 * Clears $_css array
	 */
	function clear_css()
	{
		$this->_css = array();
		return $this;
	}




	/**
	 * Get the 'timestamp' querystring value to add to assets to assist with caching
	 */
	public function get_ts()
	{
		return config_item('version');
	}




	/**
	 * Set array element to $_meta
	 */
	public function set_meta($name, $content = FALSE)
	{
		if (is_array($name))
		{
			foreach ($name as $name => $content)
			{
				$this->_meta[$name] = $content;
			}
		}
		else
		{
			$this->_meta[$name] = $content;
		}
	}




	/**
	 * Returns html used to set metadata in view
	 */
	public function get_meta()
	{
		$html = '';

		foreach ($this->_meta as $name => $content)
		{
			$html .= '<meta name="' . $name . '" content="' . $content . '">' . "\n";
		}

		return $html;
	}




	/**
	 * Clears protected $_meta array
	 */
	public function clear_meta()
	{
		$this->_meta = array();
	}




	/**
	 * Set array element to $_breadcrumb
	 */
	public function set_breadcrumb($breadcrumb, $link = '')
	{
		// is $breadcrumb an array?
		if (is_array($breadcrumb))
		{
			foreach($breadcrumb as $title => $link)
			{
				$this->_breadcrumbs[$title] = $link;
			}
		}
		else
		{
			$this->_breadcrumbs[$breadcrumb] = $link;
		}

		// return layout object
		return $this;
	}




	/**
	 * Takes $_breadcrumbs and formats them into a string for view
	 */
	public function get_breadcrumbs($delimiter = '&raquo;')
	{
		$html = '';

		// if breadcrumb is not empty
		if ( ! empty($this->_breadcrumbs))
		{
			// loop over each breadcrumb and add it to string
			foreach ($this->_breadcrumbs as $title => $link)
			{
				// if it is the last breadcrumb
				if ($link == end($this->_breadcrumbs))
				{
					// simply return title
					$html .= $title;
				}
				else
				{
					// return formatted string
					$html .= '<a href="' . $link . '">' . $title . '</a> ' . $delimiter . ' ';
				}
			}

			$html .= '</p>';
		}

		return $html;
	}




	/**
	 * Set navigation
	 */
	public function set_nav($title, $link = '')
	{
		// is $title an array?
		if (is_array($title))
		{
			// if it is, add all links to $_nav
			foreach($title as $title => $link)
			{
				$this->_nav[$title] = $link;
			}
		}
		else
		{
			$this->_nav[$title] = $link;
		}

		// return layout object
		return $this;
	}

	/**
	 * Clear the navigation, so it's ready to be created from scratch
	 */
	public function clear_nav()
	{
		$this->_nav = array();
	}


	/**
	 * Takes $_nav, formats it and returns it as a string
	 */
	public function get_nav()
	{
		// begin by creating first part of html string
		$html = '<ul id="menu" class="grey-nav">';

		// go over each element in the $_nav array
		foreach($this->_nav as $title => $href)
		{
			// create the first listed item in the navigation unordered list. Add classes where appropriate.
			$html .= '<li class="' . (in_array($title, $this->_title) ? 'selected ' : '') . (is_array($href) ? 'has_more ' : '') . '">';

			// if the link contains a url, it is just a stand alone link, if it is an array, then we have a sub nav
			$html .= ( ! is_array($href) ? '<a href="' . $href . '" class="">' . $title . '</a>' : '<span>' . $title . '</span>');

			// if we have a sub nav
			if (is_array($href))
			{
				// create sub nav html
				$html .= '<ul class="">';
				foreach ($href as $title => $href)
				{
					$html .= '<li><a href="' . $href . '" class="">' . $title . '</a></li>';
				}
				$html .= '</ul>';
			}

			// close listed item
			$html .= '</li>';
		}

		//add a special nav button at the end
		if ($this->_CI->auth->is_logged_in() && $this->_CI->uri->segment(1) != 'admin')
		{
			$html .= '<li class="js-load-orders nav-special"><span>Next Order<img class="loadicon" src="img/style/ajax-loader.gif"></span></li>';
		}

		// close unordered list
		$html .= '</ul>';

		echo '<div class="js-loaded-orders loaded-orders hidden" style="display: none;"></div>';

		// return full html
		return $html;
	}




	/**
	 * Get action
	 */
	public function get_action($div_class = 'action')
	{
		$html = '';

		// if action flashdata exists
		if ($this->_CI->session->flashdata('action'))
		{
			// build html
			$html .= "<div class=\"" . $div_class . "\">\n";
			$html .= "<p>" . $this->_CI->session->flashdata('action') . "</p>\n";
			$html .= "</div>";
        }

		return $html;
	}




}
