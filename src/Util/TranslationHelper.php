<?php

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class TranslationHelper
{
    /**
     * @return Translator
     */
    public static function getAppTranslator()
    {
        $translator = new Translator('en_EN', new MessageSelector());
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__ . DIRECTORY_SEPARATOR . '../../data/locale/messages.en.yml', 'en_EN');
        $translator->setFallbackLocales(['en']);

        return $translator;
    }
}