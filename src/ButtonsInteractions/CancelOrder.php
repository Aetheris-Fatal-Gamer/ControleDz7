<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Database\Update;
use Dz7\Services\OrderService;

class CancelOrder {

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $order = OrderService::getOrderByInteraction($interaction);
        if (empty($order)) return;

        if ($order['status'] != OrderService::STATUS_ORDER_PENDING) {
            $messageReply = MessageBuilder::new()->setContent('A encomenda #' . $order['id'] . ' não pode ser cancelar.');
            $interaction->member->user->sendMessage($messageReply, false, null, null, $interaction->message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
            return;
        }

        $nickname = $interaction->member?->nick ?? $interaction->member?->user?->username ?? 'N/A';
        $avatar = $interaction->member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $embed = $interaction->message->embeds[0];
        $embed
            ->setColor('#FF0000')
            ->setTitle('Encomenda cancelada')
            ->setFooter($nickname, $avatar);
        $message = MessageBuilder::new()
            ->setEmbeds([$embed])
            ->setComponents([]);
        $interaction->message->edit($message);

        $update = new Update;
        $update->run('orders', ['status' => OrderService::STATUS_ORDER_CANCELED], 'WHERE id = :id', ['id' => $order['id']]);

        $interaction->acknowledge();
    }
}
