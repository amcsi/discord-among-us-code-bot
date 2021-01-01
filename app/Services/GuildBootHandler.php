<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Psr\Log\LoggerInterface;

class GuildBootHandler
{
    public function __construct(
        private TargetMessageUpdater $targetMessageUpdater,
        private Discord $discord,
        private ServerConfigs $serverConfigs,
        private CodeHandler $codeHandler,
        private LoggerInterface $logger,
        private PromiseFailHandler $promiseFailHandler,
    ) {}

    public function bootGuilds(): void
    {
        $configsByServerId = $this->serverConfigs->getConfigsByServerId();
        foreach ($configsByServerId as $guildId => $serverConfig) {
            $guild = $this->discord->guilds[$guildId];

            if (!$guild instanceof Guild) {
                $this->logger->notice("No access to guild: `$guildId`");
                continue;
            }

            $this->handleGuildBoot($guild, $serverConfig->sourceChannelId);
        }
    }

    private function handleGuildBoot(Guild $guild, string $sourceChannelId): void
    {
        // Welcome message.
        $this->targetMessageUpdater->updateMessage($guild->id, trans('bot.justConnected'));

        $sourceChannel = $guild->channels[$sourceChannelId];
        if (!$sourceChannel instanceof Channel) {
            $this->logger->warning("Could not find source channel `$sourceChannelId` for guild `{$guild->name}`");
            return;
        }

        $sourceChannel->getMessageHistory([])->done(function (Collection $sourceMessages) use ($guild) {
            /** @var Message[] $sourceMessagesAscendingOrder */
            $sourceMessagesAscendingOrder = array_reverse($sourceMessages->toArray());

            // Replay message history.
            foreach ($sourceMessagesAscendingOrder as $sourceMessage) {
                // Handle without update. We'll update after the loop.
                $this->codeHandler->handleWithoutUpdate($sourceMessage);
            }

            $this->codeHandler->updateCodes($guild);
        }, $this->promiseFailHandler);
    }
}
