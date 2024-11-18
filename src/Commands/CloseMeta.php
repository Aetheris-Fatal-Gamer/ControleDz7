<?php

namespace Dz7\Commands;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Dz7\Meta;

class CloseMeta {

    public function run(array $args, Message $message, Discord $discord): void {
        if ($message->channel->id !== $_ENV['CHANNEL_ALLOW_COMMAND_META']) {
            $message->member->sendMessage('⁉️ Você não pode executar esse comando no canal **' . $message->channel->name . '**');
            $message->delete();
            return;
        }

        if (empty($_ENV['CATEGORY_META_RECORD'])) {
            $message->reply('⁉️ Não consegui encontrar a categoria de metas.');
            return;
        }

        $passports = [];
        foreach ($message->guild->channels as $channel) {
            if ($channel->parent_id !== $_ENV['CATEGORY_META_RECORD']) {
                continue;
            }
            $passport = str_replace('・ᴍᴇᴛᴀ-', '', $channel->name);
            if (!is_numeric($passport)) {
                continue;
            }
            $passports[] = $passport;
        }
        
        if (empty($passports)) {
            $message->reply('⁉️ Não consegui encontrar nenhuma sala de meta.');
            return;
        }
        sort($passports);

        // No futuro será enviado via formulário
        $unitaryValue = 250;
        $percentage = 30;
        $qtdMeta = 1000;
        
        $member = $message->member;
        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $metaObject = new Meta;
        $metas = $metaObject->searchMetaPassports($passports);
        foreach ($passports as $passport) {
            $quantity = $metas[$passport] ?? 0;
            
            $subTotal = $quantity * $unitaryValue;
            $total = ($subTotal * $percentage) / 100;

            $color = ($quantity >= $qtdMeta) ? '#00BFFF' : '#FF0000';
            $status = ($quantity >= $qtdMeta) ? 'Bateu da meta' : 'Não bateu a meta';

            $channel = $message->guild->channels->get('name', '・ᴍᴇᴛᴀ-' . $passport);
            if (empty($channel)) {
                continue;
            }
            
            $minimumMetaField = new Field($discord, [
                'name' => 'Quantidade para bater a meta:',
                'value' => $qtdMeta,
            ]);
            $statusField = new Field($discord, [
                'name' => 'Status da meta:',
                'value' => $status,
            ]);
            $quantityField = new Field($discord, [
                'name' => 'Quantidade feita:',
                'value' => $quantity,
            ]);
            $calcField = new Field($discord, [
                'name' => 'Cálculo da meta:',
                'value' => "$quantity x $unitaryValue = R$ $subTotal\n$subTotal x $percentage% = R$ " . number_format($total, 2, ',', '.'),
            ]);
            $finalValueMeta = new Field($discord, [
                'name' => 'Valor final da meta:',
                'value' => 'R$ ' . number_format($total, 2, ',', '.'),
            ]);

            $embed = new Embed($discord);
            $embed
                ->setTitle('Fechamento de meta')
                ->setThumbnail($avatar)
                ->setColor($color)
                ->setTimestamp(time())
                ->setFooter($nickname, $avatar)
                ->addField($minimumMetaField)
                ->addField($statusField)
                ->addField($quantityField)
                ->addField($calcField)
                ->addField($finalValueMeta)
                ;

            $messageBuild = MessageBuilder::new();
            $messageBuild->setEmbeds([$embed]);
            $channel->sendMessage($messageBuild);
            $channel->sendMessage('https://i.imgur.com/ljXMXSp.png');
        }
        $metaObject->closeMetaPassports($passports, $percentage, $unitaryValue, $qtdMeta);
        $message->delete();
    }
}