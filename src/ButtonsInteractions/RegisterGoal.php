<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class RegisterGoal {

    private $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);

        $passport = Util::extractPassport($interaction);
        $passport = $passport ? ' - ' . $passport : '';
        $interaction->showModal('Registro de meta' . $passport, 'register_goal', $this->actionsRows);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $passport = Util::extractPassport($interaction);
        if (empty($passport)) {
            $passportInput = TextInput::new('Digite o seu passaporte:', TextInput::STYLE_SHORT, 'passport')
                ->setRequired(true)
                ->setValue($passport);
            $passportRow = ActionRow::new()->addComponent($passportInput);
            $this->actionsRows[] = $passportRow;
        }

        $quantityInput = TextInput::new('Quantidade: (Somente números)', TextInput::STYLE_SHORT, 'quantity')
            ->setRequired(true)
            ->setPlaceholder('Ex: 200');
        $quantityRow = ActionRow::new()->addComponent($quantityInput);
        $this->actionsRows[] = $quantityRow;
    }
}