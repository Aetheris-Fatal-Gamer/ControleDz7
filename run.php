<?php
require_once __DIR__ . '/vendor/autoload.php';

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\Component;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\InteractionType;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;
use Discord\Repository\Channel\MessageRepository;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Dotenv\Dotenv;
use Dz7\ButtonInteraction;
use Dz7\Command;
use Dz7\ModalInteraction;
use Dz7\Services\BoxService;
use Dz7\Tasks\ProcessBoxQueue;
use Dz7\Tasks\ProcessLeadBoxQueue;
use Dz7\Tasks\UpdateBoxQueue;

$environment = $argv[1] ?? 'development';
if (!file_exists(__DIR__ . '/.env.' . $environment)) {
    exit;
}

$button2 = new ButtonInteraction;

$dotenv = Dotenv::createImmutable(__DIR__, '.env.' . $environment);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['TOKEN'],
    'storeMessages' => true,
    'pmChannels' => true,
    'loadAllMembers' => true,
    'intents' => [
        Intents::GUILDS,
        Intents::GUILD_MESSAGES,
        Intents::GUILD_MEMBERS,
        Intents::GUILD_INTEGRATIONS,
        Intents::GUILD_PRESENCES,
    ],
]);

$discord->on('ready', function (Discord $discord) {
    $discord->updatePresence(null, false, $_ENV['BOT_STATUS']); 

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        if ($message->author->bot) return;
        Command::handle($message, $discord);
    });

    $discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction, Discord $discord) {
        if ($interaction->data->component_type === Component::TYPE_BUTTON) {
            ButtonInteraction::handle($interaction, $discord);
        }
        if ($interaction->type === InteractionType::MODAL_SUBMIT) {
            ModalInteraction::handle($interaction, $discord, $interaction->data->components);
        }
    });

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        if ($message->channel->id == 1056256774468030464) {
            BoxService::register($message);
            BoxService::registerLeader($message);
        }
    });

    $discord->getLoop()->addPeriodicTimer(2, function() use (&$discord) {
        ProcessBoxQueue::handle($discord);
    });

    $discord->getLoop()->addPeriodicTimer(2, function() use (&$discord) {
        ProcessLeadBoxQueue::handle($discord);
    });

    $discord->getLoop()->addPeriodicTimer(5, function() use (&$discord) {
        UpdateBoxQueue::handle($discord);
    });
});
$discord->run();