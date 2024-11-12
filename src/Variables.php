<?php

namespace TrustcaptchaCraftcms;

class Variables
{
    /**
     * {{ craft.trustcaptcha.validateCaptcha() }}
     */
    public function validateCaptcha(): bool {
        return Plugin::$plugin->captchaService->validateCaptcha();
    }

    /**
     * {{ craft.trustcaptcha.insertCaptcha() }}
     */
    public function insertCaptcha(): String {
        return Plugin::$plugin->captchaService->getCaptchaComponent();
    }
}
