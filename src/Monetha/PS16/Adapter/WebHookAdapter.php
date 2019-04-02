<?php

namespace Monetha\PS16\Adapter;

use Monetha\Constants\EventType;
use Monetha\Constants\Resource;
use Order;

class WebHookAdapter {
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order = null)
    {
        $this->order = $order;
    }

    public function processWebHook($data)
    {
        switch ($data->resource) {
            case Resource::ORDER:
                switch ($data->event) {
                    case EventType::CANCELLED:
                        return $this->cancelOrder($data->payload->note);

                    case EventType::FINALIZED:
                        return $this->finalizeOrder();

                    case EventType::MONEY_AUTHORIZED:
                        return $this->finalizeOrderByCard();

                    default:
                        throw new \Exception('Bad action type');
                }

            default:
                throw new \Exception('Bad resource');
        }
    }

    private function cancelOrder($note)
    {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(6, $this->getOrderId(), true);
        return $history->save();
    }

    private function finalizeOrder()
    {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(2, $this->getOrderId(), true);
        return $history->save();
    }

    private function finalizeOrderByCard()
    {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(2, $this->getOrderId(), true);
        return $history->save();
    }

    private function getOrderId()
    {
        return (int) $this->order->id;
    }
}
