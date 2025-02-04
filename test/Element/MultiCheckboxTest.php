<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Form\Element;

use Laminas\Form\Element\MultiCheckbox as MultiCheckboxElement;
use PHPUnit\Framework\TestCase;

use function count;
use function get_class;

class MultiCheckboxTest extends TestCase
{
    public function useHiddenAttributeDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider useHiddenAttributeDataProvider
     */
    public function testProvidesInputSpecificationThatIncludesValidatorsBasedOnAttributes($useHiddenElement)
    {
        $element = new MultiCheckboxElement();
        $options = [
            '1' => 'Option 1',
            '2' => 'Option 2',
            '3' => 'Option 3',
        ];
        $element->setAttributes([
            'options' => $options,
        ]);
        $element->setUseHiddenElement($useHiddenElement);

        $inputSpec = $element->getInputSpecification();
        $this->assertArrayHasKey('validators', $inputSpec);
        $this->assertIsArray($inputSpec['validators']);

        $expectedClasses = [
            'Laminas\Validator\Explode',
        ];
        foreach ($inputSpec['validators'] as $validator) {
            $class = get_class($validator);
            $this->assertContains($class, $expectedClasses, $class);
            switch ($class) {
                case 'Laminas\Validator\Explode':
                    $inArrayValidator = $validator->getValidator();
                    $this->assertInstanceOf('Laminas\Validator\InArray', $inArrayValidator);
                    break;
                default:
                    break;
            }
        }
    }

    public function multiCheckboxOptionsDataProvider()
    {
        return [
            [
                ['foo', 'bar'],
                [
                    'foo' => 'My Foo Label',
                    'bar' => 'My Bar Label',
                ],
            ],
            [
                ['foo', 'bar'],
                [
                    0 => ['label' => 'My Foo Label', 'value' => 'foo'],
                    1 => ['label' => 'My Bar Label', 'value' => 'bar'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider multiCheckboxOptionsDataProvider
     */
    public function testInArrayValidationOfOptions($valueTests, $options)
    {
        $element = new MultiCheckboxElement('my-checkbox');
        $element->setAttributes([
            'options' => $options,
        ]);
        $inputSpec = $element->getInputSpecification();
        $this->assertArrayHasKey('validators', $inputSpec);
        $explodeValidator = $inputSpec['validators'][0];
        $this->assertInstanceOf('Laminas\Validator\Explode', $explodeValidator);
        $this->assertTrue($explodeValidator->isValid($valueTests));
    }

    /**
     * Testing that InArray Validator Haystack is Updated if the Options
     * are added after the validator is attached
     *
     * @dataProvider multiCheckboxOptionsDataProvider
     */
    public function testInArrayValidatorHaystakIsUpdated($valueTests, $options)
    {
        $element = new MultiCheckboxElement('my-checkbox');
        $inputSpec = $element->getInputSpecification();
        $inArrayValidator = $inputSpec['validators'][0]->getValidator();

        $element->setAttributes([
            'options' => $options,
        ]);
        $haystack = $inArrayValidator->getHaystack();
        $this->assertCount(count($options), $haystack);
    }

    public function testAttributeType()
    {
        $element = new MultiCheckboxElement();
        $attributes = $element->getAttributes();

        $this->assertArrayHasKey('type', $attributes);
        $this->assertEquals('multi_checkbox', $attributes['type']);
    }

    public function testSetOptionsOptions()
    {
        $element = new MultiCheckboxElement();
        $element->setOptions([
            'value_options' => ['bar' => 'baz'],
            'options' => ['foo' => 'bar'],
        ]);
        $this->assertEquals(['bar' => 'baz'], $element->getOption('value_options'));
        $this->assertEquals(['foo' => 'bar'], $element->getOption('options'));
    }

    public function testDisableInputSpecification()
    {
        $element = new MultiCheckboxElement();
        $element->setValueOptions([
            'Option 1' => 'option1',
            'Option 2' => 'option2',
            'Option 3' => 'option3',
        ]);
        $element->setDisableInArrayValidator(true);

        $inputSpec = $element->getInputSpecification();
        $this->assertArrayNotHasKey('validators', $inputSpec);
    }

    public function testUnsetValueOption()
    {
        $element = new MultiCheckboxElement();
        $element->setValueOptions([
            'Option 1' => 'option1',
            'Option 2' => 'option2',
            'Option 3' => 'option3',
        ]);
        $element->unsetValueOption('Option 2');

        $valueOptions = $element->getValueOptions();
        $this->assertArrayNotHasKey('Option 2', $valueOptions);
    }

    public function testUnsetUndefinedValueOption()
    {
        $element = new MultiCheckboxElement();
        $element->setValueOptions([
            'Option 1' => 'option1',
            'Option 2' => 'option2',
            'Option 3' => 'option3',
        ]);
        $element->unsetValueOption('Option Undefined');

        $valueOptions = $element->getValueOptions();
        $this->assertArrayNotHasKey('Option Undefined', $valueOptions);
    }

    public function testOptionValueinSelectedOptions()
    {
        $element = new MultiCheckboxElement();
        $element->setValueOptions([
            'Option 1' => 'option1',
            'Option 2' => 'option2',
            'Option 3' => 'option3',
        ]);

        $optionValue = 'option3';
        $selectedOptions = ['option1', 'option3'];
        $element->setValue($selectedOptions);
        $this->assertContains($optionValue, $element->getValue());
    }
}
