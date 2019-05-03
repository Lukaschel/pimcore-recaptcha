<?php
/**
 * PimcoreRecaptchaBundle
 * Copyright (c) Lukaschel
 */

namespace Lukaschel\PimcoreRecaptchaBundle\Components;

use Exception;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Container\ContainerInterface;

class Recaptcha
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Recaptcha constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $token
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function validate(string $token)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $recaptcha = $this->container->get('lukaschel.bundleconfiguration')->getConfig('recaptcha', '', '', 'PimcoreRecaptchaBundle');

        if (!$recaptcha['privateKey']) {
            throw new Exception('No Recaptcha private key configured');
        }

        $client = $this->container->get('pimcore.http_client');
        $response = $client->request('POST', $url, [
            'header' => 0,
            'form_params' => [
                'secret' => $recaptcha['privateKey'],
                'response' => $token,
            ],
        ]);

        if ($response instanceof GuzzleResponse) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            $data = json_decode($response->getBody()->read(999), true);
            if ($data['success']) {
                return $data;
            }

            return false;
        }

        return false;
    }
}
