# How to contribute

## Submitting bug reports

The preferred way to report bugs is to use the [GitHub issue tracker](https://github.com/joey-j/Kanso/issues). Before reporting a bug, read these pointers.

**Note:** The issue tracker is for *bugs*, not requests for help.

### Reporting bugs effectively

- Kanso is maintained by volunteers. They don't owe you anything, so be polite. Reports with an indignant or belligerent tone tend to be moved to the bottom of the pile.

- Include information about **the browser in which the problem occurred**. Even if you tested several browsers, and the problem occurred in all of them, mention this fact in the bug report. Also include browser version numbers and the operating system that you're on.

- Mention which release of Kanso you're using. Preferably, try also with the current development snapshot, to ensure the problem has not already been fixed.

- Mention very precisely what went wrong. "X is broken" is not a good bug report. What did you expect to happen? What happened instead? Describe the exact steps a maintainer has to take to make the problem occur. We can not fix something that we can not observe.

## Contributing code

- Make sure you have a [GitHub Account](https://github.com/signup/free)
- Fork [Kanso](https://github.com/joey-j/Kanso) ([how to fork a repo](https://help.github.com/articles/fork-a-repo))
- Make your changes
- If your changes are easy to test or likely to regress, add tests.
- Follow the general code style of the rest of the project (see below).
- Submit a pull request ([how to create a pull request (https://help.github.com/articles/fork-a-repo)).
- Don't put more than one feature/fix in a single pull request.

**By contributing code to Kanso you:** 

 - Agree to license the contributed code under Kanso's [GNU license](https://github.com/joey-j/Kanso/blob/master/LICENSE.md).
 
 - Confirm that you have the right to contribute and license the code in question. (Either you hold all rights on the code, or the rights holder has explicitly granted the right to use it like this,through a compatible open source license or through a direct agreement with you.)

### Coding standards

#### PHP tags
Always use long open tags. Never use short tags or ASP style tags. Class files should never include a closing tag.

```php
# Correct

<?php

# Incorrect

<?

# Incorect

<%
```


#### Files

Files should always have the same name as the class they contain. A file should never contain more than one class. The file encoding should always be UTF-8.

```php
# File: /Kanso/Database/Database.php

<?php

namespace Kanso\Database;

use Kanso\Database\Log as Log;

class Database
{

}
```


#### Namespaces

Namespaces should be written exactly as their filepath:

```php
# File: /Kanso/Utility/FileSystem.php

# Correct

namespace Kanso\Utility\;

# Incorect

namespace kanso\utility\;

```


#### Classes

Class names should be written in upper CamelCase:

```php
# Correct

class Image

# Correct

class MyImage

# Incorrect

class image

# Incorrect

class my_image
```


#### Methods

Method names should be written in lower camelCase:

```php
# Correct

public function fooBar()

# Incorrect

public function FooBar()

# Incorrect

public function foo_bar()
```

Unless a method is intended as a Polyfill or a replacement to a regular php function within a class method

```php
class foo 
{

    public function utf8_encode()
    
}
```


#### Variables

Variable names should be written in lower camelCase:

```php
# Correct

$fooBar = null;

# Incorrect

$foobar = null;

# Incorrect

$foo_bar = null;
```


#### Constants

Constant names should be written in upper case and multiple words should be separated with a underscore:

```php
# Correct

const FOO_BAR = 123;

# Correct

define('FOO_BAR', 123);

# Incorrect

const foobar = 123;

# Incorrect

define('foobar', 123);
```


#### Arrays

Arrays should always be defined using the shorthand syntax:

```php
# Correct

$array = [1, 2, 3];

# Correct

$array = ['foo' => 'bar'];

# Incorrect

$array = array(1, 2, 3);

# Incorrect

$array = array('foo' => 'bar');
```


#### Braces

Braces associated with a control statement should always be on the next line, indented to the same level as the control statement:

```php
# Correct

public function foo()
{

}

# Incorrect

public function foo() {

}
```

#### Indentation

Tabs should be used for indentation while spaces should be used to align code:

```php
<?php

namespace foo;

class Bar
{
    public function hello()
    {
        $string  = 'Hello ';
        $string .= 'World!';
        echo $string;
    }
}
```

Use common sense when aligning code:
```php
# Correct
$foo = [
    'bar'     => 'foo',
    'foobar'  => null,
    'longArrayKey'       => true
    'evenLongerArrayKey' => true
];

# Incorrect
$foo = [
    'bar'                => 'foo',
    'foobar'             => null,
    'longArrayKey'       => true
    'evenLongerArrayKey' => true
];

```

#### Comments

All classes, methods and functions are commented using the PHPDoc standard.

This makes it easy to understand what the code does and it also enables IDEs to provide improved code completion, type hinting and debugging.

```php
/**
 * Returns a greeting.
 * 
 * @access  public
 * @param   string  $name  Name of the person you want to greet
 * @return  string
 */
public function greeting($name)
{
    return 'Hello, ' . $name . '!';
}
```

For inline comments within functions, the "#" symbol should be used, rather than "//":

```php
/**
 * Returns a greeting.
 * 
 * @access  public
 * @param   string  $name  Name of the person you want to greet
 * @return  string
 */
public function greeting($name)
{
    # Return the greeting
    return 'Hello, ' . $name . '!';
}
```
