<?php
/*

Plugin Name: Forms-3rdparty Dynamic Fields
Plugin URI: https://github.com/zaus/forms-3rdparty-integration
Description: Provides some dynamic field values via placeholder to Forms 3rdparty Integration
Author: zaus, skane
Version: 0.3.2
Author URI: http://drzaus.com
Changelog:
	0.1 init
	0.2 attach to message
	0.3 GET params
	0.3.2 referer
*/



class Forms3rdpartyDynamicFields {

	const N = 'Forms3rdpartyDynamicFields';
	const B = 'Forms3rdPartyIntegration';

	public function Forms3rdpartyDynamicFields() {
		// only first form
		add_filter(self::B.'_service_filter_post', array(&$this, 'post_filter'), 10, 3);
		
		// just provides a listing of placeholders
		add_filter(self::B.'_service_metabox_after', array(&$this, 'service_metabox'), 10, 4);

		// configure whether to attach or not, how
		add_filter(self::B.'_service_settings', array(&$this, 'service_settings'), 10, 3);

		// attach to response message
		add_filter(self::B.'_service', array(&$this, 'adjust_response'), 10, 2);
	}


	private function uid($value) {
		// is this overkill?  would it just be better to try a uuid variant?
		return uniqid(sha1(php_uname('n') . NONCE_SALT . time() . serialize($value)), true);
	}

	const TIMESTAMP = '##TIMESTAMP##';
	const DATE = '##DATE_ISO##';
	const DATE_I18N = '##DATE##';
	const TIME_I18N = '##TIME##';
	const UID = '##UID##';
	const SITEURL = '##SITEURL##';
	const NETWORKSITEURL = '##NETWORKSITEURL##';
	const SITENAME = '##SITENAME##';
	const ADMINEMAIL = '##ADMINEMAIL##';
	const PAGEURL = '##PAGEURL##';
	const REQUESTURL = "##REQUESTURL##";
	const REFERER = "##REFERER##";
	const GETPARAM_PREFIX = "##GET:{";
	const PREFIX_LEN = 7; // the length of GETPARAM_PREFIX

	/**
	 * placeholder for response attachments
	 */
	private $_dynamic_attach;

	public function post_filter($post, $service, $form) {

		### _log(__CLASS__ . '::' . __METHOD__, $form);

		// if(isset($service['dynamic-field']) && isset($service['dynamic-value'])) $post[$service['dynamic-field']] = $service['dynamic-value'];

		if(isset($service['dynamic-attach']) && !empty($service['dynamic-attach']))
			$this->_dynamic_attach = array();
		else $this->_dynamic_attach = false;

		foreach($post as $field => &$value) {
			// TODO: check for multiple tokens?
			// TODO: better way to check and replace?
			if( $this->is_replace($value) ) {
				$value = $this->replace($value, $post);
				if( false !== $this->_dynamic_attach ) 
					$this->_dynamic_attach[$field] = sprintf(isset($service['dynamic-format']) && !empty($service['dynamic-format'])
								? $service['dynamic-format']
								: '%s = %s;'
								, $field, print_r($value,true)
							);
			}
		}// foreach $post

		
		return $post;
	}//--	fn	post_filter

	public function is_replace($value) {
		### _log(__CLASS__ . '/' . __FUNCTION__, $value);
		
		// known placeholders
		switch($value) {
			case self::TIMESTAMP:
			case self::DATE:
			case self::DATE_I18N:
			case self::TIME_I18N:
			case self::UID:
			case self::SITEURL:
			case self::SITENAME:
			case self::NETWORKSITEURL:
			case self::ADMINEMAIL:
			case self::REFERER:
			case self::PAGEURL:
			case self::REQUESTURL:
				return true;
			default:
				// because forms-3rdparty can also nest
				if(is_array($value)) {
					foreach($value as $k => $v) if( $this->is_replace($v) ) return true;
				}

				elseif(0 === strpos($value, self::GETPARAM_PREFIX)) return true;

				break;
		} // switch $value

		return false;
	}


	public function replace($value, $post = false) {
		// known placeholders
		switch($value) {
			case self::TIMESTAMP:
				return time();
			case self::DATE:
				return date('c'); // ISO 8601 = Y-m-d\TH:i:sP (PHP5)
			case self::DATE_I18N:
				return date_i18n( get_option('date_format'), time() );
			case self::TIME_I18N:
				return date_i18n( get_option('time_format'), time() );
			case self::UID:
				return $this->uid($post);
			case self::SITEURL:
				return get_site_url();
			case self::SITENAME:
				return get_bloginfo('name');
			case self::NETWORKSITEURL:
				return network_site_url();
			case self::REFERER:
				return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
			case self::ADMINEMAIL:
				return get_bloginfo('admin_email'); // TODO: is there a way to protect against this?
			case self::PAGEURL:
				return get_permalink();
			case self::REQUESTURL:
				return sprintf('http%s://', is_ssl() ? 's' : '') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			default:
				// because forms-3rdparty can also nest
				if(is_array($value)) {
					foreach($value as $k => &$v) {
						$v = replace($value, $post);
					}
				}
				elseif(0 === strpos($value, self::GETPARAM_PREFIX)) {
					// strip the rest of the param mask for the get key
					$value = substr($value, self::PREFIX_LEN, -3);
					return isset($_GET[ $value ]) ? $_GET[ $value ] : null;
				}

				break;
		} // switch $value

		return $value;
	}

	public function adjust_response($body, $refs) {
		if(!empty($this->_dynamic_attach)) {
			$refs['attach'] = implode(" \n", $this->_dynamic_attach);
		}
	}



	public function service_settings($eid, $P, $entity) {
		?>

			<fieldset><legend><span><?php _e('Dynamic Fields', $P); ?></span></legend>
				<div class="inside">
					<?php $field = 'dynamic-attach'; ?>
					<div class="field">
						<label for="<?php echo $field, '-', $eid ?>"><?php _e('Attach dynamic fields?', $P); ?></label>
						<input id="<?php echo $field, '-', $eid ?>" type="checkbox" class="checkbox" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="yes"<?php echo isset($entity[$field]) ? ' checked="checked"' : ''?> />
						<em class="description"><?php _e('Whether or not to attach the dynamic fields to the regular form notification.', $P); ?></em>
					</div>
					<?php $field = 'dynamic-format'; ?>
					<div class="field">
						<label for="<?php echo $field, '-', $eid ?>"><?php _e('Dynamic response value format', $P); ?></label>
						<input id="<?php echo $field, '-', $eid ?>" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo isset($entity[$field]) ? esc_attr($entity[$field]) : '%s = %s;'?>" />
						<em class="description"><?php _e('How to report each dynamic field in the form notification (e.g. <code>sprintf</code> mask like <code>%s = %s</code> for <code>$field</code> and <code>$value</code> respectively).', $P); ?></em>
					</div>
				</div>
			</fieldset>
		<?php
	}


	public function service_metabox($P, $entity) {

		?>
		<div id="metabox-<?php echo self::N; ?>" class="meta-box">
		<div class="shortcode-description postbox" data-icon="?">
			<h3 class="hndle"><span><?php _e('Dynamic Placeholder Examples', $P) ?></span></h3>
			
			<div class="description-body inside">

				<p class="description"><?php _e('List of dynamic placeholders.  Note that only one placeholder is allowed per value, it must be marked &quot;Is Value&quot;, and you must &quot;allow hooks&quot; in each service for this plugin to work.', $P) ?></p>
				<table>
					<thead>
						<tr>
							<th><?php _e('Placeholder', $P) ?></th>
							<th><?php _e('Result', $P) ?></th>
							<th><?php _e('Description', $P) ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php $t = self::TIMESTAMP; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Unix timestamp (seconds since 1970)', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::DATE; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Date string (server), ISO 8601', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::DATE_I18N; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Localized date string (as configured by WP)', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::TIME_I18N; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Localized time string (as configured by WP)', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::UID; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('&quot;Unique&quot; id (64 characters)', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::SITEURL; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Homepage url', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::NETWORKSITEURL; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('(Multisite/Network) Homepage url', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::SITENAME; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Website name', $P) ?></td>
						</tr>
						<?php if(is_admin()) : ?>
						<tr>
							<?php $t = self::ADMINEMAIL; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Administrator email', $P) ?></td>
						</tr>
						<?php endif; ?>
						<tr>
							<?php $t = self::PAGEURL; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Current WP-determined page url; only use if within Loop', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::REQUESTURL; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Current server-request url (may be same as PAGEURL if no rewrites/redirects)', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::REFERER; ?>
							<td class="dyn-field"><code><?php echo $t ?></code></td>
							<td><?php echo $this->replace($t); ?></td>
							<td><?php _e('Referer for the current page', $P) ?></td>
						</tr>
						<tr>
							<?php $t = self::GETPARAM_PREFIX; ?>
							<td class="dyn-field"><code><?php echo $t ?>page}##</code></td>
							<td><?php echo $this->replace($t . 'page}##'); ?></td>
							<td><?php _e('The indicated GET parameter (i.e. <code>?page=...</code>, indicated here with <code>{page}</code>)', $P) ?></td>
						</tr>
					</tbody>
				</table>

				<p class="description"><?php _e('Double-clicking each placeholder will put it in the last-entered \'Form Submission Field\' input.', $P) ?></p>

				<?php
				/*
				<?php $field = 'dynamic-field'; ?>
				<div class="field">
					<label for="<?php echo $field, '-', $eid ?>">Dynamic Parameter Name</label>
					<input id="<?php echo $field, '-', $eid ?>" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo isset($entity[$field]) ? esc_attr($entity[$field]) : ''?>" />
					<em class="description">Custom dynamic post field name, if needed.</em>
				</div>
				<?php $field = 'dynamic-value'; ?>
				<div class="field">
					<label for="<?php echo $field, '-', $eid ?>">Dynamic Parameter Value</label>
					<input id="<?php echo $field, '-', $eid ?>" type="text" class="text" name="<?php echo $P, '[', $eid, '][', $field, ']'?>" value="<?php echo isset($entity[$field]) ? esc_attr($entity[$field]) : ''?>" />
					<em class="description">Custom dynamic post field value, if needed.</em>
				</div>
				*/
				?>
			</div><!-- .inside -->
		</div>

			<?php /* note, the following is a lazy implementation rather than separate JS file */ ?>
			<script id="<?php echo self::N, '-js' ?>">
			(function($) {
				$(function() {
					var $lastInput = false;
					$('#poststuff').on('focus', 'input[type="text"].a', function() {
						$lastInput = $(this);
					});
					$('#metabox-<?php echo self::N ?>').on('dblclick', '.dyn-field', function(e) {
						if($lastInput) {
							$lastInput.val($(this).text());
							$lastInput.closest('tr').find('input[type=checkbox]').attr('checked', 'checked');
						}
					});
				});
			})(jQuery);
			</script>

		</div><!-- .postbox, .meta-box -->
	<?php

	}

}//---	class	Forms3partydynamic

// engage!
new Forms3rdpartyDynamicFields();