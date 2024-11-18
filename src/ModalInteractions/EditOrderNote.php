<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Discord\Repository\Interaction\ComponentRepository;
use Dz7\Database\Update;
use Dz7\Services\OrderService;

class EditOrderNote {

    public function handle(Interaction $interaction, Discord $discord, ComponentRepository $components, array $params): void {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $orderId = $params[0] ?? 0;
        $order = OrderService::getOrderById($orderId, $interaction);
        if (empty($order)) return;

        $member = $interaction->member;

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $update = new Update;
        $update->run('orders', ['order_note' => $orderNote], 'WHERE id = :id', ['id' => $order['id']]);

        $embed = new Embed($discord);
        $embed
            ->setTitle('Nova encomenda')
            ->setThumbnail($order['avatar'])
            ->setColor('#288BA8')
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar);

        $codeSaleField = new Field($discord, [
            'name' => 'Código da venda:',
            'value' => '#' . $order['id'],
        ]);
        $embed->addField($codeSaleField);
        $orderPersonNameField = new Field($discord, [
            'name' => 'Vulgo de quem encomendou:',
            'value' => $order['order_person_name'],
        ]);
        $embed->addField($orderPersonNameField);
        if (!empty($order['order_person_contact'])) {
            $orderPersonContactField = new Field($discord, [
                'name' => 'Contato de quem encomendou:',
                'value' => $order['order_person_contact'],
            ]);
            $embed->addField($orderPersonContactField);
        }
        $orderItemsField = new Field($discord, [
            'name' => 'Itens da encomenda:',
            'value' => $order['order_items'],
        ]);
        $embed->addField($orderItemsField);
        $deliveryDateField = new Field($discord, [
            'name' => 'Data da entrega:',
            'value' => $order['delivery_date'],
        ]);
        $embed->addField($deliveryDateField);
        $saleValueField = new Field($discord, [
            'name' => 'Valor da encomenda:',
            'value' => '$ ' . number_format($order['sale_value'], 2, ',', '.'),
        ]);
        $embed->addField($saleValueField);
        if (!empty($orderNote)) {
            $orderNoteValueField = new Field($discord, [
                'name' => 'Observação da encomenda:',
                'value' => $orderNote,
            ]);
            $embed->addField($orderNoteValueField);
        }
        $responsibleSaleField = new Field($discord, [
            'name' => 'Responsável pela encomenda:',
            'value' => $order['nickname'],
        ]);
        $embed->addField($responsibleSaleField);

        $message = MessageBuilder::new()
            ->setEmbeds([$embed]);

        $buttonConfirmOrder = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('ᴄᴏɴꜰɪʀᴍᴀʀ ᴇɴᴄᴏᴍᴇɴᴅᴀ')
            ->setEmoji('✅')
            ->setCustomId('confirm_order');
        $buttonCancelOrder = Button::new(Button::STYLE_DANGER)
            ->setLabel('ᴄᴀɴᴄᴇʟᴀʀ ᴇɴᴄᴏᴍᴇɴᴅᴀ')
            ->setEmoji('✖️')
            ->setCustomId('cancel_order');
        $buttonEditOrder = Button::new(Button::STYLE_SECONDARY)
            ->setLabel('ᴇᴅɪᴛᴀʀ ᴇɴᴄᴏᴍᴇɴᴅᴀ')
            ->setEmoji('📝')
            ->setCustomId('edit_order');
        $buttonEditNoteOrder = Button::new(Button::STYLE_PRIMARY)
            ->setLabel('ᴇᴅɪᴛᴀʀ ᴏʙꜱᴇʀᴠᴀᴄᴀᴏ')
            ->setEmoji('🗒️')
            ->setCustomId('edit_order_note');
        $actionRow = ActionRow::new()
            ->addComponent($buttonConfirmOrder)
            ->addComponent($buttonCancelOrder)
            ->addComponent($buttonEditOrder)
            ->addComponent($buttonEditNoteOrder);
        $message->setComponents([$actionRow]);
        $interaction->message->edit($message)->then(function() use ($interaction) {
            $interaction->acknowledge();
        });
    }
}