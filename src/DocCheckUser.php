<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\User;

class DocCheckUser extends User
{
    public function getOccupationDisciplineId()
    {
        return Arr::get($this->getRaw(), 'occupation_discipline_id');
    }

    public function getOccupationProfessionId()
    {
        return Arr::get($this->getRaw(), 'occupation_profession_id');
    }

    public function getOccupationProfessionParentId()
    {
        return Arr::get($this->getRaw(), 'occupation_profession_parent_id');
    }
}