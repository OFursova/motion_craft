<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Register;
use Illuminate\Database\Eloquent\Model;

class UserAppRegistration extends Register
{
    protected function handleRegistration(array $data): Model
    {
        if (! array_key_exists('ip', $data)) {
            $data['ip'] = request()->ip();
        }

        return $this->getUserModel()::create($data);
    }
}
