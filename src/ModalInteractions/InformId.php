<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class InformId {

    public function handle(Interaction $interaction, Discord $discord, $components): void {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        if (empty($name) || !is_numeric($passport)) {
            return;
        }
        $member = $interaction->member;

        $channel = $interaction->guild->channels->get('id', $_ENV['CHANNEL_REGISTRO_ID']);
        if (!$channel) {
            return;
        }

        $nameField = new Field($discord, [
            'name' => 'Nome:',
            'value' => $name,
        ]);
        $passportField = new Field($discord, [
            'name' => 'Passaporte:',
            'value' => $passport,
        ]);

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';
        $embed = new Embed($discord);
        $embed
            ->setTitle('Registro de Nome e Id')
            ->setThumbnail($avatar)
            ->setColor(0x00AE86)
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar)
            ->addField($nameField)
            ->addField($passportField);

        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

        $member->setNickname($passport . ' - ' . $name, 'Registro do passaporte')->done(function() use ($channel, $message, $interaction, $member) {
            if (!empty($_ENV['REGISTER_ROLE_ID'])) {
                $member->addRole($_ENV['REGISTER_ROLE_ID']);
            }
            $channel->sendMessage($message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
        });            
    }
}