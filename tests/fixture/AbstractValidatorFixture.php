<?php

namespace FHTeam\LaravelValidator\Test\Fixture;

use FHTeam\LaravelValidator\Validator\AbstractValidator;
use Illuminate\Validation\Validator;

/**
 * Class AbstractValidatorConcrete
 *
 * @package FHTeam\LaravelValidator\Test\Fixture
 */
class AbstractValidatorFixture extends AbstractValidator
{
    protected $rules = [
        'unused' => ['dummy' => 'required'],
        'group' => [
            'string' => 'required',
            'int' => 'required|numeric',
        ],
    ];

    /**
     * @param $object
     *
     * @return string
     */
    protected function getState($object)
    {
        return 'group';
    }

    /**
     * @param $object
     *
     * @return array
     */
    protected function getObjectData($object = null)
    {
        return $object;
    }

    public function setupValidator(Validator $validator)
    {
        parent::setupValidator($validator);
        $validator->sometimes(
            'sometimes',
            'numeric|min:1|max:1',
            function () {
                return true;
            }
        );
    }
}
