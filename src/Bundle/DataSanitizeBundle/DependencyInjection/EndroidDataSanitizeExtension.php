<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize\Bundle\DataSanitizeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EndroidDataSanitizeExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $sanitizerDefinition = $container->getDefinition('endroid_data_sanitize.sanitizer');
        $sanitizerDefinition->replaceArgument(0, $config['entities']);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Add the package to the assets configuration so the correct manifest is used
        $container->prependExtensionConfig('framework', [
            'assets' => [
                'packages' => [
                    'endroid_data_sanitize' => [
                        'json_manifest_path' => '%kernel.project_dir%/public/bundles/endroiddatasanitize/build/manifest.json',
                    ],
                ],
            ],
        ]);
    }
}
