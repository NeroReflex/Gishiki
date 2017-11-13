<?php
/**************************************************************************
Copyright 2017 Benato Denis

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*****************************************************************************/

namespace Gishiki\Core\MVC\Controller\Plugins;

use Gishiki\Algorithms\Collections\CollectionInterface;
use Gishiki\Core\MVC\Controller\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Gishiki\Core\Application;

/**
 * This is a plugin used to call the Twig template engine.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class TwigWrapper extends Plugin
{
    const TEMPLATE_DIRECTORY = "view";
    const CACHE_DIRECTORY = "cache";

    /**
     * @var \Twig_LoaderInterface|null the filesystem loader
     */
    private $loader = null;

    /**
     * @var \Twig_Environment|null the environment
     */
    private $twig = null;

    /**
     * TwigWrapper constructor:
     * setup the plugin
     *
     * @param RequestInterface  $request  the HTTP request
     * @param ResponseInterface $response the HTTP response
     * @param Application|null  $app      the current application instance
     */
    public function __construct(RequestInterface &$request, ResponseInterface &$response, Application $app = null)
    {
        //this is important, NEVER forget!
        parent::__construct($request, $response, $app);
    }

    private function isLoaderReady()
    {
        return ($this->loader instanceof \Twig_LoaderInterface);
    }

    public function setTwigLoader(\Twig_LoaderInterface $loader = null)
    {
        $templatesDirectory = ($this->application instanceof Application) ? $this->application->getCurrentDirectory() : filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
        $templatesDirectory .= static::TEMPLATE_DIRECTORY;

        if (!file_exists($templatesDirectory)) {
            mkdir($templatesDirectory);
        }

        //load the template directory
        $this->loader = (is_null($loader)) ? new \Twig_Loader_Filesystem($templatesDirectory) : $loader;
    }

    /**
     * Check if the twig environment is already loaded.
     *
     * @return bool true only if the twig environment is already loaded
     */
    private function isLoadedTwig() : bool
    {
        return ($this->twig instanceof \Twig_Environment);
    }

    /**
     * Prepare the Twig environment from loader loaded into constructor.
     *
     * Lazily loading the Twig environment is super-useful for Unit testing!
     */
    private function prepareTwig()
    {
        $cacheDirectory = ($this->application instanceof Application) ? $this->application->getCurrentDirectory() : filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
        $cacheDirectory .= $cacheDirectory.static::CACHE_DIRECTORY;

        if (!file_exists($cacheDirectory)) {
            mkdir($cacheDirectory);
        }

        //load the twig environment
        $this->twig = new \Twig_Environment($this->loader, [
            "cache" => $cacheDirectory
        ]);
    }

    /**
     * Render the template with the given name using passed data.
     *
     * @param string              $template the template name
     * @param CollectionInterface $data     data collection to be used on rendering
     */
    public function renderTwigTemplate($template, CollectionInterface &$data)
    {
        if (!$this->isLoadedTwig()) {
            if (!$this->isLoaderReady()) {
                $this->setLoader();
            }

            $this->prepareTwig();
        }

        //use twig to render the template.... nice and easy!
        $renderBuffer = $this->twig->render($template, $data->all());

        //write the result to the current response
        $this->getResponse()->getBody()->write($renderBuffer);
    }
}

