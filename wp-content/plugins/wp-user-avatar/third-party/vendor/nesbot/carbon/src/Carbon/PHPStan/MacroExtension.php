<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProfilePressVendor\Carbon\PHPStan;

use ProfilePressVendor\PHPStan\Reflection\Assertions;
use ProfilePressVendor\PHPStan\Reflection\ClassReflection;
use ProfilePressVendor\PHPStan\Reflection\MethodReflection;
use ProfilePressVendor\PHPStan\Reflection\MethodsClassReflectionExtension;
use ProfilePressVendor\PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use ProfilePressVendor\PHPStan\Reflection\ReflectionProvider;
use ProfilePressVendor\PHPStan\Type\TypehintHelper;
/**
 * Class MacroExtension.
 *
 * @codeCoverageIgnore Pure PHPStan wrapper.
 */
final class MacroExtension implements MethodsClassReflectionExtension
{
    /**
     * @var PhpMethodReflectionFactory
     */
    protected $methodReflectionFactory;
    /**
     * @var MacroScanner
     */
    protected $scanner;
    /**
     * Extension constructor.
     *
     * @param PhpMethodReflectionFactory $methodReflectionFactory
     * @param ReflectionProvider         $reflectionProvider
     */
    public function __construct(PhpMethodReflectionFactory $methodReflectionFactory, ReflectionProvider $reflectionProvider)
    {
        $this->scanner = new MacroScanner($reflectionProvider);
        $this->methodReflectionFactory = $methodReflectionFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return $this->scanner->hasMethod($classReflection->getName(), $methodName);
    }
    /**
     * {@inheritdoc}
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $builtinMacro = $this->scanner->getMethod($classReflection->getName(), $methodName);
        $supportAssertions = class_exists(Assertions::class);
        return $this->methodReflectionFactory->create($classReflection, null, $builtinMacro, $classReflection->getActiveTemplateTypeMap(), [], TypehintHelper::decideTypeFromReflection($builtinMacro->getReturnType()), null, null, $builtinMacro->isDeprecated()->yes(), $builtinMacro->isInternal(), $builtinMacro->isFinal(), $supportAssertions ? null : $builtinMacro->getDocComment(), $supportAssertions ? Assertions::createEmpty() : null, null, $builtinMacro->getDocComment(), []);
    }
}
