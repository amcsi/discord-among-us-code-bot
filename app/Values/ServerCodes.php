<?php
declare(strict_types=1);

namespace App\Values;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Illuminate\Support\Collection;

class ServerCodes
{
    /** @var Collection|ServerCode[][] */
    private Collection $codes;

    public function __construct()
    {
        $this->codes = new Collection();
    }

    public function setCode(Channel $voiceChannel, string $code): void
    {
        $guildId = $voiceChannel->guild->id;
        if (!isset($this->codes[$guildId])) {
            $this->codes[$guildId] = new Collection();
        }
        $this->codes[$guildId][$voiceChannel->id] = new ServerCode($voiceChannel, $code);
        $this->codes[$guildId] = $this->codes[$guildId]->sortBy(
            fn(ServerCode $serverCode) => $serverCode->voiceChannel->position
        );
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
