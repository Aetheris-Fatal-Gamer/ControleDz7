<?php

namespace Dz7;

use \Discord\Discord;
use \Discord\Parts\Channel\Message;

class Command {

    public static function handle(Message $message, Discord $discord): void {
        $prefix = $_ENV['BOT_PREFIX'];
        $content = $message->content;

        if (substr($content, 0, 1) !== $prefix) {
            return;
        }
        list($command, $args) = self::getCommand($content);
        if (empty($command)) {
            return;
        }

        if (!file_exists(__DIR__ . "/Commands/$command.php")) {
            return;
        }
        $class = "Dz7\\Commands\\" . $command;
        $instaceClass = new $class;
        $instaceClass->run($args, $message, $discord);
    }

    private static function getCommand(string $content): array {
        $content = substr($content, 1);
        if (empty($content)) {
            return [null, null];
        }
        $content = explode(' ', $content);
        $word = array_shift($content);
        $words = explode('-', $word);
        $words = array_map('ucfirst', $words);
        return [implode('', $words), array_filter($content)];
    }
}