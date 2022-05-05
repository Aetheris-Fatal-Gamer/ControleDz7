<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class PersonalWithdrawal {

    public function handle(Interaction $interaction, Discord $discord, $components): void {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $passport = Util::extractPassport($interaction);
        if (!is_numeric($quantity) || !is_numeric($passport) || empty($item)) {
            return;
        }
        $member = $interaction->member;

        $passportField = new Field($discord, [
            'name' => 'Passaporte:',
            'value' => $passport,
        ]);
        $itemField = new Field($discord, [
            'name' => 'Item retirado:',
            'value' => $item,
        ]);
        $quantityField = new Field($discord, [
            'name' => 'Quantidade:',
            'value' => $quantity,
        ]);

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $embed = new Embed($discord);
        $embed
            ->setTitle('Retirada do pessoal')
            ->setThumbnail($avatar)
            ->setColor('#FF0000')
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar)
            ->addField($passportField)
            ->addField($itemField)
            ->addField($quantityField);
        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

        if (!empty($_ENV['CHANNEL_CONTROL_PERSONAL_CHEST'])) {
            $channelGeral = $interaction->guild->channels->get('id', $_ENV['CHANNEL_CONTROL_PERSONAL_CHEST']);
            if ($channelGeral) {
                $channelGeral->sendMessage($message);
            }
        }
        $interaction->acknowledge();
    }
}