<?php

namespace TrustcaptchaCraftcms;

use Craft;

use craft\base\Component;
use craft\helpers\Html;
use Exception;
use Trustcaptcha\CaptchaManager;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\web\View;

class CaptchaService extends Component
{
    public function validateCaptcha(): bool {
        $base64verificationToken = Craft::$app->getRequest()->getParam('tc-verification-token');
        $base64secretKey = Plugin::$plugin->getSettings()->getSecretKey();
        $threshold = Plugin::$plugin->getSettings()->getThreshold();

        if (empty($base64verificationToken)) {
            return false;
        }

        if (empty($base64secretKey)) {
            return false;
        }

        $verificationResult = CaptchaManager::getVerificationResult($base64secretKey, $base64verificationToken);

        return $verificationResult->verificationPassed && $verificationResult->score <= $threshold;
    }

    public function getCaptchaComponent(): string {

        Craft::$app->view->registerJsFile('https://resources.trustcaptcha.com/1_7_x/trustcaptcha.cjs.js', [
            'position' => View::POS_HEAD,
        ]);

        return '
            <trustcaptcha-component
                sitekey="'. Plugin::$plugin->getSettings()->getSiteKey() .'"
                license="'. Plugin::$plugin->getSettings()->getLicense() .'"
                language="'. Plugin::$plugin->getSettings()->getLanguage() .'"
                theme="'. Plugin::$plugin->getSettings()->getTheme() .'"
                autostart='. (Plugin::$plugin->getSettings()->getAutostart() ? 'true' : 'false') .'
                slider="'. Plugin::$plugin->getSettings()->getSlider() .'"
                hide-branding='. (Plugin::$plugin->getSettings()->getHideBranding() ? 'true' : 'false') .'
                invisible='. (Plugin::$plugin->getSettings()->getInvisible() ? 'true' : 'false') .'
            ></trustcaptcha-component>
        ';
    }
}
