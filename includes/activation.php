<?php

if (!defined('ABSPATH')) {
	exit;
}

final class LT_Bottom_CTA_Activation
{
	const AD_INSERTER_OPTION = 'ad_inserter';
	const TARGET_BLOCK = 80;
	const TRIGGER_CODE = '<div class="botao_wpp"></div>';

	public static function on_activate(): void
	{
		if (!self::ad_inserter_active()) {
			return;
		}

		$options = self::read_options();
		if (!is_array($options)) {
			$options = [];
		}

		$existing = isset($options[self::TARGET_BLOCK]) && is_array($options[self::TARGET_BLOCK])
			? $options[self::TARGET_BLOCK]
			: [];

		$current_code = isset($existing['code']) ? trim((string) $existing['code']) : '';
		if ($current_code !== '' && $current_code !== self::TRIGGER_CODE) {
			return;
		}

		$options[self::TARGET_BLOCK] = array_merge($existing, [
			'name' => 'Botão WhatsApp (lt-bottom-cta)',
			'code' => self::TRIGGER_CODE,
			'display_type' => 6,
			'paragraph_number' => '3',
			'alignment_type' => 6,
			'display_on_posts' => '1',
			'display_on_pages' => '1',
			'display_on_homepage' => '0',
			'display_on_category_pages' => '0',
			'display_on_search_pages' => '0',
			'display_on_archive_pages' => '0',
		]);

		self::write_options($options);
	}

	private static function ad_inserter_active(): bool
	{
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active('ad-inserter/ad-inserter.php')
			|| is_plugin_active('ad-inserter-pro/ad-inserter-pro.php');
	}

	private static function read_options()
	{
		$raw = get_option(self::AD_INSERTER_OPTION, false);

		if ($raw === false) {
			return [];
		}

		if (is_array($raw)) {
			return $raw;
		}

		if (is_string($raw) && substr($raw, 0, 4) === ':AI:') {
			$decoded = base64_decode(substr($raw, 4), true);
			if ($decoded === false) {
				return null;
			}
			$value = @unserialize($decoded);
			return is_array($value) ? $value : null;
		}

		return null;
	}

	private static function write_options(array $options): void
	{
		update_option(
			self::AD_INSERTER_OPTION,
			':AI:' . base64_encode(serialize($options))
		);
	}
}
