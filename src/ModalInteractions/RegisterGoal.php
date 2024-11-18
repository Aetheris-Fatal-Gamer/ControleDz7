<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Meta;
use Dz7\Util;

class RegisterGoal
{

    public function handle(Interaction $interaction, Discord $discord, $components): void
    {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $passport = $passport ?? Util::extractPassport($interaction);

        // gunTrigger, gunPart, ironIngot, aluminumPlate, copperPlate
        if (empty($gunTrigger) && empty($gunPart) && empty($ironIngot) && empty($aluminumPlate) && empty($copperPlate)) {
            return;
        }

        if (
            !empty($gunTrigger) && !is_numeric($gunTrigger) ||
            !empty($gunPart) && !is_numeric($gunPart) ||
            !empty($ironIngot) && !is_numeric($ironIngot) ||
            !empty($aluminumPlate) && !is_numeric($aluminumPlate) ||
            !empty($copperPlate) && !is_numeric($copperPlate)
        ) {
            return;
        }

        if (!is_numeric($passport)) {
            return;
        }
        $member = $interaction->member;
        Util::setPassportInName($member, $passport);


        $embed = new Embed($discord);
        $passportField = new Field($discord, [
            'name' => 'Passaporte:',
            'value' => $passport,
        ]);
        $embed->addField($passportField);

        $items = [];
        if (!empty($gunTrigger)) {
            $items[] = '- Gatilho: **' . $gunTrigger . '**';
        }
        if (!empty($gunPart)) {
            $items[] = '- Part. Arma: **' . $gunPart . '**';
        }
        if (!empty($ironIngot)) {
            $items[] = '- Ferro: **' . $ironIngot . '**';
        }
        if (!empty($copperPlate)) {
            $items[] = '- Cobre: **' . $copperPlate . '**';
        }
        if (!empty($aluminumPlate)) {
            $items[] = '- Alumínio: **' . $aluminumPlate . '**';
        }
        $field = new Field($discord, [
            'name' => 'Itens adicionados: ',
            'value' => implode("\n", $items),
        ]);
        $embed->addField($field);

        $nickname = $member?->nick ?? $member?->user?->username ?? 'N/A';
        $avatar = $member?->user?->avatar ?? 'https://miro.medium.com/max/512/0*E3Nphq-iyw_gsZFH.png';

        $this->save($passport, (int) $gunTrigger, (int) $gunPart, (int) $ironIngot, (int) $copperPlate, (int) $aluminumPlate);
        $totais = $this->get($passport);

        $items = [];
        if (!empty($gunTrigger)) {
            $items[] = 'Gatilho: **' . $gunTrigger . '**';
        }
        if (!empty($gunPart)) {
            $items[] = 'Part. Arma: **' . $gunPart . '**';
        }
        if (!empty($ironIngot)) {
            $items[] = 'Ferro: **' . $ironIngot . '**';
        }
        if (!empty($copperPlate)) {
            $items[] = 'Cobre: **' . $copperPlate . '**';
        }
        if (!empty($aluminumPlate)) {
            $items[] = 'Alumínio: **' . $aluminumPlate . '**';
        }
        $field = new Field($discord, [
            'name' => 'Itens: ',
            'value' => implode("\n", $items),
        ]);

        $field = new Field($discord, [
            'name' => 'Itens: ',
            'value' => implode("\n", $items),
        ]);

        $totaisArray = [];
        if (!empty($totais['gunTrigger'])) {
            $totaisArray[] = '- Gatilho: **' . $totais['gunTrigger'] . '**';
        }
        if (!empty($totais['gunPart'])) {
            $totaisArray[] = '- Part. Arma: **' . $totais['gunPart'] . '**';
        }
        if (!empty($totais['ironIngot'])) {
            $totaisArray[] = '- Ferro: **' . $totais['ironIngot'] . '**';
        }
        if (!empty($totais['copperPlate'])) {
            $totaisArray[] = '- Cobre: **' . $totais['copperPlate'] . '**';
        }
        if (!empty($totais['aluminumPlate'])) {
            $totaisArray[] = '- Alumínio: **' . $totais['aluminumPlate'] . '**';
        }
        $field = new Field($discord, [
            'name' => 'Total meta:',
            'value' => implode("\n", $totaisArray),
        ]);
        $embed->addField($field);

        $embed
            ->setTitle('Registro de meta')
            ->setThumbnail($avatar)
            ->setColor(0x00AE86)
            ->setTimestamp(time())
            ->setFooter($nickname, $avatar);

        $message = MessageBuilder::new();
        $message->setEmbeds([$embed]);

        $channel = $interaction->guild->channels->get('name', '・ᴍᴇᴛᴀ-' . $passport);
        if (!empty($channel)) {
            $channel->sendMessage($message)->then(function () use ($interaction) {
                $interaction->acknowledge();
            });
        } else {
            $newChannel = $interaction->guild->channels->create([
                'name' => '・ᴍᴇᴛᴀ-' . $passport,
                'topic' => 'Meta adicionado no baú do passaporte ' . $passport,
                'type' => Channel::TYPE_TEXT,
                'parent_id' => $_ENV['CATEGORY_META_RECORD'],
                'nsfw' => false,
            ]);
            $interaction->guild->channels->save($newChannel)->done(function (Channel $channel) use ($message, $interaction) {
                $channel->sendMessage($message);
                $channel->setPermissions($interaction->member, [
                    'view_channel',
                    'read_message_history',
                ], [
                    'send_messages',
                    'add_reactions',
                ])->done(function () use ($interaction) {
                    $interaction->acknowledge();
                });
            });
        }
    }

    private function save(string $passport, int $gunTrigger, int $gunPart, int $ironIngot, int $copperPlate, int $aluminumPlate): void
    {
        $meta = new Meta;
        $meta->setMetaPassport($passport, $gunTrigger, $gunPart, $ironIngot, $copperPlate, $aluminumPlate);
    }

    private function get(string $passport): array
    {
        $meta = new Meta;
        return $meta->getMetaPassport($passport);
    }
}