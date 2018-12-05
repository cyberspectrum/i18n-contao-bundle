<?php

/**
 * This file is part of cyberspectrum/i18n-contao-bundle.
 *
 * (c) 2018 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-contao-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-contao-bundle/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\ContaoBundle\FormType;

use CyberSpectrum\I18N\Contao\Mapping\MapBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form type helps selecting the map for problem inspection.
 */
class InspectMappingFormType extends AbstractType
{
    /**
     * The Contao map builder.
     *
     * @var MapBuilderInterface
     */
    private $mapBuilder;

    /**
     * The Contao token name.
     *
     * @var string
     */
    private $tokenName;

    /**
     * Create a new instance.
     *
     * @param MapBuilderInterface $mapBuilder The database.
     * @param string              $tokenName  The crsf token name.
     */
    public function __construct(MapBuilderInterface $mapBuilder, string $tokenName)
    {
        $this->mapBuilder = $mapBuilder;
        $this->tokenName  = $tokenName;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $languages = [];
        foreach ($this->mapBuilder->getSupportedLanguages() as $language) {
            $languages[$language] = $language;
        }

        $builder
            ->add('source', ChoiceType::class, ['label' => 'Source language', 'choices' => $languages])
            ->add('target', ChoiceType::class, ['label' => 'Target language', 'choices' => $languages])
            ->add('submit', SubmitType::class, ['label' => 'Update']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_field_name' => 'REQUEST_TOKEN',
            'csrf_token_id'   => $this->tokenName,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
