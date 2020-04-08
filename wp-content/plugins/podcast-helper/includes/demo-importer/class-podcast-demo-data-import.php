<?php
/**
 * Import demo data of a theme.
 *
 * @package Podcast Helper
 */

if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
	return;
}

add_filter( 'force_filtered_html_on_import' , '__return_false', 20 );

/* Load Importer API */
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) {
		require_once $class_wp_importer;
	}
}

if ( class_exists( 'WP_Importer' ) && ! class_exists( 'Podcast_Demo_Data_Import' ) ) {

	if ( ! defined( 'SIMPLEPIE_NAMESPACE_GOOGLE_PLAY' ) ) {
		define( 'SIMPLEPIE_NAMESPACE_GOOGLE_PLAY', 'http://www.google.com/schemas/play-podcasts/1.0' );
	}

	/**
	 * Podcast Demo Data Importer
	 */
	class Podcast_Demo_Data_Import extends WP_Importer {

		/**
		 * Dispatch the import process depending on th current step.
		 */
		public function dispatch() {
			if ( empty( $_GET['step'] ) ) {
				$step = 0;
			} else {
				$step = (int) $_GET['step'];
			}
			$this->header();
			switch ( $step ) {
				case 0:
					$this->greet();
					break;
				case 1:
					$this->import();
					break;
			}
			$this->footer();
		}

		/**
		 * Display header section.
		 */
		private function header() {
			echo '<div class="wrap">';
			echo '<h2>' . esc_html__( 'Import Demo Data', 'podcast-helper' ) . '</h2>';
		}

		/**
		 * Display header section.
		 */
		private function footer() {
			echo '</div>';
		}

		/**
		 * Display greating for the first import step.
		 */
		private function greet() {
			echo '<div class="narrow">';
			echo '<p>' . esc_html__( 'Before you begin, make sure all the required/recommended plugins are activated.', 'podcast-helper' ) . '</p>';
			echo '<p>' . esc_html__( 'Importing the demo data is the easiest way to setup your theme and quickly make it look like the preview (instead of creating content from scratch). There are a couple of things you should know about this process:', 'podcast-helper' ) . '</p>';
			echo '<ul style="padding-left:15px;list-style-position:inside;list-style-type:square;"><li>' . esc_html__( 'No personal WordPress settings will be modified;', 'podcast-helper' ) . '</li><li>' . esc_html__( 'No existing posts, pages, categories, tags, images, custom post types will be deleted or modified;', 'podcast-helper' ) . '</li><li>' . esc_html__( 'Wait until the import process properly finishes - it can take a couple of minutes, so please be patient.', 'podcast-helper' ) . '</li></ul>';
			echo '<form enctype="multipart/form-data" action="admin.php?import=podcast-demo-data&amp;step=1" method="post" name="podcast-import-demo-data" id="podcast-import-demo-data">';
			wp_nonce_field( 'podcast-import-demo-data' );
			echo '<input type="submit" name="submit" id="submit" class="button button-primary button-hero" value="' . esc_html__( 'Import Demo Data', 'podcast-helper' ) . '" style="float:left">';
			echo '<span class="spinner" style="float:left;margin-top:1.04em"></span><div class="clear"></div>';
			echo '<script>jQuery( document ).on( "submit", "#podcast-import-demo-data", function(e) { jQuery( "#podcast-import-demo-data .button" ).attr("disabled", "disabled" ); jQuery( "#podcast-import-demo-data .spinner" ).addClass( "is-active" ).fadeIn();});</script>';
			echo '</form>';
			echo '</div>';
		}

		/**
		 * Process the actual import (second step of the import).
		 */
		public function import() {
			if ( ! check_admin_referer( 'podcast-import-demo-data' ) ) {
				wp_die( '<p>' . esc_html__( 'Try again.', 'podcast-helper' ) . '<p>', '', array( 'back_link' => true ) );
				return false;
			}

			$import_dir_path = apply_filters( 'theme_demo_data_dir_path', false );
			if ( ! $import_dir_path ) {
				wp_die( '<p>' . esc_html__( 'The theme must set a valid directory for the import files.', 'podcast-helper' ) . '<p>', '', array( 'back_link' => true ) );
				return false;
			}
			$content_data  = $import_dir_path . 'content.xml';
			$widgets_data  = $import_dir_path . apply_filters( 'theme_demo_data_file_widgets', 'widgets.json' );
			$theme_options = $import_dir_path . 'customizer.json';
			return $this->run_import( $content_data, $widgets_data, $theme_options, true );
		}

		/**
		 * Run the import process (can be called by a cron job).
		 */
		public function run_import( $content_data, $widgets_data, $theme_options, $verbose = false ) {
			$this->import_options_data( $theme_options );
			$this->import_widgets_data( $widgets_data );
			$this->import_demo_xml_data( $content_data );

			do_action( 'theme_demo_data_finish_import' );
		}

		/**
		 * Add widget to sidebar.
		 */
		private function add_widget_to_sidebar( $sidebar_slug, $widget_slug, $count_mod, $widget_settings = array() ) {
			$sidebars_widgets = get_option( 'sidebars_widgets' );
			if ( ! isset( $sidebars_widgets[ $sidebar_slug ] ) ) {
				$sidebars_widgets[ $sidebar_slug ] = array( '_multiwidget' => 1 );
			}
			$new_widget = get_option( 'widget_' . $widget_slug );
			if ( ! is_array( $new_widget ) ) {
				$new_widget = array();
			}
			$count                               = count( $new_widget ) + $count_mod + 1;
			$sidebars_widgets[ $sidebar_slug ][] = $widget_slug . '-' . $count;
			$new_widget[ $count ]                = $widget_settings;
			update_option( 'sidebars_widgets', $sidebars_widgets );
			update_option( 'widget_' . $widget_slug, $new_widget );
		}

		/**
		 * Import .xml file.
		 */
		private function import_demo_xml_data( $file ) {
			if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
				define( 'WP_LOAD_IMPORTERS', true );
			}
			require_once ABSPATH . 'wp-admin/includes/import.php';
			$importer_error = false;
			if ( ! class_exists( 'WP_Importer' ) ) {
				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
				if ( file_exists( $class_wp_importer ) ) {
					require_once $class_wp_importer;
				} else {
					$importer_error = true;
				}
			}
			if ( ! class_exists( 'Podcast_WP_Import' ) ) {
				$class_wp_import = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wordpress-importer.php';
				if ( file_exists( $class_wp_import ) ) {
					require_once $class_wp_import;
				} else {
					$importer_error = true;
				}
			}
			if ( $importer_error ) {
				wp_die( esc_html__( 'Error on import.', 'podcast-helper' ), '', array( 'back_link' => true ) );
			} else {
				if ( ! is_file( $file ) ) {
					echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'podcast-helper' ) . '</strong><br>';
					echo esc_html__( 'The XML file containing the dummy content is not available or could not be read.', 'podcast-helper' ) . '</p>';
				} else {
					echo '<p>' . esc_html__( 'Importing post data&hellip;', 'podcast-helper' ) . '</p>';
					$wp_import                    = new Podcast_WP_Import();
					$wp_import->fetch_attachments = true;
					$wp_import->import( $file );
				}
			}
		}

		/**
		 * Import theme options.
		 */
		private function import_options_data( $file ) {
			if ( ! file_exists( $file ) ) {
				wp_die( esc_html__( 'Theme Options import file could not be found. Please try again.', 'podcast-helper' ), '', array( 'back_link' => true ) );
			}
			$data = file_get_contents( $file ); // obtaining local file, so file_get_contents is an obvious choice over any WP_Filesystem.
			$data = json_decode( $data );
			if ( empty( $data ) || ! is_object( $data ) ) {
				wp_die( esc_html__( 'Theme Options import data could not be read. Please try a different file.', 'podcast-helper' ), '', array( 'back_link' => true ) );
			}

			$data = apply_filters( 'podcast_theme_import_options', $data );
			foreach ( $data as $k => $v ) {
				if ( is_array( $v ) ) {
					foreach ( $v as $key => $val ) {
						if ( $key !== $k && $v[ $key ] === $val ) {
							set_theme_mod( $k, $v );
							break;
						}
					}
				} else {
					set_theme_mod( $k, $v );
				}
			}

			echo '<p>' . esc_html__( 'Theme Options imported successfully&hellip;', 'podcast-helper' ) . '</p>';
		}

		/**
		 * Get available widgets.
		 */
		private function available_widgets() {
			global $wp_registered_widget_controls;
			$widget_controls   = $wp_registered_widget_controls;
			$available_widgets = array();
			foreach ( $widget_controls as $widget ) {
				if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
					$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
					$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
				}
			}

			return apply_filters( 'podcast_theme_import_available_widgets', $available_widgets );
		}

		/**
		 * Import widgets from local file.
		 */
		private function import_widgets_data( $file ) {
			if ( ! file_exists( $file ) ) {
				wp_die( esc_html__( 'Widget import file could not be found.', 'podcast-helper' ), '', array( 'back_link' => true ) );
			}
			$data                        = file_get_contents( $file ); // obtaining local file, so file_get_contents is an obvious choice over any WP_Filesystem.
			$data                        = json_decode( $data );
			$this->widget_import_results = $this->import_widgets( $data );

			echo '<p>' . esc_html__( 'Widgets imported successfully&hellip;', 'podcast-helper' ) . '</p>';
		}

		/**
		 * Import widgets downloaded from a file.
		 */
		private function import_widgets( $data ) {
			global $wp_registered_sidebars;

			if ( empty( $data ) || ! is_object( $data ) ) {
				wp_die(
					esc_html__( 'Import data could not be read. Please try a different file.', 'podcast-helper' ),
					'',
					array(
						'back_link' => true,
					)
				);
			}

			$data              = apply_filters( 'podcast_theme_import_widget_data', $data );
			$available_widgets = $this->available_widgets();
			$widget_instances  = array();
			foreach ( $available_widgets as $widget_data ) {
				$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
			}

			$results = array();
			foreach ( $data as $sidebar_id => $widgets ) {

				if ( 'wp_inactive_widgets' === $sidebar_id ) {
					continue;
				}

				if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
					$sidebar_available    = true;
					$use_sidebar_id       = $sidebar_id;
					$sidebar_message_type = 'success';
					$sidebar_message      = '';
				} else {
					$sidebar_available    = false;
					$use_sidebar_id       = 'wp_inactive_widgets';
					$sidebar_message_type = 'error';
					$sidebar_message      = esc_html__( 'Widget area does not exist in theme (using Inactive)', 'podcast-helper' );
				}

				$results[ $sidebar_id ]['name']         = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id;
				$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
				$results[ $sidebar_id ]['message']      = $sidebar_message;
				$results[ $sidebar_id ]['widgets']      = array();

				foreach ( $widgets as $widget_instance_id => $widget ) {

					$fail = false;

					$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
					$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

					if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
						$fail                = true;
						$widget_message_type = 'error';
						$widget_message      = esc_html__( 'Site does not support widget', 'podcast-helper' );
					}

					$widget = apply_filters( 'podcast_theme_import_widget_settings', $widget );
					$widget = json_decode( wp_json_encode( $widget ), true );
					$widget = apply_filters( 'podcast_theme_import_widget_settings_array', $widget );

					if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {
						$sidebars_widgets = get_option( 'sidebars_widgets' );
						$sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array();

						$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : array();
						foreach ( $single_widget_instances as $check_id => $check_widget ) {
							if ( in_array( "$id_base-$check_id", $sidebar_widgets, true ) && (array) $widget === $check_widget ) {
								$fail                = true;
								$widget_message_type = 'warning';
								$widget_message      = esc_html__( 'Widget already exists', 'podcast-helper' );
								break;
							}
						}
					}

					if ( ! $fail ) {
						$single_widget_instances   = get_option( 'widget_' . $id_base );
						$single_widget_instances   = ! empty( $single_widget_instances ) ? $single_widget_instances : array(
							'_multiwidget' => 1,
						);
						$single_widget_instances[] = $widget;

						end( $single_widget_instances );
						$new_instance_id_number = key( $single_widget_instances );

						if ( '0' === strval( $new_instance_id_number ) ) {
							$new_instance_id_number                             = 1;
							$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
							unset( $single_widget_instances[0] );
						}

						if ( isset( $single_widget_instances['_multiwidget'] ) ) {
							$multiwidget = $single_widget_instances['_multiwidget'];
							unset( $single_widget_instances['_multiwidget'] );
							$single_widget_instances['_multiwidget'] = $multiwidget;
						}

						update_option( 'widget_' . $id_base, $single_widget_instances );

						$sidebars_widgets = get_option( 'sidebars_widgets' );

						if ( ! $sidebars_widgets ) {
							$sidebars_widgets = array();
						}

						$new_instance_id                       = $id_base . '-' . $new_instance_id_number;
						$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id;
						update_option( 'sidebars_widgets', $sidebars_widgets );

						$after_widget_import = array(
							'sidebar'           => $use_sidebar_id,
							'sidebar_old'       => $sidebar_id,
							'widget'            => $widget,
							'widget_type'       => $id_base,
							'widget_id'         => $new_instance_id,
							'widget_id_old'     => $widget_instance_id,
							'widget_id_num'     => $new_instance_id_number,
							'widget_id_num_old' => $instance_id_number,
						);
						do_action( 'wie_after_widget_import', $after_widget_import );

						if ( $sidebar_available ) {
							$widget_message_type = 'success';
							$widget_message      = esc_html__( 'Imported', 'podcast-helper' );
						} else {
							$widget_message_type = 'warning';
							$widget_message      = esc_html__( 'Imported to Inactive', 'podcast-helper' );
						}
					}

					$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name']         = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base;
					$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title']        = ! empty( $widget['title'] ) ? $widget['title'] : esc_html__( 'No Title', 'podcast-helper' );
					$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message_type'] = $widget_message_type;
					$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message']      = $widget_message;
				}
			}

			do_action( 'podcast_theme_import_widget_after_import' );
			return apply_filters( 'podcast_theme_import_widget_results', $results );
		}
	}

	$podcast_demo_data_import = new Podcast_Demo_Data_Import();

	register_importer( 'podcast-demo-data', __( 'Demo Data', 'podcast-helper' ), __( 'Importing the demo data is the easiest way to setup your site and quickly make it look like the theme\'s demo preview.', 'podcast-helper' ), array( $podcast_demo_data_import, 'dispatch' ) );

} // class_exists( 'WP_Importer' )
