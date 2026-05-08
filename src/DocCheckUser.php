<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\User;

class DocCheckUser extends User
{
    public function getOccupationDisciplineId(): ?int
    {
        return Arr::get($this->getRaw(), 'discipline_id');
    }

    public function getOccupationProfessionId(): ?int
    {
        return Arr::get($this->getRaw(), 'profession_id');
    }
}
