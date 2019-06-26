<?php

namespace Monetha\PS16\Adapter;

use Monetha\Adapter\CallbackUrlInterface;
use Monetha\Adapter\ReturnUrlUrlInterface;
use Monetha\Adapter\InterceptorInterface;
use Monetha\Adapter\OrderAdapterInterface;

class OrderAdapter implements OrderAdapterInterface, CallbackUrlInterface, ReturnUrlUrlInterface {
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var InterceptorInterface[]
     */
    private $items = [];

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $returnUri;

    public function __construct(\Cart $cart, $currencyCode, $baseUrl, $discountAmount, $returnUri) {
        $this->cart = $cart;
        $this->currencyCode = $currencyCode;
        $this->baseUrl = $baseUrl;
        $this->returnUri = $returnUri;

        $items = $this->cart->getProducts();

        $shipping = $cart->getTotalShippingCost();
        if ($shipping) {
            $items[] = [
                'name' => 'Shipping',
                'quantity' => 1,
                'price_wt' => $shipping,
            ];
        }

        if ($discountAmount) {
            $items[] = [
                'name' => 'Discount',
                'quantity' => 1,
                'price_wt' => $discountAmount > 0 ? -$discountAmount : $discountAmount,
            ];
        }

        foreach ($items as $item) {
            $this->items[] = new InterceptorAdapter($item);
        }
    }

    /**
     * @return InterceptorInterface[]
     */
    public function getItems() {
        return $this->items;
    }

    public function getGrandTotalAmount() {
        return $this->cart->getOrderTotal();
    }

    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * @return mixed
     */
    public function getCartId()
    {
        return $this->cart->id;
    }

    public function getCallbackUrl()
    {
        return $this->getBaseUrl() . '/modules/monethagateway/webservices/actions.php';
    }

    public function getReturnUrl()
    {
        return $this->getBaseUrl() . '/' . $this->returnUri;
    }
}
