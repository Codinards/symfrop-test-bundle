<?php

namespace Njeaner\Symfrop\Twig;

use Njeaner\Symfrop\Core\Manager\AnnotationManager;
use Njeaner\Symfrop\Exceptions\SymfropBaseException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * The symfrop bundle twig extension
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropTwigExtension extends AbstractExtension
{

    protected ?string $locale = null;

    protected static $instance;

    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private AnnotationManager $annotationManager,
        private CsrfTokenManagerInterface $tokenManager,
        private TranslatorInterface $translator,
        private Environment $twig
    ) {
        self::$instance = $this;
        $twig->addGlobal('annotationManager', $annotationManager);
    }

    public function getFunctions(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFunction('create_symfrop_link', [$this, 'link'], ['is_safe' => ['html']]),
            new TwigFunction('create_symfrop_default_link', [$this, 'defaultLink'], ['is_safe' => ['html']]),
            new TwigFunction('__', [$this, '__']),
            new TwigFunction('__U', [$this, '__u']),
            new TwigFunction('_U', [$this, '__u']),
            new TwigFunction('__u', [$this, '__u']),
            new TwigFunction('_u', [$this, '__u']),
            new TwigFunction('_t', [$this, '__t']),
            new TwigFunction('_T', [$this, '__t']),
            new TwigFunction('__t', [$this, '__t']),
            new TwigFunction('__T', [$this, '__t']),
            new TwigFunction('merge', 'twig_array_merge'),
        ];
    }


    public function getTests()
    {
        return [
            new TwigTest('authorized action', [$this, 'isAuthorize']),
        ];
    }


    public function getTokenParsers(): array
    {
        return [
            new SymfropFormTokenParser($this->annotationManager)
        ];
    }

    public function isAuthorize(string $routeName): bool
    {
        return $this->annotationManager->isAuthorize($routeName);
    }

    public function link(
        string $path,
        string|array $label,
        array $params = [],
        array $options = []
    ): bool|string {
        $isAuthorize = $this->annotationManager->isAuthorize($path);

        if ($isAuthorize === true) {
            try {

                if (!isset($params['_locale'])) {
                    $params['_locale'] = $this->getRequestLocale();
                }

                $route = $this->router->generate($path, $params);
            } catch (\Exception $e) {
                throw new SymfropBaseException($e->getMessage());
            }
            $attrs = '';
            foreach ($options as $key => $attr) {
                $attrs .=  $key . '="' . $attr . '" ';
            }
            $label = is_array($label) ? $label[0] : $label;
            return '<a href="' . $route . '" ' . $attrs . ' >' . $label . '</a>';
        }

        return '';
    }

    public function defaultLink(
        string $path,
        string $label,
        array $params = [],
        array $options = []
    ): bool|string {
        return $this->link($path, [$label, $label], $params, $options);
    }

    public function __(string $id, $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $id = strtolower($id);
        return $this->translator->trans($id, $parameters, $domain, $locale ?? $this->getRequestLocale());
    }

    public function __u(string $id, $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return ucfirst($this->__($id, $parameters, $domain, $locale ?? $this->getRequestLocale()));
    }

    public function __t(string $id, $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->title(
            $this->twig,
            $this->translator->trans($id, $parameters, $domain, $locale ?? $this->getRequestLocale())
        );
    }

    public function getRequestLocale(): string
    {
        if ($this->locale === null) {
            $this->locale = $this->requestStack->getMainRequest()->getLocale();
        }
        return $this->locale;
    }

    /**
     * Returns a titlecased string.
     *
     * @param string $string A string
     *
     * @return string The titlecased string
     */
    public function title(Environment $env, $string)
    {
        if (null !== $charset = $env->getCharset()) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        }

        return ucwords(strtolower($string));
    }

    /**
     * Get the value of instance
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    // private function getLocale(): string
    // {
    //     $request = $this->requestStack->getMasterRequest();
    //     return $request->attributes->get('_locale') ?? $request->getLocale();
    // }
}
