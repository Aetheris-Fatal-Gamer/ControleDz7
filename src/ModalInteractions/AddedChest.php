<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class AddedChest {

    public function handle(Interaction $interaction, Discord $discord, $components): void {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $passport = $passport ?? Util::extractPassport($interaction);
        
        if (!is_numeric($quantity) || !is_numeric($currentTotal) || !is_numeric($passport) || empty($item)) {
            return;
        }
        $member = $interaction->member;
        Util::setPassportInName($member, $passport);

        $passportField = new Field($discord, [
            'name' => 'Passaporte:',
            'value' => $passport,
        ]);
        $itemField = new Field($discord, [
            'name' => 'Item adicionado:',
            'value' => $item,
        ]);
        $quantityField = new Field($discord, [
            'name' => 'Quantidade:',
            'value' => $quantity,
        ]);
        $currentTotalField = new Field($discord, [
            'name' => 'Total no baú:',
            'value' => $currentTotal,
        ]);

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $embed = new Embed($discord);
        $embed
            ->setTitle('Adicionado no baú')
            ->setThumbnail($avatar)
            ->setColor('#3EFF00')
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar)
            ->addField($passportField)
            ->addField($itemField)
            ->addField($quantityField)
            ->addField($currentTotalField);
        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

        $channel = $interaction->guild->channels->get('name', '・ʙᴀᴜ-' . $passport);
        if (!empty($channel)) {
            $channel->sendMessage($message)->then(function() use ($interaction) {
                $interaction->acknowledge();
            });
        } else {
            $newChannel = $interaction->guild->channels->create([
                'name' => '・ʙᴀᴜ-' . $passport,
                'topic' => 'Adição e retirada de itens no baú ' . $passport,
                'type' => Channel::TYPE_TEXT,
                'parent_id' => $_ENV['CATEGORY_CONTROL_CHEST'],
                'nsfw' => false,
            ]);
            $interaction->guild->channels->save($newChannel)->done(function(Channel $channel) use ($message, $interaction) {
                $channel->sendMessage($message);
                $channel->setPermissions($interaction->member, [
                    'view_channel',
                    'read_message_history',
                ], [
                    'send_messages',
                    'add_reactions',
                ])->done(function() use ($interaction) {
                    $interaction->acknowledge();
                });
            });
        }
    }
}