<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator;

use function array_unique;
use function compact;
use function in_array;
use kanso\framework\ioc\Container;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;
use kanso\framework\validator\filters\BoolVal as FilterBool;
use kanso\framework\validator\filters\Email as FilterEmail;
use kanso\framework\validator\filters\FilterInterface;
use kanso\framework\validator\filters\FloatingPoint as FilterFloat;
use kanso\framework\validator\filters\HtmlDecode as FilterHtmlDecode;
use kanso\framework\validator\filters\HtmlEncode as FilterHtmlEncode;
use kanso\framework\validator\filters\Integer as FilterInteger;
use kanso\framework\validator\filters\Json as FilterJson;
use kanso\framework\validator\filters\LowerCase as FilterLowerCase;
use kanso\framework\validator\filters\Numeric as FilterNumeric;
use kanso\framework\validator\filters\SanitizeString as FilterString;
use kanso\framework\validator\filters\StripTags as FilterStripTags;
use kanso\framework\validator\filters\Trim as FilterTrim;
use kanso\framework\validator\filters\UpperCase as FilterUpperCase;
use kanso\framework\validator\filters\UrlDecode as FilterUrlDecode;
use kanso\framework\validator\filters\UrlEncode as FilterUrlEncode;
use kanso\framework\validator\rules\Alpha;
use kanso\framework\validator\rules\Alphanumeric;
use kanso\framework\validator\rules\AlphanumericDash;
use kanso\framework\validator\rules\AlphaSpace;
use kanso\framework\validator\rules\Email;
use kanso\framework\validator\rules\ExactLength;

use kanso\framework\validator\rules\FloatingPoint;
use kanso\framework\validator\rules\GreaterThan;
use kanso\framework\validator\rules\GreaterThanOrEqualTo;
use kanso\framework\validator\rules\In;
use kanso\framework\validator\rules\Integer;
use kanso\framework\validator\rules\IP;
use kanso\framework\validator\rules\JSON;
use kanso\framework\validator\rules\LessThan;
use kanso\framework\validator\rules\LessThanOrEqualTo;
use kanso\framework\validator\rules\Match;
use kanso\framework\validator\rules\MaxLength;
use kanso\framework\validator\rules\MinLength;
use kanso\framework\validator\rules\NotIn;
use kanso\framework\validator\rules\Regex;
use kanso\framework\validator\rules\Required;
use kanso\framework\validator\rules\RuleInterface;

use kanso\framework\validator\rules\URL;
use kanso\framework\validator\rules\WithParametersInterface;
use RuntimeException;
use function vsprintf;

/**
 * Input validation.
 *
 * @author Joe J. Howard
 */
class Validator
{
	/**
	 * Input.
	 *
	 * @var array
	 */
	private $input;

	/**
	 * Rule sets.
	 *
	 * @var array
	 */
	private $ruleSets;

	/**
	 * Filter sets.
	 *
	 * @var array
	 */
	private $filterSets;

	/**
	 * Container.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	private $container;

	/**
	 * Rules.
	 *
	 * @var array
	 */
	private $rules =
	[
		'alpha'                    => Alpha::class,
		'alpha_space'              => AlphaSpace::class,
		'alpha_dash'               => AlphanumericDash::class,
		'alpha_numeric'            => Alphanumeric::class,
		'email'                    => Email::class,
		'exact_length'             => ExactLength::class,
		'float'                    => FloatingPoint::class,
		'greater_than_or_equal_to' => GreaterThanOrEqualTo::class,
		'greater_than'             => GreaterThan::class,
		'in'                       => In::class,
		'integer'                  => Integer::class,
		'ip'                       => IP::class,
		'json'                     => JSON::class,
		'less_than_or_equal_to'    => LessThanOrEqualTo::class,
		'less_than'                => LessThan::class,
		'match'                    => Match::class,
		'max_length'               => MaxLength::class,
		'min_length'               => MinLength::class,
		'not_in'                   => NotIn::class,
		'regex'                    => Regex::class,
		'required'                 => Required::class,
		'url'                      => URL::class,
	];

	/**
	 * Filters.
	 *
	 * @var array
	 */
	private $filters =
	[
		'boolean'     => FilterBool::class,
		'email'       => FilterEmail::class,
		'float'       => FilterFloat::class,
		'html_decode' => FilterHtmlDecode::class,
		'html_encode' => FilterHtmlEncode::class,
		'integer'     => FilterInteger::class,
		'json'        => FilterJson::class,
		'lowercase'   => FilterLowerCase::class,
		'numeric'     => FilterNumeric::class,
		'string'      => FilterString::class,
		'strip_tags'  => FilterStripTags::class,
		'trim'        => FilterTrim::class,
		'uppercase'   => FilterUpperCase::class,
		'url_decode'  => FilterUrlDecode::class,
		'url_encode'  => FilterUrlEncode::class,
	];

	/**
	 * Original field names.
	 *
	 * @var array
	 */
	private $originalFieldNames;

	/**
	 * Is the input valid?
	 *
	 * @var bool
	 */
	private $isValid = true;

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Constructor.
	 *
	 * @param array                               $input     Input
	 * @param array                               $ruleSets  Rule sets
	 * @param array                               $filters   Filter sets (optional default [])
	 * @param \kanso\framework\ioc\Container|null $container Container (optional) (default null)
	 */
	public function __construct(array $input, array $ruleSets, array $filters = [], Container $container = null)
	{
		$this->input = $input;

		$this->ruleSets = $ruleSets;

		$this->filterSets = $filters;

		$this->container = !$container ? Container::instance() : $container;
	}

	/**
	 * Returns true if all rules passed and false if validation failed.
	 *
	 * @access public
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isValid(array &$errors = null): bool
	{
		list($isValid, $errors) = $this->process();

		return $isValid === true;
	}

	/**
	 * Returns false if all rules passed and true if validation failed.
	 *
	 * @access public
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isInvalid(array &$errors = null): bool
	{
		list($isValid, $errors) = $this->process();

		return $isValid === false;
	}

	/**
	 * Returns the validation errors.
	 *
	 * @access public
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Filters the input.
	 *
	 * @access public
	 * @return array
	 */
	public function filter(): array
	{
		$input = $this->input;

		foreach ($this->filterSets as $field => $filters)
		{
			foreach ($filters as $filter)
			{
				if (isset($input[$field]))
				{
					$input[$field] = $this->applyFilter($input[$field], $filter);
				}
				else
				{
					$class = $this->filterFactory($filter);

					if ($class->filterWhenUnset() === true)
					{
						$input[$field] = $class->filter('');
					}
				}
			}
		}

		return $input;
	}

	/**
	 * Filters the input.
	 *
	 * @access private
	 * @param  mixed  $value Field value
	 * @param  string $name  Filter name to use
	 * @return mixed
	 */
	private function applyFilter($value, string $name)
	{
		$class = $this->filterFactory($name);

		return $class->filter($value);
	}

	/**
	 * Creates a filter instance.
	 *
	 * @access private
	 * @param  string                                             $name Rule name
	 * @return \kanso\framework\validator\filters\FilterInterface
	 */
	private function filterFactory(string $name): FilterInterface
	{
		$name = $this->getFilterClassName($name);

		if (!$this->container->has($name))
		{
			$this->container->set($name, new $name);
		}

		return $this->container->get($name);
	}

	/**
	 * Returns the filter class name.
	 *
	 * @access private
	 * @param  string $name Filter name
	 * @return string
	 */
	private function getFilterClassName(string $name): string
	{
		if(!isset($this->filters[$name]))
		{
			throw new RuntimeException(vsprintf('Call to undefined filter rule [ %s ].', [$name]));
		}

		return $this->filters[$name];
	}

	/**
	 * Saves original field name along with the expanded field name.
	 *
	 * @access private
	 * @param array  $fields Expanded field names
	 * @param string $field  Original field name
	 */
	private function saveOriginalFieldNames(array $fields, string $field)
	{
		foreach($fields as $expanded)
		{
			$this->originalFieldNames[$expanded] = $field;
		}
	}

	/**
	 * Returns the original field name.
	 *
	 * @access private
	 * @param  string $field Field name
	 * @return string
	 */
	private function getOriginalFieldName(string $field): string
	{
		return $this->originalFieldNames[$field] ?? $field;
	}

	/**
	 * Parses the rule.
	 *
	 * @access private
	 * @param  string $rule Rule
	 * @return object
	 */
	private function parseRule(string $rule)
	{
		list($name, $parameters) = $this->parseRuleParams($rule);

		return (object) compact('name', 'parameters');
	}

	/**
	 * Parses the rule parameters.
	 *
	 * @access private
	 * @param  string $rule Rule
	 * @return array
	 */
	private function parseRuleParams(string $rule): array
	{
		if (!Str::contains($rule, '('))
		{
			return [$rule, []];
		}

		$name = Str::getBeforeFirstChar($rule, '(');

		$param = trim(rtrim(Str::getAfterFirstChar($rule, '('), ')'));

		if (Str::contains($param, '[') || Str::contains($param, '{'))
		{
			$_paramArr = json_decode($param);

			if (is_array($_paramArr) && !empty($_paramArr) && json_last_error() == JSON_ERROR_NONE)
			{
				$param = $_paramArr;
			}
		}

		return [$name, [$param]];
	}

	/**
	 * Returns the rule class name.
	 *
	 * @access private
	 * @param  string $name Rule name
	 * @return string
	 */
	private function getRuleClassName(string $name): string
	{
		if(!isset($this->rules[$name]))
		{
			throw new RuntimeException(vsprintf('Call to undefined validation rule [ %s ].', [$name]));
		}

		return $this->rules[$name];
	}

	/**
	 * Creates a rule instance.
	 *
	 * @access private
	 * @param  string                                         $name Rule name
	 * @return \kanso\framework\validator\rules\RuleInterface
	 */
	private function ruleFactory(string $name): RuleInterface
	{
		$name = $this->getRuleClassName($name);

		if (!$this->container->has($name))
		{
			$this->container->set($name, function() use ($name)
			{
				return new $name;
			});
		}

		return $this->container->get($name);
	}

	/**
	 * Returns true if the input field is considered empty and false if not.
	 *
	 * @access private
	 * @param  mixed $value Value
	 * @return bool
	 */
	private function isInputFieldEmpty($value): bool
	{
		return in_array($value, ['', null, []], true);
	}

	/**
	 * Returns the error message.
	 *
	 * @access private
	 * @param  \kanso\framework\validator\rules\RuleInterface $rule       Rule
	 * @param  string                                         $field      Field name
	 * @param  object                                         $parsedRule Parsed rule
	 * @return string
	 */
	private function getErrorMessage(RuleInterface $rule, $field, $parsedRule): string
	{
		$field = $this->getOriginalFieldName($field);

		return $rule->getErrorMessage($field);
	}

	/**
	 * Validates the field using the specified rule.
	 *
	 * @access private
	 * @param  string $field Field name
	 * @param  string $rule  Rule
	 * @return bool
	 */
	private function validate(string $field, string $rule): bool
	{
		$parsedRule = $this->parseRule($rule);

		$rule = $this->ruleFactory($parsedRule->name);

		// Just return true if the input field is empty and the rule doesn't validate empty input

		if($this->isInputFieldEmpty($inputValue = Arr::get($this->input, $field)) && $rule->validateWhenEmpty() === false)
		{
			return true;
		}

		// Set parameters if the rule requires it

		if($rule instanceof WithParametersInterface)
		{
			$rule->setParameters($parsedRule->parameters);
		}

		// Validate input

		if($rule->validate($inputValue, $this->input) === false)
		{
			$this->errors[$field] = $this->getErrorMessage($rule, $field, $parsedRule);

			return $this->isValid = false;
		}

		return true;
	}

	/**
	 * Processes all validation rules and returns an array containing
	 * the validation status and potential error messages.
	 *
	 * @access private
	 * @return array
	 */
	private function process(): array
	{
		foreach($this->ruleSets as $field => $ruleSet)
		{
			// Ensure that we don't have any duplicated rules for a field

			$ruleSet = array_unique($ruleSet);

			// Validate field and stop as soon as one of the rules fail

			foreach($ruleSet as $rule)
			{
				if($this->validate($field, $rule) === false)
				{
					break;
				}
			}
		}

		return [$this->isValid, $this->errors];
	}
}
