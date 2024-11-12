<?php

namespace TrustcaptchaCraftcms;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

class Settings extends Model
{
    public $sitekey = '';
    public $secret_key = '';
    public $license = '';
    public $threshold = 0.5;
    public $language = 'auto';
    public $theme = 'light';
    public $autostart = 'true';
    public $slider = 'disabled';
    public $hide_branding = false;
    public $invisible = false;

    public bool $validateContactForm = false;
    public bool $validateUsersRegistration = false;


    public function getSiteKey(): string {
        return App::parseEnv($this->sitekey);
    }

    public function getSecretKey(): string {
        return App::parseEnv($this->secret_key);
    }

    public function getLicense(): ?string {
        return App::parseEnv($this->license);
    }

    public function getThreshold(): ?float {
        return App::parseEnv($this->threshold);
    }

    public function getLanguage(): string {
        return App::parseEnv($this->language);
    }

    public function getTheme(): string {
        return App::parseEnv($this->theme);
    }

    public function getAutostart(): bool {
        return App::parseEnv($this->autostart);
    }

    public function getSlider(): ?string {
        return App::parseEnv($this->slider);
    }

    public function getHideBranding(): bool {
        return App::parseEnv($this->hide_branding);
    }

    public function getInvisible(): bool {
        return App::parseEnv($this->invisible);
    }

    public function getValidateContactForm(): bool {
        return App::parseEnv($this->validateContactForm);
    }

    public function getValidateUsersRegistration(): bool {
        return App::parseEnv($this->validateUsersRegistration);
    }


    public function behaviors(): array {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['sitekey', 'secret_key', 'license', 'threshold', 'language', 'theme', 'autostart', 'slider', 'hide_branding', 'invisible'],
            ],
        ];
    }

    public function rules(): array {
        return [
            [['sitekey', 'secret_key'], 'required'],
            [['sitekey', 'secret_key', 'license', 'language', 'theme', 'slider'], 'string'],
            [['threshold'], 'number', 'min' => 0, 'max' => 1],
            [['autostart', 'hide_branding', 'invisible'], 'boolean'],
            ['validateContactForm', 'boolean'],
            ['validateUsersRegistration', 'boolean'],
        ];
    }
}
