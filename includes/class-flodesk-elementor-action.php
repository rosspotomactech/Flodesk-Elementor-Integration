<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use ElementorPro\Modules\Forms\Classes\Action_Base;

class Flodesk_Elementor_Action extends Action_Base {

	public function get_name() {
		return 'flodesk';
	}

	// FIX: Elementor Form Actions require get_label(), not get_title()
	public function get_label() {
		return __( 'Flodesk', 'flodesk-elementor' );
	}

	public function register_settings_section( $widget ) {
		$api = new Flodesk_API();
		// Fetch segments from API for dropdown mapping
		$segment_options = $api->get_segments_options();

		$widget->start_controls_section(
			'section_flodesk',
			array(
				'label' => __( 'Flodesk', 'flodesk-elementor' ),
				'condition' => array(
					'submit_actions' => $this->get_name(),
				),
			)
		);

		$widget->add_control(
			'flodesk_email_field',
			array(
				'label'       => __( 'Email Field ID', 'flodesk-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'email',
				'description' => __( 'The Form Field ID containing the email address.', 'flodesk-elementor' ),
			)
		);

		$widget->add_control(
			'flodesk_first_name_field',
			array(
				'label'       => __( 'First Name Field ID', 'flodesk-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'first_name',
			)
		);

		$widget->add_control(
			'flodesk_last_name_field',
			array(
				'label'       => __( 'Last Name Field ID', 'flodesk-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'last_name',
			)
		);

		// Updated to multi-select dropdown populated by the API
		$widget->add_control(
			'flodesk_static_segments',
			array(
				'label'       => __( 'Static Segment Assignment', 'flodesk-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $segment_options,
				'description' => __( 'Select Flodesk Segments to assign to ALL submissions.', 'flodesk-elementor' ),
			)
		);

		$widget->add_control(
			'flodesk_double_optin',
			array(
				'label'        => __( 'Double Opt-In', 'flodesk-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'flodesk-elementor' ),
				'label_off'    => __( 'No', 'flodesk-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		// Repeater for Custom Fields Mapping
		$cf_repeater = new Repeater();
		$cf_repeater->add_control(
			'flodesk_custom_key',
			array(
				'label' => __( 'Flodesk Custom Field Key', 'flodesk-elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$cf_repeater->add_control(
			'elementor_field_id',
			array(
				'label' => __( 'Elementor Form Field ID', 'flodesk-elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$widget->add_control(
			'flodesk_custom_fields_map',
			array(
				'label'       => __( 'Custom Fields Mapping', 'flodesk-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $cf_repeater->get_controls(),
				'title_field' => '{{{ flodesk_custom_key }}} &larr; {{{ elementor_field_id }}}',
			)
		);

		// Repeater for Conditional Segment Rules
		$segment_repeater = new Repeater();
		$segment_repeater->add_control(
			'field_id',
			array(
				'label'       => __( 'Form Field ID', 'flodesk-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'e.g., role', 'flodesk-elementor' ),
			)
		);
		$segment_repeater->add_control(
			'field_value',
			array(
				'label'       => __( 'Field Value to Match', 'flodesk-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'e.g., Student', 'flodesk-elementor' ),
			)
		);
		
		// Updated to single-select dropdown populated by the API
		$segment_repeater->add_control(
			'segment_id',
			array(
				'label'       => __( 'Flodesk Segment Target', 'flodesk-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $segment_options,
				'description' => __( 'Select the corresponding segment.', 'flodesk-elementor' ),
			)
		);

		$widget->add_control(
			'flodesk_conditional_segments',
			array(
				'label'       => __( 'Conditional Segment Mapping', 'flodesk-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $segment_repeater->get_controls(),
				'title_field' => 'If {{{ field_id }}} == "{{{ field_value }}}" &rarr; Assign Segment',
			)
		);

		$widget->end_controls_section();
	}

	public function on_export( $element ) {}

	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );
		$raw_fields = $record->get( 'fields' );

		$fields = array();
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = sanitize_text_field( $field['value'] );
		}

		$email_field_id      = ! empty( $settings['flodesk_email_field'] ) ? $settings['flodesk_email_field'] : 'email';
		$first_name_field_id = ! empty( $settings['flodesk_first_name_field'] ) ? $settings['flodesk_first_name_field'] : 'first_name';
		$last_name_field_id  = ! empty( $settings['flodesk_last_name_field'] ) ? $settings['flodesk_last_name_field'] : 'last_name';

		$email = isset( $fields[ $email_field_id ] ) ? sanitize_email( $fields[ $email_field_id ] ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			$ajax_handler->add_admin_error_message( __( 'Flodesk Integration: Valid email is required.', 'flodesk-elementor' ) );
			return;
		}

		$segment_ids = array();

		// 1. Process Static Segments (Now returns an array from SELECT2)
		if ( ! empty( $settings['flodesk_static_segments'] ) && is_array( $settings['flodesk_static_segments'] ) ) {
			$segment_ids = array_merge( $segment_ids, $settings['flodesk_static_segments'] );
		}

		// 2. Process Conditional Segments
		if ( ! empty( $settings['flodesk_conditional_segments'] ) && is_array( $settings['flodesk_conditional_segments'] ) ) {
			foreach ( $settings['flodesk_conditional_segments'] as $rule ) {
				$f_id    = isset( $rule['field_id'] ) ? trim( $rule['field_id'] ) : '';
				$f_val   = isset( $rule['field_value'] ) ? trim( $rule['field_value'] ) : '';
				$seg_id  = isset( $rule['segment_id'] ) ? trim( $rule['segment_id'] ) : '';

				if ( $f_id && $seg_id && isset( $fields[ $f_id ] ) ) {
					if ( strtolower( trim( $fields[ $f_id ] ) ) === strtolower( $f_val ) ) {
						$segment_ids[] = $seg_id;
					}
				}
			}
		}

		$segment_ids = array_values( array_unique( $segment_ids ) );

		// 3. Process Custom Fields
		$custom_fields = array();
		if ( ! empty( $settings['flodesk_custom_fields_map'] ) && is_array( $settings['flodesk_custom_fields_map'] ) ) {
			foreach ( $settings['flodesk_custom_fields_map'] as $map ) {
				$flodesk_key = isset( $map['flodesk_custom_key'] ) ? trim( $map['flodesk_custom_key'] ) : '';
				$elem_id     = isset( $map['elementor_field_id'] ) ? trim( $map['elementor_field_id'] ) : '';

				if ( $flodesk_key && $elem_id && isset( $fields[ $elem_id ] ) ) {
					$custom_fields[ $flodesk_key ] = $fields[ $elem_id ];
				}
			}
		}

		$payload = array(
			'email'        => $email,
			'first_name'   => isset( $fields[ $first_name_field_id ] ) ? $fields[ $first_name_field_id ] : '',
			'last_name'    => isset( $fields[ $last_name_field_id ] ) ? $fields[ $last_name_field_id ] : '',
			'double_optin' => ( isset( $settings['flodesk_double_optin'] ) && 'yes' === $settings['flodesk_double_optin'] ),
			'optin_ip'     => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		);

		if ( ! empty( $segment_ids ) ) {
			$payload['segment_ids'] = $segment_ids;
		}

		if ( ! empty( $custom_fields ) ) {
			$payload['custom_fields'] = $custom_fields;
		}

		$api    = new Flodesk_API();
		$result = $api->upsert_subscriber( $payload );

		if ( is_wp_error( $result ) ) {
			$ajax_handler->add_admin_error_message( 'Flodesk API Error: ' . $result->get_error_message() );
		}
	}
}
