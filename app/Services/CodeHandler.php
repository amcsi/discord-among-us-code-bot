<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use App\Values\ServerCode;
use App\Values\ServerCodes;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use React\Promise\ExtendedPromiseInterface;

/**
 * Saves a code update if the message has a code in it, then updates the codes message if so.
 */
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

        $formattedServerAndCode = CodeMatcher::matchAndFormatText($sourceMessage->content);
        if (!$formattedServerAndCode) {
            $this->logger->debug('No server code was detected in this message.');
            return \React\Promise\resolve();
        }

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
            return \React\Promise\resolve();
        }

        $this->serverCodes->setServerCode(new ServerCode($sourceMessage, $voiceChannel, $formattedServerAndCode));

        $messageContent = $this->serverCodes->getServerCodeMessageContent($guild);

        $this->logger->debug('Updating message to:');
        $this->logger->debug($messageContent);

        $targetMessage->content = $messageContent;
        return $targetMessage->channel->messages->save($targetMessage);
    }
}
