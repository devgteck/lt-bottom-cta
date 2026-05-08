<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LT_Bottom_CTA_Admin {
	private const NONCE_ACTION = 'lt_bottom_cta_metabox';
	private const NONCE_FIELD  = 'lt_bottom_cta_nonce';

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'add_meta_boxes', [ __CLASS__, 'add_metabox' ] );
		add_action( 'save_post', [ __CLASS__, 'save' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
		add_action( 'admin_footer', [ __CLASS__, 'print_admin_footer_uploader_js' ] );
	}

	private static function fields(): array {
		return [
			[
				'id'       => 'lt_mb_bottom_cta_enabled',
				'label'    => __( 'Ativar CTA fixo', 'lt-bottom-cta' ),
				'type'     => 'checkbox',
				'sanitize' => 'none',
			],
			[
				'id'       => 'lt_mb_bottom_cta_image',
				'label'    => __( 'Imagem (opcional)', 'lt-bottom-cta' ),
				'type'     => 'mediaupload',
				'sanitize' => 'absint',
			],
			[
				'id'       => 'lt_mb_bottom_cta_title',
				'label'    => __( 'Título (opcional)', 'lt-bottom-cta' ),
				'type'     => 'text',
				'input'    => 'text',
				'sanitize' => 'text',
			],
			[
				'id'       => 'lt_mb_bottom_cta_subtitle',
				'label'    => __( 'Subtítulo (opcional)', 'lt-bottom-cta' ),
				'type'     => 'text',
				'input'    => 'text',
				'sanitize' => 'text',
			],
			[
				'id'       => 'lt_mb_bottom_cta_button_text',
				'label'    => __( 'Texto do botão', 'lt-bottom-cta' ),
				'type'     => 'text',
				'input'    => 'text',
				'sanitize' => 'text',
			],
			[
				'id'       => 'lt_mb_bottom_cta_button_url',
				'label'    => __( 'Link do botão', 'lt-bottom-cta' ),
				'type'     => 'text',
				'input'    => 'url',
				'sanitize' => 'url',
			],
			[
				'id'       => 'lt_mb_bottom_cta_below_text',
				'label'    => __( 'Texto abaixo do botão (opcional)', 'lt-bottom-cta' ),
				'type'     => 'text',
				'input'    => 'text',
				'sanitize' => 'text',
			],
		];
	}

	public static function add_metabox(): void {
		foreach ( [ 'post', 'page' ] as $post_type ) {
			add_meta_box(
				'lt_bottom_cta_metabox',
				__( 'CTA fixo (rodapé)', 'lt-bottom-cta' ),
				[ __CLASS__, 'render' ],
				$post_type,
				'side',
				'high'
			);
		}
	}

	public static function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		foreach ( self::fields() as $field ) {
			$id    = $field['id'];
			$label = $field['label'];
			$value = get_post_meta( $post->ID, $id, true );

			if ( $field['type'] === 'checkbox' ) {
				$checked = (string) $value === (string) $id;
				?>
				<p>
					<label class="selectit" for="<?php echo esc_attr( $id ); ?>">
						<input id="<?php echo esc_attr( $id ); ?>" type="checkbox" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>" <?php checked( $checked ); ?>>
						<?php echo esc_html( $label ); ?>
					</label>
				</p>
				<?php
				continue;
			}

			if ( $field['type'] === 'mediaupload' ) {
				$image = '';
				$value = absint( $value );
				if ( $value ) {
					$image = wp_get_attachment_image_src( $value, 'medium' );
					if ( ! $image ) {
						$image = wp_get_attachment_image_src( $value, 'thumbnail' );
					}
				}
				$has_image      = (bool) $image;
				$remove_display = $has_image ? 'inline-block' : 'none';
				$status_text    = $has_image ? __( 'Imagem selecionada', 'lt-bottom-cta' ) : __( 'Nenhuma imagem selecionada', 'lt-bottom-cta' );
				?>
				<div class="lt-mb-media">
					<label for="<?php echo esc_attr( $id ); ?>"><b><?php echo esc_html( $label ); ?>:</b></label><br>
					<div class="lt-mb-media__row">
						<span class="lt-mb-media__status"><?php echo esc_html( $status_text ); ?></span>
						<button type="button" class="button" data-lt-mb-media-add="1"><?php echo esc_html__( 'Adicionar imagem', 'lt-bottom-cta' ); ?></button>
						<button type="button" class="button" data-lt-mb-media-remove="1" style="display:<?php echo esc_attr( $remove_display ); ?>"><?php echo esc_html__( 'Remover', 'lt-bottom-cta' ); ?></button>
					</div>
					<div class="lt-mb-media__preview" <?php if ( ! $has_image ) { echo 'style="display:none"'; } ?>>
						<?php if ( $has_image ) { ?>
							<img src="<?php echo esc_url( $image[0] ); ?>" alt="">
						<?php } ?>
					</div>
					<input id="<?php echo esc_attr( $id ); ?>" type="hidden" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
				</div>
				<?php
				continue;
			}

			$input_type = isset( $field['input'] ) ? $field['input'] : 'text';
			?>
			<p>
				<label class="selectit" for="<?php echo esc_attr( $id ); ?>"><b><?php echo esc_html( $label ); ?>:</b></label><br>
				<input class="large-text" id="<?php echo esc_attr( $id ); ?>" type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
			</p>
			<?php
		}
	}

	public static function save( int $post_id, \WP_Post $post ): void {
		if ( $post->post_type !== 'post' && $post->post_type !== 'page' ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? (string) $_POST[ self::NONCE_FIELD ] : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		foreach ( self::fields() as $field ) {
			$id       = $field['id'];
			$sanitize = $field['sanitize'] ?? 'text';

			$value = $_POST[ $id ] ?? '';
			update_post_meta( $post_id, $id, self::sanitize( $sanitize, $value ) );
		}
	}

	private static function sanitize( string $sanitize, $value ) {
		switch ( $sanitize ) {
			case 'none':
				return $value;
			case 'absint':
				return absint( $value );
			case 'url':
				return esc_url_raw( (string) $value );
			case 'text':
			default:
				return sanitize_text_field( (string) $value );
		}
	}

	public static function enqueue_admin_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, [ 'post', 'page' ], true ) ) {
			return;
		}

		wp_enqueue_media();

		$css = '
			.lt-mb-media__row{
				display:flex;
				align-items:center;
				gap: 8px;
				margin: 6px 0;
				flex-wrap: wrap;
			}
			.lt-mb-media__status{
				color: #646970;
				font-size: 12px;
			}
			.lt-mb-media__preview img{
				max-height: 120px;
				max-width: 100%;
				object-fit: contain;
				display: block;
				margin: 6px 0 0;
			}
		';
		wp_register_style( 'lt-bottom-cta-admin', false, [], LT_BOTTOM_CTA_VERSION );
		wp_enqueue_style( 'lt-bottom-cta-admin' );
		wp_add_inline_style( 'lt-bottom-cta-admin', $css );
	}

	public static function print_admin_footer_uploader_js(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, [ 'post', 'page' ], true ) ) {
			return;
		}

		global $pagenow;
		if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
			return;
		}
		?>
		<script>
		(function($){
			function getPreviewUrl(attachment){
				if(!attachment) return '';
				if(attachment.sizes){
					if(attachment.sizes.medium) return attachment.sizes.medium.url;
					if(attachment.sizes.thumbnail) return attachment.sizes.thumbnail.url;
				}
				return attachment.url || '';
			}

			$(document).on('click', '[data-lt-mb-media-add="1"]', function(e){
				e.preventDefault();

				var $button = $(this);
				var $container = $button.closest('.lt-mb-media');
				var $input = $container.find('input[type="hidden"]').first();
				var $remove = $container.find('[data-lt-mb-media-remove="1"]').first();
				var $status = $container.find('.lt-mb-media__status').first();
				var $preview = $container.find('.lt-mb-media__preview').first();

				if(typeof wp === 'undefined' || !wp.media){
					return;
				}

				var frame = wp.media({
					title: 'Selecionar imagem',
					library: { type: 'image' },
					button: { text: 'Usar esta imagem' },
					multiple: false
				});

				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					var previewUrl = getPreviewUrl(attachment);

					if(attachment && attachment.id){
						$input.val(attachment.id).trigger('change');
					}
					if(previewUrl){
						$status.text('Imagem selecionada');
						$preview.html('<img src="'+previewUrl+'" alt="" />').show();
						$remove.css('display', 'inline-block');
					}
				});

				frame.open();
			});

			$(document).on('click', '[data-lt-mb-media-remove="1"]', function(e){
				e.preventDefault();

				var $remove = $(this);
				var $container = $remove.closest('.lt-mb-media');
				var $input = $container.find('input[type="hidden"]').first();
				var $status = $container.find('.lt-mb-media__status').first();
				var $preview = $container.find('.lt-mb-media__preview').first();

				$input.val('').trigger('change');
				$status.text('Nenhuma imagem selecionada');
				$preview.empty().hide();
				$remove.css('display', 'none');
			});
		})(jQuery);
		</script>
		<?php
	}
}

LT_Bottom_CTA_Admin::init();

