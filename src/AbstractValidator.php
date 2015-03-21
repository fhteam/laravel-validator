<?php

namespace FHTeam\LaravelValidator;

use ArrayAccess;
use Exception;
use FHTeam\LaravelValidator\Utility\Arr;
use FHTeam\LaravelValidator\Utility\ArrayDataStorage;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;
use IteratorAggregate;

/**
 * Abstract class containing logic common for all validators
 *
 * @package FHTeam\LaravelValidator
 */
abstract class AbstractValidator implements MessageProvider, ArrayAccess, IteratorAggregate
{
    /**
     * @var null|bool Null if validation never ran, false if failed, true if passed
     */
    protected $validationPassed = null;

    /**
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var ArrayDataStorage
     */
    protected $dataStorage;

    /**
     * @var callable|int
     */
    protected $keyCase = ArrayDataStorage::KEY_CASE_CAMEL;

    /**
     * Template variables to replace in rules
     *
     * @var array
     */
    protected $templateReplacements = [];

    /**
     * @var array
     */
    protected $failedMessages;

    /**
     * @var array
     */
    protected $failedRules;

    /**
     * @param $object
     *
     * @return string
     */
    abstract protected function getValidationGroup($object);

    /**
     * @param $object
     *
     * @return array
     */
    abstract protected function getObjectData($object = null);

    /**
     * IoC invoked constructor
     *
     * @param Factory $validatorFactory
     */
    public function __construct(Factory $validatorFactory)
    {
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Validates an object and raises exception, if it is not valid
     *
     * @param mixed $object
     *
     * @throws ValidationException
     */
    public function assertIsValid($object)
    {
        if (null === $this->validationPassed) {
            $this->validationPassed = $this->isThisValid($object);
        }

        if (!$this->validationPassed) {
            throw new ValidationException("Passed object supposed to be valid, but it is not", $this);
        }
    }

    /**
     * Internal validation function to validate already serialized data. Data keys are camelised before validation
     *
     * @param mixed $object Object to validate
     *
     * @return bool
     */
    public function isThisValid($object = null)
    {
        $objectData = $this->getObjectData($object);
        $validationGroup = $this->getValidationGroup($object);
        $rules = Arr::mergeByCondition($this->rules, $validationGroup);
        $rules = $this->preProcessRules($rules, $objectData);

        $validator = $this->validatorFactory->make($objectData, $rules);
        $this->setupValidator($validator);
        $this->validationPassed = !$validator->fails();

        if ($this->validationPassed) {
            $this->dataStorage = new ArrayDataStorage($this->keyCase);
            $this->dataStorage->setItems($objectData);
            $this->failedMessages = [];
            $this->failedRules = [];
        } else {
            $this->failedMessages = new MessageBag($validator->getMessageBag()->getMessages());
            $this->failedRules = $validator->failed();
            $this->dataStorage = null;
        }

        return $this->validationPassed;
    }

    /**
     * Adds template variables to process with rules
     *
     * @param array $vars
     *
     * @throws Exception
     * @return $this
     */
    public function addTemplateReplacements(array $vars)
    {
        foreach ($vars as $varName => $varValue) {
            $this->templateReplacements['{'.$varName.'}'] = $varValue;
        }

        return $this;
    }

    /**
     * Method is called to preprocess rules if required. By default it templatize them
     *
     * @param array $rules Rules to preprocess
     * @param array $data  Data to be validated
     *
     * @return array Preprocessed rules
     */
    public function preProcessRules(array $rules, array $data)
    {
        foreach ($rules as $key => $text) {
            $rules[$key] = str_replace(
                array_keys($this->templateReplacements),
                array_values($this->templateReplacements),
                $text
            );
        }

        return $rules;
    }

    /**
     * Method is called to prepare validator for validation
     * just before passed() is called
     *
     * @param Validator $validator
     */
    public function setupValidator(Validator $validator)
    {
    }

    public function isValidationPassed()
    {
        return $this->validationPassed;
    }

    /**
     * Returns text version about what failed
     *
     * @return array
     */
    public function getMessageBag()
    {
        return $this->failedMessages;
    }

    /**
     * Returns a list of failed rules
     *
     * @return array
     */
    public function getFailedRules()
    {
        return $this->failedRules;
    }

    public function getIterator()
    {
        return $this->dataStorage->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->dataStorage->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->dataStorage->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->dataStorage->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->dataStorage->offsetUnset($offset);
    }

    public function __isset($name)
    {
        return $this->dataStorage->__isset($name);
    }

    public function __unset($name)
    {
        $this->dataStorage->__unset($name);
    }

    public function __invoke($object)
    {
        return $this->isThisValid($object);
    }

    public function __toString()
    {
        return implode("\r\n", $this->failedMessages);
    }

    public function __debugInfo()
    {
        return [
            "validationPassed" => $this->validationPassed,
            "rules" => $this->rules,
            "dataStorage::getItems()" => $this->dataStorage->getItems(),
            "keyCase" => $this->keyCase,
            "templateReplacements" => $this->templateReplacements,
            "failedMessages" => $this->failedMessages,
            "failedRules" => $this->failedRules,
        ];
    }
}
