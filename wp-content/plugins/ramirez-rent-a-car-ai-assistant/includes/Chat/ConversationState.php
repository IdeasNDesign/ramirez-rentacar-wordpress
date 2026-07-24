<?php
namespace BreakTheMold\RamirezAIAssistant\Chat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ConversationState {
	public $language = 'es';
	public $intent = 'book_vehicle';
	public $stage = 'welcome'; // welcome, need_gathering, vehicle_selection, lead_data, hold_payment, confirmed
	
	// Booking details
	public $pickup_location_id = null;
	public $return_location_id = null;
	public $pickup_at = '';
	public $return_at = '';
	public $passengers = 2;
	public $luggage = 2;
	public $arrival_type = 'flight'; // flight, ferry, cruise
	public $arrival_details = ''; // flight number, cruise ship name
	
	// Preferences
	public $vehicle_preferences = [
		'automatic' => true,
		'four_wheel_drive' => false
	];

	// Selection and Quote
	public $recommended_vehicle_ids = [];
	public $selected_vehicle_id = null;
	public $quote_id = null;
	public $quote_number = '';
	public $total_amount = 0.00;
	
	// Lead/Customer information
	public $customer_id = null;
	public $first_name = '';
	public $last_name = '';
	public $email = '';
	public $phone = '';
	public $whatsapp = '';
	public $country = '';
	
	// Transaction hold / Payment
	public $reservation_id = null;
	public $reservation_reference = '';
	public $hold_id = null;
	public $hold_expires_at = '';
	public $payment_status = 'unpaid'; // unpaid, processing, paid

	public $history = [];

	public function to_array(): array {
		return get_object_vars( $this );
	}

	public function from_array( array $data ) {
		foreach ( $data as $key => $val ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $val;
			}
		}
	}
}
