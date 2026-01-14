<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\Test\DependencyInjection;

use Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CyberSpectrumI18NContaoExtension;
use MadeYourDay\RockSolidCustomElements\RockSolidCustomElementsBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** This tests the bundle. */
#[CoversClass(CyberSpectrumI18NContaoExtension::class)]
class CyberSpectrumI18NContaoExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.bundles', [
            RockSolidCustomElementsBundle::class => RockSolidCustomElementsBundle::class,
            'changelanguage'                     => ContaoModuleBundle::class,
            MetaModelsCoreBundle::class          => MetaModelsCoreBundle::class,
        ]);
        $container->setParameter('cyberspectrum_i18n.provider_names', []);
        $container->setParameter('cyberspectrum_i18n.contao.extractors', []);
        $container->setParameter('kernel.root_dir', sys_get_temp_dir());
        $container->setParameter('contao.csrf_token_name', 'dummy-token');

        $bundle = new CyberSpectrumI18NContaoExtension();

        $bundle->load([], $container);

        $resources = $container->getResources();
        $files     = [];
        foreach ($resources as $resource) {
            if ($resource instanceof FileResource) {
                $files[] = (string) $resource;
            }
        }

        // Ensure all definitions are pointing to valid classes or interfaces.
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (null === $class = $definition->getClass()) {
                $class = $serviceId;
            }

            self::assertTrue(
                class_exists($class) || interface_exists($class),
                'Class ' . $class . ' does not exist for ' . $serviceId
            );
        }

        // Assert we get all files loaded.
        self::assertSame([
            realpath(__DIR__ . '/../../src/Resources/config/contao/services.php'),
            realpath(__DIR__ . '/../../src/Resources/config/contao/extractor.php'),
            realpath(__DIR__ . '/../../src/Resources/config/contao/rock-solid-custom-elements.php'),
            realpath(__DIR__ . '/../../src/Resources/config/contao/terminal42-change-language.php'),
            realpath(__DIR__ . '/../../src/Resources/config/metamodels.php'),
        ], $files);

        $container->compile();
    }

    public function testLoadSkipsConfigForUnknownBundles(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.bundles', [
        ]);

        $bundle = new CyberSpectrumI18NContaoExtension();

        $bundle->load([], $container);

        $resources = $container->getResources();
        $files     = [];
        foreach ($resources as $resource) {
            if ($resource instanceof FileResource) {
                $files[] = (string) $resource;
            }
        }

        self::assertSame([
            realpath(__DIR__ . '/../../src/Resources/config/contao/services.php'),
            realpath(__DIR__ . '/../../src/Resources/config/contao/extractor.php'),
        ], $files);
    }
}
