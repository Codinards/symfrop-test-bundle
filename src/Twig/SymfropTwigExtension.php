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
use Twig\TwigFunction;

/**
 * The symfrop twig bundle
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
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
    }

    public function getFunctions(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFunction('create_symfrop_link', [$this, 'link'], ['is_safe' => ['html']]),
            new TwigFunction('create_symfrop_link_default', [$this, 'linkDefault'], ['is_safe' => ['html']]),
            new TwigFunction('create_symfrop_form', [$this, 'form'], ['is_safe' => ['html']]),
            new TwigFunction('__', [$this, '__']),
            new TwigFunction('__U', [$this, '__u']),
            new TwigFunction('_U', [$this, '__u']),
            new TwigFunction('__u', [$this, '__u']),
            new TwigFunction('_u', [$this, '__u']),
            new TwigFunction('_t', [$this, '__t']),
            new TwigFunction('_T', [$this, '__t']),
            new TwigFunction('__t', [$this, '__t']),
            new TwigFunction('__T', [$this, '__t']),
            new TwigFunction('merge', 'array_merge'),
        ];
    }

    public function link(
        string $path,
        string|array $label,
        array $params = [],
        array $options = []
    ): bool|string {
        $returnBool = $options['boolean'] ?? false;
        $valid = $options['valid'] ?? true;
        if ($valid) {
            $isAuthorize = $this->annotationManager->isAuthorize($path);

            if ((int) $isAuthorize === 1) {
                if ($returnBool) return true;
                $title = $options['title'] ?? false;
                $style = $options['style'] ?? null;
                try {

                    if (!isset($params['_locale'])) {
                        $params['_locale'] = $this->getRequestLocale();
                    }

                    $route = $this->router->generate($path, $params);
                } catch (\Exception $e) {
                    throw new SymfropBaseException($e->getMessage());
                }
                $class = isset($options['class']) ? 'class="' . $options['class'] . '"' : '';
                $style = $style ? 'style="' . $options['style'] . '"' : '';
                $title = $title ? 'title="' . $title . '"' : '';
                $label = is_array($label) ? $label[0] : $label;
                return "<a href='{$route}' {$class} {$style} {$title}>{$label}</a>";
            }
        }
        return $returnBool ? false : (is_array($label) ? $label[0] : '');
    }

    public function linkDefault(
        string $path,
        string $label,
        array $params = [],
        array $options = []
    ): bool|string {
        return $this->link($path, [$label, $label], $params, $options);
    }

    public function form($name, string $label, $params = [], array $options = [], string $methods = 'post')
    {
        if (isset($options['valid']) and $options['valid'] === false) return '';
        $form_attr = '';
        foreach ($options['attr'] ?? [] as $key => $attr) {
            $form_attr .=  $key . '="' . $attr . '" ';
        }

        $button_attr = '';
        foreach ($options['btn_attr'] ?? [] as $key => $attr) {
            $button_attr .=  $key . '="' . $attr . '" ';
        }

        if ($this->annotationManager->isAuthorize($name)) {

            if (!isset($params['_locale'])) {
                $params['_locale'] = $this->getRequestLocale();
            }

            $path = $this->router->generate($name, $params);
            $confirm = isset($options['onSubmit']) ? ' onSubmit =" return confirm(\'' . $options['onSubmit'] . '\')"'  : '';
            $csrf_value = ($options['csrf_name'] ?? null) ? 'value="' . $this->tokenManager->getToken($options['csrf_name'])->getValue() . '"' : '';

            return "<form action='$path' method='$methods' " . trim($form_attr)  . $confirm  . ">
            <input type='hidden' name='_csrf_token'  $csrf_value/> 
            <button " . trim($button_attr) . ">$label</button>
            </form>";
        }
        return '';
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
