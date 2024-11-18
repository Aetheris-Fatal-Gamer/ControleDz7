<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Services\OrderService;

class EditOrderNote {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $order = OrderService::getOrderByInteraction($interaction);
        if (empty($order)) return;

        if ($order['status'] != OrderService::STATUS_ORDER_PENDING) {
            $messageReply = MessageBuilder::new()->setContent('A encomenda #' . $order['id'] . ' não pode ser editada a observação.');
            $interaction->member->user->sendMessage($messageReply, false, null, null, $interaction->message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
            return;
        }

        $orderNoteInput = TextInput::new('Observação da encomenda:', TextInput::STYLE_SHORT, 'orderNote')
            ->setRequired(false)
            ->setMaxLength(255)
            ->setPlaceholder('ᴇx: 10 Fives foram entregues')
            ->setValue($order['order_note']);
        $orderNoteRow = ActionRow::new()->addComponent($orderNoteInput);
        $this->actionsRows[] = $orderNoteRow;

        $interaction->showModal('Editar observação da encomenda - #' . $order['id'], 'edit_order_note-' . $order['id'], $this->actionsRows);
    }
}
