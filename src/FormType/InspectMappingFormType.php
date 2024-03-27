<?php

declare(strict_types=1);

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
    /** The Contao map builder. */
    private MapBuilderInterface $mapBuilder;

    /** The Contao token name. */
    private string $tokenName;

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_field_name' => 'REQUEST_TOKEN',
            'csrf_token_id'   => $this->tokenName,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
