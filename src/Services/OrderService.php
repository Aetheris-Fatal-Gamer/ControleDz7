<?php

namespace Dz7\Services;

use Discord\Parts\Interactions\Interaction;
use Dz7\Database\Read;
use Dz7\Util;
use stdClass;

class OrderService {

    public const STATUS_ORDER_PENDING = 'P';
    public const STATUS_ORDER_CONFIRMED = 'R';
    public const STATUS_ORDER_CANCELED = 'C';

    public static function getOrderByInteraction(Interaction $interaction): array {
        $passport = Util::extractPassport($interaction);
        if (!is_numeric($passport)) return [];

        $embed = $interaction?->message?->embeds[0] ?? null;
        if (empty($embed)) return [];

        $codeSale = $embed?->fields['Código da venda:'] ?? new stdClass;
        $codeSale = $codeSale?->value;
        $codeSale = str_replace('#', '', $codeSale);
        if (empty($codeSale) || !is_numeric($codeSale)) return [];

        $tableOrders = 'orders';

        $read = new Read;
        $order = $read->run("SELECT * FROM $tableOrders WHERE id = :id", ['id' => $codeSale])->getResult();
        $order = $order[0] ?? null;
        if (empty($order)) return [];
        $order = (array) $order;
        return $order;
    }

    public static function getOrderById(mixed $orderId, Interaction $interaction): array {
        $passport = Util::extractPassport($interaction);
        if (!is_numeric($passport)) return [];

        if (empty($orderId) || !is_numeric($orderId)) return [];

        $tableOrders = 'orders';

        $read = new Read;
        $order = $read->run("SELECT * FROM $tableOrders WHERE id = :id", ['id' => $orderId])->getResult();
        $order = $order[0] ?? null;
        if (empty($order)) return [];
        $order = (array) $order;
        return $order;
    }
}