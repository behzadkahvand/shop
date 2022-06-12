<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;
use App\Service\Pipeline\PipelineRepository;
use App\Service\Pipeline\PipelineStageInterface;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PipelineStagePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $refMap = [
            'tag'     => [],
            'payload' => [],
        ];

        foreach ($container->findTaggedServiceIds('app.pipeline_stage') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class      = $definition->getClass();

            $reflection = $container->getReflectionClass($class);

            if (!$reflection->implementsInterface(PipelineStageInterface::class)) {
                $message = sprintf(
                    'class %s must implement %s interface to be used as a pipeline stage',
                    $class,
                    PipelineStageInterface::class
                );

                throw new \RuntimeException($message);
            }

            if ($reflection->implementsInterface(TagAwarePipelineStageInterface::class)) {
                $tag                                        = $class::getTag();
                $refMap['tag'][$tag][$class::getPriority()] = new Reference($id);

                $definition->addTag($tag);
            } elseif ($reflection->implementsInterface(PayloadAwarePipelineStageInterface::class)) {
                $payload = $class::getSupportedPayload();

                if (!class_exists($payload) || !is_subclass_of($payload, AbstractPipelinePayload::class)) {
                    $message = sprintf(
                        'class %s must return FQN of a class that extends %s',
                        $class,
                        AbstractPipelinePayload::class
                    );

                    throw new \RuntimeException($message);
                }

                $refMap['payload'][$payload][$class::getPriority()] = new Reference($id);
            }
        }

        if (!empty($refMap['tag']) || !empty($refMap['payload'])) {
            foreach ($refMap as $key => $referenceSet) {
                foreach ($referenceSet as $tagOrPayload => $references) {
                    ksort($references);
                    $refMap[$key][$tagOrPayload] = new IteratorArgument(array_values(array_reverse($references)));
                }
            }

            $container->getDefinition(PipelineRepository::class)
                      ->setArgument('$stages', $refMap);
        }
    }
}
