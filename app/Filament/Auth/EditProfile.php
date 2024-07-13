<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columnSpan(2),
                Group::make()
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label(__('Avatar'))
                            ->inlineLabel(false)
                            ->directory('avatars')
                            ->image()
                            ->maxSize(1024)
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->downloadable(),
                    ])->columnSpan(1),
            ])->columns(3);
    }
}
