# RebelCode - Module Iterator Abstract

[![Build Status](https://travis-ci.org/RebelCode/module-iterator-abstract.svg?branch=develop)](https://travis-ci.org/RebelCode/module-iterator-abstract)
[![Code Climate](https://codeclimate.com/github/RebelCode/module-iterator-abstract/badges/gpa.svg)](https://codeclimate.com/github/RebelCode/module-iterator-abstract)
[![Test Coverage](https://codeclimate.com/github/RebelCode/module-iterator-abstract/badges/coverage.svg)](https://codeclimate.com/github/RebelCode/module-iterator-abstract/coverage)
[![Latest Stable Version](https://poser.pugx.org/rebelcode/module-iterator-abstract/version)](https://packagist.org/packages/rebelcode/module-iterator-abstract)
[![This package complies with Dhii standards](https://img.shields.io/badge/Dhii-Compliant-green.svg?style=flat-square)][Dhii]

## Details
Basic and abstract functionality for module iterators.

### Classes
- [`AbstractDependencyModuleIterator`] - Iterates over [dependency-aware][`DependenciesAwareInterface`] instances in an
order where dependencies come before dependents.


[Dhii]: https://github.com/Dhii/dhii

[`AbstractDependencyModuleIterator`]:                           src/AbstractDependencyModuleIterator.php

[`DependenciesAwareInterface`]:                                 https://github.com/Dhii/module-interface/blob/develop/src/DependenciesAwareInterface.php
