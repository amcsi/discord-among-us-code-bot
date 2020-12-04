<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use App\Values\ServerCodes;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

class CodeHandler
{
    private $arrayCache;

    public function __construct(
        private ServerCodes $serverCodes,
        private ServerConfigs $serverConfigs,
        private LoggerInterface $logger,
    ) {
        $this->arrayCache = cache()->driver('array');
    }

    public function handle(Message $sourceMessage, Message $targetMessage): ExtendedPromiseInterface
    {
        $guild = $sourceMessage->channel->guild;
        $serverConfig = $this->serverConfigs->getConfigsByServerId()[$guild->id];
        /** @var Collection|Channel[] $allowedVoiceChannels */
        $allowedVoiceChannels = $this->arrayCache->remember(
            "voice_channels_by_guild_id_{$guild->id}",
            60 * 15,
            fn() => $sourceMessage->channel->guild->channels->filter(
                fn(Channel $channel) => $channel->type === Channel::TYPE_VOICE && Arr::first(
                        $serverConfig->getGameVoiceRegexes(),
                        fn(string $regex) => preg_match($regex, $channel->name)
                    )
            )
        );

        $voiceChannel = null;
        foreach ($allowedVoiceChannels as $allowedVoiceChannel) {
            if ($allowedVoiceChannel->members[$sourceMessage->author->id]) {
                $voiceChannel = $allowedVoiceChannel;
                break;
            }
        }

        if (!$voiceChannel) {
            $this->logger->debug('The author of the message is not in any of the designated game voice channels.');
            $deferred = new Deferred();
            $deferred->resolve();
            return $deferred->promise();
        }

        // TODO: filter out what look like Among Us codes.
        $codeContent = $sourceMessage->content;

        $this->serverCodes->setCode($voiceChannel, $codeContent);

        $messageContent = $this->serverCodes->getServerCodeMessageContent($guild);

        $this->logger->debug('Updating message to:');
        $this->logger->debug($messageContent);

        $targetMessage->content = $messageContent;
        return $targetMessage->channel->messages->save($targetMessage);
    }
}
