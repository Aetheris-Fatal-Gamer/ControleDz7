<?php

namespace Dz7\Tasks;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Dz7\Database\Create;
use Dz7\Database\Read;
use Dz7\Database\Update;

class ProcessLeadBoxQueue {

    public static function handle(Discord &$discord): void {
        $table = 'leader_chest_processing_queue';

        $read = new Read;
        $chestsQueues = $read->run("SELECT * FROM $table AS cpq WHERE cpq.processed = :processed ORDER BY cpq.id ASC", [
            'processed' => 0
        ])->getResult();

        if (empty($chestsQueues)) {
            return;
        }

        foreach ($chestsQueues as $chestQueue) {
            $chestQueue = (array) $chestQueue;
            $guild = $discord->guilds->get('id', $chestQueue['guild_id']);
            if (empty($guild)) {
                echo 'GUILD VAZIO' . PHP_EOL;
                continue;
            }

            $tableItems = 'stock_items_leader';
            $dbItemQuantity = $read->run("SELECT * FROM $tableItems AS si WHERE si.item_name = :item_name LIMIT 1", [
                'item_name' => $chestQueue['item_name'],
            ])->getResult();
            $dbItemQuantity = $dbItemQuantity[0] ?? [];
            if (empty($dbItemQuantity)) {
                $dbItemQuantity = [
                    'item_name' => $chestQueue['item_name'],
                    'item_quantity' => 0,
                ];
                $create = new Create;
                $create->run('stock_items_leader', $dbItemQuantity);
                unset($create);
            }
            $dbItemQuantity = (array) $dbItemQuantity;
            $currentQuantity = $dbItemQuantity['item_quantity'] ?? 0;
            if ($chestQueue['item_operation'] == 'GUARDOU') {
                $currentQuantity += $chestQueue['item_quantity'];
            } else {
                $currentQuantity -= $chestQueue['item_quantity'];
            }
            $update = new Update;
            $update->run($tableItems, ['item_quantity' => $currentQuantity], 'WHERE item_name = :item_name', ['item_name' => $chestQueue['item_name']]);


            $passportField = new Field($discord, [
                'name' => 'Passaporte:',
                'value' => $chestQueue['user_passport'],
            ]);
            $itemField = new Field($discord, [
                'name' => (
                    $chestQueue['item_operation'] == 'GUARDOU'
                        ? 'Item adicionado:'
                        : 'Item retirado:'
                ),
                'value' => $chestQueue['item_name'],
            ]);
            $quantityField = new Field($discord, [
                'name' => 'Quantidade:',
                'value' => $chestQueue['item_quantity'],
            ]);
            $currentTotalField = new Field($discord, [
                'name' => (
                    $chestQueue['item_operation'] == 'GUARDOU'
                        ? 'Total no baú:'
                        : 'Restante no baú:'
                ),
                'value' => $currentQuantity,
            ]);

            $embed = new Embed($discord);
            $embed
                ->setTitle(
                    $chestQueue['item_operation'] == 'GUARDOU' ?
                        'Adicionado no baú' :
                        'Retirada do baú'
                )
                ->setThumbnail($chestQueue['user_avatar'])
                ->setColor(
                    $chestQueue['item_operation'] == 'GUARDOU' ?
                        '#3EFF00' :
                        '#FF0000'
                )
                ->setTimestamp(time())
                ->setFooter($chestQueue['user_nick'], $chestQueue['user_avatar'])
                ->addField($passportField)
                ->addField($itemField)
                ->addField($quantityField)
                ->addField($currentTotalField);
            $message = MessageBuilder::new();
            $message->setEmbeds([$embed]);

            $channelGeral = $guild->channels->get('id', 1066520287178391562);
            if ($channelGeral) {
                $channelGeral->sendMessage($message);
            }

            $update = new Update;
            $update->run($table, ['processed' => 1], 'WHERE id = :id', ['id' => $chestQueue['id']]);

        }
        $update = new Update;
        $update->run('control_update_leader_chest', ['needs_to_update' => 1], 'WHERE id = :id', ['id' => 1]);
    }
}