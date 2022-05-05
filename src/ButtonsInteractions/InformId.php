<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class InformId {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $passport = Util::extractPassport($interaction);
        if (!empty($passport)) {
            $interaction->acknowledge();
            return;
        }
        $this->buildActionsRows($interaction);
        
        $interaction->showModal('Registro de Nome e Id', 'inform_id', $this->actionsRows);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $passportInput = TextInput::new('Digite o seu passaporte:', TextInput::STYLE_SHORT, 'passport')
            ->setRequired(true)
            ->setPlaceholder('Ex: 2479');
        $passportRow = ActionRow::new()->addComponent($passportInput);
        $this->actionsRows[] = $passportRow;

        $nameInput = TextInput::new('Nome (in-game):', TextInput::STYLE_SHORT, 'name')
            ->setRequired(true)
            ->setPlaceholder('Ex: Carlos Aberto');
        $nameRow = ActionRow::new()->addComponent($nameInput);
        $this->actionsRows[] = $nameRow;
    }
}