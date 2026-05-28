<?php
/**
 * Plugin Name: Botão Fixo
 * Description: CTA fixo no rodapé para posts e páginas (metabox + render no frontend).
 * Version: 2.3.3
 * Author: Danilo Brandão
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

define('LT_BOTTOM_CTA_VERSION', '2.3.3');
define('LT_BOTTOM_CTA_DIR', __DIR__);
define('LT_BOTTOM_CTA_URL', plugin_dir_url(__FILE__));

require_once LT_BOTTOM_CTA_DIR . '/includes/admin-metabox.php';
require_once LT_BOTTOM_CTA_DIR . '/includes/frontend.php';
require_once LT_BOTTOM_CTA_DIR . '/includes/activation.php';

register_activation_hook(__FILE__, ['LT_Bottom_CTA_Activation', 'on_activate']);
