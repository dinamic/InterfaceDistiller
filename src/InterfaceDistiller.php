<?php
namespace com\github\gooh\InterfaceDistiller;
class InterfaceDistiller
{
    /**
     * @var Distillate
     */
    protected $distillate;

    /**
     * @var string
     */
    protected $reflectionClass;

    /**
     * @var integer
     */
    protected $methodModifiers;

    /**
     * @var boolean
     */
    protected $excludeImplementedMethods;

    /**
     * @var boolean
     */
    protected $excludeInheritedMethods;

    /**
     * @var boolean
     */
    protected $excludeTraitMethods;

    /**
     * @var boolean
     */
    protected $excludeMagicMethods;

    /**
     * @var boolean
     */
    protected $excludeOldStyleConstructors;

    /**
     * @var string
     */
    protected $pcrePattern;

    /**
     * @var \SplFileObject
     */
    protected $saveAs;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->distillate = new Distillate();
    }

    /**
     * @param  string $className
     * @return InterfaceDistiller
     */
    public function distillFromClass($className)
    {
        $this->reflectionClass = $className;
        return $this;
    }

    /**
     * @param  integer $reflectionMethodModifiersMask
     * @return InterfaceDistiller
     */
    public function methodsWithModifiers($reflectionMethodModifiersMask)
    {
        $this->methodModifiers = $reflectionMethodModifiersMask;
        return $this;
    }

    /**
     * @param  string $interfaceName
     * @return InterfaceDistiller
     */
    public function intoInterface($interfaceName)
    {
        $this->distillate->setInterfaceName($interfaceName);
        return $this;
    }

    /**
     * @param  string $commaSeparatedInterfaceNames
     * @return InterfaceDistiller
     */
    public function extendInterfaceFrom($commaSeparatedInterfaceNames)
    {
        $this->distillate->setExtendingInterfaces($commaSeparatedInterfaceNames);
        return $this;
    }

    /**
     * @return InterfaceDistiller
     */
    public function excludeImplementedMethods()
    {
        $this->excludeImplementedMethods = true;
        return $this;
    }

    /**
     * @return InterfaceDistiller
     */
    public function excludeInheritedMethods()
    {
        $this->excludeInheritedMethods = true;
        return $this;
    }

    /**
     * @return InterfaceDistiller
     */
    public function excludeTraitMethods()
    {
        $this->excludeTraitMethods = true;
        return $this;
    }

    /**
     * @return InterfaceDistiller
     */
    public function excludeMagicMethods()
    {
        $this->excludeMagicMethods = true;
        return $this;
    }

    /**
     * @return InterfaceDistiller
     */
    public function excludeOldStyleConstructors()
    {
        $this->excludeOldStyleConstructors = true;
        return $this;
    }

    /**
     * @param string $pcrePattern
     * @return InterfaceDistiller
     */
    public function filterMethodsByPattern($pcrePattern)
    {
        $this->pcrePattern = $pcrePattern;
        return $this;
    }

    /**
     * @param \SplFileObject $fileObject
     * @return InterfaceDistiller
     */
    public function saveAs(\SplFileObject $fileObject)
    {
        $this->saveAs = $fileObject;
        return $this;
    }

    /**
     * @return void
     */
    public function distill()
    {
        $reflector = new \ReflectionClass($this->reflectionClass);
        $iterator = new \ArrayIterator(
            $reflector->getMethods($this->methodModifiers)
        );
        $iterator = $this->decorateMethodIterator($iterator, $reflector);

        foreach ($iterator as $method) {
            $this->distillate->addMethod($method);
        }

        $writer = new Distillate\Writer($this->saveAs);
        $writer->writeToFile($this->distillate);
    }

	/**
     * @param \ArrayIterator $iterator
     * @param \ReflectionMethod $reflector
     * @return \Iterator
     */
    protected function decorateMethodIterator(\ArrayIterator $iterator, \ReflectionMethod $reflector)
    {
        if ($this->pcrePattern) {
            $iterator = new Filters\RegexMethodIterator($iterator, $this->pcrePattern);
        }
        if ($this->excludeImplementedMethods) {
            $iterator = new Filters\NoImplementedMethodsIterator($iterator);
        }
        if ($this->excludeInheritedMethods) {
            $iterator = new Filters\NoInheritedMethodsIterator($iterator, $reflector);
        }
        if ($this->excludeOldStyleConstructors) {
            $iterator = new Filters\NoOldStyleConstructorIterator($iterator);
        }
        if ($this->excludeMagicMethods) {
            $iterator = new Filters\NoMagicMethodsIterator($iterator);
        }
        if ($this->excludeTraitMethods) {
            $iterator = new Filters\NoTraitMethodsIterator($iterator);
        }
        return $iterator;
    }

}