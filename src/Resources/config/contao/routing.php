<?php

declare(strict_types=1);

use CyberSpectrum\I18N\ContaoBundle\Controller\ContaoInspectProblemsController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('cyberspectrum_i18n.contao-backend', '/contao/cs.translations')
        ->controller(ContaoInspectProblemsController::class)
        ->defaults(['_scope' => 'backend', '_token_check' => true]);
};
