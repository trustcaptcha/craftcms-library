<?php

namespace TrustcaptchaCraftcms;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\contactform\models\Submission;
use craft\elements\User;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use TrustcaptchaCraftcms\plugins\FormieTrustcaptchaCraftcms;
use yii\base\Event;
use yii\base\ModelEvent;

class Plugin extends BasePlugin
{
    public static $plugin;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public ?CaptchaService $captchaService;
    public static function config(): array {
        return [
            'components' => [],
        ];
    }

    public function init(): void {

        Craft::setAlias('@TrustcaptchaCraftcms', __DIR__ . "/../");
        parent::init();
        self::$plugin = $this;
        $this->captchaService = new CaptchaService();
        Event::on(
           View::class,
           View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
           function(RegisterTemplateRootsEvent $event) {
               $event->roots['_trustcaptcha_craftcms_cp'] = __DIR__ . '/../templates';
           }
          );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                $variable = $event->sender;
                $variable->set('trustcaptcha', Variables::class);
            }
        );


        if (class_exists(Submission::class) && $this->getSettings()->getValidateContactForm()) {
            $this->handleContactForms();
        }

        if ($this->getSettings()->getValidateUsersRegistration() && Craft::$app->getRequest()->getIsSiteRequest()) {
            $this->handleUserRegistrationForms();
        }

        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function handleContactForms() {

        Event::on(Submission::class, Submission::EVENT_BEFORE_VALIDATE, function(ModelEvent $event) {
            $submission = $event->sender;
            if (!$this->captchaService->validateCaptcha()) {
                $submission->addError('trustcaptcha', "Captcha failed. Please try again.");
                $event->isValid = false;
            }
        });
    }

    private function handleUserRegistrationForms() {

        Event::on(User::class, User::EVENT_BEFORE_VALIDATE, function(ModelEvent $event) {
            /** @var User $user */
            $user = $event->sender;

            // Only new users
            if ($user->id === null && $user->uid === null && $user->contentId === null) {
                if (!$this->captchaService->validateCaptcha()) {
                    $user->addError('trustcaptcha', "Captcha failed. Please try again.");
                    $event->isValid = false;
                }
            }
        });
    }

    private function attachEventHandlers(): void {

        if (class_exists('verbb\formie\services\Integrations') && class_exists('verbb\formie\events\RegisterIntegrationsEvent')) {
            Event::on(
                'verbb\formie\services\Integrations',
                'registerIntegrations',
                function($event) {
                    $event->captchas[] = FormieTrustcaptchaCraftcms::class;
                }
            );
        }
    }

    protected function createSettingsModel(): Model {
        return new Settings();
    }

    protected function settingsHtml(): ?string {
        return Craft::$app->view->renderTemplate(
            '_trustcaptcha_craftcms_cp/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
