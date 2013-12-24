<?php

/**
 * Settlement
 * 
 * settlement settles fund distribution. this can be CB, driver, or restaurant
 *
 */


// extend our restaurant class with methods that are specific to settlement
// our restaurant class is getting pretty bloated, so lets keep this shit out of it

// get the last payment
Restaurant::extend(['getLastPayment' => function() {
	if (!isset($this->_lastPayment)) {
		$this->_lastPayment = Payment::q('select * from payment where id_restaurant="'.$this->id_restaurant.'" order by date desc limit 1')->get(0);
	}
	return $this->_lastPayment;
}]);

// get orders that are payable; not test, within our date range
Restaurant::extend(['payableOrders' => function($filters = []) {
	if (!isset($this->_payableOrders)) {
		$q = '
			select * from `order`
			where id_restaurant="'.$this->id_restaurant.'"
			and DATE(`date`) >= "' . (new DateTime($filters['start']))->format('Y-m-d') . '"
			and DATE(`date`) <= "' . (new DateTime($filters['end']))->format('Y-m-d') . '"
			and name not like "%test%"
			order by `pay_type` asc, `date` asc
		';
		$orders = Order::q($q);


		// PER ORDER FEE CALCULATION
		// calculate each orders fees
		foreach ($orders as $order) {

			// @note: i dont know what this is at all or why its a fixed 85% -devin
			if (Crunchbutton_Credit::creditByOrderPaidBy($order->id_order, Crunchbutton_Credit::PAID_BY_PROMOTIONAL)) {
				$order->_display_price *= 0.85;
				$order->_display_final_price *= 0.85;

			} else {
				$order->_display_price = $order->price;
				$order->_display_final_price = $order->final_price;
			}

			if ($restaurant->charge_credit_fee == '0') {
				$order->_cc_fee = 0;
			} else {
				$order->_cc_fee = $order->pay_type == 'card' ? .3 + .029 * $order->_display_final_price : 0;				
			}
			$order->_cb_fee = $order->cbFee(); // return ($this->restaurant()->fee_restaurant) * ($this->price) / 100;
			
			if ($order->pay_type == 'card') {
				$order->restaurant()->_settlement_card += $order->_display_final_price;
			} else {
				$order->restaurant()->_settlement_cash += $order->_display_final_price;
			}

			// these are fees we charge the restaurant. we subtract these values from the total
			$order->restaurant()->_settlement_cc_fees += $order->_cc_fee;
			$order->restaurant()->_settlement_cb_fees += $order->_cb_fee;

		}

		$this->_payableOrders = $orders;
	}
	return $this->_payableOrders;
}]);

// get the last payment
Restaurant::extend(['sendPayment' => function($filters = []) {
	if (!isset($this->_lastPayment)) {
		$this->_lastPayment = Payment::q('select * from payment where id_restaurant="'.$this->id_restaurant.'" order by date desc limit 1')->get(0);
	}
	return $this->_lastPayment;
}]);


class Crunchbutton_Settlement extends Cana_Model {
	public function __construct($filters = []) {
		$this->restaurants = self::restaurants($filters);
		
		foreach ($this->restaurants as $restaurant) {
			$restaurant->_settlement_cash = 0;
			$restaurant->_settlement_card = 0;
			$restaurant->_settlement_cc_fees = 0;
			$restaurant->_settlement_cb_fees = 0;

			$restaurant->_payableOrders = $restaurant->payableOrders($filters);

			// PER RESTAURANT FEE CALCULATION
			// these figures are NOT correct. there is alot more that needs to be taken into account
			$restaurant->_settlement_total = 
				$restaurant->_settlement_cash
				+ $restaurant->_settlement_card
				- $restaurant->_settlement_cc_fees
				- $restaurant->_settlement_cb_fees;
		}
	}

	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = '
			select
				restaurant.*, max(p.date) as last_pay, p.id_restaurant as p_id_rest
			from restaurant
			left outer join (select id_restaurant, `date` from `payment`) as p using(id_restaurant)
			inner join restaurant_payment_type rpt on rpt.id_restaurant = restaurant.id_restaurant
			where active=1
		';
		if ($filters['payment_method']) {
			 $q .= ' and `rpt.payment_method`="'.$filters['payment_method'].'" ';
		}
		$q .= '
			group by id_restaurant
			order by
				(case when p_id_rest is null then 1 else 0 end) asc,
				last_pay asc
		';
		
		return Restaurant::q($q);
	}
}