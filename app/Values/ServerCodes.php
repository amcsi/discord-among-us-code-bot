<?php
declare(strict_types=1);

namespace App\Values;

use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Illuminate\Support\Collection;

class ServerCodes
{
    /** @var Collection|ServerCode[][] */
    private Collection $codes;
    /** @var Collection|ServerCode[] */
    private Collection $codeByMessageIdMap;

    public function __construct()
    {
        $this->codes = new Collection();
        $this->codeByMessageIdMap = new Collection();
    }

    public function setServerCode(ServerCode $serverCode): void
    {
        $voiceChannel = $serverCode->voiceChannel;
        $guildId = $voiceChannel->guild->id;
        if (!isset($this->codes[$guildId])) {
            $this->codes[$guildId] = new Collection();
        }
        $this->codes[$guildId][$voiceChannel->id] = $serverCode;
        $this->codes[$guildId] = $this->codes[$guildId]->sortBy(
            fn(ServerCode $serverCode) => $serverCode->voiceChannel->position
        );
        $this->codeByMessageIdMap[$serverCode->sourceMessage->id] = $serverCode;
    }

    /**
     * Returns if there is a server code for the passed message.
     */
    public function hasMessageServerCode(Message $sourceMessage): bool
    {
        return isset($this->codeByMessageIdMap[$sourceMessage->id]);
    }

    /** @return  Collection|ServerCode[] $codes */
    public function getCodesByGuild(Guild $guild)
    {
        return $this->codes->get($guild->id, []);
    }

    /**
     * This returns the string that has all the codes listed in it.
     */
    public function getServerCodeMessageContent(Guild $guild): string
    {
        $codes = $this->getCodesByGuild($guild);
        if (!$codes->count()) {
            return trans('bot.noCodes');
        }

        return sprintf(
            "%s\n\n%s",
            trans('bot.hereAreTheCodes'),
            $codes->map(
                fn(ServerCode $serverCode) => sprintf(
                    '%s: %s',
                    $serverCode->voiceChannel->name,
                    $serverCode->code
                )
            )->join("\n")
        );
    }
}
