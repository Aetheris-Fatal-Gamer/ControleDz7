<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Database\Update;
use Dz7\Services\OrderService;
use Dz7\Util;

class ConfirmOrder {

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction, $discord);
    }

    private function buildActionsRows(Interaction $interaction, Discord $discord): void {
        $order = OrderService::getOrderByInteraction($interaction);
        if (empty($order)) return;

        if ($order['status'] != OrderService::STATUS_ORDER_PENDING) {
            $messageReply = MessageBuilder::new()->setContent('A encomenda #' . $order['id'] . ' já foi confirmada.');
            $interaction->member->user->sendMessage($messageReply, false, null, null, $interaction->message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
            return;
        }

        $nickname = $interaction->member?->nick ?? $interaction->member?->user?->username ?? 'N/A';
        $avatar = $interaction->member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $embed = $interaction->message->embeds[0];
        $embed
            ->setColor('#3EFF00')
            ->setTitle('Encomenda confimada')
            ->setFooter($nickname, $avatar);
        $message = MessageBuilder::new()
            ->setEmbeds([$embed])
            ->setComponents([]);
        $interaction->message->edit($message);

        $update = new Update;
        $update->run('orders', ['status' => OrderService::STATUS_ORDER_CONFIRMED, 'sales_date' => Util::now()], 'WHERE id = :id', ['id' => $order['id']]);

        $embed = new Embed($discord);
        $embed
            ->setTitle('Venda confirmada')
            ->setThumbnail($order['avatar'])
            ->setColor('#3EFF00')
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar);

        $codeSaleField = new Field($discord, [
            'name' => 'Código da encomenda:',
            'value' => '[#' . $order['id'] . '](' . $interaction->message->link . ')',
        ]);
        $embed->addField($codeSaleField);

        $orderPersonNameField = new Field($discord, [
            'name' => 'Comprador:',
            'value' => $order['order_person_name'],
        ]);
        $embed->addField($orderPersonNameField);

        $orderItemsField = new Field($discord, [
            'name' => 'Itens da venda:',
            'value' => $order['order_items'],
        ]);
        $embed->addField($orderItemsField);

        $saleValueField = new Field($discord, [
            'name' => 'Valor da venda:',
            'value' => '$ ' . number_format($order['sale_value'], 2, ',', '.'),
        ]);
        $embed->addField($saleValueField);

        $responsibleSaleField = new Field($discord, [
            'name' => 'Vendedor:',
            'value' => $order['nickname'],
        ]);
        $embed->addField($responsibleSaleField);

        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

        $channelSale = $interaction->guild->channels->get('id', '969764159938003044');
        if ($channelSale) {
            $channelSale->sendMessage($message);
        }

        $interaction->acknowledge();
    }
}
