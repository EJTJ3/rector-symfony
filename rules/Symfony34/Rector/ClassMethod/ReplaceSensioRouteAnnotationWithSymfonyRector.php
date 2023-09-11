<?php

declare(strict_types=1);

namespace Rector\Symfony\Symfony34\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://medium.com/@nebkam/symfony-deprecated-route-and-method-annotations-4d5e1d34556a
 * @changelog https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 *
 * @see \Rector\Symfony\Tests\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\ReplaceSensioRouteAnnotationWithSymfonyRectorTest
 */
final class ReplaceSensioRouteAnnotationWithSymfonyRector extends AbstractRector
{
    public function __construct(
        private readonly SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory,
        private readonly PhpDocTagRemover $phpDocTagRemover,
        private readonly RenamedClassesDataCollector $renamedClassesDataCollector,
        private readonly DocBlockUpdater $docBlockUpdater,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace Sensio @Route annotation with Symfony one',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Class_::class];
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasByAnnotationClass(SymfonyAnnotation::ROUTE)) {
            return null;
        }

        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route'
        );

        if (! $doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }

        $this->renamedClassesDataCollector->addOldToNewClasses([
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route' => SymfonyAnnotation::ROUTE,
        ]);

        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);

        // unset service, that is deprecated
        $values = $doctrineAnnotationTagValueNode->getValues();
        $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($values);

        $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
