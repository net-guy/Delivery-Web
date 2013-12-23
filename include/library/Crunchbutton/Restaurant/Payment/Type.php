<?php

class Crunchbutton_Restaurant_Payment_Type extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_payment_type')
			->idVar('id_restaurant_payment_type')
			->load($id);
	}

	public function getRecipientInfo(){
		if( $this->stripe_id && !$this->_stripe_recipient ){
			try{
				$this->_stripe_recipient = Stripe_Recipient::retrieve( $this->stripe_id );
			} catch (Exception $e) {
				print_r($e);
				exit;
			}
		}
		return $this->_stripe_recipient;
	}

	function byRestaurant( $id_restaurant ){
		if( $id_restaurant ){
			$payment = Crunchbutton_User_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant = ' . $id_restaurant . ' LIMIT 1' );
			if( !$payment->count() ){
				return new Crunchbutton_Restaurant_Payment_Type();
			} else{
				return Crunchbutton_Restaurant_Payment_Type::o( $payment->id_restaurant_payment_type );
			}
		}
		return false;
	}
}