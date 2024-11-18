<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Services\OrderService;

class EditOrder {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $order = OrderService::getOrderByInteraction($interaction);
        if (empty($order)) return;

        if ($order['status'] != OrderService::STATUS_ORDER_PENDING) {
            $messageReply = MessageBuilder::new()->setContent('A encomenda #' . $order['id'] . ' não pode ser cancelada.');
            $interaction->member->user->sendMessage($messageReply, false, null, null, $interaction->message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
            return;
        }

        $orderItemsInput = TextInput::new('ɪᴛᴇɴꜱ ᴅᴀ ᴇɴᴄᴏᴍᴇɴᴅᴀ:', TextInput::STYLE_SHORT, 'orderItems')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: 5 ꜰɪᴠᴇ, 10 ꜱɪɢ')
            ->setValue($order['order_items']);
        $quantityRow = ActionRow::new()->addComponent($orderItemsInput);
        $this->actionsRows[] = $quantityRow;

        $deliveryDateInput = TextInput::new('ᴅᴀᴛᴀ ᴅᴀ ᴇɴᴛʀᴇɢᴀ:', TextInput::STYLE_SHORT, 'deliveryDate')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: ᴀ ɴᴏɪᴛᴇ ɴᴏ ᴅɪᴀ 20/01/2023')
            ->setValue($order['delivery_date']);
        $currentTotalRow = ActionRow::new()->addComponent($deliveryDateInput);
        $this->actionsRows[] = $currentTotalRow;

        $saleValueInput = TextInput::new('ᴠᴀʟᴏʀ ᴅᴀ ᴇɴᴄᴏᴍᴇɴᴅᴀ:', TextInput::STYLE_SHORT, 'saleValue')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: 356000 (ᴏ ᴠᴀʟᴏʀ ᴘʀᴇᴄɪꜱᴀ ᴇꜱᴛᴀʀ ᴇᴍ ɴᴜᴍᴇʀᴀʟ)')
            ->setValue($order['sale_value']);
        $currentTotalRow = ActionRow::new()->addComponent($saleValueInput);
        $this->actionsRows[] = $currentTotalRow;

        $interaction->showModal('ᴇᴅɪᴛᴀʀ ᴇɴᴄᴏᴍᴇɴᴅᴀ - #' . $order['id'], 'edit_order-' . $order['id'], $this->actionsRows);
    }
}
