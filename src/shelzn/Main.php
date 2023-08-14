<?php

namespace shelzn;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\metadata\FixedMetadataValue;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getLogger()->info("Enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isNickValid(string $nick): bool {
        // Regular expression to match only alphanumeric characters
        $pattern = '/^[a-zA-Z0-9]+$/';

        return preg_match($pattern, $nick) === 1;
    }

    public function setNick(Player $player): void {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            if (isset($data[0])) {
                $newNick = $data[0];

                if (strlen($newNick) > 12) {
                    $player->sendMessage("§cName is too long. Maximum length is 12 characters.");
                    return;
                }

                if (!$this->isNickValid($newNick)) {
                    $player->sendMessage("§cInvalid nickname. Nickname must contain only alphanumeric characters.");
                    return;
                }

                if ($this->isNicknameTaken($newNick)) {
                    $player->sendMessage("§cThis nickname is already taken. Please choose a different one.");
                    return;
                }

                $player->setDisplayName($newNick);
                $player->sendMessage("§aYour nickname has been set to: §f" . $newNick);
            }
        });

        $form->setTitle("Set Nickname Form");
        $form->addInput("Enter your new nickname:");

        $player->sendForm($form);
    }

    public function generateRandomNick(): string {
        $length = 8;
        $characters = 'abcdefghijkmnpqrstuvwxyz23456789';
        $randomNick = '';

        for ($i = 0; $i < $length; $i++) {
            $randomNick .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomNick;
    }

    public function setRandomNick(Player $player): void
    {
        $randomNick = $this->generateRandomNick();
        $player->setDisplayName($randomNick);
        $player->sendMessage("§aYour nickname has been set to a random name: §f" . $randomNick);
    }


    public function resetNickname(Player $player): void {
        $player->setDisplayName($player->getName());
        $player->sendMessage("§aYour nickname has been reset to your original name.");
    }

    public function isNicknameTaken(string $nick): bool {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player->getDisplayName() === $nick) {
                return true;
            }
        }
        return false;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "nick") {
            if ($sender instanceof Player) {
                $this->openNickForm($sender);
            } else {
                $sender->sendMessage("§cYou don't have permission to use this command.");
            }
            return true;
        } else {
            $sender->sendMessage("§cThis command can only be used in-game.");
        }
        return false;
    }

    public function openNickForm(Player $player) {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            switch ($data) {
                case 0:
                    $this->setNick($player);
                    break;
                case 1:
                    $this->setRandomNick($player);
                    break;
                case 2:
                    $this->resetNickname($player);
                    break;
            }
        });

        $form->setTitle("Change Nickname");
        $form->setContent("Your Currently Nick: §b" . $player->getDisplayName());
        $form->addButton("Set Nick");
        $form->addButton("Random Nick");
        $form->addButton("Reset Nick");

        $player->sendForm($form);
    }
}
