<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class AddedFarmTotal
{

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void
    {
        $this->buildActionsRows($interaction);

        $passport = Util::extractPassport($interaction);
        $passport = $passport ? ' - ' . $passport : '';
        $interaction->showModal('Adicionar itens de farm - ' . $passport, 'added_farm', $this->actionsRows);
    }

    private function buildActionsRows(Interaction $interaction): void
    {
        $passport = Util::extractPassport($interaction);
        if (empty($passport)) {
            $passportInput = TextInput::new('Digite o seu passaporte:', TextInput::STYLE_SHORT, 'passport')
                ->setRequired(true)
                ->setValue($passport);
            $passportRow = ActionRow::new()->addComponent($passportInput);
            $this->actionsRows[] = $passportRow;
        }

        $itemInput = TextInput::new('Gatilho:', TextInput::STYLE_SHORT, 'gunTrigger')
            ->setValue(0)
            ->setMaxLength(10)
            ->setPlaceholder('Ex: 30');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $itemInput = TextInput::new('Part. Arma:', TextInput::STYLE_SHORT, 'gunPart')
            ->setValue(0)
            ->setMaxLength(10)
            ->setPlaceholder('Ex: 20');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $itemInput = TextInput::new('Ferro:', TextInput::STYLE_SHORT, 'ironIngot')
            ->setValue(0)
            ->setMaxLength(10)
            ->setPlaceholder('Ex: 10');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $itemInput = TextInput::new('Alumínio: (Lata coloca a quantidade vezes 5)', TextInput::STYLE_SHORT, 'aluminumPlate')
            ->setValue(0)
            ->setMaxLength(10)
            ->setPlaceholder('Ex: 80');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $itemInput = TextInput::new('Cobre: (Pilha coloca a quantidade vezes 5)', TextInput::STYLE_SHORT, 'copperPlate')
            ->setValue(0)
            ->setMaxLength(10)
            ->setPlaceholder('Ex: 50');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;
    }
}