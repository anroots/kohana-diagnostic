kohana-diagnostic
=================

A diagnostic module for Kohana 3.2 based projects. The aim of this module is for different projects to perform self-diagnosis
and report their overall status. This is particularly useful when you are maintaining several (6+) Kohana projects and need
consolidated reports on their statuses.

# Use case

You have multiple projects, in different servers. We don't live in a perfect world so anything could go wrong,
at any time and you want to be notified when this happens, ASAP. Suppose the database drops out,
for whatever reason (maybe someone deleted your projects MySQL user). The servers itself are up and the login page gives a
perfectly good HTTP 200 (for no database queries are run until someone actually tries submitting the login). Normal simplistic
scripts don't detect this and even when they could there's still a lot of checks that can't easily be performed from the
outside world.

## Meet Kohana-Diagnostic

The module provides a simple framework for specifying PHPUnit-style tests for each of your projects.
You can write an arbitrary number of tests and have the output consolidated into a nice JSON format that can be parsed by
automatic scripts that notify You whenever some test fails.

# Installation

Standard Kohana module installation:

* Clone the repository
* Enable the module in bootstrap.php

The init.php file automatically adds a route for the module: `BASE_URL/maintenance/diagnostic/check.json`.
This will be the place to visit to get JSON output from the module. Sample output:

    {
        "status":200,
        "ts":1344086321,
        "module_version":"1.0",
        "results":{
            "test_database":true,
            "test_cache_filesystem":true
        }
    }

# Specifying tests

Create a new controller to `APPPATH/classes/controller/maintenance/diagnostic.php` and have it extend `Diagnostic_Controller`
(an example controller is provided). The controller should contain at least one method that begins with the `test_` prefix.

The module will automatically call all methods that begin with `test_`. Those methods should return a boolean value,
representing the success/failure of the test. The diagnostic run fails (with `{"status": 500}` when even one `test_` method
returns `false`.

# Adding custom output

You can add custom key/value pairs to the final output by calling `$this->add_output($key,
$value);`. The key should be a string, the value any JSON-encodable object/primitive. For example,
consider adding the testable application's version number along with the normal test status:

    $this->add_output('application_version', Kohana::$config->load('app.version'));

Sample output:

    {
        "status":200,
        "ts":1344086321,
        "module_version":"1.0",
        "application_version": "1.2.8-wip",
        "results":{
            "test_database":true,
            "test_cache_filesystem":true
        }
    }

# Parsing the results

The module is most useful when you have automatic tools parsing the output. Anything that can parse JSON is suitable... or you
can just grep for 200 (the success status code).
See [http://sqroot.eu/2012/01/python-check-that-your-projects-are-still-alive/](http://sqroot.eu/2012/01/python-check-that-your-projects-are-still-alive/)
 for an example implementation in Python.

# Licence

The MIT License (MIT)
Copyright (c) 2012 Ando Roots

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.