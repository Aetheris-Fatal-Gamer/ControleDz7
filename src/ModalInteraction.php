<?php

namespace Dz7;

use \Discord\Discord;
use \Discord\Parts\Interactions\Interaction;

class ModalInteraction {

    public static function handle(Interaction $interaction, Discord $discord, $components): void {
        $customId = Util::formatCustomId($interaction->data->custom_id);

        if (!file_exists(__DIR__ . "/ModalInteractions/$customId.php")) {
            return;
        }
        $class = "Dz7\\ModalInteractions\\" . $customId;
        $instaceClass = new $class;
        $instaceClass->handle($interaction, $discord, $components);
    }
}