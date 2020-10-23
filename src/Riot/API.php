<?php

declare(strict_types=1);

namespace Riot;

use Riot\API\Version1;
use Riot\API\Version3;
use Riot\API\Version4;

final class API
{
    private ConnectionInterface $riotConnection;

    /** @var array<string, mixed> */
    private array $apis;

    public function __construct(ConnectionInterface $riotConnection)
    {
        $this->riotConnection = $riotConnection;
    }

    public function getSummonerV4Api(): Version4\Summoner
    {
        if (!isset($this->apis['summonerV4'])) {
            $this->apis['summonerV4'] = new Version4\Summoner($this->riotConnection);
        }

        return $this->apis['summonerV4'];
    }

    public function getThirdPartyCodeV4Api(): Version4\ThirdPartyCode
    {
        if (!isset($this->apis['thirdPartyCodeV4'])) {
            $this->apis['thirdPartyCodeV4'] = new Version4\ThirdPartyCode($this->riotConnection);
        }

        return $this->apis['thirdPartyCodeV4'];
    }

    public function getAccountV1Api(): Version1\Account
    {
        if (!isset($this->apis['accountV1'])) {
            $this->apis['accountV1'] = new Version1\Account($this->riotConnection);
        }

        return $this->apis['accountV1'];
    }

    public function getChampionMasteryV4Api(): Version4\ChampionMastery
    {
        if (!isset($this->apis['championMasteryV4'])) {
            $this->apis['championMasteryV4'] = new Version4\ChampionMastery($this->riotConnection);
        }

        return $this->apis['championMasteryV4'];
    }

    public function getChampionV3Api(): Version3\Champion
    {
        if (!isset($this->apis['championV3'])) {
            $this->apis['championV3'] = new Version3\Champion($this->riotConnection);
        }

        return $this->apis['championV3'];
    }
}
