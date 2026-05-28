<?php

if (!defined('ABSPATH')) {
	exit;
}

final class LT_Bottom_CTA_Frontend
{
	private static function is_enabled($value): bool
	{
		$v = is_string($value) ? strtolower(trim($value)) : $value;

		if ($v === 'lt_mb_bottom_cta_enabled') {
			return true;
		}

		// Compatibilidade com valores comuns de checkbox no WP
		if ($v === '1' || $v === 1 || $v === true || $v === 'true' || $v === 'on' || $v === 'yes') {
			return true;
		}

		return false;
	}

	public static function init(): void
	{
		add_action('wp_head', [__CLASS__, 'print_css'], 50);
		add_action('wp_footer', [__CLASS__, 'print_html'], 30);
	}

	/**
	 * Seletor CSS do marcador no conteúdo. O CTA só aparece depois que esse elemento
	 * subiu inteiro acima da viewport (usuário rolou para baixo além dele).
	 *
	 * @example add_filter( 'lt_bottom_cta_trigger_selector', fn () => '.botao_wpp' );
	 * @example add_filter( 'lt_bottom_cta_trigger_selector', fn () => '#botao_wpp' );
	 *
	 * Compat: lt_bottom_cta_trigger_element_id ainda vira "#id" se o seletor explícito não for usado.
	 */
	private static function trigger_selector(): string
	{
		$explicit = apply_filters('lt_bottom_cta_trigger_selector', null);
		if (is_string($explicit) && $explicit !== '') {
			return self::sanitize_trigger_selector($explicit);
		}

		$id = (string) apply_filters('lt_bottom_cta_trigger_element_id', 'botao_wpp');
		$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
		$slug = $id !== '' ? $id : 'botao_wpp';

		// Aceita marcador como id OU classe sem precisar de filtro (ex.: `<div class="botao_wpp">`).
		return '#' . $slug . ', .' . $slug;
	}

	private static function sanitize_trigger_selector(string $sel): string
	{
		$sel = trim($sel);
		if ($sel === '' || strlen($sel) > 200) {
			return '#botao_wpp, .botao_wpp';
		}
		if (preg_match('/[`;{}\\\\]/', $sel)) {
			return '#botao_wpp, .botao_wpp';
		}

		return $sel;
	}

	private static function get_data(int $post_id): array
	{
		$enabled = (string) get_post_meta($post_id, 'lt_mb_bottom_cta_enabled', true);
		$image_id = absint(get_post_meta($post_id, 'lt_mb_bottom_cta_image', true));
		$title = sanitize_text_field((string) get_post_meta($post_id, 'lt_mb_bottom_cta_title', true));
		$subtitle = sanitize_text_field((string) get_post_meta($post_id, 'lt_mb_bottom_cta_subtitle', true));
		$button_text = sanitize_text_field((string) get_post_meta($post_id, 'lt_mb_bottom_cta_button_text', true));
		$button_url = esc_url((string) get_post_meta($post_id, 'lt_mb_bottom_cta_button_url', true));
		$below_text = sanitize_text_field((string) get_post_meta($post_id, 'lt_mb_bottom_cta_below_text', true));
		$show_icon = (string) get_post_meta($post_id, 'lt_mb_bottom_cta_show_icon', true);

		return [
			'enabled' => $enabled,
			'image_id' => $image_id,
			'title' => $title,
			'subtitle' => $subtitle,
			'button_text' => $button_text,
			'button_url' => $button_url,
			'below_text' => $below_text,
			'show_icon' => $show_icon,
		];
	}

	public static function print_css(): void
	{
		?>
		<style>
			:root {
				--lt-bottom-cta-height: 0px;
			}

			#av_top_wrapper,
			#av_top,
			.ad_container {
				top: 0;
				left: 0;
				width: 100%;
				z-index: 99 !important;
			}

			body.has-lt-bottom-cta {
				padding-bottom: var(--lt-bottom-cta-height);
			}

			[data-lt-bottom-cta] {
				position: fixed;
				left: 0;
				right: 0;
				bottom: 0;
				z-index: 80;
				background: #f7f7f7;
				border-top: 1px solid rgba(0, 0, 0, .08);
				box-shadow: 0 -10px 30px rgba(0, 0, 0, .08);
				opacity: 1;
				transition: opacity .18s ease;
			}

			[data-lt-bottom-cta].lt-bottom-cta--transparent {
				visibility: hidden !important;
				opacity: 0 !important;
				pointer-events: none !important;
			}

			[data-lt-bottom-cta][hidden] {
				display: none !important;
			}

			.lt-bottom-cta__inner {
				max-width: var(--lt-content-max-width, 1100px);
				margin: 0 auto;
				padding: 12px 16px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;
			}

			.lt-bottom-cta__img {
				width: 122px;
				height: 54px;
				border-radius: 10px;
				overflow: hidden;
				background: #16a34a;
				flex: 0 0 auto;
				align-items: center;
				display: flex;
				justify-content: center;
			}

			.lt-bottom-cta__img img {
				width: 45%;
				height: 100%;
				object-fit: cover;
				display: block;
			}

			.lt-bottom-cta__content {
				display: flex;
				align-items: center;
				gap: 12px;
				min-width: 0;
				flex: 1 1 auto;
			}

			.lt-bottom-cta__text {
				min-width: 0;
			}

			.lt-bottom-cta__title {
				font-weight: 800;
				font-size: 16px;
				line-height: 1.15;
				margin: 0;
				color: #1b1b1b;
			}

			.lt-bottom-cta__subtitle {
				margin: 3px 0 0;
				font-size: 12px;
				line-height: 1.2;
				color: rgba(0, 0, 0, .72);
			}

			.lt-bottom-cta__below {
				margin: 6px 0 0;
				font-size: 12px;
				line-height: 1.2;
				color: rgba(0, 0, 0, .65);
			}

			.lt-bottom-cta__btn {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 10px;
				min-height: 44px;
				padding: 0 18px;
				min-width: 220px;
				border-radius: 10px;
				background: #1f6f2e;
				color: #fff !important;
				font-weight: 800;
				text-decoration: none;
				white-space: nowrap;
				position: relative;
			}

			.lt-bottom-cta__btn svg {
				width: 18px;
				height: 18px;
				flex: 0 0 auto;
				display: block;
			}

			.lt-bottom-cta__btn.is-pulsing {
				animation: ltBottomCtaPulse 1.35s infinite;
			}

			@keyframes ltBottomCtaPulse {
				0% {
					transform: scale(1);
					box-shadow: 0 0 0 0 rgba(31, 111, 46, .55);
				}

				70% {
					transform: scale(1.03);
					box-shadow: 0 0 0 12px rgba(31, 111, 46, 0);
				}

				100% {
					transform: scale(1);
					box-shadow: 0 0 0 0 rgba(31, 111, 46, 0);
				}
			}

			.lt-bottom-cta__btn:hover {
				filter: brightness(.95);
			}

			@media (min-width: 800px) {
				.lt-bottom-cta__btn {
					min-width: 260px;
					padding: 0 26px;
				}
			}

			@media (max-width: 520px) {
				.lt-bottom-cta__inner {
					flex-direction: column;
					align-items: stretch;
					gap: 10px;
				}

				.lt-bottom-cta__btn {
					width: 100%;
				}

				.lt-bottom-cta__content {
					justify-content: center;
				}

				.lt-bottom-cta__text {
					text-align: center;
				}
			}
		</style>
		<?php
	}

	public static function print_html(): void
	{
		if (!is_singular(['post', 'page'])) {
			return;
		}

		$post_id = get_queried_object_id();
		if (!$post_id) {
			return;
		}

		$data = self::get_data((int) $post_id);
		if (!self::is_enabled($data['enabled'] ?? null)) {
			return;
		}
		if (empty($data['button_text']) || empty($data['button_url'])) {
			return;
		}

		$image_html = '';
		if (!empty($data['image_id'])) {
			$image_html = wp_get_attachment_image(
				$data['image_id'],
				'thumbnail',
				false,
				['loading' => 'lazy']
			);
		}
		$trigger_selector = self::trigger_selector();
		/** Se true e o seletor não achar elemento no DOM, exibe o CTA mesmo assim. */
		$fallback_without_trigger = (bool) apply_filters('lt_bottom_cta_show_without_trigger', false);
		/**
		 * Quantos px depois do marcador o CTA pode aparecer.
		 * Ex.: 30 => só mostra quando o marcador já passou 30px acima do topo.
		 */
		$trigger_offset_px = max(0, (int) apply_filters('lt_bottom_cta_trigger_offset_px', 30));
		/**
		 * Seletores de elementos que o CTA NUNCA pode cobrir (anúncios). Quando a barra
		 * sobrepõe qualquer um deles, ela é escondida (visibility: hidden) — o anúncio
		 * sempre vence. Política de redes de anúncio: cobrir anúncio pode banir o site.
		 *
		 * Os seletores em MANDATORY_OVERLAP_SELECTORS são SEMPRE protegidos — o filtro
		 * só pode ADICIONAR seletores extras, nunca remover esses.
		 *
		 * @example add_filter( 'lt_bottom_cta_overlap_selectors', fn () => '.meu-anuncio' );
		 */
		$mandatory = '#av_top_wrapper, #av_top, .ad_container';
		$extras    = (string) apply_filters(
			'lt_bottom_cta_overlap_selectors',
			'.code-block, ins.adsbygoogle'
		);
		$overlap_selectors = $mandatory . ($extras !== '' ? ', ' . $extras : '');
		?>
		<div class="lt-bottom-cta" data-lt-bottom-cta hidden>
			<div class="lt-bottom-cta__inner">
				<div class="lt-bottom-cta__content">
					<?php if ($image_html) { ?>
						<div class="lt-bottom-cta__img"><?php echo $image_html; ?></div>
					<?php } ?>

					<div class="lt-bottom-cta__text">
						<?php if (!empty($data['title'])) { ?>
							<p class="lt-bottom-cta__title"><?php echo esc_html($data['title']); ?></p>
						<?php } ?>
						<?php if (!empty($data['subtitle'])) { ?>
							<p class="lt-bottom-cta__subtitle"><?php echo esc_html($data['subtitle']); ?></p>
						<?php } ?>
						<?php if (!empty($data['below_text'])) { ?>
							<p class="lt-bottom-cta__below"><?php echo esc_html($data['below_text']); ?></p>
						<?php } ?>
					</div>
				</div>

				<a class="lt-bottom-cta__btn is-pulsing" href="<?php echo esc_url($data['button_url']); ?>">
					<?php if (!empty($data['show_icon'])) { ?>
						<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false">
							<path fill="currentColor"
								d="M19.11 17.62c-.27-.14-1.57-.77-1.81-.86-.24-.09-.41-.14-.58.14-.17.27-.67.86-.82 1.03-.15.17-.3.2-.56.07-.27-.14-1.12-.41-2.13-1.31-.79-.7-1.32-1.56-1.47-1.82-.15-.27-.02-.41.11-.55.12-.12.27-.3.41-.45.14-.15.17-.27.27-.44.09-.17.05-.32-.02-.45-.07-.14-.58-1.4-.79-1.92-.21-.51-.43-.44-.58-.45l-.49-.01c-.17 0-.45.06-.68.32-.23.27-.9.88-.9 2.14 0 1.26.92 2.48 1.05 2.65.14.17 1.81 2.76 4.38 3.87.61.26 1.08.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.57-.64 1.79-1.26.22-.62.22-1.15.15-1.26-.07-.11-.24-.17-.51-.31zM16.01 3C9.4 3 4 8.39 4 15c0 2.12.56 4.18 1.62 5.99L4 29l8.2-1.58A11.94 11.94 0 0 0 16 27c6.61 0 12-5.39 12-12S22.62 3 16.01 3zm0 21.87c-1.84 0-3.64-.5-5.2-1.44l-.37-.22-4.86.94.95-4.73-.24-.39A9.83 9.83 0 0 1 6.2 15c0-5.41 4.4-9.81 9.81-9.81 5.41 0 9.81 4.4 9.81 9.81 0 5.41-4.4 9.87-9.81 9.87z" />
						</svg>
					<?php } ?>
					<span><?php echo esc_html($data['button_text']); ?></span>
				</a>
			</div>
		</div>
		<script>
			(function () {
				var el = document.querySelector('[data-lt-bottom-cta]');
				if (!el) return;

				var triggerSelector = <?php echo wp_json_encode($trigger_selector); ?>;
				var fallbackWithoutTrigger = <?php echo $fallback_without_trigger ? 'true' : 'false'; ?>;
				var triggerOffsetPx = <?php echo (int) $trigger_offset_px; ?>;

				var queryTrigger = function () {
					try {
						return document.querySelector(triggerSelector);
					} catch (e) {
						return null;
					}
				};

				var setPadding = function (h) {
					document.documentElement.style.setProperty('--lt-bottom-cta-height', (h || 0) + 'px');
				};

				var show = function () {
					el.hidden = false;
					el.removeAttribute('hidden');
					document.body.classList.add('has-lt-bottom-cta');
					setPadding(el.offsetHeight || 0);
				};

				var hide = function () {
					el.hidden = true;
					document.body.classList.remove('has-lt-bottom-cta');
					setPadding(0);
				};

				var overlapSelectors = <?php echo wp_json_encode($overlap_selectors); ?>;
				var overlaps = function (a, b) {
					return !(a.right <= b.left || a.left >= b.right || a.bottom <= b.top || a.top >= b.bottom);
				};
				var syncOpacityByOverlap = function () {
					if (el.hidden) {
						el.classList.remove('lt-bottom-cta--transparent');
						return;
					}

					var blockers;
					try {
						blockers = document.querySelectorAll(overlapSelectors);
					} catch (e) {
						blockers = null;
					}
					if (!blockers || !blockers.length) {
						el.classList.remove('lt-bottom-cta--transparent');
						return;
					}

					var rEl = el.getBoundingClientRect();
					for (var i = 0; i < blockers.length; i++) {
						var rB = blockers[i].getBoundingClientRect();
						if (overlaps(rEl, rB)) {
							el.classList.add('lt-bottom-cta--transparent');
							return;
						}
					}
					el.classList.remove('lt-bottom-cta--transparent');
				};

				/**
				 * Apenas depois de passar o marcador (seletor):
				 * o topo do marcador precisa estar pelo menos `triggerOffsetPx` acima do rodapé da viewport.
				 * Ex.: 30 => quando o usuário rolar e o marcador "subir" 30px acima do fim da tela, mostra.
				 */
				var shouldReveal = function (trigger) {
					var r = trigger.getBoundingClientRect();
					return r.top <= (window.innerHeight - triggerOffsetPx);
				};

				var start = function () {
					var pollTimer = null;
					var observer = null;
					var attached = false;

					var stopWatching = function () {
						if (pollTimer) {
							window.clearInterval(pollTimer);
							pollTimer = null;
						}
						if (observer) {
							observer.disconnect();
							observer = null;
						}
					};

					var tryBind = function (trigger) {
						if (!trigger) {
							return false;
						}
						if (attached) {
							return true;
						}
						attached = true;

						stopWatching();
						hide();

						var tick = function () {
							if (shouldReveal(trigger)) {
								show();
							} else {
								hide();
							}
							syncOpacityByOverlap();
						};

						tick();
						window.addEventListener('scroll', tick, { passive: true });
						window.addEventListener('resize', tick, { passive: true });

						return true;
					};

					var trigger = queryTrigger();
					if (tryBind(trigger)) {
						// ok
					} else {
						var attempts = 0;
						pollTimer = window.setInterval(function () {
							attempts += 1;
							trigger = queryTrigger();
							if (trigger) {
								tryBind(trigger);
							} else if (attempts >= 48) {
								stopWatching();
								if (fallbackWithoutTrigger) {
									show();
								}
							}
						}, 250);

						if (typeof MutationObserver !== 'undefined') {
							observer = new MutationObserver(function () {
								trigger = queryTrigger();
								if (trigger) {
									tryBind(trigger);
								}
							});
							observer.observe(document.documentElement, { childList: true, subtree: true });
						}
					}

				};

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', start);
				} else {
					start();
				}
			})();
		</script>
		<?php
	}
}

LT_Bottom_CTA_Frontend::init();

