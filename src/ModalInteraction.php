<?php

namespace Dz7;

use \Discord\Discord;
use \Discord\Parts\Interactions\Interaction;
use Discord\Repository\Interaction\ComponentRepository;

class ModalInteraction {

    public static function handle(Interaction $interaction, Discord $discord, ComponentRepository $components): void {
        $customId = $interaction->data->custom_id;
        $params = explode('-', $customId);

        $customId = array_shift($params);
        $customId = Util::formatCustomId($customId);

        $params = empty($params) ? [] : $params;

        if (!file_exists(__DIR__ . "/ModalInteractions/$customId.php")) {
            return;
        }
        $class = "Dz7\\ModalInteractions\\" . $customId;
        $instaceClass = new $class;
        $instaceClass->handle($interaction, $discord, $components, $params);
    }
}