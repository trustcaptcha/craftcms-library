<?php

namespace TrustcaptchaCraftcms\plugins;

use Craft;
use craft\helpers\App;
use Trustcaptcha\CaptchaManager;
use verbb\formie\base\Captcha;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;


class FormieTrustcaptchaCraftcms extends Captcha
{
    public ?string $handle = 'FormieTrustcaptchaCraftcms';
    public $sitekey = '';
    public $secret_key = '';
    public $license = '';
    public $threshold = 0.5;
    public $language = 'auto';
    public $theme = 'light';
    public $autostart = true;
    public $slider = 'disabled';
    public $hide_branding = false;
    public $invisible = false;

    public function getRefreshJsVariables(Form $form, $page = null): array{
        return [];
    }

    public function getName(): string {
        return Craft::t('formie', 'Trustcaptcha');
    }

    public function getIconUrl(): string {
        return Craft::$app->getAssetManager()->getPublishedUrl("@TrustcaptchaCraftcms/resources/icon.svg", true);
    }

    public function getDescription(): string {
        return Craft::t('formie', 'The CAPTCHA, with a focus on user-experience and GDPR compliance');
    }

    public function getSettingsHtml(): string {
        return Craft::$app->getView()->renderTemplate('_trustcaptcha_craftcms_cp/plugins/formie_settings', [
            'integration' => $this,
        ]);
    }

    public function getFrontEndHtml(Form $form, $page = null): string {

        return '
            <div style="padding-top: 16px">
                <trustcaptcha-component
                    sitekey="'. App::parseEnv($this->sitekey) .'"
                    license="'. App::parseEnv($this->license) .'"
                    language="'. $this->language .'"
                    theme="'. $this->theme .'"
                    autostart='. ($this->autostart ? 'true' : 'false') .'
                    slider="'. $this->slider .'"
                    hide-branding='. ($this->hide_branding ? 'true' : 'false') .'
                    invisible='. ($this->invisible ? 'true' : 'false') .'
                ></trustcaptcha-component>
            </div>
            <script>
            (function() {
                const forms = document.querySelectorAll(\'input[type="hidden"][name="handle"][value="' . $form->handle . '"]\');
                if(!window.CPTFormsAdded) { window.CPTFormsAdded = new Set(); }
                forms.forEach(form => {
                    const closestForm = form.closest("form");
                    if (closestForm && !window.CPTFormsAdded.has(closestForm.id)) {
                        window.CPTFormsAdded.add(closestForm.id);                        
                        closestForm.addEventListener("onFormieCaptchaValidate", (e) => {
                            e.preventDefault();
                            let submitHandler = e.detail.submitHandler;
                            const trustcaptchaComponent = closestForm.querySelector("trustcaptcha-component")
                            const verificationTokenInput = closestForm.querySelector(\'input[style="display:none"][name="tc-verification-token"]\');
                            if (verificationTokenInput) {
                                submitHandler.submitForm();
                                trustcaptchaComponent.reset()
                                console.log("normal submit")
                                return
                            }
                            trustcaptchaComponent.addEventListener("captchaSolved", () => {
                                submitHandler.submitForm();
                                trustcaptchaComponent.reset()
                                console.log("auto submit")
                            }, { once: true });
                        });
                    }
                });
            })();
            </script>
        ';
    }

    public function getFrontEndJsVariables(Form $form, $page = null): ?array {
        return [
            'src' => 'https://resources.trustcaptcha.com/1_7_x/trustcaptcha.cjs.js',
        ];
    }

    public function validateSubmission(Submission $submission): bool {

        $base64verificationToken = parent::getCaptchaValue($submission, 'tc-verification-token');
        $base64secretKey = $this->secret_key;
        $threshold = $this->threshold;

        if (empty($base64verificationToken)) {
            return false;
        }

        if (empty($base64secretKey)) {
            return false;
        }

        $verificationResult = CaptchaManager::getVerificationResult($base64secretKey, $base64verificationToken);

        return $verificationResult->verificationPassed && $verificationResult->score <= $threshold;
    }
}
