<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Database\Create;
use Dz7\Services\OrderService;
use Dz7\Util;

class NewOrder {

    public function handle(Interaction $interaction, Discord $discord, $components): void {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $passport = Util::extractPassport($interaction);

        if (!is_numeric($saleValue) || !is_numeric($passport)) {
            return;
        }
        $member = $interaction->member;

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $create = new Create;
        $orderId = $create->run('orders', [
            'passport' => $passport,
            'nickname' => $nickname,
            'avatar' => $avatar,
            'order_person_name' => $orderPersonName,
            'order_person_contact' => $orderPersonContact,
            'order_items' => $orderItems,
            'delivery_date' => $deliveryDate,
            'sale_value' => $saleValue,
            'status' => OrderService::STATUS_ORDER_PENDING,
            'date' => Util::now(),
        ])->getResult();
        if (empty($orderId)) {
            return;
        }

        $embed = new Embed($discord);
        $embed
            ->setTitle('Nova encomenda')
            ->setThumbnail($avatar)
            ->setColor('#288BA8')
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar);

        $codeSaleField = new Field($discord, [
            'name' => 'Código da venda:',
            'value' => '#' . $orderId,
        ]);
        $embed->addField($codeSaleField);

        $orderPersonNameField = new Field($discord, [
            'name' => 'Vulgo de quem encomendou:',
            'value' => $orderPersonName,
        ]);
        $embed->addField($orderPersonNameField);

        if (!empty($orderPersonContact)) {
            $orderPersonContactField = new Field($discord, [
                'name' => 'Contato de quem encomendou:',
                'value' => $orderPersonContact,
            ]);
            $embed->addField($orderPersonContactField);
        }

        $orderItemsField = new Field($discord, [
            'name' => 'Itens da encomenda:',
            'value' => $orderItems,
        ]);
        $embed->addField($orderItemsField);

        $deliveryDateField = new Field($discord, [
            'name' => 'Data da entrega:',
            'value' => $deliveryDate,
        ]);
        $embed->addField($deliveryDateField);

        $saleValueField = new Field($discord, [
            'name' => 'Valor da encomenda:',
            'value' => '$ ' . number_format($saleValue, 2, ',', '.'),
        ]);
        $embed->addField($saleValueField);

        $responsibleSaleField = new Field($discord, [
            'name' => 'Responsável pela encomenda:',
            'value' => $nickname,
        ]);
        $embed->addField($responsibleSaleField);

        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

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

        $channelOrder = $interaction->guild->channels->get('id', '969764013456101437');
        if ($channelOrder) {
            $channelOrder->sendMessage($message);
        }
        $interaction->acknowledge();
    }
}