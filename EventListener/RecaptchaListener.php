<?php
/**
 * PimcoreRecaptchaBundle
 * Copyright (c) Lukaschel
 */

namespace Lukaschel\PimcoreRecaptchaBundle\EventListener;

use Pimcore\Bundle\CoreBundle\EventListener\Traits\EnabledTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PreviewRequestTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\ResponseInjectionTrait;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Tool;
use Psr\Container\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RecaptchaListener
{
    use EnabledTrait;
    use ResponseInjectionTrait;
    use PimcoreContextAwareTrait;
    use PreviewRequestTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * RecaptchaListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $request = $event->getRequest();
        if (!$event->isMasterRequest()) {
            return;
        }

        // only inject analytics code on non-admin requests
        if (!$this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        if (!Tool::useFrontendOutputFilters()) {
            return;
        }

        if ($this->isPreviewRequest($request)) {
            return;
        }

        $response = $event->getResponse();
        if (!$this->isHtmlResponse($response)) {
            return;
        }

        $crawler = new Crawler();
        $crawler->addHtmlContent($response->getContent());

        if (!$crawler->evaluate('count(//input[@class="g-recaptcha-response-input"])')[0]) {
            return;
        }

        $recaptcha = $this->container->get('lukaschel.bundleconfiguration')->getConfig('recaptcha', '', '', 'PimcoreRecaptchaBundle');

        if (!$recaptcha['publicKey'] || !$recaptcha['privateKey']) {
            return;
        }

        $this->injectBeforeHeadEnd($response, $this->generateCode($recaptcha['publicKey']));
    }

    /**
     * @param $recaptchaPublicKey
     *
     * @return string
     */
    private function generateCode($recaptchaPublicKey)
    {
        return $this->container->get('twig')->render(dirname(__DIR__) . '/Resources/views/Recaptcha/captchaCode.html.twig', ['recaptchaPublicKey' => $recaptchaPublicKey]);
    }
}
