<?php

/**
 * A class for collecting email info and submitting it to the given address.
 *
 */
class WpEmailCollection_EmailCollection
extends WpWidgetFormSubmit_Widget
implements WpWidgetFormSubmit_FormInterface
{
	/**
	 * The first name element's Id.
	 *
	 * @var string
	 */
	private $_first_name_id;

	/**
	 * The last name element's Id.
	 *
	 * @var string
	 */
	private $_last_name_id;

	/**
	 * The company name element's Id.
	 *
	 * @var string
	 */
	private $_company_name_id;

	/**
	 * The email element's Id.
	 *
	 * @var string
	 */
	private $_email_id;

	/**
	 * Whether the form has been successfully submitted.
	 *
	 * @var boolean 
	 */
	private $_successfully_submitted = false;

	/**
	 * The constructor
	 */
	public function __construct()
	{
		parent::__construct();
		add_filter('wp_widget_form_submit_form_render', array($this, 'render_form'), 10, 4);
	}

	/**
	 * Get the widget's name.
	 *
	 * @return string
	 */
	public function getWidgetName()
	{
		return __('Email Collection', 'wp-email-collection');
	}

	/**
	 * Get the widget class name.
	 *
	 * @return string 
	 */
	public function getWidgetClassName()
	{
		return 'widget_email_collection';
	}

	/**
	 * Get the widget's description.
	 *
	 * @return string The description of the widget.
	 */
	public function getWidgetDescription()
	{
		return __('A widget for creating an email-collection form.','wp-email-collection');
	}

	/**
	 * Callback invoked when constructing the form.  Should be used to add elements, validators, etc.
	 *
	 * @return WpWidgetFormSubmit_FormInterface
	 */
	public function buildForm()
	{
		$form = $this->getForm();
		if (!empty($form)) {
			$first_name = new Zend_Form_Element_Text($this->get_field_id('first_name'));
			$first_name->setAttrib('placeholder', __('First name', 'wp-email-collection'));
			$this->_first_name_id = $first_name->getName();

			$last_name = new Zend_Form_Element_Text($this->get_field_id('last_name'));
			$last_name->setAttrib('placeholder', __('Last name','wp-email-collection'));
			$this->_last_name_id = $last_name->getName();

			$company_name = new Zend_Form_Element_Text($this->get_field_id('company_name'));
			$company_name->setAttrib('placeholder', __('Company', 'wp-email-collection'));
			$this->_company_name_id = $company_name->getName();

			$email = new Zend_Form_Element_Text($this->get_field_id('email'));
			$email->setAttrib('placeholder', __('Email','wp-email-collection'));
			$this->_email_id = $email->getName();

			$submit = new Zend_Form_Element_Button($this->get_field_id('submit_button'), array(
				'ignore' => true,
				'label' => __('Subscribe','wp-email-collection'),
				'class' => 'action-initiate',
				'type' => 'submit',
			));

			$first_name->addValidator(new Zend_Validate_Alnum());

			$form->addElement($first_name);
			$form->addElement($last_name);
			$form->addElement($company_name);
			$form->addElement($email);
			$form->addElement($submit);
		}
	}

	/**
	 * Callback invoked when the submitted form has been successfully validated.
	 *
	 * @return WpWidgetFormSubmit_FormInterface
	 */
	public function whenFormPassesValidation()
	{
		$this->_successfully_submitted = true;
		$values = $this->getForm()->getValues();
		$first_name = empty($values[$this->_first_name_id]) ? '' : $values[$this->_first_name_id];
		$last_name = empty($values[$this->_last_name_id]) ? '' : $values[$this->_last_name_id];
		$company_name = empty($values[$this->_company_name_id]) ? '' : $values[$this->_company_name_id];
		$email = empty($values[$this->_email_id]) ? '' : $values[$this->_email_id];

		$instance = $this->getInstance();
		if (!empty($instance['recipient-email'])) {
			$email_text = <<<EOF
Hello,

The following person has subscribed on the site:

First name:$first_name
Last name:$last_name
Company name:$company_name
Email:$email
EOF;

			$subject = __('New Site Subscription','wp-email-collection');
			wp_mail($instance['recipient-email'], $subject, $email_text);
		}
	}

	/**
	 * Callback invoked when the submitted form has failed validation.
	 *
	 * @return WpWidgetFormSubmit_FormInterface
	 */
	public function whenFormFailsValidation()
	{
		$this->_successfully_submitted = false;
	}

	/**
	 * Render the administration configuration form for the widget.
	 *
	 * @param array $instance The configuration values of the widget.
	 *
	 * @return void
	 */
	public function form($instance) 
	{
		$instance = wp_parse_args((array) $instance, array('title' => '', 'recipient-email' => ''));
		$title = $instance['title'];
		$recipient_email = $instance['recipient-email'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:','wp-email-collection'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('recipient-email'); ?>">
				<?php _e('Email of Recipient:','wp-email-collection'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('recipient-email'); ?>" name="<?php echo $this->get_field_name('recipient-email'); ?>" type="text" value="<?php echo esc_attr($recipient_email); ?>" />
			</label>
		</p>
		<?php
	}

	/**
	 * Update the widget configuration values.
	 *
	 * @param array $new_instance The updated widget configuration values.
	 * @param array $old_instance The prior widget configuration values.
	 *
	 * @return array The updated configuration values.
	 */
	public function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array('title' => '', 'recipient-email' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['recipient-email'] = strip_tags($new_instance['recipient-email']);
		return $instance;
	}

	/**
	 * Callback for filtering the form-rendering.
	 *
	 * @param string    $markup The markup to render.
	 * @param Zend_Form $form   The form being rendered.
	 * @param Zend_View $view   The view being rendered.
	 * @param WpWidgetFormSubmit_Widget $widget The widget for which the form is being rendered.
	 *
	 * @return string The markup
	 */
	public function render_form($markup = '', $form = null, $view = null, $widget = null)
	{
		if ($this->_successfully_submitted && ($widget == $this)) {
			$markup = '<h3>' . __('Thank you for subscribing!','wp-email-collection') . '</h3>';
		}
		return $markup;
	}
}
